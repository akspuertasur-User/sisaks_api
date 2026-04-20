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
        'mensaje' => 'Error preparando consulta del alumno'
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    $alumno_id = (int)$row['id'];

    $torneos = [];
    $examenes = [];

    // TORNEOS
    $sql_torneos = "SELECT
                        t.id,
                        t.nombre_torneo,
                        t.fecha_torneo,
                        t.ciudad,
                        c.nombre_categoria AS categoria,
                        m.nombre_medalla AS medalla,
                        at.observacion,
                        at.url_imgur
                    FROM alumno_torneo at
                    INNER JOIN torneos t ON at.torneo_id = t.id
                    LEFT JOIN categorias_torneo c ON at.categoria_id = c.id
                    LEFT JOIN medallas m ON at.medalla_id = m.id
                    WHERE at.alumno_id = ?
                    ORDER BY t.fecha_torneo DESC, t.nombre_torneo ASC";

    $stmt_torneos = $conn->prepare($sql_torneos);

    if ($stmt_torneos) {
        $stmt_torneos->bind_param("i", $alumno_id);
        $stmt_torneos->execute();
        $result_torneos = $stmt_torneos->get_result();

        while ($torneo = $result_torneos->fetch_assoc()) {
            $torneos[] = $torneo;
        }

        $stmt_torneos->close();
    }

    // EXAMENES
    // OJO: cambia nombres de tabla/campos si en tu BD son distintos
    $sql_examenes = "SELECT
                        ex.id,
                        ex.fecha_examen,
                        ex.cinto_actual_id,
                        ex.cinto_a_subir_id,
                        ex.situacion,
                        ex.fecha_cambio_cinto,
                        ex.url_imgur
                     FROM examenes ex
                     WHERE ex.alumno_id = ?
                     ORDER BY ex.fecha_examen DESC, ex.id DESC";

    $stmt_examenes = $conn->prepare($sql_examenes);

    if ($stmt_examenes) {
        $stmt_examenes->bind_param("i", $alumno_id);
        $stmt_examenes->execute();
        $result_examenes = $stmt_examenes->get_result();

        while ($examen = $result_examenes->fetch_assoc()) {
            $examenes[] = $examen;
        }

        $stmt_examenes->close();
    }

    echo json_encode([
        'ok' => true,
        'alumno' => $row,
        'torneos' => $torneos,
        'examenes' => $examenes
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Alumno no encontrado'
    ]);
}

$stmt->close();
$conn->close();
