function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {

  if ($.fn.DataTable.isDataTable("#tablaespacio")) {
    $("#tablaespacio").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaespacio")) {
    $("#tablaespacio").DataTable({
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      // responsive: true, // Desactivado para evitar conflictos
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
      dom:
        "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });
  }
}

$(document).ready(function () {
  Listar();

  //////////////////////////////VALIDACIONES/////////////////////////////////////

    $("#codigoEspacio").on("keypress",function(e){
    validarkeypress(/^[A-Za-z0-9-\b]*$/,e);
	});

	$("#codigoEspacio").on("keyup keydown",function(){
		validarkeyup(/^[A-Za-z0-9]{2,5}$/,$(this),
		$("#scodigoEspacio"),"El formato permite de 2 a 5 carácteres");
		if ($("#codigoEspacio").val().length <= 3) {
			var datos = new FormData();
			datos.append('accion', 'existe');
			datos.append('codigoEspacio', $(this).val());
			enviaAjax(datos, 'existe');
		}
	});

    $("#tipoEspacio").on("keypress", function (e) {
      validarkeypress(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
    });

    $("#tipoEspacio").on("keyup keydown", function () {
      validarkeyup(
        /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,30}$/,
        $(this),
        $("#stipoEspacio"),
        "Este formato no debe estar vacío / permite un máximo 30 carácteres"
      );
    });

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("codigoEspacio", $("#codigoEspacio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("codigoEspacio", $("#codigoEspacio").val());
        datos.append("tipoEspacio", $("#tipoEspacio").val());

        enviaAjax(datos);
      }
    }
    if ($(this).text() == "ELIMINAR") {
      if (
        validarkeyup(
          /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{2,6}$/,
          $("#codigoEspacio"),
          $("#scodigoEspacio"),
          "Formato de codigo incorrecto"
        ) == 0
      ) {
        muestraMensaje(
          "error",
          4000,
          "ERROR!",
          "Seleccionó un codigo incorrecto <br/> por favor verifique nuevamente"
        );
      } else {
        
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
            datos.append("codigoEspacio", $("#codigoEspacio").val());
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
    }
  });


  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1").modal("show");
    $("#scodigoEspacio").show();
       $(
      "#tipoEspacio, #codigoEspacio"
    ).prop("disabled", false);
  });

  

});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
    var tipoEspacio = $("#tipoEspacio").val();

    if (validarkeyup( /^[A-Za-z0-9\s-]{2,5}$/,$("#codigoEspacio"),$("#scodigoEspacio"),"El formato permite de 2 a 5 caracteres, incluyendo guiones. Ej: H-12") == 0) {
        muestraMensaje("error",4000,"ERROR!","El código del espacio <br/> No debe estar vacío y debe contener entre 2 a 5 carácteres");
          return false;
        }
    else if ( tipoEspacio === null || tipoEspacio === "0") {
    muestraMensaje(
      "error",
      4000,
      "ERROR!",
      "Por favor, seleccione un tipo de espacio!"
    );
    return false;
  }
  return true;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#tipoEspacio").prop("disabled", false);
    $("#codigoEspacio").prop("disabled", true);
  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#tipoEspacio, #codigoEspacio"
    ).prop("disabled", true);
  }

  const codigo = $(linea).find("td:eq(0)").text().trim();
  let tipo = $(linea).find("td:eq(1)").text().trim();

  if (tipo) {
    tipo = tipo.charAt(0).toUpperCase() + tipo.slice(1);
  }

  $("#codigoEspacio").val(codigo);
  $("#tipoEspacio").val(tipo);

  $("#modal1").modal("show");
  $("#scodigoEspacio").hide();
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
          let tabla = "";
          lee.mensaje.forEach(item => {
            tabla += `
            <tr>
                <td>${item.esp_codigo}</td>
                <td>${item.esp_tipo}</td>
                <td class="text-center">
                    <button class="btn btn-icon btn-edit" onclick="pone(this, 0)" title="Modificar">
                        <img src="public/assets/icons/edit.svg" alt="Modificar">
                    </button>
                    <button class="btn btn-icon btn-delete" onclick="pone(this, 1)" title="Eliminar">
                        <img src="public/assets/icons/trash.svg" alt="Eliminar">
                    </button>
                </td>
            </tr>`;
          });
          $('#resultadoconsulta').html(tabla);
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el espacio correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }else if (lee.resultado == "existe") {		
          if (lee.mensaje == 'El espacio ya existe!') {
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }	
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el espacio correctamente!"
          ) {
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
  $("#tipoEspacio").val("");
  $("#codigoEspacio").val("");
}