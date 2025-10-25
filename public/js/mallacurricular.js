let ucsDisponibles = [];
let originalCohorte = '';
let estadoInicialModificar = null;
let cargandoUnidades = false;

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
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles para mostrar",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            },
            emptyTable: "No hay datos disponibles en la tabla"
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [
            [1, "asc"]
        ],
    };
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({ ...defaultConfig,
            ...config
        });
    }
}

function gestionarBotonGuardar() {
    let haySeleccionados = false;
    let todoValido = true;
    $('#contenedorAcordeonUC tbody tr').each(function() {
        haySeleccionados = true;
        $(this).find('.horas-input').each(function() {
            const valor = $(this).val();
            if (valor === null || valor.trim() === '' || valorNum === 0) {
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
        ucsAgregadas.push($(this).data('uc_codigo').toString());
    });
    select.empty();
    let disponibles = 0;
    ucsDisponibles.forEach(uc => {
        if (!ucsAgregadas.includes(uc.uc_codigo.toString())) {
            select.append(`<option value="${uc.uc_codigo}" data-trayecto="${uc.uc_trayecto}">${uc.uc_nombre}</option>`);
            disponibles++;
        }
    });
    if (disponibles === 0) {
        select.append('<option value="">Todas las unidades curriculares han sido agregadas</option>');
        select.prop('disabled', true);
    } else {
        select.prepend('<option value="">Seleccione...</option>');
        select.prop('disabled', false);
    }
    select.trigger('change');
}

function obtenerEstadoActualUCs() {
    const unidades = [];
    $('#contenedorAcordeonUC tbody tr').each(function() {
        const fila = $(this);
        const getHourValue = (selector) => {
            const val = fila.find(selector).val();
            if (val === null || val.trim() === '') {
                return null;
            }
            return parseInt(val, 10);
        };
        unidades.push({
            uc_codigo: fila.data('uc_codigo').toString(),
            hora_independiente: getHourValue('.h-indep'),
            hora_asistida: getHourValue('.h-asist'),
            hora_academica: getHourValue('.h-acad')
        });
    });
    return unidades.sort((a, b) => a.uc_codigo.localeCompare(b.uc_codigo));
}

function verificarCambiosParaModificar() {
    if (cargandoUnidades) return;
    if ($("#accion").val() !== 'modificar' || !estadoInicialModificar) {
        return;
    }
    const estadoActual = {
        codigo: $("#mal_codigo").val(),
        nombre: $("#mal_nombre").val(),
        cohorte: $("#mal_cohorte").val(),
        descripcion: $("#mal_descripcion").val(),
        unidades: obtenerEstadoActualUCs()
    };
    const haCambiado = JSON.stringify(estadoInicialModificar) !== JSON.stringify(estadoActual);
    let todoValido = true;
    if (haCambiado) {
        if ($("#mal_codigo").hasClass('is-invalid') || $("#mal_codigo").val().trim() === '') {
            todoValido = false;
        }
        estadoActual.unidades.forEach(uc => {
            if (uc.hora_independiente === null || uc.hora_asistida === null || uc.hora_academica === null) {
                todoValido = false;
            }
        });
    }
    $("#proceso").prop('disabled', !haCambiado || !todoValido);
}


function agregarUnidadAlDOM(uc) {
    const { uc_codigo, uc_nombre, uc_trayecto } = uc;
    const nombreTrayecto = uc_trayecto == '0' ? 'Trayecto Inicial' : `Trayecto ${uc_trayecto}`;
    const tabId = `mod-tab-trayecto-${uc_trayecto}`;
    const paneId = `mod-pane-trayecto-${uc_trayecto}`;
    let tabPane = $(`#${paneId}`);

    if (tabPane.length === 0) {
        const newTab = `<li class="nav-item" role="presentation"><button class="nav-link" id="${tabId}-tab" data-bs-toggle="tab" data-bs-target="#${paneId}" type="button" role="tab">${nombreTrayecto}</button></li>`;
        $('#mallaTabsMod').append(newTab);
        const newPane = `
            <div class="tab-pane fade" id="${paneId}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped mt-2 mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Unidad Curricular</th><th>H. Indep.</th><th>H. Asist.</th><th>HTE</th><th>H. Acad.</th><th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>`;
        $('#mallaTabContentMod').append(newPane);
        tabPane = $(`#${paneId}`);
    }

    const fila = `
        <tr data-uc_codigo="${uc_codigo}" data-trayecto="${uc_trayecto}">
            <td class="align-middle text-start">${uc_nombre}</td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-indep" value=""></td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-asist" value=""></td>
            <td><input type="text" class="form-control form-control-sm text-center h-total bg-light" value="" readonly></td>
            <td><input type="text" class="form-control form-control-sm text-center horas-input h-acad" value=""></td>
            <td class="align-middle"><button type="button" class="btn btn-danger btn-sm btn-remover-uc">X</button></td>
        </tr>`;
    tabPane.find('tbody').append(fila);

    new bootstrap.Tab($(`#${tabId}-tab`)).show();
    actualizarSelectUC();

    if ($("#accion").val() === 'modificar') {
        verificarCambiosParaModificar();
    } else {
        gestionarBotonGuardar();
    }
}

$(document).ready(function() {
    Listar();
    verificarCondicionesIniciales();
    var datos = new FormData();
    datos.append("accion", "consultar_ucs");
    enviaAjax(datos);

    $('#modal1').on('hidden.bs.modal', () => limpiaModal1());
    $('#modalVerMalla').on('hidden.bs.modal', () => $('#cuerpoModalVer').empty());

    $('#select_uc').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#modal1')
    });

    
    $("#mal_nombre").on("keyup down", function() { validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/, $(this), $("#smalnombre"), "El formato permite de 5 a 30 caracteres."); verificarCambiosParaModificar(); });
    $("#mal_descripcion").on("keyup down", function() { validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-¿?¡!(){}\[\]]{5,255}$/, $(this), $("#smaldescripcion"), "El formato permite de 5 a 255 caracteres."); verificarCambiosParaModificar(); });
    $("#mal_codigo").on("input", function() {
        const span = $("#smalcodigo");
        span.css('color', '');
        $("#btn-siguiente").prop("disabled", validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $(this), span, "El código debe tener entre 2 y 20 caracteres.") === 0);
    });
    $("#mal_codigo").on("keyup", function() {
        const input = $(this);
        if (input.val().trim() === '' || input.hasClass('is-invalid')) return;
        const datos = new FormData();
        datos.append('accion', 'existe');
        datos.append('mal_codigo', input.val());
        if ($("#accion").val() === 'modificar') {
            datos.append('mal_codigo_original', $("#mal_codigo_original").val());
        }
        enviaAjax(datos, 'existe_codigo');
        verificarCambiosParaModificar();
    });
    $("#mal_cohorte").on("input", function() {
        this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
        if (this.value.length > 3) this.value = this.value.slice(0, 3);
        const span = $("#smalcohorte");
        span.css('color', '');
        $("#btn-siguiente").prop("disabled", validarkeyup(/^[1-9][0-9]{0,3}$/, $(this), span, "Debe ser un número entre 1 y 999.") === 0);
        verificarCambiosParaModificar();
    });
    $("#mal_cohorte").on("keyup", function() {
        const input = $(this);
        if (input.val().trim() === '' || input.hasClass('is-invalid')) return;
        const datos = new FormData();
        datos.append('accion', 'existe_cohorte');
        datos.append('mal_cohorte', input.val());
        if ($("#accion").val() === 'modificar') {
            datos.append("mal_codigo_original", $("#mal_codigo_original").val());
        }
        enviaAjax(datos, 'existe_cohorte');
    });
    $('#contenedorAcordeonUC').on('input', '.horas-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 2) this.value = this.value.slice(0, 2);
        if ($("#accion").val() === 'modificar') {
            verificarCambiosParaModificar();
        } else {
            gestionarBotonGuardar();
        }
    });
    $('#contenedorAcordeonUC').on('input', '.h-indep, .h-asist', function() {
        const fila = $(this).closest('tr');
        const horasIndep = parseInt(fila.find('.h-indep').val()) || 0;
        const horasAsist = parseInt(fila.find('.h-asist').val()) || 0;
        fila.find('.h-total').val(horasIndep + horasAsist);
    });

    
    $('#btn_agregar_uc').on('click', function() {
        const select = $('#select_uc');
        const uc_codigo = select.val();
        if (!uc_codigo) {
            muestraMensaje('error', 3000, 'Error', 'Debe seleccionar una unidad curricular.');
            return;
        }
        const selectedOption = select.find('option:selected');
        const uc = {
            uc_codigo: uc_codigo,
            uc_nombre: selectedOption.text(),
            uc_trayecto: selectedOption.data('trayecto')
        };
        agregarUnidadAlDOM(uc);
    });

    $('#contenedorAcordeonUC').on('click', '.btn-remover-uc', function() {
        const fila = $(this).closest('tr');
        const tabPane = fila.closest('.tab-pane');
        const tabId = tabPane.attr('id');
        fila.remove();
        if (tabPane.find('tbody tr').length === 0) {
            tabPane.remove();
            $(`button[data-bs-target="#${tabId}"]`).parent().remove();
            $('#mallaTabsMod .nav-link').first().tab('show');
        }
        actualizarSelectUC();
        if ($("#accion").val() === 'modificar') {
            verificarCambiosParaModificar();
        } else {
            gestionarBotonGuardar();
        }
    });

    
    $('#btn-siguiente').on('click', function() { if (validarPagina1()) { $('#pagina1, #botones-pagina1').hide(); $('#pagina2, #botones-pagina2').show(); $('#modal1Titulo').text("Formulario de Malla (Paso 2 de 2)"); } else { muestraMensaje("error", 4000, "ERROR", "Por favor, corrija los campos marcados"); } });
    $('#btn-anterior').on('click', function() { $('#pagina2, #botones-pagina2').hide(); $('#pagina1, #botones-pagina1').show(); $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)"); });
    $("#proceso").addClass("ms-2").on("click", function() { if (validarenvio()) { $("#mal_codigo").prop("disabled", false); var datos = new FormData($("#f")[0]); let unidades = []; $('#contenedorAcordeonUC tbody tr').each(function() { const fila = $(this); unidades.push({ uc_codigo: fila.data('uc_codigo'), hora_independiente: parseInt(fila.find('.h-indep').val()) || 0, hora_asistida: parseInt(fila.find('.h-asist').val()) || 0, hora_academica: parseInt(fila.find('.h-acad').val()) || 0 }); }); datos.append("unidades", JSON.stringify(unidades)); enviaAjax(datos); } });
    $("#registrar").on("click", function() { if ($(this).is(':disabled')) return; limpiaModal1(); $("#accion").val("registrar"); $("#mal_codigo_original").val(''); $("#mal_codigo").prop('disabled', false); $("#modal1Titulo").text("Registrar Malla (Paso 1 de 2)"); $("#proceso").text("REGISTRAR"); $("#modal1").modal("show"); });
    
   
 
    $(document).on('click', '.btn-activar-malla', function(e) {
        e.preventDefault();
        const codigo = $(this).data('codigo');
        Swal.fire({
            title: '¿Está seguro de activar esta malla?',
            text: 'La malla pasará a estar activa.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, activar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append('accion', 'cambiar_estado_activo');
                datos.append('mal_codigo', codigo);
                enviaAjax(datos);
            }
        });
    });
});

