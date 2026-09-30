<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../models/Report.php';

if (defined('REPORTS_MAINTENANCE_MODE') && REPORTS_MAINTENANCE_MODE) {
    redirect(routeUrl('maintenance'));
}

requireLogin();
requireAnyRole(['admin', 'cashier']);

$canManageExpenses = hasAnyRole(['admin', 'cashier']);

$pageTitle = 'Reports';

// Date range filter (default to today)
$dateFrom = $_GET['from'] ?? date('Y-m-d');
$dateTo = $_GET['to'] ?? date('Y-m-d');

if (!strtotime($dateFrom)) {
    $dateFrom = date('Y-m-d');
}
if (!strtotime($dateTo)) {
    $dateTo = date('Y-m-d');
}
if ($dateFrom > $dateTo) {
    $dateFrom = date('Y-m-d');
    $dateTo = date('Y-m-d');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_expense') {
    try {
        validateCSRF();

        if (!$canManageExpenses) {
            throw new Exception('Only admin or cashier can delete expenses.');
        }

        $expenseId = trim((string)($_POST['expense_id'] ?? ''));
        if ($expenseId === '') {
            throw new Exception('Expense ID is required.');
        }

        $allExpenses = getReportExpenses();
        $updatedExpenses = array_values(array_filter($allExpenses, function ($row) use ($expenseId) {
            return (string)($row['id'] ?? '') !== $expenseId;
        }));

        if (count($updatedExpenses) === count($allExpenses)) {
            throw new Exception('Expense entry not found.');
        }

        $removedExpense = null;
        foreach ($allExpenses as $row) {
            if ((string)($row['id'] ?? '') === $expenseId) {
                $removedExpense = $row;
                break;
            }
        }
        if (!$removedExpense) {
            throw new Exception('Expense entry not found.');
        }

        $archivedExpenseId = archiveDeletedRecord('report_expense', $removedExpense, ['source' => 'json']);
        if (!$archivedExpenseId) {
            throw new Exception('Failed to archive expense entry before deletion.');
        }

        if (!saveReportExpenses($updatedExpenses)) {
            deleteArchivedRecordById($archivedExpenseId);
            throw new Exception('Failed to delete expense entry.');
        }

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        $removedAmount = (float)($removedExpense['amount'] ?? 0);
        $removedDate = (string)($removedExpense['expense_date'] ?? date('Y-m-d'));
        notifyRoles(
            'system',
            'Expense Deleted',
            buildNotificationMessageTemplate(
                $actorName,
                'deleted',
                'expense entry',
                'Amount: ₱' . number_format($removedAmount, 2) . ', Date: ' . date('M d, Y', strtotime($removedDate))
            ),
            ['admin', 'cashier'],
            [
                'reference_type' => 'report_expense',
            ]
        );
        logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_expense', 'Deleted expense entry: ₱' . number_format($removedAmount, 2) . ' on ' . date('M d, Y', strtotime($removedDate)));

        setMessage('Expense entry deleted successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(routeUrl('reports', ['from' => $redirectFrom, 'to' => $redirectTo]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_expense') {
    try {
        validateCSRF();

        $expenseDate = $_POST['expense_date'] ?? date('Y-m-d');
        $expenseAmount = (float)($_POST['expense_amount'] ?? 0);
        $expenseNotes = trim((string)($_POST['expense_notes'] ?? ''));
        $expenseCategory = trim((string)($_POST['expense_category'] ?? 'Outsource'));
        $plateNumber = trim((string)($_POST['plate_number'] ?? ''));
        $jobOrderIdRaw = trim((string)($_POST['job_order_id'] ?? ''));
        $jobOrderId = $jobOrderIdRaw !== '' ? (int)$jobOrderIdRaw : null;
        $paymentMethod = trim((string)($_POST['payment_method'] ?? 'Cash'));

        if (!strtotime($expenseDate)) {
            throw new Exception('Please provide a valid expense date.');
        }
        if ($expenseAmount <= 0) {
            throw new Exception('Expense amount must be greater than zero.');
        }
        if ($jobOrderId !== null && $jobOrderId <= 0) {
            throw new Exception('Please provide a valid JO ID.');
        }
        if ($jobOrderId !== null) {
            $existingJo = Database::getInstance()->fetch("SELECT id, job_order_number, vehicle_id FROM job_orders WHERE id = ? LIMIT 1", [$jobOrderId]);
            if (!$existingJo) {
                throw new Exception('The selected JO ID was not found.');
            }
            if ($plateNumber === '') {
                $vehicleInfo = Database::getInstance()->fetch("SELECT plate_number FROM vehicles WHERE id = ? LIMIT 1", [(int)($existingJo['vehicle_id'] ?? 0)]);
                if ($vehicleInfo) {
                    $plateNumber = (string)($vehicleInfo['plate_number'] ?? '');
                }
            }
        }
        if ($plateNumber !== '') {
            $plateNumber = preg_replace('/\s+/', ' ', $plateNumber);
            $plateNumber = strtoupper($plateNumber);
        }

        $added = addReportExpense([
            'expense_date' => date('Y-m-d', strtotime($expenseDate)),
            'category' => $expenseCategory !== '' ? $expenseCategory : 'Outsource',
            'description' => $expenseNotes,
            'amount' => $expenseAmount,
            'payment_method' => $paymentMethod,
            'plate_number' => $plateNumber,
            'job_order_id' => $jobOrderId,
            'created_by' => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System'
        ]);

        if (!$added) {
            throw new Exception('Failed to save expense entry.');
        }

        $actorName = sanitize($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Staff');
        notifyRoles(
            'system',
            'Expense Added',
            buildNotificationMessageTemplate(
                $actorName,
                'added',
                'an expense',
                'Amount: ₱' . number_format($expenseAmount, 2) . ', Date: ' . date('M d, Y', strtotime($expenseDate))
            ),
            ['admin', 'cashier'],
            [
                'reference_type' => 'report_expense',
            ]
        );
        logActivity((int)($_SESSION['user_id'] ?? 0), 'add_expense', 'Added expense entry: ₱' . number_format($expenseAmount, 2) . ' on ' . date('M d, Y', strtotime($expenseDate)));

        setMessage('Expense entry added successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(routeUrl('reports', ['from' => $redirectFrom, 'to' => $redirectTo]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_income') {
    try {
        validateCSRF();

        $incomeDate = $_POST['income_date'] ?? date('Y-m-d');
        $incomeAmount = (float)($_POST['income_amount'] ?? 0);
        $incomeNotes = trim((string)($_POST['income_notes'] ?? ''));
        $incomePaymentMethod = trim((string)($_POST['income_payment_method'] ?? 'Cash'));
        $allowedIncomePaymentMethods = ['Cash', 'GCash', 'Bank Transfer', 'Swipe/Card'];
        if (!in_array($incomePaymentMethod, $allowedIncomePaymentMethods, true)) {
            $incomePaymentMethod = 'Cash';
        }

        if (!strtotime($incomeDate)) {
            throw new Exception('Please provide a valid income date.');
        }
        if ($incomeAmount <= 0) {
            throw new Exception('Income amount must be greater than zero.');
        }

        $added = addReportManualIncome([
            'income_date' => date('Y-m-d', strtotime($incomeDate)),
            'description' => $incomeNotes,
            'amount' => $incomeAmount,
            'payment_method' => $incomePaymentMethod,
            'created_by' => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System'
        ]);

        if (!$added) {
            throw new Exception('Failed to save income entry.');
        }

        logActivity((int)($_SESSION['user_id'] ?? 0), 'add_manual_income', 'Added manual income: ₱' . number_format($incomeAmount, 2) . ' on ' . date('M d, Y', strtotime($incomeDate)));
        setMessage('Income entry added successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(routeUrl('reports', ['from' => $redirectFrom, 'to' => $redirectTo]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_income') {
    try {
        validateCSRF();

        if (!$canManageExpenses) {
            throw new Exception('Only admin or cashier can delete income entries.');
        }

        $incomeId = trim((string)($_POST['income_id'] ?? ''));
        if ($incomeId === '') {
            throw new Exception('Income ID is required.');
        }

        $allIncome = getReportManualIncome();
        $updatedIncome = array_values(array_filter($allIncome, function ($row) use ($incomeId) {
            return (string)($row['id'] ?? '') !== $incomeId;
        }));

        if (count($updatedIncome) === count($allIncome)) {
            throw new Exception('Income entry not found.');
        }

        $removedIncome = null;
        foreach ($allIncome as $row) {
            if ((string)($row['id'] ?? '') === $incomeId) {
                $removedIncome = $row;
                break;
            }
        }
        if (!$removedIncome) {
            throw new Exception('Income entry not found.');
        }

        $archivedIncomeId = archiveDeletedRecord('manual_income', $removedIncome, ['source' => 'json']);
        if (!$archivedIncomeId) {
            throw new Exception('Failed to archive income entry before deletion.');
        }

        if (!saveReportManualIncome($updatedIncome)) {
            deleteArchivedRecordById($archivedIncomeId);
            throw new Exception('Failed to delete income entry.');
        }

        logActivity((int)($_SESSION['user_id'] ?? 0), 'delete_manual_income', 'Deleted manual income entry');
        setMessage('Income entry deleted successfully.', 'success');
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }

    $redirectFrom = $_POST['from'] ?? $dateFrom;
    $redirectTo = $_POST['to'] ?? $dateTo;
    redirect(routeUrl('reports', ['from' => $redirectFrom, 'to' => $redirectTo]));
}

$allExpenses = getReportExpenses();
$filteredExpenses = array_values(array_filter($allExpenses, function ($row) use ($dateFrom, $dateTo) {
    $expenseDate = $row['expense_date'] ?? '';
    return $expenseDate >= $dateFrom && $expenseDate <= $dateTo;
}));

$expensePaymentTotals = [
    'cash' => 0.0,
    'bank_transfer' => 0.0,
    'gcash' => 0.0,
];
foreach ($filteredExpenses as $expensePaymentRow) {
    $expensePaymentMethod = strtolower(trim((string)($expensePaymentRow['payment_method'] ?? 'cash')));
    $expensePaymentKey = str_replace([' ', '-'], '_', $expensePaymentMethod);
    if ($expensePaymentKey === 'bank' || $expensePaymentKey === 'banktransfer') {
        $expensePaymentKey = 'bank_transfer';
    }
    if (isset($expensePaymentTotals[$expensePaymentKey])) {
        $expensePaymentTotals[$expensePaymentKey] += (float)($expensePaymentRow['amount'] ?? 0);
    }
}

usort($filteredExpenses, function ($a, $b) {
    $aDate = ($a['expense_date'] ?? '') . ' ' . ($a['created_at'] ?? '');
    $bDate = ($b['expense_date'] ?? '') . ' ' . ($b['created_at'] ?? '');
    return strcmp($aDate, $bDate); // oldest first
});

$allManualIncome = getReportManualIncome();
$filteredManualIncome = array_values(array_filter($allManualIncome, function ($row) use ($dateFrom, $dateTo) {
    $incomeDate = $row['income_date'] ?? '';
    return $incomeDate >= $dateFrom && $incomeDate <= $dateTo;
}));

usort($filteredManualIncome, function ($a, $b) {
    $aDate = ($a['income_date'] ?? '') . ' ' . ($a['created_at'] ?? '');
    $bDate = ($b['income_date'] ?? '') . ' ' . ($b['created_at'] ?? '');
    return strcmp($bDate, $aDate);
});

$reportModel = new Report();
$activeShop = function_exists('getActiveShopOption') ? getActiveShopOption() : ['name' => APP_NAME];
$activeShopName = $activeShop['name'] ?? APP_NAME;
$incomeReport = $reportModel->getIncomeReport($dateFrom, $dateTo);
$productCostExpenses = $reportModel->getPaidProductCostExpenses($dateFrom, $dateTo);
$serviceStats = $reportModel->getServiceTypeStats($dateFrom, $dateTo);
$paymentMethods = $reportModel->getPaymentMethodStats($dateFrom, $dateTo);
$paymentSummary = $reportModel->getPaymentStatusSummary($dateFrom, $dateTo);
$statusSummary = $reportModel->getJobOrderStatusSummary($dateFrom, $dateTo);
$topCustomers = $reportModel->getTopCustomers(10, $dateFrom, $dateTo);
$recentActivity = $reportModel->getRecentActivity(0, $dateFrom, $dateTo);
$techPerformance = $reportModel->getTechnicianPerformance($dateFrom, $dateTo);
$jobOrderOptions = Database::getInstance()->fetchAll(
    "SELECT jo.id, jo.job_order_number, v.plate_number
     FROM job_orders jo
     LEFT JOIN vehicles v ON v.id = jo.vehicle_id
     WHERE jo.status != 'cancelled'
     ORDER BY jo.created_at DESC",
    []
);
// Filtered list for the Add Expense dropdown — excludes paid+released JOs
$jobOrderOptionsForExpense = Database::getInstance()->fetchAll(
    "SELECT jo.id, jo.job_order_number, v.plate_number
     FROM job_orders jo
     LEFT JOIN vehicles v ON v.id = jo.vehicle_id
     WHERE jo.status != 'cancelled'
       AND NOT (jo.status = 'released' AND jo.payment_status = 'paid')
     ORDER BY jo.created_at DESC",
    []
);
$jobOrderNumberMap = [];
$jobOrderPlateMap = [];
foreach ($jobOrderOptions as $jobOrderOption) {
    $jobOrderNumberMap[(int)($jobOrderOption['id'] ?? 0)] = (string)($jobOrderOption['job_order_number'] ?? '');
    $joNum = (string)($jobOrderOption['job_order_number'] ?? '');
    if ($joNum !== '') {
        $jobOrderPlateMap[$joNum] = strtoupper(trim((string)($jobOrderOption['plate_number'] ?? '')));
    }
}

$totalOrders = array_sum(array_column($incomeReport, 'job_orders_count'));
$totalIncome = array_sum(array_column($incomeReport, 'total_income'));
$paidIncome = array_sum(array_column($incomeReport, 'paid_income')); // fully paid JO amounts only
$partialIncome = array_sum(array_column($incomeReport, 'partial_income'));
$pendingIncome = array_sum(array_column($incomeReport, 'pending_income'));
$totalManualIncome = 0;
foreach ($filteredManualIncome as $incItem) {
    $totalManualIncome += (float)($incItem['amount'] ?? 0);
}
$totalExpenses = 0;
foreach ($filteredExpenses as $expenseItem) {
    $totalExpenses += (float)($expenseItem['amount'] ?? 0);
}
foreach ($productCostExpenses as $productCostExpense) {
    $totalExpenses += (float)($productCostExpense['amount'] ?? 0);
}
$netIncome = max(0.0, ($paidIncome + $totalManualIncome) - $totalExpenses);

$unitExpenseReport = [];
$unitReportJoIds = [];
$unitReportPlateNumbers = [];
$unitReportJoCreatedAt = [];
$unitJoDetailMap = [];
$unitProductMap = [];
$unitPaymentTotals = [
    'cash' => 0.0,
    'bank_transfer' => 0.0,
    'gcash' => 0.0,
    'card' => 0.0,
];
$unitJobOrderWhere = "jo.status = 'released' AND jo.payment_status = 'paid' AND DATE(GREATEST(COALESCE(jo.payment_date, jo.updated_at), jo.updated_at)) BETWEEN ? AND ?";
$unitJobOrderParams = [$dateFrom, $dateTo];

$unitJobOrders = Database::getInstance()->fetchAll(
        "SELECT jo.id, jo.job_order_number, jo.total_amount, jo.parts_total, jo.created_at,
            COALESCE(
            NULLIF((SELECT GROUP_CONCAT(DISTINCT p.payment_method ORDER BY p.payment_method SEPARATOR ',')
                FROM job_order_payments p
                WHERE p.job_order_id = jo.id), ''),
            jo.payment_method
            ) AS jo_payment_methods,
            c.full_name AS customer_name,
            v.plate_number,
            COALESCE(jo.vehicle_id, 0) AS vehicle_id
     FROM job_orders jo
     LEFT JOIN customers c ON c.id = jo.customer_id
     LEFT JOIN vehicles v ON v.id = jo.vehicle_id
     WHERE {$unitJobOrderWhere}
     ORDER BY jo.created_at DESC",
    $unitJobOrderParams
);

$unitProductRows = Database::getInstance()->fetchAll(
    "SELECT jop.job_order_id, jop.product_id, jop.product_name, jop.product_type, jop.quantity,
           jop.created_at,
           COALESCE(
               (SELECT u.full_name FROM users u WHERE u.id = jo.created_by LIMIT 1),
               (SELECT s.full_name FROM staff s WHERE s.id = jo.created_by LIMIT 1),
               'System'
           ) AS entered_by,
           CASE WHEN pr.id IS NOT NULL THEN COALESCE(pr.cost_price, 0) ELSE 0 END AS cost_price,
           CASE WHEN pr.id IS NOT NULL THEN COALESCE(jop.quantity, 0) * COALESCE(pr.cost_price, 0) ELSE 0 END AS cost_total
     FROM job_order_products jop
     INNER JOIN job_orders jo ON jo.id = jop.job_order_id
     LEFT JOIN products pr ON pr.id = jop.product_id
     WHERE {$unitJobOrderWhere}
     ORDER BY jop.job_order_id, jop.id",
    $unitJobOrderParams
);

foreach ($unitProductRows as $unitProductRow) {
    $unitProductJoId = (int)($unitProductRow['job_order_id'] ?? 0);
    $unitProductMap[$unitProductJoId][] = $unitProductRow;
}

$unitJoPaymentRows = Database::getInstance()->fetchAll(
    "SELECT p.payment_method, SUM(p.amount) AS total_amount
     FROM job_order_payments p
     INNER JOIN job_orders jo ON jo.id = p.job_order_id
     WHERE {$unitJobOrderWhere}
     GROUP BY p.payment_method
     UNION ALL
     SELECT jo.payment_method, SUM(jo.total_amount) AS total_amount
     FROM job_orders jo
     WHERE {$unitJobOrderWhere}
       AND NOT EXISTS (SELECT 1 FROM job_order_payments p2 WHERE p2.job_order_id = jo.id)
     GROUP BY jo.payment_method",
    array_merge($unitJobOrderParams, $unitJobOrderParams)
);
foreach ($unitJoPaymentRows as $unitJoPaymentRow) {
    $unitJoPaymentKey = str_replace([' ', '-'], '_', strtolower((string)($unitJoPaymentRow['payment_method'] ?? 'cash')));
    if ($unitJoPaymentKey === 'bank' || $unitJoPaymentKey === 'banktransfer') {
        $unitJoPaymentKey = 'bank_transfer';
    }
    if ($unitJoPaymentKey === 'paymaya' || $unitJoPaymentKey === 'maya') {
        $unitJoPaymentKey = 'card';
    }
    if (isset($unitPaymentTotals[$unitJoPaymentKey])) {
        $unitPaymentTotals[$unitJoPaymentKey] += (float)($unitJoPaymentRow['total_amount'] ?? 0);
    }
}

foreach ($unitJobOrders as $unitRow) {
    $joId = (int)($unitRow['id'] ?? 0);
    $plateNumber = trim((string)($unitRow['plate_number'] ?? ''));
    $unitProductCostTotal = 0.0;
    $unitProductRowsForJo = $unitProductMap[$joId] ?? [];
    foreach ($unitProductRowsForJo as $unitProductCostRow) {
        if ((int)($unitProductCostRow['product_id'] ?? 0) <= 0) {
            continue;
        }
        $unitProductCostTotal += (float)($unitProductCostRow['cost_total'] ?? 0);
    }
    if (empty($unitProductRowsForJo)) {
        $unitProductCostTotal = (float)($unitRow['parts_total'] ?? 0);
    }
    $unitExpenseReport[$joId] = [
        'id' => $joId,
        'job_order_number' => (string)($unitRow['job_order_number'] ?? 'N/A'),
        'customer_name' => (string)($unitRow['customer_name'] ?? 'Customer'),
        'plate_number' => $plateNumber !== '' ? strtoupper($plateNumber) : 'N/A',
        'jo_payment_methods' => (string)($unitRow['jo_payment_methods'] ?? ''),
        'total_amount' => (float)($unitRow['total_amount'] ?? 0),
        'parts_total' => $unitProductCostTotal,
        'manual_expenses' => 0.0,
        'outsource_expenses' => 0.0,
        'other_expenses' => 0.0,
        'carwash_total' => 0.0,
        'stock_total' => 0.0,
    ];
    $unitJoDetailMap[$joId] = [
        'parts_total' => 0.0,
        'stock_total' => 0.0,
        'outsource_total' => 0.0,
        'other_total' => 0.0,
        'carwash_total' => 0.0,
        'payment_methods' => [],
        'entries' => [],
    ];
    $unitReportJoIds[$joId] = true;
    $unitReportJoCreatedAt[$joId] = $unitRow['created_at'] ?? null;
    if ($plateNumber !== '') {
        $plateKey = strtoupper($plateNumber);
        if (!isset($unitReportPlateNumbers[$plateKey])) {
            $unitReportPlateNumbers[$plateKey] = [];
        }
        $unitReportPlateNumbers[$plateKey][] = $joId;
    }
}

foreach ($allExpenses as $expenseRow) {
    $expenseAmount = (float)($expenseRow['amount'] ?? 0);
    if ($expenseAmount <= 0) {
        continue;
    }

    $category = strtolower(trim((string)($expenseRow['category'] ?? 'Others')));
    $jobOrderId = isset($expenseRow['job_order_id']) ? (int)$expenseRow['job_order_id'] : 0;
    $plateNumber = strtoupper(trim((string)($expenseRow['plate_number'] ?? '')));
    $paymentMethod = trim((string)($expenseRow['payment_method'] ?? 'Cash'));
    if ($paymentMethod === '') {
        $paymentMethod = 'Cash';
    }

    $targetJoId = null;

    // Only an explicit JO link is authoritative. Plate numbers are reused
    // across JOs, so they must not be used to infer ownership.
    if ($jobOrderId > 0 && isset($unitExpenseReport[$jobOrderId])) {
        $targetJoId = $jobOrderId;
    }

    if ($targetJoId === null) {
        continue;
    }

    $detailEntry = [
        'category' => $category,
        'amount' => $expenseAmount,
        'payment_method' => $paymentMethod,
        'description' => (string)($expenseRow['description'] ?? 'Expense'),
        'created_at' => $expenseRow['created_at'] ?? null,
        'entered_by' => $expenseRow['created_by'] ?? 'System',
    ];
    $unitJoDetailMap[$targetJoId]['entries'][] = $detailEntry;

    if (strpos($category, 'outsource') !== false) {
        $unitExpenseReport[$targetJoId]['outsource_expenses'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['outsource_total'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] = ($unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] ?? 0) + $expenseAmount;
    } elseif (strpos($category, 'carwash') !== false) {
        $unitExpenseReport[$targetJoId]['carwash_total'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['carwash_total'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] = ($unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] ?? 0) + $expenseAmount;
    } elseif (strpos($category, 'other') !== false) {
        $unitExpenseReport[$targetJoId]['other_expenses'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['other_total'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] = ($unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] ?? 0) + $expenseAmount;
    } else {
        $unitExpenseReport[$targetJoId]['manual_expenses'] += $expenseAmount;
        $unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] = ($unitJoDetailMap[$targetJoId]['payment_methods'][$paymentMethod] ?? 0) + $expenseAmount;
    }
}

$unitExpenseReport = array_values(array_filter($unitExpenseReport, function ($row) {
    return !empty($row['job_order_number']);
}));

usort($unitExpenseReport, function ($a, $b) {
    $aTotal = (float)($a['total_amount'] ?? 0) + (float)($a['parts_total'] ?? 0) + (float)($a['manual_expenses'] ?? 0) + (float)($a['outsource_expenses'] ?? 0);
    $bTotal = (float)($b['total_amount'] ?? 0) + (float)($b['parts_total'] ?? 0) + (float)($b['manual_expenses'] ?? 0) + (float)($b['outsource_expenses'] ?? 0);
    return $bTotal <=> $aTotal;
});

$unitReportTotalAmount = 0.0;
$unitReportTotalExpenses = 0.0;
$unitReportTotalNet = 0.0;
foreach ($unitExpenseReport as $unitReportTotalRow) {
    $unitReportTotalAmount += (float)($unitReportTotalRow['total_amount'] ?? 0);
    $unitReportExpenseTotal = (float)($unitReportTotalRow['manual_expenses'] ?? 0)
        + (float)($unitReportTotalRow['outsource_expenses'] ?? 0)
        + (float)($unitReportTotalRow['other_expenses'] ?? 0)
        + (float)($unitReportTotalRow['carwash_total'] ?? 0);
    $unitReportTotalExpenses += (float)($unitReportTotalRow['parts_total'] ?? 0) + $unitReportExpenseTotal;
    $unitReportNet = (float)($unitReportTotalRow['total_amount'] ?? 0)
        - (float)($unitReportTotalRow['parts_total'] ?? 0)
        - $unitReportExpenseTotal;
    $unitReportTotalNet += max(0.0, $unitReportNet);
}

$netIncomeTransactions = [];
$paymentLedger = Database::getInstance()->fetchAll(
    "SELECT
        COALESCE((SELECT MAX(p.payment_date) FROM job_order_payments p WHERE p.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at) AS transaction_at,
        COALESCE(jo.total_amount, 0) AS amount,
        jo.job_order_number,
        c.full_name AS customer_name
     FROM job_orders jo
     LEFT JOIN customers c ON c.id = jo.customer_id
     WHERE jo.status != 'cancelled'
       AND jo.payment_status = 'paid'
       AND DATE(COALESCE((SELECT MAX(p2.payment_date) FROM job_order_payments p2 WHERE p2.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at)) BETWEEN ? AND ?
     ORDER BY transaction_at DESC",
    [$dateFrom, $dateTo]
);

foreach ($paymentLedger as $paymentRow) {
    $paymentAmount = (float)($paymentRow['amount'] ?? 0);
    if ($paymentAmount <= 0) {
        continue;
    }

    $transactionAt = (string)($paymentRow['transaction_at'] ?? '');
    $netIncomeTransactions[] = [
        'timestamp' => $transactionAt,
        'date' => $transactionAt !== '' ? date('Y-m-d h:i A', strtotime($transactionAt)) : date('Y-m-d h:i A', strtotime($dateFrom)),
        'type' => 'JO Paid',
        'reference' => (string)($paymentRow['job_order_number'] ?? 'JO'),
        'description' => 'Job Order payment · ' . (($paymentRow['customer_name'] ?? 'Customer') ?: 'Customer'),
        'amount' => $paymentAmount,
        'direction' => 'in'
    ];
}

foreach ($filteredManualIncome as $manualIncomeRow) {
    $manualAmount = (float)($manualIncomeRow['amount'] ?? 0);
    if ($manualAmount <= 0) {
        continue;
    }

    $transactionAt = (string)($manualIncomeRow['created_at'] ?? ($manualIncomeRow['income_date'] ?? date('Y-m-d H:i:s')));
    $netIncomeTransactions[] = [
        'timestamp' => $transactionAt,
        'date' => date('Y-m-d h:i A', strtotime($transactionAt)),
        'type' => 'Income',
        'reference' => 'Manual Income',
        'description' => (string)($manualIncomeRow['description'] ?? 'Manual income'),
        'amount' => $manualAmount,
        'direction' => 'in'
    ];
}

foreach ($filteredExpenses as $expenseRow) {
    $expenseAmount = (float)($expenseRow['amount'] ?? 0);
    if ($expenseAmount <= 0) {
        continue;
    }

    $transactionAt = (string)($expenseRow['created_at'] ?? ($expenseRow['expense_date'] ?? date('Y-m-d H:i:s')));
    $expenseCategory = strtolower(trim((string)($expenseRow['category'] ?? '')));
    if (strpos($expenseCategory, 'outsource') !== false) {
        $expenseType = 'Outsource Expense';
    } elseif (strpos($expenseCategory, 'carwash') !== false) {
        $expenseType = 'Carwash Expense';
    } else {
        $expenseType = 'Other Expense';
    }
    $netIncomeTransactions[] = [
        'timestamp' => $transactionAt,
        'date' => date('Y-m-d h:i A', strtotime($transactionAt)),
        'type' => $expenseType,
        'reference' => 'Expense',
        'description' => (string)($expenseRow['description'] ?? 'Expense'),
        'amount' => $expenseAmount,
        'direction' => 'out'
    ];
}
foreach ($productCostExpenses as $productCostExpense) {
    $productCostAmount = (float)($productCostExpense['amount'] ?? 0);
    if ($productCostAmount <= 0) continue;
    $productCostAt = (string)($productCostExpense['expense_at'] ?? ($productCostExpense['expense_date'] ?? date('Y-m-d H:i:s')));
    $productCostDesc = trim((string)($productCostExpense['products'] ?? '')) !== ''
        ? (string)$productCostExpense['products']
        : 'Parts cost for paid JO - ' . (($productCostExpense['customer_name'] ?? 'Customer') ?: 'Customer');
    $netIncomeTransactions[] = ['timestamp' => $productCostAt, 'date' => date('Y-m-d h:i A', strtotime($productCostAt)), 'type' => 'Product Cost', 'reference' => (string)($productCostExpense['job_order_number'] ?? 'JO'), 'description' => $productCostDesc, 'amount' => $productCostAmount, 'direction' => 'out'];
}


usort($netIncomeTransactions, function ($a, $b) {
    $aTime = strtotime($a['timestamp'] ?: $a['date'] ?: '1970-01-01 00:00:00');
    $bTime = strtotime($b['timestamp'] ?: $b['date'] ?: '1970-01-01 00:00:00');
    return $bTime <=> $aTime;
});

if (($_GET['export'] ?? '') === 'excel') {
    $db = Database::getInstance();
    $jobOrderDetails = $db->fetchAll(
        "SELECT
            jo.job_order_number,
            jo.created_at,
            jo.status,
            jo.payment_status,
            jo.total_amount,
            jo.partial_amount,
            c.full_name AS customer_name,
            c.phone AS customer_phone,
            v.brand,
            v.model,
            v.plate_number
         FROM job_orders jo
         LEFT JOIN customers c ON jo.customer_id = c.id
         LEFT JOIN vehicles v ON jo.vehicle_id = v.id
         WHERE jo.status != 'cancelled' AND DATE(jo.created_at) BETWEEN ? AND ?
         ORDER BY jo.created_at DESC",
        [$dateFrom, $dateTo]
    );

    $safeFrom = date('m-d-y', strtotime($dateFrom));
    $safeTo   = date('m-d-y', strtotime($dateTo));
    $filename = "{$safeFrom}_{$safeTo}.xls";

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "<html><head><meta charset='UTF-8'><style>";
    echo "body{font-family:Calibri,Arial,sans-serif;font-size:12px;color:#111;}";
    echo ".report-title{font-size:22px;font-weight:700;margin:0 0 6px 0;color:#1f2937;}";
    echo ".report-sub{font-size:12px;color:#4b5563;margin:0 0 12px 0;}";
    echo ".meta{margin-bottom:10px;}";
    echo ".meta td{padding:4px 8px;border:none;}";
    echo ".note{background:#f8fafc;border:1px solid #dbe3ee;padding:8px 10px;margin:8px 0 14px 0;color:#334155;}";
    echo ".section{font-size:15px;font-weight:700;color:#1f2937;margin:14px 0 6px 0;}";
    echo "table{border-collapse:collapse;width:100%;margin-bottom:14px;}";
    echo "th,td{border:1px solid #d1d5db;padding:6px 8px;vertical-align:middle;}";
    echo "th{background:#eef2f7;color:#111827;font-weight:700;text-align:left;white-space:nowrap;}";
    echo "tr:nth-child(even) td{background:#fafafa;}";
    echo ".text-right{text-align:right;}";
    echo ".muted{color:#6b7280;}";
    echo "</style></head><body>";

    echo "<div class='report-title'>" . escape($activeShopName) . " Reports</div>";
    echo "<div class='report-sub'>Exported financial and operational report</div>";
    echo "<table class='meta'>";
    echo "<tr><td><strong>Period:</strong></td><td>" . escape($dateFrom) . " to " . escape($dateTo) . "</td></tr>";
    echo "<tr><td><strong>Generated At:</strong></td><td>" . escape(date('Y-m-d h:i A')) . "</td></tr>";
    echo "<tr><td><strong>Generated By:</strong></td><td>" . escape($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'System') . "</td></tr>";
    echo "</table>";


    echo "<div class='section'>1. Executive Summary</div>";
    echo "<table>";
    echo "<tr><th>Total Job Orders</th><th>Total Income (PHP)</th><th>JO Paid Income (PHP)</th><th>JO Partial Income (PHP)</th><th>(Outside) Income (PHP)</th><th>Expenses (PHP)</th><th>Net Income (PHP)</th><th>Pending Income (PHP)</th></tr>";
    echo "<tr>";
    echo "<td class='text-right'>" . number_format($totalOrders) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$totalIncome + (float)$totalManualIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$paidIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$partialIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$totalManualIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$totalExpenses, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$netIncome, 2) . "</td>";
    echo "<td class='text-right'>" . number_format((float)$pendingIncome, 2) . "</td>";
    echo "</tr></table>";

    echo "<div class='section'>2. Expenses Log</div>";
    echo "<table><tr><th>Date</th><th>Category</th><th>Description</th><th>Entered By</th><th>Payment Method</th><th class='text-right'>Amount (PHP)</th></tr>";

    // Merge manual expenses + product cost expenses into one list
    $allExcelExpenses = [];
    foreach ($filteredExpenses as $expenseRow) {
        $allExcelExpenses[] = [
            'date'           => $expenseRow['expense_date'] ?? '—',
            'category'       => $expenseRow['category'] ?? 'General',
            'description'    => $expenseRow['description'] ?? '—',
            'entered_by'     => $expenseRow['created_by'] ?? 'System',
            'payment_method' => $expenseRow['payment_method'] ?? 'Cash',
            'amount'         => (float)($expenseRow['amount'] ?? 0),
        ];
    }
    foreach ($productCostExpenses as $pcRow) {
        $allExcelExpenses[] = [
            'date'           => $pcRow['expense_date'] ?? '—',
            'category'       => 'Product Cost',
            'description'    => $pcRow['products'] ?? $pcRow['job_order_number'] ?? '—',
            'entered_by'     => $pcRow['processed_by'] ?? 'System',
            'payment_method' => 'Stocks',
            'amount'         => (float)($pcRow['amount'] ?? 0),
        ];
    }
    // Sort by date desc
    usort($allExcelExpenses, fn($a, $b) => strcmp($b['date'], $a['date']));

    if (empty($allExcelExpenses)) {
        echo "<tr><td colspan='6'>No expenses recorded for this range</td></tr>";
        $excelExpensePaymentTotals = [];
    } else {
        $excelExpensePaymentTotals = [];
        $excelTotalExpenses = 0.0;
        foreach ($allExcelExpenses as $exRow) {
            echo "<tr>";
            echo "<td>" . escape($exRow['date']) . "</td>";
            echo "<td>" . escape($exRow['category']) . "</td>";
            echo "<td>" . escape($exRow['description']) . "</td>";
            echo "<td>" . escape($exRow['entered_by']) . "</td>";
            echo "<td>" . escape($exRow['payment_method']) . "</td>";
            echo "<td class='text-right'>" . number_format($exRow['amount'], 2) . "</td>";
            echo "</tr>";
            $excelExpensePaymentTotals[$exRow['payment_method']] = ($excelExpensePaymentTotals[$exRow['payment_method']] ?? 0.0) + $exRow['amount'];
            $excelTotalExpenses += $exRow['amount'];
        }
        arsort($excelExpensePaymentTotals);
        $pmPartsStr = implode(' | ', array_map(
            fn($pm, $amt) => escape($pm) . ': ' . number_format((float)$amt, 2),
            array_keys($excelExpensePaymentTotals),
            array_values($excelExpensePaymentTotals)
        ));
        echo "<tr style='background:#eef2f7;font-weight:700;'>";
        echo "<td>Total Expenses</td>";
        echo "<td colspan='4'>" . $pmPartsStr . "</td>";
        echo "<td class='text-right'>" . number_format($excelTotalExpenses, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<div class='section'>3. Payment Method Breakdown</div>";
    echo "<table><tr><th>Method</th><th class='text-right'>Orders</th><th class='text-right'>Amount (PHP)</th></tr>";
    if (empty($paymentMethods)) {
        echo "<tr><td colspan='3'>No payment data for this range</td></tr>";
    } else {
        foreach ($paymentMethods as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['payment_method'] ?? 'Unknown') . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>4. Job Orders Details</div>";
    echo "<table><tr>";
    echo "<th>JO #</th><th>Date</th><th>Customer</th><th>Phone</th><th>Vehicle</th><th>Plate</th><th>Status</th><th>Payment Status</th><th class='text-right'>Total (PHP)</th><th class='text-right'>Paid (PHP)</th><th class='text-right'>Pending (PHP)</th>";
    echo "</tr>";
    if (empty($jobOrderDetails)) {
        echo "<tr><td colspan='11'>No job orders for this range</td></tr>";
    } else {
        foreach ($jobOrderDetails as $row) {
            $rowTotal = (float)($row['total_amount'] ?? 0);
            $rowPartial = (float)($row['partial_amount'] ?? 0);
            $rowPayment = (string)($row['payment_status'] ?? 'pending');
            $rowPaid = 0.0;
            $rowPending = 0.0;

            if ($rowPayment === 'paid') {
                $rowPaid = $rowTotal;
            } elseif ($rowPayment === 'partial') {
                $rowPaid = max(0, min($rowPartial, $rowTotal));
                $rowPending = max(0, $rowTotal - $rowPaid);
            } else {
                $rowPending = $rowTotal;
            }

            echo "<tr>";
            echo "<td>" . escape($row['job_order_number']) . "</td>";
            echo "<td>" . escape(date('Y-m-d h:i A', strtotime($row['created_at']))) . "</td>";
            echo "<td>" . escape($row['customer_name'] ?? 'Unknown') . "</td>";
            echo "<td>" . escape($row['customer_phone'] ?? '—') . "</td>";
            echo "<td>" . escape(trim(($row['brand'] ?? '') . ' ' . ($row['model'] ?? ''))) . "</td>";
            echo "<td>" . escape($row['plate_number'] ?? '—') . "</td>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['status'] ?? 'pending'))) . "</td>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $rowPayment))) . "</td>";
            echo "<td class='text-right'>" . number_format($rowTotal, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($rowPaid, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($rowPending, 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>5. Income Trend</div>";
    echo "<table><tr><th>Date</th><th class='text-right'>Job Orders</th><th class='text-right'>Total Income (PHP)</th><th class='text-right'>Paid Income (PHP)</th><th class='text-right'>Partial Income (PHP)</th><th class='text-right'>Pending Income (PHP)</th></tr>";
    if (empty($incomeReport)) {
        echo "<tr><td colspan='6'>No data for this range</td></tr>";
    } else {
        foreach ($incomeReport as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['date']) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['job_orders_count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_income'], 2) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['paid_income'], 2) . "</td>";
            echo "<td class='text-right'>" . number_format((float)($row['partial_income'] ?? 0), 2) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['pending_income'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>6. Service Type Revenue</div>";
    echo "<table><tr><th>Service Type</th><th class='text-right'>Orders</th><th class='text-right'>Revenue (PHP)</th></tr>";
    if (empty($serviceStats)) {
        echo "<tr><td colspan='3'>No data for this range</td></tr>";
    } else {
        foreach ($serviceStats as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['service_name'] ?? $row['service_type'] ?? 'Unknown') . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_revenue'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>7. Job Order Status Summary</div>";
    echo "<table><tr><th>Status</th><th class='text-right'>Count</th><th class='text-right'>Total (PHP)</th></tr>";
    if (empty($statusSummary)) {
        echo "<tr><td colspan='3'>No status summary available</td></tr>";
    } else {
        foreach ($statusSummary as $row) {
            echo "<tr>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['status']))) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>8. Payment Status Summary</div>";
    echo "<table><tr><th>Payment Status</th><th class='text-right'>Orders</th><th class='text-right'>Total (PHP)</th></tr>";
    if (empty($paymentSummary)) {
        echo "<tr><td colspan='3'>No payment summary available</td></tr>";
    } else {
        foreach ($paymentSummary as $row) {
            echo "<tr>";
            echo "<td>" . escape(ucwords(str_replace('_', ' ', $row['payment_status']))) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['count']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_amount'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    echo "<div class='section'>9. Top Customers</div>";
    echo "<table><tr><th>Customer</th><th>Phone</th><th class='text-right'>Visits</th><th class='text-right'>Spent (PHP)</th></tr>";
    if (empty($topCustomers)) {
        echo "<tr><td colspan='4'>No customer activity yet</td></tr>";
    } else {
        foreach ($topCustomers as $row) {
            echo "<tr>";
            echo "<td>" . escape($row['customer_name']) . "</td>";
            echo "<td>" . escape($row['customer_phone']) . "</td>";
            echo "<td class='text-right'>" . number_format((int)$row['total_visits']) . "</td>";
            echo "<td class='text-right'>" . number_format((float)$row['total_spent'], 2) . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    // ── 10. Per Unit Summary ──────────────────────────────────────────────
    echo "<div class='section'>10. Per Unit Summary</div>";
    echo "<table><tr>";
    echo "<th>JO ID</th><th>Unit (Plate)</th><th>Customer</th><th class='text-right'>Total (PHP)</th><th class='text-right'>Expenses (PHP)</th><th>Payment Method</th><th class='text-right'>Net (PHP)</th>";
    echo "</tr>";
    if (empty($unitExpenseReport)) {
        echo "<tr><td colspan='7'>No paid & released job orders for this range</td></tr>";
    } else {
        $puSummaryTotalAmount    = 0.0;
        $puSummaryTotalExpenses  = 0.0;
        $puSummaryTotalNet       = 0.0;
        foreach ($unitExpenseReport as $puRow) {
            $puTotal      = (float)($puRow['total_amount'] ?? 0);
            $puExpenses   = (float)($puRow['parts_total'] ?? 0)
                          + (float)($puRow['manual_expenses'] ?? 0)
                          + (float)($puRow['outsource_expenses'] ?? 0)
                          + (float)($puRow['other_expenses'] ?? 0)
                          + (float)($puRow['carwash_total'] ?? 0);
            $puNet        = max(0.0, $puTotal - $puExpenses);
            $puJoId       = (int)($puRow['id'] ?? 0);
            $puPayMethods = $unitJoDetailMap[$puJoId]['payment_methods'] ?? [];
            // Also include the JO-level payment method if no detail entries
            if (empty($puPayMethods)) {
                $rawMethods = array_filter(explode(',', (string)($puRow['jo_payment_methods'] ?? '')));
                foreach ($rawMethods as $rpm) {
                    $rpm = trim($rpm);
                    if ($rpm !== '') $puPayMethods[$rpm] = ($puPayMethods[$rpm] ?? 0);
                }
            }
            $puPayStr = implode(', ', array_keys($puPayMethods));
            if ($puPayStr === '') {
                $puPayStr = ucfirst((string)($puRow['jo_payment_methods'] ?? '—'));
            }
            $puSummaryTotalAmount   += $puTotal;
            $puSummaryTotalExpenses += $puExpenses;
            $puSummaryTotalNet      += $puNet;
            echo "<tr>";
            echo "<td>" . escape($puRow['job_order_number'] ?? '—') . "</td>";
            echo "<td>" . escape($puRow['plate_number'] ?? 'N/A') . "</td>";
            echo "<td>" . escape($puRow['customer_name'] ?? '—') . "</td>";
            echo "<td class='text-right'>" . number_format($puTotal, 2) . "</td>";
            echo "<td class='text-right'>" . number_format($puExpenses, 2) . "</td>";
            echo "<td>" . escape($puPayStr) . "</td>";
            echo "<td class='text-right'>" . number_format($puNet, 2) . "</td>";
            echo "</tr>";
        }
        echo "<tr style='background:#eef2f7;font-weight:700;'>";
        echo "<td colspan='3'>Total</td>";
        echo "<td class='text-right'>" . number_format($puSummaryTotalAmount, 2) . "</td>";
        echo "<td class='text-right'>" . number_format($puSummaryTotalExpenses, 2) . "</td>";
        echo "<td></td>";
        echo "<td class='text-right'>" . number_format($puSummaryTotalNet, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // ── 11. Per Unit Product & Expense Detail ────────────────────────────
    echo "<div class='section'>11. Per Unit Product &amp; Expense Detail</div>";
    if (empty($unitExpenseReport)) {
        echo "<table><tr><td>No data for this range</td></tr></table>";
    } else {
        $grandProductExpenseTotal = 0.0;
        foreach ($unitExpenseReport as $pdRow) {
            $pdJoId      = (int)($pdRow['id'] ?? 0);
            $pdJoNum     = escape($pdRow['job_order_number'] ?? '—');
            $pdPlate     = escape($pdRow['plate_number'] ?? 'N/A');
            $pdCustomer  = escape($pdRow['customer_name'] ?? '—');
            // JO header row
            echo "<table style='margin-bottom:4px;'>";
            echo "<tr style='background:#d1d5db;font-weight:700;'>";
            echo "<td colspan='4'>" . $pdJoNum . " &nbsp;|&nbsp; " . $pdPlate . " &nbsp;|&nbsp; " . $pdCustomer . "</td>";
            echo "</tr>";
            echo "<tr style='background:#eef2f7;'>";
            echo "<th>Type</th><th>Description</th><th class='text-right'>Qty</th><th class='text-right'>Cost (PHP)</th>";
            echo "</tr>";

            $joExpenseTotal = 0.0;

            // Products (non-custom only — product_id set)
            $pdProducts = $unitProductMap[$pdJoId] ?? [];
            foreach ($pdProducts as $pdProd) {
                if ((int)($pdProd['product_id'] ?? 0) <= 0) continue;
                $prodCost = (float)($pdProd['cost_total'] ?? 0);
                $joExpenseTotal += $prodCost;
                echo "<tr>";
                echo "<td>Product</td>";
                echo "<td>" . escape($pdProd['product_name'] ?? '—') . "</td>";
                echo "<td class='text-right'>" . (int)($pdProd['quantity'] ?? 1) . "</td>";
                echo "<td class='text-right'>" . number_format($prodCost, 2) . "</td>";
                echo "</tr>";
            }

            // Linked manual expenses
            $pdEntries = $unitJoDetailMap[$pdJoId]['entries'] ?? [];
            foreach ($pdEntries as $pdEntry) {
                $entryAmt = (float)($pdEntry['amount'] ?? 0);
                $joExpenseTotal += $entryAmt;
                echo "<tr>";
                echo "<td>" . escape(ucfirst($pdEntry['category'] ?? 'Expense')) . "</td>";
                echo "<td>" . escape($pdEntry['description'] ?? '—') . "</td>";
                echo "<td class='text-right'>—</td>";
                echo "<td class='text-right'>" . number_format($entryAmt, 2) . "</td>";
                echo "</tr>";
            }

            // JO subtotal row
            $grandProductExpenseTotal += $joExpenseTotal;
            echo "<tr style='background:#f3f6fb;font-weight:700;'>";
            echo "<td colspan='3'>Total Expenses for " . $pdJoNum . "</td>";
            echo "<td class='text-right'>" . number_format($joExpenseTotal, 2) . "</td>";
            echo "</tr>";
            echo "</table>";
        }

        // Grand total across all JOs
        echo "<table style='margin-top:6px;'>";
        echo "<tr style='background:#eef2f7;font-weight:700;'>";
        echo "<td colspan='3'>Grand Total Expenses (All JOs)</td>";
        echo "<td class='text-right'>" . number_format($grandProductExpenseTotal, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    echo "</body></html>";
    exit;
}

include __DIR__ . '/../partials/header.php';
?>

<style>
.report-table-wrap {
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    -ms-overflow-style: auto;
}

.report-table-wrap::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.report-table-wrap::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.35);
    border-radius: 10px;
}

.report-scroll-panel {
    max-height: 260px;
    overflow-y: auto;
    overflow-x: auto;
    scrollbar-width: thin;
    -ms-overflow-style: auto;
    -webkit-overflow-scrolling: touch;
}

.report-scroll-panel::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.report-scroll-panel::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.35);
    border-radius: 10px;
}

.report-table {
    margin-bottom: 0;
    min-width: 420px;
}

.report-table th,
.report-table td {
    padding: 7px 9px;
    vertical-align: middle;
}

.report-table th {
    white-space: nowrap;
}

.report-table td {
    white-space: normal;
}

.report-table th.text-end,
.report-table td.text-end {
    white-space: nowrap;
    min-width: 88px;
}

.report-single-line {
    min-width: 760px;
}

.report-log-scroll {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: #8a8f98 #eef0f3;
}

.report-log-scroll::-webkit-scrollbar {
    height: 8px;
}

.report-log-scroll::-webkit-scrollbar-thumb {
    background: #8a8f98;
    border-radius: 8px;
}

.report-log-scroll::-webkit-scrollbar-track {
    background: #eef0f3;
}

.report-single-line th,
.report-single-line td {
    white-space: nowrap;
}

.report-single-line .report-description-cell {
    max-width: 360px;
    white-space: normal;
    overflow-wrap: anywhere;
    line-height: 1.35;
}

.report-action-cell {
    width: 56px;
    text-align: center;
    white-space: nowrap;
}

.report-actions-menu {
    display: inline-flex;
    position: relative;
}

.report-actions-menu .action-menu-btn {
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

.report-actions-menu .action-menu-btn:hover,
.report-actions-menu .action-menu-btn:focus,
.report-actions-menu .action-menu-btn.show {
    background: #6c757d;
    border-color: #6c757d;
    color: #fff;
    box-shadow: none;
}

.report-actions-menu .dropdown-toggle::after {
    display: none;
}

.report-actions-menu .dropdown-menu {
    min-width: 120px;
    padding: 4px 0;
    border: 0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1080;
}

.report-actions-menu .dropdown-item {
    padding: 8px 14px;
    font-size: 13px;
}

.purchased-products-table {
    width: 100%;
    min-width: 1000px;
    table-layout: auto;
}

.purchased-products-scroll {
    width: 100%;
    max-width: 100%;
    overflow-x: auto !important;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
}

.purchased-products-modal .modal-content {
    height: auto;
    max-height: calc(100vh - 2rem);
}

.purchased-products-modal .modal-body {
    flex: 0 1 auto;
    min-height: 0;
    overflow-y: auto;
}

.purchased-products-scroll::-webkit-scrollbar {
    height: 10px;
}

.purchased-products-scroll::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.4);
    border-radius: 10px;
}

.purchased-products-table th,
.purchased-products-table td {
    white-space: nowrap;
    vertical-align: middle;
}

.purchased-products-table .purchased-date {
    line-height: 1.25;
}

.purchased-products-table .purchased-cost,
.purchased-products-table .purchased-total {
    white-space: nowrap;
    min-width: 110px;
}

@media (max-width: 768px) {
    .report-table {
        min-width: 400px;
    }

    .report-table th,
    .report-table td {
        font-size: 11px;
        padding: 7px 8px;
    }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Reports</h4>
        <p class="text-muted small mb-0">View business performance, payment status, and service statistics.</p>
    </div>
    <form class="row gx-2 gy-2 align-items-center" method="GET" action="">
        <div class="col-auto">
            <input type="date" class="form-control" name="from" value="<?php echo escape($dateFrom); ?>">
        </div>
        <div class="col-auto">
            <input type="date" class="form-control" name="to" value="<?php echo escape($dateTo); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
        <div class="col-auto">
            <button type="submit" name="export" value="excel" class="btn btn-success">Export Excel</button>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">Paid / Partial / Pending</p>
                <h4 class="mb-0"><?php echo number_format($paidIncome, 2); ?>/<?php echo number_format($partialIncome, 2); ?>/<?php echo number_format($pendingIncome, 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-muted mb-1 small">(Outside) Income / Expenses</p>
                <h4 class="mb-0"><span class="text-success">₱<?php echo number_format($totalManualIncome, 2); ?></span> / <span class="text-danger">₱<?php echo number_format($totalExpenses, 2); ?></span></h4>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-12">
        <div class="card h-100 border-secondary net-income-card" data-bs-toggle="modal" data-bs-target="#netIncomeTransactionsModal" role="button" aria-label="View net income transactions">
            <div class="card-body position-relative">
                <div>
                    <p class="text-dark mb-1 small">Net Income</p>
                    <h3 class="mb-0 text-dark">₱ <?php echo number_format($netIncome, 2); ?></h3>
                </div>
                <small class="text-dark">Period: <?php echo escape($dateFrom); ?> — <?php echo escape($dateTo); ?> | JO: <?php echo number_format($totalOrders); ?></small>
            </div>
        </div>
    </div>
</div>

<style>
.net-income-card {
    background: #d9d9d9;
    cursor: pointer;
    transition: background 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.net-income-card:hover,
.net-income-card:focus,
.net-income-card:active {
    background: #1d1d1d;
    box-shadow: 0 0.5rem 1rem rgba(17, 24, 39, 0.15);
    transform: translateY(-1px);
}

.net-income-card:hover .card-body p,
.net-income-card:hover .card-body small,
.net-income-card:hover .card-body h3,
.net-income-card:focus .card-body p,
.net-income-card:focus .card-body small,
.net-income-card:focus .card-body h3,
.net-income-card:active .card-body p,
.net-income-card:active .card-body small,
.net-income-card:active .card-body h3 {
    color: #fff !important;
}

.expense-title {
    color: #000 !important;
}
</style>

<div class="modal fade" id="netIncomeTransactionsModal" tabindex="-1" aria-labelledby="netIncomeTransactionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="netIncomeTransactionsModalLabel">Net Income Transactions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($netIncomeTransactions)): ?>
                    <p class="text-muted mb-0 text-center py-3">No linked transactions for this period.</p>
                <?php else: ?>
                    <div class="table-responsive report-log-scroll">
                        <table class="table table-sm align-middle mb-0 report-single-line">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($netIncomeTransactions as $tx): ?>
                                    <tr>
                                        <td><?php echo escape($tx['date']); ?></td>
                                        <td>
                                            <?php if ($tx['direction'] === 'out'): ?>
                                                <span class="badge bg-danger-subtle text-danger"><?php echo escape($tx['type']); ?></span>
                                            <?php elseif ($tx['type'] === 'Income'): ?>
                                                <span class="badge bg-success-subtle text-success">Income</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary"><?php echo escape($tx['type']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo escape($tx['reference']); ?></td>
                                        <td class="report-description-cell"><?php echo escape($tx['description']); ?></td>
                                        <td class="text-end fw-bold <?php echo $tx['direction'] === 'out' ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $tx['direction'] === 'out' ? '- ' : '+ '; ?>₱ <?php echo number_format((float)$tx['amount'], 2); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Add Income</h5>
                <p class="text-muted small mb-0">Manually record additional income (outside of job orders).</p>
            </div>
        </div>
        <form method="POST" class="row g-2 align-items-end">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add_income">
            <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
            <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="income_date" value="<?php echo escape($dateTo); ?>" required>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control" name="income_amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="income_payment_method">
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Swipe/Card">Swipe/Card</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-8">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="income_notes" maxlength="180">
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-success w-100">Add Income</button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($filteredManualIncome)): ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Manual Income Log</h5>
                <p class="text-muted small mb-0">Manually added income entries for the selected period.</p>
            </div>
        </div>
        <div class="table-responsive report-log-scroll">
            <table class="table table-sm align-middle mb-0 report-single-line">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Payment Method</th>
                        <th>Entered By</th>
                        <th class="text-end">Amount</th>
                        <?php if ($canManageExpenses): ?>
                            <th class="report-action-cell" aria-label="Actions"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredManualIncome as $incRow): ?>
                        <tr>
                            <td><?php echo escape($incRow['income_date'] ?? '—'); ?></td>
                            <td class="report-description-cell"><?php echo escape($incRow['description'] ?? '—'); ?></td>
                            <td><?php echo escape($incRow['payment_method'] ?? 'Cash'); ?></td>
                            <td><?php echo escape($incRow['created_by'] ?? 'System'); ?></td>
                            <td class="text-end text-success fw-bold">₱ <?php echo number_format((float)($incRow['amount'] ?? 0), 2); ?></td>
                            <?php if ($canManageExpenses): ?>
                                <td class="report-action-cell">
                                    <div class="dropdown report-actions-menu">
                                        <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="manualIncomeActions<?php echo escape($incRow['id'] ?? ''); ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Manual income actions">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="manualIncomeActions<?php echo escape($incRow['id'] ?? ''); ?>">
                                            <li>
                                                <form method="POST" onsubmit="return confirmIncomeDelete(this);">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="delete_income">
                                                    <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
                                                    <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
                                                    <input type="hidden" name="income_id" value="<?php echo escape($incRow['id'] ?? ''); ?>">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="fw-bold">Total Manual Income</td>
                        <td class="text-end fw-bold text-success">₱ <?php echo number_format($totalManualIncome, 2); ?></td>
                        <?php if ($canManageExpenses): ?><td></td><?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0 expense-title">Add Expense</h5>
                <p class="text-muted small mb-0">Record expenses separately for transparent net-income reporting.</p>
            </div>
        </div>
        <form method="POST" class="row g-2 align-items-end">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="add_expense">
            <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
            <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="expense_date" value="<?php echo escape($dateTo); ?>" required>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Category</label>
                <select class="form-select" name="expense_category">
                    <option value="Outsource">Outsource</option>
                    <option value="Carwash">Carwash</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">JO</label>
                <select class="form-select" name="job_order_id" onchange="this.form.elements.plate_number.value = this.options[this.selectedIndex].dataset.plate || '';">
                    <option value="">-- Select JO --</option>
                    <?php foreach ($jobOrderOptionsForExpense as $jobOrderOption): ?>
                        <?php
                            $optionPlate = trim((string)($jobOrderOption['plate_number'] ?? ''));
                            $optionText = (string)($jobOrderOption['job_order_number'] ?? 'JO #' . (int)($jobOrderOption['id'] ?? 0));
                        ?>
                        <option value="<?php echo escape((string)($jobOrderOption['id'] ?? '')); ?>" data-plate="<?php echo escape(strtoupper($optionPlate)); ?>"><?php echo escape($optionText); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Plate Number</label>
                <input type="text" class="form-control" name="plate_number" maxlength="20" placeholder="ABC 1234">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Amount</label>
                <input type="number" class="form-control" name="expense_amount" min="0.01" step="0.01" required>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="payment_method">
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Swipe/Card">Swipe/Card</option>
                </select>
            </div>
            <div class="col-lg-4 col-md-6">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" name="expense_notes" maxlength="180">
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-dark w-100">Add Expense</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Expenses Transparency Log</h5>
                <p class="text-muted small mb-0">All recorded expenses for the selected report period.</p>
            </div>
            <?php if (!empty($filteredExpenses)): ?>
                <div class="text-end small fw-bold text-nowrap">
                    Cash: ₱ <?php echo number_format($expensePaymentTotals['cash'], 2); ?>
                    &nbsp; | &nbsp;
                    GCash: ₱ <?php echo number_format($expensePaymentTotals['gcash'], 2); ?>
                    &nbsp; | &nbsp;
                    Bank: ₱ <?php echo number_format($expensePaymentTotals['bank_transfer'], 2); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="table-responsive report-log-scroll">
            <table class="table table-sm align-middle mb-0 report-single-line">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>JO ID</th>
                        <th>Plate Number</th>
                        <th>Description</th>
                        <th>Entered By</th>
                        <th class="text-end">Amount</th>
                        <th>Payment Method</th>
                        <?php if ($canManageExpenses): ?>
                            <th class="report-action-cell" aria-label="Actions"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build unified list sorted latest first for the transparency log
                    $allTransparencyRows = [];
                    foreach ($filteredExpenses as $expRow) {
                        $allTransparencyRows[] = ['type' => 'manual', 'sort_key' => ($expRow['expense_date'] ?? '') . ' ' . ($expRow['created_at'] ?? ''), 'data' => $expRow];
                    }
                    foreach ($productCostExpenses as $pcRow) {
                        $allTransparencyRows[] = ['type' => 'product_cost', 'sort_key' => ($pcRow['expense_date'] ?? '') . ' ' . ($pcRow['expense_at'] ?? ''), 'data' => $pcRow];
                    }
                    usort($allTransparencyRows, fn($a, $b) => strcmp($b['sort_key'], $a['sort_key']));
                    ?>
                    <?php if (empty($allTransparencyRows)): ?>
                        <tr><td colspan="<?php echo $canManageExpenses ? '9' : '8'; ?>" class="text-center text-muted">No expenses recorded for this date range</td></tr>
                    <?php else: ?>
                        <?php foreach ($allTransparencyRows as $tRow): ?>
                            <?php if ($tRow['type'] === 'manual'): ?>
                                <?php
                                    $expenseRow = $tRow['data'];
                                    $expenseCategory = (string)($expenseRow['category'] ?? 'General');
                                    $expensePlate = trim((string)($expenseRow['plate_number'] ?? ''));
                                    $expenseJoId = isset($expenseRow['job_order_id']) ? (int)$expenseRow['job_order_id'] : 0;
                                    $expenseJoNumber = $expenseJoId > 0
                                        ? ($jobOrderNumberMap[$expenseJoId] ?? 'JO' . str_pad((string)$expenseJoId, 3, '0', STR_PAD_LEFT))
                                        : 'N/A';
                                ?>
                                <tr>
                                    <td><?php echo escape($expenseRow['expense_date'] ?? '—'); ?></td>
                                    <td><?php echo escape($expenseCategory !== '' ? $expenseCategory : 'General'); ?></td>
                                    <td><?php echo escape($expenseJoNumber); ?></td>
                                    <td><?php echo escape($expensePlate !== '' ? strtoupper($expensePlate) : 'N/A'); ?></td>
                                    <td class="report-description-cell"><?php echo escape($expenseRow['description'] ?? '—'); ?></td>
                                    <td><?php echo escape($expenseRow['created_by'] ?? 'System'); ?></td>
                                    <td class="text-end">₱ <?php echo number_format((float)($expenseRow['amount'] ?? 0), 2); ?></td>
                                    <td><?php echo escape($expenseRow['payment_method'] ?? 'Cash'); ?></td>
                                    <?php if ($canManageExpenses): ?>
                                        <td class="report-action-cell">
                                            <div class="dropdown report-actions-menu">
                                                <button class="btn btn-sm action-menu-btn dropdown-toggle" type="button" id="expenseActions<?php echo escape($expenseRow['id'] ?? ''); ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Expense actions">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="expenseActions<?php echo escape($expenseRow['id'] ?? ''); ?>">
                                                    <li>
                                                        <form method="POST" onsubmit="return confirmExpenseDelete(this);">
                                                            <?php echo csrfField(); ?>
                                                            <input type="hidden" name="action" value="delete_expense">
                                                            <input type="hidden" name="from" value="<?php echo escape($dateFrom); ?>">
                                                            <input type="hidden" name="to" value="<?php echo escape($dateTo); ?>">
                                                            <input type="hidden" name="expense_id" value="<?php echo escape($expenseRow['id'] ?? ''); ?>">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash me-2"></i>Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php else: ?>
                                <?php
                                    $pcExpRow = $tRow['data'];
                                    $pcPlate  = $jobOrderPlateMap[$pcExpRow['job_order_number'] ?? ''] ?? '';
                                ?>
                                <tr class="table-secondary">
                                    <td><?php echo escape($pcExpRow['expense_date'] ?? '—'); ?></td>
                                    <td><span class="badge bg-secondary">Product Cost</span></td>
                                    <td><?php echo escape($pcExpRow['job_order_number'] ?? '—'); ?></td>
                                    <td><?php echo $pcPlate !== '' ? escape($pcPlate) : 'N/A'; ?></td>
                                    <td class="report-description-cell"><?php echo escape($pcExpRow['products'] ?? $pcExpRow['job_order_number'] ?? '—'); ?></td>
                                    <td><?php echo escape($pcExpRow['processed_by'] ?? 'System'); ?></td>
                                    <td class="text-end">₱ <?php echo number_format((float)($pcExpRow['amount'] ?? 0), 2); ?></td>
                                    <td>Stocks</td>
                                    <?php if ($canManageExpenses): ?><td></td><?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="card-title mb-0">Per Unit Report</h5>
                <p class="text-muted small mb-0">Paid and released JOs only for the selected period.</p>
            </div>
            <?php if (!empty($unitExpenseReport)): ?>
                <div class="text-end small fw-bold text-nowrap">
                    Cash: ₱ <?php echo number_format($unitPaymentTotals['cash'], 2); ?>
                    &nbsp; | &nbsp;
                    GCash: ₱ <?php echo number_format($unitPaymentTotals['gcash'], 2); ?>
                    &nbsp; | &nbsp;
                    Card: ₱ <?php echo number_format($unitPaymentTotals['card'], 2); ?>
                    &nbsp; | &nbsp;
                    Bank: ₱ <?php echo number_format($unitPaymentTotals['bank_transfer'], 2); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">JO ID</th>
                        <th class="text-nowrap">Unit</th>
                        <th class="text-nowrap">Customer</th>
                        <th class="text-nowrap">Total</th>
                        <th class="text-nowrap">Expenses</th>
                        <th class="text-nowrap">Payment Method</th>
                        <th class="text-nowrap">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($unitExpenseReport)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No unit reports found for this date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($unitExpenseReport as $unitReportRow): ?>
                            <?php
                                $unitParts = (float)($unitReportRow['parts_total'] ?? 0);
                                $unitOutsource = (float)($unitReportRow['outsource_expenses'] ?? 0);
                                $unitOther = (float)($unitReportRow['other_expenses'] ?? 0);
                                $unitCarwash = (float)($unitReportRow['carwash_total'] ?? 0);
                                $unitExpenseTotal = (float)($unitReportRow['manual_expenses'] ?? 0) + $unitOutsource + $unitOther + $unitCarwash;
                                $unitNet = max(0.0, (float)($unitReportRow['total_amount'] ?? 0) - $unitParts - $unitExpenseTotal);
                                $detailSummary = $unitJoDetailMap[(int)($unitReportRow['id'] ?? 0)] ?? [];
                                $unitPaymentMethods = array_filter(array_map(function ($method) {
                                    return ucwords(str_replace('_', ' ', trim($method)));
                                }, explode(',', (string)($unitReportRow['jo_payment_methods'] ?? ''))));
                            ?>
                            <tr class="cursor-pointer" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#jo-detail-modal-<?php echo (int)($unitReportRow['id'] ?? 0); ?>" aria-label="View details for <?php echo escape($unitReportRow['job_order_number'] ?? 'job order'); ?>" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); this.click(); }">
                                <td><?php echo escape($unitReportRow['job_order_number'] ?? 'N/A'); ?></td>
                                <td><?php echo escape($unitReportRow['plate_number'] ?? 'N/A'); ?></td>
                                <td><?php echo escape($unitReportRow['customer_name'] ?? 'Customer'); ?></td>
                                <td>₱ <?php echo number_format((float)($unitReportRow['total_amount'] ?? 0), 2); ?></td>
                                <td>₱ <?php echo number_format($unitParts + $unitExpenseTotal, 2); ?></td>
                                <td><?php echo escape(!empty($unitPaymentMethods) ? implode(', ', $unitPaymentMethods) : 'N/A'); ?></td>
                                <td class="fw-bold <?php echo $unitNet >= 0 ? 'text-success' : 'text-danger'; ?>">₱ <?php echo number_format($unitNet, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($unitExpenseReport)): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3">Total</td>
                            <td>₱ <?php echo number_format($unitReportTotalAmount, 2); ?></td>
                            <td>₱ <?php echo number_format($unitReportTotalExpenses, 2); ?></td>
                            <td></td>
                            <td class="<?php echo $unitReportTotalNet >= 0 ? 'text-success' : 'text-danger'; ?>">₱ <?php echo number_format($unitReportTotalNet, 2); ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
        <?php if (!empty($unitExpenseReport)): ?>
            <?php foreach ($unitExpenseReport as $unitReportRow): ?>
                <?php
                    $detailSummary = $unitJoDetailMap[(int)($unitReportRow['id'] ?? 0)] ?? [];
                    $unitProducts = array_values(array_filter(
                        $unitProductMap[(int)($unitReportRow['id'] ?? 0)] ?? [],
                        function ($unitProduct) {
                            return (int)($unitProduct['product_id'] ?? 0) > 0;
                        }
                    ));
                    $unitOutsourceEntries = array_values(array_filter($detailSummary['entries'] ?? [], function ($detailEntry) {
                        return strpos(strtolower((string)($detailEntry['category'] ?? '')), 'outsource') !== false;
                    }));
                    $unitCarwashEntries = array_values(array_filter($detailSummary['entries'] ?? [], function ($detailEntry) {
                        return strpos(strtolower((string)($detailEntry['category'] ?? '')), 'carwash') !== false;
                    }));
                    $unitOtherEntries = array_values(array_filter($detailSummary['entries'] ?? [], function ($detailEntry) {
                        return strpos(strtolower((string)($detailEntry['category'] ?? '')), 'other') !== false;
                    }));
                ?>
                <div class="modal fade purchased-products-modal" id="jo-detail-modal-<?php echo (int)($unitReportRow['id'] ?? 0); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo escape($unitReportRow['job_order_number'] ?? 'JO'); ?> · <?php echo escape($unitReportRow['plate_number'] ?? 'N/A'); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="small text-muted mb-2">Purchased products</div>
                                <?php if (!empty($unitProducts) || !empty($unitOutsourceEntries) || !empty($unitCarwashEntries) || !empty($unitOtherEntries)): ?>
                                    <div class="table-responsive purchased-products-scroll">
                                        <table class="table table-sm mb-3 purchased-products-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Date &amp; Time</th>
                                                    <th>Entered By</th>
                                                    <th>Source</th>
                                                    <th>Qty</th>
                                                    <th class="text-end">Cost Price</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($unitProducts as $unitProduct): ?>
                                                    <?php $productCreatedAt = $unitProduct['created_at'] ?? null; ?>
                                                    <tr>
                                                        <td><?php echo escape($unitProduct['product_name'] ?? 'Product'); ?></td>
                                                        <td class="purchased-date"><?php echo $productCreatedAt ? date('Y-m-d', strtotime((string)$productCreatedAt)) . '<br><small class="text-muted">' . escape(date('h:i A', strtotime((string)$productCreatedAt))) . '</small>' : '—'; ?></td>
                                                        <td><?php echo escape($unitProduct['entered_by'] ?? 'System'); ?></td>
                                                        <td>Stocks</td>
                                                        <td><?php echo (int)($unitProduct['quantity'] ?? 0); ?></td>
                                                        <td class="text-end purchased-cost">₱ <?php echo number_format((float)($unitProduct['cost_price'] ?? 0), 2); ?></td>
                                                        <td class="text-end purchased-total">₱ <?php echo number_format((float)($unitProduct['cost_total'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php foreach ($unitOutsourceEntries as $unitOutsourceEntry): ?>
                                                    <?php $outsourceCreatedAt = $unitOutsourceEntry['created_at'] ?? null; ?>
                                                    <tr>
                                                        <td><?php echo escape($unitOutsourceEntry['description'] ?? 'Outsource'); ?></td>
                                                        <td class="purchased-date"><?php echo $outsourceCreatedAt ? date('Y-m-d', strtotime((string)$outsourceCreatedAt)) . '<br><small class="text-muted">' . escape(date('h:i A', strtotime((string)$outsourceCreatedAt))) . '</small>' : '—'; ?></td>
                                                        <td><?php echo escape($unitOutsourceEntry['entered_by'] ?? 'System'); ?></td>
                                                        <td>Outsource</td>
                                                        <td>-</td>
                                                        <td class="text-end purchased-cost">₱ <?php echo number_format((float)($unitOutsourceEntry['amount'] ?? 0), 2); ?></td>
                                                        <td class="text-end purchased-total">₱ <?php echo number_format((float)($unitOutsourceEntry['amount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php foreach ($unitCarwashEntries as $unitCarwashEntry): ?>
                                                    <?php $carwashCreatedAt = $unitCarwashEntry['created_at'] ?? null; ?>
                                                    <tr>
                                                        <td><?php echo escape($unitCarwashEntry['description'] ?? 'Carwash'); ?></td>
                                                        <td class="purchased-date"><?php echo $carwashCreatedAt ? date('Y-m-d', strtotime((string)$carwashCreatedAt)) . '<br><small class="text-muted">' . escape(date('h:i A', strtotime((string)$carwashCreatedAt))) . '</small>' : '—'; ?></td>
                                                        <td><?php echo escape($unitCarwashEntry['entered_by'] ?? 'System'); ?></td>
                                                        <td>Carwash</td>
                                                        <td>-</td>
                                                        <td class="text-end purchased-cost">₱ <?php echo number_format((float)($unitCarwashEntry['amount'] ?? 0), 2); ?></td>
                                                        <td class="text-end purchased-total">₱ <?php echo number_format((float)($unitCarwashEntry['amount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php foreach ($unitOtherEntries as $unitOtherEntry): ?>
                                                    <?php $otherCreatedAt = $unitOtherEntry['created_at'] ?? null; ?>
                                                    <tr>
                                                        <td><?php echo escape($unitOtherEntry['description'] ?? 'Other'); ?></td>
                                                        <td class="purchased-date"><?php echo $otherCreatedAt ? date('Y-m-d', strtotime((string)$otherCreatedAt)) . '<br><small class="text-muted">' . escape(date('h:i A', strtotime((string)$otherCreatedAt))) . '</small>' : '—'; ?></td>
                                                        <td><?php echo escape($unitOtherEntry['entered_by'] ?? 'System'); ?></td>
                                                        <td>Others</td>
                                                        <td>-</td>
                                                        <td class="text-end purchased-cost">₱ <?php echo number_format((float)($unitOtherEntry['amount'] ?? 0), 2); ?></td>
                                                        <td class="text-end purchased-total">₱ <?php echo number_format((float)($unitOtherEntry['amount'] ?? 0), 2); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small mb-3">No stocks, outsource, carwash, or other items recorded for this JO.</div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php $perUnitReportHtml = ob_get_clean(); ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="mb-2">
            <div style="font-size:14px;font-weight:600;color:#000;">Income Trend</div>
            <div style="font-size:12px;color:#666;margin-top:2px;">Daily income between selected dates</div>
        </div>
        <div style="height:280px;position:relative;">
            <canvas id="incomeTrendChart"></canvas>
        </div>
    </div>
</div>

<!-- Technician Performance Leaderboard -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h5 class="card-title mb-0">Top Technicians</h5>
                <p class="text-muted small mb-0">Performance ranking for the selected period</p>
            </div>
        </div>
        <div class="d-flex gap-1 mb-3">
            <button class="btn btn-sm btn-dark rpt-tech-tab active" data-tab="ranking" onclick="switchRptTechTab('ranking')">Ranking</button>
            <button class="btn btn-sm btn-outline-secondary rpt-tech-tab" data-tab="history" onclick="switchRptTechTab('history')">Points History</button>
        </div>
        <!-- Ranking Tab -->
        <div id="rptTechRanking">
        <?php if (empty($techPerformance)): ?>
            <p class="text-muted text-center py-3">No technician data for this date range.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Technician</th>
                        <th class="text-center">Lead</th>
                        <th class="text-center">Assist</th>
                        <th class="text-center">Speed</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-center">Quality</th>
                        <th class="text-center">Penalty</th>
                        <th class="text-end"><strong>Score</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($techPerformance as $rank => $tp): ?>
                    <tr>
                        <td class="text-muted"><?php echo $rank + 1; ?></td>
                        <td>
                            <div>
                                <strong><?php echo escape($tp['full_name']); ?></strong>
                                <div class="text-muted" style="font-size:10px;"><?php echo $tp['total_jos']; ?> JOs · <?php echo $tp['total_hours']; ?>h</div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-dark"><?php echo $tp['lead_completed']; ?></span></td>
                        <td class="text-center"><span class="badge bg-secondary"><?php echo $tp['assist_completed']; ?></span></td>
                        <td class="text-center"><span class="text-success">+<?php echo $tp['speed_points']; ?></span></td>
                        <td class="text-end"><span class="text-success">+<?php echo number_format($tp['revenue_points'], 1); ?></span></td>
                        <td class="text-center"><span class="text-success">+<?php echo $tp['clean_points']; ?></span></td>
                        <td class="text-center">
                            <?php 
                                $penalty = $tp['return_penalty'] + $tp['removed_penalty'];
                                if ($penalty < 0): ?>
                                <span class="text-danger"><?php echo $penalty; ?></span>
                            <?php else: ?>
                                <span class="text-muted">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <strong style="font-size:14px;"><?php echo number_format($tp['total_score'], 1); ?></strong>
                            <span class="text-muted" style="font-size:10px;">pts</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        </div>
        <!-- Points History Tab -->
        <div id="rptTechHistory" style="display:none;">
        <?php
        $pointsHistory = [];
        try {
            $db = Database::getInstance();
            $phParams = [];
            $phDateCond = '';
            if ($dateFrom && $dateTo) {
                $phDateCond = " AND DATE(tp.created_at) BETWEEN ? AND ?";
                $phParams = [$dateFrom, $dateTo, $dateFrom, $dateTo];
            }
            $pointsHistory = $db->fetchAll(
                "SELECT history.staff_id, history.full_name, history.reason, history.points,
                        history.created_at, history.job_order_number
                 FROM (
                     SELECT s.staff_id, s.full_name, tp.reason, tp.points, tp.created_at,
                            jo.job_order_number
                     FROM technician_points tp
                     INNER JOIN staff s ON s.id = tp.technician_id
                     LEFT JOIN job_orders jo ON jo.id = tp.job_order_id
                     WHERE 1=1 {$phDateCond}

                     UNION ALL

                     SELECT s.staff_id, s.full_name, 'Revenue Bonus' AS reason,
                            CASE WHEN COALESCE(jot.is_assist, 0) = 1
                                 THEN ROUND(COALESCE(jo.total_amount, 0) / 1000 * 0.5, 1)
                                 ELSE ROUND(COALESCE(jo.total_amount, 0) / 1000, 1)
                            END AS points,
                            COALESCE(jo.completed_at, jo.updated_at, jo.created_at) AS created_at,
                            jo.job_order_number
                     FROM job_order_technicians jot
                     INNER JOIN staff s ON s.id = jot.technician_id
                     INNER JOIN job_orders jo ON jo.id = jot.job_order_id
                     WHERE jo.status IN ('completed', 'released')
                       AND COALESCE(jo.total_amount, 0) > 0
                       AND NOT EXISTS (
                           SELECT 1
                           FROM technician_points existing_tp
                           WHERE existing_tp.technician_id = jot.technician_id
                             AND existing_tp.job_order_id = jot.job_order_id
                             AND existing_tp.reason = 'Revenue Bonus'
                       )
                       AND DATE(COALESCE(jo.completed_at, jo.updated_at, jo.created_at)) BETWEEN ? AND ?
                 ) AS history
                 ORDER BY history.created_at DESC",
                $phParams
            );
        } catch (Exception $e) { $pointsHistory = []; }
        ?>
        <?php if (empty($pointsHistory)): ?>
            <p class="text-muted text-center py-3">No points history for this date range.</p>
        <?php else: ?>
        <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
            <table class="table table-sm table-hover align-middle mb-0" style="font-size:12px;">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Technician</th>
                        <th>JO #</th>
                        <th>Reason</th>
                        <th class="text-end">Points</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pointsHistory as $ph): ?>
                    <tr>
                        <td class="text-muted"><?php echo escape($ph['staff_id'] ?? ''); ?></td>
                        <td><strong><?php echo escape($ph['full_name']); ?></strong></td>
                        <td><?php echo escape($ph['job_order_number'] ?? '—'); ?></td>
                        <td><?php echo escape($ph['reason']); ?></td>
                        <td class="text-end">
                            <?php if ((float)$ph['points'] > 0): ?>
                                <span class="text-success fw-bold">+<?php echo number_format((float)$ph['points'], 1); ?></span>
                            <?php else: ?>
                                <span class="text-danger fw-bold"><?php echo number_format((float)$ph['points'], 1); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y h:i A', strtotime($ph['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        </div>
    </div>
</div>
<script>
function switchRptTechTab(tab) {
    document.getElementById('rptTechRanking').style.display = tab === 'ranking' ? '' : 'none';
    document.getElementById('rptTechHistory').style.display = tab === 'history' ? '' : 'none';
    document.querySelectorAll('.rpt-tech-tab').forEach(b => {
        b.className = b.dataset.tab === tab ? 'btn btn-sm btn-dark rpt-tech-tab active' : 'btn btn-sm btn-outline-secondary rpt-tech-tab';
    });
}
</script>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Service Type Revenue</h5>
                <div class="table-responsive report-scroll-panel report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Service Type</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($serviceStats)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No data for this range</td></tr>
                            <?php else: ?>
                                <?php foreach ($serviceStats as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['service_name'] ?? $row['service_type'] ?? 'Unknown'); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Payment Method Breakdown</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentMethods)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No paid orders for this range</td></tr>
                            <?php else: ?>
                                <?php foreach ($paymentMethods as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['payment_method'] ?? 'Unknown'); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4" id="recent-activity">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Job Order Status Summary</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Count</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($statusSummary)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No status summary available</td></tr>
                            <?php else: ?>
                                <?php foreach ($statusSummary as $row): ?>
                                    <tr>
                                        <td><?php echo escape(ucwords(str_replace('_', ' ', $row['status']))); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Top Customers</h5>
                <div class="table-responsive report-scroll-panel report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">Visits</th>
                                <th class="text-end">Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topCustomers)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No customer activity yet</td></tr>
                            <?php else: ?>
                                <?php foreach ($topCustomers as $row): ?>
                                    <tr>
                                        <td><?php echo escape($row['customer_name']); ?> <span class="text-muted small d-block"><?php echo escape($row['customer_phone']); ?></span></td>
                                        <td class="text-end"><?php echo number_format($row['total_visits']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_spent'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Payment Status Summary</h5>
                <div class="table-responsive report-table-wrap">
                    <table class="table table-sm align-middle mb-0 report-table">
                        <thead>
                            <tr>
                                <th>Payment Status</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($paymentSummary)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No payment data available</td></tr>
                            <?php else: ?>
                                <?php foreach ($paymentSummary as $row): ?>
                                    <tr>
                                        <td><?php echo escape(ucwords(str_replace('_', ' ', $row['payment_status']))); ?></td>
                                        <td class="text-end"><?php echo number_format($row['count']); ?></td>
                                        <td class="text-end">₱ <?php echo number_format($row['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $perUnitReportHtml; ?>

<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                </div>
                <div class="table-responsive" style="max-height:360px;overflow-y:auto;">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Activity</th>
                                <th>User</th>
                                <th class="text-end">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentActivity)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No recent activity found for selected report date</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentActivity as $activity): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo escape(formatActivityAction($activity['action'] ?? '')); ?></strong>
                                            <div class="text-muted small"><?php echo escape($activity['description']); ?></div>
                                        </td>
                                        <td><?php echo escape($activity['username'] ?? 'System'); ?></td>
                                        <td class="text-end"><?php echo escape(date('F d, Y h:i A', strtotime($activity['created_at']))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const reportMenuState = new WeakMap();

    document.addEventListener('show.bs.dropdown', function(event) {
        const button = event.target;
        const container = button.closest('.report-actions-menu');
        const menu = container?.querySelector('.dropdown-menu');
        if (!container || !menu) return;

        const placeholder = document.createComment('report-actions-menu');
        menu.parentNode.insertBefore(placeholder, menu);
        document.body.appendChild(menu);
        reportMenuState.set(button, { menu, placeholder });
    });

    document.addEventListener('shown.bs.dropdown', function(event) {
        const state = reportMenuState.get(event.target);
        if (!state) return;

        const menu = state.menu;
        const buttonRect = event.target.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';
        const menuRect = menu.getBoundingClientRect();
        const gap = 6;
        const top = buttonRect.bottom + menuRect.height + gap <= window.innerHeight
            ? buttonRect.bottom + gap
            : Math.max(gap, buttonRect.top - menuRect.height - gap);
        const left = Math.min(
            Math.max(gap, buttonRect.right - menuRect.width),
            window.innerWidth - menuRect.width - gap
        );
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.visibility = 'visible';
    });

    document.addEventListener('hidden.bs.dropdown', function(event) {
        const state = reportMenuState.get(event.target);
        if (!state) return;

        state.placeholder.parentNode.insertBefore(state.menu, state.placeholder);
        state.placeholder.remove();
        state.menu.style.position = '';
        state.menu.style.top = '';
        state.menu.style.left = '';
        state.menu.style.right = '';
        state.menu.style.display = '';
        state.menu.style.visibility = '';
        reportMenuState.delete(event.target);
    });

    window.confirmExpenseDelete = function(form) {
        const cells = form.closest('tr')?.cells || [];
        const details = cells.length >= 8
            ? `Date: ${cells[0].textContent.trim()}\nCategory: ${cells[1].textContent.trim()}\nJO: ${cells[2].textContent.trim()}\nAmount: ${cells[6].textContent.trim()}\nEntered by: ${cells[5].textContent.trim()}`
            : 'Expense details are unavailable.';
        appConfirm(`Delete this expense entry?\n\n${details}`, {
            title: 'Delete Expense',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'danger'
        }).then(function(confirmed) {
            if (confirmed && form) {
                form.submit();
            }
        });

        return false;
    };

    window.confirmIncomeDelete = function(form) {
        const cells = form.closest('tr')?.cells || [];
        const details = cells.length >= 5
            ? `Date: ${cells[0].textContent.trim()}\nDescription: ${cells[1].textContent.trim()}\nAmount: ${cells[4].textContent.trim()}\nEntered by: ${cells[3].textContent.trim()}`
            : 'Income details are unavailable.';
        appConfirm(`Delete this income entry?\n\n${details}`, {
            title: 'Delete Income',
            confirmText: 'Delete',
            cancelText: 'Cancel',
            variant: 'danger'
        }).then(function(confirmed) {
            if (confirmed && form) {
                form.submit();
            }
        });

        return false;
    };
})();
</script>

<script>
(function() {
    const reportData = <?php echo json_encode($incomeReport); ?>;
    const labels = reportData.map(item => item.date);
    const totalIncomeData = reportData.map(item => parseFloat(item.total_income) || 0);
    const paidIncomeData = reportData.map(item => parseFloat(item.paid_income) || 0);
    const partialIncomeData = reportData.map(item => parseFloat(item.partial_income) || 0);
    const pendingIncomeData = reportData.map(item => parseFloat(item.pending_income) || 0);

    const ctx = document.getElementById('incomeTrendChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Income',
                        data: totalIncomeData,
                        borderColor: '#555555',
                        backgroundColor: function(context) {
                            const chart = context.chart;
                            const { ctx: c, chartArea } = chart;
                            if (!chartArea) return 'rgba(150,150,150,0.25)';
                            const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            g.addColorStop(0, 'rgba(140,140,140,0.50)');
                            g.addColorStop(1, 'rgba(200,200,200,0.02)');
                            return g;
                        },
                        tension: 0.42,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#333',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 2
                    },
                    {
                        label: 'Paid Income',
                        data: paidIncomeData,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.08)',
                        tension: 0.42,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 1.5
                    },
                    {
                        label: 'Partial Income',
                        data: partialIncomeData,
                        borderColor: '#fd7e14',
                        backgroundColor: 'rgba(253,126,20,0.08)',
                        tension: 0.42,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#fd7e14',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 1.5
                    },
                    {
                        label: 'Pending Income',
                        data: pendingIncomeData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.06)',
                        tension: 0.42,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#dc3545',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        borderWidth: 1.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(ctx) {
                                return ' ' + ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString('en-PH');
                            }
                        }
                    }
                },
                interaction: { mode: 'nearest', intersect: false },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#777', font: { size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            color: '#777',
                            font: { size: 11 },
                            callback: function(v) {
                                if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                                if (v >= 1000) return (v / 1000).toFixed(0) + 'k';
                                return v;
                            }
                        }
                    }
                }
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
