<?php

use App\Model\ValidacionSelect;
use App\Model\Connection;

require_once 'config/dbconnection.php';
require_once 'vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['name'])) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: application/json');
    echo json_encode(['valido' => false, 'mensaje' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['valido' => false, 'mensaje' => 'Método no permitido']);
    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'validar_select_bd') {
    
    $tabla = $_POST['tabla'] ?? null;
    $columna = $_POST['columna'] ?? null;
    $valor = $_POST['valor'] ?? null;
    $columnaEstado = $_POST['columna_estado'] ?? null;

    if (!$tabla || !$columna || $valor === null) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json');
        echo json_encode(['valido' => false, 'mensaje' => 'Parámetros faltantes']);
        exit();
    }

    try {
        $connection = new Connection();
        $pdo = $connection->Con();

        ValidacionSelect::validarExisteEnBD($pdo, $tabla, $columna, $valor, $columnaEstado);

        echo json_encode([
            'valido' => true,
            'mensaje' => 'Valor válido'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'valido' => false,
            'mensaje' => $e->getMessage()
        ]);
    }

    exit();
}

if (isset($_POST['accion']) && $_POST['accion'] === 'validar_select_bd_multiple') {
    
    $tabla = $_POST['tabla'] ?? null;
    $columna = $_POST['columna'] ?? null;
    $valor = $_POST['valor'] ?? null;
    $columnaEstado = $_POST['columna_estado'] ?? null;
    $separador = $_POST['separador'] ?? null;

    if (!$tabla || !$columna || $valor === null) {
        header('HTTP/1.1 400 Bad Request');
        header('Content-Type: application/json');
        echo json_encode(['valido' => false, 'mensaje' => 'Parámetros faltantes']);
        exit();
    }

    try {
        $connection = new Connection();
        $pdo = $connection->Con();

        if ($separador && strpos($valor, '||') !== false) {
            $partes = explode('||', $valor);
            
            if ($tabla === 'tbl_titulo' && count($partes) === 2) {
                $sql = "SELECT COUNT(*) FROM {$tabla} 
                        WHERE tit_prefijo = :parte1 
                        AND tit_nombre = :parte2 
                        AND {$columnaEstado} = 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':parte1' => $partes[0],
                    ':parte2' => $partes[1]
                ]);
                
                if ($stmt->fetchColumn() == 0) {
                    throw new Exception('El título seleccionado no existe o está inactivo');
                }
            }
        } else {
            ValidacionSelect::validarExisteEnBD($pdo, $tabla, $columna, $valor, $columnaEstado);
        }

        echo json_encode([
            'valido' => true,
            'mensaje' => 'Valor válido'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'valido' => false,
            'mensaje' => $e->getMessage()
        ]);
    }

    exit();
}

header('HTTP/1.1 400 Bad Request');
echo json_encode(['valido' => false, 'mensaje' => 'Acción no válida']);
exit();
