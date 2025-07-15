document.addEventListener('DOMContentLoaded', function () {
    // Activa el buscador en el menú desplegable
    $('#cedula_docente').select2({
        theme: "bootstrap-5"
    });

    // Validación del formulario
    const formulario = document.querySelector('form');
    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            const selectorDocente = document.getElementById('cedula_docente');
            if (!selectorDocente || selectorDocente.value === "") {
                evento.preventDefault(); // Evita que el formulario se envíe
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un docente para generar el reporte.',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    }
});