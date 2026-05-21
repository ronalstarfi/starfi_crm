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
    <title>Directorio 360 | CRM STARFI</title>
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
        /* Estilos específicos para el Directorio 360 */
        .directory-layout {
            display: flex;
            flex: 1;
            overflow: hidden;
        }
        
        /* Lista General de Directorio */
        .directory-list {
            flex: 1;
            padding: 20px;
            background-color: var(--bg-main);
            overflow-y: auto;
            border-right: 1px solid var(--border-color);
        }

        .directory-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .client-table {
            background-color: var(--bg-surface);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table th {
            background-color: #F8FAFC;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .table tbody tr:hover td {
            background-color: #F8FAFC;
        }

        .table tbody tr.active td {
            background-color: #EFF6FF;
            border-left: 3px solid var(--primary);
        }

        .client-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .client-cell img {
            width: 36px;
            height: 36px;
            border-radius: 6px;
        }

        .client-cell-info h6 {
            margin: 0;
            font-weight: 600;
            color: var(--text-main);
        }

        .client-cell-info small {
            color: var(--text-muted);
        }

        /* Panel Ficha Cliente */
        .client-profile-panel {
            width: 600px;
            background-color: var(--bg-surface);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            box-shadow: -5px 0 15px rgba(0,0,0,0.05);
            z-index: 100;
        }

        .client-profile-panel.open {
            transform: translateX(0);
            position: relative;
        }

        .profile-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background-color: #F8FAFC;
        }

        .profile-body-scroll {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: row; /* 2 Columnas */
        }

        /* Columna Izquierda: Datos */
        .profile-data-col {
            width: 45%;
            padding: 20px;
            border-right: 1px solid var(--border-color);
            background-color: var(--bg-surface);
        }

        .profile-avatar-large {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-avatar-large img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 5px;
            display: block;
            font-weight: 600;
        }

        .form-control-custom {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
            color: var(--text-main);
            background-color: #F8FAFC;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #fff;
        }

        /* Columna Derecha: Timeline */
        .profile-timeline-col {
            width: 55%;
            padding: 20px;
            background-color: var(--bg-main);
            display: flex;
            flex-direction: column;
        }

        .timeline-header {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--starfi-dark);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }

        .timeline-feed {
            flex: 1;
            overflow-y: auto;
            position: relative;
        }

        .timeline-feed::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 2px;
            background-color: var(--border-color);
        }

        .timeline-item {
            position: relative;
            padding-left: 45px;
            margin-bottom: 20px;
        }

        .timeline-icon {
            position: absolute;
            left: 8px;
            top: 0;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.75rem;
            z-index: 2;
            border: 2px solid var(--bg-main);
        }

        .icon-bot { background-color: var(--starfi-dark); }
        .icon-api { background-color: var(--primary); }
        .icon-agent { background-color: var(--sla-green); }

        .timeline-content {
            background-color: var(--bg-surface);
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .timeline-time {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-bottom: 5px;
            display: block;
        }

        .timeline-text {
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.4;
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
            <a href="../directorio/directorio.php" class="nav-item active">
                <i class="fa-solid fa-address-book"></i>
                <span class="nav-text">Directorio 360</span>
            </a>
            <a href="../dashboard/dashboard.php" class="nav-item">
                <i class="fa-solid fa-chart-line"></i>
                <span class="nav-text">Métricas y KPIs</span>
            </a>
            <a href="../gestor_bots/gestor_bots.php" class="nav-item"><i class="fa-solid fa-robot"></i><span class="nav-text">Gestor de Bots</span></a>
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
        <div class="directory-layout">
            
            <!-- Lista General del Directorio -->
            <section class="directory-list">
                <div class="directory-header">
                    <div>
                        <h2 class="brand-font mb-1" style="font-weight: 600;">Directorio de Clientes</h2>
                        <p class="text-muted" style="font-size: 0.9rem;">Gestión centralizada de contactos y prospectos</p>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="search-bar" style="width: 300px;">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="searchClient" placeholder="Buscar por nombre, número o etiqueta...">
                        </div>
                        <button id="btnAddClient" class="btn btn-starfi-primary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user-plus"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>

                <div class="client-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Estado</th>
                                <th>Etiquetas</th>
                                <th>Último Contacto</th>
                            </tr>
                        </thead>
                        <tbody id="clientsTableBody">
                            <!-- JS Inject -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Panel Lateral de Ficha de Cliente (Layout 2 Columnas) -->
            <aside class="client-profile-panel open" id="profilePanel">
                <div class="profile-header-top">
                    <h5 class="mb-0 brand-font fw-bold text-starfi-dark"><i class="fa-solid fa-id-card-clip me-2 text-starfi-primary"></i>Ficha de Cliente 360</h5>
                    <button class="btn btn-sm btn-outline-secondary border-0" onclick="toggleProfilePanel()"><i class="fa-solid fa-xmark fs-5"></i></button>
                </div>
                
                <div class="profile-body-scroll">
                    <!-- Columna Izquierda: Datos -->
                    <div class="profile-data-col">
                        <div class="profile-avatar-large">
                            <img id="profAvatarImg" src="https://ui-avatars.com/api/?name=User&background=E85B14&color=fff" alt="Avatar">
                            <h5 class="brand-font fw-bold mb-0" id="profTitleName">Nombre Cliente</h5>
                            <small class="text-muted" id="profTitleId">ID: CLI-000</small>
                        </div>

                        <div class="form-group">
                            <label>Nombre Comercial / Razón Social</label>
                            <input type="text" id="profName" class="form-control-custom" value="">
                        </div>
                        <div class="form-group mb-3">
                    <label class="form-label">Número de WhatsApp</label>
                    <div class="input-group">
                        <select class="form-select bg-light" id="profPrefix" style="max-width: 100px;">
                            <option value="58414">0414</option>
                            <option value="58424">0424</option>
                            <option value="58412">0412</option>
                            <option value="58416">0416</option>
                            <option value="58426">0426</option>
                        </select>
                        <input type="text" id="profPhone" class="form-control" placeholder="1234567">
                    </div>
                </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <textarea id="profAddress" class="form-control-custom" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Etiquetas (Tags)</label>
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <span class="tag text-muted border border-dashed" style="background: none; cursor: pointer;">+ Añadir</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notas Internas</label>
                            <textarea id="profNotes" class="form-control-custom" rows="3"></textarea>
                        </div>
                        
                        <button id="btnSaveProfile" class="btn btn-starfi-primary w-100 mt-2">Guardar Cambios</button>
                    </div>

                    <!-- Columna Derecha: Línea de Tiempo (Eventos) -->
                    <div class="profile-timeline-col">
                        <div class="timeline-header">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Eventos
                        </div>
                        <div class="timeline-feed" id="profileTimeline">
                            <!-- JS Inject Timeline -->
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </main>

    <!-- JavaScript Local Bootstrap -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery-3.7.1.min.js"></script>
    
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="funciones_directorio.js"></script>
    <script>
        // Toggle Sidebar Nav
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        // Toggle Profile Panel (Fallback or manual)
        function toggleProfilePanel() {
            const panel = document.getElementById('profilePanel');
            panel.classList.toggle('open');
        }
    </script>
</body>
</html>




