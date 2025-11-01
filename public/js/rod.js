document.addEventListener('DOMContentLoaded', function () {
    
    const formulario = document.getElementById('fReporteRod');
    const anioCompletoSelect = document.getElementById('anio_completo');
    const faseSelect = document.getElementById('fase_id');
    const faseContainer = document.getElementById('fase_container');
    const generarBtn = document.getElementById("generar_reporte_rod_btn");
    
    
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
                    
                    if (window.jQuery && $('#fase_id').data('select2')) {
                        $('#fase_id').val('').trigger('change');
                    }
                }
            } else {
                faseContainer.style.display = 'block';
                
                if (faseSelect) {
                    faseSelect.setAttribute('required', 'required');
                }
            }
        }
    }

    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5" });
                $('#fase_id').select2({ theme: "bootstrap-5" });
                
                toggleFaseSelect();
            } catch (e) {
            }
        });
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