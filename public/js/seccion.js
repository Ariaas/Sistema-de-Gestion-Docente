function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({ 
        icon: tipo, 
        title: titulo, 
        html: mensaje, 
        timer: duracion, 
        timerProgressBar: true,
        showConfirmButton: false
    });
}

function getScheduleStateString() {
    const cantidad = $("#cantidadSeccionModificar").val();
    let clases = [];
    for (const claseArray of horarioContenidoGuardado.values()) {
        claseArray.forEach(claseObj => clases.push(claseObj.data));
    }
    clases.sort((a, b) => (a.dia + a.hora_inicio).localeCompare(b.dia + b.hora_inicio));

    
    const bloques = bloquesDeLaTablaActual;

    return JSON.stringify({ cantidad, clases, bloques });
}
function checkForScheduleChanges() {
    const initialState = $('#modal-horario').data('initial-state');
    const currentState = getScheduleStateString();
    if (initialState !== currentState) {
        $("#proceso").prop("disabled", false);
    } else {
        $("#proceso").prop("disabled", true);
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
let bloquesDeLaTablaActual = [];
let allUcs = [], allEspacios = [], allDocentes = [], allSecciones = [], allTurnos = [], allCohortes = [], ocupacionGlobalCompleta = [];
let modalDataLoaded = false;
let isSplittingProcess = false;
let hasSaved = false;

let draggedClassData = null;
let draggedClassKey = null;
let draggedSubgrupoId = null;

function checkForConflicts(newClassDetails) {
    const { docId, espIdJson, dia, secId, horaInicioNueva, horaFinNueva } = newClassDetails;
    const foundConflicts = []; 
    if (!dia) return { hasConflict: false };
    const espId = (espIdJson && espIdJson.startsWith('{')) ? JSON.parse(espIdJson) : null;
    const espKey = espId ? `${espId.numero}|${espId.tipo}|${espId.edificio}` : null;
    for (const claseExistente of ocupacionGlobalCompleta) {
        if (claseExistente.sec_codigo == secId) continue;
        if (normalizeDayKey(claseExistente.dia) === normalizeDayKey(dia)) {
            const inicioExistente = claseExistente.hora_inicio.substring(0, 5);
            const finExistente = claseExistente.hora_fin.substring(0, 5);
            const haySolapamiento = (horaInicioNueva < finExistente && horaFinNueva > inicioExistente);
            if (haySolapamiento) {
                const seccionConflicto = `<strong>${claseExistente.sec_codigo}</strong>`;
                if (docId && String(claseExistente.doc_cedula) == docId) {
                    foundConflicts.push({ type: 'docente', message: `<b>Conflicto:</b> Docente ya asignado en sección ${seccionConflicto} a esta hora.` });
                }
                const claseExistenteEspKey = `${claseExistente.esp_numero}|${claseExistente.esp_tipo}|${claseExistente.esp_edificio}`;
                if (espKey && claseExistenteEspKey === espKey) {
                    foundConflicts.push({ type: 'espacio', message: `<b>Conflicto:</b> Espacio ya ocupado por la sección ${seccionConflicto} a esta hora.` });
                }
            }
        }
    }
    return { hasConflict: foundConflicts.length > 0, messages: foundConflicts };
}



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
            language: { lengthMenu: "Mostrar _MENU_ registros", zeroRecords: "No hay horarios registrados", info: "Mostrando _PAGE_ de _PAGES_", infoEmpty: "No hay registros disponibles", infoFiltered: "(filtrado de _MAX_ registos totales)", search: "Buscar:", paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" } },
            responsive: true,
            autoWidth: false
        });
    }
}

function normalizeDayKey(day) {
    if (!day) return '';
    return day.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function construirBloquesParaHorario(clases, turnoSeleccionado) {
    if (!clases || clases.length === 0) {
        return [];
    }

    let minTime = '23:59:59';
    let maxTime = '00:00:00';

    clases.forEach(clase => {
        const horaInicioCompleta = (clase.hora_inicio && clase.hora_inicio.length === 5) ? clase.hora_inicio + ':00' : clase.hora_inicio;
        const horaFinCompleta = (clase.hora_fin && clase.hora_fin.length === 5) ? clase.hora_fin + ':00' : clase.hora_fin;

        if (horaInicioCompleta && horaInicioCompleta < minTime) {
            minTime = horaInicioCompleta;
        }
        if (horaFinCompleta && horaFinCompleta > maxTime) {
            maxTime = horaFinCompleta;
        }
    });

    if (minTime > maxTime) {
        return [];
    }

    const bloquesNecesarios = allTurnos.filter(turnoBase => {
        return turnoBase.tur_horainicio >= minTime && turnoBase.tur_horainicio < maxTime;
    });

    const horariosUnicos = new Set();
    clases.forEach(clase => {
        const horaInicio = (clase.hora_inicio && clase.hora_inicio.length === 5) ? clase.hora_inicio + ':00' : clase.hora_inicio;
        const horaFin = (clase.hora_fin && clase.hora_fin.length === 5) ? clase.hora_fin + ':00' : clase.hora_fin;
        horariosUnicos.add(JSON.stringify({ inicio: horaInicio, fin: horaFin }));
    });

    const bloquesSinteticos = [];
    horariosUnicos.forEach(horarioStr => {
        const horario = JSON.parse(horarioStr);
        const existeBloque = bloquesNecesarios.some(b => b.tur_horainicio === horario.inicio);
        const existeEnAllTurnos = allTurnos.some(b => b.tur_horainicio === horario.inicio);
        
        if (!existeBloque && !existeEnAllTurnos) {
            bloquesSinteticos.push({
                tur_horainicio: horario.inicio,
                tur_horafin: horario.fin,
                tur_nombre: turnoSeleccionado.charAt(0).toUpperCase() + turnoSeleccionado.slice(1),
                tur_estado: 1,
                _sintetico: true
            });
            console.log(`🔧 Bloque sintético creado: ${horario.inicio} - ${horario.fin}`);
        }
    });

    const todosBloques = [...bloquesNecesarios, ...bloquesSinteticos];
    todosBloques.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));

    if (bloquesSinteticos.length > 0) {
        const horariosCreados = bloquesSinteticos.map(b => b.tur_horainicio.substring(0, 5)).join(', ');
        console.info(`✅ Se crearon ${bloquesSinteticos.length} bloques automáticos para: ${horariosCreados}`);
    }

    return todosBloques;
}

function detectarTurnoPorHorario(clases) {
    if (!clases || clases.length === 0) {
        return 'mañana';
    }

    let minTime = '23:59:59';
    clases.forEach(clase => {
        const horaInicioCompleta = (clase.hora_inicio && clase.hora_inicio.length === 5) ? clase.hora_inicio + ':00' : clase.hora_inicio;
        if (horaInicioCompleta && horaInicioCompleta < minTime) {
            minTime = horaInicioCompleta;
        }
    });

    const horaInicio = minTime.substring(0, 5);
    if (horaInicio >= '06:00' && horaInicio < '12:00') {
        return 'mañana';
    } else if (horaInicio >= '12:00' && horaInicio < '18:00') {
        return 'tarde';
    } else {
        return 'noche';
    }
}

function generarBloquesPorDefecto(turno) {
    if (!allTurnos || allTurnos.length === 0) {
        return [];
    }

    let rangoInicio, rangoFin;
    if (turno === 'mañana') {
        rangoInicio = '06:00:00';
        rangoFin = '12:00:00';
    } else if (turno === 'tarde') {
        rangoInicio = '12:00:00';
        rangoFin = '18:00:00';
    } else {
        rangoInicio = '18:00:00';
        rangoFin = '23:59:59';
    }

    const bloquesFiltrados = allTurnos.filter(turnoBase => {
        return turnoBase.tur_horainicio >= rangoInicio && turnoBase.tur_horainicio < rangoFin;
    });

    bloquesFiltrados.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));

    return bloquesFiltrados;
}
function inicializarTablaHorario(filtroTurno = 'todos', targetTableId = "#tablaHorario", isViewOnly = false) {
    const tbody = $(`${targetTableId} tbody`);
    tbody.empty();
    const bloquesDeLaTabla = bloquesDeLaTablaActual;
    const horarioMapeado = new Map();
    for (const [key, claseArray] of horarioContenidoGuardado.entries()) {
        if (Array.isArray(claseArray) && claseArray.length > 0) {
            horarioMapeado.set(key, claseArray);
        }
    }
    const celdasProcesadas = new Set();
    const dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    bloquesDeLaTabla.forEach((bloque, rowIndex) => {
        const row = $("<tr>");
        const celdaHora = $("<td>").css({ 'display': 'flex', 'justify-content': 'space-between', 'align-items': 'center', 'padding': '0.5rem' });
        const textoHora = $("<span>").text(`${formatTime12Hour(bloque.tur_horainicio)} - ${formatTime12Hour(bloque.tur_horafin)}`);
        
        
        
        if (!isViewOnly) {
            const containerBotones = $('<div class="d-inline-flex">');
            const botonEditar = $("<button type='button' class='btn btn-sm btn-editar-fila' title='Editar esta franja horaria'>").html('<img src="public/assets/icons/edit.svg" alt="Editar" style="height: 1em; opacity: 0.6;">').data('franja-inicio', bloque.tur_horainicio).css({ 'border': 'none', 'background': 'transparent', 'padding': '0 5px' });
            const botonEliminar = $("<button type='button' class='btn btn-sm btn-eliminar-fila' title='Eliminar esta fila'>").html('<img src="public/assets/icons/trash.svg" alt="Eliminar" style="height: 1em; opacity: 0.6;">').data('franja-inicio', bloque.tur_horainicio).css({ 'border': 'none', 'background': 'transparent', 'padding': '0 5px' });
            containerBotones.append(botonEditar, botonEliminar);
            celdaHora.append(textoHora, containerBotones);
        } else {
            celdaHora.css('justify-content', 'center').append(textoHora);
        }
        row.append(celdaHora);
        dias.forEach((dia) => {
            const dia_key = normalizeDayKey(dia);
            const key_actual = `${bloque.tur_horainicio.substring(0, 5)}-${dia_key}`;
            if (celdasProcesadas.has(key_actual)) return;
            const cell = $("<td>").attr("data-franja-inicio", bloque.tur_horainicio).attr("data-dia-nombre", dia);
            cell.css('vertical-align', 'top');
            if (!isViewOnly) {
                cell.addClass("celda-horario");
                cell.css({
                    'cursor': 'pointer',
                    'user-select': 'none',
                    '-webkit-user-select': 'none',
                    '-moz-user-select': 'none',
                    '-ms-user-select': 'none'
                });
            }
            if (horarioMapeado.has(key_actual)) {
                const claseArray = horarioMapeado.get(key_actual);
                if (Array.isArray(claseArray) && claseArray.length > 0) {
                    const primeraClase = claseArray[0].data;
                    const bloques_span = primeraClase.bloques_span || 1;
                    let combinedHtml;
                    if (claseArray.length > 1) {
                        let columna1 = `<td style="width: 50%; vertical-align: top; border-right: 1px solid #dee2e6; padding: 2px;">`;
                        let columna2 = `<td style="width: 50%; vertical-align: top; padding: 2px;">`;
                        if (claseArray[0]) columna1 += generarCellContent(claseArray[0].data, isViewOnly);
                        if (claseArray[1]) columna2 += generarCellContent(claseArray[1].data, isViewOnly);
                        columna1 += '</td>';
                        columna2 += '</td>';
                        combinedHtml = `<table style="width: 100%; border: none; height: 100%;"><tbody><tr>${columna1}${columna2}</tr></tbody></table>`;
                    } else {
                        combinedHtml = generarCellContent(primeraClase, isViewOnly);
                    }
                    cell.html(combinedHtml).data("horario-data", claseArray.map(c => c.data));
                    if (bloques_span > 1) {
                        cell.attr("rowspan", bloques_span);
                        for (let i = 1; i < bloques_span; i++) {
                            if ((rowIndex + i) < bloquesDeLaTabla.length) {
                                const bloqueFuturo = bloquesDeLaTabla[rowIndex + i];
                                celdasProcesadas.add(`${bloqueFuturo.tur_horainicio.substring(0, 5)}-${dia_key}`);
                            }
                        }
                    }
                }
            }
            row.append(cell);
        });
        tbody.append(row);
    });
    if (!isViewOnly) {
        $("#tablaHorario tbody").off("click").on("click", ".celda-horario", onCeldaHorarioClick);
        
        configurarDragAndDrop();
    }
}

