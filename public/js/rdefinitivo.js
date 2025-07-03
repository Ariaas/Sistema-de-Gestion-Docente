document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.querySelector("button[name='generar_definitivo_emit']");
    const docenteSelect = document.getElementById("docente_id");
    const seccionSelect = document.getElementById("seccion_id");
    const faseSelect = document.getElementById("fase");
   
    if (generarBtn) {
        generarBtn.addEventListener("click", function() {
        
            setTimeout(function() {
                if (docenteSelect) {
                    docenteSelect.value = ""; 
                }
                if (seccionSelect) {
                    seccionSelect.value = "";
                }
                if (faseSelect) {
                    faseSelect.value = "";
                }
            }, 1800);
        });
    }
});