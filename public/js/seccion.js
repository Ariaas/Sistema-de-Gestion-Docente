// =================================================================
// ARCHIVO: public/js/seccion.js (CORREGIDO CON AÑO SIMPLIFICADO)
// =================================================================

function muestraMensaje(tipo, tiempo, titulo, mensaje) {
    if (typeof Swal !== 'undefined' && Swal.fire) {
        Swal.fire({
            icon: tipo,
            title: titulo,
            html: mensaje, 
            timer: tiempo,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } else {
        alert(`${titulo}: ${mensaje.replace(/<br\/>/g, "\n")}`);
    }
}

function getPrefijoSeccion(codigo) {
    if (!codigo) return 'IN';
    const trayecto = String(codigo).charAt(0);
    if (trayecto === '3' || trayecto === '4') {
        return 'IIN';
    }
    return 'IN';
}


function formatTime12Hour(time24) {
    if (!time24) return "";
    const [hoursStr, minutesStr] = time24.split(':');
    let hours = parseInt(hoursStr, 10);
    const minutes = minutesStr;
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${hours}:${minutes} ${ampm}`;
}

let currentClickedCell = null;
let horarioContenidoGuardado = new Map();
let allUcs = [], allEspacios = [], allDocentes = [], allSecciones = [], allTurnos = [], allCohortes = [];
let modalDataLoaded = false;

function Listar() {
    const datos = new FormData();
    datos.append("accion", "consultar_agrupado");
    enviaAjax(datos, null);
}

function destruyeDT() {
    if ($.fn.DataTable.isDataTable("#tablaListadoHorarios")) {
        $("#tablaListadoHorarios").DataTable().destroy();
    }
}

function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaListadoHorarios")) {
        $("#tablaListadoHorarios").DataTable({
            language: {
                lengthMenu: "Mostrar _MENU_ registros",
                zeroRecords: "No hay horarios registrados",
                info: "Mostrando _PAGE_ de _PAGES_",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registos totales)",
                search: "Buscar:",
                paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
            },
            responsive: true,
            autoWidth: false
        });
    }
}

function normalizeDayKey(day) {
    if (!day) return '';
    return day.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function inicializarTablaHorario(filtroTurno = 'todos', targetTableId = "#tablaHorario", isViewOnly = false) {
    const tbody = $(`${targetTableId} tbody`);
    tbody.empty();
    let turnosFiltrados = allTurnos;

    if (filtroTurno !== 'todos') {
        turnosFiltrados = allTurnos.filter(turno => {
            const horaInicio = parseInt(turno.tur_horainicio.substring(0, 2), 10);
            if (filtroTurno === 'mañana') return horaInicio < 13;
            if (filtroTurno === 'tarde') return horaInicio >= 13 && horaInicio < 18;
            if (filtroTurno === 'noche') return horaInicio >= 18;
            return false;
        });
    }
    
    turnosFiltrados.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));
    const dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

    turnosFiltrados.forEach((turno) => {
        const row = $("<tr>");
        row.append(`<td>${formatTime12Hour(turno.tur_horainicio)} - ${formatTime12Hour(turno.tur_horafin)}</td>`);
        dias.forEach((dia) => {
            const cell = $("<td>").attr("data-franja-inicio", turno.tur_horainicio).attr("data-dia-nombre", dia);
            if (!isViewOnly) {
                cell.addClass("celda-horario"); 
            }
            const dia_key = normalizeDayKey(dia);
            const key = `${turno.tur_horainicio.substring(0, 5)}-${turno.tur_horafin.substring(0, 5)}-${dia_key}`;
            if (horarioContenidoGuardado.has(key)) {
                const { html, data } = horarioContenidoGuardado.get(key);
                cell.html(html).data("horario-data", data);
            }
            row.append(cell);
        });
        tbody.append(row);
    });
    
    if (!isViewOnly) {
        $(".celda-horario").off("click").on("click", onCeldaHorarioClick);
    }
}

function validarEntradaHorario() {
   
    $("#conflicto-docente-warning, #conflicto-espacio-warning, #conflicto-uc-warning").hide().html('');
    $("#btnGuardarClase").prop("disabled", false);

    const ucId = $("#modalSeleccionarUc").val();
    const docId = $("#modalSeleccionarDocente").val();
    const espId = $("#modalSeleccionarEspacio").val();
    const dia = normalizeDayKey($("#modalDia").val());
    const secId = $("#sec_codigo_hidden").val();
    
    if (!currentClickedCell) return;

  
    if (ucId) {
        let ucDuplicada = false;
        const turnoCompleto = allTurnos.find(t => t.tur_horainicio === currentClickedCell.data("franja-inicio"));
        if (!turnoCompleto) return; 
        const diaKeyActual = normalizeDayKey(currentClickedCell.data('dia-nombre'));
        const keyActual = `${turnoCompleto.tur_horainicio.substring(0, 5)}-${turnoCompleto.tur_horafin.substring(0, 5)}-${diaKeyActual}`;

        
        horarioContenidoGuardado.forEach((valor, key) => {
            if (valor.data.uc_codigo === ucId && key !== keyActual) {
                ucDuplicada = true;
            }
        });

        if (ucDuplicada) {
            const ucInfo = allUcs.find(u => u.uc_codigo === ucId);
            const nombreUc = ucInfo ? ucInfo.uc_nombre : `código ${ucId}`;
            $('#conflicto-uc-warning').html(`<strong>Inválido:</strong> La UC <strong>${nombreUc}</strong> ya fue asignada. Una UC solo puede ser asignada una vez por horario.`).show();
            $("#btnGuardarClase").prop("disabled", true);
            return; 
        }
    }
   
    if ((!docId && !espId) || !dia || !secId) {
        return; 
    }
    
    const turnoCompleto = allTurnos.find(t => t.tur_horainicio === currentClickedCell.data("franja-inicio"));
    if (!turnoCompleto) return;
    const horaInicio = turnoCompleto.tur_horainicio;

    const datos = new FormData();
    datos.append("accion", "validar_clase_en_vivo");
    datos.append("doc_cedula", docId);
    datos.append("esp_codigo", espId);
    datos.append("dia", dia);
    datos.append("hora_inicio", horaInicio.substring(0, 5));
    datos.append("sec_codigo", secId);

    $.ajax({
        url: "", type: "POST", data: datos, contentType: false, processData: false,
        success: function(respuesta) {
            if (respuesta.conflicto === true) {
                if (respuesta.tipo === 'docente') {
                    $("#conflicto-docente-warning").html(respuesta.mensaje).show();
                } else if (respuesta.tipo === 'espacio') {
                    $("#conflicto-espacio-warning").html(respuesta.mensaje).show();
                }
                $("#btnGuardarClase").prop("disabled", true);
            }
        },
        error: function() {
            console.error("Error al validar en tiempo real con el servidor.");
        }
    });
}

function onCeldaHorarioClick() {
    currentClickedCell = $(this);
    const data = currentClickedCell.data("horario-data");
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const turnoCompleto = allTurnos.find(t => t.tur_horainicio === franjaInicio);
    
    $("#formularioEntradaHorario")[0].reset();
    $("#conflicto-docente-warning, #conflicto-espacio-warning, #conflicto-uc-warning").hide().html('');
    $("#btnGuardarClase").prop("disabled", false);

    $("#modalFranjaHoraria").val(`${formatTime12Hour(turnoCompleto.tur_horainicio)} - ${formatTime12Hour(turnoCompleto.tur_horafin)}`);
    $("#modalDia").val($(this).data("dia-nombre"));

    $("#modalSeleccionarDocente").empty().append('<option value="">Seleccionar Docente</option>').val('');
    allDocentes.forEach(doc => $("#modalSeleccionarDocente").append(`<option value="${doc.doc_cedula}">${doc.doc_nombre} ${doc.doc_apellido}</option>`));
    
    $("#modalSeleccionarEspacio").empty().append('<option value="">Seleccionar Espacio</option>').val('');
    allEspacios.forEach(esp => $("#modalSeleccionarEspacio").append(`<option value="${esp.esp_codigo}">${esp.esp_codigo} (${esp.esp_tipo})</option>`));
    
    $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente</option>').prop('disabled', true);

    if (data) {
        $("#modalSeleccionarDocente").val(data.doc_cedula);
        cargarUcPorDocente(data.doc_cedula, () => {
             $("#modalSeleccionarUc").val(data.uc_codigo).trigger('change');
        });
        $("#modalSeleccionarEspacio").val(data.esp_codigo);
        $("#btnEliminarEntrada").show();
    } else {
        $("#btnEliminarEntrada").hide();
    }
    $("#modalEntradaHorario").modal("show");
}

function cargarUcPorDocente(docCedula, callback) {
    const ucSelect = $("#modalSeleccionarUc");
    if (!docCedula) {
        ucSelect.empty().append('<option value="">Seleccione un docente</option>').prop("disabled", true);
        if (callback) callback();
        return;
    }

    const secCodigo = $("#sec_codigo_hidden").val();
    
    const datos = new FormData();
    datos.append("accion", "obtener_uc_por_docente");
    datos.append("doc_cedula", docCedula);
    datos.append("sec_codigo_actual", secCodigo);

    $.ajax({ url: "", type: "POST", data: datos, contentType: false, processData: false,
        success: function(respuesta) {
            ucSelect.empty();
            if (respuesta.resultado === 'ok' && respuesta.ucs_docente.length > 0) {
                ucSelect.append('<option value="">Seleccionar UC</option>');
                respuesta.ucs_docente.forEach(uc => {
                    let faseTexto = (uc.uc_periodo === '1') ? ' (Fase 1)' : (uc.uc_periodo === '2') ? ' (Fase 2)' : ' (Anual)';
                    ucSelect.append(`<option value="${uc.uc_codigo}">${uc.uc_nombre}${faseTexto}</option>`);
                });
                ucSelect.prop("disabled", false);
            } else {
                const mensaje = respuesta.mensaje_uc || "No hay UCs asignadas";
                ucSelect.append(`<option value="">${mensaje}</option>`).prop("disabled", true);
            }
            if (callback) callback();
        },
        error: function() {
            ucSelect.empty().append('<option value="">Error al cargar UCs</option>').prop("disabled", true);
            if (callback) callback();
        }
    });
}

function generarCellContent(clase) {
    const uc = allUcs.find(u => u.uc_codigo == clase.uc_codigo)?.uc_nombre || 'N/A';
    const esp = allEspacios.find(e => e.esp_codigo == clase.esp_codigo)?.esp_codigo || 'N/A';
    const doc = allDocentes.find(d => d.doc_cedula == clase.doc_cedula);
    const doc_nombre = doc ? `${doc.doc_nombre} ${doc.doc_apellido}` : 'N/A';
    return `<p class="m-0" style="font-size:0.8em;"><strong>${uc}</strong></p><small class="text-muted" style="font-size:0.7em;">${esp} / ${doc_nombre}</small>`;
}

function limpiaModalPrincipal() {
    $("#form-horario")[0].reset();
    $("#accion, #sec_codigo_hidden").val("");
    $("#seccion_principal_id").prop('disabled', true).val("");
    $("#filtro_turno").val("todos").prop('disabled', false);
    $("#proceso").show().removeClass("btn-danger btn-primary btn-success").text('');
}

// ▼▼▼ FUNCIÓN MODIFICADA para recibir el valor del año y no solo el texto ▼▼▼
function abrirModalHorarioParaNuevaSeccion(secCodigo, secCantidad, anioTexto, anioValue) {
    limpiaModalPrincipal();
    horarioContenidoGuardado.clear(); 
    
    // Se usan los valores del año para reconstruir el objeto de sección
    const [anioAnio, anioTipo] = anioValue.split('|');
    allSecciones.push({ sec_codigo: secCodigo, sec_cantidad: secCantidad, ani_anio: anioAnio, ani_tipo: anioTipo });
    
    let turnoSeleccionado = 'mañana';
    if (secCodigo && secCodigo.length > 1) {
        const segundoDigito = secCodigo.toString().charAt(1);
        if (segundoDigito === '2') turnoSeleccionado = 'tarde';
        else if (segundoDigito === '3') turnoSeleccionado = 'noche';
    }

    const prefijo = getPrefijoSeccion(secCodigo);
    const textoSeccion = `${prefijo}${secCodigo} (${secCantidad} Est.) (Año ${anioTexto})`;
    $("#seccion_principal_id").empty().append(`<option value="${secCodigo}" selected>${textoSeccion}</option>`).prop('disabled', true);
    $("#filtro_turno").val(turnoSeleccionado).prop('disabled', true); 
    
    $("#modalHorarioGlobalTitle").text(`Paso 2: Registrar Horario para la sección ${prefijo}${secCodigo}`);
    $("#accion").val("modificar");
    $("#proceso").text("GUARDAR HORARIO").data("action-type", "modificar").addClass("btn-success");
    $("#sec_codigo_hidden").val(secCodigo);
    $("#modal-horario").data("mode", "registrar");

    inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
    $("#modal-horario").modal("show");
}

function verificarRequisitosInicialesSeccion() {
    const mainContent = $(".main-content");
    const countDocentes = parseInt(mainContent.data('count-docentes'), 10);
    const countEspacios = parseInt(mainContent.data('count-espacios'), 10);
    const countTurnos = parseInt(mainContent.data('count-turnos'), 10);
    const countAnios = parseInt(mainContent.data('count-anios'), 10);
    const countMallas = parseInt(mainContent.data('count-mallas'), 10);

    const mensajesError = [];

    if (countMallas === 0) {
        mensajesError.push('No hay <b>mallas curriculares activas</b> registradas.');
    }
    if (countDocentes === 0) {
        mensajesError.push('No hay <b>docentes</b> registrados.');
    }
    if (countEspacios === 0) {
        mensajesError.push('No hay <b>espacios (aulas/labs)</b> registrados.');
    }
    if (countTurnos === 0) {
        mensajesError.push('No hay <b>turnos</b> registrados.');
    }
    if (countAnios === 0) {
        mensajesError.push('No hay un <b>año académico activo</b> configurado.');
    }

    if (mensajesError.length > 0) {
        const btnRegistrar = $("#btnIniciarRegistro");
        const btnUnir = $("#btnAbrirModalUnir");
        const mensajeTooltip = "Debe registrar primero los datos maestros requeridos.";

        btnRegistrar.prop('disabled', true).attr('title', mensajeTooltip);
        btnUnir.prop('disabled', true).attr('title', mensajeTooltip);
        
        btnRegistrar.addClass('disabled-look');
        btnUnir.addClass('disabled-look');

        let mensajeHtml = "Para poder gestionar secciones, primero debe configurar lo siguiente en sus respectivos módulos:<br><br><ul class='list-unstyled text-start ps-4'>";
        mensajesError.forEach(msg => {
            mensajeHtml += `<li><i class="fas fa-exclamation-circle text-warning me-2"></i>${msg}</li>`;
        });
        mensajeHtml += "</ul>";

        Swal.fire({
            icon: 'warning',
            title: 'Faltan Datos para Continuar',
            html: mensajeHtml,
            confirmButtonText: 'Entendido'
        });
    }
}

function enviaAjax(datos, boton) {
    let textoOriginal;
    if (boton) {
        textoOriginal = boton.html();
        boton.prop("disabled", true).text("Procesando...");
    }

    $.ajax({
        url: "", type: "POST", contentType: false, data: datos, processData: false,
        success: function(respuesta) {
            try {
                if (typeof respuesta !== 'object') throw new Error("Respuesta no es JSON.");
                
                if (respuesta.resultado == "consultar_agrupado") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    if (respuesta.mensaje && respuesta.mensaje.length > 0) {
                        allSecciones = respuesta.mensaje.map(s => ({...s, sec_id: s.sec_codigo}));
                        respuesta.mensaje.forEach(item => {
                            const prefijo = getPrefijoSeccion(item.sec_codigo);
                            const botones_accion = `
                              <button class="btn btn-info btn-sm ver-horario" data-sec-codigo="${item.sec_codigo}" title="Ver"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg></button>
                              <button class="btn btn-warning btn-sm modificar-horario" data-sec-codigo="${item.sec_codigo}" title="Modificar"> <img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                              <button class="btn btn-danger btn-sm eliminar-horario" data-sec-codigo="${item.sec_codigo}" title="Eliminar"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                            // ▼▼▼ MODIFICADO para mostrar solo el año ▼▼▼
                            $("#resultadoconsulta").append(`<tr><td>${prefijo}${item.sec_codigo}</td><td>${item.sec_cantidad||'N/A'}</td><td>${item.ani_anio||'N/A'}</td><td class="text-nowrap">${botones_accion}</td></tr>`);
                        });
                    }
                    crearDT();
                } else if (respuesta.resultado.endsWith("_ok")) {
                    $('.modal').modal('hide');
                    muestraMensaje("success", 4000, "¡ÉXITO!", respuesta.mensaje);
                    Listar();
                } else if (respuesta.resultado == "error") {
                    muestraMensaje("error", 8000, "¡ERROR!", respuesta.mensaje);
                }
            } catch (e) {
                muestraMensaje("error", 8000, "Error de Procesamiento", "La respuesta del servidor no es válida: " + e.message);
                console.error("Error en success de AJAX:", e);
                console.error("Respuesta del servidor:", respuesta);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) { 
            muestraMensaje("error", 5000, "Error de Conexión", `No se pudo comunicar con el servidor. ${textStatus}: ${errorThrown}`); 
            console.error("Error en AJAX:", textStatus, errorThrown, jqXHR.responseText);
        },
        complete: function() {
            if (boton) {
                boton.prop("disabled", false).html(textoOriginal);
            }
        }
    });
}


