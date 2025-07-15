function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablaespacio")) {
    $("#tablaespacio").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaespacio")) {
    $("#tablaespacio").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
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
      dom:
        "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });
  }
}

$(document).ready(function () {
  Listar();

  //////////////////////////////VALIDACIONES/////////////////////////////////////

  $("#codigoEspacio").on("keypress", function (e) {
    validarkeypress(/^[0-9\b]*$/, e);
  });

  $("#codigoEspacio").on("keyup keydown", function () {
    validarkeyup(
      /^[0-9]{1,4}$/,
      $(this),
      $("#scodigoEspacio"),
      "El formato permite de 1 a 4 dígitos numéricos."
    );

    if ($(this).val().length >= 1 && $("#edificio").val() !== "") {
      var prefijo = getPrefijoEdificio($("#edificio").val());
      var codigoCompleto = prefijo + $(this).val();
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("codigoEspacio", codigoCompleto);
      enviaAjax(datos, 'existe');
    }
  });

  $("#tipoEspacio").on("change", function () {

  });

  $("#edificio").on("change", function () {
    if ($("#codigoEspacio").val().length >= 1) {
      var prefijo = getPrefijoEdificio($(this).val());
      var codigoCompleto = prefijo + $("#codigoEspacio").val();
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("codigoEspacio", codigoCompleto);
      enviaAjax(datos, 'existe');
    }
    $("#scodigoEspacio").text('');
    $("#proceso").prop("disabled", false);
  });


  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
            var prefijo = getPrefijoEdificio($("#edificio").val());
            var codigoFinal = prefijo + $("#codigoEspacio").val();

            var datos = new FormData();
            datos.append("accion", "registrar");
            datos.append("codigoEspacio", codigoFinal);
            datos.append("tipoEspacio", $("#tipoEspacio").val());

            enviaAjax(datos);
        }
    } else if ($(this).text() == "MODIFICAR") {
        if ($("#tipoEspacio").val()) {
            var prefijo = getPrefijoEdificio($("#edificio").val());
            var codigoFinal = prefijo + $("#codigoEspacio").val();

            var datos = new FormData();
            datos.append("accion", "modificar");
            datos.append("codigoEspacio", codigoFinal);
            datos.append("tipoEspacio", $("#tipoEspacio").val());

            enviaAjax(datos);
        } else {
            muestraMensaje("error", 4000, "ERROR!", "Por favor, seleccione un tipo de espacio!");
        }

    } else if ($(this).text() == "ELIMINAR") {

        Swal.fire({
            title: "¿Está seguro de eliminar este espacio?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append("accion", "eliminar");

            
                var prefijo = getPrefijoEdificio($("#edificio").val());
                var codigoNumerico = $("#codigoEspacio").val();
                var codigoCompleto = prefijo + codigoNumerico;

           
                datos.append("codigoEspacio", codigoCompleto);

                enviaAjax(datos);
                
            } else {
                muestraMensaje(
                    "error",
                    2000,
                    "INFORMACIÓN",
                    "La eliminación ha sido cancelada."
                );
                $("#modal1").modal("hide");
            }
        });
    }
  });


  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
    $("#scodigoEspacio").text('');
    $("#tipoEspacio, #codigoEspacio, #edificio").prop("disabled", false);
    $("#edificio").val("");
  });

});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
  var codigoEspacio = $("#codigoEspacio").val();
  var tipoEspacio = $("#tipoEspacio").val();
  var edificio = $("#edificio").val();

  if (edificio === null || edificio === "") {
    muestraMensaje("error", 4000, "ERROR!", "Por favor, seleccione un edificio.");
    return false;
  }
  if (
    validarkeyup(
      /^[0-9]{1,4}$/,
      $("#codigoEspacio"),
      $("#scodigoEspacio"),
      "El formato permite de 1 a 4 dígitos numéricos."
    ) == 0
  ) {
    muestraMensaje("error", 4000, "ERROR!", "El código numérico del espacio <br/> No debe estar vacío y debe contener entre 1 a 4 dígitos.");
    return false;
  }
  if (tipoEspacio === null || tipoEspacio === "") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione un tipo de espacio!"
    );
    return false;
  }
  return true;
}


