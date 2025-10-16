document.addEventListener('DOMContentLoaded', function () {
    
    
    if (window.jQuery) {
        $(document).ready(function() {
            try {
                $('#anio_id').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Año" });
                $('#trayecto').select2({ theme: "bootstrap-5", placeholder: "Seleccione un Trayecto" });
                $('#fase').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Fase" });
                $('#ucurricular').select2({ theme: "bootstrap-5", placeholder: "Seleccione una Unidad" });
                
                $('#anio_id').on('change', function() {
                    const anioSeleccionado = $(this).val();
                    
                    if (anioSeleccionado) {
                        fetch(`?pagina=ruc&action=obtener_fase_actual&anio=${anioSeleccionado}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const faseInactiva = data.fase_numero === 1 ? 'Fase II' : 'Fase I';
                                    
                                    $('#fase option').prop('disabled', false);
                                    $('#fase option').each(function() {
                                        if ($(this).val() === faseInactiva) {
                                            $(this).prop('disabled', true);
                                        }
                                    });
                                    $('#fase').trigger('change.select2');
                                } else {
                                    $('#fase option').prop('disabled', false);
                                    $('#fase').trigger('change.select2');
                                }
                            })
                            .catch(error => {
                                console.error('Error al obtener fase actual:', error);
                            });
                    } else {
                        $('#fase option').prop('disabled', false);
                        $('#fase').trigger('change.select2');
                    }
                });
                
                $('#trayecto').on('change', function() {
                    filtrarUCs();
                });
                
                $('#fase').on('change', function() {
                    const faseSeleccionada = $(this).val();
                    if (faseSeleccionada === 'Fase I') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Fase I Seleccionada',
                            text: 'Se mostrarán las Unidades Curriculares de Fase I y las Anuales. Las UCs de Fase II no se incluirán.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else if (faseSeleccionada === 'Fase II') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Fase II Seleccionada',
                            text: 'Se mostrarán las Unidades Curriculares de Fase II y las Anuales. Las UCs de Fase I no se incluirán.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                    
                    filtrarUCs();
                });
            } catch (e) {
                console.error("Error al inicializar Select2.", e);
            }
        });
    }

    function filtrarUCs() {
        const trayectoId = $('#trayecto').val();
        const fase = $('#fase').val();
        
        let url = '?pagina=ruc&action=obtener_ucs';
        const params = [];
        
        if (trayectoId && trayectoId !== "") {
            params.push(`trayecto=${trayectoId}`);
        }
        
        if (fase && fase !== "") {
            params.push(`fase=${encodeURIComponent(fase)}`);
        }
        
        if (params.length > 0) {
            url += '&' + params.join('&');
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                actualizarSelectUC(data);
            })
            .catch(error => {
                console.error('Error al filtrar UCs:', error);
            });
    }

    function actualizarSelectUC(ucs) {
        if (window.jQuery) {
            $('#ucurricular').empty();
            $('#ucurricular').append('<option value="">Todas las Unidades</option>');
            
            ucs.forEach(uc => {
                $('#ucurricular').append(`<option value="${uc.uc_id}">${uc.uc_nombre}</option>`);
            });
            
            $('#ucurricular').trigger('change');
        } else {
            ucSelect.innerHTML = '<option value="">Todas las Unidades</option>';
            ucs.forEach(uc => {
                const option = document.createElement('option');
                option.value = uc.uc_id;
                option.textContent = uc.uc_nombre;
                ucSelect.appendChild(option);
            });
        }
    }

  
    const generarBtnUc = document.getElementById("generar_uc");
    const anioSelect = document.getElementById("anio_id");
    const ucSelect = document.getElementById("ucurricular");
    const formReporteUc = document.getElementById("fReporteUc");

    if (generarBtnUc && formReporteUc) {
        formReporteUc.addEventListener("submit", function(event) {
            event.preventDefault();
            
            if (anioSelect.value === "") {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo Requerido',
                    text: 'Por favor, seleccione un Año Académico para generar el reporte.',
                });
                return;
            }

            if (ucSelect && ucSelect.value !== "") {
                const formData = new FormData(formReporteUc);
                formData.append('validar_datos', '1');

                fetch('?pagina=ruc', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success === false) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin Datos para Reportes',
                            text: 'No se encontraron registros para la Unidad Curricular seleccionada. Por favor, intente con otra UC.',
                            confirmButtonText: 'Aceptar'
                        });
                    } else {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '?pagina=ruc';
                        form.target = '_blank';
                        
                        const inputs = formReporteUc.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            if (input.name) {
                                const hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = input.name;
                                hiddenInput.value = input.value;
                                form.appendChild(hiddenInput);
                            }
                        });
                        
                        const submitInput = document.createElement('input');
                        submitInput.type = 'hidden';
                        submitInput.name = 'generar_uc';
                        submitInput.value = '1';
                        form.appendChild(submitInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                    }
                })
                .catch(error => {
                    console.error('Error al validar datos:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al validar los datos. Intente nuevamente.',
                    });
                });
            } else {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?pagina=ruc';
                form.target = '_blank';
                
                const inputs = formReporteUc.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = input.name;
                        hiddenInput.value = input.value;
                        form.appendChild(hiddenInput);
                    }
                });
                
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'generar_uc';
                submitInput.value = '1';
                form.appendChild(submitInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        });
    }
});