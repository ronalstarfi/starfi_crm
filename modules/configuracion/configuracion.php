<?php
require_once __DIR__ . '/../../core/auth.php';
requireAuth();
$agente = getAgenteInfo();
$nombre_agente = $agente['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | CRM STARFI</title>
    <link rel="icon" href="../../docs/identidad_visual/logos/isologo.ico" type="image/x-icon">
    <!-- CSS Local de Bootstrap -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Bootstrap (Local) -->
    <link rel="stylesheet" href="../../assets/icons/bootstrap-icons/font/bootstrap-icons.min.css">
    <!-- Tema Global STARFI -->
    <link href="../../assets/css/starfi_theme.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/styles.css">
    
    <style>
        .config-container {
            flex: 1;
            padding: 30px;
            background-color: var(--bg-main);
            overflow-y: auto;
        }

        .config-card {
            background-color: var(--bg-surface);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .config-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--starfi-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .form-control {
            font-size: 0.9rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background-color: #F8FAFC;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(232, 91, 20, 0.25);
            background-color: #fff;
        }
        
        .var-tag {
            display: inline-block;
            background-color: #E2E8F0;
            color: var(--text-main);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-family: monospace;
            cursor: pointer;
            margin-right: 5px;
            margin-bottom: 5px;
            transition: background-color 0.2s;
        }
        .var-tag:hover {
            background-color: #CBD5E1;
        }

        .table-config th {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            background-color: #F8FAFC;
        }
        .table-config td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
            padding: 5px;
        }
        .action-btn:hover { color: var(--primary); }
        .action-btn.danger:hover { color: var(--starfi-danger); }
    </style>
</head>
<body>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <img src="../../docs/identidad_visual/logos/isologo.png" alt="STARFI" style="height: 30px;">
                <span>STARFI CRM</span>
            </div>
            <button class="toggle-btn" id="toggleSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="../bandeja/bandeja.php" class="nav-item">
                <i class="fa-solid fa-inbox"></i>
                <span class="nav-text">Bandeja Omnicanal</span>
            </a>
            <a href="../directorio/directorio.php" class="nav-item">
                <i class="fa-solid fa-address-book"></i>
                <span class="nav-text">Directorio 360</span>
            </a>
            <a href="../dashboard/dashboard.php" class="nav-item">
                <i class="fa-solid fa-chart-line"></i>
                <span class="nav-text">Métricas y KPIs</span>
            </a>
            <a href="../gestor_bots/gestor_bots.php" class="nav-item"><i class="fa-solid fa-robot"></i><span class="nav-text">Gestor de Bots</span></a>
            <a href="../configuracion/configuracion.php" class="nav-item active">
                <i class="fa-solid fa-gear"></i>
                <span class="nav-text">Configuración</span>
            </a>
        </nav>
        
                <div class="sidebar-footer">
            <div class="agent-profile" style="display: flex; align-items: center; width: 100%;">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre_agente) ?>&background=EBF4FF&color=1E3A8A" alt="Avatar">
                <div class="agent-info" style="flex-grow: 1;">
                    <span class="agent-name" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100px; display: inline-block;"><?= htmlspecialchars($nombre_agente) ?></span>
                    <span class="agent-status online">En línea</span>
                </div>
                <a href="/starfi_crm/logout.php" class="btn text-danger p-1 m-0" title="Cerrar Sesión" style="font-size: 1.1rem;">
                    <i class="fa-solid fa-power-off"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="config-container">
            <div class="mb-4">
                <h2 class="brand-font mb-1" style="font-weight: 600;">Configuración del Sistema</h2>
                <p class="text-muted" style="font-size: 0.9rem;">Gestión de parámetros, sedes y flujos de automatización</p>
            </div>

            <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="sedes-tab" data-bs-toggle="tab" data-bs-target="#sedes" type="button" role="tab">Sedes e Integración Meta</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button" role="tab">Gestión de Accesos</button>
                </li>
            </ul>

            <div class="tab-content" id="configTabsContent">
                
                <!-- GESTOR DE SEDES E INTEGRACIÓN -->
                <div class="tab-pane fade show active" id="sedes" role="tabpanel">
                    <div class="config-card">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <h4 class="config-card-title border-0 pb-0 mb-0"><i class="fa-brands fa-whatsapp text-success"></i> Líneas y Sedes</h4>
                            <button id="btnAddSede" class="btn btn-sm btn-outline-starfi-dark"><i class="fa-solid fa-plus"></i> Añadir Sede</button>
                        </div>
                        
                        <table class="table table-hover table-config">
                            <thead>
                                <tr>
                                    <th>Sede</th>
                                    <th>Número WhatsApp</th>
                                    <th>Meta App ID</th>
                                    <th>Estado Webhook</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="sedesTableBody">
                                <!-- JS Inject -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- GESTIÓN DE ACCESOS -->
                <div class="tab-pane fade" id="usuarios" role="tabpanel">
                    <div class="config-card">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <h4 class="config-card-title border-0 pb-0 mb-0"><i class="fa-solid fa-users text-starfi-dark"></i> Operadores y Permisos</h4>
                            <button id="btnAddUser" class="btn btn-sm btn-starfi-primary"><i class="fa-solid fa-user-plus"></i> Nuevo Operador</button>
                        </div>

                        <table class="table table-hover table-config">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Rol</th>
                                    <th>Sede Asignada</th>
                                    <th>Límite de Chats</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- JS Inject -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Añadir Sede -->
    <div class="modal fade" id="modalSede" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold text-starfi-dark">Nueva Sede e Integración</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sede</label>
                        <input type="text" class="form-control" id="sedeNombre" placeholder="Ej: Caracas - Principal">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección (Opcional)</label>
                        <input type="text" class="form-control" id="sedeDireccion" placeholder="Ubicación física">
                    </div>
                    <hr>
                    <p class="text-muted fw-bold mb-2" style="font-size: 0.85rem;"><i class="fa-brands fa-whatsapp text-success"></i> Conexión a Meta</p>
                    <div class="mb-3">
                        <label class="form-label">Número de WhatsApp (Opcional)</label>
                        <input type="text" class="form-control" id="sedeNumero" placeholder="Ej: +58 414 1234567">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta App ID (Opcional)</label>
                        <input type="text" class="form-control" id="sedeAppId" placeholder="ID de la App en Facebook Developers">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-starfi-primary" id="btnSaveSede">Guardar Sede</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Operador -->
    <div class="modal fade" id="modalOperador" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title brand-font fw-bold text-starfi-dark">Nuevo Operador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="opNombre" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="opEmail" placeholder="juan@starfi.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña Temporal</label>
                        <input type="text" class="form-control" id="opPass" value="123456" disabled>
                        <small class="text-muted">El usuario podrá cambiarla después.</small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Rol</label>
                            <select class="form-select" id="opRol">
                                <option value="AGENTE">Agente</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="ADMIN">Administrador</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Sede Asignada</label>
                            <select class="form-select" id="opSede">
                                <option value="0">Global (Todas)</option>
                                <!-- Se llenará con JS -->
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Límite de Chats Simultáneos</label>
                        <input type="number" class="form-control" id="opLimite" value="5" min="1" max="20">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-starfi-primary" id="btnSaveOperador">Crear Operador</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Local Bootstrap -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="funciones_configuracion.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>




