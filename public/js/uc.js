function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

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

$(document).ready(function () {
  Listar();
  verificarRequisitosIniciales();

  destruyeDT("#tablauc");
  crearDT("#tablauc");

  $(document).on("click", ".asignar-uc", function () {
    ucSeleccionada = $(this).closest("tr").data("codigo");
    carritoDocentes = [];
    actualizarCarritoDocentes();

    var datos = new FormData();
    datos.append("accion", "cargar_docentes_para_asignar");
    datos.append("codigo", ucSeleccionada);

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
          const lee = JSON.parse(respuesta);
          if (lee.resultado === "ok") {
            const disponibles = lee.disponibles;

            if (disponibles.length === 0) {
              Swal.fire({
                icon: "info",
                title: "No hay docentes disponibles",
                text: "Todos los docentes ya han sido asignados a esta unidad curricular o no hay docentes registrados.",
              });
              return;
            }

            const cuerpoTabla = $("#cuerpoTablaDocentesDisp");

            destruyeDTModal("#tablaDocentesDisponibles");
            cuerpoTabla.empty();

            disponibles.forEach(function (docente) {
              const prefijo = docente.doc_prefijo || "";
              const cedula = docente.doc_cedula || "";
              const nombreCompleto = `${docente.doc_nombre} ${docente.doc_apellido}`;
              cuerpoTabla.append(`
                                <tr>
                                    <td>${prefijo}-${cedula}</td>
                                    <td>${nombreCompleto}</td>
                                    <td><button type="button" class="btn btn-success btn-sm seleccionar-docente-para-uc" data-cedula="${cedula}" data-nombre="${nombreCompleto}" data-prefijo="${prefijo}">Seleccionar</button></td>
                                </tr>`);
            });

            crearDTModal("#tablaDocentesDisponibles");
            $("#modal2").modal("show");

            $("#modal2")
              .off("shown.bs.modal")
              .on("shown.bs.modal", function () {
                $("#tablaDocentesDisponibles")
                  .DataTable()
                  .columns.adjust()
                  .responsive.recalc();
              });
          } else {
            muestraMensaje(
              "error",
              5000,
              "Error",
              lee.mensaje || "No se pudo cargar la lista de docentes."
            );
          }
        } catch (e) {
          muestraMensaje(
            "error",
            5000,
            "Error",
            "Respuesta inválida del servidor."
          );
        }
      },
      error: function () {
        muestraMensaje(
          "error",
          5000,
          "Error de conexión",
          "No se pudo comunicar con el servidor."
        );
      },
    });
  });

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
    } else if ($(this).text() == "ELIMINAR") {
      var codigoUC = $("#codigoUC").val();
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
            let titulo = "¿Está seguro de eliminar esta unidad curricular?";
            let texto = "Esta acción no se puede deshacer.";

            if (lee.resultado === "en_horario") {
              titulo = "¡Atención!";
              texto =
                "Esta unidad curricular está en un horario. Si la elimina, se quitará del horario también. ¿Desea continuar?";
            }

            Swal.fire({
              title: titulo,
              text: texto,
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
                datos.append("codigoUC", codigoUC);
                enviaAjax(datos);
              } else {
                muestraMensaje(
                  "info",
                  2000,
                  "INFORMACIÓN",
                  "La eliminación ha sido cancelada."
                );
                $("#modal1").modal("hide");
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
  });

  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $(
      "#codigoUC, #nombreUC, #independienteUC, #asistidaUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC, #academicaUC"
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
      /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,50}$/,
      $(this),
      $("#snombreUC"),
      "El nombre debe tener entre 5 y 50 caracteres."
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

  $("#trayectoUC, #periodoUC, #electivaUC").on("change", function () {
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
  });

  $("#modal1").on("show.bs.modal", function () {
    if ($("#proceso").text().trim() !== "MODIFICAR") {
      $(
        "#scodigoUC, #snombreUC, #screditosUC, #strayectoUC, #seje, #sarea, #speriodoUC, #selectivaUC"
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
      /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,50}$/,
      $("#nombreUC"),
      $("#snombreUC"),
      "El nombre debe tener entre 5 y 50 caracteres."
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

  if ($("#electivaUC").val() == "" || $("#electivaUC").val() == null) {
    $("#selectivaUC").text("Debe seleccionar si es electiva.").show();
    if (esValido)
      muestraMensaje(
        "error",
        4000,
        "¡Error!",
        "Debe seleccionar si la unidad curricular es electiva."
      );
    esValido = false;
  } else {
    $("#selectivaUC").text("").hide();
  }

  if ($("#electivaUC").val() == "1" && $("#periodoUC").val() == "anual") {
    $("#speriodoUC")
      .text("Una unidad curricular electiva no puede tener periodo anual.")
      .show();
    if (esValido)
      muestraMensaje(
        "error",
        4000,
        "¡Error!",
        "Una unidad curricular electiva no puede tener periodo anual."
      );
    esValido = false;
  }

  return esValido;
}

function pone(pos, accion) {

  linea = $(pos).closest("tr");
  originalCodigoUC = linea.data("codigo");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $(
      "#codigoUC, #nombreUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC"
    ).prop("disabled", false);
  } else {
    $("#proceso").text("ELIMINAR");
    $(
      "#codigoUC, #nombreUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC"
    ).prop("disabled", true);
    $(
      "#scodigoUC, #snombreUC, #screditosUC, #strayectoUC, #seje, #sarea, #speriodoUC, #selectivaUC"
    ).hide();
  }

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
  $("#electivaUC").val(linea.data("electiva"));

  if (accion == 0) {
    $("#codigoUC, #nombreUC, #creditosUC").trigger("keyup");
    $("#trayectoUC, #ejeUC, #areaUC, #periodoUC, #electivaUC").trigger("change");
  }

  $("#modal1").modal("show");
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
            let electivaTexto = item.uc_electiva == 1 ? "Sí" : "No";
            let periodoTexto =
              item.uc_periodo === "anual"
                ? "Anual"
                : item.uc_periodo === "1"
                  ? "Fase 1"
                  : item.uc_periodo === "2"
                    ? "Fase 2"
                    : item.uc_periodo;
            let trayectoTexto = (item.uc_trayecto == 0 || item.uc_trayecto === "0") ? "Inicial" : item.uc_trayecto;
            const btnModificar = `<button class="btn btn-icon btn-edit" onclick="pone(this, 0)" title="Modificar" ${!PERMISOS.modificar ? "disabled" : ""
              }><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
            const btnEliminar = `<button class="btn btn-icon btn-delete" onclick="pone(this, 1)" title="Eliminar" ${!PERMISOS.eliminar ? "disabled" : ""
              }><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
            const btnAsignar = `<button class="btn btn-icon btn-success asignar-uc" title="Asignar" ${!PERMISOS.modificar ? "disabled" : ""
              }><img src="public/assets/icons/user-graduate-solid.svg" alt="Asignar"></button>`;
            const btnDetalles = `<button class="btn btn-icon btn-info btn-detalles-uc" title="Ver Detalles" data-codigo="${item.uc_codigo}" data-nombre="${item.uc_nombre}" data-trayecto="${item.uc_trayecto}" data-eje="${item.eje_nombre}" data-area="${item.area_nombre}" data-creditos="${item.uc_creditos}" data-periodo="${periodoTexto}" data-electiva="${electivaTexto}"><img src="public/assets/icons/eye.svg" alt="Ver Detalles"></button>`;
            tabla += `
              <tr data-codigo="${item.uc_codigo}" data-nombre="${item.uc_nombre}" data-trayecto="${item.uc_trayecto}" data-eje="${item.eje_nombre}" data-area="${item.area_nombre}" data-creditos="${item.uc_creditos}" data-periodo="${item.uc_periodo}" data-electiva="${item.uc_electiva}">
                <td>${item.uc_codigo}</td>
                <td>${item.uc_nombre}</td>
                <td>${trayectoTexto}</td>
                <td>${item.area_nombre}</td>
                <td>${periodoTexto}</td>
                <td class="text-center">
                  ${btnDetalles}
                  ${btnModificar}
                  ${btnEliminar}
                  ${btnAsignar}
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
            $("#ucVerMasElectiva").text($(this).data("electiva"));
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
            $("#detallesUcElectiva").text(data.electiva);
            $("#ucDetallesNombreModal").text(data.nombre);

            var datos = new FormData();
            datos.append("accion", "ver_docentes");
            datos.append("codigo", data.codigo);

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
                  var lista = $("#listaDocentesDetalles");
                  lista.empty();
                  if (
                    lee.resultado === "ok" &&
                    lee.mensaje &&
                    lee.mensaje.length > 0
                  ) {
                    lee.mensaje.forEach(function (docente) {
                      var prefijo = docente.doc_prefijo || "";
                      var cedula = docente.doc_cedula || "";
                      var nombre = docente.doc_nombre || "";
                      var apellido = docente.doc_apellido || "";
                      var li = `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <span>${prefijo}-${cedula}, ${nombre} ${apellido}</span>
                          <button class="btn btn-danger btn-sm quitar-docente-uc" data-uccodigo="${data.codigo}" data-doccedula="${cedula}" title="Quitar Docente">
                            Quitar
                          </button>
                        </li>`;
                      lista.append(li);
                    });
                  } else {
                    lista.append(
                      '<li class="list-group-item">No hay docentes asignados a esta unidad curricular.</li>'
                    );
                  }
                } catch (e) {
                  $("#listaDocentesDetalles")
                    .empty()
                    .append(
                      '<li class="list-group-item">Error al cargar los docentes.</li>'
                    );
                }
                $("#modalDetallesUC").modal("show");
              },
              error: function () {
                $("#listaDocentesDetalles")
                  .empty()
                  .append(
                    '<li class="list-group-item">Error de conexión al buscar docentes.</li>'
                  );
                $("#modalDetallesUC").modal("show");
              },
            });
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
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
          if (lee.mensaje.includes("eliminó la unidad curricular")) {
            $("#modal1").modal("hide");
          }
          Listar();
        } else if (lee.resultado == "asignar") {
          muestraMensaje("success", 4000, "ASIGNACIÓN", lee.mensaje);
          $("#modal2").modal("hide");
          $("#docenteUC").val("");
          $("#carritoDocentes").empty();
          carritoDocentes = [];
          actualizarCarritoDocentes();
          Listar();
        } else if (lee.resultado == "quitar") {
          muestraMensaje("success", 2000, "QUITAR", lee.mensaje);
          if (lee.uc_codigo) {
            const liToRemove = $(
              `#listaDocentesDetalles button[data-doccedula='${datos.get(
                "doc_cedula"
              )}']`
            ).closest("li");
            liToRemove.fadeOut(300, function () {
              $(this).remove();
              if ($("#listaDocentesDetalles li").length === 0) {
                $("#listaDocentesDetalles").append(
                  '<li class="list-group-item">No hay docentes asignados a esta unidad curricular.</li>'
                );
              }
            });
          }
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
  $("#electivaUC").val("");

  $(
    "#scodigoUC, #snombreUC, #screditosUC, #strayectoUC, #seje, #sarea, #speriodoUC, #selectivaUC"
  )
    .text("")
    .hide();
}

