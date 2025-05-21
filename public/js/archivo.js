function listarArchivos() {
    var datos = new FormData();
    datos.append("accion", "listar"); 
    enviaAjax(datos);
    destruyeDT();
    crearDT();
}
function destruyeDT() {

    if ($.fn.DataTable.isDataTable("#tablaArchivo")) { 
        $("#tablaArchivo").DataTable().destroy();
    }
}

function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaArchivo")) { 
        $("#tablaArchivo").DataTable({
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



function enviaAjax(datos) {
    $.ajax({
        async: true,
        url: "?pagina=archivo", 
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        beforeSend: function () {
           
        },
        timeout: 10000, 
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);

                if (lee.resultado === "listar") {
                    destruyeDT();
                    $("#resultados").empty(); 
                    if (Array.isArray(lee.datos) && lee.datos.length > 0) {
                        $.each(lee.datos, function (index, item) {
                            const downloadPath = 'archivos_subidos/' + encodeURIComponent(item.nombre_guardado);
                            $("#resultados").append(`
                                <tr>
                                    <td class="align-middle">
                                        <a href="${downloadPath}"
                                           download="${htmlspecialchars(item.nombre_guardado)}"
                                           class="text-decoration-none link-primary">
                                            <i class="fas fa-file-download me-2"></i>
                                            ${htmlspecialchars(item.nombre_guardado)}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <button onclick="eliminarArchivo('${encodeURIComponent(item.nombre_guardado)}')"
                                            class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } 
                    crearDT();
                }

                else if (lee.success !== "success") { 
                    if (lee.success) {
                        muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                        $("#modalArchivo").modal("hide");
                        listarArchivos(); 
                    } else {
                        muestraMensaje("error", 5000, "ERROR", lee.mensaje);
                    }
                    destruyeDT();
                    crearDT();
                }
                else if (lee.resultado === "eliminar") {
                    muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                    listarArchivos();
                }
                else {
                    muestraMensaje("error", 10000, "ERROR", "Respuesta inesperada del servidor: " + lee.mensaje || JSON.stringify(lee));
                }
                destruyeDT();
                crearDT();

            } catch (e) {
                console.error("Error en análisis JSON:", e);
                muestraMensaje("error", 10000, "ERROR!!!!", "Respuesta inválida del servidor. Detalles en consola.");
            }
        },
        error: function (request, status, err) {
            console.error("Error de petición AJAX:", status, err, request.responseText);
            if (status == "timeout") {
                muestraMensaje("error", 5000, "ERROR", "Servidor ocupado o conexión lenta, intente de nuevo.");
            } else {
                muestraMensaje("error", 10000, "ERROR DE CONEXIÓN", "Hubo un problema al comunicarse con el servidor. Detalles en consola.");
            }
        },

    });
}


function eliminarArchivo(nombreArchivoEncoded) {
    const nombreArchivo = decodeURIComponent(nombreArchivoEncoded);

    Swal.fire({
        title: "¿Está seguro de eliminar este archivo?",
        text: `Esta acción eliminará el archivo: "${nombreArchivo}". ¡No se puede deshacer!`,
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
            datos.append("nombre_archivo", nombreArchivoEncoded); 
            enviaAjax(datos);
            listarArchivos();
        } else {
            muestraMensaje("info", 2000, "CANCELADO", "La eliminación ha sido cancelada.");
        }
    });
}


function htmlspecialchars(str) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(str).replace(/[&<>"']/g, function(m) { return map[m]; });
}


$(document).ready(function () {
    listarArchivos(); 

    $('#btnSubir').click(function() {
        $('#modalArchivo').modal('show');
    });


    $('#modalArchivo').on('hidden.bs.modal', function () {
        $('#formArchivo')[0].reset(); 
        $('#docente').val('');
        $('#ucurricular').val('');
        $('#sdocente').text('');
        $('#sucurricular').text('');
    });

    $('#formArchivo').submit(function(e) {
        e.preventDefault(); 

        const archivoInput = document.getElementById('archivo');
        const docenteSelect = document.getElementById('docente');
        const ucurricularSelect = document.getElementById('ucurricular');
        const fechaInput = document.getElementById('fecha');

        let isValid = true;

        if (!archivoInput.files.length) {
            muestraMensaje("error", 3000, "ERROR", "Debe seleccionar un archivo.");
            isValid = false;
        }
        if (!docenteSelect.value) {
            muestraMensaje("error", 3000, "ERROR", "Debe seleccionar un docente.");
            $('#sdocente').text('Seleccione un docente.');
            isValid = false;
        } else {
            $('#sdocente').text('');
        }
        if (!ucurricularSelect.value) {
            muestraMensaje("error", 3000, "ERROR", "Debe seleccionar una unidad curricular.");
            $('#sucurricular').text('Seleccione una unidad curricular.');
            isValid = false;
        } else {
            $('#sucurricular').text('');
        }
        if (!fechaInput.value) {
            muestraMensaje("error", 3000, "ERROR", "Debe seleccionar una fecha.");
            isValid = false;
        }

        if (!isValid) {
            return; 
        }

        var formData = new FormData(this);
        formData.append('accion', 'subir'); 

        enviaAjax(formData); 
    });

});

function muestraMensaje(icon, timer, title, text) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        showConfirmButton: false,
        timer: timer,
    });
}