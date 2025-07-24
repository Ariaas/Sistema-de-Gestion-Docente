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
    $("#stituloprefijo, #stitulonombre").hide();
}

function validarenvio() {
    let esValido = true;
    
    if ($("#tituloprefijo").val() == "" || $("#tituloprefijo").val() == null) {
        $("#stituloprefijo").text("Debe seleccionar un prefijo.").show();
        if(esValido) muestraMensaje("error", 4000, "¡ERROR!", "Debe seleccionar un prefijo para el título.");
        esValido = false;
    } else {
        $("#stituloprefijo").text("").hide();
    }

    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/, $("#titulonombre"), $("#stitulonombre"), "El nombre debe tener entre 5 y 30 letras.") === 0) {
        if(esValido) muestraMensaje("error", 4000, "¡ERROR!", "El formato del nombre del título es incorrecto.");
        esValido = false;
    }
    
    return esValido;
}

function enviaAjax(datos, accion) {
    $.ajax({
        async: true, url: "", type: "POST", contentType: false,
        data: datos, processData: false, cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);

                if (accion === 'existe') {
                    if (lee.resultado === 'existe') {
                        $("#stitulonombre").text(lee.mensaje).css("color", "red").show();
                        $("#proceso").prop("disabled", true);
                    } else {
                        $("#stitulonombre").text("").css("color", "");
                        $("#proceso").prop("disabled", false);
                        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/, $("#titulonombre"), $("#stitulonombre"), "El nombre debe tener entre 5 y 30 letras.");
                    }
                    return;
                }

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
    $("#stituloprefijo").text("").hide();
    $("#stitulonombre").text("").hide();
    $("#tituloprefijo, #titulonombre").prop("disabled", false);
}

$(document).ready(function() {
    Listar();

    $("#titulonombre, #tituloprefijo").on("keyup change", function () {
        $("#stitulonombre").css("color", "");

        if ($("#tituloprefijo").val() !== "" && $("#tituloprefijo").val() !== null) {
            $("#stituloprefijo").text("").hide();
        }

        let nombreValido = validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/, $("#titulonombre"), $("#stitulonombre"), "El nombre debe tener entre 5 y 30 letras.");
        let prefijoValido = $("#tituloprefijo").val() !== "" && $("#tituloprefijo").val() !== null;

        if (nombreValido === 1 && prefijoValido) {
            let datos = new FormData();
            datos.append('accion', 'existe');
            datos.append("tituloprefijo", $("#tituloprefijo").val());
            datos.append("titulonombre", $("#titulonombre").val());
            
            if ($("#proceso").text() === "MODIFICAR") {
                datos.append('tituloprefijo_original', $("#tituloprefijo_original").val());
                datos.append('titulonombre_original', $("#titulonombre_original").val());
            }
            
            enviaAjax(datos, 'existe');
        }
    });

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("#modal1").modal("show");
        $("#stituloprefijo, #stitulonombre").show();
    });

    $("#proceso").on("click", function() {
        let accion = $(this).text().toLowerCase();

        if (accion === "eliminar") {
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

        if (!validarenvio()) {
            return;
        }

        let datos = new FormData();
        datos.append("accion", accion);
        datos.append("tituloprefijo", $("#tituloprefijo").val());
        datos.append("titulonombre", $("#titulonombre").val());

        if (accion === "modificar") {
            datos.append("tituloprefijo_original", $("#tituloprefijo_original").val());
            datos.append("titulonombre_original", $("#titulonombre_original").val());
        }
        
        enviaAjax(datos);
    });
});