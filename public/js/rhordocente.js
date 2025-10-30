document.addEventListener('DOMContentLoaded', function () {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_completo').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#fase_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
                $('#cedula_docente').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Docente" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    const formulario = document.getElementById('fReporteHorDocente');
    const anioCompletoSelect = document.getElementById('anio_completo');
    const faseSelect = document.getElementById('fase_id');
    const faseContainer = document.getElementById('fase_container');
    const selectorDocente = document.getElementById('cedula_docente');

    
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

    
    function cargarDocentesPorAnio() {
        const anioCompleto = anioCompletoSelect.value;
        
        if (!anioCompleto) {
            return;
        }

        
        if (selectorDocente) {
            const valorActual = selectorDocente.value;
            selectorDocente.disabled = true;
            
           
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
                    
                    $('#cedula_docente').empty();
                    
                    
                    $('#cedula_docente').append(new Option('-- Seleccione --', '', true, true));
                    
                    
                    data.docentes.forEach(docente => {
                        const option = new Option(
                            docente.nombreCompleto + ' (C.I: ' + docente.doc_cedula + ')',
                            docente.doc_cedula,
                            false,
                            false
                        );
                        $('#cedula_docente').append(option);
                    });
                    
                    
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

    
    if (anioCompletoSelect) {
        // Usar evento Select2 de jQuery en lugar de addEventListener nativo
        if (window.jQuery) {
            $('#anio_completo').on('change', function() {
                toggleFaseSelect();
                cargarDocentesPorAnio();
            });
        } else {
            anioCompletoSelect.addEventListener('change', function() {
                toggleFaseSelect();
                cargarDocentesPorAnio();
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