$(document).ready(function() {
    
    function validarFormularioRegistro() {
        const form = document.getElementById('formRegistroSeccion');
        const codigoInput = document.getElementById('codigoSeccion');
        const alerta = $("#alerta-cohorte");
        const btnGuardar = $("#btnGuardarSeccion");

        let isCohorteValid = false;
        if (codigoInput.value.length === 4) {
            const cohorteIngresado = parseInt(codigoInput.value.charAt(3), 10);
            if (allCohortes.includes(cohorteIngresado)) {
                alerta.hide();
                isCohorteValid = true;
            } else {
                const cohortesDisponibles = allCohortes.join(', ');
                alerta.text(`Cohorte '${cohorteIngresado}' no registrado. Válidos: ${cohortesDisponibles}`).show();
                isCohorteValid = false;
            }
        } else {
            alerta.hide();
            isCohorteValid = false;
        }

        const isFormValid = form.checkValidity();
        
        if (isCohorteValid && isFormValid) {
            btnGuardar.prop("disabled", false);
        } else {
            btnGuardar.prop("disabled", true);
        }
    }

    verificarRequisitosInicialesSeccion();
    $('#resultadoconsulta').html('<tr><td colspan="4" class="text-center">Cargando datos...</td></tr>');
    
    const datosIniciales = new FormData();
    datosIniciales.append("accion", "obtener_datos_selects");
    $.ajax({
        url: "", type: "POST", data: datosIniciales, contentType: false, processData: false,
        success: function(respuesta) {
            allUcs = respuesta.ucs.map(u => ({...u, uc_id: u.uc_codigo})) || [];
            allEspacios = respuesta.espacios.map(e => ({...e, esp_id: e.esp_codigo})) || [];
            allDocentes = respuesta.docentes.map(d => ({...d, doc_id: d.doc_cedula})) || [];
            allTurnos = respuesta.turnos || [];
            allCohortes = respuesta.cohortes.map(c => parseInt(c, 10)) || [];
            modalDataLoaded = true;
            Listar(); 
        },
        error: function() {
            $('#resultadoconsulta').html('<tr><td colspan="4" class="text-center text-danger">Error crítico al cargar datos. Recargue.</td></tr>');
            muestraMensaje("error", 0, "Error Crítico", "No se pudieron cargar los datos iniciales.");
        }
    });

    $(document).on('click', '.ver-horario, .modificar-horario, .eliminar-horario, #btnIniciarRegistro, #btnAbrirModalUnir', function(e) {
        if (!modalDataLoaded && !$(this).prop('disabled')) {
            e.stopPropagation();
            muestraMensaje('info', 2000, 'Un momento...', 'Cargando datos necesarios, por favor intente de nuevo en un segundo.');
            return;
        }
    });

    $('#btnIniciarRegistro').on('click', function() {
        $("#formRegistroSeccion")[0].reset();
        $("#alerta-cohorte").hide();
        $("#btnGuardarSeccion").prop("disabled", true);
        $("#modalRegistroSeccion").modal("show");
    });
    
    $('#formRegistroSeccion').on('input change', function() {
        validarFormularioRegistro();
    });
    
    $('#btnAbrirModalUnir').on('click', function() {
        const container = $("#unirSeccionesContainer");
        container.empty();
        const gruposCompatibles = allSecciones.reduce((acc, seccion) => {
            const trayecto = seccion.sec_codigo.toString().charAt(0);
            const key = `${seccion.ani_anio}-${seccion.ani_tipo}-${trayecto}`;
            if (!acc[key]) {
                acc[key] = { nombre: `Año ${seccion.ani_anio} - Trayecto ${trayecto}`, secciones: [] };
            }
            acc[key].secciones.push(seccion);
            return acc;
        }, {});

        let hayGrupos = false;
        for (const key in gruposCompatibles) {
            const grupo = gruposCompatibles[key];
            if (grupo.secciones.length >= 2) {
                hayGrupos = true;
                container.append(`<h6 class="text-primary mt-2">${grupo.nombre}</h6>`);
                grupo.secciones.forEach(s => {
                    const prefijo = getPrefijoSeccion(s.sec_codigo);
                    const checkboxHtml = `<div class="form-check"><input class="form-check-input" type="checkbox" name="secciones_a_unir[]" value="${s.sec_codigo}" id="check_sec_${s.sec_codigo}" data-group-key="${key}"><label class="form-check-label" for="check_sec_${s.sec_codigo}">${prefijo}${s.sec_codigo} (${s.sec_cantidad} Est.)</label></div>`;
                    container.append(checkboxHtml);
                });
                container.append('<hr class="my-2">');
            }
        }
        if (!hayGrupos) container.html('<p class="text-muted">No hay grupos de 2 o más secciones compatibles para unir.</p>');
        $("#unirSeccionOrigen").empty().append('<option value="" disabled selected>Marque primero las secciones a unir...</option>');
        $("#modalUnirHorarios").modal("show");
    });

    $(document).on('click', '.ver-horario, .modificar-horario, .eliminar-horario', function() {
        const sec_codigo = $(this).data('sec-codigo');
        const isView = $(this).hasClass('ver-horario');
        const isModify = $(this).hasClass('modificar-horario');
        const isDelete = $(this).hasClass('eliminar-horario');
        
        const seccionData = allSecciones.find(s => s.sec_codigo == sec_codigo);
        if (!seccionData) return;

        let turnoSeleccionado = 'todos';
        if (seccionData.sec_codigo) {
            const segundoDigito = seccionData.sec_codigo.toString().charAt(1);
            if (segundoDigito === '1') turnoSeleccionado = 'mañana';
            else if (segundoDigito === '2') turnoSeleccionado = 'tarde';
            else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        }

        const datos = new FormData();
        datos.append("accion", "consultar_detalles");
        datos.append("sec_codigo", sec_codigo);

        $.ajax({
            url: "", type: "POST", data: datos, contentType: false, processData: false,
            success: function(respuesta) {
                if (respuesta.resultado === 'ok' && Array.isArray(respuesta.mensaje)) {
                    horarioContenidoGuardado.clear();
                    respuesta.mensaje.forEach(clase => {
                        if (clase.hora_inicio && clase.hora_fin && clase.dia) {
                            const dia_key = normalizeDayKey(clase.dia);
                            const key = `${clase.hora_inicio.substring(0, 5)}-${clase.hora_fin.substring(0, 5)}-${dia_key}`;
                            horarioContenidoGuardado.set(key, { html: generarCellContent(clase), data: clase });
                        }
                    });
                    const prefijo = getPrefijoSeccion(seccionData.sec_codigo);
                    // ▼▼▼ MODIFICADO para mostrar solo el año ▼▼▼
                    const seccionTexto = `${prefijo}${seccionData.sec_codigo} (${seccionData.sec_cantidad} Est.) (Año ${seccionData.ani_anio})`;
                    if (isDelete) {
                        $("#detallesParaEliminar").html(`<p class="mb-1"><strong>Código:</strong> ${prefijo}${seccionData.sec_codigo}</p><p class="mb-1"><strong>Estudiantes:</strong> ${seccionData.sec_cantidad}</p><p class="mb-0"><strong>Año:</strong> ${seccionData.ani_anio}</p>`);
                        inicializarTablaHorario(turnoSeleccionado, "#tablaEliminarHorario", true);
                        $("#btnProcederEliminacion").data('sec-codigo', sec_codigo);
                        $("#modalConfirmarEliminar").modal('show');
                    } else if(isView) {
                        $("#modalVerHorarioTitle").text(`Horario: ${seccionTexto}`);
                        inicializarTablaHorario(turnoSeleccionado, "#tablaVerHorario", true);
                        $("#modalVerHorario").modal("show");
                    } else if (isModify) {
                        limpiaModalPrincipal();
                        $("#sec_codigo_hidden").val(sec_codigo);
                        $("#seccion_principal_id").html(`<option value="${sec_codigo}">${seccionTexto}</option>`).prop('disabled', true);
                        $("#filtro_turno").val(turnoSeleccionado).prop('disabled', true);
                        $("#modalHorarioGlobalTitle").text(`Modificar Horario: ${prefijo}${seccionData.sec_codigo}`);
                        $("#accion").val("modificar");
                        $("#proceso").text("GUARDAR CAMBIOS").addClass("btn-primary");
                        inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
                        $("#modal-horario").modal("show");
                    }
                }
            }
        });
    });


    $("#modalSeleccionarDocente, #modalSeleccionarUc, #modalSeleccionarEspacio").on("change", validarEntradaHorario);
    
    $('#filtro_turno').on("change", function() { inicializarTablaHorario($(this).val(), "#tablaHorario", false); });
    
    $('#modal-horario, #modalVerHorario, #modalConfirmarEliminar, #modalUnirHorarios').on('hidden.bs.modal', function () {
        if ($(this).find('form').length > 0) $(this).find('form').removeClass('was-validated')[0].reset();
        $("#unirSeccionesContainer input[type='checkbox']").prop('disabled', false);
    });

    $("#formRegistroSeccion").on("submit", function(e) {
        e.preventDefault();
        const datosSeccion = new FormData(this);
        const boton = $("#btnGuardarSeccion");
        const textoOriginal = boton.text();
        boton.prop("disabled", true).text("Guardando...");
        $.ajax({
            url: "", type: "POST", data: datosSeccion, contentType: false, processData: false,
            success: function(respuesta) {
                if (respuesta.resultado === 'registrar_seccion_ok') {
                    $("#modalRegistroSeccion").modal("hide");
                    // ▼▼▼ MODIFICADO para pasar el valor y el texto del año por separado ▼▼▼
                    const anioOption = $("#anioId option:selected");
                    const anioTexto = anioOption.text();
                    const anioValue = anioOption.val();
                    abrirModalHorarioParaNuevaSeccion(respuesta.nuevo_codigo, respuesta.nueva_cantidad, anioTexto, anioValue);
                } else {
                    muestraMensaje("error", 5000, "Error al Registrar", respuesta.mensaje);
                }
            },
            error: function() { muestraMensaje("error", 5000, "Error de Conexión", "No se pudo comunicar con el servidor."); },
            complete: function() { boton.prop("disabled", false).text(textoOriginal); }
        });
    });

    $("#btnProcederEliminacion").on("click", function() {
        const sec_codigo = $(this).data('sec-codigo');
        $('#modalConfirmarEliminar').modal('hide');
        setTimeout(() => {
            const seccion = allSecciones.find(s=>s.sec_codigo == sec_codigo);
            const prefijo = getPrefijoSeccion(seccion.sec_codigo);
            Swal.fire({
                title: '¿Está realmente seguro?',
                html: `Esta acción es irreversible y eliminará permanentemente la sección <strong>${prefijo}${seccion.sec_codigo}</strong>.`,
                icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6', confirmButtonText: 'Sí, confirmar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const datos = new FormData();
                    datos.append("accion", "eliminar_seccion_y_horario");
                    datos.append("sec_codigo", sec_codigo);
                    enviaAjax(datos, null);
                }
            });
        }, 500);
    });

    $("#formularioEntradaHorario").on("submit", function(e) {
        e.preventDefault();
        const turnoCompleto = allTurnos.find(t => t.tur_horainicio === currentClickedCell.data("franja-inicio"));
        const horarioData = {
            doc_cedula: $("#modalSeleccionarDocente").val(), uc_codigo: $("#modalSeleccionarUc").val(),
            esp_codigo: $("#modalSeleccionarEspacio").val(), dia: $("#modalDia").val(),
            hora_inicio: turnoCompleto.tur_horainicio.substring(0, 5), hora_fin: turnoCompleto.tur_horafin.substring(0, 5)
        };
        if (!horarioData.doc_cedula || !horarioData.uc_codigo || !horarioData.esp_codigo) {
            muestraMensaje("error", 3000, "Datos Incompletos", "Debe seleccionar docente, UC y espacio.");
            return;
        }
        const cellContent = generarCellContent(horarioData);
        const dia_key = normalizeDayKey(horarioData.dia);
        const key = `${horarioData.hora_inicio}-${horarioData.hora_fin}-${dia_key}`;
        currentClickedCell.html(cellContent).data("horario-data", horarioData);
        horarioContenidoGuardado.set(key, { html: cellContent, data: horarioData });
        $("#modalEntradaHorario").modal("hide");
    });

    $("#btnEliminarEntrada").on("click", function() {
        if (currentClickedCell) {
            const data = currentClickedCell.data("horario-data");
            const dia_key = normalizeDayKey(data.dia);
            const key = `${data.hora_inicio}-${data.hora_fin}-${dia_key}`;
            horarioContenidoGuardado.delete(key);
            currentClickedCell.empty().removeData("horario-data");
            $("#modalEntradaHorario").modal("hide");
        }
    });

    $("#proceso").on("click", function() {
        
        const ucsEnHorario = new Set();
        let ucDuplicada = null;
        Array.from(horarioContenidoGuardado.values()).forEach(v => {
            if (ucsEnHorario.has(v.data.uc_codigo)) {
                ucDuplicada = v.data.uc_codigo;
            }
            ucsEnHorario.add(v.data.uc_codigo);
        });

        if (ucDuplicada) {
            const ucInfo = allUcs.find(u => u.uc_codigo === ucDuplicada);
            const nombreUc = ucInfo ? ucInfo.uc_nombre : `código ${ucDuplicada}`;
            muestraMensaje("error", 6000, "Horario Inválido", `La unidad curricular <strong>${nombreUc}</strong> no puede ser asignada más de una vez.`);
            return;
        }

        const accion = $("#accion").val();
        const datos = new FormData();
        datos.append("accion", accion);
        datos.append("sec_codigo", $("#sec_codigo_hidden").val());
        const clasesAEnviar = Array.from(horarioContenidoGuardado.values()).map(v => v.data);
        datos.append("items_horario", JSON.stringify(clasesAEnviar));
        enviaAjax(datos, $(this));
    });

    $("#unirSeccionesContainer").on("change", "input[type='checkbox']", function() {
        const selectOrigen = $("#unirSeccionOrigen");
        const allCheckboxes = $("#unirSeccionesContainer input[type='checkbox']");
        const checkedBoxes = allCheckboxes.filter(":checked");
        selectOrigen.empty();
        if (checkedBoxes.length === 0) {
            allCheckboxes.prop('disabled', false);
            selectOrigen.append('<option value="" disabled selected>Marque primero...</option>');
        } else {
            const groupKey = checkedBoxes.first().data('group-key');
            allCheckboxes.not(`[data-group-key="${groupKey}"]`).prop('disabled', true);
            selectOrigen.append('<option value="" disabled selected>Seleccione una opción...</option>');
            checkedBoxes.each(function() {
                const labelText = $(this).siblings('label').text().trim();
                selectOrigen.append(`<option value="${$(this).val()}">${labelText}</option>`);
            });
        }
    });

    $("#formUnirHorarios").on("submit", function(e) {
        e.preventDefault();
        if ($("#unirSeccionesContainer input[type='checkbox']:checked").length < 2) {
            muestraMensaje("error", 4000, "Error", "Debe marcar al menos 2 secciones.");
            return;
        }
        if (this.checkValidity() === false) { e.stopPropagation(); $(this).addClass('was-validated'); return; }
        $(this).removeClass('was-validated');
        enviaAjax(new FormData(this), $("#btnConfirmarUnion"));
    });
});