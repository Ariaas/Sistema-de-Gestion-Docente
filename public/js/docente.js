$(document).ready(function() {
    let formPaso1Interacted = false;
    let formPaso2Interacted = false;
    let formPaso3Interacted = false;
    let formPaso4Interacted = false;
    let cachedTeacherData = null;

    

    function setModalStep(step) {
        const footer = $('#modal-footer');
        const modalTitle = $('#modal-title');
        footer.empty();

        $('#step1-docente, #step2-academico, #step3-actividad, #step4-preferencias').hide();
        
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
            footer.append('<button type="button" class="btn btn-primary" id="btn-next-2">SIGIENTE</button>');
        } else if (step === 3) {
            $('#step3-actividad').show();
            modalTitle.text('Paso 3: Actividades');
            $('#nombreDocenteHoras').text(nombreDocente);
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-3">ATRÁS</button>');
            footer.append('<button type="button" class="btn btn-primary" id="btn-next-3">SIGUIENTE</button>');
        } else if (step === 4) {
            $('#step4-preferencias').show();
            modalTitle.text('Paso 4: Preferencias de Horario');
            $('#nombreDocentePreferencias').text(nombreDocente);
            const finalButtonText = ($('#accion').val() === 'incluir') ? "REGISTRAR" : "MODIFICAR";
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-4">ATRÁS</button>');
            footer.append(`<button type="button" class="btn btn-success" id="btn-final-submit">${finalButtonText}</button>`);
        }
    }

 
    $(document).on('click', '#btn-next-1', function() {
        formPaso1Interacted = true;
        mostrarErroresPaso1();
        if (validarenvioPaso1()) {
            setModalStep(2);
            $('#f').one('input change', () => { formPaso2Interacted = true; mostrarErroresPaso2(); });
        }
    });

    $(document).on('click', '#btn-next-2', function() {
        formPaso2Interacted = true;
        mostrarErroresPaso2();
        if (validarenvioPaso2()) {
            const datos = new FormData();
            datos.append("accion", "consultar_paso2");
            datos.append("doc_cedula", $('#cedulaDocente').val());
            enviaAjax(datos);
        }
    });
    
    $(document).on('click', '#btn-next-3', function() {
        formPaso3Interacted = true;
        if(validarPaso3()){
            setModalStep(4);
            $('#step4-preferencias').one('input change', () => { formPaso4Interacted = true; mostrarErroresPaso4(); });
        }
    });

    $(document).on('click', '#btn-prev-2', function() { setModalStep(1); });
    $(document).on('click', '#btn-prev-3', function() { setModalStep(2); });
    $(document).on('click', '#btn-prev-4', function() { setModalStep(3); });

    $(document).on('click', '#btn-final-submit', function() {
        formPaso4Interacted = true;
        if (validarPaso4()) {
            cachedTeacherData = new FormData($('#f')[0]);
            if ($('#accion').val() === 'modificar') {
                cachedTeacherData.append('cedulaDocente', $('#cedulaDocente').val());
            }

            $('#step3-actividad .horas-input').each(function() {
                const valor = $(this).val() === '' ? '0' : $(this).val();
                cachedTeacherData.append($(this).attr('name'), valor);
            });

            $('#step4-preferencias .dia-preferencia-check').each(function() {
                if ($(this).is(':checked')) {
                    const dia = $(this).val();
                    cachedTeacherData.append(`preferencia[${dia}][activado]`, 'on');
                    cachedTeacherData.append(`preferencia[${dia}][inicio]`, $(`#inicio-${dia}`).val());
                    cachedTeacherData.append(`preferencia[${dia}][fin]`, $(`#fin-${dia}`).val());
                }
            });

            enviaAjax(cachedTeacherData);
        }
    });
    
    $('#modal1').on('hidden.bs.modal', function() { limpia(); });

    $(document).on('click', '.ver-datos-btn', function() {
        const fila = $(this).closest('tr');
        const cedula = fila.find('td:eq(1)').text();
        const nombre = fila.find('td:eq(2)').text() + ' ' + fila.find('td:eq(3)').text();
        $('#verNombreDocente').text(nombre);
        $('#verHorasAcademicas, #verHorasCreacion, #verHorasIntegracion, #verHorasGestion, #verHorasOtras').text('0');
        $('#verPreferenciasContainer').html('<p class="text-muted">No hay preferencias registradas.</p>');
        const datos = new FormData();
        datos.append('accion', 'consultar_datos_adicionales');
        datos.append('doc_cedula', cedula);
        enviaAjax(datos);
    });

    $(document).on('change', '.dia-preferencia-check', function() {
        const isChecked = $(this).is(':checked');
        const row = $(this).closest('.row');
        row.find('.hora-preferencia').prop('disabled', !isChecked);
        if(!isChecked) {
            row.find('.hora-preferencia').val('');
            validarFilaHorario(row); 
        }
        if (formPaso4Interacted) mostrarErroresPaso4();
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
        if (!$('#cedulaDocente').val()) { $('#scedulaDocente').text('La cédula es requerida.'); }
        if (!$('#nombreDocente').val()) { $('#snombreDocente').text('El nombre es requerido.'); }
        if (!$('#apellidoDocente').val()) { $('#sapellidoDocente').text('El apellido es requerido.'); }
        if (!$('#correoDocente').val()) { $('#scorreoDocente').text('El correo es requerido.'); }
        if (!$('#categoria').val()) { $('#scategoria').text('Debe seleccionar una categoría.'); }
        if (!$('#dedicacion').val()) { $('#sdedicacion').text('Debe seleccionar una dedicación.'); }
        if (!$('#condicion').val()) { $('#scondicion').text('Debe seleccionar una condición.'); }
        const anioConcursoInput = $('#anioConcurso');
        if (anioConcursoInput.prop('required') && !anioConcursoInput.val()) {
            $('#sanioConcurso').text('El año de concurso es requerido.');
        } else if (anioConcursoInput.val()) {
            const hoy = new Date();
            const fechaSeleccionada = new Date(anioConcursoInput.val());
            fechaSeleccionada.setMinutes(fechaSeleccionada.getMinutes() + fechaSeleccionada.getTimezoneOffset());
            hoy.setHours(0, 0, 0, 0);
            if (fechaSeleccionada > hoy) { $('#sanioConcurso').text('El año de concurso no puede ser una fecha futura.'); }
            else { $('#sanioConcurso').text(''); }
        }
    }

    function mostrarErroresPaso2() {
        if ($("input[name='titulos[]']:checked").length === 0) { $('#stitulos').text('Debe seleccionar al menos un título.'); }
        if (!$('#fechaIngreso').val()) { $('#sfechaIngreso').text('La fecha de ingreso es requerida.'); }
    }
    
    function mostrarErroresPaso3() {
        validarCamposHoraria();
        validarCargaHoraria();
    }

    function mostrarErroresPaso4() {
        if ($('.dia-preferencia-check:checked').length === 0) { 
            $('#spreferencias').text('Debe seleccionar al menos un día.'); 
        } else { 
            $('#spreferencias').text(''); 
        }
    }

    function validarenvioPaso1() {
        let esValido = true;
        if (!/^[0-9]{7,8}$/.test($("#cedulaDocente").val())) esValido = false;
        if (!/^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#nombreDocente").val())) esValido = false;
        if (!/^[A-Za-z\u00f1\u00d1\s]{3,30}$/.test($("#apellidoDocente").val())) esValido = false;
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test($("#correoDocente").val())) esValido = false;
        if (!$('#categoria').val()) esValido = false;
        if (!$('#dedicacion').val()) esValido = false;
        if (!$('#condicion').val()) esValido = false;
        const anioConcursoInput = $('#anioConcurso');
        if (anioConcursoInput.prop('required') && !anioConcursoInput.val()) {
            esValido = false;
        } else if (anioConcursoInput.val()) {
            const hoy = new Date();
            const fechaSeleccionada = new Date(anioConcursoInput.val());
            fechaSeleccionada.setMinutes(fechaSeleccionada.getMinutes() + fechaSeleccionada.getTimezoneOffset());
            hoy.setHours(0, 0, 0, 0);
            if (fechaSeleccionada > hoy) esValido = false;
        }
        if (!esValido && formPaso1Interacted) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise los campos del Paso 1.");
        }
        return esValido;
    }

    function validarenvioPaso2() {
        let esValido = true;
        if (!$('#fechaIngreso').val()) esValido = false;
        if ($("input[name='titulos[]']:checked").length === 0) esValido = false;
        if (!esValido && formPaso2Interacted) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise los campos del Paso 2.");
        }
        return esValido;
    }

    function validarPaso3() {
        mostrarErroresPaso3(); 
        const camposValidos = validarCamposHoraria();
        const totalValido = $('#sHorasTotales').text() === '';
        
        if (!camposValidos || !totalValido) {
            muestraMensaje("error", 4000, "Error de Validación", "Por favor, revise la carga horaria.");
            return false;
        }
        return true;
    }

    function validarPaso4() {
        let esValido = true;
        const errores = [];
        const diasSeleccionados = $('.dia-preferencia-check:checked');
        if (diasSeleccionados.length === 0) {
            errores.push('Debe seleccionar y configurar al menos un día de preferencia.');
            esValido = false;
        } else {
            let errorDeHorario = false;
            diasSeleccionados.each(function() {
                const row = $(this).closest('.row');
                if (!validarFilaHorario(row)) {
                    errorDeHorario = true;
                }
            });
            if (errorDeHorario) {
                errores.push('Revise las horas de preferencia, hay valores incorrectos.');
                esValido = false;
            }
        }
        if (!esValido) {
            let mensajeHtml = "Por favor, corrija los siguientes errores:<br><br><ul class='list-unstyled text-start ps-4'>";
            errores.forEach(error => {
                mensajeHtml += `<li><i class="fas fa-times-circle text-danger me-2"></i>${error}</li>`;
            });
            mensajeHtml += "</ul>";
            muestraMensaje('error', 8000, 'Error de Validación - Paso 4', mensajeHtml);
        }
        return esValido;
    }

    function validarCamposHoraria() {
        let esValido = true;
        const camposRequeridos = ['#actAcademicas', '#actCreacion', '#actIntegracion', '#actGestion'];
        
        camposRequeridos.forEach(function(selector) {
            const input = $(selector);
            const errorSpan = $('#s' + input.attr('id'));
            const valor = input.val();
            
            if (valor === '' || valor === null || isNaN(parseInt(valor))) {
                errorSpan.text('Este campo es requerido.');
                esValido = false;
            } else {
                errorSpan.text('');
            }
        });
        return esValido;
    }

    $('#condicion').on('change', function() {
        if (formPaso1Interacted && !$(this).val()) { $('#scondicion').text('Debe seleccionar una condición.'); }
        else { $('#scondicion').text(''); }
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
        $('#f').one('input change', () => { formPaso1Interacted = true; mostrarErroresPaso1(); });
        $("#modal1").modal("show");
    });

    $(document).on('click', '.modificar-btn', function() { pone(this, 'modificar'); });
    $(document).on('click', '.eliminar-btn', function() {
        const fila = $(this).closest("tr");
        const cedula = fila.find("td:eq(1)").text();
        Swal.fire({
            title: "¿Está seguro de eliminar este docente?", text: "Esta acción no se puede deshacer.", icon: "warning",
            showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                const datos = new FormData();
                datos.append("accion", "eliminar");
                datos.append("cedulaDocente", cedula);
                enviaAjax(datos);
            }
        });
    });
    
    function validarCargaHoraria() {
        const dedicacion = $('#dedicacion').val();
        const spanTotal = $("#sHorasTotales");
        const botonSiguiente = $("#btn-next-3");
    
        spanTotal.text('');
        botonSiguiente.prop('disabled', false);
    
        if (!dedicacion) {
            botonSiguiente.prop('disabled', true);
            return;
        }
    
        let totalCarga = 0;
        $('.horas-input').each(function() {
            if ($(this).val() !== '') {
                let valor = parseInt($(this).val());
                if(valor < 0) { 
                    $(this).val(''); 
                    valor = 0;
                }
                if (!isNaN(valor)) {
                    totalCarga += valor;
                }
            }
        });
    
        let maxHoras = 0;
        switch (String(dedicacion).toLowerCase()) {
            case 'exclusiva': maxHoras = 42; break;
            case 'tiempo completo': maxHoras = 36; break;
            case 'medio tiempo': maxHoras = 21; break;
            case 'tiempo convencional': maxHoras = 7; break;
        }
    
        if (maxHoras > 0 && totalCarga > maxHoras) {
            spanTotal.text(`Error: El total de horas (${totalCarga}) supera el límite de ${maxHoras} para esta dedicación.`);
            botonSiguiente.prop('disabled', true);
        } else {
            spanTotal.text('');
            botonSiguiente.prop('disabled', false);
        }
    }

    $('.horas-input').on('input', function() {
        if(formPaso3Interacted) {
            mostrarErroresPaso3();
        }
    });
    
    $('#dedicacion').on('change', validarCargaHoraria);
    
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
    });
    
    $("#categoria, #dedicacion, #condicion").on("change", function() {
        const el = $(this);
        const spanId = "#s" + el.attr('id');
        if (formPaso1Interacted && el.prop('required') && !el.val()) { $(spanId).text("Este campo es requerido."); }
        else { $(spanId).text(""); }
    });

    $("#fechaIngreso, #anioConcurso").on("change", function() {
        const el = $(this);
        const spanId = "#s" + el.attr('id');
        let interactedFlag = el.attr('id') === 'fechaIngreso' ? formPaso2Interacted : formPaso1Interacted;
        if (interactedFlag && el.prop('required') && !el.val()) {
             $(spanId).text("Este campo es requerido.");
             return;
        }
        
        if(el.attr('id') === 'anioConcurso' && el.val()){
            const hoy = new Date();
            const fechaSeleccionada = new Date(el.val());
            fechaSeleccionada.setMinutes(fechaSeleccionada.getMinutes() + fechaSeleccionada.getTimezoneOffset());
            hoy.setHours(0, 0, 0, 0);

            if (fechaSeleccionada > hoy) {
                $(spanId).text("El año de concurso no puede ser una fecha futura.");
            } else {
                $(spanId).text("");
            }
        } else {
             if(el.attr('id') === 'fechaIngreso' || (el.attr('id') === 'anioConcurso' && !el.prop('required')))
                $(spanId).text("");
        }
    });
    
    $("input[name='titulos[]']").on("change", function() {
        if (formPaso2Interacted && $("input[name='titulos[]']:checked").length === 0) { $("#stitulos").text("Debe seleccionar al menos un título."); }
        else { $("#stitulos").text(""); }
    });
    
    $("input[name='coordinaciones[]']").on("change", function() { $("#scoordinaciones").text(""); });
    

    function pone(pos, accion) {
        limpia();
        $("#accion").val(accion);
        const fila = $(pos).closest("tr");
        $("#prefijoCedula").val(fila.find("td:eq(0)").text());
        $("#cedulaDocente").val(fila.find("td:eq(1)").text());
        $("#nombreDocente").val(fila.find("td:eq(2)").text());
        $("#apellidoDocente").val(fila.find("td:eq(3)").text());
        $("#correoDocente").val(fila.find("td:eq(4)").text());
        $('#categoria').val(fila.find("td:eq(5)").text());
        $('#dedicacion').val(fila.find("td:eq(6)").text());
        $('#condicion').val(fila.find("td:eq(7)").text()).trigger('change');
        $("#fechaIngreso").val(fila.find("td:eq(12)").text());
        $("#observacionesDocente").val(fila.attr('data-observacion'));
        const anioConcurso = fila.find("td:eq(9)").text();
        if (anioConcurso !== 'N/A') $("#anioConcurso").val(anioConcurso);
        const titulosIds = fila.attr('data-titulos-ids');
        const coordinacionesIds = fila.attr('data-coordinaciones-ids');
        if (titulosIds) titulosIds.split(',').forEach(id => { if (id) $(`input[name='titulos[]'][value="${id.trim()}"]`).prop('checked', true); });
        if (coordinacionesIds) coordinacionesIds.split(',').forEach(id => { if (id) $(`input[name='coordinaciones[]'][value="${id.trim()}"]`).prop('checked', true); });
        $("form#f :input").prop('disabled', false);
        $("#cedulaDocente").prop('disabled', true);
        $('#f').one('input change', () => { formPaso1Interacted = true; mostrarErroresPaso1(); });
        setModalStep(1);
        $("#modal1").modal("show");
    }

    function limpia() {
        $("form#f, #form-paso3, #form-paso4")[0].reset();
        $('#actAcademicas, #actCreacion, #actIntegracion, #actGestion, #actOtras').val('');
        $('#step3-actividad .text-danger, #step4-preferencias .text-danger').text('');
        $('.dia-preferencia-check').prop('checked', false);
        $('.hora-preferencia').val('').prop('disabled', true);
        $("form#f :input").prop('disabled', false);
        $(".text-danger").text("");
        $('#concurso-fields-wrapper').hide();
        cachedTeacherData = null;
        formPaso1Interacted = false;
        formPaso2Interacted = false;
        formPaso3Interacted = false;
        formPaso4Interacted = false;
        setModalStep(1);
    }

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
    }

    function validarFilaHorario(rowElement) {
        const errorSpan = rowElement.find('.error-hora-preferencia');
        const inicioInput = rowElement.find('input[id^="inicio-"]');
        const finInput = rowElement.find('input[id^="fin-"]');
        const inicio = inicioInput.val();
        const fin = finInput.val();
        
        errorSpan.text('');

        if (!inicio || !fin) {
            return false;
        }

        const horaMinima = '07:00';
        const horaMaxima = '23:00';

        if (inicio >= fin) {
            errorSpan.text('La hora de inicio debe ser anterior a la de fin.');
            return false;
        }

        if (inicio < horaMinima || fin > horaMaxima || inicio > horaMaxima || fin < horaMinima) {
            errorSpan.text('El horario debe ser entre 07:00 AM y 11:00 PM.');
            return false;
        }

        return true;
    }

    $(document).on('change', '.hora-preferencia', function() {
        const row = $(this).closest('.row');
        validarFilaHorario(row);
    });

    function enviaAjax(datos) {
        $.ajax({
            url: "", type: "POST", contentType: false, data: datos,
            processData: false, cache: false,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    if (lee.resultado === 'Existe') {
                         if (lee.existe) { $("#scedulaDocente").text("Cédula ya registrada."); }
                         else { $("#scedulaDocente").text(""); }
                    } else if (lee.resultado === 'consultar') {
                        destruyeDT();
                        $("#resultadoconsulta").empty();
                        lee.mensaje.forEach(item => {
                             const btnModificar = `<button class="btn btn-warning btn-sm modificar-btn" title="Modificar Docente"><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
                             const btnEliminar = `<button class="btn btn-danger btn-sm eliminar-btn" title="Eliminar Docente"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                             const btnVerDatos = `<button class="btn btn-info btn-sm ver-datos-btn" title="Ver Datos Adicionales"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg></button>`;
                            $("#resultadoconsulta").append(`
                                <tr data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}" data-observacion="${item.doc_observacion || ''}">
                                    <td>${item.doc_prefijo}</td><td>${item.doc_cedula}</td>
                                    <td>${item.doc_nombre}</td><td>${item.doc_apellido}</td>
                                    <td>${item.doc_correo}</td><td>${item.cat_nombre}</td>
                                    <td>${item.doc_dedicacion || 'N/A'}</td><td>${item.doc_condicion || 'N/A'}</td>
                                    <td>${item.doc_tipo_concurso || 'N/A'}</td><td>${item.doc_anio_concurso || 'N/A'}</td>
                                    <td>${item.titulos || 'Sin títulos'}</td><td>${item.coordinaciones || 'Sin coordinaciones'}</td>
                                    <td>${item.doc_ingreso}</td><td>${item.doc_observacion || 'Sin observaciones'}</td>
                                    <td class="text-nowrap"> ${btnModificar} ${btnEliminar} ${btnVerDatos} </td>
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

                        const preferencias = lee.preferencias;
                        $('.dia-preferencia-check').prop('checked', false).trigger('change');
                        for (const dia in preferencias) {
                            $(`#check-${dia}`).prop('checked', true).trigger('change');
                            $(`#inicio-${dia}`).val(preferencias[dia].inicio);
                            $(`#fin-${dia}`).val(preferencias[dia].fin);
                        }
                        setModalStep(3);
                    } else if (lee.resultado === 'ok_datos_adicionales') {
                        const horas = lee.horas;
                        $('#verHorasAcademicas').text(horas.act_academicas || '0');
                        $('#verHorasCreacion').text(horas.act_creacion_intelectual || '0');
                        $('#verHorasIntegracion').text(horas.act_integracion_comunidad || '0');
                        $('#verHorasGestion').text(horas.act_gestion_academica || '0');
                        $('#verHorasOtras').text(horas.act_otras || '0');
                        const preferencias = lee.preferencias;
                        const container = $('#verPreferenciasContainer');
                        container.empty();
                        if (Object.keys(preferencias).length > 0) {
                            let html = '<ul class="list-group">';
                            for (const dia in preferencias) {
                                const inicio = preferencias[dia].inicio ? new Date('1970-01-01T' + preferencias[dia].inicio).toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true }) : 'No especificado';
                                const fin = preferencias[dia].fin ? new Date('1970-01-01T' + preferencias[dia].fin).toLocaleTimeString('en-US', { hour: 'numeric', minute: 'numeric', hour12: true }) : 'No especificado';
                                html += `<li class="list-group-item"><strong>${dia.charAt(0).toUpperCase() + dia.slice(1)}:</strong> De ${inicio} a ${fin}</li>`;
                            }
                            html += '</ul>';
                            container.html(html);
                        } else {
                            container.html('<p class="text-muted">No hay preferencias de horario registradas.</p>');
                        }
                        $('#modalVerDatos').modal('show');
                    } else if (['incluir', 'modificar', 'eliminar'].includes(lee.resultado)) {
                         muestraMensaje("success", 3000, "¡ÉXITO!", lee.mensaje);
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

    function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
        if (!etiqueta.val() || !er.test(etiqueta.val())) {
            etiquetamensaje.text(mensaje);
            return false;
        }
        etiquetamensaje.text("");
        return true;
    }

    function setupFilter(inputId, containerId) {
        $(document).on('keyup', inputId, function() {
            const filterValue = $(this).val().toLowerCase();
            const items = $(containerId).find('.form-check');
            items.each(function() {
                const labelText = $(this).find('label').text().toLowerCase();
                if (labelText.includes(filterValue)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }

    setupFilter('#filtroTitulos', '#titulos-container');
    setupFilter('#filtroCoordinaciones', '#coordinaciones-container');
});