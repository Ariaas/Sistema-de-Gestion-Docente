let franjasHorariasVerDocente = [
  { inicio: "07:00", fin: "09:00" }, { inicio: "09:00", fin: "11:00" },
  { inicio: "11:00", fin: "13:00" }, { inicio: "13:00", fin: "15:00" },
  { inicio: "15:00", fin: "17:00" }, { inicio: "17:00", fin: "19:00" }
];

let horarioContenidoGuardadoVerDocente = new Map();
let allUcsVer = [];
let allEspaciosVer = [];
let allSeccionesVer = [];

function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablahorario")) {
    $("#tablahorario").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablahorario")) {
    $("#tablahorario").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados en la tabla",
        emptyTable: "No hay horarios docentes registrados.",
        info: "Mostrando _PAGE_ de _PAGES_ (Total: _TOTAL_ registros)",
        infoEmpty: "No hay registros disponibles",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
      order: [[2, "asc"]],
      columns: [
        { data: 'hdo_id', visible: false },
        { data: 'doc_id', visible: false },
        { data: 'doc_nombre_completo' },

        {
          data: null,
          render: function(data, type, row) {
            return `Fase ${row.hdo_lapso || ''} (${row.tra_anio || 'Año desc.'})`;
          }
        },
        { data: 'hdo_tipoactividad' },
        { data: 'hdo_descripcion' },
        { data: 'hdo_dependencia' },
        { data: 'hdo_observacion' },
        { data: 'hdo_hora' },
        {
          data: null,
          orderable: false,
          render: function (data, type, row, meta) {
            
            return `<button type="button" class="btn btn-info btn-sm rounded-pill me-1 ver-horario-docente"
                            data-fase="${row.hdo_lapso || ''}" data-anio="${row.tra_anio || ''}"
                            onclick='poneVerHorario(this)'>Ver Horario</button>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill me-1 modificar" onclick='pone(this,0)'>Modificar</button>
                    <button type="button" class="btn btn-danger btn-sm rounded-pill eliminar" onclick='pone(this,1)'>Eliminar</button>`;
          }
        }
      ],
      createdRow: function( row, data, dataIndex ) {
        $(row).attr('data-doc-id', data.doc_id || '');
        $(row).attr('data-doc-nombre', data.doc_nombre_completo || '');

        $(row).attr('data-lapso-compuesto', `${data.hdo_lapso || ''}-${data.tra_anio || ''}`);
      }
    });
  }
}

function cargarDocentesEnSelect() {
    $.ajax({
      url: 'index.php?pagina=horarioDocente&action=load_aux_data',
      method: 'POST',
      data: {action: 'load_aux_data'},
      dataType: 'json',
      success: function (data) {
        var $select = $('#docente');
        if (data && data.success && data.teachers) {
          $select.empty();
          $select.append('<option value="">-- Seleccione un Docente --</option>');
          data.teachers.forEach(function (doc) {
            var nombre = (doc.doc_prefijo ? doc.doc_prefijo + '. ' : '') + doc.doc_nombre + ' ' + doc.doc_apellido;
            $select.append(`<option value="${doc.doc_id}">${nombre}</option>`);
          });
        } else {
          console.error("Error al cargar docentes:", data.message || "Respuesta no exitosa.");
          $select.empty().append('<option value="">Error al cargar docentes</option>');
        }
      },
      error: function(xhr, status, error) {
        console.error("Error AJAX cargar docentes: " + error);
        $('#docente').empty().append('<option value="">Error de red al cargar</option>');
      }
    });
}

