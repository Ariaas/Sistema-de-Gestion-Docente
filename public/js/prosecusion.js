function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT(tablaseccion) {

  if ($.fn.DataTable.isDataTable(tablaseccion)) {
    $(tablaseccion).DataTable().destroy();
  }
}

function crearDT(tablaseccion) {
  if (!$.fn.DataTable.isDataTable(tablaseccion)) {
    $(tablaseccion).DataTable({
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
      order: [[1, "asc"]],
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

function verificarRequisitosIniciales() {
  var datos = new FormData();
  datos.append("accion", "verificar_estado");
  $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        let warning = "";
        const prosecusionBtn = $("#btnProsecusion");

        if (!lee.anio_destino_existe && lee.anio_activo_existe) {
          warning = "No existe el año " + (lee.anio_activo + 1) + " en el sistema. Cree el año antes de realizar una prosecusión.";
          prosecusionBtn.prop("disabled", true).attr("title", warning);
          $("#prosecusion-warning").text(warning).show();
        } else if (prosecusionBtn.is(':disabled') && !prosecusionBtn.attr('title')) {
          warning = "No tiene permisos para realizar una prosecusión.";
          prosecusionBtn.prop("disabled", true).attr("title", warning);
          $("#prosecusion-warning").text(warning).show();
        } else {
          prosecusionBtn.prop("disabled", false).attr("title", "");
          $("#prosecusion-warning").text("").hide();
        }
      } catch (e) {
        console.error("Error al procesar la respuesta de verificación:", e, respuesta);
      }
    }
  });
}

function validarExiste() {
  const codigo = $("#codigoSeccion").val();
  const trayecto = $("#trayectoSeccion").val();
  const nombre = $("#nombreSeccion").val();
  if (codigo && trayecto) {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('codigoSeccion', codigo);
    datos.append('trayectoSeccion', trayecto);
    datos.append('nombreSeccion', nombre);
    enviaAjax(datos);
  }
}

$(document).ready(function () {
  Listar();
  verificarRequisitosIniciales();

  destruyeDT("#tablaseccion");
  crearDT("#tablaseccion");

  $('#modalProsecusion').on('hidden.bs.modal', function () {
    if ($("#origenProsecusion").hasClass("select2-hidden-accessible")) {
      $("#origenProsecusion").select2('destroy');
    }
    if ($("#destinoManual").hasClass("select2-hidden-accessible")) {
      $("#destinoManual").select2('destroy');
    }
  });
});

let modoProsecusion = "automatico";
$(document).on("click", ".prosecusionar", function () {
  let seccionOrigenId = $(this).data("id");
  let codigoOrigen = $(this).data("codigo");
  let cantidadOrigen = $(this).data("cantidad");
  let anioOrigen = $(this).data("anio");

  Swal.fire({
    title: "Tipo de prosecusión",
    text: "¿Desea prosecusión automática o manual?",
    icon: "question",
    showDenyButton: true,
    confirmButtonText: "Automática",
    denyButtonText: "Manual"
  }).then((result) => {
    if (result.isConfirmed) {
    } else if (result.isDenied) {
    }
  });
});

