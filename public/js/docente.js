$(document).ready(function() {
    let dataTable;

    Listar();

    $("#incluir").on("click", function () {
        limpia();
        $("#proceso").text("REGISTRAR")
                     .removeClass("btn-danger btn-warning")
                     .addClass("btn-primary");
        $("#modal1 .modal-title").text("Formulario de Registro de Docente");
        $("#modal1").modal("show");
    });

    $("#proceso").on("click", function () {
        const textoBoton = $(this).text();

        if (textoBoton === "REGISTRAR" || textoBoton === "MODIFICAR") {
            if (validarenvio()) {
                const datos = new FormData($('#f')[0]);
                const accion = (textoBoton === "REGISTRAR") ? "incluir" : "modificar";
                datos.append("accion", accion);

                if (accion === 'modificar') {
                    datos.append("prefijoCedula", $("#prefijoCedula").val());
                    datos.append("cedulaDocente", $("#cedulaDocente").val());
                }
                
                enviaAjax(datos);
            }
        } else if (textoBoton === "ELIMINAR") {
            Swal.fire({
                title: "¿Está seguro de eliminar este docente?",
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
                    datos.append("cedulaDocente", $("#cedulaDocente").val());
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

    
    $("#cedulaDocente").on("keyup", function () {
        // Se asegura de que el botón se habilite/deshabilite según la validación del formato
        if (!validarkeyup(/^[0-9]{7,8}$/, $(this), $("#scedulaDocente"), "Debe ser una cédula válida (7-8 dígitos).")){
            $("#proceso").prop("disabled", true);
            return;
        } else {
            // Si el formato es correcto y estamos registrando, se procede a verificar la existencia
            if ($("#proceso").text() === "REGISTRAR") {
                const datos = new FormData();
                datos.append('accion', 'Existe');
                datos.append('cedulaDocente', $(this).val());
                enviaAjax(datos);
            } else {
                $("#proceso").prop("disabled", false);
            }
        }
    });

    $("#nombreDocente").on("keyup", function() { validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $(this), $("#snombreDocente"), "El nombre es requerido."); });
    $("#apellidoDocente").on("keyup", function() { validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $(this), $("#sapellidoDocente"), "El apellido es requerido."); });
    $("#correoDocente").on("keyup", function() { validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $(this), $("#scorreoDocente"), "El formato de correo no es válido."); });

   

    function Listar() {
        const datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function validarenvio() {
        let esValido = true;
        if (!validarkeyup(/^[0-9]{7,8}$/, $("#cedulaDocente"), $("#scedulaDocente"), "Cédula inválida.")) esValido = false;
        if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $("#nombreDocente"), $("#snombreDocente"), "Nombre inválido.")) esValido = false;
        if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $("#apellidoDocente"), $("#sapellidoDocente"), "Apellido inválido.")) esValido = false;
        if (!validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $("#correoDocente"), $("#scorreoDocente"), "Correo inválido.")) esValido = false;
        if (!$("#categoria").val()) esValido = false;
        if (!$("#dedicacion").val()) esValido = false;
        if (!$("#condicion").val()) esValido = false;
        if ($("input[name='titulos[]']:checked").length === 0) {
            $("#stitulos").text("Debe seleccionar al menos un título.");
            esValido = false;
        } else { $("#stitulos").text(""); }
        if ($("input[name='coordinaciones[]']:checked").length === 0) {
            $("#scoordinaciones").text("Debe seleccionar al menos una coordinación.");
            esValido = false;
        } else { $("#scoordinaciones").text(""); }

        if (!esValido) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise los campos del formulario.");
        }
        return esValido;
    }

    
    function pone(pos, accion) {
        limpia();
        const fila = $(pos).closest("tr");
        
        const prefijo = fila.find("td:eq(0)").text();
        const cedula = fila.find("td:eq(1)").text();
        const nombre = fila.find("td:eq(2)").text();
        const apellido = fila.find("td:eq(3)").text();
        const correo = fila.find("td:eq(4)").text();
        const categoria = fila.find("td:eq(5)").text();
        const dedicacion = fila.find("td:eq(8)").text();
        const condicion = fila.find("td:eq(9)").text().trim();
        const titulosIds = fila.attr('data-titulos-ids');
        const coordinacionesIds = fila.attr('data-coordinaciones-ids');

        $("#prefijoCedula").val(prefijo);
        $("#cedulaDocente").val(cedula);
        $("#nombreDocente").val(nombre);
        $("#apellidoDocente").val(apellido);
        $("#correoDocente").val(correo);
        $('#dedicacion').val(dedicacion);
        $('#condicion').val(condicion);
        $('#categoria option').filter(function() { return $(this).text() == categoria; }).prop('selected', true);
        
        if (titulosIds) titulosIds.split(',').forEach(id => { if(id) $("#titulo_" + id.trim()).prop('checked', true); });
        if (coordinacionesIds) coordinacionesIds.split(',').forEach(id => { if(id) $("#coordinacion_" + id.trim()).prop('checked', true); });

        if (accion === 'modificar') {
            $("#proceso").text("MODIFICAR").removeClass("btn-danger").addClass("btn-primary");
            $("#modal1 .modal-title").text("Formulario de Modificación de Docente");
            $("#prefijoCedula, #cedulaDocente").prop('disabled', true);
        } else if (accion === 'eliminar') {
            $("#proceso").text("ELIMINAR").removeClass("btn-primary").addClass("btn-danger");
            $("#modal1 .modal-title").text("Confirmar Eliminación de Docente");
            
            $("form#f .form-control, form#f .form-select, form#f .form-check-input").prop('disabled', true);
            
            $("#proceso").prop('disabled', false);
        }
        
        $("#modal1").modal("show");
    }
 
    
    function limpia() {
        $("form#f")[0].reset();
        $("form#f :input").prop('disabled', false);
        $(".text-danger").text("");
    }

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
    }
    
    function enviaAjax(datos) {
        $.ajax({
            async: true, url: "", type: "POST", contentType: false, data: datos,
            processData: false, cache: false, timeout: 10000,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    
                    // ===== MODIFICACIÓN: Bloque para manejar la respuesta de la verificación de cédula =====
                    if (typeof lee.existe !== 'undefined') {
                        if (lee.existe) {
                            muestraMensaje("error", 4000, "Cédula Duplicada", "Ya hay un docente registrado con esta cédula.");
                            $("#scedulaDocente").text("Cédula ya registrada.");
                            $("#proceso").prop("disabled", true); // Deshabilita el botón de guardar
                        } else {
                            // Si la cédula no existe, se limpia el mensaje de error y se habilita el botón.
                            $("#scedulaDocente").text("");
                            $("#proceso").prop("disabled", false);
                        }
                        return; // Detiene la ejecución para no procesar el switch de abajo
                    }
                    // ===== FIN DE LA MODIFICACIÓN =====

                    switch (lee.resultado) {
                        case 'consultar':
                            if ($.fn.DataTable.isDataTable("#tabladocente")) {
                                $("#tabladocente").DataTable().destroy();
                            }
                            $("#resultadoconsulta").empty();
                            lee.mensaje.forEach(item => {
                                $("#resultadoconsulta").append(`
                                    <tr data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}">
                                        <td>${item.doc_prefijo}</td>
                                        <td>${item.doc_cedula}</td>
                                        <td>${item.doc_nombre}</td>
                                        <td>${item.doc_apellido}</td>
                                        <td>${item.doc_correo}</td>
                                        <td>${item.cat_nombre}</td>
                                        <td>${item.titulos || 'Sin títulos'}</td>
                                        <td>${item.coordinaciones || 'Sin coordinaciones'}</td>
                                        <td>${item.doc_dedicacion}</td>
                                        <td>${item.doc_condicion}</td>
                                        <td>
                                            <button class="btn btn-warning btn-sm modificar-btn">Modificar</button>
                                            <button class="btn btn-danger btn-sm eliminar-btn">Eliminar</button>
                                        </td>
                                    </tr>`);
                            });
                            dataTable = $("#tabladocente").DataTable({
                                paging: true, lengthChange: true, searching: true, ordering: true,
                                info: true, autoWidth: false, responsive: true, scrollX: true,
                                language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
                                order: [[1, "asc"]]
                            });
                            break;
                        case 'incluir':
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

    function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
        if (!etiqueta.val() || !er.test(etiqueta.val())) {
            etiquetamensaje.text(mensaje);
            return false;
        }
        etiquetamensaje.text("");
        return true;
    }
});