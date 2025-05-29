
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
            { type: 'natural', targets: 2 }
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
let franjasHorarias = [
  { inicio: "08:00", fin: "08:40" },
  { inicio: "08:40", fin: "09:20" },
  { inicio: "09:20", fin: "10:00" },
  { inicio: "10:00", fin: "10:40" },
  { inicio: "10:40", fin: "11:20" },
  { inicio: "11:20", fin: "12:00" }
];
let horarioContenidoGuardado = new Map();

let allUcs = [];
let allEspacios = [];
let allDocentes = [];
let allSecciones = [];

function fillSelectGenerico(selector, options, valueKey, textKey1, textKey2 = null, defaultOptionText = "Seleccionar", textKey3 = null, textKey4 = null) {
    const select = $(selector);
    select.empty().append(`<option value="">${defaultOptionText}...</option>`);
    if (options && Array.isArray(options)) {
        options.forEach(item => {
            let text = item[textKey1];
            if (textKey2 && item[textKey2]) {
                text += ` - ${item[textKey2]}`;
            }
            if (textKey3 && item[textKey3]) {
                 text += ` (Tray. ${item[textKey3]}`;
                 if (textKey4 && item[textKey4]) {
                     text += ` - Año ${item[textKey4]}`;
                 }
                 text += `)`;
            }
            select.append(`<option value="${item[valueKey]}">${text}</option>`);
        });
    }
}


function validarSolapamientoFranja(inicioNuevo, finNuevo, indexAExcluir = -1) {
    for (let i = 0; i < franjasHorarias.length; i++) {
        if (i === indexAExcluir) continue; 

        const franjaExistente = franjasHorarias[i];
        const inicioExistente = franjaExistente.inicio;
        const finExistente = franjaExistente.fin;

        if (inicioNuevo < finExistente && inicioExistente < finNuevo) {
            return true; 
        }
    }
    return false; 
}


function abrirModalEditarFranja(index) {
    const franjaAEditar = franjasHorarias[index];
    Swal.fire({
        title: "Editar Franja Horaria",
        html: `
            <div class="mb-3">
              <label for="editarHoraInicio" class="form-label">Hora Inicio (formato 24h)</label>
              <input type="time" class="form-control" id="editarHoraInicio" value="${franjaAEditar.inicio}" required>
            </div>
            <div class="mb-3">
              <label for="editarHoraFin" class="form-label">Hora Fin (formato 24h)</label>
              <input type="time" class="form-control" id="editarHoraFin" value="${franjaAEditar.fin}" required>
            </div>
            <small>La visualización será en AM/PM. Las clases asignadas a la hora anterior podrían necesitar ser reasignadas si la hora de inicio cambia.</small>
        `,
        showCancelButton: true,
        confirmButtonText: "Guardar Cambios",
        cancelButtonText: "Cancelar",
        preConfirm: () => {
            const nuevoInicio = $('#editarHoraInicio').val();
            const nuevoFin = $('#editarHoraFin').val();
            if (!nuevoInicio || !nuevoFin) { Swal.showValidationMessage('Debe completar ambos campos de hora.'); return false; }
            if (nuevoInicio >= nuevoFin) { Swal.showValidationMessage('La hora fin debe ser posterior a la hora inicio.'); return false; }

            if (validarSolapamientoFranja(nuevoInicio, nuevoFin, index)) {
                Swal.showValidationMessage('La franja horaria modificada se solapa con otra existente o es idéntica a otra.');
                return false;
            }
            return { inicio: nuevoInicio, fin: nuevoFin };
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const datosAntiguos = franjasHorarias[index];
            const datosNuevos = result.value;

            if(datosAntiguos.inicio !== datosNuevos.inicio) {
                const dias = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];
                let clasesAfectadas = false;
                dias.forEach(dia => {
                    const oldKey = `${datosAntiguos.inicio}-${dia}`;
                    if (horarioContenidoGuardado.has(oldKey)) {
                        horarioContenidoGuardado.delete(oldKey);
                        clasesAfectadas = true;
                    }
                });
                if (clasesAfectadas) {
                     muestraMensaje("warning", 7000, "Advertencia por Cambio de Hora", "La hora de inicio de la franja cambió. Las clases que estaban asignadas a la hora original en esta franja han sido desvinculadas y deberán ser reasignadas manualmente si es necesario.");
                }
            }

            franjasHorarias[index] = { inicio: datosNuevos.inicio, fin: datosNuevos.fin };
            franjasHorarias.sort((a, b) => a.inicio.localeCompare(b.inicio));
            inicializarTablaHorario();
        }
    });
}


