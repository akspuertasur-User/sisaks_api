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
            a.created_at,
            a.activo,
            a.total_asistencia
        FROM alumnos a
        LEFT JOIN escuela e ON a.escuela_id = e.id
        WHERE a.rut_alumno = ?
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
