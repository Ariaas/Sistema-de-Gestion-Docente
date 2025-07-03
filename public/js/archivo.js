const language_es = {
    "decimal": "",
    "emptyTable": "No hay datos disponibles en la tabla",
    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
    "infoFiltered": "(filtrado de _MAX_ registros totales)",
    "infoPostFix": "",
    "thousands": ",",
    "lengthMenu": "Mostrar _MENU_ registros",
    "loadingRecords": "Cargando...",
    "processing": "Procesando...",
    "search": "Buscar:",
    "zeroRecords": "No se encontraron registros coincidentes",
    "paginate": {
        "first": "Primero",
        "last": "Último",
        "next": "Siguiente",
        "previous": "Anterior"
    },
    "aria": {
        "sortAscending": ": activar para ordenar la columna de manera ascendente",
        "sortDescending": ": activar para ordenar la columna de manera descendente"
    }
};

function enviaAjax(datos, successCallback) {
    $.ajax({
        async: true,
        url: "?pagina=archivo",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        timeout: 10000,
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

function validarFormularioRegistro() {
    if ($('#anio').val() === null || $('#anio').val() === '') {
        muestraMensaje("error", 4000, "ERROR", "Debe seleccionar un Año Académico.");
        return false;
    }
    if ($('#seccion').val() === null || $('#seccion').val() === '') {
        muestraMensaje("error", 4000, "ERROR", "Debe seleccionar una Sección.");
        return false;
    }
    if ($('#ucurricular').val() === null || $('#ucurricular').val() === '') {
        muestraMensaje("error", 4000, "ERROR", "Debe seleccionar una Unidad Curricular.");
        return false;
    }

    const totalEstudiantes = parseInt($('#seccion option:selected').data('cantidad')) || 0;
    const cantidadPer = parseInt($('#cantidad_per').val());
    const cantidadReprobados = parseInt($('#cantidad_reprobados').val());

    if (isNaN(cantidadPer) || $('#cantidad_per').val() === '' || cantidadPer < 0) {
        muestraMensaje("error", 4000, "ERROR", "La cantidad para PER debe ser un número válido y no puede estar vacío.");
        return false;
    }
    if (isNaN(cantidadReprobados) || $('#cantidad_reprobados').val() === '' || cantidadReprobados < 0) {
        muestraMensaje("error", 4000, "ERROR", "La cantidad de reprobados debe ser un número válido y no puede estar vacío.");
        return false;
    }
    if (totalEstudiantes > 0 && (cantidadPer + cantidadReprobados) > totalEstudiantes) {
        muestraMensaje('error', 4000, 'Cantidad Excedida', `La suma de estudiantes (${cantidadPer + cantidadReprobados}) no puede superar el total de la sección (${totalEstudiantes}).`);
        return false;
    }

    if ($('#fecha').val() === '') {
        muestraMensaje("error", 4000, "ERROR", "Debe seleccionar una fecha de resguardo.");
        return false;
    }

    if ($('#archivo_notas').get(0).files.length === 0) {
        muestraMensaje("error", 4000, "ERROR", "Debe adjuntar el archivo de notas definitivas.");
        return false;
    }
    
    return true;
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
                const cantidadParaPer = item.per_cantidad || '0';

                const btnRegistrarPer = `<button class="btn btn-sm btn-info" title="Registrar Aprobados"
                    onclick="abrirModalPer('${item.rem_id}', '${item.sec_codigo}', '${item.uc_nombre}', '${cantidadParaPer}', '${item.per_aprobados}')">
                    <i class="fas fa-check me-1"></i> Registrar PER
                </button>`;
                
                const btnVerNotasPer = `<button class="btn btn-sm btn-secondary" title="Ver Notas PER"
                    onclick="abrirModalVerPer('${item.rem_id}', '${item.sec_codigo}', '${item.uc_nombre}', '${item.per_aprobados || 0}')">
                    <i class="fas fa-file-alt me-1"></i> Ver Archivos
                </button>`;

                const btnEliminarRegistro = `<button class="btn btn-sm btn-danger" title="Eliminar Registro Completo"
                    onclick="eliminarRegistro('${item.rem_id}', '${item.sec_codigo}', '${item.uc_nombre}')">
                    <i class="fas fa-trash"></i> ELIMINAR
                </button>`;

                let archivoDefinitivoHtml = 'N/A';
                if (item.archivo_definitivo) {
                    const downloadPath = 'archivos_subidos/' + encodeURIComponent(item.archivo_definitivo);
                    archivoDefinitivoHtml = `<a href="${downloadPath}" download="${item.archivo_definitivo}" class="text-decoration-none">
                        <i class="fas fa-download me-1"></i> Descargar
                    </a>`;
                }
                
                const accionesHtml = `
                    <div class="d-flex justify-content-start gap-2 flex-wrap">
                        ${btnRegistrarPer}
                        ${btnVerNotasPer}
                        ${btnEliminarRegistro}
                    </div>`;

                $("#resultadosRegistros").append(`
                    <tr>
                        <td>${item.ani_anio}</td>
                        <td>${item.sec_codigo}</td>
                        <td>${item.uc_nombre}</td>
                        <td>${item.sec_cantidad}</td>
                        <td>${item.reprobados || '0'}</td>
                        <td>${cantidadParaPer}</td>
                        <td>${item.per_aprobados || '0'}</td>
                        <td>${archivoDefinitivoHtml}</td>
                        <td>${accionesHtml}</td>
                    </tr>
                `);
            });
        }
        $("#tablaRegistros").DataTable({ responsive: true, language: language_es });
    });
}

