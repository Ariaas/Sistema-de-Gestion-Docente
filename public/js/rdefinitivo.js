document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.querySelector("button[name='generar_definitivo_emit']");
    const docenteSelect = document.getElementById("doc_cedula");
    const seccionSelect = document.getElementById("sec_codigo");
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