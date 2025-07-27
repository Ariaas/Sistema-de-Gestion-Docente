document.addEventListener('DOMContentLoaded', function () {
    
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#trayecto').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Trayecto" });
                $('#ucurricular').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Unidad" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

  
    const generarBtnUc = document.getElementById("generar_uc");
    const anioSelect = document.getElementById("anio_id");

    if (generarBtnUc) {
        generarBtnUc.addEventListener("click", function(event) {
            
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