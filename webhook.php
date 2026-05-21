<?php
/*
 * WEBHOOK MEJORADO PARA RECEPCIÓN DE MENSAJES WHATSAPP
 * - Identifica el número receptor (telefono_meta)
 * - Gestiona conversaciones automáticamente
 * - Vincula con sistema de gestión avanzada
 */

/*
 * VERIFICACION DEL WEBHOOK
*/
//TOKEN QUE QUEREMOS PONER 
$token = 'PARALELEPIPEDO3312';
//RETO QUE RECIBIREMOS DE FACEBOOK
$palabraReto = $_GET['hub_challenge'] ?? '';
//TOKEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
$tokenVerificacion = $_GET['hub_verify_token'] ?? '';
//SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
if ($token === $tokenVerificacion) {
    echo $palabraReto;
    exit;
}

/*
 * RECEPCION DE MENSAJES
 */
//LEEMOS LOS DATOS ENVIADOS POR WHATSAPP
$respuesta = file_get_contents("php://input");

// Log para debugging (opcional - descomentar si necesitas debug)
file_put_contents("webhook_log.txt", date('Y-m-d H:i:s') . " - " . $respuesta . "\n", FILE_APPEND);

//CONVERTIMOS EL JSON EN ARRAY DE PHP
$respuesta = json_decode($respuesta, true);

// Verificar que hay datos
if (!$respuesta || !isset($respuesta['entry'][0]['changes'][0]['value'])) {
    exit;
}

$value = $respuesta['entry'][0]['changes'][0]['value'];

// Verificar que hay mensajes
if (!isset($value['messages'][0])) {
    exit;
}

//EXTRAEMOS EL MENSAJE DEL ARRAY
$mensaje = $value['messages'][0]['text']['body'] ?? null;
//EXTRAEMOS EL TELEFONO DEL CLIENTE
$telefonoCliente = $value['messages'][0]['from'] ?? null;
//EXTRAEMOS EL ID DE WHATSAPP DEL ARRAY
$id = $value['messages'][0]['id'] ?? null;
//EXTRAEMOS EL TIEMPO DE WHATSAPP DEL ARRAY
$times = $value['messages'][0]['timestamp'] ?? time();
//EXTRAEMOS EL NOMBRE DE LA PERSONA QUE TENGA REGISTRADO EN EL PERFIL 
$perfil = $value['contacts'][0]['profile']['name'] ?? 'Usuario';

// NUEVO: Extraer el número de teléfono que recibió el mensaje (telefono_meta)
$telefonoReceptorID = $value['metadata']['phone_number_id'] ?? null;
$displayPhoneNumber = $value['metadata']['display_phone_number'] ?? null;

//SI HAY UN MENSAJE
if($mensaje != null && $telefonoCliente != null){
    save_mensaje($id, $telefonoCliente, $times, $mensaje, $perfil, $telefonoReceptorID, $displayPhoneNumber);
}

/**
 * Guardar mensaje recibido y gestionar conversación
 */
function save_mensaje($id_mensaje, $telefono_cliente, $timestamp, $cuerpo_mensaje, $perfil, $telefono_receptor_id, $display_phone) {
    
    // Incluir conexión a la base de datos 
    require_once('config/database.php');
    $con = getDbConnection();
    
    if(!$con) {
        error_log("Error de conexión a BD en webhook");
        return;
    }
    
    // Escapar datos para evitar SQL injection
    $telefono_cliente = mysqli_real_escape_string($con, $telefono_cliente);
    $cuerpo_mensaje = mysqli_real_escape_string($con, $cuerpo_mensaje);
    $perfil = mysqli_real_escape_string($con, $perfil);
    $telefono_receptor_id = mysqli_real_escape_string($con, $telefono_receptor_id);
    
    // 1. BUSCAR LA LINEA CORRESPONDIENTE AL NÚMERO RECEPTOR
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
    
    // Fallback a cualquier línea activa si no se encuentra
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
            $id_linea = 1; // Fallback definitivo
        }
    }
    
    // 2. BUSCAR O CREAR CLIENTE
    $id_cliente = null;
    $query_cliente = "SELECT id FROM clientes_contactos WHERE numero_whatsapp = '$telefono_cliente'";
    $res_cliente = mysqli_query($con, $query_cliente);
    if ($res_cliente && mysqli_num_rows($res_cliente) > 0) {
        $id_cliente = mysqli_fetch_assoc($res_cliente)['id'];
    } else {
        $insert_cliente = "INSERT INTO clientes_contactos (id_empresa, numero_whatsapp, nombre) VALUES ($id_empresa, '$telefono_cliente', '$perfil')";
        if (mysqli_query($con, $insert_cliente)) {
            $id_cliente = mysqli_insert_id($con);
        }
    }
    
    if (!$id_cliente) {
        error_log("No se pudo obtener ni crear el cliente en el webhook.");
        return;
    }
    
    // 3. BUSCAR O CREAR CONVERSACION
    $id_conversacion = null;
    $query_conv = "SELECT id FROM conversaciones WHERE id_cliente = $id_cliente AND estado NOT IN ('CERRADO', 'RESUELTO') LIMIT 1";
    $res_conv = mysqli_query($con, $query_conv);
    if ($res_conv && mysqli_num_rows($res_conv) > 0) {
        $id_conversacion = mysqli_fetch_assoc($res_conv)['id'];
        // Incrementar mensajes no leídos
        mysqli_query($con, "UPDATE conversaciones SET mensajes_no_leidos = IFNULL(mensajes_no_leidos, 0) + 1 WHERE id = $id_conversacion");
    } else {
        $insert_conv = "INSERT INTO conversaciones (id_linea, id_cliente, estado, mensajes_no_leidos) VALUES ($id_linea, $id_cliente, 'ESPERA_ASIGNACION', 1)";
        if (mysqli_query($con, $insert_conv)) {
            $id_conversacion = mysqli_insert_id($con);
        }
    }
    
    if (!$id_conversacion) {
        error_log("No se pudo obtener ni crear la conversación en el webhook.");
        return;
    }
    
    // 4. INSERTAR MENSAJE RECIBIDO
    $query_msg = "INSERT INTO mensajes_y_eventos (id_conversacion, tipo, origen, contenido) VALUES ($id_conversacion, 'TEXTO', 'CLIENTE', '$cuerpo_mensaje')";
    if (!mysqli_query($con, $query_msg)) {
        error_log("Error al guardar mensaje en la bd: " . mysqli_error($con));
    }
    
    // 5. ENVIAR RESPUESTA AUTOMÁTICA Y CONTACTOS
    if ($id_linea) {
        $q_token = mysqli_query($con, "SELECT meta_app_id, meta_token, id_sede FROM lineas_whatsapp WHERE id = $id_linea");
        if($q_token && mysqli_num_rows($q_token) > 0) {
            $linea_info = mysqli_fetch_assoc($q_token);
            enviar_respuesta_automatica($con, $linea_info, $telefono_cliente, $perfil, $id_conversacion);
        }
    }
}

