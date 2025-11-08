function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
  }
  
  function destruyeDT() {
    
    if ($.fn.DataTable.isDataTable("#tablaNoti")) {
      $("#tablaNoti").DataTable().destroy();
    }
  }
  
  function crearDT() {
    if (!$.fn.DataTable.isDataTable("#tablaNoti")) {
      $("#tablaNoti").DataTable({
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
        order: [[0, "asc"]],
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

$(document).ready(function() {
    if ($("#tablaNoti").length) {
        Listar();
    }
    
    function actualizarContador() {
        $.ajax({
            url: '?pagina=notificaciones',
            type: 'POST',
            data: { accion: 'contar_nuevas' },
            dataType: 'json',
            success: function(resp) {
                const badge = $("#notificacionesBadge");
                if (resp.resultado === 'ok' && resp.count > 0) {
                    badge.text(resp.count).show();
                } else {
                    badge.hide();
                }
            },
            error: function() {
                console.error('Error al actualizar contador de notificaciones');
            }
        });
    }

    function cargarNotificaciones() {
        $.ajax({
            url: '?pagina=notificaciones',
            type: 'POST',
            data: { accion: 'consultar_nuevas' },
            dataType: 'json',
            success: function(resp) {
                const panel = $("#notificacionesPanel");
                const badge = $("#notificacionesBadge");
                panel.empty();

                if (resp.resultado === 'ok' && Array.isArray(resp.mensaje) && resp.mensaje.length > 0) {
                    let html = '<ul class="list-group list-group-flush">';
                    resp.mensaje.forEach(function(not) {
                        html += `<li class="list-group-item">${not.not_notificacion}</li>`;
                    });
                    html += '</ul>';
                    panel.html(html);
                    badge.text(resp.mensaje.length).show();
                } else {
                    panel.html('<li><a class="dropdown-item text-muted" href="#">No hay notificaciones nuevas.</a></li>');
                    badge.hide(); 
                }
                panel.append('<div class="dropdown-divider"></div>');
                panel.append('<li><a class="dropdown-item text-center text-primary fw-bold" href="?pagina=notificaciones">Ver todas</a></li>');
            },
            error: function() {
                $("#notificacionesPanel").html('<li><a class="dropdown-item text-danger" href="#">Error al cargar.</a></li>');
                $("#notificacionesBadge").hide();
            }
        });
    }

    cargarNotificaciones();
    
    actualizarContador();
    setInterval(actualizarContador, 3000);

    $('#notificacionesDropdown').on('show.bs.dropdown', function () {
        const badge = $("#notificacionesBadge");
        if(badge.is(":visible")){
             $.ajax({
                url: '?pagina=notificaciones',
                type: 'POST',
                data: { accion: 'marcar_vistas' },
                dataType: 'json',
                success: function(resp) {
                    if(resp.resultado === 'ok'){
                        badge.hide();
                    }
                }
            });
        }
    });
});

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
            if (lee.mensaje.length > 0) {
              $.each(lee.mensaje, function (index, item) {
                $("#resultadoconsulta").append(`
                  <tr>
                    <td>${item.not_notificacion}</td>
                  </tr>
                `);
              });
            }
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