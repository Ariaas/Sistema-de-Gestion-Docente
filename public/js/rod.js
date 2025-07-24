document.addEventListener('DOMContentLoaded', function () {
    
    // Inicializa los menús desplegables con Select2
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_id').select2({ theme: "bootstrap-5" });
                $('#fase_id').select2({ theme: "bootstrap-5" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    // Lógica del botón para validar el formulario
    const generarBtn = document.getElementById("generar_reporte_rod_btn");
    const anioSelect = document.getElementById("anio_id");
    const faseSelect = document.getElementById("fase_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            
            // VALIDACIÓN: Comprueba que ambos campos estén seleccionados
            if (anioSelect.value === "" || faseSelect.value === "") {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año y una Fase para generar el reporte.',
                });
            }
        });
    }
});