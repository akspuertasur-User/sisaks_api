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

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error conexión BD"
    ]);
    exit;
}

$rut = $_POST['rut'] ?? '';
$clave = $_POST['clave'] ?? '';

if ($rut === '' || $clave === '') {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Debe ingresar rut y clave"
    ]);
    exit;
}

$sql = "SELECT id, rut, nombre_usuario, clave, nivel_de_usuario, escuela_id
        FROM usuarios
        WHERE rut = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "RUT no encontrado"
    ]);
    exit;
}

$row = $result->fetch_assoc();

if ($row["clave"] !== $clave) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Clave incorrecta"
    ]);
    exit;
}

unset($row["clave"]);

echo json_encode([
    "ok" => true,
    "usuario" => $row
]);

$stmt->close();
$conn->close();
?>
