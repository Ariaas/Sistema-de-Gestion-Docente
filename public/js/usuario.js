function Listar() {
  var datos = new FormData();
  datos.append("accion", "consultar");
  enviaAjax(datos);
}

function validarExiste() {
  const nombre = $("#usuarionombre").val();
  const correo = $("#correo").val();
  if (nombre && correo) {
    var datos = new FormData();
    datos.append('accion', 'existe');
    datos.append('nombreUsuario', nombre);
    datos.append('correoUsuario', correo);
    if ($("#proceso").text() == "MODIFICAR") {
      datos.append('usuarioId', $("#usuarioId").val());
    }
    enviaAjax(datos);
  }
}

function destruyeDT() {

  if ($.fn.DataTable.isDataTable("#tablausuario")) {
    $("#tablausuario").DataTable().destroy();
  }
}

function crearDT() {
  if (!$.fn.DataTable.isDataTable("#tablausuario")) {
    $("#tablausuario").DataTable({
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
        search: "Buscar: ",
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
        search: "Buscar: ",
        zeroRecords: "No se encontraron resultados",
      },
      dom: "<'row'<'col-sm-12'f>>" +
        "<'row'<'col-sm-12'tr>>"
    });

    $(selector + '_filter').parent().parent().css('margin-bottom', '0');
  }
}

function destruyeDTModal(selector) {
  if ($.fn.DataTable.isDataTable(selector)) {
    $(selector).DataTable().destroy();
  }
}

