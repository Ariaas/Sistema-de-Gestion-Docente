document.addEventListener('DOMContentLoaded', function () {
    
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase");
    const faseContainer = document.getElementById("fase_container");
    const formulario = document.getElementById("fReporteDefinitivoEmit");

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
                    if (window.jQuery && $.fn.select2) {
                        $('#fase').val("").trigger('change');
                    }
                }
            } else {
                // Mostrar el contenedor de fase
                faseContainer.style.display = 'block';
                // No agregar required, ya que "Todas las Fases" es válido
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

                // Escuchar cambios en el select de año usando Select2
                $('#anio_completo').on('change', function() {
                    toggleFaseSelect();
                });

                // Ejecutar al cargar la página por si hay un valor preseleccionado
                toggleFaseSelect();
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    // Validación al enviar el formulario
    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            // Validar año académico
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