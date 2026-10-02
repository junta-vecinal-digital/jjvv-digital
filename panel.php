<?php
session_start();
if(!isset($_SESSION['rol'])){
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
</head>
<body style="font-family: Arial; text-align: center; margin-top: 50px;">
    
    <h1>Bienvenido, <?php echo $_SESSION['nombre']; ?></h1>
    <h2 style="color: #0d6efd;">Tu rol en el sistema es: <strong><?php echo $_SESSION['rol']; ?></strong></h2>
    
    <p>¡El control de acceso basado en roles (RBAC) está funcionando perfectamente!</p>
    
    <a href="logout.php" style="color: red;">Cerrar Sesión</a>
</body>
</html>