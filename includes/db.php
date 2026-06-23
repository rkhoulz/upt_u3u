<?php

$DB_HOST = 'localhost';
$DB_NAME = 'telemetria_ra_db';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    error_log('Error de conexión a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    die('No fue posible conectar con la base de datos. Verifique la configuración del servidor.');
}
