<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Recibir datos POST
$telefono = $_POST['telefono'] ?? '';
$telefono_asesor = $_POST['telefono_asesor'] ?? '0000000000';
$nombre_cliente = $_POST['nombre_cliente'] ?? 'Cliente';
$monto_total = $_POST['monto_total'] ?? '0.00';
$asesor_ventas = $_POST['asesor_ventas'] ?? 'Nuestro Asesor';
$correlativo = $_POST['correlativo'] ?? '';
$nombre_empresa = $_POST['nombre_empresa'] ?? 'Nuestra Empresa';
$fecha_compra = date('Y-m-d');
$hora_actual = date('H:i:s');
$signo = 'USD'; // o EUR según configuración

if(empty($telefono)) {
    echo json_encode(['status' => 'error', 'message' => 'No phone number provided']);
    exit;
}

// Limpiar el teléfono para la API (quitar +, espacios, guiones)
$telefono = str_replace(['+', ' ', '-'], '', $telefono);

// Configuración de la base de datos de CRM
require_once __DIR__ . '/config/database.php';
$con = getDbConnection();

// Buscar línea activa de WhatsApp
$q = $con->query("SELECT l.id as id_linea, l.meta_telefono_id, l.meta_token, s.id_empresa FROM lineas_whatsapp l JOIN sedes s ON l.id_sede = s.id WHERE l.estado = 'ACTIVO' LIMIT 1");

$id_linea = 0;
$id_empresa = 1; // Default

if($q && $q->num_rows > 0) {
    $api_config = $q->fetch_assoc();
    $telefonoID = $api_config['meta_telefono_id'];
    $token = $api_config['meta_token'];
    $id_linea = $api_config['id_linea'];
    $id_empresa = $api_config['id_empresa'];
} else {
    // Usar credenciales maestras proporcionadas por el usuario si no hay linea activa
    $telefonoID = '816293698235653';
    $token = 'EAAqFHnS2hXABRrXrybUYq1Sf8wBBmnkaUZCP2XGkNesq3yv1Vwuk8JGXZBEmLqHE9fNE1Q3ZACcHGqCkqcdEUAGDKsu924eQXx3mZC5A9bftbXiceDlJ1ZC3x48o5F0LXUahgMlKpfHE5oLSgcIUdZBqrKcOxz2ZAFc1lD3CdrLfGNDpajiPonBr26gE2lZBQjBg3AZDZD';
    
    $q_fb = $con->query("SELECT l.id as id_linea, s.id_empresa FROM lineas_whatsapp l JOIN sedes s ON l.id_sede = s.id LIMIT 1");
    if($q_fb && $q_fb->num_rows > 0) {
        $fb_config = $q_fb->fetch_assoc();
        $id_linea = $fb_config['id_linea'];
        $id_empresa = $fb_config['id_empresa'];
    }
}

$url = 'https://graph.facebook.com/v23.0/'.$telefonoID.'/messages';
$header = array("Authorization: Bearer " . $token, "Content-Type: application/json");

// CONFIGURACION DEL MENSAJE (Plantilla confirmacion_de_compra_cn)
$mensaje ='{"messaging_product": "whatsapp", 
            "to": "'.$telefono.'", 
            "type": "template", 
            "template": { 
               "namespace": "be78987b_011f_4891_8e13_cd03b764fb99", 
               "name": "confirmacion_de_compra_cn", 
               "language": { 
                   "code": "es",
                   "policy": "deterministic" 
                   },
                    "components": [{
    "type": "body",
    "parameters": [
        { "type": "text", "text": "'.$nombre_cliente.'" },
        { "type": "text", "text": "'.$fecha_compra.'" },
        { "type": "text", "text": "'.$monto_total.' '.$signo.'" },
        { "type": "text", "text": "'.$asesor_ventas.'" },
        { "type": "text", "text": "'.$nombre_empresa.'" },
        { "type": "text", "text": "'.$correlativo.'" },
        { "type": "text", "text": "'.$telefono_asesor.'" }
            ]
}]
        
                }
        }';

