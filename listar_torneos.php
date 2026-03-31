<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 0);
error_reporting(0);

include 'conexion.php';

$sql = "SELECT 
            id,
            nombre_torneo,
            fecha_torneo,
            ciudad
        FROM torneos
        ORDER BY fecha_torneo DESC, id DESC";

$resultado = $conn->query($sql);

if ($resultado === false) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al listar torneos"
    ]);
    $conn->close();
    exit;
}

$torneos = [];

while ($fila = $resultado->fetch_assoc()) {
    $torneos[] = $fila;
}

echo json_encode([
    "ok" => true,
    "torneos" => $torneos
]);

$conn->close();
?>
