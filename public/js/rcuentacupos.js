document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar el formulario del reporte
    const form = document.getElementById('fReporteCuentaCupos');

    if (form) {
        form.addEventListener('submit', function(event) {
            // Obtener el valor del campo de selección del año
            const anioId = document.getElementById('anio').value;

            // Si no se ha seleccionado un año (el valor está vacío)
            if (anioId === '') {
                // Prevenir que el formulario se envíe
                event.preventDefault();
                
                // Mostrar una alerta al usuario
                alert('Por favor, seleccione un Año Académico para continuar.');
            }
            // Si se seleccionó un año, el formulario se enviará normalmente.
        });
    }
});