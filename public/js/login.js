$(document).ready(function(){

    if($.trim($("#mensajes").text()) != ""){
        muestraMensaje($("#mensajes").html());
    }
    
    $("#acceder").on("click",function(event){
        event.preventDefault();
        if(validarenvio()){
            
            $("#accion").val("acceder");	
            $("#f").submit();
            
        }
    });

    $("#nombreUsuario").on("keyup", function() {
        validarkeyup(/^[A-Za-z0-9\s]{5,30}$/, $(this), $("#snombreUsuario"), "El usuario debe tener entre 5 y 30 caracteres.");
    });

    $("#contraseniaUsuario").on("keyup", function() {
        validarkeyup(/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]).{8,30}$/, $(this), $("#scontraseniaUsuario"), "Debe tener entre 8-30 caracteres, al menos una mayúscula y un carácter especial.");
    });
    
    $("#recuperarBtn").on("click", function(){
        $("#modalRecuperarUsuario").modal("show");
    });

    $("#formRecuperarUsuario").on("submit", function(e){
        e.preventDefault();

        const captchaResponse = $(this).find('.g-recaptcha-response').val();

        if (!captchaResponse) {
            muestraMensaje("error", 4000, "Error", "Por favor, complete el captcha.");
            return;
        }

        $.post("?pagina=login&accion=recuperar", { 
            usuario: $("#usuarioRecuperar").val(),
            'g-recaptcha-response': captchaResponse 
        }, function(resp){
            grecaptcha.reset(); 
            
            if(resp.includes("enviado")) {
                muestraMensaje("info", 4000, "Recuperación", resp);
                $("#usuarioCodigo").val($("#usuarioRecuperar").val());
                $("#modalRecuperarUsuario").modal("hide");
                setTimeout(function(){
                    $("#modalCodigo").modal("show");
                }, 500);
            } else {
                muestraMensaje("error", 4000, "Error", resp);
            }
        });
    });

    $("#formCodigo").on("submit", function(e){
        e.preventDefault();
        $.post("?pagina=login&accion=validarCodigo", {
            usuario: $("#usuarioCodigo").val(),
            codigo: $("#codigoRecuperacion").val()
        }, function(resp){
            if(resp == "ok") {
                $("#usuarioClave").val($("#usuarioCodigo").val());
                $("#codigoClave").val($("#codigoRecuperacion").val());
                $("#modalCodigo").modal("hide");
                setTimeout(function(){
                    $("#modalNuevaClave").modal("show");
                }, 500);
            } else {
                muestraMensaje("error", 4000, "Código incorrecto", resp);
            }
        });
    });

    $("#formNuevaClave").on("submit", function(e){
        e.preventDefault();
        let clave1 = $("#nuevaClave1").val();
        let clave2 = $("#nuevaClave2").val();
        if(clave1 !== clave2){
            muestraMensaje("error", 4000, "Error", "Las contraseñas no coinciden");
            return;
        }
        $.post("?pagina=login&accion=cambiarClave", {
            usuario: $("#usuarioClave").val(),
            codigo: $("#codigoClave").val(),
            nuevaClave: clave1
        }, function(resp){
            muestraMensaje("success", 4000, "Contraseña actualizada", resp);
            $("#modalNuevaClave").modal("hide");
        });
    });
});
   
function validarenvio(){
    let esValido = true;
    if (validarkeyup(/^[A-Za-z0-9\s]{5,30}$/, $("#nombreUsuario"), $("#snombreUsuario"), "El usuario debe tener entre 5 y 30 caracteres.") == 0) {
        esValido = false;
    }
    if (validarkeyup(/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]).{8,30}$/, $("#contraseniaUsuario"), $("#scontraseniaUsuario"), "Debe tener entre 8-30 caracteres, al menos una mayúscula y un carácter especial.") == 0) {
        esValido = false;
    }

    if (!esValido) {
        muestraMensaje("error", 4000, "Error!", "Campos con errores en el formato.");
    }

    return esValido;
}
    document.addEventListener("DOMContentLoaded", function() {
        const mensajesDiv = document.getElementById("mensajes");
        const mensaje = mensajesDiv.getAttribute("data-mensaje");
    
        if (mensaje) {
            muestraMensaje("error", 4000, "Error", mensaje);
        }
    });