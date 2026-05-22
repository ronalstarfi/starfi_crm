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
    <title>Bandeja Omnicanal | CRM STARFI</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/styles.css">
    <!-- Emoji Picker -->
    <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.0/dist/index.min.js"></script>
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
            <a href="../bandeja/bandeja.php" class="nav-item active"><i class="fa-solid fa-inbox"></i>
                <span class="nav-text">Bandeja Omnicanal</span>
            </a>
            <a href="../directorio/directorio.php" class="nav-item"><i class="fa-solid fa-address-book"></i>
                <span class="nav-text">Directorio 360</span>
            </a>
            <a href="../dashboard/dashboard.php" class="nav-item"><i class="fa-solid fa-chart-line"></i>
                <span class="nav-text">Métricas y KPIs</span>
            </a>
            <a href="../gestor_bots/gestor_bots.php" class="nav-item"><i class="fa-solid fa-robot"></i><span class="nav-text">Gestor de Bots</span></a>
            <a href="../configuracion/configuracion.php" class="nav-item"><i class="fa-solid fa-gear"></i>
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

    <!-- Main Layout -->
    <main class="main-content">
        
        <!-- Chats Panel (Left Column) -->
        <section class="chats-panel">
            <header class="chats-header">
                <h2>Conversaciones</h2>
                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" data-target="mis-chats">Mis Chats</button>
                    <button class="tab" data-target="no-leido">No Leído <span class="badge" id="badgeNoLeidos" style="display:none;">0</span></button>
                    <button class="tab" data-target="todos">Todos</button>
                </div>
                <!-- Search -->
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" placeholder="Buscar cliente, número...">
                </div>
            </header>

            <!-- Chat List -->
            <div class="chat-list" id="chatList">
                <div class="text-center p-4 mt-4">
                    <div class="spinner-border text-secondary mb-2" role="status"></div>
                    <p class="text-muted" style="font-size: 0.85rem;">Conectando al servidor...</p>
                </div>
            </div>
        </section>

        <!-- Active Conversation Panel (Right Column) -->
        <section class="conversation-panel" style="position: relative;">
            
            <!-- Empty State -->
            <div id="emptyState" style="display:flex; flex-direction:column; align-items:center; justify-content:center; width:100%; height:100%;" class="text-muted">
                <i class="fa-solid fa-comments fs-1 mb-3 opacity-50"></i>
                <h5>Bienvenido a tu Bandeja</h5>
                <p>Selecciona una conversación de la lista para comenzar a chatear.</p>
            </div>

            <!-- Active Chat View (Oculto hasta seleccionar) -->
            <div id="activeChatView" style="display:none; flex-direction:column; height:100%; width:100%;">
                <!-- Header -->
                <header class="conv-header">
                    <div class="client-info">
                        <img id="chatHeaderImg" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Avatar" style="background-color: #F3F4F6;">
                        <div>
                            <h3 id="chatHeaderName">...</h3>
                            <span id="chatHeaderPhone">...</span>
                        </div>
                    </div>
                    <div class="conv-actions">
                        <button class="icon-btn" title="Ver Perfil 360" id="btnToggleProfile"><i class="fa-solid fa-id-card"></i></button>
                        <button class="icon-btn" title="Reasignar" id="btnReasign"><i class="fa-solid fa-user-plus"></i></button>
                        <button class="icon-btn" title="Cerrar Chat" id="btnCloseChat"><i class="fa-solid fa-check"></i></button>
                    </div>
                </header>

                <div class="messages-area" id="messagesArea">
                    <!-- Mensajes cargan aquí -->
                </div>

                <!-- Input Area -->
                <footer class="input-area">
                    <div class="tools">
                        <input type="file" id="fileInput" style="display:none;" accept="image/*,application/pdf,video/mp4">
                        <button class="tool-btn" id="btnAttach" title="Adjuntar"><i class="fa-solid fa-paperclip"></i></button>
                        <button class="tool-btn" id="btnTemplates" title="Plantillas"><i class="fa-solid fa-bolt"></i></button>
                        <button class="tool-btn" id="btnEmoji" title="Emoji"><i class="fa-regular fa-face-smile"></i></button>
                    </div>
                    <div class="input-box">
                        <textarea placeholder="Escribe un mensaje..." rows="1" id="chatInput"></textarea>
                        <button class="send-btn" id="sendBtn"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </footer>
            </div>
        </section>

        <!-- Right Sidebar Profile Preview (Hidden by default, shown when clicking ID card) -->
        <aside class="profile-preview" id="profilePreviewPanel">
            <div class="profile-header">
                <h3>Perfil 360</h3>
                <button class="close-profile" id="btnCloseProfile"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="profile-content">
                <div class="profile-card">
                    <img id="profPrevImg" src="https://ui-avatars.com/api/?name=Empresa+X&background=F3F4F6&size=128" alt="Avatar">
                    <h4 id="profPrevName">Empresa Corp. S.A.</h4>
                    <p class="role">Cliente B2B</p>
                </div>
                <div class="profile-details">
                    <div class="detail-group">
                        <label>Teléfono</label>
                        <p id="profPrevPhone">+58 412 9876543</p>
                    </div>
                    <div class="detail-group">
                        <label>Etiquetas</label>
                        <div class="tags">
                            <span class="tag blue">VIP</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

    </main>

    <!-- Modal Plantillas Rápidas -->
    <div class="modal fade" id="modalTemplates" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold">Respuestas Rápidas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <div class="list-group list-group-flush" id="templatesList">
                <button type="button" class="list-group-item list-group-item-action p-3" onclick="selectTemplate('Hola, soy el asesor asignado. ¿En qué puedo ayudarte?')">
                    <strong>Saludo inicial</strong><br>
                    <small class="text-muted">Hola, soy el asesor asignado. ¿En qué puedo ayudarte?</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action p-3" onclick="selectTemplate('Por favor envíame tu número de identificación para verificar en el sistema.')">
                    <strong>Solicitar ID</strong><br>
                    <small class="text-muted">Por favor envíame tu número de identificación...</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action p-3" onclick="selectTemplate('Dame un momento mientras verifico la información. No te retires por favor.')">
                    <strong>Espera un momento</strong><br>
                    <small class="text-muted">Dame un momento mientras verifico la información...</small>
                </button>
                <button type="button" class="list-group-item list-group-item-action p-3" onclick="selectTemplate('Tu solicitud ha sido procesada con éxito. ¡Gracias por contactarnos!')">
                    <strong>Despedida / Éxito</strong><br>
                    <small class="text-muted">Tu solicitud ha sido procesada con éxito...</small>
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container"></div>

    <!-- JavaScript Local Bootstrap -->
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <!-- Dependencias globales para modales (jQuery y SweetAlert2) -->
    <script src="../../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>

    <script src="funciones_bandeja.js?v=<?= time() ?>"></script>
</body>
</html>




