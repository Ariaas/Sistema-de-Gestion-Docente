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
                muestraMensaje("error", 5000, "Error", "Respuesta inválida del servidor."); 
            }
        },
        error: function (request, status, err) { 
            console.error("Error de petición AJAX:", status, err); 
            muestraMensaje("error", 5000, "Error", "Hubo un problema de conexión con el servidor."); 
        },
    });
}

function validarInputNumerico(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (input.value.length > 2) { input.value = input.value.slice(0, 2); }
}

function validarFormularioRegistro() {
    let isValid = true;
    $('.is-invalid').removeClass('is-invalid');

    const anio = $('#anio');
    if (!anio.val()) { anio.addClass('is-invalid'); isValid = false; }

    const seccion = $('#seccion');
    if (!seccion.val()) { seccion.addClass('is-invalid'); isValid = false; }
    
    const ucurricular = $('#ucurricular');
    if (!ucurricular.val()) { ucurricular.addClass('is-invalid'); isValid = false; }

    const cantAprobados = $('#cantidad_aprobados');
    if (cantAprobados.val() === '') { cantAprobados.addClass('is-invalid'); isValid = false; }
    
    const cantPer = $('#cantidad_per');
    if (cantPer.val() === '') { cantPer.addClass('is-invalid'); isValid = false; }

    // --- CÁLCULO DE VALIDACIÓN MEJORADO ---
    const totalEstudiantes = parseInt($('#seccion option:selected').data('cantidad')) || 0;
    const sumaAprobados = (parseInt(cantAprobados.val() || 0) + parseInt(cantPer.val() || 0));
    
    if (totalEstudiantes > 0 && sumaAprobados > totalEstudiantes) {
        cantAprobados.addClass('is-invalid');
        cantPer.addClass('is-invalid');
        $('#feedback-aprobados').text('La suma de aprobados y para PER excede el total de estudiantes.');
        isValid = false;
    } else {
         $('#feedback-aprobados').text('El campo es obligatorio.');
    }

    const archivo = $('#archivo_notas');
    if (archivo.get(0).files.length === 0) { archivo.addClass('is-invalid'); isValid = false; }

    return isValid;
}

function listarRegistros() {
    const datos = new FormData();
    datos.append("accion", "listar_registros");
    enviaAjax(datos, function (response) {
        if ($.fn.DataTable.isDataTable("#tablaRegistros")) { $("#tablaRegistros").DataTable().destroy(); }
        $("#resultadosRegistros").empty();
        if (response.resultado === 'ok_registros' && Array.isArray(response.datos)) {
            response.datos.forEach(item => {
                const totalEst = parseInt(item.sec_cantidad) || 0;
                const aprobadosDir = parseInt(item.apro_cantidad) || 0;
                const paraPer = parseInt(item.per_cantidad) || 0;
                const aprobadosPer = parseInt(item.per_aprobados) || 0;
                const aprobadosTotales = aprobadosDir + aprobadosPer;
                
                // --- CÁLCULO DE REPROBADOS CORREGIDO ---
                // El número de reprobados es simplemente el total de estudiantes menos el total de aprobados (directos + per).
                const reprobadosTotales = totalEst - aprobadosTotales;
                
                const esPerCero = paraPer === 0;

                let archivoDefinitivoHtml = item.archivo_definitivo ? `<a href="archivos_subidos/${encodeURIComponent(item.archivo_definitivo)}" download class="btn btn-sm btn-secondary" title="Descargar Acta Final"><img src="public/assets/icons/people.svg" alt="Descargar" style="width:18px; height:18px;"></a>` : '';
                let archivoPerHtml = item.archivo_per ? `<a href="archivos_per/${encodeURIComponent(item.archivo_per)}" download class="btn btn-sm btn-secondary text-primary" title="Descargar Acta PER"><img src="public/assets/icons/people.svg" alt="Descargar PER" style="width:18px; height:18px;"></a>` : `<span class="btn btn-sm disabled" style="opacity: 0.3;"><img src="public/iconos/descargar_per.svg" alt="Descargar PER" style="width:18px; height:18px;"></span>`;
                const btnRegistrarPer = `<button class="btn btn-sm btn-info" title="Registrar Notas del PER" onclick="abrirModalPer('${item.uc_codigo}', '${item.sec_codigo}', '${item.uc_nombre}', '${paraPer}', '${aprobadosPer}', '${item.ani_anio}', '${item.ani_tipo}')" ${esPerCero ? 'disabled' : ''}><img src="public/assets/icons/people.svg" alt="Registrar" style="width:16px; height:16px;"></button>`;
                const btnEliminarRegistro = `<button class="btn btn-sm btn-danger" title="Eliminar Registro" onclick="eliminarRegistro('${item.uc_codigo}', '${item.sec_codigo}', '${item.uc_nombre}')"><img src="public/assets/icons/people.svg" alt="Eliminar" style="width:16px; height:16px;"></button>`;
                
                const accionesHtml = `<div class="d-flex justify-content-start align-items-center gap-2">${archivoDefinitivoHtml}${archivoPerHtml}${btnRegistrarPer}${btnEliminarRegistro}</div>`;

                $("#resultadosRegistros").append(`<tr>
                    <td>${item.ani_anio} (${item.ani_tipo})</td>
                    <td>${item.sec_codigo}</td>
                    <td>${item.uc_nombre}</td>
                    <td class="text-center">${totalEst}</td>
                    <td class="text-center">${aprobadosDir}</td>
                    <td class="text-center">${paraPer}</td>
                    <td class="text-center">${aprobadosPer}</td>
                    <td class="text-center fw-bold text-success">${aprobadosTotales}</td>
                    <td class="text-center fw-bold text-danger">${reprobadosTotales}</td>
                    <td>${accionesHtml}</td>
                </tr>`);
            });
        }
        $("#tablaRegistros").DataTable({ responsive: true, language: language_es });
    });
}

