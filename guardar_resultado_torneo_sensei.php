<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$input = json_decode(file_get_contents("php://input"), true);

$usuario_id = intval($input['usuario_id'] ?? 0);
$id = intval($input['id'] ?? 0);
$alumno_id = intval($input['alumno_id'] ?? 0);
$torneo_id = intval($input['torneo_id'] ?? 0);
$categoria_id = intval($input['categoria_id'] ?? 0);
$medalla_id = intval($input['medalla_id'] ?? 0);
$observacion = trim($input['observacion'] ?? '');
$url_imgur = trim($input['url_imgur'] ?? '');

if ($usuario_id <= 0 || $alumno_id <= 0 || $torneo_id <= 0 || $categoria_id <= 0) {
    echo json_encode(["ok" => false, "mensaje" => "Debe completar alumno, torneo y categoría"]);
    exit;
}

$sql_validar = "SELECT ue.id
                FROM alumnos a
                INNER JOIN usuario_escuela ue ON ue.escuela_id = a.escuela_id
                WHERE a.id = ?
                  AND ue.usuario_id = ?
                LIMIT 1";

$stmt_validar = $conn->prepare($sql_validar);
$stmt_validar->bind_param("ii", $alumno_id, $usuario_id);
$stmt_validar->execute();
$res_validar = $stmt_validar->get_result();

if (!$res_validar->fetch_assoc()) {
    echo json_encode(["ok" => false, "mensaje" => "El usuario no tiene acceso a la escuela del alumno"]);
    exit;
}

$stmt_validar->close();

$medalla_param = $medalla_id > 0 ? $medalla_id : null;

try {
    if ($id > 0) {
        $sql = "UPDATE alumno_torneo
                SET alumno_id = ?,
                    torneo_id = ?,
                    categoria_id = ?,
                    medalla_id = ?,
                    observacion = ?,
                    url_imgur = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiissi",
            $alumno_id,
            $torneo_id,
            $categoria_id,
            $medalla_param,
            $observacion,
            $url_imgur,
            $id
        );
    } else {
        $sql = "INSERT INTO alumno_torneo (
                    alumno_id,
                    torneo_id,
                    categoria_id,
                    medalla_id,
                    observacion,
                    url_imgur
                ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiiiss",
            $alumno_id,
            $torneo_id,
            $categoria_id,
            $medalla_param,
            $observacion,
            $url_imgur
        );
    }

    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "ok" => true,
        "mensaje" => $id > 0 ? "Resultado actualizado correctamente" : "Resultado ingresado correctamente"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar resultado"
    ]);
}

$conn->close();
?>
