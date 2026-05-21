<?php
$respuesta = '{"entry":[{"changes":[{"value":{"messages":[{"from":"5555555","id":"msg999","timestamp":"1600000000","text":{"body":"Prueba_Ahora"}}],"contacts":[{"profile":{"name":"Test"}}],"metadata":{"phone_number_id":"123","display_phone_number":"123"}}]}]}]}';
$respuesta = json_decode($respuesta, true);
$value = $respuesta['entry'][0]['changes'][0]['value'];
$mensaje = $value['messages'][0]['text']['body'] ?? null;
$telefonoCliente = $value['messages'][0]['from'] ?? null;
$id = $value['messages'][0]['id'] ?? null;
$times = $value['messages'][0]['timestamp'] ?? time();
$perfil = $value['contacts'][0]['profile']['name'] ?? 'Usuario';
$telefonoReceptorID = $value['metadata']['phone_number_id'] ?? null;
$displayPhoneNumber = $value['metadata']['display_phone_number'] ?? null;

echo "Calling save_mensaje...\n";

require_once('webhook.php');
save_mensaje($id, $telefonoCliente, $times, $mensaje, $perfil, $telefonoReceptorID, $displayPhoneNumber);

echo "Done.\n";
?>
