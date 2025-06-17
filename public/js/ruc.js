// public/js/ruc.js

document.addEventListener('DOMContentLoaded', function () {
    const generarBtnUc = document.getElementById("generar_uc");
    const trayectoSelect = document.getElementById("trayecto");
    const ucurricularSelect = document.getElementById("ucurricular"); 

    function limpiaFormularioUnidadCurricular() {
        if (trayectoSelect) {
            trayectoSelect.value = ""; 
        }
        if (ucurricularSelect) { 
            ucurricularSelect.value = ""; 
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