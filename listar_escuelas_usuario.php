<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;

if ($usuario_id <= 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "usuario_id inválido"
    ]);
    exit;
}

$sql = "SELECT 
            e.id,
            e.nombre_escuela
        FROM usuario_escuela ue
        INNER JOIN escuela e ON e.id = ue.escuela_id
        WHERE ue.usuario_id = ?
        ORDER BY e.nombre_escuela ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$escuelas = [];

while ($row = $result->fetch_assoc()) {
    $escuelas[] = $row;
}

echo json_encode([
    "ok" => true,
    "escuelas" => $escuelas
]);

$stmt->close();
$conn->close();
?>
