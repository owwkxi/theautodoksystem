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

$isJobOrdersOnlyRole = $isTechnician || $isChiefMechanic || $isServiceAdviser;
$canManageCatalog = hasAnyRole(['admin', 'cashier']);
$canDeleteRecords = hasRole('admin');
$canCreateJobOrder = hasAnyRole(['admin', 'cashier']);
$canEditJobOrder = hasAnyRole(['admin', 'cashier']);
$canEditJoStatus = hasAnyRole(['admin', 'cashier', 'service_adviser']);
$canStartJoTimer = hasAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser']);
$canStopJoTimer = hasAnyRole(['admin', 'cashier', 'technician', 'chief_mechanic', 'service_adviser']);
$canDoneJoTimer = hasAnyRole(['admin', 'cashier', 'technician']);
$activeJobOrderStatuses = ['pending', 'ongoing', 'under_inspection', 'returned_for_revision'];

// Job-order-only roles can only access the job_orders tab
if ($isJobOrdersOnlyRole && ($_GET['tab'] ?? 'job_orders') !== 'job_orders') {
    redirect(APP_URL . '/views/services/manage.php?tab=job_orders');
}

$pageTitle = $isJobOrdersOnlyRole ? 'Job Orders' : 'Services Management';

$serviceModel = new Service();
$bundleModel = new ServiceBundle();
$staffModel = new Staff();
$printTemplateSettings = getPrintTemplateSettings();

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
            redirect('manage.php?tab=services');
        } else {
            setMessage('Failed to create service. Service code may already exist.', 'error');
        }
    } catch (Exception $e) {
        error_log("Service creation error: " . $e->getMessage());
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

// Handle form submission for creating bundle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_bundle') {
    try {
        if (!$canManageCatalog) {
            throw new Exception('Insufficient permissions');
        }
        validateCSRF();
        
        $data = [
            'bundle_name' => sanitize($_POST['bundle_name']),
            'description' => sanitize($_POST['description']),
            'package_price' => (float)$_POST['package_price'],
            'status' => sanitize($_POST['status'])
        ];
        
        // Get selected services
        $serviceIds = isset($_POST['service_ids']) ? array_map('intval', $_POST['service_ids']) : [];
        
        if (empty($serviceIds)) {
            setMessage('Please select at least one service for the bundle', 'error');
        } else {
            error_log("Creating bundle with data: " . print_r($data, true));
            error_log("Service IDs: " . print_r($serviceIds, true));
            
            $result = $bundleModel->create($data, $serviceIds);
            
            if ($result) {
                setMessage('Bundle created successfully', 'success');
                logActivity($_SESSION['user_id'] ?? 0, 'create_bundle', 'Created bundle: ' . $data['bundle_name']);
                redirect('manage.php?tab=bundles');
            } else {
                setMessage('Failed to create bundle. Please check the error log.', 'error');
                error_log("Bundle creation returned false");
            }
        }
    } catch (Exception $e) {
        error_log("Bundle creation exception: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
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
            $result = $serviceModel->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Service deleted successfully' : 'Cannot delete service (in use)']);
            exit;
            
        case 'toggle_service':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $result = $serviceModel->toggleStatus($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Status updated' : 'Failed to update status']);
            exit;
            
        case 'delete_bundle':
            if (!$canDeleteRecords) {
                echo json_encode(['success' => false, 'message' => 'Only admin can delete records']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $result = $bundleModel->delete($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Bundle deleted successfully' : 'Cannot delete bundle (in use)']);
            exit;
            
        case 'toggle_bundle':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $result = $bundleModel->toggleStatus($id);
            echo json_encode(['success' => $result, 'message' => $result ? 'Status updated' : 'Failed to update status']);
            exit;

        case 'update_service':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
            $data = [
                'service_name'  => sanitize($_POST['service_name']),
                'service_code'  => sanitize($_POST['service_code']),
                'description'   => sanitize($_POST['description'] ?? ''),
                'service_price' => (float)$_POST['service_price'],
                'labor_cost'    => (float)$_POST['labor_cost'],
                'status'        => sanitize($_POST['status']),
            ];
            $result = $serviceModel->update($id, $data);
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Service updated successfully' : 'Failed to update service']);
            exit;

        case 'update_bundle':
            if (!$canManageCatalog) {
                echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
                exit;
            }
            validateCSRF();
            $id = (int)$_POST['id'];
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
            echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Bundle updated successfully' : 'Failed to update bundle']);
            exit;
    }
}

// Get active tab
$activeTab = $_GET['tab'] ?? ($isJobOrdersOnlyRole ? 'job_orders' : 'services');

// Validate tab
$validTabs = ['services', 'bundles', 'job_orders', 'estimates'];
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
} elseif ($activeTab === 'bundles') {
    $bundles = $bundleModel->getAll($filters);
    $totalRecords = $bundleModel->count($filters);
    $stats = $bundleModel->getStats();
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

// Get all active services for bundle creation
$allActiveServices = $serviceModel->getAll(['status' => 'active']);
// Get all active bundles for job order modal
$allActiveBundles = $bundleModel->getAll(['status' => 'active']);
// Get all active technicians for job order modal
$allTechnicians = $staffModel->getAll(['role' => 'technician', 'status' => 'active']);
// Get all active inventory products for job order modal (temporary)
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, product_code, product_name, selling_price, quantity FROM products WHERE status = 'active' ORDER BY product_name ASC");
    $allInventoryProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allInventoryProducts = [];
}

// Fetch job orders for the job_orders tab
if ($activeTab === 'job_orders') {
    try {
        $dbConn = Database::getInstance()->getConnection();
        $assignedTotalJo = 0;
        $assignedActiveJo = 0;

        if ($isTechnician) {
            $techStaffId = $_SESSION['user_id'] ?? 0;
            $countRow = $dbConn->prepare("SELECT COUNT(DISTINCT jo.id) AS total_assigned FROM job_orders jo INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id WHERE jot.technician_id = ?");
            $countRow->execute([$techStaffId]);
            $assignedTotalJo = (int)($countRow->fetch(PDO::FETCH_ASSOC)['total_assigned'] ?? 0);

            $activeCountSql = "SELECT COUNT(DISTINCT jo.id) AS active_assigned FROM job_orders jo INNER JOIN job_order_technicians jot ON jot.job_order_id = jo.id WHERE jot.technician_id = ? AND jo.status IN (" . implode(',', array_fill(0, count($activeJobOrderStatuses), '?')) . ")";
            $activeCountStmt = $dbConn->prepare($activeCountSql);
            $activeCountStmt->execute(array_merge([$techStaffId], $activeJobOrderStatuses));
            $assignedActiveJo = (int)($activeCountStmt->fetch(PDO::FETCH_ASSOC)['active_assigned'] ?? 0);
        }

        $joSearch = $_GET['jo_search'] ?? '';
        $joStatus = $_GET['jo_status'] ?? '';
        $joWhere  = 'WHERE 1=1';
        $joParams = [];

        // Technicians only see job orders assigned to them
        if ($isTechnician) {
            $techStaffId = $_SESSION['user_id'] ?? 0;
            $joWhere .= " AND EXISTS (
                SELECT 1 FROM job_order_technicians jot
                WHERE jot.job_order_id = jo.id AND jot.technician_id = ?
            )";
            $joParams[] = $techStaffId;
        }

        if ($isChiefMechanic || $isServiceAdviser) {
            $joWhere .= " AND jo.status IN (" . implode(',', array_fill(0, count($activeJobOrderStatuses), '?')) . ")";
            $joParams = array_merge($joParams, $activeJobOrderStatuses);
        }

        if ($joSearch) {
            $joWhere .= " AND (jo.job_order_number LIKE ? OR c.full_name LIKE ? OR v.plate_number LIKE ?)";
            $joParams = array_merge($joParams, ["%$joSearch%", "%$joSearch%", "%$joSearch%"]);
        }
        if ($joStatus) {
            $joWhere .= " AND jo.status = ?";
            $joParams[] = $joStatus;
        }
        $joStmt = $dbConn->prepare("
            SELECT jo.id, jo.job_order_number, jo.status, jo.payment_status,
                 jo.total_amount, jo.created_at,
                 jo.status_timer_seconds, jo.status_timer_started_at,
                   c.full_name AS customer_name, c.phone AS customer_phone,
                   v.brand, v.model, v.plate_number
            FROM job_orders jo
            LEFT JOIN customers c ON jo.customer_id = c.id
            LEFT JOIN vehicles  v ON jo.vehicle_id  = v.id
            $joWhere
            ORDER BY jo.created_at DESC
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
            $estSql   .= " AND (estimate_number LIKE ? OR vehicle_plate LIKE ? OR vehicle_make LIKE ?)";
            $estParams = ["%$estSearch%", "%$estSearch%", "%$estSearch%"];
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
    min-width: 218px;
}
.jo-status-select {
    max-width: 122px;
    font-size: 11px;
    padding-top: 0.18rem;
    padding-bottom: 0.18rem;
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
.jo-row-under-inspection td {
    background-color: #fdeaea !important;
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
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-md-6">
            <h4 style="color: #000;">Services Management</h4>
            <p class="mb-0" style="color: #666;">Manage individual services and PMS bundles</p>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($activeTab === 'services'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                    <i class="bi bi-plus-circle"></i> Add Service
                </button>
            <?php elseif ($activeTab === 'bundles'): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBundleModal">
                    <i class="bi bi-plus-circle"></i> Add Bundle
                </button>
            <?php elseif ($activeTab === 'job_orders' && $canCreateJobOrder): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createJobOrderModal">
                    <i class="bi bi-plus-circle"></i> Create Job Order
                </button>
            <?php elseif ($activeTab === 'estimates' && $canManageCatalog): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jobEstimateModal">
                    <i class="bi bi-calculator"></i> New Estimate
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if ($activeTab === 'services' || $activeTab === 'bundles'): ?>
    <div class="stats-card-row mb-3">
        <div class="stats-card-col">
            <div class="card h-100 stats-card">
                <div class="card-body text-center">
                    <h6 class="mb-1" style="color: #666; font-size: 13px;">Total <?php echo $activeTab === 'services' ? 'Services' : 'Bundles'; ?></h6>
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
    <ul class="nav nav-tabs mb-3">
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
                <i class="bi bi-wrench"></i> Individual Services
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $activeTab === 'bundles' ? 'active' : ''; ?>" 
               href="?tab=bundles"
               style="color: #000; <?php echo $activeTab === 'bundles' ? 'background: #fff; border-bottom: 2px solid #000;' : ''; ?>">
                <i class="bi bi-box-seam"></i> Service Bundles
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Search and Filter -->
    <?php if ($activeTab === 'services' || $activeTab === 'bundles'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
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
                                            <?php echo escape(substr($service['description'] ?? 'N/A', 0, 50)); ?>
                                            <?php echo strlen($service['description'] ?? '') > 50 ? '...' : ''; ?>
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
                                        <div class="dropdown action-dropdown">
                                            <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="actionDropdownService<?php echo $service['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Service actions">
                                                <i class="bi bi-three-dots"></i>
                                                <span class="visually-hidden">Service actions</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdownService<?php echo $service['id']; ?>">
                                                <li>
                                                    <button type="button" class="dropdown-item" onclick="editService(<?php echo $service['id']; ?>, '<?php echo addslashes(escape($service['service_name'])); ?>', '<?php echo addslashes(escape($service['service_code'])); ?>', '<?php echo addslashes(escape($service['description'] ?? '')); ?>', <?php echo $service['service_price']; ?>, <?php echo $service['labor_cost']; ?>, '<?php echo $service['status']; ?>')">
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
                                                    <button type="button" class="dropdown-item text-danger" onclick="deleteItem('service', <?php echo $service['id']; ?>)">
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
                                    <th>Bundle Name</th>
                                    <th>Description</th>
                                    <th>Package Price</th>
                                    <th>Services Included</th>
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
                                        <span class="badge bg-info"><?php echo count($bundle['services']); ?> services</span>
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
                                        <button onclick="deleteItem('bundle', <?php echo $bundle['id']; ?>)" 
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
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="job_orders">
                    <div class="col-md-5">
                        <input type="text" name="jo_search" class="form-control form-control-sm"
                               placeholder="Search by JO#, customer, plate..."
                               value="<?php echo escape($_GET['jo_search'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="jo_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <?php foreach (['pending','ongoing','under_inspection','completed','released','returned_for_revision','cancelled'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo (($_GET['jo_status'] ?? '') === $s) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_',' ',$s)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Search</button>
                        <a href="?tab=job_orders" class="btn btn-sm btn-secondary ms-1"><i class="bi bi-x"></i> Clear</a>
                    </div>
                    <?php if ($canCreateJobOrder): ?>
                    <div class="col-auto ms-auto">
                        <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#createJobOrderModal">
                            <i class="bi bi-plus-circle"></i> Create Job Order
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
                <?php if ($isTechnician): ?>
                <div class="mt-2 small text-muted">
                    Assigned Active Job Orders: <strong><?php echo (int)$assignedActiveJo; ?></strong>
                    <span class="mx-1">|</span>
                    Assigned Total Job Orders: <strong><?php echo (int)$assignedTotalJo; ?></strong>
                </div>
                <?php endif; ?>
            </div>
        </div>

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
                                <th>Plate</th>
                                <th>Amount</th>
                                <th>Payment</th>
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
                                $rowTimerRunning = in_array($jo['status'], ['ongoing', 'under_inspection'], true) && !empty($jo['status_timer_started_at']);
                                if ($rowTimerRunning) {
                                    $rowTimerBase += max(0, time() - strtotime($jo['status_timer_started_at']));
                                }
                                $rowTimerVisible = in_array($jo['status'], ['ongoing', 'under_inspection', 'completed'], true);
                                $rowTimerLocked = $jo['status'] === 'completed';
                            ?>
                            <tr id="jo-row-<?php echo $jo['id']; ?>" class="<?php echo $jo['status'] === 'under_inspection' ? 'jo-row-under-inspection' : ''; ?>">
                                <td class="px-3 fw-bold"><?php echo escape($jo['job_order_number']); ?></td>
                                <td>
                                    <div><?php echo escape($jo['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo escape($jo['customer_phone']); ?></small>
                                </td>
                                <td><?php echo escape(trim($jo['brand'].' '.$jo['model'])); ?></td>
                                <td><?php echo escape($jo['plate_number'] ?? '—'); ?></td>
                                <td><?php echo formatCurrency($jo['total_amount']); ?></td>
                                <td><span class="badge bg-<?php echo $pc; ?>"><?php echo ucfirst($jo['payment_status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($jo['created_at'])); ?></td>
                                <td>
                                    <select
                                        id="jo-status-select-<?php echo $jo['id']; ?>"
                                        class="form-select form-select-sm jo-status-select"
                                        data-prev="<?php echo $jo['status']; ?>"
                                        <?php if (!$canEditJoStatus): ?>disabled<?php endif; ?>
                                        onchange="updateJoStatusInline(<?php echo $jo['id']; ?>, this.value, this)">
                                        <?php foreach (['pending','ongoing','under_inspection','completed','released','returned_for_revision','cancelled'] as $statusOption): ?>
                                        <option value="<?php echo $statusOption; ?>" <?php echo $jo['status'] === $statusOption ? 'selected' : ''; ?>>
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
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- View — always visible -->
                                        <button class="btn btn-outline-secondary py-0 px-2" onclick="viewJobOrder(<?php echo $jo['id']; ?>)" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($canEditJobOrder): ?>
                                        <button class="btn btn-outline-dark py-0 px-2" onclick="editJobOrder(<?php echo $jo['id']; ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-primary py-0 px-2" onclick="printJobOrder(<?php echo $jo['id']; ?>)" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <?php if (hasRole('admin')): ?>
                                        <button class="btn btn-outline-danger py-0 px-2" onclick="deleteJobOrder(<?php echo $jo['id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php endif; ?>
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

        function updateJoRowHighlight(id, status) {
            const row = document.getElementById('jo-row-' + id);
            if (!row) return;
            if (status === 'under_inspection') {
                row.classList.add('jo-row-under-inspection');
            } else {
                row.classList.remove('jo-row-under-inspection');
            }
        }

        function deleteJobOrder(id) {
            appConfirm('Delete this job order? This cannot be undone.', {
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
                .then(d => { if (d.success) location.reload(); else alert('Error: ' + d.message); })
                .catch(() => alert('Network error'));
            });
        }

        function updateJoStatusInline(id, status, selectEl) {
            const prevValue = selectEl?.dataset.prev || 'pending';
            if (!canEditJoStatus) {
                if (selectEl) selectEl.value = prevValue;
                alert('You are not allowed to update job order status.');
                return;
            }
            if (selectEl) selectEl.disabled = true;

            fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: '<?php echo generateCSRFToken(); ?>', status })
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    if (selectEl) selectEl.value = prevValue;
                    alert('Error: ' + d.message);
                    return;
                }

                const timerEl = document.getElementById('jo-status-timer-' + id);
                const timerWrapEl = document.getElementById('jo-status-timer-wrap-' + id);
                if (timerEl && timerWrapEl) {
                    const isRunningStatus = status === 'ongoing' || status === 'under_inspection';
                    const isCompleted = status === 'completed';

                    timerEl.dataset.running = isRunningStatus ? '1' : '0';
                    timerWrapEl.style.display = (isRunningStatus || isCompleted) ? '' : 'none';
                    setJoTimerControlsState(id, isRunningStatus);
                }

                updateJoRowHighlight(id, status);

                if (selectEl) selectEl.dataset.prev = status;
            })
            .catch(() => {
                if (selectEl) selectEl.value = prevValue;
                alert('Network error while updating status.');
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
            if (action === 'start' && !canStartJoTimer) {
                alert('You are not allowed to start the timer.');
                return;
            }
            if (action === 'stop' && !canStopJoTimer) {
                alert('You are not allowed to stop the timer.');
                return;
            }
            if (action === 'done' && !canDoneJoTimer) {
                alert('You are not allowed to mark this job as done.');
                return;
            }
            if (isJoTimerLocked(id)) {
                alert('Completed job order timer is locked and cannot be edited.');
                return;
            }

            const startBtn = document.getElementById('jo-timer-start-' + id);
            const stopBtn = document.getElementById('jo-timer-stop-' + id);
            const doneBtn = document.getElementById('jo-timer-done-' + id);
            if (startBtn) startBtn.disabled = true;
            if (stopBtn) stopBtn.disabled = true;
            if (doneBtn) doneBtn.disabled = true;

            fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: '<?php echo generateCSRFToken(); ?>',
                    timer_action: action
                })
            })
            .then(r => r.json())
            .then(d => {
                if (!d.success) {
                    alert('Error: ' + d.message);
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
                    const statusSelect = document.getElementById('jo-status-select-' + id);
                    if (statusSelect) {
                        statusSelect.value = 'under_inspection';
                        statusSelect.dataset.prev = 'under_inspection';
                    }
                    updateJoRowHighlight(id, 'under_inspection');
                }

                setJoTimerControlsState(id, isRunning);
            })
            .catch(() => alert('Network error while controlling timer.'))
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
                               placeholder="Search by estimate#, plate, make..."
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
                                <th class="px-3">Estimate #</th>
                                <th>Vehicle</th>
                                <th>Plate</th>
                                <th>Services</th>
                                <th>Products</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allEstimates as $est): ?>
                            <tr>
                                <td class="px-3 fw-bold"><?php echo escape($est['estimate_number']); ?></td>
                                <td><?php echo escape(trim($est['vehicle_make'].' '.$est['vehicle_model'])); ?></td>
                                <td><?php echo escape($est['vehicle_plate'] ?: '—'); ?></td>
                                <td><?php echo formatCurrency($est['services_total']); ?></td>
                                <td><?php echo formatCurrency($est['products_total']); ?></td>
                                <td class="fw-bold"><?php echo formatCurrency($est['grand_total']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $est['status'] === 'converted' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($est['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($est['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary py-0 px-2" onclick="viewEstimate(<?php echo $est['id']; ?>)" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-dark py-0 px-2" onclick="editEstimate(<?php echo $est['id']; ?>)" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-primary py-0 px-2" onclick="printEstimate(<?php echo $est['id']; ?>)" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <?php if ($canDeleteRecords): ?>
                                        <button class="btn btn-outline-danger py-0 px-2" onclick="deleteEstimate(<?php echo $est['id']; ?>)" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
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
        function deleteEstimate(id) {
            appConfirm('Delete this estimate? This cannot be undone.', {
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
                .then(d => { if (d.success) location.reload(); else alert('Error: ' + d.message); })
                .catch(() => alert('Network error'));
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
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
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
                            <input type="text" class="form-control" name="service_code" 
                                   placeholder="Auto-generated if left blank" style="border: 1.5px solid #e0e0e0;">
                            <small style="color: #666;">Leave blank to auto-generate (SVC##)</small>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Description
                            </label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="Brief description of the service..." 
                                      style="border: 1.5px solid #e0e0e0;"></textarea>
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
                        <i class="bi bi-plus-circle"></i> Add New Service Bundle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create_bundle">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Bundle Name <span style="color: #dc3545;">*</span>
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
                                      placeholder="Brief description of what's included in this bundle..." 
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
                        
                        <div class="col-12">
                            <label class="form-label" style="color: #000; font-weight: 500;">
                                Select Services <span style="color: #dc3545;">*</span>
                            </label>
                            <div style="max-height: 300px; overflow-y: auto; border: 1.5px solid #e0e0e0; border-radius: 8px; padding: 15px; background: #f9f9f9;">
                                <?php if (empty($allActiveServices)): ?>
                                    <p style="color: #666; text-align: center; margin: 20px 0;">
                                        No active services available. Please create services first.
                                    </p>
                                <?php else: ?>
                                    <?php foreach ($allActiveServices as $service): ?>
                                        <div class="form-check mb-2" style="padding: 10px; background: #fff; border-radius: 6px;">
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
                                <?php endif; ?>
                            </div>
                            <small style="color: #666;">Select at least one service to include in this bundle</small>
                        </div>
                        
                        <div class="col-12">
                            <div class="alert" style="background: #f8f9fa; border: 1px solid #e0e0e0; color: #000;">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Note:</strong> Package price is typically lower than the sum of individual services
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e0e0e0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Bundle
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
                                        <input type="tel" class="form-control form-control-sm" id="jo_customer_phone" required placeholder="09XX XXX XXXX">
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
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_make" placeholder="Toyota">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Model</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_model" placeholder="Vios">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Year</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_year" placeholder="2022">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Plate Number</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_plate" placeholder="ABC 1234">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Color</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_color" placeholder="White">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Mileage (km)</label>
                                        <input type="text" class="form-control form-control-sm" id="jo_vehicle_mileage" placeholder="50000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Services & Bundles Picker -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services &amp; Bundles</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <!-- Tabs -->
                                <ul class="nav nav-tabs nav-sm mb-3" id="joServiceTabs">
                                    <li class="nav-item">
                                        <a class="nav-link active py-1 px-3" data-bs-toggle="tab" href="#joTabIndividual" style="font-size:13px;">Individual Services</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#joTabBundles" style="font-size:13px;">Bundles (PMS)</a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <!-- Individual Services -->
                                    <div class="tab-pane fade show active" id="joTabIndividual">
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveServices)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active services found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveServices as $svc): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card">
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
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Bundles -->
                                    <div class="tab-pane fade" id="joTabBundles">
                                        <div style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                            <?php if (empty($allActiveBundles)): ?>
                                                <p class="text-muted text-center small py-3 mb-0">No active bundles found.</p>
                                            <?php else: ?>
                                                <?php foreach ($allActiveBundles as $bnd): ?>
                                                <div class="d-flex align-items-center justify-content-between py-1 px-2 mb-1 bg-white rounded jo-record-card">
                                                    <div>
                                                        <strong style="font-size:13px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services included</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($bnd['package_price']); ?></span>
                                                        <button type="button" class="btn btn-sm btn-dark py-0 px-2"
                                                            style="font-size:12px;"
                                                            onclick="joAddItem('bundle', <?php echo $bnd['id']; ?>, '<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Bundle)', <?php echo $bnd['package_price']; ?>, <?php echo isset($bnd['labor_cost']) ? (float)$bnd['labor_cost'] : 0; ?>)">
                                                            <i class="bi bi-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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
                                <div class="d-flex gap-1 mb-1">
                                    <select class="form-select form-select-sm" id="jo_product_select" style="flex:1;">
                                        <option value="">— Select product —</option>
                                        <?php foreach ($allInventoryProducts as $prod): ?>
                                        <option value="<?php echo $prod['id']; ?>"
                                            data-name="<?php echo addslashes(escape($prod['product_name'])); ?>"
                                            data-price="<?php echo $prod['selling_price']; ?>"
                                            data-stock="<?php echo $prod['quantity']; ?>"
                                            data-code="<?php echo escape($prod['product_code']); ?>">
                                            <?php echo escape($prod['product_name']); ?> — ₱<?php echo number_format($prod['selling_price'], 2); ?> (<?php echo $prod['quantity']; ?> in stock)
                                        </option>
                                        <?php endforeach; ?>
                                        <?php if (empty($allInventoryProducts)): ?>
                                        <option disabled>No products in inventory</option>
                                        <?php endif; ?>
                                    </select>
                                    <input type="number" id="jo_product_qty" class="form-control form-control-sm text-center" value="1" min="1" style="width:55px;">
                                    <button type="button" class="btn btn-sm btn-dark px-2" onclick="joAddProduct()"><i class="bi bi-plus"></i></button>
                                </div>
                                <div id="joProductsList" style="max-height:130px;overflow-y:auto;"></div>
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
                                <div id="joSelectedItems" style="min-height:80px;max-height:200px;overflow-y:auto;">
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
                                <div class="mb-2">
                                    <label class="form-label form-label-sm">Payment Method</label>
                                    <select class="form-select form-select-sm" id="jo_payment_method">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="gcash">GCash</option>
                                        <option value="paymaya">PayMaya</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label form-label-sm">Payment Status</label>
                                    <select class="form-select form-select-sm" id="jo_payment_status" onchange="joTogglePartial()">
                                        <option value="pending">Pending</option>
                                        <option value="partial">Partial</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                <!-- Partial payment field — shown only when Partial is selected -->
                                <div id="joPartialRow" style="display:none;">
                                    <label class="form-label form-label-sm">Amount Paid (₱) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-sm" id="jo_partial_amount"
                                           min="0" step="0.01" value="0" oninput="joCalcPartial()" placeholder="0.00">
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="small text-muted">Remaining Balance</span>
                                        <strong class="text-danger" id="joRemainingBalance">₱0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technician -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-tools me-1"></i>Technicians</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div id="jo_technicians" class="border rounded p-2" style="max-height:140px;overflow:auto;background:#fff;">
                                    <?php foreach ($allTechnicians as $tech): ?>
                                    <div class="form-check mb-1">
                                        <input
                                            class="form-check-input jo-tech-check"
                                            type="checkbox"
                                            value="<?php echo $tech['id']; ?>"
                                            id="jo_tech_<?php echo $tech['id']; ?>"
                                            data-name="<?php echo escape($tech['full_name']); ?>"
                                            onchange="joUpdateTechnicianIndicator()"
                                        >
                                        <label class="form-check-label small" for="jo_tech_<?php echo $tech['id']; ?>">
                                            <?php echo escape($tech['full_name']); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <small class="text-muted">You can assign one or more technicians.</small>
                                    <span id="joTechCountBadge" class="badge bg-secondary">0 selected</span>
                                </div>
                                <div id="joTechSelectedNames" class="small mt-2 text-muted">No technician assigned.</div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-chat-left-text me-1"></i>Notes</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <textarea class="form-control form-control-sm" id="jo_notes" rows="2" placeholder="Additional notes or instructions..."></textarea>
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
            size: A4 portrait;
            margin: 15mm 20mm;
        }
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
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
                page-break-after: avoid !important;
            }
            #joPrintContent, #jePrintContent {
                width: 100%;
                font-family: Arial, sans-serif;
                font-size: 9.5pt;
                color: #000 !important;
                line-height: 1.35;
                page-break-after: avoid !important;
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
                <h5 class="modal-title" id="jobEstimateModalLabel" style="color: #000;">
                    <i class="bi bi-calculator"></i> Job Estimate Calculator
                </h5>
                <button type="button" style="background: none; border: none; font-size: 24px; color: #000; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x" style="font-size: 24px;"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-car-front me-1"></i>Vehicle Information</h6>
                            </div>
                            <div class="card-body" style="padding:15px;">
                                <div class="row g-2">
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Make / Brand</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_make" placeholder="Toyota">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Model</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_model" placeholder="Vios">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Year</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_year" placeholder="2022">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Plate Number</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_plate" placeholder="ABC 1234">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Color</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_color" placeholder="White">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label form-label-sm">Mileage (km)</label>
                                        <input type="text" class="form-control form-control-sm" id="je_vehicle_mileage" placeholder="50000">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services and Bundles</h6>
                            </div>
                            <div class="card-body" style="padding:12px 15px;">
                                <ul class="nav nav-tabs mb-2" role="tablist" style="border-color:#ddd;">
                                    <li class="nav-item">
                                        <button class="nav-link active py-1 px-3" data-bs-toggle="tab" data-bs-target="#jeTabServices" type="button" role="tab" style="font-size:13px;">Services</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="tab" data-bs-target="#jeTabBundles" type="button" role="tab" style="font-size:13px;">Bundles (PMS)</button>
                                    </li>
                                </ul>
                                <div class="tab-content" style="max-height:220px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;padding:10px;background:#f9f9f9;">
                                    <div class="tab-pane fade show active" id="jeTabServices" role="tabpanel">
                                        <?php if (empty($allActiveServices)): ?>
                                            <p class="text-muted text-center small py-3 mb-0">No active services found.</p>
                                        <?php else: ?>
                                            <?php foreach ($allActiveServices as $service): ?>
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:13px;border-color:#eee !important;">
                                                <label class="form-check-label d-flex align-items-center gap-2 mb-0 flex-grow-1" for="est_service_<?php echo $service['id']; ?>" style="cursor:pointer;">
                                                    <input class="form-check-input estimate-item estimate-service" type="checkbox"
                                                           data-type="service"
                                                           data-id="<?php echo $service['id']; ?>"
                                                           data-name="<?php echo addslashes(escape($service['service_name'])); ?>"
                                                           data-price="<?php echo $service['service_price'] + $service['labor_cost']; ?>"
                                                           id="est_service_<?php echo $service['id']; ?>">
                                                    <span>
                                                        <strong style="font-size:13px;"><?php echo escape($service['service_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo escape($service['service_code']); ?></small>
                                                    </span>
                                                </label>
                                                <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($service['service_price'] + $service['labor_cost']); ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tab-pane fade" id="jeTabBundles" role="tabpanel">
                                        <?php if (empty($allActiveBundles)): ?>
                                            <p class="text-muted text-center small py-3 mb-0">No active bundles found.</p>
                                        <?php else: ?>
                                            <?php foreach ($allActiveBundles as $bnd): ?>
                                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="font-size:13px;border-color:#eee !important;">
                                                <label class="form-check-label d-flex align-items-center gap-2 mb-0 flex-grow-1" for="est_bundle_<?php echo $bnd['id']; ?>" style="cursor:pointer;">
                                                    <input class="form-check-input estimate-item estimate-bundle" type="checkbox"
                                                           data-type="bundle"
                                                           data-id="<?php echo $bnd['id']; ?>"
                                                           data-name="<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Bundle)"
                                                           data-price="<?php echo $bnd['package_price']; ?>"
                                                           id="est_bundle_<?php echo $bnd['id']; ?>">
                                                    <span>
                                                        <strong style="font-size:13px;"><?php echo escape($bnd['bundle_name']); ?></strong>
                                                        <small class="text-muted d-block"><?php echo count($bnd['services']); ?> services included</small>
                                                    </span>
                                                </label>
                                                <span style="font-size:13px;font-weight:600;min-width:72px;text-align:right;"><?php echo formatCurrency($bnd['package_price']); ?></span>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                            <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                                <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-box-seam me-1"></i>Product</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 mb-md-0">
                                    <label class="form-label form-label-sm fw-semibold" style="color:#000;">Add Product</label>
                                    <div class="d-flex gap-1 mb-1">
                                        <select class="form-select form-select-sm" id="est_product_select" style="flex:1;">
                                            <option value="">— Select product —</option>
                                            <?php foreach ($allInventoryProducts as $prod): ?>
                                            <option value="<?php echo $prod['id']; ?>"
                                                data-name="<?php echo addslashes(escape($prod['product_name'])); ?>"
                                                data-price="<?php echo $prod['selling_price']; ?>"
                                                data-stock="<?php echo $prod['quantity']; ?>">
                                                <?php echo escape($prod['product_name']); ?> — ₱<?php echo number_format($prod['selling_price'], 2); ?> (<?php echo $prod['quantity']; ?> in stock)
                                            </option>
                                            <?php endforeach; ?>
                                            <?php if (empty($allInventoryProducts)): ?>
                                            <option disabled>No products in inventory</option>
                                            <?php endif; ?>
                                        </select>
                                        <input type="number" id="est_product_qty" class="form-control form-control-sm text-center" value="1" min="1" style="width:55px;">
                                        <button type="button" class="btn btn-sm btn-dark px-2" onclick="estAddProduct()"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <div id="estProductsList" style="max-height:120px;overflow-y:auto;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card" style="background: #f8f9fa; border: 2px solid #e0e0e0;">
                            <div class="card-body">
                                <h6 style="color: #000; margin-bottom: 15px;">Estimated Summary</h6>

                                <div class="mb-3">
                                    <label class="form-label form-label-sm fw-semibold" style="color:#000;">Selected Items</label>
                                    <div id="estSelectedItemsList" style="max-height:140px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;background:#fff;padding:8px;">
                                        <p class="text-muted small text-center py-2 mb-0">No items selected.</p>
                                    </div>
                                </div>

                                <hr style="border-color: #e0e0e0;">
                                <!-- Subtotals -->
                                <div class="d-flex justify-content-between mb-1">
                                    <span style="color: #666;">Services &amp; Bundles Total:</span>
                                    <strong style="color: #000;" id="estimateTotal">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span style="color: #666;">Products Total:</span>
                                    <strong style="color: #000;" id="estimateProductsTotal">₱0.00</strong>
                                </div>

                                <hr style="border-color: #e0e0e0;">
                                <div class="d-flex justify-content-between">
                                    <strong style="color: #000;">Grand Total:</strong>
                                    <h4 style="color: #000; margin: 0;" id="estimateGrandTotal">₱0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fa; border-top: 2px solid #e0e0e0; gap: .5rem;">
                <button type="button" class="btn btn-success" onclick="jeConvertToJo()">
                    <i class="bi bi-arrow-right-circle"></i> Convert
                </button>
                <button type="button" class="btn btn-outline-dark" onclick="jePrintPreview()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <button type="button" class="btn btn-dark" onclick="jeSave()">
                    <i class="bi bi-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = '<?php echo generateCSRFToken(); ?>';
const printTemplateSettings = <?php echo json_encode($printTemplateSettings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

function applyPrintTemplate(template, vars) {
    let output = String(template || '');
    Object.keys(vars).forEach((key) => {
        const pattern = new RegExp(`\\{\\{${key}\\}\\}`, 'g');
        output = output.replace(pattern, vars[key] ?? '');
    });
    return output;
}

function getPrintHeaderHtml(documentTitle, documentNumber, documentDate) {
    return applyPrintTemplate(printTemplateSettings.header_template, {
        logo_url: printTemplateSettings.logo_url,
        company_name: printTemplateSettings.company_name,
        company_subtitle: printTemplateSettings.company_subtitle,
        contact_line: printTemplateSettings.contact_line,
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

/* ═══════════════════════════════════════════
   JOB ORDER MODAL LOGIC
═══════════════════════════════════════════ */
let joItems    = [];   // { id, type, name, basePrice, labor, price, qty }
let joProducts = [];   // { id, name, code, price, qty }
let joEditingId = null;
let joEditingStatus = 'pending';

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
function joAddProduct() {
    const sel   = document.getElementById('jo_product_select');
    const qty   = parseInt(document.getElementById('jo_product_qty').value) || 1;
    const opt   = sel.options[sel.selectedIndex];
    if (!sel.value) return;

    const id    = parseInt(sel.value);
    const name  = opt.dataset.name;
    const price = parseFloat(opt.dataset.price);
    const stock = parseInt(opt.dataset.stock);
    const code  = opt.dataset.code;

    const existing = joProducts.find(p => p.id === id);
    if (existing) {
        existing.qty += qty;
    } else {
        joProducts.push({ id, name, code, price, qty });
    }
    sel.value = '';
    document.getElementById('jo_product_qty').value = 1;
    joRenderProducts();
    joCalc();
}

function joRemoveProduct(idx) {
    joProducts.splice(idx, 1);
    joRenderProducts();
    joCalc();
}

function joChangeProductQty(idx, val) {
    const qty = parseInt(val);
    if (qty < 1) { joRemoveProduct(idx); return; }
    joProducts[idx].qty = qty;
    joCalc();
}

function joRenderProducts() {
    const container = document.getElementById('joProductsList');
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
                <small class="text-muted">₱${parseFloat(p.price).toFixed(2)} each</small>
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

function joAddItem(type, id, name, basePrice, laborCost = 0) {
    const effectivePrice = parseFloat(basePrice || 0) + parseFloat(laborCost || 0);
    const existing = joItems.find(i => i.type === type && i.id === id);
    if (existing) {
        existing.qty++;
        existing.labor = parseFloat(laborCost || existing.labor || 0);
        existing.price = parseFloat(basePrice || existing.basePrice || 0) + existing.labor;
    } else {
        joItems.push({ type, id, name, basePrice: parseFloat(basePrice || 0), labor: parseFloat(laborCost || 0), price: effectivePrice, qty: 1 });
    }
    joRenderItems();
    joCalc();
}

function joRemoveItem(idx) {
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

    if (joItems.length === 0) {
        container.innerHTML = '<p class="text-muted text-center small py-4 mb-0" id="joEmptyMsg">No items added yet.</p>';
        countBadge.textContent = '0';
        return;
    }

    countBadge.textContent = joItems.length;
    let html = '';
    joItems.forEach((item, idx) => {
        const unitPrice = (item.basePrice || 0) + (item.labor || 0);
        const lineTotal = (unitPrice * item.qty).toFixed(2);
        const baseValue = item.basePrice !== undefined && item.basePrice !== null ? parseFloat(item.basePrice).toFixed(2) : '';
        const laborValue = item.labor !== undefined && item.labor !== null ? parseFloat(item.labor).toFixed(2) : '';
        html += `
        <div class="px-3 py-2" style="border-bottom:1px solid #f0f0f0;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div style="flex:1;min-width:0;">
                    <div class="text-truncate" style="font-size:12px;font-weight:600;">${item.name}</div>
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
    container.innerHTML = html;
}

function joCalc() {
    let subtotal  = joItems.reduce((sum, i) => sum + ((i.basePrice || i.price || 0) + (i.labor || 0)) * i.qty, 0);
    let partsTotal = joProducts.reduce((sum, p) => sum + p.price * p.qty, 0);
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

    // Recalc partial balance if partial is selected
    joCalcPartial();
}

function joTogglePartial() {
    const status = document.getElementById('jo_payment_status').value;
    const row    = document.getElementById('joPartialRow');
    row.style.display = status === 'partial' ? 'block' : 'none';
    if (status !== 'partial') {
        document.getElementById('jo_partial_amount').value = '0';
        document.getElementById('joRemainingBalance').textContent = '₱0.00';
    } else {
        joCalcPartial();
    }
}

function joCalcPartial() {
    const status = document.getElementById('jo_payment_status').value;
    if (status !== 'partial') return;
    const totalText = document.getElementById('joTotal').textContent.replace(/[₱,]/g, '');
    const total     = parseFloat(totalText) || 0;
    const paid      = parseFloat(document.getElementById('jo_partial_amount').value) || 0;
    const remaining = Math.max(0, total - paid);
    const fmt = v => '₱' + v.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('joRemainingBalance').textContent = fmt(remaining);
}

function getSelectedTechnicianIds() {
    return Array.from(document.querySelectorAll('.jo-tech-check:checked'))
        .map((check) => parseInt(check.value, 10))
        .filter((id) => Number.isInteger(id) && id > 0);
}

function joUpdateTechnicianIndicator() {
    const badge = document.getElementById('joTechCountBadge');
    const namesEl = document.getElementById('joTechSelectedNames');
    if (!badge || !namesEl) return;

    const selectedChecks = Array.from(document.querySelectorAll('.jo-tech-check:checked'));
    const count = selectedChecks.length;
    const names = selectedChecks
        .map((check) => (check.dataset.name || '').trim())
        .filter(Boolean);

    badge.textContent = `${count} selected`;
    badge.className = 'badge bg-secondary';
    namesEl.textContent = count > 0 ? names.join(', ') : 'No technician assigned.';
    namesEl.className = 'small mt-2 text-muted';
}

function joSave() {
    const name  = document.getElementById('jo_customer_name').value.trim();
    const phone = document.getElementById('jo_customer_phone').value.trim();
    if (!name || !phone) { alert('Customer name and phone are required.'); return; }
    if (joItems.length === 0) { alert('Please add at least one service or bundle.'); return; }

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
        technician_ids:   getSelectedTechnicianIds(),
        discount_type:    document.getElementById('jo_discount_type').value,
        discount_value:   document.getElementById('jo_discount_value').value,
        parts_cost:       joProducts.reduce((s, p) => s + p.price * p.qty, 0),
        payment_method:   document.getElementById('jo_payment_method').value,
        payment_status:   document.getElementById('jo_payment_status').value,
        partial_amount:   parseFloat(document.getElementById('jo_partial_amount').value) || 0,
        notes:            document.getElementById('jo_notes').value.trim(),
        items:            joItems.map(item => ({
            type: item.type,
            id: item.id,
            name: item.name,
            base_price: parseFloat(item.basePrice || item.price || 0),
            labor_cost: parseFloat(item.labor || 0),
            price: parseFloat((item.basePrice || item.price || 0) + (item.labor || 0)),
            qty: parseInt(item.qty || 1)
        })),
        products:         joProducts
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
            alert(isEditMode
                ? 'Job order updated successfully.'
                : ('Job order created: ' + data.data.job_order_number));
            bootstrap.Modal.getInstance(document.getElementById('createJobOrderModal')).hide();
            joReset();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}

function joReset() {
    joEditingId = null;
    joEditingStatus = 'pending';
    joItems = [];
    joProducts = [];
    joRenderItems();
    joRenderProducts();
    joCalc();
    document.getElementById('joForm').reset();
    Array.from(document.querySelectorAll('.jo-tech-check')).forEach((check) => { check.checked = false; });
    joUpdateTechnicianIndicator();
    document.getElementById('joPartialRow').style.display = 'none';
    document.getElementById('joRemainingBalance').textContent = '₱0.00';
    joSetMode(false);
}

function joPrintPreview() {
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
    const notes   = document.getElementById('jo_notes').value.trim() || '—';
    const payMethod = document.getElementById('jo_payment_method').value;
    const payStatus = document.getElementById('jo_payment_status').value;
    const joNumber = 'JO###';
    const joDate   = new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });

    let subtotal = joItems.reduce((s, i) => s + i.price * i.qty, 0);
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

    let itemRows = '';
    joItems.forEach((item, i) => {
        itemRows += `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;">${item.name}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${item.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(item.price)}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(item.price * item.qty)}</td>
        </tr>`;
    });
    joProducts.forEach((p, i) => {
        itemRows += `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${joItems.length + i + 1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;">${p.name} <span style="color:#888;font-size:8pt;">(Product)</span></td>
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
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;padding-bottom:40mm;">

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

        <!-- Services / Items -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES / ITEMS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%">
                <col>
                <col style="width:8%">
                <col style="width:18%">
                <col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${itemRows}</tbody>
        </table>

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

        ${notes !== '—' ? `<div style="margin-top:8px;font-size:9pt;"><strong>Notes:</strong> ${notes}</div>` : ''}

        <!-- Signatures + Technician — pinned to bottom -->
        <div style="position:fixed;bottom:15mm;left:0;right:0;">
            <div style="font-size:9pt;margin-bottom:10px;padding-top:6px;">
                <strong>Assigned Technician(s):</strong> ${techName}
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <tr>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Technician Signature</div>
                    </td>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Customer Signature</div>
                    </td>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Authorized Signature</div>
                    </td>
                </tr>
            </table>
            ${getPrintFooterHtml()}
        </div>
    </div>`;

    document.getElementById('joPrintArea').style.display = 'block';
    window.print();
    document.getElementById('joPrintArea').style.display = 'none';
}

function jeSave() {
    const payload = buildEstimatePayload();

    if (payload.services.length === 0 && payload.products.length === 0) {
        alert('Please select at least one service, bundle, or product before saving.');
        return;
    }

    // Also keep in memory for convert-to-JO
    savedEstimate = { ...payload };

    saveEstimateRecord(payload)
    .then(data => {
        if (data.success) {
            alert('Estimate saved: ' + data.data.estimate_number);
            bootstrap.Modal.getInstance(document.getElementById('jobEstimateModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}

function jeConvertToJo() {
    const payload = buildEstimatePayload();

    if (payload.services.length === 0 && payload.products.length === 0) {
        alert('Please select at least one service, bundle, or product before converting.');
        return;
    }

    // Save first so converted JO can always be traced to a saved estimate.
    saveEstimateRecord(payload)
    .then(data => {
        if (!data.success) {
            alert('Error: ' + data.message);
            return;
        }

        alert('Estimate saved: ' + data.data.estimate_number + '. You can now complete conversion to Job Order.');

        joItems = payload.services.map(item => ({ ...item }));
        joProducts = payload.products.map(product => ({ ...product }));

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
    .catch(() => alert('Network error. Please try again.'));
}

function jePrintPreview() {
    const make    = document.getElementById('je_vehicle_make').value.trim() || '—';
    const model   = document.getElementById('je_vehicle_model').value.trim() || '—';
    const year    = document.getElementById('je_vehicle_year').value.trim() || '—';
    const plate   = document.getElementById('je_vehicle_plate').value.trim() || '—';
    const color   = document.getElementById('je_vehicle_color').value.trim() || '—';
    const mileage = document.getElementById('je_vehicle_mileage').value.trim() || '—';

    const selectedServices = getEstimateSelectedItems();
    const serviceRows = selectedServices.map((item, index) => {
        const name = item.name || 'Service';
        const price = parseFloat(item.price) || 0;
        return `
            <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${index + 1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${name}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">1</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">₱${price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</td>
            </tr>`;
    }).join('');

    const productRows = estProducts.map((product, index) => `
        <tr>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${selectedServices.length + index + 1}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;">${product.name}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${product.qty}</td>
            <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">₱${product.price.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</td>
        </tr>`).join('');

    const servicesTotal = parseFloat(document.getElementById('estimateTotal').textContent.replace(/[₱,]/g, '')) || 0;
    const productsTotal = parseFloat(document.getElementById('estimateProductsTotal').textContent.replace(/[₱,]/g, '')) || 0;
    const grandTotal = parseFloat(document.getElementById('estimateGrandTotal').textContent.replace(/[₱,]/g, '')) || 0;
    const estimateDate = new Date().toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' });

    document.getElementById('jePrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">
        ${getPrintHeaderHtml('JOB ESTIMATE', 'JE###', estimateDate)}
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9pt;">
            <tr>
                <td style="width:20%;padding:4px 8px;border:1px solid #ccc;font-weight:700;">Make / Model</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${make} ${model}</td>
                <td style="width:16%;padding:4px 8px;border:1px solid #ccc;font-weight:700;">Year</td>
                <td style="width:16%;padding:4px 8px;border:1px solid #ccc;">${year}</td>
            </tr>
            <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Plate No.</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${plate}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Color</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${color}</td>
            </tr>
            <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Mileage</td>
                <td colspan="3" style="padding:4px 8px;border:1px solid #ccc;">${mileage} km</td>
            </tr>
        </table>
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">ESTIMATE DETAILS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%">
                <col>
                <col style="width:8%">
                <col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                </tr>
            </thead>
            <tbody>${serviceRows || `<tr><td colspan="4" style="padding:8px;border:1px solid #ccc;text-align:center;color:#666;">No services selected</td></tr>`}${productRows}</tbody>
        </table>

        <!-- Summary Table -->
        <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
            <tr>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:9.5pt;color:#000;">₱${servicesTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:9.5pt;color:#000;">₱${productsTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>
                </td>
                <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    GRAND TOTAL<br><strong style="font-size:11pt;color:#000;">₱${grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>
                </td>
            </tr>
        </table>
        ${getPrintFooterHtml()}
    </div>`;

    document.getElementById('jePrintArea').style.display = 'block';
    window.print();
    document.getElementById('jePrintArea').style.display = 'none';
}

// Reset modal on close
document.getElementById('createJobOrderModal').addEventListener('hidden.bs.modal', joReset);

// Estimate calculator
let estProducts = []; // { id, name, price, qty }
let savedEstimate = null;

function estAddProduct() {
    const sel   = document.getElementById('est_product_select');
    const qty   = parseInt(document.getElementById('est_product_qty').value) || 1;
    const opt   = sel.options[sel.selectedIndex];
    if (!sel.value) return;

    const id    = parseInt(sel.value);
    const name  = opt.dataset.name;
    const price = parseFloat(opt.dataset.price);

    const existing = estProducts.find(p => p.id === id);
    if (existing) {
        existing.qty += qty;
    } else {
        estProducts.push({ id, name, price, qty });
    }
    sel.value = '';
    document.getElementById('est_product_qty').value = 1;
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
    return Array.from(document.querySelectorAll('.estimate-item:checked')).map((checkbox) => ({
        id: parseInt(checkbox.dataset.id, 10),
        type: checkbox.dataset.type || 'service',
        name: checkbox.dataset.name || 'Item',
        price: parseFloat(checkbox.dataset.price) || 0,
        qty: 1
    }));
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

    const labelMap = {
        service: 'Service',
        bundle: 'Bundle',
        product: 'Product'
    };

    container.innerHTML = allItems.map((item) => `
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom" style="border-color:#f0f0f0 !important;font-size:12px;">
            <div style="min-width:0;">
                <strong class="d-block text-truncate">${item.name}</strong>
                <small class="text-muted">${labelMap[item.type] || 'Item'}${item.qty > 1 ? ` • Qty: ${item.qty}` : ''}</small>
            </div>
            <span class="fw-semibold ms-2">₱${(parseFloat(item.price || 0) * parseInt(item.qty || 1, 10)).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</span>
        </div>
    `).join('');
}

function buildEstimatePayload() {
    const services = getEstimateSelectedItems();
    const products = estProducts.map(p => ({ ...p }));
    const servicesTotal = parseFloat(document.getElementById('estimateTotal').textContent.replace(/[₱,]/g, '')) || 0;
    const productsTotal = parseFloat(document.getElementById('estimateProductsTotal').textContent.replace(/[₱,]/g, '')) || 0;

    return {
        csrf_token:      csrfToken,
        vehicle_make:    document.getElementById('je_vehicle_make').value.trim(),
        vehicle_model:   document.getElementById('je_vehicle_model').value.trim(),
        vehicle_year:    document.getElementById('je_vehicle_year').value.trim(),
        vehicle_plate:   document.getElementById('je_vehicle_plate').value.trim(),
        vehicle_color:   document.getElementById('je_vehicle_color').value.trim(),
        vehicle_mileage: document.getElementById('je_vehicle_mileage').value.trim(),
        services_total:  servicesTotal,
        products_total:  productsTotal,
        services,
        products
    };
}

function saveEstimateRecord(payload) {
    return fetch('<?php echo APP_URL; ?>/api/estimates.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    }).then(r => r.json());
}

document.addEventListener('DOMContentLoaded', function() {
    const estimateCheckboxes = document.querySelectorAll('.estimate-item');

    function calculateEstimate() {
        let servicesTotal = 0;
        estimateCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                servicesTotal += parseFloat(checkbox.dataset.price);
            }
        });

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

    estimateCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateEstimate);
    });

    // reset products when modal closes
    document.getElementById('jobEstimateModal').addEventListener('hidden.bs.modal', function() {
        estProducts = [];
        estRenderProducts();
        estimateCheckboxes.forEach(cb => cb.checked = false);
        calculateEstimate();
    });

    // Init JO discount row visibility
    joCalc();
    joRenderProducts();
    estRenderProducts();
    calculateEstimate();
});

function deleteItem(type, id) {
    appConfirm(`Are you sure you want to delete this ${type}?`, {
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
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
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
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

/* ── Edit Service ── */
function editService(id, name, code, desc, price, labor, status) {
    document.getElementById('editSvcId').value          = id;
    document.getElementById('editSvcName').value        = name;
    document.getElementById('editSvcCode').value        = code;
    document.getElementById('editSvcDesc').value        = desc;
    document.getElementById('editSvcPrice').value       = price;
    document.getElementById('editSvcLabor').value       = labor;
    document.getElementById('editSvcStatus').value      = status;
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
    const params = new URLSearchParams({
        id,
        service_name:  document.getElementById('editSvcName').value.trim(),
        service_code:  document.getElementById('editSvcCode').value.trim(),
        description:   document.getElementById('editSvcDesc').value.trim(),
        service_price: document.getElementById('editSvcPrice').value,
        labor_cost:    document.getElementById('editSvcLabor').value,
        status:        document.getElementById('editSvcStatus').value,
        csrf_token:    csrfToken,
    });
    fetch('?action=update_service', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params })
        .then(r => r.json())
        .then(d => {
            if (d.success) { bootstrap.Modal.getInstance(document.getElementById('editServiceModal')).hide(); location.reload(); }
            else alert('Error: ' + d.message);
        })
        .catch(() => alert('Network error.'));
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
    // Tick the right checkboxes
    document.querySelectorAll('.edit-bundle-svc-check').forEach(cb => {
        cb.checked = editBundleSelectedIds.includes(parseInt(cb.value));
    });
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editBundleModal')).show();
}

function saveEditBundle() {
    const id = document.getElementById('editBndId').value;
    const checked = Array.from(document.querySelectorAll('.edit-bundle-svc-check:checked')).map(cb => cb.value);
    if (checked.length === 0) { alert('Please select at least one service.'); return; }
    const params = new URLSearchParams({
        id,
        bundle_name:   document.getElementById('editBndName').value.trim(),
        description:   document.getElementById('editBndDesc').value.trim(),
        package_price: document.getElementById('editBndPrice').value,
        status:        document.getElementById('editBndStatus').value,
        csrf_token:    csrfToken,
    });
    checked.forEach(v => params.append('service_ids[]', v));
    fetch('?action=update_bundle', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params })
        .then(r => r.json())
        .then(d => {
            if (d.success) { bootstrap.Modal.getInstance(document.getElementById('editBundleModal')).hide(); location.reload(); }
            else alert('Error: ' + d.message);
        })
        .catch(() => alert('Network error.'));
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
            <input type="text" class="form-control" id="editSvcCode">
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="editSvcDesc" rows="2"></textarea>
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
        <h5 class="modal-title" style="font-weight:600;"><i class="bi bi-box-seam me-2"></i>Edit Bundle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px;">
        <input type="hidden" id="editBndId">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Bundle Name <span class="text-danger">*</span></label>
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
          <div class="col-12">
            <label class="form-label">Services Included <span class="text-danger">*</span></label>
            <div style="max-height:260px;overflow-y:auto;border:1.5px solid #e0e0e0;border-radius:8px;padding:12px;background:#f9f9f9;">
              <?php if (empty($allActiveServices)): ?>
                <p class="text-muted text-center small mb-0">No active services available.</p>
              <?php else: ?>
                <?php foreach ($allActiveServices as $svc): ?>
                <div class="form-check mb-2 p-2 bg-white rounded" style="border:1px solid #eee;">
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
              <?php endif; ?>
            </div>
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
        <button type="button" class="btn btn-dark btn-sm" id="viewJoEditBtn"><i class="bi bi-pencil"></i> Edit</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
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
                    <div class="col-4"><label class="form-label form-label-sm">Make / Brand</label><input type="text" class="form-control form-control-sm" id="editJoMake" placeholder="Toyota"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Model</label><input type="text" class="form-control form-control-sm" id="editJoModel" placeholder="Vios"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Year</label><input type="text" class="form-control form-control-sm" id="editJoYear" placeholder="2022"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Plate Number</label><input type="text" class="form-control form-control-sm" id="editJoPlate" placeholder="ABC 1234"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Color</label><input type="text" class="form-control form-control-sm" id="editJoColor" placeholder="White"></div>
                    <div class="col-4"><label class="form-label form-label-sm">Mileage (km)</label><input type="text" class="form-control form-control-sm" id="editJoMileage" placeholder="50000"></div>
                  </div>
                </div>
              </div>
              <!-- Services & Bundles picker -->
              <div class="card mb-3" style="border:1.5px solid #e0e0e0;">
                <div class="card-header" style="background:#fff;border-bottom:1.5px solid #e0e0e0;padding:10px 15px;">
                  <h6 class="mb-0" style="font-weight:600;"><i class="bi bi-list-check me-1"></i>Services &amp; Bundles</h6>
                </div>
                <div class="card-body" style="padding:15px;">
                  <ul class="nav nav-tabs nav-sm mb-2" id="editJoServiceTabs">
                    <li class="nav-item"><a class="nav-link active py-1 px-3" data-bs-toggle="tab" href="#editJoTabIndividual" style="font-size:12px;">Individual Services</a></li>
                    <li class="nav-item"><a class="nav-link py-1 px-3" data-bs-toggle="tab" href="#editJoTabBundles" style="font-size:12px;">Bundles (PMS)</a></li>
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
                          <p class="text-muted text-center small py-2 mb-0">No active bundles.</p>
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
                                onclick="editJoAddItem('bundle',<?php echo $bnd['id']; ?>,'<?php echo addslashes(escape($bnd['bundle_name'])); ?> (Bundle)',<?php echo $bnd['package_price']; ?>)">
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
                    <div id="editJoSelectedItems" style="min-height:40px;max-height:150px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:6px;background:#fff;">
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
                      <option value="card">Card</option>
                      <option value="gcash">GCash</option>
                      <option value="paymaya">PayMaya</option>
                      <option value="bank_transfer">Bank Transfer</option>
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
        <button type="button" class="btn btn-success btn-sm" id="viewEstConvertBtn">
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
            const statusBadge = {pending:'secondary',ongoing:'primary',under_inspection:'info',completed:'success',released:'success',returned_for_revision:'danger',cancelled:'danger'};
            const payBadge    = {pending:'secondary',partial:'warning',paid:'success'};
            const date = d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—';
            const assignedTechnicians = Array.isArray(d.technicians) && d.technicians.length
                ? d.technicians.map((t) => t.full_name).filter(Boolean).join(', ')
                : (d.assigned_technician_name || 'Unassigned');
            const elapsedSec = parseInt(d.status_elapsed_seconds ?? d.status_timer_seconds ?? 0, 10) || 0;
            const elapsedH = String(Math.floor(elapsedSec / 3600)).padStart(2, '0');
            const elapsedM = String(Math.floor((elapsedSec % 3600) / 60)).padStart(2, '0');
            const elapsedS = String(elapsedSec % 60).padStart(2, '0');
            const recordedWorkTime = `${elapsedH}:${elapsedM}:${elapsedS}`;

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
              <!-- Status badges -->
              <div class="d-flex gap-3 mb-3">
                <div><small class="text-muted d-block">Status</small><span class="badge bg-${statusBadge[d.status]||'secondary'}">${(d.status||'').replace(/_/g,' ')}</span></div>
                <div><small class="text-muted d-block">Payment</small><span class="badge bg-${payBadge[d.payment_status]||'secondary'}">${d.payment_status||'—'}</span></div>
                <div><small class="text-muted d-block">Method</small><span class="badge bg-dark">${(d.payment_method||'—').replace(/_/g,' ')}</span></div>
                ${d.payment_status==='partial' ? `<div><small class="text-muted d-block">Paid</small><strong class="text-success">${fmt(d.partial_amount)}</strong></div><div><small class="text-muted d-block">Balance</small><strong class="text-danger">${fmt(parseFloat(d.total_amount||0)-parseFloat(d.partial_amount||0))}</strong></div>` : ''}
              </div>
                            <div class="mb-3" style="font-size:9pt;">
                                <small class="text-muted d-block">Assigned Technician(s)</small>
                                <strong>${assignedTechnicians}</strong>
                            </div>
                            <div class="mb-3" style="font-size:9pt;">
                                <small class="text-muted d-block">Recorded Work Time</small>
                                <strong>${recordedWorkTime}</strong>
                            </div>
              <!-- Services / Items table -->
              <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;margin-bottom:4px;">SERVICES / ITEMS</div>
              <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
                <colgroup><col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%"></colgroup>
                <thead>
                  <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                  </tr>
                </thead>
                <tbody>
                  ${(d.services||[]).map((s,i)=>`<tr><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${i+1}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;">${s.service_name}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${s.quantity}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(s.service_price)}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(s.total)}</td></tr>`).join('')}
                  ${(d.products||[]).map((p,i)=>`<tr><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${(d.services||[]).length+i+1}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;">${p.product_name} <span style="color:#888;font-size:8pt;">(Product)</span></td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:center;">${p.quantity}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(p.unit_price)}</td><td style="padding:4px 8px;border-bottom:1px solid #eee;text-align:right;">${fmt(p.total)}</td></tr>`).join('')}
                  ${(d.services||[]).length===0&&(d.products||[]).length===0?'<tr><td colspan="5" style="padding:10px;text-align:center;color:#999;">No items recorded</td></tr>':''}
                </tbody>
              </table>
              <!-- Summary -->
              <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
                <tr>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong style="font-size:10pt;">${fmt(d.subtotal)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong style="font-size:10pt;">${fmt(d.parts_total)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    TOTAL AMOUNT<br><strong style="font-size:12pt;">${fmt(d.total_amount)}</strong>
                  </td>
                </tr>
              </table>
              ${d.notes ? '<div style="margin-top:8px;font-size:9pt;"><strong>Notes:</strong> '+d.notes+'</div>' : ''}
            </div>`;

            document.getElementById('viewJoPrintBtn').onclick = () => { bootstrap.Modal.getInstance(document.getElementById('viewJobOrderModal')).hide(); printJobOrder(id); };
            document.getElementById('viewJoEditBtn').onclick  = () => { bootstrap.Modal.getInstance(document.getElementById('viewJobOrderModal')).hide(); editJobOrder(id); };
        })
        .catch(() => { document.getElementById('viewJoBody').innerHTML = '<p class="text-danger p-3">Failed to load job order.</p>'; });
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
        const unitPrice = (item.basePrice || 0) + (item.labor || 0);
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
    editJoItems[idx].price = (editJoItems[idx].basePrice || 0) + labor;
    editJoRenderItems();
    editJoUpdateBilling();
}

function editJoUpdateBilling() {
    const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
    const subtotal = editJoItems.reduce((s,i)=>s+(((i.basePrice||0)+(i.labor||0))*i.qty),0);
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
            if (!res.success) { alert(res.message); return; }
            const d   = res.data;
            joEditingId = d.id;
            joEditingStatus = d.status || 'pending';

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
            document.getElementById('jo_notes').value            = d.notes             || '';
            const statusAliases = {
                for_approval: 'under_inspection',
                return_for_revision: 'returned_for_revision'
            };
            const normalizedStatus = statusAliases[d.status] || d.status || 'pending';
            const selectedIds = Array.isArray(d.technician_ids) && d.technician_ids.length
                ? d.technician_ids.map((v) => parseInt(v, 10))
                : (d.service_adviser_id ? [parseInt(d.service_adviser_id, 10)] : []);
            Array.from(document.querySelectorAll('.jo-tech-check')).forEach((check) => {
                const checkId = parseInt(check.value, 10);
                check.checked = selectedIds.includes(checkId);
            });
            joUpdateTechnicianIndicator();

            if (d.payment_status === 'partial') {
                document.getElementById('joPartialRow').style.display = 'block';
                document.getElementById('jo_partial_amount').value = d.partial_amount || 0;
            } else {
                document.getElementById('joPartialRow').style.display = 'none';
                document.getElementById('jo_partial_amount').value = 0;
            }

            joItems = (d.services || []).map(s => ({
                type: s.bundle_id ? 'bundle' : 'service',
                id: s.bundle_id ? s.bundle_id : (s.service_id || null),
                name: s.service_name,
                basePrice: parseFloat(s.service_price || 0),
                labor: parseFloat(s.labor_cost || 0),
                price: parseFloat((s.service_price || 0) + (s.labor_cost || 0)),
                qty: parseInt(s.quantity || 1, 10)
            }));

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

            bootstrap.Modal.getOrCreateInstance(document.getElementById('createJobOrderModal')).show();
        })
        .catch(() => alert('Failed to load job order.'));
}

document.addEventListener('DOMContentLoaded', function () {
    joUpdateTechnicianIndicator();
});
</script>

<script>
function saveEditJobOrder() {
    const id = document.getElementById('editJoId').value;
    if (!document.getElementById('editJoCustomerName').value.trim())  { alert('Customer name is required.'); return; }
    if (!document.getElementById('editJoCustomerPhone').value.trim()) { alert('Customer phone is required.'); return; }

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
            base_price: parseFloat(item.basePrice || item.price || 0),
            labor_cost: parseFloat(item.labor || 0),
            price: parseFloat((item.basePrice || item.price || 0) + (item.labor || 0)),
            qty: parseInt(item.qty || 1)
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
        } else { alert('Error: ' + data.message); }
    })
    .catch(() => alert('Network error.'));
}
</script>

<script>
function printJobOrder(id) {
    fetch(APP_URL + '/api/job_orders.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert(res.message); return; }
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
            const recordedSec = parseInt(d.status_elapsed_seconds ?? d.status_timer_seconds ?? 0, 10) || 0;
            const recordedTime = `${String(Math.floor(recordedSec / 3600)).padStart(2, '0')}:${String(Math.floor((recordedSec % 3600) / 60)).padStart(2, '0')}:${String(recordedSec % 60).padStart(2, '0')}`;

            // Build item rows — same style as joPrintPreview
            let itemRows = '';
            (d.services||[]).forEach((s, i) => {
                itemRows += `
                <tr>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;">${s.service_name}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${s.quantity}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(s.service_price)}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(s.total)}</td>
                </tr>`;
            });
            (d.products||[]).forEach((p, i) => {
                itemRows += `
                <tr>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${(d.services||[]).length+i+1}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;">${p.product_name} <span style="color:#888;font-size:8pt;">(Product)</span></td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.quantity}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.unit_price)}</td>
                    <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.total)}</td>
                </tr>`;
            });
            if (!itemRows) itemRows = '<tr><td colspan="5" style="padding:10px;text-align:center;color:#999;">No items recorded</td></tr>';

            const discLabel = d.discount_type === 'senior_citizen' ? 'Senior Citizen (20%)'
                            : d.discount_type === 'pwd'            ? 'PWD (20%)'
                            : d.discount_type === 'custom'         ? 'Discount'
                            : '';

            document.getElementById('joPrintContent').innerHTML = `
    <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;padding-bottom:40mm;">

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

        <!-- Services / Items -->
        <div style="font-size:8.5pt;font-weight:700;letter-spacing:.5px;padding-bottom:3px;">SERVICES / ITEMS</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
            <colgroup>
                <col style="width:5%"><col><col style="width:8%"><col style="width:18%"><col style="width:18%">
            </colgroup>
            <thead>
                <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>${itemRows}</tbody>
        </table>

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
                    ${d.payment_status==='partial' ? `<br><span style="font-size:8pt;color:#555;">Paid: ${fmt(partialAmt)}</span><br><span style="font-size:8pt;color:#c00;font-weight:700;">Balance: ${fmt(remaining)}</span>` : ''}
                </td>
            </tr>
        </table>

        ${d.notes ? `<div style="margin-top:8px;font-size:9pt;"><strong>Notes:</strong> ${d.notes}</div>` : ''}

        <!-- Technician + Signatures pinned to bottom -->
        <div style="position:fixed;bottom:15mm;left:0;right:0;">
            <div style="font-size:9pt;margin-bottom:10px;padding-top:6px;">
                <strong>Assigned Technician:</strong> ${assignedTechnician}
                <span style="margin-left:12px;"><strong>Recorded Work Time:</strong> ${recordedTime}</span>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                <tr>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Technician Signature</div>
                    </td>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Customer Signature</div>
                    </td>
                    <td style="width:33%;text-align:center;padding:0 10px;">
                        <div style="border-top:1px solid #555;padding-top:5px;margin-top:36px;">Authorized Signature</div>
                    </td>
                </tr>
            </table>
            ${getPrintFooterHtml()}
        </div>
    </div>`;

            document.getElementById('joPrintArea').style.display = 'block';
            window.print();
            document.getElementById('joPrintArea').style.display = 'none';
        })
        .catch(() => alert('Failed to load job order for printing.'));
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
            let services = [], products = [];
            try { services = JSON.parse(d.services_json||'[]'); } catch(e){}
            try { products = JSON.parse(d.products_json||'[]'); } catch(e){}

            let svcRows = services.map((s,i) => `<tr><td>${i+1}</td><td>${s.name}</td><td class="text-center">${s.qty||1}</td><td class="text-end">${fmt(s.price)}</td></tr>`).join('');
            let prdRows = products.map((p,i) => `<tr><td>${services.length+i+1}</td><td>${p.name} <small class="text-muted">(Product)</small></td><td class="text-center">${p.qty}</td><td class="text-end">${fmt(p.price)}</td></tr>`).join('');

            document.getElementById('viewEstBody').innerHTML = `
            <div class="row g-2 mb-3">
              <div class="col-6"><strong>Estimate #:</strong> ${d.estimate_number}</div>
              <div class="col-6"><strong>Status:</strong> <span class="badge bg-${d.status==='converted'?'success':'secondary'}">${d.status}</span></div>
              <div class="col-6"><strong>Vehicle:</strong> ${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</div>
              <div class="col-6"><strong>Plate:</strong> ${d.vehicle_plate||'—'}</div>
              <div class="col-4"><strong>Year:</strong> ${d.vehicle_year||'—'}</div>
              <div class="col-4"><strong>Color:</strong> ${d.vehicle_color||'—'}</div>
              <div class="col-4"><strong>Mileage:</strong> ${d.vehicle_mileage||'—'} km</div>
            </div>
            <table class="table table-sm table-bordered mb-3">
              <thead class="table-light"><tr><th>#</th><th>Description</th><th class="text-center">Qty</th><th class="text-end">Price</th></tr></thead>
              <tbody>${svcRows||''}${prdRows||'<tr><td colspan="4" class="text-center text-muted">No items</td></tr>'}</tbody>
            </table>
            <div class="d-flex justify-content-end gap-4">
              <div><small class="text-muted">Services</small><br><strong>${fmt(d.services_total)}</strong></div>
              <div><small class="text-muted">Products</small><br><strong>${fmt(d.products_total)}</strong></div>
              <div><small class="text-muted">Grand Total</small><br><strong class="fs-5">${fmt(d.grand_total)}</strong></div>
            </div>`;
            document.getElementById('viewEstPrintBtn').onclick   = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); printEstimate(id); };
            document.getElementById('viewEstEditBtn').onclick    = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); editEstimate(id); };
            document.getElementById('viewEstConvertBtn').onclick = () => { bootstrap.Modal.getInstance(document.getElementById('viewEstimateModal')).hide(); convertEstimateToJo(d); };
        })
        .catch(() => { document.getElementById('viewEstBody').innerHTML = '<p class="text-danger p-3">Failed to load estimate.</p>'; });
}

function editEstimate(id) {
    fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert(res.message); return; }
            const d = res.data;
            document.getElementById('editEstId').value      = d.id;
            document.getElementById('editEstMake').value    = d.vehicle_make    || '';
            document.getElementById('editEstModel').value   = d.vehicle_model   || '';
            document.getElementById('editEstYear').value    = d.vehicle_year    || '';
            document.getElementById('editEstPlate').value   = d.vehicle_plate   || '';
            document.getElementById('editEstColor').value   = d.vehicle_color   || '';
            document.getElementById('editEstMileage').value = d.vehicle_mileage || '';
            document.getElementById('editEstStatus').value  = d.status          || 'draft';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editEstimateModal')).show();
        })
        .catch(() => alert('Failed to load estimate.'));
}

function saveEditEstimate() {
    const id = document.getElementById('editEstId').value;
    const payload = {
        csrf_token:      csrfToken,
        vehicle_make:    document.getElementById('editEstMake').value.trim(),
        vehicle_model:   document.getElementById('editEstModel').value.trim(),
        vehicle_year:    document.getElementById('editEstYear').value.trim(),
        vehicle_plate:   document.getElementById('editEstPlate').value.trim(),
        vehicle_color:   document.getElementById('editEstColor').value.trim(),
        vehicle_mileage: document.getElementById('editEstMileage').value.trim(),
        status:          document.getElementById('editEstStatus').value,
    };
    fetch(APP_URL + '/api/estimates.php?id=' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editEstimateModal')).hide();
            location.reload();
        } else { alert('Error: ' + data.message); }
    })
    .catch(() => alert('Network error.'));
}
</script>

<script>
function printEstimate(id) {
    fetch(APP_URL + '/api/estimates.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { alert(res.message); return; }
            const d   = res.data;
            const fmt = v => '₱' + parseFloat(v||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
            let services = [], products = [];
            try { services = JSON.parse(d.services_json||'[]'); } catch(e){}
            try { products = JSON.parse(d.products_json||'[]'); } catch(e){}
            const date = d.created_at ? new Date(d.created_at).toLocaleDateString('en-PH',{year:'numeric',month:'long',day:'numeric'}) : '—';

            let svcRows = services.map((s,i) => `
              <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${i+1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${s.name}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${s.qty||1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(s.price)}</td>
              </tr>`).join('');
            let prdRows = products.map((p,i) => `
              <tr>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${services.length+i+1}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;">${p.name} <span style="color:#888;font-size:8pt;">(Product)</span></td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:center;">${p.qty}</td>
                <td style="padding:4px 8px;border:1px solid #ccc;text-align:right;">${fmt(p.price)}</td>
              </tr>`).join('');

            document.getElementById('jePrintContent').innerHTML = `
            <div style="font-family:Arial,sans-serif;font-size:9.5pt;color:#000;line-height:1.4;">
                            ${getPrintHeaderHtml('JOB ESTIMATE', d.estimate_number || '—', date)}
              <table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:9pt;">
                <tr>
                  <td style="width:20%;padding:4px 8px;border:1px solid #ccc;font-weight:700;">Make / Model</td>
                  <td style="padding:4px 8px;border:1px solid #ccc;">${(d.vehicle_make||'')+' '+(d.vehicle_model||'')}</td>
                  <td style="width:16%;padding:4px 8px;border:1px solid #ccc;font-weight:700;">Year</td>
                  <td style="width:16%;padding:4px 8px;border:1px solid #ccc;">${d.vehicle_year||'—'}</td>
                </tr>
                <tr>
                  <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Plate No.</td>
                  <td style="padding:4px 8px;border:1px solid #ccc;">${d.vehicle_plate||'—'}</td>
                  <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Color</td>
                  <td style="padding:4px 8px;border:1px solid #ccc;">${d.vehicle_color||'—'}</td>
                </tr>
                <tr>
                  <td style="padding:4px 8px;border:1px solid #ccc;font-weight:700;">Mileage</td>
                  <td colspan="3" style="padding:4px 8px;border:1px solid #ccc;">${d.vehicle_mileage||'—'} km</td>
                </tr>
              </table>
              <div style="font-size:8.5pt;font-weight:700;padding-bottom:3px;">ESTIMATE DETAILS</div>
              <table style="width:100%;border-collapse:collapse;margin-bottom:0;font-size:9pt;">
                <colgroup><col style="width:5%"><col><col style="width:8%"><col style="width:18%"></colgroup>
                <thead>
                  <tr>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">#</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:left;">Description</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:center;">Qty</th>
                    <th style="padding:5px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;text-align:right;">Unit Price</th>
                  </tr>
                </thead>
                <tbody>${svcRows||''}${prdRows||'<tr><td colspan="4" style="padding:8px;text-align:center;color:#666;">No items</td></tr>'}</tbody>
              </table>
              <table style="width:100%;border-collapse:collapse;font-size:9pt;margin-top:0;">
                <tr>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;font-size:8.5pt;color:#555;">
                    Services Subtotal<br><strong>${fmt(d.services_total)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;">
                    Products Subtotal<br><strong>${fmt(d.products_total)}</strong>
                  </td>
                  <td style="padding:6px 8px;border-top:1.5px solid #333;border-bottom:1.5px solid #333;border-left:1px solid #ddd;font-size:8.5pt;color:#555;text-align:right;">
                    GRAND TOTAL<br><strong style="font-size:11pt;">${fmt(d.grand_total)}</strong>
                  </td>
                </tr>
              </table>
                            ${getPrintFooterHtml()}
            </div>`;
            document.getElementById('jePrintArea').style.display = 'block';
            window.print();
            document.getElementById('jePrintArea').style.display = 'none';
        })
        .catch(() => alert('Failed to load estimate for printing.'));
}
</script>

<script>
/* ═══════════════════════════════════════════
   CONVERT ESTIMATE → JOB ORDER
═══════════════════════════════════════════ */
function convertEstimateToJo(d) {
    // Parse services and products from the saved estimate
    let services = [], products = [];
    try { services = JSON.parse(d.services_json || '[]'); } catch(e) {}
    try { products = JSON.parse(d.products_json || '[]'); } catch(e) {}

    // Load services into joItems
    joItems = services.map(s => ({
        type:  'service',
        id:    s.id    || 0,
        name:  s.name  || '',
        price: parseFloat(s.price) || 0,
        qty:   parseInt(s.qty)     || 1
    }));

    // Load products into joProducts
    joProducts = products.map(p => ({
        id:    p.id    || 0,
        name:  p.name  || '',
        code:  p.code  || '',
        price: parseFloat(p.price) || 0,
        qty:   parseInt(p.qty)     || 1
    }));

    // Pre-fill vehicle fields
    const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val || ''; };
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

    // Mark estimate as converted via API (fire-and-forget)
    if (d.id && d.status !== 'converted') {
        fetch(APP_URL + '/api/estimates.php?id=' + d.id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'converted' })
        }).catch(() => {});
    }

    // Open the Create Job Order modal
    bootstrap.Modal.getOrCreateInstance(document.getElementById('createJobOrderModal')).show();
}
</script>

<?php include_once '../partials/footer.php'; ?>
