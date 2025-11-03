# Script para ejecutar todas las pruebas de seguridad con JMeter
# Sistema de Gestión Docente
# Autor: [Equipo de QA]
# Fecha: Noviembre 2025

# Configuración
$JMETER_HOME = "C:\jmeter"
$JMETER_BIN = "$JMETER_HOME\bin\jmeter.bat"
$TESTS_DIR = $PSScriptRoot
$RESULTS_DIR = Join-Path $TESTS_DIR "..\results"
$FECHA = Get-Date -Format "yyyy-MM-dd_HHmmss"
$RESULTS_FOLDER = Join-Path $RESULTS_DIR $FECHA

# Colores para output
$COLOR_SUCCESS = "Green"
$COLOR_ERROR = "Red"
$COLOR_INFO = "Cyan"
$COLOR_WARNING = "Yellow"

# Banner
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host "   PRUEBAS DE SEGURIDAD - SISTEMA DE GESTIÓN DOCENTE" -ForegroundColor $COLOR_INFO
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host ""

# Verificar que JMeter existe
if (-not (Test-Path $JMETER_BIN)) {
    Write-Host "❌ ERROR: JMeter no encontrado en $JMETER_BIN" -ForegroundColor $COLOR_ERROR
    Write-Host "Por favor, instala JMeter o actualiza la variable JMETER_HOME" -ForegroundColor $COLOR_WARNING
    exit 1
}

Write-Host "✅ JMeter encontrado: $JMETER_BIN" -ForegroundColor $COLOR_SUCCESS
Write-Host ""

# Verificar que XAMPP está corriendo
Write-Host "🔍 Verificando que el sistema esté accesible..." -ForegroundColor $COLOR_INFO
try {
    $response = Invoke-WebRequest -Uri "http://localhost/org/Sistema-de-Gestion-Docente/" -UseBasicParsing -TimeoutSec 5
    Write-Host "✅ Sistema accesible en http://localhost/org/Sistema-de-Gestion-Docente/" -ForegroundColor $COLOR_SUCCESS
} catch {
    Write-Host "❌ ERROR: Sistema no accesible. ¿XAMPP está corriendo?" -ForegroundColor $COLOR_ERROR
    Write-Host "Por favor, inicia XAMPP y verifica que Apache esté activo" -ForegroundColor $COLOR_WARNING
    exit 1
}
Write-Host ""

# Crear carpeta de resultados
if (-not (Test-Path $RESULTS_FOLDER)) {
    New-Item -ItemType Directory -Path $RESULTS_FOLDER -Force | Out-Null
    Write-Host "📁 Carpeta de resultados creada: $RESULTS_FOLDER" -ForegroundColor $COLOR_SUCCESS
}
Write-Host ""

# Lista de tests a ejecutar
$tests = @(
    @{
        File = "01_login_basico_test.jmx"
        Name = "Login Básico"
        Description = "Validación de credenciales"
        Duration = "10 segundos"
    },
    @{
        File = "02_brute_force_test.jmx"
        Name = "Fuerza Bruta"
        Description = "Protección contra ataques de fuerza bruta"
        Duration = "30 segundos"
    },
    @{
        File = "03_sql_injection_test.jmx"
        Name = "SQL Injection"
        Description = "Detección de inyección SQL"
        Duration = "20 segundos"
    },
    @{
        File = "04_xss_test.jmx"
        Name = "Cross-Site Scripting"
        Description = "Detección de vulnerabilidades XSS"
        Duration = "25 segundos"
    },
    @{
        File = "05_load_test.jmx"
        Name = "Prueba de Carga"
        Description = "Rendimiento con 50 usuarios"
        Duration = "3 minutos"
    }
)

# Preguntar al usuario qué tests ejecutar
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host "SELECCIONA LOS TESTS A EJECUTAR:" -ForegroundColor $COLOR_INFO
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host ""
Write-Host "1. Ejecutar TODOS los tests (~4 minutos)" -ForegroundColor $COLOR_INFO
Write-Host "2. Ejecutar solo tests CRÍTICOS (Login, SQL Injection) (~30 segundos)" -ForegroundColor $COLOR_INFO
Write-Host "3. Ejecutar solo tests de SEGURIDAD (sin carga) (~1.5 minutos)" -ForegroundColor $COLOR_INFO
Write-Host "4. Selección PERSONALIZADA" -ForegroundColor $COLOR_INFO
Write-Host "5. SALIR" -ForegroundColor $COLOR_WARNING
Write-Host ""

