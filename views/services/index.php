<?php
session_start();
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . routeUrl('login'));
    exit;
}

$pageTitle = 'Services Catalog';
include __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Services Catalog</h4>
            <p class="text-muted small mb-0">Browse available services and packages</p>
        </div>
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createJobOrderModal">
            <i class="bi bi-plus-circle"></i> Create Job Order
        </button>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" id="searchServices" 
                           placeholder="Search services or packages...">
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="filterType">
                        <option value="">All Services</option>
                        <option value="bundles">Packages Only</option>
                        <option value="individual">Individual Only</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Packages Section -->
    <div class="mb-5" id="bundlesSection">
        <h5 class="mb-3">Service Packages</h5>
        <div class="row g-3" id="bundlesContainer">
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="mb-5" id="servicesSection">
        <h5 class="mb-3">Services</h5>
        <div class="row g-3" id="servicesContainer">
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Job Order Modal -->
<div class="modal fade" id="createJobOrderModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Job Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="jobOrderForm">
                    <div class="row">
                        <!-- Left Column: Customer & Vehicle Info -->
                        <div class="col-md-7">
                            <!-- Customer Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-person"></i> Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Name *</label>
                                            <input type="text" class="form-control" name="customer_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone *</label>
                                            <input type="tel" class="form-control" name="customer_phone" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g, '')">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="customer_email">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Address</label>
                                            <input type="text" class="form-control" name="customer_address">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Year</label>
                                            <input type="text" class="form-control" name="vehicle_year">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Make</label>
                                            <input type="text" class="form-control" name="vehicle_make">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Model</label>
                                            <input type="text" class="form-control" name="vehicle_model">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">License Plate</label>
                                            <input type="text" class="form-control" name="vehicle_license">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Color</label>
                                            <input type="text" class="form-control" name="vehicle_color">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Selected Services & Billing -->
                        <div class="col-md-5">
                            <!-- Selected Services -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-list-check"></i> Selected Services</h6>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <div id="selectedServicesList">
                                        <p class="text-muted small text-center py-3 mb-0">
                                            No services selected yet
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Summary -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-receipt"></i> Billing Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span>Services Subtotal:</span>
                                        <span class="fw-bold" id="servicesSubtotal">₱0.00</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Parts Cost</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="parts_cost" id="partsCost" value="0" step="0.01" min="0">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Discount Type</label>
                                        <select class="form-select form-select-sm" name="discount_type" id="discountType">
                                            <option value="none">None</option>
                                            <option value="percentage">Percentage</option>
                                            <option value="fixed">Fixed Amount</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Discount Amount</label>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="discount_amount" id="discountAmount" value="0" step="0.01" min="0">
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Total Amount:</span>
                                        <span class="fw-bold fs-4" id="totalAmount">₱0.00</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label small">Payment Method</label>
                                        <select class="form-select form-select-sm" name="payment_method">
                                            <option value="cash">Cash</option>
                                            <option value="gcash">GCash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="card">Swipe/Card</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Payment Status</label>
                                        <select class="form-select form-select-sm" name="payment_status">
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="partial">Partial</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label small">Notes</label>
                                        <textarea class="form-control form-control-sm" name="notes" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-dark" id="submitJobOrder">
                    <i class="bi bi-save"></i> Create Job Order
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo APP_URL; ?>/assets/js/services.js"></script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
