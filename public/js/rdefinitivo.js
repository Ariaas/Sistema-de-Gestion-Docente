document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.querySelector("button[name='generar_definitivo_emit']");
    const docenteSelect = document.getElementById("docente_id");
   
    if (generarBtn) {
        generarBtn.addEventListener("click", function() {
        
            setTimeout(function() {
                if (docenteSelect) {
                    docenteSelect.value = ""; 
                }
            }, 1800);
        });
    }
});