function cargarLapsosParaDocente(docId, isModoEliminar = false) {
    const lapsoSelect = $('#lapso');
    if (!docId) {
        lapsoSelect.empty().append('<option value="" disabled selected>Seleccione un docente primero</option>').prop('disabled', true);
        return;
    }
    lapsoSelect.empty().append('<option value="" disabled selected>Cargando lapsos...</option>').prop('disabled', true);

    $.ajax({
        url: 'index.php?pagina=horarioDocente&action=obtener_lapsos_docente',
        method: 'POST',
        data: { action: 'obtener_lapsos_docente', doc_id: docId },
        dataType: 'json',
        success: function(data) {
            lapsoSelect.empty();
            if (data && data.success && data.lapsos && data.lapsos.length > 0) {
                lapsoSelect.append('<option value="" disabled selected>Seleccione un lapso</option>');
                data.lapsos.forEach(function(lapso) {
                    lapsoSelect.append(`<option value="${lapso.hor_fase}-${lapso.tra_anio}">Fase ${lapso.hor_fase} - Año ${lapso.tra_anio}</option>`);
                });
                if (isModoEliminar) {
                    lapsoSelect.prop('disabled', true);
                } else {
                    lapsoSelect.prop('disabled', false);
                }
            } else {
                lapsoSelect.append('<option value="" disabled selected>No hay lapsos para este docente</option>');
                lapsoSelect.prop('disabled', true);
                if(data.message) muestraMensaje("info", 5000, "Información", data.message);
            }
        },
        error: function() {
            lapsoSelect.empty().append('<option value="" disabled selected>Error al cargar lapsos</option>');
            lapsoSelect.prop('disabled', true);
            muestraMensaje("error", 5000, "Error de Red", "No se pudieron cargar los lapsos del docente.");
        }
    });
}


