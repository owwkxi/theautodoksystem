<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();

$currentUserRole = $_SESSION['user_role'] ?? '';
$isCashier = ($currentUserRole === 'cashier');

// Only admin and cashier can access inventory
if (!hasAnyRole(['admin', 'cashier'])) {
    redirect(APP_URL . '/views/services/manage.php?tab=job_orders');
}

$pageTitle = 'Inventory';

$db     = Database::getInstance()->getConnection();
$tab    = $_GET['tab'] ?? 'products';
$search = $_GET['search'] ?? '';
$catFilter  = $_GET['category'] ?? '';
$statFilter = $_GET['status'] ?? '';

// ── Handle AJAX POST actions ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    try {
        validateCSRF();
        $action = $_GET['action'];

        if ($action === 'add_product') {
          $inputCode = strtoupper(sanitize($_POST['product_code'] ?? ''));
          if ($inputCode && preg_match('/^PRD\d{2,}$/', $inputCode)) {
            $code = $inputCode;
          } else {
            $last = $db->query(
              "SELECT MAX(CAST(SUBSTRING(product_code, 4) AS UNSIGNED)) AS max_num
               FROM products
               WHERE product_code REGEXP '^PRD[0-9]+$'"
            )->fetch(PDO::FETCH_ASSOC);
            $next = (int)($last['max_num'] ?? 0) + 1;
            $code = 'PRD' . str_pad((string)$next, 2, '0', STR_PAD_LEFT);
          }
            $stmt = $db->prepare("INSERT INTO products (product_code,product_name,category_id,brand_id,unit_id,description,cost_price,selling_price,quantity,min_stock_level,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$code, sanitize($_POST['product_name']), $_POST['category_id']?:null, $_POST['brand_id']?:null, $_POST['unit_id']?:null, sanitize($_POST['description']??''), (float)$_POST['cost_price'], (float)$_POST['selling_price'], (int)$_POST['quantity'], (int)($_POST['min_stock_level']??10), sanitize($_POST['status']??'active')]);
            echo json_encode(['success'=>true,'message'=>'Product added']);
        } elseif ($action === 'edit_product') {
            $stmt = $db->prepare("UPDATE products SET product_name=?,category_id=?,brand_id=?,unit_id=?,description=?,cost_price=?,selling_price=?,min_stock_level=?,status=? WHERE id=?");
            $stmt->execute([sanitize($_POST['product_name']), $_POST['category_id']?:null, $_POST['brand_id']?:null, $_POST['unit_id']?:null, sanitize($_POST['description']??''), (float)$_POST['cost_price'], (float)$_POST['selling_price'], (int)($_POST['min_stock_level']??10), sanitize($_POST['status']), (int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Product updated']);
        } elseif ($action === 'delete_product') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete products');
          }
            $db->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Product deleted']);
        } elseif ($action === 'stock_in') {
            $id  = (int)$_POST['product_id'];
            $qty = (int)$_POST['quantity'];
            $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id=?")->execute([$qty,$id]);
            $db->prepare("INSERT INTO inventory_transactions (product_id,transaction_type,quantity,notes,created_by) VALUES (?,?,?,?,?)")->execute([$id,'stock_in',$qty,sanitize($_POST['notes']??''),$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Stock added']);
        } elseif ($action === 'stock_out') {
            $id  = (int)$_POST['product_id'];
            $qty = (int)$_POST['quantity'];
            $cur = $db->query("SELECT quantity FROM products WHERE id=$id")->fetch()['quantity'];
            if ($qty > $cur) { echo json_encode(['success'=>false,'message'=>'Insufficient stock']); exit; }
            $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id=?")->execute([$qty,$id]);
            $db->prepare("INSERT INTO inventory_transactions (product_id,transaction_type,quantity,notes,created_by) VALUES (?,?,?,?,?)")->execute([$id,'stock_out',$qty,sanitize($_POST['notes']??''),$_SESSION['user_id']]);
            echo json_encode(['success'=>true,'message'=>'Stock removed']);
        } elseif ($action === 'add_category') {
            $db->prepare("INSERT INTO product_categories (category_name,description,status) VALUES (?,?,?)")->execute([sanitize($_POST['category_name']),sanitize($_POST['description']??''),sanitize($_POST['status']??'active')]);
            echo json_encode(['success'=>true,'message'=>'Category added']);
        } elseif ($action === 'edit_category') {
            $db->prepare("UPDATE product_categories SET category_name=?,description=?,status=? WHERE id=?")->execute([sanitize($_POST['category_name']),sanitize($_POST['description']??''),sanitize($_POST['status']),(int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Category updated']);
        } elseif ($action === 'delete_category') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete categories');
          }
            $db->prepare("DELETE FROM product_categories WHERE id=?")->execute([(int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Category deleted']);
        } elseif ($action === 'add_supplier') {
            $db->prepare("INSERT INTO suppliers (supplier_name,contact_person,phone,email,address,status) VALUES (?,?,?,?,?,?)")->execute([sanitize($_POST['supplier_name']),sanitize($_POST['contact_person']??''),sanitize($_POST['phone']??''),sanitize($_POST['email']??''),sanitize($_POST['address']??''),sanitize($_POST['status']??'active')]);
            echo json_encode(['success'=>true,'message'=>'Supplier added']);
        } elseif ($action === 'edit_supplier') {
            $db->prepare("UPDATE suppliers SET supplier_name=?,contact_person=?,phone=?,email=?,address=?,status=? WHERE id=?")->execute([sanitize($_POST['supplier_name']),sanitize($_POST['contact_person']??''),sanitize($_POST['phone']??''),sanitize($_POST['email']??''),sanitize($_POST['address']??''),sanitize($_POST['status']),(int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Supplier updated']);
        } elseif ($action === 'delete_supplier') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete suppliers');
          }
            $db->prepare("DELETE FROM suppliers WHERE id=?")->execute([(int)$_POST['id']]);
            echo json_encode(['success'=>true,'message'=>'Supplier deleted']);
        } else {
            echo json_encode(['success'=>false,'message'=>'Unknown action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

// ── Fetch data for current tab ───────────────────────────────────────────────
$categories = $db->query("SELECT * FROM product_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
$brands     = $db->query("SELECT * FROM brands ORDER BY brand_name")->fetchAll(PDO::FETCH_ASSOC);
$units      = $db->query("SELECT * FROM units ORDER BY unit_name")->fetchAll(PDO::FETCH_ASSOC);
$suppliers  = $db->query("SELECT * FROM suppliers ORDER BY supplier_name")->fetchAll(PDO::FETCH_ASSOC);

// Products with joins
$pWhere = 'WHERE 1=1';
$pParams = [];
if ($search)    { $pWhere .= " AND (p.product_name LIKE ? OR p.product_code LIKE ?)"; $pParams[] = "%$search%"; $pParams[] = "%$search%"; }
if ($catFilter) { $pWhere .= " AND p.category_id = ?"; $pParams[] = $catFilter; }
if ($statFilter){ $pWhere .= " AND p.status = ?"; $pParams[] = $statFilter; }
$pStmt = $db->prepare("SELECT p.*, pc.category_name, b.brand_name, u.unit_symbol FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id LEFT JOIN brands b ON p.brand_id=b.id LEFT JOIN units u ON p.unit_id=u.id $pWhere ORDER BY p.product_name");
$pStmt->execute($pParams);
$products = $pStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalProducts  = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock       = $db->query("SELECT COUNT(*) FROM products WHERE quantity <= min_stock_level AND status='active'")->fetchColumn();
$totalValue     = $db->query("SELECT SUM(quantity * cost_price) FROM products WHERE status='active'")->fetchColumn() ?? 0;
$outOfStock     = $db->query("SELECT COUNT(*) FROM products WHERE quantity = 0 AND status='active'")->fetchColumn();

// Recent transactions
$recentTx = $db->query("SELECT it.*, p.product_name FROM inventory_transactions it LEFT JOIN products p ON it.product_id=p.id ORDER BY it.created_at DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../partials/header.php';
?>

<!-- Stats row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Total Products</div>
      <div class="fw-bold fs-4"><?php echo $totalProducts; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Low Stock</div>
      <div class="fw-bold fs-4 text-warning"><?php echo $lowStock; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Out of Stock</div>
      <div class="fw-bold fs-4 text-danger"><?php echo $outOfStock; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Inventory Value</div>
      <div class="fw-bold fs-5">₱<?php echo number_format($totalValue, 2); ?></div>
    </div></div>
  </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
  <?php foreach (['products'=>'<i class="bi bi-box-seam"></i> Products','categories'=>'<i class="bi bi-tag"></i> Categories','suppliers'=>'<i class="bi bi-truck"></i> Suppliers','transactions'=>'<i class="bi bi-arrow-left-right"></i> Transactions'] as $t=>$label): ?>
  <li class="nav-item">
    <a class="nav-link <?php echo $tab===$t?'active':''; ?>" href="?tab=<?php echo $t; ?>"
       style="color:#000;<?php echo $tab===$t?'border-bottom:2px solid #000;background:#fff;':'' ?>"><?php echo $label; ?></a>
  </li>
  <?php endforeach; ?>
</ul>

<?php if ($tab === 'products'): ?>
<!-- Search/filter bar -->
<div class="card mb-3"><div class="card-body py-2">
  <form method="GET" class="row g-2 align-items-center">
    <input type="hidden" name="tab" value="products">
    <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search product name or code..." value="<?php echo escape($search); ?>"></div>
    <div class="col-md-3">
      <select name="category" class="form-select form-select-sm">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $catFilter==$c['id']?'selected':''; ?>><?php echo escape($c['category_name']); ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="active" <?php echo $statFilter==='active'?'selected':''; ?>>Active</option>
        <option value="inactive" <?php echo $statFilter==='inactive'?'selected':''; ?>>Inactive</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Search</button>
      <a href="?tab=products" class="btn btn-sm btn-secondary ms-1"><i class="bi bi-x"></i> Clear</a>
    </div>
    <div class="col-auto ms-auto">
      <button type="button" class="btn btn-sm btn-dark" onclick="openAddProduct()"><i class="bi bi-plus-circle"></i> Add Product</button>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body p-0">
  <?php if (empty($products)): ?>
    <div class="text-center py-5"><i class="bi bi-box-seam" style="font-size:3rem;color:#ccc;"></i><p class="text-muted mt-3">No products found</p></div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover mb-0" style="font-size:13px;">
      <thead style="background:#f8f8f8;">
        <tr><th class="px-3">Code</th><th>Product Name</th><th>Category</th><th>Brand</th><th>Cost</th><th>Selling</th><th>Stock</th><th>Min</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($products as $p):
        $low = $p['quantity'] <= $p['min_stock_level'];
        $out = $p['quantity'] == 0;
      ?>
        <tr class="<?php echo $out?'table-danger':($low?'table-warning':''); ?>">
          <td class="px-3"><span class="fw-semibold text-body"><?php echo escape($p['product_code']); ?></span></td>
          <td>
            <div class="fw-semibold"><?php echo escape($p['product_name']); ?></div>
            <?php if ($p['description']): ?><small class="text-muted"><?php echo escape(substr($p['description'],0,40)); ?></small><?php endif; ?>
          </td>
          <td><?php echo escape($p['category_name']??'—'); ?></td>
          <td><?php echo escape($p['brand_name']??'—'); ?></td>
          <td>₱<?php echo number_format($p['cost_price'],2); ?></td>
          <td>₱<?php echo number_format($p['selling_price'],2); ?></td>
          <td>
            <span class="fw-bold <?php echo $out?'text-danger':($low?'text-warning':''); ?>">
              <?php echo $p['quantity']; ?> <?php echo escape($p['unit_symbol']??''); ?>
            </span>
            <?php if ($out): ?><span class="badge bg-danger ms-1">Out</span><?php elseif ($low): ?><span class="badge bg-warning text-dark ms-1">Low</span><?php endif; ?>
          </td>
          <td><?php echo $p['min_stock_level']; ?></td>
          <td><span class="badge bg-<?php echo $p['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
          <td>
            <div class="btn-group btn-group-sm">
              <button class="btn btn-outline-success py-0 px-2" onclick="openStockIn(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)" title="Stock In"><i class="bi bi-plus-lg"></i></button>
              <button class="btn btn-outline-warning py-0 px-2" onclick="openStockOut(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)" title="Stock Out"><i class="bi bi-dash-lg"></i></button>
              <button class="btn btn-outline-dark py-0 px-2" onclick="openEditProduct(<?php echo htmlspecialchars(json_encode($p),ENT_QUOTES); ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
              <?php if (!$isCashier): ?>
              <button class="btn btn-outline-danger py-0 px-2" onclick="deleteProduct(<?php echo $p['id']; ?>)" title="Delete"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div></div>
<?php endif; ?>

<?php if ($tab === 'categories'): ?>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-sm btn-dark" onclick="openAddCategory()"><i class="bi bi-plus-circle"></i> Add Category</button>
</div>
<div class="card"><div class="card-body p-0">
  <table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#f8f8f8;"><tr><th class="px-3">Category Name</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
      <tr>
        <td class="px-3 fw-semibold"><?php echo escape($c['category_name']); ?></td>
        <td><small class="text-muted"><?php echo escape($c['description']??'—'); ?></small></td>
        <td><span class="badge bg-<?php echo $c['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-dark py-0 px-2" onclick="openEditCategory(<?php echo $c['id']; ?>,'<?php echo addslashes(escape($c['category_name'])); ?>','<?php echo addslashes(escape($c['description']??'')); ?>','<?php echo $c['status']; ?>')"><i class="bi bi-pencil"></i></button>
            <?php if (!$isCashier): ?>
            <button class="btn btn-outline-danger py-0 px-2" onclick="deleteCategory(<?php echo $c['id']; ?>)"><i class="bi bi-trash"></i></button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($categories)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No categories yet</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>

<?php if ($tab === 'suppliers'): ?>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-sm btn-dark" onclick="openAddSupplier()"><i class="bi bi-plus-circle"></i> Add Supplier</button>
</div>
<div class="card"><div class="card-body p-0">
  <table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#f8f8f8;"><tr><th class="px-3">Supplier Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($suppliers as $s): ?>
      <tr>
        <td class="px-3 fw-semibold"><?php echo escape($s['supplier_name']); ?></td>
        <td><?php echo escape($s['contact_person']??'—'); ?></td>
        <td><?php echo escape($s['phone']??'—'); ?></td>
        <td><?php echo escape($s['email']??'—'); ?></td>
        <td><span class="badge bg-<?php echo $s['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($s['status']); ?></span></td>
        <td>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-dark py-0 px-2" onclick="openEditSupplier(<?php echo htmlspecialchars(json_encode($s),ENT_QUOTES); ?>)"><i class="bi bi-pencil"></i></button>
            <?php if (!$isCashier): ?>
            <button class="btn btn-outline-danger py-0 px-2" onclick="deleteSupplier(<?php echo $s['id']; ?>)"><i class="bi bi-trash"></i></button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($suppliers)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No suppliers yet</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>

<?php if ($tab === 'transactions'): ?>
<div class="card"><div class="card-body p-0">
  <table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#f8f8f8;"><tr><th class="px-3">Product</th><th>Type</th><th>Qty</th><th>Notes</th><th>Date</th></tr></thead>
    <tbody>
    <?php foreach ($recentTx as $tx): ?>
      <tr>
        <td class="px-3"><?php echo escape($tx['product_name']??'—'); ?></td>
        <td><span class="badge bg-<?php echo $tx['transaction_type']==='stock_in'?'success':($tx['transaction_type']==='stock_out'?'warning':'secondary'); ?>"><?php echo ucfirst(str_replace('_',' ',$tx['transaction_type'])); ?></span></td>
        <td class="fw-bold <?php echo $tx['transaction_type']==='stock_in'?'text-success':'text-warning'; ?>"><?php echo ($tx['transaction_type']==='stock_in'?'+':'-').$tx['quantity']; ?></td>
        <td><small class="text-muted"><?php echo escape($tx['notes']??'—'); ?></small></td>
        <td><?php echo date('M d, Y h:i A', strtotime($tx['created_at'])); ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($recentTx)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No transactions yet</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?php endif; ?>

<!-- ── ADD PRODUCT MODAL ─────────────────────────────────────────────────── -->
<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add Product</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label form-label-sm">Product Name *</label><input type="text" class="form-control form-control-sm" id="ap_name" required></div>
        <div class="col-md-6"><label class="form-label form-label-sm">Product Code <small class="text-muted">(PRD##, auto if blank)</small></label><input type="text" class="form-control form-control-sm" id="ap_code" placeholder="PRD01"></div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Category</label>
          <select class="form-select form-select-sm" id="ap_category">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo escape($c['category_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Brand</label>
          <select class="form-select form-select-sm" id="ap_brand">
            <option value="">— None —</option>
            <?php foreach ($brands as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo escape($b['brand_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Unit</label>
          <select class="form-select form-select-sm" id="ap_unit">
            <option value="">— None —</option>
            <?php foreach ($units as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo escape($u['unit_name']); ?> (<?php echo escape($u['unit_symbol']); ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label form-label-sm">Description</label><textarea class="form-control form-control-sm" id="ap_desc" rows="2"></textarea></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Cost Price (₱) *</label><input type="number" class="form-control form-control-sm" id="ap_cost" step="0.01" min="0" value="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Selling Price (₱) *</label><input type="number" class="form-control form-control-sm" id="ap_sell" step="0.01" min="0" value="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Initial Stock</label><input type="number" class="form-control form-control-sm" id="ap_qty" min="0" value="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Min Stock Level</label><input type="number" class="form-control form-control-sm" id="ap_min" min="0" value="10"></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Status</label><select class="form-select form-select-sm" id="ap_status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-dark btn-sm" onclick="saveAddProduct()"><i class="bi bi-save"></i> Save Product</button>
    </div>
  </div></div>
</div>

<!-- ── EDIT PRODUCT MODAL ────────────────────────────────────────────────── -->
<div class="modal fade" id="editProductModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Edit Product</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="ep_id">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label form-label-sm">Product Name *</label><input type="text" class="form-control form-control-sm" id="ep_name" required></div>
        <div class="col-md-6"><label class="form-label form-label-sm">Product Code</label><input type="text" class="form-control form-control-sm" id="ep_code" readonly style="background:#f5f5f5;"></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Category</label><select class="form-select form-select-sm" id="ep_category"><option value="">— None —</option><?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo escape($c['category_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Brand</label><select class="form-select form-select-sm" id="ep_brand"><option value="">— None —</option><?php foreach ($brands as $b): ?><option value="<?php echo $b['id']; ?>"><?php echo escape($b['brand_name']); ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Unit</label><select class="form-select form-select-sm" id="ep_unit"><option value="">— None —</option><?php foreach ($units as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo escape($u['unit_name']); ?> (<?php echo escape($u['unit_symbol']); ?>)</option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label form-label-sm">Description</label><textarea class="form-control form-control-sm" id="ep_desc" rows="2"></textarea></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Cost Price (₱)</label><input type="number" class="form-control form-control-sm" id="ep_cost" step="0.01" min="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Selling Price (₱)</label><input type="number" class="form-control form-control-sm" id="ep_sell" step="0.01" min="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Min Stock Level</label><input type="number" class="form-control form-control-sm" id="ep_min" min="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Status</label><select class="form-select form-select-sm" id="ep_status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-dark btn-sm" onclick="saveEditProduct()"><i class="bi bi-save"></i> Save Changes</button>
    </div>
  </div></div>
</div>

<!-- ── STOCK IN MODAL ────────────────────────────────────────────────────── -->
<div class="modal fade" id="stockInModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-plus-lg me-2 text-success"></i>Stock In</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="si_product_id">
      <div class="mb-3"><label class="form-label form-label-sm">Product</label><input type="text" class="form-control form-control-sm" id="si_product_name" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Current Stock</label><input type="text" class="form-control form-control-sm" id="si_current" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Quantity to Add *</label><input type="number" class="form-control form-control-sm" id="si_qty" min="1" value="1"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Notes</label><textarea class="form-control form-control-sm" id="si_notes" rows="2" placeholder="e.g. Purchase from supplier..."></textarea></div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-success btn-sm" onclick="saveStockIn()"><i class="bi bi-plus-lg"></i> Add Stock</button>
    </div>
  </div></div>
</div>

<!-- ── STOCK OUT MODAL ───────────────────────────────────────────────────── -->
<div class="modal fade" id="stockOutModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-dash-lg me-2 text-warning"></i>Stock Out</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="so_product_id">
      <div class="mb-3"><label class="form-label form-label-sm">Product</label><input type="text" class="form-control form-control-sm" id="so_product_name" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Current Stock</label><input type="text" class="form-control form-control-sm" id="so_current" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Quantity to Remove *</label><input type="number" class="form-control form-control-sm" id="so_qty" min="1" value="1"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Notes</label><textarea class="form-control form-control-sm" id="so_notes" rows="2" placeholder="e.g. Used in job order..."></textarea></div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-warning btn-sm" onclick="saveStockOut()"><i class="bi bi-dash-lg"></i> Remove Stock</button>
    </div>
  </div></div>
</div>

<!-- ── CATEGORY MODALS ───────────────────────────────────────────────────── -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold" id="catModalTitle"><i class="bi bi-tag me-2"></i>Category</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="cat_id">
      <div class="mb-3"><label class="form-label form-label-sm">Category Name *</label><input type="text" class="form-control form-control-sm" id="cat_name" required></div>
      <div class="mb-3"><label class="form-label form-label-sm">Description</label><textarea class="form-control form-control-sm" id="cat_desc" rows="2"></textarea></div>
      <div class="mb-3"><label class="form-label form-label-sm">Status</label><select class="form-select form-select-sm" id="cat_status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-dark btn-sm" onclick="saveCategory()"><i class="bi bi-save"></i> Save</button>
    </div>
  </div></div>
</div>

<!-- ── SUPPLIER MODALS ───────────────────────────────────────────────────── -->
<div class="modal fade" id="supplierModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold" id="supModalTitle"><i class="bi bi-truck me-2"></i>Supplier</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="sup_id">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label form-label-sm">Supplier Name *</label><input type="text" class="form-control form-control-sm" id="sup_name" required></div>
        <div class="col-md-6"><label class="form-label form-label-sm">Contact Person</label><input type="text" class="form-control form-control-sm" id="sup_contact"></div>
        <div class="col-md-6"><label class="form-label form-label-sm">Phone</label><input type="text" class="form-control form-control-sm" id="sup_phone"></div>
        <div class="col-md-6"><label class="form-label form-label-sm">Email</label><input type="email" class="form-control form-control-sm" id="sup_email"></div>
        <div class="col-12"><label class="form-label form-label-sm">Address</label><textarea class="form-control form-control-sm" id="sup_address" rows="2"></textarea></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Status</label><select class="form-select form-select-sm" id="sup_status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-dark btn-sm" onclick="saveSupplier()"><i class="bi bi-save"></i> Save</button>
    </div>
  </div></div>
</div>

<script>
const INV_URL  = '<?php echo APP_URL; ?>/views/inventory/index.php';
const INV_CSRF = '<?php echo generateCSRFToken(); ?>';

function invPost(action, data, onSuccess) {
    const params = new URLSearchParams({ csrf_token: INV_CSRF, ...data });
    fetch(INV_URL + '?action=' + action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(r => r.json())
    .then(d => { if (d.success) { onSuccess(); location.reload(); } else alert('Error: ' + d.message); })
    .catch(() => alert('Network error.'));
}

/* ── Products ── */
function openAddProduct() {
    document.getElementById('ap_name').value = '';
    document.getElementById('ap_code').value = '';
    document.getElementById('ap_desc').value = '';
    document.getElementById('ap_cost').value = '0';
    document.getElementById('ap_sell').value = '0';
    document.getElementById('ap_qty').value  = '0';
    document.getElementById('ap_min').value  = '10';
    document.getElementById('ap_category').value = '';
    document.getElementById('ap_brand').value    = '';
    document.getElementById('ap_unit').value     = '';
    document.getElementById('ap_status').value   = 'active';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('addProductModal')).show();
}

function saveAddProduct() {
    const name = document.getElementById('ap_name').value.trim();
    if (!name) { alert('Product name is required.'); return; }
    invPost('add_product', {
        product_name:   name,
        product_code:   document.getElementById('ap_code').value.trim(),
        category_id:    document.getElementById('ap_category').value,
        brand_id:       document.getElementById('ap_brand').value,
        unit_id:        document.getElementById('ap_unit').value,
        description:    document.getElementById('ap_desc').value.trim(),
        cost_price:     document.getElementById('ap_cost').value,
        selling_price:  document.getElementById('ap_sell').value,
        quantity:       document.getElementById('ap_qty').value,
        min_stock_level:document.getElementById('ap_min').value,
        status:         document.getElementById('ap_status').value,
    }, () => bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide());
}

function openEditProduct(p) {
    document.getElementById('ep_id').value       = p.id;
    document.getElementById('ep_name').value     = p.product_name;
    document.getElementById('ep_code').value     = p.product_code;
    document.getElementById('ep_desc').value     = p.description || '';
    document.getElementById('ep_cost').value     = p.cost_price;
    document.getElementById('ep_sell').value     = p.selling_price;
    document.getElementById('ep_min').value      = p.min_stock_level;
    document.getElementById('ep_status').value   = p.status;
    document.getElementById('ep_category').value = p.category_id || '';
    document.getElementById('ep_brand').value    = p.brand_id    || '';
    document.getElementById('ep_unit').value     = p.unit_id     || '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal')).show();
}

function saveEditProduct() {
    const name = document.getElementById('ep_name').value.trim();
    if (!name) { alert('Product name is required.'); return; }
    invPost('edit_product', {
        id:             document.getElementById('ep_id').value,
        product_name:   name,
        category_id:    document.getElementById('ep_category').value,
        brand_id:       document.getElementById('ep_brand').value,
        unit_id:        document.getElementById('ep_unit').value,
        description:    document.getElementById('ep_desc').value.trim(),
        cost_price:     document.getElementById('ep_cost').value,
        selling_price:  document.getElementById('ep_sell').value,
        min_stock_level:document.getElementById('ep_min').value,
        status:         document.getElementById('ep_status').value,
    }, () => bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide());
}

function deleteProduct(id) {
  appConfirm('Delete this product? This cannot be undone.', {
    title: 'Delete Product',
    confirmText: 'Delete',
    variant: 'danger'
  }).then(confirmed => {
    if (!confirmed) return;
    invPost('delete_product', { id }, () => {});
  });
}

/* ── Stock In / Out ── */
function openStockIn(id, name, current) {
    document.getElementById('si_product_id').value   = id;
    document.getElementById('si_product_name').value = name;
    document.getElementById('si_current').value      = current;
    document.getElementById('si_qty').value           = 1;
    document.getElementById('si_notes').value         = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('stockInModal')).show();
}

function saveStockIn() {
    const qty = parseInt(document.getElementById('si_qty').value);
    if (!qty || qty < 1) { alert('Enter a valid quantity.'); return; }
    invPost('stock_in', {
        product_id: document.getElementById('si_product_id').value,
        quantity:   qty,
        notes:      document.getElementById('si_notes').value.trim(),
    }, () => bootstrap.Modal.getInstance(document.getElementById('stockInModal')).hide());
}

function openStockOut(id, name, current) {
    document.getElementById('so_product_id').value   = id;
    document.getElementById('so_product_name').value = name;
    document.getElementById('so_current').value      = current;
    document.getElementById('so_qty').value           = 1;
    document.getElementById('so_notes').value         = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('stockOutModal')).show();
}

function saveStockOut() {
    const qty = parseInt(document.getElementById('so_qty').value);
    if (!qty || qty < 1) { alert('Enter a valid quantity.'); return; }
    invPost('stock_out', {
        product_id: document.getElementById('so_product_id').value,
        quantity:   qty,
        notes:      document.getElementById('so_notes').value.trim(),
    }, () => bootstrap.Modal.getInstance(document.getElementById('stockOutModal')).hide());
}

/* ── Categories ── */
function openAddCategory() {
    document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-tag me-2"></i>Add Category';
    document.getElementById('cat_id').value     = '';
    document.getElementById('cat_name').value   = '';
    document.getElementById('cat_desc').value   = '';
    document.getElementById('cat_status').value = 'active';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryModal')).show();
}

function openEditCategory(id, name, desc, status) {
    document.getElementById('catModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Category';
    document.getElementById('cat_id').value     = id;
    document.getElementById('cat_name').value   = name;
    document.getElementById('cat_desc').value   = desc;
    document.getElementById('cat_status').value = status;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryModal')).show();
}

function saveCategory() {
    const name = document.getElementById('cat_name').value.trim();
    if (!name) { alert('Category name is required.'); return; }
    const id = document.getElementById('cat_id').value;
    invPost(id ? 'edit_category' : 'add_category', {
        id, category_name: name,
        description: document.getElementById('cat_desc').value.trim(),
        status:      document.getElementById('cat_status').value,
    }, () => bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide());
}

function deleteCategory(id) {
  appConfirm('Delete this category?', {
    title: 'Delete Category',
    confirmText: 'Delete',
    variant: 'danger'
  }).then(confirmed => {
    if (!confirmed) return;
    invPost('delete_category', { id }, () => {});
  });
}

/* ── Suppliers ── */
function openAddSupplier() {
    document.getElementById('supModalTitle').innerHTML = '<i class="bi bi-truck me-2"></i>Add Supplier';
    ['sup_id','sup_name','sup_contact','sup_phone','sup_email','sup_address'].forEach(i => document.getElementById(i).value = '');
    document.getElementById('sup_status').value = 'active';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierModal')).show();
}

function openEditSupplier(s) {
    document.getElementById('supModalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Supplier';
    document.getElementById('sup_id').value      = s.id;
    document.getElementById('sup_name').value    = s.supplier_name;
    document.getElementById('sup_contact').value = s.contact_person || '';
    document.getElementById('sup_phone').value   = s.phone   || '';
    document.getElementById('sup_email').value   = s.email   || '';
    document.getElementById('sup_address').value = s.address || '';
    document.getElementById('sup_status').value  = s.status;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierModal')).show();
}

function saveSupplier() {
    const name = document.getElementById('sup_name').value.trim();
    if (!name) { alert('Supplier name is required.'); return; }
    const id = document.getElementById('sup_id').value;
    invPost(id ? 'edit_supplier' : 'add_supplier', {
        id,
        supplier_name:   name,
        contact_person:  document.getElementById('sup_contact').value.trim(),
        phone:           document.getElementById('sup_phone').value.trim(),
        email:           document.getElementById('sup_email').value.trim(),
        address:         document.getElementById('sup_address').value.trim(),
        status:          document.getElementById('sup_status').value,
    }, () => bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide());
}

function deleteSupplier(id) {
  appConfirm('Delete this supplier?', {
    title: 'Delete Supplier',
    confirmText: 'Delete',
    variant: 'danger'
  }).then(confirmed => {
    if (!confirmed) return;
    invPost('delete_supplier', { id }, () => {});
  });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
