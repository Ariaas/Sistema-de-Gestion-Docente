let mallaSeleccionadaId = null;
let carritoUCsParaMalla = [];
let carritoCertificadosParaMalla = [];

// Contenedores de las tablas
const tablaPrincipalContainer = $("#tablaMallaPrincipalContainer");
const tablaMallaUCContainer = $("#tablaMallaUCContainer");
const tablaMallaCertContainer = $("#tablaMallaCertContainer");

function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos, "consultar_mallas");
}

function ListarAsignacionesUC() {
  var datos = new FormData();
  datos.append("accion", "consultar_asignaciones_uc");
  enviaAjax(datos, "consultar_asignaciones_uc_response");
}

function ListarAsignacionesCertificados() {
  var datos = new FormData();
  datos.append("accion", "consultar_asignaciones_certificados");
  enviaAjax(datos, "consultar_asignaciones_cert_response");
}


function destruyeDT(selector = "#tablamalla") {
  if ($.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable().destroy();
  }
}

function crearDT(selector = "#tablamalla", config = {}) {
    const defaultConfig = {
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
            infoEmpty: "No hay registros disponibles para mostrar",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
            first: "Primero",
            last: "Último",
            next: "Siguiente",
            previous: "Anterior",
            },
            emptyTable: "No hay datos disponibles en la tabla"
        },
        order: [[1, "asc"]], 
    };

  if (!$.fn.DataTable.isDataTable(selector)) {
    let finalConfig = {...defaultConfig, ...config };
    $(selector).DataTable(finalConfig);
  }
}

function limpiarModalAsignarUC() {
    $("#selectUCParaMalla").val($("#selectUCParaMalla option:first").val());
    carritoUCsParaMalla = [];
    actualizarListaUCsSeleccionadasMalla();
}

function limpiarModalAsignarCertificado() {
    $("#selectCertificadoParaMalla").val($("#selectCertificadoParaMalla option:first").val());
    carritoCertificadosParaMalla = [];
    actualizarListaCertificadosSeleccionadosMalla();
}


