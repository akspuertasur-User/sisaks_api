
<?php
$host = "centerbeam.proxy.rlwy.net";
$port = 30012;
$database = "sisaks_db";
$user = "root";
$password = "yAgbwVRFgBKVOkiTKXWoKvEprFhXdoFe";

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
