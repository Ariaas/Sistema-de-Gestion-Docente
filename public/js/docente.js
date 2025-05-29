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

  //////////////////////////////VALIDACIONES/////////////////////////////////////
  
  $("#cedulaDocente").on("keypress", function (e) {
    validarkeypress(/^[0-9-\b]*$/, e);
  });

  $("#cedulaDocente").on("keyup keydown", function () {
    validarkeyup(
      /^[0-9]{7,8}$/,
      $(this),
      $("#scedulaDocente"),
      "El formato debe ser un número de cédula válido"
    );
    if ($("#cedulaDocente").val().length <= 10) {
      var datos = new FormData();
      datos.append('accion', 'Existe');
      datos.append('cedulaDocente', $(this).val());
      enviaAjax(datos);
    }
  });

  $("#nombreDocente").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z\u00f1\u00d1\b\s]*$/, e);
  });

  $("#nombreDocente").on("keyup keydown", function () {
    validarkeyup(
      /^[A-Za-z\u00f1\u00d1\s]{1,30}$/,
      $(this),
      $("#snombreDocente"),
      "Se debe llenar este campo y debe contener un máximo de 30 caracteres"
    );
  });

  $("#apellidoDocente").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z\u00f1\u00d1\b\s]*$/, e);
  });

  $("#apellidoDocente").on("keyup keydown", function () {
    validarkeyup(
      /^[A-Za-z\u00f1\u00d1\s]{1,30}$/,
      $(this),
      $("#sapellidoDocente"),
      "Se debe llenar este campo y debe contener un máximo de 30 caracteres"
    );
  });

  $("#correoDocente").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z0-9@_.\b\u00f1\u00d1\u00E0-\u00FC.!@#$%^&*()-_=+[\]{};:'",<>/?\\|~`]*$/, e);
  });

  $("#correoDocente").on("keyup keydown", function () {
    validarkeyup(
      /^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC.!@#$%^&*()-_=+[\]{};:'",<>/?\\|~`]{3,30}[@]{1}[A-Za-z0-9]{3,8}[.]{1}[A-Za-z]{2,3}$/,
      $(this),
      $("#scorreoDocente"),
      "El formato sólo permite un correo válido!"
    );
  });
