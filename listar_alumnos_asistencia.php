<?php
header('Content-Type: application/json');
include 'conexion.php';

$sql = "SELECT
            a.id,
            a.rut_alumno,
            a.nombre_alumno,
            a.escuela_id,
            e.nombre_escuela,
            a.total_asistencia,
            a.activo
        FROM alumnos a
        INNER JOIN escuela e ON e.id = a.escuela_id
        WHERE a.activo = 1
        ORDER BY a.nombre_alumno ASC";

$result = $conn->query($sql);

$alumnos = [];

while ($row = $result->fetch_assoc()) {
    $alumnos[] = $row;
}

echo json_encode([
    "ok" => true,
    "alumnos" => $alumnos
]);

$conn->close();
?>
