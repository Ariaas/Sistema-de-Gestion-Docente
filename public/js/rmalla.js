document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_rmalla_btn");
    const mallaSelect = document.getElementById("malla_codigo");

    if (generarBtn && mallaSelect) {
       
        generarBtn.disabled = true;

       
        mallaSelect.addEventListener('change', function() {
           
            if (this.value && this.value !== "") {
                generarBtn.disabled = false;
            } else {
                generarBtn.disabled = true;
            }
        });

       
    }
});