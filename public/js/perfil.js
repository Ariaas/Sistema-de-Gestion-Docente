let estadoInicialPerfil = null;
let estadoInicialDocente = null;
let catalogosDocente = { titulos: [], coordinaciones: [] };
let docenteFormularioHabilitado = false;

function Listar() {
    const datos = new FormData();
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
    if (!estadoInicialPerfil) {
        $('#formPerfil button[type="submit"], #formPerfil #btnGuardarPerfil, #formPerfil .btn-primary').prop('disabled', true);
        return;
    }
    const actual = obtenerEstadoActualPerfil();
    const haCambiado = actual.nombre !== estadoInicialPerfil.nombre ||
        actual.correo !== estadoInicialPerfil.correo ||
        actual.foto !== estadoInicialPerfil.foto ||
        actual.contrasenia.length > 0;
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

function actualizarResumenSelect($select, $container) {
    const seleccionados = $select.find('option:selected');
    $container.empty();
    if (!seleccionados.length) {
        $container.append('<span class="text-muted">Sin elementos seleccionados</span>');
        return;
    }
    seleccionados.each(function () {
        const texto = $(this).text();
        $container.append('<span>' + texto + '</span>');
    });
}

function establecerCatalogosDocente(catalogos, seleccionTitulos = [], seleccionCoordinaciones = []) {
    if (!catalogos) {
        return;
    }
    catalogosDocente = catalogos;
    const $titulos = $('#docenteTitulos');
    const $coordinaciones = $('#docenteCoordinaciones');

    $titulos.empty();
    (catalogosDocente.titulos || []).forEach(item => {
        $titulos.append(new Option(item.texto, item.valor, false, false));
    });

    $coordinaciones.empty();
    (catalogosDocente.coordinaciones || []).forEach(item => {
        $coordinaciones.append(new Option(item.texto, item.valor, false, false));
    });

    if (seleccionTitulos.length) {
        $titulos.val(seleccionTitulos).trigger('change.select2');
    } else {
        $titulos.val(null).trigger('change.select2');
    }

    if (seleccionCoordinaciones.length) {
        $coordinaciones.val(seleccionCoordinaciones).trigger('change.select2');
    } else {
        $coordinaciones.val(null).trigger('change.select2');
    }

    actualizarResumenSelect($titulos, $('#docenteTitulosResumen'));
    actualizarResumenSelect($coordinaciones, $('#docenteCoordinacionesResumen'));
}

function habilitarFormularioDocente() {
    docenteFormularioHabilitado = true;
    $('#perfilAccesoCol').removeClass('mx-auto');
    $('#perfilDocenteWrapper').removeClass('d-none');
    $('#formPerfilDocente :input').prop('disabled', true);
    $('#docenteCedula, #docenteNombre, #docenteApellido, #docenteCategoria').prop('readOnly', true);
    $('#docenteTipoConcurso').prop('readOnly', true);
    $('#docenteCorreo').prop('disabled', false);
    $('#docenteTitulos').prop('disabled', false);
    $('#docenteCoordinaciones').prop('disabled', false);
    $('#btnGuardarDocente').prop('disabled', true);
    $('#docenteTitulos').trigger('change.select2');
    $('#docenteCoordinaciones').trigger('change.select2');
}

function deshabilitarFormularioDocente() {
    docenteFormularioHabilitado = false;
    estadoInicialDocente = null;
    $('#perfilAccesoCol').addClass('mx-auto');
    $('#perfilDocenteWrapper').addClass('d-none');
    $('#formPerfilDocente :input').prop('disabled', true);
    $('#docenteTitulos').val(null).trigger('change.select2');
    $('#docenteCoordinaciones').val(null).trigger('change.select2');
    $('#docenteTitulosResumen').html('<span class="text-muted">Sin datos disponibles</span>');
    $('#docenteCoordinacionesResumen').html('<span class="text-muted">Sin datos disponibles</span>');
    $('#docenteObservacion').val('');
    $('#btnGuardarDocente').prop('disabled', true);
}

function poblarFormularioDocente(docente, seleccionTitulos = [], seleccionCoordinaciones = []) {
    if (!docente || !docente.datos) {
        deshabilitarFormularioDocente();
        return;
    }

    habilitarFormularioDocente();
    const datos = docente.datos;
    const cedulaFormateada = (datos.doc_prefijo ? datos.doc_prefijo + '-' : '') + (datos.doc_cedula || '');

    $('#docenteCedula').val(cedulaFormateada);
    $('#docenteNombre').val(datos.doc_nombre || '');
    $('#docenteApellido').val(datos.doc_apellido || '');
    $('#docenteCategoria').val(datos.cat_nombre || '');
    $('#docenteCorreo').val(datos.doc_correo || '');
    $('#docenteDedicacion').val(datos.doc_dedicacion || '');
    $('#docenteCondicion').val(datos.doc_condicion || '');
    $('#docenteTipoConcurso').val(datos.doc_tipo_concurso || '');
    $('#docenteAnioConcurso').val(datos.doc_anio_concurso || '');
    $('#docenteIngreso').val(datos.doc_ingreso || '');
    $('#docenteObservacion').val('');

    $('#docenteTitulos').val(seleccionTitulos.length ? seleccionTitulos : null).trigger('change.select2');
    $('#docenteCoordinaciones').val(seleccionCoordinaciones.length ? seleccionCoordinaciones : null).trigger('change.select2');

    actualizarResumenSelect($('#docenteTitulos'), $('#docenteTitulosResumen'));
    actualizarResumenSelect($('#docenteCoordinaciones'), $('#docenteCoordinacionesResumen'));

    actualizarTipoConcursoSegunCondicion(true);
    estadoInicialDocente = obtenerEstadoActualDocente();
    verificarCambiosDocente();
}

function obtenerEstadoActualDocente() {
    if (!docenteFormularioHabilitado) {
        return null;
    }
    const titulos = ($('#docenteTitulos').val() || []).slice().sort();
    const coordinaciones = ($('#docenteCoordinaciones').val() || []).slice().sort();
    return {
        correo: ($('#docenteCorreo').val() || '').trim(),
        titulos: titulos,
        coordinaciones: coordinaciones
    };
}

function verificarCambiosDocente() {
    if (!estadoInicialDocente) {
        $('#btnGuardarDocente').prop('disabled', true);
        return;
    }
    const actual = obtenerEstadoActualDocente();
    if (!actual) {
        $('#btnGuardarDocente').prop('disabled', true);
        return;
    }
    const haCambiado = actual.correo !== estadoInicialDocente.correo ||
        JSON.stringify(actual.titulos) !== JSON.stringify(estadoInicialDocente.titulos) ||
        JSON.stringify(actual.coordinaciones) !== JSON.stringify(estadoInicialDocente.coordinaciones);

    $('#btnGuardarDocente').prop('disabled', !haCambiado);
}

function actualizarTipoConcursoSegunCondicion(desdeCarga = false) {
    if (!docenteFormularioHabilitado) {
        return;
    }
    const condicion = $('#docenteCondicion').val();
    const $tipo = $('#docenteTipoConcurso');
    const $anio = $('#docenteAnioConcurso');

    if (condicion === 'Ordinario') {
        if (!desdeCarga || !$tipo.val()) {
            $tipo.val('Oposición');
        }
        $anio.prop('required', true);
    } else if (condicion === 'Contratado por Credenciales') {
        if (!desdeCarga || !$tipo.val()) {
            $tipo.val('Credenciales');
        }
        $anio.prop('required', true);
    } else if (condicion === 'Suplente') {
        if (!desdeCarga) {
            $tipo.val('');
        }
        $anio.prop('required', false);
    } else {
        if (!desdeCarga) {
            $tipo.val('');
        }
        $anio.prop('required', false);
    }
}

function validarPerfilDocente() {
    if (!docenteFormularioHabilitado) {
        muestraMensaje('error', 3000, 'DATOS DOCENTE', 'No hay datos de docente vinculados al usuario.');
        return false;
    }

    const correo = $('#docenteCorreo').val().trim();
    const titulosSeleccionados = $('#docenteTitulos').val() || [];
    const regexCorreo = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

    if (correo === '' || !regexCorreo.test(correo)) {
        muestraMensaje('error', 4000, 'DATOS DOCENTE', 'Ingrese un correo institucional válido.');
        return false;
    }
    if (!titulosSeleccionados.length) {
        muestraMensaje('error', 4000, 'DATOS DOCENTE', 'Debe seleccionar al menos un título académico.');
        return false;
    }
    return true;
}

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
                const lee = JSON.parse(respuesta);
                if (lee.resultado === 'consultar') {
                    $('#nombreUsuario').val(lee.mensaje.usu_nombre || '');
                    $('#correoUsuario').val(lee.mensaje.usu_correo || '');
                    const foto = lee.mensaje.usu_foto && (lee.mensaje.usu_foto.startsWith('public/assets/profile/') || lee.mensaje.usu_foto.startsWith('public/assets/icons/'))
                        ? lee.mensaje.usu_foto
                        : 'public/assets/icons/user-circle.svg';
                    $('#fotoPerfil').attr('src', foto + '?v=' + new Date().getTime());
                    estadoInicialPerfil = {
                        nombre: lee.mensaje.usu_nombre || '',
                        correo: lee.mensaje.usu_correo || '',
                        contrasenia: '',
                        foto: $('#fotoPerfil').attr('src')
                    };
                    verificarCambiosPerfil();

                    const esDocente = Boolean(lee.tiene_docente && lee.docente);
                    if (esDocente) {
                        const titulosSeleccion = lee.docente.titulos ? lee.docente.titulos.seleccionados || [] : [];
                        const coordinacionesSeleccion = lee.docente.coordinaciones ? lee.docente.coordinaciones.seleccionadas || [] : [];

                        if (lee.catalogos) {
                            establecerCatalogosDocente(lee.catalogos, titulosSeleccion, coordinacionesSeleccion);
                        }

                        poblarFormularioDocente(lee.docente, titulosSeleccion, coordinacionesSeleccion);
                    } else {
                        deshabilitarFormularioDocente();
                    }
                } else if (lee.resultado === 'modificar') {
                    muestraMensaje('info', 4000, 'PERFIL', lee.mensaje);
                    Listar();
                    const nuevaFoto = $('#fotoPerfil').attr('src');
                    $('.navbar-nav img[alt="Foto de perfil"]').attr('src', nuevaFoto);
                } else if (lee.resultado === 'actualizar_docente') {
                    const esDocente = Boolean(lee.tiene_docente && lee.docente);
                    if (esDocente) {
                        const titulosSeleccion = lee.docente.titulos ? lee.docente.titulos.seleccionados || [] : [];
                        const coordinacionesSeleccion = lee.docente.coordinaciones ? lee.docente.coordinaciones.seleccionadas || [] : [];

                        if (lee.catalogos) {
                            establecerCatalogosDocente(lee.catalogos, titulosSeleccion, coordinacionesSeleccion);
                        }
                        poblarFormularioDocente(lee.docente, titulosSeleccion, coordinacionesSeleccion);
                    } else {
                        deshabilitarFormularioDocente();
                    }

                    muestraMensaje('info', 4000, 'DOCENTE', lee.mensaje);
                } else if (lee.resultado === 'error') {
                    muestraMensaje('error', 4000, 'ERROR', lee.mensaje);
                }
            } catch (e) {
                alert('Error en JSON: ' + e.message);
            }
        },
        error: function () {
            muestraMensaje('error', 4000, 'ERROR', 'Error de conexión');
        }
    });
}

