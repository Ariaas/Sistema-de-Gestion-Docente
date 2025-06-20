// Variable global para la tabla DataTable
let dataTable;

function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
}

function cargarTrayectos() {
    var datos = new FormData();
    datos.append("accion", "consultar_trayectos");
    
    $.ajax({
        async: true,
        url: "", // Deja la URL vacía para que la petición sea a la misma página (controlador)
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === 'consultar_trayectos') {
                    const selectTrayecto = $("#traId");
                    selectTrayecto.empty().append('<option value="">Seleccione un trayecto</option>');
                    lee.mensaje.forEach(item => {
                        selectTrayecto.append(`<option value="${item.tra_id}">Trayecto ${item.tra_numero} (${item.tra_tipo}) - Año ${item.ani_anio}</option>`);
                    });
                }
            } catch (e) {
                console.error("Error al parsear JSON de trayectos:", e);
            }
        },
        error: function(request, status, err) {
            console.error("Error al cargar trayectos:", err);
        }
    });
}

function destruyeDT() {
    if ($.fn.DataTable.isDataTable("#tablafase")) {
        $('#tablafase').DataTable().destroy();
    }
}

function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablafase")) {
        dataTable = $("#tablafase").DataTable({
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            order: [[1, "asc"]]
        });
    }
}

$(document).ready(function() {
    Listar();
    cargarTrayectos();

    // VALIDACIONES
    $("#traId").on("change", function() {
        validarSelect($(this), $("#straId"), "Seleccione un trayecto.");
    });
    $("#faseNumero").on("change", function() {
        validarSelect($(this), $("#sfaseNumero"), "Seleccione un número de fase.");
    });
    $("#faseApertura").on("change", function() {
        validarFecha($(this), $("#sfaseApertura"), "Seleccione una fecha de apertura.");
    });
    $("#faseCierre").on("change", function() {
        validarFecha($(this), $("#sfaseCierre"), "Seleccione una fecha de cierre.");
        validarRangoFechas();
    });

    // BOTONES
    $("#proceso").on("click", function() {
        if (validarenvio()) {
            var datos = new FormData($("#f")[0]);
            if ($(this).text() === "Guardar") {
                datos.append("accion", "registrar");
            } else if ($(this).text() === "Modificar") {
                datos.append("accion", "modificar");
            }
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("Guardar");
        $("#modal1 .modal-title").text("Formulario de Registro de Fase");
        $(".form-control, .form-select").prop("disabled", false);
        $("#modal1").modal("show");
    });
});

function validarenvio() {
    let esValido = true;
    esValido &= validarSelect($("#traId"), $("#straId"), "Debe seleccionar un trayecto.");
    esValido &= validarSelect($("#faseNumero"), $("#sfaseNumero"), "Debe seleccionar un número de fase.");
    esValido &= validarFecha($("#faseApertura"), $("#sfaseApertura"), "Debe seleccionar una fecha de apertura.");
    esValido &= validarFecha($("#faseCierre"), $("#sfaseCierre"), "Debe seleccionar una fecha de cierre.");
    esValido &= validarRangoFechas();
    return esValido;
}

function validarSelect(input, span, mensaje) {
    if (!input.val()) {
        span.text(mensaje).show();
        return false;
    }
    span.hide();
    return true;
}

function validarFecha(input, span, mensaje) {
    if (!input.val()) {
        span.text(mensaje).show();
        return false;
    }
    span.hide();
    return true;
}

function validarRangoFechas() {
    const apertura = $("#faseApertura").val();
    const cierre = $("#faseCierre").val();
    const spanCierre = $("#sfaseCierre");
    if (apertura && cierre && new Date(cierre) <= new Date(apertura)) {
        spanCierre.text("La fecha de cierre debe ser posterior a la de apertura.").show();
        return false;
    }
    // Si la fecha es válida, pero el mensaje de error anterior estaba, lo oculta.
    if(spanCierre.text() === "La fecha de cierre debe ser posterior a la de apertura."){
       spanCierre.hide();
    }
    return true;
}


function pone(pos, accion) {
    let linea = $(pos).closest("tr");
    if (dataTable) { // Si se usa DataTable, obtener datos de la API
        linea = dataTable.row($(pos).closest("tr")).data();
    }
    
    // Si es un array, acceder por índice, si es objeto, por propiedad
    const id = Array.isArray(linea) ? linea[0] : $(linea).find("td:eq(0)").text();
    const faseNumero = Array.isArray(linea) ? linea[2] : $(linea).find("td:eq(2)").text();
    const apertura = Array.isArray(linea) ? linea[3] : $(linea).find("td:eq(3)").text();
    const cierre = Array.isArray(linea) ? linea[4] : $(linea).find("td:eq(4)").text();
    const traId = Array.isArray(linea) ? linea[5] : $(linea).find("td:eq(5)").text();

    limpia();
    $("#faseId").val(id);
    $("#traId").val(traId);
    $("#faseNumero").val(faseNumero);
    $("#faseApertura").val(apertura);
    $("#faseCierre").val(cierre);
    
    if (accion === 'modificar') {
        $("#proceso").text("Modificar");
        $("#modal1 .modal-title").text("Formulario de Modificación de Fase");
        $(".form-control, .form-select").prop("disabled", false);
    } else if (accion === 'eliminar') {
        Swal.fire({
            title: "¿Está seguro de eliminar esta fase?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData();
                datos.append("accion", "eliminar");
                datos.append("faseId", $("#faseId").val());
                enviaAjax(datos);
            }
        });
        return; // Detener la ejecución para no mostrar el modal
    }

    $("#modal1").modal("show");
}

