

document.addEventListener('DOMContentLoaded', function () {

    const generarBtn = document.getElementById("generar_rhd_btn");
    const docenteSelect = document.getElementById("docente_rhd_id");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            if (docenteSelect && docenteSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Docente para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        });
    }
});