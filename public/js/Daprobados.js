$(document).ready(function() {
    let myChart = null;
    let currentResponseData = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    const colorPalette = [
        'rgba(54, 162, 235, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(153, 102, 255, 0.7)', 'rgba(255, 99, 132, 0.7)',
        'rgba(100, 220, 150, 0.7)', 'rgba(220, 100, 100, 0.7)'
    ];

    /**
     * Función principal para mostrar/actualizar el gráfico.
     */
    function displayChart(chartType) {
        if (!currentResponseData) {
            renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
            return;
        }

        const tipoReporte = $('#tipo_reporte').val();
        let chartDataForBar = buildChartData(currentResponseData, tipoReporte);
        let chartTitle = getChartTitle(tipoReporte);
        let finalChartData = chartDataForBar;

        if (chartType === 'pie' || chartType === 'doughnut') {
            const transformResult = transformForPieChart(chartDataForBar);
            finalChartData = transformResult.data;
            chartTitle = transformResult.title;
        }
        
        renderChart(finalChartData, chartType, chartTitle);
    }

    /**
     * Construye la estructura de datos para un gráfico.
     */
    function buildChartData(data, tipoReporte) {
        const labels = [];
        const dataValues = [];

        if (tipoReporte === 'general') {
            labels.push('Total del Periodo Académico');
            dataValues.push(parseInt(data.total_aprobados_directo, 10));
        } else {
            data.forEach(item => {
                labels.push((tipoReporte === 'seccion') ? item.uc_nombre : 'Sección ' + item.sec_codigo);
                dataValues.push(parseInt(item.apro_cantidad, 10));
            });
        }
        
        const backgroundColors = dataValues.map((_, index) => colorPalette[index % colorPalette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        return {
            labels,
            datasets: [{
                label: 'Aprobados Directo',
                data: dataValues,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };
    }

    /**
     * Transforma los datos para un gráfico de torta/anillo.
     */
    function transformForPieChart(barData) {
        const tipoReporte = $('#tipo_reporte').val();
        const newTitle = getChartTitle(tipoReporte);
        return {
            title: newTitle,
            data: {
                labels: barData.labels,
                datasets: [{
                    label: 'Aprobados Directo',
                    data: barData.datasets[0].data,
                    backgroundColor: barData.datasets[0].backgroundColor,
                    borderColor: barData.datasets[0].borderColor,
                    borderWidth: 1
                }]
            }
        };
    }
    
    /**
     * Renderiza el gráfico en el canvas.
     */
    function renderChart(chartData, chartType, chartTitle) {
        if (myChart) myChart.destroy();
        
        const isHorizontal = chartType === 'bar';

        myChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                indexAxis: isHorizontal ? 'y' : 'x',
                responsive: true, maintainAspectRatio: false,
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: {
                        display: !isHorizontal
                    },
                    title: { display: true, text: chartTitle, font: { size: 16 } },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                const datasetLabel = tooltipItem.dataset.label || '';
                                const label = `${datasetLabel}: ${tooltipItem.formattedValue}`;
                                return " " + label;
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Genera el título del gráfico.
     */
    function getChartTitle(tipoReporte) {
        if (tipoReporte === 'general') return 'Total de Aprobados Directo';
        if (tipoReporte === 'seccion') return 'Aprobados Directo por Unidad Curricular';
        if (tipoReporte === 'uc') return 'Aprobados Directo por Sección';
        return 'Seleccione los filtros para generar un reporte';
    }

    // --- MANEJO DE EVENTOS ---

    $('#tipo_reporte').change(function() {
        const tipo = $(this).val();
        $('#filtro_seccion_container').toggle(tipo === 'seccion');
        $('#filtro_uc_container').toggle(tipo === 'uc');
    });

    $('#tipo_grafico').change(function() {
        if (currentResponseData) {
            displayChart($('#tipo_grafico').val());
        }
    });

    $('#anio_reporte').change(function() {
        const anio_completo = $(this).val();
        const seccionSelect = $('#seccion_codigo');
        const ucSelect = $('#uc_codigo');

        $('#tipo_reporte').val('general');
        $('#filtro_seccion_container, #filtro_uc_container').hide();

        if (!anio_completo) {
            seccionSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            ucSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            return;
        }

        seccionSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=Daprobados', { accion: 'obtener_secciones', anio_completo: anio_completo }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.sec_codigo}">${item.sec_codigo}</option>`);
                seccionSelect.prop('disabled', false);
            } else { options = '<option value="">No hay secciones</option>'; }
            seccionSelect.html(options);
        }, 'json');

        ucSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=Daprobados', { accion: 'obtener_uc', anio_completo: anio_completo }, function(data) {
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
            url: '?pagina=Daprobados', type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if (response.success && response.datos && (Array.isArray(response.datos) ? response.datos.length > 0 : Object.keys(response.datos).length > 0)) {
                    currentResponseData = response.datos;
                    displayChart($('#tipo_grafico').val());
                } else {
                    currentResponseData = null;
                    Swal.fire({ icon: 'info', title: 'Sin Datos', text: response.mensaje || 'No se encontraron datos para generar el reporte.' });
                    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
                }
            },
            error: function() {
                currentResponseData = null;
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'Hubo un problema al contactar con el servidor.' });
            }
        });
    });

    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
});