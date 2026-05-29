<?php
// config/database.php - Универсальные настройки
class DatabaseConfig
{
    // Автоматически определяем окружение
    private static function isDocker()
    {
        // Если файл .env существует и содержит DOCKER_ENV=true
        if (file_exists(__DIR__ . '/../.env')) {
            $env = parse_ini_file(__DIR__ . '/../.env');
            if ($env !== false && isset($env['DOCKER_ENV'])) {
                return $env['DOCKER_ENV'] === 'true';
            }
        }
        // Или проверяем наличие переменной окружения
        return getenv('DOCKER_ENV') === 'true';
    }
    
    // Динамические константы - используйте методы вместо констант
    public static function getDBHost()
    {
        return self::isDocker() ? 'db' : 'localhost';
    }
    
    // Статические константы (не изменяются)
    const DB_NAME = 'crm_asti';
    const DB_USER = 'crm_user';
    const DB_PASS = 'crm_password';
    const CHARSET = 'utf8mb4';
}
