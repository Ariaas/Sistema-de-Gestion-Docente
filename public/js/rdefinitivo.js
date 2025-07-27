document.addEventListener('DOMContentLoaded', function () {
    

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

   
    const generarBtn = document.getElementById("generar_btn");
    const anioSelect = document.getElementById("anio_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            
           
            if (anioSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'error',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                });
            }
        });
    }
});