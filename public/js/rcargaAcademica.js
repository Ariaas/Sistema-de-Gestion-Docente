document.addEventListener('DOMContentLoaded', function () {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#fase_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
                $('#trayecto').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Trayecto" });
            } catch (e) {
            }
        });
    }

    const formReporte = document.getElementById("fReporteUnidadCurricular");
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase_id");
    const faseContainer = document.getElementById("fase_container");

    
    function esAnioIntensivo() {
        if (!anioCompletoSelect || anioCompletoSelect.value === "") {
            return false;
        }
        const partes = anioCompletoSelect.value.split('|');
        const tipoAnio = partes[1] ? partes[1].toLowerCase().trim() : '';
        
        return tipoAnio.includes('intensivo');
    }

    
    function toggleFaseSelect() {
        const esIntensivo = esAnioIntensivo();
        
        if (faseContainer) {
            if (esIntensivo) {
                
                faseContainer.style.display = 'none';
                
                if (faseSelect) {
                    faseSelect.removeAttribute('required');
                    faseSelect.value = ''; 
                }
            } else {
               
                faseContainer.style.display = 'block';
                
                if (faseSelect) {
                    faseSelect.setAttribute('required', 'required');
                }
            }
        }
    }

    
    if (anioCompletoSelect) {
        if (window.jQuery) {
            $('#anio_completo').on('change', function() {
                toggleFaseSelect();
            });
        } else {
            anioCompletoSelect.addEventListener('change', toggleFaseSelect);
        }
        
        toggleFaseSelect();
    }

    if (formReporte) {
        formReporte.addEventListener("submit", function(event) {
            const esIntensivo = esAnioIntensivo();
            
            
            if (!anioCompletoSelect || anioCompletoSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            
            if (!esIntensivo && (!faseSelect || faseSelect.value === "")) {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione una Fase para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

      
            return true;
        });
    }
});