$opcion = Read-Host "Selecciona una opción (1-5)"

$testsAEjecutar = @()

switch ($opcion) {
    "1" {
        $testsAEjecutar = $tests
        Write-Host "✅ Ejecutando TODOS los tests" -ForegroundColor $COLOR_SUCCESS
    }
    "2" {
        $testsAEjecutar = $tests | Where-Object { $_.File -in @("01_login_basico_test.jmx", "03_sql_injection_test.jmx") }
        Write-Host "✅ Ejecutando tests CRÍTICOS" -ForegroundColor $COLOR_SUCCESS
    }
    "3" {
        $testsAEjecutar = $tests | Where-Object { $_.File -ne "05_load_test.jmx" }
        Write-Host "✅ Ejecutando tests de SEGURIDAD" -ForegroundColor $COLOR_SUCCESS
    }
    "4" {
        Write-Host ""
        Write-Host "Selecciona los tests a ejecutar (separados por coma, ej: 1,3,4):" -ForegroundColor $COLOR_INFO
        for ($i = 0; $i -lt $tests.Count; $i++) {
            Write-Host "$($i+1). $($tests[$i].Name) - $($tests[$i].Description)" -ForegroundColor $COLOR_INFO
        }
        $seleccion = Read-Host "Tests"
        $indices = $seleccion -split "," | ForEach-Object { [int]$_.Trim() - 1 }
        $testsAEjecutar = $indices | ForEach-Object { $tests[$_] }
        Write-Host "✅ Tests seleccionados: $($testsAEjecutar.Count)" -ForegroundColor $COLOR_SUCCESS
    }
    "5" {
        Write-Host "👋 Saliendo..." -ForegroundColor $COLOR_WARNING
        exit 0
    }
    default {
        Write-Host "❌ Opción inválida" -ForegroundColor $COLOR_ERROR
        exit 1
    }
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host "INICIANDO EJECUCIÓN DE TESTS" -ForegroundColor $COLOR_INFO
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host ""

# Contador de tests
$totalTests = $testsAEjecutar.Count
$testActual = 0
$testsExitosos = 0
$testsFallidos = 0

# Ejecutar cada test
foreach ($test in $testsAEjecutar) {
    $testActual++
    
    Write-Host "───────────────────────────────────────────────────────────" -ForegroundColor $COLOR_INFO
    Write-Host "[$testActual/$totalTests] Ejecutando: $($test.Name)" -ForegroundColor $COLOR_INFO
    Write-Host "Descripción: $($test.Description)" -ForegroundColor $COLOR_INFO
    Write-Host "Duración estimada: $($test.Duration)" -ForegroundColor $COLOR_INFO
    Write-Host "───────────────────────────────────────────────────────────" -ForegroundColor $COLOR_INFO
    
    $testFile = Join-Path $TESTS_DIR $test.File
    $resultFile = Join-Path $RESULTS_FOLDER "$($test.File -replace '.jmx', '.jtl')"
    $reportFolder = Join-Path $RESULTS_FOLDER "$($test.File -replace '.jmx', '_report')"
    
    # Verificar que el archivo de test existe
    if (-not (Test-Path $testFile)) {
        Write-Host "❌ ERROR: Archivo de test no encontrado: $testFile" -ForegroundColor $COLOR_ERROR
        $testsFallidos++
        continue
    }
    
    # Ejecutar JMeter
    $startTime = Get-Date
    
    try {
        # Ejecutar en modo no-GUI con generación de reporte
        $proceso = Start-Process -FilePath $JMETER_BIN `
            -ArgumentList "-n", "-t", "`"$testFile`"", "-l", "`"$resultFile`"", "-e", "-o", "`"$reportFolder`"" `
            -NoNewWindow -Wait -PassThru
        
        $endTime = Get-Date
        $duracion = ($endTime - $startTime).TotalSeconds
        
        if ($proceso.ExitCode -eq 0) {
            Write-Host "✅ Test completado exitosamente en $([math]::Round($duracion, 2)) segundos" -ForegroundColor $COLOR_SUCCESS
            Write-Host "📊 Resultados: $resultFile" -ForegroundColor $COLOR_INFO
            Write-Host "📈 Reporte HTML: $reportFolder\index.html" -ForegroundColor $COLOR_INFO
            $testsExitosos++
            
            # Analizar resultados básicos
            if (Test-Path $resultFile) {
                $contenido = Get-Content $resultFile
                $totalSamples = ($contenido | Measure-Object).Count - 1 # -1 por el header
                $errores = ($contenido | Select-String "false" | Measure-Object).Count
                
                Write-Host "   • Total de requests: $totalSamples" -ForegroundColor $COLOR_INFO
                Write-Host "   • Errores: $errores" -ForegroundColor $(if ($errores -gt 0) { $COLOR_WARNING } else { $COLOR_SUCCESS })
                
                if ($errores -gt 0) {
                    Write-Host "   ⚠️  Se detectaron errores. Revisar resultados." -ForegroundColor $COLOR_WARNING
                }
            }
        } else {
            Write-Host "❌ Test falló con código de salida: $($proceso.ExitCode)" -ForegroundColor $COLOR_ERROR
            $testsFallidos++
        }
    } catch {
        Write-Host "❌ ERROR al ejecutar test: $_" -ForegroundColor $COLOR_ERROR
        $testsFallidos++
    }
    
    Write-Host ""
}

# Resumen final
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host "RESUMEN DE EJECUCIÓN" -ForegroundColor $COLOR_INFO
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host ""
Write-Host "Total de tests ejecutados: $totalTests" -ForegroundColor $COLOR_INFO
Write-Host "Tests exitosos: $testsExitosos" -ForegroundColor $COLOR_SUCCESS
Write-Host "Tests fallidos: $testsFallidos" -ForegroundColor $(if ($testsFallidos -gt 0) { $COLOR_ERROR } else { $COLOR_SUCCESS })
Write-Host ""
Write-Host "📁 Resultados guardados en: $RESULTS_FOLDER" -ForegroundColor $COLOR_INFO
Write-Host ""

# Preguntar si desea abrir los reportes
$abrirReportes = Read-Host "¿Deseas abrir los reportes HTML en el navegador? (S/N)"
if ($abrirReportes -eq "S" -or $abrirReportes -eq "s") {
    Get-ChildItem -Path $RESULTS_FOLDER -Filter "index.html" -Recurse | ForEach-Object {
        Start-Process $_.FullName
    }
    Write-Host "✅ Reportes abiertos en el navegador" -ForegroundColor $COLOR_SUCCESS
}

Write-Host ""
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host "PRÓXIMOS PASOS:" -ForegroundColor $COLOR_INFO
Write-Host "═══════════════════════════════════════════════════════════" -ForegroundColor $COLOR_INFO
Write-Host ""
Write-Host "1. Revisar los reportes HTML generados" -ForegroundColor $COLOR_INFO
Write-Host "2. Analizar los archivos .jtl para detalles" -ForegroundColor $COLOR_INFO
Write-Host "3. Documentar hallazgos en Plantilla_Reporte_Ejecucion.md" -ForegroundColor $COLOR_INFO
Write-Host "4. Actualizar Matriz_Trazabilidad.md con resultados" -ForegroundColor $COLOR_INFO
Write-Host ""
Write-Host "✅ Ejecución completada!" -ForegroundColor $COLOR_SUCCESS
Write-Host ""

# Generar archivo de resumen
$resumenFile = Join-Path $RESULTS_FOLDER "resumen_ejecucion.txt"
$resumenContent = @"
═══════════════════════════════════════════════════════════
RESUMEN DE EJECUCIÓN - PRUEBAS DE SEGURIDAD
Sistema de Gestión Docente
═══════════════════════════════════════════════════════════

Fecha de ejecución: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Usuario: $env:USERNAME
Máquina: $env:COMPUTERNAME

TESTS EJECUTADOS:
─────────────────────────────────────────────────────────
Total: $totalTests
Exitosos: $testsExitosos
Fallidos: $testsFallidos

TESTS INDIVIDUALES:
─────────────────────────────────────────────────────────
"@

foreach ($test in $testsAEjecutar) {
    $resumenContent += "`n• $($test.Name) - $($test.Description)"
}

$resumenContent += @"

`n
UBICACIÓN DE RESULTADOS:
─────────────────────────────────────────────────────────
$RESULTS_FOLDER

ARCHIVOS GENERADOS:
─────────────────────────────────────────────────────────
"@

Get-ChildItem -Path $RESULTS_FOLDER | ForEach-Object {
    $resumenContent += "`n• $($_.Name)"
}

$resumenContent += @"

`n
═══════════════════════════════════════════════════════════
FIN DEL RESUMEN
═══════════════════════════════════════════════════════════
"@

$resumenContent | Out-File -FilePath $resumenFile -Encoding UTF8
Write-Host "📄 Resumen guardado en: $resumenFile" -ForegroundColor $COLOR_INFO
Write-Host ""