function enviaAjax(datos, tipoLlamada = '') {
    $.ajax({
        async: true,
        url: "",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (tipoLlamada === 'existe_codigo') {
                    const mensajeSpan = $("#smalcodigo");
                    const btnSiguiente = $("#btn-siguiente");
                    if (lee.resultado === 'existe') {
                        mensajeSpan.text(lee.mensaje).css('color', 'red');
                        btnSiguiente.prop("disabled", true);
                    } else if (lee.resultado === 'ok') {
                        mensajeSpan.text('').css('color', '');
                        btnSiguiente.prop("disabled", false);
                    }
                    if ($("#accion").val() === 'modificar') {
                        verificarCambiosParaModificar();
                    }
                    return;
                }
                if (tipoLlamada === 'existe_cohorte') {
                    const mensajeSpan = $("#smalcohorte");
                    const btnSiguiente = $("#btn-siguiente");
                    if (lee.resultado === 'existe') {
                        mensajeSpan.text(lee.mensaje).css('color', 'red');
                        btnSiguiente.prop("disabled", true);
                    }
                    return;
                }
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
                        $.each(lee.mensaje, function(index, item) {
                            const esActiva = parseInt(item.mal_activa) === 1;
                            const estadoBadge = esActiva ? '<span class="uc-badge activa">Activa</span>' : '<span class="uc-badge desactivada">Desactivada</span>';
                            const btnDesactivar = `<button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Desactivar" aria-label="Desactivar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/power.svg" alt="Desactivar"></button>`;
                            const btnActivar = `<button class="btn btn-icon btn-success btn-activar-malla" title="Activar" aria-label="Activar" data-codigo="${item.mal_codigo}" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/check.svg" alt="Activar"></button>`;
                            const botonesAccion = `<td class="text-center">
                                <button class="btn btn-icon btn-info" onclick='pone(this,2)' title="Ver malla"><img src="public/assets/icons/eye.svg" alt="Ver"></button> 
                                <button class="btn btn-icon btn-edit" onclick='pone(this,0)' ${!PERMISOS.modificar ? 'disabled' : ''} title="Modificar malla"><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                                ${esActiva ? btnDesactivar : btnActivar}
                            </td>`;
                            $("#resultadoconsulta").append(`<tr><td>${item.mal_codigo}</td><td>${item.mal_nombre}</td><td>${item.mal_cohorte}</td><td>${item.mal_descripcion}</td><td>${estadoBadge}</td>${botonesAccion}</tr>`);
                        });
                        crearDT("#tablamalla");
                        break;
                    case 'ok':
                        if (lee.accion === 'consultar_ucs') {
                            ucsDisponibles = lee.mensaje;
                            actualizarSelectUC();
                        } else if (lee.accion === 'consultar_ucs_por_malla') {
                            if ($('#modal1').is(':visible')) {
                                cargandoUnidades = true;
                                $('#mallaTabsMod, #mallaTabContentMod').empty();
                                
                                
                                lee.mensaje.forEach(function(uc_sel) {
                                  
                                    agregarUnidadAlDOM(uc_sel);

                                    
                                    const fila = $(`#contenedorAcordeonUC tr[data-uc_codigo="${uc_sel.uc_codigo}"]`);
                                    fila.find('.h-indep').val(uc_sel.mal_hora_independiente);
                                    fila.find('.h-asist').val(uc_sel.mal_hora_asistida);
                                    fila.find('.h-acad').val(uc_sel.mal_hora_academica);
                                    fila.find('.h-total').val(parseInt(uc_sel.mal_hora_independiente) + parseInt(uc_sel.mal_hora_asistida));
                                });
                                
                                if (estadoInicialModificar) {
                                    estadoInicialModificar.unidades = obtenerEstadoActualUCs();
                                }
                                cargandoUnidades = false;
                                verificarCambiosParaModificar(); 
                            }
                        } else {
                            muestraMensaje("success", 4000, "ÉXITO", lee.mensaje);
                            Listar();
                        }
                        break;
                    case 'registrar':
                    case 'modificar':
                        muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
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
        error: function(request, status, err) {
            muestraMensaje("error", 5000, "Error de Comunicación", "ERROR: " + request.responseText);
        },
    });
}


