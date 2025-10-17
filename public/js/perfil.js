let estadoInicialPerfil = null;

function Listar() {
    var datos = new FormData();
    datos.append('accion', 'consultar');
    enviaAjax(datos);
}

function obtenerEstadoActualPerfil() {
    return {
        nombre: $('#nombreUsuario').val(),
        correo: $('#correoUsuario').val(),
        contrasenia: $('#contraseniaPerfil').val(),
        foto: $('#fotoPerfil').attr('src')
    };
}

function verificarCambiosPerfil() {
    if (!estadoInicialPerfil) return;
    const actual = obtenerEstadoActualPerfil();
    let haCambiado = actual.nombre !== estadoInicialPerfil.nombre ||
        actual.correo !== estadoInicialPerfil.correo ||
        (actual.contrasenia.length > 0) ||
        actual.foto !== estadoInicialPerfil.foto;
    $('#formPerfil button[type="submit"], #formPerfil #btnGuardarPerfil, #formPerfil .btn-primary').prop('disabled', !haCambiado);
}

function validarPerfil() {
    const correo = $('#correoUsuario').val().trim();
    const regexCorreo = /^[a-zA-Z0-9._-]{5,30}@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (correo === '') {
        muestraMensaje('error', 2000, 'ERROR', 'El correo no puede estar vacío');
        return false;
    }
    if (!regexCorreo.test(correo)) {
        muestraMensaje('error', 2000, 'ERROR', 'Formato de correo incorrecto. Ej: usuario@dominio.com');
        return false;
    }
    if ($('#correoUsuario').hasClass('is-invalid')) {
        muestraMensaje('error', 2000, 'ERROR', 'Debe ingresar un correo válido y no repetido');
        return false;
    }
    return true;
}

$(document).ready(function () {
    Listar();

    $('.perfil-foto-label, #fotoPerfil').on('click', function (e) {
        e.preventDefault();
        $('#fotoPerfilInput').click();
    });

    $('#formPerfil').on('submit', function (e) {
        e.preventDefault();
        if (validarPerfil()) {
            var datos = new FormData();
            datos.append('accion', 'modificar');
            datos.append('correoUsuario', $('#correoUsuario').val());

            if ($('#contraseniaPerfil').val().length > 0) {
                datos.append('contraseniaUsuario', $('#contraseniaPerfil').val());
            }

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

    $('#formPerfil input, #formPerfil textarea').on('input change', function () {
        verificarCambiosPerfil();
    });
    $('#fotoPerfilInput').on('change', function () {
        setTimeout(verificarCambiosPerfil, 200); 
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

    $('#correoUsuario').on('keyup change', function () {
        const correo = $(this).val();
        const regexCorreo = /^[a-zA-Z0-9._-]{5,30}@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        $('#correoUsuario').removeClass('is-invalid');
        $('#correoUsuario').next('.invalid-feedback').remove();
        if (correo.trim() === '') {
            return;
        }
        if (!regexCorreo.test(correo)) {
            $('#correoUsuario').addClass('is-invalid');
            $('#correoUsuario').after('<div class="invalid-feedback d-block">Formato incorrecto. Ej: usuario@dominio.com</div>');
            return;
        }
        const datos = new FormData();
        datos.append('accion', 'existe_correo_perfil');
        datos.append('correoUsuario', correo);
        $.ajax({
            url: '',
            type: 'POST',
            contentType: false,
            data: datos,
            processData: false,
            cache: false,
            success: function (respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    $('#correoUsuario').removeClass('is-invalid');
                    $('#correoUsuario').next('.invalid-feedback').remove();
                    if (lee.resultado === 'existe_usuario' || lee.resultado === 'existe_docente') {
                        $('#correoUsuario').addClass('is-invalid');
                        $('#correoUsuario').after('<div class="invalid-feedback d-block">' + lee.mensaje + '</div>');
                    }
                } catch (e) { }
            }
        });
    });

    $("#contraseniaPerfil").on("keyup keydown", function () {
        $("#scontraseniaPerfil").css("color", "");
        if ($(this).val().length > 0) {
            validarkeyup(
                /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]).{8,30}$/,
                $(this),
                $("#scontraseniaPerfil"),
                "Debe tener entre 8-30 caracteres, al menos una mayúscula y un carácter especial."
            );
        } else {
            $("#scontraseniaPerfil").text("");
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
                    estadoInicialPerfil = {
                        nombre: lee.mensaje.usu_nombre,
                        correo: lee.mensaje.usu_correo,
                        contrasenia: '',
                        foto: $('#fotoPerfil').attr('src')
                    };
                    verificarCambiosPerfil();
                } else if (lee.resultado === 'modificar') {
                    muestraMensaje('info', 4000, 'PERFIL', lee.mensaje);
                    Listar();
                    let nuevaFoto = $('#fotoPerfil').attr('src');
                    $('.navbar-nav img[alt="Foto de perfil"]').attr('src', nuevaFoto);
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


