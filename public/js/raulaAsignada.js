document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById("fReporteAsignacionAulas");
    const anioSelect = document.getElementById("ani_anio");

    if (form) {
        form.addEventListener("submit", function(event) {
           
            if (!anioSelect.value || anioSelect.value === "") {
                event.preventDefault(); 
                alert("Por favor, seleccione un año académico para generar el reporte.");
                anioSelect.focus();
            }
        });
    }
});