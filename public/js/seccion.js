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
let allUcs = [],
    allEspacios = [],
    allDocentes = [],
    allSecciones = [],
    allTurnos = [],
    allCohortes = [];
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
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
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
    const celdasProcesadas = new Set();

    const horarioMapeado = new Map();
    for (const clase of horarioContenidoGuardado.values()) {
        if (clase.data && clase.data.dia && clase.data.hora_inicio) {
            const dia_key = normalizeDayKey(clase.data.dia);
            const key_inicio = `${clase.data.hora_inicio.substring(0, 5)}-${dia_key}`;
            horarioMapeado.set(key_inicio, clase);
        }
    }

    turnosFiltrados.forEach((turno, rowIndex) => {
        const row = $("<tr>");
        row.append(`<td>${formatTime12Hour(turno.tur_horainicio)} - ${formatTime12Hour(turno.tur_horafin)}</td>`);

        dias.forEach((dia) => {
            const dia_key = normalizeDayKey(dia);
            const key_actual = `${turno.tur_horainicio.substring(0, 5)}-${dia_key}`;

            if (celdasProcesadas.has(key_actual)) {
                return;
            }

            const cell = $("<td>").attr("data-franja-inicio", turno.tur_horainicio).attr("data-dia-nombre", dia);
            if (!isViewOnly) {
                cell.addClass("celda-horario");
            }

            if (horarioMapeado.has(key_actual)) {
                const {
                    html,
                    data
                } = horarioMapeado.get(key_actual);
                const bloques_span = data.bloques_span || 1;

                if (bloques_span > 1) {
                    cell.attr("rowspan", bloques_span);
                    for (let i = 1; i < bloques_span; i++) {
                        const turnoFuturo = turnosFiltrados[rowIndex + i];
                        if (turnoFuturo) {
                            const key_futuro = `${turnoFuturo.tur_horainicio.substring(0, 5)}-${dia_key}`;
                            celdasProcesadas.add(key_futuro);
                        }
                    }
                }
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
    const espIdJson = $("#modalSeleccionarEspacio").val();
    const dia = $("#modalDia").val();
    const secId = $("#sec_codigo_hidden").val();

    if (!currentClickedCell) return;
    const datosClaseActual = currentClickedCell.data("horario-data");

   
    if (ucId) {
        let ucDuplicada = false;
        const claveEdicion = datosClaseActual ? `${datosClaseActual.hora_inicio.substring(0,5)}-${normalizeDayKey(datosClaseActual.dia)}` : null;

        for (const [key, valor] of horarioContenidoGuardado.entries()) {
            if (valor.data && valor.data.uc_codigo === ucId && key !== claveEdicion) {
                ucDuplicada = true;
                break;
            }
        }

        if (ucDuplicada) {
            const ucInfo = allUcs.find(u => u.uc_codigo === ucId);
            const nombreUc = ucInfo ? ucInfo.uc_nombre : `código ${ucId}`;
            $('#conflicto-uc-warning').html(`<strong>Inválido:</strong> La UC <strong>${nombreUc}</strong> ya fue asignada. Una UC solo puede ser asignada una vez por horario.`).show();
            $("#btnGuardarClase").prop("disabled", true);
            return; 
        }
    }

    if (!docId || !ucId || !espIdJson) {
        return;
    }

  
    const franjaInicioActual = currentClickedCell.data("franja-inicio");
    const indiceTurnoActual = allTurnos.findIndex(t => t.tur_horainicio === franjaInicioActual);
    
    if (indiceTurnoActual === -1) return;

    const horaInicio = allTurnos[indiceTurnoActual].tur_horainicio;
    
    const espId = JSON.parse(espIdJson);
    const datos = new FormData();
    datos.append("accion", "validar_clase_en_vivo");
    datos.append("doc_cedula", docId);
    datos.append("esp_numero", espId.numero);
    datos.append("esp_tipo", espId.tipo);
    datos.append("esp_edificio", espId.edificio);
    datos.append("dia", dia);
    datos.append("hora_inicio", horaInicio);
    datos.append("sec_codigo", secId);
    datos.append("uc_codigo", ucId);

     $.ajax({
        url: "",
        type: "POST",
        data: datos,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            if (respuesta.conflicto === true) {
                const warningDiv = respuesta.tipo === 'docente' ? $("#conflicto-docente-warning") : $("#conflicto-espacio-warning");
                warningDiv.html(respuesta.mensaje).show();
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
    allEspacios.forEach(esp => $("#modalSeleccionarEspacio").append(`<option value='${JSON.stringify({numero: esp.numero, tipo: esp.tipo, edificio: esp.edificio})}'>${esp.numero} (${esp.tipo} - ${esp.edificio})</option>`));

    $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente</option>').prop('disabled', true);

    if (data) {
        $("#modalSeleccionarDocente").val(data.doc_cedula);
        cargarUcPorDocente(data.doc_cedula, () => {
            $("#modalSeleccionarUc").val(data.uc_codigo).trigger('change');
        });
        if (data.espacio && data.espacio.numero) {
            $('#modalSeleccionarEspacio').val(JSON.stringify({
                numero: data.espacio.numero,
                tipo: data.espacio.tipo,
                edificio: data.espacio.edificio
            }));
        }
        $("#modalBloquesClase").val(data.bloques_span || 1);
        $("#btnEliminarEntrada").show();
    } else {
        $("#modalBloquesClase").val(1);
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

    $.ajax({
        url: "",
        type: "POST",
        data: datos,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            ucSelect.empty();
            if (respuesta.resultado === 'ok' && respuesta.ucs_docente.length > 0) {
                ucSelect.append('<option value="">Seleccionar UC</option>');
                respuesta.ucs_docente.forEach(uc => {
                    let faseTexto = '';
                    const periodo = uc.uc_periodo;
                    if (periodo === 'Fase I') {
                        faseTexto = ' (Fase I)';
                    } else if (periodo === 'Fase II') {
                        faseTexto = ' (Fase II)';
                    } else if (periodo === 'Anual') {
                        faseTexto = ' (Anual)';
                    } else if (periodo === '0') {
                        faseTexto = ' (Inicial)';
                    }
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
    const doc = allDocentes.find(d => d.doc_cedula == clase.doc_cedula);
    const doc_nombre = doc ? `${doc.doc_nombre} ${doc.doc_apellido}` : 'N/A';


    let codigoEspacioFormateado = 'N/A';
    if (clase.espacio && clase.espacio.numero && clase.espacio.tipo && clase.espacio.edificio) {
        const tipo = clase.espacio.tipo.toLowerCase();
        const edificio = clase.espacio.edificio;
        const numero = clase.espacio.numero;

        if (tipo === 'aula') {
            codigoEspacioFormateado = `${edificio.charAt(0).toUpperCase()}-${numero}`;
        } else if (tipo === 'laboratorio') {
            codigoEspacioFormateado = `${tipo.charAt(0).toUpperCase()}-${numero}`;
        } else {

            codigoEspacioFormateado = numero;
        }
    }

    return `<p class="m-0" style="font-size:0.8em;"><strong>${uc}</strong></p><small class="text-muted" style="font-size:0.7em;">${codigoEspacioFormateado} / ${doc_nombre}</small>`;
}

function limpiaModalPrincipal() {
    $("#form-horario")[0].reset();
    $("#accion, #sec_codigo_hidden").val("");
    $("#seccion_principal_id").prop('disabled', true).val("");
    $("#filtro_turno").val("mañana").prop('disabled', false);
    $("#proceso").show().removeClass("btn-danger btn-primary btn-success").text('');
}

function abrirModalHorarioParaNuevaSeccion(secCodigo, secCantidad, anioTexto, anioValue) {
    limpiaModalPrincipal();
    horarioContenidoGuardado.clear();

    const [anioAnio, anioTipo] = anioValue.split('|');
    allSecciones.push({
        sec_codigo: secCodigo,
        sec_cantidad: secCantidad,
        ani_anio: anioAnio,
        ani_tipo: anioTipo
    });

    let turnoSeleccionado = 'mañana';
    if (secCodigo && secCodigo.length > 1) {
        const segundoDigito = secCodigo.toString().charAt(1);
        if (segundoDigito === '2') turnoSeleccionado = 'tarde';
        else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        else if (segundoDigito === '1' || segundoDigito === '4' || segundoDigito === '0') turnoSeleccionado = 'mañana';
    }

    const prefijo = getPrefijoSeccion(secCodigo);
    const textoSeccion = `${prefijo}${secCodigo} (${secCantidad} Est.) (Año ${anioTexto})`;
    $("#seccion_principal_id").empty().append(`<option value="${secCodigo}" selected>${textoSeccion}</option>`).prop('disabled', true);
    $("#cantidadSeccionModificar").val(secCantidad);
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
        url: "",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        success: function(respuesta) {
            try {
                if (typeof respuesta !== 'object') throw new Error("Respuesta no es JSON.");

                if (respuesta.resultado == "consultar_agrupado") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    if (respuesta.mensaje && respuesta.mensaje.length > 0) {
                        allSecciones = respuesta.mensaje.map(s => ({ ...s,
                            sec_id: s.sec_codigo
                        }));
                        respuesta.mensaje.forEach(item => {
                            const prefijo = getPrefijoSeccion(item.sec_codigo);
                            const botones_accion = `
                               <button class="btn btn-icon btn-info ver-horario" data-sec-codigo="${item.sec_codigo}" title="Ver Horario">
                                 <img src="public/assets/icons/eye.svg" alt="Ver Horario">
                               </button>
                               <button class="btn btn-icon btn-warning modificar-horario" data-sec-codigo="${item.sec_codigo}" title="Modificar Horario"> 
                                 <img src="public/assets/icons/edit.svg" alt="Modificar">
                               </button>
                               <button class="btn btn-icon btn-danger eliminar-horario" data-sec-codigo="${item.sec_codigo}" title="Eliminar Horario">
                                 <img src="public/assets/icons/trash.svg" alt="Eliminar">
                               </button>`;

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

        const cantidadInput = document.getElementById('cantidadSeccion');
        const cantidadError = $('#cantidad-seccion-error');
        if (cantidadInput.validity.rangeOverflow || cantidadInput.validity.rangeUnderflow) {
            cantidadError.show();
        } else {
            cantidadError.hide();
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
        url: "",
        type: "POST",
        data: datosIniciales,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            allUcs = respuesta.ucs.map(u => ({ ...u,
                uc_id: u.uc_codigo
            })) || [];
            allEspacios = respuesta.espacios || [];
            allDocentes = respuesta.docentes.map(d => ({ ...d,
                doc_id: d.doc_cedula
            })) || [];
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

    $('#cantidadSeccionModificar').on('input', function() {
        const input = $(this);
        const errorDiv = $('#cantidad-seccion-modificar-error');
        const cantidad = parseInt(input.val(), 10);
        const isValid = !isNaN(cantidad) && cantidad >= 0 && cantidad <= 99;

        if (!isValid && input.val() !== '') {
            errorDiv.show();
        } else {
            errorDiv.hide();
        }
    });

    $('#btnAbrirModalUnir').on('click', function() {
        const container = $("#unirSeccionesContainer");
        container.empty();
        const gruposCompatibles = allSecciones.reduce((acc, seccion) => {
            const codigoStr = seccion.sec_codigo.toString();
            const trayecto = codigoStr.charAt(0);
            const turno = codigoStr.charAt(1);

            const turnosNombres = {
                '1': 'Mañana',
                '2': 'Tarde',
                '3': 'Noche'
            };
            const turnoNombre = turnosNombres[turno] || 'Desconocido';

            const key = `${seccion.ani_anio}-${seccion.ani_tipo}-${trayecto}-${turno}`;

            if (!acc[key]) {
                acc[key] = {
                    nombre: `Año ${seccion.ani_anio} / Trayecto ${trayecto} / Turno ${turnoNombre}`,
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
            if (segundoDigito === '1' || segundoDigito === '4' || segundoDigito === '0') turnoSeleccionado = 'mañana';
            else if (segundoDigito === '2') turnoSeleccionado = 'tarde';
            else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        }

        const datos = new FormData();
        datos.append("accion", "consultar_detalles");
        datos.append("sec_codigo", sec_codigo);

        $.ajax({
            url: "",
            type: "POST",
            data: datos,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if (respuesta.resultado === 'ok' && Array.isArray(respuesta.mensaje)) {
                    horarioContenidoGuardado.clear();
                    
                    const clasesAgrupadas = new Map();
                    respuesta.mensaje.forEach(clase => {
                        const inicio = new Date(`1970-01-01T${clase.hora_inicio}`);
                        const fin = new Date(`1970-01-01T${clase.hora_fin}`);
                        const diffMinutes = (fin - inicio) / (1000 * 60);
                        const bloques = Math.round(diffMinutes / 40);
                        
                        clase.bloques_span = bloques > 0 ? bloques : 1;

                        const dia_key = normalizeDayKey(clase.dia);
                        const key = `${clase.hora_inicio.substring(0, 5)}-${dia_key}`;
                        
                        clasesAgrupadas.set(key, {
                           html: generarCellContent(clase),
                           data: clase
                        });
                    });
                    horarioContenidoGuardado = clasesAgrupadas;

                    const prefijo = getPrefijoSeccion(seccionData.sec_codigo);
                    const seccionTexto = `${prefijo}${seccionData.sec_codigo} (${seccionData.sec_cantidad} Est.) (Año ${seccionData.ani_anio})`;
                    if (isDelete) {
                        $("#detallesParaEliminar").html(`<p class="mb-1"><strong>Código:</strong> ${prefijo}${seccionData.sec_codigo}</p><p class="mb-1"><strong>Estudiantes:</strong> ${seccionData.sec_cantidad}</p><p class="mb-0"><strong>Año:</strong> ${seccionData.ani_anio}</p>`);
                        inicializarTablaHorario(turnoSeleccionado, "#tablaEliminarHorario", true);
                        $("#btnProcederEliminacion").data('sec-codigo', sec_codigo);
                        $("#modalConfirmarEliminar").modal('show');
                    } else if (isView) {
                        $("#modalVerHorarioTitle").text(`Horario: ${seccionTexto}`);
                        inicializarTablaHorario(turnoSeleccionado, "#tablaVerHorario", true);
                        $("#modalVerHorario").modal("show");
                    } else if (isModify) {
                        limpiaModalPrincipal();
                        $("#sec_codigo_hidden").val(sec_codigo);
                        $("#seccion_principal_id").html(`<option value="${sec_codigo}">${seccionTexto}</option>`).prop('disabled', true);
                        $("#cantidadSeccionModificar").val(seccionData.sec_cantidad);
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


    $("#modalSeleccionarDocente, #modalSeleccionarUc, #modalSeleccionarEspacio, #modalBloquesClase").on("change", validarEntradaHorario);

    $('#filtro_turno').on("change", function() {
        inicializarTablaHorario($(this).val(), "#tablaHorario", false);
    });
    $("#modalSeleccionarDocente").on("change", function() {
        const docCedula = $(this).val();
        cargarUcPorDocente(docCedula, () => {
            validarEntradaHorario();
        });
    });
    $('#modal-horario, #modalVerHorario, #modalConfirmarEliminar, #modalUnirHorarios').on('hidden.bs.modal', function() {
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
            url: "",
            type: "POST",
            data: datosSeccion,
            contentType: false,
            processData: false,
            success: function(respuesta) {
                if (respuesta.resultado === 'registrar_seccion_ok') {
                    $("#modalRegistroSeccion").modal("hide");
                    const anioOption = $("#anioId option:selected");
                    const anioTexto = anioOption.text();
                    const anioValue = anioOption.val();
                    abrirModalHorarioParaNuevaSeccion(respuesta.nuevo_codigo, respuesta.nueva_cantidad, anioTexto, anioValue);
                    if (respuesta.horario_aleatorio && respuesta.horario_aleatorio.horario) {
                        horarioContenidoGuardado.clear();
                        respuesta.horario_aleatorio.horario.forEach(clase => {
                            const inicio = new Date(`1970-01-01T${clase.hora_inicio}:00`);
                            const fin = new Date(`1970-01-01T${clase.hora_fin}:00`);
                            const diffMinutes = (fin - inicio) / (1000 * 60);
                            clase.bloques_span = Math.round(diffMinutes / 40) || 1;

                            if (clase.hora_inicio && clase.dia) {
                                const dia_key = normalizeDayKey(clase.dia);
                                const key = `${clase.hora_inicio.substring(0, 5)}-${dia_key}`;
                                horarioContenidoGuardado.set(key, {
                                    html: generarCellContent(clase),
                                    data: clase
                                });
                            }
                        });
                        let turnoSeleccionado = 'mañana';
                        if (respuesta.nuevo_codigo && respuesta.nuevo_codigo.length > 1) {
                            const segundoDigito = respuesta.nuevo_codigo.toString().charAt(1);
                            if (segundoDigito === '2') turnoSeleccionado = 'tarde';
                            else if (segundoDigito === '3') turnoSeleccionado = 'noche';
                            else if (segundoDigito === '1' || segundoDigito === '4' || segundoDigito === '0') turnoSeleccionado = 'mañana';
                        }
                        inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
                    }
                } else {
                    muestraMensaje("error", 5000, "Error al Registrar", respuesta.mensaje);
                }
            },
            error: function() {
                muestraMensaje("error", 5000, "Error de Conexión", "No se pudo comunicar con el servidor.");
            },
            complete: function() {
                boton.prop("disabled", false).text(textoOriginal);
            }
        });
    });

    $("#btnProcederEliminacion").on("click", function() {
        const sec_codigo = $(this).data('sec-codigo');
        $('#modalConfirmarEliminar').modal('hide');
        setTimeout(() => {
            const seccion = allSecciones.find(s => s.sec_codigo == sec_codigo);
            const prefijo = getPrefijoSeccion(seccion.sec_codigo);
            Swal.fire({
                title: '¿Está realmente seguro?',
                html: `Esta acción es irreversible y eliminará permanentemente la sección <strong>${prefijo}${seccion.sec_codigo}</strong>.`,
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
                    datos.append("sec_codigo", sec_codigo);
                    enviaAjax(datos, null);
                }
            });
        }, 500);
    });

    $("#formularioEntradaHorario").on("submit", function(e) {
        e.preventDefault();

        const bloquesSeleccionados = parseInt($("#modalBloquesClase").val(), 10) || 1;
        const franjaInicioActual = currentClickedCell.data("franja-inicio");
        const diaActual = $("#modalDia").val();
        const dia_key = normalizeDayKey(diaActual);

        const indiceTurnoActual = allTurnos.findIndex(t => t.tur_horainicio === franjaInicioActual);

        if (indiceTurnoActual + bloquesSeleccionados > allTurnos.length) {
            muestraMensaje("error", 4000, "Error de Duración", "La duración seleccionada excede los bloques disponibles en el horario.");
            return;
        }

        const turnoDeInicio = allTurnos[indiceTurnoActual];
        const turnoDeFin = allTurnos[indiceTurnoActual + bloquesSeleccionados - 1];
        const horaFinReal = turnoDeFin.tur_horafin;

        const espacioSeleccionado = $("#modalSeleccionarEspacio").val();
        const horarioData = {
            doc_cedula: $("#modalSeleccionarDocente").val(),
            uc_codigo: $("#modalSeleccionarUc").val(),
            espacio: espacioSeleccionado ? JSON.parse(espacioSeleccionado) : null,
            dia: diaActual,
            hora_inicio: turnoDeInicio.tur_horainicio,
            hora_fin: horaFinReal,
            bloques_span: bloquesSeleccionados
        };

        if (!horarioData.doc_cedula || !horarioData.uc_codigo || !horarioData.espacio) {
            muestraMensaje("error", 3000, "Datos Incompletos", "Debe seleccionar docente, UC y espacio.");
            return;
        }

        const key = `${horarioData.hora_inicio.substring(0, 5)}-${dia_key}`;
        const cellContent = generarCellContent(horarioData);

        horarioContenidoGuardado.set(key, {
            html: cellContent,
            data: horarioData
        });

        const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
        inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);

        $("#modalEntradaHorario").modal("hide");
    });

    $("#btnEliminarEntrada").on("click", function() {
        if (currentClickedCell) {
            const dataOriginal = currentClickedCell.data("horario-data");
            if (dataOriginal) {
                const dia_key = normalizeDayKey(dataOriginal.dia);
                const key = `${dataOriginal.hora_inicio.substring(0, 5)}-${dia_key}`;
                horarioContenidoGuardado.delete(key);
            }

            const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
            inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
            $("#modalEntradaHorario").modal("hide");
        }
    });

   
    $("#proceso").on("click", function() {
        const cantidadInput = $('#cantidadSeccionModificar');
        const cantidad = parseInt(cantidadInput.val(), 10);
        if (isNaN(cantidad) || cantidad < 0 || cantidad > 99) {
            muestraMensaje("error", 5000, "Error de Validación", `La cantidad de estudiantes debe ser un número entre 0 y 99.`);
            $('#cantidad-seccion-modificar-error').show();
            return;
        } else {
            $('#cantidad-seccion-modificar-error').hide();
        }
        
        const ucsAsignadas = new Set();
        let ucDuplicada = null;
    
        for (const v of horarioContenidoGuardado.values()) {
            if (v.data && v.data.uc_codigo) {
                const uc = v.data.uc_codigo;
                if (ucsAsignadas.has(uc)) {
                    ucDuplicada = uc;
                    break; 
                }
                ucsAsignadas.add(uc);
            }
        }
    
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
    
        if (accion === 'modificar') {
            datos.append("cantidadSeccion", $("#cantidadSeccionModificar").val());
        }
    
        const clasesAEnviar = Array.from(horarioContenidoGuardado.values())
                                     .map(v => v.data)
                                     .filter(item => item && item.uc_codigo && item.hora_inicio && item.hora_fin); 
        
        
        clasesAEnviar.forEach(item => {
            item.hora_inicio = item.hora_inicio.substring(0, 5);
            item.hora_fin = item.hora_fin.substring(0, 5);
        });

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
        if (this.checkValidity() === false) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }
        $(this).removeClass('was-validated');
        enviaAjax(new FormData(this), $("#btnConfirmarUnion"));
    });
});