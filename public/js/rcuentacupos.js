document.addEventListener('DOMContentLoaded', function() {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#fase_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
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
        console.log('Tipo de año detectado:', tipoAnio, '- Es intensivo:', tipoAnio === 'intensivo');
        return tipoAnio === 'intensivo';
    }

    
    function toggleFaseSelect() {
        const esIntensivo = esAnioIntensivo();
        
        console.log('Toggle fase - Es intensivo:', esIntensivo);
        
        if (faseContainer) {
            if (esIntensivo) {
                
                console.log('Ocultando contenedor de fase');
                faseContainer.style.display = 'none';
                
                if (faseSelect) {
                    faseSelect.removeAttribute('required');
                    faseSelect.value = ''; 
                }
            } else {
                
                console.log('Mostrando contenedor de fase');
                faseContainer.style.display = 'block';
                
                if (faseSelect) {
                    faseSelect.setAttribute('required', 'required');
                }
            }
        } else {
            console.error('No se encontró el contenedor de fase');
        }
    }

    
    if (anioCompletoSelect) {
        // Usar evento Select2 de jQuery en lugar de addEventListener nativo
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