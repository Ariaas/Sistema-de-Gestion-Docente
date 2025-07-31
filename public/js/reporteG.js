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

    function displayChart(chartType) {
        if (!currentResponseData) {
            renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
            return;
        }
        const tipoReporte = $('#tipo_reporte').val();
        let chartDataForBar = buildBarChartData(currentResponseData, tipoReporte);
        let chartTitle = getChartTitle(tipoReporte);
        let finalChartData = chartDataForBar;

        toggleDetailFilter();

        if (chartType === 'pie' || chartType === 'doughnut') {
            const transformResult = transformForPieChart(chartDataForBar, tipoReporte);
            finalChartData = transformResult.data;
            chartTitle = transformResult.title;
        }
        
        renderChart(finalChartData, chartType, chartTitle);
    }

    function buildBarChartData(data, tipoReporte) {
        const datasets = [
            { label: 'Aprobados Directo', data: [], backgroundColor: colorPalette[1] },
            { label: 'Reprobaron PER', data: [], backgroundColor: colorPalette[5] },
            { label: 'Aprobaron PER', data: [], backgroundColor: colorPalette[0] }
        ];
        const labels = [];
        if (tipoReporte === 'general') {
            const enPer = parseInt(data.total_en_per, 10);
            const aprobadosPer = parseInt(data.total_aprobados_per, 10);
            labels.push('Resultados Generales');
            datasets[0].data.push(parseInt(data.total_aprobados_directo, 10));
            datasets[1].data.push(enPer - aprobadosPer);
            datasets[2].data.push(aprobadosPer);
        } else {
            data.forEach(item => {
                labels.push((tipoReporte === 'seccion') ? item.uc_nombre : 'Sección ' + item.sec_codigo);
                const enPer = parseInt(item.per_cantidad, 10);
                const aprobadosPer = parseInt(item.per_aprobados, 10);
                datasets[0].data.push(parseInt(item.aprobados_directo, 10));
                datasets[1].data.push(enPer - aprobadosPer);
                datasets[2].data.push(aprobadosPer);
            });
        }
        return { labels, datasets };
    }

    function transformForPieChart(barData, tipoReporte) {
        const backgroundColors = barData.datasets.map(d => d.backgroundColor);
        const borderColors = backgroundColors.map(color => color.replace('0.7', '1'));
        const pieData = {
            labels: barData.datasets.map(d => d.label),
            datasets: [{
                data: [],
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };
        let newTitle;
        const selectedIndex = parseInt($('#filtro_detalle').val(), 10) || 0;

        if (barData.labels.length === 0) {
            newTitle = getChartTitle(tipoReporte);
        } else if (tipoReporte === 'general') {
            newTitle = 'Resultados del Proceso de Remedial (PER)';
            pieData.datasets[0].data = barData.datasets.map(d => d.data[0] || 0);
        } else {
            newTitle = `Resultados para: ${barData.labels[selectedIndex]}`;
            pieData.datasets[0].data = barData.datasets.map(d => d.data[selectedIndex] || 0);
        }
        return { data: pieData, title: newTitle };
    }

    function renderChart(chartData, chartType, chartTitle) {
        if (myChart) myChart.destroy();
        myChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                 indexAxis: 'y', 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
               
                x: { beginAtZero: true, ticks: { precision: 0 } } 
            },
            plugins: {
                legend: { display: true, position: 'top' },
                title: { display: true, text: chartTitle, font: { size: 16 } },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                if (tooltipItem.dataset.label) return `${tooltipItem.dataset.label}: ${tooltipItem.formattedValue}`;
                                return `${tooltipItem.label}: ${tooltipItem.formattedValue}`;
                            }
                        }
                    }
                }
            }
        });
    }

    function getChartTitle(tipoReporte) {
        if (tipoReporte === 'general') return 'Resultados del Proceso de Remedial (PER)';
        if (tipoReporte === 'seccion') return 'Resultados por Unidad Curricular';
        if (tipoReporte === 'uc') return 'Resultados por Sección';
        return 'Seleccione los filtros para generar un reporte';
    }

    function toggleDetailFilter() {
        const tipoReporte = $('#tipo_reporte').val();
        const tipoGrafico = $('#tipo_grafico').val();
        const detailContainer = $('#filtro_detalle_container');
        if ((tipoReporte === 'seccion' || tipoReporte === 'uc') && (tipoGrafico === 'pie' || tipoGrafico === 'doughnut')) {
            detailContainer.show();
        } else {
            detailContainer.hide();
        }
    }

    function populateDetailFilter() {
        const detailSelect = $('#filtro_detalle');
        if (!currentResponseData || (Array.isArray(currentResponseData) && currentResponseData.length === 0)) {
            detailSelect.html('<option>No hay datos</option>').prop('disabled', true);
            return;
        }
        const tipoReporte = $('#tipo_reporte').val();
        if (tipoReporte === 'seccion' || tipoReporte === 'uc') {
            const labels = currentResponseData.map(item => (tipoReporte === 'seccion') ? item.uc_nombre : 'Sección ' + item.sec_codigo);
            let options = '';
            labels.forEach((label, index) => {
                options += `<option value="${index}">${label}</option>`;
            });
            detailSelect.html(options).prop('disabled', false);
        }
    }

    // --- Eventos de los filtros (CORREGIDOS) ---
    $('#tipo_grafico, #filtro_detalle').change(function() {
        if (currentResponseData) displayChart($('#tipo_grafico').val());
    });

    $('#tipo_reporte').change(function() {
        const tipo = $(this).val();
        $('#filtro_seccion_container').toggle(tipo === 'seccion');
        $('#filtro_uc_container').toggle(tipo === 'uc');
        toggleDetailFilter();
    });
    
    $('#anio_reporte').change(function() {
        const anio_completo = $(this).val();
        const seccionSelect = $('#seccion_codigo');
        const ucSelect = $('#uc_codigo');

        // Resetea los filtros al cambiar el año
        $('#tipo_reporte').val('general');
        $('#filtro_seccion_container').hide();
        $('#filtro_uc_container').hide();
        $('#filtro_detalle_container').hide();

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
                if (response.success && response.datos && (Array.isArray(response.datos) ? response.datos.length > 0 : response.datos)) {
                    currentResponseData = response.datos;
                    populateDetailFilter();
                    displayChart($('#tipo_grafico').val());
                } else {
                    currentResponseData = null;
                    Swal.fire({ icon: 'info', title: 'Sin Datos', text: response.mensaje || 'No se encontraron datos para generar el reporte.' });
                    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
                }
                toggleDetailFilter();
            },
            error: function() {
                currentResponseData = null;
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'Hubo un problema al contactar con el servidor.' });
                renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
                toggleDetailFilter();
            }
        });
    });

    renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
});