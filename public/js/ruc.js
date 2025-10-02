$(document).ready(function() {
    $('#anio_id, #trayecto, #ucurricular').select2({
        theme: "bootstrap-5"
    });

    $('#trayecto').on('change', function() {
        var trayectoId = $(this).val();
        var ucurricularSelect = $('#ucurricular');

        ucurricularSelect.html('<option value="">Cargando...</option>').prop('disabled', true);

        $.ajax({
            url: '?pagina=ruc',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'filtrar_uc',
                trayecto_id: trayectoId
            },
            success: function(unidades) {
                ucurricularSelect.html('').prop('disabled', false);
                
                ucurricularSelect.append($('<option>', {
                    value: '',
                    text: 'Todas las Unidades'
                }));

                if (unidades) {
                    $.each(unidades, function(index, unidad) {
                        ucurricularSelect.append($('<option>', {
                            value: unidad.uc_id,
                            text: unidad.uc_nombre
                        }));
                    });
                }
                
                ucurricularSelect.trigger('change');
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición AJAX: ", status, error);
                ucurricularSelect.html('<option value="">Error al cargar unidades</option>');
            }
        });
    });
});