document.addEventListener('DOMContentLoaded', function () {
    
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

    const formulario = document.getElementById('fReporteAsignacionAulas');
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
        anioCompletoSelect.addEventListener('change', function() {
            toggleFaseSelect();
        });
        
        toggleFaseSelect();
    }

    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            const esIntensivo = esAnioIntensivo();

            
            if (!anioCompletoSelect || anioCompletoSelect.value === "") {
                evento.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año Académico.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            
            if (!esIntensivo && (!faseSelect || faseSelect.value === "")) {
                evento.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione una Fase para años regulares.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
        });
    }
});