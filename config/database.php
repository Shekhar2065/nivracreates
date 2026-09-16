<?php
declare(strict_types=1);

/** @return array<string, mixed> */
function nivra_config(): array
{
    static $config;
    if (is_array($config)) {
        return $config;
    }

    $configFile = __DIR__ . '/config.php';
    $config = is_file($configFile) ? require $configFile : [];
    return is_array($config) ? $config : [];
}

/** @return PDO|null */
function nivra_db(): ?PDO
{
    static $connection = false;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = nivra_config();
    if ($config === []) {
        return null;
    }

    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '3306',
            $config['database'] ?? 'nivra_portfolio',
            $config['charset'] ?? 'utf8mb4'
        );
        $connection = new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $connection;
    } catch (PDOException $exception) {
        error_log('Nivra database connection failed: ' . $exception->getMessage());
        return null;
    }
}
