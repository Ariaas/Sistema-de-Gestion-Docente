function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  // se destruye el datatablet
  if ($.fn.DataTable.isDataTable("#tablatitulo")) {
    $("#tablatitulo").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablatitulo")) {
    $("#tablatitulo").DataTable({
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
    $("#titulonombre").on("keydown keyup", function () {
  
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/,$("#titulonombre"),$("#stitulonombre"),"El formato permite de 5 a 30 carácteres, Ej:En sistemas");
  
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append("tituloprefijo", $("#tituloprefijo").val());
        datos.append("titulonombre", $("#titulonombre").val());
        enviaAjax(datos);
   
    });

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("tituloprefijo", $("#tituloprefijo").val());
        datos.append("titulonombre", $("#titulonombre").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("tituloid", $("#tituloid").val());
        datos.append("tituloprefijo", $("#tituloprefijo").val());
        datos.append("titulonombre", $("#titulonombre").val());

        enviaAjax(datos);
      }
    }
    if ($(this).text() == "ELIMINAR") {  
        // Mostrar confirmación usando SweetAlert
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
            // Si se confirma, proceder con la eliminación
            var datos = new FormData();
            datos.append("accion", "eliminar");
            datos.append("tituloid", $("#tituloid").val());
            enviaAjax(datos);
          } else {
            muestraMensaje("error",2000,"INFORMACIÓN","La eliminación ha sido cancelada.");
            $("#modal1").modal("hide");
          }
        });
    }
  });


  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
   let prefijo = $("#tituloprefijo").val();
  if (prefijo === null || prefijo === "0") {
        muestraMensaje("error",4000,"ERROR!","Por favor, seleccione un prefijo!"); 
          return false;
  } else if (validarkeyup( /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/,$("#titulonombre"),$("#stitulonombre"),"El formato permite de 5 a 30 carácteres, Ej:En sistemas") == 0) {
        muestraMensaje("error",4000,"ERROR!","El nombre del titulo <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
          return false;
  }
  return true;
}

// funcion para pasar de la lista a el formulario
function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#tituloprefijo").prop("disabled", false);
    $("#titulonombre").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#tituloprefijo, #titulonombre").prop("disabled", false);
  }
  $("#tituloid").val($(linea).find("td:eq(0)").text());
  $("#titulonombre").val($(linea).find("td:eq(2)").text());
  $("#tituloprefijo").val($(linea).find("td:eq(1)").text());
 

  $("#modal1").modal("show");
}

//funcion que envia y recibe datos por AJAX
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
    timeout: 10000, //tiempo maximo de espera por la respuesta del servidor
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.tit_id}</td>
                <td>${item.tit_prefijo}</td>
                <td>${item.tit_nombre}</td>
                <td>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'data-id="${item.tit_id}" data-prefijo="${item.tit_prefijo}" data-nombre="${item.tit_nombre}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'data-id="${item.tit_id}" data-prefijo="${item.tit_prefijo}" data-nombre="${item.tit_nombre}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
      if ( lee.mensaje =='Registro Incluido!<br/> Se registró el título correctamente!' ) {
            $("#modal1").modal("hide");
            limpia();
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje =="Registro Modificado!<br/>Se modificó el titulo correctamente!") {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "existe") {
          if ($("#proceso").text() == "REGISTRAR") {
            muestraMensaje('info', 4000, 'Atención!', lee.mensaje);
          }
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
        if (  lee.mensaje =="Registro Eliminado!<br/>Se eliminó el titulo correctamente!") {
            $("#modal1").modal("hide");
            Listar();
         }
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e); // Registrar el error para depuración
        alert("Error en JSON " + e.name + ": " + e.message);
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
  $("#tituloprefijo").val("");
  $("#titulonombre").val("");
}


