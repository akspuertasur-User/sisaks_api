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

$escuela_id = isset($_GET['escuela_id']) && $_GET['escuela_id'] !== ''
    ? intval($_GET['escuela_id'])
    : null;

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
            a.fecha_nacimiento,
            a.escuela_id,
            e.nombre_escuela,
            a.activo
        FROM alumnos a
        LEFT JOIN escuela e ON e.id = a.escuela_id
        WHERE a.activo = 1
          AND a.fecha_nacimiento IS NOT NULL";

$params = [];
$types = '';

if ($escuela_id !== null && $escuela_id > 0) {
    $sql .= " AND a.escuela_id = ?";
    $types .= 'i';
    $params[] = $escuela_id;
}

$sql .= " ORDER BY MONTH(a.fecha_nacimiento), DAY(a.fecha_nacimiento), a.nombre_alumno";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error preparando consulta'
    ]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$hoy = new DateTime('today');
$lista = [];

while ($row = $result->fetch_assoc()) {
    $fn = $row['fecha_nacimiento'];
    if (!$fn) {
        continue;
    }

    [$anio, $mes, $dia] = explode('-', $fn);

    $candidatas = [
        new DateTime(($hoy->format('Y') - 1) . "-$mes-$dia"),
        new DateTime($hoy->format('Y') . "-$mes-$dia"),
        new DateTime(($hoy->format('Y') + 1) . "-$mes-$dia"),
    ];

    $mejor = null;
    $mejorDiff = null;

    foreach ($candidatas as $fechaCumple) {
        $diff = (int)$hoy->diff($fechaCumple)->format('%r%a');
        if ($mejor === null || abs($diff) < abs($mejorDiff)) {
            $mejor = $fechaCumple;
            $mejorDiff = $diff;
        }
    }

    if (abs($mejorDiff) <= 7) {
        $row['cumple_recordatorio'] = $mejor->format('Y-m-d');
        $row['dias_diferencia'] = $mejorDiff;
        $lista[] = $row;
    }
}

usort($lista, function ($a, $b) {
    return strcmp($a['cumple_recordatorio'], $b['cumple_recordatorio']);
});

echo json_encode([
    'ok' => true,
    'alumnos' => $lista
]);

$stmt->close();
$conn->close();
