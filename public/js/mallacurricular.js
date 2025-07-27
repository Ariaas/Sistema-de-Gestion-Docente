let ucsDisponibles = [];

function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
}

function verificarCondicionesIniciales() {
    var datos = new FormData();
    datos.append("accion", "verificar_condiciones");
    enviaAjax(datos);
}

function destruyeDT(selector = "#tablamalla") {
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();
    }
}

function crearDT(selector = "#tablamalla", config = {}) {
    const defaultConfig = {
        paging: true, lengthChange: true, searching: true, ordering: true, info: true, autoWidth: false, responsive: true,
        language: { lengthMenu: "Mostrar _MENU_ registros", zeroRecords: "No se encontraron resultados", info: "Mostrando _PAGE_ de _PAGES_", infoEmpty: "No hay registros disponibles para mostrar", infoFiltered: "(filtrado de _MAX_ registros totales)", search: "Buscar:", paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }, emptyTable: "No hay datos disponibles en la tabla" },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [[1, "asc"]],
    };
    if (!$.fn.DataTable.isDataTable(selector)) { $(selector).DataTable({ ...defaultConfig, ...config }); }
}

function gestionarBotonGuardar() {
    let haySeleccionados = false;
    let todoValido = true;

    $('#contenedorAcordeonUC tbody tr').each(function() {
        haySeleccionados = true;
        $(this).find('.horas-input').each(function() {
            const valor = $(this).val();
            if (!valor || valor.trim() === '' || parseInt(valor) <= 0) {
                todoValido = false;
            }
        });
    });

    if (haySeleccionados && todoValido) {
        $("#proceso").prop('disabled', false);
    } else {
        $("#proceso").prop('disabled', true);
    }
}


function actualizarSelectUC() {
    const select = $("#select_uc");
    const ucsAgregadas = [];
    $('#contenedorAcordeonUC tbody tr').each(function() {
        ucsAgregadas.push($(this).data('uc_codigo'));
    });

    select.empty().append('<option value="">Seleccione...</option>');
    ucsDisponibles.forEach(uc => {
        if (!ucsAgregadas.includes(uc.uc_codigo)) {
            select.append(`<option value="${uc.uc_codigo}" data-trayecto="${uc.uc_trayecto}">${uc.uc_nombre}</option>`);
        }
    });
    select.trigger('change');
}

