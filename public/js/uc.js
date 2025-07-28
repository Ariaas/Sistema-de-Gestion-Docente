function Listar() {
    var datos = new FormData();
    datos.append("accion", "consultar");
    enviaAjax(datos);
}

function destruyeDT(selector) {

    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().destroy();
    }
}

function crearDT(selector) {
    if (!$.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable({
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

function verificarRequisitosIniciales() {
    const totalEjes = parseInt($('.main-content').data('total-ejes'), 10);
    const totalAreas = parseInt($('.main-content').data('total-areas'), 10);
    const registrarBtn = $("#registrar");
    const warningSpan = $("#registrar-warning");
    let warningMsg = "";

    if (totalEjes === 0 && totalAreas === 0) {
        warningMsg = "Debe registrar al menos un Eje y un Área.";
    } else if (totalEjes === 0) {
        warningMsg = "Debe registrar al menos un Eje.";
    } else if (totalAreas === 0) {
        warningMsg = "Debe registrar al menos un Área.";
    }

    if (warningMsg) {
        registrarBtn.prop("disabled", true).attr("title", warningMsg);
        warningSpan.text(warningMsg);
    }
}

$(document).ready(function() {
    Listar();
    verificarRequisitosIniciales();

    destruyeDT("#tablauc");
    crearDT("#tablauc");

    destruyeDT("#tabladocente");
    crearDT("#tabladocente");

    let docentesAsignadosUC = [];

    $(document).on("click", ".asignar-uc", function() {
        ucSeleccionada = $(this).closest("tr").data("codigo");
        carritoDocentes = [];
        actualizarCarritoDocentes();

        var datos = new FormData();
        datos.append('accion', 'ver_docentes');
        datos.append('codigo', ucSeleccionada);
        enviaAjax(datos, 'cargarDocentesAsignados');
    });

    $("#proceso").on("click", function() {
        if ($(this).text() == "MODIFICAR") {
            if (validarenvio()) {
                var datos = new FormData($("#f")[0]);
                datos.append("accion", "modificar");
                datos.append("codigoUCOriginal", originalCodigoUC);
                enviaAjax(datos);
            }
        } else if ($(this).text() == "REGISTRAR") {
            if (validarenvio()) {
                var datos = new FormData($("#f")[0]);
                datos.append("accion", "registrar");
                enviaAjax(datos);
            }
        }
        if ($(this).text() == "ELIMINAR") {
            var codigoUC = $("#codigoUC").val();
            var datosVerificacion = new FormData();
            datosVerificacion.append("accion", "verificar_horario");
            datosVerificacion.append("codigoUC", codigoUC);

            $.ajax({
                async: true,
                url: "",
                type: "POST",
                contentType: false,
                data: datosVerificacion,
                processData: false,
                cache: false,
                success: function(respuesta) {
                    try {
                        var lee = JSON.parse(respuesta);
                        let titulo = "¿Está seguro de eliminar esta unidad curricular?";
                        let texto = "Esta acción no se puede deshacer.";

                        if (lee.resultado === 'en_horario') {
                            titulo = "¡Atención!";
                            texto = "Esta unidad curricular está en un horario. Si la elimina, se quitará del horario también. ¿Desea continuar?";
                        }

                        Swal.fire({
                            title: titulo,
                            text: texto,
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
                                datos.append("codigoUC", codigoUC);
                                enviaAjax(datos);
                            } else {
                                muestraMensaje("info", 2000, "INFORMACIÓN", "La eliminación ha sido cancelada.");
                                $("#modal1").modal("hide");
                            }
                        });

                    } catch(e) {
                         muestraMensaje("error", 5000, "¡Error en la operación!", "No se pudo verificar el estado de la unidad curricular.");
                    }
                },
                error: function() {
                    muestraMensaje("error", 5000, "¡Error de conexión!", "No se pudo comunicar con el servidor.");
                }
            });
        }
    });

    $("#registrar").on("click", function() {
        limpia();
        $("#proceso").text("REGISTRAR");
        $("#codigoUC, #nombreUC, #independienteUC, #asistidaUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC, #academicaUC").prop("disabled", false);
        $("#modal1").modal("show");
        $("span[id^='s']").show();
    });

    $("#codigoUC").on("keyup keydown", function() {
        $("#scodigoUC").css("color", "");
        let formatoValido = validarkeyup(/^[A-Za-z0-9-]{5,20}$/, $(this), $("#scodigoUC"), "El código debe tener entre 5 y 20 caracteres.");
        if (formatoValido === 1) {
            var datos = new FormData();
            datos.append('accion', 'existe');
            datos.append('codigoUC', $(this).val());
            if ($("#proceso").text() === "MODIFICAR") {
                datos.append('codigoExcluir', originalCodigoUC);
            }
            enviaAjax(datos, 'existe');
        }
    });

    $("#nombreUC").on("keyup keydown", function() {
        $("#snombreUC").css("color", "");
        validarkeyup(/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]{5,50}$/, $(this), $("#snombreUC"), "El nombre debe tener entre 5 y 50 caracteres.");
    });

    $("#creditosUC").on("keyup keydown", function() {
        $("#screditosUC").css("color", "");
        validarkeyup(/^([3-9]|[1-9][0-9])$/, $(this), $("#screditosUC"), "Debe ser un número entre 3 y 99.");
    });
});

function validarenvio() {
    if ($("#codigoUC").val() == "" || $("#codigoUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "El código de la unidad curricular es obligatorio.");
        $("#codigoUC").focus();
        return false;
    }
    if ($("#nombreUC").val() == "" || $("#nombreUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "El nombre de la unidad curricular es obligatorio.");
        $("#nombreUC").focus();
        return false;
    }
    if (validarkeyup(/^([3-9]|[1-9][0-9])$/, $("#creditosUC"), $("#screditosUC"), "Debe ser un número entre 3 y 99.") === 0) {
        if(esValido) muestraMensaje("error", 4000, "ERROR!", "Las unidades de crédito deben ser entre 3 y 99.");
        esValido = false;
    }
    if ($("#trayectoUC").val() == "" || $("#trayectoUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "Debe seleccionar un trayecto.");
        $("#trayectoUC").focus();
        return false;
    }
    if ($("#ejeUC").val() == "" || $("#ejeUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "Debe seleccionar un eje.");
        $("#ejeUC").focus();
        return false;
    }
    if ($("#areaUC").val() == "" || $("#areaUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "Debe seleccionar un área.");
        $("#areaUC").focus();
        return false;
    }
    if ($("#periodoUC").val() == "" || $("#periodoUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "Debe seleccionar un periodo.");
        $("#periodoUC").focus();
        return false;
    }
    if ($("#electivaUC").val() == "" || $("#electivaUC").val() == null) {
        muestraMensaje("error", 4000, "Atención!", "Debe seleccionar si es electiva o no.");
        $("#electivaUC").focus();
        return false;
    }
    if ($("#electivaUC").val() == "1" && $("#periodoUC").val() == "anual") {
        muestraMensaje("error", 4000, "Atención!", "Una unidad curricular electiva no puede tener periodo anual.");
        $("#periodoUC").focus();
        return false;
    }

    return true;
}

function pone(pos, accion) {
    linea = $(pos).closest("tr");
    originalCodigoUC = linea.data("codigo");

    if (accion == 0) {
        $("#proceso").text("MODIFICAR");
        $("#codigoUC, #nombreUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC").prop("disabled", false);
    } else {
        $("#proceso").text("ELIMINAR");
        $("#codigoUC, #nombreUC, #trayectoUC, #ejeUC, #areaUC, #creditosUC, #periodoUC, #electivaUC").prop("disabled", true);
    }

    $("#codigoUC").val(linea.data("codigo"));
    $("#nombreUC").val(linea.data("nombre"));
    $("#trayectoUC").val(linea.data("trayecto"));
    $("#ejeUC").val(linea.data("eje"));
    $("#areaUC").val(linea.data("area"));
    $("#creditosUC").val(linea.data("creditos"));
    $("#periodoUC").val(linea.data("periodo"));
    $("#electivaUC").val(linea.data("electiva"));
    
    $("#modal1").modal("show");
    $("#scodigoUC, #snombreUC, #strayectoUC, #seje, #sarea, #screditosUC, #speriodoUC, #selectivaUC").hide();
}

function verDocentes(ucCodigo, ucNombre) {
    var datos = new FormData();
    datos.append('accion', 'ver_docentes');
    datos.append('codigo', ucCodigo);

    $.ajax({
        async: true,
        url: "",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);
                if (lee.resultado === 'ok' && lee.mensaje) {
                    $('#ucNombreModal').text(ucNombre);
                    var lista = $('#listaDocentesAsignados');
                    lista.empty(); 

                    if(lee.mensaje.length > 0) {
                        lee.mensaje.forEach(function(docente) {
                            var prefijo = docente.doc_prefijo || '';
                            var cedula = docente.doc_cedula || '';
                            var nombre = docente.doc_nombre || '';
                            var apellido = docente.doc_apellido || '';
                            var li = `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        ${prefijo}-${cedula}, ${nombre} ${apellido}
                                    </span>
                                    <button class="btn btn-danger btn-sm quitar-docente-uc" data-uccodigo="${ucCodigo}" data-doccedula="${cedula}" title="Quitar Docente">
                                        Quitar
                                    </button>
                                </li>`;
                            lista.append(li);
                        });
                    } else {
                        lista.append('<li class="list-group-item">No hay docentes asignados a esta unidad curricular.</li>');
                    }

                    $('#modalVerDocentes').modal('show');
                } else {
                    muestraMensaje("error", 4000, "Error", "No se pudieron cargar los docentes.");
                }
            } catch (e) {
                muestraMensaje("error", 4000, "Error", "Respuesta inválida del servidor: " + respuesta);
            }
        },
        error: function(solicitud, estado, error) {
            muestraMensaje("error", 4000, "Error", "Error de conexión: " + error);
        }
    });
}

function enviaAjax(datos, accion = "") {
    $.ajax({
        async: true,
        url: "",
        type: "POST",
        contentType: false,
        data: datos,
        processData: false,
        cache: false,
        success: function(respuesta) {
            try {
                var lee = JSON.parse(respuesta);

                if (accion === 'cargarDocentesAsignados') {
                    docentesAsignadosUC = [];
                    if (lee.resultado === 'ok' && lee.mensaje) {
                        docentesAsignadosUC = lee.mensaje.map(d => d.doc_cedula.toString());
                    }
                    $("#modal2").modal("show");
                    return;
                }

                if (accion === "verificarQuitarDocente") {
                    let titulo = "¿Está seguro de quitar este docente de la unidad curricular?";
                    let texto = "Esta acción puede revertirse asignando de nuevo al docente.";
                    if (lee.resultado === 'en_horario') {
                        titulo = "¡Atención!";
                        texto = "Este docente está asignado a un horario con esta UC. Si lo quita, se eliminará de la planificación. ¿Desea continuar?";
                    }
                    const docCedula = datos.get("doc_cedula");
                    const ucCodigo = datos.get("uc_codigo");
                    Swal.fire({
                        title: titulo,
                        text: texto,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Sí, quitar",
                        cancelButtonText: "Cancelar",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var datosQuitar = new FormData();
                            datosQuitar.append("accion", "quitar");
                            datosQuitar.append("doc_cedula", docCedula);
                            datosQuitar.append("uc_codigo", ucCodigo);
                            enviaAjax(datosQuitar);
                        }
                    });
                    return;
                }

                if (accion === 'existe') {
                    if (lee.resultado === 'existe') {
                        $("#scodigoUC").text(lee.mensaje).css("color", "red");
                        $("#proceso").prop("disabled", true);
                    } else {
                        $("#scodigoUC").text("");
                        $("#proceso").prop("disabled", false);
                    }
                    return;
                }
                if (lee.resultado === "consultar") {
                    destruyeDT("#tablauc");
                    $("#resultadoconsulta1").empty();
                    let tabla = "";
                    lee.mensaje.forEach(item => {
                        let electivaTexto = item.uc_electiva == 1 ? "Sí" : "No";
                        let periodoTexto = item.uc_periodo === "anual" ? "Anual" : (item.uc_periodo === "1" ? "Fase 1" : (item.uc_periodo === "2" ? "Fase 2" : item.uc_periodo));
                        const btnModificar = `<button class="btn btn-icon btn-edit" onclick="pone(this, 0)" title="Modificar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/edit.svg" alt="Modificar"></button>`;
                        const btnEliminar = `<button class="btn btn-icon btn-delete" onclick="pone(this, 1)" title="Eliminar" ${!PERMISOS.eliminar ? 'disabled' : ''}><img src="public/assets/icons/trash.svg" alt="Eliminar"></button>`;
                        const btnAsignar = `<button class="btn btn-icon btn-success asignar-uc" title="Asignar" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/user-graduate-solid.svg" alt="Asignar"></button>`;
                        const btnVerDocentes = `<button class="btn btn-icon btn-info" onclick="verDocentes('${item.uc_codigo}', '${item.uc_nombre}')" title="Ver Docentes" ${!PERMISOS.modificar ? 'disabled' : ''}><img src="public/assets/icons/people.svg" alt="Ver Docentes"></button>`;
                        tabla += `
                            <tr data-codigo="${item.uc_codigo}" data-nombre="${item.uc_nombre}" data-trayecto="${item.uc_trayecto}" data-eje="${item.eje_nombre}" data-area="${item.area_nombre}" data-creditos="${item.uc_creditos}" data-periodo="${item.uc_periodo}" data-electiva="${item.uc_electiva}">
                                <td>${item.uc_codigo}</td>
                                <td>${item.uc_nombre}</td>
                                <td>${item.uc_trayecto}</td>
                                <td>${item.eje_nombre}</td>
                                <td>${item.area_nombre}</td>
                                <td>${item.uc_creditos}</td>
                                <td>${periodoTexto}</td>
                                <td>${electivaTexto}</td>
                                <td class="text-center">
                                    ${btnModificar}
                                    ${btnEliminar}
                                    ${btnAsignar}
                                    ${btnVerDocentes}
                                </td>
                            </tr>`;
                    });
                    $('#resultadoconsulta1').html(tabla);
                    crearDT("#tablauc");
                } else if (lee.resultado == "registrar") {
                    muestraMensaje("info", 4000, "REGISTRAR", lee.mensaje);
                    if (lee.mensaje.includes("¡Registro Incluido!")) {
                        $("#modal1").modal("hide");
                    }
                    Listar();
                } else if (lee.resultado == "modificar") {
                    muestraMensaje("info", 4000, "MODIFICAR", lee.mensaje);
                    if (lee.mensaje.includes("modificó la unidad curricular")) {
                        $("#modal1").modal("hide");
                    }
                    Listar();
                } else if (lee.resultado == "eliminar") {
                    muestraMensaje("info", 4000, "ELIMINAR", lee.mensaje);
                    if (lee.mensaje.includes("eliminó la unidad curricular")) {
                        $("#modal1").modal("hide");
                    }
                    Listar();
                } else if (lee.resultado == 'asignar') {
                    muestraMensaje("info", 4000, "ASIGNACIÓN", lee.mensaje);
                    $("#modal2").modal("hide");
                    $("#docenteUC").val("");
                    $("#carritoDocentes").empty();
                    carritoDocentes = [];
                    actualizarCarritoDocentes();
                    Listar();
                } else if (lee.resultado == "quitar") {
                    muestraMensaje("info", 2000, "QUITAR", lee.mensaje);
                    if (lee.uc_codigo) {
                        const ucNombre = $('#ucNombreModal').text();
                        verDocentes(lee.uc_codigo, ucNombre);
                    }
                    Listar();
                } else if (lee.resultado == 'error' || lee.resultado == 'existe') {
                    muestraMensaje("error", 5000, "¡Atención!", lee.mensaje);
                }
            } catch (e) {
                console.error("Error al parsear JSON: ", e, "Respuesta: ", respuesta);
                muestraMensaje("error", 5000, "Error", "Respuesta inválida del servidor.");
            }
        },
        error: function(solicitud, estado, error) {
            console.log(solicitud, estado, error);
            muestraMensaje("error", 5000, "Error", "No se pudo comunicar con el servidor.");
        },
    });
}

function limpia() {
    $("#codigoUC").val("");
    $("#nombreUC").val("");
    $("#creditosUC").val("");
    $("#independienteUC").val("");
    $("#asistidaUC").val("");
    $("#academicaUC").val("");
    $("#trayectoUC").val(""); 
    $("#ejeUC").val("");      
    $("#areaUC").val("");     
    $("#periodoUC").val("");  
    $("#electivaUC").val(""); 
}


let carritoDocentes = [];
let ucSeleccionada = null;

function actualizarCarritoDocentes() {
    const ul = document.getElementById("carritoDocentes");
    if (!ul) return;
    ul.innerHTML = "";
    carritoDocentes.forEach((asignacion, idx) => {
        const texto = `${asignacion.prefijo}-${asignacion.cedula}, ${asignacion.nombre}`;
        const li = document.createElement("li");
        li.className = "list-group-item d-flex justify-content-between align-items-center";
        li.innerHTML = `
            <span>${texto}</span>
            <button type="button" class="btn btn-danger btn-sm quitar-docente" data-idx="${idx}">Quitar</button>
        `;
        ul.appendChild(li);
    });
}

$(document).on("click", "#agregarDocente", function() {
    const select = document.getElementById("docenteUC");
    const docenteCedula = select.value;
    const selectedOption = select.options[select.selectedIndex];
    const docenteNombre = selectedOption?.text;
    const docentePrefijo = selectedOption?.getAttribute('data-prefijo') || '';

    if (!docenteCedula) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Seleccione un docente válido.' });
        return;
    }

    if (docentesAsignadosUC.includes(docenteCedula)) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Este docente ya está asignado a esta unidad curricular.' });
        return;
    }

    if (carritoDocentes.some(doc => doc.cedula === docenteCedula)) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Este docente ya está en la lista.' });
        return;
    }

    carritoDocentes.push({
        cedula: docenteCedula,
        nombre: docenteNombre.replace(/^.*?,\s*/, ""),
        prefijo: docentePrefijo
    });
    actualizarCarritoDocentes();
    $("#docenteUC").val("");
});

$(document).on("click", ".quitar-docente", function() {
    const idx = $(this).data("idx");
    carritoDocentes.splice(idx, 1);
    actualizarCarritoDocentes();
});

$(document).on("click", "#asignarDocentes", function() {
    if (carritoDocentes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: '¡Agregue al menos un docente a la lista!'
        });
        return;
    }
   
    if (!ucSeleccionada) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'No se ha seleccionado una unidad curricular.'
        });
        return;
    }

    var datos = new FormData();
    datos.append("accion", "asignar");
    datos.append("asignaciones", JSON.stringify(carritoDocentes));
    datos.append("ucs", JSON.stringify([ucSeleccionada]));
    enviaAjax(datos);
});

$(document).on("click", ".ver-docentes", function() {
    const uc_codigo = $(this).closest("tr").data("codigo");
    const uc_nombre = $(this).closest("tr").data("nombre");
    verDocentes(uc_codigo, uc_nombre);
});

function solicitarDocentesPorUC(uc_codigo) {
    var datos = new FormData();
    datos.append("accion", "consultarAsignacion");
    datos.append("uc_codigo", uc_codigo);
    enviaAjax(datos, "mostrarDocentesDeUC");
}

$(document).on("click", ".quitar-docente-uc", function() {
    const docCedula = $(this).data("doccedula");
    const ucCodigo = $(this).data("uccodigo");

    var datosVerificacion = new FormData();
    datosVerificacion.append("accion", "verificar_docente_horario");
    datosVerificacion.append("uc_codigo", ucCodigo);
    datosVerificacion.append("doc_cedula", docCedula);
    enviaAjax(datosVerificacion, "verificarQuitarDocente");
});