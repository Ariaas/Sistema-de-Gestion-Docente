$(document).ready(function() {
    let dataTable;


    Listar();
    cargarTrayectos();

 
    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("#modal1 .modal-title").text("Formulario de Registro de Convenio");
        $(".form-control, .form-select").prop("disabled", false);
        $("#modal1").modal("show");
    });

  
    $("#proceso").on("click", function() {
        const textoBoton = $(this).text();

        if (textoBoton === "Guardar" || textoBoton === "MODIFICAR") {
            if (validarenvio()) {
                const datos = new FormData($("#f")[0]);
                const accion = (textoBoton === "REGISTRAR") ? "registrar" : "MODIFICAR";
                datos.append("accion", accion);
                enviaAjax(datos);
            }
        } else if (textoBoton === "ELIMINAR") {
            Swal.fire({
                title: "¿Está seguro de eliminar este convenio?",
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
                    datos.append("convenioId", $("#convenioId").val());
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


    
    $("#convenioNombre").on("keyup", function() {
        validarkeyup(/^[A-Za-z0-9\s.,-]{4,30}$/, $(this), $("#sconvenioNombre"), "El nombre debe tener entre 4 y 30 caracteres.");
    });
    $("#traId").on("change", function() {
        validarSelect($(this), $("#straId"), "Seleccione un trayecto.");
    });
    $("#convenioInicio").on("change", function() {
        validarFecha($(this), $("#sconvenioInicio"), "Seleccione una fecha de inicio.");
    });

  

    function Listar() {
        const datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function cargarTrayectos() {
        const datos = new FormData();
        datos.append("accion", "consultar_trayectos");
        enviaAjax(datos);
    }

    function destruyeDT() {
        if ($.fn.DataTable.isDataTable("#tablaconvenio")) {
            $('#tablaconvenio').DataTable().destroy();
        }
    }

    function crearDT() {
        if (!$.fn.DataTable.isDataTable("#tablaconvenio")) {
            dataTable = $("#tablaconvenio").DataTable({
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
                order: [[1, "asc"]]
            });
        }
    }

    function validarenvio() {
        if (!validarkeyup(/^[A-Za-z0-9\s.,-]{4,30}$/, $("#convenioNombre"), $("#sconvenioNombre"), "El nombre debe tener entre 4 y 30 caracteres.")) {
            muestraMensaje("error", 4000, "Error de validación", "Por favor, corrija el nombre del convenio.");
            return false;
        }
        if (!validarSelect($("#traId"), $("#straId"), "Debe seleccionar un trayecto.")) {
            muestraMensaje("error", 4000, "Error de validación", "Por favor, seleccione un trayecto.");
            return false;
        }
        if (!validarFecha($("#convenioInicio"), $("#sconvenioInicio"), "Debe seleccionar una fecha de inicio.")) {
            muestraMensaje("error", 4000, "Error de validación", "Por favor, seleccione una fecha de inicio válida.");
            return false;
        }
        return true;
    }
    
    function validarSelect(input, span, mensaje) {
        if (!input.val()) {
            span.text(mensaje).show();
            return false;
        }
        span.hide();
        return true;
    }

    function validarFecha(input, span, mensaje) {
        if (!input.val()) {
            span.text(mensaje).show();
            return false;
        }
        span.hide();
        return true;
    }

    function pone(pos, accion) {
        const fila = $(pos).closest("tr");
        const datosFila = dataTable.row(fila).data();
        
        limpia();
        $("#convenioId").val(datosFila[0]);
        $("#convenioNombre").val(datosFila[1]);
        $("#convenioInicio").val(datosFila[2]);
        $("#traId").val(datosFila[4]);
        
        if (accion === 'modificar') {
            $("#proceso").text("MODIFICAR");
            $("#modal1 .modal-title").text("Formulario de Modificación de Convenio");
        } else if (accion === 'eliminar') {
            $("#proceso").text("ELIMINAR");
            $("#modal1 .modal-title").text("Confirmar Eliminación de Convenio");
            $("#convenioNombre, #traId, #convenioInicio").prop("disabled", true);
        }
        
        $("#modal1").modal("show");
    }

    function limpia() {
        $("#f")[0].reset();
        $(".form-control, .form-select").prop('disabled', false);
        $("#sconvenioNombre, #straId, #sconvenioInicio").hide().text("");
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
            async: true,
            url: "",
            type: "POST",
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
            timeout: 10000,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    const resultado = lee.resultado;

                    switch (resultado) {
                        case 'consultar':
                            destruyeDT();
                            $("#resultadoconsulta").empty();
                            lee.mensaje.forEach(item => {
                                $("#resultadoconsulta").append(`
                                  <tr>
                                    <td style="display: none;">${item.con_id}</td>
                                    <td>${item.con_nombre}</td>
                                    <td>${item.con_inicio}</td>
                                    <td>Trayecto ${item.tra_numero} (${item.ani_anio})</td>
                                    <td style="display: none;">${item.tra_id}</td>
                                    <td>
                                      <button class="btn btn-warning btn-sm modificar-btn">Modificar</button>
                                      <button class="btn btn-danger btn-sm eliminar-btn">Eliminar</button>
                                    </td>
                                  </tr>
                                `);
                            });
                            crearDT();
                            break;

                        case 'consultar_trayectos':
                            const selectTrayecto = $("#traId");
                            selectTrayecto.empty().append('<option value="">Seleccione un trayecto</option>');
                            lee.mensaje.forEach(item => {
                                selectTrayecto.append(`<option value="${item.tra_id}">Trayecto ${item.tra_numero} (${item.tra_tipo}) - Año ${item.ani_anio}</option>`);
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
                            muestraMensaje("error", 5000, "ERROR", lee.mensaje);
                            break;

                        default:
                            muestraMensaje("info", 4000, "ATENCIÓN", lee.mensaje);
                            break;
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta:", e, "Respuesta:", respuesta);
                    muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta del servidor.");
                }
            },
            error: function(request, status, err) {
                muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", status == "timeout" ? "Servidor ocupado, intente de nuevo." : "Ocurrió un error: " + err);
            }
        });
    }
});