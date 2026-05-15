<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
include 'conexion.php';

$categorias = [];
$medallas = [];

$res1 = $conn->query("SELECT id, nombre_categoria FROM categorias_torneo ORDER BY nombre_categoria ASC");
while ($row = $res1->fetch_assoc()) {
    $categorias[] = $row;
}

$res2 = $conn->query("SELECT id, nombre_medalla FROM medallas ORDER BY id ASC");
while ($row = $res2->fetch_assoc()) {
    $medallas[] = $row;
}

echo json_encode([
    "ok" => true,
    "categorias" => $categorias,
    "medallas" => $medallas
]);

$conn->close();
?>
