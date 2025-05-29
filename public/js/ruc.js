// public/js/ruc.js

document.addEventListener('DOMContentLoaded', function () {
    const generarBtnUc = document.getElementById("generar_uc");
    const trayectoSelect = document.getElementById("trayecto");
    const ucurricularSelect = document.getElementById("ucurricular"); // ID del select de UC

    function limpiaFormularioUnidadCurricular() {
        if (trayectoSelect) {
            trayectoSelect.value = ""; // Resetea a la opción por defecto
        }
        if (ucurricularSelect) { // Limpiar el desplegable de Unidad Curricular
            ucurricularSelect.value = ""; // Resetea a la opción por defecto
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