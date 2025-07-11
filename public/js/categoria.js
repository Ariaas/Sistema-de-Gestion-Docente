function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function destruyeDT() {
 
  if ($.fn.DataTable.isDataTable("#tablacategoria")) {
    $("#tablacategoria").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablacategoria")) {
    $("#tablacategoria").DataTable({

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

let originalNombreCategoria = '';

$(document).ready(function () {
  Listar();

 $("#categoriaNombre").on("keypress",function(e){
    validarkeypress(/^[A-Za-z0-9-\b]*$/,e);
	});


$("#categoriaNombre").on("keydown keyup", function () {
  validarkeyup(
    /^[A-Za-z0-9\s]{5,30}$/,
    $("#categoriaNombre"),
    $("#scategoriaNombre"),
    "El formato permite de 5 a 30 carácteres. Ej:Instructor"
  );
  if ($("#categoriaNombre").val().length >= 5) {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('categoriaNombre', $("#categoriaNombre").val());
    enviaAjax(datos, 'existe');
  }
});

$("#categoriaDescripcion").on("keypress", function(e){
  validarkeypress(/^[A-Za-z0-9-\b]*$/, e);
});

$("#categoriaDescripcion").on("keydown keyup", function () {
  validarkeyup(
    /^[A-Za-z0-9\s]{5,100}$/,
    $("#categoriaDescripcion"),
    $("#scategoriaDescripcion"),
    "El formato permite de 5 a 100 carácteres. Ej:Esta categoría..."
  );
});

//////////////////////////////BOTONES/////////////////////////////////////

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("categoriaNombre", $("#categoriaNombre").val());
        datos.append("categoriaDescripcion", $("#categoriaDescripcion").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("categoriaNombre", $("#categoriaNombre").val());
        datos.append("categoriaDescripcion", $("#categoriaDescripcion").val());
        datos.append("categoriaNombreOriginal", originalNombreCategoria);
        enviaAjax(datos);
      }
    }
    if ($(this).text() == "ELIMINAR") {
      if (
        validarkeyup(
          /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{5,30}$/,
          $("#categoriaNombre"),
          $("#scategoriaNombre"),
          "Formato incorrecto"
        ) == 0
      ) {
        muestraMensaje(
          "error",
          4000,
          "ERROR!",
          "Seleccionó categoría incorrecta <br/> por favor verifique nuevamente"
        );
      } else {
        
        Swal.fire({
          title: "¿Está seguro de eliminar esta categoría?",
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
            datos.append("categoriaNombre", $("#categoriaNombre").val());
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
    $("#scategoriaNombre").show();
        $(
      "#categoriaDescripcion, #categoriaNombre"
    ).prop("disabled", false);
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {

  if (validarkeyup( /^[A-Za-z0-9\s]{5,30}$/,$("#categoriaNombre"),
  $("#scategoriaNombre"),"El formato permite de 5 a 30 carácteres. Ej:Instructor") == 0) {
        muestraMensaje("error",4000,"ERROR!","El nombre de la Categoría <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
          return false;
  }
  if (validarkeyup( /^[A-Za-z0-9\s]{5,100}$/,$("#categoriaDescripcion"),
  $("#scategoriaDescripcion"),"La descripción debe tener entre 5 y 100 caracteres.") == 0) {
        muestraMensaje("error",4000,"ERROR!","La descripción de la Categoría <br/> No debe estar vacía y debe contener entre 5 a 100 carácteres");
          return false;
  }
  return true;
}


function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalNombreCategoria = $(linea).find("td:eq(0)").text();

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#categoriaNombre").prop("disabled", false);
    $("#categoriaDescripcion").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#categoriaNombre, #categoriaDescripcion").prop("disabled", true);
  }
  $("#scategoriaNombre, #scategoriaDescripcion").hide();
  $("#categoriaNombre").val($(linea).find("td:eq(0)").text());
  $("#categoriaDescripcion").val($(linea).find("td:eq(1)").text());

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
          let tabla = "";
          $.each(lee.mensaje, function (index, item) {
            const btnModificar = `<button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-codigo="${item.cat_id}" data-tipo="${item.cat_nombre}" ${!PERMISOS.modificar ? 'disabled' : ''}>Modificar</button>`;
            const btnEliminar = `<button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-codigo="${item.cat_id}" data-tipo="${item.cat_nombre}" ${!PERMISOS.eliminar ? 'disabled' : ''}>Eliminar</button>`;
            
            tabla += `
              <tr>
                <td>${item.cat_nombre}</td>
                <td>${item.cat_descripcion}</td>
                <td class="text-center">
                  ${btnModificar}
                  ${btnEliminar}
                </td>
              </tr>
            `;
          });
          $("#resultadoconsulta").html(tabla);
          crearDT();
        }
        ////////
        else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró la CATEGORÍA correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó la CATEGORÍA correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        }else if (lee.resultado == "existe") {		
          if (lee.mensaje == 'El Categoría ya existe!') {
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }	
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó la CATEGORÍA correctamente!"
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
  $("#categoriaDescripcion").val("");
  $("#categoriaNombre").val("");
}


