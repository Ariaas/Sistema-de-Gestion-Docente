function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablaarea")) {
    $("#tablaarea").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaarea")) {
    $("#tablaarea").DataTable({
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
      order: [[0, "asc"]],
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

let originalNombreArea = ''; 

$(document).ready(function () {
  Listar();

  //////////////////////////////VALIDACIONES/////////////////////////////////////

  $("#areaNombre").on("keypress", function (e) {
    validarkeypress(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ,#\b\s-]*$/, e);
  });

  $("#areaNombre").on("keyup keydown", function () {
    $("#sareaNombre").css("color", "");
    
    let formatoValido = validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9,#\b\s-]{5,30}$/,
      $(this),
      $("#sareaNombre"),
      "El nombre debe tener entre 5 y 30 caracteres"
    );

    if (formatoValido === 1) {
      var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('areaNombre', $(this).val());
      if ($("#proceso").text() === "MODIFICAR") {
          datos.append('areaExcluir', originalNombreArea);
      }
      enviaAjax(datos, 'existe');
    }
  });

  $("#areaDescripcion").on("keyup", function() {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s.,-]{5,100}$/, $(this), $("#sareaDescripcion"), "La descripción debe tener entre 5 y 100 caracteres. Ej:Esta categoría...");
  });

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    let accion = $(this).text();
    if (accion === "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("areaNombre", $("#areaNombre").val());
        datos.append("areaDescripcion", $("#areaDescripcion").val());
        datos.append("areaNombreOriginal", originalNombreArea); 
        enviaAjax(datos);
      }
    } else if (accion === "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("areaNombre", $("#areaNombre").val());
        datos.append("areaDescripcion", $("#areaDescripcion").val());
        enviaAjax(datos);
      }
    } else if (accion === "ELIMINAR") {
        Swal.fire({
          title: "¿Está seguro de eliminar esta área?",
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
            datos.append("areaNombre", $("#areaNombre").val());
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
    $("#sareaNombre").show();
    $("#sareaDescripcion").show();
  });
});

function validarenvio() {
    let esValido = true;
    if (validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9,#\b\s-]{5,30}$/,
      $("#areaNombre"),
      $("#sareaNombre"),
      "El nombre debe tener entre 5 y 30 caracteres, Ej: Matemáticas"
    ) == 0) {
      if(esValido) muestraMensaje("error",4000,"ERROR!","El formato del nombre es incorrecto");
      esValido = false;
    }
    if (validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s.,-]{5,100}$/,
      $("#areaDescripcion"),
      $("#sareaDescripcion"),
      "La descripción debe tener entre 5 y 100 caracteres."
    ) == 0) {
      if(esValido) muestraMensaje("error",4000,"ERROR!","El formato de la descripción es incorrecto");
      esValido = false;
    }

  return esValido;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalNombreArea = $(linea).find("td:eq(0)").text(); 

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#areaNombre").prop("disabled", false);
    $("#areaDescripcion").prop("disabled", false);
    $("#sareaNombre").text("").show();
    $("#sareaDescripcion").text("").show();
  } else {
    $("#proceso").text("ELIMINAR");
    $("#areaNombre, #areaDescripcion").prop("disabled", true);
    $("#sareaNombre").hide();
    $("#sareaDescripcion").hide();
  }
  
  $("#areaNombre").val($(linea).find("td:eq(0)").text());
  $("#areaDescripcion").val($(linea).find("td:eq(1)").text());

  $("#modal1").modal("show");
}

function enviaAjax(datos, accion) {
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
        if (accion === 'existe') {
          if (lee.resultado === 'existe') {
            $("#sareaNombre").text(lee.mensaje).css("color", "red");
            $("#proceso").prop("disabled", true);
          } else {
            $("#proceso").prop("disabled", false);
          }
          return;
        }
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta").append(`
              <tr>
                <td>${item.area_nombre}</td>
                <td>${item.area_descripcion}</td>
                <td>
                  <button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                  <button  class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el área correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el área correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }else if (lee.resultado == "existe") {		
          if (lee.mensaje == 'El área ya existe!') {
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }	
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el área correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.log("Error en análisis JSON:", e);
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
  $("#areaNombre").val("");
  $("#areaDescripcion").val("");
  $("#areaNombre").prop('disabled', false);
  $("#areaDescripcion").prop('disabled', false);
  $("#sareaNombre").text("");
  $("#sareaDescripcion").text("");
}