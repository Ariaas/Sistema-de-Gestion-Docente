document.addEventListener('DOMContentLoaded', function () {
    
    // Inicializa los menús desplegables con Select2
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_id').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione un Año"
                });
                $('#fase').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione una Fase"
                });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    // Lógica del botón para validar el formulario
    const generarBtn = document.getElementById("generar_btn");
    const anioSelect = document.getElementById("anio_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            
            // VALIDACIÓN: Comprueba si el campo de año está vacío
            if (anioSelect.value === "") {
                event.preventDefault(); // Detiene el envío del formulario
                Swal.fire({
                    icon: 'error',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                });
            }
        });
    }
});