document.addEventListener('DOMContentLoaded', function () {
    $('#cedula_docente').select2({
        theme: "bootstrap-5"
    });

    const formulario = document.getElementById('fReporteHorDocente');
    const anioCompletoSelect = document.getElementById('anio_completo');
    const faseSelect = document.getElementById('fase_id');
    const faseContainer = document.getElementById('fase_container');
    const selectorDocente = document.getElementById('cedula_docente');

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

    // Función para cargar docentes filtrados por año
    function cargarDocentesPorAnio() {
        const anioCompleto = anioCompletoSelect.value;
        
        if (!anioCompleto) {
            return;
        }

        // Mostrar loading en el select de docentes
        if (selectorDocente) {
            const valorActual = selectorDocente.value;
            selectorDocente.disabled = true;
            
            // Realizar petición AJAX
            fetch('?pagina=rhordocente', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=obtener_docentes_por_anio&anio_completo=' + encodeURIComponent(anioCompleto)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar select2
                    $('#cedula_docente').empty();
                    
                    // Agregar opción por defecto
                    $('#cedula_docente').append(new Option('-- Seleccione --', '', true, true));
                    
                    // Agregar docentes filtrados
                    data.docentes.forEach(docente => {
                        const option = new Option(
                            docente.nombreCompleto + ' (C.I: ' + docente.doc_cedula + ')',
                            docente.doc_cedula,
                            false,
                            false
                        );
                        $('#cedula_docente').append(option);
                    });
                    
                    // Refrescar select2
                    $('#cedula_docente').trigger('change');
                    selectorDocente.disabled = false;
                } else {
                    console.error('Error al cargar docentes:', data.message);
                    selectorDocente.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error en la petición AJAX:', error);
                selectorDocente.disabled = false;
            });
        }
    }

    // Escuchar cambios en el select de año
    if (anioCompletoSelect) {
        anioCompletoSelect.addEventListener('change', function() {
            toggleFaseSelect();
            cargarDocentesPorAnio();
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

            // Validar docente
            if (!selectorDocente || selectorDocente.value === "") {
                evento.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Docente para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
        });
    }
});