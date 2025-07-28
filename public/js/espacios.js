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

  $("#numeroEspacio").on("keypress", function (e) {
    validarkeypress(/^[0-9\b]*$/, e);
  });

  $("#numeroEspacio").on("keyup", function () {
    $("#snumeroEspacio").css("color", "");
    const esValido = validarkeyup(
      /^[0-9]{1,2}$/,
      $(this),
      $("#snumeroEspacio"),
      "El formato permite de 1 a 2 dígitos numéricos."
    );

    if (esValido && $(this).val().length >= 1 && $("#edificio").val() !== "") {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("numeroEspacio", $(this).val());
      datos.append("edificioEspacio", $("#edificio").val());
      if ($("#proceso").text() === "MODIFICAR") {
        datos.append("numeroEspacioExcluir", $("#modal1").data("original-numero"));
        datos.append("edificioEspacioExcluir", $("#modal1").data("original-edificio"));
      }
      enviaAjax(datos, 'existe');
    } else if (!esValido) {
      $("#proceso").prop("disabled", false);
    }
  });

  $("#tipoEspacio").on("change", function () {
    $("#stipoEspacio").text("");
  });

  $("#edificio").on("change", function () {
    $("#sedificio").text("");
    if ($("#numeroEspacio").val().length >= 1) {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("numeroEspacio", $("#numeroEspacio").val());
      datos.append("edificioEspacio", $(this).val());
      enviaAjax(datos, 'existe');
    }
    $("#snumeroEspacio").css("color", "");
    $("#proceso").prop("disabled", false);
  });


  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
            var datos = new FormData();
            datos.append("accion", "registrar");
            datos.append("numeroEspacio", $("#numeroEspacio").val());
            datos.append("edificioEspacio", $("#edificio").val());
            datos.append("tipoEspacio", $("#tipoEspacio").val());

            enviaAjax(datos);
        }
    } else if ($(this).text() == "MODIFICAR") {
        if ($("#tipoEspacio").val()) {
            var datos = new FormData();
            datos.append("accion", "modificar");

            datos.append("original_numeroEspacio", $("#modal1").data("original-numero"));
            datos.append("original_edificioEspacio", $("#modal1").data("original-edificio"));

       
            datos.append("numeroEspacio", $("#numeroEspacio").val());
            datos.append("edificioEspacio", $("#edificio").val());
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
                datos.append("numeroEspacio", $("#numeroEspacio").val());
                datos.append("edificioEspacio", $("#edificio").val());
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
    $("#snumeroEspacio").text('');
    $("#tipoEspacio, #numeroEspacio, #edificio").prop("disabled", false);
    $("#edificio").val("");
  });

});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
  let esValido = true;

  if (
    validarkeyup(
      /^[0-9]{1,2}$/,
      $("#numeroEspacio"),
      $("#snumeroEspacio"),
      "El formato permite de 1 a 2 dígitos numéricos."
    ) == 0
  ) {
    if(esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del número de espacio es incorrecto.");
    esValido = false;
  }

  if ($("#edificio").val() === null || $("#edificio").val() === "") {
    $("#sedificio").text("Debe seleccionar un edificio.");
    if(esValido) muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar un edificio.");
    esValido = false;
  } else {
     $("#sedificio").text("");
  }

  if ($("#tipoEspacio").val() === null || $("#tipoEspacio").val() === "") {
    $("#stipoEspacio").text("Debe seleccionar un tipo de espacio.");
    if(esValido) muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar un tipo de espacio.");
    esValido = false;
  } else {
    $("#stipoEspacio").text("");
  }
  
  return esValido;
}


function getPrefijoEdificio(nombreEdificio) {
    if (!nombreEdificio) return '';
    return nombreEdificio.charAt(0).toUpperCase();
}

function separarCodigoEdificio(codigoCompleto) {
    if (!codigoCompleto || codigoCompleto.length < 2) {
        return { prefijo: '', codigoNumerico: codigoCompleto, nombreEdificio: 'Desconocido' };
    }
    const prefijo = codigoCompleto.charAt(0);
    const codigoNumerico = codigoCompleto.substring(1);
    
    let nombreEdificio = 'Desconocido';
    if (prefijo === 'P') nombreEdificio = 'Principal';
    else if (prefijo === 'A') nombreEdificio = 'Anexo';
    else if (prefijo === 'L') nombreEdificio = 'Laboratorios';

    return { prefijo, codigoNumerico, nombreEdificio };
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");

  const numero = $(linea).data("numero");
  const edificio = $(linea).data("edificio");
  const tipo = $(linea).data("tipo");

  if (accion == 0) { 
    $("#proceso").text("MODIFICAR");
   

    $("#modal1").data("original-numero", numero);
    $("#modal1").data("original-edificio", edificio);

    $("#tipoEspacio, #numeroEspacio, #edificio").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#tipoEspacio, #numeroEspacio, #edificio").prop("disabled", true);
  }

 
  $("#numeroEspacio").val(numero);
  $("#tipoEspacio").val(tipo);
  $("#edificio").val(edificio);


  $("#modal1").modal("show");
  $("#snumeroEspacio, #sedificio, #stipoEspacio").text('');
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
            tabla += `
            <tr data-numero="${item.esp_numero}" data-edificio="${item.esp_edificio}" data-tipo="${item.esp_tipo}">
                <td>${item.esp_codigo}</td>
                <td>${item.esp_tipo}</td>
                <td>${item.esp_edificio}</td>
                <td class="text-center">
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
        } else if (lee.resultado == "existe" || lee.resultado == "no_existe") {

          if (tipo_accion_local === 'existe' && lee.resultado === 'existe') {
            $("#snumeroEspacio").text('El espacio ya existe.').css('color', 'red');
            $("#proceso").prop("disabled", true);
          } else {
            $("#snumeroEspacio").text('').css('color', '');
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
  $("#numeroEspacio").val("");
  $("#edificio").val("");
  $("#snumeroEspacio, #sedificio, #stipoEspacio").text('');
  $("#proceso").prop("disabled", false);
}