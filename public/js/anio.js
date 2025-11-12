let duplicacionPendiente = null;

function actualizarTituloModal(accionTexto, detalle = "") {
  const titulo = $("#modalTituloAccion");
  if (!titulo.length) {
    return;
  }
  titulo.text(detalle ? `${accionTexto} Año - ${detalle}` : `${accionTexto} Año`);
}

function formatearTipoAnio(tipo) {
  if (!tipo) {
    return "";
  }
  return tipo.charAt(0).toUpperCase() + tipo.slice(1);
}

function toggleCampoAnioModificar(ocultar) {
  const $anioCol = $("#aniAnio").closest(".col-md-6, .col-md-12");
  const $tipoCol = $("#tipoAnio").closest(".col-md-6, .col-md-12");

  if (ocultar) {
    $anioCol.addClass("d-none");
    $tipoCol.addClass("d-none");
  } else {
    $anioCol.removeClass("d-none");
    $tipoCol.removeClass("d-none");
    if (!$anioCol.hasClass("col-md-6")) {
      $anioCol.removeClass("col-md-12").addClass("col-md-6");
    }
    if (!$tipoCol.hasClass("col-md-6")) {
      $tipoCol.removeClass("col-md-12").addClass("col-md-6");
    }
  }
}

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

  $("#aniAnio").on("change", function () {
    const year = $(this).val();
    if (year) {
      const minDate = `${year}-01-01`;
      const maxDate = `${year}-12-31`;
      $("#aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2")
        .attr("min", minDate)
        .attr("max", maxDate);
    }
  });


  $("#aniAnio").on("keydown keyup", function () {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('aniAnio', $(this).val());
    enviaAjax(datos, 'existe');
  });

  $("#aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").on("change", function () {
    validarFechas();
  });

  $('#modal1').on('hidden.bs.modal', function () {
    limpia();
  });


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
    limpia();
    $("#proceso").text("REGISTRAR");
    var currentYear = new Date().getFullYear();
    $("#aniAnio").val(currentYear).trigger('change');
    $("#aniId").prop("disabled", true);
    $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);

    $('#stipoAnio').text('').hide();
    $("label[for='aniAperturaFase1']").text("Apertura Fase 1");
    $("label[for='aniCierraFase1']").text("Cierre Fase 1");
    $(".row.g-3:has(#aniAperturaFase2), .row.g-3:has(#aniCierraFase2)").show();

    $("#tipoAnio").trigger("change");
    actualizarTituloModal('Registrar');
    $("#modal1").modal("show");

    existeAnio();
  });

  $(document).on("click", ".ver-per-btn", function () {
    const anio = $(this).data("anio");
    const tipo = $(this).data("tipo");
    const datos = new FormData();
    datos.append("accion", "consultar_per");
    datos.append("aniAnio", anio);
    datos.append("aniTipo", tipo);
    enviaAjax(datos);
  });

  $("#tipoAnio").on("change", function () {
    const tipo = $(this).val();
    if (tipo === "intensivo" && !$('#tipoAnio option[value="intensivo"]').is(':disabled')) {
      $("label[for='aniAperturaFase1']").text("Apertura");
      $("label[for='aniCierraFase1']").text("Cierre");
      $(".row.g-3:has(#aniAperturaFase2), .row.g-3:has(#aniCierraFase2)").hide();
    } else {
      $("label[for='aniAperturaFase1']").text("Apertura Fase 1");
      $("label[for='aniCierraFase1']").text("Cierre Fase 1");
      $(".row.g-3:has(#aniAperturaFase2), .row.g-3:has(#aniCierraFase2)").show();
    }
  });

  $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").on("input change", function () {
    verificarCambiosAnio();
  });
});

function existeAnio() {
  var datos = new FormData();
  datos.append('accion', 'existe');
  datos.append('aniAnio', $("#aniAnio").val());
  datos.append('tipoAnio', $("#tipoAnio").val());
  if ($("#proceso").text() === "MODIFICAR") {
    datos.append('anioOriginal', $("#anioOriginal").val());
    datos.append('tipoOriginal', $("#tipoOriginal").val());
  }
  enviaAjax(datos, 'existe');
}

$("#aniAnio").on("change", function () {
  existeAnio();
});
$("#tipoAnio").on("change", function () {
  existeAnio();
});

