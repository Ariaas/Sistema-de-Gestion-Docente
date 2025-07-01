function cargarUnidadesCurriculares() {
    var datos = new FormData();
    datos.append("accion", "consultar_ucs");
    enviaAjax(datos);
}

function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
}

function destruyeDT(selector = "#tablamalla") {
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();
    }
}

function crearDT(selector = "#tablamalla", config = {}) {
    const defaultConfig = {
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
            infoEmpty: "No hay registros disponibles para mostrar",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" },
            emptyTable: "No hay datos disponibles en la tabla"
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [[1, "asc"]],
    };
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({ ...defaultConfig, ...config });
    }
}

$(document).ready(function () {
    Listar();
    cargarUnidadesCurriculares();

    $('#select_uc').select2({
        theme: "bootstrap-5",
        dropdownParent: $('#modal1')
    });

    $('#btn-siguiente').on('click', function () {
        if (validarPagina1()) {
            $('#pagina1').hide(); $('#botones-pagina1').hide();
            $('#pagina2').show(); $('#botones-pagina2').show();
            $('#modal1Titulo').text("Formulario de Malla (Paso 2 de 2)");
        }
    });

    $('#btn-anterior').on('click', function () {
        $('#pagina2').hide(); $('#botones-pagina2').hide();
        $('#pagina1').show(); $('#botones-pagina1').show();
        $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)");
    });

    $("#filtroUnidadesAgregadas").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#tablaUnidadesAgregadas tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('#modalVerMalla').on('hidden.bs.modal', function () { destruyeDT('#tablaVerUnidades'); });

    $("#btn_agregar_uc").on("click", function () {
        const select = $("#select_uc");
        const uc_id = select.val();
        const uc_nombre = select.find("option:selected").text();
        if (!uc_id) {
            muestraMensaje("error", 3000, "ERROR", "Debe seleccionar una unidad curricular.");
            return;
        }
        let yaExiste = false;
        $("#tablaUnidadesAgregadas tbody tr").each(function () {
            if ($(this).data("ucid") == uc_id) { yaExiste = true; }
        });
        if (yaExiste) {
            muestraMensaje("info", 3000, "Atención", "Esa unidad curricular ya fue agregada.");
            return;
        }

        const h_ind = $("#uc_horas_ind").val() || 0;
        const h_asis = $("#uc_horas_asis").val() || 0;
        const h_acad = $("#uc_horas_acad").val() || 0;

        const fila = `
            <tr data-ucid="${uc_id}">
                <td class="align-middle">${uc_nombre}</td>
                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${h_ind}" min="0"></td>
                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${h_asis}" min="0"></td>
                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${h_acad}" min="0"></td>
                <td class="align-middle"><button type="button" class="btn btn-danger btn-sm btn-remover-uc">X</button></td>
            </tr>`;

        $("#tablaUnidadesAgregadas tbody").append(fila);
        $("#contenedorTablaUnidades").show();

        select.val('').trigger('change');
        $("#uc_horas_ind").val(0);
        $("#uc_horas_asis").val(0);
        $("#uc_horas_acad").val(0);
    });

    $("#tablaUnidadesAgregadas").on("click", ".btn-remover-uc", function () {
        $(this).closest("tr").remove();
        if ($("#tablaUnidadesAgregadas tbody tr").length === 0) {
            $("#contenedorTablaUnidades").hide();
        }
    }); 
    
    
    $("#mal_codigo").on("keydown ", function () {
  
   validarkeyup(/^[A-Za-z0-9\s-]{2,10}$/, $("#mal_codigo"), $("#smalcodigo"),"El código permite de 2 a 10 caracteres alfanuméricos, espacios o guiones.");
   
    });

    $("#mal_codigo").on("keyup", function () {
        validarkeyup(/^[A-Za-z0-9\s-]{2,10}$/, $("#mal_codigo"), $("#smalcodigo"),"El código permite de 2 a 10 caracteres alfanuméricos, espacios o guiones.");
        var datos = new FormData();
        datos.append('accion', 'existe');
        datos.append("mal_codigo",$("#mal_codigo").val());
        if ($("#mal_id").val()) datos.append("mal_id", $("#mal_id").val());
        enviaAjax(datos);
    });

    $("#mal_nombre").on("keydown", function () {
  
    validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,20}$/,$("#mal_nombre"),$("#smalnombre"),"El formato permite de 5 a 20 carácteres, Ej:Malla 1");
   
    });
    $("#mal_nombre").on("keyup", function () {
       validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{5,20}$/,$("#mal_nombre"),$("#smalnombre"),"El formato permite de 5 a 20 carácteres, Ej:Malla 1");
       
    });
     $("#mal_descripcion").on("keydown", function () {
        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $("#mal_descripcion"), $("#smaldescripcion"),"El formato permite de 5 a 30 carácteres, una breve descripcion");
    });
    
    $("#mal_descripcion").on("keyup", function () {
        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $("#mal_descripcion"), $("#smaldescripcion"),"El formato permite de 5 a 30 carácteres, una breve descripcion  ");
    });
    $("#mal_cohorte").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
        if (this.value.length > 3) this.value = this.value.slice(0, 3);
    });
    $("#mal_cohorte").on("keydown", function () {
    validarkeyup(/^[1-9][0-9]{0,3}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 3 carácteres, solo numero enteros EJ:4")
    });


     $("#mal_cohorte").on(" keyup", function () {
    validarkeyup(/^[1-9][0-9]{0,3}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 3 carácteres, solo numero enteros EJ:4")
    
        var datos = new FormData();
        datos.append('accion', 'existe_cohorte');
        datos.append("mal_cohorte",$("#mal_cohorte").val());
        if ($("#mal_id").val()) datos.append("mal_id", $("#mal_id").val());
        enviaAjax(datos);   
});

 $("#uc_horas_ind, #uc_horas_asis, #uc_horas_acad").on("input", function() {
        let valor = $(this).val();
        valor = valor.replace(/[^0-9]/g, '');
        if (valor.length > 1) {
            valor = valor.replace(/^0+/, '');
        }
        if (valor.length > 3) {
            valor = valor.slice(0, 3);
        }
        $(this).val(valor);
    });

    
$("#tablaUnidadesAgregadas").on("input", ".horas-input", function() {
    let valor = $(this).val();
    valor = valor.replace(/[^0-9]/g, '');
    if (valor.length > 1) {
        valor = valor.replace(/^0+/, '');
    }
    if (valor.length > 3) {
        valor = valor.slice(0, 3);
    }
    $(this).val(valor);
});


    $("#proceso").on("click", function () {
        if (validarenvio()) {
            var datos = new FormData($("#f")[0]);
            let unidades = [];
            $("#tablaUnidadesAgregadas tbody tr").each(function () {
                let fila = $(this);
                unidades.push({
                    uc_id: fila.data("ucid"),
                    hora_independiente: fila.find("td:eq(1) input").val() || 0,
                    hora_asistida: fila.find("td:eq(2) input").val() || 0,
                    hora_academica: fila.find("td:eq(3) input").val() || 0
                });
            });
            datos.append("unidades", JSON.stringify(unidades));
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function () {
        limpiaModal1();
        $("#accion").val("registrar");
        $("#modal1Titulo").text("Formulario de Malla (Paso 1 de 2)");
        $("#proceso").text("GUARDAR");
        $("#modal1").modal("show");
    });
});

function validarPagina1() {
    if (validarkeyup(/^[A-Za-z0-9\s-]{2,10}$/, $("#mal_codigo"), $("#smalcodigo"), "El código permite de 2 a 10 caracteres alfanuméricos, espacios o guiones.") == 0) {
        muestraMensaje("error", 4000, "ERROR", "El codigo de la malla <br/> No puede estar vacío y debe contener entre 2 a 10 carácteres.");
        return false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{5,20}$/, $("#malnombre"), $("#smalnombre"), "El formato permite de 5 a 20 carácteres, Ej:Malla 1") == 0) {
        muestraMensaje("error", 4000, "ERROR", "El nombre de la malla <br/> No puede estar vacío y debe contener entre 5 a 20 carácteres");
        return false;
    }
    if (validarkeyup(/^[1-9][0-9]{0,3}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 3 carácteres, solo numero enteros EJ:4.") == 0) {
        muestraMensaje("error", 4000, "ERROR", "La cohorte de la malla <br/> No puede estar vacío y debe contener entre 1 a 3 carácteres.");
        return false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $("#mal_descripcion"), $("#smaldescripcion"), "El formato permite de 5 a 30 carácteres, una breve descripcion") == 0) {
        muestraMensaje("error", 4000, "ERROR", "La descripcion de la malla <br/> No puede estar vacía y debe contener entre 5 a 30 carácteres.");
        return false;
    }
    return true;
}

function validarenvio() {
    if (!validarPagina1()) return false;
    if ($("#tablaUnidadesAgregadas tbody tr").length === 0) {
        muestraMensaje("error", 4000, "ERROR", "Debe agregar al menos una unidad curricular.");
        return false;
    }
    return true;
}

function pone(pos, accionBtn) {
    linea = $(pos).closest("tr");
    var mal_id = $(linea).find("td:eq(0)").text();
    var mal_nombre = $(linea).find("td:eq(2)").text();

    if (accionBtn === 2) {
        $("#modalVerMallaTitulo").text("Unidades de: " + mal_nombre);
        destruyeDT('#tablaVerUnidades');
        const tablaBodyVer = $("#tablaUnidadesVer");
        tablaBodyVer.html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></td></tr>');
        $("#modalVerMalla").modal("show");
        $('#modalVerMalla').off('shown.bs.modal').on('shown.bs.modal', function () {
            var datos = new FormData();
            datos.append("accion", "consultar_ucs_por_malla");
            datos.append("mal_id", mal_id);
            enviaAjax(datos);
        });
        return;
    }

    limpiaModal1();
    $("#mal_id").val(mal_id);
    $("#mal_codigo").val($(linea).find("td:eq(1)").text());
    $("#mal_nombre").val(mal_nombre);
    $("#mal_cohorte").val($(linea).find("td:eq(3)").text());
    $("#mal_descripcion").val($(linea).find("td:eq(4)").text());

    if (accionBtn === 0) {
        $("#accion").val("modificar");
        $("#modal1Titulo").text("Modificar Malla (Paso 1 de 2)");
        $("#proceso").text("MODIFICAR");
        $("#modal1").modal("show");
        $('#modal1').off('shown.bs.modal').on('shown.bs.modal', function () {
            var datos = new FormData();
            datos.append("accion", "consultar_ucs_por_malla");
            datos.append("mal_id", mal_id);
            enviaAjax(datos);
        });
    } else if (accionBtn === 1) {
        Swal.fire({
            title: "¿Está seguro de eliminar esta malla?", text: "Esta acción no se puede deshacer.",
            icon: "warning", showCancelButton: true, confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6", confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var datos = new FormData(); datos.append("accion", "eliminar"); datos.append("mal_id", mal_id);
                enviaAjax(datos);
            }
        });
    }
}

