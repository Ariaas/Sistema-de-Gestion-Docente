function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  
  if ($.fn.DataTable.isDataTable("#tablaseccion")) {
    $("#tablaseccion").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaseccion")) {
    $("#tablaseccion").DataTable({
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
  
  
  $("#codigoSeccion").on("keyup", function(e){
    if ($("#codigoSeccion").val().length === 4) {
			var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('codigoSeccion', $(this).val());
      console.log("Año ID:", $("#anioId").val());
      datos.append('anioId', $("#anioId").val());
      enviaAjax(datos);
		}
    
  });

  $("#anioId").on("change", function() {
    let codigo = $("#codigoSeccion").val();
    let anio = $(this).val();
    if (codigo.length === 4 && anio && anio !== "0") {
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append('codigoSeccion', codigo);
        datos.append('anioId', anio);
        enviaAjax(datos);
    }
  });
  
  $("#codigoSeccion").on(" keydown keyup", function () {
    validarkeyup(/^[0-9][0-9]{3}$/, $(this), $("#scodigoSeccion"), "Formato incorrecto, el código debe tener exactamente 4 dígitos. Ej: 3104");
  });

  $("#cantidadSeccion").on(" keypress", function(e){
    validarkeypress(/^[0-9][0-9]*$/, e);
  });
  
  $("#cantidadSeccion").on("keydown keyup", function () {
    let valor = $(this).val();
  
    if (valor.length >= 3) {
      validarkeyup(/^[0-9]{2}$/, $(this), $("#scantidadSeccion"), "Error de formato, la cantidad de estudiantes es muy alta, debe ser menor. Ej: 20");
    } else {
      validarkeyup(/^[1-9][0-9]?$/, $(this), $("#scantidadSeccion"), "Error de formato, la cantidad de estudiantes debe ser mayor a 0. Ej: 20");
    }
  });

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("codigoSeccion", $("#codigoSeccion").val());
        datos.append("cantidadSeccion", $("#cantidadSeccion").val());
        datos.append("anioId", $("#anioId").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("seccionId", $("#seccionId").val());
        datos.append("codigoSeccion", $("#codigoSeccion").val());
        datos.append("cantidadSeccion", $("#cantidadSeccion").val());
        datos.append("anioId", $("#anioId").val());

        enviaAjax(datos);
      }
    }
    if ($(this).text() == "ELIMINAR") {  
        
        Swal.fire({
          title: "¿Está seguro de eliminar este espacio?",
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
            datos.append("seccionId", $("#seccionId").val());
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

  
  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
    $("#scodigoSeccion").show();
    $("#scantidadSeccion").show();
    $("#sanioId").show();
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {

  var anioSeleccionado = $("#anioId").val();

    if (validarkeyup( /^[A-Za-z0-9\s-]{4}$/,$("#codigoSeccion"),$("#scodigoSeccion"),"El formato permite 4 caracteres. Ej: 3104") == 0) {
      muestraMensaje("error",4000,"ERROR!","El código de la sección<br/> No debe estar vacío y debe contener 4 carácteres exactos");
      return false;
    } else if (validarkeyup( /^[A-Za-z0-9\s-]{1,2}$/,$("#cantidadSeccion"),$("#scantidadSeccion"),"El formato permite numeros solamente numeros mayores a 0. Ej: 20") == 0) {
      muestraMensaje("error",4000,"ERROR!","El código del espacio <br/> No debe estar vacío");
      return false;
    } else if (anioSeleccionado === null || anioSeleccionado === "0") {
        muestraMensaje(
            "error",
            4000,
            "ERROR!",
            "Por favor, seleccione un año! <br/> Recuerde que debe tener alguno registrado!"
        );
        return false;
    }
  return true;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#codigoSeccion").prop("disabled", false);
    $("#cantidadSeccion").prop("disabled", false);
    $("#anioId").prop("disabled", false);

  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#codigoSeccion, #cantidadSeccion, #anioId"
    ).prop("disabled", false);
  }
  
  $("#seccionId").val($(linea).find("td:eq(0)").text());
  $("#codigoSeccion").val($(linea).find("td:eq(1)").text());
  $("#cantidadSeccion").val($(linea).find("td:eq(2)").text());
  let ani_id = $(linea).find("td:eq(3)").data("anio");
  $("#anioId").val(ani_id);
  
  console.log("Sección ID:", $(linea).find("td:eq(3)").text());
  
  $("#scodigoSeccion").hide();
  $("#scantidadSeccion").hide();
  $("#sanioId").hide();
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
        if (lee.resultado === "consultar") {
          destruyeDT("#tablaseccion");
          $("#resultadoconsulta1").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta1").append(`
              <tr>
                <td style="display: none;">${item.sec_id}</td>
                <td>${item.sec_codigo}</td>
                <td>${item.sec_cantidad}</td>
                <td data-anio="${item.ani_id}">${item.ani_anio}</td>
                <td>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-cantidad="${item.sec_cantidad}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-cantidad="${item.sec_cantidad}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaseccion");
        } else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró la sección correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó la sección correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "existe") {		
          if (lee.mensaje == 'La SECCIÓN colocada YA existe!') {
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }	
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó la sección correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e, respuesta); 
        alert("Error en JSON " + e.name + ": " + e.message + "\nRespuesta: " + respuesta);
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
  $("#seccionId").val("");
  $("#anioId").val("");
  $("#codigoSeccion").val("");
  $("#cantidadSeccion").val("");
  $("#sanioId, #scodigoSeccion, #scantidadSeccion").text("");
}