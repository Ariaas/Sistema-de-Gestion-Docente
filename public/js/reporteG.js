$(document).ready(function() {
    let myChart = null;
    let currentResponseData = null; // Variable para guardar los datos actuales
    const ctx = document.getElementById('reporteChart').getContext('2d');

    // Función que procesa los datos y renderiza el gráfico
    function displayChart(chartType) {
        if (!currentResponseData) {
            renderChart({ labels: [], datasets: [] }, 'bar', 'Seleccione los filtros para generar un reporte');
            return;
        }

        const tipoReporte = $('#tipo_reporte').val();
        let chartData = { labels: [], datasets: [] };
        let chartTitle = 'Resultados del Proceso de Remedial (PER)';

        // 1. Prepara los datos en formato de barras (múltiples datasets)
        if (tipoReporte === 'general') {
            const aprobadosDirecto = parseInt(currentResponseData.total_aprobados_directo, 10);
            const enPer = parseInt(currentResponseData.total_en_per, 10);
            const aprobadosPer = parseInt(currentResponseData.total_aprobados_per, 10);
            
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

            currentResponseData.forEach(item => {
                const label = (tipoReporte === 'seccion') ? item.uc_nombre : 'Sección ' + item.sec_codigo;
                labels.push(label);
                
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

        // 2. Si es Torta o Anillo, transforma los datos a un solo dataset
        if ((chartType === 'pie' || chartType === 'doughnut') && tipoReporte === 'general') {
            const transformedData = {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: []
                }]
            };

            chartData.datasets.forEach(dataset => {
                transformedData.labels.push(dataset.label);
                transformedData.datasets[0].data.push(dataset.data[0]);
                transformedData.datasets[0].backgroundColor.push(dataset.backgroundColor);
            });
            chartData = transformedData; // Usa los datos transformados
        }
        
        renderChart(chartData, chartType, chartTitle);
    }

    // Función que dibuja/actualiza el canvas del gráfico
    function renderChart(chartData, chartType, chartTitle) {
        if (myChart) {
            myChart.destroy();
        }

        // Para los gráficos de torta/anillo detallados, las barras agrupadas no se traducen bien.
        // Se podría implementar una lógica para mostrar solo un dataset si se desea.
        const isGrouped = chartData.datasets.length > 1;

        myChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: {
                responsive: true, maintainAspectRatio: false,
                scales: { 
                    x: { stacked: chartType === 'bar' && !isGrouped }, // Apilado si no es agrupado
                    y: { beginAtZero: true, display: true, ticks: { precision: 0 }, stacked: chartType === 'bar' && !isGrouped } 
                },
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: chartTitle, font: { size: 16 } }
                }
            }
        });
    }

    // Eventos de los filtros
    $('#tipo_grafico').change(function() {
        displayChart($(this).val());
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

        if (!anio_completo) {
            seccionSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            ucSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            return;
        }

        // Lógica para cargar secciones y UCs...
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
                    currentResponseData = response.datos; // Guarda los datos
                    displayChart($('#tipo_grafico').val()); // Llama a la función principal para mostrar el gráfico
                } else {
                    currentResponseData = null;
                    Swal.fire({ icon: 'error', title: 'Error', text: response.mensaje || 'No se pudo generar el reporte.' });
                    displayChart('bar'); // Limpia el gráfico
                }
            },
            error: function() {
                currentResponseData = null;
                Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'Hubo un problema al contactar con el servidor.' });
                displayChart('bar'); // Limpia el gráfico
            }
        });
    });

    displayChart('bar'); // Renderiza el gráfico vacío al iniciar
});