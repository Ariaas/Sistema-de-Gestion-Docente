// Definición de una función básica muestraMensaje para evitar el ReferenceError
// Idealmente, esta función debería venir de un archivo de utilidades/notificaciones
// como SweetAlert2. Si ya usas SweetAlert2, asegúrate de que su script se cargue
// ANTES de horario.js en tu HTML.
function muestraMensaje(tipo, tiempo, titulo, mensaje) {
    if (typeof Swal !== 'undefined' && Swal.fire) { // Si SweetAlert2 está disponible
        Swal.fire({
            icon: tipo,
            title: titulo,
            text: mensaje,
            timer: tiempo,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } else { // Fallback si SweetAlert2 no está cargado
        console.log(`[${tipo.toUpperCase()}] ${titulo}: ${mensaje}`);
        alert(`${titulo}: ${mensaje}`);
    }
}


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
      scrollX: true,
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando _PAGE_ de _PAGES_",
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
      autoWidth: false,
      dom:
        "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });

    $("div.dataTables_length select").css({
      width: "auto",
      display: "inline",
      "margin-top": "10px",
    });

    $("div.dataTables_filter").css({
      "margin-bottom": "50px",
      "margin-top": "10px",
    });

    $("div.dataTables_filter label").css({
      float: "left",
    });

    $("div.dataTables_filter input").css({
      width: "300px",
      float: "right",
      "margin-left": "10px",
    });
  }
}

let currentClickedCell = null;
let franjasHorarias = [
  { inicio: "08:00", fin: "10:00" },
  { inicio: "11:00", fin: "13:00" },
  { inicio: "14:00", fin: "16:00" },
  { inicio: "16:00", fin: "18:00" },
];
let horarioContenidoGuardado = new Map();

