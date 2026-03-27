<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Falta usuario_id"
    ]);
    exit;
}

$sql = "SELECT
            a.id,
            a.rut_alumno,
            a.nombre_alumno,
            a.escuela_id,
            e.nombre_escuela,
            a.total_asistencia,
            a.activo
        FROM alumnos a
        INNER JOIN escuela e
            ON e.id = a.escuela_id
        INNER JOIN usuario_escuela ue
            ON ue.escuela_id = a.escuela_id
        WHERE a.activo = 1
          AND ue.usuario_id = ?
        ORDER BY a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando consulta"
    ]);
    exit;
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$alumnos = [];

while ($row = $result->fetch_assoc()) {
    $alumnos[] = $row;
}

echo json_encode([
    "ok" => true,
    "alumnos" => $alumnos
]);

$stmt->close();
$conn->close();
?>