$(document).ready(function () {
  Listar();

  $("#usuarionombre").on("keyup keydown", function () {
    $("#susuarionombre").css("color", "");
    let formatoValido = validarkeyup(
      /^[A-Za-zÁÉÍÓÚáéíóúÜüÑñ0-9\s]{5,30}$/,
      $(this),
      $("#susuarionombre"),
      "El usuario debe tener entre 5 y 30 caracteres y no puede contener caracteres especiales."
    );
    if (formatoValido === 1) {
      var datos = new FormData();
      datos.append('accion', 'existe');
      datos.append('nombreUsuario', $(this).val());
      if ($("#proceso").text() === "MODIFICAR") {
        datos.append('usuarioId', $("#usuarioId").val());
      }
      enviaAjax(datos, 'existe_usuario');
    }
  });

  $("#correo").on("keyup", function () {
    if ($("#usu_cedula").val()) return;

    $("#scorreo").text("").css("color", "red");
    $("#proceso").prop("disabled", false);

    let formatoValido = validarkeyup(/^[a-zA-Z0-9._-]{5,30}@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $(this), $("#scorreo"), "El correo debe tener un formato válido, Ej: usuario@dominio.com");

    if (formatoValido === 1) {
      const correo = $(this).val();
      const usuarioId = ($("#proceso").text() === "MODIFICAR") ? $("#usuarioId").val() : null;

      const datos = new FormData();
      datos.append('accion', 'verificar_correo_docente');
      datos.append('correo', correo);
      if (usuarioId) {
        datos.append('usuarioId', usuarioId);
      }
      enviaAjax(datos, 'verificar_correo_docente');
    }
  });

  $("#contrasenia").on("keyup keydown", function () {
    $("#scontrasenia").css("color", "");
    validarkeyup(/^.{5,30}$/, $("#contrasenia"), $("#scontrasenia"), "La contraseña debe tener entre 5 y 30 caracteres.");
  });


  $("#proceso").on("click", function () {
    if ($(this).text() == "REGISTRAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "registrar");
        datos.append("nombreUsuario", $("#usuarionombre").val());
        datos.append("correoUsuario", $("#correo").val());
        datos.append("contraseniaUsuario", $("#contrasenia").val());
        datos.append("usuarioRol", $("#usuarioRol").val());
        datos.append("usu_docente", $("#usu_docente").val());
        datos.append("usu_cedula", $("#usu_cedula").val());
        enviaAjax(datos);
      }
    } else if ($(this).text() == "MODIFICAR") {
      if (validarenvio()) {
        var datos = new FormData();
        datos.append("accion", "modificar");
        datos.append("usuarioId", $("#usuarioId").val());
        datos.append("nombreUsuario", $("#usuarionombre").val());
        datos.append("correoUsuario", $("#correo").val());
        datos.append("usuarioRol", $("#usuarioRol").val());
        datos.append("usu_docente", $("#usu_docente").val());
        datos.append("usu_cedula", $("#usu_cedula").val());

        if ($("#contrasenia").val()) {
          datos.append("contraseniaUsuario", $("#contrasenia").val());
        }

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
          datos.append("usuarioId", $("#usuarioId").val());
          enviaAjax(datos);
        } else {
          muestraMensaje("error", 2000, "INFORMACIÓN", "La eliminación ha sido cancelada.");
          $("#modal1").modal("hide");
        }
      });
    }
  });


  $("#registrar").on("click", function () {
    limpia();
    $("#proceso").text("REGISTRAR");
    $("#usuarionombre, #correo, #contrasenia, #btnSeleccionarDocente, #btnQuitarDocente, #btnSeleccionarRol, #btnQuitarRol").prop("disabled", false);
    $(".grupo-modificar").show();
    $("#modal1").modal("show");
  });

  $('#btnSeleccionarDocente').on('click', function () {
    $('#modal1').modal('hide');
    var datos = new FormData();
    datos.append("accion", "consultar_docentes");
    $.ajax({
      async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
      success: function (respuesta) {
        try {
          var lee = JSON.parse(respuesta);
          if (lee.resultado === 'ok') {
            const cuerpoTabla = $('#cuerpoTablaDocentes');
            destruyeDTModal('#tablaDocentes');
            cuerpoTabla.empty();
            if (lee.mensaje.length > 0) {
              lee.mensaje.forEach(function (docente) {
                cuerpoTabla.append(`
                                <tr>
                                    <td>${docente.doc_cedula}</td>
                                    <td>${docente.doc_nombre} ${docente.doc_apellido}</td>
                                    <td><button class="btn btn-success btn-sm btn-seleccionar-doc" data-cedula="${docente.doc_cedula}" data-nombre="${docente.doc_nombre} ${docente.doc_apellido}" data-correo="${docente.doc_correo}">Seleccionar</button></td>
                                </tr>
                            `);
              });
            } else {
              cuerpoTabla.append('<tr><td colspan="3" class="text-center">No hay docentes disponibles para asignar.</td></tr>');
            }
            crearDTModal('#tablaDocentes');
            $('#modalDocentes').modal('show');
          } else {
            muestraMensaje("error", 5000, "Error", lee.mensaje);
          }
        } catch (e) {
          alert("Error al procesar la respuesta de docentes: " + e);
        }
      }
    });
  });

  $(document).on('click', '.btn-seleccionar-doc', function () {
    const nombre = $(this).data('nombre');
    const cedula = $(this).data('cedula');
    const correo = $(this).data('correo');
    $('#usu_docente').val(nombre);
    $('#usu_cedula').val(cedula);
    $('#docente_asignado_nombre').val(nombre);
    $('#correo').val(correo).prop('readonly', true);
    $('#scorreo').text('');
    $('#modalDocentes').modal('hide');
  });

  $('#modalDocentes').on('hidden.bs.modal', function () {
    $('#modal1').modal('show');
  });

  $('#btnQuitarDocente').on('click', function () {
    $('#usu_docente').val('');
    $('#usu_cedula').val('');
    $('#docente_asignado_nombre').val('');
    $('#correo').val('').prop('readonly', false);
  });

  $('#btnSeleccionarRol').on('click', function () {
    $('#modal1').modal('hide');
    destruyeDTModal('#tablaRoles');
    crearDTModal('#tablaRoles');
    $('#modalRoles').modal('show');
  });

  $('#modalRoles').on('shown.bs.modal', function () {
    $('#tablaRoles').DataTable().columns.adjust().responsive.recalc();
  });

  $(document).on('click', '.btn-seleccionar-rol', function () {
    const id = $(this).data('id');
    const nombre = $(this).data('nombre');
    $('#usuarioRol').val(id);
    $('#rol_asignado_nombre').val(nombre);
    $('#modalRoles').modal('hide');
  });

  $('#modalRoles').on('hidden.bs.modal', function () {
    $('#modal1').modal('show');
  });

  $('#btnQuitarRol').on('click', function () {
    $('#usuarioRol').val('');
    $('#rol_asignado_nombre').val('');
  });

  $('#modal1').on('hidden.bs.modal', function () {
    $("#proceso").prop("disabled", false);
    $("#susuarionombre").text("").css("color", "");
    $("#scorreo").text("").css("color", "");
    $("#scontrasenia").text("").css("color", "");
    $("#susuarioRol").text("").css("color", "");
  });
});

