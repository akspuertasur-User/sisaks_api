<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$input = json_decode(file_get_contents("php://input"), true);

$usuario_id = intval($input['usuario_id'] ?? 0);
$escuela_id = intval($input['escuela_id'] ?? 0);
$anio = intval($input['anio'] ?? 0);
$mes = intval($input['mes'] ?? 0);
$registros = $input['registros'] ?? [];

if ($usuario_id <= 0 || $escuela_id <= 0 || $anio <= 0 || $mes <= 0 || !is_array($registros)) {
    echo json_encode(["ok" => false, "mensaje" => "Datos inválidos"]);
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

$conn->begin_transaction();

try {
    foreach ($registros as $r) {
        $alumno_id = intval($r['alumno_id'] ?? 0);
        $estado = strtoupper(trim($r['estado'] ?? 'PENDIENTE'));
        $monto = floatval($r['monto'] ?? 0);
        $fecha_pago = trim($r['fecha_pago'] ?? '');
        $medio_pago = trim($r['medio_pago'] ?? '');
        $observacion = trim($r['observacion'] ?? '');

        if ($alumno_id <= 0) {
            continue;
        }

        if (!in_array($estado, ['PENDIENTE', 'PAGADO'])) {
            $estado = 'PENDIENTE';
        }

        if ($estado === 'PENDIENTE') {
            $fecha_pago = null;
            $monto = 0;
            $medio_pago = '';
        } else {
            if ($fecha_pago === '') {
                $fecha_pago = date('Y-m-d');
            }
        }

        $sql = "INSERT INTO mensualidades (
                    alumno_id, anio, mes, fecha_pago, monto, estado, medio_pago, observacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    fecha_pago = VALUES(fecha_pago),
                    monto = VALUES(monto),
                    estado = VALUES(estado),
                    medio_pago = VALUES(medio_pago),
                    observacion = VALUES(observacion)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiisdsss",
            $alumno_id,
            $anio,
            $mes,
            $fecha_pago,
            $monto,
            $estado,
            $medio_pago,
            $observacion
        );
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();

    echo json_encode([
        "ok" => true,
        "mensaje" => "Mensualidades guardadas correctamente"
    ]);

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar mensualidades"
    ]);
}

$conn->close();
?>
