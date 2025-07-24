document.addEventListener('DOMContentLoaded', function () {
    $('#cedula_docente').select2({
        theme: "bootstrap-5"
    });

    const formulario = document.getElementById('fReporteHorDocente');
    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            const anioSelect = document.getElementById('anio_id');
            const faseSelect = document.getElementById('fase_id');
            const selectorDocente = document.getElementById('cedula_docente');

            if (!anioSelect || anioSelect.value === "" || !faseSelect || faseSelect.value === "" || !selectorDocente || selectorDocente.value === "") {
                evento.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor, seleccione un Año Académico, una Fase y un Docente para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    }
});