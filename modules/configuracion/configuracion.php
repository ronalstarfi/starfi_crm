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

        /* Estilos Premium Pestañas y Modales */
        .nav-tabs .nav-link {
            border: none;
            color: #64748B;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px 10px 0 0;
            transition: all 0.3s ease;
        }
        .nav-tabs .nav-link:hover {
            color: var(--starfi-dark);
            background-color: #F8FAFC;
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background-color: transparent;
            border-bottom: 3px solid var(--primary);
        }
        .modal-content-premium {
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        .modal-header-premium {
            padding: 20px 30px;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            background-color: #F8FAFC;
            border-bottom: none;
        }
        .form-control-premium, .form-select-premium {
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.2s;
            background-color: #F8FAFC;
        }
        .form-control-premium:focus, .form-select-premium:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(232, 91, 20, 0.1);
            background-color: #ffffff;
        }
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
                <a href="#" onclick="confirmLogout(event)" class="btn text-danger p-1 m-0" title="Cerrar Sesión" style="font-size: 1.1rem;">
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
                    <div class="config-card" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.04);">
                            <h4 class="config-card-title border-0 pb-0 mb-0"><i class="fa-brands fa-whatsapp text-success me-2"></i> Líneas y Sedes</h4>
                            <button id="btnAddSede" class="btn btn-starfi-primary" style="border-radius: 30px; font-weight: 600; padding: 8px 20px; box-shadow: 0 4px 12px rgba(232, 91, 20, 0.25);">
                                <i class="fa-solid fa-plus me-1"></i> Añadir Sede
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-config mb-0 align-middle table-borderless">
                                <thead style="background-color: #F8FAFC;">
                                    <tr>
                                        <th style="padding-left: 24px;">Sede</th>
                                        <th>Número WhatsApp</th>
                                        <th>Meta App ID</th>
                                        <th>Estado Webhook</th>
                                        <th style="text-align: right; padding-right: 24px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="sedesTableBody">
                                    <!-- JS Inject -->
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">Cargando sedes...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- GESTIÓN DE ACCESOS -->
                <div class="tab-pane fade" id="usuarios" role="tabpanel">
                    <div class="config-card" style="padding: 0;">
                        <div class="d-flex justify-content-between align-items-center" style="padding: 20px 24px; border-bottom: 1px solid rgba(0,0,0,0.04);">
                            <h4 class="config-card-title border-0 pb-0 mb-0"><i class="fa-solid fa-users text-starfi-dark me-2"></i> Operadores y Permisos</h4>
                            <button id="btnAddUser" class="btn btn-starfi-primary" style="border-radius: 30px; font-weight: 600; padding: 8px 20px; box-shadow: 0 4px 12px rgba(232, 91, 20, 0.25);">
                                <i class="fa-solid fa-user-plus me-1"></i> Nuevo Operador
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-config mb-0 align-middle table-borderless">
                                <thead style="background-color: #F8FAFC;">
                                    <tr>
                                        <th style="padding-left: 24px;">Usuario</th>
                                        <th>Rol</th>
                                        <th>Sede Asignada</th>
                                        <th>Límite de Chats</th>
                                        <th style="text-align: right; padding-right: 24px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <!-- JS Inject -->
                                    <tr>
                                        <td colspan="5" class="text-center text-muted p-4">Cargando usuarios...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Añadir Sede -->
    <div class="modal fade" id="modalSede" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-premium">
                <div class="modal-header modal-header-premium">
                    <h5 class="modal-title brand-font fw-bold text-starfi-dark mb-0"><i class="fa-solid fa-building me-2 text-starfi-primary"></i>Nueva Sede e Integración</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nombre de la Sede</label>
                        <input type="text" class="form-control form-control-premium" id="sedeNombre" placeholder="Ej: Caracas - Principal">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Dirección (Opcional)</label>
                        <input type="text" class="form-control form-control-premium" id="sedeDireccion" placeholder="Ubicación física">
                    </div>
                    
                    <div class="p-3 mb-2" style="background-color: #F0FDF4; border-radius: 12px; border: 1px dashed #BBF7D0;">
                        <p class="text-success fw-bold mb-3" style="font-size: 0.85rem;"><i class="fa-brands fa-whatsapp me-1"></i> Conexión a Meta</p>
                        <div class="mb-3">
                            <label class="form-label text-success fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Número de WhatsApp (Opcional)</label>
                            <input type="text" class="form-control form-control-premium" id="sedeNumero" style="background-color: #ffffff; border-color: #BBF7D0;" placeholder="Ej: +58 414 1234567">
                        </div>
                        <div class="mb-1">
                            <label class="form-label text-success fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Meta App ID (Opcional)</label>
                            <input type="text" class="form-control form-control-premium" id="sedeAppId" style="background-color: #ffffff; border-color: #BBF7D0;" placeholder="ID de la App en Facebook Developers">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" style="border-radius: 10px; padding: 10px 20px;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-starfi-primary fw-bold shadow-sm" style="border-radius: 10px; padding: 10px 20px;" id="btnSaveSede">Guardar Sede</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Operador -->
    <div class="modal fade" id="modalOperador" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-premium">
                <div class="modal-header modal-header-premium">
                    <h5 class="modal-title brand-font fw-bold text-starfi-dark mb-0"><i class="fa-solid fa-user-tie me-2 text-starfi-primary"></i>Nuevo Operador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nombre Completo</label>
                        <input type="text" class="form-control form-control-premium" id="opNombre" placeholder="Ej: Juan Pérez">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Correo Electrónico</label>
                        <input type="email" class="form-control form-control-premium" id="opEmail" placeholder="juan@starfi.com">
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Rol de Acceso</label>
                            <select class="form-select form-select-premium" id="opRol">
                                <option value="AGENTE">Agente</option>
                                <option value="SUPERVISOR">Supervisor</option>
                                <option value="ADMIN">Administrador</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Sede Asignada</label>
                            <select class="form-select form-select-premium" id="opSede">
                                <option value="0">Global (Todas)</option>
                                <!-- Se llenará con JS -->
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Contraseña Temporal</label>
                        <input type="text" class="form-control form-control-premium" id="opPass" value="123456" disabled>
                        <small class="text-muted mt-1 d-block"><i class="fa-solid fa-circle-info me-1"></i>El usuario podrá cambiarla después.</small>
                    </div>

                    <div class="mb-2 p-3" style="background-color: #F8FAFC; border-radius: 12px; border: 1px solid #E2E8F0;">
                        <label class="form-label text-starfi-dark fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Límite de Chats Simultáneos</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-control-range flex-grow-1" id="opLimite" min="1" max="20" value="5" oninput="document.getElementById('opLimiteNum').innerText = this.value">
                            <span id="opLimiteNum" class="badge bg-primary fs-6 px-3 py-2 rounded-pill">5</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" style="border-radius: 10px; padding: 10px 20px;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-starfi-primary fw-bold shadow-sm" style="border-radius: 10px; padding: 10px 20px;" id="btnSaveOperador">Crear Operador</button>
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




