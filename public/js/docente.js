$(document).ready(function() {
    let formInteracted = false;
    let initialState = '';


    $('#modal1').on('hidden.bs.modal', function () {
        $('#modification_tip_wrapper').remove();
    });
    
  
    function verificarRequisitosIniciales() {
        const mainContent = $(".main-content");
        const countTitulos = parseInt(mainContent.data('count-titulos'), 10);
        const countCategorias = parseInt(mainContent.data('count-categorias'), 10);
        const countCoordinaciones = parseInt(mainContent.data('count-coordinaciones'), 10);

        const mensajesError = [];

        if (countTitulos === 0) {
            mensajesError.push('No hay <b>títulos</b> registrados.');
        }
        if (countCategorias === 0) {
            mensajesError.push('No hay <b>categorías</b> registradas.');
        }
        if (countCoordinaciones === 0) {
            mensajesError.push('No hay <b>coordinaciones</b> registradas.');
        }

        if (mensajesError.length > 0) {
            const botonRegistrar = $("#registrar");
            const mensajeTooltip = "Debe registrar primero los datos maestros requeridos.";
            
            botonRegistrar.prop('disabled', true).attr('title', mensajeTooltip);
            
            let mensajeHtml = "Para poder registrar un nuevo docente, primero debe configurar lo siguiente en sus respectivos módulos:<br><br><ul class='list-unstyled text-start ps-4'>";
            mensajesError.forEach(msg => {
                mensajeHtml += `<li><i class="fas fa-exclamation-circle text-warning me-2"></i>${msg}</li>`;
            });
            mensajeHtml += "</ul>";

            Swal.fire({
                icon: 'warning',
                title: 'Faltan Datos para Continuar',
                html: mensajeHtml,
                confirmButtonText: 'Entendido'
            });
        }
    }

    function mostrarErroresCamposVacios() {
        if (!$('#cedulaDocente').val()) { $('#scedulaDocente').text('La cédula es requerida.'); }
        if (!$('#nombreDocente').val()) { $('#snombreDocente').text('El nombre es requerido.'); }
        if (!$('#apellidoDocente').val()) { $('#sapellidoDocente').text('El apellido es requerido.'); }
        if (!$('#correoDocente').val()) { $('#scorreoDocente').text('El correo es requerido.'); }
        if ($("input[name='titulos[]']:checked").length === 0) { $('#stitulos').text('Debe seleccionar al menos un título.'); }
        if (!$('#categoria').val()) { $('#scategoria').text('Debe seleccionar una categoría.'); }
        if (!$('#dedicacion').val()) { $('#sdedicacion').text('Debe seleccionar una dedicación.'); }
        if (!$('#condicion').val()) { $('#scondicion').text('Debe seleccionar una condición.'); }
        if (!$('#fechaIngreso').val()) { $('#sfechaIngreso').text('La fecha de ingreso es requerida.'); }
        if ($('#anioConcurso').prop('required') && !$('#anioConcurso').val()) {
            $('#sanioConcurso').text('El año de concurso es requerido.');
        }
    }

    function actualizarEstadoBoton() {
        if ($("#accion").val() !== 'incluir') { return; }
        const cedulaValida = /^[0-9]{7,8}$/.test($("#cedulaDocente").val());
        const nombreValido = /^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#nombreDocente").val());
        const apellidoValido = /^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#apellidoDocente").val());
        const correoValido = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test($("#correoDocente").val());
        const titulosValidos = $("input[name='titulos[]']:checked").length > 0;
        const categoriaValida = $("#categoria").val() ? true : false;
        const dedicacionValida = $("#dedicacion").val() ? true : false;
        const condicionValida = $("#condicion").val() ? true : false;
        const fechaValida = $("#fechaIngreso").val() ? true : false;
        
        let anioConcursoValido = true;
        if ($('#anioConcurso').prop('required')) {
            anioConcursoValido = $('#anioConcurso').val() ? true : false;
        }

        if (cedulaValida && nombreValido && apellidoValido && correoValido && titulosValidos && categoriaValida && dedicacionValida && condicionValida && fechaValida && anioConcursoValido) {
            $("#proceso").prop("disabled", false);
        } else {
            $("#proceso").prop("disabled", true);
        }
    }

  
    $('#condicion').on('change', function() {
        if (formInteracted && !$(this).val()) {
            $('#scondicion').text('Debe seleccionar una condición.');
        } else {
            $('#scondicion').text('');
        }

        const seleccion = $(this).val();
        const concursoWrapper = $('#concurso-fields-wrapper');
        const tipoConcursoInput = $('#tipoConcurso');
        const anioConcursoInput = $('#anioConcurso');

        switch(seleccion) {
            case 'Ordinario':
                tipoConcursoInput.val('Oposición');
                anioConcursoInput.prop('required', true);
                concursoWrapper.slideDown();
                break;
            case 'Contratado por Credenciales':
                tipoConcursoInput.val('Credenciales');
                anioConcursoInput.prop('required', true);
                concursoWrapper.slideDown();
                break;
            default:
                concursoWrapper.slideUp();
                tipoConcursoInput.val('');
                anioConcursoInput.val('').prop('required', false);
                $('#sanioConcurso').text('');
                break;
        }
        actualizarEstadoBoton();
    });

    $('#f').on('input change', function() {
        if ($("#accion").val() === 'modificar') {
            const currentState = $('#f').serialize();
            if (currentState !== initialState) {
                $("#proceso").prop('disabled', false);
                $('#modification_tip_wrapper').hide();
            } else {
                $("#proceso").prop('disabled', true);
                $('#modification_tip_wrapper').show();
            }
        }
    });

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
          paging: true, lengthChange: true, searching: true, ordering: true, info: true,
          autoWidth: false, responsive: true, scrollX: true,
          language: {
            lengthMenu: "Mostrar _MENU_ registros", zeroRecords: "No se encontraron resultados",
            info: "Mostrando _PAGE_ de _PAGES_", infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)", search: "Buscar:",
            paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
          },
          order: [[1, "asc"]],
          dom: "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        });
        $("div.dataTables_length select").css({ width: "auto", display: "inline", "margin-top": "10px" });
        $("div.dataTables_filter").css({ "margin-bottom": "50px", "margin-top": "10px" });
        $("div.dataTables_filter label").css({ float: "left" });
        $("div.dataTables_filter input").css({ width: "300px", float: "right", "margin-left": "10px" });
      }
    }

    Listar();
    verificarRequisitosIniciales();

    $("#registrar").on("click", function() {
        limpia();
        $("#modal1 .modal-header").removeClass('bg-danger').addClass('bg-primary');
        $("#accion").val("incluir");
        $("#proceso").text("REGISTRAR").removeClass('btn-danger').addClass('btn-primary');
        $("form#f :input").prop('disabled', false);
        $("#modal1 .modal-title").text("Formulario de Registro de Docente");
        $("#proceso").prop("disabled", true);
        $('#f :input').one('input change', function() {
            formInteracted = true;
            mostrarErroresCamposVacios();
        });
        $("#modal1").modal("show");
    });

    $("#proceso").on("click", function() {
        const accion = $("#accion").val();
        if (accion === "incluir" || accion === "modificar") {
            if (validarenvio()) {
                const datos = new FormData($('#f')[0]);
                enviaAjax(datos);
            }
        } else if (accion === "eliminar") {
            Swal.fire({
                title: "¿Está seguro de eliminar este docente?", text: "Esta acción no se puede deshacer.", icon: "warning",
                showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
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

    $(document).on('click', '.modificar-btn', function() { pone(this, 'modificar'); });
    $(document).on('click', '.eliminar-btn', function() { pone(this, 'eliminar'); });
    $(document).on('click', '.ver-horas-btn', function() {
        const fila = $(this).closest("tr");
        const doc_cedula = fila.data('doc-cedula');
        const nombreCompleto = fila.find("td:eq(2)").text() + ' ' + fila.find("td:eq(3)").text();
        $("#nombreDocenteHoras").text(nombreCompleto);

        const datos = new FormData();
        datos.append("accion", "consultar_horas");
        datos.append("doc_cedula", doc_cedula);
        enviaAjax(datos);
    });

    $("#cedulaDocente, #nombreDocente, #apellidoDocente, #correoDocente").on("keyup", function() {
        const id = $(this).attr('id');
        if (id === 'cedulaDocente') {
            this.value = this.value.replace(/[^0-9]/g, '');
            validarkeyup(/^[0-9]{7,8}$/, $(this), $("#scedulaDocente"), "Cédula inválida (7-8 dígitos).");
            if ($("#accion").val() === "incluir" && /^[0-9]{7,8}$/.test(this.value)) {
                const datos = new FormData();
                datos.append('accion', 'Existe');
                datos.append('cedulaDocente', $(this).val());
                enviaAjax(datos);
            }
        } else if (id === 'nombreDocente' || id === 'apellidoDocente') {
            this.value = this.value.replace(/[0-9]/g, '');
            validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{3,30}$/, $(this), $("#s" + id), "Formato inválido.");
        } else if (id === 'correoDocente') {
            validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $(this), $("#scorreoDocente"), "Correo inválido.");
        }
        actualizarEstadoBoton();
    });
    
    $("#categoria, #dedicacion, #fechaIngreso, #anioConcurso").on("change", function() {
        const el = $(this);
        const spanId = "#s" + el.attr('id');

        if (formInteracted && el.prop('required') && !el.val()) {
            $(spanId).text("Este campo es requerido.");
        } else {
            $(spanId).text("");
        }
        actualizarEstadoBoton();
    });

    $("input[name='titulos[]']").on("change", function() {
        if (formInteracted && $("input[name='titulos[]']:checked").length === 0) {
            $("#stitulos").text("Debe seleccionar al menos un título.");
        } else {
            $("#stitulos").text("");
        }
        actualizarEstadoBoton();
    });
    
    $("input[name='coordinaciones[]']").on("change", function() {
        $("#scoordinaciones").text("");
        actualizarEstadoBoton();
    });
    
    function validarenvio() {
        if (!formInteracted) {
            mostrarErroresCamposVacios();
        }
        let esValido = true;
        $("#scoordinaciones").text("");

        if (!/^[0-9]{7,8}$/.test($("#cedulaDocente").val())) esValido = false;
        if (!/^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#nombreDocente").val())) esValido = false;
        if (!/^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#apellidoDocente").val())) esValido = false;
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test($("#correoDocente").val())) {
             $("#scorreoDocente").text("Correo inválido.");
             esValido = false;
        }
        if (!$('#categoria').val()) esValido = false;
        if (!$('#dedicacion').val()) esValido = false;
        if (!$('#condicion').val()) esValido = false;
        if (!$('#fechaIngreso').val()) esValido = false;
        if ($("input[name='titulos[]']:checked").length === 0) esValido = false;
        if ($('#anioConcurso').prop('required') && !$('#anioConcurso').val()) esValido = false;
    
        if (!esValido) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise los campos del formulario.");
        }
        return esValido;
    }

    function pone(pos, accion) {
        limpia();
        $("#modal1 .modal-header").removeClass('bg-danger').addClass('bg-primary');

        const fila = $(pos).closest("tr");
        const prefijo = fila.find("td:eq(0)").text();
        const cedula = fila.find("td:eq(1)").text();
        const nombre = fila.find("td:eq(2)").text();
        const apellido = fila.find("td:eq(3)").text();
        const correo = fila.find("td:eq(4)").text();
        const categoria = fila.find("td:eq(5)").text();
        const dedicacion = fila.find("td:eq(6)").text();
        const condicion = fila.find("td:eq(7)").text();
        const anioConcurso = fila.find("td:eq(9)").text();
        const fechaIngreso = fila.find("td:eq(12)").text();
        const observaciones = fila.attr('data-observacion');
        const titulosIds = fila.attr('data-titulos-ids');
        const coordinacionesIds = fila.attr('data-coordinaciones-ids');

        $("#accion").val(accion);
        $("#prefijoCedula").val(prefijo);
        $("#cedulaDocente").val(cedula);
        $("#nombreDocente").val(nombre);
        $("#apellidoDocente").val(apellido);
        $("#correoDocente").val(correo);
        $('#categoria').val(categoria);
        $('#dedicacion').val(dedicacion);
        $('#condicion').val(condicion).trigger('change');
        $("#fechaIngreso").val(fechaIngreso);
        $("#observacionesDocente").val(observaciones);
        if (anioConcurso !== 'N/A') {
            $("#anioConcurso").val(anioConcurso);
        }
        
        if (titulosIds) titulosIds.split(',').forEach(id => { if (id) $(`input[name='titulos[]'][value="${id.trim()}"]`).prop('checked', true); });
        if (coordinacionesIds) coordinacionesIds.split(',').forEach(id => { if (id) $(`input[name='coordinaciones[]'][value="${id.trim()}"]`).prop('checked', true); });

        $("form#f :input").prop('disabled', false);
        
        if (accion === 'modificar') {
            $("#proceso").text("MODIFICAR").removeClass("btn-danger").addClass("btn-primary");
            $("#modal1 .modal-title").text("Formulario de Modificación de Docente");
            $("#prefijoCedula, #cedulaDocente").prop('disabled', true);
            $("#proceso").prop('disabled', true);
            $(".modal-footer").prepend('<div id="modification_tip_wrapper" class="w-100 text-center mb-2"><small class="form-text text-danger">Realice un cambio para poder modificar.</small></div>');
            
            setTimeout(function() {
                initialState = $('#f').serialize();
            }, 200);

        } else if (accion === 'eliminar') {
            $("#modal1 .modal-header").removeClass('bg-primary').addClass('bg-danger');
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
        $("#accion").val("incluir");
        formInteracted = false;
        initialState = '';
        $('#concurso-fields-wrapper').hide();
        $('#anioConcurso').prop('required', false);
        $('#modification_tip_wrapper').remove();
    }

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
    }

    function enviaAjax(datos) {
        $.ajax({
            url: "", type: "POST", contentType: false, data: datos,
            processData: false, cache: false, timeout: 15000,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    if (lee.resultado === 'existe' || typeof lee.existe !== 'undefined') {
                        if (lee.existe) {
                            $("#scedulaDocente").text("Cédula ya registrada.");
                            $("#proceso").prop("disabled", true);
                        } else {
                            $("#scedulaDocente").text("");
                            actualizarEstadoBoton();
                        }
                    } else if (lee.resultado === 'consultar') {
                        destruyeDT();
                        $("#resultadoconsulta").empty();
                        lee.mensaje.forEach(item => {
                            $("#resultadoconsulta").append(`
                                <tr data-doc-cedula="${item.doc_cedula}" data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}" data-observacion="${item.doc_observacion || ''}">
                                    <td>${item.doc_prefijo}</td>
                                    <td>${item.doc_cedula}</td>
                                    <td>${item.doc_nombre}</td>
                                    <td>${item.doc_apellido}</td>
                                    <td>${item.doc_correo}</td>
                                    <td>${item.cat_nombre}</td>
                                    <td>${item.doc_dedicacion || 'N/A'}</td>
                                    <td>${item.doc_condicion || 'N/A'}</td>
                                    <td>${item.doc_tipo_concurso || 'N/A'}</td>
                                    <td>${item.doc_anio_concurso || 'N/A'}</td>
                                    <td>${item.titulos || 'Sin títulos'}</td>
                                    <td>${item.coordinaciones || 'Sin coordinaciones'}</td>
                                    <td>${item.doc_ingreso}</td>
                                    <td>${item.doc_observacion || 'Sin observaciones'}</td>
                                    <td>
                                         <button class="btn btn-warning btn-sm modificar-btn"><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                                        <button class="btn btn-danger btn-sm eliminar-btn"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                                    
                                        <button class="btn btn-info btn-sm ver-horas-btn"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>Ver horas</svg></button>
                                    </td>
                                </tr>`);
                        });
                        crearDT();
                    } else if (lee.resultado === 'consultar_horas' || lee.resultado === 'horas_no_encontradas') {
                        const horas = lee.mensaje;
                        $("#horasCreacion").text(horas.act_creacion_intelectual || 'N/A');
                        $("#horasIntegracion").text(horas.act_integracion_comunidad || 'N/A');
                        $("#horasGestion").text(horas.act_gestion_academica || 'N/A');
                        $("#horasOtras").text(horas.act_otras || 'N/A');
                        $("#modalHoras").modal("show");
                    } else if (['incluir', 'modificar', 'eliminar'].includes(lee.resultado)) {
                         muestraMensaje("success", 3000, "¡ÉXITO!", lee.mensaje);
                         $("#modal1").modal("hide");
                         Listar();
                    } else {
                        muestraMensaje("error", 5000, "ERROR", lee.mensaje || "Ocurrió un error inesperado.");
                    }
                } catch (e) {
                    console.error("Error parsing JSON:", e, "Server response:", respuesta);
                    muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta del servidor.");
                }
            },
            error: (request, status, err) => muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", `Ocurrió un error: ${status} - ${err}`)
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