function inicializarTablaHorario() {
    const tbody = $("#tablaHorario1 tbody");
    tbody.empty();
    franjasHorarias.sort((a, b) => a.inicio.localeCompare(b.inicio));
    // CORRECCIÓN AQUÍ: "Miercoles" sin tilde para coincidir con el ENUM de la BD
    const dias = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado"];

    franjasHorarias.forEach((franja) => {
      const row = $("<tr>");
      row.append(`<td>${franja.inicio} - ${franja.fin}</td>`);

      dias.forEach((dia) => {
        const cell = $("<td>")
          .addClass("celda-horario")
          .attr("data-franja-inicio", franja.inicio)
          .attr("data-dia-nombre", dia); // dia será "Miercoles" aquí

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

    $(".celda-horario")
      .off("click")
      .on("click", function () {
        currentClickedCell = $(this);

        const franjaInicio = $(this).data("franja-inicio");
        const diaNombre = $(this).data("dia-nombre"); // Capturará "Miercoles" correctamente
        const franjaCompleta = franjasHorarias.find((f) => f.inicio === franjaInicio);

        $("#modalFranjaHoraria").val(`${franjaCompleta.inicio} - ${franjaCompleta.fin}`);
        $("#modalDia").val(diaNombre); // Se usa en el modal de entrada de clase

        $("#modalSeleccionarEspacio").val("");
        $("#modalSeleccionarUc").val("");
        $("#modalSeleccionarSeccion").val("");
        $("#modalSeleccionarDocente").val("");
        $("#btnEliminarEntrada").hide();

        if (currentClickedCell.data("horario-data")) {
          const data = currentClickedCell.data("horario-data");
          $("#modalSeleccionarEspacio").val(data.esp_id);
          $("#modalSeleccionarUc").val(data.uc_id);
          $("#modalSeleccionarSeccion").val(data.sec_id);
          $("#modalSeleccionarDocente").val(data.doc_id);
          $("#btnEliminarEntrada").show();
        }
        $("#modalEntradaHorario").modal("show");
      });
}


$(document).ready(function () {
  Listar(); // Esta lista actualmente filtra por dia = ''
  cargarSelects();
  inicializarTablaHorario();

  function cargarSelects() {
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

          function fillSelect(selector, options, valueKey, textKey1, textKey2 = null) {
            const select = $(selector);
            select.empty().append('<option value="">Seleccionar</option>');

            if (options && Array.isArray(options)) { // Verificar que options sea un array
                options.forEach(item => {
                  let text = item[textKey1];
                  if (textKey2 && item[textKey2]) {
                    text += ` - ${item[textKey2]}`;
                  }
                  select.append(`<option value="${item[valueKey]}">${text}</option>`);
                });
            }
          }

          fillSelect("#modalSeleccionarEspacio", data.espacios || [], "esp_id", "esp_codigo", "esp_tipo");
          fillSelect("#modalSeleccionarUc", data.ucs || [], "uc_id", "uc_codigo", "uc_nombre");
          fillSelect("#modalSeleccionarSeccion", data.secciones || [], "sec_id", "sec_codigo");
          fillSelect("#modalSeleccionarDocente", data.docentes || [], "doc_id", "doc_nombre", "doc_apellido");

          fillSelect("#esp_id", data.espacios || [], "esp_id", "esp_codigo", "esp_tipo");
          fillSelect("#uc_id", data.ucs || [], "uc_id", "uc_codigo", "uc_nombre");
          fillSelect("#sec_id", data.secciones || [], "sec_id", "sec_codigo");
          fillSelect("#doc_id", data.docentes || [], "doc_id", "doc_nombre", "doc_apellido");
          
          $("#hor_fase").empty().append('<option value="">Seleccionar Fase</option>');
          $("#hor_fase").append('<option value="1">1</option>');
          $("#hor_fase").append('<option value="2">2</option>');
          // Si tu ENUM para hor_fase en tbl_horario solo tiene '1', '2', entonces '3' no sería válido
          // Revisa la definición de tbl_horario.hor_fase ENUM('1','2') en tu SQL.
          // Si solo es 1 y 2, la opción 3 debería quitarse.
          // $("#hor_fase").append('<option value="3">3</option>'); // Comentado, verificar ENUM

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

  $("#btnAnadirFranja").on("click", function () {
    Swal.fire({
      title: "Nueva Franja Horaria",
      html: `
        <div class="mb-3">
          <label for="nuevaHoraInicio" class="form-label">Hora Inicio</label>
          <input type="time" class="form-control" id="nuevaHoraInicio" required>
        </div>
        <div class="mb-3">
          <label for="nuevaHoraFin" class="form-label">Hora Fin</label>
          <input type="time" class="form-control" id="nuevaHoraFin" required>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: "Añadir",
      cancelButtonText: "Cancelar",
      preConfirm: () => {
        const inicio = $('#nuevaHoraInicio').val();
        const fin = $('#nuevaHoraFin').val();
        
        if (!inicio || !fin) {
          Swal.showValidationMessage('Debe completar ambos campos');
          return false;
        }
        
        if (inicio >= fin) {
          Swal.showValidationMessage('La hora fin debe ser mayor a la hora inicio');
          return false;
        }
        
        const exists = franjasHorarias.some(f => f.inicio === inicio && f.fin === fin);
        if (exists) {
            Swal.showValidationMessage('Esta franja horaria ya existe.');
            return false;
        }
        return { inicio, fin };
      },
    }).then((result) => {
      if (result.isConfirmed) {
        franjasHorarias.push({ inicio: result.value.inicio, fin: result.value.fin });
        inicializarTablaHorario();
      }
    });
  });

  $("#formularioEntradaHorario").on("submit", function (e) {
    e.preventDefault();

    const espacio_id = $("#modalSeleccionarEspacio").val();
    const espacio_texto = $("#modalSeleccionarEspacio option:selected").text();
    const uc_id = $("#modalSeleccionarUc").val();
    const uc_texto = $("#modalSeleccionarUc option:selected").text();
    const seccion_id = $("#modalSeleccionarSeccion").val();
    const seccion_texto = $("#modalSeleccionarSeccion option:selected").text();
    const docente_id = $("#modalSeleccionarDocente").val();
    const docente_texto = $("#modalSeleccionarDocente option:selected").text();

    if (!espacio_id || !uc_id || !seccion_id || !docente_id) {
      muestraMensaje("error", 4000, "ERROR", "Debe completar todos los campos obligatorios (Espacio, Unidad Curricular, Sección, Docente).");
      return;
    }

    const franjaInicio = currentClickedCell.data("franja-inicio");
    const diaNombre = currentClickedCell.data("dia-nombre"); // Este diaNombre vendrá de la celda, ya corregido ("Miercoles")
    const franjaCompleta = franjasHorarias.find((f) => f.inicio === franjaInicio);

    const horarioData = {
      esp_id: espacio_id,
      uc_id: uc_id,
      sec_id: seccion_id,
      doc_id: docente_id,
      hora_inicio: franjaCompleta.inicio,
      hora_fin: franjaCompleta.fin,
      dia: diaNombre // Se guarda el día correcto aquí
    };
    currentClickedCell.data("horario-data", horarioData);

    const uc_nombre_solo = uc_texto.includes(" - ") ? uc_texto.split(" - ").slice(1).join(" - ") : uc_texto;
    const esp_codigo_solo = espacio_texto.includes(" - ") ? espacio_texto.split(" - ")[0] : espacio_texto;
    const cellContent = `
      <p style="margin-bottom: 2px; font-size: 0.9em;"><strong>${uc_nombre_solo}</strong></p>
      <small style="font-size: 0.8em;">${esp_codigo_solo}</small><br>
      <small style="font-size: 0.8em;">${seccion_texto}</small><br>
      <small style="font-size: 0.8em;">${docente_texto}</small>
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

      const franjaInicio = currentClickedCell.data("franja-inicio");
      const diaNombre = currentClickedCell.data("dia-nombre");
      const key = `${franjaInicio}-${diaNombre}`;
      horarioContenidoGuardado.delete(key);

      $("#modalEntradaHorario").modal("hide");
    }
  });


  $("#proceso").on("click", async function () {
    const accionActual = $(this).text();

    if (accionActual === "REGISTRAR") {
        const faseGlobal = $("#hor_fase").val();
        if (!faseGlobal) {
            muestraMensaje("error", 4000, "ERROR", "Debe seleccionar la fase del horario antes de registrar.");
            return;
        }

        if (horarioContenidoGuardado.size === 0) {
            muestraMensaje("error", 4000, "ERROR", "Debe añadir al menos una clase al horario para registrar.");
            return;
        }

        let erroresEncontrados = false;
        let mensajesError = [];
        const promesasRegistro = [];
        const clasesARegistrar = Array.from(horarioContenidoGuardado.values());

        for (const clase of clasesARegistrar) {
            const claseData = clase.data;

            // console.log("Intentando registrar clase con dia:", claseData.dia); // Para depuración

            if (!claseData || !claseData.esp_id || !claseData.dia || !claseData.hora_inicio || !claseData.hora_fin || !claseData.sec_id || !claseData.uc_id || !claseData.doc_id) {
                erroresEncontrados = true;
                mensajesError.push(`Error: Datos incompletos para una clase (${claseData.dia || 'Día Desc.'} ${claseData.hora_inicio || 'Hora Desc.'}).`);
                continue;
            }

            const datosClase = new FormData();
            datosClase.append("accion", "registrar");
            datosClase.append("esp_id", claseData.esp_id);
            datosClase.append("hor_fase", faseGlobal);
            datosClase.append("dia", claseData.dia); // Se envía el día corregido ("Miercoles")
            datosClase.append("hora_inicio", claseData.hora_inicio);
            datosClase.append("hora_fin", claseData.hora_fin);
            datosClase.append("sec_id", claseData.sec_id);
            datosClase.append("uc_id", claseData.uc_id);
            datosClase.append("doc_id", claseData.doc_id);

            promesasRegistro.push(
                $.ajax({
                    url: "", 
                    type: "POST",
                    contentType: false,
                    data: datosClase,
                    processData: false,
                    cache: false
                }).then(function(respuesta) { 
                    try {
                        const lee = JSON.parse(respuesta);
                        if (lee.resultado !== "registrar") {
                            erroresEncontrados = true;
                            mensajesError.push(`Clase (${claseData.uc_id} en ${claseData.dia} ${claseData.hora_inicio}): ${lee.mensaje}`);
                        }
                    } catch (e) {
                        erroresEncontrados = true;
                        mensajesError.push(`Respuesta inválida del servidor para clase (${claseData.dia} ${claseData.hora_inicio}): ${e.message}. Respuesta: ${respuesta}`);
                    }
                }, function(jqXHR, textStatus, errorThrown) { 
                    erroresEncontrados = true;
                    mensajesError.push(`Error de comunicación para clase (${claseData.dia} ${claseData.hora_inicio}): ${textStatus} - ${errorThrown}`);
                })
            );
        }

        try {
            await Promise.all(promesasRegistro);

            if (!erroresEncontrados) {
                muestraMensaje("success", 4000, "REGISTRO COMPLETO", "Todas las clases del horario han sido registradas correctamente.");
                $("#modal-horario").modal("hide");
                limpia();
                Listar(); // Recarga la lista (que puede estar filtrada por dia='')
            } else {
                muestraMensaje("error", 15000, "REGISTRO CON ERRORES", "Algunas clases no pudieron ser registradas. Detalles: \n" + mensajesError.join("\n") + "\n\nEl formulario de horario se limpiará. Por favor, revise los datos e intente de nuevo con las clases que faltaron.");
                limpia();
            }
        } catch (error) { 
            muestraMensaje("error", 10000, "ERROR GENERAL DE REGISTRO", "Ocurrió un error inesperado durante el proceso de registro: " + error.message + ". El formulario se limpiará.");
            console.error("Error en Promise.all:", error);
            limpia();
        }
        return;
    } 


    if (!validarenvio()) { 
        return;
    }

    var datosFormularioPrincipal = new FormData($('#form-horario')[0]);
    
    if (accionActual === "MODIFICAR") {
      datosFormularioPrincipal.append("accion", "modificar");
      if (!$("#hor_id").val()) { 
          muestraMensaje("error", 4000, "ERROR", "ID de horario (bloque) no encontrado para modificar.");
          return;
      }
      enviaAjax(datosFormularioPrincipal);

    } else if (accionActual === "ELIMINAR") {
      Swal.fire({
        title: "¿Está seguro de eliminar este bloque de horario y todas sus clases asociadas?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          var datosEliminar = new FormData();
          datosEliminar.append("accion", "eliminar");
          datosEliminar.append("hor_id", $("#hor_id").val()); 
          enviaAjax(datosEliminar);
        } else {
          muestraMensaje( "info", 2000, "CANCELADO", "La eliminación ha sido cancelada.");
        }
      });
      return;
    }
  });

  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#fase-group").show();
    $(".form-group-horario:not(#fase-group)").hide();
    $("#btnAnadirFranja").show();
    $("#tablaHorario1").show();
    $("#modal-horario").modal("show");
    $("#shora_inicio").hide().text("");
    $("#shora_fin").hide().text("");
  });
});


function validarHora(campo, span, mensaje) {
  if (!/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(campo.val())) {
    span.text(mensaje).addClass("text-danger").show();
    return false;
  } else {
    span.text("").removeClass("text-danger").hide();
    return true;
  }
}

function validarRangoHoras() {
  var inicio = $("#hora_inicio").val();
  var fin = $("#hora_fin").val();
  var spanFin = $("#shora_fin");

  if (inicio && fin) { 
    if (inicio >= fin) {
      spanFin.text("La hora fin debe ser mayor a la hora inicio").addClass("text-danger").show();
      return false;
    } else {
      spanFin.text("").removeClass("text-danger").hide();
      return true;
    }
  }
  spanFin.text("").removeClass("text-danger").hide(); 
  return true; 
}


function validarenvio() {
  if ($("#proceso").text() === "REGISTRAR") {
      return true;
  }

  var camposSelect = [
    {selector: "#esp_id", nombre: "Espacio"},
    {selector: "#hor_fase", nombre: "Fase"},
    {selector: "#dia", nombre: "Día"},
    {selector: "#sec_id", nombre: "Sección"},
    {selector: "#uc_id", nombre: "Unidad curricular"},
    {selector: "#doc_id", nombre: "Docente"}
  ];
  var valido = true;
  
  camposSelect.forEach(function (campo) {
    if (!$(campo.selector).val()) {
      muestraMensaje("error", 4000, "CAMPO REQUERIDO", `Por favor, seleccione un valor para ${campo.nombre}.`);
      valido = false;
      return false; 
    }
  });
  
  if (!valido) return false;

  if ($("#hora-inicio-group").is(":visible")) {
    if (!validarHora($("#hora_inicio"), $("#shora_inicio"), "Formato de hora inicio inválido (HH:MM)")) {
        $("#hora_inicio").focus();
        return false;
    }
  }
  if ($("#hora-fin-group").is(":visible")) {
    if (!validarHora($("#hora_fin"), $("#shora_fin"), "Formato de hora fin inválido (HH:MM)")) {
        $("#hora_fin").focus();
        return false;
    }
  }
  if ($("#hora-inicio-group").is(":visible") && $("#hora-fin-group").is(":visible")) {
    if (!validarRangoHoras()) {
        $("#hora_fin").focus();
        return false;
    }
  }
  return true;
}

function pone(elementoHTML, accion) { 
  const idHorarioOClase = $(elementoHTML).data("id");

  horarioContenidoGuardado.clear();
  inicializarTablaHorario(); 
  $("#form-horario")[0].reset(); 

  $(".form-group-horario").show(); 
  $("#fase-group").show();         
  $("#btnAnadirFranja").hide();   
  $("#tablaHorario1").hide();      

  $("#hor_id").val(idHorarioOClase); 

  let urlDestino = ""; 

  if (accion === 0) { 
    $("#proceso").text("MODIFICAR");
    $("#esp_id, #hor_fase, #dia, #hora_inicio, #hora_fin, #sec_id, #uc_id, #doc_id").prop('disabled', false);
    $("#shora_inicio").hide().text("");
    $("#shora_fin").hide().text("");

    $.post(urlDestino, { accion: "ver_horario", hor_id: idHorarioOClase }, function (response) {
      try {
        const parsedResponse = JSON.parse(response);
        if (parsedResponse.resultado === "consultar" && parsedResponse.mensaje) {
          const horario = parsedResponse.mensaje;
          $("#esp_id").val(horario.esp_id);
          $("#hor_fase").val(horario.fase);
          $("#dia").val(horario.dia); // Aquí se carga el día. Asegúrate que coincida con los valores del ENUM ("Miercoles")
          $("#hora_inicio").val(horario.hor_inicio);
          $("#hora_fin").val(horario.hor_fin);
          $("#sec_id").val(horario.sec_id);
          $("#uc_id").val(horario.uc_id);
          $("#doc_id").val(horario.doc_id);
          
          $("#modal-horario").modal("show");
        } else {
          muestraMensaje("error", 4000, "ERROR AL CARGAR", parsedResponse.mensaje || "No se pudieron cargar los datos para modificar.");
        }
      } catch (e) {
        console.error("Error al parsear JSON (ver_horario modificar):", e, response);
        muestraMensaje("error", 5000, "ERROR DE DATOS", "Respuesta inválida del servidor al cargar horario. Consulte consola.");
      }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error AJAX (ver_horario modificar):", textStatus, errorThrown, jqXHR.responseText);
        muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", "No se pudo cargar el horario para modificar.");
    });

  } else if (accion === 1) { 
    $("#proceso").text("ELIMINAR");
    $("#esp_id, #hor_fase, #dia, #hora_inicio, #hora_fin, #sec_id, #uc_id, #doc_id").prop('disabled', true);
    
    $.post(urlDestino, { accion: "ver_horario", hor_id: idHorarioOClase }, function (response) {
      try {
        const parsedResponse = JSON.parse(response);
        if (parsedResponse.resultado === "consultar" && parsedResponse.mensaje) {
          const horario = parsedResponse.mensaje;
          $("#esp_id").val(horario.esp_id);
          $("#hor_fase").val(horario.fase);
          $("#dia").val(horario.dia); // Asegúrate que coincida con los valores del ENUM
          $("#hora_inicio").val(horario.hor_inicio);
          $("#hora_fin").val(horario.hor_fin);
          $("#sec_id").val(horario.sec_id);
          $("#uc_id").val(horario.uc_id);
          $("#doc_id").val(horario.doc_id);
          
          $("#modal-horario").modal("show"); 
        } else {
          muestraMensaje("error", 4000, "ERROR AL CARGAR", parsedResponse.mensaje || "No se pudieron cargar los datos para eliminar.");
        }
      } catch (e) {
        console.error("Error al parsear JSON (ver_horario eliminar):", e, response);
        muestraMensaje("error", 5000, "ERROR DE DATOS", "Respuesta inválida del servidor al cargar datos para eliminar.");
      }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Error AJAX (ver_horario eliminar):", textStatus, errorThrown, jqXHR.responseText);
        muestraMensaje("error", 5000, "ERROR DE CONEXIÓN", "No se pudo cargar el horario para eliminar.");
    });
  }
}


function enviaAjax(datos) {
  $.ajax({
    async: true,
    url: "", 
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
    timeout: 10000, 
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado == "consultar") {
          destruyeDT();
          // Vacía el tbody de la tabla antes de llenarlo
          $("#resultadoconsulta").empty();

          if (Array.isArray(lee.mensaje) && lee.mensaje.length > 0) {
            $.each(lee.mensaje, function (index, item) {
              let idParaAccion = item.hor_id; 
              $("#resultadoconsulta").append(`
                <tr>
                  <td>${item.espacio || ''}</td>
                  <td>${item.fase || ''}</td>
                  <td>${item.seccion || ''}</td>
                  <td>${ (item.cod_unidad_curricular ? item.cod_unidad_curricular + ' - ' : '') + (item.unidad_curricular || '')}</td>
                  <td>${item.docente || ''}</td>
                  <td>
                    <button class="btn btn-warning btn-sm editar-horario" data-id="${idParaAccion}" onclick='pone(this,0)'>Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar-horario" data-id="${idParaAccion}" onclick='pone(this,1)'>Eliminar</button>
                  </td>
                </tr>
              `);
            });
          } else {
            // Solo una fila con colspan=6 si no hay datos
            $("#resultadoconsulta").append('<tr><td colspan="6" class="text-center">No hay horarios para mostrar (o que coincidan con el filtro actual si existe).</td></tr>');
          }
          crearDT();
        } else if (lee.resultado == "registrar") {
          // No se necesita mensaje individual aquí si el global funciona bien
        } else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICACIÓN", lee.mensaje);
          if (lee.mensaje && lee.mensaje.toLowerCase().includes("correctamente")) { 
            $("#modal-horario").modal("hide");
            limpia(); 
            Listar();
          }
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINACIÓN", lee.mensaje);
           if (lee.mensaje && lee.mensaje.toLowerCase().includes("correctamente")) {
            $("#modal-horario").modal("hide");
            limpia(); 
            Listar();
          }
        } else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR DE OPERACIÓN", lee.mensaje);
        } else {
           muestraMensaje("warning", 5000, "RESPUESTA DESCONOCIDA", lee.mensaje || "El servidor envió una respuesta no estándar.");
        }
      } catch (e) {
        // Solo mostrar error si el JSON es realmente inválido
        console.error("Error en análisis JSON o lógica de success AJAX:", e);
        console.log("Respuesta cruda: " + respuesta);
        muestraMensaje("error", 10000, "ERROR DE RESPUESTA", "La respuesta del servidor no pudo ser procesada. Consulte la consola. Raw: " + (typeof respuesta === 'string' ? respuesta.substring(0, 200) : ''));
      }
    },
    error: function (request, status, err) {
      console.error("Error en la petición AJAX:", status, err, request.responseText);
      if (status == "timeout") {
        muestraMensaje("error", 5000, "SERVIDOR OCUPADO", "El servidor tardó demasiado en responder, intente de nuevo.");
      } else {
        muestraMensaje("error", 5000, "ERROR DE CONEXIÓN", `Error (${request.status}): ${err}. Consulte la consola.`);
      }
    },
  });
}

function limpia() {
  $("#form-horario")[0].reset();
  $("#hor_id").val("");
  
  $("#esp_id, #hor_fase, #dia, #hora_inicio, #hora_fin, #sec_id, #uc_id, #doc_id").prop('disabled', false);
  
  $(".form-group-horario").hide();
  $("#fase-group").show();
  $("#btnAnadirFranja").show();
  $("#tablaHorario1").show();

  horarioContenidoGuardado.clear();
  inicializarTablaHorario();

  $("#shora_inicio").text("").removeClass("text-danger").hide();
  $("#shora_fin").text("").removeClass("text-danger").hide();
}