function getPrefijoEdificio(nombreEdificio) {
  if (nombreEdificio === "Hilandera") {
    return "H";
  } else if (nombreEdificio === "Giraluna") {
    return "G";
  } else if (nombreEdificio === "Rio 7 Estrellas") {
    return "R";
  }
  return "";
}


function separarCodigoEdificio(codigoCompleto) {
  if (codigoCompleto.length > 0) {
    const prefijo = codigoCompleto.charAt(0);
    const codigoNumerico = codigoCompleto.substring(1);
    let nombreEdificio = "";
    if (prefijo === "H") {
      nombreEdificio = "Hilandera";
    } else if (prefijo === "G") {
      nombreEdificio = "Giraluna";
    } else if (prefijo === "R") {
      nombreEdificio = "Rio 7 Estrellas";
    }
    return { prefijo: prefijo, codigoNumerico: codigoNumerico, nombreEdificio: nombreEdificio };
  }
  return { prefijo: "", codigoNumerico: "", nombreEdificio: "" };
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");

  const codigoCompleto = $(linea).find("td:eq(0)").text().trim();
  const tipo = $(linea).find("td:eq(1)").text().trim();
  const edificio = $(linea).find("td:eq(2)").text().trim();

  const { codigoNumerico } = separarCodigoEdificio(codigoCompleto);

  if (accion == 0) { 
    $("#proceso").text("MODIFICAR");
   
    $("#tipoEspacio").prop("disabled", false);
    $("#codigoEspacio, #edificio").prop("disabled", true);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#tipoEspacio, #codigoEspacio, #edificio").prop("disabled", true);
  }

 
  $("#codigoEspacio").val(codigoNumerico);
  $("#tipoEspacio").val(tipo.toLowerCase());
  $("#edificio").val(edificio);


  $("#modal1").modal("show");
  $("#scodigoEspacio").text('');
}


function enviaAjax(datos, tipo_accion_local = null) {
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
          destruyeDT();
          $("#resultadoconsulta").empty();
          let tabla = "";
          lee.mensaje.forEach(item => {
            const { prefijo, codigoNumerico, nombreEdificio } = separarCodigoEdificio(item.esp_codigo);
            tabla += `
            <tr>
                <td>${item.esp_codigo}</td>
                <td>${item.esp_tipo}</td>
                <td>${nombreEdificio}</td> <td class="text-center">
                    <button class="btn btn-icon btn-edit" onclick="pone(this, 0)" title="Modificar" ${!PERMISOS.modificar ? 'disabled' : ''}>
                        <img src="public/assets/icons/edit.svg" alt="Modificar">
                    </button>
                    <button class="btn btn-icon btn-delete" onclick="pone(this, 1)" title="Eliminar" ${!PERMISOS.eliminar ? 'disabled' : ''}>
                        <img src="public/assets/icons/trash.svg" alt="Eliminar">
                    </button>
                </td>
            </tr>`;
          });
          $('#resultadoconsulta').html(tabla);
          crearDT();
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "existe") {

          if (tipo_accion_local === 'existe' && lee.mensaje === 'El ESPACIO colocado YA existe!') {
            $("#scodigoEspacio").text('El espacio ya existe con este prefijo de edificio y código.').css('color', 'red');
            $("#proceso").prop("disabled", true);
          } else {
            $("#scodigoEspacio").text('').css('color', '');
            $("#proceso").prop("disabled", false);
          }
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e);
        alert("Error en JSON " + e.name + ": " + e.message);
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

function limpia() {
  $("#tipoEspacio").val("");
  $("#codigoEspacio").val("");
  $("#edificio").val("");
  $("#scodigoEspacio").text('');
  $("#proceso").prop("disabled", false);
}