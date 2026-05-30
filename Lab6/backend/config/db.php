<?php
$_env = parse_ini_file(__DIR__ . '/../.env');
if ($_env === false) {
    http_response_code(500);
    exit('Missing .env file. Copy .env.example to .env and fill in credentials.');
}

define('DB_HOST', $_env['DB_HOST']);
define('DB_NAME', $_env['DB_NAME']);
define('DB_USER', $_env['DB_USER']);
define('DB_PASS', $_env['DB_PASS']);

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function get_groups(): array {
    return ['1A', '1B', '2A', '2B', '3A', '3B'];
}
