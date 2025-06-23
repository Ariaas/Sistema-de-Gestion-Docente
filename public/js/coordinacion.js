$(document).ready(function() {
    let dataTable;

    Listar();



    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR")
                     .removeClass("btn-danger btn-warning")
                     .addClass("btn-primary");
        $("#modal1 .modal-title").text("Formulario de Registro de Coordinación");
        $("#modal1").modal("show");
    });

    $("#proceso").on("click", function() {
        const textoBoton = $(this).text();

        if (textoBoton === "REGISTRAR" || textoBoton === "MODIFICAR") {
            if (validarenvio()) {
                const datos = new FormData($("#f")[0]);
                const accion = (textoBoton === "REGISTRAR") ? "registrar" : "modificar";
                datos.append("accion", accion);
                enviaAjax(datos);
            }
        } else if (textoBoton === "ELIMINAR") {
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
                    const datos = new FormData();
                    datos.append("accion", "eliminar");
                    datos.append("coordinacionId", $("#coordinacionId").val());
                    enviaAjax(datos);
                }
            });
        }
    });

   
    $(document).on('click', '.modificar-btn', function() {
        pone(this, 'modificar');
    });

    $(document).on('click', '.eliminar-btn', function() {
        pone(this, 'eliminar');
    });

   
    $("#coordinacionNombre").on("keyup", function() {
        validarkeyup(/^[A-Za-z\sñÑáéíóúÁÉÍÓÚ-]{4,30}$/, $(this), $("#scoordinacionNombre"), "El nombre debe tener entre 4 y 30 caracteres.");
    });




    function Listar() {
        const datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function validarenvio() {
        if (!validarkeyup(/^[A-Za-z\sñÑáéíóúÁÉÍÓÚ-]{4,30}$/, $("#coordinacionNombre"), $("#scoordinacionNombre"), "El nombre debe tener entre 4 y 30 caracteres.")) {
            muestraMensaje("error", 4000, "Error de validación", "Por favor, corrija el nombre de la coordinación.");
            return false;
        }
        return true;
    }
    
   
    function pone(pos, accion) {
        const fila = $(pos).closest("tr");
        const id = fila.find("td:eq(0)").text();
        const nombre = fila.find("td:eq(1)").text();
        
        limpia();
        $("#coordinacionId").val(id);
        $("#coordinacionNombre").val(nombre);
        
        if (accion === 'modificar') {
            $("#proceso").text("MODIFICAR")
                         .removeClass("btn-danger btn-warning")
                         .addClass("btn-primary");
            $("#modal1 .modal-title").text("Formulario de Modificación de Coordinación");
        } else if (accion === 'eliminar') {
            $("#proceso").text("ELIMINAR")
                         .removeClass("btn-primary btn-warning")
                         .addClass("btn-danger");
            $("#modal1 .modal-title").text("Confirmar Eliminación de Coordinación");
         
            $("#coordinacionNombre").prop("disabled", true);
        }
        
       
        $("#modal1").modal("show");
    }
 
    
    function limpia() {
        $("#f")[0].reset();
        $("#coordinacionNombre").prop('disabled', false);
        $("#scoordinacionNombre").hide().text("");
    }
    
    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({
            icon: tipo,
            title: titulo,
            html: mensaje,
            timer: duracion,
            timerProgressBar: true,
        });
    }



    function enviaAjax(datos) {
        $.ajax({
            async: true, url: "", type: "POST", contentType: false, data: datos,
            processData: false, cache: false, timeout: 10000,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    switch (lee.resultado) {
                        case 'consultar':
                            if ($.fn.DataTable.isDataTable("#tablacoordinacion")) {
                                $("#tablacoordinacion").DataTable().destroy();
                            }
                            $("#resultadoconsulta").empty();
                            lee.mensaje.forEach(item => {
                                $("#resultadoconsulta").append(`
                                  <tr>
                                    <td style="display: none;">${item.cor_id}</td>
                                    <td>${item.cor_nombre}</td>
                                    <td>
                                      <button class="btn btn-warning btn-sm modificar-btn">Modificar</button>
                                      <button class="btn btn-danger btn-sm eliminar-btn">Eliminar</button>
                                    </td>
                                  </tr>
                                `);
                            });
                            dataTable = $("#tablacoordinacion").DataTable({
                                paging: true, lengthChange: true, searching: true, ordering: true,
                                info: true, autoWidth: false, responsive: true,
                                language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
                                order: [[1, "asc"]]
                            });
                            break;
                        case 'registrar':
                        case 'modificar':
                        case 'eliminar':
                            muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                            $("#modal1").modal("hide");
                            Listar();
                            break;
                        case 'error':
                        default:
                            muestraMensaje("error", 5000, "ERROR", lee.mensaje || "Ocurrió un error inesperado.");
                            break;
                    }
                } catch (e) {
                    console.error("Error:", e, "Respuesta:", respuesta);
                    muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta.");
                }
            },
            error: (request, status, err) => muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", `Ocurrió un error: ${err}`)
        });
    }
});


function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
    if (etiqueta.val() === "") {
        etiquetamensaje.text("");
        return 0;
    } else if (er.test(etiqueta.val())) {
        etiquetamensaje.text("");
        return 1;
    } else {
        etiquetamensaje.text(mensaje);
        return 0;
    }
}