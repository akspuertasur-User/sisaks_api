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
            nombre_alumno,
            categoria,
            medalla
        FROM medallero_torneo
        WHERE torneo_id = ?
        ORDER BY 
          CASE medalla
            WHEN 'Oro' THEN 1
            WHEN 'Plata' THEN 2
            WHEN 'Bronce' THEN 3
            WHEN 'Reconocimiento' THEN 4
            ELSE 5
          END,
          nombre_alumno ASC";

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

$medallero = [];

while ($fila = $resultado->fetch_assoc()) {
    $medallero[] = $fila;
}

echo json_encode([
    "ok" => true,
    "medallero" => $medallero
]);

$stmt->close();
$conn->close();
?>
