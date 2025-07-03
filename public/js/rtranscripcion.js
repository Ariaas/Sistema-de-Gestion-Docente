

document.addEventListener('DOMContentLoaded', function () {
   
    const generarBtn = document.getElementById("generar_asignacion_btn");
    const anioSelect = document.getElementById("anio_asig");
    

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


        });
    }
});