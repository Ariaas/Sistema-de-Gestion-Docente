<?php

if (empty($_SESSION)) {
    session_start();
}


$usuarioAutenticado = isset($_SESSION['name']) && !empty($_SESSION['name']);


$requestUri = $_SERVER['REQUEST_URI'];


$rutasProtegidas = [
    '/model/',
    '/controller/',
    '/config/',
    '/db/',
    '/views/',
    '/vendor/',
    '/archivos_subidos/',
    '/respaldos/'
];


$accesoRutaProtegida = false;
foreach ($rutasProtegidas as $ruta) {
    if (strpos($requestUri, $ruta) !== false) {
        $accesoRutaProtegida = true;
        break;
    }
}


$rutaInexistente = isset($_GET['invalid_route']) && $_GET['invalid_route'] == '1';

if ($accesoRutaProtegida || $rutaInexistente) {
    
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    $baseUrl = rtrim($scriptName, '/');
    
    if ($usuarioAutenticado) {
        
        header("Location: " . $baseUrl . "/?pagina=principal");
        exit();
    } else {
        
        header("Location: " . $baseUrl . "/?pagina=login");
        exit();
    }
} else {
   
    http_response_code(403);
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d32f2f;
            font-size: 48px;
            margin: 0;
        }
        p {
            color: #666;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>403</h1>
        <p>Acceso Denegado</p>
    </div>
</body>
</html>';
    exit();
}
