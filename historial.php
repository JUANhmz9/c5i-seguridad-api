<?php
// =====================================================
// El dashboard llama a este endpoint para mostrar la
// bitacora de eventos de un sitio (o de todos).
//
// Metodo: GET
// Query params opcionales: sitio_id, limite (default 100)
// =====================================================

require __DIR__ . '/config.php';

$sitio_id = $_GET['sitio_id'] ?? null;
$limite   = isset($_GET['limite']) ? (int)$_GET['limite'] : 100;

if ($sitio_id) {
    $stmt = $pdo->prepare(
        "SELECT * FROM eventos WHERE sitio_id = ? ORDER BY timestamp DESC LIMIT $limite"
    );
    $stmt->execute([$sitio_id]);
} else {
    $stmt = $pdo->query(
        "SELECT * FROM eventos ORDER BY timestamp DESC LIMIT $limite"
    );
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