$(document).ready(function () {
    Listar();
    verificarCondicionesIniciales();
    var datos = new FormData();
    datos.append("accion", "consultar_ucs");
    enviaAjax(datos);

    $('#modal1').on('hidden.bs.modal', function () { limpiaModal1(); });
    $('#modalVerMalla').on('hidden.bs.modal', function () { $('#cuerpoModalVer').empty(); });
    $('#select_uc').select2({ theme: "bootstrap-5", dropdownParent: $('#modal1') });

    $('#btn-siguiente').on('click', function () {
        if (!validarPagina1()) {
            muestraMensaje("error", 4000, "ERROR", "Por favor, corrija los campos marcados en rojo.");
            return;
        };

        const boton = $(this);
        const accion = $("#accion").val();

        const verificarCodigo = new Promise((resolve, reject) => {
            if (accion === 'modificar') return resolve({resultado: 'ok'}); 
            const datos = new FormData();
            datos.append('accion', 'existe');
            datos.append("mal_codigo", $("#mal_codigo").val());
            $.ajax({
                url: '', type: 'POST', data: datos, processData: false, contentType: false,
                success: (response) => resolve(JSON.parse(response)),
                error: () => reject({mensaje: 'Error de comunicación al verificar el código.'})
            });
        });

        const verificarCohorte = new Promise((resolve, reject) => {
            const datos = new FormData();
            datos.append('accion', 'existe_cohorte');
            datos.append("mal_cohorte", $("#mal_cohorte").val());
            if (accion === 'modificar') {
                datos.append("mal_codigo", $("#mal_codigo").val());
            }
            $.ajax({
                url: '', type: 'POST', data: datos, processData: false, contentType: false,
                success: (response) => resolve(JSON.parse(response)),
                error: () => reject({mensaje: 'Error de comunicación al verificar la cohorte.'})
            });
        });

        boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Validando...');
        
        Promise.all([verificarCodigo, verificarCohorte])
            .then(([respCodigo, respCohorte]) => {
                if(respCodigo.resultado === 'existe'){ throw new Error(respCodigo.mensaje); }
                if(respCohorte.resultado === 'existe'){ throw new Error(respCohorte.mensaje); }
                
                $('#pagina1').hide(); $('#botones-pagina1').hide();
                $('#pagina2').show(); $('#botones-pagina2').show();
                $('#modal1Titulo').text("Formulario de Malla (Paso 2 de 2)");
            })
            .catch(error => {
                const errorMsg = error.message || error.mensaje || 'Ocurrió un error inesperado.';
                muestraMensaje('warning', 4000, 'Validación fallida', errorMsg);
            })
            .finally(() => {
                boton.prop('disabled', false).html('Siguiente &raquo;');
            });
    });

    $('#btn-anterior').on('click', function () {
        $('#pagina2').hide(); $('#botones-pagina2').hide();
        $('#pagina1').show(); $('#botones-pagina1').show();
        $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)");
    });
    
   $('#btn_agregar_uc').on('click', function() {
    const select = $('#select_uc');
    const uc_codigo = select.val();
    const selectedOption = select.find('option:selected');
    const uc_nombre = selectedOption.text();
    const uc_trayecto = selectedOption.data('trayecto');

    if (!uc_codigo) {
        muestraMensaje('error', 3000, 'Error', 'Debe seleccionar una unidad curricular.');
        return;
    }

    const nombreTrayecto = uc_trayecto == '0' ? 'Trayecto Inicial' : `Trayecto ${uc_trayecto}`;
    // IDs únicos para las pestañas y paneles del modal de edición
    const tabId = `mod-tab-trayecto-${uc_trayecto}`;
    const paneId = `mod-pane-trayecto-${uc_trayecto}`;

    let tabPane = $(`#${paneId}`);

    // Si la pestaña para este trayecto no existe, la creamos
    if (tabPane.length === 0) {
        // Crear la pestaña de navegación
        const newTab = `
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="${tabId}-tab" data-bs-toggle="tab" data-bs-target="#${paneId}" type="button" role="tab">${nombreTrayecto}</button>
            </li>`;
        $('#mallaTabsMod').append(newTab);

        // Crear el panel de contenido de la pestaña con su tabla
        const newPane = `
            <div class="tab-pane fade" id="${paneId}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped mt-2 mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Unidad Curricular</th>
                                <th>H. Indep.</th>
                                <th>H. Asist.</th>
                                <th>HTE</th>
                                <th>H. Acad.</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>`;
        $('#mallaTabContentMod').append(newPane);
        tabPane = $(`#${paneId}`); // Re-seleccionamos el panel recién creado
    }

    // Crear la fila para la nueva unidad curricular
    const fila = `
        <tr data-uc_codigo="${uc_codigo}" data-trayecto="${uc_trayecto}">
            <td class="align-middle text-start">${uc_nombre}</td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-indep" value="0"></td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-asist" value="0"></td>
            <td><input type="text" class="form-control form-control-sm text-center h-total" value="0" readonly></td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-acad" value="0"></td>
            <td class="align-middle"><button type="button" class="btn btn-danger btn-sm btn-remover-uc">X</button></td>
        </tr>`;
    
    tabPane.find('tbody').append(fila);

    // Activar la pestaña recién creada o actualizada
    new bootstrap.Tab($(`#${tabId}-tab`)).show();
    
    actualizarSelectUC();
    gestionarBotonGuardar();
});


// ✅ FUNCIÓN MODIFICADA: btn-remover-uc
$('#contenedorAcordeonUC').on('click', '.btn-remover-uc', function() {
    const fila = $(this).closest('tr');
    const tabPane = fila.closest('.tab-pane');
    const tabId = tabPane.attr('id');
    
    fila.remove();
    
    // Si la tabla en la pestaña queda vacía, eliminamos la pestaña y su panel
    if (tabPane.find('tbody tr').length === 0) {
        // Eliminar el panel de contenido
        tabPane.remove();
        // Eliminar la pestaña de navegación usando el atributo data-bs-target
        $(`button[data-bs-target="#${tabId}"]`).parent().remove();

        // Activar la primera pestaña que quede, si existe
        $('#mallaTabsMod .nav-link').first().tab('show');
    }

    actualizarSelectUC();
    gestionarBotonGuardar();
});
   

    $("#mal_codigo").on("keyup", function () {
        validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $(this), $("#smalcodigo"),"El código permite de 2 a 20 caracteres alfanuméricos, espacios o guiones.");
    });

    $("#mal_nombre").on("keyup", function () {
       validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/,$(this),$("#smalnombre"),"El formato permite de 5 a 30 caracteres. Ej: Malla 2024");
    });
    
    $("#mal_descripcion").on("keyup", function () {
        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $(this), $("#smaldescripcion"),"El formato permite de 5 a 30 caracteres, una breve descripcion.");
    });

    $("#mal_cohorte").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
        if (this.value.length > 3) this.value = this.value.slice(0, 3);
    });

    $("#mal_cohorte").on("keyup", function () {
        validarkeyup(/^[1-9][0-9]{0,3}$/,$(this),$("#smalcohorte"),"El formato permite de 1 a 3 caracteres, solo numeros enteros EJ:4");
    });
    
    $("#proceso").on("click", function () {
        if (validarenvio()) {
            $("#mal_codigo").prop("disabled", false);
            var datos = new FormData($("#f")[0]);
            let unidades = [];
            $('#contenedorAcordeonUC tbody tr').each(function() {
                const fila = $(this);
                unidades.push({
                    uc_codigo: fila.data('uc_codigo'),
                    hora_independiente: parseInt(fila.find('.h-indep').val()) || 0,
                    hora_asistida: parseInt(fila.find('.h-asist').val()) || 0,
                    hora_academica: parseInt(fila.find('.h-acad').val()) || 0
                });
            });

            datos.append("unidades", JSON.stringify(unidades));
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function () {
        if($(this).is(':disabled')) return;
        
        $("#accion").val("registrar");
        $("#modal1Titulo").text("Formulario de Malla (Paso 1 de 2)");
        $("#proceso").text("GUARDAR");
        $("#modal1").modal("show");
    });

    $('#resultadoconsulta').on('click', '.btn-activar', function() {
        const mal_codigo = $(this).closest('tr').find('td:eq(0)').text();
        Swal.fire({
            title: '¿Desea activar esta malla?',
            text: "Se desactivará cualquier otra malla que esté activa.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, activar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const datos = new FormData();
                datos.append('accion', 'activar');
                datos.append('mal_codigo', mal_codigo);
                enviaAjax(datos);
            }
        });
    });
});

