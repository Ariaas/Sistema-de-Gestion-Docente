const language_es = {
    "decimal": "", "emptyTable": "No hay datos disponibles en la tabla", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros", "infoEmpty": "Mostrando 0 a 0 de 0 registros", "infoFiltered": "(filtrado de _MAX_ registros totales)", "infoPostFix": "", "thousands": ",", "lengthMenu": "Mostrar _MENU_ registros", "loadingRecords": "Cargando...", "processing": "Procesando...", "search": "Buscar:", "zeroRecords": "No se encontraron registros coincidentes", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }, "aria": { "sortAscending": ": activar para ordenar la columna de manera ascendente", "sortDescending": ": activar para ordenar la columna de manera descendente" }
};

function enviaAjax(datos, successCallback) {
    $.ajax({
        async: true, url: "?pagina=archivo", type: "POST", contentType: false, data: datos, processData: false, cache: false, timeout: 10000,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                successCallback(lee);
            } catch (e) {
                console.error("Error en análisis JSON:", e, respuesta);
                alert("Error: La respuesta del servidor no es válida.");
            }
        },
        error: function (request, status, err) {
            console.error("Error de petición AJAX:", status, err);
            alert("Error de conexión con el servidor.");
        },
    });
}

function validarFormularioRegistro() {
    let isValid = true;
    $('.is-invalid').removeClass('is-invalid');

    if (!$('#anio').val()) { $('#anio').addClass('is-invalid'); isValid = false; }
    if (!$('#docente').val()) { $('#docente').addClass('is-invalid'); isValid = false; }
    if (!$('#seccion').val()) { $('#seccion').addClass('is-invalid'); isValid = false; }
    if (!$('#ucurricular').val()) { $('#ucurricular').addClass('is-invalid'); isValid = false; }
    
    const archivo = $('#archivo_notas');
    if (archivo.get(0).files.length === 0) {
        archivo.addClass('is-invalid');
        isValid = false;
    }

    return isValid;
}

function listarRegistros() {
    const datos = new FormData();
    datos.append("accion", "listar_registros");

    enviaAjax(datos, function (response) {
        if ($.fn.DataTable.isDataTable("#tablaRegistros")) {
            $("#tablaRegistros").DataTable().destroy();
        }
        $("#resultadosRegistros").empty();

        if (response.resultado === 'ok_registros' && Array.isArray(response.datos)) {
            response.datos.forEach(item => {
                const archivoFinalHtml = `
                    <a href="archivos_subidos/${encodeURIComponent(item.archivo_definitivo || '')}" 
                       class="btn btn-icon btn-info" 
                       title="Descargar Acta Final" 
                       download>
                        <img src="public/assets/icons/file-earmark-down2.svg">
                    </a>`;
                
                const btnEliminarRegistro = `<button class="btn btn-icon btn-delete btn-eliminar" title="Eliminar Registro" data-uc-codigo="${item.uc_codigo}" data-sec-codigo="${item.sec_codigo}" data-uc-nombre="${item.uc_nombre}" data-anio="${item.ani_anio}" data-tipo="${item.ani_tipo}"><img src="public/assets/icons/trash.svg"></button>`;

                const accionesHtml = `<div class="d-flex justify-content-start align-items-center gap-2">${archivoFinalHtml}${btnEliminarRegistro}</div>`;

                $("#resultadosRegistros").append(`<tr>
                    <td>${item.ani_anio}</td>
                    <td>${item.doc_nombre || ''} ${item.doc_apellido || ''}</td>
                    <td>${item.sec_codigo.replace(/,/g, '-')}</td>
                    <td>${item.uc_nombre}</td>
                    <td>${accionesHtml}</td>
                </tr>`);
            });
        }
        $("#tablaRegistros").DataTable({ responsive: true, language: language_es });
    });
}

function cargarSecciones() {
    const anio_compuesto = $('#anio').val();
    const doc_cedula = $('#docente').val();
    const seccionSelect = $('#seccion');
    const ucSelect = $('#ucurricular');

    seccionSelect.prop('disabled', true).html('<option value="" disabled selected>Seleccione año y docente</option>');
    ucSelect.prop('disabled', true).html('<option value="" disabled selected>Seleccione una sección primero</option>');

    if (!anio_compuesto || !doc_cedula) {
        return;
    }

    seccionSelect.html('<option value="">Cargando...</option>');

    const datos = new FormData();
    datos.append("accion", "obtener_secciones");
    datos.append("anio_compuesto", anio_compuesto);
    datos.append("doc_cedula", doc_cedula);
    
    enviaAjax(datos, function (secciones) {
        let options = '<option value="" selected disabled>Seleccione una sección o grupo</option>';
        if (Array.isArray(secciones) && secciones.length > 0) {
            secciones.forEach(sec => {
                options += `<option value="${sec.sec_codigo}">${sec.sec_codigo_label}</option>`;
            });
            seccionSelect.prop('disabled', false);
        } else {
            options = '<option value="">No hay secciones asignadas</option>';
        }
        seccionSelect.html(options);
    });
}

