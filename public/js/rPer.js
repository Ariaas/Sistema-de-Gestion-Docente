$(document).ready(function() {
    let myChart = null;
    let currentResponseData = null; // Se usará para cambiar entre tipos de gráfico sin recargar
    const ctx = document.getElementById('reporteChart').getContext('2d');

    const colorPalette = [
        'rgba(255, 99, 132, 0.7)', 'rgba(255, 159, 64, 0.7)',
        'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)',
        'rgba(54, 162, 235, 0.7)', 'rgba(153, 102, 255, 0.7)',
        'rgba(201, 203, 207, 0.7)', 'rgba(100, 220, 150, 0.7)'
    ];

    /**
     * Renderiza el gráfico en el canvas con las nuevas opciones.
     */
    function renderChart(chartData, chartType, chartTitle) {
        if (myChart) {
            myChart.destroy();
        }

        // Determina si el gráfico de barras será horizontal
        const isHorizontal = chartType === 'bar';

        myChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                // Eje 'y' para barras horizontales, 'x' para verticales
                indexAxis: isHorizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    // Para barras horizontales, el eje de valores es 'x'
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: {
                        display: !isHorizontal // Oculta la leyenda para barras, la muestra para torta/anillo
                    },
                    title: {
                        display: true,
                        text: chartTitle,
                        font: { size: 16 }
                    },
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
     * Procesa los datos del servidor y los prepara para el gráfico.
     */
    function processChartData(responseData) {
        const tipoReporte = $('#tipo_reporte').val();
        let labels = [];
        let data = [];
        let chartTitle = 'Estudiantes en PER';

        if (tipoReporte === 'general') {
            const totalEnPer = parseInt(responseData.total_en_per, 10);
            labels.push('Total del Periodo Académico');
            data.push(totalEnPer);
            chartTitle = 'Total de Estudiantes en PER';
        } else if (tipoReporte === 'seccion') {
            chartTitle = 'Estudiantes en PER por Unidad Curricular';
            responseData.forEach(item => {
                labels.push(item.uc_nombre);
                data.push(parseInt(item.per_cantidad, 10));
            });
        } else if (tipoReporte === 'uc') {
            chartTitle = 'Estudiantes en PER por Sección';
            responseData.forEach(item => {
                labels.push('Sección ' + item.sec_codigo);
                data.push(parseInt(item.per_cantidad, 10));
            });
        }

        const backgroundColors = data.map((_, index) => colorPalette[index % colorPalette.length]);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));

        const finalChartData = {
            labels: labels,
            datasets: [{
                label: 'Estudiantes en PER',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };

        renderChart(finalChartData, $('#tipo_grafico').val(), chartTitle);
    }


    $('#tipo_grafico').change(function() {
        if (currentResponseData) {
            processChartData(currentResponseData);
        }
    });

    $('#tipo_reporte').change(function() {
        const tipo = $(this).val();
        $('#filtro_seccion_container').toggle(tipo === 'seccion');
        $('#filtro_uc_container').toggle(tipo === 'uc');
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
        $.post('?pagina=rPer', {
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
        $.post('?pagina=rPer', {
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
            url: '?pagina=rPer',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    currentResponseData = response.datos; // Guardamos los datos
                    processChartData(currentResponseData); // Procesamos y renderizamos
                } else {
                    currentResponseData = null;
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin Datos',
                        text: response.mensaje || 'No se encontraron datos para generar el reporte.'
                    });
                    // Limpiamos el gráfico
                    renderChart({ labels: [], datasets: [{ data:[] }] }, 'bar', 'Seleccione los filtros para generar un reporte');
                }
            },
            error: function() {
                currentResponseData = null;
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Hubo un problema al contactar con el servidor.'
                });
            }
        });
    });

    // Estado inicial del gráfico
    renderChart({ labels: [], datasets: [{ data:[] }] }, 'bar', 'Seleccione los filtros para generar un reporte');
});