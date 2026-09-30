<?php
define('APP_ACCESS', true);
require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/Database.php';
require_once '../../includes/functions.php';
require_once '../../includes/security.php';
require_once '../../models/Service.php';
require_once '../../models/ServiceBundle.php';
require_once '../../models/Staff.php';

// Check authentication
requireLogin();

$currentUserRole = $_SESSION['user_role'] ?? '';
$isTechnician = $currentUserRole === 'technician';
$isChiefMechanic = $currentUserRole === 'chief_mechanic';
$isServiceAdviser = $currentUserRole === 'service_adviser';
$isCashier = $currentUserRole === 'cashier';
$isLeadMan = $currentUserRole === 'lead_man';
$isStockman = $currentUserRole === 'stockman';

$isJobOrdersOnlyRole = $isTechnician || $isChiefMechanic || $isLeadMan || $isStockman;
$canManageCatalog = hasAnyRole(['admin', 'cashier', 'service_adviser']);
$canDeleteRecords = hasRole('admin');
$canCreateJobOrder = hasAnyRole(['admin', 'cashier', 'service_adviser']);
$canEditJobOrder = hasAnyRole(['admin', 'cashier', 'service_adviser']);
$canEditJoStatus = hasAnyRole(['admin', 'cashier', 'service_adviser', 'chief_mechanic']);
$canEditJoPayments = hasAnyRole(['admin', 'cashier']);
$canStartJoTimer = hasAnyRole(['admin', 'cashier', 'service_adviser']);
$canStopJoTimer = hasAnyRole(['admin', 'cashier', 'technician', 'service_adviser']);
$canDoneJoTimer = hasAnyRole(['admin', 'cashier', 'technician']);
$isViewOnlyJoRole = $isLeadMan || $isStockman;
$activeJobOrderStatuses = ['pending', 'ongoing', 'under_inspection', 'completed', 'car_washing', 'released', 'returned_for_revision'];

// Job-order-only roles can only access the job_orders tab
if ($isJobOrdersOnlyRole && ($_GET['tab'] ?? 'job_orders') !== 'job_orders') {
    redirect(routeUrl('services', ['tab' => 'job_orders']));
}

$pageTitle = $isJobOrdersOnlyRole ? 'Job Orders' : 'Services Management';

$serviceModel = new Service();
$bundleModel = new ServiceBundle();
$staffModel = new Staff();
$printTemplateSettings = getPrintTemplateSettings();

function dispatchCatalogChangeNotification($title, $action, $subject, $details = '', $referenceType = 'catalog', $referenceId = null) {
    $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
    notifyRoles(
        'system',
        $title,
        buildNotificationMessageTemplate($actorName, $action, $subject, $details),
        ['admin', 'cashier', 'service_adviser'],
        [
            'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId !== null ? (int)$referenceId : null,
        ]
    );
}

// Handle form submission for creating service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_service') {
    try {
        if (!$canManageCatalog) {
            throw new Exception('Insufficient permissions');
        }
        validateCSRF();
        
        // Auto-generate service code if empty
        $serviceCode = !empty($_POST['service_code']) ? sanitize($_POST['service_code']) : $serviceModel->generateServiceCode();
        
        $data = [
            'service_name' => sanitize($_POST['service_name']),
            'service_code' => $serviceCode,
            'description' => sanitize($_POST['description']),
            'service_price' => (float)$_POST['service_price'],
            'labor_cost' => (float)$_POST['labor_cost'],
            'status' => sanitize($_POST['status'])
        ];
        
        $result = $serviceModel->create($data);
        
        if ($result) {
            setMessage('Service created successfully', 'success');
            logActivity($_SESSION['user_id'] ?? 0, 'create_service', 'Created service: ' . $data['service_name']);
            dispatchCatalogChangeNotification(
                'Service Added',
                'added',
                'service ' . $data['service_name'],
                'Price: ₱' . number_format((float)$data['service_price'], 2) . ', Status: ' . strtoupper((string)$data['status']),
                'service',
                (int)$result
            );
            redirect(routeUrl('services', ['tab' => 'services']));
        } else {
            setMessage('Failed to create service. Service code may already exist.', 'error');
        }
    } catch (Exception $e) {
        error_log("Service creation error: " . $e->getMessage());
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

// Handle AJAX requests
if (isset($_GET['action']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'delete_service':
            if (!$canDeleteRecords) {
                echo json_encode(['success' => false, 'message' => 'Only admin can delete records']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $existingService = $serviceModel->findById($id);
            $serviceArchivePayload = Database::getInstance()->fetch("SELECT * FROM services WHERE id = ? LIMIT 1", [$id]);
            if (!is_array($serviceArchivePayload) || empty($serviceArchivePayload)) {
                echo json_encode(['success' => false, 'message' => 'Service not found']);
                exit;
            }
            $serviceInJobOrders = Database::getInstance()->fetch("SELECT COUNT(*) AS count FROM job_order_services WHERE service_id = ?", [$id]);
            $serviceInBundles = Database::getInstance()->fetch("SELECT COUNT(*) AS count FROM bundle_services WHERE service_id = ?", [$id]);
            if ((int)($serviceInJobOrders['count'] ?? 0) > 0 || (int)($serviceInBundles['count'] ?? 0) > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete service (in use)']);
                exit;
            }
            $archiveId = archiveDeletedRecord('service', $serviceArchivePayload, ['source' => 'database', 'meta' => ['table' => 'services']]);
            if (!$archiveId) {
                echo json_encode(['success' => false, 'message' => 'Failed to archive service before deletion']);
                exit;
            }
            $result = $serviceModel->delete($id);
            if ($result) {
                $serviceName = sanitizeTextValue($existingService['service_name'] ?? ('Service #' . $id));
                logActivity($_SESSION['user_id'] ?? 0, 'delete_service', 'Deleted service: ' . $serviceName);
                dispatchCatalogChangeNotification('Service Removed', 'removed', 'service ' . $serviceName, '', 'service', $id);
            }
            if (!$result) {
                deleteArchivedRecordById($archiveId);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Service deleted successfully' : 'Cannot delete service (in use)']);
            exit;
            
        case 'toggle_service':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $beforeService = $serviceModel->findById($id);
            $result = $serviceModel->toggleStatus($id);
            if ($result) {
                $afterService = $serviceModel->findById($id);
                $serviceName = sanitizeTextValue($afterService['service_name'] ?? $beforeService['service_name'] ?? ('Service #' . $id));
                $oldStatus = strtoupper((string)($beforeService['status'] ?? 'unknown'));
                $newStatus = strtoupper((string)($afterService['status'] ?? 'unknown'));
                logActivity($_SESSION['user_id'] ?? 0, 'toggle_service_status', 'Updated service status: ' . $serviceName . ' (' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchCatalogChangeNotification('Service Status Updated', 'updated status for', 'service ' . $serviceName, 'Status: ' . $oldStatus . ' -> ' . $newStatus, 'service', $id);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Status updated' : 'Failed to update status']);
            exit;
            
        case 'delete_bundle':
            if (!$canDeleteRecords) {
                echo json_encode(['success' => false, 'message' => 'Only admin can delete records']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $existingBundle = $bundleModel->findById($id);
            $bundleArchiveRow = Database::getInstance()->fetch("SELECT * FROM service_bundles WHERE id = ? LIMIT 1", [$id]);
            if (!is_array($bundleArchiveRow) || empty($bundleArchiveRow)) {
                echo json_encode(['success' => false, 'message' => 'Bundle not found']);
                exit;
            }
            $bundleInJobOrders = Database::getInstance()->fetch("SELECT COUNT(*) AS count FROM job_order_services WHERE bundle_id = ?", [$id]);
            if ((int)($bundleInJobOrders['count'] ?? 0) > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete bundle (in use)']);
                exit;
            }
            $bundleArchiveServices = Database::getInstance()->fetchAll("SELECT * FROM bundle_services WHERE bundle_id = ?", [$id]);
            $bundleArchiveProducts = Database::getInstance()->fetchAll("SELECT * FROM bundle_products WHERE bundle_id = ?", [$id]);
            $bundleArchiveId = archiveDeletedRecord('service_bundle', [
                'bundle' => $bundleArchiveRow,
                'bundle_services' => $bundleArchiveServices,
                'bundle_products' => $bundleArchiveProducts,
            ], ['source' => 'database', 'meta' => ['table' => 'service_bundles']]);
            if (!$bundleArchiveId) {
                echo json_encode(['success' => false, 'message' => 'Failed to archive bundle before deletion']);
                exit;
            }
            $result = $bundleModel->delete($id);
            if ($result) {
                $bundleName = sanitizeTextValue($existingBundle['bundle_name'] ?? ('Bundle #' . $id));
                logActivity($_SESSION['user_id'] ?? 0, 'delete_bundle', 'Deleted bundle: ' . $bundleName);
                dispatchCatalogChangeNotification('Service Bundle Removed', 'removed', 'bundle ' . $bundleName, '', 'service_bundle', $id);
            }
            if (!$result) {
                deleteArchivedRecordById($bundleArchiveId);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Bundle deleted successfully' : 'Cannot delete bundle (in use)']);
            exit;
            
        case 'toggle_bundle':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $beforeBundle = $bundleModel->findById($id);
            $result = $bundleModel->toggleStatus($id);
            if ($result) {
                $afterBundle = $bundleModel->findById($id);
                $bundleName = sanitizeTextValue($afterBundle['bundle_name'] ?? $beforeBundle['bundle_name'] ?? ('Bundle #' . $id));
                $oldStatus = strtoupper((string)($beforeBundle['status'] ?? 'unknown'));
                $newStatus = strtoupper((string)($afterBundle['status'] ?? 'unknown'));
                logActivity($_SESSION['user_id'] ?? 0, 'toggle_bundle_status', 'Updated bundle status: ' . $bundleName . ' (' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchCatalogChangeNotification('Service Bundle Status Updated', 'updated status for', 'bundle ' . $bundleName, 'Status: ' . $oldStatus . ' -> ' . $newStatus, 'service_bundle', $id);
            }
            echo json_encode(['success' => $result, 'message' => $result ? 'Status updated' : 'Failed to update status']);
            exit;

        case 'update_service':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $beforeService = $serviceModel->findById($id);
            $data = [
                'service_name'  => sanitize($_POST['service_name']),
                'service_code'  => sanitize($_POST['service_code']),
                'description'   => sanitize($_POST['description'] ?? ''),
                'service_price' => (float)$_POST['service_price'],
                'labor_cost'    => (float)$_POST['labor_cost'],
                'status'        => sanitize($_POST['status']),
            ];
            $result = $serviceModel->update($id, $data);
            if ($result) {
                $oldStatus = strtoupper((string)($beforeService['status'] ?? 'unknown'));
                $newStatus = strtoupper((string)($data['status'] ?? 'unknown'));
                logActivity($_SESSION['user_id'] ?? 0, 'update_service', 'Updated service: ' . $data['service_name'] . ' (status ' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchCatalogChangeNotification('Service Updated', 'updated', 'service ' . $data['service_name'], 'Status: ' . $oldStatus . ' -> ' . $newStatus . '; Price: ₱' . number_format((float)$data['service_price'], 2), 'service', $id);
            }
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Service updated successfully' : 'Failed to update service']);
            exit;

        case 'update_bundle':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $beforeBundle = $bundleModel->findById($id);
            $data = [
                'bundle_name'   => sanitize($_POST['bundle_name']),
                'description'   => sanitize($_POST['description'] ?? ''),
                'package_price' => (float)$_POST['package_price'],
                'status'        => sanitize($_POST['status']),
            ];
            $serviceIds = isset($_POST['service_ids']) ? array_map('intval', $_POST['service_ids']) : [];
            $result = $bundleModel->update($id, $data);
            if ($result && !empty($serviceIds)) {
                $bundleModel->updateServices($id, $serviceIds);
            }
            // Save products if provided
            if ($result && isset($_POST['products_json'])) {
                $products = json_decode($_POST['products_json'], true);
                if (is_array($products)) {
                    $bundleModel->updateProducts($id, $products);
                }
            }
            if ($result) {
                $oldStatus = strtoupper((string)($beforeBundle['status'] ?? 'unknown'));
                $newStatus = strtoupper((string)($data['status'] ?? 'unknown'));
                logActivity($_SESSION['user_id'] ?? 0, 'update_bundle', 'Updated bundle: ' . $data['bundle_name'] . ' (status ' . $oldStatus . ' -> ' . $newStatus . ')');
                dispatchCatalogChangeNotification('Service Bundle Updated', 'updated', 'bundle ' . $data['bundle_name'], 'Status: ' . $oldStatus . ' -> ' . $newStatus . '; Price: ₱' . number_format((float)$data['package_price'], 2) . '; Services: ' . count($serviceIds), 'service_bundle', $id);
            }
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Bundle updated successfully' : 'Failed to update bundle']);
            exit;
    }
}

// Get active tab
$activeTab = $_GET['tab'] ?? ($isJobOrdersOnlyRole ? 'job_orders' : 'services');

// Validate tab
$validTabs = ['services', 'job_orders', 'estimates'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'services';
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Build filters
$filters = [
    'limit' => $perPage,
    'offset' => $offset
];

if (!empty($search)) {
    $filters['search'] = $search;
}

if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}

// Get data based on active tab
if ($activeTab === 'services') {
    $services = $serviceModel->getAll($filters);
    $totalRecords = $serviceModel->count($filters);
    $stats = $serviceModel->getStats();
} else {
    // job_orders and estimates tabs — no bundle/service list needed
    $totalRecords = 0;
    $stats = [
        'total_services' => 0,
        'active_services' => 0,
        'inactive_services' => 0,
        'total_bundles' => 0,
        'active_bundles' => 0,
        'inactive_bundles' => 0,
    ];
}

// Get all active services for job order and estimate selectors
$allActiveServices = $serviceModel->getAll(['status' => 'active']);
// Keep existing package data available for legacy records, but package UI is disabled.
$allActiveBundles = $bundleModel->getAll(['status' => 'active']);
// Get all active technicians for job order modal
$allTechnicians = $staffModel->getAll(['role' => 'technician', 'status' => 'active']);
usort($allTechnicians, function ($a, $b) {
    return strcasecmp((string)($a['full_name'] ?? ''), (string)($b['full_name'] ?? ''));
});
// Get all active inventory products for job order modal (temporary)
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, product_code, product_name, selling_price, quantity FROM products WHERE status = 'active' ORDER BY product_name ASC");
    $allInventoryProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allInventoryProducts = [];
}

// Build service sub-items map: parse description lines starting with "- " or "* " as sub-items
$serviceSubItemsMap = [];
foreach ($allActiveServices as $svc) {
    $desc = trim((string)($svc['description'] ?? ''));
    if ($desc === '') continue;
    $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $desc))));
    $subItems = [];
    foreach ($lines as $line) {
        if (strpos($line, '- ') === 0 || strpos($line, '* ') === 0) {
            $subItems[] = substr($line, 2);
        }
    }
    if (!empty($subItems)) {
        $serviceSubItemsMap[(int)$svc['id']] = $subItems;
    }
}

// Map bundle_id -> [service names] for use in print templates
$serviceNamesById = array_column($allActiveServices, 'service_name', 'id');
$bundleServiceNamesMap = [];
$bundleProductsMap = [];
foreach ($allActiveBundles as $bnd) {
    $svcNames = [];
    foreach (($bnd['services'] ?? []) as $svc) {
        $sid = (int)($svc['service_id'] ?? 0);
        if ($sid > 0) {
            $svcNames[] = $serviceNamesById[$sid] ?? ('Service #' . $sid);
        }
    }
    $bundleServiceNamesMap[(int)$bnd['id']] = $svcNames;
    // Map products for this bundle
    $bundleProductsMap[(int)$bnd['id']] = array_map(function($p) {
        return [
            'id' => (int)($p['product_id'] ?? 0),
            'name' => $p['product_name'] ?? '',
            'code' => $p['product_code'] ?? '',
            'price' => (float)($p['selling_price'] ?? 0),
            'qty' => (int)($p['quantity'] ?? 1),
        ];
    }, $bnd['products'] ?? []);
}

$showJobOrderArchive = (($activeTab === 'job_orders') && (isset($_GET['archive']) && $_GET['archive'] === '1'));

// Fetch job orders for the job_orders tab
if ($activeTab === 'job_orders') {
    try {
        $dbConn = Database::getInstance()->getConnection();
        $assignedTotalJo = 0;
        $assignedActiveJo = 0;

        if ($isTechnician) {
            $techStaffId = $_SESSION['user_id'] ?? 0;
            $countRow = $dbConn->prepare("SELECT COUNT(DISTINCT jo.id) AS total_assigned
                FROM job_orders jo
                INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                WHERE jot.technician_id = ?
                AND (jot.status <> 'removed' OR COALESCE(jot.work_duration, 0) > 0)");
            $countRow->execute([$techStaffId]);
            $assignedTotalJo = (int)($countRow->fetch(PDO::FETCH_ASSOC)['total_assigned'] ?? 0);

            $activeJobOrderStatusesForCount = array_values(array_filter(
                $activeJobOrderStatuses,
                static fn($status) => $status !== 'completed'
            ));
            $activeCountSql = "SELECT COUNT(DISTINCT jo.id) AS active_assigned
                FROM job_orders jo
                INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id
                WHERE jot.technician_id = ? AND jot.status IN ('assigned','working')
                AND jo.status IN (" . implode(',', array_fill(0, count($activeJobOrderStatusesForCount), '?')) . ")";
            $activeCountStmt = $dbConn->prepare($activeCountSql);
            $activeCountStmt->execute(array_merge([$techStaffId], $activeJobOrderStatusesForCount));
            $assignedActiveJo = (int)($activeCountStmt->fetch(PDO::FETCH_ASSOC)['active_assigned'] ?? 0);
        }

        $joSearch = $_GET['jo_search'] ?? '';
        $joStatus = $_GET['jo_status'] ?? '';
        $joPaymentStatus = $_GET['jo_payment_status'] ?? '';
        $joWhere  = 'WHERE 1=1';
        $joParams = [];

        // Technicians only see job orders assigned to them
        if ($isTechnician) {
            $techStaffId = $_SESSION['user_id'] ?? 0;
            $joWhere .= " AND EXISTS (
                SELECT 1 FROM job_order_technicians jot
                WHERE jot.job_order_id = jo.id AND jot.technician_id = ?
                AND (jot.status <> 'removed' OR COALESCE(jot.work_duration, 0) > 0)
            )";
            $joParams[] = $techStaffId;
        }

        if ($joSearch) {
            $joWhere .= " AND (jo.job_order_number LIKE ? OR c.full_name LIKE ? OR v.plate_number LIKE ?)";
            $joParams = array_merge($joParams, ["%$joSearch%", "%$joSearch%", "%$joSearch%"]);
        }
        if ($showJobOrderArchive) {
            $joWhere .= " AND jo.status = 'released' AND jo.payment_status = 'paid'";
        } else {
            $joWhere .= " AND NOT (jo.status = 'released' AND jo.payment_status = 'paid')";
            if ($joStatus) {
                $joWhere .= " AND jo.status = ?";
                $joParams[] = $joStatus;
            }
            if (!empty($joPaymentStatus)) {
                $joWhere .= " AND jo.payment_status = ?";
                $joParams[] = $joPaymentStatus;
            }
        }
        $joOrderBy = $showJobOrderArchive
            ? 'jo.updated_at DESC, jo.id DESC'
            : "FIELD(jo.status, 'pending','ongoing','under_inspection','completed','car_washing','released','returned_for_revision','cancelled'), jo.created_at DESC";
        $joStmt = $dbConn->prepare("
            SELECT jo.id, jo.job_order_number, jo.status, jo.payment_status,
                                 jo.total_amount, jo.created_at, jo.updated_at,
                 jo.status_timer_seconds, jo.status_timer_started_at,
                   c.full_name AS customer_name, c.phone AS customer_phone,
                   v.brand, v.model, v.plate_number
            FROM job_orders jo
            LEFT JOIN customers c ON jo.customer_id = c.id
            LEFT JOIN vehicles  v ON jo.vehicle_id  = v.id
            $joWhere
            ORDER BY $joOrderBy
        ");
        $joStmt->execute($joParams);
        $allJobOrders = $joStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $allJobOrders = [];
        $assignedTotalJo = 0;
        $assignedActiveJo = 0;
    }
}

// Fetch estimates for the estimates tab
if ($activeTab === 'estimates') {
    try {
        $dbConn   = Database::getInstance()->getConnection();
        $estSearch = $_GET['est_search'] ?? '';
        $estSql    = "SELECT * FROM job_estimates WHERE 1=1";
        $estParams = [];
        if ($estSearch) {
            $estSql   .= " AND (estimate_number LIKE ? OR vehicle_plate LIKE ? OR vehicle_make LIKE ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
            $estParams = ["%$estSearch%", "%$estSearch%", "%$estSearch%", "%$estSearch%", "%$estSearch%"];
        }
        $estSql .= " ORDER BY created_at DESC";
        $estStmt = $dbConn->prepare($estSql);
        $estStmt->execute($estParams);
        $allEstimates = $estStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $allEstimates = [];
    }
}

$totalPages = ceil($totalRecords / $perPage);

include_once '../partials/header.php';
?>

<script>
function joSearchFilter(val, rowClass) {
    var q = (val || '').toLowerCase().trim();
    var rows = document.getElementsByClassName(rowClass);
    var visible = 0;
    for (var i = 0; i < rows.length; i++) {
        var text = rows[i].getAttribute('data-search-text') || '';
        var show = (!q || text.indexOf(q) !== -1);
        if (show) {
            rows[i].style.removeProperty('display');
            visible++;
        } else {
            rows[i].style.setProperty('display', 'none', 'important');
        }
    }
    var noMatchId = rowClass.replace(/-row$/, '_no_match').replace(/-/g, '_');
    var noMatchEl = document.getElementById(noMatchId);
    if (noMatchEl) noMatchEl.style.display = (visible === 0 && q) ? 'block' : 'none';
}
</script>

<style>
/* Override Bootstrap blue colors to black/grayscale */
.nav-tabs .nav-link {
    color: #000 !important;
}
.nav-tabs .nav-link.active {
    color: #000 !important;
    background-color: #fff !important;
    border-color: #dee2e6 #dee2e6 #fff !important;
    border-bottom: 2px solid #000 !important;
}
.nav-tabs .nav-link:hover {
    border-color: #e9ecef #e9ecef #dee2e6 !important;
    color: #000 !important;
}
.badge.bg-info {
    background-color: #6b6b6b !important;
}
.table th {
    color: #000 !important;
}
.table td {
    color: #000 !important;
}
.card-body h6, .card-body h3, .card-body p {
    color: #000 !important;
}
.text-muted {
    color: #666 !important;
}
.page-link {
    color: #000 !important;
}
.page-link:hover {
    color: #000 !important;
    background-color: #e9ecef !important;
}
.page-item.active .page-link {
    background-color: #2a2a2a !important;
    border-color: #2a2a2a !important;
    color: #fff !important;
}

/* JO technician selector: keep visuals neutral gray */
.jo-tech-check:checked {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}
.jo-tech-check:focus {
    border-color: #6c757d !important;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.2) !important;
}

/* JO status row: compact dropdown + inline timer */
.jo-status-cell {
    min-width: auto;
}
.jo-status-select {
    max-width: none;
    width: auto;
    font-size: 11px;
    padding-top: 0.18rem;
    padding-bottom: 0.18rem;
    font-weight: 500;
    border-radius: 6px;
    white-space: nowrap;
}
.jo-status-timer-wrap {
    min-width: 86px;
    text-align: center;
    font-size: 10.5px;
    color: #6c757d;
    letter-spacing: 0.2px;
}
.jo-status-timer {
    display: inline-block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    background: #f3f3f3;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 1px 6px;
    min-width: 72px;
}
.jo-job-order-filter {
    background: #f7f7f7;
    border: 1px solid #ececec;
    border-radius: 12px;
    padding: 10px 12px;
    margin: 0;
}
.jo-job-order-filter .form-control,
.jo-job-order-filter .form-select {
    height: 36px;
    border-radius: 8px;
    border-color: #dfe3e8;
    background: #fff;
    box-shadow: none;
    font-size: 13px;
}
.jo-job-order-filter .btn {
    min-height: 36px;
    border-radius: 8px;
    font-size: 12px;
    padding: 0.5rem 0.85rem;
}
.jo-job-order-filter .action-group {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 6px;
    flex-wrap: nowrap;
    width: 100%;
    margin-left: 0;
}
.jo-job-order-filter .archive-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
    margin-left: 0;
}
.jo-job-order-filter .col-lg-2,
.jo-job-order-filter .col-md-3,
.jo-job-order-filter .col-md-6,
.jo-job-order-filter .col-sm-6,
.jo-job-order-filter .col-sm-12 {
    padding-left: 6px;
    padding-right: 6px;
}
.jo-timer-controls {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 4px;
}
.jo-timer-btn {
    font-size: 10px;
    line-height: 1;
    padding: 2px 6px;
}
.jo-plate-cell {
    white-space: nowrap;
    min-width: 92px;
}
.jo-actions-cell {
    width: 56px;
    text-align: center;
    white-space: nowrap;
}
.jo-actions-menu,
.je-actions-menu {
    display: inline-flex;
    position: relative;
}
.jo-actions-menu .action-menu-btn,
.je-actions-menu .action-menu-btn {
    width: 34px;
    height: 34px;
    padding: 0;
    border: 1px solid #6c757d;
    border-radius: 8px !important;
    color: #6c757d;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.jo-actions-menu .action-menu-btn:hover,
.jo-actions-menu .action-menu-btn:focus,
.jo-actions-menu .action-menu-btn.show,
.je-actions-menu .action-menu-btn:hover,
.je-actions-menu .action-menu-btn:focus,
.je-actions-menu .action-menu-btn.show {
    background: #6c757d;
    border-color: #6c757d;
    color: #fff;
    box-shadow: none;
}
.jo-actions-menu .dropdown-menu,
.je-actions-menu .dropdown-menu {
    min-width: 150px;
    padding: 4px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    z-index: 1080;
}
.jo-actions-menu .dropdown-item,
.je-actions-menu .dropdown-item {
    font-size: 13px;
    padding: 8px 14px;
}
.jo-actions-desktop {
    display: none !important;
}
.jo-row-under-inspection td {
    background-color: #fdeaea !important;
}
.jo-row-car-washing td {
    background-color: #fff7db !important;
}
.table-responsive,
.table-responsive-actions {
    display: block;
    width: 100%;
    max-height: 500px;
    overflow-x: auto !important;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #7a7a7a #f1f1f1;
}
.table-responsive table,
.table-responsive-actions table {
    min-width: 980px;
    margin-bottom: 0;
}
.table-responsive::-webkit-scrollbar,
.table-responsive-actions::-webkit-scrollbar {
    height: 8px;
}
.table-responsive::-webkit-scrollbar-track,
.table-responsive-actions::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.table-responsive::-webkit-scrollbar-thumb,
.table-responsive-actions::-webkit-scrollbar-thumb {
    background: #7a7a7a;
    border-radius: 10px;
}
.stats-card-row {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1rem;
}
.stats-card-col {
    flex: 1 1 calc(33.333% - 1rem);
    max-width: calc(33.333% - 1rem);
    min-width: 180px;
}
.stats-card {
    min-height: 110px;
}
.stats-card .card-body {
    padding: 14px;
}
.stats-card .card-body h6 {
    font-size: 12px;
}
.stats-card .card-body h2 {
    font-size: 32px;
}
.jo-record-card {
    min-height: 48px;
}
.empty-card-body {
    min-height: 360px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#joSelectedItems,
#jeSelectedItems,
#editJoSelectedItems,
#estSelectedItemsList,
#joProductsList,
#estProductsList {
    min-height: 120px;
    max-height: 360px !important;
    overflow-y: auto;
}

#joSelectedItems input[type="number"] {
    -webkit-appearance: none;
    -moz-appearance: textfield;
    appearance: textfield;
}
#joSelectedItems input[type="number"]::-webkit-inner-spin-button,
#joSelectedItems input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Ensure Add Payment modal appears above other modals */
#addPaymentModal {
    z-index: 1095 !important;
}
#joSubItemSelectModal {
    z-index: 1060 !important;
}
#joTechPickerModal {
    z-index: 1060 !important;
}
.modal-backdrop {
    pointer-events: auto;
}
.modal-backdrop + .modal-backdrop {
    z-index: 1040 !important;
    pointer-events: none !important;
}

/* Gray checkbox for subservices checklist modal */
.jo-subitem-check {
    border-color: #b8bcc2 !important;
}
.jo-subitem-check:checked {
    background-color: #8f959e !important;
    border-color: #8f959e !important;
}
.jo-subitem-check:focus {
    border-color: #8f959e !important;
    box-shadow: 0 0 0 0.2rem rgba(143, 149, 158, 0.25) !important;
}

/* Use a neutral gray checkbox style for Edit Bundle service selection */
.edit-bundle-svc-check {
    border-color: #b8bcc2 !important;
}

.edit-bundle-svc-check:checked {
    background-color: #8f959e !important;
    border-color: #8f959e !important;
}

.edit-bundle-svc-check:focus {
    border-color: #8f959e !important;
    box-shadow: 0 0 0 0.2rem rgba(143, 149, 158, 0.25) !important;
}

/* Use a neutral gray checkbox style for Add Bundle service selection */
.add-bundle-svc-check {
    border-color: #b8bcc2 !important;
}

.add-bundle-svc-check:checked {
    background-color: #8f959e !important;
    border-color: #8f959e !important;
}

.add-bundle-svc-check:focus {
    border-color: #8f959e !important;
    box-shadow: 0 0 0 0.2rem rgba(143, 149, 158, 0.25) !important;
}

/* Use a neutral gray checkbox style for bundle product selection */
.add-bundle-prod-check,
.edit-bundle-prod-check {
    border-color: #b8bcc2 !important;
}

.add-bundle-prod-check:checked,
.edit-bundle-prod-check:checked {
    background-color: #8f959e !important;
    border-color: #8f959e !important;
}

.add-bundle-prod-check:focus,
.edit-bundle-prod-check:focus {
    border-color: #8f959e !important;
    box-shadow: 0 0 0 0.2rem rgba(143, 149, 158, 0.25) !important;
}

#addBundleModal,
#editBundleModal,
#joTabBundles,
#jeTabBundles,
#editJoTabBundles,
a[href="#joTabBundles"],
a[href="#jeTabBundles"],
a[href="#editJoTabBundles"],
a[href="?tab=bundles"] {
    display: none !important;
}

/* Use a neutral gray checkbox style for Estimate service/bundle selection */
.estimate-item {
    border-color: #b8bcc2 !important;
}

.estimate-item:checked {
    background-color: #8f959e !important;
    border-color: #8f959e !important;
}

.estimate-item:focus {
    border-color: #8f959e !important;
    box-shadow: 0 0 0 0.2rem rgba(143, 149, 158, 0.25) !important;
}

@media (max-width: 768px) {
    .stats-card-col {
        flex: 1 1 100%;
        max-width: 100%;
        min-width: 0;
    }

    .stats-card .card-body h2 {
        font-size: 24px;
    }

    .jo-record-card {
        align-items: flex-start !important;
        gap: 8px;
    }

    .jo-record-card .btn {
        min-height: 32px;
        font-size: 11px !important;
        padding: 4px 8px !important;
    }

    #createJobOrderModal .modal-body,
    #editJobOrderModal .modal-body,
    #jobEstimateModal .modal-body {
        padding: 12px !important;
    }

    #createJobOrderModal .card-body,
    #editJobOrderModal .card-body,
    #jobEstimateModal .card-body {
        padding: 10px !important;
    }

    #createJobOrderModal .card-header,
    #editJobOrderModal .card-header,
    #jobEstimateModal .card-header {
        padding: 8px 10px !important;
    }

    #createJobOrderModal .col-4,
    #jobEstimateModal .col-4,
    #editJobOrderModal .col-4 {
        width: 100%;
    }

    #createJobOrderModal .d-flex.gap-1,
    #jobEstimateModal .d-flex.gap-1 {
        flex-wrap: wrap;
    }

    #jo_product_qty,
    #est_product_qty {
        width: 100% !important;
    }

    #joSelectedItems,
    #editJoSelectedItems,
    #estSelectedItemsList,
    #joProductsList,
    #estProductsList {
        max-height: 180px !important;
    }

    .table-responsive-actions {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive-actions table,
    .table-responsive table {
        min-width: 760px;
    }

    .actions-desktop {
        display: none !important;
    }

    .actions-mobile {
        display: inline-flex !important;
    }

    /* Mobile: make status select clearer */
    .jo-status-select {
        max-width: none !important;
        width: auto !important;
        font-size: 12px !important;
        padding: 4px 24px 4px 8px !important;
        font-weight: 600;
        white-space: nowrap;
    }

    .jo-status-cell {
        min-width: auto;
    }

    /* Mobile: timer controls cleaner */
    .jo-timer-btn {
        font-size: 11px;
        padding: 3px 8px;
    }

    .jo-status-timer {
        font-size: 11px;
        padding: 2px 8px;
    }

    /* Mobile: action dropdown items cleaner */
    .actions-mobile .dropdown-menu {
        min-width: 140px;
        padding: 4px 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 8px;
    }

    .actions-mobile .dropdown-item {
        font-size: 13px;
        padding: 8px 14px;
    }

    .actions-mobile .action-menu-btn {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 4px 8px;
    }
}

@media (max-width: 576px) {
    .stats-card {
        min-height: 90px;
    }

    #createJobOrderModal .modal-dialog,
    #viewJobOrderModal .modal-dialog,
    #editEstimateModal .modal-dialog {
        margin: 8px;
        max-width: calc(100% - 16px);
    }

    #createJobOrderModal .modal-body,
    #viewJobOrderModal .modal-body {
        padding: 12px;
    }

    #createJobOrderModal .modal-footer,
    #editJobOrderModal .modal-footer,
    #jobEstimateModal .modal-footer {
        padding: 10px !important;
        gap: 8px;
    }

    #createJobOrderModal .modal-footer .btn,
    #editJobOrderModal .modal-footer .btn,
    #jobEstimateModal .modal-footer .btn {
        width: 100%;
    }

    /* View JO modal: responsive tables */
    #viewJobOrderModal table {
        font-size: 9px !important;
    }

    #viewJobOrderModal .modal-body {
        padding: 10px;
    }
}

.actions-mobile {
    display: none;
}

.je-actions-menu.actions-mobile {
    display: inline-flex;
}

.actions-mobile .dropdown-toggle::after {
    display: none;
}

.services-mgmt-shell .sm-header {
    align-items: center;
}

.services-mgmt-shell .sm-action-btn {
    min-width: 180px;
    border-radius: 10px;
    font-weight: 600;
}

.services-mgmt-shell .sm-main-tabs {
    gap: 8px;
    border-bottom-color: #e6e6e6;
}

.services-mgmt-shell .sm-main-tabs .nav-link {
    border: 1px solid transparent;
    border-radius: 10px 10px 0 0;
    padding: 9px 14px;
    font-weight: 500;
}

.services-mgmt-shell .sm-main-tabs .nav-link.active {
    border: 1px solid #d7d7d7 !important;
    border-bottom: 2px solid #111 !important;
    background: #fff !important;
}

.services-mgmt-shell .sm-filter-card .card-body {
    padding: 14px;
}

.services-mgmt-shell .sm-filter-form .btn,
.services-mgmt-shell .sm-filter-form .form-control,
.services-mgmt-shell .sm-filter-form .form-select {
    min-height: 38px;
}

@media (max-width: 768px) {
    .services-mgmt-shell {
        padding-left: 0;
        padding-right: 0;
    }

    .services-mgmt-shell .sm-header {
        gap: 10px;
        margin-bottom: 12px !important;
    }

    .services-mgmt-shell .sm-action-col {
        text-align: left !important;
    }

    .services-mgmt-shell .sm-action-btn {
        width: 100%;
        min-width: 0;
    }

    .services-mgmt-shell .stats-card-row {
        gap: 8px;
        margin-bottom: 12px !important;
        flex-wrap: nowrap;
        overflow: hidden;
        overflow-y: hidden;
        justify-content: flex-start;
    }

    .services-mgmt-shell .stats-card-col {
        flex: 1 1 0;
        max-width: none;
        min-width: 0;
    }

    .services-mgmt-shell .stats-card {
        min-height: 76px;
        border-radius: 14px;
    }

    .services-mgmt-shell .stats-card .card-body {
        padding: 10px 8px;
    }

    .services-mgmt-shell .stats-card .card-body h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }

    .services-mgmt-shell .stats-card .card-body h2 {
        font-size: 18px;
    }

    .services-mgmt-shell .sm-main-tabs {
        flex-wrap: wrap;
        overflow: visible;
        gap: 6px;
        padding-bottom: 0;
        margin-bottom: 10px !important;
    }

    .services-mgmt-shell .sm-main-tabs .nav-item {
        flex: 0 0 calc(50% - 4px);
        max-width: calc(50% - 4px);
    }

    .services-mgmt-shell .sm-main-tabs .nav-link {
        white-space: normal;
        width: 100%;
        text-align: center;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 13px;
    }

    .services-mgmt-shell .sm-filter-card .card-body {
        padding: 12px;
    }

    .services-mgmt-shell .sm-filter-form {
        row-gap: 8px;
    }

    .services-mgmt-shell .sm-filter-form .col-md-4,
    .services-mgmt-shell .sm-filter-form .col-md-3,
    .services-mgmt-shell .sm-filter-form .col-md-2 {
        width: 100%;
    }

}

</style>

<div class="container-fluid services-mgmt-shell">
    <!-- Header -->
    <div class="row mb-3 sm-header">
        <div class="col-md-6 sm-title-col">
            <h4 style="color: #000;">Services Management</h4>
            <p class="mb-0" style="color: #666;">Manage individual automotive services</p>
        </div>
        <div class="col-md-6 text-end sm-action-col">
            <?php if ($activeTab === 'services'): ?>
                <button type="button" class="btn btn-primary sm-action-btn" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-circle"></i> Add Service
                </button>
            <?php elseif ($activeTab === 'job_orders' && $canCreateJobOrder): ?>
                <button type="button" class="btn btn-primary sm-action-btn" data-bs-toggle="modal" data-bs-target="#createJobOrderModal">
                    <i class="bi bi-plus-circle"></i> Create Job Order
                </button>
            <?php elseif ($activeTab === 'estimates' && $canManageCatalog): ?>
                <button type="button" class="btn btn-primary sm-action-btn" data-bs-toggle="modal" data-bs-target="#jobEstimateModal">
                    <i class="bi bi-calculator"></i> New Estimate
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if ($activeTab === 'services'): ?>
    <div class="stats-card-row mb-3">
        <div class="stats-card-col">
            <div class="card h-100 stats-card">
                <div class="card-body text-center">
                    <h6 class="mb-1" style="color: #666; font-size: 13px;">Total <?php echo $activeTab === 'services' ? 'Services' : 'Packages'; ?></h6>
                    <h2 class="mb-0" style="color: #000; font-weight: 700;"><?php echo intval($activeTab === 'services' ? ($stats['total_services'] ?? 0) : ($stats['total_bundles'] ?? 0)); ?></h2>
                </div>
            </div>
        </div>
        <div class="stats-card-col">
            <div class="card h-100 stats-card">
                <div class="card-body text-center">
                    <h6 class="mb-1" style="color: #666; font-size: 13px;">Active</h6>
                    <h2 class="mb-0" style="color: #000; font-weight: 700;"><?php echo intval($activeTab === 'services' ? ($stats['active_services'] ?? 0) : ($stats['active_bundles'] ?? 0)); ?></h2>
                </div>
            </div>
        </div>
        <div class="stats-card-col">
            <div class="card h-100 stats-card">
                <div class="card-body text-center">
                    <h6 class="mb-1" style="color: #666; font-size: 13px;">Inactive</h6>
                    <h2 class="mb-0" style="color: #000; font-weight: 700;"><?php echo intval($activeTab === 'services' ? ($stats['inactive_services'] ?? 0) : ($stats['inactive_bundles'] ?? 0)); ?></h2>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3 sm-main-tabs">
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'job_orders' ? 'active' : ''; ?>" 
               href="?tab=job_orders"
               style="color: #000; <?php echo $activeTab === 'job_orders' ? 'background: #fff; border-bottom: 2px solid #000;' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i> Job Orders
            </a>
        </li>
        <?php if (!$isJobOrdersOnlyRole): ?>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'estimates' ? 'active' : ''; ?>" 
               href="?tab=estimates"
               style="color: #000; <?php echo $activeTab === 'estimates' ? 'background: #fff; border-bottom: 2px solid #000;' : ''; ?>">
                <i class="bi bi-calculator"></i> Job Estimate
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'services' ? 'active' : ''; ?>" 
               href="?tab=services"
               style="color: #000; <?php echo $activeTab === 'services' ? 'background: #fff; border-bottom: 2px solid #000;' : ''; ?>">
                <i class="bi bi-wrench"></i> Services
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Search and Filter -->
    <?php if ($activeTab === 'services'): ?>
    <div class="card mb-3 sm-filter-card">
        <div class="card-body">
            <form method="GET" class="row g-3 sm-filter-form">
                <input type="hidden" name="tab" value="<?php echo escape($activeTab); ?>">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search..." value="<?php echo escape($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="?tab=<?php echo escape($activeTab); ?>" class="btn btn-secondary w-100">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Services Table -->
    <?php if ($activeTab === 'services'): ?>
        <div class="card">
            <div class="card-body">
                <?php if (empty($services)): ?>
                    <div class="text-center py-2 empty-card-body"></div>
                <?php else: ?>
                    <div class="table-responsive table-responsive-actions">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service Code</th>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Base Price</th>
                                    <th>Labor Cost</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong><?php echo escape($service['service_code']); ?></strong></td>
                                    <td><?php echo escape($service['service_name']); ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php
                                                // Show only the non-checklist description (strip lines starting with "- " or "* ")
                                                $rawDesc = $service['description'] ?? '';
                                                $descLines = array_filter(
                                                    explode("\n", str_replace("\r", '', $rawDesc)),
                                                    fn($l) => !(str_starts_with(trim($l), '- ') || str_starts_with(trim($l), '* '))
                                                );
                                                $cleanDesc = trim(implode("\n", $descLines)) ?: 'N/A';
                                                echo escape(substr($cleanDesc, 0, 50));
                                                echo strlen($cleanDesc) > 50 ? '...' : '';
                                            ?>
                                        </small>
                                    </td>
                                    <td><?php echo formatCurrency($service['service_price']); ?></td>
                                    <td><?php echo formatCurrency($service['labor_cost']); ?></td>
                                    <td><strong><?php echo formatCurrency($service['service_price'] + $service['labor_cost']); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $service['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($service['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm actions-desktop">
                                            <button type="button" class="btn btn-outline-dark py-0 px-2" onclick="editServiceFromButton(this)" data-service-id="<?php echo (int)$service['id']; ?>" data-service-name="<?php echo escape((string)($service['service_name'] ?? '')); ?>" data-service-code="<?php echo escape((string)($service['service_code'] ?? '')); ?>" data-service-description="<?php echo escape((string)($service['description'] ?? '')); ?>" data-service-price="<?php echo (float)($service['service_price'] ?? 0); ?>" data-service-labor="<?php echo (float)($service['labor_cost'] ?? 0); ?>" data-service-status="<?php echo escape((string)($service['status'] ?? 'active')); ?>" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="toggleStatus('service', <?php echo $service['id']; ?>)" title="<?php echo $service['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <?php if ($canDeleteRecords): ?>
                                            <button type="button" class="btn btn-outline-danger py-0 px-2" onclick="deleteItem('service', <?php echo $service['id']; ?>, <?php echo htmlspecialchars(json_encode($service['service_name'] ?? 'Unknown'), ENT_QUOTES); ?>)" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dropdown action-dropdown actions-mobile je-actions-menu">
                                            <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="actionDropdownService<?php echo $service['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Service actions">
                                                <i class="bi bi-three-dots-vertical"></i>
                                                <span class="visually-hidden">Service actions</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdownService<?php echo $service['id']; ?>">
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="editServiceFromButton(this)" data-service-id="<?php echo (int)$service['id']; ?>" data-service-name="<?php echo escape((string)($service['service_name'] ?? '')); ?>" data-service-code="<?php echo escape((string)($service['service_code'] ?? '')); ?>" data-service-description="<?php echo escape((string)($service['description'] ?? '')); ?>" data-service-price="<?php echo (float)($service['service_price'] ?? 0); ?>" data-service-labor="<?php echo (float)($service['labor_cost'] ?? 0); ?>" data-service-status="<?php echo escape((string)($service['status'] ?? 'active')); ?>">
                                                        <i class="bi bi-pencil"></i>Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="toggleStatus('service', <?php echo $service['id']; ?>)">
                                                        <i class="bi bi-arrow-repeat"></i><?php echo $service['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                    </button>
                                                </li>
                                                <?php if ($canDeleteRecords): ?>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger" onclick="deleteItem('service', <?php echo $service['id']; ?>, <?php echo htmlspecialchars(json_encode($service['service_name'] ?? 'Unknown'), ENT_QUOTES); ?>)">
                                                        <i class="bi bi-trash"></i>Delete
                                                    </button>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    
    <!-- Bundles Table -->
    <?php elseif ($activeTab === 'bundles'): ?>
        <div class="card">
            <div class="card-body">
                <?php if (empty($bundles)): ?>
                    <div class="text-center py-2 empty-card-body"></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Package Name</th>
                                    <th>Description</th>
                                    <th>Package Price</th>
                                    <th>Included</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bundles as $bundle): ?>
                                <tr>
                                    <td><strong><?php echo escape($bundle['bundle_name']); ?></strong></td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo escape(substr($bundle['description'] ?? 'N/A', 0, 50)); ?>
                                            <?php echo strlen($bundle['description'] ?? '') > 50 ? '...' : ''; ?>
                                        </small>
                                    </td>
                                    <td><strong><?php echo formatCurrency($bundle['package_price']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($bundle['services'])): ?>
                                        <div class="mb-1">
                                            <small class="fw-bold text-muted d-block" style="font-size:10px;letter-spacing:.3px;">SERVICES</small>
                                            <?php foreach ($bundle['services'] as $svc): ?>
                                            <small class="d-block" style="font-size:11px;">• <?php echo escape($svc['service_name']); ?></small>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($bundle['products'])): ?>
                                        <div>
                                            <small class="fw-bold text-muted d-block" style="font-size:10px;letter-spacing:.3px;">PRODUCTS</small>
                                            <?php foreach ($bundle['products'] as $prod): ?>
                                            <small class="d-block" style="font-size:11px;">• <?php echo escape($prod['product_name']); ?> (x<?php echo (int)$prod['quantity']; ?>)</small>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (empty($bundle['services']) && empty($bundle['products'])): ?>
                                        <small class="text-muted">None</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $bundle['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($bundle['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button onclick="editBundle(<?php echo $bundle['id']; ?>, '<?php echo addslashes(escape($bundle['bundle_name'])); ?>', '<?php echo addslashes(escape($bundle['description'] ?? '')); ?>', <?php echo $bundle['package_price']; ?>, '<?php echo $bundle['status']; ?>', [<?php echo implode(',', array_column($bundle['services'], 'service_id')); ?>])" 
                                                class="btn btn-sm btn-primary btn-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="toggleStatus('bundle', <?php echo $bundle['id']; ?>)" 
                                                class="btn btn-sm btn-warning btn-icon" title="Toggle Status">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                        <?php if ($canDeleteRecords): ?>
                                        <button onclick="deleteItem('bundle', <?php echo $bundle['id']; ?>, <?php echo htmlspecialchars(json_encode($bundle['bundle_name'] ?? 'Unknown'), ENT_QUOTES); ?>"
                                                class="btn btn-sm btn-danger btn-icon" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Job Orders Tab -->
    <?php if ($activeTab === 'job_orders'): ?>
        <!-- Search/filter bar -->
        <form method="GET" class="row g-2 align-items-center jo-job-order-filter mb-3">
            <input type="hidden" name="tab" value="job_orders">
            <?php if ($showJobOrderArchive): ?>
            <input type="hidden" name="archive" value="1">
            <?php endif; ?>
            <div class="col-lg-4 col-md-5 col-sm-12">
                <input type="text" name="jo_search" class="form-control"
                       placeholder="Search by JO#, customer, plate..."
                       value="<?php echo escape($_GET['jo_search'] ?? ''); ?>">
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <select name="jo_payment_status" class="form-select" style="min-width: 110px;" <?php echo $showJobOrderArchive ? 'disabled' : ''; ?>>
                   <option value="">All Payment</option>
                   <?php foreach (['pending','partial','paid'] as $ps): ?>
                   <option value="<?php echo $ps; ?>" <?php echo (($_GET['jo_payment_status'] ?? '') === $ps) ? 'selected' : ''; ?>>
                       <?php echo ucfirst($ps); ?>
                   </option>
                   <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6">
                <select name="jo_status" class="form-select" style="min-width: 120px;" <?php echo $showJobOrderArchive ? 'disabled' : ''; ?>>
                   <option value="">All Status</option>
                   <?php foreach (['pending','ongoing','under_inspection','completed','car_washing','released','returned_for_revision','cancelled'] as $s): ?>
                   <option value="<?php echo $s; ?>" <?php echo (($_GET['jo_status'] ?? '') === $s) ? 'selected' : ''; ?>>
                       <?php echo ucfirst(str_replace('_',' ',$s)); ?>
                   </option>
                   <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="d-flex align-items-center gap-2 w-100">
                    <button type="submit" class="btn btn-dark"><i class="bi bi-search"></i> Search</button>
                    <a href="?tab=job_orders" class="btn btn-secondary"><i class="bi bi-x"></i> Clear</a>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <?php if ($showJobOrderArchive): ?>
                        <a href="?tab=job_orders" class="btn btn-outline-dark btn-sm px-2 py-1" style="min-height:31px;"><i class="bi bi-arrow-left-circle"></i> Back</a>
                        <?php else: ?>
                        <a href="?tab=job_orders&archive=1" class="btn btn-outline-secondary btn-sm px-2 py-1" style="min-height:31px;"><i class="bi bi-archive"></i> Archive</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
        <?php if ($isTechnician): ?>
        <div class="mt-2 small text-muted">
            Assigned Active Job Orders: <strong><?php echo (int)$assignedActiveJo; ?></strong>
            <span class="mx-1">|</span>
            Assigned Total Job Orders: <strong><?php echo (int)$assignedTotalJo; ?></strong>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($allJobOrders)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-text" style="font-size:3rem;color:#ccc;"></i>
                        <p class="text-muted mt-3"><?php echo $isTechnician ? 'No job orders assigned to you' : (($isChiefMechanic || $isServiceAdviser) ? 'No active job orders found' : 'No job orders found'); ?></p>
                        <?php if ($canCreateJobOrder): ?>
                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#createJobOrderModal">
                            <i class="bi bi-plus-circle"></i> Create Job Order
                        </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px;">
                        <thead style="background:#f8f8f8;">
                            <tr>
                                <th class="px-3">JO #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th class="jo-plate-cell">Plate</th>
                                <?php if (!$isViewOnlyJoRole): ?><th>Amount</th>
                                <th>Payment</th><?php endif; ?>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Timer</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allJobOrders as $jo): ?>
                            <?php
                                $payColors = ['pending'=>'secondary','partial'=>'warning','paid'=>'success'];
                                $pc = $payColors[$jo['payment_status']] ?? 'secondary';
                                $rowTimerBase = (int)($jo['status_timer_seconds'] ?? 0);
                                $rowTimerRunning = in_array($jo['status'], ['ongoing', 'under_inspection', 'returned_for_revision'], true) && !empty($jo['status_timer_started_at']);
                                if ($rowTimerRunning) {
                                    $rowTimerBase += max(0, time() - strtotime($jo['status_timer_started_at']));
                                }
                                $rowTimerVisible = in_array($jo['status'], ['ongoing', 'under_inspection', 'returned_for_revision', 'completed'], true);
                                $rowTimerLocked = $jo['status'] === 'completed';
                            ?>
                            <tr id="jo-row-<?php echo $jo['id']; ?>" class="<?php echo $jo['status'] === 'under_inspection' ? 'jo-row-under-inspection' : ($jo['status'] === 'car_washing' ? 'jo-row-car-washing' : ''); ?>">
                                <td class="px-3 fw-bold"><?php echo escape($jo['job_order_number']); ?></td>
                                <td>
                                    <div><?php echo escape($jo['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo escape($jo['customer_phone']); ?></small>
                                </td>
                                <td><?php echo escape(trim($jo['brand'].' '.$jo['model'])); ?></td>
                                <td class="jo-plate-cell"><?php echo escape($jo['plate_number'] ?? '—'); ?></td>
                                <?php if (!$isViewOnlyJoRole): ?><td><?php echo formatCurrency($jo['total_amount']); ?></td>
                                <td><span class="badge bg-<?php echo $pc; ?>"><?php echo ucfirst($jo['payment_status']); ?></span></td><?php endif; ?>
                                <td><?php echo date('M d, Y', strtotime($jo['created_at'])); ?></td>
                                <td>
                                    <select
                                        id="jo-status-select-<?php echo $jo['id']; ?>"
                                        class="form-select form-select-sm jo-status-select"
                                        data-prev="<?php echo $jo['status']; ?>"
                                        data-version="<?php echo escape((string)($jo['updated_at'] ?? '')); ?>"
                                        <?php if (!$canEditJoStatus): ?>disabled<?php endif; ?>
                                        onchange="updateJoStatusInline(<?php echo $jo['id']; ?>, this.value, this)">
                                        <?php foreach (['pending','ongoing','under_inspection','completed','car_washing','released','returned_for_revision','cancelled'] as $statusOption): ?>
                                        <?php
                                            $isBlockedForServiceAdviser = $isServiceAdviser
                                                && $jo['status'] === 'completed'
                                                && in_array($statusOption, ['pending','ongoing','under_inspection','car_washing','cancelled'], true);
                                            $isBlockedForChiefMechanic = $isChiefMechanic
                                                && $jo['status'] === 'completed'
                                                && in_array($statusOption, ['pending','ongoing','under_inspection','car_washing','cancelled'], true);

                                            $isReleasedBackflowBlockedForServiceAdviser = $isServiceAdviser
                                                && $jo['status'] === 'released'
                                                && in_array($statusOption, ['pending','ongoing','under_inspection','car_washing','completed','cancelled'], true);
                                            $isReleasedBackflowBlockedForChiefMechanic = $isChiefMechanic
                                                && $jo['status'] === 'released'
                                                && in_array($statusOption, ['pending','ongoing','under_inspection','car_washing','completed','cancelled'], true);

                                            $isCancelledFullyLockedForServiceAdviser = $isServiceAdviser
                                                && $jo['status'] === 'cancelled';
                                            $isCancelledFullyLockedForChiefMechanic = $isChiefMechanic
                                                && $jo['status'] === 'cancelled';

                                            $isOptionBlocked = $isBlockedForServiceAdviser
                                                || $isBlockedForChiefMechanic
                                                || $isReleasedBackflowBlockedForServiceAdviser
                                                || $isReleasedBackflowBlockedForChiefMechanic
                                                || $isCancelledFullyLockedForServiceAdviser
                                                || $isCancelledFullyLockedForChiefMechanic;
                                        ?>
                                        <option value="<?php echo $statusOption; ?>" <?php echo $jo['status'] === $statusOption ? 'selected' : ''; ?> <?php echo $isOptionBlocked ? 'disabled' : ''; ?>>
                                            <?php echo ucfirst(str_replace('_', ' ', $statusOption)); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="text-center align-middle">
                                    <div
                                        id="jo-status-timer-wrap-<?php echo $jo['id']; ?>"
                                        class="jo-status-timer-wrap"
                                        style="<?php echo $rowTimerVisible ? '' : 'display:none;'; ?>"
                                    >
                                        <span
                                            id="jo-status-timer-<?php echo $jo['id']; ?>"
                                            class="jo-status-timer"
                                            data-seconds="<?php echo $rowTimerBase; ?>"
                                            data-running="<?php echo $rowTimerRunning ? '1' : '0'; ?>"
                                        >00:00:00</span>
                                        <?php if ($canStartJoTimer || $canStopJoTimer || $canDoneJoTimer): ?>
                                        <div class="jo-timer-controls" id="jo-timer-controls-<?php echo $jo['id']; ?>" style="<?php echo $rowTimerLocked ? 'display:none;' : ''; ?>">
                                            <?php if ($canStartJoTimer && !$rowTimerLocked): ?>
                                            <button
                                                type="button"
                                                id="jo-timer-start-<?php echo $jo['id']; ?>"
                                                class="btn btn-outline-secondary btn-sm jo-timer-btn"
                                                onclick="controlJoTimer(<?php echo $jo['id']; ?>, 'start', this)">
                                                Start
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($canStopJoTimer && !$isTechnician && !$rowTimerLocked): ?>
                                            <button
                                                type="button"
                                                id="jo-timer-stop-<?php echo $jo['id']; ?>"
                                                class="btn btn-outline-secondary btn-sm jo-timer-btn"
                                                onclick="controlJoTimer(<?php echo $jo['id']; ?>, 'stop', this)">
                                                Stop
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($canDoneJoTimer && !$rowTimerLocked): ?>
                                            <button
                                                type="button"
                                                id="jo-timer-done-<?php echo $jo['id']; ?>"
                                                class="btn btn-outline-danger btn-sm jo-timer-btn"
                                                onclick="controlJoTimer(<?php echo $jo['id']; ?>, 'done', this)">
                                                Done
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($jo['status'] === 'under_inspection' && hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser'])): ?>
                                        <div class="d-flex gap-1 mt-1 justify-content-center" id="jo-inspection-btns-<?php echo $jo['id']; ?>">
                                            <button type="button" class="btn btn-success btn-sm jo-timer-btn" onclick="inspectionAction(<?php echo $jo['id']; ?>, 'pass')" title="Pass Inspection">
                                                <i class="bi bi-check-lg"></i> Pass
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm jo-timer-btn" onclick="inspectionAction(<?php echo $jo['id']; ?>, 'revision')" title="Needs Revision">
                                                <i class="bi bi-arrow-repeat"></i> Revision
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="jo-actions-cell">
                                    <div class="btn-group btn-group-sm jo-actions-desktop">
                                        <!-- View — always visible -->
                                        <button class="btn btn-outline-secondary py-0 px-2" onclick="viewJobOrder(<?php echo $jo['id']; ?>)" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($canEditJobOrder): ?>
                                        <button class="btn btn-outline-dark py-0 px-2" onclick="editJobOrder(<?php echo $jo['id']; ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-primary py-0 px-2" onclick="openPrintChooser(<?php echo $jo['id']; ?>)" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary py-0 px-2" onclick="_pdfMode = true; printJobOrder(<?php echo $jo['id']; ?>).then(() => { _pdfMode = false; });" title="Save PDF">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </button>
                                        <?php if (hasRole('admin')): ?>
                                        <button class="btn btn-outline-danger py-0 px-2" onclick="deleteJobOrder(<?php echo $jo['id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown action-dropdown jo-actions-menu">
                                        <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="joActionsMobile<?php echo $jo['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Job order actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="joActionsMobile<?php echo $jo['id']; ?>">
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="viewJobOrder(<?php echo $jo['id']; ?>)">
                                                    <i class="bi bi-eye me-2"></i>View
                                                </button>
                                            </li>
                                            <?php if ($canEditJobOrder): ?>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="editJobOrder(<?php echo $jo['id']; ?>)">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="openPrintChooser(<?php echo $jo['id']; ?>)">
                                                    <i class="bi bi-printer me-2"></i>Print
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="_pdfMode = true; printJobOrder(<?php echo $jo['id']; ?>).then(() => { _pdfMode = false; });">
                                                    <i class="bi bi-file-earmark-pdf me-2"></i>Save PDF
                                                </button>
                                            </li>
                                            <?php if (hasRole('admin')): ?>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="deleteJobOrder(<?php echo $jo['id']; ?>)">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        const canEditJoStatus = <?php echo $canEditJoStatus ? 'true' : 'false'; ?>;
        const canStartJoTimer = <?php echo $canStartJoTimer ? 'true' : 'false'; ?>;
        const canStopJoTimer = <?php echo $canStopJoTimer ? 'true' : 'false'; ?>;
        const canDoneJoTimer = <?php echo $canDoneJoTimer ? 'true' : 'false'; ?>;

        const actionMenuState = new WeakMap();
        document.addEventListener('show.bs.dropdown', function (event) {
            const button = event.target;
            const menuContainer = button.closest('.jo-actions-menu, .je-actions-menu');
            const menu = menuContainer?.querySelector('.dropdown-menu');
            if (!menuContainer || !menu) return;

            const placeholder = document.createComment('actions-menu');
            menu.parentNode.insertBefore(placeholder, menu);
            menu.classList.add('action-dropdown-menu');
            document.body.appendChild(menu);
            actionMenuState.set(button, { menu, placeholder });
        });

        document.addEventListener('shown.bs.dropdown', function (event) {
            const button = event.target;
            const state = actionMenuState.get(button);
            if (!state) return;
            const menu = state.menu;
            const buttonRect = button.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.visibility = 'hidden';
            menu.style.display = 'block';
            const menuHeight = menu.scrollHeight;
            const menuWidth = menu.offsetWidth;
            const gap = 6;
            const top = buttonRect.bottom + menuHeight + gap <= window.innerHeight
                ? buttonRect.bottom + gap
                : Math.max(gap, buttonRect.top - menuHeight - gap);
            const left = Math.min(
                Math.max(gap, buttonRect.right - menuWidth),
                window.innerWidth - menuWidth - gap
            );
            menu.style.top = top + 'px';
            menu.style.left = left + 'px';
            menu.style.right = 'auto';
            menu.style.transform = 'none';
            menu.style.visibility = 'visible';
        });

        document.addEventListener('hidden.bs.dropdown', function (event) {
            const state = actionMenuState.get(event.target);
            if (!state) return;
            const menu = state.menu;
            state.placeholder.parentNode.insertBefore(menu, state.placeholder);
            state.placeholder.remove();
            menu.style.position = '';
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.transform = '';
            menu.style.display = '';
            menu.style.visibility = '';
            actionMenuState.delete(event.target);
        });

        function updateJoRowHighlight(id, status) {
            const row = document.getElementById('jo-row-' + id);
            if (!row) return;
            if (status === 'under_inspection') {
                row.classList.add('jo-row-under-inspection');
                row.classList.remove('jo-row-car-washing');
            } else if (status === 'car_washing') {
                row.classList.add('jo-row-car-washing');
                row.classList.remove('jo-row-under-inspection');
            } else {
                row.classList.remove('jo-row-under-inspection');
                row.classList.remove('jo-row-car-washing');
            }
        }

        async function deleteJobOrder(id) {
            const response = await fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id);
            const result = await response.json();
            const jo = result.success ? result.data : null;
            const details = jo
                ? `JO: ${jo.job_order_number || id}\nCustomer: ${jo.customer_name || '—'}\nPlate: ${jo.plate_number || jo.vehicle_license || '—'}\nAmount: ₱${Number(jo.total_amount || 0).toFixed(2)}`
                : `Job Order ID: ${id}`;
            appConfirm(`Delete this job order?\n\n${details}`, {
                title: 'Delete Job Order',
                confirmText: 'Delete',
                variant: 'danger'
            }).then(confirmed => {
                if (!confirmed) return;
                fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: '<?php echo generateCSRFToken(); ?>' })
                })
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else showToast('Error: ' + d.message); })
                .catch(() => showToast('Network error'));
            });
        }

        function updateJoStatusInline(id, status, selectEl) {
            const prevValue = selectEl?.dataset.prev || 'pending';
            const expectedUpdatedAt = selectEl?.dataset.version || '';
            if (!canEditJoStatus) {
                if (selectEl) selectEl.value = prevValue;
                showToast('You are not allowed to update job order status.');
                return;
            }
            if (selectEl) selectEl.disabled = true;

            fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: '<?php echo generateCSRFToken(); ?>',
                    status,
                    expected_updated_at: expectedUpdatedAt
                })
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    if (selectEl) selectEl.value = prevValue;
                    showToast('Error: ' + d.message);
                    return;
                }

                const timerEl = document.getElementById('jo-status-timer-' + id);
                const timerWrapEl = document.getElementById('jo-status-timer-wrap-' + id);
                if (timerEl && timerWrapEl) {
                    const isRunningStatus = typeof d?.data?.status_timer_is_running !== 'undefined'
                        ? !!d.data.status_timer_is_running
                        : (status === 'ongoing' || status === 'under_inspection' || status === 'returned_for_revision');
                    const isCompleted = status === 'completed';

                    timerEl.dataset.running = isRunningStatus ? '1' : '0';
                    timerWrapEl.style.display = (isRunningStatus || isCompleted) ? '' : 'none';
                    setJoTimerControlsState(id, isRunningStatus);
                }

                updateJoRowHighlight(id, status);

                if (selectEl) {
                    selectEl.dataset.prev = status;
                    if (d?.data?.updated_at) {
                        selectEl.dataset.version = d.data.updated_at;
                    }
                }
            })
            .catch(() => {
                if (selectEl) selectEl.value = prevValue;
                showToast('Network error while updating status.');
            })
            .finally(() => {
                if (selectEl) selectEl.disabled = false;
            });
        }

        function isJoTimerLocked(id) {
            const statusSelect = document.getElementById('jo-status-select-' + id);
            return !!statusSelect && statusSelect.value === 'completed';
        }

        function setJoTimerControlsState(id, isRunning) {
            const startBtn = document.getElementById('jo-timer-start-' + id);
            const stopBtn = document.getElementById('jo-timer-stop-' + id);
            const doneBtn = document.getElementById('jo-timer-done-' + id);
            const controlsWrap = document.getElementById('jo-timer-controls-' + id);
            const isLocked = isJoTimerLocked(id);
            if (controlsWrap) {
                controlsWrap.style.display = isLocked ? 'none' : '';
            }
            if (startBtn) startBtn.disabled = isLocked || !!isRunning;
            if (stopBtn) stopBtn.disabled = isLocked || !isRunning;
            if (doneBtn) doneBtn.disabled = isLocked;
        }

        function controlJoTimer(id, action, btnEl) {
            const timerEl = document.getElementById('jo-status-timer-' + id);
            if (!timerEl) return;
            const statusSelect = document.getElementById('jo-status-select-' + id);
            const expectedUpdatedAt = statusSelect?.dataset.version || '';
            if (action === 'start' && !canStartJoTimer) {
                showToast('You are not allowed to start the timer.');
                return;
            }
            if (action === 'stop' && !canStopJoTimer) {
                showToast('You are not allowed to stop the timer.');
                return;
            }
            if (action === 'done' && !canDoneJoTimer) {
                showToast('You are not allowed to mark this job as done.');
                return;
            }
            if (isJoTimerLocked(id)) {
                showToast('Completed job order timer is locked and cannot be edited.');
                return;
            }

            // If stopping, show notes modal (optional)
            if (action === 'stop') {
                document.getElementById('timerStopNotes').value = '';
                document.getElementById('timerStopConfirmBtn').onclick = function() {
                    const notes = document.getElementById('timerStopNotes').value.trim();
                    bootstrap.Modal.getInstance(document.getElementById('timerStopModal')).hide();
                    _sendTimerAction(id, action, expectedUpdatedAt, notes);
                };
                bootstrap.Modal.getOrCreateInstance(document.getElementById('timerStopModal')).show();
                return;
            }

            _sendTimerAction(id, action, expectedUpdatedAt, '');
        }

        function _sendTimerAction(id, action, expectedUpdatedAt, stopNotes) {
            const timerEl = document.getElementById('jo-status-timer-' + id);
            if (!timerEl) return;
            const statusSelect = document.getElementById('jo-status-select-' + id);

            const startBtn = document.getElementById('jo-timer-start-' + id);
            const stopBtn = document.getElementById('jo-timer-stop-' + id);
            const doneBtn = document.getElementById('jo-timer-done-' + id);
            if (startBtn) startBtn.disabled = true;
            if (stopBtn) stopBtn.disabled = true;
            if (doneBtn) doneBtn.disabled = true;

            const payload = {
                csrf_token: '<?php echo generateCSRFToken(); ?>',
                timer_action: action,
                expected_updated_at: expectedUpdatedAt
            };
            if (stopNotes) payload.stop_notes = stopNotes;

            fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    showToast('Error: ' + d.message);
                    return;
                }

                const isRunning = typeof d?.data?.status_timer_is_running !== 'undefined'
                    ? !!d.data.status_timer_is_running
                    : (action === 'start');
                timerEl.dataset.running = isRunning ? '1' : '0';
                if (typeof d?.data?.status_elapsed_seconds !== 'undefined') {
                    timerEl.dataset.seconds = String(parseInt(d.data.status_elapsed_seconds, 10) || 0);
                    timerEl.textContent = formatRowTimer(timerEl.dataset.seconds);
                }

                if (action === 'done') {
                    if (statusSelect) {
                        statusSelect.value = 'under_inspection';
                        statusSelect.dataset.prev = 'under_inspection';
                    }
                    updateJoRowHighlight(id, 'under_inspection');
                }

                if (statusSelect && d?.data?.updated_at) {
                    statusSelect.dataset.version = d.data.updated_at;
                }

                setJoTimerControlsState(id, isRunning);
            })
            .catch(() => showToast('Network error while controlling timer.'))
            .finally(() => {
                const isRunningNow = timerEl.dataset.running === '1';
                setJoTimerControlsState(id, isRunningNow);
            });
        }

        function formatRowTimer(totalSeconds) {
            const sec = Math.max(0, parseInt(totalSeconds, 10) || 0);
            const hours = String(Math.floor(sec / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
            const seconds = String(sec % 60).padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }

        function inspectionAction(id, result) {
            const statusSelect = document.getElementById('jo-status-select-' + id);
            const expectedUpdatedAt = statusSelect?.dataset.version || '';
            const newStatus = result === 'pass' ? 'completed' : 'ongoing';

            // Configure and show inspection modal
            const header = document.getElementById('inspectionModalHeader');
            const titleEl = document.getElementById('inspectionModalTitle');
            const messageEl = document.getElementById('inspectionModalMessage');
            const confirmBtn = document.getElementById('inspectionModalConfirmBtn');

            if (result === 'pass') {
                header.style.background = '#f0fff4';
                titleEl.innerHTML = '<i class="bi bi-check-circle me-1 text-success"></i> Pass Inspection';
                messageEl.textContent = 'Mark this JO as PASSED inspection? Status will move to Completed.';
                confirmBtn.className = 'btn btn-success btn-sm';
                confirmBtn.textContent = 'Pass';
            } else {
                header.style.background = '#fff8f0';
                titleEl.innerHTML = '<i class="bi bi-arrow-repeat me-1 text-warning"></i> Needs Revision';
                messageEl.textContent = 'Mark as NEEDS REVISION? Status will move back to Ongoing.';
                confirmBtn.className = 'btn btn-warning btn-sm';
                confirmBtn.textContent = 'Needs Revision';
            }

            confirmBtn.onclick = function() {
                bootstrap.Modal.getInstance(document.getElementById('inspectionModal')).hide();
                const btnsWrap = document.getElementById('jo-inspection-btns-' + id);
                if (btnsWrap) btnsWrap.querySelectorAll('button').forEach(b => b.disabled = true);
                fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: '<?php echo generateCSRFToken(); ?>',
                        status: newStatus,
                        inspection_result: result,
                        expected_updated_at: expectedUpdatedAt
                    })
                })
                .then(r => r.json())
                .then(d => {
                    if (!d.success) { showToast('Error: ' + d.message); return; }
                    if (statusSelect) {
                        statusSelect.value = newStatus;
                        statusSelect.dataset.prev = newStatus;
                        if (d?.data?.updated_at) statusSelect.dataset.version = d.data.updated_at;
                    }
                    updateJoRowHighlight(id, newStatus);
                    if (btnsWrap) btnsWrap.style.display = 'none';
                    const timerWrap = document.getElementById('jo-status-timer-wrap-' + id);
                    if (timerWrap) timerWrap.style.display = '';
                    setJoTimerControlsState(id, false);
                })
                .catch(() => showToast('Network error.'))
                .finally(() => {
                    if (btnsWrap) btnsWrap.querySelectorAll('button').forEach(b => b.disabled = false);
                });
            };

            bootstrap.Modal.getOrCreateInstance(document.getElementById('inspectionModal')).show();
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach((el, i) => {
                    el.style.zIndex = 1050 + (i * 5);
                });
            }, 150);
        }

        function renderJoRowTimers() {
            document.querySelectorAll('.jo-status-timer').forEach((timerEl) => {
                const seconds = parseInt(timerEl.dataset.seconds || '0', 10) || 0;
                timerEl.textContent = formatRowTimer(seconds);
                const id = (timerEl.id || '').replace('jo-status-timer-', '');
                if (id) {
                    setJoTimerControlsState(id, timerEl.dataset.running === '1');
                }
            });
        }

        function tickJoRowTimers() {
            document.querySelectorAll('.jo-status-timer').forEach((timerEl) => {
                if (timerEl.dataset.running === '1') {
                    const next = (parseInt(timerEl.dataset.seconds || '0', 10) || 0) + 1;
                    timerEl.dataset.seconds = String(next);
                    timerEl.textContent = formatRowTimer(next);
                }
            });
        }

        renderJoRowTimers();
        setInterval(tickJoRowTimers, 1000);
        </script>
    <?php endif; ?>
    
    <!-- Job Estimate Tab -->
    <?php if ($activeTab === 'estimates'): ?>
        <!-- Search bar -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="estimates">
                    <div class="col-md-5">
                        <input type="text" name="est_search" class="form-control form-control-sm"
                               placeholder="Search by estimate#, customer, plate, make..."
                               value="<?php echo escape($_GET['est_search'] ?? ''); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Search</button>
                        <a href="?tab=estimates" class="btn btn-sm btn-secondary ms-1"><i class="bi bi-x"></i> Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($allEstimates)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calculator" style="font-size:3rem;color:#ccc;"></i>
                        <p class="text-muted mt-3">No estimates found</p>
                        <button class="btn btn-dark btn-sm" data-bs-toggle="modal" data-bs-target="#jobEstimateModal">
                            <i class="bi bi-calculator"></i> Create Estimate
                        </button>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px;">
                        <thead style="background:#f8f8f8;">
                            <tr>
                                <th class="px-3">JE #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Plate</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allEstimates as $est): ?>
                            <tr>
                                <td class="px-3 fw-bold"><?php echo escape($est['estimate_number']); ?></td>
                                <td>
                                    <div><?php echo escape($est['customer_name'] ?? '—'); ?></div>
                                    <?php if (!empty($est['customer_phone'])): ?>
                                    <small class="text-muted"><?php echo escape($est['customer_phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape(trim(($est['vehicle_make'] ?? '').' '.($est['vehicle_model'] ?? ''))); ?></td>
                                <td><?php echo escape($est['vehicle_plate'] ?: '—'); ?></td>
                                <td class="fw-bold"><?php echo formatCurrency($est['grand_total']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($est['created_at'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $est['status'] === 'converted' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($est['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown action-dropdown actions-mobile je-actions-menu">
                                        <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="jeActionsMobile<?php echo $est['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Estimate actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="jeActionsMobile<?php echo $est['id']; ?>">
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="viewEstimate(<?php echo $est['id']; ?>)">
                                                    <i class="bi bi-eye me-2"></i>View
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="editEstimate(<?php echo $est['id']; ?>)">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="printEstimate(<?php echo $est['id']; ?>)">
                                                    <i class="bi bi-printer me-2"></i>Print
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="_pdfMode = true; printEstimate(<?php echo $est['id']; ?>).then(() => { _pdfMode = false; }).catch(() => { _pdfMode = false; });">
                                                    <i class="bi bi-file-earmark-pdf me-2"></i>Save PDF
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" onclick="convertEstimateById(<?php echo $est['id']; ?>)">
                                                    <i class="bi bi-arrow-right-circle me-2"></i>Convert to JO
                                                </button>
                                            </li>
                                            <?php if ($canDeleteRecords): ?>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="deleteEstimate(<?php echo $est['id']; ?>)">
                                                    <i class="bi bi-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        const estimateActionMenuState = new WeakMap();
        document.addEventListener('show.bs.dropdown', function (event) {
            const button = event.target;
            const menuContainer = button.closest('.je-actions-menu');
            const menu = menuContainer?.querySelector('.dropdown-menu');
            if (!menuContainer || !menu) return;

            const placeholder = document.createComment('estimate-actions-menu');
            menu.parentNode.insertBefore(placeholder, menu);
            document.body.appendChild(menu);
            estimateActionMenuState.set(button, { menu, placeholder });
        });

        document.addEventListener('shown.bs.dropdown', function (event) {
            const state = estimateActionMenuState.get(event.target);
            if (!state) return;

            const menu = state.menu;
            const buttonRect = event.target.getBoundingClientRect();
            menu.style.position = 'fixed';
            menu.style.visibility = 'hidden';
            menu.style.display = 'block';
            const menuHeight = menu.scrollHeight;
            const menuWidth = menu.offsetWidth;
            const gap = 6;
            const top = buttonRect.top - menuHeight - gap >= gap
                ? buttonRect.top - menuHeight - gap
                : buttonRect.bottom + gap;
            const left = Math.min(
                Math.max(gap, buttonRect.right - menuWidth),
                window.innerWidth - menuWidth - gap
            );
            menu.style.top = top + 'px';
            menu.style.left = left + 'px';
            menu.style.right = 'auto';
            menu.style.transform = 'none';
            menu.style.visibility = 'visible';
        });

        document.addEventListener('hidden.bs.dropdown', function (event) {
            const state = estimateActionMenuState.get(event.target);
            if (!state) return;
            state.placeholder.parentNode.insertBefore(state.menu, state.placeholder);
            state.placeholder.remove();
            state.menu.style.position = '';
            state.menu.style.top = '';
            state.menu.style.left = '';
            state.menu.style.right = '';
            state.menu.style.transform = '';
            state.menu.style.display = '';
            state.menu.style.visibility = '';
            estimateActionMenuState.delete(event.target);
        });

        async function deleteEstimate(id) {
            const response = await fetch('<?php echo APP_URL; ?>/api/estimates.php?id=' + id);
            const result = await response.json();
            const estimate = result.success ? result.data : null;
            const details = estimate
                ? `Estimate: ${estimate.estimate_number || id}\nCustomer: ${estimate.customer_name || '—'}\nPlate: ${estimate.vehicle_plate || '—'}\nTotal: ₱${Number(estimate.grand_total || 0).toFixed(2)}`
                : `Estimate ID: ${id}`;
            appConfirm(`Delete this estimate?\n\n${details}`, {
                title: 'Delete Estimate',
                confirmText: 'Delete',
                variant: 'danger'
            }).then(confirmed => {
                if (!confirmed) return;
                fetch('<?php echo APP_URL; ?>/api/estimates.php?id=' + id, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: '<?php echo generateCSRFToken(); ?>' })
                })
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else showToast('Error: ' + d.message); })
                .catch(() => showToast('Network error'));
            });
        }
        </script>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?tab=<?php echo $activeTab; ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">Previous</a>
                </li>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $startPage + 4);
                if ($endPage - $startPage < 4) $startPage = max(1, $endPage - 4);
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?tab=<?php echo $activeTab; ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?tab=<?php echo $activeTab; ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                    <h5 class="modal-title" id="addServiceModalLabel" style="color: #000;">
                        <i class="bi bi-plus-circle"></i> Add New Service
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_service">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Service Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" class="form-control" name="service_name" required 
                                   placeholder="e.g., Oil Change" style="border: 1.5px solid #e0e0e0;">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Service Code
                            </label>
                            <input type="text" class="form-control" name="service_code" readonly
                                   style="border: 1.5px solid #e0e0e0;background:#f5f5f5;" placeholder="Auto-generated">
                            <small style="color: #666;">Auto-generated (SVC##)</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Description
                            </label>
                            <textarea class="form-control" name="description" id="addSvcDesc" rows="2" 
                                      placeholder="Brief description of the service..."
                                      style="border: 1.5px solid #e0e0e0;"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Checklist Items <small class="text-muted">(optional, appear as sub-rows in print)</small>
                            </label>
                            <div id="addSvcSubItemsList" style="border:1.5px solid #e0e0e0;border-radius:6px;padding:8px;background:#f9f9f9;min-height:34px;max-height:150px;overflow-y:auto;">
                                <p class="text-muted small text-center mb-0 py-1" id="addSvcSubItemsEmpty">No items yet.</p>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <input type="text" id="addSvcSubItemInput" class="form-control form-control-sm" placeholder="e.g. Filter Cleaning" style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault();addAddSvcSubItem();}">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addAddSvcSubItem()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="border rounded-3 p-3" style="background: #f8f9fa; border-color: #e0e0e0 !important;">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label" style="color: #000; font-weight: 500;">
                                            Base Price (₱) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="addSvcPrice" name="service_price" required 
                                               step="0.01" min="0" value="0" 
                                               placeholder="0.00" style="border: 1.5px solid #e0e0e0;">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label" style="color: #000; font-weight: 500;">
                                            Labor Cost (₱) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="addSvcLabor" name="labor_cost" required 
                                               step="0.01" min="0" value="0" 
                                               placeholder="0.00" style="border: 1.5px solid #e0e0e0;">
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label" style="color: #000; font-weight: 500;">
                                            Total
                                        </label>
                                        <div class="form-control fw-bold" id="addSvcTotal" style="background: #fff; border: 1.5px solid #e0e0e0;">
                                            ₱0.00
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Edit labor cost directly and the total updates instantly.
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Status <span style="color: #dc3545;">*</span>
                            </label>
                            <select class="form-select" name="status" required style="border: 1.5px solid #e0e0e0;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e0e0e0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Bundle Modal -->
<div class="modal fade" id="addBundleModal" tabindex="-1" aria-labelledby="addBundleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                    <h5 class="modal-title" id="addBundleModalLabel" style="color: #000;">
                        <i class="bi bi-plus-circle"></i> Add New Service Package
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_bundle">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Package Name <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" class="form-control" name="bundle_name" required 
                                   placeholder="e.g., Light PMS, Heavy PMS, Regular Maintenance" style="border: 1.5px solid #e0e0e0;">
                            <small style="color: #666;">Examples: Light PMS, Heavy PMS, Regular PMS, Complete Checkup</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Description
                            </label>
                            <textarea class="form-control" name="description" rows="2" 
                                      placeholder="Brief description of what's included in this package..." 
                                      style="border: 1.5px solid #e0e0e0;"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Package Price (₱) <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="number" class="form-control" name="package_price" required 
                                   step="0.01" min="0" value="0" 
                                   placeholder="0.00" style="border: 1.5px solid #e0e0e0;">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Status <span style="color: #dc3545;">*</span>
                            </label>
                            <select class="form-select" name="status" required style="border: 1.5px solid #e0e0e0;">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <!-- Selected Items Summary -->
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                <i class="bi bi-list-check me-1"></i> Selected Items
                            </label>
                            <div id="addBundleSelectedSummary" style="border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 12px; background: #fff; min-height: 40px;">
                                <p class="text-muted text-center small mb-0" id="addBundleSelectedEmpty">No items selected yet.</p>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Select Services <span style="color: #dc3545;">*</span>
                            </label>
                            <div class="position-relative mb-2">
                                <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                <input type="text" id="addBundleSvcSearch" class="form-control form-control-sm" placeholder="Search services..." style="padding-left:30px;"
                                    onkeyup="bundleSvcSearch(this.value, 'add-bundle-svc-item')">
                            </div>
                            <div style="max-height: 300px; overflow-y: auto; border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 15px; background: #f9f9f9;">
                                <?php if (empty($allActiveServices)): ?>
                                    <p style="color: #666; text-align: center; margin: 20px 0;">
                                        No active services available. Please create services first.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($allActiveServices as $service): ?>
                                        <div class="form-check mb-2 add-bundle-svc-item" style="padding: 10px; background: #fff; border-radius: 6px;" data-search-text="<?php echo escape(strtolower(($service['service_name'] ?? '') . ' ' . ($service['service_code'] ?? ''))); ?>">
                                              <input class="form-check-input add-bundle-svc-check" type="checkbox" name="service_ids[]" 
                                                   value="<?php echo $service['id']; ?>" 
                                                   id="service_<?php echo $service['id']; ?>">
                                            <label class="form-check-label" for="service_<?php echo $service['id']; ?>" style="color: #000; width: 100%;">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?php echo escape($service['service_name']); ?></strong>
                                                        <br>
                                                        <small style="color: #666;"><?php echo escape($service['service_code']); ?></small>
                                                    </div>
                                                    <div style="text-align: right;">
                                                        <strong><?php echo formatCurrency($service['service_price'] + $service['labor_cost']); ?></strong>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    <p class="text-muted text-center small py-2 mb-0" id="addBundleSvcNoMatch" style="display:none;">No matching services found.</p>
                                <?php endif; ?>
                            </div>
                    <small style="color: #666;">Select at least one service to include in this package</small>
                        </div>

                        <!-- Products for this package -->
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Products Included <small class="text-muted">(optional)</small>
                            </label>
                            <div class="position-relative mb-2">
                                <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                <input type="text" id="addBundleProdSearch" class="form-control form-control-sm" placeholder="Search products..." style="padding-left:30px;"
                                    onkeyup="bundleSvcSearch(this.value, 'add-bundle-prod-item')">
                            </div>
                            <div style="max-height: 200px; overflow-y: auto; border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 12px; background: #f9f9f9;">
                                <?php if (empty($allInventoryProducts)): ?>
                                    <p class="text-muted text-center small mb-0">No products in inventory.</p>
                                <?php else: ?>
                                    <?php foreach ($allInventoryProducts as $prod): ?>
                                    <div class="d-flex align-items-center justify-content-between mb-1 p-2 bg-white rounded add-bundle-prod-item" style="border:1px solid #eee;" data-search-text="<?php echo escape(strtolower(($prod['product_name'] ?? '') . ' ' . ($prod['product_code'] ?? ''))); ?>">
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-check-input add-bundle-prod-check" type="checkbox"
                                                   name="product_ids[]"
                                                   value="<?php echo $prod['id']; ?>"
                                                   id="addBndProd_<?php echo $prod['id']; ?>"
                                                   data-name="<?php echo escape($prod['product_name']); ?>"
                                                   data-price="<?php echo (float)$prod['selling_price']; ?>">
                                            <label class="form-check-label small mb-0" for="addBndProd_<?php echo $prod['id']; ?>" style="cursor:pointer;">
                                                <strong><?php echo escape($prod['product_name']); ?></strong>
                                                <small class="text-muted d-block"><?php echo escape($prod['product_code']); ?> • <?php echo formatCurrency($prod['selling_price']); ?></small>
                                            </label>
                                        </div>
                                        <input type="number" class="form-control form-control-sm text-center" name="product_qty_<?php echo $prod['id']; ?>" value="1" min="1" style="width:55px;font-size:12px;">
                                    </div>
                                    <?php endforeach; ?>
                                    <p class="text-muted text-center small py-2 mb-0" id="addBundleProdNoMatch" style="display:none;">No matching products found.</p>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Check products and set quantity to include in this package.</small>
                        </div>
                        
                        <div class="col-12">
                            <div class="alert" style="background: #f8f9fa; border: 1px solid #e0e0e0; color: #000;">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Note:</strong> Package price is typically lower than the sum of services
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e0e0e0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Job Order Modal -->
<div class="modal fade" id="createJobOrderModal" tabindex="-1" aria-labelledby="createJobOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <h5 class="modal-title" id="createJobOrderModalLabel" style="color: #000; font-weight: 600;">
                    <i class="bi bi-file-earmark-text"></i> Create New Job Order
                </h5>
                <button type="button" style="background: none; border: none; font-size: 24px; color: #000; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x" style="font-size: 24px;"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; background: #fafafa;">
                <form id="joForm">
                <?php echo csrfField(); ?>
                <div class="row g-3">

                    <!-- ── LEFT COLUMN ── -->
                    <div class="col-lg-7">

                        <!-- Customer -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-person me-1"></i>Customer Information</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="jo_customer_name" required placeholder="Customer name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Contact Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control form-control-sm" id="jo_customer_phone" required placeholder="09XX XXX XXXX" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Email</label>
                                        <input type="email" class="form-control form-control-sm" id="jo_customer_email" placeholder="email@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Address</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_customer_address" placeholder="Customer address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-car-front me-1"></i>Vehicle Information</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Make / Brand</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_make">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Model</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_model">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Year</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_year">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Plate Number</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_plate">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Color</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_color">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Mileage (km)</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_mileage">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Services & Bundles Picker -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <!-- Tabs -->
                                <ul class="nav nav-tabs nav-sm mb-3" id="joServiceTabs">
                                    <li class="nav-item">
                                        <a class="nav-link active py-1 px-3" data-bs-toggle="tab" href="#joTabIndividual" style="font-size:13px;">Services</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#joTabBundles" style="font-size:13px;">Packages (PMS)</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#joTabCustom" style="font-size:13px;">Custom Entry</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <!-- Services -->
                                    <div class="tab-pane fade show active" id="joTabIndividual">
                                        <div class="position-relative mb-2">
                                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                            <input type="text" id="jo_service_search" class="form-control form-control-sm" placeholder="Search services..." style="padding-left:30px;"
                                                onkeyup="joSearchFilter(this.value, 'jo-service-row')">
                                        </div>
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveServices)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active services found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveServices as $svc): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card jo-service-row" data-search-text="<?php echo escape(strtolower(($svc['service_name'] ?? '') . ' ' . ($svc['service_code'] ?? ''))); ?>">
                                                    <div>
                                                        <strong style="font-size:13px;"><?php echo escape($svc['service_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo escape($svc['service_code']); ?></small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($svc['service_price'] + $svc['labor_cost']); ?></span>
                                                        <button type="button" class="btn btn-sm btn-dark py-0 px-2"
                                                            style="font-size:12px;"
                                                            onclick="joAddItem('service', <?php echo $svc['id']; ?>, '<?php echo addslashes(escape($svc['service_name'])); ?>', <?php echo $svc['service_price']; ?>, <?php echo (float)$svc['labor_cost']; ?>)">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                                <p id="jo_service_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching services found.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Packages -->
                                    <div class="tab-pane fade" id="joTabBundles">
                                        <div class="position-relative mb-2">
                                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                            <input type="text" id="jo_bundle_search" class="form-control form-control-sm" placeholder="Search packages..." style="padding-left:30px;"
                                                onkeyup="joSearchFilter(this.value, 'jo-bundle-row')">
                                        </div>
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveBundles)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active packages found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveBundles as $bnd): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card jo-bundle-row" data-search-text="<?php echo escape(strtolower(($bnd['bundle_name'] ?? '') . ' ' . count($bnd['services']) . ' services')); ?>">
                                                    <div>
                                                        <strong style="font-size:13px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services included</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($bnd['package_price']); ?></span>
                                                        <button type="button" class="btn btn-sm btn-dark py-0 px-2"
                                                            style="font-size:12px;"
                                                            onclick="joAddItem('bundle', <?php echo $bnd['id']; ?>, '<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Package)', <?php echo $bnd['package_price']; ?>, <?php echo isset($bnd['labor_cost']) ? (float)$bnd['labor_cost'] : 0; ?>)">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                                <p id="jo_bundle_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching packages found.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Custom Entry -->
                                    <div class="tab-pane fade" id="joTabCustom">
                                        <div style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;background:#f9f9f9;">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <input type="text" class="form-control form-control-sm" id="joCustomName" placeholder="Service name">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="joCustomPrice" placeholder="Base Price" step="0.01" min="0">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="joCustomLabor" placeholder="Labor Cost" step="0.01" min="0">
                                                </div>
                                                <div class="col-12">
                                                    <textarea class="form-control form-control-sm" id="joCustomSubItems" rows="2" placeholder=""></textarea>
                                                    <small class="text-muted">Enter sub-services or included items, one per line.</small>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" id="joAddCustomItemBtn" class="btn btn-sm btn-dark w-100" onclick="joAddCustomItem()">
                                                        <i class="bi bi-plus"></i> Add Custom Item
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-box-seam me-1"></i>Products</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="position-relative mb-2">
                                    <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                    <input type="text" id="jo_product_search" class="form-control form-control-sm" placeholder="Search products..." style="padding-left:30px;"
                                        onkeyup="joSearchFilter(this.value, 'jo-product-row')">
                                </div>
                                <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                    <?php if (empty($allInventoryProducts)): ?>
                                        <p class="text-muted text-center small py-3 mb-0">No products in inventory.</p>
                                    <?php else: ?>
                                        <?php foreach ($allInventoryProducts as $prod): ?>
                                        <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card jo-product-row" data-search-text="<?php echo escape(strtolower(($prod['product_name'] ?? '') . ' ' . ($prod['product_code'] ?? ''))); ?>">
                                            <div>
                                                <strong style="font-size:13px;"><?php echo escape($prod['product_name']); ?></strong>
                                                <small class="text-muted d-block"><?php echo escape($prod['product_code']); ?> • <?php echo (int)$prod['quantity']; ?> in stock</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($prod['selling_price']); ?></span>
                                                <input type="number" class="form-control form-control-sm text-center" id="jo_prod_qty_<?php echo (int)$prod['id']; ?>" value="1" min="1" style="width:55px;">
                                                <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:12px;"
                                                    onclick="joAddProduct(<?php echo (int)$prod['id']; ?>, '<?php echo addslashes(escape($prod['product_name'])); ?>', <?php echo (float)$prod['selling_price']; ?>, '<?php echo addslashes(escape($prod['product_code'])); ?>', <?php echo (int)$prod['quantity']; ?>, 'jo_prod_qty_<?php echo (int)$prod['id']; ?>')">
                                                    <i class="bi bi-plus"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <p id="jo_product_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching products found.</p>
                                    <?php endif; ?>
                                </div>
                                <!-- Custom product entry -->
                                <div class="d-flex gap-1 mt-2">
                                    <input type="text" class="form-control form-control-sm" id="joCustomProdName" placeholder="Product name" style="flex:2;">
                                    <input type="number" class="form-control form-control-sm" id="joCustomProdPrice" placeholder="Price" step="0.01" min="0" style="flex:1;">
                                    <input type="number" class="form-control form-control-sm" id="joCustomProdQty" placeholder="Qty" min="1" value="1" style="width:50px;">
                                    <button type="button" id="joAddCustomProductBtn" class="btn btn-sm btn-dark py-0 px-2" onclick="joAddCustomProduct()" title="Add custom product"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                        </div>

                    </div><!-- /left -->

                    <!-- ── RIGHT COLUMN ── -->
                    <div class="col-lg-5">

                        <!-- Selected Items -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-cart me-1"></i>Selected Items</h6>
                                <span class="badge bg-dark" id="joItemCount">0</span>
                            </div>
                            <div class="card-body p-0">
                                <div id="joSelectedItems" style="min-height:120px;max-height:360px;overflow-y:auto;">
                                    <p class="text-muted text-center small py-4 mb-0" id="joEmptyMsg">No items added yet.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Summary -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-receipt me-1"></i>Billing</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <hr class="my-2">
                                <!-- Subtotals -->
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Services Subtotal</span>
                                    <strong id="joSubtotal">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Products Subtotal</span>
                                    <strong id="joPartsDisplay">₱0.00</strong>
                                </div>
                                <hr class="my-2">
                                <!-- Discount -->
                                <div class="mb-2">
                                    <label class="form-label form-label-sm">Discount Type</label>
                                    <select class="form-select form-select-sm" id="jo_discount_type" onchange="joCalc()">
                                        <option value="none">None</option>
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount (₱)</option>
                                        <option value="senior">Senior Citizen (20%)</option>
                                        <option value="pwd">PWD (20%)</option>
                                    </select>
                                </div>
                                <div class="mb-2" id="joDiscountAmtRow">
                                    <label class="form-label form-label-sm">Discount Value</label>
                                    <input type="number" class="form-control form-control-sm" id="jo_discount_value" value="0" min="0" step="0.01" oninput="joCalc()">
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Discount</span>
                                    <span class="text-danger" id="joDiscountDisplay">-₱0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Amount</strong>
                                    <h5 class="mb-0" id="joTotal" style="font-weight:700;">₱0.00</h5>
                                </div>
                                <hr class="my-2">
                                <!-- Payment Records -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="fw-bold">Payments</small>
                                    <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size:11px;" onclick="openJoCreatePaymentModal()"><i class="bi bi-plus"></i> Add Payment</button>
                                </div>
                                <div id="joPaymentsList" style="min-height:30px;max-height:150px;overflow-y:auto;"></div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-muted">Total Paid</small>
                                    <strong id="joTotalPaid" class="text-success">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Balance</small>
                                    <strong id="joBalanceRemaining" class="text-danger">₱0.00</strong>
                                </div>
                                <!-- Hidden fields for compatibility -->
                                <input type="hidden" id="jo_payment_method" value="cash">
                                <input type="hidden" id="jo_payment_status" value="pending">
                                <input type="hidden" id="jo_partial_amount" value="0">
                                <div id="joPartialRow" style="display:none;"></div>
                            </div>
                        </div>

                        <!-- Technician -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;" id="joTechCard">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-tools me-1"></i>Technicians</h6>
                                <span id="joTechCountBadge" class="badge bg-secondary">0 selected</span>
                            </div>
                            <div class="card-body" style="padding:12px 15px;">

                                <!-- Search and filter -->
                                <div class="position-relative mb-2">
                                    <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                    <input type="text" id="jo_tech_search" class="form-control form-control-sm" placeholder="Search technicians..." style="padding-left:30px;padding-right:145px;"
                                        onkeyup="joTechFilter()">
                                    <div class="position-absolute d-flex gap-1" style="right:6px;top:50%;transform:translateY(-50%);">
                                        <button type="button" class="btn btn-dark btn-sm py-0 px-2" id="joTechFilterAll" style="font-size:10px;line-height:1.4;" onclick="joTechSetFilter('all')">All</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="joTechFilterAvailable" style="font-size:10px;line-height:1.4;" onclick="joTechSetFilter('available')">Available</button>
                                        <button type="button" class="btn btn-outline-warning btn-sm py-0 px-2" id="joTechFilterBusy" style="font-size:10px;line-height:1.4;" onclick="joTechSetFilter('busy')">Busy</button>
                                    </div>
                                </div>

                                <!-- Technician list with role toggle -->
                                <div id="jo_technicians" style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:8px;background:#f9f9f9;">
                                    <?php foreach ($allTechnicians as $tech): ?>
                                    <div class="d-flex align-items-center justify-content-between py-1 mb-1 px-1 bg-white rounded jo-tech-row" style="border:1px solid #eee;" id="jo_tech_row_<?php echo $tech['id']; ?>" data-tech-name="<?php echo escape(strtolower($tech['full_name'])); ?>">
                                        <div class="d-flex align-items-center gap-2">
                                            <input
                                                class="form-check-input jo-tech-check"
                                                type="checkbox"
                                                value="<?php echo $tech['id']; ?>"
                                                id="jo_tech_<?php echo $tech['id']; ?>"
                                                data-name="<?php echo escape($tech['full_name']); ?>"
                                                onchange="joOnTechChange(<?php echo $tech['id']; ?>, this.checked)"
                                            >
                                            <label class="form-check-label small mb-0" for="jo_tech_<?php echo $tech['id']; ?>" style="cursor:pointer;">
                                                <?php echo escape($tech['full_name']); ?>
                                            </label>
                                            <span class="badge jo-tech-avail-badge" id="avail_<?php echo $tech['id']; ?>" style="font-size:9px;">—</span>
                                        </div>
                                        <!-- Role toggle: shown only when checked -->
                                        <div id="jo_tech_role_<?php echo $tech['id']; ?>" style="display:none;">
                                            <select class="form-select form-select-sm py-0" style="width:100px;font-size:11px;" onchange="joOnRoleChange(<?php echo $tech['id']; ?>, this.value)">
                                                <option value="main">Technician</option>
                                                <option value="assist">Assistant</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">Check technician then set role.</small>
                                </div>
                                <div id="joTechHistory" style="display:none;margin-top:6px;"></div>

                                <!-- Hidden avail badges for picker -->
                                <?php foreach ($allTechnicians as $tech): ?>
                                <span style="display:none;" id="avail_pick_<?php echo $tech['id']; ?>"></span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Tech Picker Modal moved to global scope below -->
                        

                        <!-- Notes -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <textarea class="form-control form-control-sm" id="jo_notes" rows="2" placeholder="Additional notes or instructions..."></textarea>
                            </div>
                        </div>

                        <!-- Recommendation -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-lightbulb me-1"></i>Recommendations</h6>
                            </div>
                            <div class="card-body" style="padding:12px 15px;">
                                <p class="text-muted small mb-2">Recommend services, products, or type a custom note.</p>
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" id="jo_rec_input" class="form-control form-control-sm" placeholder="Type recommendation or browse below..." onkeydown="if(event.key==='Enter'){event.preventDefault();joAddRecommendation();}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="joAddRecommendation()" title="Add text"><i class="bi bi-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-dark" onclick="openRecBrowseModal()" title="Browse from records"><i class="bi bi-search"></i></button>
                                </div>
                                <div id="jo_rec_list" style="border:1px solid #e0e0e0;border-radius:6px;padding:6px;background:#f9f9f9;min-height:32px;max-height:120px;overflow-y:auto;">
                                    <p class="text-muted small text-center mb-0 py-1" id="jo_rec_empty">No recommendations yet.</p>
                                </div>
                            </div>
                        </div>

                    </div><!-- /right -->
                </div><!-- /row -->
                </form>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;padding:12px 20px;">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="joPrintPreview()">
                    <i class="bi bi-printer"></i> Print JO
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="joSavePDF()">
                    <i class="bi bi-file-earmark-pdf"></i> Save PDF
                </button>
                <button type="button" class="btn btn-dark btn-sm" id="joSaveBtn" onclick="joSave()">
                    <i class="bi bi-save"></i> Save Job Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── PRINT TEMPLATE (hidden, A4) ── -->
<div id="joPrintArea" style="display:none;">
    <style>
        @page {
            size: 8.5in 13in;
            margin: 10mm 10mm;
        }
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }
            body * { visibility: hidden !important; }
            #joPrintArea, #joPrintArea *,
            #jePrintArea, #jePrintArea * { visibility: visible !important; }
            #joPrintArea, #jePrintArea {
                position: absolute !important;
                top: 0; left: 0;
                width: 100%;
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            #joPrintContent, #jePrintContent {
                width: calc(100% - 1px);
                max-width: 190mm;
                min-width: 0 !important;
                margin: 0 auto;
                box-sizing: border-box;
                font-family: Arial, sans-serif;
                font-size: 9.5pt;
                color: #000 !important;
                line-height: 1.35;
                page-break-after: avoid;
                overflow: visible;
            }
            #joPrintContent table, #jePrintContent table,
            #joPrintContent img, #jePrintContent img {
                max-width: 100%;
                box-sizing: border-box;
            }
            #joPrintContent table, #jePrintContent table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse;
            }
            #joPrintContent, #joPrintContent *,
            #jePrintContent, #jePrintContent * {
                box-sizing: border-box !important;
                min-width: 0;
                max-width: 100% !important;
            }
            #joPrintContent img, #jePrintContent img {
                width: auto !important;
                height: auto !important;
                max-height: 45mm;
                object-fit: contain;
            }
            #joPrintContent td, #joPrintContent th,
            #jePrintContent td, #jePrintContent th {
                max-width: none;
                width: auto;
                overflow-wrap: anywhere;
                word-break: break-word;
                white-space: normal;
            }
            /* Hide everything else to prevent extra page */
            .modal, .modal-backdrop, nav, footer, header,
            .sidebar, .container-fluid, .services-mgmt-shell {
                display: none !important;
                height: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
    <div id="joPrintContent"></div>
</div>
<div id="jePrintArea" style="display:none;">
    <div id="jePrintContent"></div>
</div>

<!-- Job Estimate Modal -->
<div class="modal fade" id="jobEstimateModal" tabindex="-1" aria-labelledby="jobEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <h5 class="modal-title" id="jobEstimateModalLabel" style="color: #000; font-weight: 600;">
                    <i class="bi bi-calculator"></i> Job Estimate
                </h5>
                <button type="button" style="background: none; border: none; font-size: 24px; color: #000; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x" style="font-size: 24px;"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; background: #fafafa;">
                <form id="jeForm">
                <?php echo csrfField(); ?>
                <div class="row g-3">

                    <!-- ── LEFT COLUMN ── -->
                    <div class="col-lg-7">

                        <!-- Customer -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-person me-1"></i>Customer Information</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="je_customer_name" required placeholder="Customer name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Contact Number <span class="text-muted">(Optional)</span></label>
                                        <input type="tel" class="form-control form-control-sm" id="je_customer_phone" placeholder="09XX XXX XXXX" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Email</label>
                                        <input type="email" class="form-control form-control-sm" id="je_customer_email" placeholder="email@example.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label form-label-sm">Address</label>
                                        <input type="text" class="form-control form-control-sm" id="je_customer_address" placeholder="Customer address">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-car-front me-1"></i>Vehicle Information</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Make / Brand</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_make">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Model</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_model">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Year</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_year">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Plate Number</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_plate">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Color</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_color">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Mileage (km)</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_mileage">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Services & Bundles Picker -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <!-- Tabs -->
                                <ul class="nav nav-tabs nav-sm mb-3" id="jeServiceTabs">
                                    <li class="nav-item">
                                        <a class="nav-link active py-1 px-3" data-bs-toggle="tab" href="#jeTabIndividual" style="font-size:13px;">Services</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#jeTabBundles" style="font-size:13px;">Packages (PMS)</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#jeTabCustom" style="font-size:13px;">Custom Entry</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <!-- Services -->
                                    <div class="tab-pane fade show active" id="jeTabIndividual">
                                        <div class="position-relative mb-2">
                                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                            <input type="text" id="je_service_search" class="form-control form-control-sm" placeholder="Search services..." style="padding-left:30px;"
                                                onkeyup="joSearchFilter(this.value, 'je-service-row')">
                                        </div>
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveServices)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active services found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveServices as $svc): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card je-service-row" data-search-text="<?php echo escape(strtolower(($svc['service_name'] ?? '') . ' ' . ($svc['service_code'] ?? ''))); ?>">
                                                    <div>
                                                        <strong style="font-size:13px;"><?php echo escape($svc['service_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo escape($svc['service_code']); ?></small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($svc['service_price'] + $svc['labor_cost']); ?></span>
                                                        <button type="button" class="btn btn-sm btn-dark py-0 px-2"
                                                            style="font-size:12px;"
                                                            onclick="jeAddItem('service', <?php echo $svc['id']; ?>, '<?php echo addslashes(escape($svc['service_name'])); ?>', <?php echo $svc['service_price']; ?>, <?php echo (float)$svc['labor_cost']; ?>)">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                                <p id="je_service_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching services found.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Packages -->
                                    <div class="tab-pane fade" id="jeTabBundles">
                                        <div class="position-relative mb-2">
                                            <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                            <input type="text" id="je_bundle_search" class="form-control form-control-sm" placeholder="Search packages..." style="padding-left:30px;"
                                                onkeyup="joSearchFilter(this.value, 'je-bundle-row')">
                                        </div>
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveBundles)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active packages found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveBundles as $bnd): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card je-bundle-row" data-search-text="<?php echo escape(strtolower(($bnd['bundle_name'] ?? '') . ' ' . count($bnd['services']) . ' services')); ?>">
                                                    <div>
                                                        <strong style="font-size:13px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services included</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($bnd['package_price']); ?></span>
                                                        <button type="button" class="btn btn-sm btn-dark py-0 px-2"
                                                            style="font-size:12px;"
                                                            onclick="jeAddItem('bundle', <?php echo $bnd['id']; ?>, '<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Package)', <?php echo $bnd['package_price']; ?>, <?php echo isset($bnd['labor_cost']) ? (float)$bnd['labor_cost'] : 0; ?>)">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                                <p id="je_bundle_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching packages found.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Custom Entry -->
                                    <div class="tab-pane fade" id="jeTabCustom">
                                        <div style="border:1px solid #e0e0e0;border-radius:6px;padding:12px;background:#f9f9f9;">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <input type="text" class="form-control form-control-sm" id="jeCustomName" placeholder="Service name">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="jeCustomPrice" placeholder="Base Price" step="0.01" min="0">
                                                </div>
                                                <div class="col-6">
                                                    <input type="number" class="form-control form-control-sm" id="jeCustomLabor" placeholder="Labor Cost" step="0.01" min="0">
                                                </div>
                                                <div class="col-12">
                                                    <textarea class="form-control form-control-sm" id="jeCustomSubItems" rows="2" placeholder=""></textarea>
                                                    <small class="text-muted">Enter sub-services or included items, one per line.</small>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" id="jeAddCustomItemBtn" class="btn btn-sm btn-dark w-100" onclick="jeAddCustomItem()">
                                                        <i class="bi bi-plus"></i> Add Custom Item
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-box-seam me-1"></i>Products</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="position-relative mb-2">
                                    <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                                    <input type="text" id="je_product_search" class="form-control form-control-sm" placeholder="Search products..." style="padding-left:30px;"
                                        onkeyup="joSearchFilter(this.value, 'je-product-row')">
                                </div>
                                <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                    <?php if (empty($allInventoryProducts)): ?>
                                        <p class="text-muted text-center small py-3 mb-0">No products in inventory.</p>
                                    <?php else: ?>
                                        <?php foreach ($allInventoryProducts as $prod): ?>
                                        <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card je-product-row" data-search-text="<?php echo escape(strtolower(($prod['product_name'] ?? '') . ' ' . ($prod['product_code'] ?? ''))); ?>">
                                            <div>
                                                <strong style="font-size:13px;"><?php echo escape($prod['product_name']); ?></strong>
                                                <small class="text-muted d-block"><?php echo escape($prod['product_code']); ?> • <?php echo (int)$prod['quantity']; ?> in stock</small>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($prod['selling_price']); ?></span>
                                                <input type="number" class="form-control form-control-sm text-center" id="je_prod_qty_<?php echo (int)$prod['id']; ?>" value="1" min="1" style="width:55px;">
                                                <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:12px;"
                                                    onclick="jeAddProduct(<?php echo (int)$prod['id']; ?>, '<?php echo addslashes(escape($prod['product_name'])); ?>', <?php echo (float)$prod['selling_price']; ?>, '<?php echo addslashes(escape($prod['product_code'])); ?>', <?php echo (int)$prod['quantity']; ?>, 'je_prod_qty_<?php echo (int)$prod['id']; ?>')">
                                                    <i class="bi bi-plus"></i> Add
                                                </button>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <p id="je_product_no_match" class="text-muted text-center small py-2 mb-0" style="display:none;">No matching products found.</p>
                                    <?php endif; ?>
                                </div>
                                <!-- Custom product entry -->
                                <div class="d-flex gap-1 mt-2">
                                    <input type="text" class="form-control form-control-sm" id="jeCustomProdName" placeholder="Product name" style="flex:2;">
                                    <input type="number" class="form-control form-control-sm" id="jeCustomProdPrice" placeholder="Price" step="0.01" min="0" style="flex:1;">
                                    <input type="number" class="form-control form-control-sm" id="jeCustomProdQty" placeholder="Qty" min="1" value="1" style="width:50px;">
                                    <button type="button" id="jeAddCustomProductBtn" class="btn btn-sm btn-dark py-0 px-2" onclick="jeAddCustomProduct()" title="Add custom product"><i class="bi bi-plus"></i></button>
                                </div>
                            </div>
                        </div>

                    </div><!-- /left -->

                    <!-- ── RIGHT COLUMN ── -->
                    <div class="col-lg-5">

                        <!-- Selected Items -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-cart me-1"></i>Selected Items</h6>
                                <span class="badge bg-dark" id="jeItemCount">0</span>
                            </div>
                            <div class="card-body p-0">
                                <div id="jeSelectedItems" style="min-height:120px;max-height:360px;overflow-y:auto;">
                                    <p class="text-muted text-center small py-4 mb-0" id="jeEmptyMsg">No items added yet.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Summary -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-receipt me-1"></i>Billing</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <hr class="my-2">
                                <!-- Subtotals -->
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Services Subtotal</span>
                                    <strong id="jeSubtotal">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Products Subtotal</span>
                                    <strong id="jePartsDisplay">₱0.00</strong>
                                </div>
                                <hr class="my-2">
                                <!-- Discount -->
                                <div class="mb-2">
                                    <label class="form-label form-label-sm">Discount Type</label>
                                    <select class="form-select form-select-sm" id="je_discount_type" onchange="jeCalc()">
                                        <option value="none">None</option>
                                        <option value="percentage">Percentage (%)</option>
                                        <option value="fixed">Fixed Amount (₱)</option>
                                        <option value="senior">Senior Citizen (20%)</option>
                                        <option value="pwd">PWD (20%)</option>
                                    </select>
                                </div>
                                <div class="mb-2" id="jeDiscountAmtRow">
                                    <label class="form-label form-label-sm">Discount Value</label>
                                    <input type="number" class="form-control form-control-sm" id="je_discount_value" value="0" min="0" step="0.01" oninput="jeCalc()">
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small text-muted">Discount</span>
                                    <span class="text-danger" id="jeDiscountDisplay">-₱0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>Total Amount</strong>
                                    <h5 class="mb-0" id="jeTotal" style="font-weight:700;">₱0.00</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <textarea class="form-control form-control-sm" id="je_notes" rows="2" placeholder="Additional notes or instructions..."></textarea>
                            </div>
                        </div>

                        <!-- Recommendation -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-lightbulb me-1"></i>Recommendations</h6>
                            </div>
                            <div class="card-body" style="padding:12px 15px;">
                                <p class="text-muted small mb-2">Recommend services, products, or type a custom note.</p>
                                <div class="d-flex gap-2 mb-2">
                                    <input type="text" id="je_rec_input" class="form-control form-control-sm" placeholder="Type recommendation or browse below..." onkeydown="if(event.key==='Enter'){event.preventDefault();jeAddRecommendation();}">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="jeAddRecommendation()" title="Add text"><i class="bi bi-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-dark" onclick="openJeRecBrowseModal()" title="Browse from records"><i class="bi bi-search"></i></button>
                                </div>
                                <div id="je_rec_list" style="border:1px solid #e0e0e0;border-radius:6px;padding:6px;background:#f9f9f9;min-height:32px;max-height:120px;overflow-y:auto;">
                                    <p class="text-muted small text-center mb-0 py-1" id="je_rec_empty">No recommendations yet.</p>
                                </div>
                            </div>
                        </div>

                    </div><!-- /right -->
                </div><!-- /row -->
                </form>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;padding:12px 20px;">
                <button type="button" class="btn btn-dark btn-sm" onclick="jeConvertToJo()">
                    <i class="bi bi-arrow-right-circle"></i> Convert to JO
                </button>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="jePrintPreview()">
                    <i class="bi bi-printer"></i> Print JE
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="jeSavePDF()">
                    <i class="bi bi-file-earmark-pdf"></i> Save PDF
                </button>
                <button type="button" class="btn btn-dark btn-sm" id="jeSaveBtn" onclick="jeSave()">
                    <i class="bi bi-save"></i> Save Job Estimate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo generateCSRFToken(); ?>';
const canOverridePaidPaymentStatus = <?php echo hasAnyRole(['admin', 'system_administrator']) ? 'true' : 'false'; ?>;
const isTechnicianUser = <?php echo $isTechnician ? 'true' : 'false'; ?>;
const isViewOnlyJoRole = <?php echo $isViewOnlyJoRole ? 'true' : 'false'; ?>;
const hideJoPrices = isTechnicianUser || isViewOnlyJoRole;
let printTemplateSettings = <?php echo json_encode($printTemplateSettings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;
const bundleServiceNamesMap = <?php echo json_encode($bundleServiceNamesMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;
const bundleProductsMap = <?php echo json_encode($bundleProductsMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;
const serviceSubItemsMap = <?php echo json_encode($serviceSubItemsMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;
const serviceCodesMap = <?php echo json_encode(array_column($allActiveServices, 'service_code', 'id'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;

async function refreshPrintTemplateSettings() {
    try {
        const response = await fetch(`${APP_URL}/api/print_template.php`);
        if (!response.ok) return;
        const data = await response.json();
        if (data && typeof data === 'object') {
            printTemplateSettings = { ...printTemplateSettings, ...data };
        }
    } catch (e) {
        console.warn('Unable to refresh print template settings', e);
    }
}

function applyPrintTemplate(template, vars) {
    let output = String(template || '');
    Object.keys(vars).forEach((key) => {
        const pattern = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
        output = output.replace(pattern, vars[key] ?? '');
    });
    return output;
}

function getDiscountDisplayLabel(type, value = 0) {
    const normalizedType = String(type || 'none').toLowerCase();
    const numericValue = parseFloat(value ?? 0) || 0;

    if (normalizedType === 'senior' || normalizedType === 'senior_citizen') {
        return 'Senior Citizen (20%)';
    }
    if (normalizedType === 'pwd') {
        return 'PWD (20%)';
    }
    if (normalizedType === 'percentage') {
        return `Percentage (${numericValue}%)`;
    }
    if (normalizedType === 'fixed') {
        return 'Fixed Amount';
    }
    if (normalizedType === 'custom') {
        return numericValue > 0 ? `Percentage (${numericValue}%)` : 'Fixed Amount';
    }
    return 'None';
}

function normalizeSavedDiscountType(type, percentageValue = 0) {
    const normalizedType = String(type || 'none').toLowerCase();

    if (normalizedType === 'senior_citizen' || normalizedType === 'senior') {
        return 'senior';
    }
    if (normalizedType === 'pwd') {
        return 'pwd';
    }
    if (normalizedType === 'custom') {
        return (parseFloat(percentageValue || 0) > 0) ? 'percentage' : 'fixed';
    }
    if (normalizedType === 'percentage' || normalizedType === 'fixed') {
        return normalizedType;
    }
    return 'none';
}

function getPrintHeaderHtml(documentTitle, documentNumber, documentDate) {
    return applyPrintTemplate(printTemplateSettings.header_template, {
        logo_url: printTemplateSettings.logo_url,
        company_name: printTemplateSettings.company_name,
        company_subtitle: printTemplateSettings.company_subtitle,
        contact_line: printTemplateSettings.contact_line,
        address_line: printTemplateSettings.address_line || '',
        tax_info: printTemplateSettings.tax_info || '',
        document_title: documentTitle,
        document_number: documentNumber || '—',
        document_date: documentDate || '—',
        footer_note: printTemplateSettings.footer_note
    });
}

function getPrintFooterHtml() {
    return applyPrintTemplate(printTemplateSettings.footer_template, {
        logo_url: printTemplateSettings.logo_url,
        company_name: printTemplateSettings.company_name,
        company_subtitle: printTemplateSettings.company_subtitle,
        contact_line: printTemplateSettings.contact_line,
        document_title: '',
        document_number: '',
        document_date: '',
        footer_note: printTemplateSettings.footer_note
    });
}

async function waitForPrintAssets(areaId, timeoutMs = 3000) {
    const area = document.getElementById(areaId);
    if (!area) return;

    const images = Array.from(area.querySelectorAll('img'));
    if (!images.length) return;

    const waits = images.map((img) => {
        if (img.complete && img.naturalWidth > 0) {
            return Promise.resolve();
        }

        return new Promise((resolve) => {
            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                resolve();
            };

            img.addEventListener('load', finish, { once: true });
            img.addEventListener('error', finish, { once: true });
            setTimeout(finish, timeoutMs);
        });
    });

    await Promise.all(waits);
}

/**
 * Set document title for PDF save naming, print or download PDF.
 * Format: PLATE_FirstnameLastname (e.g., HASX234_DjCortez)
 */
let _pdfMode = false;
function buildPdfFileName(plate, customerName, suffix = '') {
    const cleanPlate = (plate || '').replace(/[^a-zA-Z0-9-]/g, '').toUpperCase() || 'NO-PLATE';
    let fileName = cleanPlate;
    if (customerName && customerName.trim() && customerName.trim() !== '—') {
        const parts = customerName.trim().split(/\s+/);
        const formatted = parts.map(p => p.charAt(0).toUpperCase() + p.slice(1).toLowerCase()).join('');
        fileName = cleanPlate + '_' + formatted;
    }
    return fileName + suffix;
}

function printWithPdfName(plate, customerName) {
    let fileName = buildPdfFileName(plate, customerName);

    // Determine if JO or JE and add suffix
    const joContent = document.getElementById('joPrintContent');
    const jeContent = document.getElementById('jePrintContent');
    const joArea = document.getElementById('joPrintArea');
    const jeArea = document.getElementById('jePrintArea');
    const isJO = !!(joArea && getComputedStyle(joArea).display !== 'none' && joContent && joContent.innerHTML.trim());
    const isJE = !isJO && !!(jeArea && getComputedStyle(jeArea).display !== 'none' && jeContent && jeContent.innerHTML.trim());
    if (isJO) fileName += '_JO';
    else if (isJE) fileName += '_JE';

    if (_pdfMode && (typeof html2pdf !== 'undefined')) {
        // Auto-download PDF
        const content = isJO ? joContent : jeContent;
        if (content && content.innerHTML.trim()) {
            const previousWidth = content.style.width;
            const previousMaxWidth = content.style.maxWidth;
            const previousBoxSizing = content.style.boxSizing;
            const previousOverflow = content.style.overflow;
            const previousTableStyles = Array.from(content.querySelectorAll('table')).map((table) => ({
                table,
                width: table.style.width,
                maxWidth: table.style.maxWidth,
                tableLayout: table.style.tableLayout
            }));
            const previousCellStyles = Array.from(content.querySelectorAll('td, th')).map((cell) => ({
                cell,
                boxSizing: cell.style.boxSizing,
                minWidth: cell.style.minWidth,
                maxWidth: cell.style.maxWidth,
                overflowWrap: cell.style.overflowWrap,
                wordBreak: cell.style.wordBreak,
                whiteSpace: cell.style.whiteSpace
            }));
            // Keep a small safety gutter inside html2pdf's printable width.
            content.style.width = '190mm';
            content.style.maxWidth = '190mm';
            content.style.boxSizing = 'border-box';
            content.style.overflow = 'hidden';
            content.querySelectorAll('table').forEach((table) => {
                table.style.width = '100%';
                table.style.maxWidth = '100%';
                table.style.tableLayout = 'fixed';
            });
            content.querySelectorAll('td, th').forEach((cell) => {
                cell.style.boxSizing = 'border-box';
                cell.style.minWidth = '0';
                cell.style.maxWidth = '100%';
                cell.style.overflowWrap = 'anywhere';
                cell.style.wordBreak = 'break-word';
                cell.style.whiteSpace = 'normal';
            });
            const opt = {
                margin: [10, 10, 10, 10],
                filename: fileName + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: [215.9, 330.2], orientation: 'portrait' },
                pagebreak: { mode: ['css', 'legacy'] }
            };
            return html2pdf().set(opt).from(content).save().then(() => {
                content.style.width = previousWidth;
                content.style.maxWidth = previousMaxWidth;
                content.style.boxSizing = previousBoxSizing;
                content.style.overflow = previousOverflow;
                previousTableStyles.forEach(({ table, width, maxWidth, tableLayout }) => {
                    table.style.width = width;
                    table.style.maxWidth = maxWidth;
                    table.style.tableLayout = tableLayout;
                });
                previousCellStyles.forEach(({ cell, boxSizing, minWidth, maxWidth, overflowWrap, wordBreak, whiteSpace }) => {
                    cell.style.boxSizing = boxSizing;
                    cell.style.minWidth = minWidth;
                    cell.style.maxWidth = maxWidth;
                    cell.style.overflowWrap = overflowWrap;
                    cell.style.wordBreak = wordBreak;
                    cell.style.whiteSpace = whiteSpace;
                });
                document.getElementById('joPrintArea').style.display = 'none';
                document.getElementById('jePrintArea').style.display = 'none';
            });
        }
    }
    
    if (_pdfMode) {
        // html2pdf not available - fallback to print dialog
        showToast('PDF library not loaded. Using print dialog instead.');
    }

    // Normal print with custom title
    const originalTitle = document.title;
    document.title = fileName;
    window.print();
    document.title = originalTitle;
}

/* ═══════════════════════════════════════════
   JOB ORDER MODAL LOGIC
═══════════════════════════════════════════ */
let joItems    = [];   // { id, type, name, basePrice, labor, price, qty }
let joProducts = [];   // { id, name, code, price, qty }
let joEditingId = null;
let joEditingStatus = 'pending';
let joPaymentStatusLocked = false;
let joEditingVersion = null;
let joEditingJobNumber = null;
let joEditingTechnicians = [];
let technicianRemovalSaveMode = null;
let joPendingRemovalReason = null;

function getServiceUnitPrice(item) {
    // Unit price = base price only (labor is separate, not multiplied by qty)
    if (item && item.basePrice !== undefined && item.basePrice !== null) {
        return parseFloat(item.basePrice || 0);
    }
    return parseFloat(item?.price || 0) || 0;
}

function getServiceLineTotal(item) {
    const qty = parseInt(item?.qty ?? 1, 10) || 1;
    // Line total = (price * qty) + labor (labor added once, not multiplied)
    const base = parseFloat(item?.basePrice || 0);
    const labor = parseFloat(item?.labor || 0);
    return (base * qty) + labor;
}

function reorderArrayItem(list, fromIndex, toIndex) {
    if (!Array.isArray(list) || fromIndex === toIndex) return false;
    if (fromIndex < 0 || fromIndex >= list.length || toIndex < 0 || toIndex >= list.length) return false;
    const [item] = list.splice(fromIndex, 1);
    list.splice(toIndex, 0, item);
    return true;
}

function bindSelectedListDragReorder(containerId, config) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const key = config.key || containerId;
    if (container.dataset.dragReorderBound === key) return;
    container.dataset.dragReorderBound = key;

    let activeDrag = null;

    const applyDrop = (targetCard) => {
        if (!targetCard || !activeDrag) return;
        const targetKind = targetCard.dataset.reorderKind;
        const targetIndex = Number(targetCard.dataset.reorderIndex);
        if (!targetKind || Number.isNaN(targetIndex)) return;

        if (activeDrag.kind !== targetKind) return;
        const didMove = reorderArrayItem(activeDrag.list, activeDrag.index, targetIndex);
        if (didMove) {
            config.onReorder?.();
        }
    };

    container.addEventListener('dragstart', (event) => {
        const card = event.target.closest('[data-reorder-kind]');
        if (!card) return;

        const kind = card.dataset.reorderKind;
        const list = config.getList(kind);
        const index = Number(card.dataset.reorderIndex);
        if (!list || Number.isNaN(index)) return;

        activeDrag = { kind, list, index };
        card.style.opacity = '0.6';
        card.style.cursor = 'grabbing';
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', String(index));
    });

    container.addEventListener('dragover', (event) => {
        const card = event.target.closest('[data-reorder-kind]');
        if (!card) return;
        if (activeDrag && activeDrag.kind !== card.dataset.reorderKind) return;
        event.preventDefault();
        card.style.borderColor = '#111';
        card.style.background = '#f5f5f5';
    });

    container.addEventListener('dragleave', (event) => {
        const card = event.target.closest('[data-reorder-kind]');
        if (!card) return;
        card.style.borderColor = '';
        card.style.background = '';
    });

    container.addEventListener('drop', (event) => {
        const card = event.target.closest('[data-reorder-kind]');
        if (!card) return;
        event.preventDefault();
        applyDrop(card);
        card.style.borderColor = '';
        card.style.background = '';
        activeDrag = null;
    });

    container.addEventListener('dragend', () => {
        container.querySelectorAll('[data-reorder-kind]').forEach((card) => {
            card.style.opacity = '';
            card.style.cursor = '';
            card.style.borderColor = '';
            card.style.background = '';
        });
        activeDrag = null;
    });
}

function filterPickerRows(inputEl, rowSelector, emptyEl) {
    if (!inputEl) return;
    const rows = Array.from(document.querySelectorAll(rowSelector));
    if (!rows.length) return;

    const query = String(inputEl.value || '').trim().toLowerCase();
    let visibleCount = 0;

    rows.forEach((row) => {
        const haystack = String(row.dataset.searchText || row.textContent || '').toLowerCase();
        const isVisible = query === '' || haystack.includes(query);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount += 1;
    });

    if (emptyEl) {
        emptyEl.style.display = visibleCount === 0 ? 'block' : 'none';
    }
}

function bindPickerSearch(inputId, rowSelector, emptyId) {
    const inputEl = document.getElementById(inputId);
    if (!inputEl) return;

    const emptyEl = emptyId ? document.getElementById(emptyId) : null;
    const run = () => filterPickerRows(inputEl, rowSelector, emptyEl);

    inputEl.addEventListener('input', run);
    run();
}

function applyJoPaymentStatusEditRule(paymentStatus) {
    const payStatusEl = document.getElementById('jo_payment_status');
    if (!payStatusEl) return;

    // Since payment_status is now a hidden input, just set value
    joPaymentStatusLocked = paymentStatus === 'paid' && !canOverridePaidPaymentStatus;
    payStatusEl.value = paymentStatus || 'pending';
    joTogglePartial();
}

function joSetMode(isEdit, jobOrderNumber = '') {
    const title = document.getElementById('createJobOrderModalLabel');
    const saveBtn = document.getElementById('joSaveBtn');
    if (!title || !saveBtn) return;

    if (isEdit) {
        title.innerHTML = `<i class="bi bi-pencil-square"></i> Edit Job Order${jobOrderNumber ? ` — ${jobOrderNumber}` : ''}`;
        saveBtn.innerHTML = '<i class="bi bi-save"></i> Update Job Order';
    } else {
        title.innerHTML = '<i class="bi bi-file-earmark-text"></i> Create New Job Order';
        saveBtn.innerHTML = '<i class="bi bi-save"></i> Save Job Order';
    }
}

/* ── Product picker ── */
function notifySelectedServiceAdded(name, type = 'service') {
    const label = type === 'bundle' ? 'Package' : type === 'custom' ? 'Custom service' : 'Service';
    showToast(label + ' "' + name + '" added to selected items.');
}

function notifySelectedProductAdded(name) {
    showToast('Product "' + name + '" added to selected items.');
}

function joAddProduct(id, name, price, code = '', stock = 0, qtyInputId = null) {
    const qtyInput = qtyInputId ? document.getElementById(qtyInputId) : document.getElementById('jo_product_qty');
    const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10) || 1);
    const parsedId = parseInt(id, 10);
    const parsedPrice = parseFloat(price || 0) || 0;
    const availableStock = parseInt(stock || 0, 10) || 0;
    if (!parsedId || !name) return;

    // Prevent adding if out of stock
    if (availableStock <= 0) {
        showToast('Product "' + name + '" is out of stock.');
        if (qtyInput) qtyInput.value = 1;
        return;
    }

    // Only merge with existing non-bundle product of same ID
    const existing = joProducts.find(p => p.id === parsedId && !p.fromBundle);
    if (existing) {
        const newQty = (existing.qty || 0) + qty;
        if (newQty > availableStock) {
            showToast('Cannot add. Only ' + availableStock + ' of "' + name + '" available.');
            if (qtyInput) qtyInput.value = 1;
            return;
        }
        existing.qty = newQty;
    } else {
        if (qty > availableStock) {
            showToast('Cannot add. Only ' + availableStock + ' of "' + name + '" available.');
            if (qtyInput) qtyInput.value = 1;
            return;
        }
        joProducts.push({ id: parsedId, name, code, price: parsedPrice, qty, stock: availableStock });
    }
    if (qtyInput) qtyInput.value = 1;
    notifySelectedProductAdded(name);
    joRenderProducts();
    joRenderItems();
    joCalc();
}

function joRemoveProduct(idx) {
    if (_joEditingCustomProductIdx === idx) {
        joClearCustomProductEditState();
    }
    joProducts.splice(idx, 1);
    joRenderProducts();
    joRenderItems();
    joCalc();
}

let _joEditingCustomItemIdx = null;
let _joEditingCustomProductIdx = null;

function joClearCustomItemEditState() {
    _joEditingCustomItemIdx = null;
    document.getElementById('joCustomName').value = '';
    document.getElementById('joCustomPrice').value = '';
    document.getElementById('joCustomLabor').value = '';
    document.getElementById('joCustomSubItems').value = '';
    const btn = document.getElementById('joAddCustomItemBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-plus"></i> Add Custom Item';
        btn.title = 'Add custom item';
    }
}

function joClearCustomProductEditState() {
    _joEditingCustomProductIdx = null;
    document.getElementById('joCustomProdName').value = '';
    document.getElementById('joCustomProdPrice').value = '';
    document.getElementById('joCustomProdQty').value = '1';
    const btn = document.getElementById('joAddCustomProductBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-plus"></i>';
        btn.title = 'Add custom product';
    }
}

function joEditCustomItem(idx) {
    const item = joItems[idx];
    if (!item || item.type !== 'custom') return;
    _joEditingCustomItemIdx = idx;
    _joEditingCustomProductIdx = null;
    document.getElementById('joCustomName').value = item.name || '';
    document.getElementById('joCustomPrice').value = parseFloat(item.basePrice || 0).toFixed(2);
    document.getElementById('joCustomLabor').value = parseFloat(item.labor || 0).toFixed(2);
    document.getElementById('joCustomSubItems').value = Array.isArray(item.selectedSubItems) ? item.selectedSubItems.join('\n') : '';
    const btn = document.getElementById('joAddCustomItemBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-pencil-square"></i> Update Custom Item';
        btn.title = 'Update custom item';
    }
    const productBtn = document.getElementById('joAddCustomProductBtn');
    if (productBtn) {
        productBtn.innerHTML = '<i class="bi bi-plus"></i>';
        productBtn.title = 'Add custom product';
    }
}

function joEditCustomProduct(idx) {
    const product = joProducts[idx];
    if (!product || product.id !== 0) return;
    _joEditingCustomProductIdx = idx;
    _joEditingCustomItemIdx = null;
    document.getElementById('joCustomProdName').value = product.name || '';
    document.getElementById('joCustomProdPrice').value = parseFloat(product.price || 0).toFixed(2);
    document.getElementById('joCustomProdQty').value = String(Math.max(1, parseInt(product.qty || 1, 10)));
    const btn = document.getElementById('joAddCustomProductBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-pencil-square"></i>';
        btn.title = 'Update custom product';
    }
    const serviceBtn = document.getElementById('joAddCustomItemBtn');
    if (serviceBtn) {
        serviceBtn.innerHTML = '<i class="bi bi-plus"></i> Add Custom Item';
        serviceBtn.title = 'Add custom item';
    }
}

/* ── Custom Entry (JO) ── */
function joAddCustomItem() {
    const name = document.getElementById('joCustomName').value.trim();
    if (!name) { showToast('Please enter a service/package name.'); return; }
    const basePrice = parseFloat(document.getElementById('joCustomPrice').value) || 0;
    const laborCost = parseFloat(document.getElementById('joCustomLabor').value) || 0;
    const subText = document.getElementById('joCustomSubItems').value.trim();
    const subItems = subText ? subText.split('\n').map(s => s.trim()).filter(s => s) : [];

    if (_joEditingCustomItemIdx !== null && joItems[_joEditingCustomItemIdx]) {
        const item = joItems[_joEditingCustomItemIdx];
        item.name = name;
        item.basePrice = basePrice;
        item.labor = laborCost;
        item.price = basePrice + laborCost;
        item.selectedSubItems = subItems;
        joRenderItems();
        joCalc();
        joClearCustomItemEditState();
        return;
    }

    const effectivePrice = basePrice + laborCost;
    joItems.push({ type: 'custom', id: 0, name, basePrice, labor: laborCost, price: effectivePrice, qty: 1, selectedSubItems: subItems });
    notifySelectedServiceAdded(name, 'custom');
    joRenderItems();
    joCalc();

    joClearCustomItemEditState();
}

function joAddCustomProduct() {
    const name = document.getElementById('joCustomProdName').value.trim();
    const price = parseFloat(document.getElementById('joCustomProdPrice').value) || 0;
    const qty = Math.max(1, parseInt(document.getElementById('joCustomProdQty').value) || 1);
    if (!name) { showToast('Please enter a product name.'); return; }

    if (_joEditingCustomProductIdx !== null && joProducts[_joEditingCustomProductIdx]) {
        const product = joProducts[_joEditingCustomProductIdx];
        product.name = name;
        product.price = price;
        product.qty = qty;
        joRenderProducts();
        joRenderItems();
        joCalc();
        joClearCustomProductEditState();
        return;
    }

    joProducts.push({ id: 0, name, code: '', price, qty });
    notifySelectedProductAdded(name);
    joRenderProducts();
    joRenderItems();
    joCalc();

    joClearCustomProductEditState();
}

function joChangeProductQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) { joRemoveProduct(idx); return; }
    const prod = joProducts[idx];
    if (!prod) return;
    // If stock was stored when the product was added, enforce it here for immediate UX feedback.
    if (typeof prod.stock !== 'undefined') {
        const avail = parseInt(prod.stock) || 0;
        if (avail <= 0) {
            showToast('This product is currently out of stock and has been removed from the list.');
            joRemoveProduct(idx);
            return;
        }
        if (qty > avail) {
            showToast('Only ' + avail + ' of "' + (prod.name || 'product') + '" available in stock.');
            prod.qty = avail;
            joRenderItems();
            joCalc();
            return;
        }
    }

    prod.qty = qty;
    joRenderItems();
    joCalc();
}

function joChangeProductPrice(idx, val) {
    const price = parseFloat(val);
    if (!Number.isFinite(price) || price < 0) return;
    const product = joProducts[idx];
    if (!product) return;
    product.price = price;
    joRenderProducts();
    joRenderItems();
    joCalc();
}

function joRenderProducts() {
    const container = document.getElementById('joProductsList');
    if (!container) return;
    if (joProducts.length === 0) {
        container.innerHTML = '<p class="text-muted small text-center py-2 mb-0">No products added.</p>';
        return;
    }
    let html = '';
    joProducts.forEach((p, idx) => {
        const lineTotal = (p.price * p.qty).toFixed(2);
        html += `
        <div class="d-flex align-items-center justify-content-between px-2 py-1 mb-1 bg-white rounded" style="border:1px solid #eee;font-size:12px;">
            <div style="flex:1;min-width:0;">
                <div class="text-truncate fw-semibold">${p.name}</div>
                ${p.code ? `<small class="text-muted d-block">${p.code}</small>` : ''}
                <div class="d-flex align-items-center gap-1 mt-1">
                    <small class="text-muted">₱</small>
                    <input type="number" class="form-control form-control-sm text-center" value="${parseFloat(p.price).toFixed(2)}" min="0" step="0.01"
                        style="width:82px;font-size:11px;" onchange="joChangeProductPrice(${idx}, this.value)" aria-label="Product unit price">
                    <small class="text-muted">each</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1 ms-1">
                <input type="number" class="form-control form-control-sm text-center" value="${p.qty}" min="1"
                    style="width:46px;font-size:12px;" onchange="joChangeProductQty(${idx}, this.value)">
                    <span style="min-width:58px;text-align:right;font-weight:600;">₱${lineTotal}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="joRemoveProduct(${idx})">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

let _pendingJoItem = null;

function joAddItem(type, id, name, basePrice, laborCost = 0) {
    const existing = joItems.find(i => i.type === type && i.id === id);
    if (existing) {
        existing.qty++;
        existing.labor = parseFloat(laborCost || existing.labor || 0);
        existing.price = (existing.basePrice !== undefined && existing.basePrice !== null ? parseFloat(existing.basePrice) : 0) + existing.labor;
        notifySelectedServiceAdded(name, type);
        joRenderItems();
        joCalc();
        return;
    }
    const subItems = (type === 'service') ? (serviceSubItemsMap[id] || []) : [];
    if (subItems.length > 0) {
        _pendingJoItem = { type, id, name, basePrice: parseFloat(basePrice || 0), laborCost: parseFloat(laborCost || 0) };
        bindJoSubItemConfirmDefault();
        const titleEl = document.getElementById('joSubItemSelectTitle');
        if (titleEl) titleEl.textContent = name;
        const listEl = document.getElementById('joSubItemSelectList');
        if (listEl) {
            listEl.innerHTML = subItems.map((item, i) => `
                <div class="form-check mb-1">
                    <input class="form-check-input jo-subitem-check" type="checkbox" checked id="joSubItemCb_${i}">
                    <label class="form-check-label small" for="joSubItemCb_${i}">${item}</label>
                </div>`).join('');
        }
        setTimeout(() => syncModalStacking('joSubItemSelectModal'), 20);
        bootstrap.Modal.getOrCreateInstance(document.getElementById('joSubItemSelectModal')).show();
        return;
    }
    _joAddItemFinal(type, id, name, parseFloat(basePrice || 0), parseFloat(laborCost || 0), []);
}

function confirmJoSubItemSelect() {
    if (!_pendingJoItem) return;
    const subItems = serviceSubItemsMap[_pendingJoItem.id] || [];
    const selected = subItems.filter((_, i) => {
        const cb = document.getElementById(`joSubItemCb_${i}`);
        return cb && cb.checked;
    });
    _joAddItemFinal(_pendingJoItem.type, _pendingJoItem.id, _pendingJoItem.name, _pendingJoItem.basePrice, _pendingJoItem.laborCost, selected);
    bootstrap.Modal.getInstance(document.getElementById('joSubItemSelectModal')).hide();
    _pendingJoItem = null;
}

function _joAddItemFinal(type, id, name, basePrice, laborCost, selectedSubItems) {
    const effectivePrice = basePrice + laborCost;
    joItems.push({ type, id, name, basePrice, labor: laborCost, price: effectivePrice, qty: 1, selectedSubItems });
    notifySelectedServiceAdded(name, type);

    // Auto-add bundle products to joProducts
    if (type === 'bundle') {
        const bundleProds = bundleProductsMap[id] || [];
        bundleProds.forEach(p => {
            if (!p.id) return;
            const existing = joProducts.find(ep => ep.id === p.id && ep.fromBundle);
            if (existing) {
                existing.qty += p.qty;
            } else {
                joProducts.push({ id: p.id, name: p.name, code: p.code || '', price: 0, qty: p.qty, fromBundle: id });
            }
        });
        joRenderProducts();
    }

    joRenderItems();
    joCalc();
}

function joRemoveItem(idx) {
    if (_joEditingCustomItemIdx === idx) {
        joClearCustomItemEditState();
    }
    joItems.splice(idx, 1);
    joRenderItems();
    joCalc();
}

function joChangeBasePrice(idx, val) {
    const basePrice = parseFloat(val) || 0;
    joItems[idx].basePrice = basePrice;
    joItems[idx].price = basePrice + (joItems[idx].labor || 0);
    joRenderItems();
    joCalc();
}

function joChangeLabor(idx, val) {
    const labor = parseFloat(val) || 0;
    joItems[idx].labor = labor;
    joItems[idx].price = (joItems[idx].basePrice || 0) + labor;
    joRenderItems();
    joCalc();
}

function joChangeQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) {
        joRemoveItem(idx);
        return;
    }
    joItems[idx].qty = qty;
    joRenderItems();
    joCalc();
}

function joRenderItems() {
    const container = document.getElementById('joSelectedItems');
    const emptyMsg  = document.getElementById('joEmptyMsg');
    const countBadge = document.getElementById('joItemCount');

    if (joItems.length === 0 && joProducts.length === 0) {
        container.innerHTML = '<p class="text-muted text-center small py-4 mb-0" id="joEmptyMsg">No items added yet.</p>';
        countBadge.textContent = '0';
        return;
    }

    countBadge.textContent = joItems.length + joProducts.length;
    let html = '';
    joItems.forEach((item, idx) => {
        const unitPrice = getServiceUnitPrice(item);
        const lineTotal = getServiceLineTotal(item).toFixed(2);
        const baseValue = item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice).toFixed(2) : '';
        const laborValue = item.labor !== undefined && item.labor !== null ? parseFloat(item.labor).toFixed(2) : '';
        const itemReference = item.type === 'bundle'
            ? `BND${item.id}`
            : item.type === 'service'
                ? (serviceCodesMap[item.id] || `SVC${String(item.id).padStart(3, '0')}`)
                : 'Custom';
        // Sub-items: only show what the user actually selected/confirmed
        const subItems = item.type === 'bundle'
            ? (bundleServiceNamesMap[item.id] || [])
            : (Array.isArray(item.selectedSubItems) ? item.selectedSubItems : []);
        const subItemsHtml = subItems.length > 0
            ? `<div style="padding-left:10px;margin-top:2px;">${subItems.map(s=>`<div style="font-size:11px;color:#666;word-break:break-word;">- ${s}</div>`).join('')}</div>`
            : '';
        html += `
        <div class="px-3 py-2 reorder-card ${item.type === 'custom' ? 'custom-editable-item' : ''}" draggable="true" data-reorder-kind="jo-service" data-reorder-index="${idx}" data-custom-edit="${item.type === 'custom' ? idx : ''}" style="border-bottom:1px solid #f0f0f0;cursor:${item.type === 'custom' ? 'pointer' : 'grab'};user-select:none;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="text-muted" style="font-size:11px;cursor:grab;" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                        <div style="font-size:12px;font-weight:600;word-break:break-word;">${item.name}</div>
                    </div>
                    <small class="text-muted d-block" style="font-size:10px;padding-left:21px;">${itemReference}</small>
                    ${subItemsHtml}
                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap" style="row-gap:4px;">
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Base</small>
                            <input type="number" class="form-control form-control-sm text-center" value="" min="0" step="0.01" placeholder="${baseValue || '0.00'}"
                                style="width:70px;min-width:70px;font-size:11px;padding:0.2rem 0.3rem;-moz-appearance:textfield;appearance:textfield;" onchange="joChangeBasePrice(${idx}, this.value)">
                        </div>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Labor</small>
                            <input type="number" class="form-control form-control-sm text-center" value="" min="0" step="0.01" placeholder="${laborValue || '0.00'}"
                                style="width:70px;min-width:70px;font-size:11px;padding:0.2rem 0.3rem;-moz-appearance:textfield;appearance:textfield;" onchange="joChangeLabor(${idx}, this.value)">
                        </div>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Qty</small>
                            <input type="number" class="form-control form-control-sm text-center" value="${item.qty}" min="1" style="width:42px;min-width:42px;font-size:11px;padding:0.2rem 0.3rem;" onchange="joChangeQty(${idx}, this.value)">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span style="font-size:13px;font-weight:700;min-width:74px;text-align:center;">₱${lineTotal}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;" onclick="joRemoveItem(${idx})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    joProducts.forEach((product, idx) => {
        const isFromBundle = !!product.fromBundle;
        const lineTotal = product.price * product.qty;
        const productReference = product.id > 0 ? (product.code || `PRD${String(product.id).padStart(2, '0')}`) : 'Custom';
        html += `
        <div class="px-3 py-2 reorder-card ${product.id === 0 ? 'custom-editable-item' : ''}" draggable="true" data-reorder-kind="jo-product" data-reorder-index="${idx}" data-custom-edit="${product.id === 0 ? idx : ''}" style="border-bottom:1px solid #f0f0f0;cursor:${product.id === 0 ? 'pointer' : 'grab'};user-select:none;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="text-muted" style="font-size:11px;cursor:grab;" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                        <div style="font-size:12px;font-weight:600;word-break:break-word;">${product.name}</div>
                    </div>
                    <small class="text-muted d-block" style="font-size:10px;padding-left:21px;">${productReference}</small>
                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap" style="row-gap:4px;">
                        <small class="text-muted d-flex align-items-center gap-1">₱<input type="number" class="form-control form-control-sm text-center" value="${parseFloat(product.price).toFixed(2)}" min="0" step="0.01" style="width:82px;font-size:11px;" onchange="joChangeProductPrice(${idx}, this.value)" aria-label="Product unit price"> each</small>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Qty</small>
                            <input type="number" class="form-control form-control-sm text-center" value="${product.qty}" min="1" style="width:42px;min-width:42px;font-size:11px;padding:0.2rem 0.3rem;" onchange="joChangeProductQty(${idx}, this.value)">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span style="font-size:13px;font-weight:700;min-width:74px;text-align:center;">₱${lineTotal.toFixed(2)}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;" onclick="joRemoveProduct(${idx})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
    container.querySelectorAll('.custom-editable-item').forEach(el => {
        el.addEventListener('click', (event) => {
            if (event.target.closest('input, button')) return;
            const kind = el.dataset.reorderKind;
            const idx = parseInt(el.dataset.reorderIndex, 10);
            if (kind === 'jo-service') {
                joEditCustomItem(idx);
            } else if (kind === 'jo-product') {
                joEditCustomProduct(idx);
            }
        });
    });
    bindSelectedListDragReorder('joSelectedItems', {
        key: 'jo-selected-items',
        getList: (kind) => kind === 'jo-service' ? joItems : (kind === 'jo-product' ? joProducts : null),
        onReorder: () => {
            joRenderItems();
            joCalc();
        }
    });
}

function joCalc() {
    let subtotal  = joItems.reduce((sum, i) => sum + getServiceLineTotal(i), 0);
    let partsTotal = joProducts.reduce((sum, p) => p.fromBundle ? sum : sum + p.price * p.qty, 0);
    const discType = document.getElementById('jo_discount_type').value;
    const discVal  = parseFloat(document.getElementById('jo_discount_value').value) || 0;

    const discRow = document.getElementById('joDiscountAmtRow');
    discRow.style.display = (discType === 'none' || discType === 'senior' || discType === 'pwd') ? 'none' : '';

    let discountAmt = 0;
    const base = subtotal + partsTotal;
    if (discType === 'percentage') discountAmt = base * (discVal / 100);
    else if (discType === 'fixed')  discountAmt = discVal;
    else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;

    discountAmt = Math.min(discountAmt, base);
    const total = Math.max(0, base - discountAmt);

    document.getElementById('joSubtotal').textContent        = '₱' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('joPartsDisplay').textContent    = '₱' + partsTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('joDiscountDisplay').textContent = '-₱' + discountAmt.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('joTotal').textContent           = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // Update payment balance display
    joUpdatePaymentBalance(total);
}

// ── Inline Payment Records (Create JO) ──
let joInlinePayments = []; // [{method, amount, reference}]
let joInlinePaymentActionsEnabled = true;
let joPaymentsListTargetId = 'joPaymentsList';

function getActiveJoTotalAmount() {
    const totalEl = document.getElementById('editJoTotal') || document.getElementById('joTotal');
    const totalText = (totalEl?.textContent || '0').replace(/[₱,]/g, '');
    return parseFloat(totalText) || 0;
}

function updateJoPaymentBalanceDisplay(total = getActiveJoTotalAmount()) {
    const totalPaid = joInlinePayments.reduce((s, p) => s + p.amount, 0);
    const balance = Math.max(0, (total || 0) - totalPaid);
    const balanceField = document.getElementById('ap_balance_display');
    if (balanceField) balanceField.value = '₱' + balance.toFixed(2);
    return { totalPaid, balance };
}

function joUpdatePaymentBalance(total) {
    const { totalPaid, balance } = updateJoPaymentBalanceDisplay(total);
    const fmt = n => '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    const paidEl = document.getElementById('joTotalPaid');
    const balEl  = document.getElementById('joBalanceRemaining');
    if (paidEl) paidEl.textContent = fmt(totalPaid);
    if (balEl)  balEl.textContent  = fmt(balance);

    // Update hidden payment_status and partial_amount for API compatibility
    const statusEl   = document.getElementById('jo_payment_status');
    const partialEl  = document.getElementById('jo_partial_amount');
    const methodEl   = document.getElementById('jo_payment_method');
    if (statusEl) {
        if (totalPaid <= 0) statusEl.value = 'pending';
        else if (totalPaid >= (total || 0)) statusEl.value = 'paid';
        else statusEl.value = 'partial';
    }
    if (partialEl) partialEl.value = totalPaid > 0 ? totalPaid.toFixed(2) : '0';
    if (methodEl && joInlinePayments.length > 0) methodEl.value = joInlinePayments[joInlinePayments.length - 1].method;
}

function joAddInlinePayment() {
    // Called from the modal save — not directly
}

function _ensureAddPaymentBackdropFront() {
    const backdrops = Array.from(document.querySelectorAll('.modal-backdrop'));
    if (!backdrops.length) return;

    backdrops.forEach((el, index) => {
        const isTop = index === backdrops.length - 1;
        el.style.zIndex = isTop ? '1090' : '1040';
        el.style.pointerEvents = isTop ? 'auto' : 'none';
    });
}

function _showAddPaymentModalAfterParentHide(modalEl) {
    const parentModal = _hideParentModalForAddPayment(modalEl);
    const delay = parentModal ? 220 : 0;
    setTimeout(() => {
        _ensureAddPaymentBackdropFront();
        const payModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        payModal.show();

        setTimeout(() => {
            const backdrops = Array.from(document.querySelectorAll('.modal-backdrop'));
            backdrops.forEach((el, i) => {
                const isTop = i === backdrops.length - 1;
                el.style.zIndex = isTop ? '1090' : '1040';
                el.style.pointerEvents = isTop ? 'auto' : 'none';
            });
            if (modalEl) modalEl.style.zIndex = '1095';
            try { syncModalStacking('addPaymentModal'); } catch (e) { /* ignore if undefined */ }
        }, 150);
    }, delay);
}

function openJoCreatePaymentModal() {
    const total = getActiveJoTotalAmount();
    const { totalPaid, balance } = updateJoPaymentBalanceDisplay(total);

    const modalEl = document.getElementById('addPaymentModal');
    const titleEl = document.getElementById('addPaymentModalLabel');
    const saveBtn = document.getElementById('apSaveBtn');
    document.getElementById('ap_jo_id').value = '_create_';
    document.getElementById('ap_balance_display').value = '₱' + balance.toFixed(2);
    document.getElementById('ap_amount').value = balance > 0 ? balance.toFixed(2) : '';
    document.getElementById('ap_method').value = 'cash';
    document.getElementById('ap_reference').value = '';
    document.getElementById('ap_paid_by').value = '';
    document.getElementById('ap_notes').value = '';
    document.getElementById('ap_balance_display').value = '₱' + balance.toFixed(2);
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-cash me-2"></i>Add Payment';
    if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save Payment';

    if (modalEl && modalEl.parentNode !== document.body) document.body.appendChild(modalEl);
    _showAddPaymentModalAfterParentHide(modalEl);
}

function openEditInlinePaymentModal(idx) {
    const payment = joInlinePayments[idx];
    if (!payment) {
        showToast('Payment not found.');
        return;
    }

    _editingInlinePaymentIndex = idx;
    const modalEl = document.getElementById('addPaymentModal');
    const titleEl = document.getElementById('addPaymentModalLabel');
    const saveBtn = document.getElementById('apSaveBtn');

    document.getElementById('ap_jo_id').value = '_create_';
    document.getElementById('ap_balance_display').value = 'Edit inline payment';
    document.getElementById('ap_amount').value = parseFloat(payment.amount || 0).toFixed(2);
    document.getElementById('ap_method').value = payment.method || 'cash';
    document.getElementById('ap_reference').value = payment.reference || '';
    document.getElementById('ap_paid_by').value = payment.paidBy || '';
    document.getElementById('ap_notes').value = payment.notes || '';

    if (titleEl) titleEl.innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Payment';
    if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Update Payment';

    if (modalEl && modalEl.parentNode !== document.body) document.body.appendChild(modalEl);
    _showAddPaymentModalAfterParentHide(modalEl);
}

function openEditPaymentModal(joId, paymentEncoded) {
    try {
        const payment = JSON.parse(decodeURIComponent(paymentEncoded));
        _editingPaymentRecord = { joId, paymentId: payment.id };
        document.getElementById('ep_jo_id').value = joId;
        document.getElementById('ep_payment_id').value = payment.id || '';
        document.getElementById('ep_amount').value = parseFloat(payment.amount || 0).toFixed(2);
        document.getElementById('ep_method').value = payment.payment_method || 'cash';
        document.getElementById('ep_reference').value = payment.reference_number || '';
        document.getElementById('ep_paid_by').value = payment.paid_by || '';
        document.getElementById('ep_payment_date').value = payment.payment_date ? String(payment.payment_date).slice(0, 16).replace(' ', 'T') : '';
        document.getElementById('ep_notes').value = payment.notes || '';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editPaymentModal')).show();
    } catch (err) {
        showToast('Unable to open payment editor.');
    }
}

function saveEditedPaymentRecord() {
    const payload = {
        csrf_token: '<?php echo generateCSRFToken(); ?>',
        payment_id: parseInt(document.getElementById('ep_payment_id').value, 10) || 0,
        amount: parseFloat(document.getElementById('ep_amount').value) || 0,
        payment_method: document.getElementById('ep_method').value,
        reference_number: document.getElementById('ep_reference').value.trim(),
        paid_by: document.getElementById('ep_paid_by').value.trim(),
        payment_date: document.getElementById('ep_payment_date').value ? document.getElementById('ep_payment_date').value.replace('T', ' ') + ':00' : null,
        notes: document.getElementById('ep_notes').value.trim()
    };

    if (!payload.payment_id || payload.amount <= 0) {
        showToast('Please enter a valid amount.');
        return;
    }

    fetch(`${APP_URL}/api/job_orders.php?action=update_payment&id=${document.getElementById('ep_jo_id').value}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showToast('Error: ' + data.message);
            return;
        }
        bootstrap.Modal.getInstance(document.getElementById('editPaymentModal'))?.hide();
        if (_viewingJoId) {
            viewJobOrder(_viewingJoId);
        } else {
            location.reload();
        }
    })
    .catch(() => showToast('Network error.'));
}

function deletePaymentRecord(joId, paymentId) {
    const amount = document.getElementById('ep_amount')?.value || document.querySelector(`[data-payment-id="${paymentId}"]`)?.textContent?.trim() || '—';
    const method = document.getElementById('ep_method')?.value || '—';
    appConfirm(`Delete this payment record?\n\nPayment ID: ${paymentId}\nAmount: ₱${amount}\nMethod: ${method}\nJob Order ID: ${joId}`, {
        title: 'Delete Payment',
        confirmText: 'Delete',
        variant: 'danger'
    }).then((confirmed) => {
        if (!confirmed) return;
        fetch(`${APP_URL}/api/job_orders.php?action=delete_payment&id=${joId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: '<?php echo generateCSRFToken(); ?>',
                payment_id: paymentId
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showToast('Error: ' + data.message);
                return;
            }
            if (_viewingJoId) {
                viewJobOrder(_viewingJoId);
            } else {
                location.reload();
            }
        })
        .catch(() => showToast('Network error.'));
    });
}

function joRemoveInlinePayment(idx) {
    joInlinePayments.splice(idx, 1);
    const totalEl = document.getElementById('editJoTotal') || document.getElementById('joTotal');
    const totalText = (totalEl?.textContent || '0').replace(/[₱,]/g, '');
    joRenderInlinePayments();
    joUpdatePaymentBalance(parseFloat(totalText) || 0);
}

function joRenderInlinePayments() {
    const isEditMode = !!joEditingId && !!document.getElementById('joPaymentsListEdit');
    const listEl = document.getElementById(isEditMode ? 'joPaymentsListEdit' : joPaymentsListTargetId);
    if (!listEl) return;
    if (joInlinePayments.length === 0) {
        listEl.innerHTML = '<p class="text-muted small text-center py-1 mb-0">No payments added.</p>';
        return;
    }
    const fmt = n => '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const actionsEnabled = joInlinePaymentActionsEnabled;
    listEl.innerHTML = joInlinePayments.map((p, i) => `
    <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded" style="border:1px solid #eee;font-size:12px;">
        <div class="d-flex align-items-center gap-2">
            ${actionsEnabled ? `<button type="button" class="btn btn-sm btn-outline-dark py-0 px-1" style="font-size:10px;" onclick="openEditInlinePaymentModal(${i})"><i class="bi bi-pencil"></i></button>` : ''}
            <div>
                <strong>${fmt(p.amount)}</strong>
                <small class="text-muted ms-1">${(p.method||'cash').replace(/_/g,' ')}</small>
                ${p.reference ? `<small class="text-muted ms-1">• ${p.reference}</small>` : ''}
            </div>
        </div>
        ${actionsEnabled && !isEditMode ? `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:10px;" onclick="joRemoveInlinePayment(${i})"><i class="bi bi-x"></i></button>` : ''}
    </div>`).join('');
}

function joTogglePartial() {
    // Legacy — no longer needed with inline payments system
    return;
}

function joCalcPartial() {
    // Legacy — no longer needed with inline payments system
    return;
}

let _joMainTechIds  = [];
let _joAssistTechIds = [];
let _joTechPickerMode = 'main';

function getSelectedTechnicianIds() {
    return Array.from(document.querySelectorAll('.jo-tech-check:checked'))
        .map(cb => parseInt(cb.value, 10))
        .filter(id => id > 0);
}

function joUpdateTechnicianIndicator() {
    const badge = document.getElementById('joTechCountBadge');
    if (!badge) return;
    const count = getSelectedTechnicianIds().length;
    badge.textContent = `${count} selected`;
    badge.className = 'badge bg-secondary';
}

// ── Technician Search & Filter ──
let _joTechFilterMode = 'all';

function joTechSetFilter(mode) {
    _joTechFilterMode = mode;
    const allBtn = document.getElementById('joTechFilterAll');
    const availBtn = document.getElementById('joTechFilterAvailable');
    const busyBtn = document.getElementById('joTechFilterBusy');
    allBtn.className = mode === 'all' ? 'btn btn-dark btn-sm py-0 px-2' : 'btn btn-outline-secondary btn-sm py-0 px-2';
    availBtn.className = mode === 'available' ? 'btn btn-secondary btn-sm py-0 px-2' : 'btn btn-outline-secondary btn-sm py-0 px-2';
    busyBtn.className = mode === 'busy' ? 'btn btn-warning btn-sm py-0 px-2' : 'btn btn-outline-warning btn-sm py-0 px-2';
    [allBtn, availBtn, busyBtn].forEach(b => { b.style.fontSize = '10px'; b.style.lineHeight = '1.4'; });
    joTechFilter();
}

function joTechFilter() {
    const q = (document.getElementById('jo_tech_search')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.jo-tech-row');
    rows.forEach(row => {
        const name = row.getAttribute('data-tech-name') || '';
        const nameMatch = !q || name.indexOf(q) !== -1;

        let statusMatch = true;
        if (_joTechFilterMode !== 'all') {
            const badge = row.querySelector('.jo-tech-avail-badge');
            const badgeText = (badge?.textContent || '').toLowerCase().trim();
            if (_joTechFilterMode === 'available') {
                statusMatch = badgeText === 'available';
            } else if (_joTechFilterMode === 'busy') {
                statusMatch = badgeText === 'busy';
            }
        }

        if (nameMatch && statusMatch) {
            row.style.removeProperty('display');
        } else {
            row.style.setProperty('display', 'none', 'important');
        }
    });
}

function joOnTechChange(id, checked) {
    if (!checked && joEditingId && joEditingTechnicians.some(t => parseInt(t.id, 10) === id && t.assignment_status !== 'removed')) {
        technicianRemovalSaveMode = 'edit-selection';
        joPendingRemovalReason = null;
        document.getElementById('technicianRemovalReasonSelect').value = 'Absent';
        updateTechnicianRemovalConfirmation('Absent');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('technicianRemovalReasonModal')).show();
    }
    const roleEl = document.getElementById('jo_tech_role_' + id);
    if (roleEl) roleEl.style.display = checked ? '' : 'none';
    if (!checked) {
        _joAssistTechIds = _joAssistTechIds.filter(i => i !== id);
        _joMainTechIds   = _joMainTechIds.filter(i => i !== id);
    } else if (!_joMainTechIds.includes(id) && !_joAssistTechIds.includes(id)) {
        _joMainTechIds.push(id);
    }
    joUpdateTechnicianIndicator();
}

function joOnRoleChange(id, role) {
    _joMainTechIds   = _joMainTechIds.filter(i => i !== id);
    _joAssistTechIds = _joAssistTechIds.filter(i => i !== id);
    if (role === 'assist') _joAssistTechIds.push(id);
    else _joMainTechIds.push(id);
}

function joOpenTechPicker(mode) { /* kept for legacy calls but not used in new UI */ }
function joPickTechnician(id) {}
function joRemoveMainTech(id) {}
function joRemoveAssistTech(id) {}
function joSyncCheckboxes() {
    const all = [..._joMainTechIds, ..._joAssistTechIds];
    const stMap = window._joTechStatusMap || {};
    document.querySelectorAll('.jo-tech-check').forEach(cb => {
        const id = parseInt(cb.value, 10);
        cb.checked = all.includes(id);
        const roleEl = document.getElementById('jo_tech_role_' + id);
        if (roleEl) {
            roleEl.style.display = all.includes(id) ? '' : 'none';
            const sel = roleEl.querySelector('select');
            if (sel) sel.value = _joAssistTechIds.includes(id) ? 'assist' : 'main';
        }
        // Show/hide status badge on the row
        const row = document.getElementById('jo_tech_row_' + id);
        if (row) {
            // Remove old status badges
            row.querySelectorAll('.jo-hist-badge').forEach(b => b.remove());
            const st = stMap[id];
            if (st === 'removed' || st === 'on_hold' || st === 'completed') {
                const b = document.createElement('span');
                b.className = 'badge bg-dark ms-1 jo-hist-badge';
                b.style.fontSize = '9px';
                b.textContent = 'Inactive';
                const label = row.querySelector('label');
                if (label) label.after(b);
            }
        }
    });
    joUpdateTechnicianIndicator();
}
function joRenderTechDisplays() { /* handled by joSyncCheckboxes */ }

// Load technician availability badges
function joLoadTechnicianAvailability() {
    fetch(`${APP_URL}/api/job_orders.php?action=technician_availability`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const busyIds = res.data.busy_technician_ids || [];
            document.querySelectorAll('.jo-tech-avail-badge').forEach(badge => {
                const techId = parseInt(badge.id.replace('avail_', ''), 10);
                const busy = busyIds.includes(techId);
                badge.textContent = busy ? 'Busy' : 'Available';
                badge.className = `badge jo-tech-avail-badge ${busy ? 'bg-warning text-dark' : 'bg-success'}`;
            });
            document.querySelectorAll('.ct-avail-badge').forEach(badge => {
                const techId = parseInt(badge.id.replace('ct_avail_', ''), 10);
                const busy = busyIds.includes(techId);
                badge.textContent = busy ? 'Busy' : 'Available';
                badge.className = `badge ct-avail-badge ${busy ? 'bg-warning text-dark' : 'bg-success'}`;
            });
        })
        .catch(() => {});
}
let _viewingJoId = null;
let _viewingJoData = null;

function openChangeTechModal() {
    if (!_viewingJoData) return;
    // Pre-check currently active (non-removed) technicians
    const activeTechIds = (_viewingJoData.technicians || [])
        .filter(t => t.assignment_status !== 'removed')
        .map(t => parseInt(t.id, 10));
    document.querySelectorAll('.change-tech-check').forEach(cb => {
        cb.checked = activeTechIds.includes(parseInt(cb.value, 10));
    });
    joLoadTechnicianAvailability();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('changeTechModal')).show();
}

function updateTechnicianRemovalConfirmation(reason) {
    const label = reason === 'Transfer' ? 'Transfer' : 'Absent';
    const message = document.getElementById('technicianRemovalConfirmationText');
    const button = document.getElementById('technicianRemovalConfirmButton');
    if (message) message.textContent = `Confirm removing this technician as ${label}?`;
    if (button) button.textContent = `Confirm ${label} Removal`;
}

function confirmTechnicianRemoval() {
    const reason = document.getElementById('technicianRemovalReasonSelect')?.value || 'Absent';
    if (technicianRemovalSaveMode === 'edit-selection') {
        joPendingRemovalReason = reason;
        bootstrap.Modal.getInstance(document.getElementById('technicianRemovalReasonModal'))?.hide();
        return;
    }
    if (technicianRemovalSaveMode === 'edit') {
        joSave(reason);
    } else {
        saveChangeTech(reason);
    }
}

function cancelTechnicianRemoval() {
    if (technicianRemovalSaveMode === 'edit-selection' && joPendingRemovalReason === null) {
        const unchecked = document.querySelectorAll('.jo-tech-check:not(:checked)');
        unchecked.forEach(cb => {
            const wasActive = joEditingTechnicians.some(t => parseInt(t.id, 10) === parseInt(cb.value, 10) && t.assignment_status !== 'removed');
            if (wasActive) cb.checked = true;
        });
        joSyncCheckboxes();
    }
    technicianRemovalSaveMode = null;
}

function saveChangeTech(removalReasonOverride = null) {
    if (!_viewingJoId || !_viewingJoData) return;
    const selectedIds = Array.from(document.querySelectorAll('.change-tech-check:checked'))
        .map(cb => parseInt(cb.value, 10)).filter(id => id > 0);
    if (selectedIds.length === 0) { showToast('Please select at least one technician.'); return; }
    const activeTechIds = (_viewingJoData.technicians || [])
        .filter(t => t.assignment_status !== 'removed')
        .map(t => parseInt(t.id, 10));
    const hasRemovedTechnician = activeTechIds.some(id => !selectedIds.includes(id));
    if (hasRemovedTechnician && !removalReasonOverride) {
        technicianRemovalSaveMode = 'change';
        document.getElementById('technicianRemovalReasonSelect').value = 'Absent';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('technicianRemovalReasonModal')).show();
        return;
    }
    const removalReason = removalReasonOverride || 'Absent';

    const payload = {
        csrf_token: csrfToken,
        technician_ids: selectedIds,
        removal_reason: removalReason,
        // Pass through current values unchanged
        customer_name:    _viewingJoData.customer_name    || '',
        customer_phone:   _viewingJoData.customer_phone   || '',
        customer_email:   _viewingJoData.customer_email   || '',
        customer_address: _viewingJoData.customer_address || '',
        vehicle_make:     _viewingJoData.vehicle_make     || '',
        vehicle_model:    _viewingJoData.vehicle_model    || '',
        vehicle_year:     _viewingJoData.vehicle_year     || '',
        vehicle_license:  _viewingJoData.vehicle_license  || '',
        vehicle_color:    _viewingJoData.vehicle_color    || '',
        vehicle_mileage:  _viewingJoData.vehicle_mileage  || '',
        status:           _viewingJoData.status           || 'pending',
        payment_method:   _viewingJoData.payment_method   || 'cash',
        payment_status:   _viewingJoData.payment_status   || 'pending',
        partial_amount:   parseFloat(_viewingJoData.partial_amount) || 0,
        notes:            _viewingJoData.notes            || '',
        discount_type:    normalizeSavedDiscountType(_viewingJoData.discount_type, _viewingJoData.discount_percentage || 0),
        discount_value:   normalizeSavedDiscountType(_viewingJoData.discount_type, _viewingJoData.discount_percentage || 0) === 'percentage' ? (parseFloat(_viewingJoData.discount_percentage) || 0) : (parseFloat(_viewingJoData.discount_amount) || 0),
        expected_updated_at: _viewingJoData.updated_at || '',
    };

    fetch(`${APP_URL}/api/job_orders.php?id=${_viewingJoId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('changeTechModal')).hide();
            bootstrap.Modal.getInstance(document.getElementById('technicianRemovalReasonModal'))?.hide();
            // Reload the view modal
            viewJobOrder(_viewingJoId);
        } else {
            showToast('Error: ' + data.message);
        }
    })
    .catch(() => showToast('Network error.'));
}

function fmtSeconds(sec) {
    const s = parseInt(sec, 10) || 0;
    const h = String(Math.floor(s / 3600)).padStart(2, '0');
    const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
    const ss = String(s % 60).padStart(2, '0');
    return `${h}:${m}:${ss}`;
}

function joSave(removalReasonOverride = null) {
    const name  = document.getElementById('jo_customer_name').value.trim();
    const phone = document.getElementById('jo_customer_phone').value.trim();
    if (!name || !phone) { showToast('Customer name and phone are required.', 'error'); return; }
    if (!/^09\d{9}$/.test(phone)) {
        showToast('Phone number must contain exactly 11 digits and start with 09.', 'error');
        return;
    }
    if (joItems.length === 0 && joProducts.length === 0) {
        showToast('Please add at least one service, package, or product.');
        return;
    }
    removalReasonOverride = removalReasonOverride || joPendingRemovalReason;
    const selectedTechnicianIds = getSelectedTechnicianIds();
    const activeEditingTechnicianIds = (joEditingTechnicians || [])
        .filter(t => t.assignment_status !== 'removed')
        .map(t => parseInt(t.id, 10));
    const hasRemovedEditingTechnician = !!joEditingId
        && activeEditingTechnicianIds.some(id => !selectedTechnicianIds.includes(id));
    if (hasRemovedEditingTechnician && !removalReasonOverride) {
        technicianRemovalSaveMode = 'edit';
        document.getElementById('technicianRemovalReasonSelect').value = 'Absent';
        updateTechnicianRemovalConfirmation('Absent');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('technicianRemovalReasonModal')).show();
        return;
    }

    const payload = {
        csrf_token:       csrfToken,
        customer_name:    name,
        customer_phone:   phone,
        customer_email:   document.getElementById('jo_customer_email').value.trim(),
        customer_address: document.getElementById('jo_customer_address').value.trim(),
        vehicle_make:     document.getElementById('jo_vehicle_make').value.trim(),
        vehicle_model:    document.getElementById('jo_vehicle_model').value.trim(),
        vehicle_year:     document.getElementById('jo_vehicle_year').value.trim(),
        vehicle_license:  document.getElementById('jo_vehicle_plate').value.trim(),
        vehicle_color:    document.getElementById('jo_vehicle_color').value.trim(),
        vehicle_mileage:  document.getElementById('jo_vehicle_mileage').value.trim(),
        status:           joEditingId ? (joEditingStatus || 'pending') : 'pending',
        technician_ids:   selectedTechnicianIds,
        removal_reason:   removalReasonOverride || 'Absent',
        assist_ids:       _joAssistTechIds.filter(id => id > 0),
        discount_type:    document.getElementById('jo_discount_type').value,
        discount_value:   document.getElementById('jo_discount_value').value,
        parts_cost:       joProducts.reduce((s, p) => p.fromBundle ? s : s + p.price * p.qty, 0),
        payment_method:   document.getElementById('jo_payment_method').value,
        payment_status:   joPaymentStatusLocked ? 'paid' : document.getElementById('jo_payment_status').value,
        expected_updated_at: joEditingId ? (joEditingVersion || '') : '',
        source_estimate_id: !joEditingId && jeSourceEstimateId ? parseInt(jeSourceEstimateId, 10) : 0,
        partial_amount:   parseFloat(document.getElementById('jo_partial_amount').value) || 0,
        notes:            (() => {
            const notes = document.getElementById('jo_notes').value.trim();
            const recs  = joRecommendations.length > 0
                ? '---RECOMMENDATIONS---\n' + joRecommendations.map(r => {
                    const sub = r.subItems && r.subItems.length > 0
                        ? '\n' + r.subItems.map(s => '  - ' + s).join('\n')
                        : '';
                    return '- ' + r.name + sub;
                }).join('\n')
                : '';
            return notes && recs ? notes + '\n' + recs : (notes || recs);
        })(),
        items:            joItems.map(item => ({
            type: item.type,
            id: item.id,
            name: item.name,
            base_price: item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice) : 0,
            labor_cost: parseFloat(item.labor || 0),
            price: (item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice) : 0) + parseFloat(item.labor || 0),
            qty: parseInt(item.qty || 1),
            selectedSubItems: Array.isArray(item.selectedSubItems) ? item.selectedSubItems : []
        })),
        products:         joProducts,
        inline_payments:  joInlinePayments.filter(p => p.amount > 0)
    };

    const isEditMode = !!joEditingId;

    const url = isEditMode
        ? `${APP_URL}/api/job_orders.php?id=${joEditingId}`
        : `${APP_URL}/api/job_orders.php`;

    fetch(url, {
        method: isEditMode ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(isEditMode
                ? 'Job order updated successfully.'
                : ('Job order created: ' + data.data.job_order_number));
            bootstrap.Modal.getInstance(document.getElementById('createJobOrderModal')).hide();
            joReset();
            location.reload();
            joPendingRemovalReason = null;
        } else {
            showToast('Error: ' + data.message);
        }
    })
    .catch(() => showToast('Network error. Please try again.'));
}

// ── Recommendations ──────────────────────────────────────────────────────────
let joRecommendations = []; // [{name, subItems:[]}]

function joAddRecommendation() {
    const input = document.getElementById('jo_rec_input');
    const val = (input.value || '').trim();
    if (!val) return;
    joRecommendations.push({ name: val, subItems: [] });
    input.value = '';
    joRenderRecommendations();
}

function joRemoveRecommendation(idx) {
    joRecommendations.splice(idx, 1);
    joRenderRecommendations();
}

function joRenderRecommendations() {
    const list = document.getElementById('jo_rec_list');
    if (!list) return;
    if (joRecommendations.length === 0) {
        list.innerHTML = '<p class="text-muted small text-center mb-0 py-1" id="jo_rec_empty">No recommendations yet.</p>';
        return;
    }
    list.innerHTML = joRecommendations.map((r, i) => {
        const subHtml = r.subItems && r.subItems.length > 0
            ? `<div style="padding-left:10px;">${r.subItems.map(s=>`<div style="font-size:10px;color:#888;">- ${s}</div>`).join('')}</div>`
            : '';
        return `
        <div class="px-1 py-1" style="border-bottom:1px solid #f0f0f0;">
            <div class="d-flex align-items-center justify-content-between" style="font-size:12px;">
                <span style="font-weight:600;">${r.name}</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" onclick="joRemoveRecommendation(${i})" style="font-size:11px;"><i class="bi bi-x"></i></button>
            </div>
            ${subHtml}
        </div>`;
    }).join('');
}

function joParseNotesAndRecommendations(rawNotes) {
    if (!rawNotes) return { notes: '', recommendations: [] };
    const sep = '---RECOMMENDATIONS---';
    const idx = rawNotes.indexOf(sep);
    if (idx === -1) return { notes: rawNotes.trim(), recommendations: [] };
    const notesPart = rawNotes.substring(0, idx).trim();
    const recPart   = rawNotes.substring(idx + sep.length).trim();
    // Parse lines: "- Name" starts an item, "  - SubItem" is indented sub-item
    const recs = [];
    recPart.split('\n').forEach(line => {
        const trimmed = line.trimStart();
        if (trimmed.startsWith('- ') && !line.startsWith('  ')) {
            recs.push({ name: trimmed.substring(2).trim(), subItems: [] });
        } else if ((trimmed.startsWith('- ') || trimmed.startsWith('* ')) && recs.length > 0) {
            recs[recs.length - 1].subItems.push(trimmed.substring(2).trim());
        }
    });
    return { notes: notesPart, recommendations: recs };
}

function syncModalStacking(topModalId = null) {
    const visibleModals = [...document.querySelectorAll('.modal.show')];
    const sorted = visibleModals.sort((a, b) => {
        const aZ = Number.parseInt(getComputedStyle(a).zIndex || '0', 10) || 0;
        const bZ = Number.parseInt(getComputedStyle(b).zIndex || '0', 10) || 0;
        return aZ - bZ;
    });

    sorted.forEach((modal, index) => {
        const base = 1060 + (index * 20);
        modal.style.zIndex = String(base);
    });

    if (topModalId) {
        const topModal = document.getElementById(topModalId);
        if (topModal) {
            topModal.style.zIndex = '1085';
        }
    }

    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach((el, index) => {
        el.style.zIndex = String(1050 + (index * 20));
    });
}

function bindJoSubItemConfirmDefault() {
    const confirmBtn = document.querySelector('#joSubItemSelectModal .btn-dark');
    if (confirmBtn) {
        confirmBtn.onclick = confirmJoSubItemSelect;
    }
}

function openRecBrowseModal() {
    const recBrowseModal = document.getElementById('recBrowseModal');
    if (!recBrowseModal) return;

    bootstrap.Modal.getOrCreateInstance(recBrowseModal).show();
    setTimeout(() => syncModalStacking('recBrowseModal'), 20);

    const searchInput = document.getElementById('rec_browse_search');
    if (searchInput && !searchInput._recBrowseWired) {
        searchInput._recBrowseWired = true;
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.rec-browse-row').forEach(row => {
                row.style.display = (!q || (row.dataset.searchText || '').includes(q)) ? '' : 'none';
            });
        });
    }
    if (searchInput) {
        setTimeout(() => searchInput.focus(), 150);
    }
}

let _pendingRecItem = null;

function joAddRecommendationFromBrowse(name, type, id) {
    if (!name) return;
    // For services with sub-items, show the sub-item modal
    if (type === 'service') {
        const svcId = parseInt(id, 10);
        const subItems = serviceSubItemsMap[svcId] || [];
        if (subItems.length > 0) {
            _pendingRecItem = { name, subItems };
            // Hide browse modal first so sub-item modal is visible on top
            const browseModal = bootstrap.Modal.getInstance(document.getElementById('recBrowseModal'));
            if (browseModal) browseModal.hide();

            const launchSubItemModal = () => {
                const titleEl = document.getElementById('joSubItemSelectTitle');
                if (titleEl) titleEl.textContent = name;
                const listEl = document.getElementById('joSubItemSelectList');
                if (listEl) {
                    listEl.innerHTML = subItems.map((item, i) => `
                        <div class="form-check mb-1">
                            <input class="form-check-input jo-subitem-check" type="checkbox" checked id="joSubItemCb_${i}">
                            <label class="form-check-label small" for="joSubItemCb_${i}">${item}</label>
                        </div>`).join('');
                }
                const confirmBtn = document.querySelector('#joSubItemSelectModal .btn-dark');
                if (confirmBtn) {
                    confirmBtn.onclick = function() {
                        if (_pendingRecItem) {
                            const allSubs = _pendingRecItem.subItems;
                            const selected = allSubs.filter((_, i) => {
                                const cb = document.getElementById(`joSubItemCb_${i}`);
                                return cb && cb.checked;
                            });
                            joRecommendations.push({ name: _pendingRecItem.name, subItems: selected });
                            joRenderRecommendations();
                            _pendingRecItem = null;
                        }
                        bootstrap.Modal.getInstance(document.getElementById('joSubItemSelectModal')).hide();
                        bindJoSubItemConfirmDefault();
                        setTimeout(() => {
                            syncModalStacking('recBrowseModal');
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('recBrowseModal')).show();
                        }, 100);
                    };
                }
                syncModalStacking('joSubItemSelectModal');
                bootstrap.Modal.getOrCreateInstance(document.getElementById('joSubItemSelectModal')).show();
            };

            // Wait for browse modal to finish hiding before showing sub-item modal
            const recBrowseEl = document.getElementById('recBrowseModal');
            recBrowseEl.addEventListener('hidden.bs.modal', launchSubItemModal, { once: true });
            return;
        }
    }
    // For bundles, auto-include their services
    if (type === 'bundle') {
        const bundleId = parseInt(id, 10);
        const bundleSvcs = bundleServiceNamesMap[bundleId] || [];
        joRecommendations.push({ name, subItems: bundleSvcs });
        joRenderRecommendations();
        return;
    }
    joRecommendations.push({ name, subItems: [] });
    joRenderRecommendations();
}

function joReset() {
    joEditingId = null;
    jeSourceEstimateId = null;
    joEditingTechnicians = [];
    joPendingRemovalReason = null;
    joEditingStatus = 'pending';
    joPaymentStatusLocked = false;
    joEditingVersion = null;
    joEditingJobNumber = null;
    _joEditingCustomItemIdx = null;
    _joEditingCustomProductIdx = null;
    joItems = [];
    joProducts = [];
    joRecommendations = [];
    joInlinePayments = [];
    joInlinePaymentActionsEnabled = true;
    joPaymentsListTargetId = 'joPaymentsList';
    _joMainTechIds = [];
    _joAssistTechIds = [];
    window._joTechStatusMap = {};

    // Unlock services/products cards
    const leftCol = document.querySelector('#createJobOrderModal .col-lg-7');
    if (leftCol) {
        leftCol.querySelectorAll('.card').forEach(card => {
            card.style.pointerEvents = '';
            card.style.opacity = '';
        });
    }

    joRenderItems();
    joRenderProducts();
    joRenderRecommendations();
    joSyncCheckboxes();
    joUpdateTechnicianIndicator();
    const techHistory = document.getElementById('joTechHistory');
    if (techHistory) { techHistory.innerHTML = ''; techHistory.style.display = 'none'; }
    joCalc();
    document.getElementById('joForm').reset();
    const partialRow = document.getElementById('joPartialRow');
    if (partialRow) partialRow.style.display = 'none';
    const payStatusSelect = document.getElementById('jo_payment_status');
    if (payStatusSelect) {
        payStatusSelect.disabled = false;
        payStatusSelect.title = '';
    }

    const joServiceSearch = document.getElementById('jo_service_search');
    const joBundleSearch = document.getElementById('jo_bundle_search');
    const joProductSearch = document.getElementById('jo_product_search');
    const joTechSearch = document.getElementById('jo_tech_search');
    if (joServiceSearch) joServiceSearch.value = '';
    if (joBundleSearch) joBundleSearch.value = '';
    if (joProductSearch) joProductSearch.value = '';
    if (joTechSearch) joTechSearch.value = '';

    // Reset search — show all rows
    joSearchFilter('', 'jo-service-row');
    joSearchFilter('', 'jo-bundle-row');
    joSearchFilter('', 'jo-product-row');
    _joTechFilterMode = 'all';
    joTechSetFilter('all');
    joRenderInlinePayments();
    joUpdatePaymentBalance(0);

    joSetMode(false);
}

async function joPrintPreview() {
    await refreshPrintTemplateSettings();
    const name    = document.getElementById('jo_customer_name').value.trim() || '—';
    const phone   = document.getElementById('jo_customer_phone').value.trim() || '—';
    const email   = document.getElementById('jo_customer_email').value.trim() || '—';
    const address = document.getElementById('jo_customer_address').value.trim() || '—';
    const make    = document.getElementById('jo_vehicle_make').value.trim() || '—';
    const model   = document.getElementById('jo_vehicle_model').value.trim() || '—';
    const year    = document.getElementById('jo_vehicle_year').value.trim() || '—';
    const plate   = document.getElementById('jo_vehicle_plate').value.trim() || '—';
    const color   = document.getElementById('jo_vehicle_color').value.trim() || '—';
    const mileage = document.getElementById('jo_vehicle_mileage').value.trim() || '—';
    const techNames = Array.from(document.querySelectorAll('.jo-tech-check:checked'))
        .map((check) => (check.dataset.name || '').trim())
        .filter(Boolean);
    const techName = techNames.length ? techNames.join(', ') : 'Unassigned';
    const payMethod = document.getElementById('jo_payment_method').value;
    const payStatus = document.getElementById('jo_payment_status').value;
    const joNumber = joEditingJobNumber || 'JO###';
    const joDate   = new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });

    let subtotal = joItems.reduce((s, i) => s + (parseFloat(i.basePrice||0) * (i.qty||1)) + parseFloat(i.labor||0), 0);
    const partsTotal = joProducts.reduce((s, p) => s + p.price * p.qty, 0);
    const discType = document.getElementById('jo_discount_type').value;
    const discVal  = parseFloat(document.getElementById('jo_discount_value').value) || 0;
    const base = subtotal + partsTotal;
    let discountAmt = 0;
    if (discType === 'percentage') discountAmt = base * (discVal / 100);
    else if (discType === 'fixed')  discountAmt = discVal;
    else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;
    discountAmt = Math.min(discountAmt, base);
    const total = Math.max(0, base - discountAmt);

    const fmt = n => '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    let serviceRows = '';
    let svcRowNum = 1;
    joItems.forEach((item) => {
        const base  = parseFloat(item.basePrice || 0);
        const labor = parseFloat(item.labor || 0);
        const lineTotal = (base + labor) * (item.qty || 1);
        const isBundle = item.type === 'bundle';
        const subServices = isBundle
            ? (bundleServiceNamesMap[item.id] || [])
            : (item.selectedSubItems !== undefined ? item.selectedSubItems : (serviceSubItemsMap[item.id] || []));
        const nameCell = isBundle ? `<strong>${item.name}</strong>` : item.name;
        serviceRows += `
        <tr${isBundle ? ' style="background:#f8f8f8;"' : ''}>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${svcRowNum++}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${nameCell}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${item.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(base)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(labor)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(lineTotal)}</td>
        </tr>`;
        subServices.forEach(svcName => {
            serviceRows += `
        <tr>
            <td style="padding:2px 8px;border:1px solid #ccc;text-align:center;color:#888;">-</td>
            <td style="padding:2px 8px 2px 20px;border:1px solid #ccc;color:#555;font-size:8.5pt;word-break:break-word;">${svcName}</td>
            <td colspan="4" style="padding:2px 8px;border:1px solid #ccc;"></td>
        </tr>`;
        });
    });
    let productRows = '';
    joProducts.forEach((p, i) => {
        const isFromBundle = !!p.fromBundle;
        productRows += `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${p.name}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price * p.qty)}</td>
        </tr>`;
    });

    let discLabel = '';
    if (discType === 'senior') discLabel = 'Senior Citizen (20%)';
    else if (discType === 'pwd') discLabel = 'PWD (20%)';
    else if (discType === 'percentage') discLabel = `Percentage (${discVal}%)`;
    else if (discType === 'fixed') discLabel = 'Fixed Amount';

    document.getElementById('joPrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">

        ${getPrintHeaderHtml('JOB ORDER', joNumber, joDate)}

        <!-- Customer & Vehicle -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:6px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">CUSTOMER</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #ddd;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${name}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${phone}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Email</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${email}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${address}</td></tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:6px;border-left:1px solid #ddd;">
                    <table style="width:100%;border-collapse:collapse;padding-left:6px;">
                        <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">VEHICLE</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #ddd;">Make / Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${make} ${model}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Year</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${year}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${plate}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Color</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${color}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${mileage} km</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Services -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:4%"><col><col style="width:7%">
                <col style="width:13%"><col style="width:13%"><col style="width:14%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Labor</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${serviceRows || '<tr><td colspan="6" style="padding:8px;text-align:center;color:#999;">No services</td></tr>'}</tbody>
        </table>
        ${productRows ? `
        <!-- Products / Parts -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding:6px 0 3px;">PRODUCTS / PARTS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${productRows}</tbody>
        </table>` : ''}

        <!-- Summary — full-width clean rows -->
        <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
            ${discountAmt > 0 ? `<tr>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;">Discount (${discLabel})</td>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;text-align:right;">- ${fmt(discountAmt)}</td>
            </tr>` : ''}
            <tr>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(subtotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(partsTotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    TOTAL AMOUNT<br><strong style="font-size:11pt;color:#000;">${fmt(total)}</strong>
                </td>
            </tr>
        </table>

        <!-- Terms & Conditions -->
        <div style="margin-top:12px;font-size:8.5pt;border-top:1px solid #ddd;padding-top:6px;">
            <strong style="font-size:8.5pt;letter-spacing:.4px;">TERMS AND CONDITIONS:</strong>
            <div style="margin-top:3px;color:#333;white-space:pre-wrap;">${printTemplateSettings.terms_conditions || 'All services rendered are subject to warranty as per company policy.'}</div>
        </div>

        <!-- Signatures + Technician — pinned to bottom -->
        <div style="margin-top:20px;">
            ${joRecommendations.length > 0 ? `
            <div style="font-size:8.5pt;margin-bottom:8px;">
                <div style="font-weight:700;letter-spacing:.5px;padding-bottom:3px;font-size:8.5pt;">RECOMMENDATIONS</div>
                <table style="width:100%;border-collapse:collapse;font-size:8.5pt;">
                    <thead><tr style="background:#f0f0f0;">
                        <th style="padding:3px 6px;border:1px solid #ccc;width:5%;text-align:center;">#</th>
                        <th style="padding:3px 6px;border:1px solid #ccc;text-align:left;">Service / Item</th>
                    </tr></thead>
                    <tbody>${(() => {
                        let rows = '';
                        joRecommendations.forEach((r, i) => {
                            rows += `<tr><td style="padding:3px 6px;border:1px solid #ccc;text-align:center;vertical-align:top;">${i+1}</td><td style="padding:3px 6px;border:1px solid #ccc;font-weight:600;">${r.name||r}</td></tr>`;
                            if (r.subItems && r.subItems.length > 0) {
                                r.subItems.forEach(s => {
                                    rows += `<tr><td style="padding:2px 6px;border:1px solid #ddd;text-align:center;color:#aaa;">-</td><td style="padding:2px 6px 2px 20px;border:1px solid #ddd;color:#555;font-size:8pt;">${s}</td></tr>`;
                                });
                            }
                        });
                        return rows;
                    })()}</tbody>
                </table>
            </div>` : ''}
            <div style="font-size:9pt;margin-bottom:10px;padding-top:6px;">
                <strong>Assigned Technician(s):</strong> ${techName}
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <tr>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Authorized Signature</div>
                    </td>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Customer Signature</div>
                    </td>
                </tr>
            </table>
            ${getPrintFooterHtml()}
        </div>
    </div>`;

    document.getElementById('joPrintArea').style.display = 'block';
    await waitForPrintAssets('joPrintArea');
    const pdfPromise = printWithPdfName(document.getElementById('jo_vehicle_plate').value, document.getElementById('jo_customer_name').value);
    if (!_pdfMode) document.getElementById('joPrintArea').style.display = 'none';
    return pdfPromise;
}

function jeSave() {
    const payload = buildEstimatePayload();

    if (payload.customer_phone !== '' && !/^09\d{9}$/.test(payload.customer_phone)) {
        showToast('Phone number must contain exactly 11 digits and start with 09.', 'error');
        return;
    }

    if (payload.services.length === 0 && payload.products.length === 0) {
        showToast('Please select at least one service, package, or product before saving.');
        return;
    }

    savedEstimate = { ...payload };

    // If editing an existing estimate, use PUT
    if (_editEstimateId) {
        saveEditEstimate();
        return;
    }

    saveEstimateRecord(payload)
    .then(data => {
        if (data.success) {
            showToast('Estimate saved: ' + data.data.estimate_number);
            bootstrap.Modal.getInstance(document.getElementById('jobEstimateModal')).hide();
            location.reload();
        } else {
            showToast('Error: ' + data.message);
        }
    })
    .catch(() => showToast('Network error. Please try again.'));
}

function jeConvertToJo() {
    const payload = buildEstimatePayload();

    if (!/^09\d{9}$/.test(payload.customer_phone)) {
        showToast('Cannot convert: enter a complete 11-digit phone number starting with 09.', 'error');
        return;
    }

    if (payload.services.length === 0 && payload.products.length === 0) {
        showToast('Please select at least one service, package, or product before converting.');
        return;
    }

    // Save first so converted JO can always be traced to a saved estimate.
    saveEstimateRecord(payload)
    .then(data => {
        if (!data.success) {
            showToast('Error: ' + data.message);
            return;
        }

        jeSourceEstimateId = data.data.id || null;
        showToast('Estimate saved: ' + data.data.estimate_number + '. Validating stock before conversion...');

        // Build lightweight payload for validation
        const productsToCheck = (payload.products || []).map(p => ({ id: p.id || 0, qty: parseInt(p.qty || 1, 10) || 1 }));

        fetch(APP_URL + '/api/job_orders.php?action=validate_products', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrfToken, products: productsToCheck })
        })
        .then(r => r.json())
        .then(vres => {
            if (!vres || !vres.success) {
                showToast('Stock validation failed. Please try again.');
                return;
            }

            const results = (vres.data && Array.isArray(vres.data.result)) ? vres.data.result : [];
            const insufficient = results.filter(it => !it.ok);
            if (insufficient.length > 0) {
                const names = insufficient.map(it => (it.name ? it.name : 'product #' + it.id) + ' (available: ' + (it.stock ?? 0) + ')');
                showToast('Cannot convert: insufficient stock for ' + names.join(', '));
                return;
            }

            // All products have sufficient stock — proceed with mapping and transfer
            joItems = payload.services.map(normalizeEstimateItemToJo);
            joProducts = payload.products.map(product => ({
                id: parseInt(product.id ?? 0, 10) || 0,
                name: product.name || '',
                code: product.code || '',
                price: parseFloat(product.price || 0) || 0,
                qty: parseInt(product.qty || 1, 10) || 1,
                fromBundle: product.fromBundle || null,
                // Attach stock info from validation results where available
                stock: (results.find(r => r.id === (product.id || 0)) || {}).stock ?? null
            }));

            // Transfer customer info
            document.getElementById('jo_customer_name').value = payload.customer_name || '';
            document.getElementById('jo_customer_phone').value = payload.customer_phone || '';
            document.getElementById('jo_customer_email').value = payload.customer_email || '';
            document.getElementById('jo_customer_address').value = payload.customer_address || '';

            document.getElementById('jo_vehicle_make').value = payload.vehicle_make;
            document.getElementById('jo_vehicle_model').value = payload.vehicle_model;
            document.getElementById('jo_vehicle_year').value = payload.vehicle_year;
            document.getElementById('jo_vehicle_plate').value = payload.vehicle_plate;
            document.getElementById('jo_vehicle_color').value = payload.vehicle_color;
            document.getElementById('jo_vehicle_mileage').value = payload.vehicle_mileage;

            joRenderItems();
            joRenderProducts();
            joCalc();

            const estimateModal = bootstrap.Modal.getInstance(document.getElementById('jobEstimateModal'));
            if (estimateModal) {
                estimateModal.hide();
            }
            bootstrap.Modal.getOrCreateInstance(document.getElementById('createJobOrderModal')).show();
        })
        .catch(() => showToast('Network error. Please try again.'));
    })
    .catch(() => showToast('Network error. Please try again.'));
}


function convertEstimateById(id) {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewEstimateModal')).hide();
    fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast(res.message); return; }
            convertEstimateToJo(res.data);
        })
        .catch(() => showToast('Failed to load estimate.'));
}

async function jePrintPreview() {
    await refreshPrintTemplateSettings();
    const custName = document.getElementById('je_customer_name').value.trim() || '—';
    const custPhone = document.getElementById('je_customer_phone').value.trim() || '—';
    const custEmail = document.getElementById('je_customer_email').value.trim() || '—';
    const custAddress = document.getElementById('je_customer_address').value.trim() || '—';
    const make    = document.getElementById('je_vehicle_make').value.trim() || '—';
    const model   = document.getElementById('je_vehicle_model').value.trim() || '—';
    const year    = document.getElementById('je_vehicle_year').value.trim() || '—';
    const plate   = document.getElementById('je_vehicle_plate').value.trim() || '—';
    const color   = document.getElementById('je_vehicle_color').value.trim() || '—';
    const mileage = document.getElementById('je_vehicle_mileage').value.trim() || '—';

    const jeNumber = 'JE###';
    const jeDate = new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });

    let subtotal = jeItems.reduce((s, i) => s + (i.price * (i.qty || 1)), 0);
    const partsTotal = jeProducts.reduce((s, p) => s + p.price * p.qty, 0);
    const discType = document.getElementById('je_discount_type').value;
    const discVal = parseFloat(document.getElementById('je_discount_value').value) || 0;
    const base = subtotal + partsTotal;
    let discountAmt = 0;
    if (discType === 'percentage') discountAmt = base * (discVal / 100);
    else if (discType === 'fixed') discountAmt = discVal;
    else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;
    discountAmt = Math.min(discountAmt, base);
    const total = Math.max(0, base - discountAmt);

    const fmt = n => '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    let serviceRows = '';
    let svcRowNum = 1;
    jeItems.forEach((item) => {
        const baseP = parseFloat(item.basePrice || 0);
        const labor = parseFloat(item.labor || 0);
        const lineTotal = (baseP + labor) * (item.qty || 1);
        const isBundle = item.type === 'bundle';
        const subServices = isBundle
            ? (bundleServiceNamesMap[item.id] || [])
            : (item.selectedSubItems !== undefined ? item.selectedSubItems : (serviceSubItemsMap[item.id] || []));
        const nameCell = isBundle ? `<strong>${item.name}</strong>` : item.name;
        serviceRows += `
        <tr${isBundle ? ' style="background:#f8f8f8;"' : ''}>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${svcRowNum++}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${nameCell}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${item.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(baseP)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(labor)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(lineTotal)}</td>
        </tr>`;
        subServices.forEach(svcName => {
            serviceRows += `
        <tr>
            <td style="padding:2px 8px;border:1px solid #ccc;text-align:center;color:#888;">-</td>
            <td style="padding:2px 8px 2px 20px;border:1px solid #ccc;color:#555;font-size:8.5pt;word-break:break-word;">${svcName}</td>
            <td colspan="4" style="padding:2px 8px;border:1px solid #ccc;"></td>
        </tr>`;
        });
    });
    let productRows = '';
    jeProducts.forEach((p, i) => {
        const isFromBundle = !!p.fromBundle;
        productRows += `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${p.name}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price * p.qty)}</td>
        </tr>`;
    });

    let discLabel = '';
    if (discType === 'senior') discLabel = 'Senior Citizen (20%)';
    else if (discType === 'pwd') discLabel = 'PWD (20%)';
    else if (discType === 'percentage') discLabel = `Percentage (${discVal}%)`;
    else if (discType === 'fixed') discLabel = 'Fixed Amount';

    document.getElementById('jePrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">

        ${getPrintHeaderHtml('JOB ESTIMATE', jeNumber, jeDate)}

        <!-- Customer & Vehicle -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:6px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">CUSTOMER</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #ddd;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${custName}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${custPhone}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Email</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${custEmail}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${custAddress}</td></tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:6px;border-left:1px solid #ddd;">
                    <table style="width:100%;border-collapse:collapse;padding-left:6px;">
                        <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">VEHICLE</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #ddd;">Make / Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${make} ${model}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Year</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${year}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${plate}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Color</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${color}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${mileage} km</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Services -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:4%"><col><col style="width:7%">
                <col style="width:13%"><col style="width:13%"><col style="width:14%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Labor</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${serviceRows || '<tr><td colspan="6" style="padding:8px;text-align:center;color:#999;">No services</td></tr>'}</tbody>
        </table>
        ${productRows ? `
        <!-- Products / Parts -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding:6px 0 3px;">PRODUCTS / PARTS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${productRows}</tbody>
        </table>` : ''}

        <!-- Summary -->
        <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
            ${discountAmt > 0 ? `<tr>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;">Discount (${discLabel})</td>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;text-align:right;">- ${fmt(discountAmt)}</td>
            </tr>` : ''}
            <tr>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(subtotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(partsTotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    TOTAL AMOUNT<br><strong style="font-size:11pt;color:#000;">${fmt(total)}</strong>
                </td>
            </tr>
        </table>

        <!-- Terms & Conditions -->
        <div style="margin-top:12px;font-size:8.5pt;border-top:1px solid #ddd;padding-top:6px;">
            <strong style="font-size:8.5pt;letter-spacing:.4px;">TERMS AND CONDITIONS:</strong>
            <div style="margin-top:3px;color:#333;white-space:pre-wrap;">${printTemplateSettings.terms_conditions || 'All services rendered are subject to warranty as per company policy.'}</div>
        </div>

        <!-- Recommendations -->
        ${jeRecommendations.length > 0 ? `
        <div style="margin-top:12px;font-size:8.5pt;">
            <div style="font-weight:700;letter-spacing:.5px;padding-bottom:3px;font-size:8.5pt;">RECOMMENDATIONS</div>
            <table style="width:100%;border-collapse:collapse;font-size:8.5pt;">
                <thead><tr style="background:#f0f0f0;">
                    <th style="padding:3px 6px;border:1px solid #ccc;width:5%;text-align:center;">#</th>
                    <th style="padding:3px 6px;border:1px solid #ccc;text-align:left;">Service / Item</th>
                </tr></thead>
                <tbody>${(() => {
                    let rows = '';
                    jeRecommendations.forEach((r, i) => {
                        rows += '<tr><td style="padding:3px 6px;border:1px solid #ccc;text-align:center;vertical-align:top;">' + (i+1) + '</td><td style="padding:3px 6px;border:1px solid #ccc;font-weight:600;">' + (r.name||r) + '</td></tr>';
                        if (r.subItems && r.subItems.length > 0) {
                            r.subItems.forEach(s => {
                                rows += '<tr><td style="padding:2px 6px;border:1px solid #ddd;text-align:center;color:#aaa;">-</td><td style="padding:2px 6px 2px 20px;border:1px solid #ddd;color:#555;font-size:8pt;">' + s + '</td></tr>';
                            });
                        }
                    });
                    return rows;
                })()}</tbody>
            </table>
        </div>` : ''}

        <!-- Signatures -->
        <div style="margin-top:20px;">
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <tr>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Authorized Signature</div>
                    </td>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Customer Signature</div>
                    </td>
                </tr>
            </table>
            ${getPrintFooterHtml()}
        </div>
    </div>`;

    document.getElementById('jePrintArea').style.display = 'block';
    await waitForPrintAssets('jePrintArea');
    const pdfPromise = printWithPdfName(document.getElementById('je_vehicle_plate').value, document.getElementById('je_customer_name').value);
    if (!_pdfMode) document.getElementById('jePrintArea').style.display = 'none';
    return pdfPromise;
}

// ── Save as PDF (auto-download using html2pdf) ──

async function joSavePDF() {
    _pdfMode = true;
    await joPrintPreview();
    _pdfMode = false;
}

async function jeSavePDF() {
    _pdfMode = true;
    await jePrintPreview();
    _pdfMode = false;
}

// Generic PDF download from a print area
function downloadPdfFromArea(areaId, contentId, plate, customerName) {
    const content = document.getElementById(contentId);
    if (!content || !content.innerHTML.trim()) return;

    const cleanPlate = (plate || '').replace(/[^a-zA-Z0-9-]/g, '').toUpperCase() || 'NO-PLATE';
    let fileName = cleanPlate;
    if (customerName && customerName.trim() && customerName.trim() !== '—') {
        const parts = customerName.trim().split(/\s+/);
        const formatted = parts.map(p => p.charAt(0).toUpperCase() + p.slice(1).toLowerCase()).join('');
        fileName = cleanPlate + '_' + formatted;
    }

    const opt = {
        margin: [10, 10, 10, 10],
        filename: fileName + '.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'mm', format: [215.9, 330.2], orientation: 'portrait' },
        pagebreak: { mode: ['css', 'legacy'] }
    };

    html2pdf().set(opt).from(content).save();
}

// Reset modal on close
document.getElementById('createJobOrderModal').addEventListener('hidden.bs.modal', joReset);
document.getElementById('createJobOrderModal').addEventListener('show.bs.modal', joLoadTechnicianAvailability);

// Estimate calculator
let estProducts = []; // { id, name, price, qty }
let estItemQuantities = {}; // { "type-id": qty }
let savedEstimate = null;
let jeSourceEstimateId = null;

// ── New JE (Job Estimate) modal state & functions (mirrors JO pattern) ──
let jeItems = [];    // { type, id, name, basePrice, labor, price, qty, selectedSubItems }
let jeProducts = []; // { id, name, code, price, qty }
let jeRecommendations = []; // [{name, subItems:[]}]

function jeAddItem(type, id, name, basePrice, laborCost = 0) {
    const existing = jeItems.find(i => i.type === type && i.id === id);
    if (existing) {
        existing.qty++;
        existing.labor = parseFloat(laborCost || existing.labor || 0);
        existing.price = (existing.basePrice !== undefined && existing.basePrice !== null ? parseFloat(existing.basePrice) : 0) + existing.labor;
        notifySelectedServiceAdded(name, type);
        jeRenderItems();
        jeCalc();
        return;
    }
    const subItems = (type === 'service') ? (serviceSubItemsMap[id] || []) : [];
    if (subItems.length > 0) {
        _pendingJeItem = { type, id, name, basePrice: parseFloat(basePrice || 0), laborCost: parseFloat(laborCost || 0) };
        const titleEl = document.getElementById('joSubItemSelectTitle');
        if (titleEl) titleEl.textContent = name;
        const listEl = document.getElementById('joSubItemSelectList');
        if (listEl) {
            listEl.innerHTML = subItems.map((item, i) => `
                <div class="form-check mb-1">
                    <input class="form-check-input jo-subitem-check" type="checkbox" checked id="joSubItemCb_${i}">
                    <label class="form-check-label small" for="joSubItemCb_${i}">${item}</label>
                </div>`).join('');
        }
        // Override confirm button for JE context
        const confirmBtn = document.querySelector('#joSubItemSelectModal .btn-dark');
        if (confirmBtn) {
            confirmBtn.onclick = function() {
                if (!_pendingJeItem) return;
                const allSubItems = serviceSubItemsMap[_pendingJeItem.id] || [];
                const selected = allSubItems.filter((_, i) => {
                    const cb = document.getElementById(`joSubItemCb_${i}`);
                    return cb && cb.checked;
                });
                const effectivePrice = _pendingJeItem.basePrice + _pendingJeItem.laborCost;
                jeItems.push({ type: _pendingJeItem.type, id: _pendingJeItem.id, name: _pendingJeItem.name, basePrice: _pendingJeItem.basePrice, labor: _pendingJeItem.laborCost, price: effectivePrice, qty: 1, selectedSubItems: selected });
                notifySelectedServiceAdded(_pendingJeItem.name, _pendingJeItem.type);
                jeRenderItems();
                jeCalc();
                bootstrap.Modal.getInstance(document.getElementById('joSubItemSelectModal')).hide();
                _pendingJeItem = null;
                // Restore original confirm for JO
                confirmBtn.onclick = confirmJoSubItemSelect;
            };
        }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('joSubItemSelectModal')).show();
        return;
    }
    const effectivePrice = parseFloat(basePrice || 0) + parseFloat(laborCost || 0);
    jeItems.push({ type, id, name, basePrice: parseFloat(basePrice || 0), labor: parseFloat(laborCost || 0), price: effectivePrice, qty: 1, selectedSubItems: [] });
    notifySelectedServiceAdded(name, type);

    // Auto-add bundle products to jeProducts
    if (type === 'bundle') {
        const bundleProds = bundleProductsMap[id] || [];
        bundleProds.forEach(p => {
            if (!p.id) return;
            const existing = jeProducts.find(ep => ep.id === p.id && ep.fromBundle);
            if (existing) {
                existing.qty += p.qty;
            } else {
                jeProducts.push({ id: p.id, name: p.name, code: p.code || '', price: 0, qty: p.qty, fromBundle: id });
            }
        });
    }

    jeRenderItems();
    jeCalc();
}
let _pendingJeItem = null;

function jeRemoveItem(idx) {
    if (_jeEditingCustomItemIdx === idx) {
        jeClearCustomItemEditState();
    }
    jeItems.splice(idx, 1);
    jeRenderItems();
    jeCalc();
}

function jeChangeBasePrice(idx, val) {
    const basePrice = parseFloat(val) || 0;
    jeItems[idx].basePrice = basePrice;
    jeItems[idx].price = basePrice + (jeItems[idx].labor || 0);
    jeRenderItems();
    jeCalc();
}

function jeChangeLabor(idx, val) {
    const labor = parseFloat(val) || 0;
    jeItems[idx].labor = labor;
    jeItems[idx].price = (jeItems[idx].basePrice || 0) + labor;
    jeRenderItems();
    jeCalc();
}

function jeChangeQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) { jeRemoveItem(idx); return; }
    jeItems[idx].qty = qty;
    jeRenderItems();
    jeCalc();
}

function jeAddProduct(id, name, price, code = '', stock = 0, qtyInputId = null) {
    const qtyInput = qtyInputId ? document.getElementById(qtyInputId) : null;
    const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10) || 1);
    const parsedId = parseInt(id, 10);
    const parsedPrice = parseFloat(price || 0) || 0;
    const availableStock = parseInt(stock || 0, 10) || 0;
    if (!parsedId || !name) return;

    // Prevent adding if out of stock
    if (availableStock <= 0) {
        showToast('Product "' + name + '" is out of stock.');
        if (qtyInput) qtyInput.value = 1;
        return;
    }

    // Only merge with existing non-bundle product of same ID
    const existing = jeProducts.find(p => p.id === parsedId && !p.fromBundle);
    if (existing) {
        const newQty = (existing.qty || 0) + qty;
        if (newQty > availableStock) {
            showToast('Cannot add. Only ' + availableStock + ' of "' + name + '" available.');
            if (qtyInput) qtyInput.value = 1;
            return;
        }
        existing.qty = newQty;
    } else {
        if (qty > availableStock) {
            showToast('Cannot add. Only ' + availableStock + ' of "' + name + '" available.');
            if (qtyInput) qtyInput.value = 1;
            return;
        }
        jeProducts.push({ id: parsedId, name, code, price: parsedPrice, qty, stock: availableStock });
    }
    if (qtyInput) qtyInput.value = 1;
    notifySelectedProductAdded(name);
    jeRenderItems();
    jeCalc();
}

function jeRemoveProduct(idx) {
    if (_jeEditingCustomProductIdx === idx) {
        jeClearCustomProductEditState();
    }
    jeProducts.splice(idx, 1);
    jeRenderItems();
    jeCalc();
}

let _jeEditingCustomItemIdx = null;
let _jeEditingCustomProductIdx = null;

function jeClearCustomItemEditState() {
    _jeEditingCustomItemIdx = null;
    document.getElementById('jeCustomName').value = '';
    document.getElementById('jeCustomPrice').value = '';
    document.getElementById('jeCustomLabor').value = '';
    document.getElementById('jeCustomSubItems').value = '';
    const btn = document.getElementById('jeAddCustomItemBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-plus"></i> Add Custom Item';
        btn.title = 'Add custom item';
    }
}

function jeClearCustomProductEditState() {
    _jeEditingCustomProductIdx = null;
    document.getElementById('jeCustomProdName').value = '';
    document.getElementById('jeCustomProdPrice').value = '';
    document.getElementById('jeCustomProdQty').value = '1';
    const btn = document.getElementById('jeAddCustomProductBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-plus"></i>';
        btn.title = 'Add custom product';
    }
}

function jeEditCustomItem(idx) {
    const item = jeItems[idx];
    if (!item || item.type !== 'custom') return;
    _jeEditingCustomItemIdx = idx;
    _jeEditingCustomProductIdx = null;
    document.getElementById('jeCustomName').value = item.name || '';
    document.getElementById('jeCustomPrice').value = parseFloat(item.basePrice || 0).toFixed(2);
    document.getElementById('jeCustomLabor').value = parseFloat(item.labor || 0).toFixed(2);
    document.getElementById('jeCustomSubItems').value = Array.isArray(item.selectedSubItems) ? item.selectedSubItems.join('\n') : '';
    const btn = document.getElementById('jeAddCustomItemBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-pencil-square"></i> Update Custom Item';
        btn.title = 'Update custom item';
    }
    const productBtn = document.getElementById('jeAddCustomProductBtn');
    if (productBtn) {
        productBtn.innerHTML = '<i class="bi bi-plus"></i>';
        productBtn.title = 'Add custom product';
    }
}

function jeEditCustomProduct(idx) {
    const product = jeProducts[idx];
    if (!product || product.id !== 0) return;
    _jeEditingCustomProductIdx = idx;
    _jeEditingCustomItemIdx = null;
    document.getElementById('jeCustomProdName').value = product.name || '';
    document.getElementById('jeCustomProdPrice').value = parseFloat(product.price || 0).toFixed(2);
    document.getElementById('jeCustomProdQty').value = String(Math.max(1, parseInt(product.qty || 1, 10)));
    const btn = document.getElementById('jeAddCustomProductBtn');
    if (btn) {
        btn.innerHTML = '<i class="bi bi-pencil-square"></i>';
        btn.title = 'Update custom product';
    }
    const serviceBtn = document.getElementById('jeAddCustomItemBtn');
    if (serviceBtn) {
        serviceBtn.innerHTML = '<i class="bi bi-plus"></i> Add Custom Item';
        serviceBtn.title = 'Add custom item';
    }
}

/* ── Custom Entry (JE) ── */
function jeAddCustomItem() {
    const name = document.getElementById('jeCustomName').value.trim();
    if (!name) { showToast('Please enter a service/package name.'); return; }
    const basePrice = parseFloat(document.getElementById('jeCustomPrice').value) || 0;
    const laborCost = parseFloat(document.getElementById('jeCustomLabor').value) || 0;
    const subText = document.getElementById('jeCustomSubItems').value.trim();
    const subItems = subText ? subText.split('\n').map(s => s.trim()).filter(s => s) : [];

    if (_jeEditingCustomItemIdx !== null && jeItems[_jeEditingCustomItemIdx]) {
        const item = jeItems[_jeEditingCustomItemIdx];
        item.name = name;
        item.basePrice = basePrice;
        item.labor = laborCost;
        item.price = basePrice + laborCost;
        item.selectedSubItems = subItems;
        jeRenderItems();
        jeCalc();
        jeClearCustomItemEditState();
        return;
    }

    const effectivePrice = basePrice + laborCost;
    jeItems.push({ type: 'custom', id: 0, name, basePrice, labor: laborCost, price: effectivePrice, qty: 1, selectedSubItems: subItems });
    notifySelectedServiceAdded(name, 'custom');
    jeRenderItems();
    jeCalc();

    jeClearCustomItemEditState();
}

function jeAddCustomProduct() {
    const name = document.getElementById('jeCustomProdName').value.trim();
    const price = parseFloat(document.getElementById('jeCustomProdPrice').value) || 0;
    const qty = Math.max(1, parseInt(document.getElementById('jeCustomProdQty').value) || 1);
    if (!name) { showToast('Please enter a product name.'); return; }

    if (_jeEditingCustomProductIdx !== null && jeProducts[_jeEditingCustomProductIdx]) {
        const product = jeProducts[_jeEditingCustomProductIdx];
        product.name = name;
        product.price = price;
        product.qty = qty;
        jeRenderItems();
        jeCalc();
        jeClearCustomProductEditState();
        return;
    }

    jeProducts.push({ id: 0, name, code: '', price, qty });
    notifySelectedProductAdded(name);
    jeRenderItems();
    jeCalc();

    jeClearCustomProductEditState();
}

function jeChangeProductQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) { jeRemoveProduct(idx); return; }
    const prod = jeProducts[idx];
    if (!prod) return;
    // If stock was stored when the product was added, enforce it here for immediate UX feedback.
    if (typeof prod.stock !== 'undefined') {
        const avail = parseInt(prod.stock) || 0;
        if (avail <= 0) {
            showToast('This product is currently out of stock and has been removed from the list.');
            jeRemoveProduct(idx);
            return;
        }
        if (qty > avail) {
            showToast('Only ' + avail + ' of "' + (prod.name || 'product') + '" available in stock.');
            prod.qty = avail;
            jeRenderItems();
            jeCalc();
            return;
        }
    }

    prod.qty = qty;
    jeRenderItems();
    jeCalc();
}

function jeChangeProductPrice(idx, val) {
    const price = parseFloat(val);
    if (!Number.isFinite(price) || price < 0) return;
    const product = jeProducts[idx];
    if (!product) return;
    product.price = price;
    jeRenderItems();
    jeCalc();
}

function jeRenderItems() {
    const container = document.getElementById('jeSelectedItems');
    const countBadge = document.getElementById('jeItemCount');

    if (jeItems.length === 0 && jeProducts.length === 0) {
        container.innerHTML = '<p class="text-muted text-center small py-4 mb-0" id="jeEmptyMsg">No items added yet.</p>';
        countBadge.textContent = '0';
        return;
    }

    countBadge.textContent = jeItems.length + jeProducts.length;
    let html = '';
    jeItems.forEach((item, idx) => {
        const lineTotal = (item.price * item.qty).toFixed(2);
        const baseValue = item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice).toFixed(2) : '';
        const laborValue = item.labor !== undefined && item.labor !== null ? parseFloat(item.labor).toFixed(2) : '';
        const itemReference = item.type === 'bundle'
            ? `BND${item.id}`
            : item.type === 'service'
                ? (serviceCodesMap[item.id] || `SVC${String(item.id).padStart(3, '0')}`)
                : 'Custom';
        const subItems = item.type === 'bundle'
            ? (bundleServiceNamesMap[item.id] || [])
            : (Array.isArray(item.selectedSubItems) ? item.selectedSubItems : []);
        const subItemsHtml = subItems.length > 0
            ? `<div style="padding-left:10px;margin-top:2px;">${subItems.map(s=>`<div style="font-size:11px;color:#666;word-break:break-word;">- ${s}</div>`).join('')}</div>`
            : '';
        html += `
        <div class="px-3 py-2 reorder-card ${item.type === 'custom' ? 'custom-editable-item' : ''}" draggable="true" data-reorder-kind="je-service" data-reorder-index="${idx}" style="border-bottom:1px solid #f0f0f0;cursor:${item.type === 'custom' ? 'pointer' : 'grab'};user-select:none;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="text-muted" style="font-size:11px;cursor:grab;" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                        <div style="font-size:12px;font-weight:600;word-break:break-word;">${item.name}</div>
                    </div>
                    <small class="text-muted d-block" style="font-size:10px;padding-left:21px;">${itemReference}</small>
                    ${subItemsHtml}
                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap" style="row-gap:4px;">
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Base</small>
                            <input type="number" class="form-control form-control-sm text-center" value="" min="0" step="0.01" placeholder="${baseValue || '0.00'}"
                                style="width:70px;min-width:70px;font-size:11px;padding:0.2rem 0.3rem;" onchange="jeChangeBasePrice(${idx}, this.value)">
                        </div>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Labor</small>
                            <input type="number" class="form-control form-control-sm text-center" value="" min="0" step="0.01" placeholder="${laborValue || '0.00'}"
                                style="width:70px;min-width:70px;font-size:11px;padding:0.2rem 0.3rem;" onchange="jeChangeLabor(${idx}, this.value)">
                        </div>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Qty</small>
                            <input type="number" class="form-control form-control-sm text-center" value="${item.qty}" min="1" style="width:42px;min-width:42px;font-size:11px;padding:0.2rem 0.3rem;" onchange="jeChangeQty(${idx}, this.value)">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span style="font-size:13px;font-weight:700;min-width:74px;text-align:center;">₱${lineTotal}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;" onclick="jeRemoveItem(${idx})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    jeProducts.forEach((product, idx) => {
        const isFromBundle = !!product.fromBundle;
        const lineTotal = product.price * product.qty;
        const productReference = product.id > 0 ? (product.code || `PRD${String(product.id).padStart(2, '0')}`) : 'Custom';
        html += `
        <div class="px-3 py-2 reorder-card ${product.id === 0 ? 'custom-editable-item' : ''}" draggable="true" data-reorder-kind="je-product" data-reorder-index="${idx}" style="border-bottom:1px solid #f0f0f0;cursor:${product.id === 0 ? 'pointer' : 'grab'};user-select:none;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="text-muted" style="font-size:11px;cursor:grab;" title="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
                        <div style="font-size:12px;font-weight:600;word-break:break-word;">${product.name}</div>
                    </div>
                    <small class="text-muted d-block" style="font-size:10px;padding-left:21px;">${productReference}</small>
                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap" style="row-gap:4px;">
                        <small class="text-muted d-flex align-items-center gap-1">₱<input type="number" class="form-control form-control-sm text-center" value="${parseFloat(product.price).toFixed(2)}" min="0" step="0.01" style="width:82px;font-size:11px;" onchange="jeChangeProductPrice(${idx}, this.value)" aria-label="Product unit price"> each</small>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Qty</small>
                            <input type="number" class="form-control form-control-sm text-center" value="${product.qty}" min="1" style="width:42px;min-width:42px;font-size:11px;padding:0.2rem 0.3rem;" onchange="jeChangeProductQty(${idx}, this.value)">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span style="font-size:13px;font-weight:700;min-width:74px;text-align:center;">₱${lineTotal.toFixed(2)}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;" onclick="jeRemoveProduct(${idx})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
    container.querySelectorAll('.custom-editable-item').forEach(el => {
        el.addEventListener('click', (event) => {
            if (event.target.closest('input, button')) return;
            const kind = el.dataset.reorderKind;
            const idx = parseInt(el.dataset.reorderIndex, 10);
            if (kind === 'je-service') {
                jeEditCustomItem(idx);
            } else if (kind === 'je-product') {
                jeEditCustomProduct(idx);
            }
        });
    });
    bindSelectedListDragReorder('jeSelectedItems', {
        key: 'je-selected-items',
        getList: (kind) => kind === 'je-service' ? jeItems : (kind === 'je-product' ? jeProducts : null),
        onReorder: () => {
            jeRenderItems();
            jeCalc();
        }
    });
}

function jeCalc() {
    let subtotal = jeItems.reduce((sum, i) => sum + (i.price * i.qty), 0);
    let partsTotal = jeProducts.reduce((sum, p) => p.fromBundle ? sum : sum + p.price * p.qty, 0);
    const discType = document.getElementById('je_discount_type').value;
    const discVal = parseFloat(document.getElementById('je_discount_value').value) || 0;

    const discRow = document.getElementById('jeDiscountAmtRow');
    discRow.style.display = (discType === 'none' || discType === 'senior' || discType === 'pwd') ? 'none' : '';

    let discountAmt = 0;
    const base = subtotal + partsTotal;
    if (discType === 'percentage') discountAmt = base * (discVal / 100);
    else if (discType === 'fixed') discountAmt = discVal;
    else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;

    discountAmt = Math.min(discountAmt, base);
    const total = Math.max(0, base - discountAmt);

    document.getElementById('jeSubtotal').textContent = '₱' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('jePartsDisplay').textContent = '₱' + partsTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('jeDiscountDisplay').textContent = '-₱' + discountAmt.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('jeTotal').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function jeAddRecommendation() {
    const input = document.getElementById('je_rec_input');
    const val = (input.value || '').trim();
    if (!val) return;
    jeRecommendations.push({ name: val, subItems: [] });
    input.value = '';
    jeRenderRecommendations();
}

function jeRemoveRecommendation(idx) {
    jeRecommendations.splice(idx, 1);
    jeRenderRecommendations();
}

function jeRenderRecommendations() {
    const list = document.getElementById('je_rec_list');
    if (!list) return;
    if (jeRecommendations.length === 0) {
        list.innerHTML = '<p class="text-muted small text-center mb-0 py-1" id="je_rec_empty">No recommendations yet.</p>';
        return;
    }
    list.innerHTML = jeRecommendations.map((r, i) => {
        const subHtml = r.subItems && r.subItems.length > 0
            ? `<div style="padding-left:10px;">${r.subItems.map(s=>`<div style="font-size:10px;color:#888;">- ${s}</div>`).join('')}</div>`
            : '';
        return `
        <div class="px-1 py-1" style="border-bottom:1px solid #f0f0f0;">
            <div class="d-flex align-items-center justify-content-between" style="font-size:12px;">
                <span style="font-weight:600;">${r.name}</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" onclick="jeRemoveRecommendation(${i})" style="font-size:11px;"><i class="bi bi-x"></i></button>
            </div>
            ${subHtml}
        </div>`;
    }).join('');
}

function openJeRecBrowseModal() {
    // Reuse the same browse modal as JO
    bootstrap.Modal.getOrCreateInstance(document.getElementById('recBrowseModal')).show();
}

function jeReset() {
    _jeEditingCustomItemIdx = null;
    _jeEditingCustomProductIdx = null;
    jeItems = [];
    jeProducts = [];
    jeRecommendations = [];
    if (typeof _editEstimateId !== 'undefined') _editEstimateId = null;
    document.getElementById('je_customer_name').value = '';
    document.getElementById('je_customer_phone').value = '';
    document.getElementById('je_customer_email').value = '';
    document.getElementById('je_customer_address').value = '';
    document.getElementById('je_vehicle_make').value = '';
    document.getElementById('je_vehicle_model').value = '';
    document.getElementById('je_vehicle_year').value = '';
    document.getElementById('je_vehicle_plate').value = '';
    document.getElementById('je_vehicle_color').value = '';
    document.getElementById('je_vehicle_mileage').value = '';
    document.getElementById('je_discount_type').value = 'none';
    document.getElementById('je_discount_value').value = '0';
    document.getElementById('je_notes').value = '';
    jeRenderItems();
    jeCalc();
    jeRenderRecommendations();
    // Reset title and save button
    const titleEl = document.getElementById('jobEstimateModalLabel');
    if (titleEl) titleEl.innerHTML = '<i class="bi bi-calculator"></i> Job Estimate';
    const saveBtn = document.getElementById('jeSaveBtn');
    if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-save"></i> Save Job Estimate';
}

// Reset JE modal on close
document.getElementById('jobEstimateModal').addEventListener('hidden.bs.modal', jeReset);

function estimateItemKey(type, id) {
    return `${type || 'service'}-${parseInt(id, 10) || 0}`;
}

function estAddProduct(id, name, price, stock = 0, qtyInputId = null) {
    const qtyInput = qtyInputId ? document.getElementById(qtyInputId) : document.getElementById('est_product_qty');
    const qty = Math.max(1, parseInt(qtyInput?.value || '1', 10) || 1);
    const parsedId = parseInt(id, 10);
    const parsedPrice = parseFloat(price || 0) || 0;
    if (!parsedId || !name) return;

    const existing = estProducts.find(p => p.id === parsedId);
    if (existing) {
        existing.qty += qty;
    } else {
        estProducts.push({ id: parsedId, name, price: parsedPrice, qty });
    }
    if (qtyInput) qtyInput.value = 1;
    estRenderProducts();
    calculateEstimate();
}

function estRemoveProduct(idx) {
    estProducts.splice(idx, 1);
    estRenderProducts();
    calculateEstimate();
}

function estChangeProductQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) { estRemoveProduct(idx); return; }
    estProducts[idx].qty = qty;
    calculateEstimate();
}

function estRenderProducts() {
    const container = document.getElementById('estProductsList');
    if (!container) return;
    if (estProducts.length === 0) {
        container.innerHTML = '<p class="text-muted small text-center py-2 mb-0">No products added.</p>';
        return;
    }
    let html = '';
    estProducts.forEach((p, idx) => {
        const lineTotal = (p.price * p.qty).toFixed(2);
        html += `
        <div class="d-flex align-items-center justify-content-between px-2 py-1 mb-1 bg-white rounded" style="border:1px solid #eee;font-size:12px;">
            <div style="flex:1;min-width:0;">
                <div class="text-truncate fw-semibold">${p.name}</div>
                <small class="text-muted">₱${parseFloat(p.price).toFixed(2)} each</small>
            </div>
            <div class="d-flex align-items-center gap-1 ms-1">
                <input type="number" class="form-control form-control-sm text-center" value="${p.qty}" min="1"
                    style="width:46px;font-size:12px;" onchange="estChangeProductQty(${idx}, this.value)">
                <span style="min-width:58px;text-align:right;font-weight:600;">₱${lineTotal}</span>
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="estRemoveProduct(${idx})">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    });
    container.innerHTML = html;
}

function getEstimateSelectedItems() {
    return Array.from(document.querySelectorAll('.estimate-item:checked')).map((checkbox) => {
        const id = parseInt(checkbox.dataset.id, 10);
        const type = checkbox.dataset.type || 'service';
        const key = estimateItemKey(type, id);
        const qty = Math.max(1, parseInt(estItemQuantities[key] || 1, 10) || 1);

        return {
            id,
            type,
            name: checkbox.dataset.name || 'Item',
            price: parseFloat(checkbox.dataset.price) || 0,
            base_price: checkbox.dataset.basePrice !== undefined ? parseFloat(checkbox.dataset.basePrice) : (parseFloat(checkbox.dataset.price) || 0),
            labor_cost: parseFloat(checkbox.dataset.laborCost || 0),
            qty
        };
    });
}

function estChangeSelectedServiceQty(type, id, val) {
    const key = estimateItemKey(type, id);
    estItemQuantities[key] = Math.max(1, parseInt(val, 10) || 1);
    if (typeof window.calculateEstimate === 'function') {
        window.calculateEstimate();
    }
}

function estRemoveSelectedService(type, id) {
    const checkbox = document.querySelector(`.estimate-item[data-type="${type}"][data-id="${id}"]`);
    if (checkbox) {
        checkbox.checked = false;
        checkbox.dispatchEvent(new Event('change'));
    }
}

function estRenderSelectedItems() {
    const container = document.getElementById('estSelectedItemsList');
    if (!container) return;

    const selectedServicesAndBundles = getEstimateSelectedItems();
    const selectedProducts = estProducts.map((p) => ({
        name: p.name,
        type: 'product',
        qty: p.qty,
        price: p.price
    }));

    const allItems = [...selectedServicesAndBundles, ...selectedProducts];
    if (allItems.length === 0) {
        container.innerHTML = '<p class="text-muted small text-center py-2 mb-0">No items selected.</p>';
        return;
    }

    container.innerHTML = allItems.map((item, index) => {
        const unitPrice = parseFloat(item.price || 0);
        const qty = Math.max(1, parseInt(item.qty || 1, 10) || 1);
        const lineTotal = (unitPrice * qty).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const isProduct = item.type === 'product';
        const productIndex = index - selectedServicesAndBundles.length;
        const removeAction = isProduct
            ? `estRemoveProduct(${productIndex})`
            : `estRemoveSelectedService('${item.type}', ${item.id})`;
        const qtyAction = isProduct
            ? `estChangeProductQty(${productIndex}, this.value)`
            : `estChangeSelectedServiceQty('${item.type}', ${item.id}, this.value)`;
        const itemLabel = isProduct ? 'Product' : (item.type === 'bundle' ? 'Package' : 'Service');

        // Sub-items for services and bundles
        let subItemsHtml = '';
        if (!isProduct) {
            let subItems = [];
            if (item.type === 'bundle') {
                subItems = bundleServiceNamesMap[parseInt(item.id, 10)] || [];
            } else if (item.type === 'service') {
                const svcId = parseInt(item.id, 10);
                // Use confirmed selected sub-items if available, else empty
                subItems = (window._jeSelectedSubItemsMap || {}).hasOwnProperty(svcId)
                    ? (window._jeSelectedSubItemsMap || {})[svcId]
                    : [];
            }
            if (subItems.length > 0) {
                subItemsHtml = `<div style="padding-left:10px;margin-top:2px;">${subItems.map(s=>`<div style="font-size:11px;color:#666;word-break:break-word;">- ${s}</div>`).join('')}</div>`;
            }
        }

        return `
        <div class="px-3 py-2" style="border-bottom:1px solid #f0f0f0;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;font-weight:600;word-break:break-word;">${item.name}</div>
                    ${subItemsHtml}
                    <div class="d-flex align-items-center gap-1 mt-1 flex-wrap" style="row-gap:4px;">
                        <small class="text-muted">${itemLabel}</small>
                        <small class="text-muted">•</small>
                        <small class="text-muted">₱${unitPrice.toFixed(2)} each</small>
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            <small class="text-muted">Qty</small>
                            <input type="number" class="form-control form-control-sm text-center" value="${qty}" min="1" style="width:42px;min-width:42px;font-size:11px;padding:0.2rem 0.3rem;" onchange="${qtyAction}">
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-2">
                    <span style="font-size:13px;font-weight:700;min-width:74px;text-align:center;">₱${lineTotal}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1" style="width:24px;height:24px;display:flex;align-items:center;justify-content:center;" onclick="${removeAction}">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>`;
    }).join('');
}

function buildEstimatePayload() {
    const services = jeItems.map(item => ({
        id: item.id,
        type: item.type,
        name: item.name,
        price: item.price,
        base_price: item.basePrice,
        labor_cost: item.labor,
        qty: item.qty,
        selectedSubItems: item.selectedSubItems || []
    }));
    const products = jeProducts.map(p => ({ ...p }));

    const subtotal = jeItems.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const productsTotal = jeProducts.reduce((sum, p) => p.fromBundle ? sum : sum + p.price * p.qty, 0);

    return {
        csrf_token:      csrfToken,
        customer_name:   document.getElementById('je_customer_name').value.trim(),
        customer_phone:  document.getElementById('je_customer_phone').value.trim(),
        customer_email:  document.getElementById('je_customer_email').value.trim(),
        customer_address: document.getElementById('je_customer_address').value.trim(),
        vehicle_make:    document.getElementById('je_vehicle_make').value.trim(),
        vehicle_model:   document.getElementById('je_vehicle_model').value.trim(),
        vehicle_year:    document.getElementById('je_vehicle_year').value.trim(),
        vehicle_plate:   document.getElementById('je_vehicle_plate').value.trim(),
        vehicle_color:   document.getElementById('je_vehicle_color').value.trim(),
        vehicle_mileage: document.getElementById('je_vehicle_mileage').value.trim(),
        discount_type:   document.getElementById('je_discount_type').value,
        discount_value:  parseFloat(document.getElementById('je_discount_value').value) || 0,
        notes:           document.getElementById('je_notes').value.trim(),
        recommendations: jeRecommendations,
        services_total:  subtotal,
        products_total:  productsTotal,
        services,
        products
    };
}

function lookupReturningCustomer(plateInputId, prefix) {
    const plate = (document.getElementById(plateInputId)?.value || '').trim().toUpperCase();
    if (!plate) return;

    fetch(APP_URL + '/api/job_orders.php?action=customer_lookup&plate=' + encodeURIComponent(plate))
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.data) {
                showToast('No previous customer record found for this phone number.');
                return;
            }

            const customer = res.data;
            const setIfPresent = (id, value) => {
                const field = document.getElementById(id);
                if (field && value !== null && value !== undefined && String(value) !== '') {
                    field.value = value;
                }
            };

            setIfPresent(prefix + '_customer_name', customer.customer_name);
            setIfPresent(prefix + '_customer_phone', customer.customer_phone);
            setIfPresent(prefix + '_customer_email', customer.customer_email);
            setIfPresent(prefix + '_customer_address', customer.customer_address);
            setIfPresent(prefix + '_vehicle_make', customer.vehicle_make);
            setIfPresent(prefix + '_vehicle_model', customer.vehicle_model);
            setIfPresent(prefix + '_vehicle_year', customer.vehicle_year);
            setIfPresent(prefix + '_vehicle_plate', customer.vehicle_license);
            setIfPresent(prefix + '_vehicle_color', customer.vehicle_color);
            showToast('Previous customer and vehicle details loaded. Mileage was left unchanged.');
        })
        .catch(() => showToast('Unable to check previous customer records.'));
}

['jo', 'je'].forEach(prefix => {
    const plateField = document.getElementById(prefix + '_vehicle_plate');
    if (plateField) {
        plateField.addEventListener('blur', () => lookupReturningCustomer(prefix + '_vehicle_plate', prefix));
    }
});

function saveEstimateRecord(payload) {
    return fetch('<?php echo APP_URL; ?>/api/estimates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(r => r.json());
}

function deleteEstimateAfterConversion(estimateId) {
    if (!estimateId) {
        return Promise.resolve({ success: true });
    }

    return fetch(APP_URL + '/api/estimates.php?id=' + estimateId, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: csrfToken,
            converted_cleanup: true
        })
    }).then(r => r.json());
}

function normalizeEstimateItemToJo(item) {
    // Prefer explicit base_price / labor_cost when available (set by getEstimateSelectedItems)
    // Fall back to combined price only when that's all we have (e.g. packages/bundles)
    const hasExplicitBase = item?.base_price !== undefined && item?.base_price !== null;
    const base  = hasExplicitBase
        ? parseFloat(item.base_price)
        : parseFloat(item?.price ?? item?.service_price ?? item?.unit_price ?? 0) || 0;
    const labor = parseFloat(item?.labor_cost ?? item?.labor ?? 0) || 0;
    const qty   = parseInt(item?.qty ?? item?.quantity ?? 1, 10) || 1;
    const id    = parseInt(item?.id ?? 0, 10) || 0;
    const sourceType = String(item?.type || '').toLowerCase();
    const type = id === 0 && (!sourceType || sourceType === 'service' || sourceType === 'custom_service')
        ? 'custom'
        : (sourceType || 'service');

    return {
        type,
        id,
        name: item?.name || '',
        basePrice: base,
        labor,
        price: base + labor,
        qty,
        selectedSubItems: Array.isArray(item?.selectedSubItems) ? item.selectedSubItems : []
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const estimateCheckboxes = document.querySelectorAll('.estimate-item');

    // Search binding handled via inline onkeyup on each input

    function calculateEstimate() {
        const selectedServices = getEstimateSelectedItems();
        let servicesTotal = selectedServices.reduce((sum, item) => {
            return sum + ((parseFloat(item.price) || 0) * (parseInt(item.qty || 1, 10) || 1));
        }, 0);

        const productsTotal = estProducts.reduce((s, p) => s + p.price * p.qty, 0);
        const grandTotal    = servicesTotal + productsTotal;

        const fmt = n => '₱' + n.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        document.getElementById('estimateTotal').textContent         = fmt(servicesTotal);
        document.getElementById('estimateProductsTotal').textContent = fmt(productsTotal);
        document.getElementById('estimateGrandTotal').textContent    = fmt(grandTotal);
        estRenderSelectedItems();
    }

    // expose so product functions can call it
    window.calculateEstimate = calculateEstimate;

    // Track selected sub-items per JE service
    const jeSelectedSubItemsMap = window._jeSelectedSubItemsMap = window._jeSelectedSubItemsMap || {};

    // Pending JE sub-item selection state
    let _pendingJeCheckbox = null;

    estimateCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!checkbox.checked) {
                // unchecking — just remove
                const key = estimateItemKey(checkbox.dataset.type, checkbox.dataset.id);
                delete estItemQuantities[key];
                if (checkbox.dataset.type === 'service') delete jeSelectedSubItemsMap[parseInt(checkbox.dataset.id, 10)];
                calculateEstimate();
                return;
            }

            // Checking a service — see if it has sub-items
            if (checkbox.dataset.type === 'service') {
                const svcId = parseInt(checkbox.dataset.id, 10);
                const subItems = serviceSubItemsMap[svcId] || [];
                if (subItems.length > 0) {
                    // Prevent default check state until user confirms
                    checkbox.checked = false;
                    _pendingJeCheckbox = checkbox;

                    const titleEl = document.getElementById('joSubItemSelectTitle');
                    if (titleEl) titleEl.textContent = checkbox.dataset.name || '';
                    const listEl = document.getElementById('joSubItemSelectList');
                    if (listEl) {
                        listEl.innerHTML = subItems.map((item, i) => `
                            <div class="form-check mb-1">
                                <input class="form-check-input jo-subitem-check" type="checkbox" checked id="joSubItemCb_${i}">
                                <label class="form-check-label small" for="joSubItemCb_${i}">${item}</label>
                            </div>`).join('');
                    }
                    // Override confirm button for JE context
                    const confirmBtn = document.querySelector('#joSubItemSelectModal .btn-dark');
                    if (confirmBtn) {
                        confirmBtn.onclick = function() {
                            if (_pendingJeCheckbox) {
                                const svcId = parseInt(_pendingJeCheckbox.dataset.id, 10);
                                const allSubItems = serviceSubItemsMap[svcId] || [];
                                const selected = allSubItems.filter((_, i) => {
                                    const cb = document.getElementById(`joSubItemCb_${i}`);
                                    return cb && cb.checked;
                                });
                                jeSelectedSubItemsMap[svcId] = selected;
                                _pendingJeCheckbox.checked = true;
                                const key = estimateItemKey(_pendingJeCheckbox.dataset.type, _pendingJeCheckbox.dataset.id);
                                if (!estItemQuantities[key]) estItemQuantities[key] = 1;
                                _pendingJeCheckbox = null;
                                calculateEstimate();
                            }
                            bootstrap.Modal.getInstance(document.getElementById('joSubItemSelectModal')).hide();
                            // Restore original JO confirm for future JO use
                            confirmBtn.onclick = confirmJoSubItemSelect;
                        };
                    }
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('joSubItemSelectModal')).show();
                    return;
                }
            }

            const key = estimateItemKey(checkbox.dataset.type, checkbox.dataset.id);
            if (!estItemQuantities[key]) estItemQuantities[key] = 1;
            calculateEstimate();
        });
    });

    // reset products when modal closes
    document.getElementById('jobEstimateModal').addEventListener('hidden.bs.modal', function() {
        estProducts = [];
        estItemQuantities = {};
        _editEstimateId = null;
        // Reset title back to "Job Estimate"
        const titleEl = document.getElementById('jobEstimateModalLabel');
        if (titleEl) titleEl.innerHTML = '<i class="bi bi-calculator"></i> Job Estimate';
        Object.keys(jeSelectedSubItemsMap).forEach(k => delete jeSelectedSubItemsMap[k]);
        estRenderProducts();
        estimateCheckboxes.forEach(cb => cb.checked = false);

        const jeServiceSearch = document.getElementById('je_service_search');
        const jeBundleSearch = document.getElementById('je_bundle_search');
        const jeProductSearch = document.getElementById('je_product_search');
        if (jeServiceSearch) jeServiceSearch.value = '';
        if (jeBundleSearch) jeBundleSearch.value = '';
        if (jeProductSearch) jeProductSearch.value = '';

        // Reset search — show all rows
        joSearchFilter('', 'je-service-row');
        joSearchFilter('', 'je-bundle-row');
        joSearchFilter('', 'je-product-row');

        calculateEstimate();
    });

    // Init JO discount row visibility
    joCalc();
    joRenderProducts();
    estRenderProducts();
    calculateEstimate();

    // Combine description + checklist items before Add Service form submit
    const addSvcForm = document.querySelector('#addServiceModal form');
    if (addSvcForm) {
        addSvcForm.addEventListener('submit', function() {
            const descField = addSvcForm.querySelector('[name="description"]') || document.getElementById('addSvcDesc');
            if (descField && _addSvcSubItems.length > 0) {
                let desc = descField.value.trim();
                const subLines = _addSvcSubItems.map(i => `- ${i}`).join('\n');
                descField.value = desc ? `${desc}\n${subLines}` : subLines;
            }
        });
    }

    // Reset add service checklist items when modal closes
    document.getElementById('addServiceModal')?.addEventListener('hidden.bs.modal', function() {
        _addSvcSubItems = [];
        _renderSvcSubItems('addSvcSubItemsList', 'addSvcSubItemsEmpty', _addSvcSubItems);
        const input = document.getElementById('addSvcSubItemInput');
        if (input) input.value = '';
    });

    // Reset edit service checklist items when modal closes
    document.getElementById('editServiceModal')?.addEventListener('hidden.bs.modal', function() {
        _editSvcSubItems = [];
    });
});

function deleteItem(type, id, itemName = '') {
    appConfirm(`Delete this ${type}?\n\nName: ${itemName || 'Unknown'}\n${type} ID: ${id}`, {
        title: 'Delete Item',
        confirmText: 'Delete',
        variant: 'danger'
    }).then(confirmed => {
        if (!confirmed) return;

        fetch(`?action=delete_${type}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&csrf_token=${csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showToast(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.');
        });
    });
}

function toggleStatus(type, id) {
    fetch(`?action=toggle_${type}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&csrf_token=${csrfToken}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.');
    });
}

/* ── Edit Service ── */
let _editSvcSubItems = [];
let _addSvcSubItems  = [];

function _renderSvcSubItems(listId, emptyId, arr) {
    const list = document.getElementById(listId);
    if (!list) return;
    if (!arr.length) {
        list.innerHTML = `<p class="text-muted small text-center mb-0 py-1" id="${emptyId}">No items yet.</p>`;
        return;
    }
    const prefix = listId.startsWith('edit') ? 'removeEditSvcSubItem' : 'removeAddSvcSubItem';
    list.innerHTML = arr.map((item, i) => `
        <div class="d-flex align-items-center gap-2 mb-1 px-1">
            <span class="text-muted" style="font-size:11px;">-</span>
            <span class="small flex-grow-1 text-truncate">${item}</span>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" style="font-size:10px;line-height:1.4;" onclick="${prefix}(${i})"><i class="bi bi-x"></i></button>
        </div>`).join('');
}

function addEditSvcSubItem() {
    const input = document.getElementById('editSvcSubItemInput');
    if (!input || !input.value.trim()) return;
    _editSvcSubItems.push(input.value.trim());
    input.value = '';
    _renderSvcSubItems('editSvcSubItemsList', 'editSvcSubItemsEmpty', _editSvcSubItems);
}
function removeEditSvcSubItem(idx) {
    _editSvcSubItems.splice(idx, 1);
    _renderSvcSubItems('editSvcSubItemsList', 'editSvcSubItemsEmpty', _editSvcSubItems);
}
function addAddSvcSubItem() {
    const input = document.getElementById('addSvcSubItemInput');
    if (!input || !input.value.trim()) return;
    _addSvcSubItems.push(input.value.trim());
    input.value = '';
    _renderSvcSubItems('addSvcSubItemsList', 'addSvcSubItemsEmpty', _addSvcSubItems);
}
function removeAddSvcSubItem(idx) {
    _addSvcSubItems.splice(idx, 1);
    _renderSvcSubItems('addSvcSubItemsList', 'addSvcSubItemsEmpty', _addSvcSubItems);
}

function editServiceFromButton(button) {
    editService(
        button.dataset.serviceId || '',
        button.dataset.serviceName || '',
        button.dataset.serviceCode || '',
        button.dataset.serviceDescription || '',
        button.dataset.servicePrice || 0,
        button.dataset.serviceLabor || 0,
        button.dataset.serviceStatus || 'active'
    );
}

function editService(id, name, code, desc, price, labor, status) {
    // Split description: lines starting with "- " or "* " become checklist items
    const lines = (desc || '').split('\n');
    const descLines = [];
    _editSvcSubItems = [];
    lines.forEach(line => {
        const t = line.trim();
        if (t.startsWith('- ') || t.startsWith('* ')) {
            _editSvcSubItems.push(t.substring(2));
        } else {
            descLines.push(line);
        }
    });
    document.getElementById('editSvcId').value          = id;
    document.getElementById('editSvcName').value        = name;
    document.getElementById('editSvcCode').value        = code;
    document.getElementById('editSvcDesc').value        = descLines.join('\n').trim();
    document.getElementById('editSvcPrice').value       = price;
    document.getElementById('editSvcLabor').value       = labor;
    document.getElementById('editSvcStatus').value      = status;
    _renderSvcSubItems('editSvcSubItemsList', 'editSvcSubItemsEmpty', _editSvcSubItems);
    updateServicePriceTotal('editSvc');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editServiceModal')).show();
}

function updateServicePriceTotal(prefix) {
    const baseInput = document.getElementById(prefix + 'Price');
    const laborInput = document.getElementById(prefix + 'Labor');
    const totalField = document.getElementById(prefix + 'Total');

    if (!baseInput || !laborInput || !totalField) {
        return;
    }

    const base = parseFloat(baseInput.value) || 0;
    const labor = parseFloat(laborInput.value) || 0;
    totalField.textContent = '₱' + (base + labor).toFixed(2);
}

function bindServicePriceSummary(prefix) {
    const baseInput = document.getElementById(prefix + 'Price');
    const laborInput = document.getElementById(prefix + 'Labor');

    if (!baseInput || !laborInput) {
        return;
    }

    [baseInput, laborInput].forEach(input => {
        input.addEventListener('input', () => updateServicePriceTotal(prefix));
    });

    updateServicePriceTotal(prefix);
}

document.addEventListener('DOMContentLoaded', function () {
    bindServicePriceSummary('addSvc');
    bindServicePriceSummary('editSvc');
});

function saveEditService() {
    const id = document.getElementById('editSvcId').value;
    let description = document.getElementById('editSvcDesc').value.trim();
    if (_editSvcSubItems.length > 0) {
        const subLines = _editSvcSubItems.map(i => `- ${i}`).join('\n');
        description = description ? `${description}\n${subLines}` : subLines;
    }
    const params = new URLSearchParams({
        id,
        service_name:  document.getElementById('editSvcName').value.trim(),
        service_code:  document.getElementById('editSvcCode').value.trim(),
        description:   description,
        service_price: document.getElementById('editSvcPrice').value,
        labor_cost:    document.getElementById('editSvcLabor').value,
        status:        document.getElementById('editSvcStatus').value,
        csrf_token:    csrfToken,
    });
    fetch('?action=update_service', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params })
        .then(r => r.json())
        .then(d => {
            if (d.success) { bootstrap.Modal.getInstance(document.getElementById('editServiceModal')).hide(); location.reload(); }
            else showToast('Error: ' + d.message);
        })
        .catch(() => showToast('Network error.'));
}

/* ── Bundle Service/Product Search ── */
function bundleSvcSearch(val, rowClass) {
    const q = (val || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.' + rowClass);
    let visible = 0;
    rows.forEach(row => {
        const text = row.getAttribute('data-search-text') || '';
        if (!q || text.indexOf(q) !== -1) {
            row.style.removeProperty('display');
            visible++;
        } else {
            row.style.setProperty('display', 'none', 'important');
        }
    });
    const noMatchMap = {
        'add-bundle-svc-item': 'addBundleSvcNoMatch',
        'edit-bundle-svc-item': 'editBundleSvcNoMatch',
        'add-bundle-prod-item': 'addBundleProdNoMatch',
        'edit-bundle-prod-item': 'editBundleProdNoMatch',
    };
    const noMatchEl = document.getElementById(noMatchMap[rowClass] || '');
    if (noMatchEl) noMatchEl.style.display = (visible === 0 && q) ? 'block' : 'none';
}

/* ── Bundle Selected Items Summary (Add Bundle) ── */
function updateAddBundleSelectedSummary() {
    const container = document.getElementById('addBundleSelectedSummary');
    const emptyMsg = document.getElementById('addBundleSelectedEmpty');
    if (!container) return;

    const services = Array.from(document.querySelectorAll('.add-bundle-svc-check:checked'));
    const products = Array.from(document.querySelectorAll('.add-bundle-prod-check:checked'));

    if (services.length === 0 && products.length === 0) {
        container.innerHTML = '<p class="text-muted text-center small mb-0" id="addBundleSelectedEmpty">No items selected yet.</p>';
        return;
    }

    let html = '';
    if (services.length > 0) {
        html += '<div class="mb-1"><small class="fw-bold text-muted" style="font-size:10px;letter-spacing:.3px;">SERVICES (' + services.length + ')</small></div>';
        services.forEach(cb => {
            const label = cb.closest('.add-bundle-svc-item')?.querySelector('strong')?.textContent || 'Service';
            html += '<div style="font-size:11px;padding:2px 0;">• ' + label + '</div>';
        });
    }
    if (products.length > 0) {
        if (services.length > 0) html += '<hr class="my-1">';
        html += '<div class="mb-1"><small class="fw-bold text-muted" style="font-size:10px;letter-spacing:.3px;">PRODUCTS (' + products.length + ')</small></div>';
        products.forEach(cb => {
            const name = cb.dataset.name || 'Product';
            const qtyInput = cb.closest('.add-bundle-prod-item')?.querySelector('input[type=number]');
            const qty = qtyInput ? qtyInput.value : '1';
            html += '<div style="font-size:11px;padding:2px 0;">• ' + name + ' <span class="text-muted">(x' + qty + ')</span></div>';
        });
    }
    container.innerHTML = html;
}

// Wire up checkbox change events for Add Bundle summary
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-bundle-svc-check').forEach(cb => {
        cb.addEventListener('change', updateAddBundleSelectedSummary);
    });
    document.querySelectorAll('.add-bundle-prod-check').forEach(cb => {
        cb.addEventListener('change', updateAddBundleSelectedSummary);
    });
    // Also update on product qty change
    document.querySelectorAll('.add-bundle-prod-item input[type=number]').forEach(input => {
        input.addEventListener('input', updateAddBundleSelectedSummary);
    });

    // Edit Bundle summary
    document.querySelectorAll('.edit-bundle-svc-check').forEach(cb => {
        cb.addEventListener('change', updateEditBundleSelectedSummary);
    });
    document.querySelectorAll('.edit-bundle-prod-check').forEach(cb => {
        cb.addEventListener('change', updateEditBundleSelectedSummary);
    });
    document.querySelectorAll('.edit-bundle-prod-item input[type=number]').forEach(input => {
        input.addEventListener('input', updateEditBundleSelectedSummary);
    });
});

function updateEditBundleSelectedSummary() {
    const container = document.getElementById('editBundleSelectedSummary');
    if (!container) return;

    const services = Array.from(document.querySelectorAll('.edit-bundle-svc-check:checked'));
    const products = Array.from(document.querySelectorAll('.edit-bundle-prod-check:checked'));

    if (services.length === 0 && products.length === 0) {
        container.innerHTML = '<p class="text-muted text-center small mb-0">No items selected yet.</p>';
        return;
    }

    let html = '';
    if (services.length > 0) {
        html += '<div class="mb-1"><small class="fw-bold text-muted" style="font-size:10px;letter-spacing:.3px;">SERVICES (' + services.length + ')</small></div>';
        services.forEach(cb => {
            const label = cb.closest('.edit-bundle-svc-item')?.querySelector('strong')?.textContent || 'Service';
            html += '<div style="font-size:11px;padding:2px 0;">• ' + label + '</div>';
        });
    }
    if (products.length > 0) {
        if (services.length > 0) html += '<hr class="my-1">';
        html += '<div class="mb-1"><small class="fw-bold text-muted" style="font-size:10px;letter-spacing:.3px;">PRODUCTS (' + products.length + ')</small></div>';
        products.forEach(cb => {
            const name = cb.dataset.name || 'Product';
            const qtyInput = cb.closest('.edit-bundle-prod-item')?.querySelector('input[type=number]');
            const qty = qtyInput ? qtyInput.value : '1';
            html += '<div style="font-size:11px;padding:2px 0;">• ' + name + ' <span class="text-muted">(x' + qty + ')</span></div>';
        });
    }
    container.innerHTML = html;
}

/* ── Edit Bundle ── */
let editBundleSelectedIds = [];

function editBundle(id, name, desc, price, status, serviceIds) {
    document.getElementById('editBndId').value          = id;
    document.getElementById('editBndName').value        = name;
    document.getElementById('editBndDesc').value        = desc;
    document.getElementById('editBndPrice').value       = price;
    document.getElementById('editBndStatus').value      = status;
    editBundleSelectedIds = serviceIds || [];
    // Tick the right service checkboxes
    document.querySelectorAll('.edit-bundle-svc-check').forEach(cb => {
        cb.checked = editBundleSelectedIds.includes(parseInt(cb.value));
    });
    // Reset searches
    const svcSearch = document.getElementById('editBundleSvcSearch');
    if (svcSearch) svcSearch.value = '';
    bundleSvcSearch('', 'edit-bundle-svc-item');
    const prodSearch = document.getElementById('editBundleProdSearch');
    if (prodSearch) prodSearch.value = '';
    bundleSvcSearch('', 'edit-bundle-prod-item');

    // Reset product checkboxes then load from API
    document.querySelectorAll('.edit-bundle-prod-check').forEach(cb => { cb.checked = false; });
    fetch(APP_URL + '/api/service_bundles.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data && res.data.products) {
                res.data.products.forEach(p => {
                    const cb = document.getElementById('editBndProd_' + p.product_id);
                    if (cb) cb.checked = true;
                    const qtyInput = document.getElementById('editBndProdQty_' + p.product_id);
                    if (qtyInput) qtyInput.value = p.quantity || 1;
                });
            }
            updateEditBundleSelectedSummary();
        })
        .catch(() => { updateEditBundleSelectedSummary(); });

    bootstrap.Modal.getOrCreateInstance(document.getElementById('editBundleModal')).show();
}

function saveEditBundle() {
    const id = document.getElementById('editBndId').value;
    const checkedServices = Array.from(document.querySelectorAll('.edit-bundle-svc-check:checked')).map(cb => cb.value);
    if (checkedServices.length === 0) { showToast('Please select at least one service.'); return; }

    // Gather selected products
    const products = Array.from(document.querySelectorAll('.edit-bundle-prod-check:checked')).map(cb => {
        const prodId = parseInt(cb.value);
        const qtyInput = document.getElementById('editBndProdQty_' + prodId);
        return { product_id: prodId, quantity: Math.max(1, parseInt(qtyInput?.value || '1', 10)) };
    });

    const params = new URLSearchParams({
        id,
        bundle_name:   document.getElementById('editBndName').value.trim(),
        description:   document.getElementById('editBndDesc').value.trim(),
        package_price: document.getElementById('editBndPrice').value,
        status:        document.getElementById('editBndStatus').value,
        csrf_token:    csrfToken,
    });
    checkedServices.forEach(v => params.append('service_ids[]', v));
    params.append('products_json', JSON.stringify(products));
    fetch('?action=update_bundle', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params })
        .then(r => r.json())
        .then(d => {
            if (d.success) { bootstrap.Modal.getInstance(document.getElementById('editBundleModal')).hide(); location.reload(); }
            else showToast('Error: ' + d.message);
        })
        .catch(() => showToast('Network error.'));
}
</script>

<!-- ═══════════════════════════════════════════════════════
     EDIT SERVICE MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editServiceModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-wrench me-2"></i>Edit Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <input type="hidden" id="editSvcId">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Service Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="editSvcName" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Service Code</label>
            <input type="text" class="form-control" id="editSvcCode" readonly style="background:#f5f5f5;">
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editSvcDesc" rows="2" placeholder="Brief description of the service..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Checklist Items <small class="text-muted">(optional, appear as sub-rows in print)</small></label>
            <div id="editSvcSubItemsList" style="border:1px solid #e0e0e0;border-radius:6px;padding:8px;background:#f9f9f9;min-height:34px;max-height:150px;overflow-y:auto;">
              <p class="text-muted small text-center mb-0 py-1" id="editSvcSubItemsEmpty">No items yet.</p>
            </div>
            <div class="d-flex gap-2 mt-2">
              <input type="text" id="editSvcSubItemInput" class="form-control form-control-sm" placeholder="e.g. Filter Cleaning" style="flex:1;" onkeydown="if(event.key==='Enter'){event.preventDefault();addEditSvcSubItem();}">
              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addEditSvcSubItem()"><i class="bi bi-plus"></i></button>
            </div>
          </div>
          <div class="col-12">
            <div class="border rounded-3 p-3" style="background:#f8f9fa;border-color:#e0e0e0!important;">
              <div class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">Base Price (₱) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="editSvcPrice" step="0.01" min="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Labor Cost (₱) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="editSvcLabor" step="0.01" min="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Total</label>
                  <div class="form-control fw-bold" id="editSvcTotal" style="background:#fff;border:1px solid #e0e0e0;">₱0.00</div>
                </div>
              </div>
              <small class="text-muted d-block mt-2">Edit the labor amount directly; the total updates instantly.</small>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="editSvcStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelTechnicianRemoval()">Cancel</button>
        <button type="button" class="btn btn-dark" onclick="saveEditService()">
          <i class="bi bi-save"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     EDIT BUNDLE MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editBundleModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-box-seam me-2"></i>Edit Package</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <input type="hidden" id="editBndId">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Package Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="editBndName" required>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editBndDesc" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Package Price (₱) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="editBndPrice" step="0.01" min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="editBndStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <!-- Selected Items Summary -->
          <div class="col-12">
            <label class="form-label fw-bold"><i class="bi bi-list-check me-1"></i> Selected Items</label>
            <div id="editBundleSelectedSummary" style="border:1.5px solid #e0e0e0;border-radius:8px;padding:12px;background:#fff;min-height:40px;">
                <p class="text-muted text-center small mb-0">No items selected yet.</p>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Services Included <span class="text-danger">*</span></label>
            <div class="position-relative mb-2">
                <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                <input type="text" id="editBundleSvcSearch" class="form-control form-control-sm" placeholder="Search services..." style="padding-left:30px;"
                    onkeyup="bundleSvcSearch(this.value, 'edit-bundle-svc-item')">
            </div>
            <div style="max-height:260px;overflow-y:auto;border:1.5px solid #e0e0e0;border-radius:8px;padding:12px;background:#f9f9f9;">
              <?php if (empty($allActiveServices)): ?>
                <p class="text-muted text-center small mb-0">No active services available.</p>
              <?php else: ?>
                <?php foreach ($allActiveServices as $svc): ?>
                <div class="form-check mb-2 p-2 bg-white rounded edit-bundle-svc-item" style="border:1px solid #eee;" data-search-text="<?php echo escape(strtolower(($svc['service_name'] ?? '') . ' ' . ($svc['service_code'] ?? ''))); ?>">
                  <input class="form-check-input edit-bundle-svc-check" type="checkbox"
                         value="<?php echo $svc['id']; ?>"
                         id="editBndSvc_<?php echo $svc['id']; ?>">
                  <label class="form-check-label w-100 d-flex justify-content-between" for="editBndSvc_<?php echo $svc['id']; ?>">
                    <div>
                      <strong><?php echo escape($svc['service_name']); ?></strong>
                      <small class="text-muted d-block"><?php echo escape($svc['service_code']); ?></small>
                    </div>
                    <strong><?php echo formatCurrency($svc['service_price'] + $svc['labor_cost']); ?></strong>
                  </label>
                </div>
                <?php endforeach; ?>
                <p class="text-muted text-center small py-2 mb-0" id="editBundleSvcNoMatch" style="display:none;">No matching services found.</p>
              <?php endif; ?>
            </div>
          </div>
          <!-- Products Included -->
          <div class="col-12">
            <label class="form-label">Products Included <small class="text-muted">(optional)</small></label>
            <div class="position-relative mb-2">
                <i class="bi bi-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#999;font-size:12px;"></i>
                <input type="text" id="editBundleProdSearch" class="form-control form-control-sm" placeholder="Search products..." style="padding-left:30px;"
                    onkeyup="bundleSvcSearch(this.value, 'edit-bundle-prod-item')">
            </div>
            <div style="max-height:360px;overflow-y:auto;border:1.5px solid #e0e0e0;border-radius:8px;padding:12px;background:#f9f9f9;">
              <?php if (empty($allInventoryProducts)): ?>
                <p class="text-muted text-center small mb-0">No products in inventory.</p>
              <?php else: ?>
                <?php foreach ($allInventoryProducts as $prod): ?>
                <div class="d-flex align-items-center justify-content-between mb-1 p-2 bg-white rounded edit-bundle-prod-item" style="border:1px solid #eee;" data-search-text="<?php echo escape(strtolower(($prod['product_name'] ?? '') . ' ' . ($prod['product_code'] ?? ''))); ?>">
                    <div class="d-flex align-items-center gap-2">
                        <input class="form-check-input edit-bundle-prod-check" type="checkbox"
                               value="<?php echo $prod['id']; ?>"
                               id="editBndProd_<?php echo $prod['id']; ?>"
                               data-name="<?php echo escape($prod['product_name']); ?>"
                               data-price="<?php echo (float)$prod['selling_price']; ?>">
                        <label class="form-check-label small mb-0" for="editBndProd_<?php echo $prod['id']; ?>" style="cursor:pointer;">
                            <strong><?php echo escape($prod['product_name']); ?></strong>
                            <small class="text-muted d-block"><?php echo escape($prod['product_code']); ?> • <?php echo formatCurrency($prod['selling_price']); ?></small>
                        </label>
                    </div>
                    <input type="number" class="form-control form-control-sm text-center" id="editBndProdQty_<?php echo $prod['id']; ?>" value="1" min="1" style="width:55px;font-size:12px;">
                </div>
                <?php endforeach; ?>
                <p class="text-muted text-center small py-2 mb-0" id="editBundleProdNoMatch" style="display:none;">No matching products found.</p>
              <?php endif; ?>
            </div>
            <small class="text-muted">Check products and set quantity.</small>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark" onclick="saveEditBundle()">
          <i class="bi bi-save"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TIMER STOP NOTES MODAL — optional notes when stopping timer
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="timerStopModal" tabindex="-1" aria-hidden="true" style="z-index:1060;">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header py-2">
        <h6 class="modal-title mb-0"><i class="bi bi-pause-circle me-1"></i>Stop Timer</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-2">Add a note or reason for stopping (optional).</p>
        <textarea class="form-control form-control-sm" id="timerStopNotes" rows="3" placeholder="e.g. Waiting for parts, lunch break..."></textarea>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark btn-sm" id="timerStopConfirmBtn">
          <i class="bi bi-stop-circle"></i> Stop Timer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     INSPECTION MODAL — Pass or Needs Revision
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true" style="z-index:1060;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header py-2" id="inspectionModalHeader">
        <h6 class="modal-title mb-0" id="inspectionModalTitle">Inspection</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="inspectionModalMessage" class="mb-0" style="font-size:13px;"></p>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm" id="inspectionModalConfirmBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SUB-ITEM SELECTION MODAL — shown when adding a service with checklist items
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="joSubItemSelectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0" style="font-weight:600;">Select Items to Include</h5>
          <div class="text-muted" id="joSubItemSelectTitle" style="font-size:13px;margin-top:2px;"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Uncheck items you don&#39;t want on this job order.</p>
        <div id="joSubItemSelectList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark" onclick="confirmJoSubItemSelect()">
          <i class="bi bi-plus"></i> Add Service
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     TECHNICIAN PICKER MODAL — used by Add Technician / Add Assistant buttons
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="joTechPickerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="joTechPickerTitle" style="font-weight:600;">Select Technician</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height:340px;overflow-y:auto;padding:12px;">
        <input type="text" id="joTechPickerSearch" class="form-control form-control-sm mb-3" placeholder="Search technician...">
        <?php foreach ($allTechnicians as $tech): ?>
        <div class="d-flex align-items-center justify-content-between py-2 px-2 mb-1 rounded jo-picker-tech-row"
             data-search-text="<?php echo escape(strtolower($tech['full_name'])); ?>"
             style="border:1px solid #eee;background:#fafafa;">
            <div>
                <strong style="font-size:13px;"><?php echo escape($tech['full_name']); ?></strong>
                <span class="badge ms-2" id="picker_avail_<?php echo $tech['id']; ?>" style="font-size:10px;">—</span>
            </div>
            <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                onclick="joPickTechnician(<?php echo $tech['id']; ?>)">
                Select
            </button>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     CHANGE TECHNICIAN MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="changeTechModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-people me-2"></i>Change / Add Technician</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">Select the technician(s) to assign to this job order. Removed technicians will retain their time record marked as <strong>Unfinished</strong>.</p>
        <div id="changeTechList" style="max-height:280px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
          <?php foreach ($allTechnicians as $tech): ?>
          <div class="form-check mb-2 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <input class="form-check-input change-tech-check" type="checkbox"
                     value="<?php echo $tech['id']; ?>"
                     id="ct_tech_<?php echo $tech['id']; ?>"
                     data-name="<?php echo escape($tech['full_name']); ?>">
              <label class="form-check-label small" for="ct_tech_<?php echo $tech['id']; ?>"><?php echo escape($tech['full_name']); ?></label>
            </div>
            <span class="badge ct-avail-badge" id="ct_avail_<?php echo $tech['id']; ?>" style="font-size:10px;">—</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark" onclick="saveChangeTech()"><i class="bi bi-save"></i> Save Technician</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="technicianRemovalReasonModal" tabindex="-1" aria-hidden="true" style="z-index:1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Technician Removal Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" id="technicianRemovalConfirmationText">Confirm removing this technician as Absent?</p>
                <select id="technicianRemovalReasonSelect" class="form-select" onchange="updateTechnicianRemovalConfirmation(this.value)">
                    <option value="Absent">Absent</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelTechnicianRemoval()">Cancel</button>
                <button type="button" class="btn btn-dark" id="technicianRemovalConfirmButton" onclick="confirmTechnicianRemoval()">Confirm Absent Removal</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     RECOMMENDATION BROWSE MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="recBrowseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-lightbulb me-2"></i>Browse Recommendations</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:16px;">
        <input type="text" id="rec_browse_search" class="form-control form-control-sm mb-3" placeholder="Search services, packages, or products...">
        <ul class="nav nav-tabs mb-2" role="tablist">
          <li class="nav-item"><button class="nav-link active py-1 px-3" data-bs-toggle="tab" data-bs-target="#recTabSvc" type="button" style="font-size:13px;">Services</button></li>
          <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#recTabPkg" type="button" style="font-size:13px;">Packages</button></li>
          <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#recTabProd" type="button" style="font-size:13px;">Products</button></li>
        </ul>
        <div class="tab-content" style="max-height:300px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
          <div class="tab-pane fade show active" id="recTabSvc" role="tabpanel">
            <?php foreach ($allActiveServices as $svc): ?>
            <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded rec-browse-row" data-search-text="<?php echo escape(strtolower($svc['service_name'])); ?>">
              <div>
                <strong style="font-size:12px;"><?php echo escape($svc['service_name']); ?></strong>
                <small class="text-muted d-block"><?php echo escape($svc['service_code']); ?></small>
              </div>
              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                onclick="joAddRecommendationFromBrowse('<?php echo addslashes(escape($svc['service_name'])); ?>', 'service', <?php echo (int)$svc['id']; ?>)">
                <i class="bi bi-plus"></i> Add
              </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($allActiveServices)): ?><p class="text-muted text-center small py-3 mb-0">No active services.</p><?php endif; ?>
          </div>
          <div class="tab-pane fade" id="recTabPkg" role="tabpanel">
            <?php foreach ($allActiveBundles as $bnd): ?>
            <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded rec-browse-row" data-search-text="<?php echo escape(strtolower($bnd['bundle_name'])); ?>">
              <div>
                <strong style="font-size:12px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services</small>
              </div>
              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                onclick="joAddRecommendationFromBrowse('<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Package)', 'bundle', <?php echo (int)$bnd['id']; ?>)">
                <i class="bi bi-plus"></i> Add
              </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($allActiveBundles)): ?><p class="text-muted text-center small py-3 mb-0">No active packages.</p><?php endif; ?>
          </div>
          <div class="tab-pane fade" id="recTabProd" role="tabpanel">
            <?php foreach ($allInventoryProducts as $prod): ?>
            <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded rec-browse-row" data-search-text="<?php echo escape(strtolower($prod['product_name'])); ?>">
              <div>
                <strong style="font-size:12px;"><?php echo escape($prod['product_name']); ?></strong>
                <small class="text-muted d-block"><?php echo escape($prod['product_code']); ?></small>
              </div>
              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                onclick="joAddRecommendationFromBrowse('<?php echo addslashes(escape($prod['product_name'])); ?>')">
                <i class="bi bi-plus"></i> Add
              </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($allInventoryProducts)): ?><p class="text-muted text-center small py-3 mb-0">No products.</p><?php endif; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     VIEW JOB ORDER MODAL — uses print layout
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewJobOrderModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Job Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light" id="viewJoBody">
        <div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary btn-sm" id="viewJoPrintBtn"><i class="bi bi-printer"></i> Print</button>
        <?php if ($canEditJoPayments): ?>
        <button type="button" class="btn btn-outline-dark btn-sm" id="viewJoAddPaymentBtn" onclick="openAddPaymentModal()"><i class="bi bi-plus-circle"></i> Add Payment</button>
        <?php endif; ?>
        <button type="button" class="btn btn-dark btn-sm" id="viewJoEditBtn"><i class="bi bi-pencil"></i> Edit</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     ADD PAYMENT MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true" style="z-index:1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
                <h6 class="modal-title fw-bold" id="addPaymentModalLabel"><i class="bi bi-cash me-2"></i>Add Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ap_jo_id">
                <div class="mb-3">
                    <label class="form-label form-label-sm">JO Balance Remaining</label>
                    <input type="text" class="form-control form-control-sm" id="ap_balance_display" readonly style="background:#f5f5f5;font-weight:700;">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-bold">Amount (₱) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="ap_amount" step="0.01" min="0.01" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-bold">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="ap_method">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Swipe/Card</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Reference # <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control form-control-sm" id="ap_reference" placeholder="Receipt, transaction ID...">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Paid By <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control form-control-sm" id="ap_paid_by" placeholder="Customer name or payer">
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm">Notes <small class="text-muted">(optional)</small></label>
                    <textarea class="form-control form-control-sm" id="ap_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="apSaveBtn" onclick="savePaymentRecord()"><i class="bi bi-check-lg"></i> Save Payment</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true" style="z-index:1065;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
                <h6 class="modal-title fw-bold" id="editPaymentModalLabel"><i class="bi bi-pencil me-2"></i>Edit Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ep_jo_id">
                <input type="hidden" id="ep_payment_id">
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-bold">Amount (₱) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="ep_amount" step="0.01" min="0.01" placeholder="0.00">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm fw-bold">Payment Method <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="ep_method">
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="card">Swipe/Card</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Reference #</label>
                    <input type="text" class="form-control form-control-sm" id="ep_reference">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Paid By</label>
                    <input type="text" class="form-control form-control-sm" id="ep_paid_by">
                </div>
                <div class="mb-3">
                    <label class="form-label form-label-sm">Payment Date</label>
                    <input type="datetime-local" class="form-control form-control-sm" id="ep_payment_date">
                </div>
                <div class="mb-2">
                    <label class="form-label form-label-sm">Notes</label>
                    <textarea class="form-control form-control-sm" id="ep_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark btn-sm" onclick="saveEditedPaymentRecord()"><i class="bi bi-save"></i> Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PRINT CHOOSER MODAL — choose regular or technician copy
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="printChooserModal" tabindex="-1" aria-labelledby="printChooserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#f8f9fa;border-bottom:1px solid #e0e0e0;">
                <h6 class="modal-title" id="printChooserModalLabel" style="font-weight:600;">
                    <i class="bi bi-printer me-1"></i> Print Job Order
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-3">Select print type:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-dark btn-sm" onclick="choosePrintRegular()">
                        <i class="bi bi-file-earmark-text me-1"></i> Regular Print (with prices)
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="choosePrintTechnician()">
                        <i class="bi bi-tools me-1"></i> Technician Copy (no prices)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     EDIT JOB ORDER MODAL — same layout as Create JO
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editJobOrderModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-pencil me-2"></i>Edit Job Order <span id="editJoNumber" class="text-muted fs-6"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px;background:#fafafa;">
        <form id="editJoForm">
          <input type="hidden" id="editJoId">
          <div class="row g-3">
            <!-- LEFT -->
            <div class="col-lg-7">
              <!-- Customer -->
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-person me-1"></i>Customer Information</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <div class="row g-2">
                    <div class="col-md-6">
                      <label class="form-label form-label-sm">Full Name *</label>
                      <input type="text" class="form-control form-control-sm" id="editJoCustomerName" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label form-label-sm">Contact Number *</label>
                      <input type="tel" class="form-control form-control-sm" id="editJoCustomerPhone" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label form-label-sm">Email</label>
                      <input type="email" class="form-control form-control-sm" id="editJoCustomerEmail">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label form-label-sm">Address</label>
                      <input type="text" class="form-control form-control-sm" id="editJoCustomerAddress">
                    </div>
                  </div>
                </div>
              </div>
              <!-- Vehicle -->
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-car-front me-1"></i>Vehicle Information</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <div class="row g-2">
                    <div class="col-4"><label class="form-label form-label-sm">Make / Brand</label><input type="text" class="form-control form-control-sm" id="editJoMake"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Model</label><input type="text" class="form-control form-control-sm" id="editJoModel"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Year</label><input type="text" class="form-control form-control-sm" id="editJoYear"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Plate Number</label><input type="text" class="form-control form-control-sm" id="editJoPlate"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Color</label><input type="text" class="form-control form-control-sm" id="editJoColor"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Mileage (km)</label><input type="text" class="form-control form-control-sm" id="editJoMileage"></div>
                  </div>
                </div>
              </div>
              <!-- Services & Packages picker -->
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <ul class="nav nav-tabs nav-sm mb-2" id="editJoServiceTabs">
                    <li class="nav-item"><a class="nav-link active py-1 px-3" data-bs-toggle="tab" href="#editJoTabIndividual" style="font-size:12px;">Services</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#editJoTabBundles" style="font-size:12px;">Packages (PMS)</a></li>
                  </ul>
                  <div class="tab-content">
                    <div class="tab-pane fade show active" id="editJoTabIndividual">
                      <div style="max-height:180px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:8px;background:#f9f9f9;">
                        <?php if (empty($allActiveServices)): ?>
                          <p class="text-muted text-center small py-2 mb-0">No active services.</p>
                        <?php else: ?>
                          <?php foreach ($allActiveServices as $svc): ?>
                          <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded" style="border:1px solid #eee;">
                            <div>
                              <strong style="font-size:12px;"><?php echo escape($svc['service_name']); ?></strong>
                              <small class="text-muted d-block"><?php echo escape($svc['service_code']); ?></small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                              <span style="font-size:12px;font-weight:600;"><?php echo formatCurrency($svc['service_price'] + $svc['labor_cost']); ?></span>
                              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                                onclick="editJoAddItem('service',<?php echo $svc['id']; ?>,'<?php echo addslashes(escape($svc['service_name'])); ?>',<?php echo ($svc['service_price']+$svc['labor_cost']); ?>)">
                                <i class="bi bi-plus"></i>
                              </button>
                            </div>
                          </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="editJoTabBundles">
                      <div style="max-height:180px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:8px;background:#f9f9f9;">
                        <?php if (empty($allActiveBundles)): ?>
                          <p class="text-muted text-center small py-2 mb-0">No active packages.</p>
                        <?php else: ?>
                          <?php foreach ($allActiveBundles as $bnd): ?>
                          <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded" style="border:1px solid #eee;">
                            <div>
                              <strong style="font-size:12px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                              <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                              <span style="font-size:12px;font-weight:600;"><?php echo formatCurrency($bnd['package_price']); ?></span>
                              <button type="button" class="btn btn-sm btn-dark py-0 px-2" style="font-size:11px;"
                                onclick="editJoAddItem('bundle',<?php echo $bnd['id']; ?>,'<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Package)',<?php echo $bnd['package_price']; ?>)">
                                <i class="bi bi-plus"></i>
                              </button>
                            </div>
                          </div>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <!-- Selected items list -->
                  <div class="mt-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <small class="fw-semibold text-muted">Selected Items</small>
                      <span class="badge bg-dark" id="editJoItemCount">0</span>
                    </div>
                    <div id="editJoSelectedItems" style="min-height:120px;max-height:360px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;background:#fff;">
                      <p class="text-muted text-center small py-3 mb-0" id="editJoEmptyMsg">No items added.</p>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Notes -->
              <div class="card" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <textarea class="form-control form-control-sm" id="editJoNotes" rows="2" placeholder="Additional notes..."></textarea>
                </div>
              </div>
            </div>
            <!-- RIGHT -->
            <div class="col-lg-5">
              <!-- Status -->
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-info-circle me-1"></i>Status & Payment</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <div class="mb-3">
                    <label class="form-label form-label-sm">Payment Method</label>
                    <select class="form-select form-select-sm" id="editJoPayMethod">
                      <option value="cash">Cash</option>
                      <option value="gcash">GCash</option>
                      <option value="bank_transfer">Bank Transfer</option>
                      <option value="card">Swipe/Card</option>
                    </select>
                  </div>
                  <div>
                    <label class="form-label form-label-sm">Payment Status</label>
                    <select class="form-select form-select-sm" id="editJoPayStatus" onchange="editJoTogglePartial()">
                      <option value="pending">Pending</option>
                      <option value="partial">Partial</option>
                      <option value="paid">Paid</option>
                    </select>
                  </div>
                  <!-- Partial payment field -->
                  <div id="editJoPartialRow" style="display:none;margin-top:12px;">
                    <label class="form-label form-label-sm">Amount Paid (₱) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="editJoPartialAmount"
                           min="0" step="0.01" value="0" oninput="editJoCalcPartial()" placeholder="0.00">
                    <div class="d-flex justify-content-between mt-2">
                      <span class="small text-muted">Remaining Balance</span>
                      <strong class="text-danger" id="editJoRemainingBalance">₱0.00</strong>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <div class="d-flex justify-content-between align-items-center gap-2">
                    <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-cash me-1"></i>Payment Records</h6>
                    <?php if ($canEditJoPayments): ?>
                    <button type="button" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size:11px;" onclick="openJoCreatePaymentModal()">
                      <i class="bi bi-plus"></i> Add Payment
                    </button>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="card-body" style="padding:12px;">
                  <div id="joPaymentsListEdit" style="min-height:30px;max-height:180px;overflow-y:auto;"></div>
                </div>
              </div>
              <!-- Billing summary (read-only) -->
              <div class="card" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-receipt me-1"></i>Billing Summary</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Services Subtotal</span>
                    <strong id="editJoSubtotal">₱0.00</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="small text-muted">Products Subtotal</span>
                    <strong id="editJoPartsCost">₱0.00</strong>
                  </div>
                  <hr class="my-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <strong>Total Amount</strong>
                    <h5 class="mb-0" id="editJoTotal" style="font-weight:700;">₱0.00</h5>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;padding:12px 20px;">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark btn-sm" onclick="saveEditJobOrder()">
          <i class="bi bi-save"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     VIEW ESTIMATE MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="viewEstimateModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-calculator me-2"></i>Estimate Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewEstBody">
        <div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-primary btn-sm" id="viewEstPrintBtn">
          <i class="bi bi-printer"></i> Print
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="viewEstSavePdfBtn">
          <i class="bi bi-file-earmark-pdf"></i> Save PDF
        </button>
        <button type="button" class="btn btn-dark btn-sm" id="viewEstConvertBtn">
          <i class="bi bi-arrow-right-circle"></i> Convert to Job Order
        </button>
        <button type="button" class="btn btn-dark btn-sm" id="viewEstEditBtn">
          <i class="bi bi-pencil"></i> Edit
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     EDIT ESTIMATE MODAL
═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="editEstimateModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Estimate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editEstForm">
          <input type="hidden" id="editEstId">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label form-label-sm">Make / Brand</label>
              <input type="text" class="form-control form-control-sm" id="editEstMake">
            </div>
            <div class="col-md-6">
              <label class="form-label form-label-sm">Model</label>
              <input type="text" class="form-control form-control-sm" id="editEstModel">
            </div>
            <div class="col-md-4">
              <label class="form-label form-label-sm">Year</label>
              <input type="text" class="form-control form-control-sm" id="editEstYear">
            </div>
            <div class="col-md-4">
              <label class="form-label form-label-sm">Plate No.</label>
              <input type="text" class="form-control form-control-sm" id="editEstPlate">
            </div>
            <div class="col-md-4">
              <label class="form-label form-label-sm">Color</label>
              <input type="text" class="form-control form-control-sm" id="editEstColor">
            </div>
            <div class="col-md-6">
              <label class="form-label form-label-sm">Mileage (km)</label>
              <input type="text" class="form-control form-control-sm" id="editEstMileage">
            </div>
            <div class="col-md-6">
              <label class="form-label form-label-sm">Status</label>
              <select class="form-select form-select-sm" id="editEstStatus">
                <option value="draft">Draft</option>
                <option value="converted">Converted</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark btn-sm" onclick="saveEditEstimate()">
          <i class="bi bi-save"></i> Save Changes
        </button>
      </div>
    </div>
  </div>
</div>

<script>
/* ═══════════════════════════════════════════
   JOB ORDER — VIEW / EDIT / PRINT
═══════════════════════════════════════════ */
const APP_URL = '<?php echo APP_URL; ?>';
const canEditJoPayments = <?php echo hasAnyRole(['admin', 'cashier']) ? 'true' : 'false'; ?>;

let _editingPaymentRecord = null;

function viewJobOrder(id) {
    document.getElementById('viewJoBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewJobOrderModal')).show();

    fetch(APP_URL + '/api/job_orders.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { document.getElementById('viewJoBody').innerHTML = '<p class="text-danger p-3">'+res.message+'</p>'; return; }
            const d   = res.data;
            const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
            const statusBadge = {pending:'secondary',ongoing:'primary',under_inspection:'info',car_washing:'warning',completed:'success',released:'success',returned_for_revision:'danger',cancelled:'danger'};
            const payBadge    = {pending:'secondary',partial:'warning',paid:'success'};
            const date = d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—';
            const assignedTechnicians = Array.isArray(d.technicians) && d.technicians.length
                ? d.technicians.map((t) => t.full_name).filter(Boolean).join(', ')
                : (d.assigned_technician_name || 'Unassigned');
            const elapsedSec = parseInt(d.status_elapsed_seconds ?? d.status_timer_seconds ?? 0, 10) || 0;

            document.getElementById('viewJoBody').innerHTML = `
            <div style="font-family:Arial,sans-serif;font-size:10pt;color:#000;padding:10px;">
                            ${getPrintHeaderHtml('JOB ORDER', d.job_order_number || '—', date)}
              <!-- Customer & Vehicle -->
              <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
                <tr>
                  <td style="width:50%;vertical-align:top;padding-right:8px;">
                    <table style="width:100%;border-collapse:collapse;">
                      <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;border-bottom:1px solid #333;letter-spacing:.5px;">CUSTOMER</td></tr>
                      <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #eee;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #eee;">${d.customer_name||'—'}</td></tr>
                      <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #eee;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.customer_phone||'—'}</td></tr>
                      <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #eee;">Email</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.customer_email||'—'}</td></tr>
                      <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${d.customer_address||'—'}</td></tr>
                    </table>
                  </td>
                  <td style="width:50%;vertical-align:top;padding-left:8px;border-left:1px solid #ddd;">
                    <table style="width:100%;border-collapse:collapse;">
                      <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;border-bottom:1px solid #333;letter-spacing:.5px;">VEHICLE</td></tr>
                      <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #eee;">Make/Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #eee;">${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</td></tr>
                      <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Year</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_year||'—'}</td></tr>
                      <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_license||'—'}</td></tr>
                      <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Color</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_color||'—'}</td></tr>
                      <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${d.vehicle_mileage||'—'} km</td></tr>
                    </table>
                  </td>
                </tr>
              </table>
              <!-- SERVICES table -->
              ${(d.services||[]).length > 0 ? `
              <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;margin-bottom:4px;">SERVICES</div>
              <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9pt;">
                <colgroup><col style="width:5%"><col><col style="width:8%"><col class="vjo-price-col" style="width:18%"><col class="vjo-price-col" style="width:18%"></colgroup>
                <thead>
                  <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Service</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th class="vjo-price-col" style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th class="vjo-price-col" style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  ${(d.services||[]).map((s,i)=>{
                      const svcId = parseInt(s.service_id||0,10);
                      const bndId = parseInt(s.bundle_id||0,10);
                      const isBundle = !!s.bundle_id;
                      let savedSubItems = [];
                      if (s.sub_items_json) { try { savedSubItems = JSON.parse(s.sub_items_json)||[]; } catch(e){} }
                      const subItems = isBundle
                          ? (bundleServiceNamesMap[bndId]||[])
                          : (savedSubItems.length > 0 ? savedSubItems : []);
                      const nameCell = isBundle ? `<strong>${s.service_name}</strong>` : s.service_name;
                      const mainRow = `<tr><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${i+1}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;">${nameCell}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${s.quantity}</td><td class="vjo-price-col" style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(parseFloat(s.service_price||0)+parseFloat(s.labor_cost||0))}</td><td class="vjo-price-col" style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(s.total)}</td></tr>`;
                      const subRows = subItems.map(sub=>`<tr><td style="padding:2px 8px;border-bottom:1px solid #f5f5f5;text-align:center;color:#bbb;">-</td><td style="padding:2px 8px 2px 20px;border-bottom:1px solid #f5f5f5;color:#666;font-size:10px;" colspan="4">- ${sub}</td></tr>`).join('');
                      return mainRow + subRows;
                  }).join('')}
                </tbody>
              </table>` : ''}
              <!-- PRODUCTS table -->
              ${(d.products||[]).length > 0 ? `
              <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;margin-bottom:4px;">PRODUCTS / PARTS</div>
              <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9pt;">
                <colgroup><col style="width:5%"><col><col style="width:8%"><col class="vjo-price-col" style="width:18%"><col class="vjo-price-col" style="width:18%"></colgroup>
                <thead>
                  <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th class="vjo-price-col" style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th class="vjo-price-col" style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  ${(d.products||[]).map((p,i)=>`<tr><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${i+1}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;">${p.product_name}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${p.quantity}</td><td class="vjo-price-col" style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(p.unit_price)}</td><td class="vjo-price-col" style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(p.total)}</td></tr>`).join('')}
                </tbody>
              </table>` : ''}
              ${(d.services||[]).length===0&&(d.products||[]).length===0 ? '<p style="padding:10px;text-align:center;color:#999;">No items recorded</p>' : ''}
              <!-- Summary -->
              <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;" class="vjo-summary-table">
                ${parseFloat(d.discount_amount||0) > 0 ? `<tr>
                  <td colspan="3" style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;">
                    Discount (${getDiscountDisplayLabel(d.discount_type, d.discount_percentage || d.discount_amount || 0)}): <strong>- ${fmt(d.discount_amount)}</strong>
                  </td>
                </tr>` : ''}
                <tr>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:10pt;">${fmt(d.subtotal)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:10pt;">${fmt(d.parts_total)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    TOTAL AMOUNT<br><strong style="font-size:12pt;">${fmt(d.total_amount)}</strong>
                    ${(() => { const paid = d.payment_records && d.payment_records.length > 0 ? d.payment_records.reduce((s,p)=>s+parseFloat(p.amount||0),0) : parseFloat(d.partial_amount||0); const bal = parseFloat(d.total_amount||0) - paid; return bal > 0 && d.payment_status === 'partial' ? `<br><span style="font-size:8pt;color:#000;">Balance: ${fmt(bal)}</span>` : ''; })()}
                  </td>
                </tr>
              </table>

              <!-- Payment Records -->
              ${(d.payment_records && d.payment_records.length > 0) ? `
              <div class="vjo-payment-records" style="margin-top:12px;">
                <div style="font-size:12px;font-weight:600;margin-bottom:6px;color:#000;">Payment Records</div>
                <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
                  <thead class="table-light"><tr>${canEditJoPayments ? '<th style="width:34px;"></th>' : ''}<th>Date</th><th>Method</th><th>Amount</th><th>Reference</th><th>Paid By</th><th>Notes</th></tr></thead>
                  <tbody>
                    ${d.payment_records.map(p => `<tr>
                      ${canEditJoPayments ? `<td style="white-space:nowrap;">
                        <button type="button" class="btn btn-outline-dark btn-sm py-0 px-1" onclick="openEditPaymentModal(${d.id}, '${encodeURIComponent(JSON.stringify(p)).replace(/'/g, "\\'")}')" aria-label="Edit payment"><i class="bi bi-pencil"></i></button>
                      </td>` : ''}
                      <td><small>${new Date(p.payment_date).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</small></td>
                      <td><span class="badge bg-secondary">${(p.payment_method||'').replace(/_/g,' ')}</span></td>
                      <td class="fw-bold text-success">${fmt(p.amount)}</td>
                      <td><small class="text-muted">${p.reference_number||'—'}</small></td>
                      <td><small>${p.paid_by||'—'}</small></td>
                      <td><small class="text-muted">${p.notes||'—'}</small></td>
                    </tr>`).join('')}
                    <tr class="table-light">
                      ${canEditJoPayments ? '<td></td>' : ''}
                      <td colspan="1" class="fw-bold">Total Paid</td>
                      <td class="fw-bold text-success">${fmt(d.payment_records.reduce((s,p)=>s+parseFloat(p.amount||0),0))}</td>
                      <td colspan="4"></td>
                    </tr>
                  </tbody>
                </table>
              </div>` : ''}

              <!-- Technician Time Record -->
              <div class="mb-3" style="font-size:9pt;margin-top:12px;">
                  <small class="text-muted d-block">Technician Time Record</small>
                  ${Array.isArray(d.technicians) && d.technicians.length > 0 ? `
                  <div style="border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;margin-top:4px;">
                      <table style="width:100%;border-collapse:collapse;font-size:11px;">
                          <thead><tr style="background:#f5f5f5;">
                              <th style="padding:4px 8px;border-bottom:1px solid #e0e0e0;">Technician</th>
                              <th style="padding:4px 8px;border-bottom:1px solid #e0e0e0;text-align:center;">Status</th>
                              <th style="padding:4px 8px;border-bottom:1px solid #e0e0e0;text-align:right;">Time Worked</th>
                          </tr></thead>
                          <tbody>
                              ${d.technicians.map((t, ti) => {
                                  const isActive = (t.assignment_status === 'assigned' || t.assignment_status === 'working');
                                  let stBadgeText, stClass;
                                  if (isActive) { stBadgeText = 'Active'; stClass = 'bg-success'; }
                                  else { stBadgeText = 'Inactive'; stClass = 'bg-dark'; }
                                  const sessions = Array.isArray(t.work_sessions) ? t.work_sessions : [];
                                  const hasAct = sessions.length > 0;
                                  const actRows = hasAct ? sessions.map((s, si) => {
                                      const st = s.start_time ? new Date(s.start_time).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '—';
                                      const et = s.end_time ? new Date(s.end_time).toLocaleString('en-PH',{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit'}) : '<span style="color:green;font-size:9px;">Running</span>';
                                      let dur = '—';
                                      if (s.end_time && s.start_time) { const ds=Math.max(0,Math.floor((new Date(s.end_time)-new Date(s.start_time))/1000)); dur=String(Math.floor(ds/3600)).padStart(2,'0')+':'+String(Math.floor((ds%3600)/60)).padStart(2,'0')+':'+String(ds%60).padStart(2,'0'); }
                                      else if (!s.end_time && s.start_time) { const ds=Math.max(0,Math.floor((Date.now()-new Date(s.start_time).getTime())/1000)); dur='<span style="color:green;">'+String(Math.floor(ds/3600)).padStart(2,'0')+':'+String(Math.floor((ds%3600)/60)).padStart(2,'0')+':'+String(ds%60).padStart(2,'0')+'</span>'; }
                                      let idle='';
                                      if(si>0&&sessions[si-1].end_time&&s.start_time){const g=Math.max(0,Math.floor((new Date(s.start_time)-new Date(sessions[si-1].end_time))/1000));if(g>0)idle=String(Math.floor(g/3600)).padStart(2,'0')+':'+String(Math.floor((g%3600)/60)).padStart(2,'0')+':'+String(g%60).padStart(2,'0');}
                                      const nHtml=s.notes?'<div style="font-size:9px;color:#666;margin-top:1px;">'+s.notes+'</div>':'';
                                      return '<tr><td style="padding:2px 6px;border-bottom:1px solid #f0f0f0;">'+(si+1)+'</td><td style="padding:2px 6px;border-bottom:1px solid #f0f0f0;">'+st+'</td><td style="padding:2px 6px;border-bottom:1px solid #f0f0f0;">'+et+nHtml+'</td><td style="padding:2px 6px;border-bottom:1px solid #f0f0f0;font-family:monospace;">'+dur+'</td><td style="padding:2px 6px;border-bottom:1px solid #f0f0f0;font-family:monospace;color:#999;">'+(idle||'—')+'</td></tr>';
                                  }).join('') : '';
                                  return `<tr style="${!isActive?'opacity:0.7;':''}cursor:${hasAct?'pointer':'default'};" ${hasAct?`onclick="var el=document.getElementById('viewJoTechAct_${ti}');el.style.display=el.style.display==='none'?'':'none';"`:''}> 
                                      <td style="padding:4px 8px;border-bottom:1px solid #f0f0f0;font-weight:600;">${t.full_name} <span style="font-weight:400;font-size:9px;color:#888;">${t.is_assist?'(Assistant)':'(Technician)'}</span> ${hasAct?'<i class="bi bi-chevron-down" style="font-size:9px;color:#999;"></i>':''}</td>
                                      <td style="padding:4px 8px;border-bottom:1px solid #f0f0f0;text-align:center;"><span class="badge ${stClass}" style="font-size:10px;">${stBadgeText}</span></td>
                                      <td style="padding:4px 8px;border-bottom:1px solid #f0f0f0;text-align:right;font-family:monospace;">${fmtSeconds(t.total_seconds)}</td>
                                  </tr>${hasAct?`<tr id="viewJoTechAct_${ti}" style="display:none;"><td colspan="3" style="padding:4px 8px 8px;background:#f9f9f9;"><div style="font-size:10px;font-weight:600;margin-bottom:3px;color:#666;">Time Activity Log</div><table style="width:100%;border-collapse:collapse;font-size:10px;"><thead><tr style="background:#eee;"><th style="padding:2px 6px;">#</th><th style="padding:2px 6px;">Start</th><th style="padding:2px 6px;">Stop</th><th style="padding:2px 6px;">Worked</th><th style="padding:2px 6px;">Idle</th></tr></thead><tbody>${actRows}</tbody></table></td></tr>`:''}`;
                              }).join('')}
                          </tbody>
                          <tfoot><tr style="background:#f9f9f9;"><td colspan="2" style="padding:4px 8px;font-weight:600;">Total Work Time (JO)</td><td style="padding:4px 8px;text-align:right;font-family:monospace;font-weight:700;">${fmtSeconds(elapsedSec)}</td></tr></tfoot>
                      </table>
                  </div>` : `<strong>${assignedTechnicians}</strong>`}
              </div>
              <!-- Notes -->
              <div class="d-flex gap-4 mb-3" style="font-size:9pt;flex-wrap:wrap;">
                  ${d.notes ? (() => { const p = joParseNotesAndRecommendations(d.notes||''); return (p.notes ? `<div style="max-width:100%;flex:1 1 260px;"><small class="text-muted d-block">Notes</small><span style="display:block;white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;line-height:1.5;">${p.notes}</span></div>` : '') + (p.recommendations.length > 0 ? `<div style="max-width:100%;flex:1 1 260px;"><small class="text-muted d-block">Recommendations</small><div style="border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;background:#fafafa;">
                      <table style="width:100%;border-collapse:collapse;font-size:9pt;margin:0;">
                          <thead>
                              <tr style="background:#f3f4f6;">
                                  <th style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:left;width:12%;">#</th>
                                  <th style="padding:6px 8px;border-bottom:1px solid #e5e7eb;text-align:left;">Service / Item</th>
                              </tr>
                          </thead>
                          <tbody>
                              ${p.recommendations.map((r, idx) => `
                                  <tr>
                                      <td style="padding:6px 8px;border-bottom:1px solid #f0f0f0;vertical-align:top;text-align:center;font-weight:600;">${idx + 1}</td>
                                      <td style="padding:6px 8px;border-bottom:1px solid #f0f0f0;vertical-align:top;">
                                          <div style="font-weight:600;white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;">${r.name || r}</div>
                                          ${r.subItems && r.subItems.length > 0 ? `<div style="margin-top:4px;padding-left:12px;color:#555;white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;">${r.subItems.map(s => `• ${s}`).join('<br>')}</div>` : ''}
                                      </td>
                                  </tr>
                              `).join('')}
                          </tbody>
                      </table>
                  </div></div>` : ''); })() : ''}
              </div>
              <!-- Status History -->
              <!-- Status & Payment Summary -->
              <div class="d-flex gap-3 mb-3" style="margin-top:12px;">
                <div><small class="text-muted d-block">Status</small><span class="badge bg-${statusBadge[d.status]||'secondary'}">${(d.status||'').replace(/_/g,' ')}</span></div>
                <div><small class="text-muted d-block">Payment</small><span class="badge bg-${payBadge[d.payment_status]||'secondary'}">${d.payment_status||'—'}</span></div>
              </div>
              ${Array.isArray(d.status_history) && d.status_history.length > 0 ? `
              <div class="mb-3" style="font-size:9pt;">
                  <small class="text-muted d-block mb-1">Status History</small>
                  <div style="border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;">
                      <table style="width:100%;border-collapse:collapse;font-size:10px;">
                          <thead><tr style="background:#f5f5f5;"><th style="padding:3px 8px;border-bottom:1px solid #e0e0e0;">#</th><th style="padding:3px 8px;border-bottom:1px solid #e0e0e0;">Status</th><th style="padding:3px 8px;border-bottom:1px solid #e0e0e0;">Changed By</th><th style="padding:3px 8px;border-bottom:1px solid #e0e0e0;">Date & Time</th></tr></thead>
                          <tbody>
                              ${d.status_history.map((sh, i) => `<tr><td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;">${i+1}</td><td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;text-transform:capitalize;font-weight:600;">${(sh.to_status||'—').replace(/_/g,' ')}</td><td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;">${sh.changed_by_name||'System'}</td><td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;">${sh.changed_at ? new Date(sh.changed_at).toLocaleString('en-PH',{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—'}</td></tr>`).join('')}
                          </tbody>
                      </table>
                  </div>
              </div>` : ''}
            </div>`;

            document.getElementById('viewJoPrintBtn').onclick = () => { bootstrap.Modal.getInstance(document.getElementById('viewJobOrderModal')).hide(); openPrintChooser(id); };
            document.getElementById('viewJoEditBtn').onclick  = () => { bootstrap.Modal.getInstance(document.getElementById('viewJobOrderModal')).hide(); editJobOrder(id); };
            const addPaymentButton = document.getElementById('viewJoAddPaymentBtn');
            if (addPaymentButton) {
                addPaymentButton.onclick = () => openAddPaymentModal(id, d.total_amount, d.payment_records);
            }
            // Store for change-technician modal
            _viewingJoId   = id;
            _viewingJoData = d;
            // Hide Edit button and Print button for view-only roles
            document.getElementById('viewJoEditBtn').style.display = hideJoPrices ? 'none' : '';
            document.getElementById('viewJoPrintBtn').style.display = hideJoPrices ? 'none' : '';
            if (addPaymentButton) addPaymentButton.style.display = hideJoPrices ? 'none' : '';
            // Hide price columns for view-only roles via inline style on a wrapper
            if (hideJoPrices) {
                const style = document.createElement('style');
                style.id = 'vjo-tech-hide';
                style.textContent = '.vjo-price-col { display: none !important; } .vjo-summary-table { display: none !important; }';
                document.head.appendChild(style);
                // Also hide payment records section for technicians
                setTimeout(() => {
                    document.querySelectorAll('#viewJoBody .vjo-payment-records').forEach(el => el.style.display = 'none');
                }, 100);
            } else {
                const existing = document.getElementById('vjo-tech-hide');
                if (existing) existing.remove();
            }
        })
        .catch(() => { document.getElementById('viewJoBody').innerHTML = '<p class="text-danger p-3">Failed to load job order.</p>'; });
}

// ── Payment Records ──
let _addPaymentJoId = null;
let _modalHiddenByAddPayment = null;
let _editingInlinePaymentIndex = null;

function _hideParentModalForAddPayment(modalEl) {
    _modalHiddenByAddPayment = null;
    return null;
}

// Restore parent modal (if any) after addPaymentModal is closed
(function bindAddPaymentRestore() {
    const modalEl = document.getElementById('addPaymentModal');
    if (!modalEl) return;
    modalEl.addEventListener('hidden.bs.modal', function() {
        const toRestoreId = _modalHiddenByAddPayment;
        _modalHiddenByAddPayment = null;
        _editingInlinePaymentIndex = null;
        const titleEl = document.getElementById('addPaymentModalLabel');
        const saveBtn = document.getElementById('apSaveBtn');
        if (titleEl) titleEl.innerHTML = '<i class="bi bi-cash me-2"></i>Add Payment';
        if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-check-lg"></i> Save Payment';
        if (toRestoreId) {
            const parentEl = document.getElementById(toRestoreId);
            if (parentEl) {
                try { bootstrap.Modal.getOrCreateInstance(parentEl).show(); } catch (e) { /* ignore */ }
                // ensure stacking recalculated
                setTimeout(() => { try { syncModalStacking(toRestoreId); } catch (e) {} }, 80);
                return;
            }
        }
        // if no parent to restore, still sync stacking
        try { syncModalStacking(); } catch (e) {}
    });
}());

function openAddPaymentModal(joId, totalAmount, paymentRecords) {
    _editingInlinePaymentIndex = null;
    _addPaymentJoId = joId;
    const totalPaid = Array.isArray(paymentRecords) ? paymentRecords.reduce((s,p) => s + parseFloat(p.amount||0), 0) : 0;
    const balance = Math.max(0, parseFloat(totalAmount||0) - totalPaid);
    const modalEl = document.getElementById('addPaymentModal');
    const titleEl = document.getElementById('addPaymentModalLabel');
    const saveBtn = document.getElementById('apSaveBtn');

    document.getElementById('ap_jo_id').value = joId;
    document.getElementById('ap_balance_display').value = '₱' + balance.toFixed(2);
    document.getElementById('ap_amount').value = balance > 0 ? balance.toFixed(2) : '';
    document.getElementById('ap_method').value = 'cash';
    document.getElementById('ap_reference').value = '';
    document.getElementById('ap_paid_by').value = '';
    document.getElementById('ap_notes').value = '';

    if (modalEl && modalEl.parentNode !== document.body) document.body.appendChild(modalEl);
    _showAddPaymentModalAfterParentHide(modalEl);
}

function savePaymentRecord() {
    const joId = document.getElementById('ap_jo_id').value;
    const amount = parseFloat(document.getElementById('ap_amount').value);
    if (!amount || amount <= 0) { showToast('Please enter a valid amount.'); return; }

    // Create mode — add to inline payments list (not saved to DB yet)
    if (joId === '_create_') {
        const paymentData = {
            method: document.getElementById('ap_method').value,
            amount: amount,
            reference: document.getElementById('ap_reference').value.trim(),
            paidBy: document.getElementById('ap_paid_by').value.trim(),
            notes: document.getElementById('ap_notes').value.trim()
        };
        if (_editingInlinePaymentIndex !== null && _editingInlinePaymentIndex >= 0 && joInlinePayments[_editingInlinePaymentIndex]) {
            joInlinePayments[_editingInlinePaymentIndex] = paymentData;
        } else {
            joInlinePayments.push(paymentData);
        }
        _editingInlinePaymentIndex = null;
        bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
        joRenderInlinePayments();
        joUpdatePaymentBalance(getActiveJoTotalAmount());
        return;
    }

    // View mode — save to DB via API
    const now = new Date();
    const pad = n => String(n).padStart(2,'0');
    const payDate = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

    fetch(APP_URL + '/api/job_orders.php?action=add_payment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            csrf_token: csrfToken,
            job_order_id: joId,
            amount,
            payment_method: document.getElementById('ap_method').value,
            reference_number: document.getElementById('ap_reference').value.trim(),
            paid_by: document.getElementById('ap_paid_by').value.trim(),
            notes: document.getElementById('ap_notes').value.trim(),
            payment_date: payDate
        })
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) { showToast('Error: ' + res.message); return; }
        bootstrap.Modal.getInstance(document.getElementById('addPaymentModal')).hide();
        // Wait for modal to fully close before reopening View JO
        setTimeout(() => { viewJobOrder(joId); }, 300);
    })
    .catch(() => showToast('Network error. Please try again.'));
}
</script>

<script>
/* ── Edit JO item picker ── */
let editJoItems = [];

function editJoAddItem(type, id, name, price) {
    const existing = editJoItems.find(i => i.type===type && i.id===id);
    if (existing) { existing.qty++; } else { editJoItems.push({type,id,name,price:parseFloat(price),qty:1}); }
    editJoRenderItems();
    editJoUpdateBilling();
}
function editJoRemoveItem(idx) { editJoItems.splice(idx,1); editJoRenderItems(); editJoUpdateBilling(); }
function editJoChangeQty(idx,val) { const q=parseInt(val); if(q<1){editJoRemoveItem(idx);return;} editJoItems[idx].qty=q; editJoUpdateBilling(); }

function editJoRenderItems() {
    const c = document.getElementById('editJoSelectedItems');
    const badge = document.getElementById('editJoItemCount');
    if (!editJoItems.length) {
        c.innerHTML = '<p class="text-muted text-center small py-3 mb-0" id="editJoEmptyMsg">No items added.</p>';
        badge.textContent = '0'; return;
    }
    badge.textContent = editJoItems.length;
    c.innerHTML = editJoItems.map((item,idx) => {
        const unitPrice = getServiceUnitPrice(item);
        return `
        <div class="px-2 py-2" style="border-bottom:1px solid #f0f0f0;font-size:12px;">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div style="flex:1;min-width:0;">
              <div class="text-truncate fw-semibold">${item.name}</div>
              <div class="d-flex align-items-center gap-2 mt-1">
                <small class="text-muted">Base</small>
                <small class="fw-semibold">₱${parseFloat(item.basePrice || 0).toFixed(2)}</small>
                <small class="text-muted">Labor</small>
                <input type="number" class="form-control form-control-sm text-center" value="${parseFloat(item.labor || 0).toFixed(2)}" min="0" step="0.01" style="width:70px;font-size:11px;" onchange="editJoChangeLabor(${idx},this.value)">
              </div>
            </div>
            <div class="d-flex align-items-center gap-1 ms-2">
              <input type="number" class="form-control form-control-sm text-center" value="${item.qty}" min="1" style="width:46px;font-size:11px;" onchange="editJoChangeQty(${idx},this.value)">
              <span style="min-width:60px;text-align:right;font-weight:600;">₱${(unitPrice*item.qty).toFixed(2)}</span>
              <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="editJoRemoveItem(${idx})"><i class="bi bi-x"></i></button>
            </div>
          </div>
        </div>`;
    }).join('');
}

function editJoChangeLabor(idx, val) {
    const labor = parseFloat(val) || 0;
    editJoItems[idx].labor = labor;
    const base = editJoItems[idx].basePrice !== undefined && editJoItems[idx].basePrice !== null
        ? parseFloat(editJoItems[idx].basePrice)
        : 0;
    editJoItems[idx].price = base + labor;
    editJoRenderItems();
    editJoUpdateBilling();
}

function editJoUpdateBilling() {
    const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
    const subtotal = editJoItems.reduce((s,i)=>s+getServiceLineTotal(i),0);
    document.getElementById('editJoSubtotal').textContent = fmt(subtotal);
    const parts = parseFloat(document.getElementById('editJoPartsCost').textContent.replace(/[₱,]/g,''))||0;
    document.getElementById('editJoTotal').textContent = fmt(subtotal + parts);
    editJoCalcPartial();
}

function editJoTogglePartial() {
    const status = document.getElementById('editJoPayStatus').value;
    const row    = document.getElementById('editJoPartialRow');
    row.style.display = status === 'partial' ? 'block' : 'none';
    if (status !== 'partial') {
        document.getElementById('editJoPartialAmount').value = '0';
        document.getElementById('editJoRemainingBalance').textContent = '₱0.00';
    } else {
        editJoCalcPartial();
    }
}

function editJoCalcPartial() {
    const status = document.getElementById('editJoPayStatus').value;
    if (status !== 'partial') return;
    const totalText = document.getElementById('editJoTotal').textContent.replace(/[₱,]/g,'');
    const total     = parseFloat(totalText) || 0;
    const paid      = parseFloat(document.getElementById('editJoPartialAmount').value) || 0;
    const remaining = Math.max(0, total - paid);
    const fmt = v => '₱' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
    document.getElementById('editJoRemainingBalance').textContent = fmt(remaining);
}

function editJobOrder(id) {
    fetch(APP_URL + '/api/job_orders.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast(res.message); return; }
            const d   = res.data;
            joEditingId = d.id;
            joEditingTechnicians = Array.isArray(d.technicians) ? d.technicians : [];
            joEditingStatus = d.status || 'pending';
            joEditingVersion = d.updated_at || null;
            joEditingJobNumber = d.job_order_number || null;

            document.getElementById('jo_customer_name').value    = d.customer_name     || '';
            document.getElementById('jo_customer_phone').value   = d.customer_phone    || '';
            document.getElementById('jo_customer_email').value   = d.customer_email    || '';
            document.getElementById('jo_customer_address').value = d.customer_address  || '';
            document.getElementById('jo_vehicle_make').value     = d.vehicle_make      || '';
            document.getElementById('jo_vehicle_model').value    = d.vehicle_model     || '';
            document.getElementById('jo_vehicle_year').value     = d.vehicle_year      || '';
            document.getElementById('jo_vehicle_plate').value    = d.vehicle_license   || '';
            document.getElementById('jo_vehicle_color').value    = d.vehicle_color     || '';
            document.getElementById('jo_vehicle_mileage').value  = d.vehicle_mileage   || '';
            document.getElementById('jo_payment_method').value   = d.payment_method    || 'cash';
            document.getElementById('jo_payment_status').value   = d.payment_status    || 'pending';
            applyJoPaymentStatusEditRule(d.payment_status || 'pending');

            // Load existing payment records into inline payments list
            joInlinePayments = (Array.isArray(d.payment_records) && d.payment_records.length > 0) ? d.payment_records.map(p => ({
                method: p.payment_method || 'cash',
                amount: parseFloat(p.amount || 0),
                reference: p.reference_number || '',
                paidBy: p.paid_by || '',
                notes: p.notes || ''
            })) : [];
            joRenderInlinePayments();
            document.getElementById('jo_notes').value            = d.notes             || '';
            const parsedNotesRec = joParseNotesAndRecommendations(d.notes || '');
            document.getElementById('jo_notes').value = parsedNotesRec.notes;
            joRecommendations = parsedNotesRec.recommendations;
            joRenderRecommendations();
            const savedDiscountType = normalizeSavedDiscountType(d.discount_type, d.discount_percentage || 0);
            document.getElementById('jo_discount_type').value = savedDiscountType;
            document.getElementById('jo_discount_value').value = savedDiscountType === 'percentage' ? (d.discount_percentage || 0) : ((savedDiscountType === 'fixed' || savedDiscountType === 'none') ? (parseFloat(d.discount_amount || 0) || 0) : 0);
            const statusAliases = {
                for_approval: 'under_inspection',
                return_for_revision: 'returned_for_revision'
            };
            const normalizedStatus = statusAliases[d.status] || d.status || 'pending';
            const selectedIds = Array.isArray(d.technician_ids) && d.technician_ids.length
                ? d.technician_ids.map((v) => parseInt(v, 10))
                : (d.service_adviser_id ? [parseInt(d.service_adviser_id, 10)] : []);
            // Build a map of technician status from d.technicians
            const techStatusMap = {};
            if (Array.isArray(d.technicians)) {
                d.technicians.forEach(t => { techStatusMap[parseInt(t.id,10)] = t.assignment_status; });
            }
            window._joTechStatusMap = techStatusMap;

            // Split into main and assist using is_assist flag
            // For released JOs, include 'completed' technicians so they remain visible/checked
            const joIsReleased = (d.status === 'released');
            const activeTechs = Array.isArray(d.technicians)
                ? d.technicians.filter(t => ['assigned','working'].includes(t.assignment_status) || (joIsReleased && t.assignment_status === 'completed'))
                : [];
            _joMainTechIds  = activeTechs.filter(t => !t.is_assist).map(t => parseInt(t.id,10));
            _joAssistTechIds = activeTechs.filter(t => !!t.is_assist).map(t => parseInt(t.id,10));
            // Fallback: if no main found, use technician_ids
            if (_joMainTechIds.length === 0 && Array.isArray(d.technician_ids) && d.technician_ids.length) {
                _joMainTechIds = [parseInt(d.technician_ids[0], 10)];
                _joAssistTechIds = d.technician_ids.slice(1).map(v => parseInt(v,10));
            }
            joSyncCheckboxes();
            joUpdateTechnicianIndicator();

            // Legacy partial row (no longer visible but keep value for API compat)
            const partialRowEdit = document.getElementById('joPartialRow');
            if (partialRowEdit) partialRowEdit.style.display = 'none';
            document.getElementById('jo_partial_amount').value = d.partial_amount || 0;

            joItems = (d.services || []).map(s => {
                let savedSubItems = [];
                if (s.sub_items_json) { try { savedSubItems = JSON.parse(s.sub_items_json)||[]; } catch(e){} }
                return {
                    type: s.bundle_id ? 'bundle' : (s.service_id ? 'service' : 'custom'),
                    id: s.bundle_id ? s.bundle_id : (s.service_id || 0),
                    name: s.service_name,
                    basePrice: parseFloat(s.service_price || 0),
                    labor: parseFloat(s.labor_cost || 0),
                    price: parseFloat((s.service_price || 0) + (s.labor_cost || 0)),
                    qty: parseInt(s.quantity || 1, 10),
                    selectedSubItems: savedSubItems
                };
            });

            joProducts = (d.products || []).map(p => ({
                id: p.id ? parseInt(p.id, 10) : 0,
                name: p.product_name || '',
                code: p.product_code || '',
                price: parseFloat(p.price || p.unit_price || 0),
                qty: parseInt(p.qty || p.quantity || 1, 10)
            }));

            joSetMode(true, d.job_order_number || '');
            joRenderItems();
            joRenderProducts();
            joCalc();

            // Show technician time history in the Technicians card
            const techHistoryEl = document.getElementById('joTechHistory');
            if (techHistoryEl && Array.isArray(d.technicians) && d.technicians.length > 0) {
                const hasPrev = d.technicians.some(t => t.assignment_status === 'removed' || t.assignment_status === 'completed' || parseFloat(t.total_seconds) > 0);
                if (hasPrev) {
                    techHistoryEl.innerHTML = `
                    <div style="margin-top:8px;border:1px solid #e0e0e0;border-radius:6px;overflow:hidden;">
                        <div style="background:#f5f5f5;padding:4px 8px;font-size:11px;font-weight:600;border-bottom:1px solid #e0e0e0;">Time Record</div>
                        <table style="width:100%;border-collapse:collapse;font-size:11px;">
                            ${d.technicians.map(t => {
                                const isActive = (t.assignment_status === 'assigned' || t.assignment_status === 'working');
                                const isCompleted = (t.assignment_status === 'completed');
                                const sec = parseInt(t.total_seconds||0, 10);
                                let badge, bClass;
                                if (isActive) { badge = 'Active'; bClass='bg-success'; }
                                else if (isCompleted) { badge = 'Completed'; bClass='bg-primary'; }
                                else { badge='Inactive'; bClass='bg-dark'; }
                                const roleLabel = t.is_assist ? '(Assistant)' : '(Technician)';
                                return `<tr style="${(!isActive && !isCompleted)?'opacity:0.65;':''}">
                                    <td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;font-weight:600;">${t.full_name} <span style="font-weight:400;font-size:9px;color:#888;">${roleLabel}</span></td>
                                    <td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;text-align:center;"><span class="badge ${bClass}" style="font-size:9px;">${badge}</span></td>
                                    <td style="padding:3px 8px;border-bottom:1px solid #f0f0f0;text-align:right;font-family:monospace;">${fmtSeconds(sec)}</td>
                                </tr>`;
                            }).join('')}
                        </table>
                    </div>`;
                    techHistoryEl.style.display = '';
                } else {
                    techHistoryEl.style.display = 'none';
                }
            } else if (techHistoryEl) {
                techHistoryEl.style.display = 'none';
            }

            bootstrap.Modal.getOrCreateInstance(document.getElementById('createJobOrderModal')).show();
            joLoadTechnicianAvailability();

        })
        .catch((err) => { console.error('editJobOrder error:', err); showToast('Failed to load job order.'); });
}

document.addEventListener('DOMContentLoaded', function () {
    joUpdateTechnicianIndicator();

    const fixModalBackdrops = () => {
        setTimeout(() => {
            document.querySelectorAll('.modal-backdrop').forEach((el, i) => {
                el.style.zIndex = 1050 + (i * 5);
            });
        }, 50);
    };

    // When joSubItemSelectModal is dismissed without confirming (Cancel/close),
    // restore the JO confirm button and clear any pending JE checkbox
    const subItemModal = document.getElementById('joSubItemSelectModal');
    if (subItemModal) {
        subItemModal.addEventListener('hidden.bs.modal', function() {
            bindJoSubItemConfirmDefault();
            _pendingJoItem = null;
            _pendingRecItem = null;
            document.getElementById('joSubItemSelectList')?.replaceChildren();
        });
        subItemModal.addEventListener('shown.bs.modal', function() {
            syncModalStacking('joSubItemSelectModal');
        });
    }

    const recBrowseModal = document.getElementById('recBrowseModal');
    if (recBrowseModal) {
        recBrowseModal.addEventListener('shown.bs.modal', function() {
            syncModalStacking('recBrowseModal');
        });
    }

    const createJoModal = document.getElementById('createJobOrderModal');
    if (createJoModal) {
        createJoModal.addEventListener('shown.bs.modal', function() {
            syncModalStacking('createJobOrderModal');
        });
    }
});
</script>

<script>
function saveEditJobOrder() {
    const id = document.getElementById('editJoId').value;
    if (!document.getElementById('editJoCustomerName').value.trim())  { showToast('Customer name is required.'); return; }
    if (!document.getElementById('editJoCustomerPhone').value.trim()) { showToast('Customer phone is required.'); return; }

    const payload = {
        csrf_token:       csrfToken,
        customer_name:    document.getElementById('editJoCustomerName').value.trim(),
        customer_phone:   document.getElementById('editJoCustomerPhone').value.trim(),
        customer_email:   document.getElementById('editJoCustomerEmail').value.trim(),
        customer_address: document.getElementById('editJoCustomerAddress').value.trim(),
        vehicle_make:     document.getElementById('editJoMake').value.trim(),
        vehicle_model:    document.getElementById('editJoModel').value.trim(),
        vehicle_year:     document.getElementById('editJoYear').value.trim(),
        vehicle_license:  document.getElementById('editJoPlate').value.trim(),
        vehicle_color:    document.getElementById('editJoColor').value.trim(),
        vehicle_mileage:  document.getElementById('editJoMileage').value.trim(),
        status:           joEditingStatus || 'pending',
        payment_method:   document.getElementById('editJoPayMethod').value,
        payment_status:   document.getElementById('editJoPayStatus').value,
        partial_amount:   parseFloat(document.getElementById('editJoPartialAmount').value) || 0,
        notes:            document.getElementById('editJoNotes').value.trim(),
        items:            editJoItems.map(item => ({
            type: item.type,
            id: item.id,
            name: item.name,
            base_price: item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice) : 0,
            labor_cost: parseFloat(item.labor || 0),
            price: (item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice) : 0) + parseFloat(item.labor || 0),
            qty: parseInt(item.qty || 1),
            selectedSubItems: Array.isArray(item.selectedSubItems) ? item.selectedSubItems : []
        })),
    };
    fetch(APP_URL + '/api/job_orders.php?id=' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editJobOrderModal')).hide();
            location.reload();
        } else { showToast('Error: ' + data.message); }
    })
    .catch(() => showToast('Network error.'));
}
</script>

<script>
async function printJobOrder(id) {
    await refreshPrintTemplateSettings();
    return fetch(APP_URL + '/api/job_orders.php?id=' + id)
        .then(r => r.json())
        .then(async (res) => {
            if (!res.success) { showToast(res.message); return; }
            const d    = res.data;
            const fmt  = n => '₱' + parseFloat(n||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const joDate = d.created_at
                ? new Date(d.created_at).toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'})
                : new Date().toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'});

            const subtotal   = parseFloat(d.subtotal   || 0);
            const partsTotal = parseFloat(d.parts_total || 0);
            const discAmt    = parseFloat(d.discount_amount || 0);
            const total      = parseFloat(d.total_amount || 0);
            const partialAmt = parseFloat(d.partial_amount || 0);
            const remaining  = d.payment_status === 'partial' ? Math.max(0, total - partialAmt) : 0;
            const assignedTechnician = d.assigned_technician_name || 'Unassigned';
            // Parse notes and recommendations from stored notes field
            const parsedNR     = joParseNotesAndRecommendations(d.notes || '');
            const printedNotes = parsedNR.notes;
            const printedRecs  = parsedNR.recommendations;

            let serviceRows = '';
            let pjRowNum = 1;
            (d.services||[]).forEach((s) => {
                const sBase  = parseFloat(s.service_price || 0);
                const sLabor = parseFloat(s.labor_cost || 0);
                const isBundle = !!s.bundle_id;
                const svcId = parseInt(s.service_id) || 0;
                // Use saved sub-items if available, otherwise fall back to map
                let savedSubItems = [];
                if (s.sub_items_json) {
                    try { savedSubItems = JSON.parse(s.sub_items_json) || []; } catch(e) {}
                }
                const subSvcs = isBundle
                    ? (bundleServiceNamesMap[parseInt(s.bundle_id)] || [])
                    : (savedSubItems.length > 0 ? savedSubItems : []);
                const nameCell = isBundle ? `<strong>${s.service_name}</strong>` : s.service_name;
                serviceRows += `
                <tr${isBundle ? ' style="background:#f8f8f8;"' : ''}>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${pjRowNum++}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${nameCell}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${s.quantity}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(sBase)}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(sLabor)}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(parseFloat(s.total||0))}</td>
                </tr>`;
                subSvcs.forEach(svcName => {
                    serviceRows += `
                <tr>
                    <td style="padding:2px 8px;border:1px solid #ccc;text-align:center;color:#888;">-</td>
                    <td style="padding:2px 8px 2px 20px;border:1px solid #ccc;color:#555;font-size:8.5pt;word-break:break-word;">${svcName}</td>
                    <td colspan="4" style="padding:2px 8px;border:1px solid #ccc;"></td>
                </tr>`;
                });
            });
            let productRows = '';
            (d.products||[]).forEach((p, i) => {
                const isPkg = parseFloat(p.unit_price||0)===0 && parseFloat(p.total||0)===0;
                productRows += `
                <tr>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${p.product_name}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.quantity}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.unit_price)}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(parseFloat(p.total||0))}</td>
                </tr>`;
            });

            const discLabel = getDiscountDisplayLabel(d.discount_type, d.discount_percentage || d.discount_amount || 0);

            document.getElementById('joPrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">

        ${getPrintHeaderHtml('JOB ORDER', d.job_order_number || '—', joDate)}

        <!-- Customer & Vehicle -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:6px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">CUSTOMER</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #ddd;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${d.customer_name||'—'}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.customer_phone||'—'}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Email</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.customer_email||'—'}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${d.customer_address||'—'}</td></tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:6px;border-left:1px solid #ddd;">
                    <table style="width:100%;border-collapse:collapse;padding-left:6px;">
                        <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">VEHICLE</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #ddd;">Make / Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Year</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.vehicle_year||'—'}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.vehicle_license||'—'}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Color</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.vehicle_color||'—'}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${d.vehicle_mileage||'—'} km</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Services -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:4%"><col><col style="width:7%">
                <col style="width:13%"><col style="width:13%"><col style="width:14%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Labor</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${serviceRows || '<tr><td colspan="6" style="padding:10px;text-align:center;color:#999;">No services recorded</td></tr>'}</tbody>
        </table>
        ${productRows ? `
        <!-- Products / Parts -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding:6px 0 3px;">PRODUCTS / PARTS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${productRows}</tbody>
        </table>` : ''}

        <!-- Summary -->
        <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
            ${discAmt > 0 ? `<tr>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;">Discount (${discLabel})</td>
                <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;text-align:right;">- ${fmt(discAmt)}</td>
            </tr>` : ''}
            <tr>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(subtotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(partsTotal)}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    TOTAL AMOUNT<br><strong style="font-size:11pt;color:#000;">${fmt(total)}</strong>
                    ${d.payment_status==='partial' ? `<br><span style="font-size:8pt;color:#555;">Paid: ${fmt(partialAmt)}</span><br><span style="font-size:8pt;color:#000;font-weight:700;">Balance: ${fmt(remaining)}</span>` : ''}
                </td>
            </tr>
        </table>

        <!-- Balance only if partial -->
        ${(() => {
            const totalPaid = Array.isArray(d.payment_records) ? d.payment_records.reduce((s,p)=>s+parseFloat(p.amount||0),0) : parseFloat(d.partial_amount||0);
            const balance = Math.max(0, parseFloat(d.total_amount||0) - totalPaid);
            return '';
        })()}

        <!-- Terms & Conditions -->
        <div style="margin-top:12px;font-size:8.5pt;border-top:1px solid #ddd;padding-top:6px;">
            <strong style="font-size:8.5pt;letter-spacing:.4px;">TERMS AND CONDITIONS:</strong>
            <div style="margin-top:3px;color:#333;white-space:pre-wrap;">${printTemplateSettings.terms_conditions || 'All services rendered are subject to warranty as per company policy.'}</div>
        </div>

        <!-- Technician + Signatures pinned to bottom -->
        <div style="margin-top:20px;">
            ${printedRecs.length > 0 ? `
            <div style="font-size:8.5pt;margin-bottom:8px;">
                <div style="font-weight:700;letter-spacing:.5px;padding-bottom:3px;font-size:8.5pt;">RECOMMENDATIONS</div>
                <table style="width:100%;border-collapse:collapse;font-size:8.5pt;">
                    <thead><tr style="background:#f0f0f0;">
                        <th style="padding:3px 6px;border:1px solid #ccc;width:5%;text-align:center;">#</th>
                        <th style="padding:3px 6px;border:1px solid #ccc;text-align:left;">Service / Item</th>
                    </tr></thead>
                    <tbody>${(() => {
                        let rows = '';
                        printedRecs.forEach((r, i) => {
                            rows += `<tr><td style="padding:3px 6px;border:1px solid #ccc;text-align:center;vertical-align:top;">${i+1}</td><td style="padding:3px 6px;border:1px solid #ccc;font-weight:600;">${r.name||r}</td></tr>`;
                            if (r.subItems && r.subItems.length > 0) {
                                r.subItems.forEach(s => {
                                    rows += `<tr><td style="padding:2px 6px;border:1px solid #ddd;text-align:center;color:#aaa;">-</td><td style="padding:2px 6px 2px 20px;border:1px solid #ddd;color:#555;font-size:8pt;">${s}</td></tr>`;
                                });
                            }
                        });
                        return rows;
                    })()}</tbody>
                </table>
            </div>` : ''}
            <div style="font-size:9pt;margin-bottom:10px;padding-top:6px;">
                <strong>Assigned Technician:</strong> ${assignedTechnician}
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <tr>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Authorized Signature</div>
                    </td>
                    <td style="width:50%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Customer Signature</div>
                    </td>
                </tr>
            </table>
            ${getPrintFooterHtml()}
        </div>
    </div>`;

            document.getElementById('joPrintArea').style.display = 'block';
            await waitForPrintAssets('joPrintArea');
            const pdfResult = printWithPdfName(d.vehicle_license || d.plate_number || '', d.customer_name || '');
            if (!_pdfMode) document.getElementById('joPrintArea').style.display = 'none';
            if (pdfResult && typeof pdfResult.then === 'function') {
                return pdfResult.then(() => true);
            }
            return true;
        })
        .catch(() => { showToast('Failed to load job order for printing.'); return false; });
}

// ── Print Chooser (Regular vs Technician copy) ──
let _printChooserJoId = null;

function openPrintChooser(id) {
    _printChooserJoId = id;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('printChooserModal')).show();
}

function choosePrintRegular() {
    const id = _printChooserJoId;
    bootstrap.Modal.getInstance(document.getElementById('printChooserModal')).hide();
    if (id) printJobOrder(id);
}

function choosePrintTechnician() {
    bootstrap.Modal.getInstance(document.getElementById('printChooserModal')).hide();
    executeTechPrint();
}

async function executeTechPrint() {
    if (!_printChooserJoId) return;

    await refreshPrintTemplateSettings();
    const res = await fetch(APP_URL + '/api/job_orders.php?id=' + _printChooserJoId).then(r => r.json());
    if (!res.success) { showToast(res.message); return; }
    const d = res.data;
    const joDate = d.created_at
        ? new Date(d.created_at).toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'})
        : new Date().toLocaleDateString('en-PH', {year:'numeric',month:'long',day:'numeric'});
    const assignedTechnician = d.assigned_technician_name || 'Unassigned';
    const primaryTech = Array.isArray(d.technicians) && d.technicians.length
        ? (d.technicians.find(t => !['removed'].includes(t.assignment_status)) || d.technicians[0])
        : null;
    const formatPrintDateTime = (value) => {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    };
    const techTimeValue = (value) => value ? String(value) : '';
    const parsedNR = joParseNotesAndRecommendations(d.notes || '');
    const printedNotes = parsedNR.notes || '';
    const printedRecs = parsedNR.recommendations || [];

    // Build service rows WITHOUT prices
    let serviceRows = '';
    let pjRowNum = 1;
    (d.services||[]).forEach((s) => {
        const isBundle = !!s.bundle_id;
        const svcId = parseInt(s.service_id) || 0;
        let savedSubItems = [];
        if (s.sub_items_json) { try { savedSubItems = JSON.parse(s.sub_items_json)||[]; } catch(e){} }
        const subSvcs = isBundle
            ? (bundleServiceNamesMap[parseInt(s.bundle_id)] || [])
            : (savedSubItems.length > 0 ? savedSubItems : []);
        const nameCell = isBundle ? `<strong>${s.service_name}</strong>` : s.service_name;
        serviceRows += `
        <tr${isBundle ? ' style="background:#f8f8f8;"' : ''}>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${pjRowNum++}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${nameCell}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${s.quantity}</td>
        </tr>`;
        subSvcs.forEach(svcName => {
            serviceRows += `
        <tr>
            <td style="padding:2px 8px;border:1px solid #ccc;text-align:center;color:#888;">-</td>
            <td style="padding:2px 8px 2px 20px;border:1px solid #ccc;color:#555;font-size:8.5pt;word-break:break-word;">${svcName}</td>
            <td style="padding:2px 8px;border:1px solid #ccc;"></td>
        </tr>`;
        });
    });

    let productRows = '';
    (d.products||[]).forEach((p, i) => {
        productRows += `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${p.product_name}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.quantity}</td>
        </tr>`;
    });

    document.getElementById('joPrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">

        ${getPrintHeaderHtml('JOB ORDER', d.job_order_number || '—', joDate)}

        <!-- Customer & Vehicle -->
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="width:50%;vertical-align:top;padding-right:6px;">
                    <table style="width:100%;border-collapse:collapse;">
                        <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">CUSTOMER</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #ddd;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${d.customer_name||'—'}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.customer_phone||'—'}</td></tr>
                        <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${d.customer_address||'—'}</td></tr>
                    </table>
                </td>
                <td style="width:50%;vertical-align:top;padding-left:6px;border-left:1px solid #ddd;">
                    <table style="width:100%;border-collapse:collapse;padding-left:6px;">
                        <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">VEHICLE</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #ddd;">Make / Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.vehicle_license||'—'}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Color</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${d.vehicle_color||'—'}</td></tr>
                        <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${d.vehicle_mileage||'—'} km</td></tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Services (no price columns) -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup><col style="width:6%"><col><col style="width:10%"></colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                </tr>
            </thead>
            <tbody>${serviceRows || '<tr><td colspan="3" style="padding:10px;text-align:center;color:#999;">No services recorded</td></tr>'}</tbody>
        </table>
        ${productRows ? `
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding:6px 0 3px;">PRODUCTS / PARTS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup><col style="width:6%"><col><col style="width:10%"></colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                </tr>
            </thead>
            <tbody>${productRows}</tbody>
        </table>` : ''}

        <!-- Technician Info -->
        <div style="margin-top:12px;font-size:9pt;">
            <strong>Assigned Technician:</strong> ${assignedTechnician}
        </div>

        <table style="width:100%;margin-top:12px;border-collapse:collapse;font-size:8.5pt;">
            <tr>
                <td style="width:33%;padding:0 8px 0 0;vertical-align:top;">
                    <div style="font-weight:700;margin-bottom:4px;">Time Received</div>
                    <input type="text" value="" style="width:100%;border:none;border-bottom:1px solid #333;background:transparent;padding:2px 0;font-size:8.5pt;box-sizing:border-box;outline:none;">
                </td>
                <td style="width:33%;padding:0 8px;vertical-align:top;">
                    <div style="font-weight:700;margin-bottom:4px;">Time Started</div>
                    <input type="text" value="" style="width:100%;border:none;border-bottom:1px solid #333;background:transparent;padding:2px 0;font-size:8.5pt;box-sizing:border-box;outline:none;">
                </td>
                <td style="width:33%;padding:0 0 0 8px;vertical-align:top;">
                    <div style="font-weight:700;margin-bottom:4px;">Time Finished</div>
                    <input type="text" value="" style="width:100%;border:none;border-bottom:1px solid #333;background:transparent;padding:2px 0;font-size:8.5pt;box-sizing:border-box;outline:none;">
                </td>
            </tr>
        </table>

        <!-- Technician Notes -->
        <div style="margin-top:14px;border:1.5px solid #333;border-radius:4px;padding:8px 12px;min-height:150px;">
            <strong style="font-size:8pt;letter-spacing:.4px;">TECHNICIAN NOTES:</strong>
            ${printedNotes ? `
                <div style="margin-top:6px;font-size:8pt;white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere;color:#111;min-height:100px;line-height:1.4;">${printedNotes}</div>
            ` : ''}
        </div>

        ${getPrintFooterHtml()}
    </div>`;
    document.getElementById('joPrintContent').dataset.pdfFilename = buildPdfFileName(d.vehicle_license || d.plate_number || '', d.customer_name || '', '_JO') + '.pdf';

    document.getElementById('joPrintArea').style.display = 'block';
    await waitForPrintAssets('joPrintArea');
    const pdfResult = printWithPdfName(d.vehicle_license || '', d.customer_name || '');
    if (!_pdfMode) document.getElementById('joPrintArea').style.display = 'none';
    return pdfResult && typeof pdfResult.then === 'function'
        ? pdfResult.then(() => true)
        : Promise.resolve(true);
}
</script>

<script>
/* ═══════════════════════════════════════════
   ESTIMATE — VIEW / EDIT / PRINT
═══════════════════════════════════════════ */
function viewEstimate(id) {
    document.getElementById('viewEstBody').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('viewEstimateModal')).show();

    fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { document.getElementById('viewEstBody').innerHTML = '<p class="text-danger p-3">'+res.message+'</p>'; return; }
            const d   = res.data;
            const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
            let services = [], products = [], recommendations = [];
            try { services = JSON.parse(d.services_json||'[]'); } catch(e){}
            try { products = JSON.parse(d.products_json||'[]'); } catch(e){}
            try { recommendations = JSON.parse(d.recommendations_json||'[]'); } catch(e){}

            // Compute discount
            const discType = d.discount_type || 'none';
            const discVal = parseFloat(d.discount_value || 0);
            const svcTotal = parseFloat(d.services_total || 0);
            const prdTotal = parseFloat(d.products_total || 0);
            const base = svcTotal + prdTotal;
            let discountAmt = 0;
            if (discType === 'percentage') discountAmt = base * (discVal / 100);
            else if (discType === 'fixed') discountAmt = discVal;
            else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;
            discountAmt = Math.min(discountAmt, base);
            const total = Math.max(0, base - discountAmt);

            let discLabel = '';
            if (discType === 'senior') discLabel = 'Senior Citizen (20%)';
            else if (discType === 'pwd') discLabel = 'PWD (20%)';
            else if (discType === 'percentage') discLabel = `Percentage (${discVal}%)`;
            else if (discType === 'fixed') discLabel = 'Fixed Amount';

            let svcRows = services.map((s,i) => {
                const isBundle = s.type === 'bundle';
                const svcId = parseInt(s.id||0, 10);
                const bndId = parseInt(s.id||0, 10);
                let savedSubItems = [];
                if (s.selectedSubItems && s.selectedSubItems.length > 0) savedSubItems = s.selectedSubItems;
                const subItems = isBundle
                    ? (bundleServiceNamesMap[bndId]||[])
                    : (savedSubItems.length > 0 ? savedSubItems : []);
                const nameCell = isBundle ? `<strong>${s.name}</strong>` : s.name;
                const baseP = parseFloat(s.base_price ?? s.price ?? 0);
                const labor = parseFloat(s.labor_cost ?? s.labor ?? 0);
                const qty = parseInt(s.qty||1, 10);
                const lineTotal = (baseP + labor) * qty;
                const mainRow = `<tr><td>${i+1}</td><td>${nameCell}</td><td class="text-center">${qty}</td><td class="text-end">${fmt(baseP)}</td><td class="text-end">${fmt(labor)}</td><td class="text-end">${fmt(lineTotal)}</td></tr>`;
                const subRows = subItems.map(sub=>`<tr><td style="color:#bbb;">-</td><td style="padding-left:16px;color:#666;font-size:11px;" colspan="5">- ${sub}</td></tr>`).join('');
                return mainRow + subRows;
            }).join('');
            let prdRows = products.map((p,i) => `<tr><td>${i+1}</td><td>${p.name}</td><td class="text-center">${p.qty||1}</td><td class="text-end">${fmt(p.price)}</td><td class="text-end">${fmt(parseFloat(p.price||0) * parseInt(p.qty||1,10))}</td></tr>`).join('');

            let recsHtml = '';
            if (Array.isArray(recommendations) && recommendations.length > 0) {
                recsHtml = `<p class="fw-bold mb-1 mt-3" style="font-size:12px;letter-spacing:.5px;">RECOMMENDATIONS</p>
                <ul class="list-unstyled small mb-3">${recommendations.map(r => `<li>• <strong>${r.name||r}</strong>${r.subItems && r.subItems.length > 0 ? ' ('+r.subItems.join(', ')+')' : ''}</li>`).join('')}</ul>`;
            }

            document.getElementById('viewEstBody').innerHTML = `
            <div style="font-family:Arial,sans-serif;font-size:10pt;color:#000;padding:10px;">
                ${getPrintHeaderHtml('JOB ESTIMATE', d.estimate_number || '—', d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—')}

                <!-- Customer & Vehicle -->
                <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
                    <tr>
                        <td style="width:50%;vertical-align:top;padding-right:8px;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;border-bottom:1px solid #333;letter-spacing:.5px;">CUSTOMER</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #eee;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #eee;">${d.customer_name||'—'}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #eee;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.customer_phone||'—'}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #eee;">Email</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.customer_email||'—'}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${d.customer_address||'—'}</td></tr>
                            </table>
                        </td>
                        <td style="width:50%;vertical-align:top;padding-left:8px;border-left:1px solid #ddd;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;border-bottom:1px solid #333;letter-spacing:.5px;">VEHICLE</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #eee;">Make/Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #eee;">${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Year</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_year||'—'}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_plate||'—'}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #eee;">Color</td><td style="padding:3px 0;border-bottom:1px solid #eee;">${d.vehicle_color||'—'}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${d.vehicle_mileage||'—'} km</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Status -->
                <div class="d-flex gap-3 mb-3">
                    <div><small class="text-muted d-block">Status</small><span class="badge bg-${d.status==='converted'?'success':'secondary'}">${d.status||'draft'}</span></div>
                    <div><small class="text-muted d-block">Date</small><span>${d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—'}</span></div>
                </div>

                ${d.notes ? `<div class="mb-3"><small class="text-muted d-block">Notes</small><span>${d.notes}</span></div>` : ''}
                ${recsHtml}

                ${services.length > 0 ? `
                <p class="fw-bold mb-1" style="font-size:12px;letter-spacing:.5px;">SERVICES</p>
                <table class="table table-sm table-bordered mb-3" style="font-size:12px;">
                    <thead class="table-light"><tr><th>#</th><th>Service</th><th class="text-center">Qty</th><th class="text-end">Price</th><th class="text-end">Labor</th><th class="text-end">Total</th></tr></thead>
                    <tbody>${svcRows}</tbody>
                </table>` : ''}
                ${products.length > 0 ? `
                <p class="fw-bold mb-1" style="font-size:12px;letter-spacing:.5px;">PRODUCTS / PARTS</p>
                <table class="table table-sm table-bordered mb-3" style="font-size:12px;">
                    <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-center">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                    <tbody>${prdRows}</tbody>
                </table>` : ''}
                ${services.length===0&&products.length===0 ? '<p class="text-center text-muted">No items recorded</p>' : ''}

                <!-- Summary with discount -->
                <div class="d-flex justify-content-end gap-4 pt-2" style="border-top:1.5px solid #333;">
                    <div><small class="text-muted">Services</small><br><strong>${fmt(svcTotal)}</strong></div>
                    <div><small class="text-muted">Products</small><br><strong>${fmt(prdTotal)}</strong></div>
                    ${discountAmt > 0 ? `<div><small class="text-muted">Discount (${discLabel})</small><br><strong class="text-danger">-${fmt(discountAmt)}</strong></div>` : ''}
                    <div><small class="text-muted">Total Amount</small><br><strong class="fs-5">${fmt(total)}</strong></div>
                </div>
            </div>`;
            document.getElementById('viewEstPrintBtn').onclick   = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); printEstimate(id); };
            document.getElementById('viewEstSavePdfBtn').onclick = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); _pdfMode = true; printEstimate(id).then(() => { _pdfMode = false; }).catch(() => { _pdfMode = false; }); };
            document.getElementById('viewEstEditBtn').onclick    = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); editEstimate(id); };
            document.getElementById('viewEstConvertBtn').onclick = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); convertEstimateToJo(d); };
        })
        .catch(() => { document.getElementById('viewEstBody').innerHTML = '<p class="text-danger p-3">Failed to load estimate.</p>'; });
}

let _editEstimateId = null;

function editEstimate(id) {
    fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { showToast(res.message); return; }
            const d = res.data;
            _editEstimateId = d.id;

            // Populate customer fields
            document.getElementById('je_customer_name').value    = d.customer_name    || '';
            document.getElementById('je_customer_phone').value   = d.customer_phone   || '';
            document.getElementById('je_customer_email').value   = d.customer_email   || '';
            document.getElementById('je_customer_address').value = d.customer_address || '';

            // Populate vehicle fields
            document.getElementById('je_vehicle_make').value    = d.vehicle_make    || '';
            document.getElementById('je_vehicle_model').value   = d.vehicle_model   || '';
            document.getElementById('je_vehicle_year').value    = d.vehicle_year    || '';
            document.getElementById('je_vehicle_plate').value   = d.vehicle_plate   || '';
            document.getElementById('je_vehicle_color').value   = d.vehicle_color   || '';
            document.getElementById('je_vehicle_mileage').value = d.vehicle_mileage || '';

            // Populate discount
            document.getElementById('je_discount_type').value  = d.discount_type  || 'none';
            document.getElementById('je_discount_value').value = d.discount_value  || '0';

            // Populate notes
            document.getElementById('je_notes').value = d.notes || '';

            // Load services into jeItems
            let services = [];
            try { services = JSON.parse(d.services_json || '[]'); } catch(e) {}
            jeItems = services.map(s => ({
                type: s.type || 'service',
                id: parseInt(s.id || 0, 10),
                name: s.name || '',
                basePrice: parseFloat(s.base_price ?? s.price ?? 0),
                labor: parseFloat(s.labor_cost ?? s.labor ?? 0),
                price: (parseFloat(s.base_price ?? s.price ?? 0)) + (parseFloat(s.labor_cost ?? s.labor ?? 0)),
                qty: parseInt(s.qty || 1, 10),
                selectedSubItems: s.selectedSubItems || []
            }));

            // Load products into jeProducts
            let products = [];
            try { products = JSON.parse(d.products_json || '[]'); } catch(e) {}
            jeProducts = products.map(p => ({
                id: parseInt(p.id || 0, 10),
                name: p.name || '',
                code: p.code || '',
                price: parseFloat(p.price || 0),
                qty: parseInt(p.qty || 1, 10)
            }));

            // Load recommendations
            let recs = [];
            try { recs = JSON.parse(d.recommendations_json || '[]'); } catch(e) {}
            jeRecommendations = Array.isArray(recs) ? recs : [];

            jeRenderItems();
            jeCalc();
            jeRenderRecommendations();

            // Change modal title to "Edit Job Estimate"
            const titleEl = document.getElementById('jobEstimateModalLabel');
            if (titleEl) titleEl.innerHTML = '<i class="bi bi-pencil"></i> Edit Job Estimate';
            const saveBtn = document.getElementById('jeSaveBtn');
            if (saveBtn) saveBtn.innerHTML = '<i class="bi bi-save"></i> Update Job Estimate';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('jobEstimateModal')).show();
        })
        .catch(() => showToast('Failed to load estimate.'));
}

function saveEditEstimate() {
    // This is now handled by jeSave() via the same modal
    // just redirect to jeSave with the edit ID
    if (_editEstimateId) {
        const payload = buildEstimatePayload();
        if (payload.customer_phone !== '' && !/^09\d{9}$/.test(payload.customer_phone)) {
            showToast('Phone number must contain exactly 11 digits and start with 09.', 'error');
            return;
        }
        payload.id = _editEstimateId;
        fetch(APP_URL + '/api/estimates.php?id=' + _editEstimateId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                _editEstimateId = null;
                bootstrap.Modal.getInstance(document.getElementById('jobEstimateModal')).hide();
                location.reload();
            } else { showToast('Error: ' + data.message); }
        })
        .catch(() => showToast('Network error.'));
    }
}
</script>

<script>
async function printEstimate(id) {
    await refreshPrintTemplateSettings();
    return fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(async (res) => {
            if (!res.success) { showToast(res.message); return; }
            const d   = res.data;
            const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
            let services = [], products = [], recommendations = [];
            try { services = JSON.parse(d.services_json||'[]'); } catch(e){}
            try { products = JSON.parse(d.products_json||'[]'); } catch(e){}
            try { recommendations = JSON.parse(d.recommendations_json||'[]'); } catch(e){}
            const date = d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—';

            const custName = d.customer_name || '—';
            const custPhone = d.customer_phone || '—';
            const custEmail = d.customer_email || '—';
            const custAddress = d.customer_address || '—';
            const make = d.vehicle_make || '—';
            const model = d.vehicle_model || '—';
            const year = d.vehicle_year || '—';
            const plate = d.vehicle_plate || '—';
            const color = d.vehicle_color || '—';
            const mileage = d.vehicle_mileage || '—';

            // Compute discount
            const discType = d.discount_type || 'none';
            const discVal = parseFloat(d.discount_value || 0);
            const svcTotal = parseFloat(d.services_total || 0);
            const prdTotal = parseFloat(d.products_total || 0);
            const base = svcTotal + prdTotal;
            let discountAmt = 0;
            if (discType === 'percentage') discountAmt = base * (discVal / 100);
            else if (discType === 'fixed') discountAmt = discVal;
            else if (discType === 'senior' || discType === 'pwd') discountAmt = base * 0.20;
            discountAmt = Math.min(discountAmt, base);
            const total = Math.max(0, base - discountAmt);

            let discLabel = '';
            if (discType === 'senior') discLabel = 'Senior Citizen (20%)';
            else if (discType === 'pwd') discLabel = 'PWD (20%)';
            else if (discType === 'percentage') discLabel = `Percentage (${discVal}%)`;
            else if (discType === 'fixed') discLabel = 'Fixed Amount';

            let svcRows = '';
            let svcRowNum = 1;
            services.forEach((s) => {
                const isBundle = s.type === 'bundle';
                const svcId = parseInt(s.id||0, 10);
                const baseP = parseFloat(s.base_price ?? s.price ?? 0);
                const labor = parseFloat(s.labor_cost ?? s.labor ?? 0);
                const qty = parseInt(s.qty||1, 10);
                const lineTotal = (baseP + labor) * qty;
                const subItems = isBundle
                    ? (bundleServiceNamesMap[svcId]||[])
                    : (s.selectedSubItems && s.selectedSubItems.length > 0 ? s.selectedSubItems : []);
                const nameCell = isBundle ? `<strong>${s.name}</strong>` : (s.name||'Service');
                svcRows += `
              <tr${isBundle ? ' style="background:#f8f8f8;"' : ''}>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${svcRowNum++}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${nameCell}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${qty}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(baseP)}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(labor)}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(lineTotal)}</td>
              </tr>`;
                subItems.forEach(sub => {
                    svcRows += `
              <tr>
                <td style="padding:2px 8px;border:1px solid #ccc;text-align:center;color:#888;">-</td>
                <td style="padding:2px 8px 2px 20px;border:1px solid #ccc;color:#555;font-size:8.5pt;word-break:break-word;">${sub}</td>
                <td colspan="4" style="padding:2px 8px;border:1px solid #ccc;"></td>
              </tr>`;
                });
            });

            let prdRows = '';
            products.forEach((p, i) => {
                const isPkg = false;
                prdRows += `
              <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;word-break:break-word;">${p.name}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.qty||1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price)}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt((parseFloat(p.price)||0) * (parseInt(p.qty||1,10)))}</td>
              </tr>`;
            });

            let recsHtml = '';
            if (Array.isArray(recommendations) && recommendations.length > 0) {
                let recRows = '';
                recommendations.forEach((r, i) => {
                    recRows += `<tr><td style="padding:3px 6px;border:1px solid #ccc;text-align:center;vertical-align:top;">${i+1}</td><td style="padding:3px 6px;border:1px solid #ccc;font-weight:600;">${r.name||r}</td></tr>`;
                    if (r.subItems && r.subItems.length > 0) {
                        r.subItems.forEach(s => {
                            recRows += `<tr><td style="padding:2px 6px;border:1px solid #ddd;text-align:center;color:#aaa;">-</td><td style="padding:2px 6px 2px 20px;border:1px solid #ddd;color:#555;font-size:8pt;">${s}</td></tr>`;
                        });
                    }
                });
                recsHtml = `
            <div style="margin-top:12px;font-size:8.5pt;">
                <div style="font-weight:700;letter-spacing:.5px;padding-bottom:3px;font-size:8.5pt;">RECOMMENDATIONS</div>
                <table style="width:100%;border-collapse:collapse;font-size:8.5pt;">
                    <thead><tr style="background:#f0f0f0;">
                        <th style="padding:3px 6px;border:1px solid #ccc;width:5%;text-align:center;">#</th>
                        <th style="padding:3px 6px;border:1px solid #ccc;text-align:left;">Service / Item</th>
                    </tr></thead>
                    <tbody>${recRows}</tbody>
                </table>
            </div>`;
            }

            document.getElementById('jePrintContent').innerHTML = `
            <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">
                ${getPrintHeaderHtml('JOB ESTIMATE', d.estimate_number || '—', date)}

                <!-- Customer & Vehicle -->
                <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
                    <tr>
                        <td style="width:50%;vertical-align:top;padding-right:6px;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tr><td colspan="2" style="padding:3px 0;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">CUSTOMER</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;width:35%;border-bottom:1px solid #ddd;">Name</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${custName}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Phone</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${custPhone}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;border-bottom:1px solid #ddd;">Email</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${custEmail}</td></tr>
                                <tr><td style="padding:3px 6px 3px 0;color:#555;">Address</td><td style="padding:3px 0;">${custAddress}</td></tr>
                            </table>
                        </td>
                        <td style="width:50%;vertical-align:top;padding-left:6px;border-left:1px solid #ddd;">
                            <table style="width:100%;border-collapse:collapse;padding-left:6px;">
                                <tr><td colspan="2" style="padding:3px 0 3px 6px;font-weight:700;font-size:8.5pt;letter-spacing:.5px;border-bottom:1px solid #333;">VEHICLE</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;width:38%;border-bottom:1px solid #ddd;">Make / Model</td><td style="padding:3px 0;font-weight:600;border-bottom:1px solid #ddd;">${make} ${model}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Year</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${year}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Plate No.</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${plate}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;border-bottom:1px solid #ddd;">Color</td><td style="padding:3px 0;border-bottom:1px solid #ddd;">${color}</td></tr>
                                <tr><td style="padding:3px 6px;color:#555;">Mileage</td><td style="padding:3px 0;">${mileage} km</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Services -->
                <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
                    <colgroup><col style="width:4%"><col><col style="width:7%"><col style="width:13%"><col style="width:13%"><col style="width:14%"></colgroup>
                    <thead>
                        <tr>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Price</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Labor</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${svcRows || '<tr><td colspan="6" style="padding:8px;text-align:center;color:#999;">No services</td></tr>'}</tbody>
                </table>
                ${prdRows ? `
                <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding:6px 0 3px;">PRODUCTS / PARTS</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
                    <colgroup><col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%"></colgroup>
                    <thead>
                        <tr>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Product</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                            <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${prdRows}</tbody>
                </table>` : ''}

                <!-- Summary -->
                <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
                    ${discountAmt > 0 ? `<tr>
                        <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;">Discount (${discLabel})</td>
                        <td style="padding:4px 8px;border-top:1px solid #ccc;border-bottom:1px solid #ddd;color:#b00;text-align:right;">- ${fmt(discountAmt)}</td>
                    </tr>` : ''}
                    <tr>
                        <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                            Services Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(svcTotal)}</strong>
                        </td>
                        <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                            Products Subtotal<br><strong style="font-size:9.5pt;color:#000;">${fmt(prdTotal)}</strong>
                        </td>
                        <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                            TOTAL AMOUNT<br><strong style="font-size:11pt;color:#000;">${fmt(total)}</strong>
                        </td>
                    </tr>
                </table>

                <!-- Terms & Conditions -->
                <div style="margin-top:12px;font-size:8.5pt;border-top:1px solid #ddd;padding-top:6px;">
                    <strong style="font-size:8.5pt;letter-spacing:.4px;">TERMS AND CONDITIONS:</strong>
                    <div style="margin-top:3px;color:#333;white-space:pre-wrap;">${printTemplateSettings.terms_conditions || 'All services rendered are subject to warranty as per company policy.'}</div>
                </div>

                ${recsHtml}

                <!-- Signatures -->
                <div style="margin-top:20px;">
                    <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                        <tr>
                            <td style="width:50%;text-align:center;padding:0 10px;">
                                <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Authorized Signature</div>
                            </td>
                            <td style="width:50%;text-align:center;padding:0 10px;">
                                <div style="border-top:1px solid #555;padding-top:4px;margin-top:20px;">Customer Signature</div>
                            </td>
                        </tr>
                    </table>
                    ${getPrintFooterHtml()}
                </div>
            </div>`;
            document.getElementById('jePrintArea').style.display = 'block';
            await waitForPrintAssets('jePrintArea');
            document.getElementById('jePrintContent').dataset.pdfFilename = buildPdfFileName(d.vehicle_plate || '', d.customer_name || '', '_JE') + '.pdf';
            const pdfResult = printWithPdfName(d.vehicle_plate || '', d.customer_name || '');
            if (!_pdfMode) document.getElementById('jePrintArea').style.display = 'none';
            return pdfResult && typeof pdfResult.then === 'function'
                ? pdfResult.then(() => true)
                : Promise.resolve(true);
        })
        .catch(() => { showToast('Failed to load estimate for printing.'); return false; });
}
</script>

<script>
/* ═══════════════════════════════════════════
   CONVERT ESTIMATE → JOB ORDER
═══════════════════════════════════════════ */
function convertEstimateToJo(d) {
    if (!/^09\d{9}$/.test(String(d.customer_phone || '').replace(/\D/g, ''))) {
        showToast('Cannot convert: customer phone must contain exactly 11 digits and start with 09.', 'error');
        return;
    }

    // Parse services and products from the saved estimate
    let services = [], products = [];
    try { services = JSON.parse(d.services_json || '[]'); } catch(e) {}
    try { products = JSON.parse(d.products_json || '[]'); } catch(e) {}

    jeSourceEstimateId = d.id || null;

    // Load services into joItems
    joItems = services.map(normalizeEstimateItemToJo);

    // Load products into joProducts
    joProducts = products.map(p => ({
        id:    parseInt(p.id ?? 0, 10) || 0,
        name:  p.name  || '',
        code:  p.code  || '',
        price: parseFloat(p.price ?? p.unit_price ?? 0) || 0,
        qty:   parseInt(p.qty ?? p.quantity ?? 1, 10) || 1,
        fromBundle: p.fromBundle || null
    }));

    // Pre-fill customer fields
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
    setVal('jo_customer_name',    d.customer_name);
    setVal('jo_customer_phone',   d.customer_phone);
    setVal('jo_customer_email',   d.customer_email);
    setVal('jo_customer_address', d.customer_address);

    // Pre-fill vehicle fields
    setVal('jo_vehicle_make',    d.vehicle_make);
    setVal('jo_vehicle_model',   d.vehicle_model);
    setVal('jo_vehicle_year',    d.vehicle_year);
    setVal('jo_vehicle_plate',   d.vehicle_plate);
    setVal('jo_vehicle_color',   d.vehicle_color);
    setVal('jo_vehicle_mileage', d.vehicle_mileage);

    // Render and recalculate
    joRenderItems();
    joRenderProducts();
    joCalc();

    // Open the Create Job Order modal
    bootstrap.Modal.getOrCreateInstance(document.getElementById('createJobOrderModal')).show();
}
</script>

<?php include_once '../partials/footer.php'; ?>