function abrirModalPer(rem_id, seccion, uc, cantidad_per, aprobados_actuales) {
    $('#formAprobadosPer')[0].reset();
    $('#rem_id_per').val(rem_id);
    $('#per_seccion').text(seccion);
    $('#per_uc').text(uc);
    $('#per_uc_nombre').val(uc);
    $('#per_seccion_codigo').val(seccion);
    $('#per_cantidad_en_remedial').text(cantidad_per);
    $('#cantidad_aprobados').val(aprobados_actuales || '0').attr('max', cantidad_per);
    $('#modalAprobadosPer').modal('show');
}

function validarCantidadesSeccion() {
    const totalEstudiantes = parseInt($('#seccion option:selected').data('cantidad')) || 0;
    if (totalEstudiantes === 0) return true;

    let cantidadPer = parseInt($('#cantidad_per').val()) || 0;
    let cantidadReprobados = parseInt($('#cantidad_reprobados').val()) || 0;

    if (cantidadPer < 0) $('#cantidad_per').val(0);
    if (cantidadReprobados < 0) $('#cantidad_reprobados').val(0);
    
    if ((cantidadPer + cantidadReprobados) > totalEstudiantes) {
        muestraMensaje('error', 3500, 'Cantidad Excedida', `La suma de estudiantes (${cantidadPer + cantidadReprobados}) no puede superar el total (${totalEstudiantes}).`);
        $(this).val(0);
        return false;
    }
    return true;
}

function validarAprobadosPer() {
    const totalEnPer = parseInt($('#cantidad_aprobados').attr('max')) || 0;
    let cantidadAprobados = parseInt($('#cantidad_aprobados').val()) || 0;

    if (cantidadAprobados < 0) {
        $('#cantidad_aprobados').val(0);
        cantidadAprobados = 0;
    }
    if (cantidadAprobados > totalEnPer) {
        muestraMensaje('error', 3500, 'Cantidad Excedida', `Aprobados (${cantidadAprobados}) no puede superar el total en PER (${totalEnPer}).`);
        $('#cantidad_aprobados').val(totalEnPer);
        return false;
    }
    return true;
}

$(document).ready(function () {
    listarRegistros();

    $('#btnNuevoRegistro').click(() => $('#modalRegistroNotas').modal('show'));
    
    $('#ucurricular').change(function() {
        $('#uc_nombre').val($(this).find('option:selected').text());
    });

    $('#seccion').change(function() {
        const selected = $(this).find('option:selected');
        $('#scantidad').text(selected.data('cantidad') ? `Total de estudiantes: ${selected.data('cantidad')}` : '');
        $('#seccion_codigo').val(selected.data('codigo'));
        $('#cantidad_per, #cantidad_reprobados').val('');
    });

    $('#modalRegistroNotas').on('hidden.bs.modal', function () {
        $('#formRegistro')[0].reset();
        $('#seccion').prop('disabled', true).html('<option value="">Seleccione un año primero</option>');
        $('#scantidad').text('');
    });

    $('#cantidad_per, #cantidad_reprobados').on('keyup change', validarCantidadesSeccion);
    $('#cantidad_aprobados').on('keyup change', validarAprobadosPer);

    $('#formRegistro').submit(function (e) {
        e.preventDefault();
        if (!validarFormularioRegistro()) {
            return;
        }
        enviaAjax(new FormData(this), function(response){
            if(response.success){
                muestraMensaje('success', 3500, 'Éxito', response.mensaje);
                $('#modalRegistroNotas').modal('hide');
                listarRegistros();
            } else {
                muestraMensaje('error', 4000, 'Error', response.mensaje);
            }
        });
    });

    $('#formAprobadosPer').submit(function (e) {
        e.preventDefault();
        if (!validarAprobadosPer()) {
            return;
        }
        if ($('#archivo_per').get(0).files.length === 0) {
            muestraMensaje("error", 4000, "ERROR", "Debe adjuntar el archivo de notas del PER.");
            return;
        }
        enviaAjax(new FormData(this), function(response){
             if(response.success){
                muestraMensaje('success', 4000, 'Éxito', response.mensaje);
                $('#modalAprobadosPer').modal('hide');
                listarRegistros();
            } else {
                muestraMensaje('error', 4000, 'Error', response.mensaje, 4000);
            }
        });
    });

    $('#anio').change(function () {
        const anio_id = $(this).val();
        const seccionSelect = $('#seccion');
        seccionSelect.prop('disabled', true).html('<option value="">Cargando...</option>');
        $('#scantidad').text('');
        if (!anio_id) {
            seccionSelect.html('<option value="">Seleccione un año primero</option>');
            return;
        }
        const datos = new FormData();
        datos.append("accion", "obtener_secciones");
        datos.append("anio_id", anio_id);
        enviaAjax(datos, function (secciones) {
            let options = '<option value="" selected disabled>Seleccione una sección</option>';
            if (Array.isArray(secciones) && secciones.length > 0) {
                secciones.forEach(sec => {
                    options += `<option value="${sec.sec_id}" data-cantidad="${sec.sec_cantidad}" data-codigo="${sec.sec_codigo}">${sec.sec_codigo}</option>`;
                });
                seccionSelect.prop('disabled', false);
            } else {
                options = '<option value="">No hay secciones para este año</option>';
            }
            seccionSelect.html(options);
        });
    });
});

