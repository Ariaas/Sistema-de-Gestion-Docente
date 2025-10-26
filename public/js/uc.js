function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}



var __uc_modTracking = false; 
var __uc_userModified = false;
var initialFormState = null; 

function destruyeDT(selector) {
  if ($.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable().destroy();
  }
}

function crearDTModal(selector) {
  if (!$.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable({
      paging: false,
      lengthChange: false,
      searching: true,
      ordering: true,
      info: false,
      autoWidth: false,
      responsive: true,
      language: {
        search: "",
        searchPlaceholder: "Buscar...",
        zeroRecords: "No se encontraron resultados",
      },
      dom: "f" + "<'row'<'col-sm-12'tr>>",
    });
  }
}

function destruyeDTModal(selector) {
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

function verificarRequisitosIniciales() {
  const totalEjes = parseInt($(".main-content").data("total-ejes"), 10);
  const totalAreas = parseInt($(".main-content").data("total-areas"), 10);
  const registrarBtn = $("#registrar");
  const warningSpan = $("#registrar-warning");
  let warningMsg = "";

  if (totalEjes === 0 && totalAreas === 0) {
    warningMsg = "Debe registrar al menos un Eje y un Área.";
  } else if (totalEjes === 0) {
    warningMsg = "Debe registrar al menos un Eje.";
  } else if (totalAreas === 0) {
    warningMsg = "Debe registrar al menos un Área.";
  }

  if (warningMsg) {
    registrarBtn.prop("disabled", true).attr("title", warningMsg);
    warningSpan.text(warningMsg);
  }
}

function checkFormChanges() {
  if ($("#proceso").text() !== "MODIFICAR" || initialFormState === null) {
    return;
  }

  const currentFormState = $("#f").serialize();
  
  if (currentFormState !== initialFormState) {
    $("#proceso").prop('disabled', false);
  } else {
    $("#proceso").prop('disabled', true);
  }
}

$(document).ready(function () {
  Listar();
  verificarRequisitosIniciales();

  destruyeDT("#tablauc");
  crearDT("#tablauc");

  $('#f').on('input change', 'input, select, textarea', checkFormChanges);

  

  $("#proceso").on("click", function () {
    let ejeVal = $("#ejeUC").val();
    if (!ejeVal) {
      let opcionEliminada = $("#ejeUC option:selected");
      if (opcionEliminada.length && opcionEliminada.prop("disabled")) {
        ejeVal = opcionEliminada.val();
        $("#ejeUC").val(ejeVal);
      }
    }

    let areaVal = $("#areaUC").val();
    if (!areaVal) {
      let opcionEliminadaArea = $("#areaUC option:selected");
      if (opcionEliminadaArea.length && opcionEliminadaArea.prop("disabled")) {
        areaVal = opcionEliminadaArea.val();
        $("#areaUC").val(areaVal);
      }
    }

    if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData($("#f")[0]);
        datos.append("accion", "modificar");
        datos.append("codigoUCOriginal", originalCodigoUC);
        
        if ($("#ejeUC option:selected").prop("disabled")) {
          datos.set("ejeUC", $("#ejeUC option:selected").val());
        }
        if ($("#areaUC option:selected").prop("disabled")) {
          datos.set("areaUC", $("#areaUC option:selected").val());
        }
        enviaAjax(datos);
      }
    } else if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData($("#f")[0]);
        datos.append("accion", "registrar");
        if ($("#ejeUC option:selected").prop("disabled")) {
          datos.set("ejeUC", $("#ejeUC option:selected").val());
        }
        if ($("#areaUC option:selected").prop("disabled")) {
          datos.set("areaUC", $("#areaUC option:selected").val());
        }
        enviaAjax(datos);
      }
    }
  });

  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#modal1 .modal-title").text("Registrar Unidad Curricular");
    $("#codigoUC").closest('.col-md-4').show();
    $("#nombreUC").closest('.col-md-6').removeClass('col-md-6').addClass('col-md-4');
    $("#creditosUC").closest('.col-md-6').removeClass('col-md-6').addClass('col-md-4');
    $(
      "#codigoUC, #nombreUC, #independienteUC, #asistidaUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #academicaUC"
    ).prop("disabled", false);
    $("#modal1").modal("show");
    $("span[id^='s']").show();
  });

  $("#codigoUC").on("keyup keydown", function () {
    $("#scodigoUC").css("color", "");
    let formatoValido = validarkeyup(
      /^[A-Za-z0-9-]{5,20}$/,
      $(this),
      $("#scodigoUC"),
      "El código debe tener entre 5 y 20 caracteres."
    );
    if (formatoValido === 1) {
      var datos = new FormData();
      datos.append("accion", "existe");
      datos.append("codigoUC", $(this).val());
      if ($("#proceso").text() === "MODIFICAR") {
        datos.append("codigoExcluir", originalCodigoUC);
      }
      enviaAjax(datos, "existe");
    }
  });

  $("#nombreUC").on("keyup keydown", function () {
    $("#snombreUC").css("color", "");
    validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,100}$/,
      $(this),
      $("#snombreUC"),
      "El nombre debe tener entre 5 y 100 caracteres."
    );
  });

  $("#creditosUC").on("keyup keydown", function () {
    $("#screditosUC").css("color", "");
    validarkeyup(
      /^([2-9]|[1-9][0-9])$/,
      $(this),
      $("#screditosUC"),
      "Debe ser un número entre 2 y 99."
    );
  });

  $("#trayectoUC, #periodoUC").on("change", function () {
    if ($(this).val()) {
      $(this).next("span").text("").hide();
    }
  });

  $("#ejeUC").on("change", function () {
    if ($(this).val()) {
      $("#seje").text("").hide();
    }
  });

  $("#areaUC").on("change", function () {
    if ($(this).val()) {
      $("#sarea").text("").hide();
    }
  });

  $("#modal1").on("hidden.bs.modal", function () {
    $("#proceso").prop("disabled", false);
    $("#scodigoUC").text("");
    initialFormState = null;
  });

  $("#modal1").on("show.bs.modal", function () {
    if ($("#proceso").text().trim() !== "MODIFICAR") {
      $(
        "#scodigoUC, #snombreUC, #screditosUC, #strayectoUC, #seje, #sarea, #speriodoUC"
      )
        .text("")
        .hide();
    }
  });
});

