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
      validarkeypress(/^[A-Za-z0-9-\b]*$/,e);
      });
  
      $("#nombreRol").on("keydown keyup",function(){
          validarkeyup(/^[A-Za-z0-9]{5,30}$/,$("#nombreRol"),
          $("#snombreRol"),"El formato permite de 5 a 30 carácteres, Ej:Administrador");

              var datos = new FormData();
              datos.append('accion', 'existe');
              datos.append('nombreRol', $(this).val());
              enviaAjax(datos, 'existe');
          
      });
  
    //////////////////////////////BOTONES/////////////////////////////////////
  
    $("#proceso").on("click", function () {
      if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "registrar"); // <-- LÍNEA FALTANTE
          datos.append("nombreRol", $("#nombreRol").val());
          enviaAjax(datos);
        }
      } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "modificar");
          datos.append("rolId", $("#rolId").val()); // <--- debe existir y tener valor
          datos.append("nombreRol", $("#nombreRol").val()); // <--- debe existir y tener valor
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
            "Seleccionó el eje incorrecto <br/> por favor verifique nuevamente"
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
      if (validarkeyup( /^[A-Za-z0-9\s]{5,30}$/,$("#nombreRol"),$("#snombreRol"),"El formato permite de 5 a 30 carácteres, Ej:Administrador") == 0) {
          muestraMensaje("error",4000,"ERROR!","El nombre del ROL <br/> No debe estar vacío y debe contener entre 5 a 30 carácteres");
            return false;
          }
    return true;
  }
  
  
  function pone(pos, accion) {
    linea = $(pos).closest("tr");
  
    if (accion == 0) {
      $("#proceso").text("MODIFICAR");
      $("#rolId").prop("disabled", false);
      $("#nombreRol").prop("disabled", false);
    } else {
      $("#proceso").text("ELIMINAR");
      $(
        "#rolId, #nombreRol"
      ).prop("disabled", true);
    }
    $("#snombreRol").hide();
    $("#rolId").val($(linea).find("td:eq(0)").text());
    $("#nombreRol").val($(linea).find("td:eq(1)").text());
  
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
                  <td style="display: none;">${item.rol_id}</td>
                  <td>${item.rol_nombre}</td>
                  <td>
                    <button class="btn btn-warning btn-sm agregarPermiso" data-codigo="${item.rol_id}">Permisos</button>
                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-codigo="${item.rol_id}" data-tipo="${item.rol_nombre}">Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-codigo="${item.rol_id}" data-tipo="${item.rol_nombre}">Eliminar</button>
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
          }else if (lee.resultado == "existe") {		
            if (lee.mensaje == 'El ROL colocado YA existe!') {
              muestraMensaje('info', 4000,'Atención!', lee.mensaje);
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
            renderTablaPermisos(lee.permisos || []);
            $("#modal2").modal("show");
          }
          else if (lee.resultado === "ok") {
            muestraMensaje("success", 3000, "Permisos", lee.mensaje);
            $("#modal2").modal("hide");
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
    if (permisos.length === 0) {
        alert("Seleccione al menos un permiso.");
        return;
    }
    var datos = new FormData();
    datos.append("accion", "asignarPermisos");
    datos.append("rolId", rolSeleccionado);
    datos.append("permisos", JSON.stringify(permisos));
    enviaAjax(datos);
});

function renderTablaPermisos(permisosAsignados) {
    let tbody = "";
    MODULOS_PERMISOS.forEach(modulo => {
        tbody += `<tr>
            <td>${modulo.nombre}</td>
            ${ACCIONES.map(acc => {
                const encontrado = permisosAsignados.find(
                    p => p.per_id == modulo.id && p.per_accion == acc
                );
                return `<td>
                    <input type="checkbox" class="permiso-check"
                        data-perid="${modulo.id}" data-accion="${acc}"
                        ${encontrado ? "checked" : ""}>
                </td>`;
            }).join("")}
        </tr>`;
    });
    $("#tablaPermisos tbody").html(tbody);
}

const MODULOS_PERMISOS = [
    { id: 1, nombre: "seccion" },
    { id: 2, nombre: "unidad curricular" },
    { id: 3, nombre: "espacio" },
    { id: 4, nombre: "usuario" },
    { id: 5, nombre: "reportes" },
    { id: 6, nombre: "prosecusion" },
    { id: 7, nombre: "malla curricular" },
    { id: 8, nombre: "horario docente" },
    { id: 9, nombre: "reporte estadístico" },
    { id: 10, nombre: "eje" },
    { id: 11, nombre: "categoría" },
    { id: 12, nombre: "docentes" },
    { id: 13, nombre: "año" },
    { id: 14, nombre: "coordinación" },
    { id: 15, nombre: "título" },
    { id: 16, nombre: "notas" },
    { id: 17, nombre: "actividad" },
    { id: 18, nombre: "bitácora" }
];

const ACCIONES = ["registrar", "modificar", "eliminar"];


