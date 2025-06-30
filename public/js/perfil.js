function Listar() {
    var datos = new FormData();
    datos.append('accion', 'consultar');
    enviaAjax(datos);
}

function validarPerfil() {
    if ($('#correoUsuario').val().trim() === '') {
        muestraMensaje('error', 2000, 'ERROR', 'El correo no puede estar vacío');
        return false;
    }
    return true;
}

$(document).ready(function () {
    Listar();

    $('#formPerfil').on('submit', function (e) {
        e.preventDefault();
        if (validarPerfil()) {
            var datos = new FormData();
            datos.append('accion', 'modificar');
            datos.append('correoUsuario', $('#correoUsuario').val());

            if ($('#fotoPerfilInput')[0].files.length > 0) {
                let file = $('#fotoPerfilInput')[0].files[0];
                let reader = new FileReader();
                reader.onload = function (e) {
                    datos.append('fotoPerfil', e.target.result);
                    enviaAjax(datos);
                };
                reader.readAsDataURL(file);
            } else {
                datos.append('fotoPerfil', $('#fotoPerfil').attr('src'));
                enviaAjax(datos);
            }
        }
    });

    $('#fotoPerfilInput').on('change', function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $('#fotoPerfil').attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});

function enviaAjax(datos) {
    $.ajax({
        async: true,
        url: '',
        type: 'POST',
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === 'consultar') {
                    $('#nombreUsuario').val(lee.mensaje.usu_nombre);
                    $('#correoUsuario').val(lee.mensaje.usu_correo);
                    let foto = lee.mensaje.usu_foto &&
                        (lee.mensaje.usu_foto.startsWith('public/assets/profile/') || lee.mensaje.usu_foto.startsWith('public/assets/icons/'))
                        ? lee.mensaje.usu_foto
                        : 'public/assets/icons/user-circle.svg';
                    $('#fotoPerfil').attr('src', foto + '?v=' + new Date().getTime());
                } else if (lee.resultado === 'modificar') {
                    muestraMensaje('info', 4000, 'PERFIL', lee.mensaje);
                    Listar();
                } else if (lee.resultado === 'error') {
                    muestraMensaje('error', 4000, 'ERROR', lee.mensaje);
                }
            } catch (e) {
                alert('Error en JSON: ' + e.message);
            }
        },
        error: function (request, status, err) {
            muestraMensaje('error', 4000, 'ERROR', 'Error de conexión');
        }
    });
}