function validarenvio() {
    if (!validarPagina1()) {
        muestraMensaje("error", 4000, "ERROR", "Por favor, corrija los campos marcados en el primer paso.");
        return false;
    }

    
    const trayectosRequeridos = ['0', '1', '2', '3', '4'];
    const trayectosSeleccionados = new Set();
    $('#contenedorAcordeonUC tbody tr').each(function() {
        trayectosSeleccionados.add($(this).data('trayecto').toString());
    });
    
    const trayectosFaltantes = trayectosRequeridos.filter(t => !trayectosSeleccionados.has(t));
    if (trayectosFaltantes.length > 0) {
        const nombresFaltantes = trayectosFaltantes.map(t => (t === '0' ? 'Inicial' : `Trayecto ${t}`));
        muestraMensaje("error", 7000, "Validación Fallida", "Debe agregar al menos una unidad curricular de los siguientes trayectos: <strong>" + nombresFaltantes.join(', ') + "</strong>.");
        return false;
    }

    
    let primerErrorHoras = ""; 

    
    $('#contenedorAcordeonUC tbody tr').each(function() {
        if (primerErrorHoras !== "") return false; 

        const fila = $(this);
        const ucNombre = fila.find('td:first').text(); 

        
        fila.find('.horas-input').each(function() {
            const input = $(this);
            const valorTrim = input.val().trim();
            const valorNum = parseInt(valorTrim, 10);

            let tipoHora = "";
            if (input.hasClass('h-indep')) tipoHora = "Horas Independientes";
            else if (input.hasClass('h-asist')) tipoHora = "Horas Asistidas";
            else if (input.hasClass('h-acad')) tipoHora = "Horas Académicas";

            
            if (valorTrim === '') {
                primerErrorHoras = `El campo "<strong>${tipoHora}</strong>" de la unidad "<strong>${ucNombre}</strong>" no puede estar vacío.`;
                return false; 
            }

            
            if (isNaN(valorNum) || valorNum === 0) {
                primerErrorHoras = `El campo "<strong>${tipoHora}</strong>" de la unidad "<strong>${ucNombre}</strong>" debe ser un número mayor que 0.`;
                return false; 
            }
        });
    });

    
    if (primerErrorHoras !== "") {
       
        muestraMensaje("error", 6000, "Atención", primerErrorHoras);
        return false;
    }

    return true; 
}

