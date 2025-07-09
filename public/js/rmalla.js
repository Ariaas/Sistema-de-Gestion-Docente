document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_rmalla_btn");
    const mallaSelect = document.getElementById("malla_codigo");

    if (generarBtn && mallaSelect) {
        // Deshabilitar el botón inicialmente
        generarBtn.disabled = true;

        // Escuchar cambios en el select
        mallaSelect.addEventListener('change', function() {
            // Habilitar el botón solo si se ha seleccionado una opción válida
            if (this.value && this.value !== "") {
                generarBtn.disabled = false;
            } else {
                generarBtn.disabled = true;
            }
        });

        // La lógica anterior del click ya no es necesaria, 
        // el formulario se encarga de la acción.
    }
});