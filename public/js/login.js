$(document).ready(function(){

    if($.trim($("#mensajes").text()) != ""){
        muestraMensaje($("#mensajes").html());
    }
    
    $("#acceder").on("click",function(){
        event.preventDefault();
        if(validarenvio()){
            
            $("#accion").val("acceder");	
            $("#f").submit();
            
        }
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
        
        return true;
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        const mensajesDiv = document.getElementById("mensajes");
        const mensaje = mensajesDiv.getAttribute("data-mensaje");
    
        if (mensaje) {
            muestraMensaje("error", 4000, "Error", mensaje);
        }
    });