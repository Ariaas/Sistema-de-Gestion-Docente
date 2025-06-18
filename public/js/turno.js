function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
 
  if ($.fn.DataTable.isDataTable("#tablaturno")) {
    $("#tablaturno").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaturno")) {
    $("#tablaturno").DataTable({
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
      // MODIFICADO: Ordena por la segunda columna (hora de inicio) por defecto
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
  Listar(); 

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    // MODIFICADO: Ahora el texto del botón puede ser 'REGISTRAR' o 'GUARDAR CAMBIOS'
    if ($(this).text() == "REGISTRAR" || $(this).text() == "GUARDAR CAMBIOS") {
      if (validarenvio()) {
        var datos = new FormData();
        // Se determina la acción basado en si existe un ID de turno o no
        datos.append("accion", $("#turnoid").val() ? "modificar" : "registrar");
        datos.append("turnoid", $("#turnoid").val());
        datos.append("horaInicio", $("#horaInicio").val());
        datos.append("horafin", $("#horafin").val());

        enviaAjax(datos);
      }
    }
  });


  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
    $("#horaInicio, #horafin").prop("disabled", false);
  });

});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////
// MODIFICADO: Se añaden más validaciones del lado del cliente
function validarenvio() {
   let inicio = $("#horaInicio").val();
   let fin = $("#horafin").val();

  if (!inicio) {
        muestraMensaje("error",4000,"¡ERROR!","Por favor, seleccione una hora de inicio."); 
          return false;
  } 
  if (!fin) {
        muestraMensaje("error",4000,"¡ERROR!","Por favor, seleccione una hora de fin."); 
          return false;
  }
  
  // Compara las horas. JavaScript puede comparar las cadenas de tiempo 'HH:mm' directamente.
  if (fin <= inicio) {
        muestraMensaje("error", 4000, "¡ERROR!", "La hora de fin no puede ser anterior o igual a la hora de inicio.");
        return false;
  }

  return true;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");
  limpia(); // Limpia el formulario antes de llenarlo

  $("#turnoid").val($(linea).find("td:eq(0)").text());
  $("#horaInicio").val($(linea).find("td:eq(1)").text());
  $("#horafin").val($(linea).find("td:eq(2)").text());

  if (accion == 0) { // Modificar
    $(".modal-title").text("Modificar Turno");
    $("#proceso").text("GUARDAR CAMBIOS");
    $("#horaInicio, #horafin").prop("disabled", false);
    $("#modal1").modal("show");
  } else { // Eliminar
      // Usamos una ventana de confirmación más robusta
      Swal.fire({
          title: "¿Está seguro de eliminar este turno?",
          html: `<b>Inicio:</b> ${$(linea).find("td:eq(1)").text()}<br><b>Fin:</b> ${$(linea).find("td:eq(2)").text()}<br><br>Esta acción no se puede deshacer.`,
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
          confirmButtonText: "Sí, eliminar",
          cancelButtonText: "Cancelar",
        }).then((result) => {
          if (result.isConfirmed) {
            var datos = new FormData();
            datos.append("accion", "eliminar");
            datos.append("turnoid", $("#turnoid").val());
            enviaAjax(datos);
          }
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
    beforeSend: function () {},
    timeout: 10000, 
    success: function (respuesta) {
      try {
        console.log(respuesta);
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.tur_id}</td>
                <td>${item.tur_horaInicio}</td>
                <td>${item.tur_horaFin}</td>
                <td>
                  <button class="btn btn-warning btn-sm" onclick='pone(this,0)'>Modificar</button>
                  <button class="btn btn-danger btn-sm" onclick='pone(this,1)'>Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "¡ÉXITO!", lee.mensaje);
          $("#modal1").modal("hide");
          Listar();
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "¡ÉXITO!", lee.mensaje);
          $("#modal1").modal("hide");
          Listar();
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "PROCESO COMPLETADO", lee.mensaje);
          Listar();
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "¡HA OCURRIDO UN ERROR!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e, "Respuesta recibida:", respuesta); 
        muestraMensaje("error", 15000, "Error Inesperado", "Se recibió una respuesta inválida del servidor. Revisa la consola para más detalles.");
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("error", 5000, "Servidor ocupado, intente de nuevo");
      } else {
        muestraMensaje("error", 5000, "ERROR: <br/>" + request.responseText + status + err);
      }
    },
  });
}

function limpia() {
  $("#turnoid").val("");
  $("#horaInicio").val("");
  $("#horafin").val("");
  $(".modal-title").text("Formulario de Turno"); // Restaura el título original del modal
}