$("input[name='titulos[]']").change(function() {
    var titulosSeleccionados = $("input[name='titulos[]']:checked").length;
    if (titulosSeleccionados === 0) {
      $("#stitulos").text("Debe seleccionar al menos un título").addClass("text-danger");
    } else {
      $("#stitulos").text("").removeClass("text-danger");
    }
  });
  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
            var datos = new FormData($('#f')[0]);
            datos.append("accion", "incluir");
            datos.append("prefijoCedula", $("#prefijoCedula").val());
            datos.append("cedulaDocente", $("#cedulaDocente").val());
            datos.append("nombreDocente", $("#nombreDocente").val());
            datos.append("apellidoDocente", $("#apellidoDocente").val());
            datos.append("correoDocente", $("#correoDocente").val());
            datos.append("categoria", $("#categoria").val());
            datos.append("dedicacion", $("#dedicacion").val());
            datos.append("condicion", $("#condicion").val());

            enviaAjax(datos);
        }
    } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
            var datos = new FormData($('#f')[0]);
            datos.append("accion", "modificar");
            datos.append("prefijoCedula", $("#prefijoCedula").val());
            datos.append("cedulaDocente", $("#cedulaDocente").val());
            datos.append("nombreDocente", $("#nombreDocente").val());
            datos.append("apellidoDocente", $("#apellidoDocente").val());
            datos.append("correoDocente", $("#correoDocente").val());
            datos.append("categoria", $("#categoria").val());
            datos.append("dedicacion", $("#dedicacion").val());
            datos.append("condicion", $("#condicion").val());

            enviaAjax(datos);
        }
    } else if ($(this).text() == "ELIMINAR") {
      Swal.fire({
        title: "¿Está seguro de eliminar este docente?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
      }).then((result) => {
        if (result.isConfirmed) {
          var datos = new FormData();
          datos.append("accion", "eliminar");
          datos.append("cedulaDocente", $("#cedulaDocente").val());
          enviaAjax(datos);
        } else {
          muestraMensaje(
            "error",
            2000,
            "INFORMACIÓN",
            "La eliminación ha sido cancelada."
          );
          $("#modal1").modal("hide");
        }
      });
    }
  });

  $("#incluir").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
    $("#scedulaDocente").show();
    $("#snombreDocente").show();
    $("#sapellidoDocente").show();
    $("#scorreoDocente").show();
  });
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
  
  var categoriaSeleccionada = $("#categoria").val();

  if (categoriaSeleccionada === null || categoriaSeleccionada === "0") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione un categoria! <br/> Recuerde que debe tener alguna registrada!"
    );
    
    return false;
  } var condicionSeleccionada = $("#condicion").val();

  if (condicionSeleccionada === null || condicionSeleccionada === "0") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione una condición! <br/> Recuerde que debe tener alguna registrada!"
    );
    
    return false;
  }
  var dedicacionSeleccionada = $("#dedicacion").val();

   if (dedicacionSeleccionada === null || dedicacionSeleccionada === "0") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione una dedicación! <br/> Recuerde que debe tener alguna registrada!"
    );
    
    return false;
  }
   var tituloSeleccionada = $("#titulo").val();

   if (tituloSeleccionada === null || tituloSeleccionada === "0") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione un tutilo! <br/> Recuerde que debe tener alguna registrada!"
    );
    
    return false;
  }
 
  var titulosSeleccionados = $("input[name='titulos[]']:checked").length;
  if (titulosSeleccionados === 0) {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione al menos un título!"
    );
    $("#stitulos").text("Debe seleccionar al menos un título").addClass("text-danger");
    return false;
  } 
   else if (
    validarkeyup(
      /^[0-9]{7,8}$/,
      $("#cedulaDocente"),
      $("#scedulaDocente"),
      "El formato debe ser un número de cédula válido"
    ) == 0
  ) {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "La cédula del docente debe coincidir con el formato <br/>" +
        "V-12345678"
    );
    return false;
  } else if (
    validarkeyup(
      /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{1,30}$/,
      $("#nombreDocente"),
      $("#snombreDocente"),
      "No debe contener más de 30 caracteres"
    ) == 0
  ) {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "El nombre del docente <br/> No debe estar vacío, ni contener más de 30 carácteres"
    );
    return false;
  } else if (
    validarkeyup(
      /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{1,30}$/,
      $("#apellidoDocente"),
      $("#sapellidoDocente"),
      "No debe contener más de 200 caracteres"
    ) == 0
  ) {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "El apellido del docente <br/> No debe estar vacío, ni contener más de 30 carácteres"
    );
    return false;
  } else if (
    validarkeyup(
      /^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC.!@#$%^&*()-_=+[\]{};:'",<>/?\\|~`]{3,30}[@]{1}[A-Za-z0-9]{3,8}[.]{1}[A-Za-z]{2,3}$/,
      $("#correoDocente"),
      $("#scorreoDocente"),
      "No debe contener más de 30 carácteres"
    ) == 0
  ) {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "El correo del docente <br/> No debe estar vacío, ni contener más de 30 carácteres"
    );
    return false;
  } 
  return true;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    
      $("#proceso").text("MODIFICAR");
      $("#prefijoCedula").prop('disabled', true);
      $("#cedulaDocente").prop('disabled', true);
      $("#nombreDocente, #apellidoDocente, #correoDocente, #categoria, #dedicacion, #condicion, input[name='titulos[]']").prop('disabled', false);
  } else {
      
      $("#proceso").text("ELIMINAR");
      $("#cedulaDocente, #prefijoCedula, #nombreDocente, #apellidoDocente, #correoDocente, #categoria, #dedicacion, #condicion, input[name='titulos[]']").prop('disabled', true);
  }

  
  $("#prefijoCedula").val($(linea).find("td:eq(0)").text());
  $("#cedulaDocente").val($(linea).find("td:eq(1)").text());
  $("#nombreDocente").val($(linea).find("td:eq(2)").text());
  $("#apellidoDocente").val($(linea).find("td:eq(3)").text());
  $("#correoDocente").val($(linea).find("td:eq(4)").text());
  
 
  var nombrecategoria = $(linea).find("td:eq(5)").text();
  $('#categoria option').filter(function() {
      return $(this).text() == nombrecategoria;
  }).prop('selected', true).change();
  
  
  var titulosIds = $(linea).attr('data-titulos-ids');
  

  $("input[name='titulos[]']").prop('checked', false);
  
 
  if (titulosIds) {
      var idsArray = titulosIds.split(',');
      idsArray.forEach(function(tit_id) {
          if (tit_id) { 
              $("#titulo_" + tit_id).prop('checked', true);
          }
      });
  }

 
  var dedicacion = $(linea).find("td:eq(7)").text();
  $('#dedicacion option').filter(function() {
      return $(this).text() == dedicacion;
  }).prop('selected', true).change();
  
 
  var condicion = $(linea).find("td:eq(8)").text().trim();
  $("#condicion").val(condicion);
  
  $("#scedulaDocente").hide();
  $("#snombreDocente").hide();
  $("#sapellidoDocente").hide();
  $("#scorreoDocente").hide();
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
    beforeSend: function () {},
    timeout: 10000,
    success: function (respuesta) {
      try {
     
          var lee = JSON.parse(respuesta);
             if (lee.resultado == "consultar") {
                    destruyeDT();
                    $("#resultadoconsulta").empty();
                    
                    $.each(lee.mensaje, function(index, item) {
                        $("#resultadoconsulta").append(`
                            <tr data-titulos-ids="${item.titulos_ids || ''}">
                                <td>${item.doc_prefijo}</td>
                                <td>${item.doc_cedula}</td>
                                <td>${item.doc_nombre}</td>
                                <td>${item.doc_apellido}</td>
                                <td>${item.doc_correo}</td>
                                <td>${item.cat_nombre}</td>
                                <td>${item.titulos || 'Sin títulos'}</td>
                                <td>${item.doc_dedicacion}</td>
                                <td>${item.doc_condicion}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'>Modificar</button>
                                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'>Eliminar</button>
                                </td>
                            </tr>
                        `);
                    });
                    
                    crearDT();
                } else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/> Se registró el docente correctamente"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje == "Registro Modificado!<br/> Se modificó el docente correctamente") {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (lee.mensaje == "Registro Eliminado! <br/> Se eliminó el docente correctamente") {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "Existe") {
             
          if (lee.mensaje == 'La cédula del docente ya existe!') 
            muestraMensaje('info', 4000, 'Atención', lee.mensaje);
          
        }  else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e);
        console.log("Error en JSON " + e.name + ": " + e.message);
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("Servidor ocupado, intente de nuevo");
      } else {
        muestraMensaje("ERROR: <br/>" + request + status + err);
      }
    },
    complete: function () {},
  });
}

function limpia() {
  $("#cedulaDocente").val("");
  $("#nombreDocente").val("");
  $("#apellidoDocente").val("");
  $("#correoDocente").val("");
  $("#dedicacion").val("");
  $("#condicion").val("");
  $("#categoria").val("");
 
  $("input[name='titulos[]']").prop('checked', false);
 
  $("#stitulos").text("").removeClass("text-danger");

  $("#cedulaDocente, #prefijoCedula, #nombreDocente, #apellidoDocente, #correoDocente, #categoria, input[name='titulos[]'], #condicion, #dedicacion").prop('disabled', false);
}