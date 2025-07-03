function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
   
    if ($.fn.DataTable.isDataTable("#tablaanio")) {
      $("#tablaanio").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaanio")) {
      $("#tablaanio").DataTable({
  
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
  
    $("#aniAnio").on("keydown keyup",function(){
      var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('aniAnio', $(this).val());
      enviaAjax(datos, 'existe');
    });

    $("#aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").on("change", function() {
      validarFechas();
    });

    $('#modal1').on('hidden.bs.modal', function () {
        limpia();
    });
  
    //////////////////////////////BOTONES/////////////////////////////////////
  
    $("#proceso").on("click", function () {
      if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "registrar");
          datos.append("aniAnio", $("#aniAnio").val());
          datos.append("tipoAnio", $("#tipoAnio").val());
          datos.append("aniAperturaFase1", $("#aniAperturaFase1").val());
          datos.append("aniCierraFase1", $("#aniCierraFase1").val());
          datos.append("aniAperturaFase2", $("#aniAperturaFase2").val());
          datos.append("aniCierraFase2", $("#aniCierraFase2").val());
  
          enviaAjax(datos);
        }
      } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "modificar");
          datos.append("aniAnio", $("#aniAnio").val());
          datos.append("tipoAnio", $("#tipoAnio").val());
          datos.append("aniId", $("#aniId").val());
          datos.append("aniAperturaFase1", $("#aniAperturaFase1").val());
          datos.append("aniCierraFase1", $("#aniCierraFase1").val());
          datos.append("aniAperturaFase2", $("#aniAperturaFase2").val());
          datos.append("aniCierraFase2", $("#aniCierraFase2").val());
  
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
              datos.append("aniId", $("#aniId").val());
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
      var currentYear = new Date().getFullYear();
      $("#aniAnio").val(currentYear);
      $("#aniId").prop("disabled", true);
      $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
      $("#modal1 .modal-title").text("Formulario de Año Regular/Intensivo");
      $("#modal1").modal("show");
    });

    $('#tablaanio').on('click', '.per-btn', function() {
        const anioId = $(this).data('id');
        const tienePer = $(this).data('tiene-per');

        if (tienePer) {
            const datos = new FormData();
            datos.append("accion", "consultar_per");
            datos.append("aniId", anioId);
            enviaAjax(datos);
        } else {
            Swal.fire({
                title: "¿Crear PER?",
                text: "Se creará un PER para este año. Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, crear",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    const datos = new FormData();
                    datos.append("accion", "registrar_per");
                    datos.append("aniId", anioId);
                    enviaAjax(datos);
                }
            });
        }
    });
    
  });
  
  //////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////
  
  function existeAnio() {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('aniAnio', $("#aniAnio").val());
    datos.append('tipoAnio',$("#tipoAnio").val());
    enviaAjax(datos, 'existe');
}

