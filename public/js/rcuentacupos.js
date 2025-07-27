document.addEventListener('DOMContentLoaded', function() {
   
    const form = document.getElementById('fReporteCuentaCupos');

    if (form) {
        form.addEventListener('submit', function(event) {
           
            const anioId = document.getElementById('anio').value;

          
            if (anioId === '') {
                
                event.preventDefault();
                
                
                alert('Por favor, seleccione un Año Académico para continuar.');
            }
          
        });
    }
});