<?php
header('Content-Type: application/json');
include 'conexion.php';

$rut = $_POST['rut'] ?? '';
$clave = $_POST['clave'] ?? '';

if (empty($rut) || empty($clave)) {
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
        "mensaje": "Clave incorrecta"
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
