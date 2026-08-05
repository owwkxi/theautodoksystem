<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../models/Staff.php';

// Require login and admin/cashier role
requireLogin();
requireAnyRole(['admin', 'cashier', 'chief_mechanic', 'service_adviser']);

$isCashier = (($_SESSION['user_role'] ?? '') === 'cashier');
$canManageStaff = hasAnyRole(['admin', 'cashier']);

$pageTitle = 'Staff Management';

// Get filters
$filters = [
    'role' => $_GET['role'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'limit' => RECORDS_PER_PAGE,
    'offset' => (($_GET['page'] ?? 1) - 1) * RECORDS_PER_PAGE
];

// Get staff
$staffModel = new Staff();
$staffList = $staffModel->getAll($filters);
$totalRecords = $staffModel->count($filters);
$pagination = paginate($totalRecords, $_GET['page'] ?? 1);

// Get statistics
$stats = $staffModel->getStats();

include __DIR__ . '/../partials/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Staff Management</h4>
        <p class="text-muted small mb-0">Manage all staff members and their information</p>
    </div>
    <?php if ($canManageStaff): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="bi bi-plus-circle"></i> Add New Staff
    </button>
    <?php endif; ?>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Total Staff</p>
                        <h3 class="mb-0"><?php echo $stats['total_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Active Staff</p>
                        <h3 class="mb-0"><?php echo $stats['active_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Technicians</p>
                        <h3 class="mb-0"><?php echo $stats['technician_count'] ?? 0; ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-tools" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1 small">Inactive Staff</p>
                        <h3 class="mb-0"><?php echo $stats['inactive_staff'] ?? 0; ?></h3>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" 
                      placeholder="Search by name, login ID, email, or staff ID..." 
                       value="<?php echo escape($filters['search']); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="admin" <?php echo $filters['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="cashier" <?php echo $filters['role'] === 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                    <option value="chief_mechanic" <?php echo $filters['role'] === 'chief_mechanic' ? 'selected' : ''; ?>>Chief Mechanic</option>
                    <option value="service_adviser" <?php echo $filters['role'] === 'service_adviser' ? 'selected' : ''; ?>>Service Adviser</option>
                    <option value="technician" <?php echo $filters['role'] === 'technician' ? 'selected' : ''; ?>>Technician</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="on_leave" <?php echo $filters['status'] === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?php echo APP_URL; ?>/views/staff/index.php" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Staff Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Login ID</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staffList)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No staff members found</p>
                            <?php if ($canManageStaff): ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                                <i class="bi bi-plus-circle"></i> Add First Staff Member
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($staffList as $staff): ?>
                        <tr>
                            <td><strong><?php echo escape($staff['staff_id']); ?></strong></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($staff['profile_image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . escape($staff['profile_image']); ?>" 
                                             alt="Profile" class="rounded-circle me-2" 
                                             style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" 
                                             style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                                            <?php echo strtoupper(substr($staff['full_name'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?php echo escape($staff['full_name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo escape(getRoleLabel($staff['role'])); ?>
                                </span>
                            </td>
                            <td><?php echo escape($staff['staff_id']); ?></td>
                            <td><?php echo escape($staff['contact_number']); ?></td>
                            <td><?php echo escape($staff['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $staff['status'] === 'active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo escape(getStatusLabel($staff['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($staff['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm staff-action-group">
                                    <button type="button" class="btn btn-outline-primary btn-icon" 
                                            onclick="viewStaff(<?php echo $staff['id']; ?>)" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <?php if ($canManageStaff && !($isCashier && $staff['role'] === 'admin')): ?>
                                    <button type="button" class="btn btn-outline-warning btn-icon" 
                                            onclick="editStaff(<?php echo $staff['id']; ?>)" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canManageStaff && !($isCashier && $staff['role'] === 'admin')): ?>
                                    <button type="button" class="btn btn-outline-<?php echo $staff['status'] === 'active' ? 'danger' : 'success'; ?> btn-icon" 
                                            onclick="toggleStatus(<?php echo $staff['id']; ?>, '<?php echo $staff['status']; ?>')" 
                                            title="<?php echo $staff['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                        <i class="bi bi-<?php echo $staff['status'] === 'active' ? 'x-circle' : 'check-circle'; ?>"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($canManageStaff && !$isCashier): ?>
                                    <button type="button" class="btn btn-outline-danger btn-icon" 
                                            onclick="deleteStaff(<?php echo $staff['id']; ?>)" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm mb-0 justify-content-center">
                <li class="page-item <?php echo !$pagination['has_previous'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        Previous
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&role=<?php echo $filters['role']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        Next
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-center mt-2">
            <small class="text-muted">
                Showing <?php echo $pagination['offset'] + 1; ?> to 
                <?php echo min($pagination['offset'] + $pagination['records_per_page'], $pagination['total_records']); ?> 
                of <?php echo $pagination['total_records']; ?> records
            </small>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($canManageStaff): ?>
<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">
                    <i class="bi bi-person-plus"></i> Add New Staff Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStaffForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" value="Auto-generated 5-digit ID" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="add_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add_confirm_password" name="confirm_password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-12">
                            <label for="add_address" class="form-label">Address</label>
                            <textarea class="form-control" id="add_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="add_role" class="form-label">Role/Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_role" name="role" required>
                                <option value="">Select Role</option>
                                <?php if (!$isCashier): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="cashier">Cashier</option>
                                <option value="chief_mechanic">Chief Mechanic</option>
                                <option value="service_adviser">Service Adviser</option>
                                <option value="technician">Technician</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="add_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="add_profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="add_profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed: JPG, JPEG, PNG</small>
                            <div id="add_image_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Staff Modal -->
<div class="modal fade" id="editStaffModal" tabindex="-1" aria-labelledby="editStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStaffModalLabel">
                    <i class="bi bi-pencil"></i> Edit Staff Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStaffForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_staff_id" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Login ID</label>
                            <input type="text" class="form-control" id="edit_staff_login_id" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_contact_number" name="contact_number" required>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_role" class="form-label">Role/Position <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="">Select Role</option>
                                <?php if (!$isCashier): ?>
                                <option value="admin">Admin</option>
                                <?php endif; ?>
                                <option value="cashier">Cashier</option>
                                <option value="chief_mechanic">Chief Mechanic</option>
                                <option value="service_adviser">Service Adviser</option>
                                <option value="technician">Technician</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="edit_profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="edit_profile_image" name="profile_image" accept="image/*">
                            <small class="text-muted">Max file size: 5MB. Allowed: JPG, JPEG, PNG</small>
                            <div id="edit_image_preview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Staff
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- View Staff Modal -->
<div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewStaffModalLabel">
                    <i class="bi bi-eye"></i> Staff Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewStaffContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    const APP_URL = '<?php echo APP_URL; ?>';
</script>
<script src="<?php echo APP_URL; ?>/assets/js/staff.js?v=<?php echo time(); ?>"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
