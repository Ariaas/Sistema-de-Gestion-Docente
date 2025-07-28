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
    Verificar();

    $("#aniAnio").on("change", function() {
      const year = $(this).val();
      if (year) {
        const minDate = `${year}-01-01`;
        const maxDate = `${year}-12-31`;
        $("#aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2")
          .attr("min", minDate)
          .attr("max", maxDate);
      }
    });
    
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
          datos.append("aniAperturaFase1", $("#aniAperturaFase1").val());
          datos.append("aniCierraFase1", $("#aniCierraFase1").val());
          datos.append("aniAperturaFase2", $("#aniAperturaFase2").val());
          datos.append("aniCierraFase2", $("#aniCierraFase2").val());
          datos.append("anioOriginal", $("#anioOriginal").val());
          datos.append("tipoOriginal", $("#tipoOriginal").val());

          enviaAjax(datos);
        }
      }
      if ($(this).text() == "ELIMINAR") {
       
          Swal.fire({
            title: "¿Está seguro de eliminar este año?",
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
              datos.append("aniAnio", $("#aniAnio").val());
              datos.append("tipoAnio", $("#tipoAnio").val());
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
      if ($(this).is(':disabled')) {
          const warningText = $("#registrar-warning").text();
          Swal.fire({
              icon: 'error',
              title: 'Acción no permitida',
              text: warningText || 'No se puede registrar un nuevo año en este momento.'
          });
          return;
      }
      limpia();
      $("#proceso").text("REGISTRAR");
      var currentYear = new Date().getFullYear();
      $("#aniAnio").val(currentYear).trigger('change');
      $("#aniId").prop("disabled", true);
      $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
      
      const tiposActivos = window.tiposActivos || [];
      $('#tipoAnio option').prop('disabled', false); 
      if (tiposActivos.includes('regular')) {
          $('#tipoAnio option[value="regular"]').prop('disabled', true);
      }
      if (tiposActivos.includes('intensivo')) {
          $('#tipoAnio option[value="intensivo"]').prop('disabled', true);
      }

      $("#modal1 .modal-title").text("Formulario de Año Regular/Intensivo");
      $("#modal1").modal("show");
    });

    $(document).on("click", ".ver-per-btn", function() {
        const anio = $(this).data("anio");
        const tipo = $(this).data("tipo");
        const datos = new FormData();
        datos.append("accion", "consultar_per");
        datos.append("aniAnio", anio);
        datos.append("aniTipo", tipo);
        enviaAjax(datos);
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

    $("#saniCierraFase1, #saniAperturaFase2, #saniCierraFase2").each(function() {
        if ($(this).css('color') === 'rgb(255, 0, 0)') {
            $(this).text("").hide();
        }
    });

    if (ap1 && c1 && new Date(c1) <= new Date(ap1)) {
        $("#saniCierraFase1").text("Debe ser posterior a la apertura de la fase 1.").css("color", "red").show();
        esValido = false;
    }

    if (c1 && ap2 && new Date(ap2) <= new Date(c1)) {
        $("#saniAperturaFase2").text("Debe ser posterior al cierre de la fase 1.").css("color", "red").show();
        esValido = false;
    }

    if (ap2 && c2 && new Date(c2) <= new Date(ap2)) {
        $("#saniCierraFase2").text("Debe ser posterior a la apertura de la fase 2.").css("color", "red").show();
        esValido = false;
    }
    
    return esValido;
  }

  function validarenvio() {
    let esValido = true;
    let hayErrorRequerido = false;
    let hayErrorSecuencia = false;

    const tipoAnio = $("#tipoAnio").val();
    const ap1 = $("#aniAperturaFase1").val();
    const c1 = $("#aniCierraFase1").val();
    const ap2 = $("#aniAperturaFase2").val();
    const c2 = $("#aniCierraFase2").val();

    $("#stipoAnio, #saniAperturaFase1, #saniCierraFase1, #saniAperturaFase2, #saniCierraFase2").text("").css("color", "").hide();

    if (!tipoAnio || tipoAnio === "0") {
        $("#stipoAnio").text("Debe seleccionar un tipo.").show();
        hayErrorRequerido = true;
    }
    if (!ap1) {
        $("#saniAperturaFase1").text("Debe seleccionar una fecha de apertura fase 1.").show();
        hayErrorRequerido = true;
    }
    if (!c1) {
        $("#saniCierraFase1").text("Debe seleccionar una fecha de cierre fase 1.").show();
        hayErrorRequerido = true;
    }
    if (!ap2) {
        $("#saniAperturaFase2").text("Debe seleccionar una fecha de apertura fase 2.").show();
        hayErrorRequerido = true;
    }
    if (!c2) {
        $("#saniCierraFase2").text("Debe seleccionar una fecha de cierre fase 2.").show();
        hayErrorRequerido = true;
    }

    if (ap1 && c1 && new Date(c1) <= new Date(ap1)) {
        $("#saniCierraFase1").text("Debe ser posterior a la apertura de la fase 1.").css("color", "red").show();
        hayErrorSecuencia = true;
    }
    if (c1 && ap2 && new Date(ap2) <= new Date(c1)) {
        $("#saniAperturaFase2").text("Debe ser posterior al cierre de la fase 1.").css("color", "red").show();
        hayErrorSecuencia = true;
    }
    if (ap2 && c2 && new Date(c2) <= new Date(ap2)) {
        $("#saniCierraFase2").text("Debe ser posterior a la apertura de la fase 2.").css("color", "red").show();
        hayErrorSecuencia = true;
    }

    if (hayErrorRequerido || hayErrorSecuencia) {
        esValido = false;
        let mensaje = hayErrorRequerido ? "Complete todas las fechas requeridas." : "Corrija las fechas marcadas en rojo.";
        muestraMensaje("error", 4000, "ERROR!", mensaje);
    }
    
    return esValido;
  }
  
  
  function pone(pos, accion) {
  linea = $(pos).closest("tr");
  const anioOriginal = $(linea).find("td:eq(1)").text();
  const tipoOriginal = $(linea).find("td:eq(2)").text();

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#aniId").prop("disabled", false);
    $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
    $('#tipoAnio option[value="regular"], #tipoAnio option[value="intensivo"]').prop('disabled', false);
  } else {
    $("#proceso").text("ELIMINAR");
    $("#aniId, #aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", true);
  }
  $("#saniAnio").hide();
  $("#aniId").val($(linea).find("td:eq(0)").text());
  $("#aniAnio").val($(linea).find("td:eq(1)").text()).trigger('change');
  $("#tipoAnio").val($(linea).find("td:eq(2)").text());
  $("#anioOriginal").val(anioOriginal);
  $("#tipoOriginal").val(tipoOriginal);
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
                  <td class="text-nowrap">
                    ${item.ani_tipo !== 'intensivo' ? `<button class="btn btn-icon btn-info ver-per-btn" title="Ver PER" data-anio="${item.ani_anio}" data-tipo="${item.ani_tipo}"><img src="public/assets/icons/eye.svg" alt="Ver PER"></button>` : ''}
                    <button class="btn btn-icon btn-edit" onclick='pone(this,0)' title="Modificar" data-codigo="${item.ani_id}" data-tipo="${item.ani_anio}" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                    <button class="btn btn-icon btn-delete" onclick='pone(this,1)' title="Eliminar" data-codigo="${item.ani_id}" data-tipo="${item.ani_anio}" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
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
                datos.append("aniActivo", nuevoEstado);
                enviaAjax(datos);
              });
            crearDT();
            Verificar();
          }
          else if (lee.resultado === "condiciones_registro") {
            window.tiposActivos = lee.tipos_activos || [];
            let warning = "";
            if (!lee.malla_activa) {
                warning = "Debe haber una malla curricular activa.";
            } else if (window.tiposActivos.includes('regular') && window.tiposActivos.includes('intensivo')) {
                warning = "Ya existe un año regular y uno intensivo activos.";
            }

            if (warning) {
                $("#registrar").prop("disabled", true);
                $("#registrar-warning").text(warning);
            } else {
                $("#registrar").prop("disabled", false);
                $("#registrar-warning").text("");
            }
          }
          else if (lee.resultado === "per_consultado") {
            const per1 = lee.data.per_fase1 ? new Date(lee.data.per_fase1).toLocaleDateString('es-ES') : "No definido";
            const per2 = lee.data.per_fase2 ? new Date(lee.data.per_fase2).toLocaleDateString('es-ES') : "En espera de la apertura de fase 1 del próximo año.";
            $("#perApertura1").text(per1);
            $("#perApertura2").text(per2);
            $("#modalVerPer").modal("show");
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
              Verificar();
            }
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

function Verificar() {
    var datos = new FormData();
    datos.append("accion", "verificar_condiciones_registro");
    enviaAjax(datos);
}

