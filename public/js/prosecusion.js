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

                if (prosecusionBtn.is(':disabled') && !prosecusionBtn.attr('title')) {
                     warning = "No tiene permisos para realizar una prosecusión.";
                } else if (lee.anio_activo_existe) {
                    warning = "No debe haber un año académico activo.";
                }

                if (warning) {
                    prosecusionBtn.prop("disabled", true).attr("title", warning);
                    $("#prosecusion-warning").text(warning);
                } else {
                    prosecusionBtn.prop("disabled", false).attr("title", "");
                    $("#prosecusion-warning").text("");
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
    beforeSend: function () {},
    timeout: 10000, 
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT("#tablaseccion");
          $("#resultadoconsulta1").empty();
          $.each(lee.mensaje, function (index, item) {
            const pro_id = `${item.origen_codigo}-${item.destino_codigo}`;
            $("#resultadoconsulta1").append(`
              <tr>
                <td>${item.origen_codigo}</td>
                <td>${item.origen_anio}</td>
                <td>${item.destino_codigo}</td>
                <td>${item.destino_anio}</td>
                <td>
                  <button class="btn btn-danger btn-sm eliminar-prosecusion" data-id="${pro_id}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaseccion");
        } else if (lee.resultado === 'opcionesDestinoManual') {
            const selectDestino = $("#destinoManual");
            selectDestino.empty();
            $("#destinoManualContainer").show();

            if (lee.mensaje.length > 0) {
                lee.mensaje.forEach(function(opcion) {
                    selectDestino.append(new Option(`${opcion.sec_codigo} (${opcion.ani_anio})`, opcion.sec_codigo));
                });
                $("#confirmarProsecusion").prop("disabled", false);
            } else {
                selectDestino.append(new Option("No hay destino válido", ""));
                $("#confirmarProsecusion").prop("disabled", true);
            }
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          $("#tipoProsecusion").val("automatico");
          $("#destinoManualContainer").hide();
          Listar();
        } else if (lee.resultado === "prosecusion") {
          $('#modalProsecusion').modal('hide');
          muestraMensaje("info", 4000, "PROSECUSIÓN", lee.mensaje);
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
                    datos.append('confirmar_exceso', 'true');
                    enviaAjax(datos);
                }
            });
        } else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e, respuesta); 
        alert("Error en JSON " + e.name + ": " + e.message + "\nRespuesta: " + respuesta);
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("Servidor ocupado, intente de nuevo");
      } else {
        muestraMensaje("ERROR: <br/>" + request + status + err);
      }
    },
    complete: function () {},
  });
}

$(document).on("click", ".eliminar-prosecusion", function() {
    const pro_id = $(this).data("id");
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará la prosecusión y reactivará la sección de origen. ¡No se puede deshacer!",
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

            let lee = JSON.parse(respuesta);
            let $select = $("#origenProsecusion");
            $select.empty();
            if (lee.resultado === "consultarSeccionesOrigen" && lee.mensaje.length > 0) {
                lee.mensaje.forEach(function(item) {
                    $select.append(`<option value="${item.sec_codigo}">${item.sec_codigo} (${item.ani_anio})</option>`);
                });
                $("#confirmarProsecusion").prop("disabled", false);
                $select.trigger('change');
            } else {
                $select.append('<option value="">No hay secciones disponibles</option>');
                $("#cantidadProsecusion").val(0);
                $("#confirmarProsecusion").prop("disabled", true);
            }
            
            $('#modalProsecusion').modal('show');
        }
    });
});

$("#origenProsecusion").on("change", function() {
    actualizarCantidadProsecusion();

    if ($("#tipoProsecusion").val() === "manual") {
        cargarOpcionesDestinoManual();
    }
});

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
        success: function(respuesta) {
            let res = JSON.parse(respuesta);
            $("#cantidadProsecusion").next('.validation-message').remove();

            if (res.puede_prosecusionar) {
                $("#cantidadProsecusion").val(res.cantidad_final);
                $("#confirmarProsecusion").prop("disabled", false); 
            } else {
                $("#cantidadProsecusion").val(0);
                $("#confirmarProsecucion").prop("disabled", true);
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
    }).done(function(respuesta) {
        const lee = JSON.parse(respuesta);
        const selectDestino = $("#destinoManual");
        selectDestino.empty();
        $("#destinoManualContainer").show();

        if (lee.resultado === 'opcionesDestinoManual' && lee.mensaje.length > 0) {
            lee.mensaje.forEach(function(opcion) {
                selectDestino.append(new Option(`${opcion.sec_codigo} (${opcion.ani_anio})`, opcion.sec_codigo));
            });
            $("#confirmarProsecusion").prop("disabled", false);
        } else {
            selectDestino.append(new Option("No hay destino válido", ""));
            $("#confirmarProsecusion").prop("disabled", true);
        }
    }).fail(function() {
        $("#destinoManualContainer").hide();
        muestraMensaje("error", 4000, "ERROR!", "No se pudo contactar al servidor.");
    });
}


$("#tipoProsecusion").on("change", function() {
    if ($(this).val() === "manual") {
        cargarOpcionesDestinoManual();
    } else {
        $("#destinoManualContainer").hide();
    }
});

$("#confirmarProsecusion").on("click", function() {
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
