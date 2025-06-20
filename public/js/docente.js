function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  if ($.fn.DataTable.isDataTable("#tabladocente")) {
    $("#tabladocente").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tabladocente")) {
    $("#tabladocente").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      scrollX: true,
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
      order: [[1, "asc"]],
      dom:
        "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });
  }
}

$(document).ready(function () {
  Listar();

  // VALIDACIONES
  $("#cedulaDocente").on("keypress", function (e) {
    validarkeypress(/^[0-9-\b]*$/, e);
  });

  $("#cedulaDocente").on("keyup", function () {
    if (validarkeyup(/^[0-9]{7,8}$/, $(this), $("#scedulaDocente"), "Debe ser una cédula válida (7-8 dígitos).")) {
      if ($("#proceso").text() === "REGISTRAR") {
        var datos = new FormData();
        datos.append('accion', 'Existe');
        datos.append('cedulaDocente', $(this).val());
        enviaAjax(datos);
      }
    }
  });

  $("#nombreDocente, #apellidoDocente").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z\u00f1\u00d1\b\s]*$/, e);
  });

  $("#nombreDocente").on("keyup", function () {
    validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $(this), $("#snombreDocente"), "El nombre es requerido (max 30 caracteres).");
  });

  $("#apellidoDocente").on("keyup", function () {
    validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $(this), $("#sapellidoDocente"), "El apellido es requerido (max 30 caracteres).");
  });

  $("#correoDocente").on("keyup", function () {
    validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $(this), $("#scorreoDocente"), "El formato de correo no es válido.");
  });

  $("input[name='titulos[]']").change(function() {
      if ($("input[name='titulos[]']:checked").length === 0) {
        $("#stitulos").text("Debe seleccionar al menos un título.");
      } else {
        $("#stitulos").text("");
      }
  });

  $("input[name='coordinaciones[]']").change(function() {
      if ($("input[name='coordinaciones[]']:checked").length === 0) {
        $("#scoordinaciones").text("Debe seleccionar al menos una coordinación.");
      } else {
        $("#scoordinaciones").text("");
      }
  });

  // BOTONES
  $("#proceso").on("click", function () {
    if (validarenvio()) {
        var datos = new FormData($('#f')[0]);
        var accion;

        if ($(this).text() === "REGISTRAR") {
            accion = "incluir";
        } else {
            accion = "modificar";
            datos.append("prefijoCedula", $("#prefijoCedula").val());
            datos.append("cedulaDocente", $("#cedulaDocente").val());
        }
        
        datos.append("accion", accion);
        enviaAjax(datos);
    }
  });

  $("#incluir").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
  });
});

// VALIDACIÓN ANTES DE ENVÍO
function validarenvio() {
  let esValido = true;
  if (!validarkeyup(/^[0-9]{7,8}$/, $("#cedulaDocente"), $("#scedulaDocente"), "Debe ser una cédula válida (7-8 dígitos).")) esValido = false;
  if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $("#nombreDocente"), $("#snombreDocente"), "El nombre es requerido.")) esValido = false;
  if (!validarkeyup(/^[A-Za-z\u00f1\u00d1\s]{1,30}$/, $("#apellidoDocente"), $("#sapellidoDocente"), "El apellido es requerido.")) esValido = false;
  if (!validarkeyup(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $("#correoDocente"), $("#scorreoDocente"), "Formato de correo inválido.")) esValido = false;
  if ($("#categoria").val() === null || $("#categoria").val() === "") esValido = false;
  if ($("#dedicacion").val() === null || $("#dedicacion").val() === "") esValido = false;
  if ($("#condicion").val() === null || $("#condicion").val() === "") esValido = false;
  if ($("input[name='titulos[]']:checked").length === 0) {
    $("#stitulos").text("Debe seleccionar al menos un título.");
    esValido = false;
  }
  if ($("input[name='coordinaciones[]']:checked").length === 0) {
    $("#scoordinaciones").text("Debe seleccionar al menos una coordinación.");
    esValido = false;
  }
  
  if (!esValido) {
      muestraMensaje("error", 4000, "Error de Validación", "Por favor, corrija los campos requeridos.");
  }
  
  return esValido;
}


