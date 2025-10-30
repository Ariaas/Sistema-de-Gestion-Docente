document.addEventListener('DOMContentLoaded', function () {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#malla_codigo').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Malla" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    const generarBtn = document.getElementById("generar_rmalla_btn");
    const mallaSelect = document.getElementById("malla_codigo");

    if (generarBtn && mallaSelect) {
        generarBtn.disabled = true;

        
        if (window.jQuery) {
            $('#malla_codigo').on('change', function() {
                if (this.value && this.value !== "") {
                    generarBtn.disabled = false;
                } else {
                    generarBtn.disabled = true;
                }
            });
        } else {
            mallaSelect.addEventListener('change', function() {
                if (this.value && this.value !== "") {
                    generarBtn.disabled = false;
                } else {
                    generarBtn.disabled = true;
                }
            });
        }
    }
});