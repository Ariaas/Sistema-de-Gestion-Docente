$(document).ready(function() {
    let myChart = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    const colorPalette = [
        'rgba(255, 99, 132, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(54, 162, 235, 0.7)', 'rgba(153, 102, 255, 0.7)',
        'rgba(201, 203, 207, 0.7)', 'rgba(100, 220, 150, 0.7)'
    ];

    function renderChart(chartData, chartType = 'bar', chartTitle = 'Resultados Estudiantiles') {
        if (myChart) {
            myChart.destroy();
        }
        const backgroundColors = chartData.data.map((_, index) => colorPalette[index % colorPalette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        myChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Estudiantes Reprobados en PER',
                    data: chartData.data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, display: true, ticks: { precision: 0 } } },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: chartTitle, font: { size: 16 } }
                }
            }
        });
    }

    function updateChartOnTypeChange() {
        if (myChart && myChart.data.labels.length > 0) {
             renderChart({
                labels: myChart.data.labels,
                data: myChart.data.datasets[0].data
            }, $('#tipo_grafico').val(), myChart.options.plugins.title.text);
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
        $.post('?pagina=rReprobados', {
            accion: 'obtener_secciones',
            anio_completo: anio_completo
        }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.sec_codigo}">${item.sec_codigo}</option>`);
                seccionSelect.prop('disabled', false);
            } else {
                options = '<option value="">No hay secciones</option>';
            }
            seccionSelect.html(options);
        }, 'json');

        ucSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=rReprobados', {
            accion: 'obtener_uc',
            anio_completo: anio_completo
        }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.uc_codigo}">${item.uc_nombre}</option>`);
                ucSelect.prop('disabled', false);
            } else {
                options = '<option value="">No hay U.C.</option>';
            }
            ucSelect.html(options);
        }, 'json');
    });

    $('#formReporte').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'generar_reporte');

        $.ajax({
            url: '?pagina=rReprobados',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const responseData = response.datos;
                    const tipoReporte = $('#tipo_reporte').val();
                    let chartData = { labels: [], data: [] };
                    let chartTitle = 'Estudiantes Reprobados en PER';

                    if (tipoReporte === 'general') {
                        const totalReprobadosPer = parseInt(responseData.total_reprobados_per, 10);
                        chartData.labels.push('Total del Periodo Académico');
                        chartData.data.push(totalReprobadosPer);
                        chartTitle = 'Total de Estudiantes Reprobados en PER';
                    } else if (tipoReporte === 'seccion') {
                        chartTitle = 'Reprobados en PER por Unidad Curricular';
                        responseData.forEach(item => {
                            chartData.labels.push(item.uc_nombre);
                            chartData.data.push(parseInt(item.reprobados_per, 10));
                        });
                    } else if (tipoReporte === 'uc') {
                        chartTitle = 'Reprobados en PER por Sección';
                        responseData.forEach(item => {
                            chartData.labels.push('Sección ' + item.sec_codigo);
                            chartData.data.push(parseInt(item.reprobados_per, 10));
                        });
                    }
                    renderChart(chartData, $('#tipo_grafico').val(), chartTitle);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.mensaje || 'No se pudo generar el reporte.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Hubo un problema al contactar con el servidor.'
                });
            }
        });
    });

    renderChart({ labels: [], data: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
});