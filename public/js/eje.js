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

let originalNombreEje = '';
let originalDescripcionEje = '';

$(document).ready(function () {
  Listar();

    $("#ejeNombre").on("keypress",function(e){
      validarkeypress(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ,#\b\s-]*$/, e);
    });

    $("#ejeNombre").on("keydown keyup",function(){
      $("#sejeNombre").css("color", "");

      let formatoValido = validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s-]{5,30}$/,
        $("#ejeNombre"),
        $("#sejeNombre"),
        "El formato permite de 5 a 30 carácteres, Ej:Epistemológico"
      );
    
    if (formatoValido === 1) {
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append('ejeNombre', $(this).val());
        if ($("#proceso").text() === "MODIFICAR") {
            datos.append('ejeExcluir', originalNombreEje);
            verificarCambios();
        }
        enviaAjax(datos, 'existe');
    }
    validarCamposParaHabilitarBoton();
  });

  $("#ejeDescripcion").on("keyup", function() {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s.,-]{5,100}$/, $(this), $("#sejeDescripcion"), "La descripción debe tener entre 5 y 100 caracteres. Ej:Esta categoría...");
    if ($("#proceso").text() === "MODIFICAR") {
      verificarCambios();
    }
    validarCamposParaHabilitarBoton();
  });

  $("#proceso").on("click", function () {
  let accion = $(this).text();
  if (accion === "MODIFICAR") {
    $("#sejeNombre").show();
    $("#sejeDescripcion").show();
    if (validarenvio()) {
      var datos = new FormData();
      datos.append("accion", "modificar");
      datos.append("ejeNombre", $("#ejeNombre").val());
      datos.append("ejeDescripcion", $("#ejeDescripcion").val());
      datos.append("ejeNombreOriginal", originalNombreEje); 
      enviaAjax(datos);
    }
  } else if (accion === "REGISTRAR") {
    $("#sejeNombre").show();
    $("#sejeDescripcion").show();
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
        /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s-]{5,30}$/, 
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
        
        const swalInstance = Swal.fire({
          title: "¿Está seguro de eliminar este eje?",
          text: "Esta acción no se puede deshacer.",
          icon: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: "Sí, eliminar",
          cancelButtonText: "Cancelar",
          focusConfirm: true,
          allowOutsideClick: false,
          allowEscapeKey: true,
          didOpen: () => {
            const popup = Swal.getPopup();
            popup.addEventListener('keydown', (e) => {
              if (e.key === 'Enter') {
                e.stopPropagation();
                Swal.clickConfirm();
              }
            });
          }
        });
        
        swalInstance.then((result) => {
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
    $("#sejeDescripcion").show();
    $("#ejeNombre").prop("disabled", false);
    validarCamposParaHabilitarBoton();
  });

  $('#modal1').on('hidden.bs.modal', function () {
    $("#proceso").prop("disabled", false);
    $("#sejeNombre").text("").css("color", "");
    $("#sejeDescripcion").text("").css("color", "");
  });

  $('#modal1').on('shown.bs.modal', function () {
    $("#ejeNombre").focus();
  });

  $('#modal1').on('keydown', function(e) {
    if (e.which === 13) {
      if ($("#proceso").text() === "ELIMINAR" && $('.swal2-container').length) {
        e.preventDefault();
        e.stopPropagation();
        return false;
      }
      if (!$("#proceso").prop("disabled")) {
        e.preventDefault();
        $("#proceso").click();
      }
    }
  });
});

function validarenvio() {
  let esValido = true;
  if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s-]{5,30}$/,
    $("#ejeNombre"),
    $("#sejeNombre"),
    "El formato permite de 5 a 30 carácteres, Ej:Epistemológico"
  ) == 0) {
    if(esValido) muestraMensaje("error",4000,"ERROR!","Error! <br/>El formato del nombre es incorrecto");
    esValido = false;
  }
  if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s.,-]{5,100}$/,
    $("#ejeDescripcion"),
    $("#sejeDescripcion"),
    "La descripción debe tener entre 5 y 100 caracteres."
  ) == 0) {
    if(esValido) muestraMensaje("error",4000,"ERROR!","Error! <br/>El formato de la descripción es incorrecto");
    esValido = false;
  }
  return esValido;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalNombreEje = $(linea).find("td:eq(0)").text();
  originalDescripcionEje = $(linea).find("td:eq(1)").text();

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#ejeNombre").prop("disabled", false);
    $("#ejeDescripcion").prop("disabled", false);
    $("#sejeNombre").text("").show();
    $("#sejeDescripcion").text("").show();
    $("#proceso").prop("disabled", true);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#ejeId, #ejeNombre, #ejeDescripcion").prop("disabled", true);
    $("#sejeNombre").hide();
    $("#sejeDescripcion").hide();
  }
  $("#ejeNombre").val($(linea).find("td:eq(0)").text());
  $("#ejeDescripcion").val($(linea).find("td:eq(1)").text());

  $("#modal1").modal("show");
}


function enviaAjax(datos, accion) {
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
        if (accion === 'existe') {
          if (lee.resultado === 'existe') {
            $("#sejeNombre").text(lee.mensaje).css("color", "red");
          } else {
            $("#sejeNombre").text("");
          }
          validarCamposParaHabilitarBoton();
          return;
        }
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            if (item.eje_estado == 1) {
              const btnModificar = `<button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" data-codigo="${item.eje_id}" data-tipo="${item.eje_nombre}" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
              const btnEliminar = `<button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" data-codigo="${item.eje_id}" data-tipo="${item.eje_nombre}" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;

              $("#resultadoconsulta").append(`
                <tr>
                  <td>${item.eje_nombre}</td>
                  <td>${item.eje_descripcion}</td>
                  <td class="text-nowrap">
                    ${btnModificar}
                    ${btnEliminar}
                  </td>
                </tr>
              `);
            }
          });
          crearDT();
        }
      
        else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el EJE correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "MODIFICAR", lee.mensaje);
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
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el EJE correctamente!"
          ) {
            $("#modal1").modal("hide");
            $("#ejeNombre").prop("disabled", false);
            $("#ejeDescripcion").prop("disabled", false);
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

function verificarCambios() {
  const nombreActual = $("#ejeNombre").val();
  const descripcionActual = $("#ejeDescripcion").val();
  
  if (nombreActual === originalNombreEje && descripcionActual === originalDescripcionEje) {
    $("#proceso").prop("disabled", true);
  } else {
    if ($("#sejeNombre").text() === "" || $("#sejeNombre").css("color") !== "rgb(255, 0, 0)") {
      $("#proceso").prop("disabled", false);
    }
  }
}

function limpia() {
  $("#ejeDescripcion").val("");
  $("#ejeNombre").val("");
  $("#sejeNombre").text("");
  $("#sejeDescripcion").text("");
  $("#proceso").prop("disabled", false);
}

function validarCamposParaHabilitarBoton() {
  const procesoTexto = $("#proceso").text();
  
  if (procesoTexto !== "REGISTRAR") {
    return;
  }
  
  const nombre = $("#ejeNombre").val();
  const descripcion = $("#ejeDescripcion").val();
  const nombreExiste = $("#sejeNombre").is(":visible") && $("#sejeNombre").css("color") === "rgb(255, 0, 0)";
  
  const nombreValido = /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s-]{5,30}$/.test(nombre);
  const descripcionValida = /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s.,-]{5,100}$/.test(descripcion);
  
  const habilitarBoton = nombreValido && descripcionValida && !nombreExiste;
  $("#proceso").prop("disabled", !habilitarBoton);
}
