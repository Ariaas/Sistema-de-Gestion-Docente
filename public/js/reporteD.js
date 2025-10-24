$(document).ready(function() {
    let myChart = null;
    let currentResponseData = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    const hayDatos = $('body').data('hay-datos');
    
    
    if (!hayDatos) {
        muestraMensaje('info', null, 'Sin Datos para Reportes', 'Actualmente no existen docentes con horas asignadas para generar un reporte.');
    }

    const colorPalette = [
        'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(255, 99, 71, 0.7)', 'rgba(60, 179, 113, 0.7)', 'rgba(218, 112, 214, 0.7)',
        'rgba(240, 230, 140, 0.7)', 'rgba(0, 191, 255, 0.7)', 'rgba(255, 105, 180, 0.7)'
    ];

    function displayChart(chartType) {
        if (!currentResponseData) {
            renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione el tipo de reporte para generar el gráfico');
            return;
        }

        const tipoReporte = $('#tipo_reporte').val();
        let chartData = buildChartData(currentResponseData); 
        let chartTitle = getChartTitle(tipoReporte);

        renderChart(chartData, chartType, chartTitle);
    }

    function buildChartData(data) {
        const labels = data.map(item => item.etiqueta);
        const values = data.map(item => parseInt(item.cantidad, 10));
        
        const backgroundColors = labels.map((_, i) => colorPalette[i % colorPalette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        let datasetLabel = 'Cantidad de Docentes'; 
        
        return {
            labels: labels,
            datasets: [{
                label: datasetLabel,
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
        const indexAxis = isBarChart && chartData.labels.length > 0 ? 'y' : 'x';

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
                        
                        display: isBarChart && chartData.labels.length > 0
                    },
                    y: {
                        
                        display: isBarChart && chartData.labels.length > 0
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
                                
                                return tooltipItems[0].label; 
                            },
                            label: function(tooltipItem) {
                                const quantity = tooltipItem.raw || '';
                                return `${tooltipItem.dataset.label}: ${quantity}`;
                            }
                        }
                    }
                }
            }
        });
    }

    
    function getChartTitle(tipoReporte) {
        if (tipoReporte === 'docente_distribucion') return 'Distribución de Horas Asignadas por Docente';
        if (tipoReporte === 'docente_mayor_a_diez') return 'Docentes con más de 10 Horas Asignadas';
        
        return 'Seleccione el tipo de reporte para generar el gráfico';
    }

    $('#tipo_grafico').change(function() {
        if (currentResponseData) displayChart($(this).val());
    });

    $('#formReporte').submit(function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('accion', 'generar_reporte');

        $.ajax({
            url: '?pagina=reporteD',
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
                    
                    renderChart({ labels: [], datasets: [] }, 'bar', 'No hay datos para mostrar');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                currentResponseData = null;
                console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
                muestraMensaje('error', null, 'Error de Conexión', 'Hubo un problema al contactar con el servidor. Verifique la consola para más detalles.');
                renderChart({ labels: [], datasets: [] }, 'bar', 'Error al cargar datos');
            }
        });
    });
    
    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione el tipo de reporte para generar el gráfico');
});