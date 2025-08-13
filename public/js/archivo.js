const language_es = {
    "decimal": "", "emptyTable": "No hay datos disponibles en la tabla", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros", "infoEmpty": "Mostrando 0 a 0 de 0 registros", "infoFiltered": "(filtrado de _MAX_ registros totales)", "infoPostFix": "", "thousands": ",", "lengthMenu": "Mostrar _MENU_ registros", "loadingRecords": "Cargando...", "processing": "Procesando...", "search": "Buscar:", "zeroRecords": "No se encontraron registros coincidentes", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }, "aria": { "sortAscending": ": activar para ordenar la columna de manera ascendente", "sortDescending": ": activar para ordenar la columna de manera descendente" }
};

function validarInputNumerico(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (input.value.length > 2) {
        input.value = input.value.slice(0, 2);
    }
}
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

function validarSumaAprobados() {
    const totalEstudiantes = parseInt($('#seccion option:selected').data('cantidad')) || 0;
    const cantAprobadosInput = $('#cantidad_aprobados');
    const cantPerInput = $('#cantidad_per');
    const feedbackDiv = $('#feedback-aprobados');

    if (totalEstudiantes === 0) return true;

    const aprobados = parseInt(cantAprobadosInput.val() || 0);
    const per = parseInt(cantPerInput.val() || 0);
    const suma = aprobados + per;

    if (suma > totalEstudiantes) {
        cantAprobadosInput.addClass('is-invalid');
        cantPerInput.addClass('is-invalid');
        feedbackDiv.text(`La suma (${suma}) no puede exceder el total de estudiantes (${totalEstudiantes}).`).addClass('d-block');
        return false;
    } else {
        cantAprobadosInput.removeClass('is-invalid');
        cantPerInput.removeClass('is-invalid');
        feedbackDiv.text('El campo es obligatorio.').removeClass('d-block');
        return true;
    }
}

