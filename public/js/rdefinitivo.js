// public/js/rdefinitivoemit.js

document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_definitivo_emit_btn");
    const anioSelect = document.getElementById("anio_def");
    // const faseSelect = document.getElementById("fase_def"); // No necesitamos validar fase como obligatoria

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Validar solo el Año como obligatorio
            if (anioSelect && anioSelect.value === "") {
                event.preventDefault(); // Detener el envío del formulario
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año.',
                    confirmButtonColor: '#3085d6' // Opcional: color del botón
                });
                return;
            }

            // No es necesario resetear el formulario aquí porque se abre en target="_blank"
            // y el usuario podría querer generar otro reporte con ligeros cambios.
            // Si se desea limpiar, se puede hacer después de un timeout, pero es opcional.
            /*
            setTimeout(function() {
                if (anioSelect) anioSelect.value = "";
                if (faseSelect) faseSelect.value = ""; // Si también se quiere limpiar fase
            }, 2000);
            */
        });
    }
});