function validarenvio() {
    if (!validarPagina1()) return false;
    
    gestionarBotonGuardar();
    if ($("#proceso").is(':disabled')) {
        muestraMensaje("error", 4000, "ERROR", "Debe agregar al menos una unidad curricular y completar todas sus horas.");
        return false;
    }
    return true;
}

function pone(pos, accionBtn) {
    const linea = $(pos).closest("tr");
    const mal_codigo = $(linea).find("td:eq(0)").text();
    const mal_nombre = $(linea).find("td:eq(1)").text();
    const boton = $(pos);

   if (accionBtn === 2) { // VER MALLA
    const mal_nombre = $(linea).find("td:eq(1)").text(); // Asegúrate de tener esta línea si la borraste
    const boton = $(pos); // Y esta
    const datos = new FormData();
    datos.append("accion", "consultar_ucs_por_malla");
    datos.append("mal_codigo", mal_codigo);
    
    $.ajax({
        async: true, url: "", type: "POST", contentType: false, data: datos,
        processData: false, cache: false,
        beforeSend: function() {
            // ✅ Solo deshabilitamos el botón
            boton.prop('disabled', true);
        },
         success: function(respuesta) {
            try {
                const lee = JSON.parse(respuesta);
                if (lee.resultado === 'ok') {
                    const cuerpoModal = $("#cuerpoModalVer");
                    cuerpoModal.empty();
                    $("#modalVerMallaTitulo").text("Unidades de: " + mal_nombre);

                    if (lee.mensaje.length === 0) {
                        cuerpoModal.html('<p class="text-center text-muted p-3">Esta malla no tiene unidades curriculares asignadas.</p>');
                    } else {
                        let gruposVer = { '0': [], '1': [], '2': [], '3': [], '4': [] };
                        lee.mensaje.forEach(uc => {
                            if (gruposVer[uc.uc_trayecto] !== undefined) gruposVer[uc.uc_trayecto].push(uc);
                        });

                        // --- INICIO DE LA LÓGICA PARA TABS ---
                        
                        let navTabs = '<ul class="nav nav-tabs" id="mallaTab" role="tablist">';
                        let tabContent = '<div class="tab-content" id="mallaTabContent">';
                        let primerItem = true;

                        for (const trayecto in gruposVer) {
                            if (gruposVer[trayecto].length > 0) {
                                const nombreTrayecto = (trayecto == '0') ? 'Trayecto Inicial' : `Trayecto ${trayecto}`;
                                const idTab = `tab-trayecto-${trayecto}`;

                                // Construir la Pestaña de Navegación
                                navTabs += `
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link ${primerItem ? 'active' : ''}" id="${idTab}-tab" data-bs-toggle="tab" data-bs-target="#${idTab}" type="button" role="tab">${nombreTrayecto}</button>
                                    </li>`;

                                // Construir el Contenido de la Pestaña
                                const tabla = $('<div class="table-responsive"><table class="table table-sm table-striped table-bordered mt-3"><thead><tr><th>Unidad Curricular</th><th>H. Indep.</th><th>H. Asist.</th><th>HTE</th><th>H. Acad.</th></tr></thead><tbody></tbody></table></div>');
                                
                                gruposVer[trayecto].forEach(uc => {
                                    const hte = parseInt(uc.mal_hora_independiente) + parseInt(uc.mal_hora_asistida);
                                    // Usamos .text() para seguridad contra XSS
                                    const fila = $('<tr>');
                                    fila.append($('<td>').text(uc.uc_nombre));
                                    fila.append($('<td>').text(uc.mal_hora_independiente));
                                    fila.append($('<td>').text(uc.mal_hora_asistida));
                                    fila.append($('<td>').text(hte));
                                    fila.append($('<td>').text(uc.mal_hora_academica));
                                    tabla.find('tbody').append(fila);
                                });

                                tabContent += `
                                    <div class="tab-pane fade ${primerItem ? 'show active' : ''}" id="${idTab}" role="tabpanel">
                                        ${tabla.html()}
                                    </div>`;

                                primerItem = false;
                            }
                        }
                        navTabs += '</ul>';
                        tabContent += '</div>';

                        cuerpoModal.html(navTabs + tabContent);
                        // --- FIN DE LA LÓGICA PARA TABS ---
                    }
                    $("#modalVerMalla").modal("show");
                } else {
                    muestraMensaje('error', 5000, 'Error', 'No se pudo cargar la información de la malla.');
                }
            } catch (e) {
                muestraMensaje("error", 10000, "Error de Comunicación", "La respuesta del servidor no es válida.");
            }
        },
        error: function () {
            muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo conectar con el servidor.");
        },
        complete: function(){
            // ✅ Solo habilitamos el botón
            boton.prop('disabled', false);
        }
    });
    return;
}
 
    $("#mal_codigo").val(mal_codigo).prop("disabled", true);
    $("#mal_nombre").val(mal_nombre);
    $("#mal_cohorte").val($(linea).find("td:eq(2)").text());
    $("#mal_descripcion").val($(linea).find("td:eq(3)").text());

    if (accionBtn === 0) { // MODIFICAR
        $("#accion").val("modificar");
        $("#modal1Titulo").text("Modificar Malla (Paso 1 de 2)");
        $("#proceso").text("MODIFICAR");
        $("#modal1").modal("show");
        
        $('#modal1').off('shown.bs.modal').on('shown.bs.modal', function () {
            var datos = new FormData();
            datos.append("accion", "consultar_ucs_por_malla");
            datos.append("mal_codigo", mal_codigo);
            enviaAjax(datos);
        });

    } else if (accionBtn === 1) { // ELIMINAR
        Swal.fire({
            title: "¿Está seguro de eliminar esta malla?", text: "Esta acción no se puede deshacer.",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6", confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData(); 
                datos.append("accion", "eliminar"); 
                datos.append("mal_codigo", mal_codigo);
                enviaAjax(datos);
            }
        });
    }
}

