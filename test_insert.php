<?php
require_once('config/database.php');
$con = getDbConnection();
$telefono_cliente = '1234567890';
$perfil = 'Test';
$id_empresa = 1;
$insert_cliente = "INSERT INTO clientes_contactos (id_empresa, numero_whatsapp, nombre) VALUES ($id_empresa, '$telefono_cliente', '$perfil')";
if (mysqli_query($con, $insert_cliente)) {
    echo "Cliente insertado. ID: " . mysqli_insert_id($con) . "\n";
} else {
    echo "Error cliente: " . mysqli_error($con) . "\n";
}
?>
