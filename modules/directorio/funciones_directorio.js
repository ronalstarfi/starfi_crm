// modules/directorio/funciones_directorio.js

let currentClientId = null;

$(document).ready(function() {
    loadClients();

    // Botón Nuevo Cliente
    $('#btnAddClient').on('click', function() {
        currentClientId = null; // Modo creación
        
        // UI form reset
        $('#profTitleId').text('NUEVO CLIENTE');
        $('#profTitleName').text('Crear Ficha');
        $('#profAvatarImg').attr('src', 'https://ui-avatars.com/api/?name=Nuevo&background=E85B14&color=fff');
        
        $('#profName').val('');
        $('#profPrefix').prop('disabled', false).val('58414');
        $('#profPhone').prop('disabled', false).val('');
        $('#profAddress').val('');
        $('#profNotes').val('');
        
        $('#btnSaveProfile').text('Crear Cliente');
        
        // Empty timeline
        $('#profileTimeline').html('<div class="text-muted text-center mt-3" style="font-size:0.85rem;"><i class="fa-solid fa-clock-rotate-left fs-4 mb-2 d-block"></i> Historial vacío.</div>');
        
        // Abrir panel
        $('#profilePanel').addClass('open');
    });

    $('#btnSaveProfile').on('click', function() {
        saveProfile();
    });
});

function loadClients() {
    $.ajax({
        url: 'back_directorio.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'load_clients' },
        success: function(res) {
            if (res.status === 'success') {
                renderTable(res.data);
            }
        }
    });
}

function renderTable(clients) {
    let tbody = $('#clientsTableBody');
    tbody.empty();
    
    if(clients.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted p-4">No hay contactos registrados.</td></tr>');
        return;
    }

    clients.forEach(c => {
        let statusBadge = c.estado === 'ACTIVO' ? 
            '<span class="badge bg-success bg-opacity-10 text-success border border-success">Activo</span>' : 
            '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Inactivo</span>';
        
        let dateObj = new Date(c.fecha_registro);
        let formattedDate = dateObj.toLocaleDateString();

        let tr = `
            <tr onclick="loadProfile(${c.id}, this)">
                <td>
                    <div class="client-cell">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.nombre)}&background=F3F4F6&color=37414A" alt="Avatar">
                        <div class="client-cell-info">
                            <h6>${c.nombre}</h6>
                            <small>ID: CLI-${c.id}</small>
                        </div>
                    </div>
                </td>
                <td>+${c.numero_whatsapp}</td>
                <td>${statusBadge}</td>
                <td><span class="tag text-muted">Sin tags</span></td>
                <td class="text-muted">${formattedDate}</td>
            </tr>
        `;
        tbody.append(tr);
    });
}

function loadProfile(id, rowElement) {
    currentClientId = id;
    
    // UI active state
    $('#clientsTableBody tr').removeClass('active');
    if (rowElement) $(rowElement).addClass('active');

    $.ajax({
        url: 'back_directorio.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'load_profile', id: id },
        success: function(res) {
            if (res.status === 'success') {
                const client = res.data.client;
                
                // Update UI Avatar & Title
                $('#profTitleName').text(client.nombre);
                $('#profTitleId').text(`ID: CLI-${client.id}`);
                $('#profAvatarImg').attr('src', `https://ui-avatars.com/api/?name=${encodeURIComponent(client.nombre)}&background=E85B14&color=fff`);

                // Update Form Fields
                $('#profName').val(client.nombre);
                $('#profAddress').val(client.direccion || '');
                $('#profNotes').val(client.notas_internas || '');
                
                // Parse Phone Number
                let phoneStr = client.numero_whatsapp || '';
                let prefix = phoneStr.substring(0, 5);
                let num = phoneStr.substring(5);
                
                if ($(`#profPrefix option[value='${prefix}']`).length === 0) {
                    $('#profPrefix').append(`<option value="${prefix}">${prefix}</option>`);
                }
                $('#profPrefix').val(prefix).prop('disabled', true);
                $('#profPhone').val(num).prop('disabled', true);
                
                $('#btnSaveProfile').text('Guardar Cambios');
                
                // Update Timeline
                let timeline = $('#profileTimeline');
                timeline.empty();
                
                if (res.data.events.length === 0) {
                    timeline.append('<div class="text-muted" style="font-size:0.85rem;">No hay eventos registrados.</div>');
                } else {
                    res.data.events.forEach(ev => {
                        let iconClass = ev.origen === 'BOT' ? 'icon-bot' : (ev.origen === 'API_TRANSACCIONAL' ? 'icon-api' : 'icon-agent');
                        let iconFa = ev.origen === 'BOT' ? 'fa-robot' : (ev.origen === 'API_TRANSACCIONAL' ? 'fa-bolt' : 'fa-info');
                        
                        let dateStr = new Date(ev.timestamp).toLocaleString();

                        let item = `
                            <div class="timeline-item">
                                <div class="timeline-icon ${iconClass}"><i class="fa-solid ${iconFa}"></i></div>
                                <div class="timeline-content">
                                    <span class="timeline-time">${dateStr}</span>
                                    <p class="timeline-text fw-bold text-starfi-dark">${ev.origen}</p>
                                    <p class="timeline-text text-muted">${ev.contenido}</p>
                                </div>
                            </div>
                        `;
                        timeline.append(item);
                    });
                }
                
                // Open Panel
                $('#profilePanel').addClass('open');
            }
        }
    });
}

function saveProfile() {
    const nombre = $('#profName').val().trim();
    const direccion = $('#profAddress').val().trim();
    const notas = $('#profNotes').val().trim();
    
    let action = currentClientId ? 'save_profile' : 'create_profile';
    let data = { action: action, nombre: nombre, direccion: direccion, notas: notas };
    
    if (currentClientId) {
        data.id = currentClientId;
    } else {
        const prefix = $('#profPrefix').val();
        const phone = $('#profPhone').val().trim();
        if(!phone || !nombre) {
            Swal.fire('Atención', 'El nombre y el número son obligatorios.', 'warning');
            return;
        }
        data.numero_whatsapp = prefix + phone;
    }
    
    $.ajax({
        url: 'back_directorio.php',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(res) {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'Los datos del cliente se actualizaron correctamente.',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadClients(); // Reload list
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }
    });
}
