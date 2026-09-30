<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireRole('admin');

$pageTitle = 'Role Permissions Matrix';

$roles = [
    'admin' => 'Admin',
    'cashier' => 'Cashier',
    'chief_mechanic' => 'Chief Mechanic',
    'service_adviser' => 'Service Adviser',
    'technician' => 'Technician',
];

$rules = [
    [
        'feature' => 'Access dashboard',
        'description' => 'Open dashboard and view role-specific cards/data.',
        'allowed' => ['admin', 'cashier', 'chief_mechanic', 'technician'],
    ],
    [
        'feature' => 'Open Services module',
        'description' => 'Go to Services page. Job-order-only roles are routed to Job Orders tab.',
        'allowed' => ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician'],
    ],
    [
        'feature' => 'Manage services/bundles catalog',
        'description' => 'Create/update/toggle services and bundles.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Delete service/bundle records',
        'description' => 'Delete from services and bundle lists.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Create/edit full job order',
        'description' => 'Create JO and edit JO details/line items/payment details.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'View active job orders',
        'description' => 'Active statuses are pending, ongoing, under inspection, car washing, and returned for revision.',
        'allowed' => ['admin', 'cashier', 'chief_mechanic', 'service_adviser', 'technician'],
    ],
    [
        'feature' => 'Technician assignment scope',
        'description' => 'Technician sees only assigned active job orders and assigned JO counters.',
        'allowed' => ['technician'],
    ],
    [
        'feature' => 'Edit JO status inline',
        'description' => 'Change JO status in table row.',
        'allowed' => ['admin', 'cashier', 'service_adviser'],
    ],
    [
        'feature' => 'Start JO timer',
        'description' => 'Start timer for ongoing/under inspection work.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Stop JO timer',
        'description' => 'Stop timer from JO row. Technician can stop only for assigned active JOs.',
        'allowed' => ['admin', 'cashier', 'technician'],
    ],
    [
        'feature' => 'Delete job orders',
        'description' => 'Delete JO records.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Manage estimates',
        'description' => 'Create/view/edit estimates.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Delete estimates',
        'description' => 'Delete estimate records.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Inventory access',
        'description' => 'Open inventory and perform stock in/out and edits.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Delete inventory records',
        'description' => 'Delete products/categories/suppliers.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Staff management page',
        'description' => 'Open staff list and maintain staff records.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Assign admin role',
        'description' => 'Set role to Admin when creating or editing staff.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Delete staff records',
        'description' => 'Delete staff accounts from staff module.',
        'allowed' => ['admin'],
    ],
    [
        'feature' => 'Reports and Excel export',
        'description' => 'Open reports page and export report files.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Delete manual income and expense entries',
        'description' => 'Delete manual income and expense rows from the reports log; deleted entries are sent to Deleted Archive for admin review.',
        'allowed' => ['admin', 'cashier'],
    ],
    [
        'feature' => 'Settings and print template',
        'description' => 'Open settings and update print template content.',
        'allowed' => ['admin', 'cashier'],
    ],
];

$currentRole = $_SESSION['user_role'] ?? '';

include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Role Permissions Matrix</h4>
            <p class="text-muted mb-0 small">Runtime view of access rules currently enforced in UI and API.</p>
        </div>
        <a href="<?php echo routeUrl('settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small">
                <strong>Signed-in Role:</strong>
                <span class="badge bg-dark"><?php echo escape(getRoleLabel($currentRole)); ?></span>
            </div>
            <div class="small text-muted">Legend: <span class="badge bg-success">Allowed</span> <span class="badge bg-secondary">Not Allowed</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:220px;">Feature</th>
                            <th style="min-width:280px;">Notes</th>
                            <?php foreach ($roles as $roleKey => $roleLabel): ?>
                            <th class="text-center" style="min-width:120px;"><?php echo escape($roleLabel); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($rule['feature']); ?></strong>
                            </td>
                            <td class="text-muted small"><?php echo escape($rule['description']); ?></td>
                            <?php foreach ($roles as $roleKey => $roleLabel): ?>
                                <?php $isAllowed = in_array($roleKey, $rule['allowed'], true); ?>
                                <td class="text-center">
                                    <?php if ($isAllowed): ?>
                                        <span class="badge bg-success">Allowed</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Allowed</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
