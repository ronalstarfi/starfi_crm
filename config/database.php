<?php
// config/database.php
// Archivo central para gestionar la conexión a la base de datos

function getDbConnection()
{
    $host = 'localhost';
    $user = 'root'; // Usuario por defecto de XAMPP
    $pass = 'PARALELEPIPEDO3312';     // Contraseña por defecto vacía en XAMPP local
    $dbname = 'starfi_crm'; // Asegúrate de crear esta BD en PhpMyAdmin y correr el archivo database_schema.sql

    // Crear conexión usando MySQLi
    $con = new mysqli($host, $user, $pass, $dbname);

    // Verificar conexión
    if ($con->connect_error) {
        // En producción, es mejor registrar en un log en lugar de hacer 'die'
        die("Error crítico: No se pudo conectar a la base de datos. " . $con->connect_error);
    }

    // Forzar codificación UTF-8 para evitar problemas con emojis o acentos en WhatsApp
    $con->set_charset("utf8mb4");

    return $con;
}
?>