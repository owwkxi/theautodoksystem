<?php
class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;

    private function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;

        if (session_status() === PHP_SESSION_ACTIVE) {
            $selectedDb = trim((string)($_SESSION['shop_db_name'] ?? ''));
            if ($selectedDb !== '') {
                foreach (getShopOptions() as $option) {
                    if (($option['db_name'] ?? '') === $selectedDb) {
                        $this->dbname = $selectedDb;
                        $shopUser = trim((string)($option['db_user'] ?? ''));
                        $shopPass = (string)($option['db_pass'] ?? '');
                        if ($shopUser !== '') {
                            $this->username = $shopUser;
                            $this->password = $shopPass;
                        }
                        break;
                    }
                }
            }
        }
        $this->charset = DB_CHARSET;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            // Keep MySQL NOW()/CURDATE() aligned with application timezone.
            $this->connection->exec("SET time_zone = " . $this->connection->quote(DB_TIMEZONE_OFFSET));

            $shouldCheckStatusEnum = true;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $statusEnumCheckKey = '_jo_status_enum_checked_' . $this->dbname;
                if (!empty($_SESSION[$statusEnumCheckKey])) {
                    $shouldCheckStatusEnum = false;
                }
            }

            if ($shouldCheckStatusEnum) {
                $this->ensureJobOrderStatusEnum();
                $this->ensureJobOrderTechnicianStatusEnum();
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['_jo_status_enum_checked_' . $this->dbname] = 1;
                }
            }
            $this->ensureAttendanceTable();
            $attendanceEnumKey = '_attendance_status_enum_checked_' . $this->dbname;
            if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION[$attendanceEnumKey])) {
                if ($this->ensureAttendanceStatusEnum() && session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION[$attendanceEnumKey] = 1;
                }
            }
            $this->ensureJobOrderColumns();
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }

    }

    private function ensureAttendanceStatusEnum() {
        try {
            $check = $this->connection->query("SHOW COLUMNS FROM attendance LIKE 'status'");
            $column = $check ? $check->fetch(PDO::FETCH_ASSOC) : null;
            if (!$column || stripos((string)($column['Type'] ?? ''), "'other'") !== false) {
                return (bool)$column;
            }

            $this->connection->exec(
                "ALTER TABLE attendance MODIFY status ENUM('present','late','absent','on_leave','other') NOT NULL DEFAULT 'present'"
            );
            return true;
        } catch (PDOException $e) {
            error_log("Attendance status migration skipped: " . $e->getMessage());
            return false;
        }
    }

    private function ensureAttendanceTable() {
        try {
            $this->connection->exec(
                "CREATE TABLE IF NOT EXISTS attendance (
                    id INT NOT NULL AUTO_INCREMENT,
                    staff_id INT NOT NULL,
                    date DATE NOT NULL,
                    time_in TIME NOT NULL,
                    time_out TIME DEFAULT NULL,
                    photo_in VARCHAR(255) DEFAULT NULL,
                    photo_out VARCHAR(255) DEFAULT NULL,
                    status ENUM('present','late','absent','on_leave','other') NOT NULL DEFAULT 'present',
                    notes TEXT DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_attendance_staff_date (staff_id, date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            // Morning and afternoon attendance require two rows per staff/date.
            $indexes = $this->connection->query("SHOW INDEX FROM attendance")->fetchAll(PDO::FETCH_ASSOC);
            $indexNames = array_column($indexes, 'Key_name');
            if (in_array('staff_date', $indexNames, true)) {
                // Keep a standalone staff_id index for the foreign key before
                // removing the obsolete unique staff/date constraint.
                if (!in_array('idx_staff_id', $indexNames, true)) {
                    $this->connection->exec("ALTER TABLE attendance ADD INDEX idx_staff_id (staff_id)");
                }
                $this->connection->exec("ALTER TABLE attendance DROP INDEX staff_date");
            }
        } catch (PDOException $e) {
            error_log("Attendance table check skipped: " . $e->getMessage());
        }
    }

    private function ensureJobOrderStatusEnum() {
        try {
            $check = $this->connection->query("SHOW COLUMNS FROM job_orders LIKE 'status'");
            $column = $check ? $check->fetch(PDO::FETCH_ASSOC) : null;
            if (!$column) {
                return;
            }

            $type = (string)($column['Type'] ?? '');
            if (stripos($type, 'car_washing') !== false) {
                return;
            }

            $this->connection->exec(
                "ALTER TABLE job_orders MODIFY status ENUM('pending','ongoing','under_inspection','car_washing','completed','released','returned_for_revision','cancelled') NOT NULL DEFAULT 'pending'"
            );
        } catch (PDOException $e) {
            error_log("Job order status migration skipped: " . $e->getMessage());
        }
    }

    private function ensureJobOrderTechnicianStatusEnum() {
        try {
            $check = $this->connection->query("SHOW COLUMNS FROM job_order_technicians LIKE 'status'");
            $column = $check ? $check->fetch(PDO::FETCH_ASSOC) : null;
            if (!$column) {
                return;
            }

            $type = (string)($column['Type'] ?? '');
            if (stripos($type, 'removed') !== false) {
                return; // already has 'removed'
            }
            $this->connection->exec(
                "ALTER TABLE job_order_technicians MODIFY status ENUM('assigned','working','completed','on_hold','removed') NOT NULL DEFAULT 'assigned'"
            );
        } catch (PDOException $e) {
            error_log("job_order_technicians status migration skipped: " . $e->getMessage());
        }
    }

    private function ensureJobOrderColumns() {
        $columns = [
            'payment_date' => 'DATETIME NULL',
            'status_timer_seconds' => "INT(10) UNSIGNED NOT NULL DEFAULT 0",
            'status_timer_started_at' => 'DATETIME NULL',
            'work_started_at' => 'DATETIME NULL',
            'inspection_started_at' => 'DATETIME NULL',
            'completed_at' => 'DATETIME NULL',
        ];

        try {
            $check = $this->connection->query("SHOW COLUMNS FROM job_orders");
            $existing = [];
            while ($column = $check->fetch(PDO::FETCH_ASSOC)) {
                $existing[(string)($column['Field'] ?? '')] = true;
            }

            foreach ($columns as $name => $definition) {
                if (!isset($existing[$name])) {
                    $this->connection->exec(
                        "ALTER TABLE job_orders ADD COLUMN `{$name}` {$definition}"
                    );
                    error_log("Added missing job_orders column: {$name}");
                }
            }
        } catch (PDOException $e) {
            error_log("Job order column migration skipped: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage() . " | SQL: " . $sql);
            return false;
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }


    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }


    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }

    public function rowCount($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
}
