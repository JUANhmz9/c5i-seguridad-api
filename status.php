<?php
// =====================================================
// El dashboard llama a este endpoint para mostrar el
// estado actual de todos los sitios.
//
// Metodo: GET
// =====================================================

require __DIR__ . '/config.php';

$stmt = $pdo->query(
    "SELECT id, nombre, ubicacion, modo, sirena_estado, ultima_actualizacion
     FROM sitios
     ORDER BY id"
);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
