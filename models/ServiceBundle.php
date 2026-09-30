<?php
require_once __DIR__ . '/../includes/Database.php';

class ServiceBundle {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new service bundle
     */
    public function create($data, $serviceIds = []) {
        try {
            // Validate that bundle has at least one service
            if (empty($serviceIds)) {
                throw new Exception('Service bundle must include at least one service');
            }
            
            $this->db->beginTransaction();
            
            // Generate bundle code if not provided
            $bundleCode = !empty($data['bundle_code']) ? $data['bundle_code'] : $this->generateBundleCode();
            
            // Set bundle type to custom by default
            $bundleType = 'custom';
            
            // Create bundle
            $sql = "INSERT INTO service_bundles (bundle_name, bundle_code, bundle_type, description, package_price, status) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['bundle_name'],
                $bundleCode,
                $bundleType,
                $data['description'] ?? null,
                $data['package_price'],
                $data['status'] ?? 'active'
            ]);
            
            if (!$result) {
                throw new Exception('Failed to insert bundle');
            }
            
            $bundleId = $this->db->lastInsertId();
            
            // Add services to bundle
            foreach ($serviceIds as $serviceId) {
                $this->addService($bundleId, $serviceId);
            }
            
            $this->db->commit();
            return $bundleId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ServiceBundle creation error: " . $e->getMessage());
            throw $e; // Re-throw to get the error message
        }
    }
    
    /**
     * Generate unique bundle code
     */
    public function generateBundleCode() {
        $year = date('Y');
        $prefix = "BDL-{$year}-";
        
        try {
            // Get the last bundle code for this year
            $sql = "SELECT bundle_code FROM service_bundles 
                    WHERE bundle_code LIKE ? 
                    ORDER BY bundle_code DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$prefix . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Extract number and increment
                $lastNumber = (int)substr($result['bundle_code'], -4);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } catch (PDOException $e) {
            error_log("Bundle generateBundleCode error: " . $e->getMessage());
            return $prefix . '0001';
        }
    }
    
    /**
     * Find bundle by ID with services
     */
    public function findById($id) {
        try {
            // Get bundle details
            $sql = "SELECT id, bundle_name, description, package_price, status, created_at, updated_at 
                    FROM service_bundles WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $bundle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($bundle) {
                // Get associated services
                $bundle['services'] = $this->getServices($id);
                // Get associated products
                $bundle['products'] = $this->getProducts($id);
            }
            
            return $bundle;
        } catch (PDOException $e) {
            error_log("ServiceBundle findById error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all bundles with optional filters
     */
    public function getAll($filters = []) {
        try {
            $sql = "SELECT id, bundle_name, description, package_price, status, created_at, updated_at 
                    FROM service_bundles WHERE 1=1";
            $params = [];
            
            // Status filter
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (bundle_name LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY bundle_name ASC";
            
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
            $bundles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get services for each bundle
            foreach ($bundles as &$bundle) {
                $bundle['services'] = $this->getServices($bundle['id']);
                $bundle['products'] = $this->getProducts($bundle['id']);
            }
            
            return $bundles;
        } catch (PDOException $e) {
            error_log("ServiceBundle getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update bundle
     */
    public function update($id, $data) {
        try {
            $sql = "UPDATE service_bundles 
                    SET bundle_name = ?, description = ?, package_price = ?, status = ?
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['bundle_name'],
                $data['description'] ?? null,
                $data['package_price'],
                $data['status'] ?? 'active',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("ServiceBundle update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete bundle
     */
    public function delete($id) {
        try {
            // Check if bundle is used in job orders
            $sql = "SELECT COUNT(*) as count FROM job_order_services WHERE bundle_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return false; // Cannot delete bundle in use
            }
            
            // Delete bundle (cascade will delete bundle_services)
            $sql = "DELETE FROM service_bundles WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ServiceBundle delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle bundle status
     */
    public function toggleStatus($id) {
        try {
            $sql = "UPDATE service_bundles 
                    SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("ServiceBundle toggleStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count bundles with filters
     */
    public function count($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM service_bundles WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (bundle_name LIKE ? OR description LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['count'];
        } catch (PDOException $e) {
            error_log("ServiceBundle count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get bundle statistics
     */
    public function getStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_bundles,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_bundles,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_bundles
                    FROM service_bundles";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ServiceBundle getStats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add service to bundle
     */
    public function addService($bundleId, $serviceId) {
        try {
            $sql = "INSERT IGNORE INTO bundle_services (bundle_id, service_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$bundleId, $serviceId]);
        } catch (PDOException $e) {
            error_log("ServiceBundle addService error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove service from bundle
     */
    public function removeService($bundleId, $serviceId) {
        try {
            $sql = "DELETE FROM bundle_services WHERE bundle_id = ? AND service_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$bundleId, $serviceId]);
        } catch (PDOException $e) {
            error_log("ServiceBundle removeService error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all services in a bundle
     */
    public function getServices($bundleId) {
        try {
            $sql = "SELECT s.id as service_id, s.service_name, s.service_code, 
                           s.base_price as service_price, s.labor_cost
                    FROM services s
                    INNER JOIN bundle_services bs ON s.id = bs.service_id
                    WHERE bs.bundle_id = ?
                    ORDER BY s.service_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bundleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ServiceBundle getServices error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update all services in a bundle
     */
    public function updateServices($bundleId, $serviceIds) {
        try {
            $this->db->beginTransaction();
            
            // Remove all existing services
            $sql = "DELETE FROM bundle_services WHERE bundle_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bundleId]);
            
            // Add new services
            foreach ($serviceIds as $serviceId) {
                $this->addService($bundleId, $serviceId);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ServiceBundle updateServices error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all products in a bundle
     */
    public function getProducts($bundleId) {
        try {
            $sql = "SELECT p.id as product_id, p.product_name, p.product_code, p.selling_price, bp.quantity
                    FROM products p
                    INNER JOIN bundle_products bp ON p.id = bp.product_id
                    WHERE bp.bundle_id = ?
                    ORDER BY p.product_name";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bundleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("ServiceBundle getProducts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update all products in a bundle
     * @param int $bundleId
     * @param array $products Array of ['product_id' => int, 'quantity' => int]
     */
    public function updateProducts($bundleId, $products = []) {
        try {
            $this->db->beginTransaction();

            // Remove existing
            $sql = "DELETE FROM bundle_products WHERE bundle_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$bundleId]);

            // Insert new
            $sql = "INSERT INTO bundle_products (bundle_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            foreach ($products as $prod) {
                $prodId = (int)($prod['product_id'] ?? $prod['id'] ?? 0);
                $qty = max(1, (int)($prod['quantity'] ?? $prod['qty'] ?? 1));
                if ($prodId > 0) {
                    $stmt->execute([$bundleId, $prodId, $qty]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("ServiceBundle updateProducts error: " . $e->getMessage());
            return false;
        }
    }
}
