<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$input = json_decode(file_get_contents("php://input"), true);

$usuario_id = intval($input['usuario_id'] ?? 0);
$escuela_id = intval($input['escuela_id'] ?? 0);
$fecha = trim($input['fecha'] ?? '');
$registros = $input['registros'] ?? [];

if ($usuario_id <= 0 || $escuela_id <= 0 || $fecha === '' || !is_array($registros)) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Datos inválidos"
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

$conn->begin_transaction();

try {
    foreach ($registros as $r) {
        $id_alumno = intval($r['id_alumno'] ?? 0);
        $asistencia = intval($r['asistencia'] ?? 0);

        if ($id_alumno <= 0 || !in_array($asistencia, [0, 1, 4])) {
            continue;
        }

        $sql_guardar = "INSERT INTO asistencia (id_alumno, fecha, asistencia)
                        VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE asistencia = VALUES(asistencia)";

        $stmt = $conn->prepare($sql_guardar);
        $stmt->bind_param("isi", $id_alumno, $fecha, $asistencia);
        $stmt->execute();
        $stmt->close();
    }

    $sql_recalcular = "UPDATE alumnos a
                      SET a.total_asistencia = (
                          SELECT IFNULL(SUM(s.asistencia), 0)
                          FROM asistencia s
                          WHERE s.id_alumno = a.id
                            AND s.fecha >= IFNULL((
                                SELECT MAX(ex.fecha_cambio_cinto)
                                FROM examenes ex
                                WHERE ex.alumno_id = a.id
                                  AND ex.situacion = 'APROBADO'
                                  AND ex.fecha_cambio_cinto IS NOT NULL
                            ), '1900-01-01')
                      )
                      WHERE a.escuela_id = ?";

    $stmt_recalcular = $conn->prepare($sql_recalcular);
    $stmt_recalcular->bind_param("i", $escuela_id);
    $stmt_recalcular->execute();
    $stmt_recalcular->close();

    $conn->commit();

    echo json_encode([
        "ok" => true,
        "mensaje" => "Asistencia guardada correctamente"
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar asistencia"
    ]);
}

$conn->close();
?>
