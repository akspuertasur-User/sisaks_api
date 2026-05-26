<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

include 'conexion.php';

$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$escuela_id = isset($_GET['escuela_id']) ? intval($_GET['escuela_id']) : 0;
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;

if ($usuario_id <= 0 || $escuela_id <= 0 || $anio <= 0) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Parámetros inválidos"
    ]);
    exit;
}

$sql_validar = "SELECT id
                FROM usuario_escuela
                WHERE usuario_id = ?
                  AND escuela_id = ?
                LIMIT 1";

$stmt_validar = $conn->prepare($sql_validar);
$stmt_validar->bind_param("ii", $usuario_id, $escuela_id);
$stmt_validar->execute();
$res_validar = $stmt_validar->get_result();

if (!$res_validar->fetch_assoc()) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El usuario no tiene acceso a esta escuela"
    ]);
    exit;
}

$stmt_validar->close();

$resumen = [];

for ($mes = 1; $mes <= 12; $mes++) {
    $sql = "SELECT
                COUNT(DISTINCT CASE 
                    WHEN m.estado = 'PAGADO' THEN m.alumno_id
                END) AS total_alumnos,
                IFNULL(SUM(
                    CASE 
                        WHEN m.estado = 'PAGADO' THEN m.monto
                        ELSE 0
                    END
                ), 0) AS total_mensualidades
            FROM mensualidades m
            INNER JOIN alumnos a ON a.id = m.alumno_id
            WHERE m.anio = ?
              AND m.mes = ?
              AND m.estado = 'PAGADO'
              AND a.escuela_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $anio, $mes, $escuela_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $resumen[] = [
        "mes" => $mes,
        "total_alumnos" => intval($row["total_alumnos"] ?? 0),
        "total_mensualidades" => floatval($row["total_mensualidades"] ?? 0)
    ];

    $stmt->close();
}

echo json_encode([
    "ok" => true,
    "resumen" => $resumen
]);

$conn->close();
?>
