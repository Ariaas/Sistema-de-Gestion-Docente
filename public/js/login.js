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
        $.post("?pagina=login&accion=recuperar", { usuario: $("#usuarioRecuperar").val() }, function(resp){
            muestraMensaje("info", 4000, "Recuperación", resp);
            if(resp.includes("enviado")) {
                $("#usuarioCodigo").val($("#usuarioRecuperar").val());
                $("#modalRecuperarUsuario").modal("hide");
                setTimeout(function(){
                    $("#modalCodigo").modal("show");
                }, 500);
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
  
    // function muestraMensaje(icono,tiempo,titulo,mensaje){
    //     Swal.fire({
    //     icon:icono,
    //     timer:tiempo,	
    //     title:titulo,
    //     html:mensaje,
    //     showConfirmButton:true,
    //     confirmButtonText:'Aceptar',
    //     });
    // }
    
    document.addEventListener("DOMContentLoaded", function() {
        const mensajesDiv = document.getElementById("mensajes");
        const mensaje = mensajesDiv.getAttribute("data-mensaje");
    
        if (mensaje) {
            muestraMensaje("error", 4000, "Error", mensaje);
        }
    });

    // function validarkeypress(er,e){
        
    //     key = e.keyCode;
        
        
    //     tecla = String.fromCharCode(key);
        
        
    //     a = er.test(tecla);
        
    //     if(!a){
        
    //         e.preventDefault();
    //     }
        
        
    // }
 
    // function validarkeyup(er,etiqueta,etiquetamensaje,
    // mensaje){
    //     a = er.test(etiqueta.val());
    //     if(a){
    //         etiquetamensaje.text("");
    //         return 1;
    //     }
    //     else{
    //         etiquetamensaje.text(mensaje);
    //         return 0;
    //     }
    // }