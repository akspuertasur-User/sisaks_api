<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$escuela_id = isset($_GET['escuela_id']) ? intval($_GET['escuela_id']) : 0;
$fecha = trim($_GET['fecha'] ?? '');

if ($usuario_id <= 0 || $escuela_id <= 0 || $fecha === '') {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Parámetros inválidos"
    ]);
    exit;
}

$sql_validar = "SELECT id
                FROM usuario_escuela
                WHERE usuario_id = ?
                  AND escuela_id = ?
                LIMIT 1";

$stmt_validar = $conn->prepare($sql_validar);
$stmt_validar->bind_param("ii", $usuario_id, $escuela_id);
$stmt_validar->execute();
$res_validar = $stmt_validar->get_result();

if (!$res_validar->fetch_assoc()) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El usuario no tiene acceso a esta escuela"
    ]);
    exit;
}

$stmt_validar->close();

$sql = "SELECT
            a.id,
            a.rut_alumno,
            a.nombre_alumno,
            a.escuela_id,
            e.nombre_escuela,
            IFNULL(a.total_asistencia, 0) AS total_asistencia,
            IFNULL(s.asistencia, 0) AS asistencia_dia
        FROM alumnos a
        INNER JOIN escuela e ON e.id = a.escuela_id
        LEFT JOIN asistencia s
            ON s.id_alumno = a.id
           AND s.fecha = ?
        WHERE a.activo = 1
          AND a.escuela_id = ?
        ORDER BY a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $fecha, $escuela_id);
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