function generarCellContent(clase, isViewOnly = false) {
    let uc_nombre_completo;
    if (!clase.uc_codigo || clase.uc_codigo === '' || clase.uc_codigo === null) {
        uc_nombre_completo = '<i>(Sin UC)</i>';
    } else {
        const ucEncontrada = allUcs.find(u => u.uc_codigo == clase.uc_codigo);
        uc_nombre_completo = ucEncontrada ? ucEncontrada.uc_nombre : 'UC Inválida';
    }
    const uc = abreviarNombreLargo(uc_nombre_completo, 25);

    const doc = clase.doc_cedula ? allDocentes.find(d => d.doc_cedula == clase.doc_cedula) : null;
    const doc_nombre = doc ? `${doc.doc_nombre} ${doc.doc_apellido}` : '<i>(Sin Docente)</i>';

    let codigoEspacioFormateado = '<i>(Sin Espacio)</i>';
    if (clase.espacio && clase.espacio.numero) {
        const tipo = clase.espacio.tipo.toLowerCase();
        const edificio = clase.espacio.edificio;
        const numero = clase.espacio.numero;

        if (tipo === 'aula') {
            codigoEspacioFormateado = `${edificio.charAt(0).toUpperCase()}-${numero}`;
        } else if (tipo === 'laboratorio') {
            codigoEspacioFormateado = `Lab.-${numero}`;
        } else {
            codigoEspacioFormateado = numero;
        }
    }

    const subgrupoId = clase.subgrupo || 'default';
    const subgrupoDisplay = clase.subgrupo ? `<span class="badge bg-primary-soft text-primary me-2">G(${clase.subgrupo})</span>` : '';

   
    const editButton = isViewOnly ? '' : `
        <button type="button" class="btn btn-light btn-edit-icon" title="Gestionar este bloque" style="border: none; padding: 4px 8px; line-height: 1;">
            <img src="public/assets/icons/edit.svg" style="width: 1.1em; height: 1.1em; opacity: 0.7;">
        </button>
    `;

    const cursorStyle = isViewOnly ? '' : 'cursor: grab;';
    const draggableAttr = isViewOnly ? '' : 'draggable="true"';
  

    return `<div class="subgroup-item p-1 draggable-class" ${draggableAttr} style="display: flex; align-items: center; justify-content: space-between;" data-subgrupo-id="${subgrupoId}">
                <div class="subgroup-content" style="${cursorStyle} flex-grow: 1;">
                    <p class="m-0" style="font-size:0.8em;">${subgrupoDisplay}<strong>${uc}</strong></p>
                    <small class="text-muted" style="font-size:0.7em;">${codigoEspacioFormateado} / ${doc_nombre}</small>
                </div>
                ${editButton}
            </div>`;
}

function abreviarNombreLargo(nombre, longitudMaxima = 25) {
    if (typeof nombre !== 'string' || nombre.length <= longitudMaxima || nombre.startsWith('<')) {
        return nombre;
    }
    const palabrasExcluidas = new Set(['de', 'y', 'a', 'del', 'la', 'los', 'las', 'en']);
    const partes = nombre.split(' ');
    let numeral = '';
    const ultimoTermino = partes[partes.length - 1];
    if (['I', 'II', 'III', 'IV', 'V', 'VI'].includes(ultimoTermino.toUpperCase())) {
        numeral = ' ' + partes.pop();
    }
    const iniciales = partes
        .filter(palabra => !palabrasExcluidas.has(palabra.toLowerCase()))
        .map(palabra => palabra.charAt(0).toUpperCase());
    return iniciales.join('') + numeral;
}

