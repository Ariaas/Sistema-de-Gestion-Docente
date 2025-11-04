$(document).ready(function() {
    let cachedTeacherData = null;
    let originalFormF = null; 
    let originalFormPaso3 = null; 

    $('#titulos, #coordinaciones').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione...',
        allowClear: false,
        width: '100%',
        closeOnSelect: false
    });


    function setErrorText(spanElement, message) {
        if (message) {
            spanElement.text(message).removeClass('text-danger').addClass('text-secondary');
        } else {
            spanElement.text("").removeClass('text-secondary');
        }
    }
    
    function checkFormChanges() {
        if ($('#accion').val() !== 'modificar' || originalFormF === null || originalFormPaso3 === null) {
            return;
        }

        const currentFormF = $('#f').serialize();
        const currentFormPaso3 = $('#form-paso3').serialize();

        const hasChanged = (currentFormF !== originalFormF) || (currentFormPaso3 !== originalFormPaso3);
        $('#btn-final-submit').prop('disabled', !hasChanged);
    }

    function setModalStep(step) {
        const footer = $('#modal-footer');
        const modalTitle = $('#modal-title');
        footer.empty();

        $('#step1-docente, #step2-academico, #step3-actividad').hide();
        const nombreDocente = $('#nombreDocente').val() + ' ' + $('#apellidoDocente').val();

        if (step === 1) {
            $('#step1-docente').show();
            const accion = $('#accion').val();
            modalTitle.text(accion === 'incluir' ? "Paso 1: Datos Personales" : "Paso 1: Modificar Datos Personales");
            footer.append('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>');
            footer.append('<button type="button" class="btn btn-primary" id="btn-next-1">SIGUIENTE</button>');
        } else if (step === 2) {
            $('#step2-academico').show();
            modalTitle.text("Paso 2: Datos Académicos");
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-2">ATRÁS</button>');
            footer.append('<button type="button" class="btn btn-primary" id="btn-next-2">SIGUIENTE</button>');
        } else if (step === 3) {
            $('#step3-actividad').show();
            modalTitle.text('Paso 3: Actividades');
            $('#nombreDocenteHoras').text(nombreDocente);
            const accion = $('#accion').val();
            const finalButtonText = (accion === 'incluir') ? "REGISTRAR" : "MODIFICAR";
            const buttonClass = (accion === 'incluir') ? "btn-primary" : "btn-primary";
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-3">ATRÁS</button>');
            footer.append(`<button type="button" class="btn ${buttonClass}" id="btn-final-submit">${finalButtonText}</button>`);
            
            if (accion === 'modificar') {
                $('#btn-final-submit').prop('disabled', true);
                checkFormChanges(); 
            }
        }
    }

    $(document).on('click', '#btn-next-1', function() {
        mostrarErroresPaso1();
        if (validarenvioPaso1()) {
            setModalStep(2);
        }
    });

    $(document).on('click', '#btn-next-2', function() {
        mostrarErroresPaso2();
        if (validarenvioPaso2()) {
            const datos = new FormData();
            datos.append("accion", "consultar_paso2");
            datos.append("doc_cedula", $('#cedulaDocente').val());
            enviaAjax(datos);
        }
    });
    
    $(document).on('click', '#btn-prev-2', function() { setModalStep(1); });
    $(document).on('click', '#btn-prev-3', function() { setModalStep(2); });

    $(document).on('click', '#btn-final-submit', function() {
        cachedTeacherData = new FormData($('#f')[0]);
        if ($('#accion').val() === 'modificar') {
            cachedTeacherData.append('cedulaDocente', $('#cedulaDocente').val());
        }

        $('#step3-actividad .horas-input').each(function() {
            const valor = $(this).val() === '' ? '0' : $(this).val();
            cachedTeacherData.append($(this).attr('name'), valor);
        });

        enviaAjax(cachedTeacherData);
    });
    
    $('#modal1').on('hidden.bs.modal', function() { limpia(); });

    $(document).on('click', '.btn-info', function() {
        const fila = $(this).closest('tr');
        const cedula = fila.data('cedula');
        const nombre = fila.find('td:eq(1)').text() + ' ' + fila.find('td:eq(2)').text();
        
        $('#verNombreDocente').text(nombre);
        $('#verTipoConcurso').text(fila.data('tipo-concurso') || 'N/A');
        $('#verAnioConcurso').text(fila.data('anio-concurso') || 'N/A');
        $('#verFechaIngreso').text(fila.data('fecha-ingreso') || 'N/A');
        $('#verTitulos').text(fila.data('titulos-texto') || 'Sin títulos');
        $('#verCoordinaciones').text(fila.data('coordinaciones-texto') || 'Sin coordinaciones');
        $('#verObservaciones').text(fila.data('observacion') || 'Sin observaciones');

        $('#verHorasAcademicas, #verHorasCreacion, #verHorasIntegracion, #verHorasGestion, #verHorasOtras').text('0');

        const datos = new FormData();
        datos.append('accion', 'consultar_datos_adicionales');
        datos.append('doc_cedula', cedula);
        enviaAjax(datos);
    });


    function verificarRequisitosIniciales() {
        const mainContent = $(".main-content");
        const countTitulos = parseInt(mainContent.data('count-titulos'), 10);
        const countCategorias = parseInt(mainContent.data('count-categorias'), 10);
        const countCoordinaciones = parseInt(mainContent.data('count-coordinaciones'), 10);
        const mensajesError = [];
        if (countTitulos === 0) mensajesError.push('No hay <b>títulos</b> registrados.');
        if (countCategorias === 0) mensajesError.push('No hay <b>categorías</b> registradas.');
        if (countCoordinaciones === 0) mensajesError.push('No hay <b>coordinaciones</b> registradas.');
        if (mensajesError.length > 0) {
            const botonRegistrar = $("#registrar");
            botonRegistrar.prop('disabled', true).attr('title', "Debe registrar primero los datos maestros requeridos.");
            let mensajeHtml = "Para poder registrar un nuevo docente, primero debe configurar lo siguiente:<br><br><ul class='list-unstyled text-start ps-4'>";
            mensajesError.forEach(msg => {
                mensajeHtml += `<li><i class="fas fa-exclamation-circle text-warning me-2"></i>${msg}</li>`;
            });
            mensajeHtml += "</ul>";
            Swal.fire({ icon: 'warning', title: 'Faltan Datos para Continuar', html: mensajeHtml, confirmButtonText: 'Entendido' });
        }
    }

    
    function mostrarErroresPaso1() {
        const cedulaVal = $('#cedulaDocente').val();
        const nombreVal = $('#nombreDocente').val();
        const apellidoVal = $('#apellidoDocente').val();
        const correoVal = $('#correoDocente').val();
        const categoriaVal = $('#categoria').val();
        const dedicacionVal = $('#dedicacion').val();
        
        if (!cedulaVal) {
            setErrorText($('#scedulaDocente'), 'La cédula debe tener entre 7 y 8 dígitos.');
        } else if (!/^[0-9]{7,8}$/.test(cedulaVal)) {
            setErrorText($('#scedulaDocente'), 'La cédula debe tener entre 7 y 8 dígitos.');
        }
        
        if (!nombreVal) {
            setErrorText($('#snombreDocente'), 'El nombre debe tener entre 3 y 30 caracteres.');
        } else if (!/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{3,30}$/.test(nombreVal)) {
            setErrorText($('#snombreDocente'), 'El nombre debe tener entre 3 y 30 caracteres.');
        }
        
        if (!apellidoVal) {
            setErrorText($('#sapellidoDocente'), 'El apellido debe tener entre 3 y 30 caracteres.');
        } else if (!/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{3,30}$/.test(apellidoVal)) {
            setErrorText($('#sapellidoDocente'), 'El apellido debe tener entre 3 y 30 caracteres.');
        }
        
        if (!correoVal) {
            setErrorText($('#scorreoDocente'), 'Debe ingresar un correo electrónico válido.');
        } else if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(correoVal)) {
            setErrorText($('#scorreoDocente'), 'Debe ingresar un correo electrónico válido.');
        }
        
        if (!categoriaVal) {
            setErrorText($('#scategoria'), 'Debe seleccionar una categoría.');
        }
        
        if (!dedicacionVal) {
            setErrorText($('#sdedicacion'), 'Debe seleccionar una dedicación.');
        }
        
        const anioConcursoInput = $('#anioConcurso');
        if (anioConcursoInput.prop('required') && !anioConcursoInput.val()) {
            setErrorText($('#sanioConcurso'), 'El mes y año de concurso es requerido.');
        } else if (anioConcursoInput.val()) {
            const hoy = new Date();
            const anioActual = hoy.getFullYear();
            const mesActual = hoy.getMonth() + 1;
            const [anioSeleccionado, mesSeleccionado] = anioConcursoInput.val().split('-').map(Number);
            if (anioSeleccionado > anioActual || (anioSeleccionado === anioActual && mesSeleccionado > mesActual)) {
                setErrorText($('#sanioConcurso'), 'El mes del concurso no puede ser futuro.');
            } else {
                setErrorText($('#sanioConcurso'), '');
            }
        }
    }
    function mostrarErroresPaso2() {
    }
    
    function mostrarErroresPaso3() {
        validarCamposHoraria();
        validarCargaHoraria();
    }

    function validarenvioPaso1() {
        let esValido = true;
        if (!/^[0-9]{7,8}$/.test($("#cedulaDocente").val())) esValido = false;
        if (!/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{3,30}$/.test($("#nombreDocente").val())) esValido = false;
        if (!/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{3,30}$/.test($("#apellidoDocente").val())) esValido = false;
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test($("#correoDocente").val())) esValido = false;
        if (!$('#categoria').val()) esValido = false;
        if (!$('#dedicacion').val()) esValido = false;
        
        
        if ($('#scedulaDocente').hasClass('text-danger')) esValido = false;
        if ($('#scorreoDocente').hasClass('text-danger')) esValido = false;
        
        const anioConcursoInput = $('#anioConcurso');
        if (anioConcursoInput.prop('required') && !anioConcursoInput.val()) {
            esValido = false;
        } else if (anioConcursoInput.val()) {
            const hoy = new Date();
            const anioActual = hoy.getFullYear();
            const mesActual = hoy.getMonth() + 1;
            const [anioSeleccionado, mesSeleccionado] = anioConcursoInput.val().split('-').map(Number);
            if (anioSeleccionado > anioActual || (anioSeleccionado === anioActual && mesSeleccionado > mesActual)) {
                esValido = false;
            }
        }
        if (!esValido) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise los campos del Paso 1.");
        }
        return esValido;
    }

    function validarenvioPaso2() {
        return true;
    }

    function validarCamposHoraria() {
        let esValido = true;
        const camposRequeridos = ['#actAcademicas', '#actCreacion', '#actIntegracion', '#actGestion'];
        
        camposRequeridos.forEach(function(selector) {
            const input = $(selector);
            const errorSpan = $('#s' + input.attr('id'));
            const valor = input.val();
            
            if (valor === '' || valor === null || isNaN(parseInt(valor))) {
               
            } else {
                setErrorText(errorSpan, '');
            }
        });
        return esValido;
    }

    $('#condicion').on('change', function() {
        const seleccion = $(this).val();
        const concursoWrapper = $('#concurso-fields-wrapper');
        const tipoConcursoInput = $('#tipoConcurso');
        const anioConcursoInput = $('#anioConcurso');
        
        if (!seleccion) { 
            setErrorText($('#scondicion'), 'Debe seleccionar una condición.');
        } else { 
            setErrorText($('#scondicion'), ''); 
        }

        switch(seleccion) {
            case 'Ordinario':
                tipoConcursoInput.val('Oposición');
                
                concursoWrapper.slideDown();
                break;
            case 'Contratado por Credenciales':
                tipoConcursoInput.val('Credenciales');
               
                concursoWrapper.slideDown();
                break;
            default:
                concursoWrapper.slideUp();
                tipoConcursoInput.val('');
                anioConcursoInput.val('').prop('required', false);
                setErrorText($('#sanioConcurso'),'');
                break;
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
          dom: "<'row'<'col-sm-12 col-md-2'l><'col-sm-12 col-md-6'B><'col-sm-12 col-md-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        });
      }
    }

    Listar();
    verificarRequisitosIniciales();

    $("#registrar").on("click", function() {
        limpia();
        $("#accion").val("incluir");
        setModalStep(1); 
        $("#modal1").modal("show");
    });

    $(document).on('click', '.btn-edit', function() { pone(this, 'modificar'); });
    
    $(document).on('click', '.btn-delete', function() { pone(this, 'desactivar'); });
    
    function validarCargaHoraria() {
        $('.horas-input').each(function() {
            if ($(this).val() !== '') {
                let valor = parseInt($(this).val());
                if(valor < 0) { 
                    $(this).val('0'); 
                } else if(valor > 99) {
                    $(this).val('99');
                }
            }
        });
    }

    $('.horas-input').on('input', function() {
        let valor = $(this).val();
        valor = valor.replace(/[^0-9]/g, '');
        
        if (valor.length > 2) {
            valor = valor.substring(0, 2);
        }
        
        let numero = parseInt(valor);
        if (!isNaN(numero)) {
            if (numero < 0) {
                valor = '0';
            } else if (numero > 99) {
                valor = '99';
            }
        }
        
        $(this).val(valor);
        mostrarErroresPaso3();
    });
    
    $('.horas-input').on('blur', function() {
        if ($(this).val() === '') {
            $(this).val('0');
        }
    });
    
    $('.horas-input').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            return false;
        }
    });
    
    $('#dedicacion').on('change', validarCargaHoraria);
    

    function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
        if (etiqueta.val() && !er.test(etiqueta.val())) {
            setErrorText(etiquetamensaje, mensaje);
            return false;
        }
        setErrorText(etiquetamensaje, ""); 
        return true;
    }

    $("#cedulaDocente").on("keyup", function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        const spanCedula = $("#scedulaDocente");
        
        if (this.value.length === 0) {
            spanCedula.text("").removeClass('text-danger text-secondary');
            return;
        }
        
        if (!/^[0-9]{7,8}$/.test(this.value)) {
            spanCedula.text("La cédula debe tener entre 7 y 8 dígitos.").removeClass('text-danger').addClass('text-secondary');
            return;
        }
        
        spanCedula.text("").removeClass('text-danger text-secondary');
        
        if (!$(this).prop('disabled')) {
            const datos = new FormData();
            datos.append('accion', 'Existe');
            datos.append('cedulaDocente', this.value);
            enviaAjax(datos);
        }
    }).on("blur", function() {
        if (!$(this).val()) {
            setErrorText($("#scedulaDocente"), "La cédula debe tener entre 7 y 8 dígitos.");
        }
    });

    $("#nombreDocente, #apellidoDocente").on("keyup", function() {
        this.value = this.value.replace(/[0-9]/g, '');
        const spanId = $("#s" + $(this).attr('id'));
        
        if (this.value.length === 0) {
            spanId.text("").removeClass('text-danger text-secondary');
            return;
        }
        
        if (!/^[A-Za-zñÑáéíóúÁÉÍÓÚ\s]{3,30}$/.test(this.value)) {
            const fieldName = $(this).attr('id') === 'nombreDocente' ? 'nombre' : 'apellido';
            spanId.text(`El ${fieldName} debe tener entre 3 y 30 caracteres.`).removeClass('text-danger').addClass('text-secondary');
        } else {
            spanId.text("").removeClass('text-danger text-secondary');
        }
    }).on("blur", function() {
        if (!$(this).val()) {
            const fieldName = $(this).attr('id') === 'nombreDocente' ? 'nombre' : 'apellido';
            setErrorText($("#s" + $(this).attr('id')), `El ${fieldName} debe tener entre 3 y 30 caracteres.`);
        }
    });

    $("#correoDocente").on("keyup", function() {
        const spanCorreo = $("#scorreoDocente");
        
        if (this.value.length === 0) {
            spanCorreo.text("").removeClass('text-danger text-secondary');
            return;
        }
        
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(this.value)) {
            spanCorreo.text("Debe ingresar un correo electrónico válido.").removeClass('text-danger').addClass('text-secondary');
            return;
        }
        
        spanCorreo.text("").removeClass('text-danger text-secondary');
        
        const datos = new FormData();
        datos.append('accion', 'existe_correo');
        datos.append('correoDocente', this.value);
        datos.append('cedulaDocente', $('#cedulaDocente').val());
        enviaAjax(datos);
    }).on("blur", function() {
        if (!$(this).val()) {
            setErrorText($("#scorreoDocente"), "Debe ingresar un correo electrónico válido.");
        }
    });
    
    $("#categoria, #dedicacion").on("change", function() {
        const el = $(this);
        const fieldName = el.attr('id') === 'categoria' ? 'categoría' : 'dedicación';
        if (!el.val()) {
             setErrorText($("#s" + el.attr('id')), `Debe seleccionar una ${fieldName}.`);
        } else {
            setErrorText($("#s" + el.attr('id')), "");
        }
    });

    $("#fechaIngreso, #anioConcurso").on("change", function() {
        const el = $(this);
        const spanEl = $("#s" + el.attr('id'));
        
        if (el.prop('required') && !el.val()) {
             setErrorText(spanEl, "Este campo es requerido.");
             return;
        }
        
        if(el.attr('id') === 'anioConcurso' && el.val()){
            const hoy = new Date();
            const anioActual = hoy.getFullYear();
            const mesActual = hoy.getMonth() + 1;
            const [anioSeleccionado, mesSeleccionado] = el.val().split('-').map(Number);
    
            if (anioSeleccionado > anioActual || (anioSeleccionado === anioActual && mesSeleccionado > mesActual)) {
                setErrorText(spanEl, "El mes del concurso no puede ser futuro.");
            } else {
                setErrorText(spanEl, "");
            }
        } else {
             setErrorText(spanEl, "");
        }
    });
    
    $("input[name='titulos[]']").on("change", function() {
        if ($("input[name='titulos[]']:checked").length === 0) {
        
        } else {
            setErrorText($("#stitulos"), "");
        }
    });
    
    $("input[name='coordinaciones[]']").on("change", function() { setErrorText($("#scoordinaciones"), ""); });
    
    
    $('#f').on('input change', 'input, select, textarea', checkFormChanges);
    $('#form-paso3').on('input change', 'input, select, textarea', checkFormChanges);
    
    $('#titulos, #coordinaciones').on('change', checkFormChanges);


    function pone(pos, accion) {
        const fila = $(pos).closest("tr");
        const cedulaCompleta = fila.find("td:eq(0)").text().trim();
        const [prefijo, cedula] = cedulaCompleta.split('-');
        
        if (accion === 'desactivar') {
            
            var datosVerificacion = new FormData();
            datosVerificacion.append("accion", "verificar_horario");
            datosVerificacion.append("cedulaDocente", cedula);

            $.ajax({
                async: true,
                url: "",
                type: "POST",
                contentType: false,
                data: datosVerificacion,
                processData: false,
                cache: false,
                success: function (respuesta) {
                    try {
                        var lee = JSON.parse(respuesta);
                        let titulo = "¿Está seguro de cambiar el estado de este docente?";
                        let texto = `Docente: ${prefijo}-${cedula} - ${fila.find("td:eq(1)").text().trim()} ${fila.find("td:eq(2)").text().trim()}<br>Pasará a estar inactivo`;

                        if (lee.resultado === "en_horario") {
                            titulo = "¡Atención!";
                            texto = `Este docente está asignado a un horario<br>Docente: ${prefijo}-${cedula} - ${fila.find("td:eq(1)").text().trim()} ${fila.find("td:eq(2)").text().trim()}<br>Si cambia su estado a inactivo, se quitará del horario también. ¿Desea continuar?`;
                        }

                        Swal.fire({
                            title: titulo,
                            html: texto,
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Sí, cambiar",
                            cancelButtonText: "Cancelar",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                var datos = new FormData();
                                datos.append("accion", "eliminar");
                                datos.append("cedulaDocente", cedula);
                                enviaAjax(datos);
                            }
                        });
                    } catch (e) {
                        muestraMensaje(
                            "error",
                            5000,
                            "¡Error en la operación!",
                            "No se pudo verificar el estado del docente."
                        );
                    }
                },
                error: function () {
                    muestraMensaje(
                        "error",
                        5000,
                        "¡Error de conexión!",
                        "No se pudo comunicar con el servidor."
                    );
                },
            });
            return;
        }
        
        
        limpia();
        $("#accion").val(accion);
        
        $("#prefijoCedula").val(prefijo);
        $("#cedulaDocente").val(cedula);
        
        $("#modal-title").text(`Modificar Docente - ${prefijo}-${cedula}`);
        $("#prefijoCedula").closest('.col-md-2').hide();
        $("#cedulaDocente").closest('.col-md-4').hide();
        
        $("form#f :input").prop('disabled', false);
        $("#cedulaDocente").prop('disabled', true);
        
        $("#apellidoDocente").val(fila.find("td:eq(1)").text().trim());
        $("#nombreDocente").val(fila.find("td:eq(2)").text().trim());
        $("#correoDocente").val(fila.find("td:eq(3)").text().trim());
        $('#categoria').val(fila.find("td:eq(4)").text().trim());
        $('#dedicacion').val(fila.find("td:eq(5)").text().trim());
        
        $('#condicion').val(fila.data('condicion')).trigger('change');
        
        const anioConcurso = fila.data('anio-concurso');
        if (anioConcurso) $('#anioConcurso').val(anioConcurso.substring(0, 7));

        $('#fechaIngreso').val(fila.data('fecha-ingreso'));

        $("#observacionesDocente").val(fila.data('observacion'));

        const titulosIds = fila.data('titulos-ids');
        const coordinacionesIds = fila.data('coordinaciones-ids');
        if (titulosIds) $('#titulos').val(titulosIds.split(',')).trigger('change');
        if (coordinacionesIds) $('#coordinaciones').val(coordinacionesIds.split(',')).trigger('change');
        
        if (accion === 'modificar') {
            originalFormF = $('#f').serialize(); 
        }

        setModalStep(1);
        $("#modal1").modal("show");
    }

    function limpia() {
        $("form#f, #form-paso3")[0].reset();
        $('#actAcademicas, #actCreacion, #actIntegracion, #actGestion, #actOtras').val('');
        $('#step3-actividad .text-danger').text('');
        $("form#f :input").prop('disabled', false);
        $(".text-danger, .text-secondary").text("").removeClass('text-danger text-secondary');
        $('#concurso-fields-wrapper').hide();
        cachedTeacherData = null;
        
        originalFormF = null; 
        originalFormPaso3 = null;
        
        $("#prefijoCedula").closest('.col-md-2').show();
        $("#cedulaDocente").closest('.col-md-4').show();
        
        $('#titulos').val(null).trigger('change');
        $('#coordinaciones').val(null).trigger('change');

        setModalStep(1);
    }

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
    }

    function enviaAjax(datos) {
        $.ajax({
            url: "", type: "POST", contentType: false, data: datos,
            processData: false, cache: false,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    if (lee.resultado === 'Existe') {
                         if (lee.existe) { 
                             $("#scedulaDocente").text("Cédula ya registrada.").removeClass('text-secondary').addClass('text-danger');
                         } else { 
                             $("#scedulaDocente").text("").removeClass('text-danger text-secondary');
                         }
                    } else if (lee.resultado === 'existe' || lee.resultado === 'existe_docente') {
                        $("#scorreoDocente").text(lee.mensaje || "El correo ya está registrado.").removeClass('text-secondary').addClass('text-danger');
                    } else if (lee.resultado === 'no_existe') {
                        $("#scorreoDocente").text("").removeClass('text-danger text-secondary');
                    } else if (lee.resultado === 'consultar') {
                        destruyeDT();
                        $("#resultadoconsulta").empty();
                        lee.mensaje.forEach(item => {
                            const btnModificar = `<button class="btn btn-icon btn-edit" title="Modificar Docente" ${!PERMISOS.modificar ? 'disabled' : ''}>
                                                  <img src="public/assets/icons/edit.svg" alt="Modificar">
                                                </button>`;

                            const btnDesactivar = `<button class="btn btn-icon btn-delete" title="Desactivar Docente" ${!PERMISOS.eliminar ? 'disabled' : ''}>
                                                     <img src="public/assets/icons/power.svg" alt="Desactivar">
                                                   </button>`;

                            const btnActivar = `<button class="btn btn-icon btn-success btn-activar" title="Activar Docente" data-cedula="${item.doc_cedula}">
                                                     <img src="public/assets/icons/check.svg" alt="Activar">
                                                   </button>`;

                            const btnVerDatos = `<button class="btn btn-icon btn-info" title="Ver Datos Adicionales">
                                                     <img src="public/assets/icons/eye.svg" alt="Ver Datos">
                                                   </button>`;

                            const estadoBadge = item.doc_estado == '1' 
                                ? '<span class="uc-badge activa">Activo</span>' 
                                : '<span class="uc-badge desactivada">Inactivo</span>';

                            const botonesAccion = item.doc_estado == '1'
                                ? `${btnModificar} ${btnDesactivar} ${btnVerDatos}`
                                : btnActivar;
                            
                            $("#resultadoconsulta").append(`
                                <tr 
                                    data-cedula="${item.doc_cedula}"
                                    data-prefijo="${item.doc_prefijo}"
                                    data-condicion="${item.doc_condicion || ''}"
                                    data-titulos-ids="${item.titulos_ids || ''}" 
                                    data-coordinaciones-ids="${item.coordinaciones_ids || ''}"
                                    data-titulos-texto="${item.titulos || ''}"
                                    data-coordinaciones-texto="${item.coordinaciones || ''}"
                                    data-observacion="${item.doc_observacion || ''}" 
                                    data-anio-concurso="${item.doc_anio_concurso || ''}"
                                    data-tipo-concurso="${item.doc_tipo_concurso || ''}"
                                    data-fecha-ingreso="${item.doc_ingreso || ''}"
                                >
                                    <td>${item.doc_prefijo}-${item.doc_cedula}</td>
                                    <td>${item.doc_apellido}</td>
                                    <td>${item.doc_nombre}</td>
                                    <td>${item.doc_correo}</td>
                                    <td>${item.cat_nombre}</td>
                                    <td>${item.doc_dedicacion || 'N/A'}</td>
                                    <td>${item.doc_condicion || 'N/A'}</td>
                                    <td>${estadoBadge}</td>
                                    <td class="text-nowrap"> ${botonesAccion} </td>
                                </tr>`);
                        });
                        crearDT();
                    } else if (lee.resultado === 'ok_paso2') {
                        const horas = lee.horas;
                        $('#actAcademicas').val(horas.act_academicas !== '0' ? horas.act_academicas : '');
                        $('#actCreacion').val(horas.act_creacion_intelectual !== '0' ? horas.act_creacion_intelectual : '');
                        $('#actIntegracion').val(horas.act_integracion_comunidad !== '0' ? horas.act_integracion_comunidad : '');
                        $('#actGestion').val(horas.act_gestion_academica !== '0' ? horas.act_gestion_academica : '');
                        $('#actOtras').val(horas.act_otras !== '0' ? horas.act_otras : '');
                        
                        if ($('#accion').val() === 'modificar') {
                            originalFormPaso3 = $('#form-paso3').serialize();
                        }

                        setModalStep(3);
                    } else if (lee.resultado === 'ok_datos_adicionales') {
                        const horas = lee.horas;
                        $('#verHorasAcademicas').text(horas.act_academicas || '0');
                        $('#verHorasCreacion').text(horas.act_creacion_intelectual || '0');
                        $('#verHorasIntegracion').text(horas.act_integracion_comunidad || '0');
                        $('#verHorasGestion').text(horas.act_gestion_academica || '0');
                        $('#verHorasOtras').text(horas.act_otras || '0');
                        $('#modalVerDatos').modal('show');
                    } else if (['incluir', 'modificar', 'eliminar', 'activar'].includes(lee.resultado)) {
                          const titulo = lee.resultado === 'eliminar' ? 'DESACTIVAR' : lee.resultado === 'activar' ? 'ACTIVAR' : '¡ÉXITO!';
                          muestraMensaje("success", 3000, titulo, lee.mensaje);
                          $("#modal1").modal("hide");
                          Listar();
                    } else if(lee.resultado === 'error') {
                        muestraMensaje("error", 6000, "ERROR", lee.mensaje || "Ocurrió un error.");
                    }
                } catch (e) { console.error("Error:", e, "Respuesta:", respuesta); muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta del servidor."); }
            },
            error: (request, status, err) => muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", `Ocurrió un error: ${status} - ${err}`)
        });
    }

});