function enviaAjax(datos) {
    $.ajax({
        async: true, url: "", type: "POST", contentType: false, data: datos,
        processData: false, cache: false,
        success: function (respuesta) {
            try {
                var lee = JSON.parse(respuesta);

                switch (lee.resultado) {
                    case 'consultar':
                        destruyeDT("#tablamalla");
                        $("#resultadoconsulta").empty();
                        $.each(lee.mensaje, function (index, item) {
                            let botonesAccion = `<td class="acciones-cell"><button class="btn btn-info btn-sm" onclick='pone(this,2)'>Ver Malla</button> <button class="btn btn-warning btn-sm" onclick='pone(this,0)'>Modificar</button> <button class="btn btn-danger btn-sm" onclick='pone(this,1)'>Eliminar</button></td>`;
                            $("#resultadoconsulta").append(`<tr><td style="display: none;">${item.mal_id}</td><td>${item.mal_codigo}</td><td>${item.mal_nombre}</td><td>${item.mal_cohorte}</td><td>${item.mal_descripcion}</td>${botonesAccion}</tr>`);
                        });
                        crearDT("#tablamalla");
                        break;

                    case 'ok':
                        if (lee.accion === 'consultar_ucs') {
                            const select = $("#select_uc");
                            select.empty().append('<option value="">Seleccione...</option>');
                            if (lee.mensaje.length > 0) {
                                $.each(lee.mensaje, function (i, item) { select.append(`<option value="${item.uc_id}">${item.uc_nombre}</option>`); });
                            } else { select.append('<option value="" disabled>No hay unidades disponibles</option>'); }
                            select.trigger('change');
                        } else if (lee.accion === 'consultar_ucs_por_malla') {
                            if ($('#modal1').is(':visible')) {
                                if (lee.mensaje.length > 0) {
                                    $("#contenedorTablaUnidades").show();
                                    lee.mensaje.forEach(function (uc) {
                                        const fila = `
                                            <tr data-ucid="${uc.uc_id}">
                                                <td class="align-middle">${uc.uc_nombre}</td>
                                                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${uc.mal_hora_independiente}" min="0"></td>
                                                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${uc.mal_hora_asistida}" min="0"></td>
                                                <td><input type="number" class="form-control form-control-sm text-center horas-input" value="${uc.mal_hora_academica}" min="0"></td>
                                                <td class="align-middle"><button type="button" class="btn btn-danger btn-sm btn-remover-uc">X</button></td>
                                            </tr>`;
                                        $("#tablaUnidadesAgregadas tbody").append(fila);
                                    });
                                }
                            } else if ($('#modalVerMalla').is(':visible')) {
                                const tablaBody = $("#tablaUnidadesVer");
                                tablaBody.empty();
                                if (lee.mensaje.length > 0) {
                                    lee.mensaje.forEach(function (uc) {
                                        const fila = `<tr><td>${uc.uc_nombre}</td><td>${uc.mal_hora_independiente}</td><td>${uc.mal_hora_asistida}</td><td>${uc.mal_hora_academica}</td></tr>`;
                                        tablaBody.append(fila);
                                    });
                                } else { tablaBody.append('<tr><td colspan="4">Esta malla no tiene unidades curriculares asignadas.</td></tr>'); }
                                crearDT('#tablaVerUnidades');
                            }
                        }
                        break;

                    case 'registrar':
                    case 'modificar':
                    case 'eliminar':
                        muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                        $("#modal1").modal("hide");
                        Listar();
                        break;

                    case 'existe':
                        muestraMensaje('info', 4000, 'Atención!', lee.mensaje);
                        break;

                    case 'error':
                        muestraMensaje("error", 10000, "ERROR", lee.mensaje);
                        break;
                }
            } catch (e) {
                console.error("Error en análisis JSON:", e, "Respuesta:", respuesta);
                muestraMensaje("error", 10000, "Error de Comunicación", "No se pudo procesar la respuesta del servidor.");
            }
        },
        error: function (request, status, err) {
            muestraMensaje("error", 5000, "Error de Comunicación", "ERROR: " + request.responseText);
        },
    });
}

function limpiaModal1() {
    $("#f")[0].reset();
    $("#mal_id").val("");
    $(".form-control").removeClass("is-invalid is-valid").prop("disabled", false);
    $(".validation-span").empty();
    $('#pagina2').hide();
    $('#botones-pagina2').hide();
    $('#pagina1').show();
    $('#botones-pagina1').show();
    $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)");
    $("#tablaUnidadesAgregadas tbody").empty();
    $("#contenedorTablaUnidades").hide();
    $("#select_uc").val(null).trigger('change');
    $("#filtroUnidadesAgregadas").val('');
}



// No se encontraron comentarios en este archivo. Solo código JavaScript.
function validarkeyup(er, etiqueta, etiquetamensaje, mensaje = "") {
    if (er.test(etiqueta.val())) {
        if (etiquetamensaje) etiquetamensaje.text("");
        etiqueta.removeClass('is-invalid').addClass('is-valid');
        return 1;
    } else {
        if (etiquetamensaje) etiquetamensaje.text(mensaje);
        etiqueta.removeClass('is-valid').addClass('is-invalid');
        return 0;
    }
}