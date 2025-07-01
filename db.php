<?php
$host = 'localhost';
$db = 'proyectosdb';
$user = 'root'; // Cambia si tienes otro usuario
$pass = '';     // Cambia la contraseña si tienes
 
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>