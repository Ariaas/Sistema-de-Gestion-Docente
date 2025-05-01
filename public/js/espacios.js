function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}


function destruyeDT() {
  // se destruye el datatablet
  if ($.fn.DataTable.isDataTable("#tablaespacio")) {
    $("#tablaespacio").DataTable().destroy();
  }
}
function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaespacio")) {
    var table = $("#tablaespacio").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      language: {
        lengthMenu: "Mostrar _MENU_",
        zeroRecords: "No se encontraron proveedores",
        info: "Página _PAGE_ de _PAGES_",
        infoEmpty: "No hay proveedores registrados",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar",
        paginate: {
          first: "Primera",
          last: "Última",
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

$(document).ready(function () {
//   consultar();

  //////////////////////////////VALIDACIONES/////////////////////////////////////

//   $("#tipoEspacio").on("keypress", function (e) {
//     validarkeypress(/^[A-Za-z0-9-\b]*$/, e);
//   });

//   $("#tipoEspacio").on("keyup", function () {
//     validarkeyup(
//       /^[A-Za-z0-9]{8,9}$/,
//       $(this),
//       $("#srifProveedor"),
//       "El formato permite de 8 a 9 carácteres"
//     );
//     if ($("#tipoEspacio").val().length <= 9) {
//       var datos = new FormData();
//       datos.append("accion", "buscar");
//       datos.append("tipoEspacio", $(this).val());
//       enviaAjax(datos, "buscar");
//     }
//   });

//   $("#nombreProveedor").on("keypress", function (e) {
//     validarkeypress(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
//   });

//   $("#nombreProveedor").on("keyup", function () {
//     validarkeyup(
//       /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,30}$/,
//       $(this),
//       $("#snombreProveedor"),
//       "Este formato no debe estar vacío / permite un máximo 30 carácteres"
//     );
//   });

//   $("#correoProveedor").on("keypress", function (e) {
//     validarkeypress(/^[A-Za-z0-9@_.\b\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
//   });

//   $("#correoProveedor").on("keyup", function () {
//     validarkeyup(
//       /^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC-]{3,30}[@]{1}[A-Za-z0-9]{3,8}[.]{1}[A-Za-z]{2,3}$/,
//       $(this),
//       $("#scorreoProveedor"),
//       "El formato sólo permite un correo válido!"
//     );
//   });

//   $("#telefonoProveedor").on("keypress", function (e) {
//     validarkeypress(/^[0-9-\b]*$/, e);
//   });

//   $("#telefonoProveedor").on("keyup", function () {
//     validarkeyup(
//       /^[0-9]{10,11}$/,
//       $(this),
//       $("#stelefonoProveedor"),
//       "El formato sólo permite un número válido"
//     );
//   });


  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("codigoEspacio", $("#codigoEspacio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("codigoEspacio", $("#codigoEspacio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());

        enviaAjax(datos);
      }
    }
    if ($(this).text() == "ELIMINAR") {
      if (
        validarkeyup(
          /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{2,6}$/,
          $("#codigoEspacio"),
          $("#scodigoEspacio"),
          "Formato de codigo incorrecto"
        ) == 0
      ) {
        muestraMensaje(
          "error",
          4000,
          "ERROR!",
          "Seleccionó un codigo incorrecto <br/> por favor verifique nuevamente"
        );
      } else {
        // Mostrar confirmación usando SweetAlert
        Swal.fire({
          title: "¿Está seguro de eliminar este proveedor?",
          text: "Esta acción no se puede deshacer.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sí, eliminar",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) {
            // Si se confirma, proceder con la eliminación
            var datos = new FormData();
            datos.append("accion", "eliminar");
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
    }
  });

  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
  });
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {

   return true;
}

//Función para mostrar mensajes

function muestraMensaje(icono, tiempo, titulo, mensaje) {
  Swal.fire({
    icon: icono,
    timer: tiempo,
    title: titulo,
    html: mensaje,
    showConfirmButton: true,
    confirmButtonText: "Aceptar",
  });
}



//funcion para pasar de la lista a el formulario
// function pone(pos, accion) {
//   linea = $(pos).closest("tr");

//   if (accion == 0) {
//     $("#proceso").text("MODIFICAR");
//     $("#tipoEspacio").prop("disabled", true);
//     $("#codigoEspacio").prop("disabled", true);
//   } else {
//     $("#proceso").text("ELIMINAR");
//     $(
//       "#tipoEspacio, #codigoEspacio"
//     ).prop("disabled", true);
//   }

//   $("#tipoEspacio").val($(linea).find("td:eq(1)").text());
//   $("#codigoEspacio").val($(linea).find("td:eq(2)").text());

//   $("#modal1").modal("show");
// }

//funcion que envia y recibe datos por AJAX
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
    timeout: 10000, //tiempo maximo de espera por la respuesta del servidor
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado == "consulta") {
          destruyeDT();

          // Limpiar la tabla
          $("#resultadoconsulta").empty();

          // Llenar la tabla con los datos
          if (lee.espacios && lee.espacios.length > 0) {
            var html = "";
            $.each(lee.espacios, function (index, espacio) {
              html += "<tr>";
              html += "<td>" + espacio.esp_codigo + "</td>";
              html += "<td>" + espacio.esp_tipo + "</td>";
              html +=
                "<td><button class='btn btn-warning btn-sm' onclick='pone(this,0)'><i class='fas fa-edit'></i></button> ";
              html +=
                "<button class='btn btn-danger btn-sm' onclick='pone(this,1)'><i class='fas fa-trash'></i></button></td>";
              html += "</tr>";
            });
            $("#resultadoconsulta").html(html);
          }

          crearDT();
        } else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            // consultar();
          }
        }
        // else if (lee.resultado == "modificar") {
        //   muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
        //   if (
        //     lee.mensaje ==
        //     "Registro Modificado!<br/> Se modificó el proveedor correctamente"
        //   ) {
        //     $("#modal1").modal("hide");
        //     consultar();
        //   }
        // }
        // else if (lee.resultado == "encontro") {
        //   if (lee.mensaje == "El rif ya existe!") {
        //     alert(lee.mensaje);
        //   }
        // }
        // else if (lee.resultado == "eliminar") {
        //   muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
        //   if (
        //     lee.mensaje ==
        //     "Registro Eliminado! <br/> Se eliminó el proveedor correctamente"
        //   ) {
        //     $("#modal1").modal("hide");
        //     consultar();
        //   }
        // }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e); // Registrar el error para depuración
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
}
