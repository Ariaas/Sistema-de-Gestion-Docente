//Función para validar por Keypress
function validarkeypress(er, e) {
    key = e.keyCode;

    tecla = String.fromCharCode(key);

    a = er.test(tecla);

    if (!a) {
        e.preventDefault();
    }
}

  //Función para validar por keyup
function validarkeyup(er, etiqueta, etiquetamensaje, mensaje) {
    a = er.test(etiqueta.val());
    if (a) {
        etiquetamensaje.text("");
        return 1;
    } else {
        etiquetamensaje.text(mensaje);
        return 0;
    }
}

function muestraMensaje(icono, tiempo, titulo, mensaje) {
  Swal.fire({
    icon: icono,
    timer: tiempo,
    title: titulo,
    html: mensaje,
    showConfirmButton: true,
    confirmButtonText: "Aceptar",
  });
}  