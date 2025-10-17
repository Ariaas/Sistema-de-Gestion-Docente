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

let originalNumero = '';
let originalEdificio = '';
let originalTipo = '';

$(document).ready(function () {
  Listar();

  $('#modal1').on('shown.bs.modal', function () {
    $("#edificio").focus();
  });

  $('#modal1').on('keydown', function(e) {
    if (e.which === 13) {
      if ($('.swal2-container').length) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      if (!$("#proceso").prop("disabled")) {
        e.preventDefault();
        $("#proceso").click();
      }
    }
  });



  $("#numeroEspacio").on("keypress", function (e) {
    validarkeypress(/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\b]*$/, e);
  });

  $("#numeroEspacio").on("keyup", function () {
    $("#snumeroEspacio").css("color", "");
    let valor = $(this).val();
    let tipo = $("#tipoEspacio").val();
    let esValido = false;
    
    if ($("#proceso").text() === "MODIFICAR") {
      verificarCambios();
    }

    if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{5,20}$/.test(valor)) {
      let prefijo = "";
      if (tipo === "Laboratorio") prefijo = "Lab. ";
      else if (tipo === "Aula") prefijo = "Aul. ";

      if (new RegExp("^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]{5,20}$").test(valor)) {
        esValido = true;
        $("#snumeroEspacio").text('');
      } else {
        esValido = false;
        $("#snumeroEspacio").text("Solo se permiten nombres como 'Software'.").css("color", "");
      }
    } else if (/^\d{1,2}$/.test(valor)) {
      esValido = true;
      $("#snumeroEspacio").text('');
    } else if (valor === "") {
      $("#snumeroEspacio")
        .text("El formato permite de 1 a 2 dígitos numéricos o de 5 a 20 letras.")
        .css("color", "");
    } else {
      esValido = false;
      if (/[0-9]/.test(valor)) {
        $("#snumeroEspacio").text("El formato permite de 1 a 2 dígitos numéricos.").css("color", "");
      } else {
        $("#snumeroEspacio").text("El formato permite de 5 a 20 letras.").css("color", "");
      }
    }

    if (esValido && valor.length >= 1 && $("#edificio").val() !== "" && $("#tipoEspacio").val() !== "") {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("numeroEspacio", valor);
      datos.append("edificioEspacio", $("#edificio").val());
      datos.append("tipoEspacio", $("#tipoEspacio").val());
      if ($("#proceso").text() === "GUARDAR") {
        datos.append("numeroEspacioExcluir", originalNumero);
        datos.append("edificioEspacioExcluir", originalEdificio);
        datos.append("tipoEspacioExcluir", originalTipo);
      }
      enviaAjax(datos, 'existe');
    } else if (!esValido) {
      $("#proceso").prop("disabled", false);
    }
  });

  $("#tipoEspacio").on("change", function () {
    $("#snumeroEspacio").css("color", "");
    $("#proceso").prop("disabled", false);
    
    if ($("#proceso").text() === "MODIFICAR") {
      verificarCambios();
    }
    let valor = $("#numeroEspacio").val();
    let tipo = $(this).val();
    let edificio = $("#edificio").val();
    if (valor.length >= 1 && tipo !== "" && edificio !== "") {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("numeroEspacio", valor);
      datos.append("edificioEspacio", edificio);
      datos.append("tipoEspacio", tipo);
      if ($("#proceso").text() === "GUARDAR") {
        datos.append("numeroEspacioExcluir", originalNumero);
        datos.append("edificioEspacioExcluir", originalEdificio);
        datos.append("tipoEspacioExcluir", originalTipo);
      }
      enviaAjax(datos, 'existe');
    }
  });

  $("#edificio").on("change", function () {
    $("#sedificio").text("");
    $("#snumeroEspacio").css("color", "");
    $("#proceso").prop("disabled", false);
    
    if ($("#proceso").text() === "MODIFICAR") {
      verificarCambios();
    }
    let valor = $("#numeroEspacio").val();
    let tipo = $("#tipoEspacio").val();
    let edificio = $(this).val();
    if (valor.length >= 1 && tipo !== "" && edificio !== "") {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("numeroEspacio", valor);
      datos.append("edificioEspacio", edificio);
      datos.append("tipoEspacio", tipo);
      if ($("#proceso").text() === "GUARDAR") {
        datos.append("numeroEspacioExcluir", originalNumero);
        datos.append("edificioEspacioExcluir", originalEdificio);
        datos.append("tipoEspacioExcluir", originalTipo);
      }
      enviaAjax(datos, 'existe');
    }
  });


 

  $("#proceso").on("click", function () {
    if ($(this).prop("disabled")) return; 
    if ($(this).text() == "GUARDAR") {
      if ($("#snumeroEspacio").text() === "El espacio ya existe.") {
        $("#snumeroEspacio").css("color", "red");
        return;
      }
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("numeroEspacio", $("#numeroEspacio").val());
        datos.append("edificioEspacio", $("#edificio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());
        enviaAjax(datos, 'registrar');
      }
    } else if ($(this).text() == "MODIFICAR") {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("original_numeroEspacio", $("#modal1").data("original-numero"));
        datos.append("original_edificioEspacio", $("#modal1").data("original-edificio"));
        datos.append("original_tipoEspacio", $("#modal1").data("original-tipo")); 
        datos.append("numeroEspacio", $("#numeroEspacio").val());
        datos.append("edificioEspacio", $("#edificio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());
        enviaAjax(datos, 'modificar');
    } else if ($(this).text() == "ELIMINAR") {

        const swalInstance = Swal.fire({
            title: "¿Está seguro de eliminar este espacio?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            focusConfirm: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
            didOpen: () => {
                const popup = Swal.getPopup();
                popup.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.stopPropagation();
                        Swal.clickConfirm();
                    }
                });
            }
        });
        
        swalInstance.then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append("accion", "eliminar");
                datos.append("numeroEspacio", $("#numeroEspacio").val());
                datos.append("edificioEspacio", $("#edificio").val());
                datos.append("tipoEspacio", $("#tipoEspacio").val()); 
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
    $("#proceso").text("GUARDAR");
    $("#modal1").modal("show");
    $("#snumeroEspacio").text('').css('color', '');
    $("#tipoEspacio, #numeroEspacio, #edificio").prop("disabled", false);
    $("#edificio").val("");
    originalNumero = '';
    originalEdificio = '';
    originalTipo = '';
  });

  $('#modal1').on('hidden.bs.modal', function () {
    $("#proceso").prop("disabled", false);
    $("#snumeroEspacio").text('').css('color', '');
  });
});



