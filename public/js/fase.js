$(document).ready(function() {
    let dataTable;


    Listar();
    cargarTrayectos();

   

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR")
                     .removeClass("btn-danger btn-warning")
                     .addClass("btn-primary");
        $("#modal1 .modal-title").text("Formulario de Registro de Fase");
        $("#modal1").modal("show");
    });


    $("#proceso").on("click", function() {
        const textoBoton = $(this).text();

        if (textoBoton === "REGISTRAR" || textoBoton === "MODIFICAR") {
            if (validarenvio()) {
                const datos = new FormData($("#f")[0]);
                const accion = (textoBoton === "REGISTRAR") ? "registrar" : "modificar";
                datos.append("accion", accion);
                enviaAjax(datos);
            }
        } else if (textoBoton === "ELIMINAR") {
            Swal.fire({
                title: "¿Está seguro de eliminar esta fase?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    const datos = new FormData();
                    datos.append("accion", "eliminar");
                    datos.append("faseId", $("#faseId").val());
                    enviaAjax(datos);
                }
            });
        }
    });
   
    $(document).on('click', '.modificar-btn', function() {
        pone(this, 'modificar');
    });

    $(document).on('click', '.eliminar-btn', function() {
        pone(this, 'eliminar');
    });

 
    $("#traId, #faseNumero").on("change", function() {
        validarSelects();
    });
    $("#faseApertura, #faseCierre").on("change", function() {
        validarRangoFechas();
    });



    function Listar() {
        const datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function cargarTrayectos() {
        const datos = new FormData();
        datos.append("accion", "consultar_trayectos");
        enviaAjax(datos);
    }

    function destruyeDT() {
        if ($.fn.DataTable.isDataTable("#tablafase")) {
            $('#tablafase').DataTable().destroy();
        }
    }

    function crearDT() {
        if (!$.fn.DataTable.isDataTable("#tablafase")) {
            dataTable = $("#tablafase").DataTable({
                paging: true,
                lengthChange: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                responsive: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' },
                order: [[1, "asc"]]
            });
        }
    }

    function validarenvio() {
        let esValido = true;
        if (!validarSelects()) esValido = false;
        if (!validarRangoFechas()) esValido = false;

        if (!$("#faseApertura").val() || !$("#faseCierre").val()){
            muestraMensaje("error", 4000, "Error de validación", "Las fechas de apertura y cierre son obligatorias.");
            esValido = false;
        }

        return esValido;
    }

    function validarSelects() {
        let valido = true;
        if (!$("#traId").val()) {
            $("#straId").text("Debe seleccionar un trayecto.").show();
            valido = false;
        } else {
            $("#straId").hide();
        }
        if (!$("#faseNumero").val()) {
            $("#sfaseNumero").text("Debe seleccionar un número de fase.").show();
            valido = false;
        } else {
            $("#sfaseNumero").hide();
        }
        return valido;
    }

    function validarRangoFechas() {
        const apertura = $("#faseApertura").val();
        const cierre = $("#faseCierre").val();
        const spanCierre = $("#sfaseCierre");
        if (apertura && cierre && new Date(cierre) <= new Date(apertura)) {
            spanCierre.text("La fecha de cierre debe ser posterior a la de apertura.").show();
            return false;
        }
        spanCierre.hide();
        return true;
    }

   
    function pone(pos, accion) {
        const fila = $(pos).closest("tr");
        

        const id = fila.find("td:eq(0)").text();
        const faseNumero = fila.find("td:eq(2)").text();
        const apertura = fila.find("td:eq(3)").text();
        const cierre = fila.find("td:eq(4)").text();
        const traId = fila.find("td:eq(5)").text();
        
        limpia();
        $("#faseId").val(id);
        $("#traId").val(traId);
        $("#faseNumero").val(faseNumero);
        $("#faseApertura").val(apertura);
        $("#faseCierre").val(cierre);
        
        if (accion === 'modificar') {
            $("#proceso").text("MODIFICAR")
                         .removeClass("btn-danger btn-warning")
                         .addClass("btn-primary");
            $("#modal1 .modal-title").text("Formulario de Modificación de Fase");
            $(".form-control, .form-select").prop("disabled", false);
        } else if (accion === 'eliminar') {
            $("#proceso").text("ELIMINAR")
                         .removeClass("btn-primary btn-warning")
                         .addClass("btn-danger");
            $("#modal1 .modal-title").text("Confirmar Eliminación de Fase");
           
            $("#traId, #faseNumero, #faseApertura, #faseCierre").prop("disabled", true);
        }
        
        $("#modal1").modal("show");
    }
 

    function limpia() {
        $("#f")[0].reset();
        $(".form-control, .form-select").prop('disabled', false);
        $("#straId, #sfaseNumero, #sfaseApertura, #sfaseCierre").hide().text("");
    }
    
    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({
            icon: tipo,
            title: titulo,
            html: mensaje,
            timer: duracion,
            timerProgressBar: true,
        });
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
            timeout: 10000,
            success: function(respuesta) {
                try {
                    const lee = JSON.parse(respuesta);
                    switch (lee.resultado) {
                        case 'consultar':
                            destruyeDT();
                            $("#resultadoconsulta").empty();
                            lee.mensaje.forEach(item => {
                                $("#resultadoconsulta").append(`
                                  <tr>
                                    <td style="display: none;">${item.fase_id}</td>
                                    <td>Trayecto ${item.tra_numero} (${item.ani_anio})</td>
                                    <td>${item.fase_numero}</td>
                                    <td>${item.fase_apertura}</td>
                                    <td>${item.fase_cierre}</td>
                                    <td style="display: none;">${item.tra_id}</td>
                                    <td>
                                      <button class="btn btn-warning btn-sm modificar-btn">Modificar</button>
                                      <button class="btn btn-danger btn-sm eliminar-btn">Eliminar</button>
                                    </td>
                                  </tr>
                                `);
                            });
                            crearDT();
                            break;
                        case 'consultar_trayectos':
                            const selectTrayecto = $("#traId");
                            selectTrayecto.empty().append('<option value="">Seleccione un trayecto</option>');
                            lee.mensaje.forEach(item => {
                                selectTrayecto.append(`<option value="${item.tra_id}">Trayecto ${item.tra_numero} (${item.tra_tipo}) - Año ${item.ani_anio}</option>`);
                            });
                            break;
                        case 'registrar':
                        case 'modificar':
                        case 'eliminar':
                            muestraMensaje("success", 3000, "ÉXITO", lee.mensaje);
                            $("#modal1").modal("hide");
                            Listar();
                            break;
                        case 'error':
                        default:
                            muestraMensaje("error", 5000, "ERROR", lee.mensaje || "Ocurrió un error inesperado.");
                            break;
                    }
                } catch (e) {
                    console.error("Error:", e, "Respuesta:", respuesta);
                    muestraMensaje("error", 5000, "ERROR", "No se pudo procesar la respuesta del servidor.");
                }
            },
            error: (request, status, err) => muestraMensaje("error", 5000, "ERROR DE COMUNICACIÓN", `Ocurrió un error: ${err}`)
        });
    }
});