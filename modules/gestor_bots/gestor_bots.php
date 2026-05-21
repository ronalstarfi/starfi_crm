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
    <title>Gestor de Bots | CRM STARFI</title>
    <link rel="icon" href="../../docs/identidad_visual/logos/isologo.ico" type="image/x-icon">
    <!-- CSS Local de Bootstrap -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/icons/bootstrap-icons/font/bootstrap-icons.min.css">
    <link href="../../assets/css/starfi_theme.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <a href="../gestor_bots/gestor_bots.php" class="nav-item active">
                <i class="fa-solid fa-robot"></i>
                <span class="nav-text">Gestor de Bots</span>
            </a>
            <a href="../configuracion/configuracion.php" class="nav-item">
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
                <h2 class="brand-font mb-1" style="font-weight: 600;">Gestor de Bots</h2>
                <p class="text-muted" style="font-size: 0.9rem;">Configura los flujos y respuestas automáticas</p>
            </div>

            <div class="config-card">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2" style="border-bottom: 1px solid var(--border-color);">
                    <h4 class="config-card-title m-0" style="border: none; padding: 0;"><i class="fa-solid fa-robot text-starfi-primary"></i> Tabla de Respuestas Automáticas</h4>
                    <button class="btn btn-starfi-primary" onclick="openBotModal()">
                        <i class="fa-solid fa-plus me-1"></i> Nueva Respuesta
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-borderless">
                        <thead style="background-color: #F8FAFC; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase;">
                            <tr>
                                <th style="border-radius: 6px 0 0 6px;">Tipo</th>
                                <th>Disparador / Evento</th>
                                <th>Mensaje</th>
                                <th>Estado</th>
                                <th style="border-radius: 0 6px 6px 0; text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="botRulesTable" style="font-size: 0.9rem;">
                            <!-- Dynamic Content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Formulario Bot -->
    <div class="modal fade" id="botModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header" style="border-bottom: 1px solid #f0f0f0;">
                    <h5 class="modal-title fw-bold text-dark" id="botModalTitle">Nueva Respuesta Automática</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="botForm">
                        <input type="hidden" id="ruleId">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Tipo de Regla</label>
                            <select class="form-select bg-light border-0" id="ruleType" required>
                                <option value="EVENTO_SISTEMA">Evento del Sistema (Ej: Bienvenida)</option>
                                <option value="PALABRA_CLAVE">Palabra Clave (Ej: "Precio")</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Disparador / Evento</label>
                            <input type="text" class="form-control bg-light border-0" id="ruleTrigger" placeholder="Ej: SALUDO_NUEVO, precio, ubicacion..." required>
                            <small class="text-muted" style="font-size: 0.75rem;">Para palabras clave, puedes usar comas si hay varias (ej: precio, costo, valor)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Mensaje de Respuesta</label>
                            <textarea class="form-control bg-light border-0" id="ruleMessage" rows="4" required></textarea>
                            <div class="mt-2">
                                <span class="text-muted" style="font-size: 0.75rem;">Variables:</span>
                                <span class="var-tag">{{nombre}}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.85rem;">Estado</label>
                            <select class="form-select bg-light border-0" id="ruleState">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-starfi-primary px-4" onclick="saveBotRule()">Guardar Regla</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Local Bootstrap -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="funciones_gestor_bots.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
