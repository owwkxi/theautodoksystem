<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/JobOrder.php';
require_once __DIR__ . '/../../models/User.php';

// Require login
requireLogin();

$pageTitle = 'Job Orders';

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'limit' => RECORDS_PER_PAGE,
    'offset' => (($_GET['page'] ?? 1) - 1) * RECORDS_PER_PAGE
];

// Get job orders
$jobOrderModel = new JobOrder();
$jobOrders = $jobOrderModel->getAll($filters);
$totalRecords = $jobOrderModel->count($filters);
$pagination = paginate($totalRecords, $_GET['page'] ?? 1);

// Get technicians for assignment
$userModel = new User();
$technicians = $userModel->getTechnicians();

include __DIR__ . '/../partials/header.php';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Job Orders Management</h4>
        <p class="text-muted small mb-0">Manage all job orders and service requests</p>
    </div>
    <a href="<?php echo APP_URL; ?>/views/job_orders/create.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> New Job Order
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" 
                       placeholder="Search by job order #, customer, or vehicle..." 
                       value="<?php echo escape($filters['search']); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $filters['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="<?php echo APP_URL; ?>/views/job_orders/index.php" class="btn btn-secondary w-100">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Job Orders Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Job Order #</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Service</th>
                        <th>Technician</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobOrders)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <p class="text-muted mt-3">No job orders found</p>
                            <a href="<?php echo APP_URL; ?>/views/job_orders/create.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create First Job Order
                            </a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($jobOrders as $job): ?>
                        <tr>
                            <td><strong><?php echo escape($job['job_order_number']); ?></strong></td>
                            <td>
                                <div><?php echo escape($job['customer_name']); ?></div>
                                <small class="text-muted"><?php echo escape($job['customer_phone']); ?></small>
                            </td>
                            <td>
                                <div><?php echo escape($job['vehicle_make'] . ' ' . $job['vehicle_model']); ?></div>
                                <small class="text-muted"><?php echo escape($job['vehicle_license']); ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?php echo escape(ucfirst($job['service_type'])); ?></span></td>
                            <td><?php echo escape($job['technician_name'] ?? 'Unassigned'); ?></td>
                            <td><?php echo formatCurrency($job['total_amount']); ?></td>
                            <td>
                                <span class="badge <?php echo $job['payment_status'] === 'paid' ? 'bg-success' : ($job['payment_status'] === 'partial' ? 'bg-warning' : 'bg-secondary'); ?>">
                                    <?php echo ucfirst($job['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge status-<?php echo $job['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($job['created_at']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo APP_URL; ?>/views/job_orders/view.php?id=<?php echo $job['id']; ?>" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/views/job_orders/edit.php?id=<?php echo $job['id']; ?>" 
                                       class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if (hasRole('admin')): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteJobOrder(<?php echo $job['id']; ?>)" title="Delete">
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
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        Previous
                    </a>
                </li>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <li class="page-item <?php echo !$pagination['has_next'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo urlencode($filters['search']); ?>">
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

<script>
function deleteJobOrder(id) {
    appConfirm('Are you sure you want to delete this job order? This action cannot be undone.', {
        title: 'Delete Job Order',
        confirmText: 'Delete',
        variant: 'danger'
    }).then(confirmed => {
        if (!confirmed) return;
        fetch('<?php echo APP_URL; ?>/api/job_orders.php?id=' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Job order deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting job order');
            console.error('Error:', error);
        });
    });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
