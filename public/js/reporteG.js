$(document).ready(function() {
    let myChart = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    function renderChart(chartData, chartType = 'bar', chartTitle = 'Resultados Estudiantiles') {
        if (myChart) {
            myChart.destroy();
        }

        if ((chartType === 'pie' || chartType === 'doughnut') && chartData.datasets.length > 1) {
            chartData.datasets = [chartData.datasets[0]];
        }

        myChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, display: true, ticks: { precision: 0 } } },
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: chartTitle, font: { size: 16 } }
                }
            }
        });
    }

    function updateChartOnTypeChange() {
        if (myChart) {
            myChart.config.type = $('#tipo_grafico').val();
            myChart.update();
        }
    }

    $('#tipo_grafico').change(updateChartOnTypeChange);
    $('#tipo_reporte').change(function() {
        const tipo = $(this).val();
        $('#filtro_seccion_container').toggle(tipo === 'seccion');
        $('#filtro_uc_container').toggle(tipo === 'uc');
    });

    $('#anio_reporte').change(function() {
        const anio_completo = $(this).val();
        const seccionSelect = $('#seccion_codigo');
        const ucSelect = $('#uc_codigo');

        if (!anio_completo) {
            seccionSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            ucSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            return;
        }

        seccionSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=reporteG', { accion: 'obtener_secciones', anio_completo: anio_completo }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.sec_codigo}">${item.sec_codigo}</option>`);
                seccionSelect.prop('disabled', false);
            } else { options = '<option value="">No hay secciones</option>'; }
            seccionSelect.html(options);
        }, 'json');

        ucSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=reporteG', { accion: 'obtener_uc', anio_completo: anio_completo }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.uc_codigo}">${item.uc_nombre}</option>`);
                ucSelect.prop('disabled', false);
            } else { options = '<option value="">No hay U.C.</option>'; }
            ucSelect.html(options);
        }, 'json');
    });

    $('#formReporte').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'generar_reporte');

        $.ajax({
            url: '?pagina=reporteG', type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const responseData = response.datos;
                    const tipoReporte = $('#tipo_reporte').val();
                    let chartData = { labels: [], datasets: [] };
                    let chartTitle = 'Resultados del Proceso de Remedial (PER)';

                    if (tipoReporte === 'general') {
                        // **MODIFICADO**: Se usa el dato directo del servidor
                        const aprobadosDirecto = parseInt(responseData.total_aprobados_directo, 10);
                        const enPer = parseInt(responseData.total_en_per, 10);
                        const aprobadosPer = parseInt(responseData.total_aprobados_per, 10);
                        
                        chartData.labels = ['Resultados Generales'];
                        chartData.datasets = [
                            { label: 'Aprobados Directo', data: [aprobadosDirecto], backgroundColor: 'rgba(75, 192, 192, 0.7)' },
                            { label: 'Reprobaron PER', data: [enPer - aprobadosPer], backgroundColor: 'rgba(255, 99, 132, 0.7)' },
                            { label: 'Aprobaron PER', data: [aprobadosPer], backgroundColor: 'rgba(54, 162, 235, 0.7)' }
                        ];

                    } else { // Para reportes detallados
                        chartTitle = (tipoReporte === 'seccion') ? 'Resultados por Unidad Curricular' : 'Resultados por Sección';
                        
                        const labels = [];
                        const directosData = [];
                        const reprobadosPerData = [];
                        const aprobadosPerData = [];

                        responseData.forEach(item => {
                            const label = (tipoReporte === 'seccion') ? item.uc_nombre : 'Sección ' + item.sec_codigo;
                            labels.push(label);
                            
                            // **MODIFICADO**: Se usa el dato directo del servidor
                            const aprobadosDir = parseInt(item.aprobados_directo, 10);
                            const enPer = parseInt(item.per_cantidad, 10);
                            const aprobadosPer = parseInt(item.per_aprobados, 10);
                            
                            directosData.push(aprobadosDir);
                            reprobadosPerData.push(enPer - aprobadosPer);
                            aprobadosPerData.push(aprobadosPer);
                        });

                        chartData.labels = labels;
                        chartData.datasets = [
                            { label: 'Aprobados Directo', data: directosData, backgroundColor: 'rgba(75, 192, 192, 0.7)' },
                            { label: 'Reprobaron PER', data: reprobadosPerData, backgroundColor: 'rgba(255, 99, 132, 0.7)' },
                            { label: 'Aprobaron PER', data: aprobadosPerData, backgroundColor: 'rgba(54, 162, 235, 0.7)' }
                        ];
                    }
                    
                    renderChart(chartData, $('#tipo_grafico').val(), chartTitle);

                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.mensaje || 'No se pudo generar el reporte.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'Hubo un problema al contactar con el servidor.' });
            }
        });
    });

    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
});