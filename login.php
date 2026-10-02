<?php
session_start();
include 'conexion.php';

$correo = $_POST['correo'];
$password = $_POST['password'];

$sql = "SELECT * FROM Persona WHERE correo = '$correo' AND password = '$password'";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $row = $resultado->fetch_assoc();
    $_SESSION['rut'] = $row['rut'];
    $_SESSION['nombre'] = $row['nombre'];
    $_SESSION['rol'] = $row['rol'];
    
    // Si la clave es correcta, lo manda al panel de control
    header("Location: panel.php");
} else {
    echo "<script>alert('Datos incorrectos'); window.location='index.php';</script>";
}
?>