// Variable global para la tabla DataTable
let dataTable;

function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablacoordinacion")) {
    $("#tablacoordinacion").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablacoordinacion")) {
    dataTable = $("#tablacoordinacion").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      language: {
        url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
      },
      order: [[1, "asc"]]
    });
  }
}

$(document).ready(function () {
  Listar();

  //////////////////////////////VALIDACIONES/////////////////////////////////////
  $("#coordinacionNombre").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z\sñÑáéíóúÁÉÍÓÚ-]{0,30}$/, e);
  });

  $("#coordinacionNombre").on("keyup", function () {
    validarkeyup(/^[A-Za-z\sñÑáéíóúÁÉÍÓÚ-]{4,30}$/, $(this), $("#scoordinacionNombre"), "El nombre debe tener entre 4 y 30 caracteres.");
    
    // Verificación de existencia
    if ($(this).val().length >= 4) {
      var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('coordinacionNombre', $(this).val());
      datos.append('coordinacionId', $("#coordinacionId").val()); // Enviar ID para exclusión en modificación
      enviaAjax(datos);
    }
  });

  //////////////////////////////BOTONES/////////////////////////////////////
  $("#proceso").on("click", function () {
    const textoBoton = $(this).text();
    if (validarenvio()) {
      var datos = new FormData($("#f")[0]);
      if (textoBoton === "Guardar") {
        datos.append("accion", "registrar");
      } else if (textoBoton === "Modificar") {
        datos.append("accion", "modificar");
      }
      enviaAjax(datos);
    }
  });

  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("Guardar");
    $("#modal1").modal("show");
  });
});

function validarenvio() {
  if (!validarkeyup(/^[A-Za-z\sñÑáéíóúÁÉÍÓÚ-]{4,30}$/, $("#coordinacionNombre"), $("#scoordinacionNombre"), "El nombre debe tener entre 4 y 30 caracteres.")) {
    muestraMensaje("error", 4000, "Error de validación", "Por favor, corrija el nombre de la coordinación.");
    return false;
  }
  return true;
}

function pone(pos, accion) {
  let linea = $(pos).closest("tr");
  if (dataTable) { 
      linea = dataTable.row($(pos).closest("tr")).data();
  }

  const id = Array.isArray(linea) ? linea[0] : $(linea).find("td:eq(0)").text();
  const nombre = Array.isArray(linea) ? linea[1] : $(linea).find("td:eq(1)").text();
  
  limpia();
  $("#coordinacionId").val(id);
  $("#coordinacionNombre").val(nombre);

  if (accion === 'modificar') {
    $("#proceso").text("Modificar");
    $("#coordinacionNombre").prop("disabled", false);
    $("#modal1").modal("show");
  } else if (accion === 'eliminar') {
    Swal.fire({
      title: "¿Está seguro de eliminar esta coordinación?",
      text: "Esta acción no se puede deshacer.",
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
        datos.append("coordinacionId", id);
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
    timeout: 10000,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          lee.mensaje.forEach(item => {
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.cor_id}</td>
                <td>${item.cor_nombre}</td>
                <td>
                  <button class="btn btn-warning btn-sm" onclick='pone(this, "modificar")'>Modificar</button>
                  <button class="btn btn-danger btn-sm" onclick='pone(this, "eliminar")'>Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        } else if (lee.resultado === "registrar" || lee.resultado === "modificar" || lee.resultado === "eliminar") {
          muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
          $("#modal1").modal("hide");
          Listar();
        } else if (lee.resultado === "existe") {		
          muestraMensaje('info', 4000,'Atención!', lee.mensaje);
        } else if (lee.resultado === "error") {
          muestraMensaje("error", 10000, "ERROR", lee.mensaje);
        }
      } catch (e) {
        console.error("Error al procesar la respuesta:", e, "Respuesta:", respuesta);
        muestraMensaje("error", 5000, "ERROR DE RESPUESTA", "No se pudo procesar la respuesta del servidor.");
      }
    },
    error: function (request, status, err) {
      muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", status === "timeout" ? "Servidor ocupado, intente de nuevo" : "Ocurrió un error: " + err);
    }
  });
}

function limpia() {
  $("#coordinacionId").val("");
  $("#coordinacionNombre").val("");
  $("#scoordinacionNombre").text("").hide();
  $("#coordinacionNombre").prop('disabled', false);
}

// Función para mostrar mensajes
function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true,
    });
}