function pone(pos, accionBtn) {
    const linea = $(pos).closest("tr");
    const mal_codigo = $(linea).find("td:eq(0)").text();
    const mal_nombre = $(linea).find("td:eq(1)").text();
    if (accionBtn === 0) {
        limpiaModal1();
        originalCohorte = $(linea).find("td:eq(2)").text();
        $("#mal_codigo").val(mal_codigo).prop("disabled", false);
        $("#mal_codigo_original").val(mal_codigo);
        $("#mal_nombre").val(mal_nombre);
        $("#mal_cohorte").val(originalCohorte);
        $("#mal_descripcion").val($(linea).find("td:eq(3)").text());
        $("#accion").val("modificar");
        $("#modal1Titulo").text("Modificar Malla (Paso 1 de 2)");
        $("#proceso").text("MODIFICAR").prop("disabled", true);
        $("#btn-siguiente").prop("disabled", false);
        
        estadoInicialModificar = {
            codigo: mal_codigo,
            nombre: mal_nombre,
            cohorte: originalCohorte,
            descripcion: $(linea).find("td:eq(3)").text(),
            unidades: []
        };

        $("#modal1").modal("show");
        $('#modal1').off('shown.bs.modal').on('shown.bs.modal', function() {
            var datos = new FormData();
            datos.append("accion", "consultar_ucs_por_malla");
            datos.append("mal_codigo", mal_codigo);
            $("#mal_codigo").prop('disabled', false).focus();
            enviaAjax(datos);
        });
    } else if (accionBtn === 1) {
        Swal.fire({
            title: '¿Está seguro de desactivar esta malla?',
            text: 'Esta acción puede desactivar la malla y ocultarla de la lista principal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const datos = new FormData();
                datos.append('accion', 'cambiar_estado_activo');
                datos.append('mal_codigo', mal_codigo);
                enviaAjax(datos);
            }
        });
    } else if (accionBtn === 2) { 
        const boton = $(pos);
        const datos = new FormData();
        datos.append("accion", "consultar_ucs_por_malla");
        datos.append("mal_codigo", mal_codigo);
        $.ajax({
            async: true,
            url: "",
            type: "POST",
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
            beforeSend: () => boton.prop('disabled', true),
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
                            lee.mensaje.forEach(uc => { if (gruposVer[uc.uc_trayecto] !== undefined) gruposVer[uc.uc_trayecto].push(uc); });
                            let navTabs = '<ul class="nav nav-tabs" id="mallaTab" role="tablist">';
                            let tabContent = '<div class="tab-content" id="mallaTabContent">';
                            let primerItem = true;
                            for (const trayecto in gruposVer) {
                                if (gruposVer[trayecto].length > 0) {
                                    const nombreTrayecto = (trayecto == '0') ? 'Trayecto Inicial' : `Trayecto ${trayecto}`;
                                    const idTab = `tab-trayecto-${trayecto}`;
                                    navTabs += `<li class="nav-item" role="presentation"><button class="nav-link ${primerItem ? 'active' : ''}" id="${idTab}-tab" data-bs-toggle="tab" data-bs-target="#${idTab}" type="button" role="tab">${nombreTrayecto}</button></li>`;
                                    const tabla = $('<div class="table-responsive"><table class="table table-sm table-striped table-bordered mt-3"><thead><tr><th>Unidad Curricular</th><th class="text-center">H. Indep.</th><th class="text-center">H. Asist.</th><th class="text-center">HTE</th><th class="text-center">H. Acad.</th></tr></thead><tbody></tbody></table></div>');
                                    gruposVer[trayecto].forEach(uc => {
                                        const hte = (parseInt(uc.mal_hora_independiente) || 0) + (parseInt(uc.mal_hora_asistida) || 0);
                                        const fila = $('<tr>');
                                        fila.append($('<td>').text(uc.uc_nombre));
                                        fila.append($('<td class="text-center">').text(uc.mal_hora_independiente));
                                        fila.append($('<td class="text-center">').text(uc.mal_hora_asistida));
                                        fila.append($('<td class="text-center">').text(hte));
                                        fila.append($('<td class="text-center">').text(uc.mal_hora_academica));
                                        tabla.find('tbody').append(fila);
                                    });
                                    tabContent += `<div class="tab-pane fade ${primerItem ? 'show active' : ''}" id="${idTab}" role="tabpanel">${tabla.html()}</div>`;
                                    primerItem = false;
                                }
                            }
                            navTabs += '</ul>';
                            tabContent += '</div>';
                            cuerpoModal.html(navTabs + tabContent);
                        }
                        $("#modalVerMalla").modal("show");
                    } else {
                        muestraMensaje('error', 5000, 'Error', 'No se pudo cargar la información de la malla.');
                    }
                } catch (e) {
                    muestraMensaje("error", 10000, "Error de Comunicación", "La respuesta del servidor no es válida.");
                }
            },
            error: () => muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo conectar con el servidor."),
            complete: () => boton.prop('disabled', false)
        });
    }
}

function limpiaModal1() {
    estadoInicialModificar = null;
    $('#modal1').off('shown.bs.modal');
    $("#f")[0].reset();
    $(".form-control").removeClass("is-invalid is-valid").prop("disabled", false);
    $(".validation-span").empty();
    $('#pagina2, #botones-pagina2').hide();
    $('#pagina1, #botones-pagina1').show();
    $('#mallaTabsMod, #mallaTabContentMod').empty();
    actualizarSelectUC();
}

function validarPagina1() {
    let esValido = true;
    if (validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $("#mal_codigo"), $("#smalcodigo"), "El código permite de 2 a 20 caracteres.") == 0) esValido = false;
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/, $("#mal_nombre"), $("#smalnombre"), "El formato permite de 5 a 30 caracteres.") == 0) esValido = false;
    if (validarkeyup(/^[1-9][0-9]{0,3}$/, $("#mal_cohorte"), $("#smalcohorte"), "Debe ser un número entre 1 y 999.") == 0) esValido = false;
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-¿?¡!(){}\[\]]{5,255}$/, $("#mal_descripcion"), $("#smaldescripcion"), "El formato permite de 5 a 255 caracteres.") == 0) esValido = false;
    return esValido;
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