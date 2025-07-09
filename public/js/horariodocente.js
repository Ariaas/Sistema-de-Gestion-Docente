function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
    if ($.fn.DataTable.isDataTable("#tablaHorarioDocente")) {
      $("#tablaHorarioDocente").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaHorarioDocente")) {
      $("#tablaHorarioDocente").DataTable({
        responsive: true,
        order: [[1, "asc"]],
      });
    }
  }
  
  function enviaAjax(datos) {
    $.ajax({
      async: true, url: "", type: "POST", contentType: false,
      data: datos, processData: false, cache: false,
      success: function (respuesta) {
        try {
          var lee = JSON.parse(respuesta);
          if (lee.resultado === "consultar") {
            destruyeDT();
            $("#resultadoconsulta").empty();
            $.each(lee.mensaje, function (index, item) {
              $("#resultadoconsulta").append(`
                <tr>
                  <td style="display: none;">${item.doc_cedula}</td>
                  <td>${item.doc_nombre_completo}</td>
                  <td>${item.hdo_lapso}</td>
                  <td>${item.hdo_tipoactividad}</td>
                  <td>${item.hdo_descripcion}</td>
                  <td>${item.hdo_dependencia}</td>
                  <td>${item.hdo_horas}</td>
                  <td style="display: none;">${item.hdo_observacion}</td>
                  <td>
                    <button class="btn btn-info btn-sm" onclick='poneVerHorario(this)'>Ver Horario</button>
                    <button class="btn btn-warning btn-sm" onclick='pone(this,0)'>Modificar</button>
                    <button class="btn btn-danger btn-sm" onclick='pone(this,1)'>Eliminar</button>
                  </td>
                </tr>`);
            });
            crearDT();
          } else if (["registrar", "modificar", "eliminar", "existe"].includes(lee.resultado)) {
              let titulo = lee.resultado.charAt(0).toUpperCase() + lee.resultado.slice(1);
              let tipo = lee.resultado === "existe" ? "warning" : "success";
              if(lee.mensaje.toLowerCase().includes("correctamente")) {
                  $("#modal1").modal("hide");
                  Listar();
              }
              muestraMensaje(tipo, 4000, titulo, lee.mensaje);
          } else if (lee.resultado == "error") {
            muestraMensaje("error", 10000, "ERROR", lee.mensaje);
          }
        } catch (e) {
          alert("Error en JSON " + e.name + ": " + e.message);
        }
      },
      error: (request, status, err) => muestraMensaje("error", 6000, "Error de Conexión", `Error: ${request}, Status: ${status}, Detalle: ${err}`),
    });
  }
  
  function pone(pos, accion) {
    let linea = $(pos).closest("tr");
    limpia();
  
    // Guardar valores originales en campos ocultos
    $("#original_cedula").val($(linea).find("td:eq(0)").text());
    $("#original_lapso").val($(linea).find("td:eq(2)").text());
    $("#original_actividad").val($(linea).find("td:eq(3)").text());
  
    // Llenar formulario
    $("#docente").val($(linea).find("td:eq(0)").text());
    $("#lapso").val($(linea).find("td:eq(2)").text());
    $("#actividad").val($(linea).find("td:eq(3)").text());
    $("#descripcion").val($(linea).find("td:eq(4)").text());
    $("#dependencia").val($(linea).find("td:eq(5)").text());
    $("#horas").val($(linea).find("td:eq(6)").text());
    $("#observacion").val($(linea).find("td:eq(7)").text());
    
    if (accion == 0) { // Modificar
      $("#proceso").text("MODIFICAR");
      $("#f .form-control, #f .form-select").prop("disabled", false);
    } else { // Eliminar
      $("#proceso").text("ELIMINAR");
      $("#f .form-control, #f .form-select").prop("disabled", true);
    }
    $("#modal1").modal("show");
  }
  
  function validarenvio() {
      if ($("#docente").val() == "") {
          muestraMensaje("error", 4000, "ERROR", "Debe seleccionar un docente"); return false;
      }
      if ($("#lapso").val() == "") {
          muestraMensaje("error", 4000, "ERROR", "Debe seleccionar un lapso"); return false;
      }
      if ($("#actividad").val().trim().length < 4) {
          muestraMensaje("error", 4000, "ERROR", "La actividad debe tener al menos 4 caracteres"); return false;
      }
      if ($("#descripcion").val().trim().length < 4) {
          muestraMensaje("error", 4000, "ERROR", "La descripción debe tener al menos 4 caracteres"); return false;
      }
      if ($("#dependencia").val().trim().length < 4) {
          muestraMensaje("error", 4000, "ERROR", "La dependencia debe tener al menos 4 caracteres"); return false;
      }
      if ($("#horas").val() == "" || $("#horas").val() <= 0) {
          muestraMensaje("error", 4000, "ERROR", "Debe ingresar un número de horas válido"); return false;
      }
      return true;
  }
  
  function limpia() {
    $("#f")[0].reset();
    $("#original_cedula, #original_lapso, #original_actividad").val("");
  }
  
  $(document).ready(function () {
    Listar();
    cargarSelects();
  
    $("#registrar").on("click", function () {
      limpia();
      $("#proceso").text("REGISTRAR");
      $("#f .form-control, #f .form-select").prop("disabled", false);
      $("#modal1").modal("show");
    });
  
    $("#proceso").on("click", function () {
      let accion = $(this).text();
      if (accion == "REGISTRAR" || accion == "MODIFICAR") {
          if (validarenvio()) {
              var datos = new FormData(document.getElementById("f"));
              datos.append("accion", accion.toLowerCase());
              enviaAjax(datos);
          }
      } else if (accion == "ELIMINAR") {
        Swal.fire({
            title: "¿Está seguro de eliminar?", text: "Esta acción no se puede deshacer.",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6", confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar",
          }).then((result) => {
            if (result.isConfirmed) {
              var datos = new FormData(document.getElementById("f"));
              datos.set("accion", "eliminar");
              enviaAjax(datos);
            }
        });
      }
    });
  });
  
  // --- FUNCIONES AUXILIARES PARA CARGAR SELECTS Y VER HORARIO ---
  function cargarSelects() {
      $.post("", { accion: 'load_docentes' }, function(respuesta) {
          if (respuesta.success) {
              const $select = $('#docente');
              $select.html('<option value="">-- Seleccione --</option>');
              respuesta.teachers.forEach(doc => {
                  $select.append(`<option value="${doc.doc_cedula}">${doc.doc_prefijo}. ${doc.doc_nombre} ${doc.doc_apellido}</option>`);
              });
          }
      }, 'json');
      $.post("", { accion: 'load_lapsos' }, function(respuesta) {
          if (respuesta.success) {
              const $select = $('#lapso');
              $select.html('<option value="">-- Seleccione --</option>');
              respuesta.lapsos.forEach(item => {
                  $select.append(`<option value="${item.lapso_compuesto}">${item.lapso_compuesto}</option>`);
              });
          }
      }, 'json');
  }
  
  function poneVerHorario(boton) {
      let linea = $(boton).closest('tr');
      let cedula = $(linea).find("td:eq(0)").text();
      let nombre = $(linea).find("td:eq(1)").text();
      $("#nombreDocenteHorario").text(nombre);
      $("#tablaVerHorario tbody").html('<tr><td colspan="7">Cargando...</td></tr>');
      $("#modalVerHorario").modal("show");
      $.post("", { accion: "consultar_horario_clases", doc_cedula: cedula }, function(respuesta) {
          if (respuesta.resultado === 'ok') {
              renderizarTablaHorario(respuesta.franjas, respuesta.horario);
          } else {
              $("#tablaVerHorario tbody").html(`<tr><td colspan="7" class="text-danger">${respuesta.mensaje}</td></tr>`);
          }
      }, 'json');
  }
  
  function renderizarTablaHorario(franjas, horario) {
      const tbody = $("#tablaVerHorario tbody").empty();
      const dias = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
      const horarioMap = new Map(horario.map(clase => [`${clase.hor_horainicio}-${clase.hor_dia}`, clase]));
      franjas.forEach(franja => {
          const row = $("<tr>").append(`<td class="fw-bold">${franja.inicio} - ${franja.fin}</td>`);
          dias.forEach(dia => {
              const key = `${franja.inicio}-${dia}`;
              const clase = horarioMap.get(key);
              const cell = clase ? `<strong>${clase.uc_nombre}</strong><br><small>${clase.esp_codigo}</small>` : "";
              row.append($("<td>").html(cell));
          });
          tbody.append(row);
      });
  }
  
  function muestraMensaje(tipo, duracion, titulo, mensaje) {
      Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
  }