function pone(pos, accion) {
    limpia();
    const linea = $(pos).closest("tr");
    
    const prefijo = linea.find("td:eq(0)").text();
    const cedula = linea.find("td:eq(1)").text();
    const nombre = linea.find("td:eq(2)").text();
    const apellido = linea.find("td:eq(3)").text();
    const correo = linea.find("td:eq(4)").text();
    const nombreCategoria = linea.find("td:eq(5)").text();
    const dedicacion = linea.find("td:eq(8)").text();
    const condicion = linea.find("td:eq(9)").text().trim();
    const titulosIds = linea.attr('data-titulos-ids');
    const coordinacionesIds = linea.attr('data-coordinaciones-ids');

    $("#prefijoCedula").val(prefijo);
    $("#cedulaDocente").val(cedula);
    $("#nombreDocente").val(nombre);
    $("#apellidoDocente").val(apellido);
    $("#correoDocente").val(correo);
    $('#dedicacion').val(dedicacion);
    $('#condicion').val(condicion);
    
    $('#categoria option').filter(function() { return $(this).text() == nombreCategoria; }).prop('selected', true);
    
    if (titulosIds) {
        titulosIds.split(',').forEach(id => {
            if (id) $("#titulo_" + id.trim()).prop('checked', true);
        });
    }
    
    if (coordinacionesIds) {
        coordinacionesIds.split(',').forEach(id => {
            if (id) $("#coordinacion_" + id.trim()).prop('checked', true);
        });
    }

    if (accion === 'modificar') {
        $("#proceso").text("MODIFICAR");
        $("#prefijoCedula, #cedulaDocente").prop('disabled', true);
        $("form#f :input:not(#prefijoCedula, #cedulaDocente)").prop('disabled', false);
    } else {
         const cedulaParaEliminar = linea.find("td:eq(1)").text();
         Swal.fire({
            title: "¿Está seguro de eliminar este docente?",
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
                datos.append("cedulaDocente", cedulaParaEliminar);
                enviaAjax(datos);
            }
        });
        return; 
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
    timeout: 10000,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado == "consultar") {
            destruyeDT();
            $("#resultadoconsulta").empty();
            
            $.each(lee.mensaje, function(index, item) {
                $("#resultadoconsulta").append(`
                    <tr data-titulos-ids="${item.titulos_ids || ''}" data-coordinaciones-ids="${item.coordinaciones_ids || ''}">
                        <td>${item.doc_prefijo}</td>
                        <td>${item.doc_cedula}</td>
                        <td>${item.doc_nombre}</td>
                        <td>${item.doc_apellido}</td>
                        <td>${item.doc_correo}</td>
                        <td>${item.cat_nombre}</td>
                        <td>${item.titulos || 'Sin títulos'}</td>
                        <td>${item.coordinaciones || 'Sin coordinaciones'}</td>
                        <td>${item.doc_dedicacion}</td>
                        <td>${item.doc_condicion}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick='pone(this, "modificar")'>Modificar</button>
                            <button class="btn btn-danger btn-sm" onclick='pone(this, "eliminar")'>Eliminar</button>
                        </td>
                    </tr>
                `);
            });
            
            crearDT();
        // ===== INICIO DE CORRECCIÓN =====
        } else if (lee.resultado == "registrar" || lee.resultado == "modificar" || lee.resultado == "eliminar") {
        // ===== FIN DE CORRECCIÓN =====
            let accionTitle = lee.resultado.charAt(0).toUpperCase() + lee.resultado.slice(1);
            muestraMensaje("success", 4000, `¡${accionTitle} Éxitoso!`, lee.mensaje);
            $("#modal1").modal("hide");
            Listar();
        } else if (lee.resultado == "Existe") {
            // Este bloque maneja la respuesta cuando la cédula ya existe
            // y no necesita cambios.
        }  else if (lee.resultado == "error") {
            muestraMensaje("error", 10000, "ERROR", lee.mensaje);
        }
      } catch (e) {
        console.error("Error al procesar la respuesta:", e, "\nRespuesta del servidor:\n", respuesta);
        muestraMensaje("error", 10000, "Error Inesperado", "Hubo un problema al procesar la respuesta del servidor.");
      }
    },
    error: function (request, status, err) {
      muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", status == "timeout" ? "Servidor ocupado" : "Error: " + err);
    }
  });
}

function limpia() {
  $("form#f")[0].reset();
  $("form#f :input").prop('disabled', false);
  $("#scedulaDocente, #snombreDocente, #sapellidoDocente, #scorreoDocente, #stitulos, #scoordinaciones").text("").removeClass("text-danger");
}

function muestraMensaje(tipo, duracion, titulo, mensaje) {
    Swal.fire({
        icon: tipo,
        title: titulo,
        html: mensaje,
        timer: duracion,
        timerProgressBar: true,
    });
}