function cargarDatosAuxiliaresParaVerHorario() {
    if (allUcsVer.length > 0 && allEspaciosVer.length > 0 && allSeccionesVer.length > 0) {
        return;
    }
    $.ajax({
        url: 'index.php?pagina=horarioDocente&action=load_schedule_display_data',
        method: 'POST',
        data: {action: 'load_schedule_display_data'},
        dataType: 'json',
        success: function(data) {
            if (data && data.success) {
                allUcsVer = data.ucs || [];
                allEspaciosVer = data.espacios || [];
                allSeccionesVer = data.secciones || [];
            } else {
                console.error("Error al cargar datos aux para ver horario:", data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error AJAX cargar datos aux ver horario: " + error);
        }
    });
}

function generarCellContentVerDocente(clase) {
    const ucData = allUcsVer.find(uc => uc.uc_id == clase.uc_id);
    const espData = allEspaciosVer.find(esp => esp.esp_id == clase.esp_id);

    const secData = allSeccionesVer.find(sec => sec.sec_id == clase.sec_id);


    const uc_display = ucData ? (ucData.uc_nombre || ucData.uc_codigo) : (clase.uc_codigo || 'N/A');
    const esp_display = espData ? espData.esp_codigo : (clase.esp_codigo || 'N/A');

    let sec_display_text = '';
    if (secData) {
        sec_display_text = `Sec: ${secData.sec_codigo} (Tr.${secData.tra_numero} ${secData.tra_anio})`;
    } else if (clase.seccion_codigo) {
        sec_display_text = `Sec: ${clase.seccion_codigo}`;
        if(clase.anio_trayecto) sec_display_text += ` (Año ${clase.anio_trayecto})`;
    }


    let content = `<p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_display}</strong></p>
                   <small style="font-size: 0.8em;">${esp_display}</small>`;
    if (sec_display_text) {
        content += `<br><small style="font-size: 0.8em;">${sec_display_text}</small>`;
    }
    return content;
}

function inicializarTablaVerHorarioDocente(faseMostrada, anioMostrado) {
    const tbody = $("#tablaVerHorarioDocente tbody");
    tbody.empty();


    const nombreDocenteActual = $("#nombreDocenteHorario").text();
    $("#modalVerHorarioDocenteTitle").text(`Horario de ${nombreDocenteActual} - Fase ${faseMostrada} (Año ${anioMostrado})`);


    if (Array.isArray(franjasHorariasVerDocente)) {
        franjasHorariasVerDocente.sort((a, b) => (a.inicio || "").localeCompare(b.inicio || ""));
    } else {
        franjasHorariasVerDocente = [];
    }

    const dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];

    if (horarioContenidoGuardadoVerDocente.size === 0) {
         tbody.append(`<tr><td colspan="7" class="text-center py-3">El docente no tiene un horario de clases asignado para la Fase ${faseMostrada} - Año ${anioMostrado}.</td></tr>`);
         return;
    }

    if (franjasHorariasVerDocente.length === 0) {
         tbody.append(`<tr><td colspan="7" class="text-center fst-italic">No hay franjas horarias definidas.</td></tr>`);
    }

    franjasHorariasVerDocente.forEach((franja) => {
        const row = $("<tr>");
        row.append(`<td class="align-middle">${franja.inicio || 'N/A'} - ${franja.fin || 'N/A'}</td>`);
        dias.forEach((dia) => {
            const cell = $("<td>").addClass("celda-horario-ver align-middle")
                .attr("data-franja-inicio", franja.inicio || "")
                .attr("data-dia-nombre", dia);
            const key = `${franja.inicio || ""}-${dia}`;
            if (horarioContenidoGuardadoVerDocente.has(key)) {
                let cellHTML = '';
                horarioContenidoGuardadoVerDocente.get(key).forEach(entrada => {
                    if (cellHTML !== '') cellHTML += '<hr style="margin: 4px 0; border-color: #ccc;">';
                    cellHTML += entrada.html;
                });
                cell.html(cellHTML);
            }
            row.append(cell);
        });
        tbody.append(row);
    });

}

function poneVerHorario(boton) {
    cargarDatosAuxiliaresParaVerHorario();

    var linea = $(boton).closest("tr");
    var tabla = $('#tablahorario').DataTable();
    var filaDatos = tabla.row(linea).data();

    var docId = filaDatos.doc_id;
    var docNombre = filaDatos.doc_nombre_completo;
    var faseParaVer = $(boton).data("fase");
    var anioParaVer = $(boton).data("anio");

    if (!docId || faseParaVer === undefined || anioParaVer === undefined) {
        muestraMensaje("error", 5000, "Error", "No se pudieron obtener los datos necesarios (docente, fase o año) para ver el horario.");
        return;
    }

    let tituloNombreDocente = (docNombre && docNombre !== '<em class="text-muted">No asignado</em>') ? docNombre : `Docente ID: ${docId}`;
    $("#nombreDocenteHorario").text(tituloNombreDocente);

    horarioContenidoGuardadoVerDocente.clear();
    $("#tablaVerHorarioDocente tbody").empty().html('<tr><td colspan="7" class="text-center">Cargando horario...</td></tr>');

    $("#modalVerHorarioDocente").modal("show");

    var datosAjax = new FormData();
    datosAjax.append("action", "consultar_horario_docente_especifico");
    datosAjax.append("doc_id", docId);
    datosAjax.append("fase_filtro", faseParaVer);
    datosAjax.append("anio_filtro", anioParaVer);

    $.ajax({
        async: true,
        url: "index.php?pagina=horarioDocente",
        type: "POST",
        contentType: false,
        data: datosAjax,
        processData: false,
        cache: false,
        dataType: 'json',
        success: function(respuesta) {
            horarioContenidoGuardadoVerDocente.clear();

            if (respuesta.franjas_horarias && respuesta.franjas_horarias.length > 0) {
                franjasHorariasVerDocente = respuesta.franjas_horarias.map(fr =>
                    typeof fr === 'string' ? {inicio: fr.split(' - ')[0], fin: fr.split(' - ')[1]} : fr
                );
            } else {
                franjasHorariasVerDocente = [
                    { inicio: "07:00", fin: "09:00" }, { inicio: "09:00", fin: "11:00" },
                    { inicio: "11:00", fin: "13:00" }, { inicio: "13:00", fin: "15:00" },
                    { inicio: "15:00", fin: "17:00" }, { inicio: "17:00", fin: "19:00" }
                  ];
            }

            if (respuesta.resultado === 'ok' && respuesta.horario_docente && Array.isArray(respuesta.horario_docente)) {
                respuesta.horario_docente.forEach(clase => {
                    const key = `${clase.hora_inicio}-${clase.dia}`;
                    const cellContent = generarCellContentVerDocente(clase);
                    if (!horarioContenidoGuardadoVerDocente.has(key)) {
                        horarioContenidoGuardadoVerDocente.set(key, []);
                    }
                    horarioContenidoGuardadoVerDocente.get(key).push({ html: cellContent, data: clase });
                });
            } else if (respuesta.resultado === 'vacio') {

            } else if (respuesta.resultado === 'error') {
                 muestraMensaje("error", 7000, "Error al cargar horario", respuesta.mensaje || "No se pudo obtener el horario.");
            }

            inicializarTablaVerHorarioDocente(faseParaVer, anioParaVer);

        },
        error: function(xhr, status, err) {
            muestraMensaje("error", 7000, "Error de Red", "No se pudo conectar para cargar el horario.");
            $("#tablaVerHorarioDocente tbody").empty().html(`<tr><td colspan="7" class="text-center">Error de red al cargar horario para Fase ${faseParaVer} - Año ${anioParaVer}.</td></tr>`);
        }
    });
}

$(document).ready(function () {
  Listar();
  cargarDocentesEnSelect();
  cargarDatosAuxiliaresParaVerHorario();


  $("#docente").on("change", function() {
      const selectedDocId = $(this).val();
      cargarLapsosParaDocente(selectedDocId);
  });

  $('#modal1').on('show.bs.modal', function () {
    if ($("#proceso").text() === "REGISTRAR") {
        $("#f")[0].reset();
        $("#accion").val("registrar");
        $("#docente, #lapso, #actividad, #descripcion, #dependencia, #observacion, #horas").prop("disabled", false);

        $('#lapso').empty().append('<option value="" disabled selected>Seleccione un docente primero</option>').prop('disabled', true);
        $("#f").removeClass('was-validated');
        $("#hdoId").val("");
    }
  });

  $("#registrar").on("click", function () {
    $("#f")[0].reset();
    $("#accion").val("registrar");
    $("#proceso").text("REGISTRAR");
    $("#docente, #lapso, #actividad, #descripcion, #dependencia, #observacion, #horas").prop("disabled", false);
    $('#lapso').empty().append('<option value="" disabled selected>Seleccione un docente primero</option>').prop('disabled', true);
    $("#hdoId").val("");
    $("#f").removeClass('was-validated');
    if ($('#docente option').length <= 1) {
        cargarDocentesEnSelect();
    }
    $("#modal1").modal("show");
  });

  $("#proceso").on("click", function () {
    var accionActual = $("#accion").val();
    if (accionActual === "eliminar") {
        Swal.fire({
          title: "¿Está seguro de eliminar?", text: "Esta acción no se puede deshacer.",
          icon: "warning", showCancelButton: true, confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6", confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) {
            var datosEliminar = new FormData();
            datosEliminar.append("action", "eliminar");
            datosEliminar.append("hdoId", $("#hdoId").val());
            enviaAjax(datosEliminar);
          }
        });
        return;
    }
    var form = $("#f")[0];
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        var firstInvalidField = $(form).find(':invalid').first();
        if (firstInvalidField.length) {
            firstInvalidField.focus();
        }
        return;
    }
    form.classList.remove('was-validated');

    var datosFormulario = new FormData($("#f")[0]);

    datosFormulario.set("action", accionActual);
    enviaAjax(datosFormulario);
  });
});