function alertaCapacidadMaxima(destinoId) {
  var datos = new FormData();
  datos.append("accion", "calcularCantidadProsecusion");
  datos.append("seccionId", destinoId);
  $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      let res = JSON.parse(respuesta);
      if (res.cantidad > 45) {
        Swal.fire("Atención!", "La sección destino supera la capacidad máxima de 45 alumnos.", "warning");
      }
    }
  });
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
    beforeSend: function () { },
    timeout: 10000,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT("#tablaseccion");
          $("#resultadoconsulta1").empty();
          $.each(lee.mensaje, function (index, item) {
            const pro_id = `${item.origen_codigo}-${item.ani_origen}-${item.destino_codigo}-${item.ani_destino}`;
            $("#resultadoconsulta1").append(`
              <tr>
                <td>${item.origen_codigo} (${item.pro_cantidad} estudiantes)</td>
                <td>${item.ani_origen}</td>
                <td>${item.destino_codigo}</td>
                <td>${item.ani_destino}</td>
                <td>
                  <button class="btn btn-icon btn-delete eliminar-prosecusion" title="Eliminar prosecusión" data-id="${pro_id}">
                    <img src="public/assets/icons/trash.svg" alt="Eliminar">
                  </button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaseccion");
        } else if (lee.resultado === 'opcionesDestinoManual') {
          const selectDestino = $("#destinoManual");

          if (selectDestino.hasClass("select2-hidden-accessible")) {
            selectDestino.select2('destroy');
          }

          selectDestino.empty();
          $("#destinoManualContainer").show();

          $("#mensaje-manual").remove();

          if (lee.mensaje.length > 0) {
            lee.mensaje.forEach(function (opcion) {
              selectDestino.append(new Option(`${opcion.sec_codigo} (${opcion.ani_anio})`, opcion.sec_codigo));
            });
            $("#confirmarProsecusion").prop("disabled", false);
          } else {
            selectDestino.append(new Option("No hay secciones disponibles", ""));
            $("#confirmarProsecusion").prop("disabled", true);
            selectDestino.after(`<div id="mensaje-manual" class="text-danger mt-1" style="font-size: 0.875em;">No hay sección de destino con las características necesarias</div>`);
          }

          selectDestino.select2({
            dropdownParent: $('#modalProsecusion'),
            theme: "bootstrap-5",
            placeholder: "Seleccione una sección destino",
            width: '100%'
          });
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
          $("#tipoProsecusion").val("automatico");
          $("#destinoManualContainer").hide();
          Listar();
        } else if (lee.resultado === "prosecusion") {
          $('#modalProsecusion').modal('hide');
          muestraMensaje("success", 4000, "PROSECUSIÓN", lee.mensaje);
          Listar();
        } else if (lee.resultado === 'confirmacion_requerida') {
          Swal.fire({
            title: 'Confirmación Requerida',
            text: lee.mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
          }).then((result) => {
            if (result.isConfirmed) {
              const tipo = $("#tipoProsecusion").val();
              const seccionOrigenCodigo = $("#origenProsecusion").val();
              const cantidad = $("#cantidadProsecusion").val();
              let datos = new FormData();
              datos.append("accion", "prosecusion");
              datos.append("seccionOrigenCodigo", seccionOrigenCodigo);
              datos.append("cantidad", cantidad);
              datos.append("confirmar_exceso", "true");

              if (tipo === "manual") {
                const destinoCodigo = $("#destinoManual").val();
                datos.append("seccionDestinoCodigo", destinoCodigo);
              }
              enviaAjax(datos);
            }
          });
        }
      } catch (e) {
        console.error("Error al procesar respuesta AJAX:", e, respuesta);
        muestraMensaje("error", 3000, "ERROR", "Error procesando respuesta del servidor");
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("Servidor ocupado, intente de nuevo");
      } else {
        muestraMensaje("ERROR: <br/>" + request + status + err);
      }
    },
    complete: function () { },
  });
}

$(document).on("click", ".eliminar-prosecusion", function () {
  const pro_id = $(this).data("id");
  Swal.fire({
    title: '¿Está seguro?',
    text: "Esta acción eliminará la prosecusión y devolverá los estudiantes a la sección destino. ¡No se puede deshacer!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      var datos = new FormData();
      datos.append("accion", "eliminar");
      datos.append("pro_id", pro_id);
      enviaAjax(datos);
    }
  });
});

$(document).on("click", "#btnProsecusion", function () {
  if ($(this).is(':disabled')) {
    const warningText = $("#prosecusion-warning").text();
    Swal.fire({
      icon: 'error',
      title: 'Acción no permitida',
      text: warningText || 'No se puede realizar una prosecusión en este momento.'
    });
    return;
  }
  var datos = new FormData();
  datos.append("accion", "consultarSeccionesOrigen");
  $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      $("#tipoProsecusion").val("automatico");
      $("#destinoManualContainer").hide();
      $("#mensaje-automatico, #mensaje-manual").remove();

      let lee = JSON.parse(respuesta);
      let $select = $("#origenProsecusion");

      if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2('destroy');
      }

      $select.empty();
      if (lee.resultado === "consultarSeccionesOrigen" && lee.mensaje.length > 0) {
        lee.mensaje.forEach(function (item) {
          const disponible = item.sec_cantidad - (item.cantidad_prosecusionada || 0);
          $select.append(`<option value="${item.sec_codigo}">${item.sec_codigo} (${item.ani_anio}) - ${disponible} estudiantes disponibles</option>`);
        });
        $("#confirmarProsecusion").prop("disabled", true);
      } else {
        $select.append('<option value="">No hay secciones disponibles</option>');
        $("#cantidadProsecusion").val(0);
        $("#confirmarProsecusion").prop("disabled", true);
        $select.after(`<div id="mensaje-sin-secciones" class="text-danger mt-2" style="font-size: 0.875em;">No hay secciones válidas para prosecusionar. Todas las secciones tienen 0 estudiantes.</div>`);
      }

      $select.select2({
        dropdownParent: $('#modalProsecusion'),
        theme: "bootstrap-5",
        placeholder: "Seleccione una sección",
        width: '100%'
      });

      $select.trigger('change');
      $('#modalProsecusion').modal('show');
    }
  });
});

$("#origenProsecusion").on("change", function () {
  actualizarCantidadProsecusion();

  const tipoProsecusion = $("#tipoProsecusion").val();
  if (tipoProsecusion === "manual") {
    cargarOpcionesDestinoManual();
  } else {
    verificarDestinoAutomatico();
  }
});

function verificarDestinoAutomatico() {
  const seccionOrigenCodigo = $("#origenProsecusion").val();
  if (!seccionOrigenCodigo) return;

  const datos = new FormData();
  datos.append("accion", "verificarDestinoAutomatico");
  datos.append("seccionOrigenCodigo", seccionOrigenCodigo);

  $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      let res = JSON.parse(respuesta);

      $("#mensaje-automatico").remove();

      if (res.existe) {
        $("#confirmarProsecusion").prop("disabled", false);
      } else {
        $("#confirmarProsecusion").prop("disabled", true);
        $("#tipoProsecusion").after(`<div id="mensaje-automatico" class="text-danger mt-1" style="font-size: 0.875em;">No hay sección de destino automática disponible para esta sección</div>`);
      }
    }
  });
}

function actualizarCantidadProsecusion() {
  const seccionOrigenCodigo = $("#origenProsecusion").val();
  if (!seccionOrigenCodigo) return;

  var datos = new FormData();
  datos.append("accion", "calcularCantidadProsecusion");
  datos.append("seccionCodigo", seccionOrigenCodigo);

  $.ajax({
    url: "",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
    success: function (respuesta) {
      let res = JSON.parse(respuesta);
      $("#cantidadProsecusion").next('.validation-message').remove();

      if (res.puede_prosecusionar) {
        $("#cantidadProsecusion").val(res.cantidad_final);
        $("#confirmarProsecusion").prop("disabled", false);
      } else {
        $("#cantidadProsecusion").val(0);
        $("#confirmarProsecusion").prop("disabled", true);
        $("#cantidadProsecusion").after(`<div class="text-danger validation-message" style="font-size: 0.875em; margin-top: 0.25rem;">${res.mensaje}</div>`);
      }
    }
  });
}

function cargarOpcionesDestinoManual() {
  const seccionOrigenCodigo = $("#origenProsecusion").val();
  if (!seccionOrigenCodigo) {
    $("#destinoManualContainer").hide();
    return;
  }

  const datos = new FormData();
  datos.append("accion", "obtenerOpcionesDestinoManual");
  datos.append("seccionOrigenCodigo", seccionOrigenCodigo);

  $.ajax({
    async: true,
    url: "",
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
  }).done(function (respuesta) {
    const lee = JSON.parse(respuesta);
    const selectDestino = $("#destinoManual");

    if (selectDestino.hasClass("select2-hidden-accessible")) {
      selectDestino.select2('destroy');
    }

    selectDestino.empty();
    $("#destinoManualContainer").show();

    $("#mensaje-manual").remove();

    if (lee.resultado === 'opcionesDestinoManual' && lee.mensaje.length > 0) {
      lee.mensaje.forEach(function (opcion) {
        selectDestino.append(new Option(`${opcion.sec_codigo} (${opcion.ani_anio})`, opcion.sec_codigo));
      });
      $("#confirmarProsecusion").prop("disabled", false);

      selectDestino.select2({
        dropdownParent: $('#modalProsecusion'),
        theme: "bootstrap-5",
        placeholder: "Seleccione una sección destino",
        width: '100%'
      });
    } else {
      selectDestino.append(new Option("No hay secciones disponibles", ""));
      $("#confirmarProsecusion").prop("disabled", true);
      $("#destinoManual").after(`<div id="mensaje-manual" class="text-danger mt-1" style="font-size: 0.875em;">No hay sección de destino con las características necesarias</div>`);
    }
  }).fail(function () {
    $("#destinoManualContainer").hide();
    muestraMensaje("error", 4000, "ERROR!", "No se pudo contactar al servidor.");
  });
}


$("#tipoProsecusion").on("change", function () {
  $("#mensaje-automatico, #mensaje-manual").remove();

  if ($(this).val() === "manual") {
    cargarOpcionesDestinoManual();
  } else {
    $("#destinoManualContainer").hide();
    const seccionOrigenCodigo = $("#origenProsecusion").val();
    if (seccionOrigenCodigo) {
      verificarDestinoAutomatico();
    }
  }
});

$("#confirmarProsecusion").on("click", function () {
  const tipo = $("#tipoProsecusion").val();
  const seccionOrigenCodigo = $("#origenProsecusion").val();
  const cantidad = $("#cantidadProsecusion").val();
  let datos = new FormData();
  datos.append("accion", "prosecusion");
  datos.append("seccionOrigenCodigo", seccionOrigenCodigo);
  datos.append("cantidad", cantidad);

  if (tipo === "manual") {
    const destinoCodigo = $("#destinoManual").val();
    if (!destinoCodigo) {
      muestraMensaje("error", 3000, "ERROR", "Debe seleccionar una sección destino.");
      return;
    }
    datos.append("seccionDestinoCodigo", destinoCodigo);
  }

  enviaAjax(datos);
  $("#modalProsecusion").modal("hide");
});