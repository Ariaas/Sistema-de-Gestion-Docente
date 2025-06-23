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

function validarExiste() {
  const codigo = $("#codigoSeccion").val();
  const trayecto = $("#trayectoSeccion").val();
  const nombre = $("#nombreSeccion").val();
  if (codigo && trayecto) {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('codigoSeccion', codigo);
    datos.append('trayectoSeccion', trayecto);
    datos.append('nombreSeccion', nombre);
    enviaAjax(datos);
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
  
  $("#codigoSeccion").on(" keypress", function(e){
    validarkeypress(/^[0-9][0-9]*$/, e);
  });
  
  $("#codigoSeccion").on(" keydown keyup", function () {
    validarkeyup(/^[0-9][0-9]{2}$/, $(this), $("#scodigoSeccion"), "Formato incorrecto, el código debe tener 3 dígitos Ej: 310");
    validarExiste();
  });
  
  $("#trayectoSeccion").on("keyup change", function () {
    validarExiste();
  });

  $("#nombreSeccion").on(" keypress", function(e){
    validarkeypress(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
  });
  
  $("#nombreSeccion").on(" keydown keyup", function () {
    validarkeyup(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{2}$/, $(this), $("#snombreSeccion"), "Formato incorrecto, el nombre debe tener 2 dígitos Ej: IN");
    validarExiste();
  });

  //////////////////////////////BOTONES/////////////////////////////////////
  let seccionesAUnir = [];

  $("#unir").on("click", function () {
  seccionesAUnir = [];
  $("input[type=checkbox]:checked").each(function () {
    seccionesAUnir.push($(this).val());
  });

  if (seccionesAUnir.length <= 1) {
    Swal.fire("Atención", "Debe seleccionar al menos DOS secciones para unir.", "warning");
    return;
  }

  $("#nombreGrupo").val("");
  $("#modalGrupo").modal("show");
  });

  $("#guardarGrupo").on("click", function () {
    const nombreGrupo = $("#nombreGrupo").val().trim();
    if (!nombreGrupo) {
      Swal.fire("Atención", "Debe ingresar un nombre para el grupo.", "warning");
      return;
    }
    if (seccionesAUnir.length <= 1) {
      Swal.fire("Atención", "Debe seleccionar al menos DOS secciones para unir.", "warning");
      return;
    }

    var datos = new FormData();
    datos.append("accion", "unir");
    datos.append("secciones", JSON.stringify(seccionesAUnir));
    datos.append("nombreGrupo", nombreGrupo);

    enviaAjax(datos);
    $("#modalGrupo").modal("hide");
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
        datos.append("cohorteSeccion", $("#cohorteSeccion").val());
        datos.append("nombreSeccion", $("#nombreSeccion").val());

        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("seccionId", $("#seccionId").val());
        datos.append("codigoSeccion", $("#codigoSeccion").val());
        datos.append("cantidadSeccion", $("#cantidadSeccion").val());
        datos.append("trayectoSeccion", $("#trayectoSeccion").val());
        datos.append("cohorteSeccion", $("#cohorteSeccion").val());
        datos.append("nombreSeccion", $("#nombreSeccion").val());

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
  });

  
});

//////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////

function validarenvio() {

  var trayectoSeleccionado = $("#trayectoSeccion").val();

    if (trayectoSeleccionado === null || trayectoSeleccionado === "0") {
        muestraMensaje(
            "error",
            4000,
            "ERROR!",
            "Por favor, seleccione un trayecto! <br/> Recuerde que debe tener alguno registrado!"
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
    $("#trayectoSeccion").prop("disabled", false);

  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#seccionId, #codigoSeccion, #cantidadSeccion, #trayectoSeccion"
    ).prop("disabled", false);
  }
  
  $("#seccionId").val($(linea).find("td:eq(0)").text());
  $("#nombreSeccion").val($(linea).find("td:eq(1)").text());
  $("#codigoSeccion").val($(linea).find("td:eq(2)").text());
  
  $("#cohorteSeccion").val($(linea).find("td:eq(3)").data("coh"));

  let tra_id = $(linea).find("td:eq(4)").data("tra");
  $("#trayectoSeccion").val(tra_id);
  
  $("#cantidadSeccion").val($(linea).find("td:eq(5)").text());
  
  console.log("Sección ID:", $(linea).find("td:eq(4)").text());
  console.log("Trayecto ID:", tra_id);
  
  $("#scodigoSeccion").hide();
  $("#scantidadSeccion").hide();
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
                <td>${item.sec_nombre}</td>
                <td>${item.sec_codigo}</td>
                <td data-coh="${item.coh_id}">${item.coh_numero}</td>
                <td data-tra="${item.tra_id}">${item.tra_numero} - ${item.ani_anio}</td>
                <td>${item.sec_cantidad}</td>
                <td>
                  <input type="checkbox" id="idSeccion" value="${item.sec_id}">
                  <button class="btn btn-success btn-sm prosecusion" data-id="${item.sec_id}">Prosecusión</button>
                  <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-cantidad="${item.sec_cantidad}">Modificar</button>
                  <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-id="${item.sec_id}" data-codigo="${item.sec_codigo}" data-cantidad="${item.sec_cantidad}">Eliminar</button>
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
                <td>${item.sec_grupo}</td>
                <td>${item.secciones}</td>
                <td>${item.trayecto}</td>
                <td>${item.suma_cantidades}</td>
                <td>
                  <button class="btn btn-danger btn-sm eliminar" data-id="${item.sec_grupo}">Separar</button>
                </td>
              </tr>
            `);
          });
          crearDT("#tablaunion");
          ///
        } 
        else if (lee.resultado === "unir") {
          muestraMensaje("info", 4000, "UNIR", lee.mensaje);
          Listar();
        } 
        else if (lee.resultado === "separar") {
          muestraMensaje("info", 4000, "SEPARAR", lee.mensaje);
          Listar();
        }
        else if (lee.resultado == "registrar") {
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
          if ($("#proceso").text() == "REGISTRAR") {
            muestraMensaje("info", 4000, "Atención!", lee.mensaje);
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
        } else if (lee.resultado === "prosecusion") {
          muestraMensaje("info", 4000, "PROSECUSIÓN", lee.mensaje);
          Listar();
          
        }else if (lee.resultado === "obtenerSeccionDestinoProsecusion") {
          if (lee.mensaje && lee.mensaje.sec_id) {
            Swal.fire({
              title: "Confirmar prosecusión",
              text: `¿Desea hacer prosecusión a la sección ${lee.mensaje.sec_codigo}${lee.mensaje.coh_numero} (${lee.mensaje.tra_numero} - ${lee.mensaje.ani_anio})?`,
              icon: "question",
              showCancelButton: true,
              confirmButtonText: "Sí, realizar",
              cancelButtonText: "Cancelar"
            }).then((result) => {
              if (result.isConfirmed) {
                var datos2 = new FormData();
                datos2.append("accion", "prosecusion");
                datos2.append("seccionOrigenId", seccionOrigenIdProsecusion);
                datos2.append("seccionDestinoId", lee.mensaje.sec_id);
                enviaAjax(datos2);
              }
            });
          } else {
            Swal.fire("Atención", "No existe una sección destino válida para prosecusión.", "warning");
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
  $("#codigoSeccion").val("");
  $("#cantidadSeccion").val("");
  $("#trayectoSeccion").val("");
  $("#cohorteSeccion").val("");
  $("#nombreSeccion").val("");
}

$(document).on("click", ".prosecusion", function () {
  seccionOrigenIdProsecusion = $(this).data("id");
  var datos = new FormData();
  datos.append("accion", "obtenerSeccionDestinoProsecusion");
  datos.append("seccionOrigenId", seccionOrigenIdProsecusion);
  enviaAjax(datos);
});
