<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/JobOrder.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Staff.php';

requireLogin();

$pageTitle = 'Create Job Order';

$userModel = new User();
$staffModel = new Staff();

$technicians = [];
try {
    $staffRows = $staffModel->getAll([
        'role' => 'technician',
        'status' => 'active',
    ]);

    foreach ($staffRows as $row) {
        $firstName = trim((string)($row['first_name'] ?? ''));
        $lastName = trim((string)($row['last_name'] ?? ''));
        $fullName = trim($firstName . ' ' . $lastName);

        if ($fullName === '') {
            $fullName = (string)($row['username'] ?? ('Technician #' . (int)($row['id'] ?? 0)));
        }

        $technicians[] = [
            'id' => (int)($row['id'] ?? 0),
            'full_name' => $fullName,
        ];
    }

    usort($technicians, function ($a, $b) {
        return strcasecmp((string)($a['full_name'] ?? ''), (string)($b['full_name'] ?? ''));
    });
} catch (Throwable $e) {
    // Keep form functional even if technician loading fails.
    $technicians = [];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $jobOrderModel = new JobOrder();
        $data = [
            'customer_name' => sanitize($_POST['customer_name'] ?? ''),
            'customer_phone' => sanitize($_POST['customer_phone'] ?? ''),
            'customer_email' => sanitize($_POST['customer_email'] ?? ''),
            'customer_address' => sanitize($_POST['customer_address'] ?? ''),
            'vehicle_year' => sanitize($_POST['vehicle_year'] ?? ''),
            'vehicle_make' => sanitize($_POST['vehicle_make'] ?? ''),
            'vehicle_model' => sanitize($_POST['vehicle_model'] ?? ''),
            'vehicle_color' => sanitize($_POST['vehicle_color'] ?? ''),
            'vehicle_license' => sanitize($_POST['vehicle_license'] ?? ''),
            'vehicle_mileage' => sanitize($_POST['vehicle_mileage'] ?? ''),
            'service_type' => sanitize($_POST['service_type'] ?? ''),
            'service_description' => sanitize($_POST['service_description'] ?? ''),
            'technician_id' => !empty($_POST['technician_id']) ? (int)$_POST['technician_id'] : null,
            'technician_status' => sanitize($_POST['technician_status'] ?? 'pending'),
            'subtotal' => (float)($_POST['subtotal'] ?? 0),
            'parts_blocks' => (float)($_POST['parts_blocks'] ?? 0),
            'discount_type' => sanitize($_POST['discount_type'] ?? 'none'),
            'discount_amount' => (float)($_POST['discount_amount'] ?? 0),
            'total_amount' => (float)($_POST['total_amount'] ?? 0),
            'payment_method' => sanitize($_POST['payment_method'] ?? 'cash'),
            'payment_status' => sanitize($_POST['payment_status'] ?? 'pending'),
            'status' => sanitize($_POST['status'] ?? 'pending'),
            'backup_old_parts' => sanitize($_POST['backup_old_parts'] ?? 'no'),
            'notes' => sanitize($_POST['notes'] ?? ''),
            'created_by' => $_SESSION['user_id']
        ];

        $normalizedPhone = preg_replace('/\D+/', '', $data['customer_phone']);
        if (!preg_match('/^09\d{9}$/', $normalizedPhone)) {
            $error = 'Phone number must contain exactly 11 digits and start with 09.';
        } else {
            $data['customer_phone'] = $normalizedPhone;
            $result = $jobOrderModel->create($data);
        }
        
        if (!empty($result)) {
            setMessage('Job order created successfully', 'success');
                redirect(routeUrl('job_orders'));
        } else {
            $error = 'Failed to create job order. Please try again.';
        }
    }
}

$csrfToken = generateCSRFToken();
include __DIR__ . '/../partials/header.php';
?>

