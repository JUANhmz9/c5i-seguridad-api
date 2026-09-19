<?php
// =====================================================
// El dashboard llama a este endpoint cuando alguien
// manda un comando manual desde la computadora del C5i.
//
// Metodo: POST
// Body (JSON): { "sitio_id": 1, "accion": "apagar" }
//
// accion puede ser:
//   "apagar"     -> fuerza la sirena a apagarse, ignora el PIR
//   "encender"   -> fuerza la sirena a sonar, ignora el PIR
//   "automatico" -> regresa el control al PIR (rearma el sitio)
// =====================================================

require __DIR__ . '/config.php';

$data = json_decode(file_get_contents('php://input'), true);

$sitio_id = $data['sitio_id'] ?? null;
$accion   = $data['accion'] ?? null;

$accionesValidas = ['apagar', 'encender', 'automatico'];

if (!$sitio_id || !in_array($accion, $accionesValidas, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos invalidos: se requiere sitio_id y una accion valida']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM sitios WHERE id = ?");
$stmt->execute([$sitio_id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'sitio_id no existe']);
    exit;
}

$mapa = [
    'apagar'     => ['tipo' => 'sirena_desactivada', 'modo' => 'forzado_off', 'sirena_estado' => 'apagada'],
    'encender'   => ['tipo' => 'sirena_activada',     'modo' => 'forzado_on',  'sirena_estado' => 'sonando'],
    'automatico' => ['tipo' => 'modo_automatico',     'modo' => 'automatico',  'sirena_estado' => 'apagada'],
];

$config = $mapa[$accion];

// Guarda el comando en el historico (origen manual)
$stmt = $pdo->prepare("INSERT INTO eventos (sitio_id, tipo, origen) VALUES (?, ?, 'manual')");
$stmt->execute([$sitio_id, $config['tipo']]);

// Actualiza el estado del sitio para que el dashboard lo refleje de inmediato
$stmt = $pdo->prepare("UPDATE sitios SET modo = ?, sirena_estado = ? WHERE id = ?");
$stmt->execute([$config['modo'], $config['sirena_estado'], $sitio_id]);

echo json_encode(['ok' => true]);