function enviaAjax(datos) {
    $.ajax({
        async: true,
        url: "",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        beforeSend: function() { /* Opcional: mostrar un loader */ },
        timeout: 10000,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === "consultar") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    lee.mensaje.forEach(item => {
                        $("#resultadoconsulta").append(`
                          <tr>
                            <td style="display: none;">${item.fase_id}</td>
                            <td>Trayecto ${item.tra_numero} (${item.ani_anio})</td>
                            <td>${item.fase_numero}</td>
                            <td>${item.fase_apertura}</td>
                            <td>${item.fase_cierre}</td>
                            <td style="display: none;">${item.tra_id}</td>
                            <td>
                              <button class="btn btn-warning btn-sm" onclick="pone(this, 'modificar')">Modificar</button>
                              <button class="btn btn-danger btn-sm" onclick="pone(this, 'eliminar')">Eliminar</button>
                            </td>
                          </tr>
                        `);
                    });
                    crearDT();
                } else if (lee.resultado === 'registrar' || lee.resultado === 'modificar' || lee.resultado === 'eliminar') {
                    muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                    $("#modal1").modal("hide");
                    Listar();
                } else if (lee.resultado === 'error') {
                    muestraMensaje("error", 5000, "ERROR", lee.mensaje);
                } else {
                     muestraMensaje("info", 4000, "ATENCIÓN", lee.mensaje);
                }
            } catch (e) {
                console.error("Error al procesar la respuesta:", e, "Respuesta:", respuesta);
                muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta del servidor.");
            }
        },
        error: function(request, status, err) {
            if (status === "timeout") {
                muestraMensaje("error", 5000, "ERROR", "Servidor ocupado, intente de nuevo.");
            } else {
                muestraMensaje("error", 5000, "ERROR", "Ocurrió un error: " + err);
            }
        }
    });
}

function limpia() {
    $("#faseId").val("");
    $("#traId").val("");
    $("#faseNumero").val("");
    $("#faseApertura").val("");
    $("#faseCierre").val("");
    // Limpiar mensajes de error
    $("#straId, #sfaseNumero, #sfaseApertura, #sfaseCierre").hide().text("");
}

// Función para mostrar mensajes (asumo que tienes una función similar, si no, puedes usar esta)
function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true,
    });
}