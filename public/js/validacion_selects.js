(function () {
    'use strict';

    const ENDPOINT = '?pagina=validacion_select';
    const ORIGINAL_VALUE_ATTR = 'originalValue';
    const VALIDATION_REQUEST_ATTR = 'validationRequestId';

    const selectsConfig = {
        tipoAnio: { tipo: 'enum', valores: ['regular', 'intensivo'] },
        turnonombre: { tipo: 'enum', valores: ['Mañana', 'Tarde', 'Noche'] },
        tituloprefijo: { tipo: 'enum', valores: ['Ing.', 'Msc.', 'Dr.', 'TSU.', 'Lic.', 'Esp.', 'Prof.'] },
        edificio: { tipo: 'enum', valores: ['Hilandera', 'Giraluna', 'Rio 7 Estrellas', 'Orinoco'] },
        tipoEspacio: { tipo: 'enum', valores: ['Aula', 'Laboratorio'] },
        trayectoUC: { tipo: 'enum', valores: ['0', '1', '2', '3', '4'] },
        periodoUC: { tipo: 'enum', valores: ['Anual', 'Fase I', 'Fase II'] },
        prefijoCedula: { tipo: 'enum', valores: ['V', 'E'] },
        dedicacion: { tipo: 'enum', valores: ['Exclusiva', 'Tiempo Completo', 'Medio Tiempo', 'Tiempo Convencional'] },
        condicion: { tipo: 'enum', valores: ['Ordinario', 'Contratado por Credenciales', 'Suplente'] },
        tipoProsecusion: { tipo: 'enum', valores: ['automatico', 'manual'] },
        ejeUC: { tipo: 'bd', tabla: 'tbl_eje', columna: 'eje_nombre', columnaEstado: 'eje_estado' },
        areaUC: { tipo: 'bd', tabla: 'tbl_area', columna: 'area_nombre', columnaEstado: 'area_estado' },
        categoria: { tipo: 'bd', tabla: 'tbl_categoria', columna: 'cat_nombre', columnaEstado: 'cat_estado' },
        titulos: { tipo: 'bd_multiple', tabla: 'tbl_titulo', columna: 'tit_prefijo||tit_nombre', columnaEstado: 'tit_estado', separador: '::' },
        coordinaciones: { tipo: 'bd_multiple', tabla: 'tbl_coordinacion', columna: 'cor_nombre', columnaEstado: 'cor_estado' },
        select_uc: { tipo: 'bd', tabla: 'tbl_uc', columna: 'uc_codigo', columnaEstado: 'uc_estado' },
        origenProsecusion: { tipo: 'bd', tabla: 'tbl_seccion', columna: 'sec_codigo', columnaEstado: 'sec_estado' },
        destinoManual: { tipo: 'bd', tabla: 'tbl_seccion', columna: 'sec_codigo', columnaEstado: 'sec_estado' },
        usuarioRol: { tipo: 'input_bd', tabla: 'tbl_rol', columna: 'rol_id', columnaEstado: 'rol_estado', mensajeSelector: '#rol_asignado_nombre', permitirVacio: true },
        usu_cedula: { tipo: 'input_bd', tabla: 'tbl_docente', columna: 'doc_cedula', columnaEstado: 'doc_estado', mensajeSelector: '#docente_asignado_nombre', permitirVacio: true }
    };

    function esValorVacio(valor) {
        return valor === null || valor === '';
    }

    function obtenerNuevoIdValidacion(elemento) {
        if (!elemento) {
            return '0';
        }
        const actual = parseInt(elemento.dataset[VALIDATION_REQUEST_ATTR] || '0', 10) || 0;
        const siguiente = actual + 1;
        elemento.dataset[VALIDATION_REQUEST_ATTR] = String(siguiente);
        return elemento.dataset[VALIDATION_REQUEST_ATTR];
    }

    function esRespuestaVigente(elemento, solicitudId) {
        if (!elemento) {
            return false;
        }
        return elemento.dataset[VALIDATION_REQUEST_ATTR] === solicitudId;
    }

    function marcarOpcionesIniciales(elemento) {
        if (!elemento || !elemento.options) {
            return;
        }
        Array.from(elemento.options).forEach(opcion => {
            if (opcion.dataset[ORIGINAL_VALUE_ATTR] === undefined) {
                opcion.dataset[ORIGINAL_VALUE_ATTR] = opcion.value;
            }
        });
    }

    function marcarOpciones(elemento, opciones) {
        if (!opciones || opciones.length === 0) {
            return;
        }
        opciones.forEach(opcion => {
            if (opcion.tagName === 'OPTION' && opcion.dataset[ORIGINAL_VALUE_ATTR] === undefined) {
                opcion.dataset[ORIGINAL_VALUE_ATTR] = opcion.value;
            }
        });
    }

    function obtenerOpcionesSeleccionadas(elemento) {
        if (!elemento || !elemento.selectedOptions) {
            return [];
        }
        return Array.from(elemento.selectedOptions);
    }

    function detectarManipulacionUnica(elemento, config) {
        const opcionesSeleccionadas = obtenerOpcionesSeleccionadas(elemento);
        if (opcionesSeleccionadas.length === 0) {
            return false;
        }

        const opcion = opcionesSeleccionadas[0];
        const valorOriginal = opcion.dataset[ORIGINAL_VALUE_ATTR];
        if (valorOriginal !== undefined && opcion.value !== valorOriginal) {
            mostrarAlertaSelect(elemento, config);
            return true;
        }
        return false;
    }

    function detectarManipulacionMultiple(elemento, config) {
        const opcionesSeleccionadas = obtenerOpcionesSeleccionadas(elemento);
        if (opcionesSeleccionadas.length === 0) {
            return false;
        }

        let seManipulo = false;

        opcionesSeleccionadas.forEach(opcion => {
            const valorOriginal = opcion.dataset[ORIGINAL_VALUE_ATTR];
            if (valorOriginal !== undefined && opcion.value !== valorOriginal) {
                opcion.selected = false;
                seManipulo = true;
            }
        });

        if (seManipulo) {
            mostrarAlertaSelect(elemento, config);
            const evento = new Event('change', { bubbles: true });
            elemento.dispatchEvent(evento);
            return true;
        }

        return false;
    }

    function detectarManipulacion(elemento, config) {
        if (!elemento || config.tipo === 'input_bd') {
            return false;
        }

        if (config.tipo === 'bd_multiple') {
            return detectarManipulacionMultiple(elemento, config);
        }

        return detectarManipulacionUnica(elemento, config);
    }

    function obtenerDestinoMensaje(elemento, config) {
        if (config && config.mensajeSelector) {
            const destino = document.querySelector(config.mensajeSelector);
            if (destino) {
                return destino;
            }
        }
        return elemento;
    }

    function mostrarAlertaSelect(elemento, config) {
        const destino = obtenerDestinoMensaje(elemento, config);
        if (!destino || !elemento.id) {
            return;
        }

        const selector = `.alerta-validacion-select[data-validacion-for="${elemento.id}"]`;
        const existente = document.querySelector(selector);
        if (existente) {
            return;
        }

        elemento.dataset.selectInvalid = 'true';

        const alerta = document.createElement('span');
        alerta.className = 'alerta-validacion-select text-danger d-block mt-1';
        alerta.dataset.validacionFor = elemento.id;
        alerta.textContent = 'Select con valor invalido';
        const contenedor = destino.closest && destino.closest('.input-group') ? destino.closest('.input-group') : destino;

        if (contenedor.nextSibling) {
            contenedor.parentElement.insertBefore(alerta, contenedor.nextSibling);
        } else {
            contenedor.parentElement.appendChild(alerta);
        }
    }

    function limpiarAlertaSelect(elemento, config, forzar = false) {
        if (!elemento.id) {
            return;
        }
        if (!forzar && elemento.dataset.selectInvalid === 'true') {
            return;
        }
        delete elemento.dataset.selectInvalid;
        const selector = `.alerta-validacion-select[data-validacion-for="${elemento.id}"]`;
        const alerta = document.querySelector(selector);
        if (alerta) {
            alerta.remove();
        }
    }

    function validarSelectEnum(selectElement, config) {
        const valor = selectElement.value;

        if (esValorVacio(valor)) {
            limpiarAlertaSelect(selectElement, config, true);
            return true;
        }

        if (detectarManipulacion(selectElement, config)) {
            return false;
        }

        if (!config.valores.includes(valor)) {
            mostrarAlertaSelect(selectElement, config);
            return false;
        }

        limpiarAlertaSelect(selectElement, config, true);
        return true;
    }

    function prepararValorParaBD(valor, config) {
        if (config.separador && valor.includes(config.separador)) {
            const partes = valor.split(config.separador);
            return partes.join('||');
        }
        return valor;
    }

    function crearFormData(config, valor) {
        const formData = new FormData();
        const accion = config.tipo === 'bd_multiple' ? 'validar_select_bd_multiple' : 'validar_select_bd';

        formData.append('accion', accion);
        formData.append('tabla', config.tabla);
        formData.append('columna', config.columna);
        formData.append('valor', valor);

        if (config.columnaEstado) {
            formData.append('columna_estado', config.columnaEstado);
        }

        if (config.tipo === 'bd_multiple' && config.separador) {
            formData.append('separador', config.separador);
        }

        return formData;
    }

    function validarBD(config, valor) {
        const formData = crearFormData(config, valor);
        return fetch(ENDPOINT, {
            method: 'POST',
            body: formData
        }).then(respuesta => respuesta.json());
    }

    function validarSelectBD(selectElement, config) {
        const valor = selectElement.value;

        if (esValorVacio(valor)) {
            limpiarAlertaSelect(selectElement, config, true);
            return;
        }

        if (detectarManipulacion(selectElement, config)) {
            return;
        }

        limpiarAlertaSelect(selectElement, config, true);

        const solicitudId = obtenerNuevoIdValidacion(selectElement);
        validarBD(config, valor)
            .then(data => {
                if (!esRespuestaVigente(selectElement, solicitudId)) {
                    return;
                }
                if (data.valido === false) {
                    mostrarAlertaSelect(selectElement, config);
                } else {
                    limpiarAlertaSelect(selectElement, config, true);
                }
            })
            .catch(error => console.error('Error validando select en BD:', error));
    }

    function validarSelectBDMultiple(selectElement, config) {
        const valores = Array.from(selectElement.selectedOptions)
            .map(opt => opt.value)
            .filter(valor => !esValorVacio(valor));

        if (valores.length === 0) {
            limpiarAlertaSelect(selectElement, config, true);
            return;
        }

        if (detectarManipulacion(selectElement, config)) {
            return;
        }

        limpiarAlertaSelect(selectElement, config, true);

        const solicitudId = obtenerNuevoIdValidacion(selectElement);
        const peticiones = valores.map(valorOriginal => {
            const valor = prepararValorParaBD(valorOriginal, config);
            return validarBD(config, valor)
                .then(data => ({ data, valorOriginal }))
                .catch(error => {
                    console.error('Error validando select múltiple en BD:', error);
                    return { data: { valido: false }, valorOriginal };
                });
        });

        Promise.all(peticiones).then(resultados => {
            if (!esRespuestaVigente(selectElement, solicitudId)) {
                return;
            }
            const invalido = resultados.some(resultado => resultado.data && resultado.data.valido === false);

            if (invalido) {
                resultados.forEach(resultado => {
                    if (resultado.data && resultado.data.valido === false) {
                        const opcion = Array.from(selectElement.options).find(opt => opt.value === resultado.valorOriginal);
                        if (opcion) {
                            opcion.selected = false;
                        }
                    }
                });
                mostrarAlertaSelect(selectElement, config);
                const evento = new Event('change', { bubbles: true });
                selectElement.dispatchEvent(evento);
            } else {
                limpiarAlertaSelect(selectElement, config, true);
            }
        });
    }

    function validarInputBD(inputElement, config) {
        const valor = inputElement.value;

        if (esValorVacio(valor)) {
            if (config.permitirVacio) {
                limpiarAlertaSelect(inputElement, config, true);
            } else {
                mostrarAlertaSelect(inputElement, config);
            }
            return;
        }

        limpiarAlertaSelect(inputElement, config, true);

        const solicitudId = obtenerNuevoIdValidacion(inputElement);
        validarBD(config, valor)
            .then(data => {
                if (!esRespuestaVigente(inputElement, solicitudId)) {
                    return;
                }
                if (data.valido === false) {
                    mostrarAlertaSelect(inputElement, config);
                } else {
                    limpiarAlertaSelect(inputElement, config, true);
                }
            })
            .catch(error => console.error('Error validando campo oculto en BD:', error));
    }

    function validarElemento(elemento, config) {
        if (!config) {
            return;
        }

        if (config.tipo === 'enum') {
            validarSelectEnum(elemento, config);
        } else if (config.tipo === 'bd') {
            validarSelectBD(elemento, config);
        } else if (config.tipo === 'bd_multiple') {
            validarSelectBDMultiple(elemento, config);
        } else if (config.tipo === 'input_bd') {
            validarInputBD(elemento, config);
        }
    }

    function inicializarElemento(elemento, config) {
        if (!elemento) {
            return;
        }

        marcarOpcionesIniciales(elemento);
        validarElemento(elemento, config);

        const eventos = ['change'];
        if (config.tipo !== 'input_bd') {
            eventos.push('blur');
        } else {
            eventos.push('input');
        }

        eventos.forEach(evento => {
            elemento.addEventListener(evento, function () {
                validarElemento(elemento, config);
            });
        });

        const observer = new MutationObserver(mutations => {
            let debeValidar = false;

            mutations.forEach(mutation => {
                if (mutation.type === 'attributes') {
                    if (mutation.target === elemento && (mutation.attributeName === 'value' || mutation.attributeName === 'selected')) {
                        debeValidar = true;
                    }
                    if (mutation.target.tagName === 'OPTION' && (mutation.attributeName === 'value' || mutation.attributeName === 'selected')) {
                        debeValidar = true;
                    }
                }

                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    if (mutation.type === 'childList') {
                        const agregados = Array.from(mutation.addedNodes || []).filter(node => node.tagName === 'OPTION');
                        if (agregados.length > 0) {
                            marcarOpciones(elemento, agregados);
                        }
                    }
                    debeValidar = true;
                }
            });

            if (debeValidar) {
                validarElemento(elemento, config);
            }
        });

        observer.observe(elemento, { attributes: true, attributeOldValue: true, attributeFilter: ['value', 'selected'], childList: true, subtree: true, characterData: true });
    }

    function limpiarAlertasEnContenedor(contenedor) {
        if (!contenedor) {
            return;
        }

        contenedor.querySelectorAll('.alerta-validacion-select').forEach(alerta => {
            const selectId = alerta.dataset.validacionFor;
            alerta.remove();
            if (selectId) {
                const elemento = document.getElementById(selectId);
                if (elemento) {
                    delete elemento.dataset.selectInvalid;
                }
            }
        });
    }

    function inicializarValidacion() {
        Object.keys(selectsConfig).forEach(id => {
            const elemento = document.getElementById(id);
            const config = selectsConfig[id];

            if (!config || !elemento) {
                return;
            }

            inicializarElemento(elemento, config);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarValidacion);
    } else {
        inicializarValidacion();
    }

    document.addEventListener('DOMNodeInserted', function (evento) {
        if (evento.target.tagName === 'OPTION' && evento.target.parentElement) {
            const selectElement = evento.target.parentElement;
            const config = selectsConfig[selectElement.id];

            if (config && config.tipo === 'enum') {
                const valor = evento.target.value;
                if (!esValorVacio(valor) && !config.valores.includes(valor)) {
                    console.warn('Intento de agregar opción no válida detectado y bloqueado');
                    evento.target.remove();
                }
            }
        }
    });

    document.addEventListener('hidden.bs.modal', function (evento) {
        limpiarAlertasEnContenedor(evento.target);
    });

    window.ValidacionSelects = {
        validarPorId(id) {
            const config = selectsConfig[id];
            const elemento = document.getElementById(id);
            if (config && elemento) {
                validarElemento(elemento, config);
            }
        }
    };

})();
