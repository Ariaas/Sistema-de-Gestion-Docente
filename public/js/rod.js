document.addEventListener('DOMContentLoaded', function () {
   
    // Se cambia el ID del botón para que coincida con el nuevo formulario
    const generarBtn = document.getElementById("generar_reporte_rod_btn"); 
    const anioSelect = document.getElementById("anio_id");
    const faseSelect = document.getElementById("fase");
    
    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Se asegura que el campo de año esté seleccionado
            if (anioSelect && anioSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

        
            setTimeout(function() {
                if (anioSelect) anioSelect.value = "";
                if (faseSelect) faseSelect.value = "";
            }, 1800);
        });
    }
});