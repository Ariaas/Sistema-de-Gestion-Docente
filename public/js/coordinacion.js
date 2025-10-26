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
    $("#tablacoordinacion").DataTable({
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
      order: [[0, "asc"]],
    });
  }
}

let originalNombreCoordinacion = "";
let originalHoraDescarga = null;

$(document).ready(function () {
  Listar();

  $("#coordinacionNombre").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z\b\s\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
  });

  $("#coordinacionNombre").on("keyup", function () {
    const nombreActual = $(this).val();
    const formatoValido = validarkeyup(
      /^[A-Za-z\b\s\u00f1\u00d1\u00E0-\u00FC-]{5,30}$/,
      $(this),
      $("#scoordinacionNombre"),
      "El nombre debe tener entre 5 y 30 caracteres."
    );

    if (formatoValido === 1) {
      var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('coordinacionNombre', nombreActual);
      if ($("#proceso").text() === "MODIFICAR") {
        datos.append("coordinacionOriginalNombre", originalNombreCoordinacion);
        verificarCambios();
      }
      enviaAjax(datos);
    }
  });

  $("#coordinacionHoraDescarga").on("keypress", function(e) {
    validarkeypress(/^[0-9\b]*$/, e);
  });

  $("#coordinacionHoraDescarga").on("keyup keydown", function () {
    $("#scoordinacionHoraDescarga").css("color", "");
    validarkeyup(
      /^([1-9]|[1-9][0-9])$/,
      $(this),
      $("#scoordinacionHoraDescarga"),
      "Debe ser un número entre 1 y 99."
    );
    if ($("#proceso").text() === "MODIFICAR") {
      verificarCambios();
    }
  });

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("coordinacionNombre", $("#coordinacionNombre").val());
        datos.append("coordinacionHoraDescarga", $("#coordinacionHoraDescarga").val());
        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("coordinacionNombre", $("#coordinacionNombre").val());
        datos.append("coordinacionHoraDescarga", $("#coordinacionHoraDescarga").val());
        datos.append("coordinacionOriginalNombre", originalNombreCoordinacion);
        enviaAjax(datos);
      }
    } else if ($(this).text() == "ELIMINAR") {
      const swalInstance = Swal.fire({
        title: "¿Está seguro de eliminar esta coordinación?",
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
          datos.append("coordinacionNombre", $("#coordinacionNombre").val());
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
    $("#scoordinacionNombre").show();
    $("#coordinacionNombre, #coordinacionHoraDescarga").prop("disabled", false);
  });

  $('#modal1').on('hidden.bs.modal', function () {
    $("#proceso").prop("disabled", false);
    $("#scoordinacionNombre").text("").css("color", "");
    $("#scoordinacionHoraDescarga").text("").css("color", "");
  });

  $('#modal1').on('shown.bs.modal', function () {
    $("#coordinacionNombre").focus();
  });

  $('#modal1').on('keydown', function(e) {
    if (e.which === 13) {
      if ($("#proceso").text() === "ELIMINAR" && $('.swal2-container').length) {
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
});

function validarenvio() {
  let esValido = true;

  if (validarkeyup(
    /^[A-Za-z\b\s\u00f1\u00d1\u00E0-\u00FC-]{5,30}$/,
    $("#coordinacionNombre"),
    $("#scoordinacionNombre"),
    "El nombre debe tener entre 5 y 30 caracteres."
  ) == 0) {
    if(esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del nombre de la coordinación es incorrecto.");
    esValido = false;
  }

  const horaDescarga = $("#coordinacionHoraDescarga").val();
  if (horaDescarga && validarkeyup(
    /^([1-9]|[1-9][0-9])$/,
    $("#coordinacionHoraDescarga"),
    $("#scoordinacionHoraDescarga"),
    "Debe ser un número entre 1 y 99."
  ) == 0) {
    if(esValido) muestraMensaje("error", 4000, "ERROR!", "El formato de la hora de descarga es incorrecto.");
    esValido = false;
  }

  return esValido;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalNombreCoordinacion = $(linea).find("td:eq(0)").text();
  originalHoraDescarga = $(linea).find("td:eq(1)").text();

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#coordinacionNombre").prop("disabled", false);
    $("#coordinacionHoraDescarga").prop("disabled", false);
    $("#scoordinacionNombre").text("").show();
    $("#scoordinacionHoraDescarga").text("").show();
    $("#proceso").prop("disabled", true);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#coordinacionNombre, #coordinacionHoraDescarga").prop("disabled", true);
    $("#scoordinacionNombre").hide();
    $("#scoordinacionHoraDescarga").hide();
  }
  $("#coordinacionNombre").val($(linea).find("td:eq(0)").text());
  $("#coordinacionHoraDescarga").val($(linea).find("td:eq(1)").text());

  $("#modal1").modal("show");
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
          $.each(lee.mensaje, function (index, item) {
            if (item.cor_estado == 1) {
              const btnModificar = `<button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" ${
                !PERMISOS.modificar ? "disabled" : ""
              }><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
              const btnEliminar = `<button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" ${
                !PERMISOS.eliminar ? "disabled" : ""
              }><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
              $("#resultadoconsulta").append(`
                <tr>
                  <td>${item.cor_nombre}</td>
                  <td>${item.coor_hora_descarga || 'N/A'}</td>
                  <td>
                        ${btnModificar}
                    ${btnEliminar}
                  </td>
                </tr>
              `);
            }
          });
          crearDT();
        } else if (
          lee.resultado === "registrar" ||
          lee.resultado === "modificar" ||
          lee.resultado === "eliminar"
        ) {
          let tituloMayusculas = lee.resultado.toUpperCase();
          Swal.fire({
            icon: "success",
            title: tituloMayusculas,
            text: lee.mensaje,
            timer: 2000,
          });
          $("#modal1").modal("hide");
          Listar();
        } else if (lee.resultado === "existe") {
          $("#scoordinacionNombre").text(lee.mensaje).show();
          $("#proceso").prop("disabled", true);
        } else if (lee.resultado === "no_existe") {
          $("#scoordinacionNombre").text("").hide();
          $("#proceso").prop("disabled", false);
        } else if (lee.resultado === "error") {
          Swal.fire({ icon: "error", title: "Error", html: lee.mensaje });
        }
      } catch (e) {
        Swal.fire({
          icon: "error",
          title: "Error en respuesta",
          text: "No se pudo procesar la respuesta del servidor.",
        });
        console.error("Error en JSON: ", e, "Respuesta: ", respuesta);
      }
    },
    error: function (request, status, err) {
      Swal.fire({
        icon: "error",
        title: "Error de Conexión",
        text: "Ocurrió un problema de comunicación.",
      });
    },
  });
}

function verificarCambios() {
  const nombreActual = $("#coordinacionNombre").val();
  const horaActual = $("#coordinacionHoraDescarga").val();
  
  if (nombreActual === originalNombreCoordinacion && horaActual == originalHoraDescarga) {
    $("#proceso").prop("disabled", true);
    $("#scoordinacionNombre").text("No se han realizado cambios.").show();
  } else {
    if ($("#scoordinacionNombre").text() !== "El nombre de la coordinación ya existe.") {
      $("#proceso").prop("disabled", false);
      $("#scoordinacionNombre").text("").hide();
    }
  }
}

function limpia() {
  $("#coordinacionNombre").val("").prop("disabled", false);
  $("#coordinacionHoraDescarga").val("");
  $("#scoordinacionNombre").text("");
  $("#scoordinacionHoraDescarga").text("");
  $("#proceso").prop("disabled", false);
  originalNombreCoordinacion = "";
  originalHoraDescarga = null;
}
