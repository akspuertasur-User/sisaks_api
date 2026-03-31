<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 0);
error_reporting(0);

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
            a.nombre_alumno,
            c.nombre_categoria AS categoria,
            m.nombre_medalla AS medalla
        FROM alumno_torneo at
        INNER JOIN alumnos a
            ON a.id = at.alumno_id
        INNER JOIN categorias_torneo c
            ON c.id = at.categoria_id
        LEFT JOIN medallas m
            ON m.id = at.medalla_id
        WHERE at.torneo_id = ?
        ORDER BY
            CASE m.nombre_medalla
                WHEN 'Oro' THEN 1
                WHEN 'Plata' THEN 2
                WHEN 'Bronce' THEN 3
                WHEN 'Reconocimiento' THEN 4
                ELSE 5
            END,
            a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando consulta"
    ]);
    $conn->close();
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