$(document).ready(function () {
  Listar(); 

  $('#modalAsignarUC').on('hidden.bs.modal', function () {
    limpiarModalAsignarUC();
  });

  $('#modalAsignarCertificado').on('hidden.bs.modal', function () {
    limpiarModalAsignarCertificado();
  });

  $("#btnVerTablaPrincipal").on("click", function() {
    tablaPrincipalContainer.show();
    tablaMallaUCContainer.hide();
    tablaMallaCertContainer.hide();
    if ($.fn.DataTable.isDataTable("#tablamalla")) {
        // Es bueno ajustar la tabla principal también si su visibilidad pudo haber cambiado o si se modifica el DOM.
        setTimeout(function() {
             $("#tablamalla").DataTable().columns.adjust().responsive.recalc();
        }, 50);
    }
  });

  $("#btnVerAsignacionUC").on("click", function() {
    tablaPrincipalContainer.hide();
    tablaMallaUCContainer.show();
    tablaMallaCertContainer.hide();
    ListarAsignacionesUC(); 
  });

  $("#btnVerAsignacionCert").on("click", function() {
    tablaPrincipalContainer.hide();
    tablaMallaUCContainer.hide();
    tablaMallaCertContainer.show();
    ListarAsignacionesCertificados(); 
  });


  $("#mal_codigo").on("keyup", function () {
    validarkeyup(/^[A-Za-z0-9\s-]{2,10}$/,$(this),$("#smalcodigo"),"El código permite de 2 a 10 caracteres alfanuméricos, espacios o guiones.");
  });
   $("#mal_nombre").on("keyup", function () {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,50}$/,$(this),$("#smalnombre"),"El nombre debe contener entre 5 y 50 caracteres alfanuméricos o espacios.");
  });
  $("#mal_cohorte").on("keyup", function () {
    validarkeyup(/^[A-Za-z0-9\s-]{1,20}$/,$(this),$("#smalcohorte"),"El cohorte permite de 1 a 20 caracteres alfanuméricos, espacios o guiones.");
  });
  $("#mal_descripcion").on("keyup", function () {
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,100}$/, $(this), $("#smaldescripcion"), "La descripción debe contener entre 5 y 100 caracteres.");
  });


  $("#proceso").on("click", function () {
    const accionForm = $("#accion").val();
    if (accionForm === "registrar") {
      if (validarenvio()) {
        var datos = new FormData($("#f")[0]);
        enviaAjax(datos, "registrar_malla");
      }
    } else if (accionForm === "modificar") {
      if (validarenvio()) {
        var datos = new FormData($("#f")[0]);
        enviaAjax(datos, "modificar_malla");
      }
    }
  });

  $("#registrar").on("click", function () {
    limpiaModal1();
    $("#accion").val("registrar");
    $("#modal1Titulo").text("Registrar Malla Curricular");
    $("#proceso").text("REGISTRAR").removeClass("btn-danger").addClass("btn-primary");
    $("#mal_codigo, #mal_nombre, #mal_Anio, #mal_cohorte, #mal_descripcion").prop("disabled", false);
    $("#modal1").modal("show");
  });

  $(document).on("click", ".asignar-uc-malla", function () {
    mallaSeleccionadaId = $(this).data("id");
    $("#mallaIdParaUC").val(mallaSeleccionadaId);
    actualizarListaUCsSeleccionadasMalla(); 
    $("#modalAsignarUC").modal("show");
  });

  $("#btnAgregarUCMalla").on("click", function () {
    const ucSelect = $("#selectUCParaMalla");
    const ucId = ucSelect.val();
    const ucTexto = ucSelect.find("option:selected").text();

    if (!ucId) {
      muestraMensaje("info", 2000, "Atención", "Debe seleccionar una Unidad Curricular.");
      return;
    }
    if (carritoUCsParaMalla.some(uc => uc.id === ucId)) {
      muestraMensaje("info", 2000, "Atención", "Esa Unidad Curricular ya está en la lista.");
      return;
    }
    carritoUCsParaMalla.push({ id: ucId, texto: ucTexto });
    actualizarListaUCsSeleccionadasMalla();
    ucSelect.val(ucSelect.find("option:first").val());
  });

  $(document).on("click", ".quitar-uc-carrito-malla", function () {
    const index = $(this).data("index");
    carritoUCsParaMalla.splice(index, 1);
    actualizarListaUCsSeleccionadasMalla();
  });

  $("#btnGuardarAsignacionUCMalla").on("click", function () {
    if (carritoUCsParaMalla.length === 0) {
      muestraMensaje("info", 2000, "Atención", "Debe agregar al menos una Unidad Curricular a la lista.");
      return;
    }
    var datos = new FormData();
    datos.append("accion", "asignar_uc_malla");
    datos.append("mal_id", $("#mallaIdParaUC").val());
    datos.append("uc_ids", JSON.stringify(carritoUCsParaMalla.map(uc => uc.id)));
    enviaAjax(datos, "asignar_uc_malla_response");
  });

  $(document).on("click", ".asignar-certificado-malla", function () {
    mallaSeleccionadaId = $(this).data("id");
    $("#mallaIdParaCertificado").val(mallaSeleccionadaId);
    actualizarListaCertificadosSeleccionadosMalla();
    $("#modalAsignarCertificado").modal("show");
  });

  $("#btnAgregarCertificadoMalla").on("click", function () {
    const certSelect = $("#selectCertificadoParaMalla");
    const certId = certSelect.val();
    const certTexto = certSelect.find("option:selected").text();

    if (!certId) {
      muestraMensaje("info", 2000, "Atención", "Debe seleccionar un Certificado.");
      return;
    }
    if (carritoCertificadosParaMalla.some(c => c.id === certId)) {
      muestraMensaje("info", 2000, "Atención", "Ese Certificado ya está en la lista.");
      return;
    }
    carritoCertificadosParaMalla.push({ id: certId, texto: certTexto });
    actualizarListaCertificadosSeleccionadosMalla();
    certSelect.val(certSelect.find("option:first").val());
  });

  $(document).on("click", ".quitar-certificado-carrito-malla", function () {
    const index = $(this).data("index");
    carritoCertificadosParaMalla.splice(index, 1);
    actualizarListaCertificadosSeleccionadosMalla();
  });

  $("#btnGuardarAsignacionCertificadoMalla").on("click", function () {
    if (carritoCertificadosParaMalla.length === 0) {
      muestraMensaje("info", 2000, "Atención", "Debe agregar al menos un Certificado a la lista.");
      return;
    }
    var datos = new FormData();
    datos.append("accion", "asignar_certificado_malla");
    datos.append("mal_id", $("#mallaIdParaCertificado").val());
    datos.append("cert_ids", JSON.stringify(carritoCertificadosParaMalla.map(c => c.id)));
    enviaAjax(datos, "asignar_certificado_malla_response");
  });

});