$(document).ready(function () {
    $('#docenteTitulos, #docenteCoordinaciones').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione...',
        allowClear: true,
        width: '100%',
        closeOnSelect: false
    });

    actualizarResumenSelect($('#docenteTitulos'), $('#docenteTitulosResumen'));
    actualizarResumenSelect($('#docenteCoordinaciones'), $('#docenteCoordinacionesResumen'));

    $('.perfil-foto-label, #fotoPerfil').on('click', function (e) {
        e.preventDefault();
        $('#fotoPerfilInput').click();
    });

    $('#formPerfil').on('submit', function (e) {
        e.preventDefault();
        if (!validarPerfil()) {
            return;
        }
        const datos = new FormData();
        datos.append('accion', 'modificar');
        datos.append('correoUsuario', $('#correoUsuario').val());

        if ($('#contraseniaPerfil').val().length > 0) {
            datos.append('contraseniaUsuario', $('#contraseniaPerfil').val());
        }

        if ($('#fotoPerfilInput')[0].files.length > 0) {
            const file = $('#fotoPerfilInput')[0].files[0];
            const reader = new FileReader();
            reader.onload = function (ev) {
                datos.append('fotoPerfil', ev.target.result);
                enviaAjax(datos);
            };
            reader.readAsDataURL(file);
        } else {
            datos.append('fotoPerfil', $('#fotoPerfil').attr('src'));
            enviaAjax(datos);
        }
    });

    $('#formPerfil input, #formPerfil textarea').on('input change', function () {
        verificarCambiosPerfil();
    });
    $('#fotoPerfilInput').on('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                $('#fotoPerfil').attr('src', ev.target.result);
                verificarCambiosPerfil();
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
                    verificarCambiosPerfil();
                } catch (e) { }
            }
        });
    });

    $('#contraseniaPerfil').on('keyup keydown', function () {
        $('#scontraseniaPerfil').css('color', '');
        if ($(this).val().length > 0) {
            validarkeyup(/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]).{8,30}$/, $(this), $('#scontraseniaPerfil'), 'Debe tener entre 8-30 caracteres, al menos una mayúscula y un carácter especial.');
        } else {
            $('#scontraseniaPerfil').text('');
        }
        verificarCambiosPerfil();
    });

    $('#formPerfilDocente').on('submit', function (e) {
        e.preventDefault();
        if (!validarPerfilDocente()) {
            return;
        }
        const datos = new FormData();
        datos.append('accion', 'actualizar_docente');
        datos.append('doc_correo', $('#docenteCorreo').val());

        ($('#docenteTitulos').val() || []).forEach(valor => datos.append('titulos[]', valor));
        ($('#docenteCoordinaciones').val() || []).forEach(valor => datos.append('coordinaciones[]', valor));

        enviaAjax(datos);
    });

    $('#docenteCorreo').on('input change', function () {
        verificarCambiosDocente();
    });

    $('#docenteTitulos').on('change', function () {
        actualizarResumenSelect($(this), $('#docenteTitulosResumen'));
        verificarCambiosDocente();
    });

    $('#docenteCoordinaciones').on('change', function () {
        actualizarResumenSelect($(this), $('#docenteCoordinacionesResumen'));
        verificarCambiosDocente();
    });

    Listar();
});

