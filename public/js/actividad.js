$(document).ready(function() {
    let formInteracted = false;
    let initialState = '';


    $('#modal1').on('hidden.bs.modal', function () {
        $('#modification_tip_wrapper').remove();
    });
    
    function Listar() {
        var datos = new FormData();
        datos.append("accion", "consultar");
        enviaAjax(datos);
    }

    function destruyeDT() {
      if ($.fn.DataTable.isDataTable("#tablaactividad")) {
        $("#tablaactividad").DataTable().destroy();
      }
    }

    function crearDT() {
        if (!$.fn.DataTable.isDataTable("#tablaactividad")) {
            $("#tablaactividad").DataTable({
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
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    search: "Buscar:",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
                },
                autoWidth: false,
                order: [[1, "asc"]],
                dom:
                    "<'row'<'col-sm-2'l><'col-sm-6'B><'col-sm-4'f>><'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            });
            $("div.dataTables_length select").css({ width: "auto", display: "inline", "margin-top": "10px" });
            $("div.dataTables_filter").css({ "margin-bottom": "50px", "margin-top": "10px" });
            $("div.dataTables_filter label").css({ float: "left" });
            $("div.dataTables_filter input").css({ width: "300px", float: "right", "margin-left": "10px" });
        }
    }

    function validarHorasEnTiempoReal() {
        const dedicacion = $('#docId option:selected').data('dedicacion');
        const spanError = $("#sHorasTotales");
        const botonProceso = $("#proceso");

        if (!dedicacion) {
            spanError.text("");
            botonProceso.prop('disabled', true);
            return;
        }

        let maxHoras = 0;
        switch (String(dedicacion).toLowerCase()) {
            case 'exclusiva': maxHoras = 29; break;
            case 'tiempo completo': maxHoras = 23; break;
            case 'medio tiempo': maxHoras = 13; break;
            case 'tiempo convencional': maxHoras = 0; break;
        }

        let totalActual = 0;
        $('.horas-actividad').each(function() {
            totalActual += parseInt($(this).val()) || 0;
        });

        if (totalActual > maxHoras) {
            spanError.text(`Error: El total de horas (${totalActual}) supera el límite de ${maxHoras} para la dedicación '${dedicacion}'.`);
            botonProceso.prop('disabled', true);
        } else {
            spanError.text("");
            if ($("#accion").val() !== 'modificar') {
                if ($("#docId").val() && $("#sdocId").css('color') !== 'rgb(220, 53, 69)') {
                    botonProceso.prop('disabled', false);
                } else {
                     botonProceso.prop('disabled', true);
                }
            }
        }
    }
    
    $('#f').on('input change', function() {
        if ($("#accion").val() === 'modificar') {
            const currentState = $('#f').serialize();
            if (currentState !== initialState) {
                if(!$("#sHorasTotales").text()){
                   $("#proceso").prop('disabled', false);
                }
                $('#modification_tip_wrapper').hide();
            } else {
                $("#proceso").prop('disabled', true);
                $('#modification_tip_wrapper').show();
            }
        }
    });

    Listar();
    verificarRequisitosIniciales();

    $("#registrar").on("click", function () {
        limpia();
        CargarDocentes(null, 'registrar'); 
        $("#accion").val("registrar");
        $("#proceso").text("REGISTRAR").prop('disabled', true);
        $("#modal1 .modal-title").text("Registrar Actividad");
        $("form#f :input").prop('disabled', false);
        
        $('#f').one('input change', function() {
            formInteracted = true;
            if(!$("#docId").val()){
                $("#sdocId").removeClass('text-success').addClass('text-danger').text("Debe seleccionar un docente.");
            }
        });
        
        $("#modal1").modal("show");
    });
    
    $("#proceso").on("click", function () {
        const accion = $("#accion").val();
        if (accion === "registrar" || accion === "modificar") {
            if (validarenvio()) {
                const datos = new FormData($('#f')[0]);
                enviaAjax(datos);
            }
        } else if (accion === "eliminar") {
            Swal.fire({
                title: "¿Está seguro de eliminar este registro?",
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
                    datos.append("actId", $("#actId").val());
                    enviaAjax(datos);
                }
            });
        }
    });

    $('#docId').on('change', function() {
        if (formInteracted && !$(this).val()) {
            $("#sdocId").removeClass('text-success').addClass('text-danger').text("Debe seleccionar un docente.");
        }
        
        if ($("#accion").val() === "registrar") {
            var docId = $(this).val();
            var spanDocId = $("#sdocId");
            if (docId) {
                var datos = new FormData();
                datos.append('accion', 'verificar_docente');
                datos.append('docId', docId);
                $.ajax({
                    async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
                    success: function(respuesta) {
                        try {
                            var lee = JSON.parse(respuesta);
                            if (lee.existe) {
                                spanDocId.html("Este docente ya tiene horas registradas.").removeClass('text-success').addClass('text-danger');
                            } else {
                                spanDocId.html("Docente disponible.").removeClass('text-danger').addClass('text-success');
                            }
                        } catch(e) { console.error("Error en la verificación", e); }
                        validarHorasEnTiempoReal();
                    }
                });
            } else {
                spanDocId.html("");
                validarHorasEnTiempoReal();
            }
        } else {
             validarHorasEnTiempoReal();
        }
    });

    $('.horas-actividad').on('input', function() {
        validarHorasEnTiempoReal();
    });

    function CargarDocentes(callback, accion) {
        var datos = new FormData();
        datos.append("accion", "listar_docentes");
        $.ajax({
            async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
            success: function (respuesta) {
                try {
                    var lee = JSON.parse(respuesta);
                    if (lee.resultado === "listar_docentes") {
                        const docSelect = $("#docId");
                        docSelect.empty().append('<option value="">Seleccione un docente</option>');
                        
                        $.each(lee.mensaje, function (index, item) {
                            let isDisabled = false;
                            let displayText = `${item.doc_nombre} ${item.doc_apellido}`;

                            if (accion === 'registrar' && item.tiene_actividad == '1') {
                                isDisabled = true;
                                displayText += ' (Ya registrado)';
                            }
                            
                            const option = $('<option></option>')
                                .val(item.doc_cedula)
                                .text(displayText)
                                .data('dedicacion', item.doc_dedicacion)
                                .prop('disabled', isDisabled);
                            
                            docSelect.append(option);
                        });

                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                } catch (e) { alert("Error al cargar docentes: " + e); }
            }
        });
    }

    function validarenvio() {
        if (!$("#docId").val()) {
            muestraMensaje("error", 4000, "ERROR", "Debe seleccionar un docente");
            return false;
        }
        validarHorasEnTiempoReal();
        if ($("#proceso").is(':disabled')) {
             muestraMensaje("error", 4000, "ERROR", "Verifique los datos del formulario. El total de horas podría exceder el límite o el docente ya tiene un registro.");
            return false;
        }
        return true;
    }

    $(document).on('click', '.modificar-btn, .eliminar-btn', function() {
        const accion = $(this).hasClass('modificar-btn') ? 'modificar' : 'eliminar';
        pone(this, accion);
    });

    function pone(pos, accion) {
        limpia();
        const fila = $(pos).closest("tr");
        const docCedula = fila.data('docid');
        
        $("#actId").val(docCedula);
        $("#accion").val(accion);
        
        CargarDocentes(function() {
            $("#docId").val(docCedula);
            $("#actCreacion").val(fila.find("td:eq(2)").text());
            $("#actIntegracion").val(fila.find("td:eq(3)").text());
            $("#actGestion").val(fila.find("td:eq(4)").text());
            $("#actOtras").val(fila.find("td:eq(5)").text());

            if (accion === 'modificar') {
                $("#proceso").prop('disabled', true);
                $(".modal-footer").prepend('<div id="modification_tip_wrapper" class="w-100 text-center mb-2"><small class="form-text text-danger">Realice un cambio para poder modificar.</small></div>');
                initialState = $('#f').serialize();
            }
        }, accion);

        if (accion === 'modificar') {
            $("#modal1 .modal-header").removeClass('bg-danger').addClass('bg-primary');
            $("#proceso").text("MODIFICAR");
            $("#modal1 .modal-title").text("Modificar Actividad");
            $("#docId").prop('disabled', true);
        } else if (accion === 'eliminar') {
            $("#modal1 .modal-header").removeClass('bg-primary').addClass('bg-danger');
            $("#proceso").text("ELIMINAR");
            $("#modal1 .modal-title").text("Confirmar Eliminación");
            $("form#f :input").not("[data-bs-dismiss='modal']").prop('disabled', true);
            $("#proceso").prop('disabled', false);
        }
        
        $("#modal1").modal("show");
    }

    function enviaAjax(datos) {
        $.ajax({
            async: true, url: "", type: "POST", contentType: false, data: datos, processData: false, cache: false,
            success: function (respuesta) {
                try {
                    var lee = JSON.parse(respuesta);
                    if (lee.resultado === "consultar") {
                        destruyeDT();
                        $("#resultadoconsulta").empty();
                        $.each(lee.mensaje, function (index, item) {
                            $("#resultadoconsulta").append(`
                                <tr data-docid='${item.doc_cedula}'>
                                    <td style="display: none;">${item.doc_cedula}</td>
                                    <td>${item.doc_nombre} ${item.doc_apellido}</td>
                                    <td>${item.act_creacion_intelectual}</td>
                                    <td>${item.act_integracion_comunidad}</td>
                                    <td>${item.act_gestion_academica}</td>
                                    <td>${item.act_otras}</td>
                                    <td><span class="badge bg-primary fs-6">${item.horas_totales}</span></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm modificar-btn"><img src="public/assets/icons/edit.svg" alt="Modificar"></button>
                                        <button class="btn btn-danger btn-sm eliminar-btn"><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>
                                    </td>
                                </tr>`);
                        });
                        crearDT();
                    } else if (lee.resultado == "registrar" || lee.resultado == "modificar" || lee.resultado == "eliminar") {
                        muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
                    } else if (lee.resultado == "error") {
                        muestraMensaje("error", 10000, "ERROR", lee.mensaje);
                    }
                } catch (e) {
                    alert("Error en JSON " + e);
                }
            },
            error: function (request, status, err) {
                muestraMensaje("error", 6000, "ERROR DE SERVIDOR", `${status}: ${err}`);
            }
        });
    }

    function limpia() {
        $("#f")[0].reset();
        $("#actId").val("");
        $("#docId").val("").prop('disabled', false);
        $("#sdocId").text("").removeClass("text-danger text-success");
        $("#sHorasTotales").text("");
        $("#proceso").prop('disabled', true);
        formInteracted = false;
        initialState = '';
        $('#modification_tip_wrapper').remove();
        $("#modal1 .modal-header").removeClass('bg-danger').addClass('bg-primary');
    }

    function muestraMensaje(tipo, duracion, titulo, mensaje) {
        Swal.fire({ icon: tipo, title: titulo, html: mensaje, timer: duracion, timerProgressBar: true });
    }

    function verificarRequisitosIniciales() {
        const totalDocentes = parseInt($('.main-content').data('total-docentes'), 10);

        if (totalDocentes === 0) {
            const botonRegistrar = $("#registrar");
            const mensajeTooltip = "Debe registrar al menos un docente antes de asignar actividades.";
            
            botonRegistrar.prop('disabled', true).attr('title', mensajeTooltip);
            
            Swal.fire({
                icon: 'warning',
                title: 'No Hay Docentes Registrados',
                text: 'Para poder registrar una actividad, primero debe existir al menos un docente en el sistema.',
                confirmButtonText: 'Entendido'
            });
        }
    }
});