function abrirModalPer(uc_codigo, sec_codigo, uc_nombre, cantidad_per, aprobados_actuales, anio, tipo) {
    $('#formAprobadosPer')[0].reset();
    $('#per_uc_codigo').val(uc_codigo);
    $('#per_sec_codigo').val(sec_codigo);
    $('#per_uc_nombre').val(uc_nombre);
    $('#per_anio_anio').val(anio);
    $('#per_anio_tipo').val(tipo);
    $('#per_seccion').text(sec_codigo);
    $('#per_uc').text(uc_nombre);
    $('#per_cantidad_en_remedial').text(cantidad_per);
    $('#cantidad_aprobados_per').val(aprobados_actuales || '0').attr('max', cantidad_per);
    $('#modalAprobadosPer').modal('show');
}

$(document).ready(function () {
    listarRegistros();
    $('#btnNuevoRegistro').click(() => $('#modalRegistroNotas').modal('show'));
    $('#ucurricular').change(function () { $('#uc_nombre').val($(this).find('option:selected').text()); });
    $('#seccion').change(function () {
        const selected = $(this).find('option:selected');
        const cantidad = selected.data('cantidad');
        $('#scantidad').text(cantidad ? `Total de estudiantes: ${cantidad}` : '');
    });
    $('#anio').change(function () {
        const anio_compuesto = $(this).val();
        const seccionSelect = $('#seccion');
        seccionSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
        $('#scantidad').text('');
        if (!anio_compuesto) { seccionSelect.html('<option value="">Seleccione un año primero</option>'); return; }
        const datos = new FormData();
        datos.append("accion", "obtener_secciones");
        datos.append("anio_compuesto", anio_compuesto);
        enviaAjax(datos, function (secciones) {
            let options = '<option value="" selected disabled>Seleccione una sección</option>';
            if (Array.isArray(secciones) && secciones.length > 0) {
                secciones.forEach(sec => { options += `<option value="${sec.sec_codigo}" data-cantidad="${sec.sec_cantidad}">${sec.sec_codigo}</option>`; });
                seccionSelect.prop('disabled', false);
            } else { options = '<option value="">No hay secciones asignadas</option>'; }
            seccionSelect.html(options);
        });
    }).trigger('change');
    $('#formRegistro').submit(function (e) {
        e.preventDefault();
        if (validarFormularioRegistro()) {
            enviaAjax(new FormData(this), function (response) {
                if (response.success) { muestraMensaje('success', 3500, 'Éxito', response.mensaje); $('#modalRegistroNotas').modal('hide'); listarRegistros(); } else { muestraMensaje('error', 4000, 'Error', response.mensaje); }
            });
        }
    });
    $('#formAprobadosPer').submit(function (e) {
        e.preventDefault();
        const totalEnPer = parseInt($('#cantidad_aprobados_per').attr('max')) || 0;
        let cantidadAprobados = parseInt($('#cantidad_aprobados_per').val()) || 0;
        if (cantidadAprobados > totalEnPer) { muestraMensaje('error', 3500, 'Cantidad Excedida', `Aprobados (${cantidadAprobados}) no puede superar el total en PER (${totalEnPer}).`); return; }
        if ($('#archivo_per').get(0).files.length === 0) { muestraMensaje("error", 4000, "ERROR", "Debe adjuntar el acta de notas del PER."); return; }
        enviaAjax(new FormData(this), function (response) {
            if (response.success) { muestraMensaje('success', 4000, 'Éxito', response.mensaje); $('#modalAprobadosPer').modal('hide'); listarRegistros(); } else { muestraMensaje('error', 4000, 'Error', response.mensaje, 4000); }
        });
    });
});

function eliminarRegistro(uc_codigo, sec_codigo, uc_nombre) {
    Swal.fire({
        title: "¿Está seguro?", html: `Se eliminará el registro de notas y archivos de:<br><b>Sección:</b> ${sec_codigo}<br><b>U.C.:</b> ${uc_nombre}.<br>¡Esta acción no se puede deshacer!`, icon: "warning", showCancelButton: true, confirmButtonColor: "#d33", cancelButtonText: "Cancelar", confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = new FormData();
            datos.append("accion", "eliminar_registro");
            datos.append("uc_codigo", uc_codigo);
            datos.append("sec_codigo", sec_codigo);
            enviaAjax(datos, function(response) {
                if (response.success) { muestraMensaje('success', 4000, 'Eliminado', response.mensaje); listarRegistros(); } else { muestraMensaje('error', 4000, 'Error', response.mensaje); }
            });
        }
    });
}