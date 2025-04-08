<?php
$pagina = "principal"; 

if (!empty($_GET['pagina'])){
    $pagina = $_GET['pagina'];  
}


if(is_file("controller/".$pagina.".php")){ 

    require_once("controller/".$pagina.".php");
}
else{
    echo "PAGINA EN CONSTRUCCIÓN";
}
 
?> 