/**
 * Enviar respuesta automática indicando que este canal no es para atención directa
 */
function enviar_respuesta_automatica($con, $linea_info, $telefono_cliente, $perfil, $id_conversacion) {
    
    $telefonoID = $linea_info['meta_app_id'];
    $token_seguro = $linea_info['meta_token'];
    
    if(empty($telefonoID) || empty($token_seguro)) {
        return; // No se puede enviar sin token/id
    }
    
    $enviado = 'Estimado/a ' . $perfil . ': Este canal no se encuentra habilitado para atención directa. Para recibir asistencia personalizada, le invitamos a contactar directamente a uno de nuestros asesores:';
    
    // URL para enviar mensaje
    $url = 'https://graph.facebook.com/v23.0/' . $telefonoID . '/messages';
    
    // Configuración del mensaje
    $mensaje_enviar = json_encode([
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $telefono_cliente,
        'type' => 'text',
        'text' => [
            'preview_url' => false,
            'body' => $enviado
        ]
    ]);
    
    // Declarar cabeceras
    $header = [
        "Authorization: Bearer " . $token_seguro,
        "Content-Type: application/json"
    ];
    
    // Iniciar CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje_enviar);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    // Obtener respuesta
    $response = curl_exec($curl);
    $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    // Guardar mensaje enviado en BD (Mismo formato de starfi_crm)
    if ($status_code == 200) {
        $enviado_esc = mysqli_real_escape_string($con, $enviado);
        mysqli_query($con, "INSERT INTO mensajes_y_eventos (id_conversacion, tipo, origen, contenido) VALUES ($id_conversacion, 'TEXTO', 'BOT', '$enviado_esc')");
        
        $id_sede = $linea_info['id_sede'] ?? null;
        enviar_contactos_asesores($telefonoID, $token_seguro, $telefono_cliente, $id_sede, $con, $id_conversacion);
    } else {
        error_log("Error al enviar respuesta automatica: " . $response);
    }
}

/**
 * Enviar contactos de asesores (fallback lista)
 */
function enviar_contactos_asesores($telefonoID, $token_seguro, $telefono_cliente, $id_sede, $con, $id_conversacion) {
    
    // Usar la lista por defecto del API_STARFI_WSAP
    $asesores = [
        ['nombre' => 'Ceny Landaeta', 'telefono' => '+584126127873'],
        ['nombre' => 'Gabriel Benitez', 'telefono' => '+584242907452'],
        ['nombre' => 'Junior Sosa', 'telefono' => '+584123695820'],
        ['nombre' => 'Luis Albarran', 'telefono' => '+584242988805'],
        ['nombre' => 'Yorman Cardozo', 'telefono' => '+584127029710'],
        ['nombre' => 'Marcos Santo Domingo', 'telefono' => '+584122949976'],
        ['nombre' => 'Miguel Strocchia', 'telefono' => '+584126862683']
    ];
    
    $url = 'https://graph.facebook.com/v23.0/' . $telefonoID . '/messages';
    
    foreach ($asesores as $asesor) {
        $mensaje_contacto = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $telefono_cliente,
            'type' => 'contacts',
            'contacts' => [
                [
                    'name' => [
                        'formatted_name' => $asesor['nombre'] . ' SuperFormica',
                        'first_name' => $asesor['nombre'],
                        'last_name' => 'SuperFormica'
                    ],
                    'phones' => [
                        [
                            'phone' => $asesor['telefono'],
                            'type' => 'CELL'
                        ]
                    ]
                ]
            ]
        ]);
        
        $header = [
            "Authorization: Bearer " . $token_seguro,
            "Content-Type: application/json"
        ];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $mensaje_contacto);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl);
        
        // Registrar en CRM que se enviaron los contactos
        $cont_str = mysqli_real_escape_string($con, "Contacto enviado: " . $asesor['nombre'] . " (" . $asesor['telefono'] . ")");
        mysqli_query($con, "INSERT INTO mensajes_y_eventos (id_conversacion, tipo, origen, contenido) VALUES ($id_conversacion, 'TEXTO', 'BOT', '$cont_str')");
        
        // Pequeña pausa entre envíos
        usleep(500000); // 0.5 segundos
    }
}
?>