$("#aniAnio").on("change", function() {
    existeAnio();
});
$("#tipoAnio").on("change", function() {
  existeAnio();
});

  function validarFechas() {
    let esValido = true;
    const ap1 = $("#aniAperturaFase1").val();
    const c1 = $("#aniCierraFase1").val();
    const ap2 = $("#aniAperturaFase2").val();
    const c2 = $("#aniCierraFase2").val();

    if (ap1 && c1 && new Date(c1) <= new Date(ap1)) {
        $("#saniCierraFase1").text("Debe ser posterior a la apertura de la fase 1.").show();
        esValido = false;
    } else {
        $("#saniCierraFase1").text("");
    }

    if (c1 && ap2 && new Date(ap2) <= new Date(c1)) {
        $("#saniAperturaFase2").text("Debe ser posterior al cierre de la fase 1.").show();
        esValido = false;
    } else {
        $("#saniAperturaFase2").text("");
    }

    if (ap2 && c2 && new Date(c2) <= new Date(ap2)) {
        $("#saniCierraFase2").text("Debe ser posterior a la apertura de la fase 2.").show();
        esValido = false;
    } else {
        $("#saniCierraFase2").text("");
    }
    
    return esValido;
  }

  function validarenvio() {
    const ap1 = $("#aniAperturaFase1").val();
    const c1 = $("#aniCierraFase1").val();
    const ap2 = $("#aniAperturaFase2").val();
    const c2 = $("#aniCierraFase2").val();

    if (!ap1) {
        muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar la fecha de apertura de la fase 1!");
        return false;
    }
    if (!c1) {
        muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar la fecha de cierre de la fase 1!");
        return false;
    }
    if (!ap2) {
        muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar la fecha de apertura de la fase 2!");
        return false;
    }
    if (!c2) {
        muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar la fecha de cierre de la fase 2!");
        return false;
    }

    if (!validarFechas()) {
        muestraMensaje("error", 4000, "ERROR!", "Por favor, corrija las fechas!");
        return false;
    }
    if (tipoAnio === null || tipoAnio === "0") {
      muestraMensaje(
          "error",
          4000,
          "ERROR!",
          "Por favor, seleccione un tipo!"
      );
      return false;
  }
    
    return true;
  }
  
  
  function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#aniId").prop("disabled", false);
    $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#aniId, #aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", true);
  }
  $("#saniAnio").hide();
  $("#aniId").val($(linea).find("td:eq(0)").text());
  $("#aniAnio").val($(linea).find("td:eq(1)").text());
  $("#tipoAnio").val($(linea).find("td:eq(2)").text());
  $("#aniAperturaFase1").val(convertirFecha($(linea).find("td:eq(3)").text()));
  $("#aniCierraFase1").val(convertirFecha($(linea).find("td:eq(4)").text()));
  $("#aniAperturaFase2").val(convertirFecha($(linea).find("td:eq(5)").text()));
  $("#aniCierraFase2").val(convertirFecha($(linea).find("td:eq(6)").text()));

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
              let perButton = '';
              if (item.ani_tipo === 'Regular' || item.ani_tipo === 'regular') {
                  const tienePer = item.per_id !== null;
                  const btnClass = tienePer ? 'btn-info' : 'btn-primary';
                  const btnText = tienePer ? 'Ver PER' : 'Crear PER';
                  perButton = `<button class="btn ${btnClass} btn-sm per-btn" data-id="${item.ani_id}" data-tiene-per="${tienePer}">${btnText}</button>`;
              }

              $("#resultadoconsulta").append(`
                <tr>
                  <td style="display: none;">${item.ani_id}</td>
                  <td>${item.ani_anio}</td>
                  <td>${item.ani_tipo}</td>
                  <td>${item.ani_apertura_fase1}</td>
                  <td>${item.ani_cierra_fase1}</td>
                  <td>${item.ani_apertura_fase2}</td>
                  <td>${item.ani_cierra_fase2}</td>
                  <td>
                    <button class="btn btn-${item.ani_activo == 1 ? 'secondary' : 'success'} btn-sm activar-toggle" 
                    data-id="${item.ani_id}" 
                    data-estado="${item.ani_activo}" 
                    disabled>
                    ${item.ani_activo == 1 ? 'Activo' : 'Inactivo'}
                    </button>
                  </td>
                  <td>
                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-codigo="${item.ani_id}" data-tipo="${item.ani_anio}">Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-codigo="${item.ani_id}" data-tipo="${item.ani_anio}">Eliminar</button>
                    ${perButton}
                  </td>
                </tr>
              `);
            });

            $(".activar-toggle").off("click").on("click", function () {
                var id = $(this).data("id");
                var estado = $(this).data("estado");
                var nuevoEstado = estado == 1 ? 0 : 1;
                var datos = new FormData();
                datos.append("accion", "activar");
                datos.append("aniId", id);
                datos.append("aniActivo", nuevoEstado);
                enviaAjax(datos);
              });
            crearDT();
          }
          ////////
          else if (lee.resultado == "registrar") {
            muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Incluido!<br/>Se registró el AÑO correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado == "modificar") {
            muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Modificado!<br/>Se modificó el AÑO correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }else if (lee.resultado == "existe") {		
            muestraMensaje('info', 4000,'Atención!', lee.mensaje);
          }
          else if (lee.resultado == "eliminar") {
            muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Eliminado!<br/>Se eliminó el AÑO correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado == "consultar_per") {
            $("#perApertura1").text(lee.mensaje.per_apertura_fase1);
            $("#perApertura2").text(lee.mensaje.per_apertura_fase2);
            $("#modalVerPer").modal("show");
          }
          else if (lee.resultado == "activar") {
            muestraMensaje("info", 2000, "ESTADO", lee.mensaje);
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
    $("#aniId").val("");
    $("#aniAnio").val("");
    $("#tipoAnio").val("");
    $("#aniAperturaFase1").val("");
    $("#aniCierraFase1").val("");
    $("#aniAperturaFase2").val("");
    $("#aniCierraFase2").val("");
    $("#saniAnio").text("");
    $("#saniAperturaFase1, #saniCierraFase1, #saniAperturaFase2, #saniCierraFase2").text("");
  }

  function convertirFecha(fecha) {
  if (!fecha) return "";
  const partes = fecha.split("/");
  if (partes.length !== 3) return fecha;
  return `${partes[2]}-${partes[1].padStart(2, "0")}-${partes[0].padStart(2, "0")}`;
}

