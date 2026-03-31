<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$torneo_id = isset($_GET['torneo_id']) ? intval($_GET['torneo_id']) : 0;

if ($torneo_id <= 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "torneo_id inválido"
    ]);
    exit;
}

$sql = "SELECT 
            id, 
            titulo, 
            url_imagen
        FROM galeria_torneo
        WHERE torneo_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando consulta"
    ]);
    exit;
}

$stmt->bind_param("i", $torneo_id);
$stmt->execute();
$resultado = $stmt->get_result();

$fotos = [];

while ($fila = $resultado->fetch_assoc()) {
    $fotos[] = $fila;
}

echo json_encode([
    "ok" => true,
    "fotos" => $fotos
]);

$stmt->close();
$conn->close();
?>
