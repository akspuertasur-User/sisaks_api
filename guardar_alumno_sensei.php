<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$input = json_decode(file_get_contents("php://input"), true);

$usuario_id = intval($input['usuario_id'] ?? 0);
$id = intval($input['id'] ?? 0);
$rut_alumno = trim($input['rut_alumno'] ?? '');
$nombre_alumno = trim($input['nombre_alumno'] ?? '');
$edad = $input['edad'] === '' ? null : intval($input['edad'] ?? 0);
$fecha_nacimiento = trim($input['fecha_nacimiento'] ?? '');
$fecha_ingreso = trim($input['fecha_ingreso'] ?? '');
$direccion = trim($input['direccion'] ?? '');
$rut_apoderado = trim($input['rut_apoderado'] ?? '');
$nombre_apoderado = trim($input['nombre_apoderado'] ?? '');
$talla_polera = trim($input['talla_polera'] ?? '');
$talla_karategui = trim($input['talla_karategui'] ?? '');
$cinto_inicial = trim($input['cinto_inicial'] ?? '');
$escuela_id = intval($input['escuela_id'] ?? 0);
$activo = intval($input['activo'] ?? 1);
$cinto_actual = trim($input['cinto_actual'] ?? '');
$total_asistencia = intval($input['total_asistencia'] ?? 0);

if ($usuario_id <= 0 || $escuela_id <= 0 || $rut_alumno === '' || $nombre_alumno === '') {
    echo json_encode(["ok" => false, "mensaje" => "Debe ingresar RUT, nombre y escuela"]);
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
    echo json_encode(["ok" => false, "mensaje" => "El usuario no tiene acceso a esta escuela"]);
    exit;
}

$stmt_validar->close();

if ($fecha_nacimiento === '') {
    $fecha_nacimiento = null;
}

if ($fecha_ingreso === '') {
    $fecha_ingreso = null;
}

try {
    if ($id > 0) {
        $sql = "UPDATE alumnos SET
                    rut_alumno = ?,
                    nombre_alumno = ?,
                    edad = ?,
                    fecha_nacimiento = ?,
                    fecha_ingreso = ?,
                    direccion = ?,
                    rut_apoderado = ?,
                    nombre_apoderado = ?,
                    talla_polera = ?,
                    talla_karategui = ?,
                    cinto_inicial = ?,
                    escuela_id = ?,
                    activo = ?,
                    cinto_actual = ?,
                    total_asistencia = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissssssssiisii",
            $rut_alumno,
            $nombre_alumno,
            $edad,
            $fecha_nacimiento,
            $fecha_ingreso,
            $direccion,
            $rut_apoderado,
            $nombre_apoderado,
            $talla_polera,
            $talla_karategui,
            $cinto_inicial,
            $escuela_id,
            $activo,
            $cinto_actual,
            $total_asistencia,
            $id
        );
    } else {
        $sql = "INSERT INTO alumnos (
                    rut_alumno,
                    nombre_alumno,
                    edad,
                    fecha_nacimiento,
                    fecha_ingreso,
                    direccion,
                    rut_apoderado,
                    nombre_apoderado,
                    talla_polera,
                    talla_karategui,
                    cinto_inicial,
                    escuela_id,
                    activo,
                    cinto_actual,
                    total_asistencia
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissssssssiisi",
            $rut_alumno,
            $nombre_alumno,
            $edad,
            $fecha_nacimiento,
            $fecha_ingreso,
            $direccion,
            $rut_apoderado,
            $nombre_apoderado,
            $talla_polera,
            $talla_karategui,
            $cinto_inicial,
            $escuela_id,
            $activo,
            $cinto_actual,
            $total_asistencia
        );
    }

    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "ok" => true,
        "mensaje" => $id > 0 ? "Alumno actualizado correctamente" : "Alumno ingresado correctamente"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar alumno"
    ]);
}

$conn->close();
?>