let carritoDocentes = [];
let ucSeleccionada = null;

function actualizarCarritoDocentes() {
  const ul = document.getElementById("carritoDocentes");
  if (!ul) return;
  ul.innerHTML = "";
  carritoDocentes.forEach((asignacion, idx) => {
    const texto = `${asignacion.prefijo}-${asignacion.cedula}, ${asignacion.nombre}`;
    const li = document.createElement("li");
    li.className =
      "list-group-item d-flex justify-content-between align-items-center";
    li.innerHTML = `
            <span>${texto}</span>
            <button type="button" class="btn btn-danger btn-sm quitar-docente" data-idx="${idx}">Quitar</button>
        `;
    ul.appendChild(li);
  });
}

$(document).on("click", "#agregarDocente", function () {
  const select = document.getElementById("docenteUC");
  const docenteCedula = select.value;
  const selectedOption = select.options[select.selectedIndex];
  const docenteNombre = selectedOption?.text;
  const docentePrefijo = selectedOption?.getAttribute("data-prefijo") || "";

  if (!docenteCedula) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Seleccione un docente válido.",
    });
    return;
  }

  if (docentesAsignadosUC.includes(docenteCedula)) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Este docente ya está asignado a esta unidad curricular.",
    });
    return;
  }

  if (carritoDocentes.some((doc) => doc.cedula === docenteCedula)) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "Este docente ya está en la lista.",
    });
    return;
  }

  carritoDocentes.push({
    cedula: docenteCedula,
    nombre: docenteNombre.replace(/^.*?,\s*/, ""),
    prefijo: docentePrefijo,
  });
  actualizarCarritoDocentes();
  $("#docenteUC").val("");
});

