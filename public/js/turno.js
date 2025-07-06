let nombresExistentes = [];
let solapamientoDetectado = false;
let timeoutValidacion = null;

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
        validarLogicaHoras();
        chequearEstadoBoton();
    });

   
    $('#horaInicio, #horafin').on('change', function() {
        validarHorasEnServidor();
    });



    $("#proceso").on("click", function () {
        if ($(this).text() === "REGISTRAR") {
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
        $("#proceso").text("REGISTRAR");
        $(".modal-title").text("Registrar Turno");
        $("#modal1").modal("show");
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
    const turnoAEliminar = $("#turnoid_eliminar").val();
    Swal.fire({
        title: "¿Está seguro?", text: `Se eliminará el turno "${turnoAEliminar}".`, icon: "warning",
        showCancelButton: true, confirmButtonColor: "#d33", cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            var datos = new FormData();
            datos.append("accion", "eliminar");
            datos.append("turnoid", turnoAEliminar);
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
    let esLogicaValida = true;

    if (spanInicio.text() === 'Las horas no pueden ser iguales.') spanInicio.text('');
    if (spanFin.text() === 'Las horas no pueden ser iguales.' || spanFin.text().includes('anterior')) spanFin.text('');
    if (spanInicio.text() === '') inputInicio.removeClass('is-invalid');
    if (spanFin.text() === '') inputFin.removeClass('is-invalid');

    if (inicio && fin) {
        if (inicio === fin) {
            inputInicio.addClass('is-invalid');
            inputFin.addClass('is-invalid');
            spanInicio.text('Las horas no pueden ser iguales.');
            spanFin.text('Las horas no pueden ser iguales.');
            esLogicaValida = false;
        } else if (fin < inicio) {
            inputFin.addClass('is-invalid');
            spanFin.text('La hora de fin no puede ser anterior a la de inicio.');
            esLogicaValida = false;
        }
    }
    
    if(!inicio) { validarCampo(inputInicio); esLogicaValida = false; }
    if(!fin) { validarCampo(inputFin); esLogicaValida = false; }

    return esLogicaValida;
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
                    if (lee.solapamiento) {
                        solapamientoDetectado = true;
                        const mensaje = `Este horario choca con el turno: ${lee.turno_choca}`;
                        $('#sSolapamiento').text(mensaje);
                        $('#horaInicio, #horafin').addClass('is-invalid');
                    } else {
                        solapamientoDetectado = false;
                        $('#sSolapamiento').text('');
                    }
                    chequearEstadoBoton();
                } catch (e) { console.error("Error en validación de solapamiento:", e, respuesta); }
            }
        });
    }, 500);
}

function validarenvio() {
    let esNombreValido = validarCampo($('#turnonombre'));
    let sonHorasValidas = validarLogicaHoras();
    
    if (!esNombreValido || !sonHorasValidas) {
        muestraMensaje('error', 4000, '¡CAMPOS INCOMPLETOS!', 'Por favor, rellene todos los campos marcados en rojo.');
        return false;
    }
    if (solapamientoDetectado) {
        muestraMensaje('error', 4000, '¡HORARIO OCUPADO!', 'El rango de horas seleccionado se solapa con otro turno.');
        return false;
    }

    return true;
}




function chequearEstadoBoton() {
    const nombre = $('#turnonombre').val();
    const inicio = $('#horaInicio').val();
    const fin = $('#horafin').val();
    if (nombre && inicio && fin && fin > inicio && !solapamientoDetectado) {
        $('#proceso').prop('disabled', false);
    } else {
        $('#proceso').prop('disabled', true);
    }
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
        $(".modal-title").text("Modificar Turno");
        $("#proceso").text("MODIFICAR");
        $('#proceso').prop('disabled', false);
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
                nombresExistentes.push(item.tur_nombre); 
                $("#resultadoconsulta").append(`<tr data-horainicio-24h="${item.hora_inicio_24h}" data-horafin-24h="${item.hora_fin_24h}">
                    <td>${item.tur_nombre}</td>
                    <td>${item.hora_inicio_12h}</td>
                    <td>${item.hora_fin_12h}</td>
                    <td><button type="button" class="btn btn-warning btn-sm" onclick='pone(this,0)'><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                     <button type="button" class="btn btn-danger btn-sm" onclick='pone(this,1)'><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                     </td>
                     </tr>`);
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