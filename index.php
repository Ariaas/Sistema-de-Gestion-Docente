<?php
require_once 'vendor/autoload.php';
$pagina = 'login';

if (!empty($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
}

$name = "";
if (is_file("model/verifica.php")) {
    require_once("model/verifica.php");
    $v = new verifica();
    if ($pagina == 'fin') {
        $v->destruyesesion();
    } else {
        $name = $v->leesesion();
    }
}

if (is_file('controller/' . $pagina . ".php")) {
    require_once  "controller/" . $pagina . ".php";
} else if (is_file('controller/reportes/' . $pagina . ".php")) {
    require_once  "controller/reportes/" . $pagina . ".php";
} else {
    echo 'PÁGINA EN CONSTRUCCIÓN';
}
