$(document).ready(function() {

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        const config = {
            icon: tipo,
            title: titulo,
            html: mensaje,
            showConfirmButton: false,
            timer: duracion > 0 ? duracion : undefined, 
            allowOutsideClick: false, 
        };
        if (duracion === 0) { 
            config.showConfirmButton = false; 
            config.allowOutsideClick = false;
        }
        Swal.fire(config);
    }

    function cargarRespaldosDisponibles() {
        $.ajax({
            url: '?pagina=mantenimiento', 
            type: 'POST',
            data: {
                accion: 'obtener_respaldos' 
            },
            dataType: 'json',
            success: function(archivosZip) {
                let selectRestauracion = $('#selectArchivoRespaldo');
                selectRestauracion.empty();
                selectRestauracion.append('<option value="">Seleccione un punto de restauración...</option>');

                if (archivosZip && archivosZip.length > 0) {
                    archivosZip.forEach(function(nombreArchivoZip) {

                        let textoOpcion = nombreArchivoZip;
                        const match = nombreArchivoZip.match(/(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2})/);
                        if (match) {
                            textoOpcion = `Respaldo del ${match[1]}/${match[2]}/${match[3]} ${match[4]}:${match[5]}:${match[6]}`;
                        }
                        selectRestauracion.append(`<option value="${nombreArchivoZip}">${textoOpcion}</option>`);
                    });
                } else {
                    selectRestauracion.append('<option value="">No hay respaldos ZIP disponibles</option>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error al cargar respaldos:", textStatus, errorThrown, jqXHR.responseText);
                $('#mensajeRestauracion').text('No se pudieron cargar los respaldos. Verifique la consola.');
                let selectRestauracion = $('#selectArchivoRespaldo');
                selectRestauracion.empty();
                selectRestauracion.append('<option value="">Error al cargar respaldos</option>');
            }
        });
    }

    cargarRespaldosDisponibles();

    $('#guardarRespaldo').on('click', function() {
        muestraMensaje('info', 0, 'Generando Respaldo', 'Por favor espere, esto puede tardar unos segundos o minutos...');
        $.ajax({
            url: '?pagina=mantenimiento',
            type: 'POST',
            data: {
                accion: 'guardar_respaldo' 
            },
            dataType: 'json',
            success: function(respuesta) {
                Swal.close();
                if (respuesta.status === 'success') {
                    muestraMensaje('success', 3000, 'Éxito', respuesta.message);
                    cargarRespaldosDisponibles(); 
                } else if (respuesta.status === 'warning') {
                    muestraMensaje('warning', 5000, 'Advertencia', respuesta.message);
                    cargarRespaldosDisponibles(); 
                } else {
                    muestraMensaje('error', 5000, 'Error', respuesta.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.close();
                console.error("Error al guardar respaldo:", textStatus, errorThrown, jqXHR.responseText);
                muestraMensaje('error', 5000, 'Error', 'Ocurrió un error al intentar guardar el respaldo. Revise la consola para más detalles.');
            }
        });
    });

    $('#restaurarSistemaBtn').on('click', function() {
        const archivoZipSeleccionado = $('#selectArchivoRespaldo').val(); 

        if (!archivoZipSeleccionado) {
            $('#mensajeRestauracion').text('Por favor, seleccione un archivo de respaldo (.zip) de la lista.');
            Swal.fire('Atención', 'Debe seleccionar un archivo de respaldo de la lista.', 'warning');
            return;
        }
        $('#mensajeRestauracion').text(''); 

        Swal.fire({
            title: '¿Está seguro de restaurar el sistema?',
            html: `Se restaurarán <strong>ambas bases de datos</strong> desde el archivo:<br>
                   <strong>${archivoZipSeleccionado}</strong><br><br>
                   Esta acción es irreversible y podría causar pérdida de datos recientes.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ¡Restaurar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                muestraMensaje('info', 0, 'Restaurando Sistema', 'Por favor espere, esto puede tardar varios minutos...');
                $.ajax({
                    url: '?pagina=mantenimiento',
                    type: 'POST',
                    data: {
                        accion: 'restaurar_sistema',
                        archivo_sql: archivoZipSeleccionado 
                    },
                    dataType: 'json',
                    success: function(respuesta) {
                        Swal.close();
                        if (respuesta.status === 'success') {
                            muestraMensaje('success', 4000, 'Éxito', respuesta.message);
                           
                            let selectRestauracion = $('#selectArchivoRespaldo');
                            selectRestauracion.empty(); 
                            selectRestauracion.append('<option value="">Cargando respaldos...</option>'); 
                            cargarRespaldosDisponibles(); 
                            $('#mensajeRestauracion').text(''); 
                        } else if (respuesta.status === 'warning') {
                             muestraMensaje('warning', 6000, 'Advertencia', respuesta.message);
                           
                        }
                        else {
                            muestraMensaje('error', 6000, 'Error', respuesta.message);
                            $('#mensajeRestauracion').text(respuesta.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.close();
                        console.error("Error al restaurar sistema:", textStatus, errorThrown, jqXHR.responseText);
                        muestraMensaje('error', 6000, 'Error', 'Ocurrió un error al intentar restaurar el sistema. Revise la consola.');
                        $('#mensajeRestauracion').text('Error de comunicación con el servidor durante la restauración.');
                    }
                });
            }
        });
    });
});