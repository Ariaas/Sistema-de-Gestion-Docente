document.addEventListener('DOMContentLoaded', function () {
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_academico').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    const formulario = document.getElementById("fReporteProsecucion");
    const anioSelect = document.getElementById("anio_academico");

    if (formulario) {
        formulario.addEventListener('submit', function(evento) {
            
            if (!anioSelect || anioSelect.value === "") {
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
