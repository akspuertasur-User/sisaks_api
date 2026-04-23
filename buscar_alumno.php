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

$busqueda = trim($_GET['busqueda'] ?? '');

if ($busqueda === '') {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Búsqueda vacía'
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

/**
 * =========================================================
 * FUNCION: obtener detalle de un alumno por ID
 * =========================================================
 */
function obtenerDetalleAlumno($conn, $alumno_id) {
    $sql_alumno = "SELECT 
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
                   WHERE a.id = ?
                   LIMIT 1";

    $stmt_alumno = $conn->prepare($sql_alumno);

    if (!$stmt_alumno) {
        return [
            'ok' => false,
            'mensaje' => 'Error preparando consulta del alumno'
        ];
    }

    $stmt_alumno->bind_param("i", $alumno_id);
    $stmt_alumno->execute();
    $result_alumno = $stmt_alumno->get_result();

    if (!($alumno = $result_alumno->fetch_assoc())) {
        $stmt_alumno->close();
        return [
            'ok' => false,
            'mensaje' => 'Alumno no encontrado'
        ];
    }

    $stmt_alumno->close();

    $torneos = [];
    $examenes = [];

    // =========================
    // TORNEOS
    // =========================
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
                    INNER JOIN torneos t 
                        ON at.torneo_id = t.id
                    LEFT JOIN categorias_torneo c 
                        ON at.categoria_id = c.id
                    LEFT JOIN medallas m 
                        ON at.medalla_id = m.id
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

    // =========================
    // EXAMENES
    // =========================
    $sql_examenes = "SELECT
                        ex.id,
                        ex.fecha_examen,
                        ca.cinto AS cinto_actual,
                        cs.cinto AS cinto_a_subir,
                        ex.situacion,
                        ex.fecha_cambio_cinto,
                        ex.url_imgur
                     FROM examenes ex
                     LEFT JOIN cintos ca
                        ON ex.cinto_actual_id = ca.id
                     LEFT JOIN cintos cs
                        ON ex.cinto_a_subir_id = cs.id
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

    return [
        'ok' => true,
        'modo' => 'detalle',
        'alumno' => $alumno,
        'torneos' => $torneos,
        'examenes' => $examenes
    ];
}

/**
 * =========================================================
 * 1) Primero intentamos por RUT exacto
 * =========================================================
 */
$sql_rut = "SELECT id
            FROM alumnos
            WHERE rut_alumno = ?
            LIMIT 1";

$stmt_rut = $conn->prepare($sql_rut);

if (!$stmt_rut) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error preparando búsqueda por RUT'
    ]);
    $conn->close();
    exit;
}

$stmt_rut->bind_param("s", $busqueda);
$stmt_rut->execute();
$result_rut = $stmt_rut->get_result();

if ($row_rut = $result_rut->fetch_assoc()) {
    $stmt_rut->close();
    $respuesta = obtenerDetalleAlumno($conn, (int)$row_rut['id']);
    echo json_encode($respuesta);
    $conn->close();
    exit;
}

$stmt_rut->close();

/**
 * =========================================================
 * 2) Si no encontró por RUT, buscamos por nombre
 * =========================================================
 */
$sql_nombre = "SELECT 
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
               WHERE a.nombre_alumno LIKE ?
               ORDER BY a.nombre_alumno ASC
               LIMIT 20";

$stmt_nombre = $conn->prepare($sql_nombre);

if (!$stmt_nombre) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error preparando búsqueda por nombre'
    ]);
    $conn->close();
    exit;
}

$nombre_like = '%' . $busqueda . '%';
$stmt_nombre->bind_param("s", $nombre_like);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();

$alumnos = [];
while ($row = $result_nombre->fetch_assoc()) {
    $alumnos[] = $row;
}

$stmt_nombre->close();

if (count($alumnos) === 0) {
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Alumno no encontrado'
    ]);
    $conn->close();
    exit;
}

if (count($alumnos) === 1) {
    $respuesta = obtenerDetalleAlumno($conn, (int)$alumnos[0]['id']);
    echo json_encode($respuesta);
    $conn->close();
    exit;
}

echo json_encode([
    'ok' => true,
    'modo' => 'lista',
    'alumnos' => $alumnos
]);

$conn->close();
