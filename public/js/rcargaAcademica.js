

document.addEventListener('DOMContentLoaded', function () {
    const generarBtnUc = document.getElementById("generar_uc");
    const trayectoSelect = document.getElementById("trayecto");
    const seccionSelect = document.getElementById("seccion"); 

    function limpiaFormularioUnidadCurricular() {
        if (trayectoSelect) {
            trayectoSelect.value = ""; 
        }
        if (seccionSelect) { 
            seccionSelect.value = ""; 
        }
    }

    if (generarBtnUc) {
        generarBtnUc.addEventListener("click", function() {
            setTimeout(function() {
                limpiaFormularioUnidadCurricular();
            }, 1800); 
        });
    }
});