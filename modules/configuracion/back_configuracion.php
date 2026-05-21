<?php
require_once __DIR__ . '/../../core/auth.php';
requireAuth();
header('Content-Type: application/json');

$con = getDbConnection();
$action = $_POST['action'] ?? '';
$id_empresa = 1; // Prototipo: asumiendo empresa 1

switch ($action) {
    case 'load_sedes':
        $query = "
            SELECT s.id, s.nombre_sede as sede, l.numero_telefono as numero, l.meta_app_id as app_id, l.estado_conexion as webhook
            FROM sedes s
            LEFT JOIN lineas_whatsapp l ON s.id = l.id_sede
            WHERE s.id_empresa = $id_empresa AND s.estado = 'ACTIVO'
        ";
        $res = $con->query($query);
        $data = [];
        if($res){
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'add_sede':
        $nombre_sede = $_POST['nombre_sede'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $numero = $_POST['numero'] ?? '';
        $app_id = $_POST['app_id'] ?? '';
        
        if (empty($nombre_sede)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre de la sede es obligatorio.']);
            exit;
        }

        $con->begin_transaction();
        try {
            $stmt = $con->prepare("INSERT INTO sedes (id_empresa, nombre_sede, direccion) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $id_empresa, $nombre_sede, $direccion);
            $stmt->execute();
            $sede_id = $stmt->insert_id;

            if (!empty($numero) || !empty($app_id)) {
                $stmt2 = $con->prepare("INSERT INTO lineas_whatsapp (id_sede, numero_telefono, meta_app_id, meta_token, webhook_verify_token) VALUES (?, ?, ?, 'temp_token', 'temp_verify')");
                $stmt2->bind_param("iss", $sede_id, $numero, $app_id);
                $stmt2->execute();
            }

            $con->commit();
            echo json_encode(['status' => 'success', 'message' => 'Sede creada correctamente.']);
        } catch (Exception $e) {
            $con->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Error al crear la sede.']);
        }
        break;

    case 'load_users':
        $query = "
            SELECT u.id, u.nombre_completo as nombre, u.rol, s.nombre_sede as sede, u.limite_chats_simultaneos as limite
            FROM usuarios_agentes u
            LEFT JOIN sedes s ON u.id_sede = s.id
            WHERE u.id_empresa = $id_empresa AND u.estado = 'ACTIVO'
        ";
        $res = $con->query($query);
        $data = [];
        if($res){
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    case 'add_user':
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
        $rol = $_POST['rol'] ?? 'AGENTE';
        $sede_id = intval($_POST['sede_id'] ?? 0);
        $limite = intval($_POST['limite'] ?? 5);

        if (empty($nombre) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Nombre y email son obligatorios.']);
            exit;
        }

        // Si sede_id es 0, lo hacemos null
        $sede_val = $sede_id > 0 ? $sede_id : null;

        $stmt = $con->prepare("INSERT INTO usuarios_agentes (id_empresa, id_sede, nombre_completo, email, password_hash, rol, limite_chats_simultaneos) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iissssi", $id_empresa, $sede_val, $nombre, $email, $password, $rol, $limite);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Operador creado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'El correo electrónico ya está registrado.']);
            }
        }
        break;

    case 'get_sedes_list':
        $res = $con->query("SELECT id, nombre_sede FROM sedes WHERE id_empresa = $id_empresa AND estado = 'ACTIVO'");
        $data = [];
        if($res) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
        break;
}