function eliminarRegistro(uc_codigo, sec_codigo, uc_nombre, anio, tipo) {
    Swal.fire({
        title: '¿Estás seguro?',
        html: `Se eliminará el registro de notas de:<br><b>Sección(es):</b> ${sec_codigo.replace(/,/g, '-')}<br><b>U.C.:</b> ${uc_nombre}<br><b>Año:</b> ${anio}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = new FormData();
            datos.append("accion", "eliminar_registro");
            datos.append("uc_codigo", uc_codigo);
            datos.append("sec_codigo", sec_codigo);
            datos.append("ani_anio", anio);
            datos.append("ani_tipo", tipo);
            
            enviaAjax(datos, function(response) {
                if (response.success) {
                    Swal.fire('¡Eliminado!', response.mensaje, 'success').then(() => {
                        listarRegistros();
                    });
                } else {
                    Swal.fire('Error', response.mensaje, 'error');
                }
            });
        }
    });
}


function verificarRegistroExistente() {
    const anio_compuesto = $('#anio').val();
    const uc_codigo = $('#ucurricular').val();
    const sec_codigo = $('#seccion').val();
    const submitButton = $('#formRegistro button[type="submit"]');

    if (!anio_compuesto || !uc_codigo || !sec_codigo) {
        submitButton.prop('disabled', false);
        return;
    }

    const datos = new FormData();
    datos.append("accion", "verificar_existencia");
    datos.append("anio_compuesto", anio_compuesto);
    datos.append("uc_codigo", uc_codigo);
    datos.append("sec_codigo", sec_codigo);

    enviaAjax(datos, function(response) {
        if (response.existe) {
            Swal.fire({
                icon: 'warning',
                title: 'Registro Duplicado',
                text: 'Ya existe un registro con el mismo año, sección y unidad curricular. No se puede crear de nuevo.',
            });
            submitButton.prop('disabled', true); 
        } else {
            submitButton.prop('disabled', false); 
        }
    });
}

$(document).ready(function () {
    $('#docente').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalRegistroNotas')
    });

    listarRegistros();

    $('#btnNuevoRegistro').click(() => {
        $('#formRegistro')[0].reset();
        $('#docente').val(null).trigger('change');
        $('#seccion').prop('disabled', true).html('<option value="" disabled selected>Seleccione año y docente</option>');
        $('#ucurricular').prop('disabled', true).html('<option value="" disabled selected>Seleccione una sección primero</option>');
        $('#formRegistro button[type="submit"]').prop('disabled', false); 
        $('#modalRegistroNotas').modal('show');
    });

    $('#tablaRegistros').on('click', '.btn-eliminar', function() {
        const uc_codigo = $(this).data('uc-codigo');
        const sec_codigo = $(this).data('sec-codigo');
        const uc_nombre = $(this).data('uc-nombre');
        const anio = $(this).data('anio');
        const tipo = $(this).data('tipo');
        eliminarRegistro(uc_codigo, sec_codigo, uc_nombre, anio, tipo);
    });
    
    
    $('#ucurricular').change(function() {
        $('#uc_nombre').val($(this).find('option:selected').text());
        verificarRegistroExistente(); 
    });

    $('#anio, #docente, #seccion').change(verificarRegistroExistente);

    $('#anio, #docente').change(cargarSecciones);

    $('#seccion').change(function () {
        const seccion_codigo = $(this).val();
        const doc_cedula = $('#docente').val();
        const ucSelect = $('#ucurricular');

        if (!seccion_codigo || !doc_cedula) {
            ucSelect.prop('disabled', true).html('<option value="">Seleccione una sección primero</option>');
            return;
        }

        ucSelect.prop('disabled', true).html('<option value="">Cargando U.C....</option>');
        const datos = new FormData();
        datos.append("accion", "obtener_uc_por_seccion");
        datos.append("sec_codigo", seccion_codigo);
        datos.append("doc_cedula", doc_cedula);
        enviaAjax(datos, function (unidades) {
            let options = '<option value="" selected disabled>Seleccione una U.C.</option>';
            if (Array.isArray(unidades) && unidades.length > 0) {
                unidades.forEach(uc => { options += `<option value="${uc.uc_codigo}">${uc.uc_nombre}</option>`; });
                ucSelect.prop('disabled', false);
            } else {
                options = '<option value="">No hay U.C. para esta sección o grupo</option>';
            }
            ucSelect.html(options);
        });
    });

    $('#formRegistro').submit(function (e) {
        e.preventDefault();
        
        if (validarFormularioRegistro()) {
            enviaAjax(new FormData(this), function (response) {
                if (response.success) {
                    $('#modalRegistroNotas').modal('hide');
                    Swal.fire({
                        icon: 'success', title: '¡Registrado!', text: response.mensaje, timer: 2000, showConfirmButton: false
                    }).then(() => {
                        listarRegistros();
                    });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Error', text: response.mensaje
                    });
                }
            });
        }
    });
});