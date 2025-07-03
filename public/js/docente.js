
function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

    function destruyeDT() {
        if ($.fn.DataTable.isDataTable("#tabladocente")) {
            $("#tabladocente").DataTable().destroy();
        }
    }

  
    function crearDT() {
        if (!$.fn.DataTable.isDataTable("#tabladocente")) {
            $("#tabladocente").DataTable({
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                scrollX: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
                },
                order: [
                    [1, "asc"]
                ]
            });
        }
    }
$(document).ready(function() {
    
    Listar();

 


    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("form#f :input").prop('disabled', false);
        $("#modal1 .modal-title").text("Formulario de Registro de Docente");
        $("#modal1").modal("show");
    });


    $("#proceso").on("click", function() {
        const textoBoton = $(this).text();

        if (textoBoton === "REGISTRAR") {
            if (validarenvio()) {
                const datos = new FormData($('#f')[0]);
                datos.append("accion", "incluir");
                enviaAjax(datos);
            }
        } else if (textoBoton === "MODIFICAR") {
            if (validarenvio()) {
                const datos = new FormData($('#f')[0]);
                datos.append("accion", "modificar");
                datos.append("prefijoCedula", $("#prefijoCedula").val());
                datos.append("cedulaDocente", $("#cedulaDocente").val());
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

    $(document).on('click', '.ver-horas-btn', function() {
        const fila = $(this).closest("tr");
        const doc_id = fila.data('doc-id');
        const nombreCompleto = fila.find("td:eq(2)").text() + ' ' + fila.find("td:eq(3)").text();
        $("#nombreDocenteHoras").text(nombreCompleto);
        const datos = new FormData();
        datos.append("accion", "consultar_horas");
        datos.append("doc_id", doc_id);
        enviaAjax(datos);
    });


    $("#cedulaDocente").on("input", function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    $("#nombreDocente, #apellidoDocente").on("input", function() {
        this.value = this.value.replace(/[0-9]/g, '');
    });

    $("#cedulaDocente").on("keyup", function() {
        if (!validarkeyup(/^[0-9]{7,8}$/, $(this), $("#scedulaDocente"), "Debe ser una cédula válida (7-8 dígitos).")) {
            $("#proceso").prop("disabled", true);
            return;
        } else {
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

    $("#nombreDocente").on("keyup", function() {
        validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{4,30}$/, $(this), $("#snombreDocente"), "El formato del nombre es inválido.");
    });
    $("#apellidoDocente").on("keyup", function() {
        validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{4,30}$/, $(this), $("#sapellidoDocente"), "El formato del apellido es inválido.");
    });
    $("#correoDocente").on("keyup", function() {
        validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $(this), $("#scorreoDocente"), "El formato del correo es inválido.");
    });

    function Listar() {
        const datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function validarenvio() {
        let esValido = true;

        if (!validarkeyup(/^[0-9]{7,8}$/, $("#cedulaDocente"), $("#scedulaDocente"), "Debe ser una cédula válida (7-8 dígitos).")) esValido = false;
        if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{3,30}$/, $("#nombreDocente"), $("#snombreDocente"), "El formato del nombre es inválido.")) esValido = false;
        if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{3,30}$/, $("#apellidoDocente"), $("#sapellidoDocente"), "El formato del apellido es inválido.")) esValido = false;
        if (!validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $("#correoDocente"), $("#scorreoDocente"), "El formato del correo es inválido.")) esValido = false;

        if (!$("#categoria").val()) {
            $("#scategoria").text("Debe seleccionar una categoría.");
            esValido = false;
        } else {
            $("#scategoria").text("");
        }

        if (!$("#dedicacion").val()) {
            $("#sdedicacion").text("Debe seleccionar una dedicación.");
            esValido = false;
        } else {
            $("#sdedicacion").text("");
        }

        if (!$("#condicion").val()) {
            $("#scondicion").text("Debe seleccionar una condición.");
            esValido = false;
        } else {
            $("#scondicion").text("");
        }

        if ($("input[name='titulos[]']:checked").length === 0) {
            $("#stitulos").text("Debe seleccionar al menos un título.");
            esValido = false;
        } else {
            $("#stitulos").text("");
        }

        if (!$("#fechaIngreso").val()) {
            $("#sfechaIngreso").text("Debe seleccionar una fecha de ingreso.");
            esValido = false;
        } else {
            $("#sfechaIngreso").text("");
        }

        if (!$("#observacionesDocente").val().trim()) {
            $("#sobservacionesDocente").text("El campo de observaciones no puede estar vacío.");
            esValido = false;
        } else {
            $("#sobservacionesDocente").text("");
        }

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
        const fechaIngreso = fila.find("td:eq(10)").text();
        const observaciones = fila.find("td:eq(11)").text();
        const titulosIds = fila.attr('data-titulos-ids');
        const coordinacionesIds = fila.attr('data-coordinaciones-ids');

        $("#prefijoCedula").val(prefijo);
        $("#cedulaDocente").val(cedula);
        $("#nombreDocente").val(nombre);
        $("#apellidoDocente").val(apellido);
        $("#correoDocente").val(correo);
        $('#dedicacion').val(dedicacion);
        $('#condicion').val(condicion);
        $("#fechaIngreso").val(fechaIngreso);
        $("#observacionesDocente").val(observaciones);
        
        $('#categoria option').filter(function() {
            return $(this).text() == categoria;
        }).prop('selected', true);

        if (titulosIds) titulosIds.split(',').forEach(id => {
            if (id) $("#titulo_" + id.trim()).prop('checked', true);
        });
        if (coordinacionesIds) coordinacionesIds.split(',').forEach(id => {
            if (id) $("#coordinacion_" + id.trim()).prop('checked', true);
        });

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
        Swal.fire({
            icon: tipo,
            title: titulo,
            html: mensaje,
            timer: duracion,
            timerProgressBar: true
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

                    if (typeof lee.existe !== 'undefined') {
                        if (lee.existe) {
                            muestraMensaje("error", 4000, "Cédula Duplicada", "Ya hay un docente registrado con esta cédula.");
                            $("#scedulaDocente").text("Cédula ya registrada.");
                            $("#proceso").prop("disabled", true);
                        } else {
                            $("#scedulaDocente").text("");
                            $("#proceso").prop("disabled", false);
                        }
                        return;
                    }

                    if (lee.resultado === 'consultar') {
                        destruyeDT();
                        $("#resultadoconsulta").empty();
                        lee.mensaje.forEach(item => {
                            $("#resultadoconsulta").append(`
                                <tr data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}" data-doc-id="${item.doc_id}">
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
                                    <td>${item.doc_ingreso}</td>
                                    <td>${item.doc_observacion}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm modificar-btn">Modificar</button>
                                        <button class="btn btn-danger btn-sm eliminar-btn">Eliminar</button>
                                        <button class="btn btn-success btn-sm ver-horas-btn">Horas</button>
                                    </td>
                                </tr>`);
                        });
                        crearDT();
                    } else if (lee.resultado === 'consultar_horas' || lee.resultado === 'horas_no_encontradas') {
                        const horas = lee.mensaje;
                        $("#horasCreacion").text(horas.act_creacion_intelectual);
                        $("#horasIntegracion").text(horas.act_integracion_comunidad);
                        $("#horasGestion").text(horas.act_gestion_academica);
                        $("#horasOtras").text(horas.act_otras);
                        $("#modalHoras").modal("show");
                    } else if (lee.resultado === 'incluir') {
                        muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
                    } else if (lee.resultado === 'modificar') {
                        muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
                    } else if (lee.resultado === 'eliminar') {
                        muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
                    } else {
                        muestraMensaje("error", 5000, "ERROR", lee.mensaje || "Ocurrió un error inesperado.");
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