$(document).ready(function() {
    let myChart = null;
    const ctx = document.getElementById('reporteChart').getContext('2d');

    function renderChart(chartData, chartType = 'bar') {
        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Cantidad de Estudiantes',
                    data: chartData.data,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)' 
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        display: chartType === 'bar', 
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: chartType === 'bar' ? 'top' : 'right',
                        display: chartType !== 'bar' 
                    },
                    title: {
                        display: true,
                        text: 'Resultados Estudiantes (Aprobados Directo)',
                        font: {
                            size: 16
                        }
                    }
                }
            }
        });
    }

    function updateChart() {
        $('#formReporte').trigger('submit');
    }

    $('#tipo_grafico').change(updateChart);

    $('#tipo_reporte').change(function() {
        const tipo = $(this).val();
        $('#filtro_seccion_container').toggle(tipo === 'seccion');
        $('#filtro_uc_container').toggle(tipo === 'uc');
    });

    $('#anio_reporte').change(function() {
        const anio_id = $(this).val();
        const seccionSelect = $('#seccion_id');
        const ucSelect = $('#uc_id');

        if (!anio_id) {
            seccionSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            ucSelect.html('<option>Seleccione un año</option>').prop('disabled', true);
            return;
        }

        // Cargar Secciones
        seccionSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=reporte', {
            accion: 'obtener_secciones',
            anio_id: anio_id
        }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.sec_id}">${item.sec_codigo}</option>`);
                seccionSelect.prop('disabled', false);
            } else {
                options = '<option value="">No hay secciones</option>';
            }
            seccionSelect.html(options);
        }, 'json');

        ucSelect.html('<option>Cargando...</option>').prop('disabled', true);
        $.post('?pagina=reporte', {
            accion: 'obtener_uc',
            anio_id: anio_id
        }, function(data) {
            let options = '<option value="" selected disabled>Seleccionar...</option>';
            if (data.length > 0) {
                data.forEach(item => options += `<option value="${item.uc_id}">${item.uc_nombre}</option>`);
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
            url: '?pagina=reporte',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const datos = response.datos;
                    const totalEstudiantes = parseInt(datos.total_estudiantes);
                    const enPer = parseInt(datos.total_en_per);

                    const aprobadosDirecto = totalEstudiantes - enPer;

                    const chartData = {
                        
                        labels: ['Estudiantes Aprobados Directamente'],
                        
                        data: [aprobadosDirecto]
                    };
                    

                    renderChart(chartData, $('#tipo_grafico').val());
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

    renderChart({
        labels: [],
        data: []
    });
});