function validarFechas() {
  let esValido = true;
  const tipoAnio = $("#tipoAnio").val();
  const ap1 = $("#aniAperturaFase1").val();
  const c1 = $("#aniCierraFase1").val();
  const ap2 = $("#aniAperturaFase2").val();
  const c2 = $("#aniCierraFase2").val();

  $("#saniCierraFase1, #saniAperturaFase2, #saniCierraFase2").each(function () {
    if ($(this).css('color') === 'rgb(255, 0, 0)') {
      $(this).text("").hide();
    }
  });

  if (ap1 && c1 && new Date(c1) <= new Date(ap1)) {
    let mensaje = tipoAnio === "intensivo"
      ? "Debe ser posterior a la apertura del año."
      : "Debe ser posterior a la apertura de la fase 1.";
    $("#saniCierraFase1").text(mensaje).css("color", "red").show();
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

  if (tipoAnio === "intensivo") {
    if (!ap1) {
      $("#saniAperturaFase1").text("Debe seleccionar fecha de apertura.").show();
      hayErrorRequerido = true;
    }
    if (!c1) {
      $("#saniCierraFase1").text("Debe seleccionar fecha de cierre.").show();
      hayErrorRequerido = true;
    }
    if (ap1 && c1 && new Date(c1) <= new Date(ap1)) {
      $("#saniCierraFase1").text("Debe ser posterior a la apertura.").css("color", "red").show();
      hayErrorSecuencia = true;
    }
  } else {
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
  }

  if (hayErrorRequerido || hayErrorSecuencia) {
    esValido = false;
    let mensaje = hayErrorRequerido ? "Complete todos los campos requeridos." : "Corrija las fechas marcadas en rojo.";
    muestraMensaje("error", 4000, "ERROR!", mensaje);
  }

  return esValido;
}

let estadoInicialAnio = null;

function obtenerEstadoActualAnio() {
  return {
    aniAnio: $("#aniAnio").val(),
    tipoAnio: $("#tipoAnio").val(),
    aniAperturaFase1: $("#aniAperturaFase1").val(),
    aniCierraFase1: $("#aniCierraFase1").val(),
    aniAperturaFase2: $("#aniAperturaFase2").val(),
    aniCierraFase2: $("#aniCierraFase2").val()
  };
}

function verificarCambiosAnio() {
  if (!estadoInicialAnio) return;
  const actual = obtenerEstadoActualAnio();
  let haCambiado = actual.aniAnio !== estadoInicialAnio.aniAnio ||
    actual.tipoAnio !== estadoInicialAnio.tipoAnio ||
    actual.aniAperturaFase1 !== estadoInicialAnio.aniAperturaFase1 ||
    actual.aniCierraFase1 !== estadoInicialAnio.aniCierraFase1 ||
    actual.aniAperturaFase2 !== estadoInicialAnio.aniAperturaFase2 ||
    actual.aniCierraFase2 !== estadoInicialAnio.aniCierraFase2;
  $("#proceso").prop("disabled", !haCambiado);
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");
  const anioOriginal = $(linea).find("td:eq(1)").text();
  const tipoOriginal = $(linea).find("td:eq(2)").text();
  const tipoFormateado = formatearTipoAnio(tipoOriginal);
  const detalleModal = tipoFormateado ? `${anioOriginal} (${tipoFormateado})` : anioOriginal;

  toggleCampoAnioModificar(false);

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    actualizarTituloModal('Modificar', detalleModal);
    toggleCampoAnioModificar(true);
    $("#aniId").prop("disabled", false);
    $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
    $("#aniAnio, #tipoAnio").prop("disabled", true);
    $('#tipoAnio option[value="regular"], #tipoAnio option[value="intensivo"]').prop('disabled', false);
    estadoInicialAnio = {
      aniAnio: $(linea).find("td:eq(1)").text(),
      tipoAnio: $(linea).find("td:eq(2)").text(),
      aniAperturaFase1: convertirFecha($(linea).find("td:eq(3)").text()),
      aniCierraFase1: convertirFecha($(linea).find("td:eq(4)").text()),
      aniAperturaFase2: convertirFecha($(linea).find("td:eq(5)").text()),
      aniCierraFase2: convertirFecha($(linea).find("td:eq(6)").text())
    };
    setTimeout(verificarCambiosAnio, 200);
  } else {
    $("#proceso").text("ELIMINAR");
    actualizarTituloModal('Eliminar', detalleModal);
    $("#aniId, #aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", true);
  }
  $("#saniAnio").hide();
  $("#aniId").val($(linea).find("td:eq(0)").text());
  $("#aniAnio").val($(linea).find("td:eq(1)").text()).trigger('change');
  $("#tipoAnio").val($(linea).find("td:eq(2)").text());

  if ($(linea).find("td:eq(2)").text() === "intensivo") {
    $("label[for='aniAperturaFase1']").text("Apertura");
    $("label[for='aniCierraFase1']").text("Cierre");
    $(".row.g-3:has(#aniAperturaFase2), .row.g-3:has(#aniCierraFase2)").hide();
  } else {
    $("label[for='aniAperturaFase1']").text("Apertura Fase 1");
    $("label[for='aniCierraFase1']").text("Cierre Fase 1");
    $(".row.g-3:has(#aniAperturaFase2), .row.g-3:has(#aniCierraFase2)").show();
  }

  $("#anioOriginal").val(anioOriginal);
  $("#tipoOriginal").val(tipoOriginal);
  $("#aniAperturaFase1").val(convertirFecha($(linea).find("td:eq(3)").text()));
  $("#aniCierraFase1").val(convertirFecha($(linea).find("td:eq(4)").text()));
  $("#aniAperturaFase2").val(convertirFecha($(linea).find("td:eq(5)").text()));
  $("#aniCierraFase2").val(convertirFecha($(linea).find("td:eq(6)").text()));

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
    beforeSend: function () { },
    timeout: 10000,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);
        if (accion === 'existe') {
          if (lee.resultado === 'existe') {
            $("#saniAnio").text(lee.mensaje).css("color", "red").show();
          } else {
            $("#saniAnio").text("").hide();
          }
          verificarCambiosAnio();
          return;
        }
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
                  <td>${item.ani_tipo === 'intensivo' ? "" : item.ani_apertura_fase2 || ""}</td>
                  <td>${item.ani_tipo === 'intensivo' ? "" : item.ani_cierra_fase2 || ""}</td>
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
          const per1 = lee.data.per_fase1 ? ajustarFechaUTC(lee.data.per_fase1) : "No definido";
          const per2 = lee.data.per_fase2 ? ajustarFechaUTC(lee.data.per_fase2) : "En espera de la apertura de fase 1 del próximo año.";

          function calcularCierre(perApertura, esFase2 = false) {
            if (!perApertura || perApertura === "No definido" || perApertura === "En espera de la apertura de fase 1 del próximo año.") {
              if (esFase2) return "En espera del inicio de per de fase 2";
              return "No definido";
            }
            const fecha = new Date(perApertura.split("/").reverse().join("-"));
            fecha.setDate(fecha.getDate() + 70);
            return fecha.toLocaleDateString('es-ES');
          }
          const perCierre1 = calcularCierre(per1);
          const perCierre2 = calcularCierre(per2, true);

          $("#perApertura1").text(per1);
          $("#perCierre1").text(perCierre1);
          $("#perApertura2").text(per2);
          $("#perCierre2").text(perCierre2);
          $("#modalVerPer").modal("show");
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Incluido!<br/>Se registró el AÑO correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
            duplicacionPendiente = lee.duplicacion || null;
            if (duplicacionPendiente && duplicacionPendiente.secciones > 0) {
              iniciarDuplicacionAutomatica();
            }
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "MODIFICAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Modificado!<br/>Se modificó el AÑO correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (lee.resultado == "existe") {
          muestraMensaje('info', 4000, 'Atención!', lee.mensaje);
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
          if (
            lee.mensaje ==
            "Registro Eliminado!<br/>Se eliminó el AÑO correctamente!"
          ) {
            $("#modal1").modal("hide");
            Listar();
            Verificar();
          }
        }
        else if (lee.resultado === "duplicar_secciones_ok") {
          const tipo = lee.mensaje && lee.mensaje.toLowerCase().includes('no ') ? 'info' : 'success';
          muestraMensaje(tipo, 4000, "DUPLICAR", lee.mensaje);
          if (duplicacionPendiente && duplicacionPendiente.horarios > 0) {
            solicitarDuplicarHorarios();
          } else {
            duplicacionPendiente = null;
          }
        }
        else if (lee.resultado === "duplicar_horarios_ok") {
          const tipo = lee.mensaje && lee.mensaje.toLowerCase().includes('no ') ? 'info' : 'success';
          muestraMensaje(tipo, 4000, "DUPLICAR", lee.mensaje);
          duplicacionPendiente = null;
        }
        else if (lee.resultado == "activar") {
          muestraMensaje("info", 2000, "ESTADO", lee.mensaje);
          Listar();
        }
        else if (lee.resultado == "error") {
          muestraMensaje("error", 10000, "ERROR!!!!", lee.mensaje);
          duplicacionPendiente = null;
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
    complete: function () { },
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
  $("#aniAnio, #tipoAnio, #aniAperturaFase1, #aniCierraFase1, #aniAperturaFase2, #aniCierraFase2").prop("disabled", false);
  toggleCampoAnioModificar(false);
  actualizarTituloModal('Registrar');
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

function iniciarDuplicacionAutomatica() {
  if (!duplicacionPendiente || duplicacionPendiente.secciones <= 0) {
    duplicacionPendiente = null;
    return;
  }
  solicitarDuplicarSecciones();
}

function solicitarDuplicarSecciones() {
  if (!duplicacionPendiente) {
    return;
  }
  const datos = duplicacionPendiente;
  const texto = `Se copiarán ${datos.secciones} secciones del ${datos.anioOrigen} al ${datos.anioDestino}.`;
  Swal.fire({
    title: `¿Duplicar secciones ${datos.anioOrigen} → ${datos.anioDestino}?`,
    html: texto,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, duplicar secciones',
    cancelButtonText: 'No duplicar'
  }).then((result) => {
    if (result.isConfirmed) {
      const datosAjax = new FormData();
      datosAjax.append('accion', 'duplicar_secciones');
      datosAjax.append('anioOrigen', datos.anioOrigen);
      datosAjax.append('anioDestino', datos.anioDestino);
      datosAjax.append('aniTipo', datos.aniTipo);
      datosAjax.append('aniTipoDestino', datos.aniTipo);
      enviaAjax(datosAjax, 'duplicar_secciones');
    } else {
      duplicacionPendiente = null;
    }
  });
}

function solicitarDuplicarHorarios() {
  if (!duplicacionPendiente || duplicacionPendiente.horarios <= 0) {
    duplicacionPendiente = null;
    return;
  }
  const datos = duplicacionPendiente;
  const texto = `Se copiarán ${datos.horarios} bloques de horario del ${datos.anioOrigen}.`;
  Swal.fire({
    title: `¿Duplicar horarios ${datos.anioOrigen} → ${datos.anioDestino}?`,
    html: texto,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, duplicar horarios',
    cancelButtonText: 'No duplicar'
  }).then((result) => {
    if (result.isConfirmed) {
      const datosAjax = new FormData();
      datosAjax.append('accion', 'duplicar_horarios');
      datosAjax.append('anioOrigen', datos.anioOrigen);
      datosAjax.append('anioDestino', datos.anioDestino);
      datosAjax.append('aniTipo', datos.aniTipo);
      datosAjax.append('aniTipoDestino', datos.aniTipo);
      enviaAjax(datosAjax, 'duplicar_horarios');
    } else {
      duplicacionPendiente = null;
    }
  });
}

function actualizarTiposPorAnio() {
  const anioSeleccionado = $("#aniAnio").val();
  let tiposDeshabilitar = [];
  if (window.tiposActivosPorAnio && anioSeleccionado) {
    tiposDeshabilitar = window.tiposActivosPorAnio[anioSeleccionado] || [];
  }
  $('#tipoAnio option').prop('disabled', false);
  tiposDeshabilitar.forEach(tipo => {
    $('#tipoAnio option[value="' + tipo + '"]').prop('disabled', true);
  });
}

function procesaTiposActivosPorAnio(data) {
  window.tiposActivosPorAnio = {};
  data.forEach(item => {
    if (!window.tiposActivosPorAnio[item.ani_anio]) window.tiposActivosPorAnio[item.ani_anio] = [];
    window.tiposActivosPorAnio[item.ani_anio].push(item.ani_tipo);
  });
}

if (lee.resultado === "consultar") {
  procesaTiposActivosPorAnio(lee.mensaje);
}

$("#aniAnio").on("change", function () {
  actualizarTiposPorAnio();
});
$("#registrar").on("click", function () {
  actualizarTiposPorAnio();
});

function ajustarFechaUTC(fechaStr) {
  if (!fechaStr) return "";
  const fecha = new Date(fechaStr);
  fecha.setDate(fecha.getDate() + 1);
  return fecha.toLocaleDateString('es-ES');
}
