document.addEventListener('DOMContentLoaded', function () {
   
    const generarBtn = document.getElementById("generar_reporte_rod_btn");
    // Se actualiza el ID del campo a validar
    const anioSelect = document.getElementById("anio_id"); 
    
    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Se asegura que el campo de año esté seleccionado
            if (anioSelect && anioSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Limpia el campo después de un tiempo para que el formulario se envíe
            setTimeout(function() {
                if (anioSelect) anioSelect.value = "";
            }, 1800);
        });
    }
});