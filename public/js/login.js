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
        
    });
    
   
    function validarenvio(){
        
        return true;
    }
    
    
  
    function muestraMensaje(icono,tiempo,titulo,mensaje){
        Swal.fire({
        icon:icono,
        timer:tiempo,	
        title:titulo,
        html:mensaje,
        showConfirmButton:true,
        confirmButtonText:'Aceptar',
        });
    }
    
    document.addEventListener("DOMContentLoaded", function() {
        const mensajesDiv = document.getElementById("mensajes");
        const mensaje = mensajesDiv.getAttribute("data-mensaje");
    
        if (mensaje) {
            muestraMensaje("error", 4000, "Error", mensaje);
        }
    });

    function validarkeypress(er,e){
        
        key = e.keyCode;
        
        
        tecla = String.fromCharCode(key);
        
        
        a = er.test(tecla);
        
        if(!a){
        
            e.preventDefault();
        }
        
        
    }
 
    function validarkeyup(er,etiqueta,etiquetamensaje,
    mensaje){
        a = er.test(etiqueta.val());
        if(a){
            etiquetamensaje.text("");
            return 1;
        }
        else{
            etiquetamensaje.text(mensaje);
            return 0;
        }
    }