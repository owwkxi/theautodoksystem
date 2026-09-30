<?php
require_once __DIR__ . '/../includes/Database.php';

class Service {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new service
     */
    public function create($data) {
        try {
            // Generate service code if not provided
            if (empty($data['service_code'])) {
                $data['service_code'] = $this->generateServiceCode();
            }
            
            // Check for duplicate service code
            if ($this->codeExists($data['service_code'])) {
                return false;
            }
            
            $sql = "INSERT INTO services (service_name, service_code, description, base_price, labor_cost, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['service_name'],
                $data['service_code'],
                $data['description'] ?? null,
                $data['service_price'] ?? $data['base_price'] ?? 0,
                $data['labor_cost'] ?? 0,
                $data['status'] ?? 'active'
            ]);
            
            if ($result) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Service creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find service by ID
     */
    public function findById($id) {
        try {
            $sql = "SELECT id, service_name, service_code, description, base_price as service_price, 
                           labor_cost, status, created_at, updated_at 
                    FROM services WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Service findById error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find service by code
     */
    public function findByCode($serviceCode) {
        try {
            $sql = "SELECT id, service_name, service_code, description, base_price as service_price, 
                           labor_cost, status, created_at, updated_at 
                    FROM services WHERE service_code = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$serviceCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Service findByCode error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all services with optional filters
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT id, service_name, service_code, description, base_price as service_price, 
                           labor_cost, status, created_at, updated_at 
                    FROM services WHERE 1=1";
            $params = [];
            
            // Status filter
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (service_name LIKE ? OR service_code LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Order by
            $sql .= " ORDER BY service_name ASC";
            
            // Pagination
            if (isset($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
                
                if (isset($filters['offset'])) {
                    $sql .= " OFFSET ?";
                    $params[] = (int)$filters['offset'];
                }
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Service getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update service
     */
    public function update($id, $data) {
        try {
            // Check for duplicate service code (excluding current service)
            if (!empty($data['service_code']) && $this->codeExists($data['service_code'], $id)) {
                return false;
            }
            
            $sql = "UPDATE services 
                    SET service_name = ?, service_code = ?, description = ?, 
                        base_price = ?, labor_cost = ?, status = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['service_name'],
                $data['service_code'],
                $data['description'] ?? null,
                $data['service_price'] ?? $data['base_price'] ?? 0,
                $data['labor_cost'] ?? 0,
                $data['status'] ?? 'active',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Service update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete service
     */
    public function delete($id) {
        try {
            // Check if service is used in job orders
            $sql = "SELECT COUNT(*) as count FROM job_order_services WHERE service_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return false; // Cannot delete service in use
            }
            
            // Check if service is in bundles
            $sql = "SELECT COUNT(*) as count FROM bundle_services WHERE service_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return false; // Cannot delete service in bundles
            }
            
            // Delete service
            $sql = "DELETE FROM services WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Service delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle service status
     */
    public function toggleStatus($id) {
        try {
            $sql = "UPDATE services 
                    SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Service toggleStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count services with filters
     */
    public function count($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM services WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (service_name LIKE ? OR service_code LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            error_log("Service count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get service statistics
     */
    public function getStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_services,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_services,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_services
                    FROM services";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Service getStats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate unique service code
     */
    public function generateServiceCode() {
        $prefix = 'SVC';
        
        try {
            $sql = "SELECT MAX(CAST(SUBSTRING(service_code, 4) AS UNSIGNED)) AS max_num
                    FROM services
                WHERE service_code REGEXP '^(SVS|SVC)[0-9]+$'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $newNumber = (int)($result['max_num'] ?? 0) + 1;
            return $prefix . str_pad((string)$newNumber, 2, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Service generateServiceCode error: " . $e->getMessage());
            return $prefix . '01';
        }
    }
    
    /**
     * Check if service code exists
     */
    public function codeExists($code, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM services WHERE service_code = ?";
            $params = [$code];
            
            if ($excludeId !== null) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Service codeExists error: " . $e->getMessage());
            return false;
        }
    }
}
