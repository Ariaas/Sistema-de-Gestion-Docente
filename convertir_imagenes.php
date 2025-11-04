<?php
/**
 * Script temporal para convertir imágenes SVG a PNG
 * Ejecutar una vez y luego eliminar este archivo
 */

echo "<h2>Conversión de Imágenes SVG a PNG</h2>";

$baseDir = __DIR__;
$imgDir = $baseDir . '/public/assets/img/';

// Archivos a convertir
$archivos = [
    'LOGO.svg' => 'LOGO.png',
    'Sintillo.svg' => 'Sintillo.png'
];

echo "<p><strong>Directorio de imágenes:</strong> $imgDir</p>";

foreach ($archivos as $svgFile => $pngFile) {
    $svgPath = $imgDir . $svgFile;
    $pngPath = $imgDir . $pngFile;
    
    echo "<hr>";
    echo "<h3>Procesando: $svgFile</h3>";
    
    if (!file_exists($svgPath)) {
        echo "<p style='color: red;'>❌ ERROR: No se encontró el archivo $svgPath</p>";
        continue;
    }
    
    echo "<p>✓ Archivo SVG encontrado</p>";
    
    // Verificar si ya existe el PNG
    if (file_exists($pngPath)) {
        echo "<p style='color: orange;'>⚠ El archivo PNG ya existe: $pngPath</p>";
        echo "<p>Si deseas recrearlo, elimínalo manualmente primero.</p>";
        continue;
    }
    
    echo "<p style='color: blue;'>ℹ Para convertir este SVG a PNG, tienes las siguientes opciones:</p>";
    echo "<ol>";
    echo "<li><strong>Online:</strong> Descarga el SVG y súbelo a <a href='https://cloudconvert.com/svg-to-png' target='_blank'>CloudConvert</a></li>";
    echo "<li><strong>Photoshop/GIMP:</strong> Abre el SVG y exporta como PNG (300 DPI)</li>";
    echo "<li><strong>Inkscape:</strong> File → Export PNG Image</li>";
    echo "<li><strong>Imagemagick (si está instalado):</strong> <code>convert $svgPath -background none -resize 1000x $pngPath</code></li>";
    echo "</ol>";
    
    echo "<p><strong>Ruta del archivo SVG:</strong> <code>$svgPath</code></p>";
    echo "<p><strong>Guardar PNG como:</strong> <code>$pngPath</code></p>";
}

echo "<hr>";
echo "<h3>Resumen</h3>";
echo "<p>Una vez que hayas creado los archivos PNG y los hayas guardado en la carpeta correcta, los reportes Word mostrarán las imágenes correctamente.</p>";
echo "<p><strong>Tamaños recomendados:</strong></p>";
echo "<ul>";
echo "<li>LOGO.png: ~300x300px o más</li>";
echo "<li>Sintillo.png: ~1500x200px (ancho según tu diseño)</li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: green;'><strong>✓ Script ejecutado. Puedes eliminar este archivo (convertir_imagenes.php) después de crear las imágenes PNG.</strong></p>";
?>