function validarenvio() {
  let esValido = true;
  let valor = $("#numeroEspacio").val();

  if (/^\d{1,2}$/.test(valor)) {
    $("#snumeroEspacio").text('');
  } else if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{5,20}$/.test(valor)) {
    $("#snumeroEspacio").text('');
  } else {
    esValido = false;
    if (/[0-9]/.test(valor)) {
      if(esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del número de espacio es incorrecto.");
      $("#snumeroEspacio").text("El formato permite de 1 a 2 dígitos numéricos.").css("color", "");
    } else if (valor === "") {
      $("#snumeroEspacio").text("El formato permite de 1 a 2 dígitos numéricos o de 5 a 20 letras.").css("color", "");
    } else {
      if(esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del número de espacio es incorrecto.");
      $("#snumeroEspacio").text("El formato permite de 5 a 20 letras.").css("color", "");
    }
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

function verificarCambios() {
  const numeroActual = $("#numeroEspacio").val();
  const edificioActual = $("#edificio").val();
  const tipoActual = $("#tipoEspacio").val();
  
  if (numeroActual === originalNumero && 
      edificioActual === originalEdificio && 
      tipoActual === originalTipo) {
    $("#proceso").prop("disabled", true);
    $("#snumeroEspacio").text("No se han realizado cambios.").show();
  } else {
    if ($("#snumeroEspacio").text() !== "El espacio ya existe.") {
      $("#proceso").prop("disabled", false);
      $("#snumeroEspacio").text("").hide();
    }
  }
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

  $("#modal1").data("original-numero", numero);
  $("#modal1").data("original-edificio", edificio);
  $("#modal1").data("original-tipo", tipo);

  originalNumero = numero;
  originalEdificio = edificio;
  originalTipo = tipo;

  if (accion == 0) { 
    $("#proceso").text("MODIFICAR");
    $("#tipoEspacio, #numeroEspacio, #edificio").prop("disabled", false);
    $("#proceso").prop("disabled", true);
    $("#snumeroEspacio").text("Realice un cambio para poder modificar.").show();
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
            if (item.esp_estado == 1) {
            let numeroMostrado = item.esp_numero;
            if (/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{5,20}$/.test(numeroMostrado)) {
              if (item.esp_tipo === "Laboratorio" && !numeroMostrado.startsWith("Lab. ")) {
                numeroMostrado = "Lab. " + numeroMostrado.trim();
              } else if (item.esp_tipo === "Aula" && !numeroMostrado.startsWith("Aul. ")) {
                numeroMostrado = "Aul. " + numeroMostrado.trim();
              }
            } else if (/^\d{1,2}$/.test(numeroMostrado)) {
              if (item.esp_tipo === "Laboratorio" && !numeroMostrado.startsWith("L-")) {
                numeroMostrado = "L-" + numeroMostrado;
              } else if (item.esp_tipo === "Aula" && !numeroMostrado.startsWith("A-")) {
    const letraEdificio = item.esp_edificio ? item.esp_edificio.charAt(0).toUpperCase() : '';
    numeroMostrado = letraEdificio + "-" + numeroMostrado;
  }
            }

            tabla += `
            <tr data-numero="${item.esp_numero}" data-edificio="${item.esp_edificio}" data-tipo="${item.esp_tipo}">
                <td>${numeroMostrado}</td>
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
            }
          });
          $('#resultadoconsulta').html(tabla);
          crearDT();
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "GUARDAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "existe" || lee.resultado == "no_existe") {
          if (lee.resultado === 'existe') {
            $("#snumeroEspacio").text('El espacio ya existe.').css('color', 'red');
            $("#proceso").prop("disabled", true);
          } else if (lee.resultado == "no_existe") {
            $("#snumeroEspacio").text('');
            $("#snumeroEspacio").css('color', '');
            $("#proceso").prop("disabled", false);
          }
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
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
  originalNumero = '';
  originalEdificio = '';
  originalTipo = '';
}