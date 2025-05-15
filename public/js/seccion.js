function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
  
  var datosUnion = new FormData();
  datosUnion.append("accion", "consultarUnion");
  enviaAjax(datosUnion);
}

function Cambiar(){
  document.getElementById('toggleTables').addEventListener('click', function() {
  const tablaseccion = document.getElementById('tablaseccionContainer');
  const tablaunion = document.getElementById('tablaunionContainer');

  if (tablaseccion.style.display === 'none') {
    tablaseccion.style.display = 'block';
    tablaunion.style.display = 'none';
    } else {
      tablaseccion.style.display = 'none';
      tablaunion.style.display = 'block';
    }
  });
}

function destruyeDT(selector) {
  // Se destruye el datatable
  if ($.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable().destroy();
  }
}

function crearDT(selector) {
  if (!$.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable({
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
  Cambiar();
  
  destruyeDT("#tablaseccion");
  crearDT("#tablaseccion");

  destruyeDT("#tablaunion");
  crearDT("#tablaunion");

  //////////////////////////////VALIDACIONES/////////////////////////////////////

  $("#trayectoNumero").on("keyup", function () {
    const valor = $(this).val();
    validarkeyup(/^[1-9][0-9]*$/, $(this), $("#strayectoNumero"), "El número debe ser mayor a 0");
    
    if ($(this).val().length > 0 && $("#trayectoAnio").val().length > 0) {
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append('trayectoNumero', $(this).val());
        datos.append('trayectoAnio', $("#trayectoAnio").val());
        enviaAjax(datos);
    }

    if (valor <= 0) {
        $("#strayectoNumero").text("El número debe ser mayor a 0");
    } else {
        $("#strayectoNumero").text("");
    }
    });

    $("#trayectoAnio").on("keyup", function () {
        if ($(this).val().length > 0 && $("#trayectoNumero").val().length > 0) {
            var datos = new FormData();
            datos.append('accion', 'existe');
            datos.append('trayectoNumero', $("#trayectoNumero").val());
            datos.append('trayectoAnio', $(this).val());
            enviaAjax(datos);
        }
    });

  //////////////////////////////BOTONES/////////////////////////////////////
  
  $("#unir").on("click", function () {
  const seccionesSeleccionadas = [];
  $("input[type=checkbox]:checked").each(function () {
      seccionesSeleccionadas.push($(this).val());
  });

  if (seccionesSeleccionadas.length === 0) {
      Swal.fire("Atención", "Debe seleccionar al menos una sección para unir.", "warning");
      return;
  }
  var datos = new FormData();
  datos.append("accion", "unir");
  datos.append("secciones", JSON.stringify(seccionesSeleccionadas));

  enviaAjax(datos);
  });

  $(document).on("click", "#tablaunion .eliminar", function () {
    const grupoId = $(this).data("id");

    Swal.fire({
      title: "¿Está seguro de separar este grupo?",
      text: "Esta acción no se puede deshacer.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, separar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        var datos = new FormData();
        datos.append("accion", "separar");
        datos.append("grupoId", grupoId);

        enviaAjax(datos);
      }
    });
  });

  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("codigoSeccion", $("#codigoSeccion").val());
        datos.append("cantidadSeccion", $("#cantidadSeccion").val());
        datos.append("trayectoSeccion", $("#trayectoSeccion").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("codigoSeccion", $("#codigoSeccion").val());
        datos.append("cantidadSeccion", $("#cantidadSeccion").val());
        datos.append("trayectoSeccion", $("#trayectoSeccion").val());

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
            datos.append("trayectoId", $("#trayectoId").val());
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
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {
  return true;
}

// funcion para pasar de la lista a el formulario
function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#trayectoAnio").prop("disabled", false);
    $("#trayectoNumero").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#trayectoAnio, #trayectoNumero"
    ).prop("disabled", false);
  }
  $("#trayectoId").val($(linea).find("td:eq(0)").text());
  $("#trayectoAnio").val($(linea).find("td:eq(2)").text());
  $("#trayectoNumero").val($(linea).find("td:eq(1)").text());

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
          destruyeDT("#tablaseccion");
          $("#resultadoconsulta1").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta1").append(`
              <tr>
                <td style="display: none;">${item.sec_id}</td>
                <td>${item.sec_codigo}</td>
                <td style="display: none;">${item.tra_id}</td>
                <td>${item.tra_numero} - ${item.tra_anio}</td>
                <td>${item.sec_cantidad}</td>
                <td>
                  <input type="checkbox" id="seccionId" value="${item.sec_id}">
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-tra="${item.tra_id}" data-cantidad="${item.sec_cantidad}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-tra="${item.tra_id}" data-cantidad="${item.sec_cantidad}">Eliminar</button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaseccion");
        } else if (lee.resultado === "consultarUnion") {
          destruyeDT("#tablaunion");
          $("#resultadoconsulta2").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta2").append(`
              <tr>
                <td style="display: none;">${item.gro_id}</td>
                <td>${item.secciones}</td>
                <td>${item.trayecto}</td>
                <td>
                  <button class="btn btn-danger btn-sm eliminar" data-id="${item.gro_id}">Separar</button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaunion");
          ///
        } else if (lee.resultado === "unir") {
          muestraMensaje("info", 4000, "UNIR", lee.mensaje);
          if (lee.mensaje === "Secciones unidas correctamente al grupo.") {
            Listar(); 
          }
        } else if (lee.resultado === "separar") {
          muestraMensaje("info", 4000, "SEPARAR", lee.mensaje);
          if (lee.mensaje === "Secciones separadas!<br/>Se separaron las secciones correctamente!") {
            Listar(); 
          }
        } else if (lee.resultado == "registrar") {
          muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el trayecto correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "modificar") {
          muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el trayecto correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "existe") {
          if ($("#proceso").text() == "REGISTRAR") {
            muestraMensaje("info", 4000, "Atención!", lee.mensaje);
          }
        } else if (lee.resultado == "eliminar") {
          muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el trayecto correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "error") {
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
  
}