function validarenvio() {
    let anio = $("#mal_Anio").val();
    let isValid = true;

    if (validarkeyup(/^[A-Za-z0-9\s-]{2,10}$/,$("#mal_codigo"),$("#smalcodigo"),"El formato permite de 2 a 10 caracteres.") == 0) {
        isValid = false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,50}$/,$("#mal_nombre"),$("#smalnombre"),"El nombre debe contener entre 5 y 50 caracteres.") == 0) {
         isValid = false;
    }
    if (anio === null || anio === "" || anio === "0") {
         isValid = false;
    }
    if (validarkeyup(/^[A-Za-z0-9\s-]{1,20}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 20 caracteres.") == 0) {
         isValid = false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,100}$/,$("#mal_descripcion"),$("#smaldescripcion"),"La descripción debe contener entre 5 y 100 caracteres.") == 0) {
        isValid = false;
    }
     if(!isValid) {
        muestraMensaje("error",4000,"ERROR DE VALIDACIÓN","Por favor, corrija los campos marcados.");
    }
    return isValid;
}

function pone(pos, accionBtn) {
  $(".form-control").removeClass("is-invalid is-valid");
  $(".form-text").empty();

  linea = $(pos).closest("tr");
  $("#mal_id").val($(linea).find("td:eq(0)").text());
  $("#mal_codigo").val($(linea).find("td:eq(1)").text());
  $("#mal_nombre").val($(linea).find("td:eq(2)").text());
  $("#mal_Anio").val($(linea).find("td:eq(3)").text());
  $("#mal_cohorte").val($(linea).find("td:eq(4)").text());
  $("#mal_descripcion").val($(linea).find("td:eq(5)").text());

  if (accionBtn === 0) {
    $("#accion").val("modificar");
    $("#modal1Titulo").text("Modificar Malla Curricular");
    $("#proceso").text("MODIFICAR").removeClass("btn-danger").addClass("btn-primary");
    $("#mal_codigo, #mal_nombre, #mal_Anio, #mal_cohorte, #mal_descripcion").prop("disabled", false);
    $("#modal1").modal("show");
  } else if (accionBtn === 1) {
    Swal.fire({
      title: "¿Está seguro de eliminar esta malla curricular?",
      html: `<strong>Código:</strong> ${$(linea).find("td:eq(1)").text()}<br><strong>Nombre:</strong> ${$(linea).find("td:eq(2)").text()}<br><br>Esta acción no se puede deshacer.`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        var datos = new FormData();
        datos.append("accion", "eliminar");
        datos.append("mal_id", $(linea).find("td:eq(0)").text());
        enviaAjax(datos, "eliminar_malla");
      }
    });
  }
}

