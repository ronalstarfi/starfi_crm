<?php
// Recibir datos POST
$telefono = $_POST['telefono'] ?? '';
$nombre_cliente = $_POST['nombre_cliente'] ?? 'Cliente';
$monto_total = $_POST['monto_total'] ?? '0.00';
$asesor_ventas = $_POST['asesor_ventas'] ?? 'Nuestro Asesor';
$correlativo = $_POST['correlativo'] ?? '';

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
    echo json_encode(['status' => 'error', 'message' => 'No active WhatsApp API configured']);
    exit;
}
$api_config = $q->fetch_assoc();
$meta_telefono_id = $api_config['meta_telefono_id'];
$token_meta = $api_config['meta_token'];

// Construir el mensaje formateado
$mensaje = "Hola *{$nombre_cliente}* 👋\n\n";
$mensaje .= "✅ Tu compra ha sido procesada con éxito.\n\n";
$mensaje .= "📄 *Nota de Entrega:* {$correlativo}\n";
$mensaje .= "💰 *Monto Total:* {$monto_total}$\n";
$mensaje .= "👤 *Atendido por:* {$asesor_ventas}\n\n";
$mensaje .= "Gracias por preferirnos. ¡Esperamos verte pronto! 🌟";

// Enviar a Meta API
$url = "https://graph.facebook.com/v20.0/{$meta_telefono_id}/messages";
$data = [
    'messaging_product' => 'whatsapp',
    'recipient_type' => 'individual',
    'to' => $telefono,
    'type' => 'text',
    'text' => [
        'preview_url' => false,
        'body' => $mensaje
    ]
];

$headers = [
    "Authorization: Bearer {$token_meta}",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
curl_close($ch);

echo json_encode(['status' => 'success', 'message' => 'Notification request sent', 'meta_response' => json_decode($result, true)]);
