document.addEventListener('DOMContentLoaded', function () {
   
    const generarBtn = document.getElementById("generar_reporte_rod_btn");
    const faseSelect = document.getElementById("fase_id"); 
    
    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
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

            setTimeout(function() {
                if (faseSelect) faseSelect.value = "";
            }, 1800);
        });
    }
});