document.addEventListener('DOMContentLoaded', function() {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#fase_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
            } catch (e) {
            }
        });
    }
   
    const form = document.getElementById('fReporteCuentaCupos');
    const anioCompletoSelect = document.getElementById('anio_completo');
    const faseSelect = document.getElementById('fase_id');
    const faseContainer = document.getElementById('fase_container');

    
    function esAnioIntensivo() {
        if (!anioCompletoSelect || anioCompletoSelect.value === "") {
            return false;
        }
        const partes = anioCompletoSelect.value.split('|');
        const tipoAnio = partes[1] ? partes[1].toLowerCase() : '';
        return tipoAnio === 'intensivo';
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
            anioCompletoSelect.addEventListener('change', function() {
                toggleFaseSelect();
            });
        }
        
        toggleFaseSelect();
    }

   
    if (form) {
        form.addEventListener('submit', function(event) {
            const esIntensivo = esAnioIntensivo();

          
            if (!anioCompletoSelect || anioCompletoSelect.value === "") {
                event.preventDefault();
                alert('Por favor, seleccione un Año Académico para continuar.');
                return false;
            }

           
            if (!esIntensivo && (!faseSelect || faseSelect.value === "")) {
                event.preventDefault();
                alert('Por favor, seleccione una Fase para años regulares.');
                return false;
            }
        });
    }
});