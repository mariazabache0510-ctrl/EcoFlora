<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '5432');
        $database = env('DB_DATABASE', 'ecoflora_db');
        $username = env('DB_USERNAME', 'postgres');
        $password = env('DB_PASSWORD', '');
        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $database;

        self::$instance = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return self::$instance;
    }
}
