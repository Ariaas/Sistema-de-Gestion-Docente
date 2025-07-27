document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_seccion_btn");
    const anioSelect = document.getElementById("anio_id");
    const faseSelect = document.getElementById("fase_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Validar que se haya seleccionado un año y una fase
            if (!anioSelect || anioSelect.value === "" || !faseSelect || faseSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año Académico y una Fase para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        });
    }
});