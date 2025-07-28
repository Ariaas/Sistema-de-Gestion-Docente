function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
   
    if ($.fn.DataTable.isDataTable("#tablarol")) {
      $("#tablarol").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablarol")) {
      $("#tablarol").DataTable({
  
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
  
      $("#nombreRol").on("keypress",function(e){
      validarkeypress(/^[A-Za-z0-9-\b\s]*$/,e);
      });
  
      $("#nombreRol").on("keydown keyup",function(){
          $("#snombreRol").css("color", "");
          let formatoValido = validarkeyup(/^[A-Za-z0-9\s]{5,30}$/,$("#nombreRol"),
          $("#snombreRol"),"El formato permite de 5 a 30 carácteres, Ej: Administrador");

          if(formatoValido === 1){
              var datos = new FormData();
              datos.append('accion', 'existe');
              datos.append('nombreRol', $(this).val());
              if ($("#proceso").text() === "MODIFICAR") {
                  datos.append('rolId', $("#rolId").val());
              }
              enviaAjax(datos, 'existe');
          }
      });
  
    //////////////////////////////BOTONES/////////////////////////////////////
  
    $("#proceso").on("click", function () {
      if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "registrar");
          datos.append("nombreRol", $("#nombreRol").val());
          enviaAjax(datos);
        }
      } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "modificar");
          datos.append("rolId", $("#rolId").val()); 
          datos.append("nombreRol", $("#nombreRol").val()); 
          enviaAjax(datos);
        }
      }
      if ($(this).text() == "ELIMINAR") {
        if (
          validarkeyup(
            /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{5,30}$/,
            $("#nombreRol"),
            $("#snombreRol"),
            "Formato incorrecto"
          ) == 0
        ) {
          muestraMensaje(
            "error",
            4000,
            "ERROR!",
            "Seleccionó el rol incorrecto <br/> por favor verifique nuevamente"
          );
        } else {
          
          Swal.fire({
            title: "¿Está seguro de eliminar este rol?",
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
              datos.append("rolId", $("#rolId").val());
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
      }
    });
  
  
    $("#registrar").on("click", function () {
      limpia();
      $("#proceso").text("REGISTRAR");
      $("#modal1").modal("show");
      $("#snombreRol").show();
      $("#nombreRol").prop("disabled", false);
    });
  
    
  });
  
  //////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////
  
  function validarenvio() {
      let esValido = true;
      if (validarkeyup( /^[A-Za-z0-9\s]{5,30}$/,$("#nombreRol"),$("#snombreRol"),"El formato permite de 5 a 30 carácteres, Ej:Administrador") == 0) {
          if(esValido) muestraMensaje("error",4000,"ERROR!","El formato del nombre del rol es incorrecto.");
          esValido = false;
      }
    return esValido;
  }
  
  
  function pone(pos, accion) {
  linea = $(pos).closest("tr");

  if (accion == 0) {
    $("#proceso").text("MODIFICAR");
    $("#rolId").prop("disabled", false);
    $("#nombreRol").prop("disabled", false);
    $("#snombreRol").text("").show();
  } else {
    $("#proceso").text("ELIMINAR");
    $("#rolId, #nombreRol").prop("disabled", true);
    $("#snombreRol").hide();
  }
  $("#rolId").val($(linea).find("td:eq(0)").text());
  $("#nombreRol").val($(linea).find("td:eq(1)").text());

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
      beforeSend: function () {},
      timeout: 10000, 
      success: function (respuesta) {
        try {
          var lee = JSON.parse(respuesta);
          if (accion === 'existe') {
            if (lee.resultado === 'existe') {
              $("#snombreRol").text(lee.mensaje).css("color", "red");
              $("#proceso").prop("disabled", true);
            } else {
              $("#snombreRol").text("");
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
                  <td style="display: none;">${item.rol_id}</td>
                  <td>${item.rol_nombre}</td>
                  <td>
                    <button class="btn btn-warning btn-sm agregarPermiso" data-codigo="${item.rol_id}" ${!PERMISOS.registrar ? 'disabled' : ''}>Permisos</button>
                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-id="${item.rol_id}" data-nombre="${item.rol_nombre}" ${!PERMISOS.modificar ? 'disabled' : ''}>Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-id="${item.rol_id}" data-nombre="${item.rol_nombre}" ${!PERMISOS.eliminar ? 'disabled' : ''}>Eliminar</button>
                  </td>
                </tr>
              `);
            });
            crearDT();
          }
          ////////
          else if (lee.resultado == "registrar") {
            muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Incluido!<br/>Se registró el rol correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado == "modificar") {
            muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Modificado!<br/>Se modificó el rol correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado == "eliminar") {
            muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Eliminado!<br/>Se eliminó el rol correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado === "listarPermisos") { 
            renderTablaPermisos(lee.data.modulosDisponibles, lee.data.permisosAsignados);
            $("#modal2").modal("show");
          }
          else if (lee.resultado === "ok") {
            muestraMensaje("success", 3000, "Permisos", lee.mensaje);
            $("#modal2").modal("hide");
            setTimeout(() => {
            location.reload();
           }, 3100); 
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
    $("#rolId").val("");
    $("#nombreRol").val("");
    $("#snombreRol").text("");
    $("#proceso").prop("disabled", false);
  }

  let carritoPermisos = [];
let rolSeleccionado = null;

function actualizarCarritoPermisos() {
    const ul = document.getElementById("carritoPermisos");
    if (!ul) return;
    ul.innerHTML = "";
    carritoPermisos.forEach((perm, idx) => {
        const nombre = $("#permisos option[value='" + perm + "']").text();
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";
        li.innerHTML = `
            ${nombre}
            <button type="button" class="btn btn-danger btn-sm quitar-permiso" data-idx="${idx}">Quitar</button>
        `;
        ul.appendChild(li);
    });
}

$(document).on("click", "#agregarPermiso", function () {
    const select = document.getElementById("permisos");
    const permisoId = select.value;
    if (!permisoId) {
        alert("Seleccione un permiso válido.");
        return;
    }
    if (carritoPermisos.includes(permisoId)) {
        alert("Este permiso ya está seleccionado.");
        return;
    }
    carritoPermisos.push(permisoId);
    actualizarCarritoPermisos();
});

$(document).on("click", ".quitar-permiso", function () {
    const idx = $(this).data("idx");
    carritoPermisos.splice(idx, 1);
    actualizarCarritoPermisos();
});

$(document).on("click", ".agregarPermiso", function () {
    rolSeleccionado = $(this).data("codigo");
    var datos = new FormData();
    datos.append("accion", "listarPermisos");
    datos.append("rolId", rolSeleccionado);
    enviaAjax(datos);
});

$(document).on("click", "#guardarPermisos", function () {
    if (!rolSeleccionado) {
        alert("Seleccione un rol.");
        return;
    }
    let permisos = [];
    $("#tablaPermisos .permiso-check:checked").each(function () {
        permisos.push({
            per_id: $(this).data("perid"),
            per_accion: $(this).data("accion")
        });
    });
    
    var datos = new FormData();
    datos.append("accion", "asignarPermisos");
    datos.append("rolId", rolSeleccionado);
    datos.append("permisos", JSON.stringify(permisos));
    enviaAjax(datos);
});

function renderTablaPermisos(modulosDisponibles, permisosAsignados) {
    let tbody = "";
    const ACCIONES_ESTANDAR = ["registrar", "modificar", "eliminar"];

    modulosDisponibles.forEach(modulo => {
        tbody += `<tr><td>${modulo.per_modulo}</td>`;

        if (modulo.per_modulo.toLowerCase() === 'reportes') {
            const encontrado = permisosAsignados.find(
                p => p.per_id == modulo.per_id && p.per_accion == 'registrar'
            );
            tbody += `
                <td class="text-center">
                    <div class="form-check d-inline-block">
                        <input type="checkbox" class="form-check-input permiso-check"
                            id="permiso-ver-${modulo.per_id}"
                            data-perid="${modulo.per_id}" data-accion="registrar"
                            ${encontrado ? "checked" : ""}>
                        <label class="form-check-label" for="permiso-ver-${modulo.per_id}">Ver</label>
                    </div>
                </td>
                <td></td>
                <td></td>`;
        } else {
            tbody += ACCIONES_ESTANDAR.map(acc => {
                const encontrado = permisosAsignados.find(
                    p => p.per_id == modulo.per_id && p.per_accion == acc
                );
                const idUnico = `permiso-${acc}-${modulo.per_id}`;
                return `<td class="text-center">
                    <div class="form-check d-inline-block">
                        <input type="checkbox" class="form-check-input permiso-check"
                            id="${idUnico}"
                            data-perid="${modulo.per_id}" data-accion="${acc}"
                            ${encontrado ? "checked" : ""}>
                        <label class="form-check-label" for="${idUnico}" style="visibility: hidden;">Ver</label>
                    </div>
                </td>`;
            }).join("");
        }
        tbody += `</tr>`;
    });
    $("#tablaPermisos tbody").html(tbody);
}


