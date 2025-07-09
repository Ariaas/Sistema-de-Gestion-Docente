document.addEventListener('DOMContentLoaded', function () {
   
    const generarBtn = document.getElementById("generar_reporte_rod_btn");
    // Se actualiza el ID del campo a 'fase_id'
    const faseSelect = document.getElementById("fase_id"); 
    
    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Se asegura que el campo de fase y año esté seleccionado
            if (faseSelect && faseSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione una Fase y Año para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Limpia los campos después de un tiempo prudencial para que el formulario se envíe
            setTimeout(function() {
                if (faseSelect) faseSelect.value = "";
            }, 1800);
        });
    }
});