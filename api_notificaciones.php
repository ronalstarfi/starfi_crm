<?php
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
$q = $con->query("SELECT meta_telefono_id, meta_token FROM lineas_whatsapp WHERE estado = 'ACTIVO' LIMIT 1");
if(!$q || $q->num_rows == 0) {
    // Si no hay activa, forzamos usar la de Starfi CRM (Id 1) temporalmente o devolvemos error
    $q_fallback = $con->query("SELECT meta_telefono_id, meta_token FROM lineas_whatsapp LIMIT 1");
    if($q_fallback && $q_fallback->num_rows > 0) {
        $api_config = $q_fallback->fetch_assoc();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No WhatsApp API configured in CRM']);
        exit;
    }
} else {
    $api_config = $q->fetch_assoc();
}

$telefonoID = $api_config['meta_telefono_id'];
$token = $api_config['meta_token'];
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

$response['meta_response'] = $estado;
echo json_encode($response);
?>
