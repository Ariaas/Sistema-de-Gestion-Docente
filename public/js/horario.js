

function muestraMensaje(tipo, tiempo, titulo, mensaje) {
  if (typeof Swal !== 'undefined' && Swal.fire) {
      Swal.fire({
          icon: tipo,
          title: titulo,
          text: mensaje,
          timer: tiempo,
          timerProgressBar: true,
          showConfirmButton: false
      });
  } else {
      console.log(`[${tipo.toUpperCase()}] ${titulo}: ${mensaje}`);
      alert(`${titulo}: ${mensaje}`);
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

function Listar() {
var datos = new FormData();
datos.append("accion", "consultar_agrupado");
enviaAjax(datos);
}

function destruyeDT() {
if ($.fn.DataTable.isDataTable("#tablaListadoHorarios")) {
  $("#tablaListadoHorarios").DataTable().destroy();
}
}

function crearDT() {
if (!$.fn.DataTable.isDataTable("#tablaListadoHorarios")) {
  $("#tablaListadoHorarios").DataTable({
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
      infoEmpty: "No hay registros disponibles",
      infoFiltered: "(filtrado de _MAX_ registos totales)",
      search: "Buscar:",
      paginate: {
        first: "Primero",
        last: "Último",
        next: "Siguiente",
        previous: "Anterior",
      },
    },
    columnDefs: [
          { type: 'natural', targets: 0 },
          { type: 'natural', targets: 1 },
          { type: 'natural', targets: 2 },
          { type: 'natural', targets: 3 }
    ],
    autoWidth: false,
    dom:
      "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
  });

  $("div.dataTables_length select").css({ width: "auto", display: "inline", "margin-top": "10px"});
  $("div.dataTables_filter").css({ "margin-bottom": "50px", "margin-top": "10px" });
  $("div.dataTables_filter label").css({ float: "left" });
  $("div.dataTables_filter input").css({ width: "300px", float: "right", "margin-left": "10px" });
}
}

let currentClickedCell = null;
let horarioContenidoGuardado = new Map();

let allUcs = [];
let allEspacios = [];
let allDocentes = [];
let allSecciones = [];
let allTurnos = [];
let allFases = [];

function fillSelectGenerico(selector, options, valueKey, textKey1, textKey2 = null, defaultOptionText = "Seleccionar", textKey3 = null, textKey4 = null, textKey5 = null) {
  const select = $(selector);
  select.empty().append(`<option value="">${defaultOptionText}...</option>`);
  if (options && Array.isArray(options)) {
      options.forEach(item => {
          let text = item[textKey1];
          if (textKey2 && item[textKey2]) text += ` - ${item[textKey2]}`;
          if (textKey3 && item[textKey3]) text += ` (Tray. ${item[textKey3]}`;
          if (textKey4 && item[textKey4]) text += ` - Año ${item[textKey4]}`;
          if (textKey3) text += `)`;
          if (textKey5 && item[textKey5]) text = `${textKey5} ${text}`;
          select.append(`<option value="${item[valueKey]}">${text}</option>`);
      });
  }
}

function inicializarTablaHorario() {
  const tbody = $("#tablaHorario tbody");
  tbody.empty();
  allTurnos.sort((a, b) => a.tur_horainicio.localeCompare(b.tur_horainicio));
  const dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

  allTurnos.forEach((turno) => {
    const row = $("<tr>");
    const franjaCell = $('<td>');
    franjaCell.html(`${formatTime12Hour(turno.tur_horainicio)} - ${formatTime12Hour(turno.tur_horafin)}`);
    row.append(franjaCell);
    dias.forEach((dia) => {
      const cell = $("<td>")
        .addClass("celda-horario")
        .attr("data-franja-inicio", turno.tur_horainicio)
        .attr("data-dia-nombre", dia);
      
      const horaInicioKey = turno.tur_horainicio.substring(0, 5);
      const horaFinKey = turno.tur_horafin.substring(0, 5);
      const key = `${horaInicioKey}-${horaFinKey}-${dia}`;

      if (horarioContenidoGuardado.has(key)) {
        const { html, data } = horarioContenidoGuardado.get(key);
        cell.html(html);
        cell.data("horario-data", data);
      }
      row.append(cell);
    });
    tbody.append(row);
  });

  const currentModalMode = $("#modal-horario").data("mode"); 
  if (currentModalMode !== "delete-confirm" && currentModalMode !== "view-only") {
      $(".celda-horario")
        .off("click")
        .on("click", function () {
          currentClickedCell = $(this);
          $("#btnGuardarClase").prop("disabled", false);
          $("#modalSeleccionarEspacio, #modalSeleccionarDocente").css("border-color", "");

          const franjaInicio24h = $(this).data("franja-inicio");
          const diaNombre = $(this).data("dia-nombre");
          const turnoCompleto = allTurnos.find((t) => t.tur_horainicio === franjaInicio24h);
          if (!turnoCompleto) {
              muestraMensaje("error", 3000, "Error Interno", "No se pudo encontrar la definición de la franja horaria (turno).");
              return;
          }
          $("#modalFranjaHoraria").val(`${formatTime12Hour(turnoCompleto.tur_horainicio)} - ${formatTime12Hour(turnoCompleto.tur_horafin)}`);
          $("#modalDia").val(diaNombre);
          $("#modalSeleccionarDocente").val("");
          $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente primero</option>').prop("disabled", true);
          fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccionar Espacio");
          $("#modalSeleccionarEspacio").prop("disabled", true).val("");
          $("#btnEliminarEntrada").hide();
          if (currentClickedCell.data("horario-data")) {
            const data = currentClickedCell.data("horario-data");
            $("#modalSeleccionarDocente").val(data.doc_id);
            const seccionPrincipalId = $("#seccion_principal_id").val();
            let traIdParaFiltrar = null;
            if (seccionPrincipalId) {
                const seccionData = allSecciones.find(s => s.sec_id == seccionPrincipalId);
                if (seccionData) traIdParaFiltrar = seccionData.tra_id;
            }
            if (data.doc_id) {
              cargarUcPorDocente(data.doc_id, traIdParaFiltrar, function() {
                  if (!$("#modalSeleccionarUc").prop('disabled')) $("#modalSeleccionarUc").val(data.uc_id);
                  if (allEspacios.length > 0) $("#modalSeleccionarEspacio").prop("disabled", false);
                  $("#modalSeleccionarEspacio").val(data.esp_id);
              });
            }
            $("#btnEliminarEntrada").show();
          }
          $("#modalEntradaHorario").modal("show");
        });
  } else {
      $("#tablaHorario .celda-horario").off("click");
  }
}


function cargarUcPorDocente(docId, traId, callback) {
  const ucSelect = $("#modalSeleccionarUc");
  const espacioSelect = $("#modalSeleccionarEspacio");

  if (!docId) {
      ucSelect.empty().append('<option value="">Seleccione un docente primero</option>').prop("disabled", true);
      espacioSelect.prop("disabled", true).val("");
      if (callback && typeof callback === "function") callback();
      return;
  }

  ucSelect.empty().append('<option value="">Cargando UCs...</option>').prop("disabled", true);
  espacioSelect.prop("disabled", true).val("");

  const datos = new FormData();
  datos.append("accion", "obtener_uc_por_docente");
  datos.append("doc_id", docId);
  if (traId) datos.append("tra_id", traId);
  
  $.ajax({
      url: "", type: "POST", data: datos, contentType: false, processData: false,
      success: function(respuesta) {
          try {
              const data = JSON.parse(respuesta);
              ucSelect.empty();
              if (data.resultado === 'ok' && data.ucs_docente && Array.isArray(data.ucs_docente) && data.ucs_docente.length > 0) {
                  fillSelectGenerico("#modalSeleccionarUc", data.ucs_docente, "uc_id", "uc_codigo", "uc_nombre", "Seleccionar Unidad Curricular");
                  ucSelect.prop("disabled", false);
                  espacioSelect.prop("disabled", false);
              } else {
                  ucSelect.append('<option value="">No hay UCs asignadas</option>').prop("disabled", true);
                  espacioSelect.prop("disabled", true);
              }
              if (callback && typeof callback === "function") callback();
          } catch (e) {
              console.error("Error procesando UCs del docente:", e, respuesta);
              ucSelect.empty().append('<option value="">Error al cargar UCs</option>').prop("disabled", true);
              if (callback && typeof callback === "function") callback();
          }
      },
      error: function() {
          console.error("Error AJAX al obtener UCs del docente.");
          ucSelect.empty().append('<option value="">Error de red</option>').prop("disabled", true);
          if (callback && typeof callback === "function") callback();
      }
  });
}


$(document).ready(function () {
Listar();
cargarSelectsGenerales();

function cargarSelectsGenerales() {
  var datos = new FormData();
  datos.append("accion", "obtener_datos_selects");
  $.ajax({
    url: "", type: "POST", data: datos, contentType: false, processData: false,
    success: function (respuesta) {
      try {
        const data = JSON.parse(respuesta);
        allUcs = data.ucs || [];
        allEspacios = data.espacios || [];
        allDocentes = data.docentes || [];
        allSecciones = data.secciones || [];
        allTurnos = data.turnos || [];
        allFases = data.fases || [];
        
        fillSelectGenerico("#modalSeleccionarDocente", allDocentes, "doc_id", "doc_nombre", "doc_apellido", "Seleccionar Docente");
        
        $("#fase_id").empty().append('<option value="">Seleccione una sección primero...</option>').prop("disabled", true);
        inicializarTablaHorario();
      } catch (e) {
        muestraMensaje("error", 4000, "ERROR", "No se pudieron cargar los datos.");
      }
    },
    error: function () {
      muestraMensaje("error", 4000, "ERROR", "Error al cargar los datos de los selects.");
    },
  });
}

$("#seccion_principal_id").on("change", function() {
    const faseSelect = $("#fase_id");
    const selectedSecId = $(this).val();
    let traIdActual = null;
    if (selectedSecId) {
        const seccionData = allSecciones.find(s => s.sec_id == selectedSecId);
        if (seccionData) traIdActual = seccionData.tra_id;
    }

    faseSelect.empty().append('<option value="">Cargando...</option>').prop("disabled", true);

    if (traIdActual) {
        const fasesFiltradas = allFases.filter(fase => fase.tra_id == traIdActual);
        if (fasesFiltradas.length > 0) {
            faseSelect.empty().append('<option value="">Seleccione una Fase...</option>');
            fasesFiltradas.forEach(item => faseSelect.append(`<option value="${item.fase_id}">Fase ${item.fase_numero}</option>`));
            faseSelect.prop("disabled", false);
        } else {
            faseSelect.empty().append('<option value="">No hay fases para esta sección</option>');
        }
    } else {
        faseSelect.empty().append('<option value="">Seleccione una sección primero...</option>');
    }
    horarioContenidoGuardado.clear(); 
    inicializarTablaHorario(); 
});

$("#modalSeleccionarDocente").on("change", function() {
    const docId = $(this).val();
    const seccionPrincipalId = $("#seccion_principal_id").val();
    const guardarBtn = $("#btnGuardarClase");

    $(this).css("border-color", "");

    let traIdParaFiltrar = null;
    if (seccionPrincipalId) {
        const seccionData = allSecciones.find(s => s.sec_id == seccionPrincipalId);
        if (seccionData) traIdParaFiltrar = seccionData.tra_id;
    }
    cargarUcPorDocente(docId, traIdParaFiltrar, null);

    if (!docId || !currentClickedCell) {
        if ($("#modalSeleccionarEspacio").css('border-color') !== 'rgb(255, 0, 0)') {
            guardarBtn.prop("disabled", false);
        }
        return;
    }
    
    const franjaInicio24h = currentClickedCell.data("franja-inicio");
    const turnoCompleto = allTurnos.find((t) => t.tur_horainicio === franjaInicio24h);
    if (!turnoCompleto) return;

    const datos = new FormData();
    datos.append("accion", "verificar_conflicto_docente");
    datos.append("doc_id", docId);
    datos.append("dia", $("#modalDia").val());
    datos.append("hora_inicio", turnoCompleto.tur_horainicio.substring(0, 5));
    datos.append("sec_id_actual", $("#seccion_principal_id").val());
    datos.append("fase_id_actual", $("#fase_id").val());
    datos.append("sec_id_original", $("#current_editing_sec_id_hidden").val() || $("#seccion_principal_id").val());
    datos.append("fase_id_original", $("#current_editing_fase_id_hidden").val() || $("#fase_id").val());

    $.ajax({
        url: "", type: "POST", data: datos, contentType: false, processData: false,
        success: function(respuesta) {
            try {
                const data = JSON.parse(respuesta);
                if (data.conflicto) {
                    muestraMensaje("error", 8000, "Conflicto de Docente", data.mensaje);
                    $("#modalSeleccionarDocente").css("border-color", "red");
                    guardarBtn.prop("disabled", true);
                } else {
                    if ($("#modalSeleccionarEspacio").css('border-color') !== 'rgb(255, 0, 0)') {
                        guardarBtn.prop("disabled", false);
                    }
                }
            } catch(e) { console.error("Error al procesar respuesta de conflicto de docente", e); }
        }
    });
});

$("#modalSeleccionarEspacio").on("change", function() {
    const esp_id = $(this).val();
    const guardarBtn = $("#btnGuardarClase");
    
    $(this).css("border-color", "");

    if (!esp_id || !currentClickedCell) {
        if ($("#modalSeleccionarDocente").css('border-color') !== 'rgb(255, 0, 0)') {
            guardarBtn.prop("disabled", false);
        }
        return;
    }

    const franjaInicio24h = currentClickedCell.data("franja-inicio");
    const turnoCompleto = allTurnos.find((t) => t.tur_horainicio === franjaInicio24h);
    if (!turnoCompleto) return;
    
    const datos = new FormData();
    datos.append("accion", "verificar_conflicto_espacio");
    datos.append("esp_id", esp_id);
    datos.append("dia", $("#modalDia").val());
    datos.append("hora_inicio", turnoCompleto.tur_horainicio.substring(0, 5));
    datos.append("sec_id_actual", $("#seccion_principal_id").val());
    datos.append("fase_id_actual", $("#fase_id").val());
    datos.append("sec_id_original", $("#current_editing_sec_id_hidden").val() || $("#seccion_principal_id").val());
    datos.append("fase_id_original", $("#current_editing_fase_id_hidden").val() || $("#fase_id").val());
    
    $.ajax({
        url: "", type: "POST", data: datos, contentType: false, processData: false,
        success: function(respuesta) {
            try {
                const data = JSON.parse(respuesta);
                if (data.conflicto) {
                    muestraMensaje("error", 8000, "Conflicto de Espacio", data.mensaje);
                    $("#modalSeleccionarEspacio").css("border-color", "red");
                    guardarBtn.prop("disabled", true);
                } else {
                    if ($("#modalSeleccionarDocente").css('border-color') !== 'rgb(255, 0, 0)') {
                        guardarBtn.prop("disabled", false);
                    }
                }
            } catch(e) { console.error("Error al procesar respuesta de conflicto de espacio", e); }
        }
    });
});

$("#formularioEntradaHorario").on("submit", function (e) {
  e.preventDefault();

  const docente_id = $("#modalSeleccionarDocente").val();
  const uc_id = $("#modalSeleccionarUc").val();
  const espacio_id = $("#modalSeleccionarEspacio").val();
  
  if (!docente_id || !uc_id || !espacio_id) {
    muestraMensaje("error", 4000, "CAMPOS REQUERIDOS", "Debe seleccionar Docente, Unidad Curricular y Espacio.");
    return;
  }

  const franjaInicio24h = currentClickedCell.data("franja-inicio"); 
  const diaNombre = currentClickedCell.data("dia-nombre");
  const turnoCompleto = allTurnos.find((t) => t.tur_horainicio === franjaInicio24h);
  
  if (!turnoCompleto) { 
      muestraMensaje("error", 4000, "Error Interno", "No se pudo determinar la franja horaria.");
      return;
  }

  const horaInicioKey = turnoCompleto.tur_horainicio.substring(0, 5);
  const horaFinKey = turnoCompleto.tur_horafin.substring(0, 5);
  const keyCeldaActual = `${horaInicioKey}-${horaFinKey}-${diaNombre}`;
  
  const horarioData = {
    tur_id: turnoCompleto.tur_id,
    esp_id: espacio_id, 
    uc_id: uc_id, 
    sec_id: $("#seccion_principal_id").val(),
    doc_id: docente_id, 
    hora_inicio: horaInicioKey,
    hora_fin: horaFinKey,
    dia: diaNombre
  };
  currentClickedCell.data("horario-data", horarioData);

  const ucData = allUcs.find(uc => uc.uc_id == uc_id);
  const espData = allEspacios.find(esp => esp.esp_id == espacio_id);
  const docData = allDocentes.find(doc => doc.doc_id == docente_id);

  const uc_nombre_display = ucData ? ucData.uc_nombre : 'N/A';
  const esp_codigo_display = espData ? espData.esp_codigo : 'N/A';
  const doc_nombre_completo_display = docData ? `${docData.doc_nombre} ${docData.doc_apellido}` : 'N/A';

  const cellContent = `<p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_nombre_display}</strong></p><small style="font-size: 0.8em;">${esp_codigo_display}</small><br><small style="font-size: 0.8em;">${doc_nombre_completo_display}</small>`;
  currentClickedCell.html(cellContent);

  horarioContenidoGuardado.set(keyCeldaActual, { html: cellContent, data: horarioData });
  $("#modalEntradaHorario").modal("hide");
});

$("#btnEliminarEntrada").on("click", function () {
  if (currentClickedCell) {
    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre");
    const turnoCompleto = allTurnos.find(t => t.tur_horainicio === franjaInicio);

    if (turnoCompleto) {
      const horaInicioKey = turnoCompleto.tur_horainicio.substring(0, 5);
      const horaFinKey = turnoCompleto.tur_horafin.substring(0, 5);
      const key = `${horaInicioKey}-${horaFinKey}-${diaNombre}`;
      horarioContenidoGuardado.delete(key);
    }
    
    currentClickedCell.empty();
    currentClickedCell.removeData("horario-data");
    $("#modalEntradaHorario").modal("hide");
  }
});

async function procesarGuardadoHorario(accionActual, seccionPrincipalId, faseGlobalId, botonProceso) {
    let clasesAEnviar = [];
    for (const value of horarioContenidoGuardado.values()) {
        clasesAEnviar.push(value.data); 
    }
    if (accionActual === "registrar" || accionActual === "modificar_grupo") {
        const datosGrupo = new FormData();
        datosGrupo.append("accion", "modificar_grupo");
        datosGrupo.append("sec_id_original", $("#current_editing_sec_id_hidden").val() || seccionPrincipalId);
        datosGrupo.append("fase_id_original", $("#current_editing_fase_id_hidden").val() || faseGlobalId);
        datosGrupo.append("nueva_seccion_id", seccionPrincipalId);
        datosGrupo.append("nueva_fase_id", faseGlobalId);
        datosGrupo.append("items_horario", JSON.stringify(clasesAEnviar)); 
        enviaAjax(datosGrupo); 
    }
}

$("#proceso").on("click", async function () {
  const botonProceso = $(this);
  const accionBoton = botonProceso.data("action-type");

  if (accionBoton === "confirm-delete-group") {
      const sec_id = botonProceso.data("delete-sec-id");
      const fase_id = botonProceso.data("delete-fase-id");
      const seccionNombre = botonProceso.data("delete-seccion-nombre");
      $("#modal-horario").modal("hide");
      Swal.fire({
          title: `¿Eliminar todo el horario para ${seccionNombre} - Fase ${$("#fase_id option:selected").text().split('(')[0].trim()}?`,
          text: "Esta acción no se puede deshacer y eliminará todas las clases asociadas.",
          icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
          confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
      }).then((result) => {
          if (result.isConfirmed) {
              var datosEliminar = new FormData();
              datosEliminar.append("accion", "eliminar_por_seccion_fase");
              datosEliminar.append("sec_id", sec_id);
              datosEliminar.append("fase_id", fase_id);
              enviaAjax(datosEliminar); 
          } else {
              muestraMensaje("info", 2000, "CANCELADO", "La eliminación ha sido cancelada.");
          }
      });
      return;
  }

  const accionActual = $("#accion").val();
  if (accionActual === "registrar" || accionActual === "modificar_grupo") {
      if (botonProceso.text() === "Procesando...") return;
      botonProceso.prop("disabled", true).text("Procesando...");

      const seccionPrincipalId = $("#seccion_principal_id").val();
      const faseGlobalId = $("#fase_id").val();
      
      if (!seccionPrincipalId || !faseGlobalId) {
          muestraMensaje("error", 4000, "ERROR", "Debe seleccionar la Sección y la Fase del horario.");
          botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
          return;
      }
      if (horarioContenidoGuardado.size === 0 && accionActual === "registrar") {
          muestraMensaje("error", 4000, "ERROR", "Debe añadir al menos una clase al horario para registrar.");
          botonProceso.prop("disabled", false).text("REGISTRAR");
          return;
      }
      
      await procesarGuardadoHorario(accionActual, seccionPrincipalId, faseGlobalId, botonProceso);
  }
});

$("#registrar").on("click", function () {
  limpiaParaNuevoRegistro();

  if (allSecciones.length === 0) {
      muestraMensaje("warning", 5000, "Datos no cargados", "Las secciones aún no se han cargado, por favor espere un momento.");
      return;
  }

  const maxYear = Math.max(...allSecciones.map(s => parseInt(s.ani_anio, 10)));
  const seccionesAnioActual = allSecciones.filter(s => parseInt(s.ani_anio, 10) === maxYear);
  const seccionSelect = $("#seccion_principal_id");
  fillSelectGenerico(seccionSelect, seccionesAnioActual, "sec_id", "sec_codigo", "sec_nombre", "Seleccionar Sección...", "tra_numero", "ani_anio");

  $("#modalHorarioGlobalTitle").text("Registrar Nuevo Horario");
  $("#accion").val("registrar");
  $("#proceso").text("REGISTRAR").data("action-type", "registrar").removeClass("btn-danger btn-warning").addClass("btn-primary").prop("disabled", false);
  
  $("#seccion_principal_id").prop("disabled", false);
  $("#fase_id").prop("disabled", true); 
  
  $("#controlesTablaHorario, #contenedorTablaHorario").show();
  $("#modal-horario").data("mode", "registrar"); 
  $("#modal-horario").modal("show");
});

function generarCellContentParaHorarioPrincipal(clase) {
    const ucData = allUcs.find(uc => uc.uc_id == clase.uc_id);
    const espData = allEspacios.find(esp => esp.esp_id == clase.esp_id);
    const docData = clase.doc_id ? allDocentes.find(doc => doc.doc_id == clase.doc_id) : null;
    const uc_display = ucData ? ucData.uc_nombre : 'N/A';
    const esp_display = espData ? espData.esp_codigo : 'N/A';
    const doc_display = docData ? `${docData.doc_nombre} ${docData.doc_apellido}` : 'N/A';
    return `<p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_display}</strong></p><small style="font-size: 0.8em;">${esp_display}</small><br><small style="font-size: 0.8em;">${doc_display}</small>`;
}

$(document).on('click', '.modificar-grupo-horario', function() {
    const sec_id_original = $(this).data('sec-id');
    const fase_id_original = $(this).data('fase-id');
    const $row = $(this).closest('tr');
    const seccionNombreCompleto = `${$row.find('td:nth-child(1)').text()} - ${$row.find('td:nth-child(2)').text()} ${$row.find('td:nth-child(3)').text()}`;
    const faseNum = $row.find('td:nth-child(4)').text();

    limpiaParaNuevoRegistro();
    
    fillSelectGenerico("#seccion_principal_id", allSecciones, "sec_id", "sec_codigo", "sec_nombre", "Seleccionar Sección...", "tra_numero", "ani_anio");

    $("#modalHorarioGlobalTitle").text(`Modificar Horario: ${seccionNombreCompleto} - Fase ${faseNum}`);
    $("#accion").val("modificar_grupo");
    $("#proceso").text("GUARDAR").data("action-type", "modificar_grupo").removeClass("btn-danger btn-warning").addClass("btn-primary").prop("disabled", false);

    $("#current_editing_sec_id_hidden").val(sec_id_original);
    $("#current_editing_fase_id_hidden").val(fase_id_original);
    $("#seccion_principal_id").val(sec_id_original);
    
    $("#seccion_principal_id").trigger("change");
    
    setTimeout(() => {
      $("#fase_id").val(fase_id_original);
    }, 500);

    $("#seccion_principal_id, #fase_id").prop("disabled", false);
    $("#controlesTablaHorario, #contenedorTablaHorario").show();
    $("#modal-horario").data("mode", "modificar"); 

    var datosConsulta = new FormData();
    datosConsulta.append("accion", "consultar_detalles_para_grupo");
    datosConsulta.append("sec_id", sec_id_original);
    datosConsulta.append("fase_id", fase_id_original);

    $.ajax({
        url: "", type: "POST", data: datosConsulta, contentType: false, processData: false,
        success: function(respuesta) {
            try {
                const lee = JSON.parse(respuesta);
                if (lee.resultado === 'ok' && lee.mensaje) {
                    horarioContenidoGuardado.clear();
                    if (Array.isArray(lee.mensaje)) {
                        lee.mensaje.forEach(clase => {
                            const key = `${clase.hora_inicio}-${clase.hora_fin}-${clase.dia}`;
                            const cellContent = generarCellContentParaHorarioPrincipal(clase);
                            horarioContenidoGuardado.set(key, { html: cellContent, data: clase });
                        });
                    }
                    inicializarTablaHorario(); 
                    $("#modal-horario").modal("show");
                } else { muestraMensaje("error", 5000, "Error al cargar", lee.mensaje || "No se pudieron cargar los detalles."); }
            } catch (e) { muestraMensaje("error", 5000, "Error de respuesta", "Respuesta inválida del servidor."); }
        },
        error: function() { muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo contactar al servidor."); }
    });
});

$(document).on('click', '.eliminar-grupo-horario, .ver-grupo-horario', function() {
    const isViewOnly = $(this).hasClass('ver-grupo-horario');
    const sec_id = $(this).data('sec-id');
    const fase_id = $(this).data('fase-id');
    const $row = $(this).closest('tr');
    const seccionNombre = `${$row.find('td:nth-child(1)').text()} - ${$row.find('td:nth-child(2)').text()} ${$row.find('td:nth-child(3)').text()}`;
    const faseNum = $row.find('td:nth-child(4)').text();
    
    fillSelectGenerico("#seccion_principal_id", allSecciones, "sec_id", "sec_codigo", "sec_nombre", "Seleccionar Sección...", "tra_numero", "ani_anio");

    $("#modal-horario").data("mode", isViewOnly ? "view-only" : "delete-confirm"); 

    var datosConsulta = new FormData();
    datosConsulta.append("accion", "consultar_detalles_para_grupo");
    datosConsulta.append("sec_id", sec_id);
    datosConsulta.append("fase_id", fase_id);

    $.ajax({
        url: "", type: "POST", data: datosConsulta, contentType: false, processData: false,
        success: function(respuesta) {
            try {
                const lee = JSON.parse(respuesta);
                if (lee.resultado === 'ok' && lee.mensaje) {
                    limpiaParaNuevoRegistro();

                    $("#seccion_principal_id").val(sec_id).prop("disabled", true);
                    $("#seccion_principal_id").trigger("change");
                    
                    setTimeout(() => {
                      $("#fase_id").val(fase_id).prop("disabled", true);
                    }, 500);

                    horarioContenidoGuardado.clear();
                    if (Array.isArray(lee.mensaje)) {
                        lee.mensaje.forEach(clase => {
                            const key = `${clase.hora_inicio}-${clase.hora_fin}-${clase.dia}`;
                            horarioContenidoGuardado.set(key, { html: generarCellContentParaHorarioPrincipal(clase), data: clase });
                        });
                    }
                    inicializarTablaHorario(); 
                    $("#tablaHorario .celda-horario").off("click");

                    if (isViewOnly) {
                        $("#modalHorarioGlobalTitle").text(`Ver Horario: ${seccionNombre} - Fase ${faseNum}`);
                        $("#controlesTablaHorario, #proceso").hide();
                    } else {
                        $("#modalHorarioGlobalTitle").text(`Confirmar Eliminación: ${seccionNombre} - Fase ${faseNum}`);
                        $("#controlesTablaHorario").hide();
                        $("#proceso").text("CONFIRMAR ELIMINACIÓN")
                                 .data("action-type", "confirm-delete-group")
                                 .data("delete-sec-id", sec_id).data("delete-fase-id", fase_id)
                                 .data("delete-seccion-nombre", seccionNombre)
                                 .removeClass("btn-primary").addClass("btn-danger").prop("disabled", false).show();
                    }
                    $("#modal-horario").modal("show");
                } else { muestraMensaje("error", 5000, "Error", lee.mensaje || "No se pudieron cargar detalles."); }
            } catch (e) { muestraMensaje("error", 5000, "Error", "Respuesta inválida del servidor."); }
        },
        error: function() { muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo contactar al servidor."); }
    });
});

$('#modal-horario').on('hidden.bs.modal', function () {
    limpiaParaNuevoRegistro(); 
    $("#proceso").text("REGISTRAR").data("action-type", "").removeClass("btn-danger btn-warning").addClass("btn-primary").prop("disabled", false).show();
    $(this).removeData("mode");
    if (window.history.replaceState) window.history.replaceState(null, null, window.location.href);
});

}); 

function enviaAjax(datos) {
const accionEnviada = datos.get("accion");
let botonProceso = $("#proceso");

if (accionEnviada === "modificar_grupo") {
  if (botonProceso.text() !== "Procesando...") { 
      botonProceso.prop("disabled", true).text("Procesando...");
  }
}

$.ajax({
  async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false, timeout: 25000,
  success: function (respuesta) {
    try {
      var lee = JSON.parse(respuesta);
      let textoBotonOriginal = $("#accion").val() === "modificar_grupo" ? "GUARDAR CAMBIOS" : "REGISTRAR";
      
      if(accionEnviada === 'modificar_grupo') {
          botonProceso.prop("disabled", false).text(textoBotonOriginal);
      }

      if (lee.resultado == "consultar_agrupado") {
        destruyeDT();
        $("#resultadoconsulta").empty();
        if (lee.mensaje && lee.mensaje.length > 0) {
          $.each(lee.mensaje, function (index, item) {
            // ===== INICIO DE MODIFICACIÓN =====
            $("#resultadoconsulta").append(`
              <tr>
                <td>${item.sec_codigo || 'N/A'} - ${item.sec_nombre || ''}</td>
                <td>Trayecto ${item.tra_numero || 'N/A'}</td>
                <td>${item.ani_anio || 'N/A'}</td>
                <td>${item.fase_numero || 'N/A'}</td>
                <td>
                  <button class="btn btn-info btn-sm ver-grupo-horario" data-sec-id="${item.sec_id}" data-fase-id="${item.fase_id}" title="Ver Horario"><i class="bi bi-eye-fill"></i> Ver</button>
                  <button class="btn btn-warning btn-sm modificar-grupo-horario" data-sec-id="${item.sec_id}" data-fase-id="${item.fase_id}" title="Modificar Horario"><i class="bi bi-pencil-square"></i> Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar-grupo-horario" data-sec-id="${item.sec_id}" data-fase-id="${item.fase_id}" title="Eliminar Horario"><i class="bi bi-trash-fill"></i> Eliminar</button>
                </td>
              </tr>`);
            // ===== FIN DE MODIFICACIÓN =====
          });
        }
        crearDT();
      } else if (lee.resultado == "modificar_grupo_ok" || lee.resultado == "eliminar_por_seccion_fase_ok") {
          muestraMensaje("success", 4000, lee.resultado.includes("modificar") ? "ÉXITO" : "ELIMINACIÓN EXITOSA", lee.mensaje);
          $("#modal-horario").modal("hide"); 
          Listar(); 
      } else if (lee.resultado == "error") {
        muestraMensaje("error", 10000, "ERROR DE OPERACIÓN", lee.mensaje);
      }
    } catch (e) {
      console.error("Error en JSON o success AJAX:", e, "Respuesta:", respuesta);
      muestraMensaje("error", 10000, "ERROR DE RESPUESTA", "La respuesta del servidor no pudo ser procesada.");
      if(accionEnviada === 'modificar_grupo') {
          botonProceso.prop("disabled", false).text("Reintentar");
      }
    }
  },
  error: function (request, status, err) {
    console.error("Error AJAX:", status, err, request.responseText);
    muestraMensaje("error", 5000, status == "timeout" ? "SERVIDOR OCUPADO" : "ERROR DE CONEXIÓN", `No se pudo comunicar con el servidor.`);
    if(accionEnviada === 'modificar_grupo') {
      botonProceso.prop("disabled", false).text("Reintentar");
    }
  },
});
}

function limpiaParaNuevoRegistro() {
$("#form-horario")[0].reset(); 
$("#accion, #hor_id, #current_editing_sec_id_hidden, #current_editing_fase_id_hidden").val("");
// He modificado también la función que rellena los selects para que muestre el código y el nombre.
$("#seccion_principal_id").prop('disabled', false).empty().append('<option value="">Seleccione una opción...</option>'); 
$("#fase_id").prop('disabled', true).empty().append('<option value="">Seleccione una sección primero...</option>');
$("#controlesTablaHorario, #contenedorTablaHorario").show();
horarioContenidoGuardado.clear(); 
inicializarTablaHorario(); 
}