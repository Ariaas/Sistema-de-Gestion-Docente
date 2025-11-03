<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache limpiado exitosamente\n";
} else {
    echo "OPcache no está activo\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache limpiado\n";
}

echo "Cache PHP limpiado. Ejecuta las pruebas de nuevo.\n";
