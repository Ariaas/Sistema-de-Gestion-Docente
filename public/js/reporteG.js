$(document).ready(function() {
    let myChart = null;
    let currentResponseData = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    const hayDatos = $('body').data('hay-datos');
    
    if (!hayDatos) {
        muestraMensaje('info', null, 'Sin Datos para Reportes', 'Actualmente no existen datos de estudiantes registrados para generar un reporte.');
    }

    const colorPalette = [
        'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(255, 99, 71, 0.7)', 'rgba(60, 179, 113, 0.7)', 'rgba(218, 112, 214, 0.7)',
        'rgba(240, 230, 140, 0.7)', 'rgba(0, 191, 255, 0.7)', 'rgba(255, 105, 180, 0.7)'
    ];

    function displayChart(chartType) {
        if (!currentResponseData) {
            renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
            return;
        }

        const tipoReporte = $('#tipo_reporte').val();
        let chartData = buildChartData(currentResponseData, chartType);
        let chartTitle = getChartTitle(tipoReporte);

        renderChart(chartData, chartType, chartTitle);
    }

    function buildChartData(data) {
        const labels = data.map(item => item.etiqueta);
        const values = data.map(item => parseInt(item.cantidad, 10));
        
        const backgroundColors = labels.map((_, i) => colorPalette[i % colorPalette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        return {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Estudiantes',
                data: values,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };
    }

    function renderChart(chartData, chartType, chartTitle) {
        if (myChart) myChart.destroy();
        
        const isBarChart = chartType === 'bar';
        const indexAxis = isBarChart ? 'y' : 'x';

        myChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                indexAxis: indexAxis,
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { 
                        beginAtZero: true, 
                        ticks: { precision: 0 },
                        display: isBarChart
                    },
                    y: {
                        display: isBarChart
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        onClick: () => {},
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const meta = chart.getDatasetMeta(0);
                                        const style = meta.controller.getStyle(i);
                                        return {
                                            text: label,
                                            fillStyle: style.backgroundColor,
                                            strokeStyle: style.borderColor,
                                            lineWidth: style.borderWidth,
                                            hidden: !chart.getDataVisibility(i),
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    title: { display: true, text: chartTitle, font: { size: 16 } },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                const tipoReporte = $('#tipo_reporte').val();
                                if (tipoReporte === 'general') {
                                    return '';
                                }
                                return tooltipItems[0].label;
                           },
                            label: function(tooltipItem) {
                                const quantity = tooltipItem.raw || '';
                                return `Cantidad de Estudiantes: ${quantity}`;
                            }
                        }
                    }
                }
            }
        });
    }

    function getChartTitle(tipoReporte) {
        if (tipoReporte === 'general') return 'Total de Estudiantes del Periodo Activo';
        if (tipoReporte === 'seccion') return 'Cantidad de Estudiantes por Sección';
        if (tipoReporte === 'trayecto') return 'Cantidad de Estudiantes por Trayecto';
        return 'Seleccione los filtros para generar un reporte';
    }

    $('#tipo_grafico').change(function() {
        if (currentResponseData) displayChart($(this).val());
    });

    $('#formReporte').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'generar_reporte');

        $.ajax({
            url: '?pagina=reporteG',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success && response.datos && response.datos.length > 0) {
                    currentResponseData = response.datos;
                    displayChart($('#tipo_grafico').val());
                } else {
                    currentResponseData = null;
                    muestraMensaje('info', null, 'Sin Datos', response.mensaje || 'No se encontraron datos para generar el reporte.');
                    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
                }
            },
            error: function() {
                currentResponseData = null;
                muestraMensaje('error', null, 'Error de Conexión', 'Hubo un problema al contactar con el servidor.');
                renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
            }
        });
    });
    
    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
});

