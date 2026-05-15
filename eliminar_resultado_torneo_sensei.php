<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$input = json_decode(file_get_contents("php://input"), true);

$usuario_id = intval($input['usuario_id'] ?? 0);
$id = intval($input['id'] ?? 0);

if ($usuario_id <= 0 || $id <= 0) {
    echo json_encode(["ok" => false, "mensaje" => "Datos inválidos"]);
    exit;
}

$sql_validar = "SELECT at.id
                FROM alumno_torneo at
                INNER JOIN alumnos a ON a.id = at.alumno_id
                INNER JOIN usuario_escuela ue ON ue.escuela_id = a.escuela_id
                WHERE at.id = ?
                  AND ue.usuario_id = ?
                LIMIT 1";

$stmt = $conn->prepare($sql_validar);
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res->fetch_assoc()) {
    echo json_encode(["ok" => false, "mensaje" => "No tiene permiso para eliminar este registro"]);
    exit;
}
$stmt->close();

$stmt_del = $conn->prepare("DELETE FROM alumno_torneo WHERE id = ?");
$stmt_del->bind_param("i", $id);
$stmt_del->execute();
$stmt_del->close();

echo json_encode(["ok" => true, "mensaje" => "Resultado eliminado correctamente"]);

$conn->close();
?>
