function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
    
    var datosUnion = new FormData();
    datosUnion.append("accion", "consultarAsignacion");
    enviaAjax(datosUnion);
  }
  
  function Cambiar(){
    document.getElementById('toggleTables').addEventListener('click', function() {
    const tablauc = document.getElementById('tablaucContainer');
    const tablaunion = document.getElementById('tablaunionContainer');
  
    if (tablauc.style.display === 'none') {
      tablauc.style.display = 'block';
      tablaunion.style.display = 'none';
      } else {
        tablauc.style.display = 'none';
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
  
//   function validarExiste() {
//     const codigo = $("#codigoSeccion").val();
//     const trayecto = $("#trayectoSeccion").val();
//     if (codigo && trayecto) {
//       var datos = new FormData();
//       datos.append('accion', 'existe');
//       datos.append('codigoSeccion', codigo);
//       datos.append('trayectoSeccion', trayecto);
//       enviaAjax(datos);
//     }
//   }
  
  $(document).ready(function () {
    Listar();
    Cambiar();
    
    destruyeDT("#tablauc");
    crearDT("#tablauc");
  
    destruyeDT("#tablaunion");
    crearDT("#tablaunion");

    destruyeDT("#tabladocente");
    crearDT("#tabladocente");
  
    //////////////////////////////VALIDACIONES/////////////////////////////////////
    
    // $("#codigoSeccion").on("keypress", function(e){
    //   validarkeypress(/^[0-9][0-9]*$/, e);
    // });
    
    // $("#codigoSeccion").on("keyup", function () {
    //   validarkeyup(/^[0-9][0-9]{3}$/, $(this), $("#scodigoSeccion"), "Formato incorrecto, el código debe tener 4 dígitos");
    //   validarExiste();
    // });
    
    // $("#trayectoSeccion").on("keyup change", function () {
    //   validarExiste();
    // });
  
    //////////////////////////////BOTONES/////////////////////////////////////
    
    $(document).on("click", ".asignar-uc", function () {
      $("#modal2").modal("show");
    });

    $("#proceso").on("click", function () {
      if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
          var datos = new FormData();
      datos.append("accion", "registrar");
      datos.append("codigoUC", $("#codigoUC").val()); 
      datos.append("nombreUC", $("#nombreUC").val()); 
      datos.append("independienteUC", $("#independienteUC").val()); 
      datos.append("asistidaUC", $("#asistidaUC").val()); 
      datos.append("trayectoUC", $("#trayectoUC").val()); 
      datos.append("ejeUC", $("#ejeUC").val()); 
      datos.append("areaUC", $("#areaUC").val()); 
      datos.append("creditosUC", $("#creditosUC").val()); 
      datos.append("periodoUC", $("#periodoUC").val()); 
      datos.append("electivaUC", $("#electivaUC").val()); 
      datos.append("academicaUC", $("#academicaUC").val());     
  
          enviaAjax(datos);
        }
      } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "modificar");
          datos.append("idUC", $("#idUC").val());
          datos.append("codigoUC", $("#codigoUC").val()); 
          datos.append("nombreUC", $("#nombreUC").val()); 
          datos.append("independienteUC", $("#independienteUC").val()); 
          datos.append("asistidaUC", $("#asistidaUC").val()); 
          datos.append("trayectoUC", $("#trayectoUC").val()); 
          datos.append("ejeUC", $("#ejeUC").val()); 
          datos.append("areaUC", $("#areaUC").val()); 
          datos.append("creditosUC", $("#creditosUC").val()); 
          datos.append("periodoUC", $("#periodoUC").val()); 
          datos.append("electivaUC", $("#electivaUC").val()); 
          datos.append("academicaUC", $("#academicaUC").val());     
  
          enviaAjax(datos);
        }
      }
      if ($(this).text() == "ELIMINAR") {  
          Swal.fire({
            title: "¿Está seguro de eliminar esta unidad curricular?",
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
              datos.append("idUC", $("#idUC").val());
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
  
    // var trayectoSeleccionado = $("#trayectoSeccion").val();
  
    //   if (trayectoSeleccionado === null || trayectoSeleccionado === "0") {
    //       muestraMensaje(
    //           "error",
    //           4000,
    //           "ERROR!",
    //           "Por favor, seleccione un trayecto! <br/> Recuerde que debe tener alguno registrado!"
    //       );
    //       return false;
    //   }
    return true;
  }
  
  // funcion para pasar de la lista a el formulario
  function pone(pos, accion) {
    linea = $(pos).closest("tr");
  
    if (accion == 0) {
      $("#proceso").text("MODIFICAR");
      $("#idUC").prop("disabled", false);
      $("#codigoUC").prop("disabled", false);
      $("#nombreUC").prop("disabled", false);
      $("#independicenteUC").prop("disabled", false);
      $("#asistidaUC").prop("disabled", false);
      $("#trayectoUC").prop("disabled", false);
      $("#ejeUC").prop("disabled", false);
      $("#areaUC").prop("disabled", false);
      $("#creditosUC").prop("disabled", false);
      $("#periodoUC").prop("disabled", false);
      $("#electivaUC").prop("disabled", false);
      $("#academicaUC").prop("disabled", false);
  
    } else {
      $("#proceso").text("ELIMINAR");
      $(
        "#idUC, #codigoUC, #nombreUC, #independicenteUC, #asistidaUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC, #academicaUC"
      ).prop("disabled", false);
    }
    
    $("#idUC").val($(linea).find("td:eq(0)").text());
    $("#codigoUC").val($(linea).find("td:eq(1)").text());
    $("#nombreUC").val($(linea).find("td:eq(2)").text());
    $("#independienteUC").val($(linea).find("td:eq(3)").text());
    $("#asistidaUC").val($(linea).find("td:eq(4)").text());

    let tra_text = $(linea).find("td:eq(6)").text();
    let tra_id = tra_text.split(" - ")[0]; 
    $("#trayectoUC").val(tra_id);

    let eje_id = $(linea).find("td:eq(7)").data("eje");
    $("#ejeUC").val(eje_id);

    let area_id = $(linea).find("td:eq(8)").data("area");
    $("#areaUC").val(area_id);

    $("#creditosUC").val($(linea).find("td:eq(9)").text());
    $("#periodoUC").val($(linea).find("td:eq(11)").data("periodo"));
    $("#electivaUC").val($(linea).find("td:eq(12)").data("electiva"));
    $("#academicaUC").val($(linea).find("td:eq(10)").text());
    
  
    console.log("Sección ID:", $(linea).find("td:eq(13)").text());

    
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
            destruyeDT("#tablauc");
            $("#resultadoconsulta1").empty();
            $.each(lee.mensaje, function (index, item) {
              
              $("#resultadoconsulta1").append(`
                <tr>
                  <td style="display: none;">${item.uc_id}</td>
                  <td>${item.uc_codigo}</td>
                  <td>${item.uc_nombre}</td>
                  <td>${item.uc_hora_independiente}</td>
                  <td>${item.uc_hora_asistida}</td>
                  <td>${item.uc_hora_independiente + item.uc_hora_asistida}</td>
                  <td data-tra="${item.tra_id}">${item.tra_numero} - ${item.tra_anio}</td>
                  <td data-eje="${item.eje_id}">${item.eje_nombre}</td>
                  <td data-area="${item.area_id}">${item.area_nombre}</td>
                  <td>${item.uc_creditos}</td>
                  <td>${item.uc_hora_academica}</td>
                  <td data-periodo="${item.uc_periodo}">${item.uc_periodo === "anual" ? "Anual" : item.uc_periodo === "1" ? "Fase 1" : item.uc_periodo === "2" ? "Fase 2" : item.uc_periodo}</td>
                  <td data-electiva="${item.uc_electiva}">${item.uc_electiva == "1" ? "Electiva" : "No Electiva"}</td>
                  <td>
                    <button class="btn btn-warning btn-sm asignar-uc" data-id="${item.uc_id}">Asignar</button>
                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-id="${item.uc_id}" data-codigo="${item.uc_codigo}">Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-id="${item.uc_id}" data-codigo="${item.uc_codigo}">Eliminar</button>
                  </td>
                </tr>
              `);
            });
            crearDT("#tablauc");
          } else if (lee.resultado === "consultarAsignacion") {
            destruyeDT("#tabladocente");
            if (datos.has && datos.has("uc_id")) {
                $("#resultadoconsulta3").empty();
                if (lee.mensaje.length > 0) {
                    let hayDocentes = false;
                    $.each(lee.mensaje, function (index2, item2) {
                        if (item2.doc_id) { 
                            hayDocentes = true;
                            $("#resultadoconsulta3").append(`
                                <tr>
                                <td style="display: none;">${item2.uc_id}</td>
                                <td style="display: none;">${item2.doc_id}</td>
                                <td>${item2.doc_nombre ? item2.doc_nombre + ' ' + item2.doc_apellido : ''}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm quitar-docente-uc" data-ucid="${item2.uc_id}" data-docid="${item2.doc_id}">Quitar</button>
                                </td>
                                </tr>
                            `);
                        }
                    });
                }             
                crearDT("#tabladocente");
            } else if (!datos.has || !datos.has("uc_id")) {
                destruyeDT("#tablaunion");
                $("#resultadoconsulta2").empty();
                const ucMap = {};
                lee.mensaje.forEach(item => {
                    if (!ucMap[item.uc_id]) {
                        ucMap[item.uc_id] = item;
                    }
                });
                Object.values(ucMap).forEach(item => {
                    $("#resultadoconsulta2").append(`
                        <tr>
                            <td style="display: none;">${item.uc_id}</td>
                            <td>${item.uc_codigo}</td>
                            <td>${item.uc_nombre}</td>
                            <td>
                                <button 
                                class="btn btn-primary btn-sm ver-docentes" 
                                data-uc="${item.uc_nombre}">
                                Ver docentes
                                </button>
                            </td>
                        </tr>
                    `);
                });
                $("#resultadoconsulta3").empty();
                crearDT("#tablaunion");
                crearDT("#tabladocente");
            }
          } else if (lee.resultado === "asignar") {
            muestraMensaje("info", 4000, "ASIGNAR", lee.mensaje);
            if (lee.mensaje === "Docentes asignados correctamente a las unidades curriculares!") {
              $("#modal2").modal("hide");
              Listar(); 
            }
          } else if (lee.resultado === "quitar") {
            muestraMensaje("info", 4000, "QUITAR", lee.mensaje);
            if (lee.mensaje === "El docente ahora está fuera de esta unidad curricular.") {
                if (datos.has && datos.has("uc_id")) {
                    solicitarDocentesPorUC(datos.get("uc_id"));
                } else {
                    Listar();
                }
            }
          } else if (lee.resultado == "registrar") {
            muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Incluido!<br/>Se registró la unidad de curricular correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          } else if (lee.resultado == "modificar") {
            muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Modificado!<br/>Se modificó la unidad curricular correctamente!"
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
              "Registro Eliminado!<br/>Se eliminó la unidad curricular correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          } else if (lee.resultado == "error") {
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
    $("#seccionId").val("");
    $("#codigoSeccion").val("");
    $("#cantidadSeccion").val("");
    $("#trayectoSeccion").val("");
  }

let carritoDocentes = [];
let ucSeleccionada = null;

function actualizarCarritoDocentes() {
    const ul = document.getElementById("carritoDocentes");
    if (!ul) return;
    ul.innerHTML = "";
    carritoDocentes.forEach((doc, idx) => {
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";
        li.innerHTML = `
            ${doc.nombre}
            <button type="button" class="btn btn-danger btn-sm quitar-docente" data-idx="${idx}">Quitar</button>
        `;
        ul.appendChild(li);
    });
}

$(document).on("click", "#agregarDocente", function () {
    const select = document.getElementById("docenteUC");
    const docenteId = select.value;
    const docenteNombre = select.options[select.selectedIndex]?.text;

    if (!docenteId) {
        alert("Seleccione un docente válido.");
        return;
    }

    if (carritoDocentes.some(doc => doc.id === docenteId)) {
        alert("Este docente ya está seleccionado.");
        return;
    }

    carritoDocentes.push({
        id: docenteId,
        nombre: docenteNombre
    });
    actualizarCarritoDocentes();
});

$(document).on("click", ".quitar-docente", function () {
    const idx = $(this).data("idx");
    carritoDocentes.splice(idx, 1);
    actualizarCarritoDocentes();
});

$(document).on("click", ".asignar-uc", function () {
    carritoDocentes = [];
    actualizarCarritoDocentes();
    ucSeleccionada = $(this).data("id");
});

$(document).on("click", "#asignarDocentes", function () {
    if (carritoDocentes.length === 0) {
        alert("Seleccione al menos un docente!");
        return;
    }
    if (!ucSeleccionada) {
        alert("No se ha seleccionado una unidad curricular.");
        return;
    }

    var datos = new FormData();
    datos.append("accion", "asignar");
    datos.append("docentes", JSON.stringify(carritoDocentes.map(d => d.id)));
    datos.append("ucs", JSON.stringify([ucSeleccionada]));
    
    enviaAjax(datos);
});

$(document).on("click", ".ver-docentes", function () {
  const uc_id = $(this).closest("tr").find("td:eq(0)").text();
  $("#tabladocenteContainer").show();
  $("#modal3").modal("show");
  solicitarDocentesPorUC(uc_id);
});

function solicitarDocentesPorUC(uc_id) {
  var datos = new FormData();
  datos.append("accion", "consultarAsignacion");
  datos.append("uc_id", uc_id);
  enviaAjax(datos, "mostrarDocentesDeUC");
}

$(document).on("click", ".quitar-docente-uc", function () {
  const docId = $(this).data("docid");
  const ucId = $(this).data("ucid");
  Swal.fire({
      title: "¿Está seguro de quitar este docente?",
      text: "Esta acción puede revertirse asignando de nuevo.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, quitar",
      cancelButtonText: "Cancelar",
  }).then((result) => {
      if (result.isConfirmed) {
          var datos = new FormData();
          datos.append("accion", "quitar");
          datos.append("doc_id", docId);
          datos.append("uc_id", ucId);
          enviaAjax(datos);
      }
  });
});