function validarenvio() {
  let esValido = true;

  if (
    validarkeyup(
      /^[A-Za-z0-9-]{5,20}$/,
      $("#codigoUC"),
      $("#scodigoUC"),
      "El código debe tener entre 5 y 20 caracteres."
    ) === 0
  ) {
    if (esValido)
      muestraMensaje(
        "error",
        4000,
        "¡Error!",
        "Error en el formato del código."
      );
    esValido = false;
  }

  if (
    validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,100}$/,
      $("#nombreUC"),
      $("#snombreUC"),
      "El nombre debe tener entre 5 y 100 caracteres."
    ) === 0
  ) {
    if (esValido)
      muestraMensaje(
        "error",
        4000,
        "¡Error!",
        "Error en el formato del nombre."
      );
    esValido = false;
  }

  if (
    validarkeyup(
      /^([2-9]|[1-9][0-9])$/,
      $("#creditosUC"),
      $("#screditosUC"),
      "Las unidades de crédito deben ser entre 2 y 99."
    ) === 0
  ) {
    if (esValido)
      muestraMensaje(
        "error",
        4000,
        "¡Error!",
        "Error en el formato de las unidades de crédito."
      );
    esValido = false;
  }

  if ($("#trayectoUC").val() == "" || $("#trayectoUC").val() == null) {
    $("#strayectoUC").text("Debe seleccionar un trayecto.").show();
    if (esValido)
      muestraMensaje("error", 4000, "¡Error!", "Debe seleccionar un trayecto.");
    esValido = false;
  } else {
    $("#strayectoUC").text("").hide();
  }

  if ($("#ejeUC").val() == "" || $("#ejeUC").val() == null) {
    $("#seje").text("Debe seleccionar un eje.").show();
    if (esValido)
      muestraMensaje("error", 4000, "¡Error!", "Debe seleccionar un eje.");
    esValido = false;
  } else {
    $("#seje").text("").hide();
  }

  if ($("#areaUC").val() == "" || $("#areaUC").val() == null) {
    $("#sarea").text("Debe seleccionar un área.").show();
    if (esValido)
      muestraMensaje("error", 4000, "¡Error!", "Debe seleccionar un área.");
    esValido = false;
  } else {
    $("#sarea").text("").hide();
  }

  if ($("#periodoUC").val() == "" || $("#periodoUC").val() == null) {
    $("#speriodoUC").text("Debe seleccionar un periodo.").show();
    if (esValido)
      muestraMensaje("error", 4000, "¡Error!", "Debe seleccionar un periodo.");
    esValido = false;
  } else {
    $("#speriodoUC").text("").hide();
  }

  

  return esValido;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");
  originalCodigoUC = linea.data("codigo");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    const codigoUC = linea.data("codigo");
    $("#modal1 .modal-title").text("Modificar Unidad Curricular - " + codigoUC);
    $("#codigoUC").closest('.col-md-4').hide();
    $("#nombreUC").closest('.col-md-4').removeClass('col-md-4').addClass('col-md-6');
    $("#creditosUC").closest('.col-md-4').removeClass('col-md-4').addClass('col-md-6');
    $(
      "#nombreUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC"
    ).prop("disabled", false);
    $("#scodigoUC").hide();

    $("#codigoUC").val(linea.data("codigo"));
    $("#nombreUC").val(linea.data("nombre"));
    $("#trayectoUC").val(linea.data("trayecto"));

    const ejeValor = linea.data("eje");
    const $ejeUC = $("#ejeUC");
    $ejeUC.find("option").each(function () {
      if ($(this).text().includes("(eliminado)")) {
        $(this).remove();
      }
    });
    if (
      $ejeUC.find("option[value='" + ejeValor + "']").length === 0 &&
      ejeValor
    ) {
      $ejeUC.prepend(
        $("<option>", {
          value: ejeValor,
          text: ejeValor + " (eliminado)",
          disabled: true,
          selected: true,
        })
      );
    } else {
      $ejeUC.val(ejeValor);
      $("#seje").text("").hide();
    }

    const areaValor = linea.data("area");
    const $areaUC = $("#areaUC");
    $areaUC.find("option").each(function () {
      if ($(this).text().includes("(eliminado)")) {
        $(this).remove();
      }
    });
    if (
      $areaUC.find("option[value='" + areaValor + "']").length === 0 &&
      areaValor
    ) {
      $areaUC.prepend(
        $("<option>", {
          value: areaValor,
          text: areaValor + " (eliminado)",
          disabled: true,
          selected: true,
        })
      );
    } else {
      $areaUC.val(areaValor);
      $("#sarea").text("").hide();
    }

    $("#creditosUC").val(linea.data("creditos"));
    $("#periodoUC").val(linea.data("periodo"));

    $("#codigoUC, #nombreUC, #creditosUC").trigger("keyup");
    $("#trayectoUC, #ejeUC, #areaUC, #periodoUC").trigger("change");
    
    setTimeout(function() {
      initialFormState = $("#f").serialize();
      $("#proceso").prop('disabled', true);
    }, 100);

    $("#modal1").modal("show");
  } else if (accion == 1) {
    // Desactivar directamente sin abrir modal
    var codigoUC = linea.data("codigo");
    var datosVerificacion = new FormData();
    datosVerificacion.append("accion", "verificar_horario");
    datosVerificacion.append("codigoUC", codigoUC);

    $.ajax({
      async: true,
      url: "",
      type: "POST",
      contentType: false,
      data: datosVerificacion,
      processData: false,
      cache: false,
      success: function (respuesta) {
        try {
          var lee = JSON.parse(respuesta);
          let titulo = "¿Está seguro de desactivar esta unidad curricular?";
          let texto =
            "Esta acción puede desactivar la unidad curricular y ocultarla de la lista principal.";

          if (lee.resultado === "en_horario") {
            titulo = "¡Atención!";
            texto =
              "Esta unidad curricular está en un horario. Si la desactiva, se quitará del horario también. ¿Desea continuar?";
          }

          Swal.fire({
            title: titulo,
            text: texto,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              var datos = new FormData();
              datos.append("accion", "eliminar");
              datos.append("codigoUC", codigoUC);
              enviaAjax(datos);
            }
          });
        } catch (e) {
          muestraMensaje(
            "error",
            5000,
            "¡Error en la operación!",
            "No se pudo verificar el estado de la unidad curricular."
          );
        }
      },
      error: function () {
        muestraMensaje(
          "error",
          5000,
          "¡Error de conexión!",
          "No se pudo comunicar con el servidor."
        );
      },
    });
  }
}

