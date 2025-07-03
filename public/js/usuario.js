function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function validarExiste() {
  const nombre = $("#usuarionombre").val();
  const correo = $("#correo").val();
  if (nombre && correo) {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('nombreUsuario', nombre);
    datos.append('correoUsuario', correo);
    if ($("#proceso").text() == "MODIFICAR") {
      datos.append('usuarioId', $("#usuarioId").val());
    }
    enviaAjax(datos);
  }
}

function destruyeDT() {
  
  if ($.fn.DataTable.isDataTable("#tablausuario")) {
    $("#tablausuario").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablausuario")) {
    $("#tablausuario").DataTable({
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

    $("#usuarionombre, #correo").on("keyup change", function () {
      validarExiste();
    });

    $("#usuarionombre").on("keyup keydown", function () {
    const valor = $(this).val();
    validarkeyup(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,15}$/,$("#usuarionombre"),$("#susuarionombre"),"Este formato permite de 4 a 15 carácteres"); 
    });

    $("#correo").on("keyup keydown", function () {
    const valor = $(this).val();
    validarkeyup(/^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC-]{3,30}[@]{1}[A-Za-z0-9]{3,8}[.]{1}[A-Za-z]{2,3}$/,$("#correo"),$("#scorreo"),"El formato sólo permite un correo válido!");
    });

    $("#contrasenia").on("keyup keydown", function () {
    const valor = $(this).val();
    validarkeyup(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,15}$/,$("#contrasenia"),$("#scontrasenia"),"Solo letras y numeros entre 4 y 15 caracteres");

    });
 

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("nombreUsuario", $("#usuarionombre").val());
        datos.append("correoUsuario", $("#correo").val());
        datos.append("contraseniaUsuario", $("#contrasenia").val());
        datos.append("usuarioRol", $("#usuarioRol").val());
        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("usuarioId", $("#usuarioId").val()); 
        datos.append("nombreUsuario", $("#usuarionombre").val());
        datos.append("correoUsuario", $("#correo").val());
        datos.append("usuarioRol", $("#usuarioRol").val());
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
            datos.append("usuarioId", $("#usuarioId").val()); 
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
    $(".grupo-modificar").show();
    $("#modal1").modal("show");
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {

 
  
   if (validarkeyup( /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,15}$/,$("#usuarionombre"),$("#susuarionombre"),"Este formato permite de 4 a 15 carácteres") == 0) {
        muestraMensaje("error",4000,"ERROR!","El usuario debe coincidir con el formato <br/>" + 
			"se permiten de 4 a 15 carácteres");
          return false;
  } else if (validarkeyup(/^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC.-]{3,30}@([A-Za-z0-9-]+\.){1,3}[A-Za-z]{2,3}$/, $("#correo"), $("#scorreo"), "El formato sólo permite un correo válido!") == 0) {
    muestraMensaje("error", 4000, "ERROR!", "El correo del usuario <br/> No debe estar vacío, ni contener más de 30 carácteres");
    return false;
}

    return true;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#usuarionombre").prop("disabled", false);
    $("#correo").prop("disabled", false);
    $("#contrasenia").prop("disabled", false);
    $("#usuarioRol").prop("disabled", false);
    $(".grupo-modificar").show();
  } else {
    $("#proceso").text("ELIMINAR");
    $("#usuarionombre, #correo, #contrasenia, #usuarioRol").prop("disabled", true);
    $(".grupo-modificar").hide();
  }

  $("#usuarioId").val($(linea).find("td:eq(0)").text());
  $("#usuarionombre").val($(linea).find("td:eq(1)").text());
  $("#correo").val($(linea).find("td:eq(2)").text());
  $("#usuarioRol").val($(linea).find("td:eq(3)").data("rol") || "");

  $("#susuarionombre").hide();
  $("#scontrasenia").hide();
  $("#scorreo").hide();

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
                <td style="display: none;">${item.usu_id}</td>
                <td>${item.usu_nombre}</td>
                <td>${item.usu_correo}</td>
                <td>${item.rol_nombre ?? ''}</td>
                <td>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-id="${item.usu_id}" data-nombre="${item.usu_nombre}" data-correo="${item.usu_correo}" data-rol="${item.rol_id}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-id="${item.usu_id}" data-nombre="${item.usu_nombre}" data-correo="${item.usu_correo}" data-rol="${item.rol_id}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
      if ( lee.mensaje =='Registro Incluido!<br/> Se registró el usuario correctamente!' ) {
            $("#modal1").modal("hide");
            limpia();
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje =="Registro Modificado!<br/>Se modificó el usuario correctamente!") {
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
        if ( lee.mensaje =="Registro Eliminado!<br/>Se eliminó el usuario correctamente!") {
            $("#modal1").modal("hide");
            Listar();
        }
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
  $("#usuarioId").val("");
  $("#usuarionombre").val("");
  $("#contrasenia").val("");
  $("#correo").val("");
  $("#usuarioRol").val("");
}

