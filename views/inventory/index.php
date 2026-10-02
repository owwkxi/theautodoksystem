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
$isStockman = ($currentUserRole === 'stockman');

// Admin, cashier, and stockman can access inventory
if (!hasAnyRole(['admin', 'cashier', 'stockman'])) {
  redirect(routeUrl('services', ['tab' => 'job_orders']));
}

$pageTitle = 'Inventory';

$db     = Database::getInstance()->getConnection();
$tab    = $_GET['tab'] ?? 'products';
$search = $_GET['search'] ?? '';
$catFilter  = $_GET['category'] ?? '';
$statFilter = $_GET['status'] ?? '';
$transactionDate = $_GET['transaction_date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $transactionDate)) {
  $transactionDate = date('Y-m-d');
}

// ── Handle AJAX GET actions ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_supplier_history') {
    header('Content-Type: application/json');
    try {
        $supplierId = (int)($_GET['supplier_id'] ?? 0);
        if ($supplierId <= 0) throw new Exception('Invalid supplier ID');
        $stmt = $db->prepare("SELECT * FROM supplier_transactions WHERE supplier_id = ? ORDER BY transaction_date DESC, created_at DESC LIMIT 100");
        $stmt->execute([$supplierId]);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Handle AJAX POST actions ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    try {
        validateCSRF();
        $action = $_GET['action'];
      $actorName = sanitizeTextValue($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');

        if ($action === 'add_product') {
          $inputCode = strtoupper(sanitizeTextValue($_POST['product_code'] ?? ''));
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
            $productName = sanitizeTextValue($_POST['product_name'] ?? '');
            $description = sanitizeTextValue($_POST['description'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            // Resolve brand: find or create
            $brandId = null;
            $brandText = trim($_POST['brand'] ?? '');
            if ($brandText !== '') {
                $existingBrand = $db->prepare("SELECT id FROM brands WHERE brand_name = ? LIMIT 1");
                $existingBrand->execute([$brandText]);
                $brandRow = $existingBrand->fetch(PDO::FETCH_ASSOC);
                if ($brandRow) {
                    $brandId = (int)$brandRow['id'];
                } else {
                    $db->prepare("INSERT INTO brands (brand_name, status) VALUES (?, 'active')")->execute([$brandText]);
                    $brandId = (int)$db->lastInsertId();
                }
            }
            $stmt = $db->prepare("INSERT INTO products (product_code,product_name,category_id,brand_id,unit_id,description,supplier_id,cost_price,selling_price,quantity,min_stock_level,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$code, $productName !== '' ? $productName : 'Unnamed Product', $_POST['category_id'] ?: null, $brandId, $_POST['unit_id'] ?: null, $description, $_POST['supplier_id'] ?: null, (float)($_POST['cost_price'] ?? 0), (float)($_POST['selling_price'] ?? 0), max(0, (int)($_POST['quantity'] ?? 0)), max(0, (int)($_POST['min_stock_level'] ?? 10)), $status]);
            $newProductId = (int)$db->lastInsertId();
            $normalizedProductName = $productName !== '' ? $productName : 'Unnamed Product';
            logActivity((int)($_SESSION['user_id'] ?? 0), 'add_product', 'Added product: ' . $normalizedProductName . ' [' . $code . ']');
            notifyRoles(
              'system',
              'Product Added',
              buildNotificationMessageTemplate($actorName, 'added product', $normalizedProductName, 'Code: ' . $code . ', Status: ' . strtoupper($status)),
              ['admin', 'cashier'],
              [
                'reference_type' => 'product',
                'reference_id' => $newProductId,
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Product added']);
        } elseif ($action === 'edit_product') {
            $rawProductId = trim((string)($_POST['id'] ?? ''));
            $productId = $rawProductId !== '' ? (int)$rawProductId : 0;
            $productName = sanitizeTextValue($_POST['product_name'] ?? '');
            $description = sanitizeTextValue($_POST['description'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            // Resolve brand: find or create
            $brandId = null;
            $brandText = trim($_POST['brand'] ?? '');
            if ($brandText !== '') {
                $existingBrand = $db->prepare("SELECT id FROM brands WHERE brand_name = ? LIMIT 1");
                $existingBrand->execute([$brandText]);
                $brandRow = $existingBrand->fetch(PDO::FETCH_ASSOC);
                if ($brandRow) {
                    $brandId = (int)$brandRow['id'];
                } else {
                    $db->prepare("INSERT INTO brands (brand_name, status) VALUES (?, 'active')")->execute([$brandText]);
                    $brandId = (int)$db->lastInsertId();
                }
            }

            if ($productId > 0) {
                $existingProductStmt = $db->prepare("SELECT product_name, status FROM products WHERE id=? LIMIT 1");
                $existingProductStmt->execute([$productId]);
                $existingProduct = $existingProductStmt->fetch(PDO::FETCH_ASSOC);
                if (!$existingProduct) {
                    throw new Exception('Product not found');
                }
                $stmt = $db->prepare("UPDATE products SET product_name=?,category_id=?,brand_id=?,unit_id=?,description=?,supplier_id=?,cost_price=?,selling_price=?,min_stock_level=?,status=? WHERE id=?");
                $stmt->execute([$productName !== '' ? $productName : 'Unnamed Product', $_POST['category_id'] ?: null, $brandId, $_POST['unit_id'] ?: null, $description, $_POST['supplier_id'] ?: null, (float)($_POST['cost_price'] ?? 0), (float)($_POST['selling_price'] ?? 0), max(0, (int)($_POST['min_stock_level'] ?? 10)), $status, $productId]);
                $normalizedProductName = $productName !== '' ? $productName : 'Unnamed Product';
                $oldStatus = strtoupper((string)($existingProduct['status'] ?? 'unknown'));
                $newStatus = strtoupper((string)$status);
                logActivity((int)($_SESSION['user_id'] ?? 0), 'edit_product', 'Updated product: ' . $normalizedProductName . ' (status ' . $oldStatus . ' -> ' . $newStatus . ')');
                notifyRoles(
                  'system',
                  'Product Updated',
                  buildNotificationMessageTemplate($actorName, 'updated product', $normalizedProductName, 'Status: ' . $oldStatus . ' -> ' . $newStatus),
                  ['admin', 'cashier'],
                  [
                    'reference_type' => 'product',
                    'reference_id' => $productId,
                    'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
                  ]
                );
                echo json_encode(['success'=>true,'message'=>'Product updated']);
            } else {
                $inputCode = strtoupper(sanitizeTextValue($_POST['product_code'] ?? ''));
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

                $stmt = $db->prepare("INSERT INTO products (product_code,product_name,category_id,brand_id,unit_id,description,supplier_id,cost_price,selling_price,quantity,min_stock_level,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$code, $productName !== '' ? $productName : 'Unnamed Product', $_POST['category_id'] ?: null, $brandId, $_POST['unit_id'] ?: null, $description, $_POST['supplier_id'] ?: null, (float)($_POST['cost_price'] ?? 0), (float)($_POST['selling_price'] ?? 0), 0, max(0, (int)($_POST['min_stock_level'] ?? 10)), $status]);
                $newProductId = (int)$db->lastInsertId();
                $normalizedProductName = $productName !== '' ? $productName : 'Unnamed Product';
                logActivity((int)($_SESSION['user_id'] ?? 0), 'add_product', 'Added product: ' . $normalizedProductName . ' [' . $code . ']');
                notifyRoles(
                  'system',
                  'Product Added',
                  buildNotificationMessageTemplate($actorName, 'added product', $normalizedProductName, 'Code: ' . $code . ', Status: ' . strtoupper($status)),
                  ['admin', 'cashier'],
                  [
                    'reference_type' => 'product',
                    'reference_id' => $newProductId,
                    'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
                  ]
                );
                echo json_encode(['success'=>true,'message'=>'Product added','data'=>['id'=>$newProductId]]);
            }
        } elseif ($action === 'delete_product') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete products');
          }
            $existingProductStmt = $db->prepare("SELECT product_name, product_code FROM products WHERE id=? LIMIT 1");
            $existingProductStmt->execute([(int)$_POST['id']]);
            $existingProduct = $existingProductStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $productArchiveRow = $db->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
            $productArchiveRow->execute([(int)$_POST['id']]);
            $productArchivePayload = $productArchiveRow->fetch(PDO::FETCH_ASSOC) ?: [];
            if (empty($productArchivePayload)) {
                throw new Exception('Product not found');
            }
            $archiveId = archiveDeletedRecord('product', $productArchivePayload, ['source' => 'database', 'meta' => ['table' => 'products']]);
            if (!$archiveId) {
                throw new Exception('Failed to archive product before deletion');
            }
            try {
                $db->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]);
            } catch (Exception $deleteException) {
                deleteArchivedRecordById($archiveId);
                throw $deleteException;
            }
            $deletedProductName = sanitizeTextValue($existingProduct['product_name'] ?? ('Product #' . (int)$_POST['id']));
            logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_product', 'Deleted product: ' . $deletedProductName);
            notifyRoles(
              'system',
              'Product Removed',
              buildNotificationMessageTemplate($actorName, 'removed product', $deletedProductName, 'Code: ' . sanitizeTextValue($existingProduct['product_code'] ?? 'N/A')),
              ['admin', 'cashier'],
              [
                'reference_type' => 'product',
                'reference_id' => (int)$_POST['id'],
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Product deleted']);
        } elseif ($action === 'stock_in') {
            $id  = (int)$_POST['product_id'];
            $qty = (int)$_POST['quantity'];
            $productStmt = $db->prepare("SELECT product_name FROM products WHERE id = ?");
            $productStmt->execute([$id]);
            $productRow = $productStmt->fetch(PDO::FETCH_ASSOC);
            $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id=?")->execute([$qty,$id]);
            $db->prepare("INSERT INTO inventory_transactions (product_id,transaction_type,quantity,notes,created_by) VALUES (?,?,?,?,?)")->execute([$id,'stock_in',$qty,sanitizeTextValue($_POST['notes'] ?? ''),$_SESSION['user_id']]);
          $productName = sanitizeTextValue($productRow['product_name'] ?? ('Product #' . $id));
          logActivity((int)($_SESSION['user_id'] ?? 0), 'stock_in', 'Stock in: ' . $productName . ' (+' . $qty . ')');
          notifyRoles(
            'system',
            'Inventory Stock In',
            buildNotificationMessageTemplate($actorName, 'added stock', $productName, 'Quantity: +' . $qty),
            ['admin', 'cashier'],
            [
              'reference_type' => 'inventory_transaction',
              'reference_id' => $id,
            ]
          );
            echo json_encode(['success'=>true,'message'=>'Stock added']);
        } elseif ($action === 'stock_out') {
            $id  = (int)$_POST['product_id'];
            $qty = (int)$_POST['quantity'];
            $productStmt = $db->prepare("SELECT product_name, quantity FROM products WHERE id = ?");
            $productStmt->execute([$id]);
            $productRow = $productStmt->fetch(PDO::FETCH_ASSOC);
            $cur = (int)($productRow['quantity'] ?? 0);
            if ($qty > $cur) { echo json_encode(['success'=>false,'message'=>'Insufficient stock']); exit; }
            $db->prepare("UPDATE products SET quantity = quantity - ? WHERE id=?")->execute([$qty,$id]);
            $db->prepare("INSERT INTO inventory_transactions (product_id,transaction_type,quantity,notes,created_by) VALUES (?,?,?,?,?)")->execute([$id,'stock_out',$qty,sanitizeTextValue($_POST['notes'] ?? ''),$_SESSION['user_id']]);
          $productName = sanitizeTextValue($productRow['product_name'] ?? ('Product #' . $id));
          logActivity((int)($_SESSION['user_id'] ?? 0), 'stock_out', 'Stock out: ' . $productName . ' (-' . $qty . ')');
          notifyRoles(
            'system',
            'Inventory Stock Out',
            buildNotificationMessageTemplate($actorName, 'deducted stock', $productName, 'Quantity: -' . $qty),
            ['admin', 'cashier'],
            [
              'reference_type' => 'inventory_transaction',
              'reference_id' => $id,
            ]
          );
            echo json_encode(['success'=>true,'message'=>'Stock removed']);
        } elseif ($action === 'add_category') {
            $categoryName = sanitizeTextValue($_POST['category_name'] ?? '');
            $description = sanitizeTextValue($_POST['description'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            $db->prepare("INSERT INTO product_categories (category_name,description,status) VALUES (?,?,?)")->execute([$categoryName !== '' ? $categoryName : 'Unnamed Category', $description, $status]);
            $newCategoryId = (int)$db->lastInsertId();
            $normalizedCategoryName = $categoryName !== '' ? $categoryName : 'Unnamed Category';
            logActivity((int)($_SESSION['user_id'] ?? 0), 'add_category', 'Added category: ' . $normalizedCategoryName);
            notifyRoles(
              'system',
              'Category Added',
              buildNotificationMessageTemplate($actorName, 'added category', $normalizedCategoryName, 'Status: ' . strtoupper($status)),
              ['admin', 'cashier'],
              [
                'reference_type' => 'product_category',
                'reference_id' => $newCategoryId,
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Category added']);
        } elseif ($action === 'edit_category') {
            $existingCategoryStmt = $db->prepare("SELECT category_name, status FROM product_categories WHERE id=? LIMIT 1");
            $existingCategoryStmt->execute([(int)$_POST['id']]);
            $existingCategory = $existingCategoryStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $categoryName = sanitizeTextValue($_POST['category_name'] ?? '');
            $description = sanitizeTextValue($_POST['description'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            $db->prepare("UPDATE product_categories SET category_name=?,description=?,status=? WHERE id=?")->execute([$categoryName !== '' ? $categoryName : 'Unnamed Category', $description, $status, (int)$_POST['id']]);
            $normalizedCategoryName = $categoryName !== '' ? $categoryName : 'Unnamed Category';
            $oldStatus = strtoupper((string)($existingCategory['status'] ?? 'unknown'));
            $newStatus = strtoupper((string)$status);
            logActivity((int)($_SESSION['user_id'] ?? 0), 'edit_category', 'Updated category: ' . $normalizedCategoryName . ' (status ' . $oldStatus . ' -> ' . $newStatus . ')');
            notifyRoles(
              'system',
              'Category Updated',
              buildNotificationMessageTemplate($actorName, 'updated category', $normalizedCategoryName, 'Status: ' . $oldStatus . ' -> ' . $newStatus),
              ['admin', 'cashier'],
              [
                'reference_type' => 'product_category',
                'reference_id' => (int)$_POST['id'],
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Category updated']);
        } elseif ($action === 'delete_category') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete categories');
          }
            $existingCategoryStmt = $db->prepare("SELECT category_name FROM product_categories WHERE id=? LIMIT 1");
            $existingCategoryStmt->execute([(int)$_POST['id']]);
            $existingCategory = $existingCategoryStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $categoryArchiveStmt = $db->prepare("SELECT * FROM product_categories WHERE id=? LIMIT 1");
            $categoryArchiveStmt->execute([(int)$_POST['id']]);
            $categoryArchivePayload = $categoryArchiveStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            if (empty($categoryArchivePayload)) {
                throw new Exception('Category not found');
            }
            $archiveId = archiveDeletedRecord('product_category', $categoryArchivePayload, ['source' => 'database', 'meta' => ['table' => 'product_categories']]);
            if (!$archiveId) {
                throw new Exception('Failed to archive category before deletion');
            }
            try {
                $db->prepare("DELETE FROM product_categories WHERE id=?")->execute([(int)$_POST['id']]);
            } catch (Exception $deleteException) {
                deleteArchivedRecordById($archiveId);
                throw $deleteException;
            }
            $deletedCategoryName = sanitizeTextValue($existingCategory['category_name'] ?? ('Category #' . (int)$_POST['id']));
            logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_category', 'Deleted category: ' . $deletedCategoryName);
            notifyRoles(
              'system',
              'Category Removed',
              buildNotificationMessageTemplate($actorName, 'removed category', $deletedCategoryName),
              ['admin', 'cashier'],
              [
                'reference_type' => 'product_category',
                'reference_id' => (int)$_POST['id'],
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Category deleted']);
        } elseif ($action === 'add_supplier') {
            $supplierName = sanitizeTextValue($_POST['supplier_name'] ?? '');
            $contactPerson = sanitizeTextValue($_POST['contact_person'] ?? '');
            $phone = sanitizeTextValue($_POST['phone'] ?? '');
            $email = sanitizeTextValue($_POST['email'] ?? '');
            $address = sanitizeTextValue($_POST['address'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            $db->prepare("INSERT INTO suppliers (supplier_name,contact_person,phone,email,address,status) VALUES (?,?,?,?,?,?)")->execute([$supplierName !== '' ? $supplierName : 'Unnamed Supplier', $contactPerson, $phone, $email, $address, $status]);
            $newSupplierId = (int)$db->lastInsertId();
            $normalizedSupplierName = $supplierName !== '' ? $supplierName : 'Unnamed Supplier';
            logActivity((int)($_SESSION['user_id'] ?? 0), 'add_supplier', 'Added supplier: ' . $normalizedSupplierName);
            notifyRoles(
              'system',
              'Supplier Added',
              buildNotificationMessageTemplate($actorName, 'added supplier', $normalizedSupplierName, 'Status: ' . strtoupper($status)),
              ['admin', 'cashier'],
              [
                'reference_type' => 'supplier',
                'reference_id' => $newSupplierId,
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Supplier added']);
        } elseif ($action === 'edit_supplier') {
            $existingSupplierStmt = $db->prepare("SELECT supplier_name, status FROM suppliers WHERE id=? LIMIT 1");
            $existingSupplierStmt->execute([(int)$_POST['id']]);
            $existingSupplier = $existingSupplierStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $supplierName = sanitizeTextValue($_POST['supplier_name'] ?? '');
            $contactPerson = sanitizeTextValue($_POST['contact_person'] ?? '');
            $phone = sanitizeTextValue($_POST['phone'] ?? '');
            $email = sanitizeTextValue($_POST['email'] ?? '');
            $address = sanitizeTextValue($_POST['address'] ?? '');
            $status = sanitizeTextValue($_POST['status'] ?? 'active', 'active');
            $db->prepare("UPDATE suppliers SET supplier_name=?,contact_person=?,phone=?,email=?,address=?,status=? WHERE id=?")->execute([$supplierName !== '' ? $supplierName : 'Unnamed Supplier', $contactPerson, $phone, $email, $address, $status, (int)$_POST['id']]);
            $normalizedSupplierName = $supplierName !== '' ? $supplierName : 'Unnamed Supplier';
            $oldStatus = strtoupper((string)($existingSupplier['status'] ?? 'unknown'));
            $newStatus = strtoupper((string)$status);
            logActivity((int)($_SESSION['user_id'] ?? 0), 'edit_supplier', 'Updated supplier: ' . $normalizedSupplierName . ' (status ' . $oldStatus . ' -> ' . $newStatus . ')');
            notifyRoles(
              'system',
              'Supplier Updated',
              buildNotificationMessageTemplate($actorName, 'updated supplier', $normalizedSupplierName, 'Status: ' . $oldStatus . ' -> ' . $newStatus),
              ['admin', 'cashier'],
              [
                'reference_type' => 'supplier',
                'reference_id' => (int)$_POST['id'],
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Supplier updated']);
        } elseif ($action === 'delete_supplier') {
          if ($isCashier) {
            throw new Exception('Cashier is not allowed to delete suppliers');
          }
            $existingSupplierStmt = $db->prepare("SELECT supplier_name FROM suppliers WHERE id=? LIMIT 1");
            $existingSupplierStmt->execute([(int)$_POST['id']]);
            $existingSupplier = $existingSupplierStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $supplierArchiveStmt = $db->prepare("SELECT * FROM suppliers WHERE id=? LIMIT 1");
            $supplierArchiveStmt->execute([(int)$_POST['id']]);
            $supplierArchivePayload = $supplierArchiveStmt->fetch(PDO::FETCH_ASSOC) ?: [];
            if (empty($supplierArchivePayload)) {
                throw new Exception('Supplier not found');
            }
            $archiveId = archiveDeletedRecord('supplier', $supplierArchivePayload, ['source' => 'database', 'meta' => ['table' => 'suppliers']]);
            if (!$archiveId) {
                throw new Exception('Failed to archive supplier before deletion');
            }
            try {
                $db->prepare("DELETE FROM suppliers WHERE id=?")->execute([(int)$_POST['id']]);
            } catch (Exception $deleteException) {
                deleteArchivedRecordById($archiveId);
                throw $deleteException;
            }
            $deletedSupplierName = sanitizeTextValue($existingSupplier['supplier_name'] ?? ('Supplier #' . (int)$_POST['id']));
            logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_supplier', 'Deleted supplier: ' . $deletedSupplierName);
            notifyRoles(
              'system',
              'Supplier Removed',
              buildNotificationMessageTemplate($actorName, 'removed supplier', $deletedSupplierName),
              ['admin', 'cashier'],
              [
                'reference_type' => 'supplier',
                'reference_id' => (int)$_POST['id'],
                'exclude_user_id' => (int)($_SESSION['user_id'] ?? 0),
              ]
            );
            echo json_encode(['success'=>true,'message'=>'Supplier deleted']);
        } elseif ($action === 'supplier_transaction') {
            $supplierId = (int)($_POST['supplier_id'] ?? 0);
            $type = sanitizeTextValue($_POST['transaction_type'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $reference = sanitizeTextValue($_POST['reference_number'] ?? '');
            $txDate = sanitizeTextValue($_POST['transaction_date'] ?? date('Y-m-d'));
            $notes = sanitizeTextValue($_POST['notes'] ?? '');

            if ($supplierId <= 0) throw new Exception('Invalid supplier');
            if (!in_array($type, ['purchase','payment','adjustment','return'], true)) throw new Exception('Invalid transaction type');
            if ($amount <= 0) throw new Exception('Amount must be greater than 0');

            // Insert transaction
            $db->prepare("INSERT INTO supplier_transactions (supplier_id, transaction_type, amount, reference_number, notes, transaction_date, created_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$supplierId, $type, $amount, $reference, $notes, $txDate, (int)($_SESSION['user_id'] ?? 0)]);

            // Update supplier totals
            if ($type === 'purchase') {
                $db->prepare("UPDATE suppliers SET total_purchases = total_purchases + ?, balance = balance + ? WHERE id = ?")->execute([$amount, $amount, $supplierId]);
            } elseif ($type === 'payment') {
                $db->prepare("UPDATE suppliers SET total_payments = total_payments + ?, balance = balance - ? WHERE id = ?")->execute([$amount, $amount, $supplierId]);
            } elseif ($type === 'return') {
                $db->prepare("UPDATE suppliers SET total_purchases = total_purchases - ?, balance = balance - ? WHERE id = ?")->execute([$amount, $amount, $supplierId]);
            }

            $supplierRow = $db->prepare("SELECT supplier_name FROM suppliers WHERE id=?");
            $supplierRow->execute([$supplierId]);
            $supplierData = $supplierRow->fetch(PDO::FETCH_ASSOC);
            logActivity((int)($_SESSION['user_id'] ?? 0), 'supplier_' . $type, ucfirst($type) . ' ₱' . number_format($amount, 2) . ' for supplier: ' . ($supplierData['supplier_name'] ?? '#' . $supplierId));

            echo json_encode(['success'=>true,'message'=>ucfirst($type) . ' recorded successfully']);
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
$pStmt = $db->prepare("SELECT p.*, pc.category_name, b.brand_name, u.unit_symbol, s.supplier_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id LEFT JOIN brands b ON p.brand_id=b.id LEFT JOIN units u ON p.unit_id=u.id LEFT JOIN suppliers s ON p.supplier_id=s.id $pWhere ORDER BY p.product_name");
$pStmt->execute($pParams);
$products = $pStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalProducts  = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock       = $db->query("SELECT COUNT(*) FROM products WHERE quantity <= min_stock_level AND status='active'")->fetchColumn();
$totalValue     = $db->query("SELECT SUM(quantity * cost_price) FROM products WHERE status='active'")->fetchColumn() ?? 0;
$outOfStock     = $db->query("SELECT COUNT(*) FROM products WHERE quantity = 0 AND status='active'")->fetchColumn();

// Transactions for the selected calendar day
$txStmt = $db->prepare("SELECT it.*, p.product_name FROM inventory_transactions it LEFT JOIN products p ON it.product_id=p.id WHERE DATE(it.created_at) = ? ORDER BY it.created_at DESC");
$txStmt->execute([$transactionDate]);
$recentTx = $txStmt->fetchAll(PDO::FETCH_ASSOC);
$unpaidParts = $db->query("SELECT jo.job_order_number, jo.payment_status, c.full_name AS customer_name, jop.product_name, jop.quantity, COALESCE(p.cost_price, 0) AS cost_price, CASE WHEN jo.payment_status = 'pending' THEN jop.quantity ELSE jop.quantity * GREATEST(COALESCE(jo.total_amount, 0) - COALESCE(jo.partial_amount, 0), 0) / NULLIF(jo.total_amount, 0) END AS unpaid_quantity, CASE WHEN jo.payment_status = 'pending' THEN jop.quantity * COALESCE(p.cost_price, 0) ELSE jop.quantity * COALESCE(p.cost_price, 0) * GREATEST(COALESCE(jo.total_amount, 0) - COALESCE(jo.partial_amount, 0), 0) / NULLIF(jo.total_amount, 0) END AS unpaid_cost FROM job_orders jo INNER JOIN job_order_products jop ON jop.job_order_id = jo.id INNER JOIN products p ON p.id = jop.product_id LEFT JOIN customers c ON c.id = jo.customer_id WHERE jo.status != 'cancelled' AND jo.payment_status IN ('pending', 'partial') ORDER BY jo.created_at DESC, jo.job_order_number DESC, jop.product_name ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../partials/header.php';
?>

<style>
.inventory-list-wrap {
  overflow-x: auto;
  overflow-y: visible;
  -webkit-overflow-scrolling: touch;
}

.inventory-list-table {
  margin-bottom: 0;
  table-layout: auto;
}

.inventory-list-table th,
.inventory-list-table td {
  white-space: nowrap;
}

.inventory-products-table {
  min-width: 980px;
}

.inventory-categories-table {
  min-width: 560px;
}

.inventory-suppliers-table {
  min-width: 760px;
}

.inventory-transactions-table {
  min-width: 700px;
}

.inventory-date-toolbar {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px;
  border: 1px solid #e2e2e2;
  border-radius: 8px;
  background: #fafafa;
}

.inventory-date-toolbar .form-control {
  width: 150px;
  border-color: #d8d8d8;
  background: #fff;
}

.inventory-date-toolbar .btn {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.inventory-main-tabs {
  border-bottom-color: #e6e6e6;
}

.inventory-main-tabs .nav-link {
  border: 1px solid transparent;
  border-radius: 10px 10px 0 0;
  padding: 8px 12px;
  font-weight: 500;
}

.inventory-main-tabs .nav-link.active {
  border: 1px solid #d7d7d7 !important;
  border-bottom: 2px solid #111 !important;
  background: #fff !important;
}

@media (max-width: 768px) {
  .inventory-main-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 10px !important;
  }

  .inventory-main-tabs .nav-item {
    flex: 0 0 calc(50% - 3px);
    max-width: calc(50% - 3px);
  }

  .inventory-main-tabs .nav-link {
    width: 100%;
    text-align: center;
    white-space: normal;
    border-radius: 10px;
    font-size: 13px;
    padding: 8px 10px;
  }

  .inventory-stats-row {
    margin-bottom: 10px !important;
  }

  .inventory-stats-row .inventory-stat-col {
    flex: 0 0 50%;
    max-width: 50%;
    padding-left: 4px;
    padding-right: 4px;
    margin-bottom: 6px;
  }

  .inventory-stats-row .card-body {
    padding: 10px 8px !important;
  }

  .inventory-stats-row .text-muted.small {
    font-size: 11px !important;
    margin-bottom: 3px !important;
  }

  .inventory-stats-row .fs-4 {
    font-size: 1.5rem !important;
    line-height: 1.1;
  }

  .inventory-stats-row .fs-5 {
    font-size: 1.15rem !important;
    line-height: 1.1;
  }

  .inventory-list-wrap::-webkit-scrollbar {
    height: 8px;
  }

  .inventory-list-wrap::-webkit-scrollbar-thumb {
    background: #c5c8cc;
    border-radius: 8px;
  }
}
</style>

<!-- Stats row -->
<div class="row g-2 mb-4 inventory-stats-row">
  <div class="col-6 col-md-3 inventory-stat-col">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Total Products</div>
      <div class="fw-bold fs-4"><?php echo $totalProducts; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 inventory-stat-col">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Low Stock</div>
      <div class="fw-bold fs-4 text-warning"><?php echo $lowStock; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 inventory-stat-col">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Out of Stock</div>
      <div class="fw-bold fs-4 text-danger"><?php echo $outOfStock; ?></div>
    </div></div>
  </div>
  <div class="col-6 col-md-3 inventory-stat-col">
    <div class="card text-center h-100"><div class="card-body py-3">
      <div class="text-muted small mb-1">Inventory Value</div>
      <div class="fw-bold fs-5">₱<?php echo number_format($totalValue, 2); ?></div>
    </div></div>
  </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3 inventory-main-tabs">
  <?php
  $inventoryTabs = ['products'=>'<i class="bi bi-box-seam"></i> Products','categories'=>'<i class="bi bi-tag"></i> Categories','suppliers'=>'<i class="bi bi-truck"></i> Suppliers','transactions'=>'<i class="bi bi-arrow-left-right"></i> Transactions','unpaid_parts'=>'<i class="bi bi-hourglass-split"></i> Unpaid Parts'];
  if ($isStockman) $inventoryTabs = ['products'=>'<i class="bi bi-box-seam"></i> Products','transactions'=>'<i class="bi bi-arrow-left-right"></i> Transactions','unpaid_parts'=>'<i class="bi bi-hourglass-split"></i> Unpaid Parts'];
  foreach ($inventoryTabs as $t=>$label): ?>
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
      <?php if (!$isStockman): ?><button type="button" class="btn btn-sm btn-dark" onclick="openAddProduct()"><i class="bi bi-plus-circle"></i> Add Product</button><?php endif; ?>
    </div>
  </form>
</div></div>

<div class="card"><div class="card-body p-0">
  <?php if (empty($products)): ?>
    <div class="text-center py-5"><i class="bi bi-box-seam" style="font-size:3rem;color:#ccc;"></i><p class="text-muted mt-3">No products found</p></div>
  <?php else: ?>
  <div class="table-responsive table-responsive-actions inventory-list-wrap">
    <table class="table table-hover inventory-list-table inventory-products-table" style="font-size:13px;">
      <thead style="background:#f8f8f8;">
        <tr><th class="px-3">Code</th><th>Product Name</th><th>Category</th><th>Brand</th><th>Supplier</th><?php if (!$isStockman): ?><th>Cost</th><th>Selling</th><?php endif; ?><th>Stock</th><th>Min</th><th>Status</th><?php if (!$isStockman): ?><th>Actions</th><?php endif; ?></tr>
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
          <td><?php echo escape($p['supplier_name']??'—'); ?></td>
          <?php if (!$isStockman): ?>
          <td>₱<?php echo number_format($p['cost_price'],2); ?></td>
          <td>₱<?php echo number_format($p['selling_price'],2); ?></td>
          <?php endif; ?>
          <td>
            <span class="fw-bold <?php echo $out?'text-danger':($low?'text-warning':''); ?>">
              <?php echo $p['quantity']; ?> <?php echo escape($p['unit_symbol']??''); ?>
            </span>
            <?php if ($out): ?><span class="badge bg-danger ms-1">Out</span><?php elseif ($low): ?><span class="badge bg-warning text-dark ms-1">Low</span><?php endif; ?>
          </td>
          <td><?php echo $p['min_stock_level']; ?></td>
          <td><span class="badge bg-<?php echo $p['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
          <?php if (!$isStockman): ?>
          <td>
            <div class="btn-group btn-group-sm d-none d-md-inline-flex">
              <?php if (!$isStockman): ?>
              <button class="btn btn-outline-success py-0 px-2" onclick="openStockIn(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)" title="Stock In"><i class="bi bi-plus-lg"></i></button>
              <button class="btn btn-outline-warning py-0 px-2" onclick="openStockOut(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)" title="Stock Out"><i class="bi bi-dash-lg"></i></button>
              <button class="btn btn-outline-dark py-0 px-2" onclick="openEditProduct(<?php echo htmlspecialchars(json_encode($p),ENT_QUOTES); ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
              <?php if (!$isCashier): ?>
              <button class="btn btn-outline-danger py-0 px-2" onclick="deleteProduct(<?php echo $p['id']; ?>, <?php echo htmlspecialchars(json_encode($p['product_name']), ENT_QUOTES); ?>)" title="Delete"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
              <?php endif; ?>
            </div>
            <div class="dropdown action-dropdown d-inline-flex d-md-none">
              <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="productActionsMobile<?php echo $p['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Product actions">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="productActionsMobile<?php echo $p['id']; ?>">
                <?php if (!$isStockman): ?>
                <li><button type="button" class="dropdown-item" onclick="openStockIn(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)"><i class="bi bi-plus-lg me-2"></i>Stock In</button></li>
                <li><button type="button" class="dropdown-item" onclick="openStockOut(<?php echo $p['id']; ?>,'<?php echo addslashes(escape($p['product_name'])); ?>',<?php echo $p['quantity']; ?>)"><i class="bi bi-dash-lg me-2"></i>Stock Out</button></li>
                <li><button type="button" class="dropdown-item" onclick="openEditProduct(<?php echo htmlspecialchars(json_encode($p),ENT_QUOTES); ?>)"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                <?php if (!$isCashier): ?>
                <li><button type="button" class="dropdown-item text-danger" onclick="deleteProduct(<?php echo $p['id']; ?>, <?php echo htmlspecialchars(json_encode($p['product_name']), ENT_QUOTES); ?>)"><i class="bi bi-trash me-2"></i>Delete</button></li>
                <?php endif; ?>
                <?php endif; ?>
              </ul>
            </div>
          </td>
          <?php endif; /* !$isStockman actions td */ ?>
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
  <div class="table-responsive table-responsive-actions inventory-list-wrap">
    <table class="table table-hover inventory-list-table inventory-categories-table" style="font-size:13px;">
      <thead style="background:#f8f8f8;"><tr><th class="px-3">Category Name</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($categories as $c): ?>
        <tr>
          <td class="px-3 fw-semibold"><?php echo escape($c['category_name']); ?></td>
          <td><small class="text-muted"><?php echo escape($c['description']??'—'); ?></small></td>
          <td><span class="badge bg-<?php echo $c['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
          <td>
            <div class="btn-group btn-group-sm d-none d-md-inline-flex">
              <button class="btn btn-outline-dark py-0 px-2" onclick="openEditCategory(<?php echo $c['id']; ?>,'<?php echo addslashes(escape($c['category_name'])); ?>','<?php echo addslashes(escape($c['description']??'')); ?>','<?php echo $c['status']; ?>')"><i class="bi bi-pencil"></i></button>
              <?php if (!$isCashier): ?>
              <button class="btn btn-outline-danger py-0 px-2" onclick="deleteCategory(<?php echo $c['id']; ?>, <?php echo htmlspecialchars(json_encode($c['category_name']), ENT_QUOTES); ?>)"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
            </div>
            <div class="dropdown action-dropdown d-inline-flex d-md-none">
              <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="categoryActionsMobile<?php echo $c['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Category actions">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="categoryActionsMobile<?php echo $c['id']; ?>">
                <li><button type="button" class="dropdown-item" onclick="openEditCategory(<?php echo $c['id']; ?>,'<?php echo addslashes(escape($c['category_name'])); ?>','<?php echo addslashes(escape($c['description']??'')); ?>','<?php echo $c['status']; ?>')"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                <?php if (!$isCashier): ?>
                <li><button type="button" class="dropdown-item text-danger" onclick="deleteCategory(<?php echo $c['id']; ?>, <?php echo htmlspecialchars(json_encode($c['category_name']), ENT_QUOTES); ?>)"><i class="bi bi-trash me-2"></i>Delete</button></li>
                <?php endif; ?>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($categories)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No categories yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php endif; ?>

<?php if ($tab === 'suppliers'): ?>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-sm btn-dark" onclick="openAddSupplier()"><i class="bi bi-plus-circle"></i> Add Supplier</button>
</div>
<div class="card"><div class="card-body p-0">
  <div class="table-responsive table-responsive-actions inventory-list-wrap">
    <table class="table table-hover inventory-list-table inventory-suppliers-table" style="font-size:13px;">
      <thead style="background:#f8f8f8;"><tr><th class="px-3">Supplier Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Purchases</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($suppliers as $s): ?>
        <tr>
          <td class="px-3 fw-semibold"><?php echo escape($s['supplier_name']); ?></td>
          <td><?php echo escape($s['contact_person']??'—'); ?></td>
          <td><?php echo escape($s['phone']??'—'); ?></td>
          <td><?php echo escape($s['email']??'—'); ?></td>
          <td>₱<?php echo number_format((float)($s['total_purchases']??0),2); ?></td>
          <td class="text-success">₱<?php echo number_format((float)($s['total_payments']??0),2); ?></td>
          <td class="fw-bold <?php echo ((float)($s['balance']??0))>0?'text-danger':''; ?>">₱<?php echo number_format((float)($s['balance']??0),2); ?></td>
          <td><span class="badge bg-<?php echo $s['status']==='active'?'success':'secondary'; ?>"><?php echo ucfirst($s['status']); ?></span></td>
          <td>
            <div class="btn-group btn-group-sm d-none d-md-inline-flex">
              <button class="btn btn-outline-success py-0 px-2" onclick="openSupplierPayment(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>', <?php echo (float)($s['balance']??0); ?>)" title="Record Payment"><i class="bi bi-cash"></i></button>
              <button class="btn btn-outline-primary py-0 px-2" onclick="openSupplierPurchase(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>')" title="Record Purchase"><i class="bi bi-cart-plus"></i></button>
              <button class="btn btn-outline-secondary py-0 px-2" onclick="viewSupplierHistory(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>')" title="View History"><i class="bi bi-clock-history"></i></button>
              <button class="btn btn-outline-dark py-0 px-2" onclick="openEditSupplier(<?php echo htmlspecialchars(json_encode($s),ENT_QUOTES); ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
              <?php if (!$isCashier): ?>
              <button class="btn btn-outline-danger py-0 px-2" onclick="deleteSupplier(<?php echo $s['id']; ?>, <?php echo htmlspecialchars(json_encode($s['supplier_name']), ENT_QUOTES); ?>)" title="Delete"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
            </div>
            <div class="dropdown action-dropdown d-inline-flex d-md-none">
              <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="supplierActionsMobile<?php echo $s['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Supplier actions">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="supplierActionsMobile<?php echo $s['id']; ?>">
                <li><button type="button" class="dropdown-item" onclick="openSupplierPayment(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>', <?php echo (float)($s['balance']??0); ?>)"><i class="bi bi-cash me-2"></i>Record Payment</button></li>
                <li><button type="button" class="dropdown-item" onclick="openSupplierPurchase(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>')"><i class="bi bi-cart-plus me-2"></i>Record Purchase</button></li>
                <li><button type="button" class="dropdown-item" onclick="viewSupplierHistory(<?php echo $s['id']; ?>, '<?php echo addslashes(escape($s['supplier_name'])); ?>')"><i class="bi bi-clock-history me-2"></i>View History</button></li>
                <li><button type="button" class="dropdown-item" onclick="openEditSupplier(<?php echo htmlspecialchars(json_encode($s),ENT_QUOTES); ?>)"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                <?php if (!$isCashier): ?>
                <li><button type="button" class="dropdown-item text-danger" onclick="deleteSupplier(<?php echo $s['id']; ?>, <?php echo htmlspecialchars(json_encode($s['supplier_name']), ENT_QUOTES); ?>)"><i class="bi bi-trash me-2"></i>Delete</button></li>
                <?php endif; ?>
              </ul>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($suppliers)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No suppliers yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php endif; ?>

<?php if ($tab === 'transactions'): ?>
<div class="card mb-3"><div class="card-body py-2 d-flex align-items-center justify-content-between gap-2 flex-wrap">
  <form method="GET" class="inventory-date-toolbar">
    <input type="hidden" name="tab" value="transactions">
    <a class="btn btn-sm btn-outline-secondary" href="?tab=transactions&transaction_date=<?php echo date('Y-m-d', strtotime($transactionDate . ' -1 day')); ?>" title="Previous day" aria-label="Previous day"><i class="bi bi-chevron-left"></i></a>
    <input type="date" name="transaction_date" class="form-control form-control-sm" value="<?php echo escape($transactionDate); ?>" aria-label="Transaction date">
    <button type="submit" class="btn btn-sm btn-dark" title="Apply date" aria-label="Apply date"><i class="bi bi-calendar-check"></i></button>
    <a class="btn btn-sm btn-outline-secondary" href="?tab=transactions&transaction_date=<?php echo date('Y-m-d', strtotime($transactionDate . ' +1 day')); ?>" title="Next day" aria-label="Next day"><i class="bi bi-chevron-right"></i></a>
  </form>
  <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo date('F d, Y', strtotime($transactionDate)); ?></div>
</div></div>
<div class="card"><div class="card-body p-0">
  <div class="table-responsive table-responsive-actions inventory-list-wrap">
    <table class="table table-hover inventory-list-table inventory-transactions-table" style="font-size:13px;">
      <thead style="background:#f8f8f8;"><tr><th class="px-3">Product</th><th>Type</th><th>Qty</th><th>Notes</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($recentTx as $tx): ?>
        <tr>
          <td class="px-3"><?php echo escape($tx['product_name']??'—'); ?></td>
          <td><span class="badge bg-<?php echo $tx['transaction_type']==='stock_in'?'success':($tx['transaction_type']==='return'?'info':($tx['transaction_type']==='stock_out'?'warning':'secondary')); ?>"><?php echo ucfirst(str_replace('_',' ',$tx['transaction_type'])); ?></span></td>
          <td class="fw-bold <?php echo in_array($tx['transaction_type'], ['stock_in','return']) ?'text-success':'text-warning'; ?>"><?php echo (in_array($tx['transaction_type'], ['stock_in','return'])?'+':'-').$tx['quantity']; ?></td>
          <td><small class="text-muted"><?php echo escape($tx['notes']??'—'); ?></small></td>
          <td><?php echo date('M d, Y h:i A', strtotime($tx['created_at'])); ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recentTx)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No transactions yet</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>
<?php endif; ?>

<?php if ($tab === 'unpaid_parts'): ?>
<div class="card mb-3"><div class="card-header bg-white"><strong><i class="bi bi-hourglass-split me-2"></i>Unpaid Parts</strong> <span class="badge bg-warning text-dark float-end"><?php echo count($unpaidParts); ?></span><div class="small text-muted">Pending or partially paid job-order parts and their outstanding cost.</div></div><div class="table-responsive"><table class="table table-hover mb-0" style="font-size:13px;"><thead style="background:#fff8e1;"><tr><th class="px-3">JO #</th><th>Customer</th><th>Payment</th><th>Part</th><th>Unpaid Qty</th><th>Cost / Unit</th><th class="text-end">Unpaid Cost</th></tr></thead><tbody><?php foreach ($unpaidParts as $part): ?><tr><td class="px-3 fw-semibold"><?php echo escape($part['job_order_number']); ?></td><td><?php echo escape($part['customer_name'] ?: 'Customer'); ?></td><td><?php echo ucfirst($part['payment_status']); ?></td><td><?php echo escape($part['product_name']); ?></td><td><?php echo number_format((float)$part['unpaid_quantity'], 2); ?> of <?php echo (int)$part['quantity']; ?></td><td>₱<?php echo number_format((float)$part['cost_price'], 2); ?></td><td class="text-end fw-semibold">₱<?php echo number_format((float)$part['unpaid_cost'], 2); ?></td></tr><?php endforeach; ?><?php if (empty($unpaidParts)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No unpaid parts.</td></tr><?php endif; ?></tbody></table></div></div>
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
        <div class="col-md-6"><label class="form-label form-label-sm">Product Code <small class="text-muted">(auto-generated)</small></label><input type="text" class="form-control form-control-sm" id="ap_code" readonly style="background:#f5f5f5;" placeholder="Auto-generated"></div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Category</label>
          <select class="form-select form-select-sm" id="ap_category">
            <option value="">— None —</option>
            <?php foreach ($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo escape($c['category_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Brand</label>
          <input type="text" class="form-control form-control-sm" id="ap_brand" placeholder="">
        </div>
        <div class="col-md-4">
          <label class="form-label form-label-sm">Unit</label>
          <select class="form-select form-select-sm" id="ap_unit">
            <option value="">— None —</option>
            <?php foreach ($units as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo escape($u['unit_name']); ?> (<?php echo escape($u['unit_symbol']); ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><label class="form-label form-label-sm">Description</label><textarea class="form-control form-control-sm" id="ap_desc" rows="2"></textarea></div>
        <div class="col-md-6">
          <label class="form-label form-label-sm">Supplier</label>
          <select class="form-select form-select-sm" id="ap_supplier">
            <option value="">— None —</option>
            <?php foreach ($suppliers as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo escape($s['supplier_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
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
        <div class="col-md-4"><label class="form-label form-label-sm">Brand</label><input type="text" class="form-control form-control-sm" id="ep_brand" placeholder=""></div>
        <div class="col-md-4"><label class="form-label form-label-sm">Unit</label><select class="form-select form-select-sm" id="ep_unit"><option value="">— None —</option><?php foreach ($units as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo escape($u['unit_name']); ?> (<?php echo escape($u['unit_symbol']); ?>)</option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label form-label-sm">Description</label><textarea class="form-control form-control-sm" id="ep_desc" rows="2"></textarea></div>
        <div class="col-md-6">
          <label class="form-label form-label-sm">Supplier</label>
          <select class="form-select form-select-sm" id="ep_supplier">
            <option value="">— None —</option>
            <?php foreach ($suppliers as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo escape($s['supplier_name']); ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label form-label-sm">Cost Price (₱)</label><input type="number" class="form-control form-control-sm" id="ep_cost" step="0.01" min="0"></div>
        <div class="col-md-3"><label class="form-label form-label-sm">Selling Price (₱)</label><input type="number" class="form-control form-control-sm" id="ep_sell" step="0.01" min="0"></div>
        <div class="col-12"><small class="text-muted">Price changes apply to new job orders. Existing job orders keep their recorded product prices.</small></div>
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
const INV_URL  = '<?php echo routeUrl('inventory'); ?>';
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
    document.getElementById('ap_supplier').value = '';
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
        product_code:   '',
        category_id:    document.getElementById('ap_category').value,
        brand:          document.getElementById('ap_brand').value.trim(),
        unit_id:        document.getElementById('ap_unit').value,
        description:    document.getElementById('ap_desc').value.trim(),
        supplier_id:    document.getElementById('ap_supplier').value,
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
    document.getElementById('ep_supplier').value = p.supplier_id || '';
    document.getElementById('ep_cost').value     = p.cost_price;
    document.getElementById('ep_sell').value     = p.selling_price;
    document.getElementById('ep_min').value      = p.min_stock_level;
    document.getElementById('ep_status').value   = p.status;
    document.getElementById('ep_category').value = p.category_id || '';
    document.getElementById('ep_brand').value    = p.brand_name    || '';
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
        brand:          document.getElementById('ep_brand').value.trim(),
        unit_id:        document.getElementById('ep_unit').value,
        description:    document.getElementById('ep_desc').value.trim(),
        supplier_id:    document.getElementById('ep_supplier').value,
        cost_price:     document.getElementById('ep_cost').value,
        selling_price:  document.getElementById('ep_sell').value,
        min_stock_level:document.getElementById('ep_min').value,
        status:         document.getElementById('ep_status').value,
    }, () => bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide());
}

function deleteProduct(id, productName = '') {
  appConfirm(`Delete this product?\n\nProduct: ${productName || 'Unknown'}\nProduct ID: ${id}\nThis cannot be undone.`, {
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

function deleteCategory(id, categoryName = '') {
  appConfirm(`Delete this category?\n\nCategory: ${categoryName || 'Unknown'}\nCategory ID: ${id}`, {
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

function deleteSupplier(id, supplierName = '') {
  appConfirm(`Delete this supplier?\n\nSupplier: ${supplierName || 'Unknown'}\nSupplier ID: ${id}`, {
    title: 'Delete Supplier',
    confirmText: 'Delete',
    variant: 'danger'
  }).then(confirmed => {
    if (!confirmed) return;
    invPost('delete_supplier', { id }, () => {});
  });
}
</script>

<!-- ── SUPPLIER PAYMENT MODAL ──────────────────────────────────────────── -->
<div class="modal fade" id="supplierPaymentModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-cash me-2 text-success"></i>Record Payment</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="sp_supplier_id">
      <div class="mb-3"><label class="form-label form-label-sm">Supplier</label><input type="text" class="form-control form-control-sm" id="sp_supplier_name" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Current Balance</label><input type="text" class="form-control form-control-sm" id="sp_balance" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Payment Amount (₱) *</label><input type="number" class="form-control form-control-sm" id="sp_amount" step="0.01" min="0.01" value=""></div>
      <div class="mb-3"><label class="form-label form-label-sm">Reference # <small class="text-muted">(receipt, check no.)</small></label><input type="text" class="form-control form-control-sm" id="sp_reference"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Date *</label><input type="date" class="form-control form-control-sm" id="sp_date"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Notes</label><textarea class="form-control form-control-sm" id="sp_notes" rows="2"></textarea></div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-success btn-sm" onclick="saveSupplierPayment()"><i class="bi bi-check-lg"></i> Save Payment</button>
    </div>
  </div></div>
</div>

<!-- ── SUPPLIER PURCHASE MODAL ─────────────────────────────────────────── -->
<div class="modal fade" id="supplierPurchaseModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus me-2 text-primary"></i>Record Purchase</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="spu_supplier_id">
      <div class="mb-3"><label class="form-label form-label-sm">Supplier</label><input type="text" class="form-control form-control-sm" id="spu_supplier_name" readonly style="background:#f5f5f5;"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Purchase Amount (₱) *</label><input type="number" class="form-control form-control-sm" id="spu_amount" step="0.01" min="0.01" value=""></div>
      <div class="mb-3"><label class="form-label form-label-sm">Reference # <small class="text-muted">(invoice, PO no.)</small></label><input type="text" class="form-control form-control-sm" id="spu_reference"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Date *</label><input type="date" class="form-control form-control-sm" id="spu_date"></div>
      <div class="mb-3"><label class="form-label form-label-sm">Notes</label><textarea class="form-control form-control-sm" id="spu_notes" rows="2"></textarea></div>
    </div>
    <div class="modal-footer" style="background:#f8f9fa;border-top:2px solid #e0e0e0;">
      <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary btn-sm" onclick="saveSupplierPurchase()"><i class="bi bi-check-lg"></i> Save Purchase</button>
    </div>
  </div></div>
</div>

<!-- ── SUPPLIER HISTORY MODAL ──────────────────────────────────────────── -->
<div class="modal fade" id="supplierHistoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header" style="background:#f8f9fa;border-bottom:2px solid #e0e0e0;">
      <h5 class="modal-title fw-bold"><i class="bi bi-clock-history me-2"></i>Transaction History — <span id="sh_supplier_name"></span></h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="sh_body" style="min-height:200px;">
      <div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>
    </div>
  </div></div>
</div>

<script>
// ── Supplier Payment / Purchase / History ──
function openSupplierPayment(id, name, balance) {
    document.getElementById('sp_supplier_id').value = id;
    document.getElementById('sp_supplier_name').value = name;
    document.getElementById('sp_balance').value = '₱' + parseFloat(balance||0).toFixed(2);
    document.getElementById('sp_amount').value = '';
    document.getElementById('sp_reference').value = '';
    document.getElementById('sp_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('sp_notes').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierPaymentModal')).show();
}

function saveSupplierPayment() {
    const id = document.getElementById('sp_supplier_id').value;
    const amount = parseFloat(document.getElementById('sp_amount').value);
    const date = document.getElementById('sp_date').value;
    if (!amount || amount <= 0) { alert('Please enter a valid amount.'); return; }
    if (!date) { alert('Please select a date.'); return; }
    invPost('supplier_transaction', {
        supplier_id: id,
        transaction_type: 'payment',
        amount: amount,
        reference_number: document.getElementById('sp_reference').value.trim(),
        transaction_date: date,
        notes: document.getElementById('sp_notes').value.trim(),
    }, () => bootstrap.Modal.getInstance(document.getElementById('supplierPaymentModal')).hide());
}

function openSupplierPurchase(id, name) {
    document.getElementById('spu_supplier_id').value = id;
    document.getElementById('spu_supplier_name').value = name;
    document.getElementById('spu_amount').value = '';
    document.getElementById('spu_reference').value = '';
    document.getElementById('spu_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('spu_notes').value = '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierPurchaseModal')).show();
}

function saveSupplierPurchase() {
    const id = document.getElementById('spu_supplier_id').value;
    const amount = parseFloat(document.getElementById('spu_amount').value);
    const date = document.getElementById('spu_date').value;
    if (!amount || amount <= 0) { alert('Please enter a valid amount.'); return; }
    if (!date) { alert('Please select a date.'); return; }
    invPost('supplier_transaction', {
        supplier_id: id,
        transaction_type: 'purchase',
        amount: amount,
        reference_number: document.getElementById('spu_reference').value.trim(),
        transaction_date: date,
        notes: document.getElementById('spu_notes').value.trim(),
    }, () => bootstrap.Modal.getInstance(document.getElementById('supplierPurchaseModal')).hide());
}

function viewSupplierHistory(id, name) {
    document.getElementById('sh_supplier_name').textContent = name;
    document.getElementById('sh_body').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-secondary"></div></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('supplierHistoryModal')).show();

    fetch('?action=get_supplier_history&supplier_id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) { document.getElementById('sh_body').innerHTML = '<p class="text-danger">'+res.message+'</p>'; return; }
            const txs = res.data || [];
            if (txs.length === 0) {
                document.getElementById('sh_body').innerHTML = '<p class="text-muted text-center py-4">No transactions found.</p>';
                return;
            }
            const fmt = n => '₱' + parseFloat(n||0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            let html = `<table class="table table-sm table-bordered" style="font-size:12px;">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Amount</th><th>Reference</th><th>Notes</th></tr></thead><tbody>`;
            txs.forEach(tx => {
                const isPayment = tx.transaction_type === 'payment';
                const isReturn = tx.transaction_type === 'return';
                const badgeClass = isPayment ? 'bg-success' : (isReturn ? 'bg-info' : (tx.transaction_type === 'purchase' ? 'bg-primary' : 'bg-secondary'));
                html += `<tr>
                    <td>${tx.transaction_date}</td>
                    <td><span class="badge ${badgeClass}">${tx.transaction_type.charAt(0).toUpperCase()+tx.transaction_type.slice(1)}</span></td>
                    <td class="${isPayment||isReturn?'text-success':'text-danger'} fw-bold">${isPayment||isReturn?'-':'+'} ${fmt(tx.amount)}</td>
                    <td>${tx.reference_number||'—'}</td>
                    <td><small class="text-muted">${tx.notes||'—'}</small></td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('sh_body').innerHTML = html;
        })
        .catch(() => { document.getElementById('sh_body').innerHTML = '<p class="text-danger">Failed to load history.</p>'; });
}
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