function inicializarTablaHorario() {
    const tbody = $("#tablaHorario tbody");
    tbody.empty();
    franjasHorarias.sort((a, b) => a.inicio.localeCompare(b.inicio));
    const dias = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    franjasHorarias.forEach((franja, franjaIndex) => {
      const row = $("<tr>");
      
      const franjaCell = $('<td>');
      franjaCell.html(`${formatTime12Hour(franja.inicio)} - ${formatTime12Hour(franja.fin)} `);
      
      const currentMode = $("#modal-horario").data("mode");
      if (currentMode !== "delete-confirm" && currentMode !== "view-only") {
        const editButton = $('<button type="button" class="btn btn-sm btn-outline-primary ms-2 py-0 px-1" title="Editar esta franja horaria"><i class="bi bi-pencil-fill"></i> Editar</button>');
        editButton.on('click', function(e) {
            e.stopPropagation(); 
            abrirModalEditarFranja(franjaIndex);
        });
        franjaCell.append(editButton);
      }
      row.append(franjaCell);

      dias.forEach((dia) => {
        const cell = $("<td>")
          .addClass("celda-horario")
          .attr("data-franja-inicio", franja.inicio)
          .attr("data-dia-nombre", dia);

        const key = `${franja.inicio}-${dia}`;
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

            const franjaInicio24h = $(this).data("franja-inicio");
            const diaNombre = $(this).data("dia-nombre");
            const franjaCompleta = franjasHorarias.find((f) => f.inicio === franjaInicio24h);

            if (!franjaCompleta) {
                console.error("Error: Franja horaria no encontrada para el inicio:", franjaInicio24h);
                muestraMensaje("error", 3000, "Error Interno", "No se pudo encontrar la definición de la franja horaria seleccionada.");
                return;
            }

            $("#modalFranjaHoraria").val(`${formatTime12Hour(franjaCompleta.inicio)} - ${formatTime12Hour(franjaCompleta.fin)}`);
            $("#modalDia").val(diaNombre);

            $("#modalSeleccionarDocente").val("");
            $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente primero</option>').prop("disabled", true);
            fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccione un docente primero");
            $("#modalSeleccionarEspacio").prop("disabled", true).val("");

            $("#btnEliminarEntrada").hide();

            if (currentClickedCell.data("horario-data")) {
              const data = currentClickedCell.data("horario-data");
              $("#modalSeleccionarDocente").val(data.doc_id);

              const seccionPrincipalId = $("#seccion_principal_id").val();
              let traIdParaFiltrar = null;
              if (seccionPrincipalId) {
                  const seccionData = allSecciones.find(s => s.sec_id == seccionPrincipalId);
                  if (seccionData) {
                      traIdParaFiltrar = seccionData.tra_id;
                  }
              }

              if (data.doc_id) {
                cargarUcPorDocente(data.doc_id, traIdParaFiltrar, function() {
                    if (!$("#modalSeleccionarUc").prop('disabled')) {
                        $("#modalSeleccionarUc").val(data.uc_id);
                    }
                    if (allEspacios.length > 0) {
                       $("#modalSeleccionarEspacio").prop("disabled", false);
                    }
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
        fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccione un docente primero");
        espacioSelect.prop("disabled", true).val("");
        if (callback && typeof callback === "function") {
            callback();
        }
        return;
    }

    ucSelect.empty().append('<option value="">Cargando UCs...</option>').prop("disabled", true);
    fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Cargando...");
    espacioSelect.prop("disabled", true).val("");


    const datos = new FormData();
    datos.append("accion", "obtener_uc_por_docente");
    datos.append("doc_id", docId);
    if (traId) {
        datos.append("tra_id", traId);
    }

    $.ajax({
        url: "",
        type: "POST",
        data: datos,
        contentType: false,
        processData: false,
        success: function(respuesta) {
            try {
                const data = JSON.parse(respuesta);
                ucSelect.empty();
                if (data.resultado === 'ok' && data.ucs_docente && Array.isArray(data.ucs_docente) && data.ucs_docente.length > 0) {
                    fillSelectGenerico("#modalSeleccionarUc", data.ucs_docente, "uc_id", "uc_codigo", "uc_nombre", "Seleccionar Unidad Curricular");
                    ucSelect.prop("disabled", false);

                    fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccionar Espacio");
                    if (allEspacios.length > 0) {
                        espacioSelect.prop("disabled", false);
                    } else {
                         espacioSelect.prop("disabled", true).empty().append('<option value="">No hay espacios disponibles</option>');
                    }

                } else {
                    ucSelect.append('<option value="">No hay UCs asignadas para este docente/trayecto</option>').prop("disabled", true);
                    fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccione docente con UCs");
                    espacioSelect.prop("disabled", true).val("");
                }
                if (callback && typeof callback === "function") {
                    callback();
                }
            } catch (e) {
                console.error("Error procesando UCs del docente:", e, respuesta);
                ucSelect.empty().append('<option value="">Error al cargar UCs</option>').prop("disabled", true);
                fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Error");
                espacioSelect.prop("disabled", true).val("");
                if (callback && typeof callback === "function") { callback(); }
            }
        },
        error: function() {
            console.error("Error AJAX al obtener UCs del docente.");
            ucSelect.empty().append('<option value="">Error de red</option>').prop("disabled", true);
            fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Error");
            espacioSelect.prop("disabled", true).val("");
            if (callback && typeof callback === "function") { callback(); }
        }
    });
}


$(document).ready(function () {
  Listar();
  cargarSelectsGenerales();

  function cargarSelectsGenerales() {
    const datos = new FormData();
    datos.append("accion", "obtener_datos_selects");

    $.ajax({
      url: "",
      type: "POST",
      data: datos,
      contentType: false,
      processData: false,
      success: function (respuesta) {
        try {
          const data = JSON.parse(respuesta);
          allUcs = data.ucs || [];
          allEspacios = data.espacios || [];
          allDocentes = data.docentes || [];
          allSecciones = data.secciones || [];

          fillSelectGenerico("#seccion_principal_id", allSecciones, "sec_id", "sec_codigo", null, "Seleccionar Sección...", "tra_numero", "tra_anio");
          fillSelectGenerico("#modalSeleccionarDocente", allDocentes, "doc_id", "doc_nombre", "doc_apellido", "Seleccionar Docente");
          inicializarTablaHorario();

        } catch (e) {
          console.error("Error al procesar los datos para los selects:", e, respuesta);
          muestraMensaje("error", 4000, "ERROR", "No se pudieron cargar los datos para los select. Respuesta: " + respuesta);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error AJAX al cargar selects:", textStatus, errorThrown, jqXHR.responseText);
        muestraMensaje("error", 4000, "ERROR", "Error al cargar los datos de los selects. Consulte la consola.");
      },
    });
  }

  $("#seccion_principal_id").on("change", function() {
      const selectedSecId = $(this).val();
      let traIdActual = null;
      if (selectedSecId) {
          const seccionData = allSecciones.find(s => s.sec_id == selectedSecId);
          if (seccionData) {
              traIdActual = seccionData.tra_id;
          }
      }
      horarioContenidoGuardado.clear(); 
      inicializarTablaHorario(); 

      const docIdModalEntrada = $("#modalSeleccionarDocente").val();
      if (docIdModalEntrada) {
          cargarUcPorDocente(docIdModalEntrada, traIdActual, null);
      } else {
           $("#modalSeleccionarUc").empty().append('<option value="">Seleccione un docente primero</option>').prop("disabled", true);
           fillSelectGenerico("#modalSeleccionarEspacio", allEspacios, "esp_id", "esp_codigo", "esp_tipo", "Seleccione un docente primero");
           $("#modalSeleccionarEspacio").prop("disabled", true).val("");
      }
  });


  $("#modalSeleccionarDocente").on("change", function() {
      const docId = $(this).val();
      const seccionPrincipalId = $("#seccion_principal_id").val();
      let traIdParaFiltrar = null;
      if (seccionPrincipalId) {
          const seccionData = allSecciones.find(s => s.sec_id == seccionPrincipalId);
          if (seccionData) {
              traIdParaFiltrar = seccionData.tra_id;
          }
      }
      cargarUcPorDocente(docId, traIdParaFiltrar, null);
  });


  $("#btnAnadirFranja").on("click", function () {
    Swal.fire({
      title: "Nueva Franja Horaria",
      html: `
        <div class="mb-3">
          <label for="nuevaHoraInicio" class="form-label">Hora Inicio (formato 24h)</label>
          <input type="time" class="form-control" id="nuevaHoraInicio" required>
        </div>
        <div class="mb-3">
          <label for="nuevaHoraFin" class="form-label">Hora Fin (formato 24h)</label>
          <input type="time" class="form-control" id="nuevaHoraFin" required>
        </div>
        <small>La visualización será en AM/PM.</small>
      `,
      showCancelButton: true,
      confirmButtonText: "Añadir",
      cancelButtonText: "Cancelar",
      preConfirm: () => {
        const inicio = $('#nuevaHoraInicio').val(); 
        const fin = $('#nuevaHoraFin').val();     
        if (!inicio || !fin) { Swal.showValidationMessage('Debe completar ambos campos de hora.'); return false; }
        if (inicio >= fin) { Swal.showValidationMessage('La hora fin debe ser posterior a la hora inicio.'); return false; }
        
        if (validarSolapamientoFranja(inicio, fin)) {
            Swal.showValidationMessage('Esta franja horaria se solapa con una existente o es idéntica a una ya registrada.');
            return false;
        }
        return { inicio, fin }; 
      },
    }).then((result) => {
      if (result.isConfirmed) {
        franjasHorarias.push({ inicio: result.value.inicio, fin: result.value.fin });
        franjasHorarias.sort((a, b) => a.inicio.localeCompare(b.inicio)); 
        inicializarTablaHorario(); 
      }
    });
  });

  $("#formularioEntradaHorario").on("submit", function (e) {
    e.preventDefault();

    const docente_id = $("#modalSeleccionarDocente").val();
    const uc_id = $("#modalSeleccionarUc").val();
    const espacio_id = $("#modalSeleccionarEspacio").val();
    const seccion_global_id = $("#seccion_principal_id").val();


    if (!docente_id) {
      muestraMensaje("error", 4000, "DOCENTE REQUERIDO", "Debe seleccionar un Docente.");
      return;
    }
    if (!uc_id ) {
      muestraMensaje("error", 4000, "UC REQUERIDA", "Debe seleccionar una Unidad Curricular.");
      return;
    }
    if (!espacio_id ) {
      muestraMensaje("error", 4000, "ESPACIO REQUERIDO", "Debe seleccionar un Espacio.");
      return;
    }

    const franjaInicio24h = currentClickedCell.data("franja-inicio"); 
    const diaNombre = currentClickedCell.data("dia-nombre");
    const franjaCompleta = franjasHorarias.find((f) => f.inicio === franjaInicio24h);

    if (!franjaCompleta) { 
        console.error("Error: No se encontró franja completa para el inicio:", franjaInicio24h, "en la celda clicada.");
        muestraMensaje("error", 4000, "Error Interno", "No se pudo determinar la franja horaria de la celda.");
        return;
    }

    const horarioData = {
      esp_id: espacio_id,
      uc_id: uc_id,
      sec_id: seccion_global_id,
      doc_id: docente_id,
      hora_inicio: franjaCompleta.inicio, 
      hora_fin: franjaCompleta.fin,     
      dia: diaNombre
    };
    currentClickedCell.data("horario-data", horarioData);

    const ucData = allUcs.find(uc => uc.uc_id == uc_id);
    const espData = allEspacios.find(esp => esp.esp_id == espacio_id);
    const docData = allDocentes.find(doc => doc.doc_id == docente_id);

    const uc_nombre_display = ucData ? ucData.uc_nombre : 'N/A';
    const esp_codigo_display = espData ? espData.esp_codigo : 'N/A';
    const doc_nombre_completo_display = docData ? `${docData.doc_nombre} ${docData.doc_apellido}` : 'N/A';


    const cellContent = `
      <p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_nombre_display}</strong></p>
      <small style="font-size: 0.8em;">${esp_codigo_display}</small><br>
      <small style="font-size: 0.8em;">${doc_nombre_completo_display}</small>
      `;
    currentClickedCell.html(cellContent);

    const key = `${franjaCompleta.inicio}-${diaNombre}`; 
    horarioContenidoGuardado.set(key, { html: cellContent, data: horarioData });

    $("#modalEntradaHorario").modal("hide");
  });

  $("#btnEliminarEntrada").on("click", function () {
    if (currentClickedCell) {
      currentClickedCell.empty();
      currentClickedCell.removeData("horario-data");
      const key = `${currentClickedCell.data("franja-inicio")}-${currentClickedCell.data("dia-nombre")}`;
      horarioContenidoGuardado.delete(key);
      $("#modalEntradaHorario").modal("hide");
    }
  });

  async function procesarGuardadoHorario(accionActual, seccionPrincipalId, faseGlobal, botonProceso) {
      let clasesAEnviar = [];
      for (const [key, value] of horarioContenidoGuardado.entries()) {
          let claseData = value.data; 
          clasesAEnviar.push(claseData);
      }

      if (accionActual === "registrar") {
     
          let erroresEncontrados = false;
          let mensajesError = [];
          const promesasRegistro = [];

          for (const claseData of clasesAEnviar) {
              if (!claseData.esp_id || !claseData.dia || !claseData.hora_inicio || !claseData.hora_fin || !seccionPrincipalId || !claseData.uc_id || !claseData.doc_id) {
                  erroresEncontrados = true;
                  mensajesError.push(`Datos incompletos para una clase (Día: ${claseData.dia || 'No def.'}, Hora: ${claseData.hora_inicio || 'No def.'}). UC: ${claseData.uc_id}. Esp: ${claseData.esp_id}. Doc: ${claseData.doc_id}. Sec: ${seccionPrincipalId}. Fase: ${faseGlobal}`);
                  continue;
              }
              const datosClase = new FormData();
              datosClase.append("accion", "registrar_clase_individual");
              datosClase.append("esp_id", claseData.esp_id);
              datosClase.append("hor_fase", faseGlobal);
              datosClase.append("dia", claseData.dia);
              datosClase.append("hora_inicio", claseData.hora_inicio); 
              datosClase.append("hora_fin", claseData.hora_fin);     
              datosClase.append("sec_id", seccionPrincipalId);
              datosClase.append("uc_id", claseData.uc_id);
              datosClase.append("doc_id", claseData.doc_id);

              promesasRegistro.push(
                  $.ajax({ url: "", type: "POST", contentType: false, data: datosClase, processData: false, cache: false })
                  .then(function(respuesta) {
                      try {
                          const lee = JSON.parse(respuesta);
                          if (lee.resultado !== 'registrar_clase_ok' && lee.resultado !== 'registrar_clase_ok_existente') {
                              erroresEncontrados = true;
                              mensajesError.push(`Clase (UC ${claseData.uc_id} en ${claseData.dia} ${claseData.hora_inicio}): ${lee.mensaje}`);
                          }
                      } catch (e) {
                          erroresEncontrados = true;
                          mensajesError.push(`Respuesta inválida del servidor para clase UC ${claseData.uc_id}.`);
                          console.error("Error parseando respuesta registro individual:", e, respuesta);
                      }
                  }, function(jqXHR, textStatus, errorThrown) {
                      erroresEncontrados = true;
                      mensajesError.push(`Error de comunicación para clase UC ${claseData.uc_id}: ${textStatus} - ${errorThrown}`);
                      console.error("Error AJAX registro individual:", jqXHR.responseText);
                  })
              );
          }

          if (promesasRegistro.length > 0) {
              await Promise.all(promesasRegistro);
              if (!erroresEncontrados) {
                  muestraMensaje("success", 4000, "REGISTRO COMPLETO", "Todas las clases del horario han sido procesadas.");
                  $("#modal-horario").modal("hide");
                  limpiaParaNuevoRegistro();
                  Listar(); 
              } else {
                  muestraMensaje("error", 15000, "REGISTRO CON ERRORES", "Algunas clases no pudieron ser registradas o hubo problemas. Detalles: \n" + mensajesError.join("\n"));
              }
          } else if (erroresEncontrados) {
              muestraMensaje("error", 15000, "ERROR EN DATOS PREVIOS", "No se procesó ninguna clase debido a datos faltantes. Detalles: \n" + mensajesError.join("\n"));
          } else if (clasesAEnviar.length === 0 && !erroresEncontrados) {
              muestraMensaje("info", 4000, "SIN CLASES", "No había clases nuevas para registrar.");
          }
          botonProceso.prop("disabled", false).text("REGISTRAR");


      } else if (accionActual === "modificar_grupo") {
          const datosGrupo = new FormData();
          datosGrupo.append("accion", "modificar_grupo");
          datosGrupo.append("sec_id", $("#current_editing_sec_id_hidden").val());
          datosGrupo.append("hor_fase", $("#current_editing_hor_fase_hidden").val());
          datosGrupo.append("nueva_seccion_id", seccionPrincipalId);
          datosGrupo.append("nueva_hor_fase", faseGlobal);
          datosGrupo.append("items_horario", JSON.stringify(clasesAEnviar)); 
          enviaAjax(datosGrupo); 
      }
  }


  $("#proceso").on("click", async function () {
    const botonProceso = $(this);
    const accionBoton = botonProceso.data("action-type");

    if (accionBoton === "confirm-delete-group") {
        const sec_id = botonProceso.data("delete-sec-id");
        const hor_fase = botonProceso.data("delete-hor-fase");
        const seccionNombre = botonProceso.data("delete-seccion-nombre");

        $("#modal-horario").modal("hide");

        Swal.fire({
            title: `¿Eliminar todo el horario para ${seccionNombre} - Fase ${hor_fase}?`,
            text: "Esta acción no se puede deshacer y eliminará todas las clases asociadas.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datosEliminar = new FormData();
                datosEliminar.append("accion", "eliminar_por_seccion_fase");
                datosEliminar.append("sec_id", sec_id);
                datosEliminar.append("hor_fase", hor_fase);
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
        const faseGlobal = $("#hor_fase").val();
        const seccionData = allSecciones.find(s => s.sec_id == seccionPrincipalId);
        const seccionNombreParaMsg = seccionData ? `${seccionData.sec_codigo} (Tray. ${seccionData.tra_numero} - Año ${seccionData.tra_anio})` : `ID ${seccionPrincipalId}`;


        if (!seccionPrincipalId) {
            muestraMensaje("error", 4000, "ERROR", "Debe seleccionar la Sección principal del horario.");
            botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
            return;
        }
        if (!faseGlobal) {
            muestraMensaje("error", 4000, "ERROR", "Debe seleccionar la Fase del horario.");
            botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
            return;
        }
        if (horarioContenidoGuardado.size === 0 && accionActual === "registrar") {
            muestraMensaje("error", 4000, "ERROR", "Debe añadir al menos una clase al horario para registrar.");
            botonProceso.prop("disabled", false).text("REGISTRAR");
            return;
        }
        if (horarioContenidoGuardado.size === 0 && accionActual === "modificar_grupo") {
             console.info("Modificando a un horario vacío. Se eliminarán todas las clases del grupo original.");
        }


        var datosVerificacion = new FormData();
        let necesitaVerificacion = false;

        if (accionActual === "registrar") {
            datosVerificacion.append("sec_id", seccionPrincipalId);
            datosVerificacion.append("hor_fase", faseGlobal);
            necesitaVerificacion = true;
        } else if (accionActual === "modificar_grupo") {
            const secIdOriginal = $("#current_editing_sec_id_hidden").val();
            const horFaseOriginal = $("#current_editing_hor_fase_hidden").val();
            if (seccionPrincipalId !== secIdOriginal || faseGlobal !== horFaseOriginal) {
                datosVerificacion.append("sec_id", seccionPrincipalId);
                datosVerificacion.append("hor_fase", faseGlobal);
                necesitaVerificacion = true;
            }
        }

        if (necesitaVerificacion) {
            datosVerificacion.append("accion", "verificar_horario_existente");
            $.ajax({
                url: "", type: "POST", data: datosVerificacion, contentType: false, processData: false,
                success: async function(respuestaVerif) {
                    try {
                        const verif = JSON.parse(respuestaVerif);
                        if (verif.resultado === 'ok' && verif.existe) {
                            muestraMensaje("error", 7000, "HORARIO DUPLICADO", `Ya existe un horario activo para la sección ${seccionNombreParaMsg} y Fase ${faseGlobal}. No se puede registrar/modificar a esta combinación.`);
                            botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
                        } else if (verif.resultado === 'ok' && !verif.existe) {
                           await procesarGuardadoHorario(accionActual, seccionPrincipalId, faseGlobal, botonProceso);
                        } else {
                            muestraMensaje("error", 5000, "Error de Verificación", verif.mensaje || "No se pudo verificar la existencia del horario.");
                            botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
                        }
                    } catch (e) {
                        console.error("Error parseando respuesta de verificación:", e, respuestaVerif);
                        muestraMensaje("error", 5000, "Error Verificación", "Respuesta de verificación inválida.");
                        botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
                    }
                },
                error: function() {
                    muestraMensaje("error", 5000, "Error de Red", "No se pudo contactar al servidor para verificar el horario.");
                    botonProceso.prop("disabled", false).text(accionActual === "registrar" ? "REGISTRAR" : "GUARDAR CAMBIOS");
                }
            });
        } else {
            await procesarGuardadoHorario(accionActual, seccionPrincipalId, faseGlobal, botonProceso);
        }
        return;
    }

    if (!validarenvioIndividual()) { return; }
    var datosFormularioPrincipal = new FormData($('#form-horario')[0]);
    if (accionActual === "modificar_clase_individual") {
      datosFormularioPrincipal.append("accion", "modificar_clase_individual");
      if (!$("#hor_id").val()) { muestraMensaje("error", 4000, "ERROR", "ID de clase no encontrado para modificar."); botonProceso.prop("disabled", false).text("Modificar Clase"); return; }
      enviaAjax(datosFormularioPrincipal);
    } else if (accionActual === "eliminar_clase_individual") {
      Swal.fire({ title: "¿Eliminar esta clase específica?", text: "Esta acción no se puede deshacer.", icon: "warning", showCancelButton: true, confirmButtonColor: "#3085d6", cancelButtonColor: "#d33", confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar" })
      .then((result) => {
        if (result.isConfirmed) {
          var datosEliminar = new FormData();
          datosEliminar.append("accion", "eliminar_clase_individual");
          datosEliminar.append("hor_id", $("#hor_id").val());
          enviaAjax(datosEliminar);
        } else {
            muestraMensaje( "info", 2000, "CANCELADO", "La eliminación ha sido cancelada.");
            botonProceso.prop("disabled", false).text("Eliminar Clase");
        }
      });
    }
  });

  $("#registrar").on("click", function () {
    limpiaParaNuevoRegistro();
    $("#modalHorarioGlobalTitle").text("Registrar Nuevo Horario");
    $("#accion").val("registrar");
    $("#proceso").text("REGISTRAR").data("action-type", "registrar").removeClass("btn-danger btn-warning").addClass("btn-primary").prop("disabled", false);
    $("#seccion_principal_id, #hor_fase").prop("disabled", false);
    $("#controlesTablaHorario, #contenedorTablaHorario").show();
    $("#btnAnadirFranja").show();
    $("#modal-horario").data("mode", "registrar"); 
    inicializarTablaHorario(); 
    $("#modal-horario").modal("show");
  });


  function generarCellContentParaHorarioPrincipal(clase) {
      const ucData = allUcs.find(uc => uc.uc_id == clase.uc_id);
      const espData = allEspacios.find(esp => esp.esp_id == clase.esp_id);
      const docData = clase.doc_id ? allDocentes.find(doc => doc.doc_id == clase.doc_id) : null;

      const uc_nombre_display = ucData ? ucData.uc_nombre : (clase.uc_id ? `UC ID: ${clase.uc_id}` : 'N/A');
      const esp_codigo_display = espData ? espData.esp_codigo : (clase.esp_id ? `ESP ID: ${clase.esp_id}` : 'N/A');
      const doc_nombre_completo_display = docData ? `${docData.doc_nombre} ${docData.doc_apellido}` : (clase.doc_id ? `Doc. ID: ${clase.doc_id}` : 'Doc. ND');

      return `
        <p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_nombre_display}</strong></p>
        <small style="font-size: 0.8em;">${esp_codigo_display}</small><br>
        <small style="font-size: 0.8em;">${doc_nombre_completo_display}</small>
      `;
  }

  $(document).on('click', '.modificar-grupo-horario', function() {
      const sec_id_original = $(this).data('sec-id');
      const hor_fase_original = $(this).data('hor-fase');
      const $row = $(this).closest('tr');
      const seccionCodigo = $row.find('td:nth-child(1)').text();
      const trayectoNum = $row.find('td:nth-child(2)').text();
      const trayectoAnio = $row.find('td:nth-child(3)').text();
      const seccionNombreCompleto = `${seccionCodigo} ${trayectoNum} ${trayectoAnio}`;


      limpiaParaNuevoRegistro();
      $("#modalHorarioGlobalTitle").text(`Modificar Horario: ${seccionNombreCompleto} - Fase ${hor_fase_original}`);
      $("#accion").val("modificar_grupo");
      $("#proceso").text("GUARDAR").data("action-type", "modificar_grupo").removeClass("btn-danger btn-warning").addClass("btn-primary").prop("disabled", false);

      $("#current_editing_sec_id_hidden").val(sec_id_original);
      $("#current_editing_hor_fase_hidden").val(hor_fase_original);

      $("#seccion_principal_id").val(sec_id_original);
      $("#hor_fase").val(hor_fase_original);

      $("#seccion_principal_id, #hor_fase").prop("disabled", false);
      $("#controlesTablaHorario, #contenedorTablaHorario").show();
      $("#btnAnadirFranja").show();
      $("#modal-horario").data("mode", "modificar"); 

      var datosConsulta = new FormData();
      datosConsulta.append("accion", "consultar_detalles_para_grupo");
      datosConsulta.append("sec_id", sec_id_original);
      datosConsulta.append("hor_fase", hor_fase_original);

      $.ajax({
          url: "", type: "POST", data: datosConsulta, contentType: false, processData: false,
          success: function(respuesta) {
              try {
                  const lee = JSON.parse(respuesta);
                  if (lee.resultado === 'ok' && lee.mensaje) {
                      horarioContenidoGuardado.clear();
                      if (Array.isArray(lee.mensaje)) {
                          lee.mensaje.forEach(clase => {
                              const key = `${clase.hora_inicio}-${clase.dia}`; 
                              const cellContent = generarCellContentParaHorarioPrincipal(clase);
                              horarioContenidoGuardado.set(key, {
                                  html: cellContent,
                                  data: clase 
                              });
                          });
                      }
                      inicializarTablaHorario(); 
                      $("#modal-horario").modal("show");
                  } else { muestraMensaje("error", 5000, "Error al cargar", lee.mensaje || "No se pudieron cargar los detalles del horario para modificar."); }
              } catch (e) { muestraMensaje("error", 5000, "Error de respuesta", "Respuesta inválida del servidor al cargar detalles."); console.error(e, respuesta); }
          },
          error: function() { muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo contactar al servidor para cargar detalles."); }
      });
  });

  $(document).on('click', '.eliminar-grupo-horario', function() {
      const sec_id = $(this).data('sec-id');
      const hor_fase = $(this).data('hor-fase');
      const $row = $(this).closest('tr');
      const seccionCodigo = $row.find('td:nth-child(1)').text();
      const trayectoNum = $row.find('td:nth-child(2)').text();
      const trayectoAnio = $row.find('td:nth-child(3)').text();
      const seccionNombre = `${seccionCodigo} ${trayectoNum} ${trayectoAnio}`;

      $("#modal-horario").data("mode", "delete-confirm"); 

      var datosConsulta = new FormData();
      datosConsulta.append("accion", "consultar_detalles_para_grupo");
      datosConsulta.append("sec_id", sec_id);
      datosConsulta.append("hor_fase", hor_fase);

      $.ajax({
          url: "",
          type: "POST",
          data: datosConsulta,
          contentType: false,
          processData: false,
          success: function(respuesta) {
              try {
                  const lee = JSON.parse(respuesta);
                  if (lee.resultado === 'ok' && lee.mensaje) {
                      limpiaParaNuevoRegistro(); 
                      $("#modalHorarioGlobalTitle").text(`Confirmar Eliminación: ${seccionNombre} - Fase ${hor_fase}`);

                      $("#seccion_principal_id").val(sec_id).prop("disabled", true);
                      $("#hor_fase").val(hor_fase).prop("disabled", true);
                      $("#btnAnadirFranja, #controlesTablaHorario").hide(); 

                      horarioContenidoGuardado.clear();
                      if (Array.isArray(lee.mensaje)) {
                          lee.mensaje.forEach(clase => {
                              const key = `${clase.hora_inicio}-${clase.dia}`; 
                              const cellContent = generarCellContentParaHorarioPrincipal(clase);
                              horarioContenidoGuardado.set(key, { html: cellContent, data: clase });
                          });
                      }
                      inicializarTablaHorario(); 
                      $("#tablaHorario .celda-horario").off("click"); 

                      $("#proceso").text("ELIMINAR")
                                   .data("action-type", "confirm-delete-group")
                                   .data("delete-sec-id", sec_id)
                                   .data("delete-hor-fase", hor_fase)
                                   .data("delete-seccion-nombre", seccionNombre)
                                   .removeClass("btn-primary").addClass("btn-danger")
                                   .prop("disabled", false).show();
                      $("#modal-horario").modal("show");
                  } else { muestraMensaje("error", 5000, "Error", lee.mensaje || "No se pudieron cargar detalles para confirmar eliminación."); }
              } catch (e) { muestraMensaje("error", 5000, "Error", "Respuesta inválida del servidor al cargar para eliminar."); console.error(e, respuesta); }
          },
          error: function() { muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo contactar al servidor."); }
      });
  });

  $(document).on('click', '.ver-grupo-horario', function() {
      const sec_id = $(this).data('sec-id');
      const hor_fase = $(this).data('hor-fase');
      const $row = $(this).closest('tr');
      const seccionCodigo = $row.find('td:nth-child(1)').text();
      const trayectoNum = $row.find('td:nth-child(2)').text();
      const trayectoAnio = $row.find('td:nth-child(3)').text();
      const seccionNombre = `${seccionCodigo} ${trayectoNum} ${trayectoAnio}`;

      $("#modal-horario").data("mode", "view-only"); 

      var datosConsulta = new FormData();
      datosConsulta.append("accion", "consultar_detalles_para_grupo");
      datosConsulta.append("sec_id", sec_id);
      datosConsulta.append("hor_fase", hor_fase);

      $.ajax({
          url: "",
          type: "POST",
          data: datosConsulta,
          contentType: false,
          processData: false,
          success: function(respuesta) {
              try {
                  const lee = JSON.parse(respuesta);
                  if (lee.resultado === 'ok' && lee.mensaje) {
                      limpiaParaNuevoRegistro();
                      $("#modalHorarioGlobalTitle").text(`Ver Horario: ${seccionNombre} - Fase ${hor_fase}`);
                       $("#seccion_principal_id").val(sec_id).prop("disabled", true);
                       $("#hor_fase").val(hor_fase).prop("disabled", true);
                       $("#btnAnadirFranja, #controlesTablaHorario").hide(); 
                       $("#proceso").hide(); 

                      horarioContenidoGuardado.clear();
                      if (Array.isArray(lee.mensaje)) {
                          lee.mensaje.forEach(clase => {
                              const key = `${clase.hora_inicio}-${clase.dia}`; 
                              const cellContent = generarCellContentParaHorarioPrincipal(clase);
                              horarioContenidoGuardado.set(key, { html: cellContent, data: clase });
                          });
                      }
                      inicializarTablaHorario(); 
                      $("#tablaHorario .celda-horario").off("click"); 
                      $("#modal-horario").modal("show");
                  } else { muestraMensaje("error", 5000, "Error", lee.mensaje || "No se pudieron cargar los detalles del horario."); }
              } catch (e) { muestraMensaje("error", 5000, "Error", "Respuesta inválida del servidor."); console.error("Error en .ver-grupo-horario:", e, respuesta); }
          },
          error: function() { muestraMensaje("error", 5000, "Error de Comunicación", "No se pudo contactar al servidor."); }
      });
  });

  $('#modal-horario').on('hidden.bs.modal', function () {
      limpiaParaNuevoRegistro(); 
      $("#seccion_principal_id, #hor_fase").prop("disabled", false);
      $("#btnAnadirFranja, #controlesTablaHorario, #proceso").show();
      $("#proceso").text("REGISTRAR") 
                   .data("action-type", "") 
                   .removeData("delete-sec-id")
                   .removeData("delete-hor-fase")
                   .removeData("delete-seccion-nombre")
                   .removeClass("btn-danger btn-warning").addClass("btn-primary")
                   .prop("disabled", false);
      $(this).removeData("mode"); 
   
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
  });

}); 


function validarHoraIndividual(campo, span, mensaje) {
  if ($(".form-group-horario-individual:visible").length > 0 && $(campo).is(":visible")) {
    if (!/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(campo.val())) {
      span.text(mensaje).addClass("text-danger").show(); return false;
    }
  }
  span.text("").removeClass("text-danger").hide(); return true;
}

function validarRangoHorasIndividual() {
  if ($(".form-group-horario-individual:visible").length > 0 && $("#hora_inicio").is(":visible") && $("#hora_fin").is(":visible")) {
    var inicio = $("#hora_inicio").val(); var fin = $("#hora_fin").val();
    if (inicio && fin && inicio >= fin) {
      $("#shora_fin").text("La hora fin debe ser mayor a la hora inicio").addClass("text-danger").show(); return false;
    }
  }
  $("#shora_fin").text("").removeClass("text-danger").hide(); return true;
}

function validarenvioIndividual() {
  const accionProceso = $("#accion").val();
  const currentMode = $("#modal-horario").data("mode");

  if (accionProceso === "registrar" || accionProceso === "modificar_grupo" || currentMode === "delete-confirm" || currentMode === "view-only") {
      return true;
  }

  if ($(".form-group-horario-individual:visible").length > 0) {
    var camposSelect = [
        {selector: "#esp_id", nombre: "Espacio"},
        {selector: "#dia", nombre: "Día"},
        {selector: "#uc_id", nombre: "Unidad curricular"},
        {selector: "#doc_id", nombre: "Docente"}
    ];
    var valido = true;
    camposSelect.forEach(function (campo) {
      if ($(campo.selector).is(":visible") && !$(campo.selector).val()) {
        muestraMensaje("error", 4000, "CAMPO REQUERIDO", `Por favor, seleccione ${campo.nombre}.`);
        valido = false;
        return false; 
      }
    });
    if (!valido) return false;
    if (!validarHoraIndividual($("#hora_inicio"), $("#shora_inicio"), "Formato HH:MM")) return false;
    if (!validarHoraIndividual($("#hora_fin"), $("#shora_fin"), "Formato HH:MM")) return false;
    if (!validarRangoHorasIndividual()) return false;
  }
  return true; 
}

function enviaAjax(datos) {
  const accionEnviada = datos.get("accion");
  let botonProceso = $("#proceso");

  if (accionEnviada !== "verificar_horario_existente" &&
      accionEnviada !== "consultar_agrupado" &&
      accionEnviada !== "consultar_detalles_para_grupo" &&
      accionEnviada !== "obtener_uc_por_docente" &&
      accionEnviada !== "obtener_datos_selects" &&
      accionEnviada !== "registrar_clase_individual" 
     ) {
        if (botonProceso.text() !== "Procesando...") { 
            botonProceso.prop("disabled", true).text("Procesando...");
        }
  }


  $.ajax({
    async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false, timeout: 25000,
    success: function (respuesta) {
      try {
  
        var lee = JSON.parse(respuesta);

        let textoBotonOriginal = "REGISTRAR"; 
        if (accionEnviada === "modificar_grupo") textoBotonOriginal = "GUARDAR CAMBIOS";
        else if (accionEnviada === "eliminar_por_seccion_fase") textoBotonOriginal = "CONFIRMAR ELIMINACIÓN";


        if (lee.resultado == "consultar_agrupado") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          if (lee.mensaje && lee.mensaje.length > 0) {
            $.each(lee.mensaje, function (index, item) {
              $("#resultadoconsulta").append(`
                <tr>
                  <td>${item.sec_codigo || 'N/A'}</td>
                  <td>Trayecto ${item.tra_numero || 'N/A'}</td>
                  <td>Año ${item.tra_anio || 'N/A'}</td>
                  <td>${item.hor_fase || 'N/A'}</td>
                  <td>
                    <button class="btn btn-info btn-sm ver-grupo-horario" data-sec-id="${item.sec_id}" data-hor-fase="${item.hor_fase}" title="Ver Horario"><i class="bi bi-eye-fill"></i> Ver Horario</button>
                    <button class="btn btn-warning btn-sm modificar-grupo-horario" data-sec-id="${item.sec_id}" data-hor-fase="${item.hor_fase}" title="Modificar Horario"><i class="bi bi-pencil-square"></i> Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar-grupo-horario" data-sec-id="${item.sec_id}" data-hor-fase="${item.hor_fase}" title="Eliminar Horario"><i class="bi bi-trash-fill"></i> Eliminar</button>
                  </td>
                </tr>`);
            });
          }
          crearDT();
        
          if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
          }
        } else if (lee.resultado == "registrar_clase_ok" || lee.resultado == "registrar_clase_ok_existente") {
        
        }
        else if (lee.resultado == "modificar_grupo_ok" || lee.resultado == "eliminar_por_seccion_fase_ok") {
            muestraMensaje("success", 4000, lee.resultado.includes("modificar") ? "MODIFICACIÓN EXITOSA" : "ELIMINACIÓN EXITOSA", lee.mensaje);
            $("#modal-horario").modal("hide"); 
            Listar(); 
        }
        else if (lee.resultado == "modificar_clase_individual_ok" || lee.resultado == "eliminar_clase_individual_ok") {
          const tipoAccion = lee.resultado.includes("modificar") ? "MODIFICACIÓN CLASE" : "ELIMINACIÓN CLASE";
          muestraMensaje("info", 4000, tipoAccion, lee.mensaje); 
          $("#modal-horario").modal("hide");
          Listar(); 
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR DE OPERACIÓN", lee.mensaje);
           if (accionEnviada === "modificar_grupo" || accionEnviada === "registrar" || accionEnviada === "eliminar_por_seccion_fase") {
                botonProceso.prop("disabled", false).text(textoBotonOriginal);
                if(accionEnviada === "eliminar_por_seccion_fase") botonProceso.addClass("btn-danger").removeClass("btn-primary");
           } else if (accionEnviada !== "verificar_horario_existente" && 
                      accionEnviada !== "consultar_agrupado" && 
                      accionEnviada !== "consultar_detalles_para_grupo" && 
                      accionEnviada !== "obtener_uc_por_docente" &&
                      accionEnviada !== "obtener_datos_selects" &&
                      accionEnviada !== "registrar_clase_individual") {
                botonProceso.prop("disabled", false).text(textoBotonOriginal); 
           }
        } else if (lee.resultado === 'ok' && (accionEnviada === 'consultar_detalles_para_grupo' || accionEnviada === 'obtener_uc_por_docente' || accionEnviada === 'obtener_datos_selects' || accionEnviada === 'verificar_horario_existente')) {
          
        } else {
           muestraMensaje("warning", 5000, "RESPUESTA DESCONOCIDA", lee.mensaje || "Respuesta no estándar del servidor.");
           if (accionEnviada === "modificar_grupo" || accionEnviada === "registrar" || accionEnviada === "eliminar_por_seccion_fase") {
                botonProceso.prop("disabled", false).text(textoBotonOriginal);
                 if(accionEnviada === "eliminar_por_seccion_fase") botonProceso.addClass("btn-danger").removeClass("btn-primary");
           } else if (accionEnviada !== "verificar_horario_existente" && 
                      accionEnviada !== "consultar_agrupado" && 
                      true){ 
           }
        }
      } catch (e) {
        console.error("Error en JSON o success AJAX:", e, "Respuesta:", respuesta);
        muestraMensaje("error", 10000, "ERROR DE RESPUESTA", "La respuesta del servidor no pudo ser procesada. Revise la consola. Raw: " + String(respuesta).substring(0, 200));
        
        let textoBotonOriginalCatch = "REGISTRAR"; 
        if (accionEnviada === "modificar_grupo") textoBotonOriginalCatch = "GUARDAR CAMBIOS";
        else if (accionEnviada === "eliminar_por_seccion_fase") textoBotonOriginalCatch = "CONFIRMAR ELIMINACIÓN";

        if (accionEnviada !== "verificar_horario_existente" && 
            accionEnviada !== "consultar_agrupado" && 
            accionEnviada !== "consultar_detalles_para_grupo" && 
            accionEnviada !== "obtener_uc_por_docente" &&
            accionEnviada !== "obtener_datos_selects" &&
            accionEnviada !== "registrar_clase_individual"){
             botonProceso.prop("disabled", false).text(textoBotonOriginalCatch);
             if(accionEnviada === "eliminar_por_seccion_fase") botonProceso.addClass("btn-danger").removeClass("btn-primary");
        }
      }
    },
    error: function (request, status, err) {
      console.error("Error AJAX:", status, err, request.responseText);
      muestraMensaje("error", 5000, status == "timeout" ? "SERVIDOR OCUPADO (Timeout)" : "ERROR DE CONEXIÓN", `No se pudo comunicar con el servidor. (${request.status}): ${err}. Revise la consola.`);
      
      let textoBotonOriginalError = "REGISTRAR"; 
      if (accionEnviada === "modificar_grupo") textoBotonOriginalError = "GUARDAR CAMBIOS";
      else if (accionEnviada === "eliminar_por_seccion_fase") textoBotonOriginalError = "CONFIRMAR ELIMINACIÓN";

      if (accionEnviada !== "verificar_horario_existente" && 
          accionEnviada !== "consultar_agrupado" && 
          accionEnviada !== "registrar_clase_individual"){ 
           botonProceso.prop("disabled", false).text(textoBotonOriginalError);
           if(accionEnviada === "eliminar_por_seccion_fase") botonProceso.addClass("btn-danger").removeClass("btn-primary");
      }
    },
  });
}

function limpiaParaNuevoRegistro() {
  $("#form-horario")[0].reset(); 
  $("#accion").val(""); 
  $("#hor_id").val(""); 

  $("#current_editing_sec_id_hidden").val("");
  $("#current_editing_hor_fase_hidden").val("");

  $(".form-group-horario-individual").hide();
  $("#esp_id, #dia, #hora_inicio, #hora_fin, #uc_id, #doc_id").prop('disabled', true);

  $("#seccion-principal-group, #fase-group").show();
  $("#seccion_principal_id, #hor_fase").prop('disabled', false).val(''); 

  $("#controlesTablaHorario, #contenedorTablaHorario").show();
  $("#btnAnadirFranja").show(); 

  horarioContenidoGuardado.clear(); 
  inicializarTablaHorario(); 

  $("#shora_inicio, #shora_fin").text("").removeClass("text-danger").hide();
}