function enviaAjax(datos) {
    $.ajax({
        async: true, url: "", type: "POST", contentType: false, data: datos,
        processData: false, cache: false,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                
                if (datos.get('accion') === 'verificar_condiciones') {
                    const registrarBtn = $("#registrar");
                    const mensajeSpan = $("#mensaje-validacion");
                    
                    if (registrarBtn.is(':not(:disabled)')) {
                        if (lee.puede_registrar) {
                            registrarBtn.prop('disabled', false);
                            mensajeSpan.text('');
                        } else {
                            registrarBtn.prop('disabled', true);
                            mensajeSpan.text(lee.mensaje);
                        }
                    }
                    return;
                }
                
                switch (lee.resultado) {
                    case 'consultar':
                        destruyeDT("#tablamalla");
                        $("#resultadoconsulta").empty();
                        $.each(lee.mensaje, function (index, item) {
                            let estadoActiva = (item.mal_activa == 1) ? '<span class="badge bg-success">Activa</span>' : '<button class="btn btn-xs btn-secondary btn-activar">Activar</button>';
                            let botonesAccion = `<td class="acciones-cell">
                                <button class="btn btn-icon btn-info" onclick='pone(this,2)'> <img src="public/assets/icons/people.svg" alt="Ver malla"></button> 
                                <button class="btn btn-icon btn-edit" onclick='pone(this,0)' ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button> 
                                <button class="btn btn-icon btn-delete" onclick='pone(this,1)' ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                            </td>`;
                            $("#resultadoconsulta").append(`<tr><td>${item.mal_codigo}</td><td>${item.mal_nombre}</td><td>${item.mal_cohorte}</td><td>${item.mal_descripcion}</td><td>${estadoActiva}</td>${botonesAccion}</tr>`);
                        });
                        crearDT("#tablamalla");
                        break;
                    case 'ok':
                        if (lee.accion === 'consultar_ucs') {
                            ucsDisponibles = lee.mensaje;
                            actualizarSelectUC();
                        } else if (lee.accion === 'consultar_ucs_por_malla') {
                           if ($('#modal1').is(':visible')) { 
                                limpiaModal1(false);
                                lee.mensaje.forEach(function(uc_sel) {
                                    $('#select_uc').val(uc_sel.uc_codigo).trigger('change');
                                    $('#btn_agregar_uc').trigger('click');
                                    const fila = $(`#contenedorAcordeonUC tr[data-uc_codigo="${uc_sel.uc_codigo}"]`);
                                    fila.find('.h-indep').val(uc_sel.mal_hora_independiente);
                                    fila.find('.h-asist').val(uc_sel.mal_hora_asistida);
                                    fila.find('.h-acad').val(uc_sel.mal_hora_academica);
                                    fila.find('.h-total').val(parseInt(uc_sel.mal_hora_independiente) + parseInt(uc_sel.mal_hora_asistida));
                                });
                                gestionarBotonGuardar();
                            }
                        }
                        break;
                    case 'registrar': case 'modificar': case 'eliminar': case 'activar':
                        muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                        if (lee.resultado !== 'activar') $("#modal1").modal("hide");
                        Listar();
                        verificarCondicionesIniciales();
                        break;
                    case 'existe': case 'existe_cohorte':
                        if(lee.resultado == 'existe'){ muestraMensaje('info', 4000, 'Atención!', lee.mensaje); }
                        break;
                    case 'error':
                        muestraMensaje("error", 10000, "ERROR", lee.mensaje);
                        break;
                }
            } catch (e) {
                console.error("Error en análisis JSON:", e, "Respuesta:", respuesta);
                muestraMensaje("error", 10000, "Error de Comunicación", "No se pudo procesar la respuesta del servidor.");
            }
        },
        error: function (request, status, err) {
            muestraMensaje("error", 5000, "Error de Comunicación", "ERROR: " + request.responseText);
        },
    });
}

