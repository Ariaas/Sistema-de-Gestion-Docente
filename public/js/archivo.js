$(document).ready(function() {
    
    $('#btnSubir').click(function() {
        $('#modalArchivo').modal('show');
    });

    $('#modalArchivo').on('hidden.bs.modal', function () {
        $('#formArchivo')[0].reset();  // Limpia todos los campos del formulario
        $('#archivo').val('');    // Limpia específicamente el input de archivo
    });
   
    listarArchivos();

    $('#formArchivo').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '?pagina=archivo',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var res = JSON.parse(response);
                alert(res.mensaje);
                if (res.resultado === 'guardar') {
                    $('#modalArchivo').modal('hide');
                    listarArchivos();
                }
            }
        });
    });
});

function listarArchivos() {
    $.ajax({
        url: '?pagina=archivo',
        type: 'POST',
        data: { accion: 'listar' },
        success: function(response) {
            var res = JSON.parse(response);
            var html = '';
            
            res.datos.forEach(function(archivo) {
                // ► Enlace descargable con icono
                html += `
                <tr>
                    <td class="align-middle">
                        <a href="uploads/${archivo.nombre_guardado}" 
                           download="${archivo.nombre_guardado}"
                           class="text-decoration-none link-primary">
                            <i class="fas fa-file-download me-2"></i>
                            ${archivo.nombre_guardado}
                        </a>
                    </td>
                    <td class="text-center">
                       <button onclick="eliminarArchivo('${archivo.nombre_guardado}')" 
                        class="btn btn-sm btn-danger">
                      <i class="fas fa-trash me-1"></i> Eliminar
                     </button>         
                    </td>
                </tr>`;
            });
            $('#resultados').html(html);
        }
    });
}


function eliminarArchivo(nombreArchivo) {
    if (confirm('¿Eliminar este archivo permanentemente?')) {
        $.ajax({
            url: '?pagina=archivo',
            type: 'POST',
            data: {
                accion: 'eliminar',
                nombre_archivo: nombreArchivo
            },
            success: function(response) {
                var res = JSON.parse(response);
                alert(res.mensaje);
                if (res.resultado === 'eliminar') {
                    listarArchivos(); 
                }
            }
        });
    }
}

