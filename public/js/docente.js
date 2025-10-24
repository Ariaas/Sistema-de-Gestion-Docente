$(document).ready(function() {
    let cachedTeacherData = null;

    $('#titulos, #coordinaciones').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione...',
        allowClear: false,
        width: '100%',
        closeOnSelect: false
    });

    $('#titulos, #coordinaciones').on('select2:unselecting', function(e) {
        const currentValues = $(this).val() || [];
        if (currentValues.length <= 1) {
            e.preventDefault();
        }
    });

    function setErrorText(spanElement, message) {
        if (message) {
            spanElement.text(message).removeClass('text-danger').addClass('text-secondary');
        } else {
            spanElement.text("").removeClass('text-secondary');
        }
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
            if (accion === 'desactivar') {
                footer.append('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>');
                footer.append('<button type="button" class="btn btn-danger" id="btn-desactivar">DESACTIVAR</button>');
            } else {
                modalTitle.text(accion === 'incluir' ? "Paso 1: Datos Personales" : "Paso 1: Modificar Datos Personales");
                footer.append('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>');
                footer.append('<button type="button" class="btn btn-primary" id="btn-next-1">SIGUIENTE</button>');
            }
        } else if (step === 2) {
            $('#step2-academico').show();
            modalTitle.text("Paso 2: Datos Académicos");
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-2">ATRÁS</button>');
            footer.append('<button type="button" class="btn btn-primary" id="btn-next-2">SIGUIENTE</button>');
        } else if (step === 3) {
            $('#step3-actividad').show();
            modalTitle.text('Paso 3: Actividades');
            $('#nombreDocenteHoras').text(nombreDocente);
            const finalButtonText = ($('#accion').val() === 'incluir') ? "REGISTRAR" : "MODIFICAR";
            footer.append('<button type="button" class="btn btn-secondary" id="btn-prev-3">ATRÁS</button>');
            footer.append(`<button type="button" class="btn btn-success" id="btn-final-submit">${finalButtonText}</button>`);
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

    $(document).on('click', '#btn-desactivar', function() {
        const datos = new FormData();
        datos.append("accion", "eliminar");
        datos.append("cedulaDocente", $('#cedulaDocente').val());
        enviaAjax(datos);
    });

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
        $('#verPreferenciasContainer').html('<p class="text-muted">No hay preferencias registradas.</p>');
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
        if (!$('#cedulaDocente').val()) { setErrorText($('#scedulaDocente'), 'La cédula es requerida.'); }
        if (!$('#nombreDocente').val()) { setErrorText($('#snombreDocente'), 'El nombre es requerido.'); }
        if (!$('#apellidoDocente').val()) { setErrorText($('#sapellidoDocente'), 'El apellido es requerido.'); }
        if (!$('#correoDocente').val()) { setErrorText($('#scorreoDocente'), 'El correo es requerido.'); }
        if (!$('#categoria').val()) { setErrorText($('#scategoria'), 'Debe seleccionar una categoría.'); }
        if (!$('#dedicacion').val()) { setErrorText($('#sdedicacion'), 'Debe seleccionar una dedicación.'); }
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
                }
            }
        });
    }

    $('.horas-input').on('input', function() {
        mostrarErroresPaso3();
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
            spanCedula.text("Cédula inválida (7-8 dígitos).").removeClass('text-danger').addClass('text-secondary');
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
            $("#scedulaDocente").text("Este campo es requerido.").removeClass('text-secondary').addClass('text-danger');
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
            spanId.text("Formato inválido.").removeClass('text-danger').addClass('text-secondary');
        } else {
            spanId.text("").removeClass('text-danger text-secondary');
        }
    }).on("blur", function() {
        if (!$(this).val()) {
            $("#s" + $(this).attr('id')).text("Este campo es requerido.").removeClass('text-secondary').addClass('text-danger');
        }
    });

    $("#correoDocente").on("keyup", function() {
        const spanCorreo = $("#scorreoDocente");
        
        if (this.value.length === 0) {
            spanCorreo.text("").removeClass('text-danger text-secondary');
            return;
        }
        
        if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/.test(this.value)) {
            spanCorreo.text("Correo inválido.").removeClass('text-danger').addClass('text-secondary');
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
            $("#scorreoDocente").text("Este campo es requerido.").removeClass('text-secondary').addClass('text-danger');
        }
    });
    
    $("#categoria, #dedicacion").on("change", function() {
        const el = $(this);
        if (!el.val()) {
             setErrorText($("#s" + el.attr('id')), "Debe seleccionar una opción.");
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
    

    function pone(pos, accion) {
        limpia();
        $("#accion").val(accion);
        const fila = $(pos).closest("tr");

        const cedulaCompleta = fila.find("td:eq(0)").text().trim();
        const [prefijo, cedula] = cedulaCompleta.split('-');
        
        $("#prefijoCedula").val(prefijo);
        $("#cedulaDocente").val(cedula);
        
        if (accion === 'desactivar') {
            $("#modal-title").text(`Desactivar Docente - ${prefijo}-${cedula}`);
            $("form#f :input").prop('disabled', true);
            $("#proceso").text("DESACTIVAR");
        } else {
            $("#modal-title").text(`Modificar Docente - ${prefijo}-${cedula}`);
            $("#prefijoCedula").closest('.col-md-2').hide();
            $("#cedulaDocente").closest('.col-md-4').hide();
            $("#nombreDocente").closest('.col-md-6').removeClass('col-md-6').addClass('col-md-12');
            $("form#f :input").prop('disabled', false);
            $("#cedulaDocente").prop('disabled', true);
        }
        
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
        
        $("#prefijoCedula").closest('.col-md-2').show();
        $("#cedulaDocente").closest('.col-md-4').show();
        $("#nombreDocente").closest('.col-md-12').removeClass('col-md-12').addClass('col-md-6');
        
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
                    } else if ((lee.resultado === 'existe' || lee.resultado === 'existe_docente') && lee.mensaje) {
                        setErrorText($("#scorreoDocente"), lee.mensaje);
                    } else if (lee.resultado === 'no_existe') {
                        setErrorText($("#scorreoDocente"), "");
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

const btnVerDatos = `<button class="btn btn-icon btn-info" onclick='poneVerHorario(this)' title="Ver Datos Adicionales">
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
        title: "¿Está seguro de activar este docente?",
        text: "El docente pasará a estar activo.",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, activar",
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
                                                const btnVerDatos = `<button class="btn btn-icon btn-info" onclick='poneVerHorario(this)' title="Ver Datos Adicionales"><img src="public/assets/icons/eye.svg" alt="Ver Datos"></button>`;
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