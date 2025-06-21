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
    if ($.fn.DataTable.isDataTable("#tablaconvenio")) {
        $('#tablaconvenio').DataTable().destroy();
    }
}

function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaconvenio")) {
        dataTable = $("#tablaconvenio").DataTable({
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

    // VALIDACIONES (usando funciones de validacion.js)
    $("#convenioNombre").on("keyup", function() {
        validarkeyup(/^[A-Za-z0-9\s.,-]{4,30}$/, $(this), $("#sconvenioNombre"), "El nombre debe tener entre 4 y 30 caracteres (letras, números, espacios y .,-).");
    });
    $("#traId").on("change", function() {
        validarSelect($(this), $("#straId"), "Seleccione un trayecto.");
    });
    $("#convenioInicio").on("change", function() {
        validarFecha($(this), $("#sconvenioInicio"), "Seleccione una fecha de inicio.");
    });

    // BOTONES
    $("#proceso").on("click", function() {
        if (validarenvio()) {
            var datos = new FormData($("#f")[0]);
            const textoBoton = $(this).text();

            if (textoBoton === "Guardar") {
                datos.append("accion", "registrar");
            } else if (textoBoton === "Modificar") {
                datos.append("accion", "modificar");
            }
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("#modal1 .modal-title").text("Formulario de Registro de Convenio");
        $(".form-control, .form-select").prop("disabled", false);
        $("#modal1").modal("show");
    });
});

function validarenvio() {
    if (!validarkeyup(/^[A-Za-z0-9\s.,-]{4,30}$/, $("#convenioNombre"), $("#sconvenioNombre"), "El nombre debe tener entre 4 y 30 caracteres.")) {
        muestraMensaje("error", 4000, "Error de validación", "Por favor, corrija el nombre del convenio.");
        return false;
    }
    if (!validarSelect($("#traId"), $("#straId"), "Debe seleccionar un trayecto.")) {
        muestraMensaje("error", 4000, "Error de validación", "Por favor, seleccione un trayecto.");
        return false;
    }
    if (!validarFecha($("#convenioInicio"), $("#sconvenioInicio"), "Debe seleccionar una fecha de inicio.")) {
        muestraMensaje("error", 4000, "Error de validación", "Por favor, seleccione una fecha de inicio válida.");
        return false;
    }
    return true;
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


function pone(pos, accion) {
    let linea = $(pos).closest("tr");
    if (dataTable) { 
        linea = dataTable.row($(pos).closest("tr")).data();
    }
    
    const id = Array.isArray(linea) ? linea[0] : $(linea).find("td:eq(0)").text();
    const nombre = Array.isArray(linea) ? linea[1] : $(linea).find("td:eq(1)").text();
    const inicio = Array.isArray(linea) ? linea[2] : $(linea).find("td:eq(2)").text();
    const traId = Array.isArray(linea) ? linea[4] : $(linea).find("td:eq(4)").text();

    limpia();
    $("#convenioId").val(id);
    $("#convenioNombre").val(nombre);
    $("#convenioInicio").val(inicio);
    $("#traId").val(traId);
    
    if (accion === 'modificar') {
        $("#proceso").text("MODIFICAR");
        $("#modal1 .modal-title").text("Formulario de Modificación de Convenio");
        $(".form-control, .form-select").prop("disabled", false);
        $("#modal1").modal("show");
    } else if (accion === 'eliminar') {
        Swal.fire({
            title: "¿Está seguro de eliminar este convenio?",
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
                datos.append("convenioId", id);
                enviaAjax(datos);
            }
        });
    }
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
                            <td style="display: none;">${item.con_id}</td>
                            <td>${item.con_nombre}</td>
                            <td>${item.con_inicio}</td>
                            <td>Trayecto ${item.tra_numero} (${item.ani_anio})</td>
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
            muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", status == "timeout" ? "Servidor ocupado, intente de nuevo." : "Ocurrió un error: " + err);
        }
    });
}

function limpia() {
    $("#convenioId").val("");
    $("#convenioNombre").val("");
    $("#convenioInicio").val("");
    $("#traId").val("");
    // Limpiar mensajes de error
    $("#sconvenioNombre, #straId, #sconvenioInicio").hide().text("");
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