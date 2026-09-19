<?php
// =====================================================
// El ESP32 llama a este endpoint cada pocos segundos
// (polling) para saber si hay un comando manual nuevo.
//
// Metodo: GET
// Query params: sitio_id, ultimo_timestamp (el que el
//   propio ESP32 ya proceso la ultima vez)
// =====================================================

require __DIR__ . '/config.php';

$sitio_id         = $_GET['sitio_id'] ?? null;
$ultimoProcesado  = $_GET['ultimo_timestamp'] ?? '';

if (!$sitio_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta sitio_id']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT tipo, timestamp FROM eventos
     WHERE sitio_id = ? AND origen = 'manual'
     ORDER BY timestamp DESC
     LIMIT 1"
);
$stmt->execute([$sitio_id]);
$ultimo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ultimo || $ultimo['timestamp'] === $ultimoProcesado) {
    echo json_encode(['comando' => null]);
    exit;
}

$mapaComandos = [
    'sirena_desactivada' => 'apagar',
    'sirena_activada'    => 'encender',
    'modo_automatico'    => 'automatico',
];

echo json_encode([
    'comando'   => $mapaComandos[$ultimo['tipo']] ?? null,
    'timestamp' => $ultimo['timestamp'],
]);
