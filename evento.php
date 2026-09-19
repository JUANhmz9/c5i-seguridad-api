<?php
// =====================================================
// El ESP32 llama a este endpoint cuando:
//   - el PIR detecta movimiento
//   - la sirena se enciende o se apaga sola (modo automatico)
//
// Metodo: POST
// Body (JSON): { "sitio_id": 1, "tipo": "movimiento_detectado" }
// =====================================================

require __DIR__ . '/config.php';

$data = json_decode(file_get_contents('php://input'), true);

$sitio_id = $data['sitio_id'] ?? null;
$tipo     = $data['tipo'] ?? null;

$tiposValidos = ['movimiento_detectado', 'sirena_activada', 'sirena_desactivada'];

if (!$sitio_id || !in_array($tipo, $tiposValidos, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos invalidos: se requiere sitio_id y un tipo valido']);
    exit;
}

// Verifica que el sitio exista antes de insertar
$stmt = $pdo->prepare("SELECT id FROM sitios WHERE id = ?");
$stmt->execute([$sitio_id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'sitio_id no existe']);
    exit;
}

// Guarda el evento en el historico
$stmt = $pdo->prepare("INSERT INTO eventos (sitio_id, tipo, origen) VALUES (?, ?, 'automatico')");
$stmt->execute([$sitio_id, $tipo]);

// Actualiza el estado visible de la sirena en el sitio
if ($tipo === 'sirena_activada' || $tipo === 'sirena_desactivada') {
    $sirenaEstado = ($tipo === 'sirena_activada') ? 'sonando' : 'apagada';
    $stmt = $pdo->prepare("UPDATE sitios SET sirena_estado = ? WHERE id = ?");
    $stmt->execute([$sirenaEstado, $sitio_id]);
}

echo json_encode(['ok' => true]);
