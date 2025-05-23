function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  // se destruye el datatablet
  if ($.fn.DataTable.isDataTable("#tablamalla")) {
    $("#tablamalla").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablamalla")) {
    $("#tablamalla").DataTable({
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
  /*  $("#certificadonombre").on("keyup", function () {
    const valor = $(this).val();
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/,$("#certificadonombre"),$("#scertificadonombre"),"No debe contener más de 30 caracteres");
  
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append("certificadonombre", $("#certificadonombre").val());
        datos.append("trayecto", $("#trayecto").val());
        enviaAjax(datos);
   
    });*/

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
    //  if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("mal_codigo", $("#mal_codigo").val());
        datos.append("mal_nombre", $("#mal_nombre").val());
        datos.append("mal_Anio", $("#mal_Anio").val());
        datos.append("mal_cohorte", $("#mal_cohorte").val());
        datos.append("mal_descripcion", $("#mal_descripcion").val());
        console.table(datos);

        enviaAjax(datos);
        
        
    //  }
    } else if ($(this).text() == "MODIFICAR") {
     // if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("mal_id", $("#mal_id").val());
        datos.append("mal_codigo", $("#mal_codigo").val());
        datos.append("mal_nombre", $("#mal_nombre").val());
        datos.append("mal_Anio", $("#mal_Anio").val());
        datos.append("mal_cohorte", $("#mal_cohorte").val());
        datos.append("mal_descripcion", $("#mal_descripcion").val());
        enviaAjax(datos);
     // }
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
            datos.append("mal_id", $("#mal_id").val());
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
  let trayecto = $("#trayecto").val();
  
   if (validarkeyup( /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,30}$/,$("#certificadonombre"),$("#scertificadonombre"),"No debe contener más de 30 caracteres") == 0) {
        muestraMensaje("error",4000,"ERROR!","El nombre del certificado <br/> No debe estar vacío, ni contener más de 30 carácteres");
          return false;
  } else if (trayecto === null || trayecto === "0") {
        muestraMensaje("error",4000,"ERROR!","Por favor, seleccione un trayecto!"); 
          return false;

}
    return true;
}
// funcion para pasar de la lista a el formulario
function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#mal_codigo").prop("disabled", false);
    $("#mal_nombre").prop("disabled", false);
    $("#mal_Anio").prop("disabled", false);
    $("#mal_cohorte").prop("disabled", false);
    $("#mal_descripcion").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#mal_id, #mal_codigo, #mal_nombre, #mal_Anio, #mal_cohorte, #mal_descripcion").prop("disabled", false);
  }



  $("#mal_id").val($(linea).find("td:eq(0)").text());
  $("#mal_codigo").val($(linea).find("td:eq(1)").text());
  $("#mal_nombre").val($(linea).find("td:eq(2)").text());
  $("#mal_Anio").val($(linea).find("td:eq(3)").text());
  $("#mal_cohorte").val($(linea).find("td:eq(4)").text());
  $("#mal_descripcion").val($(linea).find("td:eq(5)").text());
   

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
                <td style="display: none;">${item.mal_id}</td>
                <td>${item.mal_codigo}</td>
                <td>${item.mal_nombre}</td>
                <td>${item.mal_anio}</td>
                <td>${item.mal_cohorte}</td>
                <td>${item.mal_descripcion}</td>
                <td>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (lee.mensaje =='Registro Incluido!<br/> Se registró la malla curricular correctamente!' ) {
            $("#modal1").modal("hide");
            limpia();
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
            muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
            if (lee.mensaje =="Registro Modificado!<br/>Se modificó la malla curricular correctamente!") {
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
          if (lee.mensaje =="Registro Eliminado!<br/>Se eliminó la malla curricular correctamente!!") {
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
  $("#mal_codigo").val("");
  $("#mal_nombre").val("");
  $("#mal_Anio").val("");
  $("#mal_cohorte").val("");
  $("#mal_descripcion").val("");
   
}
