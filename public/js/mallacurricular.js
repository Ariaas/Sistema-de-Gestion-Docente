let ucsAgrupadas = {};

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
        paging: true, lengthChange: true, searching: true, ordering: true, info: true, autoWidth: false, responsive: true,
        language: { lengthMenu: "Mostrar _MENU_ registros", zeroRecords: "No se encontraron resultados", info: "Mostrando _PAGE_ de _PAGES_", infoEmpty: "No hay registros disponibles para mostrar", infoFiltered: "(filtrado de _MAX_ registros totales)", search: "Buscar:", paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }, emptyTable: "No hay datos disponibles en la tabla" },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [[1, "asc"]],
    };
    if (!$.fn.DataTable.isDataTable(selector)) { $(selector).DataTable({ ...defaultConfig, ...config }); }
}

function gestionarBotonGuardar() {
    let todoCompleto = true;
    if (Object.keys(ucsAgrupadas).length === 0) {
        todoCompleto = false;
    }

    for (const trayecto in ucsAgrupadas) {
        for (const uc of ucsAgrupadas[trayecto]) {
            if (!uc.h_indep || uc.h_indep <= 0 || !uc.h_asist || uc.h_asist <= 0 || !uc.h_acad || uc.h_acad <= 0) {
                todoCompleto = false;
                break;
            }
        }
        if (!todoCompleto) break;
    }

    if (todoCompleto) {
        $("#proceso").show();
    } else {
        $("#proceso").hide();
    }
}

function renderizarTabsUCs() {
    for (const trayecto in ucsAgrupadas) {
        const contenedor = $(`#trayecto-${trayecto}`);
        contenedor.empty();
        const ucs = ucsAgrupadas[trayecto];
        if (ucs.length === 0) {
            contenedor.html('<p class="text-center text-muted mt-3">No hay unidades curriculares para este trayecto.</p>');
            continue;
        }

        const tabla = $(`
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Unidad Curricular</th>
                            <th title="Horas de Trabajo Independiente">H. Indep.</th>
                            <th title="Horas con Asistencia del Docente">H. Asist.</th>
                            <th title="Horas Totales del Estudiante (calculado)">HTE</th>
                            <th title="Horas Académicas">H. Acad.</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        `);

        const cuerpoTabla = tabla.find('tbody');
        ucs.forEach(uc => {
            const h_indep_val = uc.h_indep > 0 ? `value="${uc.h_indep}"` : '';
            const h_asist_val = uc.h_asist > 0 ? `value="${uc.h_asist}"` : '';
            const h_total_val = uc.h_total > 0 ? `value="${uc.h_total}"` : '';
            const h_acad_val = uc.h_acad > 0 ? `value="${uc.h_acad}"` : '';

            const fila = `
                <tr data-uc_codigo="${uc.uc_codigo}">
                    <td class="align-middle text-start">${uc.uc_nombre}</td>
                    <td><input type="text" class="form-control form-control-sm text-center horas-input h-indep" ${h_indep_val}></td>
                    <td><input type="text" class="form-control form-control-sm text-center horas-input h-asist" ${h_asist_val}></td>
                    <td><input type="text" class="form-control form-control-sm text-center h-total" ${h_total_val} readonly></td>
                    <td><input type="text" class="form-control form-control-sm text-center horas-input h-acad" ${h_acad_val}></td>
                </tr>`;
            cuerpoTabla.append(fila);
        });
        contenedor.append(tabla);
    }
}

function actualizarDatosUC(ucCodigo, campo, valor) {
    for (const trayecto in ucsAgrupadas) {
        const ucIndex = ucsAgrupadas[trayecto].findIndex(uc => uc.uc_codigo === ucCodigo);
        if (ucIndex !== -1) {
            ucsAgrupadas[trayecto][ucIndex][campo] = valor;
            return;
        }
    }
}

