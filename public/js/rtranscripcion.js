document.addEventListener('DOMContentLoaded', function () {
   
    const generarBtn = document.getElementById("generar_transcripcion_btn");
    const anioSelect = document.getElementById("anio_id");
    const faseSelect = document.getElementById("fase");
    
    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
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