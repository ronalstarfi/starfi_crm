$(document).ready(function() {
    // Cargar datos iniciales desde BD
    loadSedes();
    loadUsers();

    // 1. Añadir Sede
    $('#btnAddSede').on('click', function() {
        $('#sedeNombre').val('');
        $('#sedeDireccion').val('');
        $('#sedeNumero').val('');
        $('#sedeAppId').val('');
        var myModal = new bootstrap.Modal(document.getElementById('modalSede'));
        myModal.show();
    });

    $('#btnSaveSede').on('click', function() {
        const nombre = $('#sedeNombre').val().trim();
        if(!nombre) { Swal.fire('Error', 'El nombre es obligatorio', 'warning'); return; }
        
        $.ajax({
            url: 'back_configuracion.php', type: 'POST', dataType: 'json',
            data: {
                action: 'add_sede',
                nombre_sede: nombre,
                direccion: $('#sedeDireccion').val().trim(),
                numero: $('#sedeNumero').val().trim(),
                app_id: $('#sedeAppId').val().trim()
            },
            success: function(res) {
                if(res.status === 'success'){
                    bootstrap.Modal.getInstance(document.getElementById('modalSede')).hide();
                    Swal.fire('Éxito', res.message, 'success');
                    loadSedes();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    });

    // 2. Nuevo Operador
    $('#btnAddUser').on('click', function() {
        $('#opNombre').val('');
        $('#opEmail').val('');
        $('#opRol').val('AGENTE');
        $('#opLimite').val(5);
        
        // Cargar lista de sedes para el select
        $.ajax({
            url: 'back_configuracion.php', type: 'POST', dataType: 'json',
            data: { action: 'get_sedes_list' },
            success: function(res) {
                if(res.status === 'success'){
                    let select = $('#opSede');
                    select.html('<option value="0">Global (Todas)</option>');
                    res.data.forEach(s => {
                        select.append(`<option value="${s.id}">${s.nombre_sede}</option>`);
                    });
                    var myModal = new bootstrap.Modal(document.getElementById('modalOperador'));
                    myModal.show();
                }
            }
        });
    });

    $('#btnSaveOperador').on('click', function() {
        const nombre = $('#opNombre').val().trim();
        const email = $('#opEmail').val().trim();
        if(!nombre || !email) { Swal.fire('Error', 'Nombre y email son obligatorios', 'warning'); return; }
        
        $.ajax({
            url: 'back_configuracion.php', type: 'POST', dataType: 'json',
            data: {
                action: 'add_user',
                nombre: nombre,
                email: email,
                rol: $('#opRol').val(),
                sede_id: $('#opSede').val(),
                limite: $('#opLimite').val()
            },
            success: function(res) {
                if(res.status === 'success'){
                    bootstrap.Modal.getInstance(document.getElementById('modalOperador')).hide();
                    Swal.fire('Éxito', res.message, 'success');
                    loadUsers();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    });

    // Eventos de botones de acción genéricos
    $('.table-config').on('click', '.action-btn', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Acción Restringida',
            text: 'No tienes permisos de SuperAdmin para modificar este registro.',
            timer: 2000,
            showConfirmButton: false
        });
    });

});

function loadSedes() {
    $.ajax({
        url: 'back_configuracion.php', type: 'POST', dataType: 'json',
        data: { action: 'load_sedes' },
        success: function(res) {
            if(res.status === 'success') {
                let tbody = $('#sedesTableBody');
                tbody.empty();
                if(res.data.length === 0){
                    tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay sedes registradas.</td></tr>');
                    return;
                }
                res.data.forEach(s => {
                    let badge = s.webhook === 'CONECTADO' ? '<span class="badge bg-success">Conectado</span>' : 
                                (s.webhook === 'REQUIERE_VERIFICACION' ? '<span class="badge bg-warning text-dark">Pendiente</span>' : '<span class="badge bg-secondary">Sin Token</span>');
                    let tr = `<tr>
                        <td class="fw-bold" style="padding-left: 24px;">${s.sede}</td>
                        <td class="text-muted">${s.numero || '---'}</td>
                        <td class="text-muted" style="font-family: monospace;">${s.app_id || '---'}</td>
                        <td>${badge}</td>
                        <td style="text-align: right; padding-right: 24px;">
                            <button class="action-btn" title="Editar"><i class="fa-solid fa-pen"></i></button>
                            <button class="action-btn danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>`;
                    tbody.append(tr);
                });
            }
        }
    });
}

function loadUsers() {
    $.ajax({
        url: 'back_configuracion.php', type: 'POST', dataType: 'json',
        data: { action: 'load_users' },
        success: function(res) {
            if(res.status === 'success') {
                let tbody = $('#usersTableBody');
                tbody.empty();
                if(res.data.length === 0){
                    tbody.append('<tr><td colspan="5" class="text-center text-muted">No hay operadores registrados.</td></tr>');
                    return;
                }
                res.data.forEach(u => {
                    let rolBadge = u.rol === 'ADMIN' ? '<span class="badge bg-danger">Admin</span>' : 
                                  (u.rol === 'SUPERVISOR' ? '<span class="badge bg-primary">Supervisor</span>' : '<span class="badge bg-secondary">Agente</span>');
                    let sedeTxt = u.sede || 'Global';
                    let tr = `<tr>
                        <td class="fw-bold" style="padding-left: 24px;"><i class="fa-solid fa-user-circle text-muted me-2"></i> ${u.nombre}</td>
                        <td>${rolBadge}</td>
                        <td class="text-muted">${sedeTxt}</td>
                        <td>${u.limite} chats simultáneos</td>
                        <td style="text-align: right; padding-right: 24px;">
                            <button class="action-btn" title="Editar Permisos"><i class="fa-solid fa-shield-halved"></i></button>
                            <button class="action-btn danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>`;
                    tbody.append(tr);
                });
            }
        }
    });
}

function confirmLogout(event) {
    event.preventDefault();
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: "Tendrás que volver a ingresar tus credenciales.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#E85B14',
        cancelButtonColor: '#94A3B8',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/starfi_crm/logout.php';
        }
    });
}
