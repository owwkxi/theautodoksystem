-- ============================================================================
-- THE AUTODOK - SEED DATA
-- Only Admin Account and Essential Data
-- ============================================================================

USE `autodok_db`;

-- ============================================================================
-- ADMIN ACCOUNT ONLY
-- ============================================================================

-- Insert admin user
-- Username: admin_owwkxi
-- Password: helloworld!
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`, `status`) VALUES
('admin_owwkxi', '$2y$12$gatZHDc5g1ac9/7H2IIHJOj01ggs23VBcjL0ckwVYP8cmxMxxE80W', 'System Administrator', 'admin@autodok.com', 'admin', 'active');

-- ============================================================================
-- SERVICE CATEGORIES
-- ============================================================================

INSERT INTO `service_categories` (`category_name`, `category_code`, `description`, `status`) VALUES
('Light PMS', 'CAT-LPMS', 'Light Preventive Maintenance Service', 'active'),
('Regular PMS', 'CAT-RPMS', 'Regular Preventive Maintenance Service', 'active'),
('Heavy PMS', 'CAT-HPMS', 'Heavy Preventive Maintenance Service', 'active'),
('Repair Services', 'CAT-REPAIR', 'General Repair Services', 'active'),
('Diagnostic Services', 'CAT-DIAG', 'Diagnostic and Inspection Services', 'active');

-- ============================================================================
-- PRODUCT CATEGORIES
-- ============================================================================

INSERT INTO `product_categories` (`category_name`, `description`, `status`) VALUES
('Engine Oil', 'Engine lubricants and oils', 'active'),
('Filters', 'Oil filters, air filters, fuel filters', 'active'),
('Parts', 'Automotive parts and components', 'active'),
('Fluids', 'Brake fluid, coolant, transmission fluid', 'active'),
('Others', 'Miscellaneous automotive products', 'active');

-- ============================================================================
-- UNITS OF MEASUREMENT
-- ============================================================================

INSERT INTO `units` (`unit_name`, `unit_symbol`) VALUES
('Piece', 'pc'),
('Liter', 'L'),
('Gallon', 'gal'),
('Set', 'set'),
('Bottle', 'btl'),
('Can', 'can'),
('Box', 'box');

-- ============================================================================
-- PERMISSIONS
-- ============================================================================

INSERT INTO `permissions` (`permission_name`, `permission_code`, `description`, `module`) VALUES
-- Dashboard
('View Dashboard', 'dashboard.view', 'View dashboard and statistics', 'dashboard'),
-- Job Orders
('View Job Orders', 'job_orders.view', 'View job orders list', 'job_orders'),
('Create Job Order', 'job_orders.create', 'Create new job order', 'job_orders'),
('Edit Job Order', 'job_orders.edit', 'Edit existing job order', 'job_orders'),
('Delete Job Order', 'job_orders.delete', 'Delete job order', 'job_orders'),
('Approve Job Order', 'job_orders.approve', 'Approve completed job orders', 'job_orders'),
-- Staff Management
('View Staff', 'staff.view', 'View staff list', 'staff'),
('Create Staff', 'staff.create', 'Add new staff member', 'staff'),
('Edit Staff', 'staff.edit', 'Edit staff information', 'staff'),
('Delete Staff', 'staff.delete', 'Delete staff member', 'staff'),
-- Attendance
('View Attendance', 'attendance.view', 'View attendance records', 'attendance'),
('Manage Attendance', 'attendance.manage', 'Manage attendance records', 'attendance'),
-- Inventory
('View Inventory', 'inventory.view', 'View inventory list', 'inventory'),
('Manage Inventory', 'inventory.manage', 'Add, edit, delete inventory items', 'inventory'),
('Stock Adjustment', 'inventory.adjust', 'Adjust stock levels', 'inventory'),
-- Services
('View Services', 'services.view', 'View services and bundles', 'services'),
('Manage Services', 'services.manage', 'Add, edit, delete services', 'services'),
-- Reports
('View Reports', 'reports.view', 'View and generate reports', 'reports'),
('Export Reports', 'reports.export', 'Export reports to PDF/Excel', 'reports');


-- ============================================================================
-- ROLE PERMISSIONS (Admin has all permissions)
-- ============================================================================

INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'admin', id FROM `permissions`;

-- Cashier permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'cashier', id FROM `permissions` 
WHERE `permission_code` IN (
    'dashboard.view',
    'job_orders.view',
    'job_orders.create',
    'reports.view'
);

-- Chief Mechanic permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'chief_mechanic', id FROM `permissions` 
WHERE `permission_code` IN (
    'dashboard.view',
    'job_orders.view',
    'job_orders.edit',
    'job_orders.approve',
    'staff.view',
    'attendance.view',
    'attendance.manage',
    'inventory.view',
    'services.view',
    'reports.view'
);

-- Service Adviser permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'service_adviser', id FROM `permissions` 
WHERE `permission_code` IN (
    'dashboard.view',
    'job_orders.view',
    'job_orders.create',
    'job_orders.edit',
    'job_orders.approve',
    'staff.view',
    'inventory.view',
    'services.view',
    'reports.view'
);

-- Lead Man permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'lead_man', id FROM `permissions` 
WHERE `permission_code` IN (
    'dashboard.view',
    'job_orders.view',
    'staff.view',
    'attendance.view',
    'inventory.view',
    'services.view'
);

-- Technician permissions
INSERT INTO `role_permissions` (`role`, `permission_id`)
SELECT 'technician', id FROM `permissions` 
WHERE `permission_code` IN (
    'dashboard.view',
    'job_orders.view',
    'attendance.view'
);

-- ============================================================================
-- SYSTEM SETTINGS
-- ============================================================================

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('company_name', 'The Autodok', 'string', 'Company name'),
('company_address', '', 'string', 'Company address'),
('company_phone', '', 'string', 'Company phone number'),
('company_email', 'info@autodok.com', 'string', 'Company email'),
('tax_rate', '0', 'number', 'Tax rate percentage'),
('currency_symbol', '₱', 'string', 'Currency symbol'),
('date_format', 'Y-m-d', 'string', 'Date format'),
('time_format', 'H:i:s', 'string', 'Time format'),
('timezone', 'Asia/Manila', 'string', 'System timezone'),
('low_stock_threshold', '10', 'number', 'Low stock alert threshold'),
('job_order_prefix', 'JO', 'string', 'Job order number prefix'),
('enable_notifications', '1', 'boolean', 'Enable system notifications'),
('enable_email_notifications', '0', 'boolean', 'Enable email notifications'),
('maintenance_mode', '0', 'boolean', 'Maintenance mode status');

COMMIT;
