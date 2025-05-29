// public/js/rasignacionsecciones.js (new name for clarity, adapt if using rtranscripcion.js)

document.addEventListener('DOMContentLoaded', function () {
    // Use the updated button and select IDs from the view
    const generarBtn = document.getElementById("generar_asignacion_btn");
    const anioSelect = document.getElementById("anio_asig");
    // const faseSelect = document.getElementById("fase_asig"); // Fase is not mandatory

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            if (anioSelect && anioSelect.value === "") {
                event.preventDefault(); // Stop form submission
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Optional: Clear form after a delay if desired, though target="_blank"
            // means the current page remains.
            /*
            setTimeout(function() {
                if (anioSelect) anioSelect.value = "";
                if (faseSelect) faseSelect.value = "";
            }, 1800);
            */
        });
    }
});