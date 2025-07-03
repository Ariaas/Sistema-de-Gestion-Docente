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
let allUcs = [], allEspacios = [], allDocentes = [], allSecciones = [], allTurnos = [];
let isNewSectionRegistration = false;

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

function realizarValidacionEnVivo() {
    const docId = $("#modalSeleccionarDocente").val();
    const espId = $("#modalSeleccionarEspacio").val();
    const dia = normalizeDayKey($("#modalDia").val());
    const secId = $("#seccion_id_hidden").val();

    $("#conflicto-docente-warning").hide().html('');
    $("#conflicto-espacio-warning").hide().html('');
    $("#btnGuardarClase").prop("disabled", false);

    if ((!docId && !espId) || !dia || !secId || !currentClickedCell) {
        return;
    }
    
    const turnoCompleto = allTurnos.find(t => t.tur_horainicio === currentClickedCell.data("franja-inicio"));
    if (!turnoCompleto) return;
    const horaInicio = turnoCompleto.tur_horainicio;

    const datos = new FormData();
    datos.append("accion", "validar_clase_en_vivo");
    datos.append("doc_id", docId);
    datos.append("esp_id", espId);
    datos.append("dia", dia);
    datos.append("hora_inicio", horaInicio.substring(0, 5));
    datos.append("sec_id", secId);

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
            console.error("Error al validar en tiempo real.");
        }
    });
}

function onCeldaHorarioClick() {
    currentClickedCell = $(this);
    const data = currentClickedCell.data("horario-data");
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const turnoCompleto = allTurnos.find(t => t.tur_horainicio === franjaInicio);
    
    $("#formularioEntradaHorario")[0].reset();
    $("#conflicto-docente-warning").hide().html('');
    $("#conflicto-espacio-warning").hide().html('');
    $("#btnGuardarClase").prop("disabled", false);

    $("#modalFranjaHoraria").val(`${formatTime12Hour(turnoCompleto.tur_horainicio)} - ${formatTime12Hour(turnoCompleto.tur_horafin)}`);
    $("#modalDia").val($(this).data("dia-nombre"));

    $("#modalSeleccionarDocente").empty().append('<option value="">Seleccionar Docente</option>').val('');
    allDocentes.forEach(doc => $("#modalSeleccionarDocente").append(`<option value="${doc.doc_id}">${doc.doc_nombre} ${doc.doc_apellido}</option>`));
    
    $("#modalSeleccionarEspacio").empty().append('<option value="">Seleccionar Espacio</option>').val('');
    allEspacios.forEach(esp => $("#modalSeleccionarEspacio").append(`<option value="${esp.esp_id}">${esp.esp_codigo} (${esp.esp_tipo})</option>`));
    
    $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente</option>').prop('disabled', true);

    if (data) {
        $("#modalSeleccionarDocente").val(data.doc_id);
        cargarUcPorDocente(data.doc_id, () => {
             $("#modalSeleccionarUc").val(data.uc_id);
             realizarValidacionEnVivo(); 
        });
        $("#modalSeleccionarEspacio").val(data.esp_id);
        $("#btnEliminarEntrada").show();
        realizarValidacionEnVivo();
    } else {
        $("#btnEliminarEntrada").hide();
    }
    $("#modalEntradaHorario").modal("show");
}

