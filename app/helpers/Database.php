<?php

declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $c = Config::database();
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'],
            $c['port'],
            $c['database'],
            $c['charset']
        );
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];
        $pdoClass = new ReflectionClass(PDO::class);
        if ($pdoClass->hasConstant('MYSQL_ATTR_CONNECT_TIMEOUT')) {
            $opts[(int) $pdoClass->getConstant('MYSQL_ATTR_CONNECT_TIMEOUT')] = 5;
        }
        try {
            self::$pdo = new PDO($dsn, $c['username'], $c['password'], $opts);
        } catch (PDOException $e) {
            AppError::log($e);
            if (self::shouldThrowOnConnectFailure()) {
                throw new RuntimeException('Database connection failed', 0, $e);
            }
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=UTF-8');
            }
            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>Base de dados indisponível</title></head><body>';
            echo '<p>Não foi possível estabelecer ligação à base de dados. Tente novamente em instantes.</p>';
            echo '</body></html>';
            exit;
        }
        return self::$pdo;
    }

    public static function prepare(string $sql): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare SQL statement');
        }

        return $stmt;
    }

    public static function query(string $sql): PDOStatement
    {
        $stmt = self::pdo()->query($sql);
        if ($stmt === false) {
            throw new RuntimeException('Failed to execute SQL query');
        }

        return $stmt;
    }

    private static function shouldThrowOnConnectFailure(): bool
    {
        if (PHP_SAPI === 'cli') {
            return true;
        }

        return defined('TITANIUM_TESTING');
    }
}