function validarFormularioRegistro() {
    let isValid = true;
    $('.is-invalid').removeClass('is-invalid');

    if (!$('#anio').val()) { $('#anio').addClass('is-invalid'); isValid = false; }
    if (!$('#seccion').val()) { $('#seccion').addClass('is-invalid'); isValid = false; }
    if (!$('#ucurricular').val()) { $('#ucurricular').addClass('is-invalid'); isValid = false; }
    if ($('#cantidad_aprobados').val() === '') { $('#cantidad_aprobados').addClass('is-invalid'); isValid = false; }
    if ($('#cantidad_per').val() === '') { $('#cantidad_per').addClass('is-invalid'); isValid = false; }

    if (!validarSumaAprobados()) {
        isValid = false;
    }
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

    const filtroCheckbox = document.getElementById('filtroMisRegistros');
    if (filtroCheckbox) {
        datos.append("filtrar_propios", filtroCheckbox.checked);
    }

    enviaAjax(datos, function (response) {
        if ($.fn.DataTable.isDataTable("#tablaRegistros")) { 
            $("#tablaRegistros").DataTable().destroy(); 
        }
        $("#resultadosRegistros").empty();

        if (response.resultado === 'ok_registros' && Array.isArray(response.datos)) {
            response.datos.forEach(item => {
                const totalEst = parseInt(item.sec_cantidad) || 0;
const aprobadosDir = parseInt(item.apro_cantidad) || 0;
const paraPer = parseInt(item.per_cantidad) || 0;
const aprobadosPer = parseInt(item.per_aprobados) || 0;


const aprobadosTotales = aprobadosDir + aprobadosPer;


const reprobadosTotales = totalEst - aprobadosTotales;

const esPerCero = paraPer === 0;

                let archivoDefinitivoHtml = `
                    <a href="archivos_subidos/${encodeURIComponent(item.archivo_definitivo || '')}" 
                       class="btn btn-icon btn-info ${!item.archivo_definitivo ? 'disabled' : ''}" 
                       title="Descargar Acta Final" 
                       ${item.archivo_definitivo ? 'download' : ''}>
                       <img src="public/assets/icons/file-earmark-down2.svg">
                    </a>`;

                let archivoPerHtml = `
                    <a href="archivos_per/${encodeURIComponent(item.archivo_per || '')}" 
                       class="btn btn-icon btn-edit ${!item.archivo_per ? 'disabled' : ''}" 
                       title="Descargar Acta PER" 
                       ${item.archivo_per ? 'download' : ''}>
                       <img src="public/assets/icons/file-earmark-down.svg"></i>
                    </a>`;

                const perHabilitado = !esPerCero && item.per_abierto;
                const tituloBoton = perHabilitado ? "Registrar Notas del PER" : "El período de registro para PER no está activo";
                
                const btnRegistrarPer = `<button class="btn btn-icon btn-success" title="${tituloBoton}" onclick="abrirModalPer('${item.uc_codigo}', '${item.sec_codigo}', '${item.uc_nombre}', '${paraPer}', '${aprobadosPer}', '${item.ani_anio}', '${item.ani_tipo}', '${item.fase_numero}')" ${!perHabilitado ? 'disabled' : ''}><img src="public/assets/icons/file-earmark-ruled.svg"></button>`;                 
                const btnEliminarRegistro = `<button class="btn btn-icon btn-delete btn-eliminar" title="Eliminar Registro" data-uc-codigo="${item.uc_codigo}" data-sec-codigo="${item.sec_codigo}" data-uc-nombre="${item.uc_nombre}" data-anio="${item.ani_anio}" data-tipo="${item.ani_tipo}" data-fase="${item.fase_numero}"><img src="public/assets/icons/trash.svg"></button>`;
                const accionesHtml = `<div class="d-flex justify-content-start align-items-center gap-2">${archivoDefinitivoHtml}${archivoPerHtml}${btnRegistrarPer}${btnEliminarRegistro}</div>`;

                $("#resultadosRegistros").append(`<tr>
                    <td>${item.ani_anio} (${item.ani_tipo})<br><b>Fase: ${item.fase_numero}</b></td>
                    <td>${item.sec_codigo.replace(/,/g, '-')}</td>
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

function abrirModalPer(uc_codigo, sec_codigo, uc_nombre, cantidad_per, aprobados_actuales, anio, tipo, fase_numero) {
    $('#formAprobadosPer')[0].reset();
    $('#per_uc_codigo').val(uc_codigo);
    $('#per_sec_codigo').val(sec_codigo);
    $('#per_uc_nombre').val(uc_nombre);
    $('#per_anio_anio').val(anio);
    $('#per_anio_tipo').val(tipo);
    $('#per_fase_numero').val(fase_numero);
    $('#per_seccion').text(sec_codigo.replace(/,/g, '-'));
    $('#per_uc').text(uc_nombre);
    $('#per_cantidad_en_remedial').text(cantidad_per);
    $('#cantidad_aprobados_per').val(aprobados_actuales || '0').attr('max', cantidad_per);
    $('#modalAprobadosPer').modal('show');
}

$(document).ready(function () {
    listarRegistros();

    $('#btnNuevoRegistro').click(() => $('#modalRegistroNotas').modal('show'));
    
    $('#filtroMisRegistros').change(listarRegistros);

    $('#ucurricular').change(function () { 
        $('#uc_nombre').val($(this).find('option:selected').text()); 
    });

    $('#tablaRegistros').on('click', '.btn-eliminar', function() {
        const uc_codigo = $(this).data('uc-codigo');
        const sec_codigo = $(this).data('sec-codigo');
        const uc_nombre = $(this).data('uc-nombre');
        const anio = $(this).data('anio');
        const tipo = $(this).data('tipo');
        const fase = $(this).data('fase');
        eliminarRegistro(uc_codigo, sec_codigo, uc_nombre, anio, tipo, fase);
    });

    $('#anio').change(function () {
        const anio_compuesto = $(this).val();
        const seccionSelect = $('#seccion');
        const ucSelect = $('#ucurricular');

        seccionSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
        ucSelect.prop('disabled', true).html('<option value="">Seleccione una sección primero</option>');
        $('#scantidad').text('');

        if (!anio_compuesto) { 
            seccionSelect.html('<option value="">Seleccione un año primero</option>'); 
            return; 
        }

        const datos = new FormData();
        datos.append("accion", "obtener_secciones");
        datos.append("anio_compuesto", anio_compuesto);
        enviaAjax(datos, function (secciones) {
            let options = '<option value="" selected disabled>Seleccione una sección o grupo</option>';
            if (Array.isArray(secciones) && secciones.length > 0) {
                secciones.forEach(sec => { 
                    options += `<option value="${sec.sec_codigo}" data-cantidad="${sec.sec_cantidad}">${sec.sec_codigo_label}</option>`; 
                });
                seccionSelect.prop('disabled', false);
            } else { 
                options = '<option value="">No hay secciones asignadas</option>'; 
            }
            seccionSelect.html(options);
        });
    }).trigger('change');

    $('#seccion').change(function() {
        const seccion_codigo = $(this).val();
        const ucSelect = $('#ucurricular');

        const selected = $(this).find('option:selected');
        const cantidad = selected.data('cantidad');
        $('#scantidad').text(cantidad ? `Total de estudiantes: ${cantidad}` : '');
        validarSumaAprobados();

        if (!seccion_codigo) {
            ucSelect.prop('disabled', true).html('<option value="">Seleccione una sección primero</option>');
            return;
        }

        ucSelect.prop('disabled', true).html('<option value="">Cargando U.C....</option>');
        const datos = new FormData();
        datos.append("accion", "obtener_uc_por_seccion");
        datos.append("sec_codigo", seccion_codigo);
        enviaAjax(datos, function(unidades) {
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

    $('#cantidad_aprobados, #cantidad_per').on('input', validarSumaAprobados);

    $('#formRegistro').submit(function (e) {
        e.preventDefault();
        const archivo = $('#archivo_notas');
        if (archivo.get(0).files.length === 0) {
            $('#archivo_notas').addClass('is-invalid');
            return; 
        }
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

    $('#formAprobadosPer').submit(function (e) {
        e.preventDefault();
        enviaAjax(new FormData(this), function (response) {
            if (response.success) {
                $('#modalAprobadosPer').modal('hide');
                Swal.fire({
                    icon: 'success', title: '¡Actualizado!', text: response.mensaje, timer: 2000, showConfirmButton: false
                }).then(() => {
                    listarRegistros();
                });
            } else {
                Swal.fire({
                    icon: 'error', title: 'Error', text: response.mensaje
                });
            }
        });
    });

    function eliminarRegistro(uc_codigo, sec_codigo, uc_nombre, anio, tipo, fase) {
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Se eliminará el registro de notas de:<br><b>Sección(es):</b> ${sec_codigo.replace(/,/g, '-')}<br><b>U.C.:</b> ${uc_nombre}<br><b>Año:</b> ${anio} (${tipo})`,
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
                datos.append("fase_numero", fase);
                
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
});

/*  const totalEst = parseInt(item.sec_cantidad) || 0;

                const aprobadosDir = parseInt(item.apro_cantidad) || 0;

                const paraPer = parseInt(item.per_cantidad) || 0;

                const aprobadosPer = parseInt(item.per_aprobados) || 0;

                const aprobadosTotales = aprobadosDir + aprobadosPer;

                





                const reprobadosdirectos = totalEst - aprobadosDir;

                const reprobadosper = item.archivo_per ? (paraPer - aprobadosPer) : 0;

               const reprobadosTotales = reprobadosdirectos + reprobadosper;







                const esPerCero = paraPer === 0;*/