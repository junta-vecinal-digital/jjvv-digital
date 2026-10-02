<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "junta_vecinal_db";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>