$(document).ready(function () {
    Listar();
    var datos = new FormData();
    datos.append("accion", "consultar_ucs");
    enviaAjax(datos);

    $('#modal1').on('hidden.bs.modal', function () { limpiaModal1(); });
    $('#modalVerMalla').on('hidden.bs.modal', function () { $('#cuerpoModalVer').empty(); });

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
    
    $('#ucTabsContent').on('input', '.horas-input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 3) {
            this.value = this.value.slice(0, 3);
        }

        const fila = $(this).closest('tr');
        const ucCodigo = fila.data('uc_codigo');
        
        const hIndepInput = fila.find('.h-indep');
        const hAsistInput = fila.find('.h-asist');
        const hTotalInput = fila.find('.h-total');
        const hAcadInput = fila.find('.h-acad');
        
        const hIndep = parseInt(hIndepInput.val()) || 0;
        const hAsist = parseInt(hAsistInput.val()) || 0;
        const hAcad = parseInt(hAcadInput.val()) || 0;
        
        const hTotal = hIndep + hAsist;
        hTotalInput.val(hTotal);

        actualizarDatosUC(ucCodigo, 'h_indep', hIndep);
        actualizarDatosUC(ucCodigo, 'h_asist', hAsist);
        actualizarDatosUC(ucCodigo, 'h_total', hTotal);
        actualizarDatosUC(ucCodigo, 'h_acad', hAcad);
        
        gestionarBotonGuardar();
    });

    $("#mal_codigo").on("keyup", function () {
        validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $(this), $("#smalcodigo"),"El código permite de 2 a 20 caracteres alfanuméricos, espacios o guiones.");
        if ($("#accion").val() === 'registrar') {
            var datos = new FormData();
            datos.append('accion', 'existe');
            datos.append("mal_codigo",$(this).val());
            enviaAjax(datos);
        }
    });

    $("#mal_nombre").on("keyup", function () {
       validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/,$(this),$("#smalnombre"),"El formato permite de 5 a 30 caracteres. Ej: Malla 2024");
    });
    
    $("#mal_descripcion").on("keyup", function () {
        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $(this), $("#smaldescripcion"),"El formato permite de 5 a 30 caracteres, una breve descripcion.");
    });

    $("#mal_cohorte").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
        if (this.value.length > 3) this.value = this.value.slice(0, 3);
    });

    $("#mal_cohorte").on("keyup", function () {
        validarkeyup(/^[1-9][0-9]{0,3}$/,$(this),$("#smalcohorte"),"El formato permite de 1 a 3 caracteres, solo numeros enteros EJ:4");
        var datos = new FormData();
        datos.append('accion', 'existe_cohorte');
        datos.append("mal_cohorte",$(this).val());
        if ($("#accion").val() === 'modificar') {
            datos.append("mal_codigo", $("#mal_codigo").val());
        }
        enviaAjax(datos);   
    });
    
    $("#proceso").on("click", function () {
        if (validarenvio()) {
            $("#mal_codigo").prop("disabled", false);
            var datos = new FormData($("#f")[0]);
            let unidadesSeleccionadas = [];
            for(const trayecto in ucsAgrupadas){
                ucsAgrupadas[trayecto].forEach(uc => {
                    unidadesSeleccionadas.push({
                        uc_codigo: uc.uc_codigo,
                        hora_independiente: uc.h_indep,
                        hora_asistida: uc.h_asist,
                        hora_academica: uc.h_acad
                    });
                });
            }
            datos.append("unidades", JSON.stringify(unidadesSeleccionadas));
            
            enviaAjax(datos);
        }
    });

    $("#registrar").on("click", function () {
        $("#accion").val("registrar");
        $("#modal1Titulo").text("Formulario de Malla (Paso 1 de 2)");
        $("#proceso").text("GUARDAR");
        $("#modal1").modal("show");
    });
});

function validarenvio() {
    if (!validarPagina1()) return false;
    
    gestionarBotonGuardar();
    if ($("#proceso").is(':hidden')) {
        muestraMensaje("error", 4000, "ERROR", "Debe asignar horas (mayores a 0) a todas las unidades curriculares de todos los trayectos.");
        return false;
    }
    return true;
}