function limpiaModal1(resetearTodo = true) {
    if (resetearTodo) {
        $("#f")[0].reset();
        $(".form-control").removeClass("is-invalid is-valid").prop("disabled", false);
        $(".validation-span").empty();
    }
    
    $('#pagina2').hide();
    $('#botones-pagina2').hide();
    $('#pagina1').show();
    $('#botones-pagina1').show();
    $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)");
    
    // Limpiar contenedores de pestañas
    $('#mallaTabsMod').empty();
    $('#mallaTabContentMod').empty();
    
    actualizarSelectUC();
    gestionarBotonGuardar();
}

function validarPagina1() {
    let esValido = true;
    if (validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $("#mal_codigo"), $("#smalcodigo"), "El código permite de 2 a 20 caracteres alfanuméricos, espacios o guiones.") == 0) {
       esValido = false;
    }
    if ( validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/,$("#mal_nombre"),$("#smalnombre"), "El formato permite de 5 a 30 caracteres, Ej:Malla 2024") == 0) {
        esValido = false;
    }
    if (validarkeyup(/^[1-9][0-9]{0,3}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 3 caracteres, solo numeros enteros EJ:4.") == 0) {
        esValido = false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $("#mal_descripcion"), $("#smaldescripcion"), "El formato permite de 5 a 30 caracteres, una breve descripcion") == 0) {
        esValido = false;
    }
    return esValido;
}

function muestraMensaje(icono, tiempo, titulo, mensaje) {
    Swal.fire({ icon: icono, timer: tiempo, title: titulo, html: mensaje, showConfirmButton: false });
}

function validarkeyup(er, etiqueta, etiquetamensaje, mensaje = "") {
    if (etiqueta.prop('disabled')) return 1;
    if (er.test(etiqueta.val())) {
        if (etiquetamensaje) etiquetamensaje.text("");
        etiqueta.removeClass('is-invalid').addClass('is-valid');
        return 1;
    } else {
        if (etiquetamensaje) etiquetamensaje.text(mensaje);
        etiqueta.removeClass('is-valid').addClass('is-invalid');
        return 0;
    }
}