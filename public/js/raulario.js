document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.getElementById("fReporteAulario");
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase_id");
    const faseContainer = document.getElementById("fase_container");
    const espacioSelect = document.getElementById("espacio_id");

    // Inicializar Select2 para el select de aulas
    if (espacioSelect) {
        $(espacioSelect).select2({
            theme: 'bootstrap-5',
            placeholder: '-- Todas las Aulas --',
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
    }

    // Función para verificar si el año seleccionado es intensivo
    function esAnioIntensivo() {
        if (!anioCompletoSelect || anioCompletoSelect.value === "") {
            return false;
        }
        const partes = anioCompletoSelect.value.split('|');
        const tipoAnio = partes[1] ? partes[1].toLowerCase() : '';
        return tipoAnio === 'intensivo';
    }

    // Función para mostrar/ocultar el select de fase
    function toggleFaseSelect() {
        const esIntensivo = esAnioIntensivo();
        
        if (faseContainer) {
            if (esIntensivo) {
                // Ocultar el contenedor de fase
                faseContainer.style.display = 'none';
                // Remover el atributo required
                if (faseSelect) {
                    faseSelect.removeAttribute('required');
                    faseSelect.value = ''; // Limpiar el valor
                }
            } else {
                // Mostrar el contenedor de fase
                faseContainer.style.display = 'block';
                // Agregar el atributo required
                if (faseSelect) {
                    faseSelect.setAttribute('required', 'required');
                }
            }
        }
    }

    // Función para cargar espacios filtrados por año
    function cargarEspaciosPorAnio() {
        const anioCompleto = anioCompletoSelect.value;
        
        if (!anioCompleto) {
            return;
        }

        // Mostrar loading en el select de espacios
        if (espacioSelect) {
            // Deshabilitar Select2
            $(espacioSelect).prop('disabled', true);
            
            // Realizar petición AJAX
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
                    // Limpiar select
                    $(espacioSelect).empty();
                    
                    // Agregar opción por defecto
                    $(espacioSelect).append(new Option('-- Todas las Aulas --', '', true, true));
                    
                    // Agregar espacios filtrados
                    data.espacios.forEach(espacio => {
                        const optionText = espacio.esp_codigo + ' (' + espacio.esp_tipo + ')';
                        $(espacioSelect).append(new Option(optionText, espacio.esp_codigo, false, false));
                    });
                    
                    // Actualizar Select2 y habilitar
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

    // Escuchar cambios en el select de año
    if (anioCompletoSelect) {
        anioCompletoSelect.addEventListener('change', function() {
            toggleFaseSelect();
            cargarEspaciosPorAnio();
        });
        // Ejecutar al cargar la página por si hay un valor preseleccionado
        toggleFaseSelect();
    }

    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            const esIntensivo = esAnioIntensivo();

            // Validar año académico
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

            // Validar fase solo si NO es intensivo
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