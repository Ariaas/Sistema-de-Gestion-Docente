function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablatitulo")) {
    $("#tablatitulo").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablatitulo")) {
    $("#tablatitulo").DataTable({
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
      order: [[0, "asc"]],
      dom:
        "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });
  }
}

function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true
    });
}

function pone(pos, accion) {
    limpia();
    let linea = $(pos).closest("tr");
    let prefijo = $(linea).find("td:eq(0)").text();
    let nombre = $(linea).find("td:eq(1)").text();

    $("#tituloprefijo").val(prefijo);
    $("#titulonombre").val(nombre);

    if (accion === 0) {
        $("#proceso").text("MODIFICAR");
        $("#tituloprefijo, #titulonombre").prop("disabled", false);
       
        $("#tituloprefijo_original").val(prefijo);
        $("#titulonombre_original").val(nombre);
    } else { 
        $("#proceso").text("ELIMINAR");
        $("#tituloprefijo, #titulonombre").prop("disabled", true);
    }
    $("#modal1").modal("show");
}

function validarenvio() {
    let prefijo = $("#tituloprefijo").val();
    if (!prefijo) {
        muestraMensaje("error", 4000, "¡ERROR!", "Por favor, seleccione un prefijo."); 
        return false;
    }
    if (!/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/.test($("#titulonombre").val())) {
        muestraMensaje("error", 4000, "¡ERROR!", "El nombre del título no es válido.<br/>Debe contener entre 5 y 30 letras.");
        return false;
    }
    return true;
}

function enviaAjax(datos) {
    $.ajax({
        async: true, url: "", type: "POST", contentType: false,
        data: datos, processData: false, cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === "consultar") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    lee.mensaje.forEach(function(item) {
                         const btnModificar = `<button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'  ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
                       const btnEliminar = `<button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'  ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                        $("#resultadoconsulta").append(`
                            <tr>
                                <td>${item.tit_prefijo}</td>
                                <td>${item.tit_nombre}</td>
                                <td>
                                    ${btnModificar}
                                    ${btnEliminar}
                                </td>
                            </tr>
                        `);
                    });
                    crearDT();
                } else if (lee.resultado === "registrar" || lee.resultado === "modificar" || lee.resultado === "eliminar") {
                    muestraMensaje("success", 4000, "¡ÉXITO!", lee.mensaje);
                    $("#modal1").modal("hide");
                    Listar();
                } else if (lee.resultado === 'existe') {
                    $("#stitulonombre").text(lee.mensaje);
                } else if (lee.resultado === 'no_existe') {
                    $("#stitulonombre").text('');
                } else if (lee.resultado === "error") {
                    muestraMensaje("error", 8000, "¡ERROR!", lee.mensaje);
                }
            } catch (e) {
                console.error("Error al procesar JSON: ", e, respuesta);
                muestraMensaje("error", 8000, "Error de Respuesta", "No se pudo procesar la respuesta del servidor.");
            }
        },
        error: (request, status, err) => muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", `Ocurrió un error: ${status} - ${err}`)
    });
}

function limpia() {
    $("#tituloprefijo").val("");
    $("#titulonombre").val("");
    $("#tituloprefijo_original").val("");
    $("#titulonombre_original").val("");
    $(".text-danger").text("");
    $("#tituloprefijo, #titulonombre").prop("disabled", false);
}

$(document).ready(function() {
    Listar();

    $("#titulonombre, #tituloprefijo").on("keyup change", function () {
        if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/, $("#titulonombre"), $("#stitulonombre"), "")) {
            let datos = new FormData();
            datos.append('accion', 'existe');
            datos.append("tituloprefijo", $("#tituloprefijo").val());
            datos.append("titulonombre", $("#titulonombre").val());
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("#modal1").modal("show");
    });

    $("#proceso").on("click", function() {
        if (!validarenvio()) {
            return;
        }

        let accion = $(this).text().toLowerCase();
        let datos = new FormData();
        datos.append("accion", accion);

        if (accion === "registrar") {
            datos.append("tituloprefijo", $("#tituloprefijo").val());
            datos.append("titulonombre", $("#titulonombre").val());
        } else if (accion === "modificar") {
            datos.append("tituloprefijo_original", $("#tituloprefijo_original").val());
            datos.append("titulonombre_original", $("#titulonombre_original").val());
            datos.append("tituloprefijo", $("#tituloprefijo").val());
            datos.append("titulonombre", $("#titulonombre").val());
        } else if (accion === "eliminar") {
            Swal.fire({
                title: "¿Está seguro de eliminar este título?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    let datos_eliminar = new FormData();
                    datos_eliminar.append("accion", "eliminar");
                    datos_eliminar.append("tituloprefijo", $("#tituloprefijo").val());
                    datos_eliminar.append("titulonombre", $("#titulonombre").val());
                    enviaAjax(datos_eliminar);
                } else {
                    $("#modal1").modal("hide");
                }
            });
            return; 
        }
        enviaAjax(datos);
    });
});