<div class="row">
    <div class="col-12 mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Create New Job Order</h4>
                <p class="text-muted small mb-0">Fill in the details below to create a new job order</p>
            </div>
            <a href="<?php echo routeUrl('job_orders'); ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo escape($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="" id="jobOrderForm" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    
    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            
            <!-- Company Header -->
            <div class="card mb-3">
                <div class="card-body text-center py-3">
                    <h5 class="mb-0 fw-bold">The Autodok</h5>
                    <p class="text-muted small mb-0">Automotive Care Services</p>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="customer_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="customer_address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" name="customer_phone" required inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="customer_email">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehicle Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Year</label>
                            <input type="text" class="form-control" name="vehicle_year" placeholder="2024">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">License Number</label>
                            <input type="text" class="form-control" name="vehicle_license" placeholder="ABC1234">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mileage</label>
                            <input type="text" class="form-control" name="vehicle_mileage" placeholder="50000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Make</label>
                            <input type="text" class="form-control" name="vehicle_make" placeholder="Toyota">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="vehicle_model" placeholder="Vios">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="vehicle_color" placeholder="White">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type of Repair <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="service_type" value="brake" id="brake" required>
                                    <label class="form-check-label" for="brake">Brake</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="service_type" value="aircon" id="aircon">
                                    <label class="form-check-label" for="aircon">Aircon</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="service_type" value="oil" id="oil">
                                    <label class="form-check-label" for="oil">Oil</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="service_type" value="fix" id="fix">
                                    <label class="form-check-label" for="fix">Fix</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="service_type" value="other" id="other">
                                    <label class="form-check-label" for="other">Other</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Do you want to back your old spare parts?</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="backup_old_parts" value="yes" id="backup_yes">
                                    <label class="form-check-label" for="backup_yes">Yes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="backup_old_parts" value="no" id="backup_no" checked>
                                    <label class="form-check-label" for="backup_no">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Customer Signature</label>
                            <div class="border rounded p-3 text-center bg-light" style="min-height: 80px;">
                                <small class="text-muted">Signature area</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Authorized Signature</label>
                            <div class="border rounded p-3 text-center bg-light" style="min-height: 80px;">
                                <small class="text-muted">Signature area</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            
            <!-- Billing -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Billing</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Subtotal</label>
                        <input type="number" class="form-control" name="subtotal" id="subtotal" value="0" step="0.01" onchange="calculateTotal()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parts/Blocks</label>
                        <input type="number" class="form-control" name="parts_blocks" id="parts_blocks" value="0" step="0.01" onchange="calculateTotal()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Type</label>
                        <select class="form-select" name="discount_type" id="discount_type" onchange="calculateTotal()">
                            <option value="none">None</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Amount</label>
                        <input type="number" class="form-control" name="discount_amount" id="discount_amount" value="0" step="0.01" onchange="calculateTotal()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Amount</label>
                        <input type="number" class="form-control fw-bold" name="total_amount" id="total_amount" value="0" step="0.01" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment</label>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="cash" id="cash" checked>
                                <label class="form-check-label" for="cash">Cash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="gcash" id="gcash">
                                <label class="form-check-label" for="gcash">GCash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="bank_transfer" id="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">Bank Transfer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="card" id="card">
                                <label class="form-check-label" for="card">Swipe/Card</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technician -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Technician</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <select class="form-select" name="technician_id" id="technician_id">
                            <option value="">Select Technician</option>
                            <?php foreach ($technicians as $tech): ?>
                            <option value="<?php echo $tech['id']; ?>"><?php echo escape($tech['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <input type="text" class="form-control" name="technician_status" value="pending" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ID</label>
                        <input type="text" class="form-control" id="tech_id_display" readonly>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save me-2"></i>Save Job Order
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print
                </button>
            </div>

        </div>
    </div>
</form>

<script>
// Calculate total amount
function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const partsBlocks = parseFloat(document.getElementById('parts_blocks').value) || 0;
    const discountType = document.getElementById('discount_type').value;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;

    let total = subtotal + partsBlocks;

    if (discountType === 'percentage') {
        total = total - (total * (discountAmount / 100));
    } else if (discountType === 'fixed') {
        total = total - discountAmount;
    }

    document.getElementById('total_amount').value = Math.max(0, total).toFixed(2);
}

// Update technician ID display
document.getElementById('technician_id').addEventListener('change', function() {
    document.getElementById('tech_id_display').value = this.value || '';
});

// Form validation
(function() {
    'use strict';
    const form = document.getElementById('jobOrderForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
