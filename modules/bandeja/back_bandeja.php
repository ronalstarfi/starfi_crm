<?php
// modules/bandeja/back_bandeja.php
require_once __DIR__ . '/../../core/auth.php';
requireAuth();
header('Content-Type: application/json');

$con = getDbConnection();
$action = $_POST['action'] ?? '';
$agente_id = intval($_SESSION['agente_id']);

switch ($action) {
    case 'load_chats':
        $filter = $_POST['filter'] ?? 'mis-chats';
        
        $query = "
            SELECT 
                IFNULL(c.id, 0) as id, 
                cl.id as id_cliente, 
                cl.nombre as cliente_nombre, 
                cl.numero_whatsapp, 
                IFNULL(c.estado, 'SIN INICIAR') as estado, 
                IFNULL(c.fecha_inicio, cl.fecha_registro) as fecha_inicio,
                IFNULL(c.mensajes_no_leidos, 0) as no_leidos
            FROM clientes_contactos cl
            LEFT JOIN conversaciones c ON cl.id = c.id_cliente AND c.estado != 'CERRADO'
            WHERE cl.id_empresa = 1
        ";
        
        if ($filter === 'mis-chats') {
            // Solo los que hemos escrito (asignados al agente)
            $query .= " AND c.id_agente = $agente_id";
        } elseif ($filter === 'no-leido') {
            // Solo los que tienen mensajes sin leer
            $query .= " AND c.mensajes_no_leidos > 0";
        } elseif ($filter === 'todos') {
            // Todos los chats (ya sean nuevos o en proceso, ya manejados por el LEFT JOIN)
        }

        $query .= " ORDER BY no_leidos DESC, fecha_inicio DESC";
        $res = $con->query($query);
        
        $chats = [];
        if($res){
            while ($row = $res->fetch_assoc()) {
                $chats[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'data' => $chats]);
        break;

    case 'load_messages':
        $conversacion_id = intval($_POST['conversacion_id'] ?? 0);
        
        $query = "
            SELECT id, tipo, origen, contenido, timestamp 
            FROM mensajes_y_eventos 
            WHERE id_conversacion = ? 
            ORDER BY timestamp ASC
        ";
        $stmt = $con->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $conversacion_id);
            $stmt->execute();
            $res = $stmt->get_result();
            
            // Marcar mensajes como leídos
            $con->query("UPDATE conversaciones SET mensajes_no_leidos = 0 WHERE id = $conversacion_id");
            
            $messages = [];
            while ($row = $res->fetch_assoc()) {
                $messages[] = $row;
            }
            echo json_encode(['status' => 'success', 'data' => $messages]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error preparando consulta.']);
        }
        break;
        
    case 'send_message':
        $conversacion_id = intval($_POST['conversacion_id'] ?? 0);
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        $contenido = trim($_POST['contenido'] ?? '');
        
        if (empty($contenido) || ($conversacion_id <= 0 && $cliente_id <= 0)) {
            echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
            exit;
        }

        $is_new_chat = false;
        // Si no hay conversación activa, la creamos
        if ($conversacion_id <= 0 && $cliente_id > 0) {
            // Buscar la primera línea conectada
            $resLinea = $con->query("SELECT id FROM lineas_whatsapp WHERE estado_conexion = 'CONECTADO' LIMIT 1");
            $id_linea = ($resLinea && $resLinea->num_rows > 0) ? $resLinea->fetch_assoc()['id'] : 1;
            
            $stmt = $con->prepare("INSERT INTO conversaciones (id_linea, id_cliente, id_agente, estado) VALUES (?, ?, ?, 'ATENDIENDO')");
            $stmt->bind_param("iii", $id_linea, $cliente_id, $agente_id);
            $stmt->execute();
            $conversacion_id = $stmt->insert_id;
            $is_new_chat = true;
        } else {
            // Auto-assign to agent if replying to a waiting chat
            $con->query("UPDATE conversaciones SET estado = 'ATENDIENDO', id_agente = $agente_id, fecha_primera_respuesta = IFNULL(fecha_primera_respuesta, NOW()) WHERE id = $conversacion_id AND (id_agente IS NULL OR estado = 'ESPERA_ASIGNACION')");
        }
        
        // -------------------------------------------------------------
        // INICIO BLOQUE ENVÍO A WHATSAPP CLOUD API
        // -------------------------------------------------------------
        $queryChat = "
            SELECT c.id_cliente, cl.numero_whatsapp, l.meta_token, l.meta_app_id as phone_number_id
            FROM conversaciones c
            JOIN clientes_contactos cl ON c.id_cliente = cl.id
            JOIN lineas_whatsapp l ON c.id_linea = l.id
            WHERE c.id = $conversacion_id
        ";
        $resChat = $con->query($queryChat);
        $chat_data = $resChat ? $resChat->fetch_assoc() : null;

        if ($chat_data && !empty($chat_data['meta_token']) && $chat_data['meta_token'] !== 'temp_token') {
            $numero_destino = preg_replace('/[^0-9]/', '', $chat_data['numero_whatsapp']);
            $meta_token = $chat_data['meta_token'];
            $phone_number_id = $chat_data['phone_number_id'];

            $url = "https://graph.facebook.com/v19.0/{$phone_number_id}/messages";
            
            $post_data = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $numero_destino,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $contenido
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $meta_token,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout rápido para no bloquear
            $response = curl_exec($ch);
            curl_close($ch);
            // NOTA: Para un sistema en producción robusto, se debe evaluar $response y registrar si hubo error de Meta.
        }
        // -------------------------------------------------------------
        // FIN BLOQUE ENVÍO
        // -------------------------------------------------------------

        $origen = 'AGENTE';
        $tipo = 'TEXTO';
        
        $query = "INSERT INTO mensajes_y_eventos (id_conversacion, tipo, origen, contenido) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        if ($stmt) {
            $stmt->bind_param("isss", $conversacion_id, $tipo, $origen, $contenido);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message_id' => $stmt->insert_id, 'new_chat_id' => ($is_new_chat ? $conversacion_id : null)]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Fallo al guardar mensaje en BD.']);
            }
        }
        break;

    case 'close_chat':
        $conversacion_id = intval($_POST['conversacion_id'] ?? 0);
        if($conversacion_id > 0) {
            $con->query("UPDATE conversaciones SET estado = 'CERRADO', fecha_resolucion = NOW() WHERE id = $conversacion_id");
            $con->query("INSERT INTO mensajes_y_eventos (id_conversacion, origen, contenido) VALUES ($conversacion_id, 'EVENTO_SISTEMA', 'Conversación cerrada por el operador')");
            echo json_encode(['status' => 'success']);
        }
        break;

    case 'reassign_chat':
        $conversacion_id = intval($_POST['conversacion_id'] ?? 0);
        $nuevo_agente_id = intval($_POST['nuevo_agente_id'] ?? 0);
        if($conversacion_id > 0 && $nuevo_agente_id > 0) {
            $con->query("UPDATE conversaciones SET id_agente = $nuevo_agente_id, estado = 'ESPERA_ASIGNACION' WHERE id = $conversacion_id");
            
            $res = $con->query("SELECT nombre_completo FROM usuarios_agentes WHERE id = $nuevo_agente_id");
            $nombre_agente = $res->fetch_assoc()['nombre_completo'];

            $con->query("INSERT INTO mensajes_y_eventos (id_conversacion, origen, contenido) VALUES ($conversacion_id, 'EVENTO_SISTEMA', 'Conversación reasignada a $nombre_agente')");
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
        }
        break;

    case 'load_profile':
        $cliente_id = intval($_POST['cliente_id'] ?? 0);
        if($cliente_id > 0) {
            $res = $con->query("SELECT * FROM clientes_contactos WHERE id = $cliente_id");
            if($row = $res->fetch_assoc()) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Cliente no encontrado']);
            }
        }
        break;

    case 'get_agents':
        $res = $con->query("SELECT id, nombre_completo FROM usuarios_agentes WHERE estado = 'ACTIVO'");
        $agents = [];
        while($row = $res->fetch_assoc()) {
            $agents[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $agents]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
?>
