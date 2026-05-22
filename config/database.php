<?php
// config/database.php
// Archivo central para gestionar la conexión a la base de datos

function getDbConnection()
{
    // Cargar variables de entorno
    $envPath = __DIR__ . '/../.env';
    $env = file_exists($envPath) ? parse_ini_file($envPath) : [];

    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USER'] ?? 'root';
    $pass = $env['DB_PASS'] ?? '';
    $dbname = $env['DB_NAME'] ?? 'starfi_crm';

    // Crear conexión usando MySQLi
    $con = new mysqli($host, $user, $pass, $dbname);

    // Verificar conexión
    if ($con->connect_error) {
        // En producción, es mejor registrar en un log en lugar de hacer 'die'
        error_log("Error crítico: No se pudo conectar a la base de datos. " . $con->connect_error);
        die("Error crítico: No se pudo conectar a la base de datos.");
    }

    // Forzar codificación UTF-8 para evitar problemas con emojis o acentos en WhatsApp
    $con->set_charset("utf8mb4");

    return $con;
}
?>