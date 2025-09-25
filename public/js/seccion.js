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
let bloquesDeLaTablaActual = [];
let allUcs = [],
    allEspacios = [],
    allDocentes = [],
    allSecciones = [],
    allTurnos = [],
    allCohortes = [],
    ocupacionGlobalCompleta = [];
let modalDataLoaded = false;
let isSplittingProcess = false;
let hasSaved = false;

// CORRECCIÓN 1: Esta función ahora devuelve un array con TODOS los conflictos encontrados.
function checkForConflicts(newClassDetails) {
    const { docId, espIdJson, dia, secId, horaInicioNueva, horaFinNueva } = newClassDetails;
    const foundConflicts = []; // Usaremos un array para acumular conflictos
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
                const prefijo = getPrefijoSeccion(claseExistente.sec_codigo);
                const seccionConflicto = `<strong>${prefijo}${claseExistente.sec_codigo}</strong>`;
                
                // Conflicto de docente
                if (docId && String(claseExistente.doc_cedula) == docId) {
                    foundConflicts.push({ // Añade al array en lugar de retornar
                        type: 'docente',
                        message: `<b>Conflicto:</b> Docente ya asignado en sección ${seccionConflicto} a esta hora.`
                    });
                }
                
                // Conflicto de espacio
                const claseExistenteEspKey = `${claseExistente.esp_numero}|${claseExistente.esp_tipo}|${claseExistente.esp_edificio}`;
                if (espKey && claseExistenteEspKey === espKey) {
                    foundConflicts.push({ // Añade al array en lugar de retornar
                        type: 'espacio',
                        message: `<b>Conflicto:</b> Espacio ya ocupado por la sección ${seccionConflicto} a esta hora.`
                    });
                }
            }
        }
    }
    // Retorna el resultado final después de revisar todo
    return { 
        hasConflict: foundConflicts.length > 0, 
        messages: foundConflicts // Devuelve el array completo de mensajes
    };
}
// --- FIN DE LA LÓGICA PORTADA ---


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

    const bloquesTurno = allTurnos.filter(turno => {
        const horaInicio = parseInt(turno.tur_horainicio.substring(0, 2), 10);
        if (filtroTurno === 'todos') return true;
        if (filtroTurno === 'mañana') return horaInicio < 13;
        if (filtroTurno === 'tarde') return horaInicio >= 13 && horaInicio < 18;
        if (filtroTurno === 'noche') return horaInicio >= 18;
        return false;
    });

    const todosLosBloquesMap = new Map();

    bloquesTurno.forEach(b => {
        todosLosBloquesMap.set(b.tur_horainicio, {
            tur_horainicio: b.tur_horainicio,
            tur_horafin: b.tur_horafin,
            isCustom: false
        });
    });

    for (const claseArray of horarioContenidoGuardado.values()) {
        if (Array.isArray(claseArray) && claseArray.length > 0) {
            const clase = claseArray[0];
            const horaInicio = clase.data.hora_inicio;
            if (clase.data && horaInicio && !todosLosBloquesMap.has(horaInicio)) {
                todosLosBloquesMap.set(horaInicio, {
                    tur_horainicio: horaInicio,
                    tur_horafin: clase.data.hora_fin,
                    isCustom: true
                });
            }
        }
    }

    const bloquesDeLaTabla = Array.from(todosLosBloquesMap.values())
        .sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));

    if (targetTableId === "#tablaHorario") {
        bloquesDeLaTablaActual = bloquesDeLaTabla;
    }

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

        const celdaHora = $("<td>").css({
            'display': 'flex',
            'justify-content': 'center',
            'align-items': 'center'
        });

        const textoHora = $("<span>").text(`${formatTime12Hour(bloque.tur_horainicio)} - ${formatTime12Hour(bloque.tur_horafin)}`);
        celdaHora.append(textoHora);

        if (bloque.isCustom && !isViewOnly) {
            const botonEliminar = $("<button type='button' class='btn btn-sm btn-eliminar-fila-personalizada' title='Eliminar esta fila'>")
                .html('<img src="public/assets/icons/trash.svg" alt="Eliminar" style="height: 1em; opacity: 0.6; margin-left: 8px;">')
                .data('franja-inicio', bloque.tur_horainicio)
                .css({
                    'border': 'none',
                    'background': 'transparent',
                    'padding': '0 5px'
                });
            celdaHora.append(botonEliminar);
        }
        row.append(celdaHora);

        dias.forEach((dia) => {
            const dia_key = normalizeDayKey(dia);
            const key_actual = `${bloque.tur_horainicio.substring(0, 5)}-${dia_key}`;

            if (celdasProcesadas.has(key_actual)) {
                return;
            }

            const cell = $("<td>").attr("data-franja-inicio", bloque.tur_horainicio).attr("data-dia-nombre", dia);
            cell.css('vertical-align', 'top');
            if (!isViewOnly) {
                cell.addClass("celda-horario");
            }

            if (horarioMapeado.has(key_actual)) {
                const claseArray = horarioMapeado.get(key_actual);

                if (Array.isArray(claseArray) && claseArray.length > 0) {
                    const primeraClase = claseArray[0].data;
                    let bloques_span = 1;
                    const horaFinClaseStr = primeraClase.hora_fin.substring(0, 5);
                    for (let i = rowIndex + 1; i < bloquesDeLaTabla.length; i++) {
                        const bloqueSiguiente = bloquesDeLaTabla[i];
                        if (bloqueSiguiente.tur_horainicio.substring(0, 5) < horaFinClaseStr) {
                            bloques_span++;
                        } else {
                            break;
                        }
                    }

                    let combinedHtml;
                    if (claseArray.length > 1) {
                        let columna1 = `<td style="width: 50%; vertical-align: top; border-right: 1px solid #dee2e6; padding: 2px;">`;
                        let columna2 = `<td style="width: 50%; vertical-align: top; padding: 2px;">`;

                        if (claseArray[0]) columna1 += generarCellContent(claseArray[0].data);
                        if (claseArray[1]) columna2 += generarCellContent(claseArray[1].data);

                        columna1 += '</td>';
                        columna2 += '</td>';
                        combinedHtml = `<table style="width: 100%; border: none; height: 100%;"><tbody><tr>${columna1}${columna2}</tr></tbody></table>`;

                    } else {
                        combinedHtml = generarCellContent(claseArray[0].data);
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
    }
}

function generarCellContent(clase) {
    const uc_nombre_completo = clase.uc_codigo ? (allUcs.find(u => u.uc_codigo == clase.uc_codigo)?.uc_nombre || `UC Inválida`) : '<i>(Sin UC)</i>';
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

    const editButton = `
        <button type="button" class="btn btn-light btn-edit-icon" title="Gestionar este bloque" style="border: none; padding: 4px 8px; line-height: 1;">
            <img src="public/assets/icons/edit.svg" style="width: 1.1em; height: 1.1em; opacity: 0.7;">
        </button>
    `;

    return `<div class="subgroup-item p-1" style="display: flex; align-items: center; justify-content: space-between;" data-subgrupo-id="${subgrupoId}">
                <div class="subgroup-content" style="cursor: pointer; flex-grow: 1;">
                    <p class="m-0" style="font-size:0.8em;">${subgrupoDisplay}<strong>${uc}</strong></p>
                    <small class="text-muted" style="font-size:0.7em;">${codigoEspacioFormateado} / ${doc_nombre}</small>
                </div>
                ${editButton}
            </div>`;
}


function abreviarNombreLargo(nombre, longitudMaxima = 25) {
    if (typeof nombre !== 'string' || nombre.length <= longitudMaxima) {
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

function inicializarFiltroUcPorDocente(docenteSelect, ucSelect) {
    docenteSelect.on("change", function() {
        const docCedula = $(this).val();
        const ucToSelect = ucSelect.data('uc-to-select-after-filter');

        if (!docCedula) {
            ucSelect.empty().append('<option value="">Seleccione una UC</option>');
            const secCodigo = $("#sec_codigo_hidden").val();
            const trayecto = secCodigo ? String(secCodigo).charAt(0) : null;
            
            const ucsFiltradas = trayecto ? allUcs.filter(uc => uc.uc_trayecto == trayecto) : allUcs;
            
            ucsFiltradas.forEach(uc => ucSelect.append(`<option value="${uc.uc_codigo}">${uc.uc_nombre}</option>`));

            ucSelect.prop("disabled", false).trigger('change');
            return;
        }

        const datos = new FormData();
        datos.append("accion", "obtener_uc_por_docente");
        datos.append("doc_cedula", docCedula);
        datos.append("sec_codigo_actual", $("#sec_codigo_hidden").val());

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
                    const mensaje = respuesta.mensaje_uc || "No hay UCs asignadas";
                    ucSelect.append(`<option value="">${mensaje}</option>`).prop("disabled", true);
                }

                if (ucToSelect) {
                    ucSelect.val(ucToSelect).trigger('change');
                    ucSelect.data('uc-to-select-after-filter', null);
                }
            },
            error: function() {
                ucSelect.empty().append('<option value="">Error al cargar UCs</option>').prop("disabled", true);
            }
        });
    });
}


function abrirFormularioClaseSimple(claseData, franjaInicio, diaNombre) {
    const modalBody = $("#modal-body-gestion-clase");
    const turnoCompleto = bloquesDeLaTablaActual.find(b => b.tur_horainicio === franjaInicio);
    const isEditing = !!claseData;

    const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
    let maxBloques = 0;
    if (indiceInicio !== -1) {
        maxBloques = 1;
        for (let i = indiceInicio; i < bloquesDeLaTablaActual.length - 1; i++) {
            if (bloquesDeLaTablaActual[i].tur_horafin === bloquesDeLaTablaActual[i + 1].tur_horainicio) {
                maxBloques++;
            } else {
                break;
            }
        }
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
                <label for="modalSeleccionarDocente" class="form-label">Docente</label>
                <select class="form-select" id="modalSeleccionarDocente" style="width: 100%;"></select>
                <div id="docente-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarUc" class="form-label">Unidad Curricular</label>
                <select class="form-select" id="modalSeleccionarUc" style="width: 100%;"></select>
                <div id="uc-conflicto-info" class="form-text text-danger mt-1"></div>
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
    ucSelect.empty().append('<option value="">Seleccione una UC o un docente para filtrar</option>');
    const secCodigo = $("#sec_codigo_hidden").val();
    const trayecto = secCodigo ? String(secCodigo).charAt(0) : null;
    
    const ucsFiltradas = trayecto ? allUcs.filter(uc => uc.uc_trayecto == trayecto) : allUcs;
    
    ucsFiltradas.forEach(uc => ucSelect.append(`<option value="${uc.uc_codigo}">${uc.uc_nombre}</option>`));
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

    inicializarFiltroUcPorDocente(docenteSelect, ucSelect);
    
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
        ucSelect.data('uc-to-select-after-filter', claseData.uc_codigo);
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
    const placeholderSubgrupo = clasesEnCelda.some(c => c.data.subgrupo === 'A') ? 'Tarde, Práctica...' : 'A, Mañana...';

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
                <label for="modalSeleccionarDocente" class="form-label">Docente</label>
                <select class="form-select" id="modalSeleccionarDocente" style="width: 100%;"></select>
                 <div id="docente-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarUc" class="form-label">Unidad Curricular</label>
                <select class="form-select" id="modalSeleccionarUc" style="width: 100%;"></select>
                <div id="uc-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="modalSeleccionarEspacio" class="form-label">Espacio (Aula/Lab)</label>
                <select class="form-select" id="modalSeleccionarEspacio" style="width: 100%;"></select>
                <div id="espacio-conflicto-info" class="form-text text-danger mt-1"></div>
            </div>
            <button type="submit" class="btn btn-primary">${esEdicion ? 'Guardar Cambios' : 'Añadir Subgrupo'}</button>
            ${mostrarBotonVolver ? '<button type="button" class="btn btn-secondary" id="btn-volver-a-lista">Volver a la lista</button>' : ''}
        </form>
    `;
    modalBody.html(formHtml);

    const select2Config = {
        theme: "bootstrap-5",
        dropdownParent: $('#modalEntradaHorario .modal-content')
    };

    const ucSelect = $("#modalSeleccionarUc");
    ucSelect.empty().append('<option value="">Seleccione una UC o un docente para filtrar</option>');
    const secCodigo = $("#sec_codigo_hidden").val();
    const trayecto = secCodigo ? String(secCodigo).charAt(0) : null;
    
    const ucsFiltradas = trayecto ? allUcs.filter(uc => uc.uc_trayecto == trayecto) : allUcs;
    
    ucsFiltradas.forEach(uc => ucSelect.append(`<option value="${uc.uc_codigo}">${uc.uc_nombre}</option>`));
    ucSelect.select2(select2Config);

    const docenteSelect = $("#modalSeleccionarDocente");
    docenteSelect.empty().append('<option value="">Seleccionar Docente</option>');
    allDocentes.forEach(doc => docenteSelect.append(`<option value="${doc.doc_cedula}">${doc.doc_nombre} ${doc.doc_apellido}</option>`));
    docenteSelect.select2(select2Config);

    const espacioSelect = $("#modalSeleccionarEspacio");
    espacioSelect.empty().append('<option value="">Seleccionar Espacio</option>');
    allEspacios.forEach(esp => espacioSelect.append(`<option value='${JSON.stringify({numero: esp.numero, tipo: esp.tipo, edificio: esp.edificio})}'>${esp.numero} (${esp.tipo} - ${esp.edificio})</option>`));
    espacioSelect.select2(select2Config);

    inicializarFiltroUcPorDocente(docenteSelect, ucSelect);
    
    $("#modalSeleccionarDocente, #modalSeleccionarUc, #modalSeleccionarEspacio, #modalSubgrupo").on('change keyup', function() {
        validarBloqueEnTiempoReal();
    });

    if (claseData) {
        $("#modalSubgrupo").val(claseData.subgrupo);
        ucSelect.data('uc-to-select-after-filter', claseData.uc_codigo);
        $("#modalSeleccionarDocente").val(claseData.doc_cedula).trigger('change');
        if (claseData.espacio && claseData.espacio.numero) {
            $("#modalSeleccionarEspacio").val(JSON.stringify(claseData.espacio)).trigger('change');
        }
    }
}

function validarBloqueEnTiempoReal() {
    // Limpiar alertas previas
    $('#docente-conflicto-info, #uc-conflicto-info, #espacio-conflicto-info, #duracion-conflicto-info').html('');
    const submitButton = $('#formularioEntradaHorario button[type="submit"]');
    submitButton.prop('disabled', false);

    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const dia_key = normalizeDayKey(diaNombre);

    // --- MODIFICACIÓN INICIA: Lógica de validación de duración local, corregida y robusta ---
    const duracionSelect = $("#modalDuracionSubgrupo");
    if (duracionSelect.length > 0) {
        const nuevaDuracion = parseInt(duracionSelect.val(), 10);
        const indiceInicio = bloquesDeLaTablaActual.findIndex(b => b.tur_horainicio === franjaInicio);
        
        // Obtiene la clave de la clase que se está editando para poder ignorarla en la verificación
        const originalKey = `${franjaInicio.substring(0, 5)}-${dia_key}`;

        // Itera sobre los bloques que la clase ocuparía si se extiende
        for (let i = 1; i < nuevaDuracion; i++) {
            const indiceBloqueSiguiente = indiceInicio + i;
            if (indiceBloqueSiguiente < bloquesDeLaTablaActual.length) {
                const bloqueSiguiente = bloquesDeLaTablaActual[indiceBloqueSiguiente];
                const tiempoBloqueSiguiente = bloqueSiguiente.tur_horainicio.substring(0, 5);

                // Revisa en todo el horario guardado si algún bloque futuro está ocupado por OTRA clase
                for (const [key, claseArray] of horarioContenidoGuardado.entries()) {
                    // Si la clase encontrada es la que estamos editando, la ignora y continúa
                    if (key === originalKey) {
                        continue;
                    }
                    
                    const claseExistente = claseArray[0].data;
                    const inicioExistente = claseExistente.hora_inicio.substring(0, 5);
                    const finExistente = claseExistente.hora_fin.substring(0, 5);
                    
                    // Comprueba si el bloque que queremos usar está dentro del rango de una clase existente en el mismo día
                    if (normalizeDayKey(claseExistente.dia) === dia_key) {
                        if (tiempoBloqueSiguiente >= inicioExistente && tiempoBloqueSiguiente < finExistente) {
                            // ¡Conflicto encontrado! Muestra el mensaje, deshabilita el guardado y detiene la validación.
                            $('#duracion-conflicto-info').html(`<b>Conflicto:</b> El bloque de las ${formatTime12Hour(bloqueSiguiente.tur_horainicio)} ya está ocupado.`);
                            submitButton.prop('disabled', true);
                            return; // Detiene la ejecución para prevenir más validaciones
                        }
                    }
                }
            }
        }
    }
    // --- MODIFICACIÓN TERMINA ---

    // 2. Validar UC duplicada (localmente, no estricto)
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

    // 3. Validación de Docente/Espacio con consulta al servidor (AJAX)
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
    
    // Determina la duración de la clase según el contexto del formulario
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

    // Prepara los datos para la validación vía AJAX
    const datosValidacion = new FormData();
    datosValidacion.append("accion", "validar_clase_en_vivo");
    datosValidacion.append("doc_cedula", $("#modalSeleccionarDocente").val());
    datosValidacion.append("uc_codigo", $("#modalSeleccionarUc").val());
    datosValidacion.append("espacio", $("#modalSeleccionarEspacio").val());
    datosValidacion.append("dia", diaNombre);
    datosValidacion.append("sec_codigo", $("#sec_codigo_hidden").val());
    datosValidacion.append("hora_inicio", bloquesDeLaTablaActual[indiceInicio].tur_horainicio.substring(0, 5));
    datosValidacion.append("hora_fin", bloquesDeLaTablaActual[indiceFin].tur_horafin.substring(0, 5));

    // Realiza la llamada AJAX para una validación completa y centralizada
    $.ajax({
        url: "",
        type: "POST",
        data: datosValidacion,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            if (respuesta.conflicto && Array.isArray(respuesta.mensajes)) {
                // Si el backend reporta conflictos, los muestra en el pop-up de confirmación
                const conflictos = respuesta.mensajes.map(c => c.mensaje);
                let mensajeHtml = "Se encontraron los siguientes conflictos:<ul class='text-start mt-2'>";
                conflictos.forEach(msg => { mensajeHtml += `<li>${msg}</li>`; });
                mensajeHtml += "</ul><br>¿Desea asignar la clase de todas formas?";

                Swal.fire({
                    title: 'Conflictos Detectados',
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
                // Si no hay conflictos, procede a guardar localmente de inmediato
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

    const nuevaClaseData = {
        subgrupo: subgrupoNuevo,
        uc_codigo: $("#modalSeleccionarUc").val(),
        doc_cedula: $("#modalSeleccionarDocente").val(),
        espacio: $("#modalSeleccionarEspacio").val() ? JSON.parse($("#modalSeleccionarEspacio").val()) : null,
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
        }
    });
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
        const datos = new FormData();
        datos.append("accion", accion);
        datos.append("sec_codigo", $("#sec_codigo_hidden").val());

        if (accion === 'modificar') {
            datos.append("cantidadSeccion", $("#cantidadSeccionModificar").val());
        }

        let clasesAEnviar = [];
        for (const claseArray of horarioContenidoGuardado.values()) {
            claseArray.forEach(claseObj => clasesAEnviar.push(claseObj.data));
        }

        clasesAEnviar = clasesAEnviar.filter(item => item && item.hora_inicio && item.hora_fin);

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
    if (secCodigo && secCodigo.length > 1) {
        const segundoDigito = secCodigo.toString().charAt(1);
        if (segundoDigito === '2') turnoSeleccionado = 'tarde';
        else if (segundoDigito === '3') turnoSeleccionado = 'noche';
        else if (['1', '4', '0'].includes(segundoDigito)) turnoSeleccionado = 'mañana';
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
                } else if (respuesta.resultado == "consultar_agrupado") {
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
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                horarioContenidoGuardado.clear();
                const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
                inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);
                muestraMensaje("success", 3000, "Horario Limpiado", "Se han eliminado todas las clases. Presione 'Guardar Cambios' para hacer la acción permanente.");
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
       /*  $("#btnGuardarSeccion").prop("disabled", true); */
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
            if (['1', '4', '0'].includes(segundoDigito)) turnoSeleccionado = 'mañana';
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
                    respuesta.mensaje.forEach(clase => {
                        const inicio = new Date(`1970-01-01T${clase.hora_inicio}`);
                        const fin = new Date(`1970-01-01T${clase.hora_fin}`);
                        const diffMinutes = (fin - inicio) / (1000 * 60);
                        clase.bloques_span = Math.round(diffMinutes / 40) || 1;

                        const dia_key = normalizeDayKey(clase.dia);
                        const key = `${clase.hora_inicio.substring(0, 5)}-${dia_key}`;
                        if (!horarioContenidoGuardado.has(key)) {
                            horarioContenidoGuardado.set(key, []);
                        }
                        horarioContenidoGuardado.get(key).push({
                            data: clase,
                            html: generarCellContent(clase)
                        });
                    });

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

    $('#filtro_turno').on("change", function() {
        inicializarTablaHorario($(this).val(), "#tablaHorario", false);
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
                const prefijo = getPrefijoSeccion(seccion.sec_codigo);
                selectorOrigen.append(`<option value="${seccion.sec_codigo}">${prefijo}${seccion.sec_codigo}</option>`);
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

    $(document).on('click', '#btnAnadirFilaHorario', async function(e) {
        e.preventDefault();

        let horaInicioSugerida = "13:00";
        let horaFinSugerida = "13:40";
        const ultimaFila = $("#tablaHorario tbody tr:last");

        if (ultimaFila.length > 0) {
            const franjaTexto = ultimaFila.find('td:first span').text();
            const partes = franjaTexto.split(' - ');
            if (partes.length === 2) {
                const horaFinUltimoBloque = partes[1].trim();
                const parse12HourTime = (timeStr) => {
                    const [time, modifier] = timeStr.split(' ');
                    let [hours, minutes] = time.split(':').map(Number);
                    if (modifier && modifier.toUpperCase() === 'PM' && hours < 12) hours += 12;
                    if (modifier && modifier.toUpperCase() === 'AM' && hours === 12) hours = 0;
                    const date = new Date();
                    date.setHours(hours, minutes, 0, 0);
                    return date;
                };
                try {
                    const fechaInicio = parse12HourTime(horaFinUltimoBloque);
                    horaInicioSugerida = fechaInicio.toTimeString().substring(0, 5);
                    fechaInicio.setMinutes(fechaInicio.getMinutes() + 40);
                    horaFinSugerida = fechaInicio.toTimeString().substring(0, 5);
                } catch (error) {
                    console.error("Error al parsear hora:", error);
                }
            }
        }

        const validarBloqueHorario = (inicioNuevo, finNuevo) => {
            if (!inicioNuevo || !finNuevo) return `Debes especificar una hora de inicio y fin.`;
            if (inicioNuevo >= finNuevo) return `La hora de inicio debe ser anterior a la hora de fin.`;
            const horaMinima = "07:00";
            const horaMaxima = "23:59";
            if (inicioNuevo < horaMinima || finNuevo > horaMaxima) return `El horario debe estar entre las 7:00 a. m. y las 12:00 a. m.`;
            let haySolapamiento = false;
            $("#tablaHorario tbody tr").each(function() {
                const cellText = $(this).find('td:first span').text();
                const [startText, endText] = cellText.split(' - ');
                if (!startText || !endText) return;

                const to24Hour = timeStr => {
                    const [time, modifier] = timeStr.trim().split(' ');
                    let [hours, minutes] = time.split(':');
                    if (hours === '12') {
                        hours = '00';
                    }
                    if (modifier.toUpperCase() === 'PM') {
                        hours = parseInt(hours, 10) + 12;
                    }
                    return `${String(hours).padStart(2, '0')}:${minutes}`;
                };

                const inicioExistente = to24Hour(startText);
                const finExistente = to24Hour(endText);

                if (inicioExistente && finExistente && (inicioNuevo < finExistente && finNuevo > inicioExistente)) {
                    haySolapamiento = true;
                    return false;
                }
            });
            if (haySolapamiento) return 'El bloque horario se solapa con un bloque existente.';
            return null;
        };

        const {
            value: formValues,
            isConfirmed
        } = await Swal.fire({
            title: 'Añadir Nuevo Bloque Horario',
            html: `
                <p class="text-muted">Introduce la hora de inicio y fin para la nueva fila del horario.</p>
                <div class="form-floating mb-2">
                    <input type="time" id="swal-hora-inicio" class="form-control" value="${horaInicioSugerida}" step="1800">
                    <label for="swal-hora-inicio">Hora de Inicio</label>
                </div>
                <div class="form-floating">
                    <input type="time" id="swal-hora-fin" class="form-control" value="${horaFinSugerida}" step="1800">
                    <label for="swal-hora-fin">Hora de Fin</label>
                </div>`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Añadir Fila',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                const inicioInput = document.getElementById('swal-hora-inicio');
                const finInput = document.getElementById('swal-hora-fin');
                const confirmButton = Swal.getConfirmButton();

                function onInput() {
                    const error = validarBloqueHorario(inicioInput.value, finInput.value);
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
                if (validarBloqueHorario(inicio, fin)) {
                    return false;
                }
                return {
                    inicio,
                    fin
                };
            }
        });

        if (isConfirmed && formValues) {
            const nuevaClaseFantasma = {
                data: {
                    hora_inicio: formValues.inicio + ':00',
                    hora_fin: formValues.fin + ':00',
                    isPlaceholder: true
                }
            };
            const keyFantasma = `placeholder-${formValues.inicio}`;
            horarioContenidoGuardado.set(keyFantasma, [nuevaClaseFantasma]);

            const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
            inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);

            horarioContenidoGuardado.delete(keyFantasma);

            $("#filtro_turno").prop('disabled', true);
            muestraMensaje("success", 2500, "Fila Añadida", "El nuevo bloque horario ha sido agregado.");
        }
    });

    $(document).on('click', '.btn-eliminar-fila-personalizada', function() {
        const franjaInicio = $(this).data('franja-inicio');

        const keysToDelete = [];
        for (const key of horarioContenidoGuardado.keys()) {
            if (key.startsWith(franjaInicio.substring(0, 5))) {
                keysToDelete.push(key);
            }
        }
        keysToDelete.forEach(key => horarioContenidoGuardado.delete(key));

        const turnoActualFiltro = $("#filtro_turno").val() || 'todos';
        inicializarTablaHorario(turnoActualFiltro, "#tablaHorario", false);

        muestraMensaje("info", 2000, "Fila Eliminada", "El bloque horario y sus clases han sido eliminados.");
    });
});