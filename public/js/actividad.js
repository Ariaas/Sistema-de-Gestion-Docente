function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tablaactividad")) {
    $("#tablaactividad").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaactividad")) {
    $("#tablaactividad").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      language: {
        lengthMenu: "Mostrar _MENU_ registros",
        zeroRecords: "No se encontraron resultados",
        info: "Mostrando _PAGE_ de _PAGES_",
        infoEmpty: "No hay registros disponibles",
        infoFiltered: "(filtrado de _MAX_ registros totales)",
        search: "Buscar:",
        paginate: {
          first: "Primero",
          last: "Último",
          next: "Siguiente",
          previous: "Anterior",
        },
      },
      autoWidth: false,
      order: [[0, "asc"]],
      dom:
        "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });

    $("div.dataTables_length select").css({
      width: "auto",
      display: "inline",
      "margin-top": "10px",
    });

    $("div.dataTables_filter").css({
      "margin-bottom": "50px",
      "margin-top": "10px",
    });

    $("div.dataTables_filter label").css({
      float: "left",
    });

    $("div.dataTables_filter input").css({
      width: "300px",
      float: "right",
      "margin-left": "10px",
    });
  }
}

$(document).ready(function () {
    Listar();

    $("#registrar").on("click", function () {
        limpia();
        CargarDocentes(); 
        $("#proceso").text("REGISTRAR");
        $("#modal1 .modal-title").text("Registrar Actividad");
        $("form#f :input").prop('disabled', false); 
        $("#modal1").modal("show");
    });
    
    $("#proceso").on("click", function () {
        const accion = $(this).text();
        const datos = new FormData();

        if (accion === "REGISTRAR" || accion === "MODIFICAR") {
            if (validarenvio()) {
                datos.append("accion", accion.toLowerCase());
                // actId ahora contiene la cédula del docente para la modificación
                datos.append("actId", $("#actId").val()); 
                datos.append("docId", $("#docId").val());
                datos.append("actCreacion", $("#actCreacion").val());
                datos.append("actIntegracion", $("#actIntegracion").val());
                datos.append("actGestion", $("#actGestion").val());
                datos.append("actOtras", $("#actOtras").val());
                enviaAjax(datos);
            }
        } else if (accion === "ELIMINAR") {
            Swal.fire({
                title: "¿Está seguro de eliminar este registro de actividad?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    datos.append("accion", "eliminar");
                    // actId ahora contiene la cédula para identificar el registro a eliminar
                    datos.append("actId", $("#actId").val());
                    enviaAjax(datos);
                }
            });
        }
    });

    $("#docId").on("change", function() {
        if ($("#actId").val()) return; 
        var docId = $(this).val(); // docId es la cédula
        var spanDocId = $("#sdocId");
        var botonProceso = $("#proceso");
        if (docId) {
            var datos = new FormData();
            datos.append('accion', 'verificar_docente');
            datos.append('docId', docId);
            $.ajax({
                async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
                success: function(respuesta) {
                    try {
                        var lee = JSON.parse(respuesta);
                        if (lee.existe) {
                            spanDocId.html("Este docente ya tiene horas registradas.").css("color", "red");
                            botonProceso.prop('disabled', true);
                        } else {
                            spanDocId.html("Docente disponible.").css("color", "green");
                            botonProceso.prop('disabled', false);
                        }
                    } catch (e) { console.log("Error en la verificación:", e); }
                }
            });
        } else {
            spanDocId.text("");
            botonProceso.prop('disabled', false);
        }
    });
});

function CargarDocentes() {
    var datos = new FormData();
    datos.append("accion", "listar_docentes");
    $.ajax({
        async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === "listar_docentes") {
                    $("#docId").empty().append('<option value="">Seleccione un docente</option>');
                    $.each(lee.mensaje, function (index, item) {
                        // El valor del option es ahora la cédula del docente
                        $("#docId").append(`<option value="${item.doc_cedula}">${item.doc_nombre} ${item.doc_apellido}</option>`);
                    });
                }
            } catch (e) { alert("Error al cargar docentes: " + e); }
        }
    });
}

function validarenvio() {
    if ($("#docId").val() == "") {
        muestraMensaje("error", 4000, "ERROR", "Debe seleccionar un docente");
        return false;
    }
    return true;
}

function pone(pos, accion) {
    limpia();
    CargarDocentes();
    const fila = $(pos).closest("tr");

    // El primer TD (oculto) y el data-docid contienen la cédula del docente
    const docCedula = fila.find("td:eq(0)").text();
    $("#actId").val(docCedula); // Se usa el campo oculto para guardar la cédula
    $("#actCreacion").val(fila.find("td:eq(2)").text());
    $("#actIntegracion").val(fila.find("td:eq(3)").text());
    $("#actGestion").val(fila.find("td:eq(4)").text());
    $("#actOtras").val(fila.find("td:eq(5)").text());
    
    // Se selecciona el docente en el dropdown usando su cédula
    setTimeout(() => $("#docId").val(docCedula), 200);

    if (accion === 0) { // Modificar
        $("#proceso").text("MODIFICAR");
        $("#modal1 .modal-title").text("Modificar Actividad");
        $("form#f :input").prop('disabled', false);
        $("#docId").prop('disabled', true); // Se deshabilita cambiar el docente al modificar
    } else if (accion === 1) { // Eliminar
        $("#proceso").text("ELIMINAR");
        $("#modal1 .modal-title").text("Confirmar Eliminación");
        $("form#f .form-control, form#f .form-select").prop('disabled', true); 
    }
    
    $("#modal1").modal("show");
}

function enviaAjax(datos) {
    $.ajax({
        async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === "consultar") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    $.each(lee.mensaje, function (index, item) {
                        $("#resultadoconsulta").append(`
                            <tr data-docid='${item.doc_cedula}'>
                                <td style="display: none;">${item.doc_cedula}</td>
                                <td>${item.doc_nombre} ${item.doc_apellido}</td>
                                <td>${item.act_creacion_intelectual}</td>
                                <td>${item.act_integracion_comunidad}</td>
                                <td>${item.act_gestion_academica}</td>
                                <td>${item.act_otras}</td>
                                <td><span class="badge bg-primary fs-6">${item.horas_totales}</span></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick='pone(this,0)'>Modificar</button>
                                    <button class="btn btn-danger btn-sm" onclick='pone(this,1)'>Eliminar</button>
                                </td>
                            </tr>
                        `);
                    });
                    crearDT();
                } else if (lee.resultado == "registrar" || lee.resultado == "modificar" || lee.resultado == "eliminar") {
                    muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                    $("#modal1").modal("hide");
                    Listar();
                } else if (lee.resultado == "error") {
                    muestraMensaje("error", 10000, "ERROR", lee.mensaje);
                }
            } catch (e) {
                alert("Error en JSON " + e);
            }
        },
        error: function (request, status, err) {
            muestraMensaje("error", 6000, "ERROR DE SERVIDOR", `${status}: ${err}`);
        }
    });
}

function limpia() {
    $("#f")[0].reset();
    $("#actId").val("");
    $("#docId").val("").prop('disabled', false);
    $("#sdocId").text("");
    $("#proceso").prop('disabled', false);
}

function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
}