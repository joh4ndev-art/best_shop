<?php
$host = 'localhost';
$user = 'root';        
$password = '';        
$database = 'nombre_de_tu_base_de_datos';        

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