function renderizarModalDeGestion(clases, franjaInicio, diaNombre) {
    const modalBody = $("#modal-body-gestion-clase");
    modalBody.empty();

    let listHtml = '<p class="text-muted">Este bloque horario tiene múltiples clases (subgrupos). Puede editar o eliminar cada uno.</p>';
    listHtml += '<ul class="list-group">';

    clases.forEach(claseData => {
        const uc = claseData.uc_codigo ? (allUcs.find(u => u.uc_codigo == claseData.uc_codigo)?.uc_nombre || 'N/A') : '(Sin UC)';
        const doc = claseData.doc_cedula ? (allDocentes.find(d => d.doc_cedula == claseData.doc_cedula)?.doc_nombre + ' ' + allDocentes.find(d => d.doc_cedula == claseData.doc_cedula)?.doc_apellido || 'N/A') : '(Sin Docente)';
        const subgrupoId = claseData.subgrupo || 'default';
        const subgrupoDisplay = claseData.subgrupo ? `Grupo ${claseData.subgrupo}` : 'Grupo Único';

        listHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${subgrupoDisplay}:</strong> ${uc}<br>
                            <small class="text-muted">${doc}</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-editar-subgrupo" data-subgrupo-id="${subgrupoId}">Editar</button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-subgrupo" data-subgrupo-id="${subgrupoId}">Eliminar</button>
                        </div>
                     </li>`;
    });

    listHtml += '</ul>';

    if (clases.length < 2) {
        listHtml += '<div class="text-center mt-3"><button type="button" class="btn btn-success" id="btn-anadir-otro-subgrupo">Añadir otro Subgrupo</button></div>';
    } else {
        listHtml += '<p class="text-center text-muted mt-3">Máximo de 2 subgrupos por bloque alcanzado.</p>';
    }

    modalBody.html(listHtml);
}

function renderizarModalClaseUnica(claseData, franjaInicio, diaNombre) {
    const modalBody = $("#modal-body-gestion-clase");
    const uc = allUcs.find(u => u.uc_codigo == claseData.uc_codigo)?.uc_nombre || 'N/A';
    const doc = allDocentes.find(d => d.doc_cedula == claseData.doc_cedula);
    const doc_nombre = doc ? `${doc.doc_nombre} ${doc.doc_apellido}` : 'N/A';
    const espacio_info = (claseData.espacio && claseData.espacio.numero) ? `${claseData.espacio.numero} (${claseData.espacio.tipo})` : '<i>(Sin Espacio)</i>';

    const html = `
        <h5 class="mb-3">Gestionar Bloque Horario</h5>
        <ul class="list-group mb-4">
            <li class="list-group-item"><strong>UC:</strong> ${uc}</li>
            <li class="list-group-item"><strong>Docente:</strong> ${doc_nombre}</li>
            <li class="list-group-item"><strong>Espacio:</strong> ${espacio_info}</li>
        </ul>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-outline-primary" id="btn-editar-clase-unica">
                <img src="public/assets/icons/edit.svg" alt="Editar" style="height: 1em; margin-right: 8px;">Editar Clase
            </button>
            <button type="button" class="btn btn-outline-success" id="btn-dividir-bloque">
                 <img src="public/assets/icons/columns.svg" alt="Dividir" style="height: 1em; margin-right: 8px;">Dividir Bloque para Subgrupos
            </button>
            <button type="button" class="btn btn-outline-danger mt-2" id="btn-eliminar-clase-unica">
                <img src="public/assets/icons/trash.svg" alt="Eliminar" style="height: 1em; margin-right: 8px;">Eliminar Clase
            </button>
        </div>
    `;
    modalBody.html(html);
}
function populateUcSelectForModal(ucSelect, ucToSelect) {
    const secCodigo = $("#sec_codigo_hidden").val();
    ucSelect.empty().append('<option value="">Cargando UCs...</option>').prop("disabled", true);

    const datos = new FormData();
    datos.append("accion", "obtener_uc_por_docente");
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
                    let faseTexto = uc.uc_periodo ? ` (${uc.uc_periodo})` : '';
                    ucSelect.append(`<option value="${uc.uc_codigo}">${uc.uc_nombre}${faseTexto}</option>`);
                });
                ucSelect.prop("disabled", false);
            } else {
                const mensaje = respuesta.mensaje_uc || "No hay UCs disponibles para este trayecto/fase";
                ucSelect.append(`<option value="">${mensaje}</option>`).prop("disabled", true);
            }

            if (ucToSelect && ucToSelect !== '' && ucToSelect !== null) {
                ucSelect.val(ucToSelect).trigger('change');
            }
        },
        error: function() {
            ucSelect.empty().append('<option value="">Error al cargar UCs</option>').prop("disabled", true);
        }
    });
}


function abrirFormularioClaseSimple(claseData, franjaInicio, diaNombre) {
    const modalBody = $("#modal-body-gestion-clase");
    const turnoCompleto = bloquesDeLaTablaActual.find(b => b.tur_horainicio === franjaInicio);
    const isEditing = !!claseData;

    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    let maxBloques = 0;
    if (indiceInicio !== -1) {
        maxBloques = bloquesDeLaTablaActual.length - indiceInicio;
    }

    let opcionesDuracion = '';
    for (let i = 1; i <= maxBloques; i++) {
        opcionesDuracion += `<option value="${i}">${i} Bloque${i > 1 ? 's' : ''} (${i * 40} min)</option>`;
    }

   
    const formHtml = `
        <form id="formularioEntradaHorario" autocomplete="off" novalidate>
            <input type="hidden" id="formContext" value="simple">
            <input type="hidden" id="subgrupoOriginalId" value="default">
            <div class="mb-3"><label class="form-label">Franja Horaria:</label><input type="text" class="form-control" id="franjaHorariaDisplay" value="${formatTime12Hour(turnoCompleto.tur_horainicio)} - ${formatTime12Hour(turnoCompleto.tur_horafin)}" readonly></div>
            <div class="mb-3"><label class="form-label">Día:</label><input type="text" class="form-control" value="${diaNombre}" readonly></div>
            <div class="mb-3">
                <label for="modalSeleccionarUc" class="form-label">Unidad Curricular</label>
                <select class="form-select" id="modalSeleccionarUc" style="width: 100%;"></select>
                <div id="uc-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarDocente" class="form-label">Docente</label>
                <select class="form-select" id="modalSeleccionarDocente" style="width: 100%;"></select>
                <div id="docente-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarEspacio" class="form-label">Espacio (Aula/Lab)</label>
                <select class="form-select" id="modalSeleccionarEspacio" style="width: 100%;"></select>
                <div id="espacio-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalDuracionSubgrupo" class="form-label">Duración de la Clase:</label>
                <select class="form-select" id="modalDuracionSubgrupo">${opcionesDuracion}</select>
                <div id="duracion-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
             <div class="d-flex justify-content-start gap-2 mt-4">
                <button type="submit" class="btn btn-primary">${isEditing ? 'Guardar Cambios' : 'Guardar Clase'}</button>
                ${isEditing ? '<button type="button" class="btn btn-danger" id="btn-eliminar-clase-unica-desde-form">Eliminar</button>' : ''}
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    `;

    modalBody.html(formHtml);

    const select2Config = {
        theme: "bootstrap-5",
        dropdownParent: $('#modalEntradaHorario .modal-content')
    };
    
   
    const ucSelect = $("#modalSeleccionarUc");
    populateUcSelectForModal(ucSelect, claseData ? claseData.uc_codigo : null); 
    ucSelect.select2(select2Config);

    const docenteSelect = $("#modalSeleccionarDocente");
    docenteSelect.empty().append('<option value="">Seleccionar Docente</option>');
    allDocentes.forEach(doc => docenteSelect.append(`<option value="${doc.doc_cedula}">${doc.doc_nombre} ${doc.doc_apellido}</option>`));
    docenteSelect.select2(select2Config);


    const espacioSelect = $("#modalSeleccionarEspacio");
    espacioSelect.empty().append('<option value="">Seleccionar Espacio</option>');
    allEspacios.forEach(esp => espacioSelect.append(`<option value='${JSON.stringify({numero: esp.numero, tipo: esp.tipo, edificio: esp.edificio})}'>${esp.numero} (${esp.tipo} - ${esp.edificio})</option>`));
    espacioSelect.select2(select2Config);

    $("#modalDuracionSubgrupo").select2({
        ...select2Config,
        minimumResultsForSearch: Infinity
    });
    
 
    
    $("#modalSeleccionarDocente, #modalSeleccionarUc, #modalSeleccionarEspacio, #modalDuracionSubgrupo").on('change', function() {
        validarBloqueEnTiempoReal();
    });
   

    $("#modalDuracionSubgrupo").on('change', function() {
        const bloquesSeleccionados = parseInt($(this).val(), 10);
        const indiceFin = indiceInicio + bloquesSeleccionados - 1;
        if (indiceFin < bloquesDeLaTablaActual.length) {
            const horaFin = bloquesDeLaTablaActual[indiceFin].tur_horafin;
            $("#franjaHorariaDisplay").val(`${formatTime12Hour(franjaInicio)} - ${formatTime12Hour(horaFin)}`);
        }
    });

    if (claseData) {
        $("#modalDuracionSubgrupo").val(claseData.bloques_span).trigger('change');
       
        $("#modalSeleccionarDocente").val(claseData.doc_cedula).trigger('change');
        if (claseData.espacio && claseData.espacio.numero) {
            $("#modalSeleccionarEspacio").val(JSON.stringify(claseData.espacio)).trigger('change');
        }
    } else {
        $("#modalDuracionSubgrupo").val(maxBloques >= 2 ? '2' : '1').trigger('change');
    }
}

function abrirFormularioSubgrupo(claseData, franjaInicio, diaNombre) {
    const modalBody = $("#modal-body-gestion-clase");
    const subgrupoOriginal = claseData ? (claseData.subgrupo || '') : '';
    const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
    const clasesEnCelda = horarioContenidoGuardado.get(key_horario) || [];
    const esEdicion = !!claseData;
    const placeholderSubgrupo = clasesEnCelda.some(c => c.data.subgrupo === 'A') ? 'Tarde, Práctica...' : 'B, Mañana...';

    const duracionFija = clasesEnCelda.length > 0 ? clasesEnCelda[0].data.bloques_span : 1;
    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    const horaFin = bloquesDeLaTablaActual[indiceInicio + duracionFija - 1].tur_horafin;
    const textoFranjaCompleta = `${formatTime12Hour(franjaInicio)} - ${formatTime12Hour(horaFin)}`;

    const mostrarBotonVolver = clasesEnCelda.length > 0;


    const formHtml = `
        <form id="formularioEntradaHorario" autocomplete="off" novalidate>
            <input type="hidden" id="formContext" value="subgrupo">
            <input type="hidden" id="subgrupoOriginalId" value="${subgrupoOriginal}">
            <div class="mb-3"><label class="form-label">Franja Horaria Completa:</label><input type="text" class="form-control" value="${textoFranjaCompleta}" readonly></div>
            <div class="mb-3"><label class="form-label">Día:</label><input type="text" class="form-control" value="${diaNombre}" readonly></div>
            <hr>
            <div class="mb-3">
                <label for="modalSubgrupo" class="form-label">Identificador del Subgrupo <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="modalSubgrupo" placeholder="Ej: ${placeholderSubgrupo}" required>
                <div class="invalid-feedback">Este identificador ya existe o está vacío.</div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarUc" class="form-label">Unidad Curricular</label>
                <select class="form-select" id="modalSeleccionarUc" style="width: 100%;"></select>
                <div id="uc-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarDocente" class="form-label">Docente</label>
                <select class="form-select" id="modalSeleccionarDocente" style="width: 100%;"></select>
                 <div id="docente-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarEspacio" class="form-label">Espacio (Aula/Lab)</label>
                <select class="form-select" id="modalSeleccionarEspacio" style="width: 100%;"></select>
                <div id="espacio-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <button type="submit" class="btn btn-primary">${esEdicion ? 'GUARDAR CAMBIOS' : 'AÑADIR SUBGRUPO'}</button>
            ${mostrarBotonVolver ? '<button type="button" class="btn btn-secondary" id="btn-volver-a-lista">VOLVER A LA LISTA</button>' : ''}
        </form>
    `;
   
    modalBody.html(formHtml);

    const select2Config = {
        theme: "bootstrap-5",
        dropdownParent: $('#modalEntradaHorario .modal-content')
    };

   
    const ucSelect = $("#modalSeleccionarUc");
    populateUcSelectForModal(ucSelect, claseData ? claseData.uc_codigo : null); 
    ucSelect.select2(select2Config);

    const docenteSelect = $("#modalSeleccionarDocente");
    docenteSelect.empty().append('<option value="">Seleccionar Docente</option>');
    allDocentes.forEach(doc => docenteSelect.append(`<option value="${doc.doc_cedula}">${doc.doc_nombre} ${doc.doc_apellido}</option>`));
    docenteSelect.select2(select2Config);


    const espacioSelect = $("#modalSeleccionarEspacio");
    espacioSelect.empty().append('<option value="">Seleccionar Espacio</option>');
    allEspacios.forEach(esp => espacioSelect.append(`<option value='${JSON.stringify({numero: esp.numero, tipo: esp.tipo, edificio: esp.edificio})}'>${esp.numero} (${esp.tipo} - ${esp.edificio})</option>`));
    espacioSelect.select2(select2Config);


    
    $("#modalSeleccionarDocente, #modalSeleccionarUc, #modalSeleccionarEspacio, #modalSubgrupo").on('change keyup', function() {
        validarBloqueEnTiempoReal();
    });
 

    if (claseData) {
        $("#modalSubgrupo").val(claseData.subgrupo);
    
        $("#modalSeleccionarDocente").val(claseData.doc_cedula).trigger('change');
        if (claseData.espacio && claseData.espacio.numero) {
            $("#modalSeleccionarEspacio").val(JSON.stringify(claseData.espacio)).trigger('change');
        }
    }
}

function validarBloqueEnTiempoReal() {
    
    $('#docente-conflicto-info, #uc-conflicto-info, #espacio-conflicto-info, #duracion-conflicto-info').html('');
    const submitButton = $('#formularioEntradaHorario button[type="submit"]');
    submitButton.prop('disabled', false);

    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const dia_key = normalizeDayKey(diaNombre);

    
    const duracionSelect = $("#modalDuracionSubgrupo");
    if (duracionSelect.length > 0) {
        const nuevaDuracion = parseInt(duracionSelect.val(), 10);
        const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
        
        
        const originalKey = `${franjaInicio.substring(0, 5)}-${dia_key}`;

        
        for (let i = 1; i < nuevaDuracion; i++) {
            const indiceBloqueSiguiente = indiceInicio + i;
            if (indiceBloqueSiguiente < bloquesDeLaTablaActual.length) {
                const bloqueSiguiente = bloquesDeLaTablaActual[indiceBloqueSiguiente];
                const tiempoBloqueSiguiente = bloqueSiguiente.tur_horainicio.substring(0, 5);

               
                for (const [key, claseArray] of horarioContenidoGuardado.entries()) {
                   
                    if (key === originalKey) {
                        continue;
                    }
                    
                    const claseExistente = claseArray[0].data;
                    const inicioExistente = claseExistente.hora_inicio.substring(0, 5);
                    const finExistente = claseExistente.hora_fin.substring(0, 5);
                    
                    
                    if (normalizeDayKey(claseExistente.dia) === dia_key) {
                        if (tiempoBloqueSiguiente >= inicioExistente && tiempoBloqueSiguiente < finExistente) {
                            
                            $('#duracion-conflicto-info').html(`<b>Conflicto:</b> El bloque de las ${formatTime12Hour(bloqueSiguiente.tur_horainicio)} ya está ocupado.`);
                            submitButton.prop('disabled', true);
                            return; 
                        }
                    }
                }
            }
        }
    }

    const ucCodigo = $("#modalSeleccionarUc").val();
    if (ucCodigo) {
        const key_actual = `${franjaInicio.substring(0, 5)}-${dia_key}`;
        for (const [key, claseArray] of horarioContenidoGuardado.entries()) {
            if (key !== key_actual && claseArray.some(c => c.data.uc_codigo === ucCodigo)) {
                const nombreUc = allUcs.find(u => u.uc_codigo === ucCodigo)?.uc_nombre || ucCodigo;
                $('#uc-conflicto-info').html(`<b>Advertencia:</b> La UC '${nombreUc}' ya está asignada en esta sección.`);
                break;
            }
        }
    }

   
    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    const bloques_span_ajax = duracionSelect.length > 0 ? parseInt(duracionSelect.val(), 10) : (horarioContenidoGuardado.get(`${franjaInicio.substring(0, 5)}-${dia_key}`) || [{data:{bloques_span: 1}}])[0].data.bloques_span;

    const indiceFin = indiceInicio + bloques_span_ajax - 1;
    if (indiceFin >= bloquesDeLaTablaActual.length || indiceFin < 0) return;

    const datosValidacion = new FormData();
    datosValidacion.append("accion", "validar_clase_en_vivo");
    datosValidacion.append("doc_cedula", $("#modalSeleccionarDocente").val());
    datosValidacion.append("uc_codigo", $("#modalSeleccionarUc").val());
    datosValidacion.append("espacio", $("#modalSeleccionarEspacio").val());
    datosValidacion.append("dia", diaNombre);
    datosValidacion.append("sec_codigo", $("#sec_codigo_hidden").val());
    datosValidacion.append("ani_anio", $("#ani_anio_hidden").val());
    datosValidacion.append("hora_inicio", bloquesDeLaTablaActual[indiceInicio].tur_horainicio.substring(0, 5));
    datosValidacion.append("hora_fin", bloquesDeLaTablaActual[indiceFin].tur_horafin.substring(0, 5));
    
    $.ajax({
        url: "",
        type: "POST",
        data: datosValidacion,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            if (respuesta.conflicto && Array.isArray(respuesta.mensajes)) {
                let conflictosDocente = [];
                let conflictosEspacio = [];
                
                respuesta.mensajes.forEach(conflicto => {
                    if (conflicto.tipo === 'docente') {
                        conflictosDocente.push(`<div>${conflicto.mensaje}</div>`);
                    } else if (conflicto.tipo === 'espacio') {
                        conflictosEspacio.push(`<div>${conflicto.mensaje}</div>`);
                    }
                });

                if (conflictosDocente.length > 0) {
                    $('#docente-conflicto-info').html(conflictosDocente.join(''));
                }
                if (conflictosEspacio.length > 0) {
                    $('#espacio-conflicto-info').html(conflictosEspacio.join(''));
                }
            }
        }
    });
}


function guardarClase() {
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    const duracionSelect = $("#modalDuracionSubgrupo");

    let bloques_span;
    if (duracionSelect.length > 0) {
        bloques_span = parseInt(duracionSelect.val(), 10) || 1;
    } else {
        const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
        const clasesEnCelda = horarioContenidoGuardado.get(key_horario) || [];
        bloques_span = clasesEnCelda.length > 0 ? clasesEnCelda[0].data.bloques_span : 1;
    }

    const indiceFin = indiceInicio + bloques_span - 1;
    if (indiceFin >= bloquesDeLaTablaActual.length || indiceFin < 0) {
        muestraMensaje("error", 4000, "Error de Duración", "La duración seleccionada excede los bloques disponibles.");
        return;
    }

    const localWarnings = [];
    const docVal = $("#modalSeleccionarDocente").val();
    const ucVal = $("#modalSeleccionarUc").val();
    const espVal = $("#modalSeleccionarEspacio").val();

    if (!docVal) {
        localWarnings.push({ mensaje: "El campo <b>Docente</b> está vacío." });
    }
    if (!ucVal) {
        localWarnings.push({ mensaje: "El campo <b>Unidad Curricular</b> está vacío." });
    }
    if (!espVal) {
        localWarnings.push({ mensaje: "El campo <b>Espacio</b> está vacío." });
    }


    if (ucVal && ucVal !== '' && ucVal !== null) {
        const franjaInicioActual = franjaInicio.substring(0, 5);
        const diaKeyActual = normalizeDayKey(diaNombre);

        for (const [key, claseArray] of horarioContenidoGuardado.entries()) {
            const [franjaExistente, diaKeyExistente] = key.split('-');
            
            
            if (franjaExistente === franjaInicioActual && diaKeyExistente === diaKeyActual) {
                continue;
            }

            
            if (claseArray.some(c => c.data.uc_codigo && c.data.uc_codigo === ucVal)) {
                const nombreUc = allUcs.find(u => u.uc_codigo === ucVal)?.uc_nombre || ucVal;
                localWarnings.push({ mensaje: `Advertencia: La UC <b>'${nombreUc}'</b> ya está asignada en esta sección.` });
                break; 
            }
        }
    }

    const datosValidacion = new FormData();
    datosValidacion.append("accion", "validar_clase_en_vivo");
    datosValidacion.append("doc_cedula", docVal || "");
    datosValidacion.append("uc_codigo", ucVal || "");
    datosValidacion.append("espacio", espVal || "");
    datosValidacion.append("dia", diaNombre);
    datosValidacion.append("sec_codigo", $("#sec_codigo_hidden").val());
    datosValidacion.append("ani_anio", $("#ani_anio_hidden").val());
    datosValidacion.append("hora_inicio", bloquesDeLaTablaActual[indiceInicio].tur_horainicio.substring(0, 5));
    datosValidacion.append("hora_fin", bloquesDeLaTablaActual[indiceFin].tur_horafin.substring(0, 5));

  
    $.ajax({
        url: "",
        type: "POST",
        data: datosValidacion,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            
            const serverConflicts = (respuesta.conflicto && Array.isArray(respuesta.mensajes)) ? respuesta.mensajes : [];
            const combinedIssues = [...localWarnings, ...serverConflicts];

            if (combinedIssues.length > 0) {
                const issueMessages = combinedIssues.map(c => c.mensaje);
                let mensajeHtml = "Se encontraron los siguientes conflictos y/o advertencias:<ul class='text-start mt-2'>";
                issueMessages.forEach(msg => { mensajeHtml += `<li>${msg}</li>`; });
                mensajeHtml += "</ul><br>¿Desea asignar la clase de todas formas?";

                Swal.fire({
                    title: 'Conflictos y Advertencias',
                    html: mensajeHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, guardar de todas formas',
                    cancelButtonText: 'Cancelar y corregir'
                }).then((result) => {
                    if (result.isConfirmed) {
                        procederConGuardadoLocal();
                    }
                });
            } else {
                procederConGuardadoLocal();
            }
        },
        error: function() {
            muestraMensaje("error", 5000, "Error de Conexión", "No se pudo validar la clase con el servidor.");
        }
    });
   
}


function procederConGuardadoLocal() {
    hasSaved = true;
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const dia_key = normalizeDayKey(diaNombre);
    const key_horario = `${franjaInicio.substring(0, 5)}-${dia_key}`;

    const context = $("#formContext").val();
    const subgrupoOriginalId = $("#subgrupoOriginalId").val();
    let clasesEnCelda = (horarioContenidoGuardado.get(key_horario) || []).map(c => c.data);

    let subgrupoNuevo, bloques_span, hora_fin;

    if (context === 'simple') {
        subgrupoNuevo = null;
        bloques_span = parseInt($("#modalDuracionSubgrupo").val(), 10) || 1;
    } else {
        const subgrupoInput = $("#modalSubgrupo");
        subgrupoNuevo = subgrupoInput.val().trim();
        if (!subgrupoNuevo) {
            subgrupoInput.addClass('is-invalid');
            return;
        }
        const otroConMismoNombre = clasesEnCelda.find(c => c.subgrupo === subgrupoNuevo);
        if (otroConMismoNombre && subgrupoNuevo !== subgrupoOriginalId) {
            subgrupoInput.addClass('is-invalid');
            return;
        }
        subgrupoInput.removeClass('is-invalid');
        bloques_span = clasesEnCelda.length > 0 ? clasesEnCelda[0].bloques_span : 1;
    }

    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    const indiceFin = indiceInicio + bloques_span - 1;
    hora_fin = bloquesDeLaTablaActual[indiceFin].tur_horafin;

    const ucValor = $("#modalSeleccionarUc").val();
    const docValor = $("#modalSeleccionarDocente").val();
    const espValor = $("#modalSeleccionarEspacio").val();
    
    const nuevaClaseData = {
        subgrupo: subgrupoNuevo,
        uc_codigo: ucValor || null,
        doc_cedula: docValor || null,
        espacio: espValor ? JSON.parse(espValor) : null,
        dia: diaNombre,
        hora_inicio: franjaInicio,
        hora_fin: hora_fin,
        bloques_span: bloques_span
    };

    let nuevaListaClases = [];
    if (context === 'simple') {
        nuevaListaClases.push({ data: nuevaClaseData });
    } else {
        if (isSplittingProcess) {
            clasesEnCelda[0].subgrupo = 'A';
            nuevaListaClases = clasesEnCelda.map(c => ({ data: c }));
            nuevaListaClases.push({ data: nuevaClaseData });
        } else {
            let seEncontro = false;
            nuevaListaClases = clasesEnCelda.map(claseData => {
                if ((claseData.subgrupo || 'default') === subgrupoOriginalId) {
                    seEncontro = true;
                    return { data: nuevaClaseData };
                }
                return { data: claseData };
            });
            if (!seEncontro) {
                nuevaListaClases.push({ data: nuevaClaseData });
            }
        }
    }

    nuevaListaClases.forEach(c => {
        c.data.bloques_span = bloques_span;
        c.data.hora_fin = hora_fin;
    });

    horarioContenidoGuardado.set(key_horario, nuevaListaClases);
    inicializarTablaHorario($("#filtro_turno").val(), "#tablaHorario", false);
    $("#modalEntradaHorario").modal("hide");

    checkForScheduleChanges();
}

function eliminarSubgrupo(subgrupoId) {
    Swal.fire({
        title: `¿Eliminar ${subgrupoId === 'default' ? 'esta clase' : 'el subgrupo ' + subgrupoId}?`,
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const key_horario = `${currentClickedCell.data("franja-inicio").substring(0, 5)}-${normalizeDayKey(currentClickedCell.data("dia-nombre"))}`;

            let clasesEnCelda = (horarioContenidoGuardado.get(key_horario) || []).map(c => c.data);
            clasesEnCelda = clasesEnCelda.filter(c => (c.subgrupo || 'default') !== subgrupoId);

            if (clasesEnCelda.length > 0) {
                if (clasesEnCelda.length === 1) {
                    clasesEnCelda[0].subgrupo = null;
                }
                const nuevaListaConHtml = clasesEnCelda.map(c => ({
                    data: c
                }));
                horarioContenidoGuardado.set(key_horario, nuevaListaConHtml);
            } else {
                horarioContenidoGuardado.delete(key_horario);
            }

            inicializarTablaHorario($("#filtro_turno").val(), "#tablaHorario", false);
            $("#modalEntradaHorario").modal("hide");
            checkForScheduleChanges(); 
        }
    });
}

function configurarDragAndDrop() {
    $("#tablaHorario .draggable-class").each(function() {
        const $this = $(this);
        
        this.addEventListener('dragstart', function(e) {
            const $cell = $this.closest('td.celda-horario');
            const franjaInicio = $cell.data("franja-inicio");
            const diaNombre = $cell.data("dia-nombre");
            const subgrupoId = $this.data('subgrupo-id');
            
            const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
            const clasesEnCelda = horarioContenidoGuardado.get(key_horario) || [];
            
            const claseData = clasesEnCelda.find(c => (c.data.subgrupo || 'default') === subgrupoId);
            
            if (claseData) {
                draggedClassData = claseData.data;
                draggedClassKey = key_horario;
                draggedSubgrupoId = subgrupoId;
                
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/html', this.innerHTML);
                
                $this.css('opacity', '0.5');
                $this.css('cursor', 'grabbing');
            }
        });
        
        this.addEventListener('dragend', function(e) {
            $this.css('opacity', '1');
            $this.css('cursor', 'grab');
        });
    });
    
    $("#tablaHorario .celda-horario").each(function() {
        const $cell = $(this);
        
        this.addEventListener('dragover', function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            e.dataTransfer.dropEffect = 'move';
            
            $cell.addClass('drag-over');
            return false;
        });
        
        this.addEventListener('dragenter', function(e) {
            $cell.addClass('drag-over');
        });
        
        this.addEventListener('dragleave', function(e) {
            $cell.removeClass('drag-over');
        });
        
        this.addEventListener('drop', function(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }
            
            $cell.removeClass('drag-over');
            
            if (!draggedClassData) return false;
            
            const franjaInicio = $cell.data("franja-inicio");
            const diaNombre = $cell.data("dia-nombre");
            const key_destino = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
            
            if (key_destino === draggedClassKey) {
                draggedClassData = null;
                draggedClassKey = null;
                draggedSubgrupoId = null;
                return false;
            }
            
            const clasesEnDestino = horarioContenidoGuardado.get(key_destino) || [];
            if (clasesEnDestino.length >= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Celda llena',
                    text: 'Esta celda ya tiene el máximo de 2 subgrupos. Elimine uno primero.',
                    timer: 3000
                });
                draggedClassData = null;
                draggedClassKey = null;
                draggedSubgrupoId = null;
                return false;
            }
            
            const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
            const bloques_span = draggedClassData.bloques_span || 1;
            const indiceFin = indiceInicio + bloques_span - 1;
            
            if (indiceFin >= bloquesDeLaTablaActual.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Duración excedida',
                    text: 'La duración de esta clase excede los bloques disponibles en este horario.',
                    timer: 3000
                });
                draggedClassData = null;
                draggedClassKey = null;
                draggedSubgrupoId = null;
                return false;
            }
            
            const diaKey = normalizeDayKey(diaNombre);
            let hayConflictoDeBloques = false;
            let bloqueConflictivo = '';
            
            for (let i = indiceInicio; i <= indiceFin; i++) {
                const bloqueActual = bloquesDeLaTablaActual[i];
                const keyBloque = `${bloqueActual.tur_horainicio.substring(0, 5)}-${diaKey}`;
                
                const clasesEnBloque = horarioContenidoGuardado.get(keyBloque);
                if (clasesEnBloque && clasesEnBloque.length > 0) {
                    hayConflictoDeBloques = true;
                    bloqueConflictivo = `${formatTime12Hour(bloqueActual.tur_horainicio)} - ${formatTime12Hour(bloqueActual.tur_horafin)}`;
                    break;
                }
            }
            
            if (hayConflictoDeBloques) {
                Swal.fire({
                    icon: 'error',
                    title: 'Conflicto de bloques horarios',
                    html: `No se puede mover la clase porque uno de sus bloques choca con otra clase existente.<br><br><strong>Bloque en conflicto:</strong> ${bloqueConflictivo}`,
                    confirmButtonText: 'Entendido',
                    timer: 4000
                });
                draggedClassData = null;
                draggedClassKey = null;
                draggedSubgrupoId = null;
                return false;
            }
            
            validarYMoverClase(draggedClassData, draggedClassKey, key_destino, diaNombre, franjaInicio, bloques_span);
            
            return false;
        });
    });
}

function validarYMoverClase(claseData, keyOrigen, keyDestino, diaNuevo, horaNueva, bloques_span) {
    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === horaNueva);
    const indiceFin = indiceInicio + bloques_span - 1;
    
    const datosValidacion = new FormData();
    datosValidacion.append("accion", "validar_clase_en_vivo");
    datosValidacion.append("doc_cedula", (claseData.doc_cedula && claseData.doc_cedula !== null) ? claseData.doc_cedula : "");
    datosValidacion.append("uc_codigo", (claseData.uc_codigo && claseData.uc_codigo !== null && claseData.uc_codigo !== '') ? claseData.uc_codigo : "");
    datosValidacion.append("espacio", (claseData.espacio && claseData.espacio.numero) ? JSON.stringify(claseData.espacio) : "");
    datosValidacion.append("dia", diaNuevo);
    datosValidacion.append("sec_codigo", $("#sec_codigo_hidden").val());
    datosValidacion.append("ani_anio", $("#ani_anio_hidden").val());
    datosValidacion.append("hora_inicio", bloquesDeLaTablaActual[indiceInicio].tur_horainicio.substring(0, 5));
    datosValidacion.append("hora_fin", bloquesDeLaTablaActual[indiceFin].tur_horafin.substring(0, 5));
    
    $.ajax({
        url: "",
        type: "POST",
        data: datosValidacion,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            const serverConflicts = (respuesta.conflicto && Array.isArray(respuesta.mensajes)) ? respuesta.mensajes : [];
            
            if (serverConflicts.length > 0) {
                let mensajeHtml = "Se encontraron los siguientes conflictos:<ul class='text-start mt-2'>";
                serverConflicts.forEach(c => { mensajeHtml += `<li>${c.mensaje}</li>`; });
                mensajeHtml += "</ul><br>¿Desea mover la clase de todas formas?";
                
                Swal.fire({
                    title: 'Conflictos detectados',
                    html: mensajeHtml,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, mover',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        ejecutarMovimientoClase(claseData, keyOrigen, keyDestino, diaNuevo, horaNueva, bloques_span);
                    } else {
                        draggedClassData = null;
                        draggedClassKey = null;
                        draggedSubgrupoId = null;
                    }
                });
            } else {
                ejecutarMovimientoClase(claseData, keyOrigen, keyDestino, diaNuevo, horaNueva, bloques_span);
            }
        },
        error: function() {
            muestraMensaje("error", 5000, "Error de Conexión", "No se pudo validar la clase con el servidor.");
            draggedClassData = null;
            draggedClassKey = null;
            draggedSubgrupoId = null;
        }
    });
}

function ejecutarMovimientoClase(claseData, keyOrigen, keyDestino, diaNuevo, horaNueva, bloques_span) {
    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === horaNueva);
    const indiceFin = indiceInicio + bloques_span - 1;
    const nuevaHoraFin = bloquesDeLaTablaActual[indiceFin].tur_horafin;
    
    const claseActualizada = {
        ...claseData,
        dia: diaNuevo,
        hora_inicio: horaNueva,
        hora_fin: nuevaHoraFin,
        bloques_span: bloques_span
    };
    
    let clasesOrigen = horarioContenidoGuardado.get(keyOrigen) || [];
    clasesOrigen = clasesOrigen.filter(c => (c.data.subgrupo || 'default') !== draggedSubgrupoId);
    
    if (clasesOrigen.length > 0) {
        if (clasesOrigen.length === 1) {
            clasesOrigen[0].data.subgrupo = null;
        }
        horarioContenidoGuardado.set(keyOrigen, clasesOrigen);
    } else {
        horarioContenidoGuardado.delete(keyOrigen);
    }
    
    let clasesDestino = horarioContenidoGuardado.get(keyDestino) || [];
    
    if (clasesDestino.length === 1 && !clasesDestino[0].data.subgrupo) {
        clasesDestino[0].data.subgrupo = 'A';
        claseActualizada.subgrupo = 'B';
    } else if (clasesDestino.length === 1 && clasesDestino[0].data.subgrupo) {
        const subgruposExistentes = clasesDestino.map(c => c.data.subgrupo);
        claseActualizada.subgrupo = subgruposExistentes.includes('A') ? 'B' : 'A';
    } else {
        claseActualizada.subgrupo = null;
    }
    
    clasesDestino.forEach(c => {
        c.data.bloques_span = bloques_span;
        c.data.hora_fin = nuevaHoraFin;
    });
    
    clasesDestino.push({ data: claseActualizada });
    horarioContenidoGuardado.set(keyDestino, clasesDestino);
    
    draggedClassData = null;
    draggedClassKey = null;
    draggedSubgrupoId = null;
    
    const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
    inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
    checkForScheduleChanges();
    
    muestraMensaje("success", 2500, "Clase movida", "La clase se ha movido exitosamente al nuevo día y horario.");
}

function onCeldaHorarioClick(e) {
    currentClickedCell = $(e.currentTarget);
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
    const clasesEnCelda = (horarioContenidoGuardado.get(key_horario) || []).map(c => c.data);

    const botonEditClickeado = $(e.target).closest('.btn-edit-icon');
    const contenidoClickeado = $(e.target).closest('.subgroup-content');

    if (botonEditClickeado.length > 0) {
        if (clasesEnCelda.length > 1) {
            renderizarModalDeGestion(clasesEnCelda, franjaInicio, diaNombre);
        } else if (clasesEnCelda.length === 1) {
            renderizarModalClaseUnica(clasesEnCelda[0], franjaInicio, diaNombre);
        }
    } else if (contenidoClickeado.length > 0) {
        const subgrupoId = contenidoClickeado.parent('.subgroup-item').data('subgrupo-id');
        const claseAEditar = clasesEnCelda.find(c => (c.subgrupo || 'default') === subgrupoId);
        if (claseAEditar.subgrupo) {
            abrirFormularioSubgrupo(claseAEditar, franjaInicio, diaNombre);
        } else {
            abrirFormularioClaseSimple(claseAEditar, franjaInicio, diaNombre);
        }
    } else {
        if (clasesEnCelda.length === 0) {
            abrirFormularioClaseSimple(null, franjaInicio, diaNombre);
        }
    }

    $("#modalEntradaHorario").modal("show");
}

function procederConGuardado() {
    const ejecutarGuardado = () => {
        const accion = $("#accion").val();
        const modoModal = $("#modal-horario").data("mode");
        const datos = new FormData();
        datos.append("accion", accion);
        datos.append("sec_codigo", $("#sec_codigo_hidden").val());
        datos.append("ani_anio", $("#ani_anio_hidden").val());
        datos.append("modo_operacion", modoModal);
        
        if (accion === 'modificar' || accion === 'registrar') {
            datos.append("cantidadSeccion", $("#cantidadSeccionModificar").val());
        }

        let clasesAEnviar = [];
        for (const claseArray of horarioContenidoGuardado.values()) {
            claseArray.forEach(claseObj => clasesAEnviar.push(claseObj.data));
        }

        clasesAEnviar = clasesAEnviar.filter(item => item && item.dia && item.hora_inicio && item.hora_fin);
        clasesAEnviar.forEach(item => {
            item.hora_inicio = item.hora_inicio.substring(0, 5);
            item.hora_fin = item.hora_fin.substring(0, 5);
        });

        datos.append("items_horario", JSON.stringify(clasesAEnviar));
        enviaAjax(datos, $("#proceso"));
    };
    ejecutarGuardado();
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
    if (secCodigo) {
        const digitos = secCodigo.toString().match(/\d/g);
        if (digitos && digitos.length >= 2) {
            const segundoDigito = digitos[1];
            if (segundoDigito === '1') turnoSeleccionado = 'mañana';
            else if (segundoDigito === '2') turnoSeleccionado = 'tarde';
            else if (segundoDigito === '3') turnoSeleccionado = 'noche';
            else turnoSeleccionado = 'mañana';
        }
    }
    const turnoTexto = turnoSeleccionado.charAt(0).toUpperCase() + turnoSeleccionado.slice(1);
    $("#cantidadSeccionModificar").val(secCantidad);
    $("#filtro_turno").val(turnoSeleccionado);
    $("#modalHorarioGlobalTitle").text(`Paso 2: Registrar Horario - ${secCodigo} | Año ${anioTexto} | Turno ${turnoTexto}`);
    $("#accion").val("modificar");
    $("#proceso").text("REGISTRAR HORARIO").data("action-type", "modificar").addClass("btn-success");
    $("#sec_codigo_hidden").val(secCodigo);
    $("#ani_anio_hidden").val(anioAnio);
    $("#modal-horario").data("mode", "registrar");
    
    bloquesDeLaTablaActual = generarBloquesPorDefecto(turnoSeleccionado);
    
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
        btnRegistrar.prop('disabled', true).attr('title', mensajeTooltip).addClass('disabled-look');
        btnUnir.prop('disabled', true).attr('title', mensajeTooltip).addClass('disabled-look');
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

                if (respuesta.resultado === 'confirmar_conflicto') {
                    Swal.fire({
                        title: 'Conflictos Detectados en el Horario',
                        html: `${respuesta.mensaje}<br><br>¿Desea guardar el horario de todas formas?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, guardar de todas formas',
                        cancelButtonText: 'Cancelar y corregir'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            datos.append("forzar_guardado", "true");
                            enviaAjax(datos, boton);
                        }
                    });
                } else if (respuesta.resultado === 'confirmar_cohorte') {
                    Swal.fire({
                        title: 'Confirmación Requerida',
                        html: respuesta.mensaje,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, Crear',
                        cancelButtonText: 'No, Corregir'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            datos.append("forzar_cohorte", "true");
                            enviaAjax(datos, boton);
                        }
                    });
                } else if (respuesta.resultado == "consultar_agrupado") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    if (respuesta.mensaje && respuesta.mensaje.length > 0) {
                        allSecciones = respuesta.mensaje.map(s => ({ ...s,
                            sec_id: s.sec_codigo
                        }));
                        respuesta.mensaje.forEach(item => {
                          const tipoTexto = item.ani_tipo === 'regular' ? 'Regular' : 'Intensivo';
                          const botones_accion = `
        <button class="btn btn-icon btn-info ver-horario " data-sec-codigo="${item.sec_codigo}" data-ani-anio="${item.ani_anio}" title="Ver Horario"><img src="public/assets/icons/eye.svg" alt="Ver Horario"></button>
        <button class="btn btn-icon btn-secondary generar-reporte me-1" data-sec-codigo="${item.sec_codigo}" data-ani-anio="${item.ani_anio}" title="Generar Reporte del Horario"><img src="public/assets/icons/printer.svg" alt="Generar Reporte"></button>
        <button class="btn btn-icon btn-warning modificar-horario " data-sec-codigo="${item.sec_codigo}" data-ani-anio="${item.ani_anio}" title="Modificar Horario"><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
        <button class="btn btn-icon btn-danger eliminar-horario" data-sec-codigo="${item.sec_codigo}" data-ani-anio="${item.ani_anio}" title="Eliminar Horario"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
    `;
                            $("#resultadoconsulta").append(`<tr><td>${item.sec_codigo}</td><td>${item.sec_cantidad||'N/A'}</td><td>${item.ani_anio||'N/A'}</td><td>${tipoTexto}</td><td class="text-nowrap">${botones_accion}</td></tr>`);
                        });
                    }
                    crearDT();
                } else if (respuesta.resultado === 'registrar_seccion_ok') {
                    $('#modalRegistroSeccion').modal('hide');
                    muestraMensaje("success", 3000, "REGISTRAR", respuesta.mensaje);

                    const anioTexto = $('#anioId option:selected').text();
                    const anioValue = $('#anioId').val();

                    abrirModalHorarioParaNuevaSeccion(
                        respuesta.nuevo_codigo,
                        respuesta.nueva_cantidad,
                        anioTexto,
                        anioValue
                    );
                } else if (respuesta.resultado === 'modificar_ok') {
                    $('.modal').modal('hide');
                    
                    const modoModal = $("#modal-horario").data("mode");
                    const tituloAlerta = (modoModal === "registrar") ? "REGISTRAR" : "MODIFICAR";
                    muestraMensaje("success", 3000, tituloAlerta, respuesta.mensaje);

                    const datosGlobales = new FormData();
                    datosGlobales.append("accion", "obtener_datos_selects");
                    $.ajax({
                        url: "",
                        type: "POST",
                        data: datosGlobales,
                        contentType: false,
                        processData: false,
                        success: function(r) {
                            ocupacionGlobalCompleta = r.horarios_existentes || [];
                            Listar();
                        }
                    });
                } else if (respuesta.resultado === 'eliminar_ok') {
                    $('.modal').modal('hide');
                    muestraMensaje("success", 3000, "ELIMINAR", respuesta.mensaje);

                    const datosGlobales = new FormData();
                    datosGlobales.append("accion", "obtener_datos_selects");
                    $.ajax({
                        url: "",
                        type: "POST",
                        data: datosGlobales,
                        contentType: false,
                        processData: false,
                        success: function(r) {
                            ocupacionGlobalCompleta = r.horarios_existentes || [];
                            Listar();
                        }
                    });
                } else if (respuesta.resultado.endsWith("_ok")) {
                    $('.modal').modal('hide');
                    muestraMensaje("success", 3000, "¡ÉXITO!", respuesta.mensaje);

                    const datosGlobales = new FormData();
                    datosGlobales.append("accion", "obtener_datos_selects");
                    $.ajax({
                        url: "",
                        type: "POST",
                        data: datosGlobales,
                        contentType: false,
                        processData: false,
                        success: function(r) {
                            ocupacionGlobalCompleta = r.horarios_existentes || [];
                            Listar();
                        }
                    });
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

   
$(document).on('click', '.generar-reporte', function() {
    const secCodigo = $(this).data('sec-codigo');
    const anioAnio = $(this).data('ani-anio');

    
    $('#reporteSeccionCodigo').text(secCodigo);
    $('#reporte_sec_codigo_hidden').val(secCodigo);
    $('#reporte_ani_anio_hidden').val(anioAnio);


    $('#modalReporteHorario').modal('show');
});

$(document).on('click', '.btn-generar-reporte-tipo', function() {
    
    const secCodigo = $('#reporte_sec_codigo_hidden').val();
    const anioAnio = $('#reporte_ani_anio_hidden').val();
    const formato = $(this).data('tipo');

    
    $('#form_reporte_sec_codigo').val(secCodigo);
    $('#form_reporte_ani_anio').val(anioAnio);
    $('#form_reporte_formato').val(formato);


    $('#formGenerarReporte').submit();

   
    $('#modalReporteHorario').modal('hide');
});
    $('#cantidadSeccionModificar').on('input', checkForScheduleChanges);

    $('#modalEntradaHorario').on('show.bs.modal', function() {
        const mainScheduleModalInstance = bootstrap.Modal.getInstance(document.getElementById('modal-horario'));
        if (mainScheduleModalInstance) {
            mainScheduleModalInstance._config.keyboard = false;
        }
    }).on('hidden.bs.modal', function() {
        const mainScheduleModalInstance = bootstrap.Modal.getInstance(document.getElementById('modal-horario'));
        if (mainScheduleModalInstance) {
            mainScheduleModalInstance._config.keyboard = true;
        }
    });

    const modalBody = $("#modal-body-gestion-clase");

    modalBody.on("submit", "#formularioEntradaHorario", function(e) {
        e.preventDefault();
        guardarClase();
    });

    modalBody.on("click", "#btn-volver-a-lista", function() {
        const franjaInicio = currentClickedCell.data("franja-inicio");
        const diaNombre = currentClickedCell.data("dia-nombre");
        const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
        const clasesEnCelda = (horarioContenidoGuardado.get(key_horario) || []).map(c => c.data);

        renderizarModalDeGestion(clasesEnCelda, franjaInicio, diaNombre);
    });

    modalBody.on("click", "#btn-editar-clase-unica", function() {
        const franjaInicio = currentClickedCell.data("franja-inicio");
        const diaNombre = currentClickedCell.data("dia-nombre");
        const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
        const claseData = (horarioContenidoGuardado.get(key_horario) || [])[0].data;
        abrirFormularioClaseSimple(claseData, franjaInicio, diaNombre);
    });

    modalBody.on("click", "#btn-dividir-bloque", function() {
        isSplittingProcess = true;
        const franjaInicio = currentClickedCell.data("franja-inicio");
        const diaNombre = currentClickedCell.data("dia-nombre");
        abrirFormularioSubgrupo(null, franjaInicio, diaNombre);
    });

    modalBody.on("click", "#btn-eliminar-clase-unica, #btn-eliminar-clase-unica-desde-form", function() {
        eliminarSubgrupo('default');
    });

    modalBody.on("click", "#btn-anadir-otro-subgrupo", function() {
        const franjaInicio = currentClickedCell.data("franja-inicio");
        const diaNombre = currentClickedCell.data("dia-nombre");
        abrirFormularioSubgrupo(null, franjaInicio, diaNombre);
    });

    modalBody.on("click", ".btn-editar-subgrupo", function() {
        const subgrupoId = $(this).data('subgrupo-id');
        const franjaInicio = currentClickedCell.data("franja-inicio");
        const diaNombre = currentClickedCell.data("dia-nombre");
        const key_horario = `${franjaInicio.substring(0, 5)}-${normalizeDayKey(diaNombre)}`;
        const clasesEnCelda = (horarioContenidoGuardado.get(key_horario) || []).map(c => c.data);
        const claseAEditar = clasesEnCelda.find(c => (c.subgrupo || 'default') === subgrupoId);
        abrirFormularioSubgrupo(claseAEditar, franjaInicio, diaNombre);
    });

    modalBody.on("click", ".btn-eliminar-subgrupo", function() {
        eliminarSubgrupo($(this).data('subgrupo-id'));
    });

    $("#btnLimpiarHorario").on("click", function() {
        if (horarioContenidoGuardado.size === 0) {
            muestraMensaje("info", 2000, "Horario ya vacío", "No hay clases para limpiar.");
            return;
        }
        Swal.fire({
            title: '¿Está seguro de limpiar el horario?',
            text: "Esta acción eliminará todas las clases de la vista actual. Los cambios no serán permanentes hasta que guarde.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                horarioContenidoGuardado.clear();
                const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
                inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
                muestraMensaje("success", 3000, "Horario Limpiado", "Se han eliminado todas las clases. Presione 'Guardar Cambios' para hacer la acción permanente.");
                checkForScheduleChanges();
            }
        });
    });

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
            ocupacionGlobalCompleta = respuesta.horarios_existentes || [];
            modalDataLoaded = true;
           

            Listar();
        },
        error: function() {
            $('#resultadoconsulta').html('<tr><td colspan="4" class="text-center text-danger">Error crítico al cargar datos. Recargue.</td></tr>');
            muestraMensaje("error", 0, "Error Crítico", "No se pudieron cargar los datos iniciales.");
        }
    });