function pone(pos, accionBtn) {
    linea = $(pos).closest("tr");
    var mal_codigo = $(linea).find("td:eq(0)").text();
    var mal_nombre = $(linea).find("td:eq(1)").text();

    if (accionBtn === 2) {
        $("#modalVerMallaTitulo").text("Unidades de: " + mal_nombre);
        $("#cuerpoModalVer").html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        $("#modalVerMalla").modal("show");
        
        var datos = new FormData();
        datos.append("accion", "consultar_ucs_por_malla");
        datos.append("mal_codigo", mal_codigo);
        enviaAjax(datos);
        return;
    }
 
    $("#mal_codigo").val(mal_codigo).prop("disabled", true);
    $("#mal_nombre").val(mal_nombre);
    $("#mal_cohorte").val($(linea).find("td:eq(2)").text());
    $("#mal_descripcion").val($(linea).find("td:eq(3)").text());

    if (accionBtn === 0) {
        $("#accion").val("modificar");
        $("#modal1Titulo").text("Modificar Malla (Paso 1 de 2)");
        $("#proceso").text("MODIFICAR");
        $("#modal1").modal("show");
        
        $('#modal1').off('shown.bs.modal').on('shown.bs.modal', function () {
            var datos = new FormData();
            datos.append("accion", "consultar_ucs_por_malla");
            datos.append("mal_codigo", mal_codigo);
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
                var datos = new FormData(); 
                datos.append("accion", "eliminar"); 
                datos.append("mal_codigo", mal_codigo);
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
                            let estadoActiva = (item.mal_activa == 1) ? '<span class="badge bg-success">Activa</span>' : '<button class="btn btn-xs btn-secondary btn-activar">Activar</button>';
                            let botonesAccion = `<td class="acciones-cell"><button class="btn btn-info btn-sm" onclick='pone(this,2)'>Ver Malla</button> <button class="btn btn-warning btn-sm" onclick='pone(this,0)'>Modificar</button> <button class="btn btn-danger btn-sm" onclick='pone(this,1)'>Eliminar</button></td>`;
                            $("#resultadoconsulta").append(`<tr><td>${item.mal_codigo}</td><td>${item.mal_nombre}</td><td>${item.mal_cohorte}</td><td>${item.mal_descripcion}</td><td>${estadoActiva}</td>${botonesAccion}</tr>`);
                        });
                        crearDT("#tablamalla");
                        break;
                    case 'ok':
                        if (lee.accion === 'consultar_ucs') {
                            ucsAgrupadas = { '0': [], '1': [], '2': [], '3': [], '4': [] };
                            lee.mensaje.forEach(uc => {
                                const trayecto = uc.uc_trayecto;
                                if(ucsAgrupadas[trayecto] !== undefined){
                                    ucsAgrupadas[trayecto].push({...uc, h_indep: 0, h_asist: 0, h_total: 0, h_acad: 0 });
                                }
                            });
                            renderizarTabsUCs();
                            gestionarBotonGuardar();
                        } else if (lee.accion === 'consultar_ucs_por_malla') {
                            if ($('#modal1').is(':visible')) { // Para Modificar
                                // Primero, reiniciamos las horas de todas las UCs a 0
                                for(const trayecto in ucsAgrupadas){
                                    ucsAgrupadas[trayecto].forEach(uc => {
                                        uc.h_indep = 0; uc.h_asist = 0; uc.h_total = 0; uc.h_acad = 0;
                                    });
                                }
                                // Luego, actualizamos solo las que vienen de la base de datos
                                lee.mensaje.forEach(function(uc_sel) {
                                    actualizarDatosUC(uc_sel.uc_codigo, 'h_indep', parseInt(uc_sel.mal_hora_independiente));
                                    actualizarDatosUC(uc_sel.uc_codigo, 'h_asist', parseInt(uc_sel.mal_hora_asistida));
                                    actualizarDatosUC(uc_sel.uc_codigo, 'h_total', parseInt(uc_sel.mal_hora_independiente) + parseInt(uc_sel.mal_hora_asistida));
                                    actualizarDatosUC(uc_sel.uc_codigo, 'h_acad', parseInt(uc_sel.mal_hora_academica));
                                });
                                renderizarTabsUCs();
                                gestionarBotonGuardar();
                            } else if ($('#modalVerMalla').is(':visible')) { // Para Ver Malla
                                const cuerpoModal = $("#cuerpoModalVer");
                                cuerpoModal.empty();

                                if(lee.mensaje.length === 0){
                                    cuerpoModal.html('<p class="text-center text-muted p-3">Esta malla no tiene unidades curriculares asignadas.</p>');
                                    return;
                                }

                                cuerpoModal.append('<ul class="nav nav-tabs" id="verUcTabs" role="tablist"></ul><div class="tab-content border border-top-0 p-3" id="verUcTabsContent"></div>');
                                
                                let gruposVer = {'0':[], '1':[], '2':[], '3':[], '4':[]};
                                lee.mensaje.forEach(uc => {
                                    if(gruposVer[uc.uc_trayecto] !== undefined) gruposVer[uc.uc_trayecto].push(uc);
                                });

                                let primerTab = true;
                                for(const trayecto in gruposVer){
                                    if(gruposVer[trayecto].length > 0){
                                        const nombreTrayecto = (trayecto == '0') ? 'T. Inicial' : `Trayecto ${'I'.repeat(parseInt(trayecto))}`;
                                        $('#verUcTabs').append(`<li class="nav-item" role="presentation"><button class="nav-link ${primerTab ? 'active' : ''}" data-bs-toggle="tab" data-bs-target="#ver-trayecto-${trayecto}" type="button">${nombreTrayecto}</button></li>`);
                                        $('#verUcTabsContent').append(`<div class="tab-pane fade ${primerTab ? 'show active' : ''}" id="ver-trayecto-${trayecto}"></div>`);
                                        primerTab = false;
                                        
                                        const tabla = $('<div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Unidad Curricular</th><th>H. Indep.</th><th>H. Asist.</th><th>H. Acad.</th></tr></thead><tbody></tbody></table></div>');
                                        gruposVer[trayecto].forEach(uc => {
                                            tabla.find('tbody').append(`<tr><td>${uc.uc_nombre}</td><td>${uc.mal_hora_independiente}</td><td>${uc.mal_hora_asistida}</td><td>${uc.mal_hora_academica}</td></tr>`);
                                        });
                                        $(`#ver-trayecto-${trayecto}`).append(tabla);
                                    }
                                }
                            }
                        }
                        break;
                    case 'registrar': case 'modificar': case 'eliminar': case 'activar':
                        muestraMensaje("success", 4000, lee.resultado.toUpperCase(), lee.mensaje);
                        if (lee.resultado !== 'activar') $("#modal1").modal("hide");
                        Listar();
                        limpiaModal1();
                        break;
                    case 'existe': case 'existe_cohorte':
                        if(lee.resultado == 'existe'){ muestraMensaje('info', 4000, 'Atención!', lee.mensaje); }
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
    $(".form-control").removeClass("is-invalid is-valid").prop("disabled", false);
    $(".validation-span").empty();
    $('#pagina2').hide(); $('#botones-pagina2').hide();
    $('#pagina1').show(); $('#botones-pagina1').show();
    $('#modal1Titulo').text("Formulario de Malla (Paso 1 de 2)");
    
    for(const trayecto in ucsAgrupadas){
        ucsAgrupadas[trayecto].forEach(uc => {
            uc.h_indep = 0;
            uc.h_asist = 0;
            uc.h_total = 0;
            uc.h_acad = 0;
        });
    }
    renderizarTabsUCs();
    gestionarBotonGuardar();
}

function validarPagina1() {
    let esValido = true;
    if (validarkeyup(/^[A-Za-z0-9\s-]{2,20}$/, $("#mal_codigo"), $("#smalcodigo"), "El código permite de 2 a 20 caracteres alfanuméricos, espacios o guiones.") == 0) {
       esValido = false;
    }
    if ( validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s,\-_]{5,30}$/,$("#mal_nombre"),$("#smalnombre"), "El formato permite de 5 a 30 caracteres, Ej:Malla 2024") == 0) {
        esValido = false;
    }
    if (validarkeyup(/^[1-9][0-9]{0,3}$/,$("#mal_cohorte"),$("#smalcohorte"),"El formato permite de 1 a 3 caracteres, solo numeros enteros EJ:4.") == 0) {
        esValido = false;
    }
    if (validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,30}$/, $("#mal_descripcion"), $("#smaldescripcion"), "El formato permite de 5 a 30 caracteres, una breve descripcion") == 0) {
        esValido = false;
    }
    if(!esValido) muestraMensaje("error", 4000, "ERROR", "Por favor, corrija los campos marcados en rojo.");
    return esValido;
}

function muestraMensaje(icono, tiempo, titulo, mensaje) {
    Swal.fire({ icon: icono, timer: tiempo, title: titulo, html: mensaje, showConfirmButton: false });
}

function validarkeyup(er, etiqueta, etiquetamensaje, mensaje = "") {
    if (etiqueta.prop('disabled')) return 1;
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