$(document).on("click", ".btn-activar", function(e) {
    e.preventDefault();
    const cedula = $(this).data("cedula");
    Swal.fire({
        title: "¿Está seguro de cambiar el estado de este docente?",
        text: "El docente pasará a estar activo.",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: "Sí, cambiar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            var datos = new FormData();
            datos.append("accion", "activar");
            datos.append("cedulaDocente", cedula);
            $.ajax({
                url: "",
                type: "POST",
                contentType: false,
                data: datos,
                processData: false,
                cache: false,
                success: function(respuesta) {
                    try {
                        const lee = JSON.parse(respuesta);
                        if (lee.resultado === 'activar') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ACTIVAR',
                                html: lee.mensaje,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            
                            var datosListar = new FormData();
                            datosListar.append("accion", "consultar");
                            $.ajax({
                                url: "",
                                type: "POST",
                                contentType: false,
                                data: datosListar,
                                processData: false,
                                cache: false,
                                success: function(resp) {
                                    try {
                                        const data = JSON.parse(resp);
                                        if (data.resultado === 'consultar') {
                                            if ($.fn.DataTable.isDataTable("#tabladocente")) {
                                                $("#tabladocente").DataTable().destroy();
                                            }
                                            $("#resultadoconsulta").empty();
                                            
                                            data.mensaje.forEach(item => {
                                                const btnModificar = `<button class="btn btn-icon btn-edit" title="Modificar Docente" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
                                                const btnDesactivar = `<button class="btn btn-icon btn-delete" title="Desactivar Docente" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/power.svg" alt="Desactivar"></button>`;
                                                const btnActivar = `<button class="btn btn-icon btn-success btn-activar" title="Activar Docente" data-cedula="${item.doc_cedula}"><img src="public/assets/icons/check.svg" alt="Activar"></button>`;
                                                const btnVerDatos = `<button class="btn btn-icon btn-info" title="Ver Datos Adicionales"><img src="public/assets/icons/eye.svg" alt="Ver Datos"></button>`;
                                                const estadoBadge = item.doc_estado == '1' ? '<span class="uc-badge activa">Activo</span>' : '<span class="uc-badge desactivada">Inactivo</span>';
                                                const botonesAccion = item.doc_estado == '1' ? `${btnModificar} ${btnDesactivar} ${btnVerDatos}` : btnActivar;
                                                
                                                $("#resultadoconsulta").append(`
                                                    <tr data-cedula="${item.doc_cedula}" data-prefijo="${item.doc_prefijo}" data-condicion="${item.doc_condicion || ''}" data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}" data-titulos-texto="${item.titulos || ''}" data-coordinaciones-texto="${item.coordinaciones || ''}" data-observacion="${item.doc_observacion || ''}" data-anio-concurso="${item.doc_anio_concurso || ''}" data-tipo-concurso="${item.doc_tipo_concurso || ''}" data-fecha-ingreso="${item.doc_ingreso || ''}">
                                                        <td>${item.doc_prefijo}-${item.doc_cedula}</td>
                                                        <td>${item.doc_apellido}</td>
                                                        <td>${item.doc_nombre}</td>
                                                        <td>${item.doc_correo}</td>
                                                        <td>${item.cat_nombre}</td>
                                                        <td>${item.doc_dedicacion || 'N/A'}</td>
                                                        <td>${item.doc_condicion || 'N/A'}</td>
                                                        <td>${estadoBadge}</td>
                                                        <td class="text-nowrap">${botonesAccion}</td>
                                                    </tr>
                                                `);
                                            });
                                            
                                            $("#tabladocente").DataTable({
                                                paging: true, lengthChange: true, searching: true, ordering: true, info: true,
                                                autoWidth: false, responsive: true, scrollX: true,
                                                language: {
                                                    lengthMenu: "Mostrar _MENU_ registros",
                                                    zeroRecords: "No se encontraron resultados",
                                                    info: "Mostrando _PAGE_ de _PAGES_",
                                                    infoEmpty: "No hay registros disponibles",
                                                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                                                    search: "Buscar:",
                                                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                                                },
                                                order: [[1, "asc"]],
                                                dom: "<'row'<'col-sm-12 col-md-2'l><'col-sm-12 col-md-6'B><'col-sm-12 col-md-4'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
                                            });
                                        }
                                    } catch (e) {
                                        console.error("Error:", e);
                                    }
                                }
                            });
                        } else if (lee.resultado === 'error') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ERROR',
                                html: lee.mensaje,
                                timer: 6000,
                                timerProgressBar: true
                            });
                        }
                    } catch (e) {
                        console.error("Error:", e);
                    }
                }
            });
        }
    });
});