function pone(pos, accionModo) {
  var tr = $(pos).closest('tr');
  var tabla = $('#tablahorario').DataTable();
  var dataFila = tabla.row(tr).data();

  $("#f")[0].reset();
  $("#f").removeClass('was-validated');
  $("#f").find('.form-control, .form-select').removeClass('is-invalid');
  $("#f").find('.invalid-feedback').hide();
  $("#f").find('[id^="s"]').text('');

  var docIdParaLapso = null;
  var hdo_lapso = '';
  var tra_anio = '';

  if (dataFila) {
    $("#hdoId").val(dataFila.hdo_id);
    $("#docente").val(dataFila.doc_id);
    docIdParaLapso = dataFila.doc_id;
    hdo_lapso = dataFila.hdo_lapso || '';
    tra_anio = dataFila.tra_anio || '';
    $("#actividad").val(dataFila.hdo_tipoactividad);
    $("#descripcion").val(dataFila.hdo_descripcion);
    $("#dependencia").val(dataFila.hdo_dependencia);
    $("#observacion").val(dataFila.hdo_observacion);
    $("#horas").val(dataFila.hdo_hora);
  } else {
    $("#hdoId").val(tr.find("td:eq(0)").text());
    docIdParaLapso = tr.find("td:eq(1)").text();
    $("#docente").val(docIdParaLapso);
    let lapsoCompuestoAttr = tr.attr('data-lapso-compuesto');
    if (lapsoCompuestoAttr && lapsoCompuestoAttr.includes('-')) {
        let parts = lapsoCompuestoAttr.split('-');
        hdo_lapso = parts[0] || '';
        tra_anio = parts[1] || '';
    }
    $("#actividad").val(tr.find("td:eq(4)").text());
    $("#descripcion").val(tr.find("td:eq(5)").text());
    $("#dependencia").val(tr.find("td:eq(6)").text());
    $("#observacion").val(tr.find("td:eq(7)").text());
    $("#horas").val(tr.find("td:eq(8)").text());
  }

  const lapsoCompuestoParaSeleccionar = `${hdo_lapso}-${tra_anio}`;
  let textoDelLapsoActual = '';
  if (hdo_lapso && tra_anio) {
      textoDelLapsoActual = `Fase ${hdo_lapso} - Año ${tra_anio}`;
  } else if (hdo_lapso) {
      textoDelLapsoActual = `Fase ${hdo_lapso}`;
  } else if (tra_anio) {
      textoDelLapsoActual = `Año ${tra_anio}`;
  }

  const lapsoSelect = $('#lapso');

  if (accionModo == 1) { 
    $("#proceso").text("ELIMINAR");
    $("#accion").val("eliminar");
    $("#docente, #actividad, #descripcion, #dependencia, #observacion, #horas").prop("disabled", true);

    lapsoSelect.empty();
    if (lapsoCompuestoParaSeleccionar && lapsoCompuestoParaSeleccionar !== '-' && textoDelLapsoActual) {
      lapsoSelect.append(`<option value="${lapsoCompuestoParaSeleccionar}">${textoDelLapsoActual}</option>`);
      lapsoSelect.val(lapsoCompuestoParaSeleccionar);
    } else {
      lapsoSelect.append('<option value="">Lapso no asignado</option>');
      lapsoSelect.val("");
    }
    lapsoSelect.prop('disabled', true);

  } else { 
    $("#proceso").text("MODIFICAR");
    $("#accion").val("modificar");
    $("#docente, #actividad, #descripcion, #dependencia, #observacion, #horas").prop("disabled", false);

    if (docIdParaLapso) {
      cargarLapsosParaDocente(docIdParaLapso, false);
      setTimeout(function() {
        if (lapsoCompuestoParaSeleccionar) {
          lapsoSelect.val(lapsoCompuestoParaSeleccionar);
        }
      }, 500);
    } else {
      lapsoSelect.empty().append('<option value="" disabled selected>Seleccione un docente primero</option>').prop('disabled', true);
    }
  }
  $("#modal1").modal("show");
}