function eliminarRegistro(rem_id, seccion, uc) {
    Swal.fire({
        title: "¿Está realmente seguro?",
        html: `Esta acción eliminará permanentemente el registro de remedial para:<br><b>Sección:</b> ${seccion}<br><b>U.C.:</b> ${uc}<br>También se borrarán todos los archivos asociados.<br><b>¡Esta acción no se puede deshacer!</b>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar todo",
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = new FormData();
            datos.append("accion", "eliminar_registro");
            datos.append("rem_id", rem_id);
            
            enviaAjax(datos, function(response) {
                if (response.success) {
                    muestraMensaje('success', 4000, 'Eliminado', response.mensaje);
                    listarRegistros();
                } else {
                    muestraMensaje('error', 4000, 'Error', response.mensaje);
                }
            });
        }
    });
}

function abrirModalVerPer(rem_id, seccion, uc, aprobados) {
    $('#verPer_seccion').text(seccion);
    $('#verPer_uc').text(uc);
    $('#verPer_aprobados').text(aprobados);
    const tbody = $('#listaArchivosPerModal');
    tbody.html('<tr><td colspan="2" class="text-center">Cargando...</td></tr>');
    
    const datos = new FormData();
    datos.append("accion", "listar_per_por_id");
    datos.append("rem_id", rem_id);

    enviaAjax(datos, function(response) {
        tbody.empty();
        if (response.success && Array.isArray(response.datos) && response.datos.length > 0) {
            response.datos.forEach(archivo => {
                const downloadPath = 'archivos_per/' + encodeURIComponent(archivo.nombre_guardado);
                const fila = `
                    <tr>
                        <td>
                            <a href="${downloadPath}" download="${archivo.nombre_guardado}" class="text-decoration-none">
                                <i class="fas fa-file-download me-2"></i> ${archivo.nombre_guardado}
                            </a>
                        </td>
                        <td class="text-center">
                            <button onclick="eliminarArchivoPer(encodeURIComponent('${archivo.nombre_guardado}'), '${rem_id}', '${seccion}', '${uc}', '${aprobados}')" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash me-1"></i> Eliminar
                            </button>
                        </td>
                    </tr>`;
                tbody.append(fila);
            });
        } else {
            tbody.html('<tr><td colspan="2" class="text-center">No hay archivos PER para este registro.</td></tr>');
        }
    });

    $('#modalVerNotasPer').modal('show');
}

function eliminarArchivoPer(nombreArchivoEncoded, rem_id, seccion, uc, aprobados) {
    const nombreArchivo = decodeURIComponent(nombreArchivoEncoded);
    Swal.fire({
        title: "¿Está seguro?",
        text: `Eliminará el archivo PER: "${nombreArchivo}".`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonText: "Cancelar",
        confirmButtonText: "Sí, eliminar",
    }).then((result) => {
        if (result.isConfirmed) {
            const datos = new FormData();
            datos.append("accion", "eliminar_archivo_per");
            datos.append("nombre_archivo", nombreArchivo);
            enviaAjax(datos, function(response) {
                if (response.success) {
                    muestraMensaje('success', 4000, 'Éxito', response.mensaje);
                    // Cierra el modal actual y lo vuelve a abrir para refrescar la lista
                    $('#modalVerNotasPer').modal('hide');
                    abrirModalVerPer(rem_id, seccion, uc, aprobados);
                    listarRegistros();
                } else {
                    muestraMensaje('error', 4000, 'Error', response.mensaje);
                }
            });
        }
    });
}