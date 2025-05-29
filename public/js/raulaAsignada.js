// public/js/rasignacionaulas.js

document.addEventListener('DOMContentLoaded', function () {
    const generarBtn = document.getElementById("generar_asignacion_aulas_btn");

    if (generarBtn) {
        generarBtn.addEventListener("click", function(event) {
            // Opcional: Mostrar una confirmación o un mensaje de "Generando..."
            // Por ejemplo, con SweetAlert2:
            // Swal.fire({
            //   title: 'Generando Reporte',
            //   text: 'Por favor espere...',
            //   allowOutsideClick: false,
            //   didOpen: () => {
            //     Swal.showLoading()
            //   }
            // });
            // El formulario se enviará y el PDF se abrirá en una nueva pestaña.
        });
    }
});