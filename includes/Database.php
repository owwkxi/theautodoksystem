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

        if (session_status() === PHP_SESSION_ACTIVE) {
            $selectedDb = trim((string)($_SESSION['shop_db_name'] ?? ''));
            if ($selectedDb !== '') {
                foreach (getShopOptions() as $option) {
                    if (($option['db_name'] ?? '') === $selectedDb) {
                        $this->dbname = $selectedDb;
                        break;
                    }
                }
            }
        }

        $this->username = DB_USER;
        $this->password = DB_PASS;
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

            $shouldCheckStatusEnum = true;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $statusEnumCheckKey = '_jo_status_enum_checked_' . $this->dbname;
                if (!empty($_SESSION[$statusEnumCheckKey])) {
                    $shouldCheckStatusEnum = false;
                }
            }

            if ($shouldCheckStatusEnum) {
                $this->ensureJobOrderStatusEnum();
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['_jo_status_enum_checked_' . $this->dbname] = 1;
                }
            }
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
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
            throw new Exception("Database query failed.");
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
