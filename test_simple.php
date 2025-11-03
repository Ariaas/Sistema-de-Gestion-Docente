<?php
require_once 'model/usuario.php';

echo "Probando verificarUsuarioExiste...\n";

$usuario = new Usuario();
$usuario->set_usuarioId(999);

try {
    $resultado = $usuario->verificarUsuarioExiste(999);
    print_r($resultado);
    echo "\n✅ La función funciona correctamente\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}
