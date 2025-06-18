function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
   
    if ($.fn.DataTable.isDataTable("#tablacohorte")) {
      $("#tablacohorte").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablacohorte")) {
      $("#tablacohorte").DataTable({
  
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
  
      $("#cohNumero").on("keypress",function(e){
      validarkeypress(/^[1-9][0-9]{0,2}$/, e);
      });
  
      $("#cohNumero").on("keydown keyup",function(){
        validarkeyup(/^[1-9][0-9]{0,2}$/, $("#cohNumero"),
        $("#scohNumero"),"El formato permite de 1 a 3 carácteres, Ej:Cohorte '4'");
              var datos = new FormData();
              datos.append('accion', 'existe');
              datos.append('cohNumero', $(this).val());
              enviaAjax(datos, 'existe');
      });
  
    //////////////////////////////BOTONES/////////////////////////////////////
  
    $("#proceso").on("click", function () {
      if ($(this).text() == "REGISTRAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "registrar");
          datos.append("cohNumero", $("#cohNumero").val());
  
          enviaAjax(datos);
        }
      } else if ($(this).text() == "MODIFICAR") {
        if (validarenvio()) {
          var datos = new FormData();
          datos.append("accion", "modificar");
          datos.append("cohNumero", $("#cohNumero").val());
          datos.append("cohId", $("#cohId").val());
  
          enviaAjax(datos);
        }
      }
      if ($(this).text() == "ELIMINAR") {
        if (
          validarkeyup(
            /^[[A-Za-z0-9,\#\b\s\u00f1\u00d1\u00E0-\u00FC-]{1,3}$/,
            $("#cohNumero"),
            $("#scohNumero"),
            "Formato incorrecto"
          ) == 0
        ) {
          muestraMensaje(
            "error",
            4000,
            "ERROR!",
            "Seleccionó la cohorte incorrecta <br/> por favor verifique nuevamente"
          );
        } else {
          
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
              datos.append("cohId", $("#cohId").val());
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
      $("#scohNumero").show();
      $("#cohNumero").prop("disabled", false);
    });
  
    
  });
  
  //////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////
  
  function validarenvio() {
      if (validarkeyup( /^[A-Za-z0-9\s]{1,3}$/,$("#cohNumero"),$("#scohNumero"),"El formato permite de 1 a 3 carácteres, Ej:Cohorte '4'") == 0) {
          muestraMensaje("error",4000,"ERROR!","La Cohorte <br/> No debe estar vacío y debe contener entre 1 a 3 carácteres");
            return false;
          }
    return true;
  }
  
  
  function pone(pos, accion) {
    linea = $(pos).closest("tr");
  
    if (accion == 0) {
      $("#proceso").text("MODIFICAR");
      $("#cohId").prop("disabled", false);
      $("#cohNumero").prop("disabled", false);
    } else {
      $("#proceso").text("ELIMINAR");
      $(
        "#cohId, #cohNumero"
      ).prop("disabled", true);
    }
    $("#scohNumero").hide();
    $("#cohId").val($(linea).find("td:eq(0)").text());
    $("#cohNumero").val($(linea).find("td:eq(1)").text());
  
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
                  <td style="display: none;">${item.coh_id}</td>
                  <td>${item.coh_numero}</td>
                  <td>
                    <button class="btn btn-warning btn-sm modificar" onclick='pone(this,0)' data-codigo="${item.coh_id}" data-tipo="${item.coh_numero}">Modificar</button>
                    <button class="btn btn-danger btn-sm eliminar" onclick='pone(this,1)' data-codigo="${item.coh_id}" data-tipo="${item.coh_numero}">Eliminar</button>
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
              "Registro Incluido!<br/>Se registró la COHORTE correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }
          else if (lee.resultado == "modificar") {
            muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Modificado!<br/>Se modificó la COHORTE correctamente!"
            ) {
              $("#modal1").modal("hide");
              Listar();
            }
          }else if (lee.resultado == "existe") {		
            if (lee.mensaje == 'La COHORTE colocada YA existe!') {
              muestraMensaje('info', 4000,'Atención!', lee.mensaje);
            }	
          }
          else if (lee.resultado == "eliminar") {
            muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
            if (
              lee.mensaje ==
              "Registro Eliminado!<br/>Se eliminó la COHORTE correctamente!"
            ) {
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
      complete: function () {},
    });
  }
  
  function limpia() {
    $("#cohId").val("");
    $("#cohNumero").val("");
  }
  
  
  