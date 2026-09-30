<?php
/**
 * Report Model - Version 2.0
 * Simplified for new database schema
 * Handles report generation and statistics
 */

class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];

        // Total job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status != 'cancelled'";
        $result = $this->db->fetch($sql);
        $stats['total_job_orders'] = $result['total'] ?? 0;

        // Pending job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'pending' AND status != 'cancelled'";
        $result = $this->db->fetch($sql);
        $stats['pending_job_orders'] = $result['total'] ?? 0;

        // In progress job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'in_progress' AND status != 'cancelled'";
        $result = $this->db->fetch($sql);
        $stats['in_progress_job_orders'] = $result['total'] ?? 0;

        // Completed job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'completed' AND status != 'cancelled'";
        $result = $this->db->fetch($sql);
        $stats['completed_job_orders'] = $result['total'] ?? 0;

        // Total users (staff + admin)
        $sql = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->fetch($sql);
        $stats['total_users'] = $result['total'] ?? 0;

        // Today's income
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders
                WHERE status != 'cancelled' AND DATE(created_at) = CURDATE()";
        $result = $this->db->fetch($sql);
        $stats['today_income'] = $result['total'] ?? 0;

        // Yesterday's income
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders
                WHERE status != 'cancelled' AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        $result = $this->db->fetch($sql);
        $stats['yesterday_income'] = $result['total'] ?? 0;

        // This month's income
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders 
                WHERE status != 'cancelled'
                AND YEAR(created_at) = YEAR(CURDATE()) 
                AND MONTH(created_at) = MONTH(CURDATE())";
        $result = $this->db->fetch($sql);
        $stats['month_income'] = $result['total'] ?? 0;

        // Last month's income
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders 
                WHERE status != 'cancelled'
                AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        $result = $this->db->fetch($sql);
        $stats['last_month_income'] = $result['total'] ?? 0;

        // This year's income
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders 
                WHERE status != 'cancelled'
                AND YEAR(created_at) = YEAR(CURDATE()) 
                ";
        $result = $this->db->fetch($sql);
        $stats['year_income'] = $result['total'] ?? 0;

        return $stats;
    }

    /**
     * Get income for a specific date (combines new payment records + legacy JO data)
     */
    public function getDailyIncomeByDate($date) {
        // New payment records
        $sql1 = "SELECT COALESCE(SUM(p.amount), 0) as total
                FROM job_order_payments p
                INNER JOIN job_orders jo ON jo.id = p.job_order_id
                WHERE jo.status != 'cancelled' AND DATE(p.payment_date) = ?";
        $r1 = $this->db->fetch($sql1, [$date]);
        $newTotal = (float)($r1['total'] ?? 0);

        // Legacy: JOs paid on this date that have NO records in job_order_payments
        $sql2 = "SELECT SUM(
                    CASE
                        WHEN jo.payment_status = 'paid' THEN jo.total_amount
                        WHEN jo.payment_status = 'partial' THEN COALESCE(jo.partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders jo
                WHERE jo.status != 'cancelled'
                  AND DATE(COALESCE(jo.payment_date, jo.created_at)) = ?
                  AND jo.payment_status IN ('paid','partial')
                  AND jo.id NOT IN (SELECT DISTINCT job_order_id FROM job_order_payments)";
        $r2 = $this->db->fetch($sql2, [$date]);
        $legacyTotal = (float)($r2['total'] ?? 0);

        return $newTotal + $legacyTotal;
    }

    /**
     * Get income for a specific year and month
     */
    public function getMonthlyIncomeByYearMonth($year, $month) {
        $sql1 = "SELECT COALESCE(SUM(p.amount), 0) as total
                FROM job_order_payments p
                INNER JOIN job_orders jo ON jo.id = p.job_order_id
                WHERE jo.status != 'cancelled' AND YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?";
        $r1 = $this->db->fetch($sql1, [(int)$year, (int)$month]);
        $newTotal = (float)($r1['total'] ?? 0);

        $sql2 = "SELECT SUM(
                    CASE
                        WHEN jo.payment_status = 'paid' THEN jo.total_amount
                        WHEN jo.payment_status = 'partial' THEN COALESCE(jo.partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders jo
                WHERE jo.status != 'cancelled'
                  AND YEAR(COALESCE(jo.payment_date, jo.created_at)) = ?
                  AND MONTH(COALESCE(jo.payment_date, jo.created_at)) = ?
                  AND jo.payment_status IN ('paid','partial')
                  AND jo.id NOT IN (SELECT DISTINCT job_order_id FROM job_order_payments)";
        $r2 = $this->db->fetch($sql2, [(int)$year, (int)$month]);
        $legacyTotal = (float)($r2['total'] ?? 0);

        return $newTotal + $legacyTotal;
    }

    /**
     * Get income report by date range
     */
    public function getIncomeReport($dateFrom, $dateTo) {
        // Paid income is recognized only when JO is fully paid.
        // Partial collections are tracked separately and excluded from paid/net.
        $sql = "SELECT date,
                       SUM(job_orders_count) as job_orders_count,
                       SUM(total_income) as total_income,
                       SUM(paid_income) as paid_income,
                       SUM(partial_income) as partial_income,
                       SUM(pending_income) as pending_income
                FROM (
                    SELECT DATE(jo.created_at) as date,
                           COUNT(*) as job_orders_count,
                           0 as total_income,
                           0 as paid_income,
                           0 as partial_income,
                           0 as pending_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND DATE(jo.created_at) BETWEEN ? AND ?
                    GROUP BY DATE(jo.created_at)
                    UNION ALL
                    SELECT DATE(COALESCE((SELECT MAX(p.payment_date) FROM job_order_payments p WHERE p.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at)) as date,
                           0 as job_orders_count,
                           SUM(COALESCE(jo.total_amount, 0)) as total_income,
                           SUM(COALESCE(jo.total_amount, 0)) as paid_income,
                           0 as partial_income,
                           0 as pending_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND jo.payment_status = 'paid'
                      AND DATE(COALESCE((SELECT MAX(p2.payment_date) FROM job_order_payments p2 WHERE p2.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at)) BETWEEN ? AND ?
                    GROUP BY DATE(COALESCE((SELECT MAX(p3.payment_date) FROM job_order_payments p3 WHERE p3.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at))
                    UNION ALL
                    SELECT DATE(COALESCE((SELECT MAX(p.payment_date) FROM job_order_payments p WHERE p.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at)) as date,
                           0 as job_orders_count,
                           SUM(COALESCE(jo.partial_amount, 0)) as total_income,
                           0 as paid_income,
                           SUM(COALESCE(jo.partial_amount, 0)) as partial_income,
                           0 as pending_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND jo.payment_status = 'partial'
                      AND DATE(COALESCE((SELECT MAX(p2.payment_date) FROM job_order_payments p2 WHERE p2.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at)) BETWEEN ? AND ?
                    GROUP BY DATE(COALESCE((SELECT MAX(p3.payment_date) FROM job_order_payments p3 WHERE p3.job_order_id = jo.id), jo.payment_date, jo.updated_at, jo.created_at))
                    UNION ALL
                    SELECT DATE(jo.created_at) as date,
                           0 as job_orders_count,
                           0 as total_income,
                           0 as paid_income,
                           0 as partial_income,
                           SUM(COALESCE(jo.total_amount, 0)) as pending_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND DATE(jo.created_at) BETWEEN ? AND ?
                      AND jo.payment_status = 'pending'
                    GROUP BY DATE(jo.created_at)
                    UNION ALL
                    SELECT DATE(jo.created_at) as date,
                           0 as job_orders_count,
                           0 as total_income,
                           0 as paid_income,
                           0 as partial_income,
                           SUM(GREATEST(COALESCE(jo.total_amount, 0) - COALESCE(jo.partial_amount, 0), 0)) as pending_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND DATE(jo.created_at) BETWEEN ? AND ?
                      AND jo.payment_status = 'partial'
                    GROUP BY DATE(jo.created_at)
                ) combined
                GROUP BY date
                ORDER BY date ASC";
        
        return $this->db->fetchAll($sql, [
            $dateFrom, $dateTo,
            $dateFrom, $dateTo,
            $dateFrom, $dateTo,
            $dateFrom, $dateTo,
            $dateFrom, $dateTo
        ]);
    }

public function getPaidProductCostExpenses($dateFrom, $dateTo) {
            $sql = "SELECT COALESCE((SELECT MAX(p.payment_date) FROM job_order_payments p WHERE p.job_order_id = jo.id), jo.payment_date, jo.created_at) AS expense_at,
            DATE(COALESCE((SELECT MAX(p.payment_date) FROM job_order_payments p WHERE p.job_order_id = jo.id), jo.payment_date, jo.created_at)) AS expense_date,
                   jo.job_order_number,
                   c.full_name AS customer_name,
                   SUM(COALESCE(jop.quantity, 0) * COALESCE(pr.cost_price, 0)) AS amount,
                   GROUP_CONCAT(DISTINCT CASE WHEN jop.product_id IS NOT NULL THEN jop.product_name END ORDER BY jop.product_name SEPARATOR ', ') AS products,
                   COALESCE(
                       (SELECT u.full_name FROM job_order_payments p
                        INNER JOIN users u ON u.id = p.created_by
                        WHERE p.job_order_id = jo.id
                        ORDER BY p.payment_date DESC LIMIT 1),
                       (SELECT u2.full_name FROM users u2 WHERE u2.id = jo.created_by LIMIT 1),
                       'System'
                   ) AS processed_by
            FROM job_orders jo
            LEFT JOIN customers c ON c.id = jo.customer_id
            LEFT JOIN job_order_products jop ON jop.job_order_id = jo.id
            LEFT JOIN products pr ON pr.id = jop.product_id
            WHERE jo.status != 'cancelled' AND jo.payment_status = 'paid'
              AND DATE(COALESCE((SELECT MAX(p2.payment_date) FROM job_order_payments p2 WHERE p2.job_order_id = jo.id), jo.payment_date, jo.created_at)) BETWEEN ? AND ?
            GROUP BY jo.id, jo.job_order_number, c.full_name, processed_by
            HAVING amount > 0
            ORDER BY expense_date DESC, job_order_number DESC";

    return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
}

    /**
     * Get monthly income statistics
     */
    public function getMonthlyIncomeStats($year = null) {
        if (!$year) $year = date('Y');

        $sql = "SELECT month, SUM(job_orders_count) as job_orders_count, SUM(total_income) as total_income
                FROM (
                    SELECT MONTH(COALESCE(
                               (SELECT MAX(p.payment_date)
                                FROM job_order_payments p
                                WHERE p.job_order_id = jo.id),
                               jo.payment_date,
                               jo.updated_at,
                               jo.created_at
                           )) as month,
                           COUNT(*) as job_orders_count,
                           SUM(jo.total_amount) as total_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND jo.payment_status = 'paid'
                      AND YEAR(COALESCE(
                          (SELECT MAX(p2.payment_date)
                           FROM job_order_payments p2
                           WHERE p2.job_order_id = jo.id),
                          jo.payment_date,
                          jo.updated_at,
                          jo.created_at
                      )) = ?
                    GROUP BY MONTH(COALESCE(
                        (SELECT MAX(p3.payment_date)
                         FROM job_order_payments p3
                         WHERE p3.job_order_id = jo.id),
                        jo.payment_date,
                        jo.updated_at,
                        jo.created_at
                    ))
                    UNION ALL
                    SELECT MONTH(COALESCE(jo.payment_date, jo.created_at)) as month,
                           COUNT(*) as job_orders_count,
                           SUM(jo.total_amount) as total_income
                    FROM job_orders jo
                    WHERE jo.status != 'cancelled'
                      AND YEAR(COALESCE(jo.payment_date, jo.created_at)) = ?
                      AND jo.payment_status = 'paid'
                      AND jo.id NOT IN (SELECT DISTINCT job_order_id FROM job_order_payments)
                    GROUP BY MONTH(COALESCE(jo.payment_date, jo.created_at))
                ) combined
                GROUP BY month
                ORDER BY month ASC";
        
        $results = $this->db->fetchAll($sql, [$year, $year]);
        
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = ['month' => $m, 'job_orders_count' => 0, 'total_income' => 0];
        }
        foreach ($results as $row) {
            $monthlyData[$row['month']] = $row;
        }

        // Manual income is recorded separately from job-order payments.
        // Include it in the dashboard chart for the active shop and year.
        if (function_exists('getReportManualIncome')) {
            foreach (getReportManualIncome() as $incomeRow) {
                $incomeDate = (string)($incomeRow['income_date'] ?? '');
                if ($incomeDate === '' || date('Y', strtotime($incomeDate)) !== (string)$year) {
                    continue;
                }

                $month = (int)date('n', strtotime($incomeDate));
                if ($month >= 1 && $month <= 12) {
                    $monthlyData[$month]['total_income'] =
                        (float)$monthlyData[$month]['total_income'] + (float)($incomeRow['amount'] ?? 0);
                }
            }
        }
        
        return array_values($monthlyData);
    }

    /**
     * Get service type statistics — groups by actual service name from job_order_services,
     * falls back to job_orders if no service rows exist
     */
    public function getServiceTypeStats($dateFrom = null, $dateTo = null) {
        $params = [];
        $dateWhere = '';
        if ($dateFrom && $dateTo) {
            $dateWhere = " AND DATE(jo.created_at) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        // Try job_order_services first (has actual service names)
        $sql = "SELECT 
                    jos.service_name AS service_name,
                    COUNT(DISTINCT jo.id) AS count,
                    SUM(jos.total) AS total_revenue
                FROM job_order_services jos
                INNER JOIN job_orders jo ON jos.job_order_id = jo.id
                WHERE jo.status != 'cancelled' {$dateWhere}
                GROUP BY jos.service_name
                ORDER BY total_revenue DESC";

        $rows = $this->db->fetchAll($sql, $params);

        // If no service rows, fall back to job_orders grouped by payment_method as a proxy
        if (empty($rows)) {
            $params2 = ['cancelled'];
            $dateWhere2 = '';
            if ($dateFrom && $dateTo) {
                $dateWhere2 = " AND DATE(created_at) BETWEEN ? AND ?";
                $params2[] = $dateFrom;
                $params2[] = $dateTo;
            }
            $sql2 = "SELECT 
                        COALESCE(NULLIF(notes,''), 'General Service') AS service_name,
                        COUNT(*) AS count,
                        SUM(total_amount) AS total_revenue
                     FROM job_orders
                     WHERE status != ?{$dateWhere2}
                     GROUP BY service_name
                     ORDER BY total_revenue DESC";
            $rows = $this->db->fetchAll($sql2, $params2);
        }

        return $rows;
    }

    /**
     * Normalize payment methods into the business-facing categories used in reports.
     */
    private function normalizePaymentMethodGroup($paymentMethod) {
        $value = strtolower(trim((string)$paymentMethod));
        $value = preg_replace('/[\-_]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        if ($value === '' || $value === 'null' || $value === 'none') {
            return 'cash';
        }

        if (in_array($value, ['cash', 'cash payment'], true)) {
            return 'cash';
        }

        if (strpos($value, 'gcash') !== false || strpos($value, 'g cash') !== false) {
            return 'gcash';
        }

        if (strpos($value, 'bank') !== false || strpos($value, 'transfer') !== false) {
            return 'bank_transfer';
        }

        if (strpos($value, 'card') !== false || strpos($value, 'swipe') !== false || strpos($value, 'paymaya') !== false || strpos($value, 'maya') !== false || strpos($value, 'online') !== false) {
            return 'card';
        }

        return 'cash';
    }

    /**
     * Get payment method statistics based on actual payment records, with a legacy fallback.
     */
    public function getPaymentMethodStats($dateFrom = null, $dateTo = null) {
        $totals = [
            'cash' => ['payment_method' => 'Cash', 'count' => 0, 'total_amount' => 0.0],
            'gcash' => ['payment_method' => 'GCash', 'count' => 0, 'total_amount' => 0.0],
            'bank_transfer' => ['payment_method' => 'Bank Transfer', 'count' => 0, 'total_amount' => 0.0],
            'card' => ['payment_method' => 'Swipe/Card', 'count' => 0, 'total_amount' => 0.0],
        ];

        // All payments from job_order_payments (covers both paid and partial JOs)
        $paymentSql = "SELECT p.payment_method, COUNT(*) as count, SUM(p.amount) as total_amount
            FROM job_order_payments p
            INNER JOIN job_orders jo ON jo.id = p.job_order_id
            WHERE jo.status != 'cancelled' AND jo.payment_status IN ('paid', 'partial')";
        $paymentParams = [];

        if ($dateFrom && $dateTo) {
            $paymentSql .= " AND DATE(p.payment_date) BETWEEN ? AND ?";
            $paymentParams[] = $dateFrom;
            $paymentParams[] = $dateTo;
        }

        $paymentSql .= " GROUP BY p.payment_method";
        $paymentRows = $this->db->fetchAll($paymentSql, $paymentParams);

        foreach ($paymentRows as $row) {
            $methodKey = $this->normalizePaymentMethodGroup($row['payment_method'] ?? 'cash');
            if (!isset($totals[$methodKey])) {
                $methodKey = 'cash';
            }
            $totals[$methodKey]['count'] += (int)($row['count'] ?? 0);
            $totals[$methodKey]['total_amount'] += (float)($row['total_amount'] ?? 0);
        }

        // Legacy JOs with no entries in job_order_payments (paid or partial, use partial_amount for partial)
        $legacySql = "SELECT payment_method, COUNT(*) as count,
                SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE COALESCE(partial_amount, 0) END) as total_amount
            FROM job_orders
            WHERE status != 'cancelled' AND payment_status IN ('paid', 'partial')
              AND id NOT IN (SELECT DISTINCT job_order_id FROM job_order_payments)";
        $legacyParams = [];

        if ($dateFrom && $dateTo) {
            $legacySql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $legacyParams[] = $dateFrom;
            $legacyParams[] = $dateTo;
        }

        $legacySql .= " GROUP BY payment_method";
        $legacyRows = $this->db->fetchAll($legacySql, $legacyParams);

        foreach ($legacyRows as $row) {
            $methodKey = $this->normalizePaymentMethodGroup($row['payment_method'] ?? 'cash');
            if (!isset($totals[$methodKey])) {
                $methodKey = 'cash';
            }
            $totals[$methodKey]['count'] += (int)($row['count'] ?? 0);
            $totals[$methodKey]['total_amount'] += (float)($row['total_amount'] ?? 0);
        }

        return [
            $totals['cash'],
            $totals['gcash'],
            $totals['bank_transfer'],
            $totals['card'],
        ];
    }

    /**
     * Get top customers
     */
    public function getTopCustomers($limit = 10, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    c.full_name as customer_name,
                    c.phone as customer_phone,
                    COUNT(jo.id) as total_visits,
                    SUM(jo.total_amount) as total_spent
                FROM job_orders jo
                INNER JOIN customers c ON jo.customer_id = c.id
                WHERE jo.status != 'cancelled'";

        $params = [];
        if ($dateFrom && $dateTo) {
            $sql .= " AND DATE(jo.created_at) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        $sql .= "
                GROUP BY c.id, c.full_name, c.phone
                ORDER BY total_spent DESC
                LIMIT ?";

        $params[] = (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get payment status summary
     */
    public function getPaymentStatusSummary($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    payment_status,
                    COUNT(*) as count,
                    SUM(
                        CASE
                            WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                            ELSE total_amount
                        END
                    ) as total_amount
                FROM job_orders
                WHERE status != 'cancelled'";

        $params = [];
        if ($dateFrom && $dateTo) {
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        $sql .= "
                GROUP BY payment_status
                ORDER BY count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get job order status summary
     */
    public function getJobOrderStatusSummary($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(total_amount) as total_amount
                FROM job_orders
                WHERE status != 'cancelled'";

        $params = [];
        if ($dateFrom && $dateTo) {
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        $sql .= "
                GROUP BY status
                ORDER BY 
                    CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed' THEN 3
                        WHEN 'cancelled' THEN 4
                    END";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    al.action,
                    al.description,
                    al.created_at,
                    COALESCE(NULLIF(u.username, ''), NULLIF(s.username, ''), NULLIF(s.full_name, ''), 'System') AS username
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                LEFT JOIN staff s ON al.user_id = s.id
                WHERE 1=1";

        $params = [];
        $cleanFrom = trim((string)$dateFrom);
        $cleanTo = trim((string)$dateTo);
        if ($cleanFrom !== '' && $cleanTo !== '') {
            $sql .= " AND DATE(al.created_at) BETWEEN ? AND ?";
            $params[] = $cleanFrom;
            $params[] = $cleanTo;
        } elseif ($cleanFrom !== '') {
            $sql .= " AND DATE(al.created_at) = ?";
            $params[] = $cleanFrom;
        } elseif ($cleanTo !== '') {
            $sql .= " AND DATE(al.created_at) = ?";
            $params[] = $cleanTo;
        }

        $sql .= " ORDER BY al.created_at DESC";
        if ((int)$limit > 0) {
            $sql .= " LIMIT ?";
            $params[] = (int)$limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get technician performance scores (point-based ranking)
     * 
     * Scoring:
     * - Completed JO as Lead: +10 pts
     * - Completed JO as Assistant: +5 pts
     * - Speed Bonus: +1 to +5 pts per JO (faster = more)
     * - Revenue as Lead: +1 pt per ₱1,000
     * - Revenue as Assist: +0.5 pt per ₱1,000
     * - Clean completion (no return): +3 pts per JO
     * - Return before release: -5 pts
     * - Return after release: -10 pts
    * - Absent from JO: -5 pts; remains eligible for completion points
    * - Transfer from JO: no removal deduction; remains eligible for completion points
     */
    public function getTechnicianPerformance($dateFrom = null, $dateTo = null) {
        $params = [];
        $dateCondition = '';
        if ($dateFrom && $dateTo) {
            $dateCondition = " AND DATE(tp.created_at) BETWEEN ? AND ?";
            $params = [$dateFrom, $dateTo];
        }

        try {
            // Step 1: Aggregate points per technician
            $sql = "SELECT 
                        tp.technician_id,
                        SUM(CASE WHEN tp.reason LIKE '%Completed (Lead)%' THEN 1 ELSE 0 END) AS lead_completed,
                        SUM(CASE WHEN tp.reason LIKE '%Completed (Assistant)%' THEN 1 ELSE 0 END) AS assist_completed,
                        SUM(CASE WHEN tp.reason = 'Speed Bonus' THEN tp.points ELSE 0 END) AS speed_points,
                        SUM(CASE WHEN tp.reason = 'Revenue Bonus' THEN tp.points ELSE 0 END) AS revenue_points,
                        SUM(CASE WHEN tp.reason = 'Clean Completion' THEN tp.points ELSE 0 END) AS clean_points,
                        SUM(CASE WHEN tp.points < 0 THEN tp.points ELSE 0 END) AS penalty_points,
                        SUM(tp.points) AS total_score,
                        COUNT(DISTINCT tp.job_order_id) AS total_jos
                    FROM technician_points tp
                    WHERE 1=1 {$dateCondition}
                    GROUP BY tp.technician_id
                    ORDER BY total_score DESC";

            $rows = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            return [];
        }

        $revenueByTech = [];
        try {
            $revenueParams = [];
            $revenueDateCondition = '';
            if ($dateFrom && $dateTo) {
                $revenueDateCondition = " AND DATE(COALESCE(jo.completed_at, jo.updated_at, jo.created_at)) BETWEEN ? AND ?";
                $revenueParams = [$dateFrom, $dateTo];
            }

            // Only compute fallback revenue for technicians who HAVE stored points
            // but are missing a Revenue Bonus row — not for techs with zero stored points
            // (which happens after a full TRUNCATE of technician_points).
            $revenueSql = "SELECT jot.technician_id,
                                 SUM(
                                     CASE
                                         WHEN COALESCE(jot.is_assist, 0) = 1 THEN ROUND(COALESCE(jo.total_amount, 0) / 1000 * 0.5, 1)
                                         ELSE ROUND(COALESCE(jo.total_amount, 0) / 1000, 1)
                                     END
                                 ) AS revenue_points
                           FROM job_order_technicians jot
                           INNER JOIN job_orders jo ON jo.id = jot.job_order_id
                           WHERE jo.status IN ('completed', 'released')
                             AND jot.technician_id IN (
                                 SELECT DISTINCT technician_id FROM technician_points
                             )
                             AND NOT EXISTS (
                                 SELECT 1 FROM technician_points tp2
                                 WHERE tp2.technician_id = jot.technician_id
                                   AND tp2.job_order_id = jo.id
                                   AND tp2.reason = 'Revenue Bonus'
                             )
                             {$revenueDateCondition}
                           GROUP BY jot.technician_id";

            $revenueRows = $this->db->fetchAll($revenueSql, $revenueParams);
            foreach ($revenueRows as $rr) {
                $tid = (int)($rr['technician_id'] ?? 0);
                if ($tid > 0) {
                    $revenueByTech[$tid] = (float)($rr['revenue_points'] ?? 0);
                }
            }
        } catch (\Exception $e) {
            $revenueByTech = [];
        }

        // Include technicians whose completed JO revenue exists but whose older
        // point history did not receive a stored Revenue Bonus row.
        $knownTechnicians = [];
        foreach ($rows as $row) {
            $knownTechnicians[(int)($row['technician_id'] ?? 0)] = true;
        }
        foreach ($revenueByTech as $technicianId => $revenuePoints) {
            if (isset($knownTechnicians[(int)$technicianId])) {
                continue;
            }
            $rows[] = [
                'technician_id' => (int)$technicianId,
                'lead_completed' => 0,
                'assist_completed' => 0,
                'speed_points' => 0,
                'revenue_points' => 0,
                'clean_points' => 0,
                'penalty_points' => 0,
                'total_score' => 0,
                'total_jos' => 0,
            ];
        }

        if (empty($rows)) return [];

        // Step 2: Get staff names for each technician
        $result = [];
        foreach ($rows as $r) {
            $tid = (int)$r['technician_id'];
            $staffInfo = null;
            try {
                $staffInfo = $this->db->fetch("SELECT full_name, staff_id FROM staff WHERE id=? LIMIT 1", [$tid]);
            } catch (\Exception $e) {}
            if (!$staffInfo) {
                try {
                    $staffInfo = $this->db->fetch("SELECT COALESCE(full_name, username) AS full_name, '' AS staff_id FROM users WHERE id=? LIMIT 1", [$tid]);
                } catch (\Exception $e) {}
            }

            $storedRevenuePoints = (float)$r['revenue_points'];
            $actualRevenuePoints = isset($revenueByTech[$tid]) ? (float)$revenueByTech[$tid] : $storedRevenuePoints;
            $adjustedTotalScore = (float)$r['total_score'] - $storedRevenuePoints + $actualRevenuePoints;

            $result[] = [
                'tech_id' => $tid,
                'staff_id' => $staffInfo['staff_id'] ?? '',
                'full_name' => $staffInfo['full_name'] ?? 'Technician #'.$tid,
                'profile_image' => null,
                'lead_completed' => (int)$r['lead_completed'],
                'assist_completed' => (int)$r['assist_completed'],
                'speed_points' => (float)$r['speed_points'],
                'revenue_points' => $actualRevenuePoints,
                'clean_points' => (float)$r['clean_points'],
                'return_penalty' => (float)$r['penalty_points'],
                'removed_penalty' => 0,
                'total_score' => $adjustedTotalScore,
                'total_jos' => (int)$r['total_jos'],
                'total_hours' => 0,
            ];
        }

        usort($result, function ($a, $b) {
            $scoreDiff = (float)($b['total_score'] ?? 0) - (float)($a['total_score'] ?? 0);
            if (abs($scoreDiff) > 0.00001) {
                return $scoreDiff > 0 ? 1 : -1;
            }

            $joDiff = (int)($b['total_jos'] ?? 0) - (int)($a['total_jos'] ?? 0);
            if ($joDiff !== 0) {
                return $joDiff;
            }

            return strcmp((string)($a['full_name'] ?? ''), (string)($b['full_name'] ?? ''));
        });

        // Step 3: Get total work hours (filtered by date range)
        try {
            $hourParams = [];
            $hourDateCond = '';
            if ($dateFrom && $dateTo) {
                $hourDateCond = " AND DATE(jo.created_at) BETWEEN ? AND ?";
                $hourParams = [$dateFrom, $dateTo];
            }
            $hourRows = $this->db->fetchAll(
                "SELECT jot.technician_id, SUM(COALESCE(jot.work_duration,0)) AS total_secs
                 FROM job_order_technicians jot
                 INNER JOIN job_orders jo ON jo.id = jot.job_order_id
                 WHERE jo.status != 'cancelled' {$hourDateCond}
                 GROUP BY jot.technician_id",
                $hourParams
            );
            foreach ($hourRows as $hr) {
                $hTid = (int)$hr['technician_id'];
                foreach ($result as &$rr) {
                   if ($rr['tech_id'] === $hTid) {
                       $rr['total_hours'] = round((int)$hr['total_secs'] / 3600, 1);
                       break;
                   }
                }
                unset($rr);
            }
        } catch (\Exception $e) {}

        return $result;
    }
}
