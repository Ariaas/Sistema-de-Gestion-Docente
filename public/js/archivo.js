// --- Objeto de idioma para DataTables ---
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

// --- Funciones de ayuda y AJAX ---
function muestraMensaje(icon, title, text, timer = 2000) {
    Swal.fire({ icon, title, text, showConfirmButton: false, timer });
}

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
                muestraMensaje("error", "Error", "Respuesta inválida del servidor.", 5000);
            }
        },
        error: function (request, status, err) {
            console.error("Error de petición AJAX:", status, err);
            muestraMensaje("error", "Error", "Hubo un problema de conexión con el servidor.", 5000);
        },
    });
}

// --- Lógica de la Tabla Principal ---
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
                const btnRegistrarPer = `<button class="btn btn-sm btn-info me-1" title="Registrar Aprobados"
                    onclick="abrirModalPer('${item.rem_id}', '${item.sec_codigo}', '${item.uc_nombre}', '${item.cantidad_per}', '${item.per_aprobados}')">
                    <i class="fas fa-check"></i>
                </button>`;
                
                const btnVerNotasPer = `<button class="btn btn-sm btn-secondary" title="Ver Notas PER"
                    onclick="abrirModalVerPer('${item.rem_id}', '${item.sec_codigo}', '${item.uc_nombre}')">
                    <i class="fas fa-file-alt"></i>
                </button>`;

                let archivoDefinitivoHtml = 'N/A';
                if (item.archivo_definitivo) {
                    const downloadPath = 'archivos_subidos/' + encodeURIComponent(item.archivo_definitivo);
                    archivoDefinitivoHtml = `<a href="${downloadPath}" download="${item.archivo_definitivo}" class="text-decoration-none">
                        <i class="fas fa-download me-1"></i> Descargar
                    </a>`;
                }

                $("#resultadosRegistros").append(`
                    <tr>
                        <td>${item.ani_anio}</td>
                        <td>${item.sec_codigo}</td>
                        <td>${item.uc_nombre}</td>
                        <td>${item.sec_cantidad}</td>
                        <td>${item.cantidad_per}</td>
                        <td>${item.per_aprobados || '0'}</td>
                        <td>${archivoDefinitivoHtml}</td>
                        <td class="text-center">${btnRegistrarPer} ${btnVerNotasPer}</td>
                    </tr>
                `);
            });
        }
        $("#tablaRegistros").DataTable({ responsive: true, language: language_es });
    });
}

// --- Lógica de Modales ---
function abrirModalVerPer(rem_id, seccion, uc) {
    $('#verPer_seccion').text(seccion);
    $('#verPer_uc').text(uc);
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
                            <button onclick="eliminarArchivoPer('${encodeURIComponent(archivo.nombre_guardado)}', '${rem_id}', '${seccion}', '${uc}')" class="btn btn-sm btn-danger">
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

function eliminarArchivoPer(nombreArchivoEncoded, rem_id, seccion, uc) {
    const nombreArchivo = decodeURIComponent(nombreArchivoEncoded);
    Swal.fire({
        title: "¿Está seguro?",
        text: `Eliminará el archivo PER: "${nombreArchivo}". Esta acción no se puede deshacer.`,
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
                    muestraMensaje('success', 'Éxito', response.mensaje);
                    abrirModalVerPer(rem_id, seccion, uc);
                } else {
                    muestraMensaje('error', 'Error', response.mensaje);
                }
            });
        }
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

// --- Eventos y Document Ready ---
$(document).ready(function () {
    listarRegistros();

    $('#btnNuevoRegistro').click(() => $('#modalRegistroNotas').modal('show'));

    $('#ucurricular').change(function() {
        const nombre_uc = $(this).find('option:selected').text();
        $('#uc_nombre').val(nombre_uc);
    });

    $('#seccion').change(function() {
        const selected = $(this).find('option:selected');
        const cantidad = selected.data('cantidad');
        const codigo = selected.data('codigo');
        $('#scantidad').text(cantidad ? `Total de estudiantes: ${cantidad}` : '');
        $('#seccion_codigo').val(codigo);
    });

    $('#modalRegistroNotas').on('hidden.bs.modal', function () {
        $('#formRegistro')[0].reset();
        $('#seccion').prop('disabled', true).html('<option value="">Seleccione un año primero</option>');
        $('#scantidad').text('');
    });
    
    $('#formRegistro').submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        enviaAjax(formData, function(response){
            if(response.success){
                muestraMensaje('success', 'Éxito', response.mensaje);
                $('#modalRegistroNotas').modal('hide');
                listarRegistros();
            } else {
                muestraMensaje('error', 'Error', response.mensaje, 4000);
            }
        });
    });

    $('#formAprobadosPer').submit(function (e) {
        e.preventDefault();
        const max = parseInt($('#cantidad_aprobados').attr('max'));
        const val = parseInt($('#cantidad_aprobados').val());
        if (val > max) {
            muestraMensaje('error', 'Error', 'Aprobados no puede ser mayor que estudiantes en PER.');
            return;
        }
        var formData = new FormData(this);
        enviaAjax(formData, function(response){
             if(response.success){
                muestraMensaje('success', 'Éxito', response.mensaje);
                $('#modalAprobadosPer').modal('hide');
                listarRegistros();
            } else {
                muestraMensaje('error', 'Error', response.mensaje, 4000);
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