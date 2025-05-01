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

  $("#tipoEspacio").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z0-9-\b]*$/, e);
  });

  $("#tipoEspacio").on("keyup", function () {
    validarkeyup(
      /^[A-Za-z0-9]{8,9}$/,
      $(this),
      $("#srifProveedor"),
      "El formato permite de 8 a 9 carácteres"
    );
    if ($("#tipoEspacio").val().length <= 9) {
      var datos = new FormData();
      datos.append("accion", "buscar");
      datos.append("tipoEspacio", $(this).val());
      enviaAjax(datos, "buscar");
    }
  });

  $("#nombreProveedor").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
  });

  $("#nombreProveedor").on("keyup", function () {
    validarkeyup(
      /^[A-Za-z0-9,#\b\s\u00f1\u00d1\u00E0-\u00FC-]{4,30}$/,
      $(this),
      $("#snombreProveedor"),
      "Este formato no debe estar vacío / permite un máximo 30 carácteres"
    );
  });

  $("#correoProveedor").on("keypress", function (e) {
    validarkeypress(/^[A-Za-z0-9@_.\b\u00f1\u00d1\u00E0-\u00FC-]*$/, e);
  });

  $("#correoProveedor").on("keyup", function () {
    validarkeyup(
      /^[A-Za-z0-9_\u00f1\u00d1\u00E0-\u00FC-]{3,30}[@]{1}[A-Za-z0-9]{3,8}[.]{1}[A-Za-z]{2,3}$/,
      $(this),
      $("#scorreoProveedor"),
      "El formato sólo permite un correo válido!"
    );
  });

  $("#telefonoProveedor").on("keypress", function (e) {
    validarkeypress(/^[0-9-\b]*$/, e);
  });

  $("#telefonoProveedor").on("keyup", function () {
    validarkeyup(
      /^[0-9]{10,11}$/,
      $(this),
      $("#stelefonoProveedor"),
      "El formato sólo permite un número válido"
    );
  });