const mostrarPrompt = $(".main-content").data("mostrar-prompt-duplicar");
    if (mostrarPrompt === true) {
        const anioActivo = new Date().getFullYear(); 
        const anioAnterior = anioActivo - 1;

        Swal.fire({
            title: `Bienvenido al Año Académico ${anioActivo}`,
            html: `Hemos detectado que no hay secciones registradas para este año. ¿Desea duplicar la estructura de horarios del año <b>${anioAnterior}</b>?<br><br><small class="text-muted"><b>Nota:</b> Se copiarán las unidades curriculares, pero los <b>docentes y espacios</b> quedarán vacíos para ser asignados.</small>`,
            icon: 'question',
            showDenyButton: true,
            confirmButtonText: 'SÍ, DUPLICAR',
            denyButtonText: 'NO, EMPEZAR DE CERO',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                const datos = new FormData();
                datos.append("accion", "duplicar_anio_anterior");
                enviaAjax(datos, null);
            } else if (result.isDenied) {
                muestraMensaje('info', 4000, 'Entendido', 'Comenzará el año académico desde cero.');
            }
        });
    }

    $(document).on('click', '.ver-horario, .modificar-horario, .eliminar-horario, #btnIniciarRegistro, #btnAbrirModalUnir', function(e) {
        if (!modalDataLoaded && !$(this).prop('disabled')) {
            e.stopPropagation();
            muestraMensaje('info', 2000, 'Un momento...', 'Cargando datos necesarios, por favor intente de nuevo en un segundo.');
            return;
        }
    });

    $('#btnIniciarRegistro').on('click', function() {
        $("#formRegistroSeccion")[0].reset();
        $("#alerta-codigo").hide();
        $("#btnGuardarSeccion").prop("disabled", true);
        $("#modalRegistroSeccion").modal("show");
    });

    const codigoInput = $('#codigoSeccion');
    const anioInput = $('#anioId');
    const cantidadInput = $('#cantidadSeccion');
    const guardarBtn = $('#btnGuardarSeccion');
    const alertaCodigo = $('#alerta-codigo');

   function validarCodigoSeccion() {
  
      const codigo = codigoInput.val().toUpperCase(); 
    const anio = anioInput.val();
    
    guardarBtn.prop('disabled', true);
    alertaCodigo.hide();

    if (codigo.length === 0) {
        return;
    }


    const match = codigo.match(/^([A-Z]{2,3})(\d{4})$/);

  
    if (!match) {
        alertaCodigo.html(`<strong>Formato inválido.</strong> Debe tener 2-3 letras seguido de exactamente 4 dígitos (Ej: IN1101, IIN3104).`).show();
        return;
    }

    const prefix = match[1]; 
    const numericPart = match[2];
    

    if (numericPart.length > 4) {
        alertaCodigo.html(`<strong>Error:</strong> El código no puede tener más de 4 dígitos numéricos.`).show();
        return;
    } 
    const firstDigit = numericPart.charAt(0); 

    if (['0', '1', '2'].includes(firstDigit)) {
        if (prefix !== 'IN') {
            alertaCodigo.html(`<strong>Error de formato:</strong> Las secciones que inician con 0, 1, o 2 deben tener el prefijo <strong>IN</strong>.`).show();
            return;
        }
    } 

    else if (['3', '4'].includes(firstDigit)) {
        if (prefix !== 'IIN') {
            alertaCodigo.html(`<strong>Error de formato:</strong> Las secciones que inician con 3 o 4 deben tener el prefijo <strong>IIN</strong>.`).show();
            return;
        }
    }

    if (!anio) {
         alertaCodigo.html(`Seleccione un año académico para verificar la disponibilidad del código.`).show();
         return;
    }
    
   
    const ultimoDigito = parseInt(numericPart.charAt(3));
    let cohorteExiste = false;
    
    const datosMalla = new FormData();
    datosMalla.append("accion", "verificar_malla");
    datosMalla.append("numeroMalla", ultimoDigito);
    
    $.ajax({
        url: "",
        type: "POST",
        data: datosMalla,
        contentType: false,
        processData: false,
        async: false,
        success: function(respuestaMalla) {
            if (respuestaMalla.resultado === 'ok') {
                cohorteExiste = respuestaMalla.existe;
            }
        }
    });
    
    if (!cohorteExiste) {
        alertaCodigo.html(`<strong>Cohorte no encontrada.</strong> No existe la Cohorte ${ultimoDigito} creada en la malla. `).show();
 
    }
    
    const datos = new FormData();
    datos.append("accion", "verificar_codigo_seccion");
    datos.append("codigoSeccion", codigo); 
    datos.append("anioId", anio);

    $.ajax({
        url: "",
        type: "POST",
        data: datos,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            if (respuesta.resultado === 'ok') {
                if(respuesta.existe) {
                    alertaCodigo.html(`<strong>Código no disponible.</strong> Ya existe una sección con este código para el año seleccionado.`).show();
                    guardarBtn.prop('disabled', true);
                } else {
                 
                    if (cantidadInput.val() !== '') {
                        guardarBtn.prop('disabled', false);
                    }
                  
                    if (cohorte3Existe) {
                        alertaCodigo.hide();
                    }
                }
            } else {
                alertaCodigo.html(`Error al validar el código. Intente de nuevo.`).show();
            }
        },
        error: function() {
             alertaCodigo.html(`Error de conexión al validar el código.`).show();
        }
    });
}

    codigoInput.on('keyup', validarCodigoSeccion);
    anioInput.on('change', validarCodigoSeccion);
    cantidadInput.on('keyup', function() {
       
        if (codigoInput.val() && anioInput.val() && cantidadInput.val() !== '') {
            validarCodigoSeccion();
        } else {
             guardarBtn.prop('disabled', true);
        }
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
            const trayecto = codigoStr.match(/\d/)[0];
            const turno = codigoStr.match(/\d/g)[1];
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
                    const checkboxHtml = `<div class="form-check"><input class="form-check-input" type="checkbox" name="secciones_a_unir[]" value="${s.sec_codigo}" id="check_sec_${s.sec_codigo}" data-group-key="${key}"><label class="form-check-label" for="check_sec_${s.sec_codigo}">${s.sec_codigo} (${s.sec_cantidad} Est.)</label></div>`;
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
    const ani_anio = $(this).data('ani-anio');
    const isView = $(this).hasClass('ver-horario');
    const isModify = $(this).hasClass('modificar-horario');
    const isDelete = $(this).hasClass('eliminar-horario');
    const seccionData = allSecciones.find(s => s.sec_codigo == sec_codigo);
    if (!seccionData) return;

    let turnoSeleccionado = 'mañana';
    const digitos = seccionData.sec_codigo.toString().match(/\d/g);
    if (digitos && digitos.length >= 2) {
        const segundoDigito = digitos[1];
        if (segundoDigito === '1') turnoSeleccionado = 'mañana';
        else if (segundoDigito === '2') turnoSeleccionado = 'tarde';
        else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        else turnoSeleccionado = 'mañana';
    }

    const datos = new FormData();
    datos.append("accion", "consultar_detalles");
    datos.append("sec_codigo", sec_codigo);
    datos.append("ani_anio", ani_anio);
   $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function(respuesta) {
        if (respuesta.resultado === 'ok' && Array.isArray(respuesta.mensaje)) {
            horarioContenidoGuardado.clear();

            if (respuesta.mensaje.length > 0) {
                turnoSeleccionado = detectarTurnoPorHorario(respuesta.mensaje);
            }

            const bloquesParaEstaTabla = respuesta.mensaje.length > 0 
                ? construirBloquesParaHorario(respuesta.mensaje, turnoSeleccionado)
                : generarBloquesPorDefecto(turnoSeleccionado);
            bloquesDeLaTablaActual = bloquesParaEstaTabla;

            respuesta.mensaje.forEach(clase => {
                const startIndex = bloquesParaEstaTabla.findIndex(b => b.tur_horainicio === clase.hora_inicio);
                let span = 1;
                if (startIndex > -1) {
                    for (let i = startIndex + 1; i < bloquesParaEstaTabla.length; i++) {
                        if (bloquesParaEstaTabla[i].tur_horafin <= clase.hora_fin) {
                            span++;
                        } else if (bloquesParaEstaTabla[i].tur_horainicio < clase.hora_fin) {
                            span++;
                            break;
                        } else {
                            break;
                        }
                    }
                } else {
                    console.warn(`No se encontró bloque para hora_inicio: ${clase.hora_inicio}`);
                }
                clase.bloques_span = span;

                const dia_key = normalizeDayKey(clase.dia);
                const key = `${clase.hora_inicio.substring(0, 5)}-${dia_key}`;
                if (!horarioContenidoGuardado.has(key)) {
                    horarioContenidoGuardado.set(key, []);
                }
                horarioContenidoGuardado.get(key).push({ data: clase });
            });

            const seccionTexto = `${seccionData.sec_codigo} (${seccionData.sec_cantidad} Est.) (Año ${seccionData.ani_anio})`;

            if (isDelete) {
                $("#detallesParaEliminar").html(`<p class="mb-1"><strong>Código:</strong> ${seccionData.sec_codigo}</p><p class="mb-1"><strong>Estudiantes:</strong> ${seccionData.sec_cantidad}</p><p class="mb-0"><strong>Año:</strong> ${seccionData.ani_anio}</p>`);
                inicializarTablaHorario(turnoSeleccionado, "#tablaEliminarHorario", true);
                $("#btnProcederEliminacion").data('sec-codigo', sec_codigo).data('ani-anio', ani_anio);  
                $("#modalConfirmarEliminar").modal('show');
            } else if (isView) {
                $("#modalVerHorarioTitle").text(`Horario: ${seccionTexto}`);
                inicializarTablaHorario(turnoSeleccionado, "#tablaVerHorario", true);
                $("#modalVerHorario").modal("show");
            } else if (isModify) {
                limpiaModalPrincipal();
                const turnoTexto = turnoSeleccionado.charAt(0).toUpperCase() + turnoSeleccionado.slice(1);
                const anioTipo = seccionData.ani_tipo === 'regular' ? 'Regular' : 'Intensivo';
                $("#sec_codigo_hidden").val(sec_codigo);
                $("#ani_anio_hidden").val(ani_anio);
                $("#cantidadSeccionModificar").val(seccionData.sec_cantidad);
                $("#filtro_turno").val(turnoSeleccionado);
                $("#modalHorarioGlobalTitle").text(`MODIFICAR Horario - ${seccionData.sec_codigo} | Año ${seccionData.ani_anio} (${anioTipo}) | Turno ${turnoTexto}`);
                $("#accion").val("modificar");
                $("#proceso").text("MODIFICAR").addClass("btn-primary");
                $("#modal-horario").data("mode", "modificar");
                inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
                $("#proceso").prop("disabled", true);
                $('#modal-horario').data('initial-state', getScheduleStateString());
                $("#modal-horario").modal("show");
            }
        }
    }
});
});

    $('#filtro_turno').on("change", function() {
        const turnoSeleccionado = $(this).val();
        const clasesExistentes = [];
        for (const claseArray of horarioContenidoGuardado.values()) {
            claseArray.forEach(claseObj => clasesExistentes.push(claseObj.data));
        }
        
        if (clasesExistentes.length > 0) {
            bloquesDeLaTablaActual = construirBloquesParaHorario(clasesExistentes, turnoSeleccionado);
        } else {
            bloquesDeLaTablaActual = generarBloquesPorDefecto(turnoSeleccionado);
        }
        
        inicializarTablaHorario(turnoSeleccionado, "#tablaHorario", false);
    });

    $('#modal-horario, #modalVerHorario, #modalConfirmarEliminar, #modalUnirHorarios').on('hidden.bs.modal', function() {
        if ($(this).find('form').length > 0) $(this).find('form').removeClass('was-validated')[0].reset();
        $("#unirSeccionesContainer input[type='checkbox']").prop('disabled', false);
    });

    $('#modalEntradaHorario').on('hidden.bs.modal', function() {
        if (isSplittingProcess && !hasSaved) {
            const key_horario = `${currentClickedCell.data("franja-inicio").substring(0, 5)}-${normalizeDayKey(currentClickedCell.data("dia-nombre"))}`;
            let clasesArray = horarioContenidoGuardado.get(key_horario);
            if (clasesArray && clasesArray.length === 1 && clasesArray[0].data.subgrupo === 'A') {
                clasesArray[0].data.subgrupo = null;
                horarioContenidoGuardado.set(key_horario, clasesArray);
            }
        }
        isSplittingProcess = false;
        hasSaved = false;
    });

    $("#formRegistroSeccion").on("submit", function(e) {
        e.preventDefault();
        const datos = new FormData(this);
        enviaAjax(datos, $("#btnGuardarSeccion"));
    });

    $("#btnProcederEliminacion").on("click", function() {
        const sec_codigo = $(this).data('sec-codigo');
        $('#modalConfirmarEliminar').modal('hide');
        setTimeout(() => {
            const seccion = allSecciones.find(s => s.sec_codigo == sec_codigo);
            Swal.fire({
                title: '¿Está seguro de eliminar esta sección?',
                html: `Esta acción es irreversible y eliminará permanentemente la sección <strong>${seccion.sec_codigo}</strong>.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
               if (result.isConfirmed) {
                  
                    const seccion = allSecciones.find(s => s.sec_codigo == sec_codigo); 
                    const datos = new FormData();
                    datos.append("accion", "eliminar_seccion_y_horario");
                    datos.append("sec_codigo", sec_codigo);
                    datos.append("ani_anio", seccion.ani_anio); 
                    enviaAjax(datos, null);
                }
            });
        }, 500);
    });

    $("#proceso").on("click", function() {
        procederConGuardado();
    });

    $("#unirSeccionesContainer").on("change", "input[type='checkbox']", function() {
        const selectorOrigen = $("#unirSeccionOrigen");
        selectorOrigen.empty().append('<option value="" disabled selected>Seleccione una sección...</option>');
        
        const seleccionados = $("#unirSeccionesContainer input:checked");
        
       if(seleccionados.length > 0) {
            const primerGrupo = seleccionados.first().data('group-key');
            $("#unirSeccionesContainer input[type='checkbox']").each(function() {
                const esSeleccionado = $(this).is(":checked");
                if ($(this).data('group-key') !== primerGrupo && !esSeleccionado) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
        } else {
            $("#unirSeccionesContainer input[type='checkbox']").prop('disabled', false);
        }

        seleccionados.each(function() {
            const seccion = allSecciones.find(s => s.sec_codigo == $(this).val());
            if (seccion) {
                selectorOrigen.append(`<option value="${seccion.sec_codigo}">${seccion.sec_codigo}</option>`);
            }
        });
    });

    $("#formUnirHorarios").on("submit", function(e) {
        e.preventDefault();
        if(this.checkValidity() === false) {
            e.stopPropagation();
        } else {
            const seleccionados = $("#unirSeccionesContainer input:checked");
            if(seleccionados.length < 2) {
                muestraMensaje('warning', 3000, 'Selección Incompleta', 'Debe marcar al menos 2 secciones para unir.');
                return;
            }
             const datos = new FormData(this);
             enviaAjax(datos, $("#btnConfirmarUnion"));
        }
        $(this).addClass('was-validated');
    });
    
   
    const validarBloqueHorario = (inicioNuevo, finNuevo, ignorarInicio = null) => {
        if (!inicioNuevo || !finNuevo) return `Debes especificar una hora de inicio y fin.`;
        if (inicioNuevo >= finNuevo) return `La hora de inicio debe ser anterior a la hora de fin.`;

        let haySolapamiento = bloquesDeLaTablaActual.some(bloque => {
           
            if (bloque.tur_horainicio === ignorarInicio) {
                return false;
            }
            const inicioExistente = bloque.tur_horainicio.substring(0, 5);
            const finExistente = bloque.tur_horafin.substring(0, 5);
         
            return (inicioNuevo < finExistente && finNuevo > inicioExistente);
        });

        if (haySolapamiento) return 'El bloque horario se solapa con un bloque existente.';
        return null;
    };

   
    const abrirModalGestionFila = async (franjaAEditar = null) => {
        
        let horaInicioSugerida = "13:00";
        let horaFinSugerida = "13:40";

        if (franjaAEditar) {
            const bloque = bloquesDeLaTablaActual.find(b => b.tur_horainicio === franjaAEditar);
            if (bloque) {
                horaInicioSugerida = bloque.tur_horainicio.substring(0, 5);
                horaFinSugerida = bloque.tur_horafin.substring(0, 5);
            }
        } else if (bloquesDeLaTablaActual.length > 0) {
            const ultimoBloque = bloquesDeLaTablaActual[bloquesDeLaTablaActual.length - 1];
            const fechaFin = new Date(`1970-01-01T${ultimoBloque.tur_horafin}`);
            horaInicioSugerida = fechaFin.toTimeString().substring(0, 5);
            fechaFin.setMinutes(fechaFin.getMinutes() + 40);
            horaFinSugerida = fechaFin.toTimeString().substring(0, 5);
        }

        const { value: formValues, isConfirmed } = await Swal.fire({
            title: franjaAEditar ? 'Editar Bloque Horario' : 'Añadir Nuevo Bloque Horario',
            html: `
                <p class="text-muted">Introduce la hora de inicio y fin para la franja horaria.</p>
                <div class="form-floating mb-2">
                    <input type="time" id="swal-hora-inicio" class="form-control" value="${horaInicioSugerida}" step="600">
                    <label for="swal-hora-inicio">Hora de Inicio</label>
                </div>
                <div class="form-floating">
                    <input type="time" id="swal-hora-fin" class="form-control" value="${horaFinSugerida}" step="600">
                    <label for="swal-hora-fin">Hora de Fin</label>
                </div>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: franjaAEditar ? 'GUARDAR CAMBIOS' : 'AÑADIR FILA',
            cancelButtonText: 'CANCELAR',
            didOpen: () => {
                const inicioInput = document.getElementById('swal-hora-inicio');
                const finInput = document.getElementById('swal-hora-fin');
                const confirmButton = Swal.getConfirmButton();

                function onInput() {
                    const error = validarBloqueHorario(inicioInput.value, finInput.value, franjaAEditar);
                    if (error) {
                        Swal.showValidationMessage(error);
                        confirmButton.disabled = true;
                    } else {
                        Swal.resetValidationMessage();
                        confirmButton.disabled = false;
                    }
                }
                inicioInput.addEventListener('input', onInput);
                finInput.addEventListener('input', onInput);
                onInput();
            },
            preConfirm: () => {
                const inicio = document.getElementById('swal-hora-inicio').value;
                const fin = document.getElementById('swal-hora-fin').value;
                if (validarBloqueHorario(inicio, fin, franjaAEditar)) {
                    return false;
                }
                return { inicio, fin };
            }
        });

        if (isConfirmed && formValues) {
            const nuevoInicio = formValues.inicio + ':00';
            const nuevoFin = formValues.fin + ':00';
            const turnoActualFiltro = $("#filtro_turno").val() || 'todos';

              if (franjaAEditar) {  
            const bloqueIndex = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaAEditar);
            if (bloqueIndex > -1) {
                const oldBlock = { ...bloquesDeLaTablaActual[bloqueIndex] };
                
                bloquesDeLaTablaActual[bloqueIndex].tur_horainicio = nuevoInicio;
                bloquesDeLaTablaActual[bloqueIndex].tur_horafin = nuevoFin;
                bloquesDeLaTablaActual.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));
                
                const llavesAMover = [];
                horarioContenidoGuardado.forEach((value, key) => {
                    if (key.startsWith(oldBlock.tur_horainicio.substring(0, 5))) {
                        llavesAMover.push({ oldKey: key, value });
                    }
                });

                llavesAMover.forEach(item => {
                    const newKey = nuevoInicio.substring(0, 5) + item.oldKey.substring(5);
                    const updatedValue = item.value.map(v => {
                        const span = v.data.bloques_span;
                        const newStartIndex = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === nuevoInicio);
                        
                        let newEndIndex = newStartIndex + span - 1;
                        let newHoraFin = (newEndIndex < bloquesDeLaTablaActual.length) ? bloquesDeLaTablaActual[newEndIndex].tur_horafin : nuevoFin;
                        let newSpan = span;

                        if (newEndIndex >= bloquesDeLaTablaActual.length) {
                            newSpan = 1; newHoraFin = nuevoFin;
                        }
                        
                        return { ...v, data: { ...v.data, hora_inicio: nuevoInicio, hora_fin: newHoraFin, bloques_span: newSpan }};
                    });
                    
                    horarioContenidoGuardado.delete(item.oldKey);
                    horarioContenidoGuardado.set(newKey, updatedValue);
                });
            }
        } else {
                bloquesDeLaTablaActual.push({ tur_horainicio: nuevoInicio, tur_horafin: nuevoFin });
            }
            
            bloquesDeLaTablaActual.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));
            inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
            checkForScheduleChanges();
            muestraMensaje("success", 2500, "Éxito", `El bloque horario ha sido ${franjaAEditar ? 'modificado' : 'agregado'}.`);
        }
    };

    
    $(document).on('click', '#btnAnadirFilaHorario', function(e) {
        e.preventDefault();
        abrirModalGestionFila(null);
    });

    
    $(document).on('click', '.btn-editar-fila', function(e) {
        e.preventDefault();
        const franjaInicio = $(this).data('franja-inicio');
        abrirModalGestionFila(franjaInicio);
    });

   
    $(document).on('click', '.btn-eliminar-fila', function() {
        const franjaInicio = $(this).data('franja-inicio');
        const inicioCorto = franjaInicio.substring(0, 5);
        
        let clasesEnLaFila = false;
        horarioContenidoGuardado.forEach((value, key) => {
            if (key.startsWith(inicioCorto)) {
                clasesEnLaFila = true;
            }
        });

        const procederEliminacion = () => {
            bloquesDeLaTablaActual = bloquesDeLaTablaActual.filter(b => b.tur_horainicio !== franjaInicio);
            
            const llavesAEliminar = [];
            horarioContenidoGuardado.forEach((value, key) => {
                if (key.startsWith(inicioCorto)) {
                    llavesAEliminar.push(key);
                }
            });
            llavesAEliminar.forEach(key => horarioContenidoGuardado.delete(key));

            const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
            inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
            checkForScheduleChanges();
            muestraMensaje("info", 2000, "Fila Eliminada", "El bloque horario y sus clases han sido eliminados.");
        };

        if (clasesEnLaFila) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta fila contiene clases asignadas. ¡Eliminarla también borrará esas clases del horario!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'SÍ, ELIMINAR',
                cancelButtonText: 'CANCELAR'
            }).then((result) => {
                if (result.isConfirmed) {
                    procederEliminacion();
                }
            });
        } else {
            procederEliminacion();
        }
    });


});

