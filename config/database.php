<?php
class DatabaseConfig {
    const DB_HOST = 'localhost';
    const DB_NAME = 'crm_asti';
    const DB_USER = 'crm_user';
    const DB_PASS = 'secure_password_2024';
    const CHARSET = 'utf8mb4';
}

class Database {
    private static $pdo = null;
    
    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . DatabaseConfig::DB_HOST . 
                    ";dbname=" . DatabaseConfig::DB_NAME . 
                    ";charset=" . DatabaseConfig::CHARSET,
                    DatabaseConfig::DB_USER,
                    DatabaseConfig::DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("❌ Ошибка подключения к БД: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
    
    public static function query($sql, $params = []) {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
?>