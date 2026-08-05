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
        $sql = "SELECT COUNT(*) as total FROM job_orders";
        $result = $this->db->fetch($sql);
        $stats['total_job_orders'] = $result['total'] ?? 0;

        // Pending job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'pending'";
        $result = $this->db->fetch($sql);
        $stats['pending_job_orders'] = $result['total'] ?? 0;

        // In progress job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'in_progress'";
        $result = $this->db->fetch($sql);
        $stats['in_progress_job_orders'] = $result['total'] ?? 0;

        // Completed job orders
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE status = 'completed'";
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
                WHERE DATE(created_at) = CURDATE()";
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
                WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
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
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
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
                WHERE YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
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
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
                ";
        $result = $this->db->fetch($sql);
        $stats['year_income'] = $result['total'] ?? 0;

        return $stats;
    }

    /**
     * Get income for a specific date
     */
    public function getDailyIncomeByDate($date) {
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders
                WHERE DATE(created_at) = ?";
        $result = $this->db->fetch($sql, [$date]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get income for a specific year and month
     */
    public function getMonthlyIncomeByYearMonth($year, $month) {
        $sql = "SELECT SUM(
                    CASE
                        WHEN payment_status = 'paid' THEN total_amount
                        WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                        ELSE 0
                    END
                ) as total
                FROM job_orders
                WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?";
        $result = $this->db->fetch($sql, [(int)$year, (int)$month]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get income report by date range
     */
    public function getIncomeReport($dateFrom, $dateTo) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as job_orders_count,
                    SUM(total_amount) as total_income,
                    SUM(
                        CASE
                            WHEN payment_status = 'paid' THEN total_amount
                            WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                            ELSE 0
                        END
                    ) as paid_income,
                    SUM(
                        CASE
                            WHEN payment_status = 'pending' THEN total_amount
                            WHEN payment_status = 'partial' THEN GREATEST(total_amount - COALESCE(partial_amount, 0), 0)
                            ELSE 0
                        END
                    ) as pending_income
                FROM job_orders
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY DATE(created_at)
                ORDER BY DATE(created_at) ASC";
        
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }

    /**
     * Get monthly income statistics
     */
    public function getMonthlyIncomeStats($year = null) {
        if (!$year) $year = date('Y');

        $sql = "SELECT 
                    MONTH(created_at) as month,
                    COUNT(*) as job_orders_count,
                    SUM(
                        CASE
                            WHEN payment_status = 'paid' THEN total_amount
                            WHEN payment_status = 'partial' THEN COALESCE(partial_amount, 0)
                            ELSE 0
                        END
                    ) as total_income
                FROM job_orders
                WHERE YEAR(created_at) = ?
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at) ASC";
        
        $results = $this->db->fetchAll($sql, [$year]);
        
        // Fill in missing months with zero values, keyed by month number 1-12
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[$m] = ['month' => $m, 'job_orders_count' => 0, 'total_income' => 0];
        }
        foreach ($results as $row) {
            $monthlyData[$row['month']] = $row;
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
                WHERE 1=1 {$dateWhere}
                GROUP BY jos.service_name
                ORDER BY total_revenue DESC";

        $rows = $this->db->fetchAll($sql, $params);

        // If no service rows, fall back to job_orders grouped by payment_method as a proxy
        if (empty($rows)) {
            $params2 = [];
            $dateWhere2 = '';
            if ($dateFrom && $dateTo) {
                $dateWhere2 = " WHERE DATE(created_at) BETWEEN ? AND ?";
                $params2[] = $dateFrom;
                $params2[] = $dateTo;
            }
            $sql2 = "SELECT 
                        COALESCE(NULLIF(notes,''), 'General Service') AS service_name,
                        COUNT(*) AS count,
                        SUM(total_amount) AS total_revenue
                     FROM job_orders
                     {$dateWhere2}
                     GROUP BY service_name
                     ORDER BY total_revenue DESC";
            $rows = $this->db->fetchAll($sql2, $params2);
        }

        return $rows;
    }

    /**
     * Get payment method statistics
     */
    public function getPaymentMethodStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    payment_method,
                    COUNT(*) as count,
                    SUM(total_amount) as total_amount
                FROM job_orders
                WHERE payment_status = 'paid'";
        
        $params = [];
        if ($dateFrom && $dateTo) {
            $sql .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $dateFrom;
            $params[] = $dateTo;
        }

        $sql .= " GROUP BY payment_method ORDER BY count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get top customers
     */
    public function getTopCustomers($limit = 10) {
        $sql = "SELECT 
                    c.full_name as customer_name,
                    c.phone as customer_phone,
                    COUNT(jo.id) as total_visits,
                    SUM(jo.total_amount) as total_spent
                FROM job_orders jo
                INNER JOIN customers c ON jo.customer_id = c.id
                GROUP BY c.id, c.full_name, c.phone
                ORDER BY total_spent DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [(int)$limit]);
    }

    /**
     * Get payment status summary
     */
    public function getPaymentStatusSummary() {
        $sql = "SELECT 
                    payment_status,
                    COUNT(*) as count,
                    SUM(total_amount) as total_amount
                FROM job_orders
                GROUP BY payment_status
                ORDER BY count DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get job order status summary
     */
    public function getJobOrderStatusSummary() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(total_amount) as total_amount
                FROM job_orders
                GROUP BY status
                ORDER BY 
                    CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed' THEN 3
                        WHEN 'cancelled' THEN 4
                    END";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10) {
        $sql = "SELECT 
                    al.action,
                    al.description,
                    al.created_at,
                    u.username
                FROM activity_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [(int)$limit]);
    }
}
