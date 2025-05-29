// public/js/ruc.js

document.addEventListener('DOMContentLoaded', function () {
    const generarBtnUc = document.getElementById("generar_uc");
    const trayectoSelect = document.getElementById("trayecto");
    const seccionSelect = document.getElementById("seccion"); // Nuevo: para el desplegable de sección

    function limpiaFormularioUnidadCurricular() {
        if (trayectoSelect) {
            trayectoSelect.value = ""; // Resetea al valor de "-- Todos los Trayectos --"
        }
        if (seccionSelect) { // Nuevo: limpiar el desplegable de sección
            seccionSelect.value = ""; // Resetea al valor de "-- Todas las Secciones --"
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