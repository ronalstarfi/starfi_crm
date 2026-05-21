$(document).ready(function() {
    loadBotRules();
});

let botModalObj = null;

function loadBotRules() {
    $.ajax({
        url: 'back_gestor_bots.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'load_rules' },
        success: function(response) {
            if (response.status === 'success') {
                renderBotRules(response.data);
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }
    });
}

function renderBotRules(rules) {
    const tbody = $('#botRulesTable');
    tbody.empty();

    if (rules.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center text-muted p-4">No hay reglas configuradas.</td></tr>');
        return;
    }

    rules.forEach(rule => {
        let tipoBadge = rule.tipo === 'EVENTO_SISTEMA' 
            ? '<span class="badge bg-secondary">Evento</span>' 
            : '<span class="badge bg-primary">Palabra Clave</span>';
            
        let estadoBadge = rule.estado === 'ACTIVO'
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-light text-dark">Inactivo</span>';

        let html = `
            <tr>
                <td>${tipoBadge}</td>
                <td class="fw-bold">${rule.disparador}</td>
                <td><small class="text-muted text-wrap" style="max-width: 300px; display: block;">${rule.mensaje}</small></td>
                <td>${estadoBadge}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light text-primary me-1" onclick="editBotRule(${rule.id}, '${rule.tipo}', '${rule.disparador}', '${rule.mensaje.replace(/'/g, "\\'").replace(/\n/g, "\\n")}', '${rule.estado}')">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-danger" onclick="deleteBotRule(${rule.id})">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(html);
    });
}

function openBotModal() {
    $('#botForm')[0].reset();
    $('#ruleId').val('0');
    $('#botModalTitle').text('Nueva Respuesta Automática');
    
    if(!botModalObj) botModalObj = new bootstrap.Modal(document.getElementById('botModal'));
    botModalObj.show();
}

function editBotRule(id, tipo, disparador, mensaje, estado) {
    $('#ruleId').val(id);
    $('#ruleType').val(tipo);
    $('#ruleTrigger').val(disparador);
    $('#ruleMessage').val(mensaje);
    $('#ruleState').val(estado);
    $('#botModalTitle').text('Editar Respuesta Automática');
    
    if(!botModalObj) botModalObj = new bootstrap.Modal(document.getElementById('botModal'));
    botModalObj.show();
}

function saveBotRule() {
    const data = {
        action: 'save_rule',
        id: $('#ruleId').val(),
        tipo: $('#ruleType').val(),
        disparador: $('#ruleTrigger').val(),
        mensaje: $('#ruleMessage').val(),
        estado: $('#ruleState').val()
    };

    if (!data.disparador || !data.mensaje) {
        Swal.fire('Atención', 'El disparador y el mensaje son obligatorios.', 'warning');
        return;
    }

    $.ajax({
        url: 'back_gestor_bots.php',
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            if (response.status === 'success') {
                botModalObj.hide();
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                loadBotRules();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }
    });
}

function deleteBotRule(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'back_gestor_bots.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'delete_rule', id: id },
                success: function(response) {
                    if (response.status === 'success') {
                        loadBotRules();
                        Swal.fire('Eliminada', 'La regla ha sido eliminada.', 'success');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}