// INICIAMOS EL CURL
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$estado = json_decode(curl_exec($curl), true);
$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$response = [];

if($status_code == 200) {
    $response['status_envio'] = "[EXITOSO]";
    $status_bd = 'ENVIADO';
} else {
    $response['status_envio'] = "[ERROR]";
    $status_bd = 'ERROR';
}

// Registrar en base de datos
$telefono_emisor = $telefonoID;   
$telefono_receptor = $telefono;
$contenido = 'Confirmacion de compra, cliente: '.$nombre_cliente.', fecha compra: '.$fecha_compra.', monto total: '.$monto_total.' '.$signo.', asesor de ventas: '.$asesor_ventas.', empresa: '.$nombre_empresa.', correlativo: '.$correlativo.', contacto: '.$telefono_asesor;

$insertar = $con->query("INSERT INTO notificacion_enviada (template, fecha, hora, telefono_emisor, telefono_receptor, contenido, status) 
                         VALUES ('confirmacion_de_compra_cn', '$fecha_compra', '$hora_actual', '$telefono_emisor', '$telefono_receptor', '$contenido', '$status_bd')");

if($insertar) {
    $response['registro_bd'] = "[EXITOSO]";
} else {
    $response['registro_bd'] = "[ERROR]";
}

// Vinculacion con el inbox nativo del CRM (mensajes_y_eventos y conversaciones)
if($id_linea > 0) {
    // 1. Buscar o crear cliente
    $q_cliente = $con->query("SELECT id FROM clientes_contactos WHERE id_empresa = $id_empresa AND numero_whatsapp = '$telefono'");
    if($q_cliente && $q_cliente->num_rows > 0) {
        $id_cliente = $q_cliente->fetch_assoc()['id'];
        $con->query("UPDATE clientes_contactos SET ultimo_contacto = '$fecha_compra $hora_actual' WHERE id = $id_cliente");
    } else {
        $nombre_cli_esc = $con->real_escape_string($nombre_cliente);
        $con->query("INSERT INTO clientes_contactos (id_empresa, numero_whatsapp, nombre, fecha_registro, ultimo_contacto) VALUES ($id_empresa, '$telefono', '$nombre_cli_esc', '$fecha_compra $hora_actual', '$fecha_compra $hora_actual')");
        $id_cliente = $con->insert_id;
    }

    // 2. Buscar o crear conversación activa
    $q_conv = $con->query("SELECT id FROM conversaciones WHERE id_linea = $id_linea AND id_cliente = $id_cliente AND estado != 'CERRADO' ORDER BY id DESC LIMIT 1");
    if($q_conv && $q_conv->num_rows > 0) {
        $id_conversacion = $q_conv->fetch_assoc()['id'];
    } else {
        $con->query("INSERT INTO conversaciones (id_linea, id_cliente, estado, fecha_inicio) VALUES ($id_linea, $id_cliente, 'RESUELTO', '$fecha_compra $hora_actual')");
        $id_conversacion = $con->insert_id;
    }

    // 3. Insertar mensaje en el chat
    $id_mensaje_meta = "";
    if(isset($estado['messages'][0]['id'])) {
        $id_mensaje_meta = $estado['messages'][0]['id'];
    }
    
    $contenido_esc = $con->real_escape_string($contenido);
    $estado_envio = ($status_code == 200) ? 'ENVIADO' : 'FALLIDO';
    
    $con->query("INSERT INTO mensajes_y_eventos (id_conversacion, id_mensaje_meta, tipo, origen, contenido, estado_envio, timestamp) 
                 VALUES ($id_conversacion, '$id_mensaje_meta', 'EVENTO_SISTEMA', 'API_TRANSACCIONAL', '$contenido_esc', '$estado_envio', '$fecha_compra $hora_actual')");
}

$response['meta_response'] = $estado;
echo json_encode($response);
?>
