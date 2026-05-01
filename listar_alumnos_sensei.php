<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode(["ok" => false, "mensaje" => "usuario_id inválido"]);
    exit;
}

$sql = "SELECT
            a.id,
            a.rut_alumno,
            a.nombre_alumno,
            a.edad,
            a.fecha_nacimiento,
            a.fecha_ingreso,
            a.direccion,
            a.rut_apoderado,
            a.nombre_apoderado,
            a.talla_polera,
            a.talla_karategui,
            a.cinto_inicial,
            a.escuela_id,
            e.nombre_escuela,
            a.activo,
            a.cinto_actual,
            IFNULL(a.total_asistencia, 0) AS total_asistencia
        FROM alumnos a
        INNER JOIN usuario_escuela ue ON ue.escuela_id = a.escuela_id
        INNER JOIN escuela e ON e.id = a.escuela_id
        WHERE ue.usuario_id = ?
        ORDER BY e.nombre_escuela ASC, a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);
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
