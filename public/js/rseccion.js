document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_seccion_btn");
    const seccionSelect = document.getElementById("seccion_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            if (seccionSelect && seccionSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione una Sección para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        });
    }
});