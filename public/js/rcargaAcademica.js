document.addEventListener('DOMContentLoaded', function () {
    
  
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_id').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione un Año"
                });
                $('#trayecto').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione un Trayecto"
                });
                $('#seccion').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione una Sección"
                });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }


    const generarBtnUc = document.getElementById("generar_uc");
    const anioSelect = document.getElementById("anio_id");
    const trayectoSelect = document.getElementById("trayecto");
    const seccionSelect = document.getElementById("seccion"); 

    function limpiaFormulario() {
         
        if (anioSelect) {
            anioSelect.value = ""; 
            if (window.jQuery) $(anioSelect).trigger('change'); 
        }
        if (trayectoSelect) {
            trayectoSelect.value = ""; 
            if (window.jQuery) $(trayectoSelect).trigger('change');
        }
        if (seccionSelect) { 
            seccionSelect.value = ""; 
            if (window.jQuery) $(seccionSelect).trigger('change');
        }
    }

    if (generarBtnUc) {
        generarBtnUc.addEventListener("click", function(event) {
            
            if (anioSelect.value === "") {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                });
                return;
            }

           
            setTimeout(function() {
                limpiaFormulario();
            }, 1800); 
        });
    }
});