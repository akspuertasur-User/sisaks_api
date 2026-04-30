<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$escuela_id = isset($_GET['escuela_id']) ? intval($_GET['escuela_id']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : 0;

if ($usuario_id <= 0 || $escuela_id <= 0 || $anio <= 0 || $mes <= 0) {
    echo json_encode(["ok" => false, "mensaje" => "Parámetros inválidos"]);
    exit;
}

$sql_validar = "SELECT id FROM usuario_escuela WHERE usuario_id = ? AND escuela_id = ? LIMIT 1";
$stmt_validar = $conn->prepare($sql_validar);
$stmt_validar->bind_param("ii", $usuario_id, $escuela_id);
$stmt_validar->execute();
$res_validar = $stmt_validar->get_result();

if (!$res_validar->fetch_assoc()) {
    echo json_encode(["ok" => false, "mensaje" => "El usuario no tiene acceso a esta escuela"]);
    exit;
}

$stmt_validar->close();

$sql = "SELECT
            a.id AS alumno_id,
            a.rut_alumno,
            a.nombre_alumno,
            a.escuela_id,
            e.nombre_escuela,
            IFNULL(m.fecha_pago, '') AS fecha_pago,
            IFNULL(m.monto, 0) AS monto,
            IFNULL(m.estado, 'PENDIENTE') AS estado,
            IFNULL(m.medio_pago, '') AS medio_pago,
            IFNULL(m.observacion, '') AS observacion
        FROM alumnos a
        INNER JOIN escuela e ON e.id = a.escuela_id
        LEFT JOIN mensualidades m
            ON m.alumno_id = a.id
           AND m.anio = ?
           AND m.mes = ?
        WHERE a.activo = 1
          AND a.escuela_id = ?
        ORDER BY a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $anio, $mes, $escuela_id);
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
