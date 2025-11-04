document.addEventListener('DOMContentLoaded', function () {
    
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase");
    const faseContainer = document.getElementById("fase_container");
    const formulario = document.getElementById("fReporteDefinitivoEmit");

   
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
                    if (window.jQuery && $.fn.select2) {
                        $('#fase').val("").trigger('change');
                    }
                }
            } else {
                
                faseContainer.style.display = 'block';
                
            }
        }
    }

    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione un Año"
                });
                $('#fase').select2({ 
                    theme: "bootstrap-5",
                    placeholder: "Seleccione una Fase"
                });

                
                $('#anio_completo').on('change', function() {
                    toggleFaseSelect();
                });

                
                toggleFaseSelect();
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    
    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            
            if (!anioCompletoSelect || anioCompletoSelect.value === "") {
                evento.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
        });
    }
});