<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 0);
error_reporting(0);

$host = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$dbname = 'sisaks_db';
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');

$rut = trim($_GET['rut'] ?? '');

if ($rut === '') {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'RUT vacío'
    ]);
    exit;
}

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error de conexión'
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

$sql = "SELECT id, rut_alumno, nombre_alumno, edad, fecha_nacimiento, fecha_ingreso,
               direccion, rut_apoderado, nombre_apoderado, talla_polera,
               talla_karategui, cinto_inicial, escuela_id, created_at, activo
        FROM alumnos
        WHERE rut_alumno = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error preparando consulta'
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'ok' => true,
        'alumno' => $row
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Alumno no encontrado'
    ]);
}

$stmt->close();
$conn->close();
