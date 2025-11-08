(function() {
    'use strict';

    const selectsConfig = {
        'tipoAnio': {
            tipo: 'enum',
            valores: ['regular', 'intensivo']
        },
        'turnonombre': {
            tipo: 'enum',
            valores: ['Mañana', 'Tarde', 'Noche']
        },
        'tituloprefijo': {
            tipo: 'enum',
            valores: ['Ing.', 'Msc.', 'Dr.', 'TSU.', 'Lic.', 'Esp.', 'Prof.']
        },
        'edificio': {
            tipo: 'enum',
            valores: ['Hilandera', 'Giraluna', 'Rio 7 Estrellas', 'Orinoco']
        },
        'tipoEspacio': {
            tipo: 'enum',
            valores: ['Aula', 'Laboratorio']
        },
        'trayectoUC': {
            tipo: 'enum',
            valores: ['0', '1', '2', '3', '4']
        },
        'periodoUC': {
            tipo: 'enum',
            valores: ['Anual', 'Fase I', 'Fase II']
        },
        'prefijoCedula': {
            tipo: 'enum',
            valores: ['V', 'E']
        },
        'dedicacion': {
            tipo: 'enum',
            valores: ['Exclusiva', 'Tiempo Completo', 'Medio Tiempo', 'Tiempo Convencional']
        },
        'condicion': {
            tipo: 'enum',
            valores: ['Ordinario', 'Contratado por Credenciales', 'Suplente']
        },
        'tipoProsecusion': {
            tipo: 'enum',
            valores: ['automatico', 'manual']
        },
        'ejeUC': {
            tipo: 'bd',
            tabla: 'tbl_eje',
            columna: 'eje_nombre',
            columnaEstado: 'eje_estado'
        },
        'areaUC': {
            tipo: 'bd',
            tabla: 'tbl_area',
            columna: 'area_nombre',
            columnaEstado: 'area_estado'
        },
        'categoria': {
            tipo: 'bd',
            tabla: 'tbl_categoria',
            columna: 'cat_nombre',
            columnaEstado: 'cat_estado'
        },
        'titulos': {
            tipo: 'bd_multiple',
            tabla: 'tbl_titulo',
            columna: 'tit_prefijo||tit_nombre', 
            columnaEstado: 'tit_estado',
            separador: '::'
        },
        'coordinaciones': {
            tipo: 'bd_multiple',
            tabla: 'tbl_coordinacion',
            columna: 'cor_nombre',
            columnaEstado: 'cor_estado'
        },
        'select_uc': {
            tipo: 'bd',
            tabla: 'tbl_uc',
            columna: 'uc_codigo',
            columnaEstado: 'uc_estado'
        }
    };

    /**
     * Valida que el valor del select sea permitido
     */
    function validarSelectEnum(selectElement) {
        const selectId = selectElement.id;
        const config = selectsConfig[selectId];
        
        if (!config || config.tipo !== 'enum') {
            return true;
        }

        const valorSeleccionado = selectElement.value;
        
        if (valorSeleccionado === '' || valorSeleccionado === null) {
            return true;
        }

        if (!config.valores.includes(valorSeleccionado)) {
            mostrarAlertaSelect(selectElement, `Valor no válido detectado. El sistema ha restablecido el select.`);
            resetearSelect(selectElement);
            return false;
        }

        return true;
    }

    function validarSelectBD(selectElement) {
        const selectId = selectElement.id;
        const config = selectsConfig[selectId];
        
        if (!config || config.tipo !== 'bd') {
            return;
        }

        const valorSeleccionado = selectElement.value;
        
        if (valorSeleccionado === '' || valorSeleccionado === null) {
            return;
        }

        const formData = new FormData();
        formData.append('accion', 'validar_select_bd');
        formData.append('tabla', config.tabla);
        formData.append('columna', config.columna);
        formData.append('valor', valorSeleccionado);
        formData.append('columna_estado', config.columnaEstado);

        fetch('?pagina=validacion_select', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.valido === false) {
                mostrarAlertaSelect(selectElement, `El valor seleccionado no existe o está inactivo en la base de datos.`);
                resetearSelect(selectElement);
            }
        })
        .catch(error => {
            console.error('Error validando select:', error);
        });
    }

    function validarSelectBDMultiple(selectElement) {
        const selectId = selectElement.id;
        const config = selectsConfig[selectId];
        
        if (!config || config.tipo !== 'bd_multiple') {
            return;
        }

        const valoresSeleccionados = Array.from(selectElement.selectedOptions).map(opt => opt.value);
        
        if (valoresSeleccionados.length === 0) {
            return;
        }

        valoresSeleccionados.forEach(valor => {
            if (valor === '' || valor === null) {
                return;
            }

            let valorParaValidar = valor;
            if (config.separador && valor.includes(config.separador)) {
                const partes = valor.split(config.separador);
                valorParaValidar = partes.join('||'); 
            }

            const formData = new FormData();
            formData.append('accion', 'validar_select_bd_multiple');
            formData.append('tabla', config.tabla);
            formData.append('columna', config.columna);
            formData.append('valor', valorParaValidar);
            formData.append('columna_estado', config.columnaEstado);
            formData.append('separador', config.separador || '');

            fetch('?pagina=validacion_select', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.valido === false) {
                    mostrarAlertaSelect(selectElement, `Uno o más valores seleccionados no existen o están inactivos en la base de datos.`);
                    const opcionInvalida = Array.from(selectElement.options).find(opt => opt.value === valor);
                    if (opcionInvalida) {
                        opcionInvalida.selected = false;
                    }
                }
            })
            .catch(error => {
                console.error('Error validando select múltiple:', error);
            });
        });
    }

    function resetearSelect(selectElement) {
        const primeraOpcion = selectElement.querySelector('option[value=""]') || 
                             selectElement.querySelector('option:first-child');
        
        if (primeraOpcion) {
            selectElement.value = primeraOpcion.value;
        } else {
            selectElement.selectedIndex = 0;
        }

        const evento = new Event('change', { bubbles: true });
        selectElement.dispatchEvent(evento);
    }

    function mostrarAlertaSelect(selectElement, mensaje) {
        const alertaExistente = selectElement.parentElement.querySelector('.alerta-validacion-select');
        if (alertaExistente) {
            alertaExistente.remove();
        }

        const alerta = document.createElement('div');
        alerta.className = 'alerta-validacion-select alert alert-danger mt-2';
        alerta.style.fontSize = '0.85rem';
        alerta.style.padding = '0.5rem';
        alerta.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> ${mensaje}`;

        selectElement.parentElement.insertBefore(alerta, selectElement.nextSibling);

        setTimeout(() => {
            alerta.remove();
        }, 5000);
    }

    function inicializarValidacion() {
        Object.keys(selectsConfig).forEach(selectId => {
            const selectElement = document.getElementById(selectId);
            
            if (!selectElement) {
                return; 
            }

            const config = selectsConfig[selectId];

            if (config.tipo === 'enum') {
                validarSelectEnum(selectElement);
            }

            selectElement.addEventListener('change', function() {
                if (config.tipo === 'enum') {
                    validarSelectEnum(this);
                } else if (config.tipo === 'bd') {
                    validarSelectBD(this);
                } else if (config.tipo === 'bd_multiple') {
                    validarSelectBDMultiple(this);
                }
            });

            selectElement.addEventListener('blur', function() {
                if (config.tipo === 'enum') {
                    validarSelectEnum(this);
                } else if (config.tipo === 'bd') {
                    validarSelectBD(this);
                } else if (config.tipo === 'bd_multiple') {
                    validarSelectBDMultiple(this);
                }
            });

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        if (config.tipo === 'enum') {
                            validarSelectEnum(selectElement);
                        }
                    }
                });
            });

            observer.observe(selectElement, {
                attributes: true,
                attributeOldValue: true
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarValidacion);
    } else {
        inicializarValidacion();
    }

    document.addEventListener('DOMNodeInserted', function(e) {
        if (e.target.tagName === 'OPTION' && e.target.parentElement) {
            const selectElement = e.target.parentElement;
            const config = selectsConfig[selectElement.id];
            
            if (config && config.tipo === 'enum') {
                const valorNuevaOpcion = e.target.value;
                if (valorNuevaOpcion !== '' && !config.valores.includes(valorNuevaOpcion)) {
                    console.warn('Intento de agregar opción no válida detectado y bloqueado');
                    e.target.remove();
                }
            }
        }
    });

})();
