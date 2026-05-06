<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = self::env('DB_HOST', '127.0.0.1');
        $port = self::env('DB_PORT', '3306');
        $dbName = self::env('DB_NAME', 'startsmart');
        $username = self::env('DB_USER', 'root');
        $password = self::env('DB_PASS', '');
        $charset = self::env('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $dbName, $charset);

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException('Database connection failed: ' . $exception->getMessage(), (int) $exception->getCode());
        }

        return self::$connection;
    }

    private static function env(string $key, string $default): string
    {
        $fromEnvArray = $_ENV[$key] ?? null;
        if (is_string($fromEnvArray) && $fromEnvArray !== '') {
            return $fromEnvArray;
        }

        $fromServer = $_SERVER[$key] ?? null;
        if (is_string($fromServer) && $fromServer !== '') {
            return $fromServer;
        }

        $fromGetEnv = getenv($key);
        if (is_string($fromGetEnv) && $fromGetEnv !== '') {
            return $fromGetEnv;
        }

        return $default;
    }
}