function enviaAjax(datos) {
  var textoBotonOriginal = $("#proceso").text();
  var esEliminar = (datos.get("action") === "eliminar" && $("#proceso").text() === "ELIMINAR");

  var esCargaDeDatos = ['load_aux_data', 'obtener_lapsos_docente', 'load_schedule_display_data', 'consultar_horario_docente_especifico'].includes(datos.get("action"));


  $.ajax({
    async: true,
    url: "index.php?pagina=horarioDocente",
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
    dataType: 'json',
    beforeSend: function () {
      if (!esEliminar && $("#proceso").length && $("#proceso").is(":visible") && !esCargaDeDatos) {
          $("#proceso").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
      }
    },
    timeout: 10000,
    success: function (respuestaJSON) {
      if ($("#proceso").length && !esEliminar && $("#proceso").is(":visible") && !esCargaDeDatos) {
          $("#proceso").prop("disabled", false).text(textoBotonOriginal);
      }
      var lee = respuestaJSON;

      if (lee.resultado === "consultar") {
        var dataSet = [];
        if (lee.mensaje && Array.isArray(lee.mensaje) && lee.mensaje.length > 0) {
          $.each(lee.mensaje, function (index, item) {
            let nombreDocenteDisplay = item.doc_nombre_completo || '<em class="text-muted">No asignado</em>';
            dataSet.push({
                hdo_id: item.hdo_id,
                doc_id: item.doc_id || '',
                doc_nombre_completo: nombreDocenteDisplay,
                hdo_lapso: item.hdo_lapso || '',
                tra_anio: item.tra_anio || '',
                hdo_tipoactividad: item.hdo_tipoactividad || '',
                hdo_descripcion: item.hdo_descripcion || '',
                hdo_dependencia: item.hdo_dependencia || '',
                hdo_observacion: item.hdo_observacion || '',
                hdo_hora: item.hdo_hora || '',
            });
          });
        }

        if ($.fn.DataTable.isDataTable("#tablahorario")) {
             $("#tablahorario").DataTable().clear().rows.add(dataSet).draw();
        } else {
             crearDT();
             if (dataSet.length > 0) {
                $("#tablahorario").DataTable().rows.add(dataSet).draw();
             }
        }
      }
      else if (lee.resultado == "registrar" || lee.resultado == "modificar" || lee.resultado == "eliminar") {
        const exito = lee.mensaje && lee.mensaje.toLowerCase().includes("correctamente");
        muestraMensaje(exito ? "success" : "error", 4000, lee.resultado.toUpperCase(), lee.mensaje);
        if (exito) {
          $("#modal1").modal("hide");
          Listar();
        }
      } else if (lee.resultado == "existe") {
        muestraMensaje('warning', 4000,'Atención!', lee.mensaje);
      }
      else if (lee.resultado == "error") {
        muestraMensaje("error", 10000, "ERROR EN OPERACIÓN", lee.mensaje || "Ocurrió un error desconocido.");
      } else if (lee.resultado === undefined && lee.success === false && lee.message) {

        if (!esCargaDeDatos) {
            console.error("Error genérico servidor:", lee.message);
            muestraMensaje("error", 7000, "Error del Servidor", lee.message);
        }
      }
    },
    error: function (xhr, status, err) {
      if ($("#proceso").length && !esEliminar && $("#proceso").is(":visible") && !esCargaDeDatos) {
        $("#proceso").prop("disabled", false).text(textoBotonOriginal);
      }
      var msgError = "Error de comunicación con el servidor. Verifique su conexión de red.";
      if (status === "timeout") {
        msgError = "El servidor tardó demasiado en responder. Intente nuevamente.";
      } else if (xhr.responseText) {
        console.error("Respuesta error AJAX:", xhr.status, xhr.responseText, err);
        try {
            var errorResponse = JSON.parse(xhr.responseText);
            if (errorResponse && errorResponse.message) {
                msgError = errorResponse.message;
            } else if (errorResponse && errorResponse.mensaje) {
                msgError = errorResponse.mensaje;
            } else {
                 msgError = `Error del servidor (Estado: ${xhr.status}). Detalles en la consola.`;
            }
        } catch (e) {
            msgError = `Error del servidor (Estado: ${xhr.status}). Respuesta no es JSON. Detalles en la consola.`;
        }
      }

      if (!esCargaDeDatos) {
          muestraMensaje("error", 10000, "Error de Red o Servidor", msgError);
      }
    }
  });
}

function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true,
        showConfirmButton: (tipo === 'error' || tipo === 'warning'),
        allowOutsideClick: !(tipo === 'error'),
    });
}