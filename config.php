<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'aba_post1.1.0316');

function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Error de conexión a la base de datos: " . $conn->connect_error);
    }
    return $conn;
}
?>