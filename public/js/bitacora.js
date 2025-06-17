function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
    
    if ($.fn.DataTable.isDataTable("#tablaBitacora")) {
      $("#tablaBitacora").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaBitacora")) {
      $("#tablaBitacora").DataTable({
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
    
  });
  
  //////////////////////////////VALIDACIONES ANTES DEL ENVIO/////////////////////////////////////
  
 
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
                  <td>${item.usu_nombre}</td>
                  <td>${item.bit_modulo}</td>
                  <td>${item.bit_accion}</td>
                  <td>${item.bit_fecha}</td>
                </tr>
              `);
            });
            crearDT();
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