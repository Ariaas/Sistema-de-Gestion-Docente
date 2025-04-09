document.addEventListener("DOMContentLoaded", function () {
    // Inicializar DataTables con estilos de Bootstrap
    $('#tabla-docentes').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" // Traducción al español
        },
        responsive: true, // Hace la tabla responsiva
        pageLength: 10, // Número de filas por página
        lengthMenu: [5, 10, 25, 50, 100], // Opciones de filas por página
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' + // Personaliza el diseño
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        classes: {
            sWrapper: "dataTables_wrapper dt-bootstrap5",
        }
    });
});


