function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
  
  if ($.fn.DataTable.isDataTable("#tablacertificado")) {
    $("#tablacertificado").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablacertificado")) {
    $("#tablacertificado").DataTable({
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


  $("#certificadotipo").on("keydown keyup", function () {

   
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,30}$/,$("#certificadotipo"),$("#scertificadotipo"),"El tipo permite de 2 a 30 caracteres alfanuméricos, espacios o guiones.");
  });


  $("#certificadonombre").on("keydown", function () {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,30}$/,$("#certificadonombre"),$("#scertificadonombre"),"El formato permite de 5 a 30 carácteres. Ej:Certificado trayecto 1"); // revisar el ejemplo
    });


  $("#certificadonombre").on("keyup", function () {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,30}$/,$("#certificadonombre"),$("#scertificadonombre"),"El formato permite de 5 a 30 carácteres. Ej:Certificado trayecto 1"); // revisar el ejemplo
  
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append("certificadonombre", $(this).val());
        
        var certifi = $("#certificadoid").val();
        if (certifi) {
        datos.append("mal_id", certifi);
            }

        enviaAjax(datos);
    });

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("certificadonombre", $("#certificadonombre").val());
        datos.append("trayecto", $("#trayecto").val());
        datos.append("certificadotipo", $("#certificadotipo").val());
        
      
        
        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("certificadoid", $("#certificadoid").val());
        datos.append("certificadonombre", $("#certificadonombre").val());
        datos.append("trayecto", $("#trayecto").val());
        datos.append("certificadotipo", $("#certificadotipo").val());
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
              datos.append("certificadoid", $("#certificadoid").val());
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
    $("#scertificadonombre").show();
    $("#scertificadotipo").show();
    $("#certificadoid, #certificadonombre, #trayecto, certificadotipo").prop("disabled", false);
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
  let trayecto = $("#trayecto").val();
  
   if (validarkeyup( /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,30}$/,$("#certificadonombre"),$("#scertificadonombre"),"El formato permite de 5 a 30 carácteres. Ej:Certificado trayecto 1") == 0) {
        muestraMensaje("error",4000,"ERROR!","El nombre del certificado <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
          return false;
  } else if (trayecto === null || trayecto === "0") {
        muestraMensaje("error",4000,"ERROR!","Por favor, seleccione un trayecto!"); 
          return false;
      }
  else if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,30}$/,$("#certificadotipo"),$("#certificadotipo"),"El formato permite de 5 a 30 carácteres.") == 0) {
         muestraMensaje("error",4000,"ERROR!","El tipo de certificado <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
          return false;
      }
    return true;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");
 
  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#certificadonombre").prop("disabled", false);
    $("#trayecto").prop("disabled", false);
    $("#certificadotipo").prop("disabled", false);

  } else {
    $("#proceso").text("ELIMINAR");
  
    $("#certificadonombre, #trayecto, certificadotipo").prop("disabled", true);
  }

  
  $("#scertificadonombre").hide();
  $("#certificadoid").val($(linea).find("td:eq(0)").text());
  $("#certificadonombre").val($(linea).find("td:eq(1)").text());
 
    let tra_id = $(linea).find("td:eq(2)").attr("data-traid");
  $("#trayecto").val(tra_id);

  $("#certificadotipo").val($(linea).find("td:eq(3)").text());
  
  

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
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.cert_id}</td>
                <td>${item.cert_nombre}</td>
                <td data-traid="${item.tra_id}">${item.tra_numero}</td>
                <td>${item.cert_tipo}</td>
                <td>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'data-id="${item.cert_id}"  data-nombre="${item.cert_nombre}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'data-id="${item.cert_id}"  data-nombre="${item.cert_nombre}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
      if ( lee.mensaje =='Registro Incluido!<br/> Se registró el certificado correctamente!' ) {
            $("#modal1").modal("hide");
            limpia();
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje =="Registro Modificado!<br/>Se modificó el certificado correctamente!") {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "existe") {
          
            muestraMensaje('info', 4000, 'Atención!', lee.mensaje);
          
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
     
            $("#modal1").modal("hide");
            Listar();
      
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error en análisis JSON:", e); 
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
  $("#certificadonombre").val("");
  $("#trayecto").val("");
  $("#certificadotipo").val("");  
}
