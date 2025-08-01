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

let originalNombreCoordinacion = ''; 

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

    if (!formatoValido) {
      $("#proceso").prop("disabled", true);
      return; 
    }

    
    if (originalNombreCoordinacion !== '' && nombreActual === originalNombreCoordinacion) {
        $("#scoordinacionNombre").text("No se han realizado cambios.").show();
        $("#proceso").prop("disabled", true);
        return; 
    }

    
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('coordinacionNombre', nombreActual);
    datos.append('coordinacionOriginalNombre', originalNombreCoordinacion);
    enviaAjax(datos);
  });


  $("#proceso").on("click", function () {
    let accion = $(this).text();

    if (accion === "ELIMINAR") {
        Swal.fire({
          title: "¿Está seguro que quieres Eliminar esta coordinación?",
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
            datos.append("coordinacionNombre", $("#coordinacionNombre").val());
            enviaAjax(datos);
          }
        });
        return;
    }
    
    if (validarenvio()) { 
        var datos = new FormData($("#f")[0]);
        if (accion === "MODIFICAR") {
            datos.append("accion", "modificar");
            datos.append("coordinacionOriginalNombre", originalNombreCoordinacion);
        } else {
            datos.append("accion", "registrar");
        }
        enviaAjax(datos);
    }
  });

  $("#registrar").on("click", function () {
    limpia();
    let modalHeader = $("#modal1 .modal-header");
    let modalTitle = $("#modal1 .modal-title");
    
    modalHeader.removeClass("bg-danger").addClass("bg-primary");
    modalTitle.text("Formulario de Registro de Coordinación");

    $("#proceso").text("REGISTRAR").removeClass("btn-danger btn-warning").addClass("btn-primary");
    $("#proceso").prop("disabled", true); 
    $("#modal1").modal("show");
  });
});

function validarenvio() {
    if ($("#proceso").is(":disabled")) {
        Swal.fire({
            icon: 'error',
            title: 'Acción no permitida',
            text: 'Por favor, corrija los errores en el formulario antes de continuar.'
        });
        return false;
    }
    return true;
}

function pone(pos, accion) {
  let linea = $(pos).closest("tr");
  originalNombreCoordinacion = $(linea).find("td:eq(0)").text(); 
  
  let modalHeader = $("#modal1 .modal-header");
  let modalTitle = $("#modal1 .modal-title");
  let procesoBtn = $("#proceso");

  if (accion === 0) { 
    modalTitle.text("Formulario de Modificación de Coordinación");
    procesoBtn.text("MODIFICAR");
    procesoBtn.removeClass("btn-danger").addClass("btn-primary");

    $("#coordinacionNombre").prop("disabled", false);

    procesoBtn.prop("disabled", true);
    $("#scoordinacionNombre").text("Realice un cambio para poder modificar.").show();

  } else { 
    modalTitle.text("Confirmar Eliminación de Coordinación");
    procesoBtn.text("ELIMINAR");
    
    $("#coordinacionNombre").prop("disabled", true);
    procesoBtn.prop("disabled", false);
  }
  
  $("#coordinacionNombre").val(originalNombreCoordinacion);
  
  if (accion !== 0) {
    $("#scoordinacionNombre").hide();
  }
  $("#modal1").modal("show");
} 

function enviaAjax(datos) {
  $.ajax({
    async: true, url: "", type: "POST", contentType: false, data: datos,
    processData: false, cache: false, timeout: 10000,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
              const btnModificar = `<button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
            const btnEliminar = `<button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
            $("#resultadoconsulta").append(`
              
              <tr>
                <td>${item.cor_nombre}</td>
                <td>
                      ${btnModificar}
                  ${btnEliminar}
                </td>
              </tr>
            `);
          });
          crearDT();
        } 
        else if (lee.resultado === "registrar" || lee.resultado === "modificar" || lee.resultado === "eliminar") {
          let tituloMayusculas = lee.resultado.toUpperCase();

          Swal.fire({
            icon: 'success',
            title: tituloMayusculas,
            text: lee.mensaje,
            timer: 2000
          });

          $("#modal1").modal("hide");
          Listar();
        }
        else if (lee.resultado === "existe") {
            $("#scoordinacionNombre").text(lee.mensaje).show();
            $("#proceso").prop("disabled", true);
        } else if (lee.resultado === "no_existe") {
            $("#scoordinacionNombre").text("").hide();
            $("#proceso").prop("disabled", false);
        } else if (lee.resultado === "error") {
            Swal.fire({ icon: 'error', title: 'Error', html: lee.mensaje });
        }
      } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error en respuesta', text: 'No se pudo procesar la respuesta del servidor.'});
        console.error("Error en JSON: ", e, "Respuesta: ", respuesta);
      }
    },
    error: function (request, status, err) {
      Swal.fire({ icon: 'error', title: 'Error de Conexión', text: "Ocurrió un problema de comunicación." });
    }
  });
}

function limpia() {
  $("#coordinacionNombre").val("").prop('disabled', false);
  $("#scoordinacionNombre").text("");
  originalNombreCoordinacion = '';
}

function validarkeypress(er, e) {
  let key = e.keyCode || e.which;
  let teclado = String.fromCharCode(key);
  if (!er.test(teclado)) {
    e.preventDefault();
  }
}

function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
  if (etiqueta.val() === "" || !er.test(etiqueta.val())) {
    etiquetamensaje.text(mensaje).show();
    return false;
  } else {
    etiquetamensaje.hide().text("");
    return true;
  }
}