function enviaAjax(datos, accion = "") {
  $.ajax({
    async: true,
    url: "",
    type: "POST",
    contentType: false,
    data: datos,
    processData: false,
    cache: false,
    success: function (respuesta) {
      try {
        var lee = JSON.parse(respuesta);

        if (accion === "verificarQuitarDocente") {
          let titulo =
            "¿Está seguro de quitar este docente de la unidad curricular?";
          let texto =
            "Esta acción puede revertirse asignando de nuevo al docente.";
          if (lee.resultado === "en_horario") {
            titulo = "¡Atención!";
            texto =
              "Este docente está asignado a un horario con esta UC. Si lo quita, se eliminará de la planificación. ¿Desea continuar?";
          }
          const docCedula = datos.get("doc_cedula");
          const ucCodigo = datos.get("uc_codigo");
          Swal.fire({
            title: titulo,
            text: texto,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, quitar",
            cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              var datosQuitar = new FormData();
              datosQuitar.append("accion", "quitar");
              datosQuitar.append("doc_cedula", docCedula);
              datosQuitar.append("uc_codigo", ucCodigo);
              enviaAjax(datosQuitar);
            }
          });
          return;
        }

        if (accion === "existe") {
          if (lee.resultado === "existe") {
            $("#scodigoUC").text(lee.mensaje).css("color", "red");
            $("#proceso").prop("disabled", true);
          } else {
            $("#scodigoUC").text("");
            $("#proceso").prop("disabled", false);
          }
          return;
        }
        if (lee.resultado === "consultar") {
          destruyeDT("#tablauc");
          $("#resultadoconsulta1").empty();
          let tabla = "";
          lee.mensaje.forEach((item) => {
           
            let periodoTexto =
              item.uc_periodo === "anual"
                ? "Anual"
                : item.uc_periodo === "1"
                ? "Fase 1"
                : item.uc_periodo === "2"
                ? "Fase 2"
                : item.uc_periodo;
            let trayectoTexto =
              item.uc_trayecto == 0 || item.uc_trayecto === "0"
                ? "Inicial"
                : item.uc_trayecto;
            
            const btnModificar = `<button class="btn btn-icon btn-edit" onclick="pone(this, 0)" title="Modificar" aria-label="Modificar" ${
              !PERMISOS.modificar ? "disabled" : ""
            }><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
            const btnEliminar = `<button class="btn btn-icon btn-delete" onclick="pone(this, 1)" title="Desactivar" aria-label="Desactivar" ${
              !PERMISOS.eliminar ? "disabled" : ""
            }><img src="public/assets/icons/power.svg" alt="Desactivar"></button>`;
            const btnActivar = `<button class="btn btn-icon btn-success btn-activar" title="Activar" aria-label="Activar" data-codigo="${
              item.uc_codigo
            }" ${
              !PERMISOS.eliminar ? "disabled" : ""
            }><img src="public/assets/icons/check.svg" alt="Activar"></button>`;
            
            const btnDetalles = `<button class="btn btn-icon btn-info btn-detalles-uc" title="Ver Detalles" data-codigo="${item.uc_codigo}" data-nombre="${item.uc_nombre}" data-trayecto="${item.uc_trayecto}" data-eje="${item.eje_nombre}" data-area="${item.area_nombre}" data-creditos="${item.uc_creditos}" data-periodo="${periodoTexto}"><img src="public/assets/icons/eye.svg" alt="Ver Detalles"></button>`;
            const estadoTexto =
              item.uc_estado == 1 || item.uc_estado === "1"
                ? "Activa"
                : "Desactivada";
            const estadoBadge =
              item.uc_estado == 1 || item.uc_estado === "1"
                ? '<span class="uc-badge activa">Activa</span>'
                : '<span class="uc-badge desactivada">Desactivada</span>';
            tabla += `
              <tr data-codigo="${item.uc_codigo}" data-nombre="${
              item.uc_nombre
            }" data-trayecto="${item.uc_trayecto}" data-eje="${
              item.eje_nombre
            }" data-area="${item.area_nombre}" data-creditos="${
              item.uc_creditos
            }" data-periodo="${item.uc_periodo}" data-estado="${
              item.uc_estado
            }">
                <td>${item.uc_codigo}</td>
                <td>${item.uc_nombre}</td>
                <td>${trayectoTexto}</td>
                <td>${item.area_nombre}</td>
                <td>${periodoTexto}</td>
                <td>${estadoBadge}</td>
                <td class="text-center">
                  ${btnDetalles}
                  ${btnModificar}
                  ${
                    item.uc_estado == 1 || item.uc_estado === "1"
                      ? btnEliminar
                      : btnActivar
                  }
                </td>
              </tr>`;
          });
          $(document).on("click", ".btn-ver-mas-uc", function () {
            $("#ucVerMasCodigo").text($(this).data("codigo"));
            $("#ucVerMasNombre").text($(this).data("nombre"));
            $("#ucVerMasTrayecto").text($(this).data("trayecto"));
            $("#ucVerMasArea").text($(this).data("area"));
            $("#ucVerMasEje").text($(this).data("eje"));
            $("#ucVerMasCreditos").text($(this).data("creditos"));
            $("#ucVerMasPeriodo").text($(this).data("periodo"));
            
            $("#modalVerMasUC").modal("show");
          });

          $(document).on("click", ".btn-detalles-uc", function () {
            const data = $(this).data();
            $("#detallesUcCodigo").text(data.codigo);
            $("#detallesUcNombre").text(data.nombre);
            $("#detallesUcTrayecto").text(data.trayecto);
            $("#detallesUcArea").text(data.area);
            $("#detallesUcEje").text(data.eje);
            $("#detallesUcCreditos").text(data.creditos);
            $("#detallesUcPeriodo").text(data.periodo);
            
            $("#ucDetallesNombreModal").text(data.nombre);
            
            $("#modalDetallesUC").modal("show");
          });

          $("#resultadoconsulta1").html(tabla);
          crearDT("#tablauc");
        } else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "REGISTRAR", lee.mensaje);
          if (
            lee.mensaje.includes("¡Registro Incluido!") ||
            lee.mensaje.includes(
              "registró la unidad de curricular correctamente"
            )
          ) {
            $("#modal1").modal("hide");
          }
          Listar();
        } else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje.includes("modificó la unidad curricular")) {
            $("#modal1").modal("hide");
          }
          Listar();
        } else if (lee.resultado == "eliminar") {
          
          muestraMensaje("success", 4000, "DESACTIVAR", lee.mensaje);
          if (
            lee.mensaje.includes("eliminó la unidad curricular") ||
            lee.mensaje.includes("desactiv")
          ) {
            $("#modal1").modal("hide");
          }
          Listar();
        } else if (lee.resultado == "activar") {
          muestraMensaje("success", 4000, "ACTIVAR", lee.mensaje);
          Listar();
        } else if (lee.resultado == "error" || lee.resultado == "existe") {
          muestraMensaje("error", 5000, "¡Atención!", lee.mensaje);
        }
      } catch (e) {
        console.error("Error al parsear JSON: ", e, "Respuesta: ", respuesta);
        muestraMensaje(
          "error",
          5000,
          "Error",
          "Respuesta inválida del servidor."
        );
      }
    },
    error: function (solicitud, estado, error) {
      console.log(solicitud, estado, error);
      muestraMensaje(
        "error",
        5000,
        "Error",
        "No se pudo comunicar con el servidor."
      );
    },
  });
}


$(document).on("click", ".btn-activar", function (e) {
  e.preventDefault();
  const codigo = $(this).data("codigo");
  Swal.fire({
    title: "¿Está seguro de activar esta unidad curricular?",
    text: "La unidad curricular pasará a estar activa.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, activar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      var datos = new FormData();
      datos.append("accion", "activar");
      datos.append("codigoUC", codigo);
      enviaAjax(datos);
    }
  });
});

function limpia() {
  $("#ejeUC option").each(function () {
    if ($(this).text().includes("(eliminado)")) {
      $(this).remove();
    }
  });
  $("#areaUC option").each(function () {
    if ($(this).text().includes("(eliminado)")) {
      $(this).remove();
    }
  });

  $("#codigoUC").val("");
  $("#nombreUC").val("");
  $("#creditosUC").val("");
  $("#independienteUC").val("");
  $("#asistidaUC").val("");
  $("#academicaUC").val("");
  $("#trayectoUC").val("");
  $("#ejeUC").val("");
  $("#areaUC").val("");
  $("#periodoUC").val("");
  
  $(
    "#scodigoUC, #snombreUC, #screditosUC, #strayectoUC, #seje, #sarea, #speriodoUC"
  )
    .text("")
    .hide();
}


