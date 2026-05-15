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
            at.id,
            at.alumno_id,
            a.nombre_alumno,
            a.rut_alumno,
            a.escuela_id,
            e.nombre_escuela,
            at.torneo_id,
            t.nombre_torneo,
            t.fecha_torneo,
            at.categoria_id,
            c.nombre_categoria,
            at.medalla_id,
            m.nombre_medalla,
            at.observacion,
            at.url_imgur
        FROM alumno_torneo at
        INNER JOIN alumnos a ON a.id = at.alumno_id
        INNER JOIN usuario_escuela ue ON ue.escuela_id = a.escuela_id
        INNER JOIN escuela e ON e.id = a.escuela_id
        INNER JOIN torneos t ON t.id = at.torneo_id
        INNER JOIN categorias_torneo c ON c.id = at.categoria_id
        LEFT JOIN medallas m ON m.id = at.medalla_id
        WHERE ue.usuario_id = ?
        ORDER BY t.fecha_torneo DESC, a.nombre_alumno ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$datos = [];

while ($row = $result->fetch_assoc()) {
    $datos[] = $row;
}

echo json_encode([
    "ok" => true,
    "resultados" => $datos
]);

$stmt->close();
$conn->close();
?>
