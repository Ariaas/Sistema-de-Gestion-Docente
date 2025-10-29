document.addEventListener('DOMContentLoaded', function () {
    const formReporte = document.getElementById("fReporteSeccion");
    const anioCompletoSelect = document.getElementById("anio_completo");
    const faseSelect = document.getElementById("fase_id");
    const faseContainer = document.getElementById("fase_container");

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

    // Escuchar cambios en el select de año
    if (anioCompletoSelect) {
        anioCompletoSelect.addEventListener('change', toggleFaseSelect);
        // Ejecutar al cargar la página por si hay un valor preseleccionado
        toggleFaseSelect();
    }

    if (formReporte) {
        formReporte.addEventListener("submit", function(event) {
            const esIntensivo = esAnioIntensivo();
            
            // Validar año académico
            if (!anioCompletoSelect || anioCompletoSelect.value === "") {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Validar fase solo si NO es intensivo
            if (!esIntensivo && (!faseSelect || faseSelect.value === "")) {
                event.preventDefault(); 
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione una Fase para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // Si todo está bien, permitir el submit
            return true;
        });
    }
});