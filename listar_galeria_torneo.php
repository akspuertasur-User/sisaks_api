<?php
header('Content-Type: application/json');
include 'conexion.php';

$torneo_id = isset($_GET['torneo_id']) ? intval($_GET['torneo_id']) : 0;

$sql = "SELECT id, titulo, url_imagen
        FROM galeria_torneo
        WHERE torneo_id = $torneo_id
        ORDER BY id DESC";

$resultado = $conn->query($sql);

$fotos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $fotos[] = $fila;
    }

    echo json_encode([
        "ok" => true,
        "fotos" => $fotos
    ]);
} else {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al listar galería"
    ]);
}
?>
