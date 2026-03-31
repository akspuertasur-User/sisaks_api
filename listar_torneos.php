<?php
header('Content-Type: application/json');
include 'conexion.php';

$sql = "SELECT 
            id, 
            nombre AS nombre_torneo,
            fecha AS fecha_torneo,
            lugar AS ciudad,
            descripcion
        FROM Torneos
        ORDER BY fecha DESC, id DESC";

$resultado = $conn->query($sql);

$torneos = [];

if ($resultado) {
    while ($fila = $resultado->fetch_assoc()) {
        $torneos[] = $fila;
    }

    echo json_encode([
        "ok" => true,
        "torneos" => $torneos
    ]);
} else {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al listar torneos"
    ]);
}

$conn->close();
?>
