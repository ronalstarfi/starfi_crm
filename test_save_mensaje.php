<?php
require_once('config/database.php');

function test_save_mensaje($id_mensaje, $telefono_cliente, $timestamp, $cuerpo_mensaje, $perfil, $telefono_receptor_id, $display_phone) {
    
    $con = getDbConnection();
    
    if(!$con) {
        echo "Error de conexión a BD en webhook\n";
        return;
    }
    
    $telefono_cliente = mysqli_real_escape_string($con, $telefono_cliente);
    $cuerpo_mensaje = mysqli_real_escape_string($con, $cuerpo_mensaje);
    $perfil = mysqli_real_escape_string($con, $perfil);
    $telefono_receptor_id = mysqli_real_escape_string($con, $telefono_receptor_id);
    
    $id_linea = null;
    $id_empresa = 1; // Default
    
    if ($telefono_receptor_id) {
        $query_api = "SELECT l.id, s.id_empresa FROM lineas_whatsapp l 
                      LEFT JOIN sedes s ON l.id_sede = s.id 
                      WHERE l.meta_app_id = '$telefono_receptor_id' AND l.estado_conexion = 'CONECTADO' LIMIT 1";
        $result_api = mysqli_query($con, $query_api);
        if ($result_api && mysqli_num_rows($result_api) > 0) {
            $row = mysqli_fetch_assoc($result_api);
            $id_linea = $row['id'];
            if ($row['id_empresa']) $id_empresa = $row['id_empresa'];
        }
    }
    
    if (!$id_linea) {
        $query_api = "SELECT l.id, s.id_empresa FROM lineas_whatsapp l 
                      LEFT JOIN sedes s ON l.id_sede = s.id 
                      WHERE l.estado_conexion = 'CONECTADO' LIMIT 1";
        $result_api = mysqli_query($con, $query_api);
        if ($result_api && mysqli_num_rows($result_api) > 0) {
            $row = mysqli_fetch_assoc($result_api);
            $id_linea = $row['id'];
            if ($row['id_empresa']) $id_empresa = $row['id_empresa'];
        } else {
            $id_linea = 1; 
        }
    }
    
    $id_cliente = null;
    $query_cliente = "SELECT id FROM clientes_contactos WHERE numero_whatsapp = '$telefono_cliente'";
    $res_cliente = mysqli_query($con, $query_cliente);
    if ($res_cliente && mysqli_num_rows($res_cliente) > 0) {
        $id_cliente = mysqli_fetch_assoc($res_cliente)['id'];
    } else {
        $insert_cliente = "INSERT INTO clientes_contactos (id_empresa, numero_whatsapp, nombre) VALUES ($id_empresa, '$telefono_cliente', '$perfil')";
        if (mysqli_query($con, $insert_cliente)) {
            $id_cliente = mysqli_insert_id($con);
        } else {
            echo "Error al insertar cliente: " . mysqli_error($con) . "\n";
        }
    }
    
    if (!$id_cliente) {
        echo "No se pudo obtener ni crear el cliente en el webhook.\n";
        return;
    }
    
    $id_conversacion = null;
    $query_conv = "SELECT id FROM conversaciones WHERE id_cliente = $id_cliente AND estado NOT IN ('CERRADO', 'RESUELTO') LIMIT 1";
    $res_conv = mysqli_query($con, $query_conv);
    if ($res_conv && mysqli_num_rows($res_conv) > 0) {
        $id_conversacion = mysqli_fetch_assoc($res_conv)['id'];
        mysqli_query($con, "UPDATE conversaciones SET mensajes_no_leidos = IFNULL(mensajes_no_leidos, 0) + 1 WHERE id = $id_conversacion");
    } else {
        $insert_conv = "INSERT INTO conversaciones (id_linea, id_cliente, estado, mensajes_no_leidos) VALUES ($id_linea, $id_cliente, 'ESPERA_ASIGNACION', 1)";
        if (mysqli_query($con, $insert_conv)) {
            $id_conversacion = mysqli_insert_id($con);
        } else {
            echo "Error al insertar conversacion: " . mysqli_error($con) . "\n";
        }
    }
    
    if (!$id_conversacion) {
        echo "No se pudo obtener ni crear la conversación en el webhook.\n";
        return;
    }
    
    $query_msg = "INSERT INTO mensajes_y_eventos (id_conversacion, tipo, origen, contenido) VALUES ($id_conversacion, 'TEXTO', 'CLIENTE', '$cuerpo_mensaje')";
    if (!mysqli_query($con, $query_msg)) {
        echo "Error al guardar mensaje en la bd: " . mysqli_error($con) . "\n";
    } else {
        echo "Mensaje guardado correctamente.\n";
    }
}

test_save_mensaje('msg2', '999999999', time(), 'Hola', 'Test User', '123', '123');
?>
