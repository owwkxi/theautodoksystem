<?php
/**
 * JobOrder Model - Version 2.0
 * Simplified for new database schema
 * Handles job order-related database operations
 */

class JobOrder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new job order
     */
    public function create($data) {
        $sql = "INSERT INTO job_orders (
                    job_order_number, customer_name, customer_phone, customer_email, customer_address,
                    vehicle_year, vehicle_make, vehicle_model, vehicle_color, vehicle_license, vehicle_mileage,
                    service_type, service_description,
                    subtotal, parts_cost, discount_type, discount_amount, total_amount,
                    payment_method, payment_status, status, notes, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['job_order_number'],
                $data['customer_name'],
                $data['customer_phone'],
                $data['customer_email'] ?? null,
                $data['customer_address'] ?? null,
                $data['vehicle_year'] ?? null,
                $data['vehicle_make'] ?? null,
                $data['vehicle_model'] ?? null,
                $data['vehicle_color'] ?? null,
                $data['vehicle_license'] ?? null,
                $data['vehicle_mileage'] ?? null,
                $data['service_type'],
                $data['service_description'] ?? null,
                $data['subtotal'] ?? 0,
                $data['parts_cost'] ?? 0,
                $data['discount_type'] ?? 'none',
                $data['discount_amount'] ?? 0,
                $data['total_amount'],
                $data['payment_method'] ?? 'cash',
                $data['payment_status'] ?? 'pending',
                $data['status'] ?? 'pending',
                $data['notes'] ?? null,
                $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Job order creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find job order by ID
     */
    public function findById($id) {
        $sql = "SELECT jo.*, 
                       creator.username as created_by_name
                FROM job_orders jo
                LEFT JOIN users creator ON jo.created_by = creator.id
                WHERE jo.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Find job order by number
     */
    public function findByNumber($jobOrderNumber) {
        $sql = "SELECT * FROM job_orders WHERE job_order_number = ?";
        return $this->db->fetch($sql, [$jobOrderNumber]);
    }

    /**
     * Get all job orders
     */
    public function getAll($filters = []) {
        $sql = "SELECT jo.*, 
                       creator.username as created_by_name
                FROM job_orders jo
                LEFT JOIN users creator ON jo.created_by = creator.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND jo.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND jo.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['service_type'])) {
            $sql .= " AND jo.service_type = ?";
            $params[] = $filters['service_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (jo.job_order_number LIKE ? OR jo.customer_name LIKE ? OR jo.vehicle_license LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(jo.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(jo.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY jo.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
            
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int)$filters['offset'];
            }
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Update job order
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'customer_name', 'customer_phone', 'customer_email', 'customer_address',
            'vehicle_year', 'vehicle_make', 'vehicle_model', 'vehicle_color', 'vehicle_license', 'vehicle_mileage',
            'service_type', 'service_description',
            'subtotal', 'parts_cost', 'discount_type', 'discount_amount', 'total_amount',
            'payment_method', 'payment_status', 'status', 'notes'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE job_orders SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            error_log("Job order update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete job order
     */
    public function delete($id) {
        $sql = "DELETE FROM job_orders WHERE id = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (Exception $e) {
            error_log("Job order deletion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count job orders
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM job_orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $sql .= " AND payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (job_order_number LIKE ? OR customer_name LIKE ? OR vehicle_license LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /**
     * Get daily income
     */
    public function getDailyIncome($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        $sql = "SELECT SUM(total_amount) as total 
                FROM job_orders 
                WHERE DATE(created_at) = ? AND payment_status = 'paid'";
        $result = $this->db->fetch($sql, [$date]);
        return $result['total'] ?? 0;
    }

    /**
     * Get monthly income
     */
    public function getMonthlyIncome($year = null, $month = null) {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');

        $sql = "SELECT SUM(total_amount) as total 
                FROM job_orders 
                WHERE YEAR(created_at) = ? AND MONTH(created_at) = ? AND payment_status = 'paid'";
        $result = $this->db->fetch($sql, [$year, $month]);
        return $result['total'] ?? 0;
    }

    /**
     * Get income statistics by month
     */
    public function getIncomeByMonth($year = null) {
        if (!$year) $year = date('Y');

        $sql = "SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                FROM job_orders 
                WHERE YEAR(created_at) = ? AND payment_status = 'paid'
                GROUP BY MONTH(created_at)
                ORDER BY MONTH(created_at)";
        return $this->db->fetchAll($sql, [$year]);
    }

    /**
     * Get recent job orders
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT * FROM job_orders
                ORDER BY created_at DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [(int)$limit]);
    }

    /**
     * Generate unique job order number
     */
    public function generateJobOrderNumber() {
        $result = $this->db->fetch(
            "SELECT MAX(CAST(SUBSTRING(job_order_number, 3) AS UNSIGNED)) AS max_num
             FROM job_orders
             WHERE job_order_number REGEXP '^JO[0-9]+$'"
        );
        $next = (int)($result['max_num'] ?? 0) + 1;
        return 'JO' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }
}