$(document).on("click", ".seleccionar-docente-para-uc", function () {
  const btn = $(this);
  const docenteCedula = btn.data("cedula").toString();
  const docenteNombre = btn.data("nombre");
  const docentePrefijo = btn.data("prefijo");

  if (carritoDocentes.some((doc) => doc.cedula === docenteCedula)) {
    return;
  }

  carritoDocentes.push({
    cedula: docenteCedula,
    nombre: docenteNombre,
    prefijo: docentePrefijo,
  });
  actualizarCarritoDocentes();
  btn.prop("disabled", true).text("Seleccionado");
});

$(document).on("click", ".quitar-docente", function () {
  const idx = $(this).data("idx");
  const docenteCedula = carritoDocentes[idx].cedula;
  carritoDocentes.splice(idx, 1);
  actualizarCarritoDocentes();

  $(`.seleccionar-docente-para-uc[data-cedula='${docenteCedula}']`)
    .prop("disabled", false)
    .text("Seleccionar");
});

$(document).on("click", "#asignarDocentes", function () {
  if (carritoDocentes.length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "¡Agregue al menos un docente a la lista!",
    });
    return;
  }

  if (!ucSeleccionada) {
    Swal.fire({
      icon: "warning",
      title: "Atención",
      text: "No se ha seleccionado una unidad curricular.",
    });
    return;
  }

  var datos = new FormData();
  datos.append("accion", "asignar");
  datos.append("asignaciones", JSON.stringify(carritoDocentes));
  datos.append("ucs", JSON.stringify([ucSeleccionada]));
  enviaAjax(datos);
});

$(document).on("click", ".ver-docentes", function () {
  const uc_codigo = $(this).closest("tr").data("codigo");
  const uc_nombre = $(this).closest("tr").data("nombre");
  verDocentes(uc_codigo, uc_nombre);
});

function solicitarDocentesPorUC(uc_codigo) {
  var datos = new FormData();
  datos.append("accion", "consultarAsignacion");
  datos.append("uc_codigo", uc_codigo);
  enviaAjax(datos, "mostrarDocentesDeUC");
}

$(document).on("click", ".quitar-docente-uc", function () {
  const docCedula = $(this).data("doccedula");
  const ucCodigo = $(this).data("uccodigo");

  var datosVerificacion = new FormData();
  datosVerificacion.append("accion", "verificar_docente_horario");
  datosVerificacion.append("uc_codigo", ucCodigo);
  datosVerificacion.append("doc_cedula", docCedula);
  enviaAjax(datosVerificacion, "verificarQuitarDocente");
});