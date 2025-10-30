document.addEventListener('DOMContentLoaded', function () {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#fase_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
                $('#espacio_id').select2({ 
                    theme: "bootstrap-5", 
                    placeholder: "Todas las Aulas",
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return "No se encontraron resultados";
                        },
                        searching: function() {
                            return "Buscando...";
                        }
                    }
                });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    const formulario = document.getElementById("fReporteAulario");
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase_id");
    const faseContainer = document.getElementById("fase_container");
    const espacioSelect = document.getElementById("espacio_id");

    
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

    
    function cargarEspaciosPorAnio() {
        const anioCompleto = anioCompletoSelect.value;
        
        if (!anioCompleto) {
            return;
        }

        
        if (espacioSelect) {
            
            $(espacioSelect).prop('disabled', true);
            
            
            fetch('?pagina=raulario', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=obtener_espacios_por_anio&anio_completo=' + encodeURIComponent(anioCompleto)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    $(espacioSelect).empty();
                    
                    
                    $(espacioSelect).append(new Option('-- Todas las Aulas --', '', true, true));
                    
                    
                    data.espacios.forEach(espacio => {
                        const optionText = espacio.esp_codigo + ' (' + espacio.esp_tipo + ')';
                        $(espacioSelect).append(new Option(optionText, espacio.esp_codigo, false, false));
                    });
                    
                    
                    $(espacioSelect).trigger('change');
                    $(espacioSelect).prop('disabled', false);
                } else {
                    console.error('Error al cargar espacios:', data.message);
                    $(espacioSelect).prop('disabled', false);
                }
            })
            .catch(error => {
                console.error('Error en la petición AJAX:', error);
                $(espacioSelect).prop('disabled', false);
            });
        }
    }

    
    if (anioCompletoSelect) {
        // Usar evento Select2 de jQuery en lugar de addEventListener nativo
        if (window.jQuery) {
            $('#anio_completo').on('change', function() {
                toggleFaseSelect();
                cargarEspaciosPorAnio();
            });
        } else {
            anioCompletoSelect.addEventListener('change', function() {
                toggleFaseSelect();
                cargarEspaciosPorAnio();
            });
        }
        
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