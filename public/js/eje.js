function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
 
  if ($.fn.DataTable.isDataTable("#tablaeje")) {
    $("#tablaeje").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablaeje")) {
    $("#tablaeje").DataTable({

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

    $("#ejeNombre").on("keypress",function(e){
    validarkeypress(/^[A-Za-z0-9-\b]*$/,e);
	});

	$("#ejeNombre").on("keydown keyup",function(){
		validarkeyup(/^[A-Za-z0-9]{5,30}$/,$("#ejeNombre"),
		$("#sejeNombre"),"El formato permite de 5 a 30 carácteres, Ej:Epistemológico");
		if ($("#ejeNombre").val().length >= 5) {
			var datos = new FormData();
			datos.append('accion', 'existe');
			datos.append('ejeNombre', $(this).val());
			enviaAjax(datos, 'existe');
		}
	});

  //////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    let accion = $(this).text();
    if (accion === "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("ejeNombre", $("#ejeNombre").val());
        datos.append("ejeDescripcion", $("#ejeDescripcion").val());
        datos.append("ejeNombreOriginal", originalNombreEje); 
        enviaAjax(datos);
      }
    } else if (accion === "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("ejeNombre", $("#ejeNombre").val());
        datos.append("ejeDescripcion", $("#ejeDescripcion").val());

        enviaAjax(datos);
      }
    } else if (accion === "ELIMINAR") {
      if (
        validarkeyup(
          /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{5,30}$/,
          $("#ejeNombre"),
          $("#sejeNombre"),
          "Formato incorrecto"
        ) == 0
      ) {
        muestraMensaje(
          "error",
          4000,
          "ERROR!",
          "Seleccionó el eje incorrecto <br/> por favor verifique nuevamente"
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
            datos.append("ejeNombre", $("#ejeNombre").val());
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
    $("#sejeNombre").show();
    $("#ejeNombre").prop("disabled", false);
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
    if (validarkeyup( /^[A-Za-z0-9\s]{5,30}$/,$("#ejeNombre"),$("#sejeNombre"),"El formato permite de 5 a 30 carácteres, Ej:Epistemológico") == 0) {
        muestraMensaje("error",4000,"ERROR!","El nombre del Eje <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
          return false;
        }
  return true;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalNombreEje = $(linea).find("td:eq(0)").text(); 

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#ejeNombre").prop("disabled", false);
    $("#ejeDescripcion").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#ejeId, #ejeNombre"
    ).prop("disabled", true);
  }
  $("#sejeNombre").hide();
  $("#ejeNombre").val($(linea).find("td:eq(0)").text());
  $("#ejeDescripcion").val($(linea).find("td:eq(1)").text());

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
            
            const btnModificar = `<button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-codigo="${item.eje_id}" data-tipo="${item.eje_nombre}" ${!PERMISOS.modificar ? 'disabled' : ''}>Modificar</button>`;
            const btnEliminar = `<button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-codigo="${item.eje_id}" data-tipo="${item.eje_nombre}" ${!PERMISOS.eliminar ? 'disabled' : ''}>Eliminar</button>`;

            $("#resultadoconsulta").append(`
              <tr>
                <td>${item.eje_nombre}</td>
                <td>${item.eje_descripcion}</td>
                <td>
                  ${btnModificar}
                  ${btnEliminar}
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el EJE correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el EJE correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }else if (lee.resultado == "existe") {		
          if (lee.mensaje == 'El EJE colocado YA existe!') {
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }	
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el EJE correctamente!"
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
  $("#ejeId").val("");
  $("#ejeNombre").val("");
}