function enviaAjax(datos, origen = "") {
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
        if (origen === "consultar_mallas" && lee.resultado === "consultar") {
          destruyeDT("#tablamalla");
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            let botonesAccion = `
                <td class="acciones-cell">
                    <div class="acciones-fila">
                        <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)'>Modificar</button>
                        <button class="btn btn-info btn-sm asignar-uc-malla" data-id="${item.mal_id}" data-nombre="${item.mal_nombre}">Asignar UC</button>
                    </div>
                    <div class="acciones-fila">
                        <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)'>Eliminar</button>
                        <button class="btn btn-dark btn-sm asignar-certificado-malla" data-id="${item.mal_id}" data-nombre="${item.mal_nombre}">Asignar Cert.</button>
                    </div>
                </td>`;
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.mal_id}</td>
                <td>${item.mal_codigo}</td>
                <td>${item.mal_nombre}</td>
                <td>${item.mal_anio}</td>
                <td>${item.mal_cohorte}</td>
                <td>${item.mal_descripcion}</td>
                ${botonesAccion}
              </tr>`);
          });
          crearDT("#tablamalla");
        } else if ((origen === "registrar_malla" && lee.resultado === "registrar") || (origen === "modificar_malla" && lee.resultado === "modificar")) {
          muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
          if (lee.mensaje.includes("correctamente")) {
            $("#modal1").modal("hide");
            Listar();
          }
        } else if (origen === "eliminar_malla" && lee.resultado === "eliminar") {
           muestraMensaje("success", 4000, "ELIMINADO", lee.mensaje);
           if (lee.mensaje.includes("correctamente")) {
             Listar();
           }
        } else if (lee.resultado === "existe" && origen.includes("_malla")) {
             muestraMensaje('warning', 4000, 'Atención', lee.mensaje);
             $("#mal_codigo").addClass("is-invalid");
             $("#smalcodigo").text(lee.mensaje);
        } else if (origen === "asignar_uc_malla_response" && lee.resultado === 'ok') {
            muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
            $("#modalAsignarUC").modal("hide");
        } else if (origen === "asignar_certificado_malla_response" && lee.resultado === 'ok') {
            muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
            $("#modalAsignarCertificado").modal("hide");
        } 
        else if (origen === "consultar_asignaciones_uc_response" && lee.resultado === 'ok_asignaciones_uc') {
            destruyeDT("#tablaMallaUC");
            $("#resultadoAsignacionesUC").empty(); 
            if(lee.mensaje && lee.mensaje.length > 0){ 
                $.each(lee.mensaje, function(index, item){
                    $("#resultadoAsignacionesUC").append(`
                        <tr>
                            <td style="display: none;">${item.mal_id}</td>
                            <td>${item.mal_codigo}</td>
                            <td>${item.malla_nombre}</td>
                            <td>${item.ucs_asignadas ? item.ucs_asignadas : '<em>Ninguna asignada</em>'}</td>
                        </tr>
                    `);
                });
            }
            crearDT("#tablaMallaUC");
            if ($.fn.DataTable.isDataTable("#tablaMallaUC")) {
                setTimeout(function() { // <--- Cambio aquí
                    $("#tablaMallaUC").DataTable().columns.adjust().responsive.recalc();
                }, 50); // Un pequeño delay
            }
        } else if (origen === "consultar_asignaciones_cert_response" && lee.resultado === 'ok_asignaciones_cert') {
            destruyeDT("#tablaMallaCert");
            $("#resultadoAsignacionesCert").empty(); 
             if(lee.mensaje && lee.mensaje.length > 0){ 
                $.each(lee.mensaje, function(index, item){
                    $("#resultadoAsignacionesCert").append(`
                        <tr>
                            <td style="display: none;">${item.mal_id}</td>
                            <td>${item.mal_codigo}</td>
                            <td>${item.malla_nombre}</td>
                            <td>${item.certificados_asignados ? item.certificados_asignados : '<em>Ninguno asignado</em>'}</td>
                        </tr>
                    `);
                });
            }
            crearDT("#tablaMallaCert");
            if ($.fn.DataTable.isDataTable("#tablaMallaCert")) {
                 setTimeout(function() { // <--- Cambio aquí
                    $("#tablaMallaCert").DataTable().columns.adjust().responsive.recalc();
                }, 50); // Un pequeño delay
            }
        }
        else if (lee.resultado === 'error') { 
          muestraMensaje("error", 10000, "ERROR", lee.mensaje);
        }

      } catch (e) {
        console.error("Error en análisis JSON:", e, "Respuesta:", respuesta);
        // Mostrar parte de la respuesta si no es JSON válido
        let errorMsg = "No se pudo procesar la respuesta del servidor: " + e.message;
        if (typeof respuesta === 'string' && respuesta.length > 0 && respuesta.length < 500) { // Mostrar solo si no es demasiado larga
            errorMsg += "<br><pre style='text-align:left; max-height: 100px; overflow-y:auto;'>" + respuesta.replace(/</g, "&lt;").replace(/>/g, "&gt;") + "</pre>";
        }
        muestraMensaje("error", 10000, "Error de Comunicación", errorMsg);
      }
    },
    error: function (request, status, err) {
      if (status == "timeout") {
        muestraMensaje("error", 5000, "Error de Comunicación", "Servidor ocupado, intente de nuevo.");
      } else {
        muestraMensaje("error", 5000, "Error de Comunicación", "ERROR: " + request.responseText + "<br>Status: " + status + "<br>Error: " + err);
      }
    },
    complete: function () { },
  });
}

function limpiaModal1() {
  $("#f")[0].reset();
  $("#mal_id").val("");
  $(".form-control").removeClass("is-invalid is-valid");
  $(".form-text").empty();
  $("#mal_Anio").val("");
}

function actualizarListaUCsSeleccionadasMalla() {
  const listaElement = $("#listaUCsSeleccionadasMalla");
  listaElement.empty();
  if (carritoUCsParaMalla.length === 0) {
    listaElement.append('<li class="list-group-item">No hay UCs seleccionadas.</li>');
    return;
  }
  carritoUCsParaMalla.forEach((uc, index) => {
    listaElement.append(`
      <li class="list-group-item d-flex justify-content-between align-items-center">
        ${uc.texto}
        <button type="button" class="btn btn-danger btn-sm quitar-uc-carrito-malla" data-index="${index}">Quitar</button>
      </li>
    `);
  });
}

function actualizarListaCertificadosSeleccionadosMalla() {
  const listaElement = $("#listaCertificadosSeleccionadosMalla");
  listaElement.empty();
  if (carritoCertificadosParaMalla.length === 0) {
    listaElement.append('<li class="list-group-item">No hay Certificados seleccionados.</li>');
    return;
  }
  carritoCertificadosParaMalla.forEach((cert, index) => {
    listaElement.append(`
      <li class="list-group-item d-flex justify-content-between align-items-center">
        ${cert.texto}
        <button type="button" class="btn btn-danger btn-sm quitar-certificado-carrito-malla" data-index="${index}">Quitar</button>
      </li>
    `);
  });
}