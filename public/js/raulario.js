

document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_aulario_btn");
    const espacioSelect = document.getElementById("espacio_aul");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            if (espacioSelect && espacioSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Aula para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        });
    }
});