function cargarUcPorDocente(docId, callback) {
    const ucSelect = $("#modalSeleccionarUc");
    if (!docId) {
        ucSelect.empty().append('<option value="">Seleccione un docente</option>').prop("disabled", true);
        if (callback) callback();
        return;
    }

    const secId = $("#seccion_id_hidden").val();
    const seccionActual = allSecciones.find(s => s.sec_id == secId);
    let seccionTrayecto = null;
    if (seccionActual && seccionActual.sec_codigo.length > 0) {
        seccionTrayecto = seccionActual.sec_codigo.charAt(0);
    }

    const datos = new FormData();
    datos.append("accion", "obtener_uc_por_docente");
    datos.append("doc_id", docId);
    
    $.ajax({ url: "", type: "POST", data: datos, contentType: false, processData: false,
        success: function(respuesta) {
            ucSelect.empty();
            if (respuesta.resultado === 'ok' && respuesta.ucs_docente.length > 0) {
                
                const ucsFiltradas = seccionTrayecto 
                    ? respuesta.ucs_docente.filter(uc => uc.uc_trayecto == seccionTrayecto)
                    : respuesta.ucs_docente;

                if (ucsFiltradas.length > 0) {
                    ucSelect.append('<option value="">Seleccionar UC</option>');
                    ucsFiltradas.forEach(uc => {
                        let faseTexto = '';
                        if (uc.uc_periodo === '1') {
                            faseTexto = ' (Fase 1)';
                        } else if (uc.uc_periodo === '2') {
                            faseTexto = ' (Fase 2)';
                        } else if (uc.uc_periodo === 'anual') {
                            faseTexto = ' (Anual)';
                        }
                        ucSelect.append(`<option value="${uc.uc_id}">${uc.uc_codigo} - ${uc.uc_nombre}${faseTexto}</option>`);
                    });
                    ucSelect.prop("disabled", false);
                } else {
                    ucSelect.append(`<option value="">Docente sin UCs del Trayecto ${seccionTrayecto}</option>`).prop("disabled", true);
                }

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
    const uc = allUcs.find(u => u.uc_id == clase.uc_id)?.uc_nombre || 'N/A';
    const esp = allEspacios.find(e => e.esp_id == clase.esp_id)?.esp_codigo || 'N/A';
    const doc = allDocentes.find(d => d.doc_id == clase.doc_id);
    const doc_nombre = doc ? `${doc.doc_nombre} ${doc.doc_apellido}` : 'N/A';
    return `<p class="m-0" style="font-size:0.8em;"><strong>${uc}</strong></p><small class="text-muted" style="font-size:0.7em;">${esp} / ${doc_nombre}</small>`;
}
function limpiaModalPrincipal() {
    $("#form-horario")[0].reset();
    $("#accion, #seccion_id_hidden").val("");
    $("#seccion_principal_id").prop('disabled', true).val("");
    $("#filtro_turno").val("todos").prop('disabled', false);
    horarioContenidoGuardado.clear();
    $("#proceso").show().removeClass("btn-danger btn-primary btn-success").text('');
    isNewSectionRegistration = false; 
    $("#detalles-tab").show();
    $("#horario-tab").show();
    $("#detalles-tab-btn").tab('show');
}
function abrirModalHorarioParaNuevaSeccion(secId, secCodigo, anioAnio, secCantidad) {
    limpiaModalPrincipal();
    isNewSectionRegistration = true; 
    
    allSecciones.push({ sec_id: secId, sec_codigo: secCodigo, ani_anio: anioAnio, sec_cantidad: secCantidad });
    
    let turnoSeleccionado = 'mañana';
    if (secCodigo && secCodigo.length > 1) {
        const segundoDigito = secCodigo.charAt(1);
        if (segundoDigito === '2') turnoSeleccionado = 'tarde';
        else if (segundoDigito === '3') turnoSeleccionado = 'noche';
    }

    const textoSeccion = `IN${secCodigo} (${secCantidad} Est.) (Año ${anioAnio})`;
    $("#seccion_principal_id").empty().append(`<option value="${secId}" selected>${textoSeccion}</option>`).prop('disabled', true);
    $("#filtro_turno").val(turnoSeleccionado).prop('disabled', true); 
    
    $("#modalHorarioGlobalTitle").text(`Paso 2: Registrar Horario para la sección IN${secCodigo}`);
    $("#accion").val("modificar");
    $("#proceso").text("GUARDAR HORARIO").data("action-type", "modificar").addClass("btn-success");
    
    $("#seccion_id_hidden").val(secId);
    $("#modal-horario").data("mode", "registrar");

    $("#detalles-tab").hide();
    $("#horario-tab-btn").tab('show');

    inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
    $("#modal-horario").modal("show");
}

function validarHorarioCliente() {
    const clases = Array.from(horarioContenidoGuardado.values()).map(v => v.data);
    const conflictos = [];

    for (let i = 0; i < clases.length; i++) {
        for (let j = i + 1; j < clases.length; j++) {
            const claseA = clases[i];
            const claseB = clases[j];

            if (claseA.dia === claseB.dia && claseA.hora_inicio === claseB.hora_inicio) {
                if (claseA.doc_id === claseB.doc_id) {
                    const docente = allDocentes.find(d => d.doc_id == claseA.doc_id);
                    const nombreDocente = docente ? `${docente.doc_nombre} ${docente.doc_apellido}` : `ID ${claseA.doc_id}`;
                    conflictos.push(`El docente <strong>${nombreDocente}</strong> está asignado dos veces el <strong>${claseA.dia}</strong> a las <strong>${formatTime12Hour(claseA.hora_inicio)}</strong>.`);
                }
                if (claseA.esp_id === claseB.esp_id) {
                    const espacio = allEspacios.find(e => e.esp_id == claseA.esp_id);
                    const nombreEspacio = espacio ? espacio.esp_codigo : `ID ${claseA.esp_id}`;
                    conflictos.push(`El espacio <strong>${nombreEspacio}</strong> está asignado dos veces el <strong>${claseA.dia}</strong> a las <strong>${formatTime12Hour(claseA.hora_inicio)}</strong>.`);
                }
            }
        }
    }

    if (conflictos.length > 0) {
        const mensajesUnicos = [...new Set(conflictos)];
        muestraMensaje("error", 8000, "Conflicto de Horario Detectado", mensajesUnicos.join("<br>"));
        return false;
    }

    return true;
}


$(document).ready(function() {
    Listar();
    
    const datosIniciales = new FormData();
    datosIniciales.append("accion", "obtener_datos_selects");
    $.ajax({ url: "", type: "POST", data: datosIniciales, contentType: false, processData: false,
        success: function(respuesta) {
            allUcs = respuesta.ucs || [];
            allEspacios = respuesta.espacios || [];
            allDocentes = respuesta.docentes || [];
            allSecciones = respuesta.secciones || [];
            allTurnos = respuesta.turnos || [];
        }
    });

    $("#modalSeleccionarDocente, #modalSeleccionarEspacio").on("change", function() {
        realizarValidacionEnVivo();
    });

    $("#modalSeleccionarDocente").on("change", function() {
        cargarUcPorDocente($(this).val(), null);
    });

    $("#filtro_turno").on("change", function() {
        inicializarTablaHorario($(this).val(), "#tablaHorario", false);
    });
    
    $("#btnIniciarRegistro").on("click", function() {
        $("#formRegistroSeccion")[0].reset();
        $("#modalRegistroSeccion").modal("show");
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
                    const anioTexto = $("#anioId option:selected").text();
                    abrirModalHorarioParaNuevaSeccion(respuesta.nuevo_id, respuesta.nuevo_codigo, anioTexto, respuesta.nueva_cantidad);
                } else {
                    muestraMensaje("error", 5000, "Error al Registrar", respuesta.mensaje);
                }
            },
            error: function() { muestraMensaje("error", 5000, "Error de Conexión", "No se pudo comunicar con el servidor."); },
            complete: function() { boton.prop("disabled", false).text(textoOriginal); }
        });
    });

    $(document).on('click', '.ver-horario, .modificar-horario, .eliminar-horario', function() {
        const sec_id = $(this).data('sec-id');
        const isView = $(this).hasClass('ver-horario');
        const isModify = $(this).hasClass('modificar-horario');
        const isDelete = $(this).hasClass('eliminar-horario');
        
        const seccionData = allSecciones.find(s => s.sec_id == sec_id);
        if (!seccionData) return;

        let turnoSeleccionado = 'todos';
        if (seccionData.sec_codigo) {
            const segundoDigito = seccionData.sec_codigo.charAt(1);
            if (segundoDigito === '1') turnoSeleccionado = 'mañana';
            else if (segundoDigito === '2') turnoSeleccionado = 'tarde';
            else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        }

        const datos = new FormData();
        datos.append("accion", "consultar_detalles");
        datos.append("sec_id", sec_id);

        $.ajax({
            url: "", type: "POST", data: datos, contentType: false, processData: false,
            success: function(respuesta) {
                if (respuesta.resultado === 'ok' && Array.isArray(respuesta.mensaje)) {
                    horarioContenidoGuardado.clear();
                    respuesta.mensaje.forEach(clase => {
                        const turnoAsociado = allTurnos.find(t => t.tur_id == clase.tur_id);
                        if(turnoAsociado) {
                             const dia_key = normalizeDayKey(clase.dia);
                             const key = `${turnoAsociado.tur_horainicio.substring(0, 5)}-${turnoAsociado.tur_horafin.substring(0, 5)}-${dia_key}`;
                             horarioContenidoGuardado.set(key, { html: generarCellContent(clase), data: clase });
                        }
                    });
                    
                    const seccionTexto = `IN${seccionData.sec_codigo} (${seccionData.sec_cantidad} Est.) (Año ${seccionData.ani_anio})`;

                    if (isDelete) {
                        const detallesDiv = $("#detallesParaEliminar");
                        detallesDiv.html(
                            `<p class="mb-1"><strong>Código de Sección:</strong> IN${seccionData.sec_codigo}</p>` +
                            `<p class="mb-1"><strong>Cantidad de Estudiantes:</strong> ${seccionData.sec_cantidad}</p>` +
                            `<p class="mb-0"><strong>Año:</strong> ${seccionData.ani_anio}</p>`
                        );

                        inicializarTablaHorario(turnoSeleccionado, "#tablaEliminarHorario", true);

                        $("#btnProcederEliminacion").data('sec-id', sec_id);
                        $("#modalConfirmarEliminar").modal('show');

                    } else if(isView) {
                        $("#modalVerHorarioTitle").text(`Horario: ${seccionTexto}`);
                        inicializarTablaHorario(turnoSeleccionado, "#tablaVerHorario", true);
                        $("#modalVerHorario").modal("show");

                    } else if (isModify) {
                        limpiaModalPrincipal();
                        $("#seccion_id_hidden").val(sec_id);
                        $("#seccion_principal_id").html(`<option value="${sec_id}">${seccionTexto}</option>`).prop('disabled', true);
                        $("#filtro_turno").val(turnoSeleccionado).prop('disabled', true);
                        $("#modal-horario").data("mode", "modify");
                        $("#modalHorarioGlobalTitle").text(`Modificar Horario y Sección: IN${seccionData.sec_codigo}`);
                        $("#accion").val("modificar");
                        $("#proceso").text("GUARDAR CAMBIOS").data("action-type", "modificar").addClass("btn-primary");
                        
                        $("#modSecId").val(seccionData.sec_id);
                        $("#modCodigoSeccion").val(seccionData.sec_codigo);
                        $("#modCantidadSeccion").val(seccionData.sec_cantidad);
                        $("#modAnioId").val(seccionData.ani_id);

                        inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
                        $("#modal-horario").modal("show");
                    }
                }
            }
        });
    });

    $("#btnProcederEliminacion").on("click", function() {
        const sec_id = $(this).data('sec-id');
        const seccionData = allSecciones.find(s => s.sec_id == sec_id);
        if (!seccionData) return;

        $('#modalConfirmarEliminar').modal('hide');

        // Retraso para asegurar que el modal se oculte antes de mostrar el SweetAlert
        setTimeout(() => {
            Swal.fire({
                title: '¿Está realmente seguro?',
                html: `Esta acción es irreversible y eliminará permanentemente la sección <strong>IN${seccionData.sec_codigo}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const datos = new FormData();
                    datos.append("accion", "eliminar_seccion_y_horario");
                    datos.append("sec_id", sec_id);
                    enviaAjax(datos, null); // No se pasa el botón porque está dentro de la alerta
                }
            });
        }, 500);
    });

    $("#formularioEntradaHorario").on("submit", function(e) {
        e.preventDefault();
        const turnoCompleto = allTurnos.find(t => t.tur_horainicio === currentClickedCell.data("franja-inicio"));
        const horarioData = {
            doc_id: $("#modalSeleccionarDocente").val(), uc_id: $("#modalSeleccionarUc").val(),
            esp_id: $("#modalSeleccionarEspacio").val(), dia: $("#modalDia").val(),
            hora_inicio: turnoCompleto.tur_horainicio.substring(0, 5), hora_fin: turnoCompleto.tur_horafin.substring(0, 5),
            tur_id: turnoCompleto.tur_id
        };
        if (!horarioData.doc_id || !horarioData.uc_id || !horarioData.esp_id) {
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
        if (!validarHorarioCliente()) {
            return;
        }

        const accion = $("#accion").val();
        const datos = new FormData();
        datos.append("accion", accion);
        datos.append("seccion_id", $("#seccion_id_hidden").val());
        
        const clasesAEnviar = Array.from(horarioContenidoGuardado.values()).map(v => {
            v.data.dia = normalizeDayKey(v.data.dia);
            return v.data;
        });
        datos.append("items_horario", JSON.stringify(clasesAEnviar));
        enviaAjax(datos, $(this));
    });

    $('#modal-horario, #modalVerHorario, #modalConfirmarEliminar, #modalUnirHorarios').on('hidden.bs.modal', function () {
        if ($(this).find('form').length > 0) {
            $(this).find('form').removeClass('was-validated')[0].reset();
        }
    });

    $("#btnAbrirModalUnir").on("click", function() {
        const container = $("#unirSeccionesContainer");
        container.empty();
        
        const gruposCompatibles = allSecciones.reduce((acc, seccion) => {
            const trayecto = seccion.sec_codigo.charAt(0);
            const key = `${seccion.ani_id}-${trayecto}`;
            if (!acc[key]) {
                acc[key] = {
                    nombre: `Año ${seccion.ani_anio} - Trayecto ${trayecto}`,
                    secciones: []
                };
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
                    const checkboxHtml = `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="secciones_a_unir[]" value="${s.sec_id}" id="check_sec_${s.sec_id}">
                            <label class="form-check-label" for="check_sec_${s.sec_id}">
                                IN${s.sec_codigo} (${s.sec_cantidad} Est.)
                            </label>
                        </div>`;
                    container.append(checkboxHtml);
                });
                container.append('<hr class="my-2">');
            }
        }
        
        if (!hayGrupos) {
            container.html('<p class="text-muted">No hay grupos de 2 o más secciones compatibles para unir.</p>');
        }

        $("#unirSeccionOrigen").empty().append('<option value="" disabled selected>Marque primero las secciones a unir...</option>');
        $("#modalUnirHorarios").modal("show");
    });

    $("#unirSeccionesContainer").on("change", "input[type='checkbox']", function() {
        const selectOrigen = $("#unirSeccionOrigen");
        selectOrigen.empty().append('<option value="" disabled selected>Seleccione una opción...</option>');

        const checkedBoxes = $("#unirSeccionesContainer input[type='checkbox']:checked");
        
        if (checkedBoxes.length > 0) {
            checkedBoxes.each(function() {
                const labelText = $(this).siblings('label').text().trim();
                selectOrigen.append(`<option value="${$(this).val()}">${labelText}</option>`);
            });
        } else {
            selectOrigen.empty().append('<option value="" disabled selected>Marque primero las secciones a unir...</option>');
        }
    });

    $("#formUnirHorarios").on('submit', function(e) {
        e.preventDefault();
        const seccionesSeleccionadas = $("#unirSeccionesContainer input[type='checkbox']:checked");

        if (seccionesSeleccionadas.length < 2) {
            muestraMensaje("error", 4000, "Error de validación", "Debe marcar al menos 2 secciones para unir.");
            return;
        }

        if (this.checkValidity() === false) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        $(this).removeClass('was-validated');
        
        const datos = new FormData(this);
        enviaAjax(datos, $("#btnConfirmarUnion"));
    });
});

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
                if (typeof respuesta !== 'object') throw new Error("La respuesta no es un objeto JSON válido.");
                
                if (respuesta.resultado == "consultar_agrupado") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    if (respuesta.mensaje && respuesta.mensaje.length > 0) {
                        allSecciones = respuesta.mensaje; 
                        respuesta.mensaje.forEach(item => {
                            const botones_accion = `
                              <button class="btn btn-info btn-sm ver-horario" data-sec-id="${item.sec_id}" title="Ver Horario"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg></button>
                              <button class="btn btn-warning btn-sm modificar-horario" data-sec-id="${item.sec_id}" title="Modificar Horario"> <img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                              <button class="btn btn-danger btn-sm eliminar-horario" data-sec-id="${item.sec_id}" title="Eliminar Horario"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                              
                            $("#resultadoconsulta").append(`<tr><td>IN${item.sec_codigo}</td><td>${item.sec_cantidad||'N/A'}</td><td>${item.ani_anio||'N/A'}</td><td class="text-nowrap">${botones_accion}</td></tr>`);
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
                muestraMensaje("error", 8000, "Error de Procesamiento", "La respuesta del servidor no es válida.");
            }
        },
        error: function() {
            muestraMensaje("error", 5000, "Error de Conexión", "No se pudo comunicar con el servidor.");
        },
        complete: function() {
            if (boton) {
                boton.prop("disabled", false).html(textoOriginal);
            }
        }
    });
}