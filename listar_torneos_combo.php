<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$sql = "SELECT id, nombre_torneo, fecha_torneo, ciudad
        FROM torneos
        ORDER BY fecha_torneo DESC, nombre_torneo ASC";

$result = $conn->query($sql);
$torneos = [];

while ($row = $result->fetch_assoc()) {
    $torneos[] = $row;
}

echo json_encode(["ok" => true, "torneos" => $torneos]);
$conn->close();
?>
