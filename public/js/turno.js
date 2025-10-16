let nombresExistentes = [];
let solapamientoDetectado = false;
let timeoutValidacion = null;
let originalNombreTurno = '';
let originalHoraInicio = '';
let originalHoraFin = '';

function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablaturno")) {
    $("#tablaturno").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaturno")) {
    $("#tablaturno").DataTable({
        paging: true, lengthChange: true, searching: true, ordering: true, info: true, autoWidth: false, responsive: true, scrollX: true,
        language: { lengthMenu: "Mostrar _MENU_ registros", zeroRecords: "No se encontraron resultados", info: "Mostrando _PAGE_ de _PAGES_", infoEmpty: "No hay registros disponibles", infoFiltered: "(filtrado de _MAX_ registros totales)", search: "Buscar:", paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }},
        order: [[1, "asc"]],
    });
  }
}

$(document).ready(function () {
    Listar(); 

    $('#f .form-control').on('focus input change', function() {
        validarCampo($('#turnonombre'));
        validarCampo($('#horaInicio'));
        validarCampo($('#horafin'));
        validarLogicaHoras();
        chequearEstadoBoton();
    });

    $('#horaInicio, #horafin').on('change', function() {
        validarHorasEnServidor();
    });

    $("#proceso").on("click", function () {
        if ($(this).text() === "GUARDAR") {
            procesarRegistro();
        } else {
            procesarModificacion();
        }
    });

    $("#registrar").on("click", function () {
        limpia();
        $('#turnonombre option').each(function() {
            $(this).prop('disabled', nombresExistentes.includes($(this).val()));
        });
        $("#proceso").text("GUARDAR");
        $(".modal-title").text("Registrar Turno");
        $("#modal1").modal("show");
    });

    $('#modal1').on('shown.bs.modal', function () {
        $("#turnonombre").focus();
    });

    $('#modal1').on('keydown', function(e) {
        if (e.which === 13) {
            if ($('.swal2-container').length) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            if (!$("#proceso").prop("disabled")) {
                e.preventDefault();
                $("#proceso").click();
            }
        }
    });

    $("#btnConfirmarEliminar").on("click", function() {
        procesarEliminacion();
    });
});

function procesarRegistro() {
    if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("turnonombre", $("#turnonombre").val());
        datos.append("horaInicio", $("#horaInicio").val());
        datos.append("horafin", $("#horafin").val());
        enviaAjax(datos, function(respuesta) {
            muestraMensaje("success", 4000, "¡REGISTRO EXITOSO!", "El nuevo turno ha sido guardado.");
            $("#modal1").modal("hide");
            Listar();
        });
    }
}

function procesarModificacion() {
    if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("turnoid", $("#turnoid").val());
        datos.append("turnonombre", $("#turnonombre").val());
        datos.append("horaInicio", $("#horaInicio").val());
        datos.append("horafin", $("#horafin").val());
        enviaAjax(datos, function(respuesta) {
            muestraMensaje("success", 4000, "¡MODIFICACIÓN COMPLETA!", "Los cambios en el turno se guardaron.");
            $("#modal1").modal("hide");
            Listar();
        });
    }
}

function procesarEliminacion() {
    const swalInstance = Swal.fire({
        title: "¿Está seguro que quieres Eliminar este turno?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        focusConfirm: true,
        allowOutsideClick: false,
        allowEscapeKey: true,
        didOpen: () => {
            const popup = Swal.getPopup();
            popup.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.stopPropagation();
                    Swal.clickConfirm();
                }
            });
        }
    });
    
    swalInstance.then((result) => {
        if (result.isConfirmed) {
            var datos = new FormData();
            datos.append("accion", "eliminar");
            datos.append("turnoid", $("#turnoid_eliminar").val());
            enviaAjax(datos, function(respuesta) {
                muestraMensaje("info", 4000, "PROCESO COMPLETADO", "El turno ha sido eliminado.");
                $("#modalEliminar").modal("hide");
                Listar();
            });
        }
    });
}

function validarCampo(input) {
    const errorSpan = $(`#s${input.attr('id')}`);
    const inputId = input.attr('id');
    let mensajeError = '';
    
    switch (inputId) {
        case 'turnonombre': mensajeError = 'Debe seleccionar un turno.'; break;
        case 'horaInicio': mensajeError = 'Debe seleccionar una hora de inicio.'; break;
        case 'horafin': mensajeError = 'Debe seleccionar una hora fin.'; break;
        default: mensajeError = 'Este campo es requerido.';
    }
    
    if (!input.val()) {
        input.addClass('is-invalid');
        errorSpan.text(mensajeError);
        return false;
    } else {
        input.removeClass('is-invalid');
        errorSpan.text('');
        return true;
    }
}

function validarLogicaHoras() {
    const inputInicio = $('#horaInicio');
    const inputFin = $('#horafin');
    const spanInicio = $('#shoraInicio');
    const spanFin = $('#shorafin');
    const inicio = inputInicio.val();
    const fin = inputFin.val();

    if (spanInicio.text() === 'Las horas no pueden ser iguales.') spanInicio.text('');
    if (spanFin.text() === 'Las horas no pueden ser iguales.' || spanFin.text().includes('anterior')) spanFin.text('');

    if (inicio && fin) {
        if (inicio === fin) {
            inputInicio.addClass('is-invalid');
            inputFin.addClass('is-invalid');
            spanInicio.text('Las horas no pueden ser iguales.');
            spanFin.text('Las horas no pueden ser iguales.');
        } else if (fin < inicio) {
            inputFin.addClass('is-invalid');
            spanFin.text('La hora de fin no puede ser anterior a la de inicio.');
        }
    }
}