function validarenvio() {
  let esValido = true;

  if (validarkeyup(/^[A-Za-z0-9\s]{5,30}$/, $("#usuarionombre"), $("#susuarionombre"), "El usuario debe tener entre 5 y 30 caracteres.") == 0) {
    if (esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del nombre de usuario es incorrecto.");
    esValido = false;
  }

  if ($("#correo").val().trim() !== '' && validarkeyup(/^[a-zA-Z0-9._-]{5,30}@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/, $("#correo"), $("#scorreo"), "Formato incorrecto, Ej: usuario@dominio.com.") == 0) {
    if (esValido) muestraMensaje("error", 4000, "ERROR!", "El formato del correo es incorrecto.");
    esValido = false;
  }

  if ($("#proceso").text() === "REGISTRAR" || $("#contrasenia").val().length > 0) {
    if (validarkeyup(/^.{5,30}$/, $("#contrasenia"), $("#scontrasenia"), "La contraseña debe tener entre 5 y 30 caracteres.") == 0) {
      if (esValido) muestraMensaje("error", 4000, "ERROR!", "El formato de la contraseña es incorrecto.");
      esValido = false;
    }
  }

  if ($("#usuarioRol").val() === "") {
    $("#susuarioRol").text("Debe seleccionar un rol para el usuario.").css("color", "red");
    if (esValido) muestraMensaje("error", 4000, "ERROR!", "Debe seleccionar un rol para el usuario.");
    esValido = false;
  } else {
    $("#susuarioRol").text("");
  }

  return esValido;
}

function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#usuarionombre, #correo, #contrasenia, #btnSeleccionarDocente, #btnQuitarDocente, #btnSeleccionarRol, #btnQuitarRol").prop("disabled", false);
    $(".grupo-modificar").show();
    $("#susuarionombre").text("").show();
    $("#scorreo").text("").show();
    $("#scontrasenia").text("").show();
    $("#susuarioRol").text("").show();
  } else {
    $("#proceso").text("ELIMINAR");
    $("#usuarionombre, #correo, #contrasenia, #btnSeleccionarDocente, #btnQuitarDocente, #btnSeleccionarRol, #btnQuitarRol").prop("disabled", true);
    $(".grupo-modificar").hide();
    $("#susuarionombre, #scorreo, #scontrasenia, #susuarioRol").hide();
  }

  $("#usuarioId").val($(linea).find("td:eq(0)").text());
  $("#usuarionombre").val($(linea).find("td:eq(1)").text());
  $("#correo").val($(linea).find("td:eq(2)").text());

  const rolId = $(linea).find("td:eq(3)").data("rol") || "";
  const rolNombre = $(linea).find("td:eq(3)").text() || "";
  $("#usuarioRol").val(rolId);
  if (rolNombre === 'Usuario sin rol' || !rolId) {
    $("#rol_asignado_nombre").val('');
    $("#usuarioRol").val('');
    $("#susuarioRol").text('').hide();
  } else {
    $("#rol_asignado_nombre").val(rolNombre);
    $("#susuarioRol").text('').hide();
  }

  const docenteAsignado = $(linea).find("td:eq(4)").text() || "";
  const cedulaAsignada = $(linea).find("td:eq(4)").data("cedula") || "";
  $("#usu_docente").val(docenteAsignado === 'Usuario no es un docente' ? '' : docenteAsignado);
  $("#usu_cedula").val(cedulaAsignada);
  $("#docente_asignado_nombre").val(docenteAsignado === 'Usuario no es un docente' ? '' : docenteAsignado);

  if (cedulaAsignada) {
    $("#correo").prop("readonly", true);
  } else {
    $("#correo").prop("readonly", false);
  }

  $("#susuarionombre, #scontrasenia, #scorreo, #susuarioRol").text("");

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
        if (accion === 'existe_usuario') {
          if (lee.resultado === 'existe') {
            $("#susuarionombre").text(lee.mensaje).css("color", "red");
            $("#proceso").prop("disabled", true);
          } else {
            $("#susuarionombre").text("");
            $("#proceso").prop("disabled", false);
          }
          return;
        }
        if (accion === 'existe_correo') {
          if (lee.resultado === 'existe') {
            $("#scorreo").text(lee.mensaje).css("color", "red");
            $("#proceso").prop("disabled", true);
          } else {
            $("#scorreo").text("");
            $("#proceso").prop("disabled", false);
          }
          return;
        }
        if (accion === 'verificar_correo_docente') {
          if (lee.resultado === 'existe_docente' || lee.resultado === 'existe_usuario') {
            $("#scorreo").text(lee.mensaje).css("color", "red").show();
            $("#proceso").prop("disabled", true);
          } else {
            $("#scorreo").text("").hide();
            $("#proceso").prop("disabled", false);
          }
          return;
        }
        if (lee.resultado === "consultar") {
          destruyeDT();
          $("#resultadoconsulta").empty();
          $.each(lee.mensaje, function (index, item) {
            $("#resultadoconsulta").append(`
              <tr>
                <td style="display: none;">${item.usu_id}</td>
                <td>${item.usu_nombre}</td>
                <td>${item.usu_correo}</td>
                <td data-rol="${item.rol_id}">${item.rol_nombre || 'Usuario sin rol'}</td>
                <td data-cedula="${item.usu_cedula || ''}">${item.usu_docente || 'Usuario no es un docente'}</td>
                <td>
                  <button class="btn btn-icon btn-edit" onclick='pone(this,0)' data-id="${item.usu_id}" data-nombre="${item.usu_nombre}" data-correo="${item.usu_correo}" data-rol="${item.rol_id}" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                  <button class="btn btn-icon btn-delete" onclick='pone(this,1)' data-id="${item.usu_id}" data-nombre="${item.usu_nombre}" data-correo="${item.usu_correo}" data-rol="${item.rol_id}" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                </td>
              </tr>
            `);
          });
          crearDT();
        }
        else if (lee.resultado == "registrar") {
          muestraMensaje("success", 4000, "REGISTRAR", lee.mensaje);
          if (lee.mensaje == 'Registro Incluido!<br/> Se registró el usuario correctamente!') {
            $("#modal1").modal("hide");
            limpia();
            Listar();
          }
        }
        else if (lee.resultado == "modificar") {
          muestraMensaje("success", 4000, "MODIFICAR", lee.mensaje);
          if (lee.mensaje == "Registro Modificado!<br/>Se modificó el usuario correctamente!") {
            $("#modal1").modal("hide");
            Listar();
          }
        }

        else if (lee.resultado == "existe") {
          if ($("#proceso").text() == "REGISTRAR") {
            muestraMensaje('success', 4000, 'Atención!', lee.mensaje);
          }
        }
        else if (lee.resultado == "eliminar") {
          muestraMensaje("success", 4000, "ELIMINAR", lee.mensaje);
          if (lee.autoeliminado) {
            setTimeout(function () {
              window.location.href = '.';
            }, 4100);
          } else if (lee.mensaje == "Registro Eliminado!<br/>Se eliminó el usuario correctamente!") {
            $("#modal1").modal("hide");
            Listar();
          }
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
    complete: function () { },
  });
}

function limpia() {
  $("#usuarioId").val("");
  $("#usuarionombre").val("");
  $("#contrasenia").val("");
  $("#correo").val("").prop("readonly", false);
  $("#usuarioRol").val("");
  $("#usu_docente").val("");
  $("#usu_cedula").val("");
  $("#docente_asignado_nombre").val("");
  $("#rol_asignado_nombre").val("");
  $("#susuarionombre").text("").show();
  $("#scorreo").text("").show();
  $("#scontrasenia").text("").show();
  $("#susuarioRol").text("").show();
  $("#proceso").prop("disabled", false);
}

