<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}


$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'check':
       
        checkSession();
        break;
        
    case 'renew':
        
        renewSession();
        break;
        
    case 'activity':
        
        updateActivity();
        break;
        
    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}


function checkSession() {
    $active = isset($_SESSION['name']) && !empty($_SESSION['name']);
    
    if ($active) {
            
        if (isset($_SESSION['last_activity'])) {
            $timeout = 2 * 60 * 60; 
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > $timeout) {
                
                session_destroy();
                echo json_encode(['active' => false, 'reason' => 'expired']);
                exit;
            }
        }
    }
    
    echo json_encode([
        'active' => $active,
        'user' => $active ? $_SESSION['name'] : null
    ]);
}


function renewSession() {
    if (!isset($_SESSION['name']) || empty($_SESSION['name'])) {
        echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
        exit;
    }
    
    
    $_SESSION['last_activity'] = time();
    $_SESSION['session_start'] = time();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión renovada exitosamente',
        'timestamp' => $_SESSION['last_activity']
    ]);
}


function updateActivity() {
    if (!isset($_SESSION['name']) || empty($_SESSION['name'])) {
        echo json_encode(['success' => false, 'error' => 'No hay sesión activa']);
        exit;
    }
    
    
    $_SESSION['last_activity'] = time();
    
    echo json_encode([
        'success' => true,
        'timestamp' => $_SESSION['last_activity']
    ]);
}