function validarHorasEnServidor() {
    clearTimeout(timeoutValidacion);
    if (!validarLogicaHoras()) {
        chequearEstadoBoton();
        return;
    }
    
    const inicio = $('#horaInicio').val();
    const fin = $('#horafin').val();
    const turnoid = $('#turnoid').val();

    timeoutValidacion = setTimeout(() => {
        var datos = new FormData();
        datos.append("accion", "chequear_solapamiento");
        datos.append("horaInicio", inicio);
        datos.append("horaFin", fin);
        if (turnoid) datos.append("turnoid", turnoid);

        $.ajax({
            url: "", type: "POST", data: datos, contentType: false, processData: false, cache: false,
            success: function(respuesta) {
                try {
                    var lee = JSON.parse(respuesta);
                    solapamientoDetectado = !!lee.solapamiento;
                    if (lee.solapamiento) {
                        $('#sSolapamiento').text(`Este horario choca con el turno: ${lee.turno_choca}`);
                        $('#horaInicio, #horafin').addClass('is-invalid');
                    } else {
                        $('#sSolapamiento').text('');
                    }
                    chequearEstadoBoton();
                } catch (e) { console.error("Error en validación de solapamiento:", e, respuesta); }
            }
        });
    }, 500);
}

function validarenvio() {
    if ($('#proceso').is(':disabled')) {
        muestraMensaje('error', 4000, '¡ACCIÓN NO PERMITIDA!', 'Debe corregir los errores o realizar cambios para poder continuar.');
        return false;
    }
    return true;
}

function chequearEstadoBoton() {
    const nombre = $('#turnonombre').val();
    const inicio = $('#horaInicio').val();
    const fin = $('#horafin').val();
    const esModoModificar = $('#turnoid').val() !== '';
    const spanSolapamiento = $('#sSolapamiento');
    
    if (solapamientoDetectado) {
        $('#proceso').prop('disabled', true);
        return;
    }

    let esValido = nombre && inicio && fin && fin > inicio;

    if (esValido && esModoModificar) {
        if (inicio === originalHoraInicio && fin === originalHoraFin) {
            esValido = false;
            spanSolapamiento.text('Realice un cambio para poder modificar.');
        } else {
            spanSolapamiento.text('');
        }
    }
    
    $('#proceso').prop('disabled', !esValido);
}

function pone(pos, accion) {
    const linea = $(pos).closest("tr");
    const nombreTurno = $(linea).find("td:eq(0)").text();
    const horaInicio24h = $(linea).data('horainicio-24h');
    const horaFin24h = $(linea).data('horafin-24h');

    if (accion === 0) {
        limpia(); 
        $("#turnoid").val(nombreTurno);
        $("#turnonombre").val(nombreTurno).prop('disabled', true);
        $("#horaInicio").val(horaInicio24h);
        $("#horafin").val(horaFin24h);
        
        originalHoraInicio = horaInicio24h;
        originalHoraFin = horaFin24h;

        $(".modal-title").text("Modificar Turno");
        $("#proceso").text("MODIFICAR");
        $('#proceso').prop('disabled', true);
        $('#sSolapamiento').text('Realice un cambio para poder modificar.');

        $("#modal1").modal("show");
    } else {
        const horaInicio12h = $(linea).find("td:eq(1)").text();
        const horaFin12h = $(linea).find("td:eq(2)").text();
        $("#turnoid_eliminar").val(nombreTurno);
        $("#turnonombre_eliminar").val(nombreTurno);
        $("#horaInicio_eliminar").val(horaInicio12h);
        $("#horafin_eliminar").val(horaFin12h);
        $("#modalEliminar").modal("show");
    }
}

function limpia() {
  limpiarErrores();
  solapamientoDetectado = false;
  originalHoraInicio = '';
  originalHoraFin = '';
  $("#turnoid").val("");
  $("#turnonombre").val("").prop('disabled', false);
  $("#horaInicio").val("");
  $("#horafin").val("");
  $(".modal-title").text("Formulario de Turno");
  $('#proceso').prop('disabled', true);
}

function enviaAjax(datos, callbackExito) {
  $.ajax({
    async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
            destruyeDT();
            $("#resultadoconsulta").empty();
            nombresExistentes = []; 
            $.each(lee.mensaje, function (index, item) {
                if (item.tur_estado == 1) {
                    nombresExistentes.push(item.tur_nombre); 
                    const btnModificar = `<button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
                    const btnEliminar = `<button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                    $("#resultadoconsulta").append(`<tr data-horainicio-24h="${item.hora_inicio_24h}" data-horafin-24h="${item.hora_fin_24h}">
                        <td>${item.tur_nombre}</td>
                        <td>${item.hora_inicio_12h}</td>
                        <td>${item.hora_fin_12h}</td>
                        <td>
                                ${btnModificar}
                      ${btnEliminar}
                         </td>
                         </tr>`);
                }
            });
            crearDT();
        } else if (lee.resultado === "error") {
            muestraMensaje("error", 10000, "¡HA OCURRIDO UN ERROR!", lee.mensaje);
        } else {
            if (typeof callbackExito === 'function') {
                callbackExito(lee);
            }
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e, "Respuesta recibida:", respuesta); 
        muestraMensaje("error", 15000, "Error Inesperado", "Se recibió una respuesta inválida del servidor.");
      }
    }
  });
}

function limpiarErrores() {
    $('#f .form-control').each(function() {
        $(this).removeClass('is-invalid');
    });
    $('#f .text-danger').each(function() {
        $(this).text('');
    });
    $('#sSolapamiento').text('');
}

function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true,
        showConfirmButton: false
    });
}