<?php
// =====================================================
// Conexion a la base de datos - la incluyen todos los
// demas archivos de /api
// =====================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// ---------- Datos de conexion (se leen de variables de entorno en Render) ----------
$host = getenv('DB_HOST') ?: 'mysql-1be0cd3e-c5i-seguridad.g.aivencloud.com';
$port = getenv('DB_PORT') ?: '26972';
$db   = getenv('DB_NAME') ?: 'defaultdb';
$user = getenv('DB_USER') ?: 'avnadmin';
$pass = getenv('DB_PASS') ?: '';
// -----------------------------------------------------------------------------

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexion a la base de datos']);
    exit;
}