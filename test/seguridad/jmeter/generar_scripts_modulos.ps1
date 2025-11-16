# ============================================================================
# Generador de Scripts JMeter por Módulo
# Sistema de Gestión Docente
# ============================================================================

$TEST_DIR = "C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests"

# Lista de módulos del sistema
$modulos = @(
    @{ Nombre = "UC"; Pagina = "uc"; Campos = @("uc_nombre", "uc_codigo") },
    @{ Nombre = "Área"; Pagina = "area"; Campos = @("area_nombre") },
    @{ Nombre = "Eje"; Pagina = "eje"; Campos = @("eje_nombre") },
    @{ Nombre = "Categoría"; Pagina = "categoria"; Campos = @("cat_nombre") },
    @{ Nombre = "Título"; Pagina = "titulo"; Campos = @("tit_nombre") },
    @{ Nombre = "Turno"; Pagina = "turno"; Campos = @("tur_nombre") },
    @{ Nombre = "Rol"; Pagina = "rol"; Campos = @("rol_nombre") },
    @{ Nombre = "Coordinación"; Pagina = "coordinacion"; Campos = @("coo_nombre") },
    @{ Nombre = "Malla Curricular"; Pagina = "mallacurricular"; Campos = @("mal_nombre") },
    @{ Nombre = "Prosecución"; Pagina = "prosecusion"; Campos = @("pro_nombre") },
    @{ Nombre = "Año"; Pagina = "anio"; Campos = @("anio_nombre") },
    @{ Nombre = "Espacios"; Pagina = "espacios"; Campos = @("esp_nombre") },
    @{ Nombre = "Fase"; Pagina = "fase"; Campos = @("fas_nombre") },
    @{ Nombre = "Perfil"; Pagina = "perfil"; Campos = @("usu_nombre", "usu_correo") },
    @{ Nombre = "Backup"; Pagina = "backup"; Campos = @() },
    @{ Nombre = "Bitácora"; Pagina = "bitacora"; Campos = @() }
)

Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  GENERADOR DE SCRIPTS JMETER POR MÓDULO                       ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$generados = 0

foreach ($modulo in $modulos) {
    $nombreArchivo = "modulo_$($modulo.Pagina)_test.jmx"
    $rutaArchivo = Join-Path $TEST_DIR $nombreArchivo
    
    Write-Host "📝 Generando: " -NoNewline -ForegroundColor Yellow
    Write-Host $nombreArchivo -ForegroundColor White
    
    # Determinar campo principal para pruebas
    $campoPrincipal = if ($modulo.Campos.Count -gt 0) { $modulo.Campos[0] } else { "nombre" }
    
    # Generar contenido del script
    $contenido = @"
<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2" properties="5.0" jmeter="5.6.3">
  <hashTree>
    <TestPlan guiclass="TestPlanGui" testclass="TestPlan" testname="Pruebas de Seguridad - Módulo $($modulo.Nombre)">
      <stringProp name="TestPlan.comments">Pruebas de seguridad para el módulo $($modulo.Nombre)</stringProp>
      <boolProp name="TestPlan.tearDown_on_shutdown">true</boolProp>
      <elementProp name="TestPlan.user_defined_variables" elementType="Arguments" guiclass="ArgumentsPanel" testclass="Arguments">
        <collectionProp name="Arguments.arguments">
          <elementProp name="USUARIO" elementType="Argument">
            <stringProp name="Argument.name">USUARIO</stringProp>
            <stringProp name="Argument.value">LigiaDuran</stringProp>
            <stringProp name="Argument.metadata">=</stringProp>
          </elementProp>
          <elementProp name="PASSWORD" elementType="Argument">
            <stringProp name="Argument.name">PASSWORD</stringProp>
            <stringProp name="Argument.value">Carolina.16</stringProp>
            <stringProp name="Argument.metadata">=</stringProp>
          </elementProp>
        </collectionProp>
      </elementProp>
    </TestPlan>
    <hashTree>
      <ConfigTestElement guiclass="HttpDefaultsGui" testclass="ConfigTestElement" testname="HTTP Request Defaults">
        <stringProp name="HTTPSampler.domain">localhost</stringProp>
        <stringProp name="HTTPSampler.port">80</stringProp>
        <stringProp name="HTTPSampler.protocol">http</stringProp>
        <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments">
          <collectionProp name="Arguments.arguments"/>
        </elementProp>
      </ConfigTestElement>
      <hashTree/>
      <CookieManager guiclass="CookiePanel" testclass="CookieManager" testname="HTTP Cookie Manager" enabled="true">
        <collectionProp name="CookieManager.cookies"/>
        <boolProp name="CookieManager.clearEachIteration">false</boolProp>
      </CookieManager>
      <hashTree/>
      <HeaderManager guiclass="HeaderPanel" testclass="HeaderManager" testname="HTTP Header Manager" enabled="true">
        <collectionProp name="HeaderManager.headers">
          <elementProp name="" elementType="Header">
            <stringProp name="Header.name">Content-Type</stringProp>
            <stringProp name="Header.value">application/x-www-form-urlencoded</stringProp>
          </elementProp>
          <elementProp name="" elementType="Header">
            <stringProp name="Header.name">X-Requested-With</stringProp>
            <stringProp name="Header.value">XMLHttpRequest</stringProp>
          </elementProp>
        </collectionProp>
      </HeaderManager>
      <hashTree/>
      <SetupThreadGroup guiclass="SetupThreadGroupGui" testclass="SetupThreadGroup" testname="Setup: Login" enabled="true">
        <intProp name="ThreadGroup.num_threads">1</intProp>
        <intProp name="ThreadGroup.ramp_time">1</intProp>
        <elementProp name="ThreadGroup.main_controller" elementType="LoopController" guiclass="LoopControlPanel" testclass="LoopController">
          <stringProp name="LoopController.loops">1</stringProp>
        </elementProp>
      </SetupThreadGroup>
      <hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="Login" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=login</stringProp>
          <stringProp name="HTTPSampler.method">POST</stringProp>
          <boolProp name="HTTPSampler.follow_redirects">true</boolProp>
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments">
            <collectionProp name="Arguments.arguments">
              <elementProp name="accion" elementType="HTTPArgument">
                <stringProp name="Argument.name">accion</stringProp>
                <stringProp name="Argument.value">ingresar</stringProp>
              </elementProp>
              <elementProp name="usu_usuario" elementType="HTTPArgument">
                <stringProp name="Argument.name">usu_usuario</stringProp>
                <stringProp name="Argument.value">`${USUARIO}</stringProp>
              </elementProp>
              <elementProp name="usu_clave" elementType="HTTPArgument">
                <stringProp name="Argument.name">usu_clave</stringProp>
                <stringProp name="Argument.value">`${PASSWORD}</stringProp>
              </elementProp>
            </collectionProp>
          </elementProp>
        </HTTPSamplerProxy>
        <hashTree/>
      </hashTree>
      <ThreadGroup guiclass="ThreadGroupGui" testclass="ThreadGroup" testname="Pruebas de Seguridad - $($modulo.Nombre)" enabled="true">
        <intProp name="ThreadGroup.num_threads">1</intProp>
        <intProp name="ThreadGroup.ramp_time">1</intProp>
        <elementProp name="ThreadGroup.main_controller" elementType="LoopController" guiclass="LoopControlPanel" testclass="LoopController">
          <stringProp name="LoopController.loops">1</stringProp>
        </elementProp>
      </ThreadGroup>
      <hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="1. Acceso al Módulo" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=$($modulo.Pagina)</stringProp>
          <stringProp name="HTTPSampler.method">GET</stringProp>
        </HTTPSamplerProxy>
        <hashTree>
          <ResponseAssertion guiclass="AssertionGui" testclass="ResponseAssertion" testname="Debe Permitir Acceso" enabled="true">
            <collectionProp name="Asserion.test_strings">
              <stringProp name="49586">200</stringProp>
            </collectionProp>
            <stringProp name="Assertion.test_field">Assertion.response_code</stringProp>
            <intProp name="Assertion.test_type">8</intProp>
            <stringProp name="Assertion.custom_message">Error: No se puede acceder al módulo $($modulo.Nombre)</stringProp>
          </ResponseAssertion>
          <hashTree/>
        </hashTree>
        <CSVDataSet guiclass="TestBeanGUI" testclass="CSVDataSet" testname="CSV - SQL Payloads" enabled="true">
          <stringProp name="filename">C:/xampp/htdocs/org/Sistema-de-Gestion-Docente/test/seguridad/jmeter/data/sql_payloads.csv</stringProp>
          <stringProp name="variableNames">PAYLOAD</stringProp>
          <boolProp name="recycle">false</boolProp>
          <boolProp name="stopThread">true</boolProp>
        </CSVDataSet>
        <hashTree/>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="2. SQL Injection - Búsqueda" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=$($modulo.Pagina)</stringProp>
          <stringProp name="HTTPSampler.method">POST</stringProp>
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments">
            <collectionProp name="Arguments.arguments">
              <elementProp name="accion" elementType="HTTPArgument">
                <stringProp name="Argument.name">accion</stringProp>
                <stringProp name="Argument.value">buscar</stringProp>
              </elementProp>
              <elementProp name="$campoPrincipal" elementType="HTTPArgument">
                <stringProp name="Argument.name">$campoPrincipal</stringProp>
                <stringProp name="Argument.value">`${PAYLOAD}</stringProp>
              </elementProp>
            </collectionProp>
          </elementProp>
        </HTTPSamplerProxy>
        <hashTree>
          <ResponseAssertion guiclass="AssertionGui" testclass="ResponseAssertion" testname="No Debe Mostrar Errores SQL" enabled="true">
            <collectionProp name="Asserion.test_strings">
              <stringProp name="104382626">mysql</stringProp>
              <stringProp name="1611946891">syntax error</stringProp>
              <stringProp name="82350">SQL</stringProp>
            </collectionProp>
            <stringProp name="Assertion.test_field">Assertion.response_data</stringProp>
            <intProp name="Assertion.test_type">6</intProp>
            <stringProp name="Assertion.custom_message">¡VULNERABLE! SQL Injection en $($modulo.Nombre)</stringProp>
          </ResponseAssertion>
          <hashTree/>
        </hashTree>
        <CSVDataSet guiclass="TestBeanGUI" testclass="CSVDataSet" testname="CSV - XSS Payloads" enabled="true">
          <stringProp name="filename">C:/xampp/htdocs/org/Sistema-de-Gestion-Docente/test/seguridad/jmeter/data/xss_payloads.csv</stringProp>
          <stringProp name="variableNames">XSS_PAYLOAD</stringProp>
          <boolProp name="recycle">false</boolProp>
          <boolProp name="stopThread">true</boolProp>
        </CSVDataSet>
        <hashTree/>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="3. XSS - Campo Principal" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=$($modulo.Pagina)</stringProp>
          <stringProp name="HTTPSampler.method">POST</stringProp>
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments">
            <collectionProp name="Arguments.arguments">
              <elementProp name="accion" elementType="HTTPArgument">
                <stringProp name="Argument.name">accion</stringProp>
                <stringProp name="Argument.value">guardar</stringProp>
              </elementProp>
              <elementProp name="$campoPrincipal" elementType="HTTPArgument">
                <stringProp name="Argument.name">$campoPrincipal</stringProp>
                <stringProp name="Argument.value">`${XSS_PAYLOAD}</stringProp>
              </elementProp>
            </collectionProp>
          </elementProp>
        </HTTPSamplerProxy>
        <hashTree>
          <ResponseAssertion guiclass="AssertionGui" testclass="ResponseAssertion" testname="No Debe Reflejar Script" enabled="true">
            <collectionProp name="Asserion.test_strings">
              <stringProp name="-879047401">&lt;script&gt;</stringProp>
              <stringProp name="2019908052">onerror=</stringProp>
            </collectionProp>
            <stringProp name="Assertion.test_field">Assertion.response_data</stringProp>
            <intProp name="Assertion.test_type">6</intProp>
            <stringProp name="Assertion.custom_message">¡VULNERABLE! XSS en $($modulo.Nombre)</stringProp>
          </ResponseAssertion>
          <hashTree/>
        </hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="4. IDOR - Acceso No Autorizado" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=$($modulo.Pagina)&amp;id=999999</stringProp>
          <stringProp name="HTTPSampler.method">GET</stringProp>
        </HTTPSamplerProxy>
        <hashTree>
          <ResponseAssertion guiclass="AssertionGui" testclass="ResponseAssertion" testname="Debe Rechazar Acceso" enabled="true">
            <collectionProp name="Asserion.test_strings">
              <stringProp name="49586">200</stringProp>
            </collectionProp>
            <stringProp name="Assertion.test_field">Assertion.response_code</stringProp>
            <intProp name="Assertion.test_type">20</intProp>
            <stringProp name="Assertion.custom_message">¡VULNERABLE! IDOR en $($modulo.Nombre)</stringProp>
          </ResponseAssertion>
          <hashTree/>
        </hashTree>
        <HTTPSamplerProxy guiclass="HttpTestSampleGui" testclass="HTTPSamplerProxy" testname="5. CSRF - Guardar Sin Token" enabled="true">
          <stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/?pagina=$($modulo.Pagina)</stringProp>
          <stringProp name="HTTPSampler.method">POST</stringProp>
          <elementProp name="HTTPsampler.Arguments" elementType="Arguments" guiclass="HTTPArgumentsPanel" testclass="Arguments">
            <collectionProp name="Arguments.arguments">
              <elementProp name="accion" elementType="HTTPArgument">
                <stringProp name="Argument.name">accion</stringProp>
                <stringProp name="Argument.value">guardar</stringProp>
              </elementProp>
              <elementProp name="$campoPrincipal" elementType="HTTPArgument">
                <stringProp name="Argument.name">$campoPrincipal</stringProp>
                <stringProp name="Argument.value">Test CSRF</stringProp>
              </elementProp>
            </collectionProp>
          </elementProp>
        </HTTPSamplerProxy>
        <hashTree>
          <ResponseAssertion guiclass="AssertionGui" testclass="ResponseAssertion" testname="Debe Rechazar Sin Token" enabled="true">
            <collectionProp name="Asserion.test_strings">
              <stringProp name="3548">ok</stringProp>
              <stringProp name="-1315374803">exitoso</stringProp>
            </collectionProp>
            <stringProp name="Assertion.test_field">Assertion.response_data</stringProp>
            <intProp name="Assertion.test_type">6</intProp>
            <stringProp name="Assertion.custom_message">¡VULNERABLE! CSRF en $($modulo.Nombre)</stringProp>
          </ResponseAssertion>
          <hashTree/>
        </hashTree>
      </hashTree>
      <ResultCollector guiclass="ViewResultsFullVisualizer" testclass="ResultCollector" testname="Ver Árbol de Resultados" enabled="true">
        <boolProp name="ResultCollector.error_logging">false</boolProp>
        <objProp>
          <name>saveConfig</name>
          <value class="SampleSaveConfiguration">
            <time>true</time>
            <latency>true</latency>
            <timestamp>true</timestamp>
            <success>true</success>
            <label>true</label>
            <code>true</code>
            <message>true</message>
            <threadName>true</threadName>
            <dataType>true</dataType>
            <encoding>false</encoding>
            <assertions>true</assertions>
            <subresults>true</subresults>
            <responseData>true</responseData>
            <samplerData>false</samplerData>
            <xml>false</xml>
            <fieldNames>true</fieldNames>
            <responseHeaders>false</responseHeaders>
            <requestHeaders>false</requestHeaders>
            <responseDataOnError>true</responseDataOnError>
            <saveAssertionResultsFailureMessage>true</saveAssertionResultsFailureMessage>
            <assertionsResultsToSave>0</assertionsResultsToSave>
            <bytes>true</bytes>
            <sentBytes>true</sentBytes>
            <url>true</url>
            <threadCounts>true</threadCounts>
            <idleTime>true</idleTime>
            <connectTime>true</connectTime>
          </value>
        </objProp>
        <stringProp name="filename"></stringProp>
      </ResultCollector>
      <hashTree/>
      <ResultCollector guiclass="SummaryReport" testclass="ResultCollector" testname="Reporte Resumen" enabled="true">
        <boolProp name="ResultCollector.error_logging">false</boolProp>
        <objProp>
          <name>saveConfig</name>
          <value class="SampleSaveConfiguration">
            <time>true</time>
            <latency>true</latency>
            <timestamp>true</timestamp>
            <success>true</success>
            <label>true</label>
            <code>true</code>
            <message>true</message>
            <threadName>true</threadName>
            <dataType>true</dataType>
            <encoding>false</encoding>
            <assertions>true</assertions>
            <subresults>true</subresults>
            <responseData>false</responseData>
            <samplerData>false</samplerData>
            <xml>false</xml>
            <fieldNames>true</fieldNames>
            <responseHeaders>false</responseHeaders>
            <requestHeaders>false</requestHeaders>
            <responseDataOnError>false</responseDataOnError>
            <saveAssertionResultsFailureMessage>true</saveAssertionResultsFailureMessage>
            <assertionsResultsToSave>0</assertionsResultsToSave>
            <bytes>true</bytes>
            <sentBytes>true</sentBytes>
            <url>true</url>
            <threadCounts>true</threadCounts>
            <idleTime>true</idleTime>
            <connectTime>true</connectTime>
          </value>
        </objProp>
        <stringProp name="filename"></stringProp>
      </ResultCollector>
      <hashTree/>
    </hashTree>
  </hashTree>
</jmeterTestPlan>
"@
    
    # Guardar archivo
    $contenido | Out-File -FilePath $rutaArchivo -Encoding UTF8
    
    Write-Host "   ✅ Generado exitosamente`n" -ForegroundColor Green
    $generados++
}

Write-Host "╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║  RESUMEN                                                       ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Green

Write-Host "✅ Scripts generados: " -NoNewline
Write-Host $generados -ForegroundColor White
Write-Host "📁 Ubicación: " -NoNewline
Write-Host "$TEST_DIR`n" -ForegroundColor White

Write-Host "🎯 Próximos pasos:" -ForegroundColor Cyan
Write-Host "   1. Revisar los scripts generados"
Write-Host "   2. Ejecutar pruebas individuales:"
Write-Host "      C:\jmeter\bin\jmeter.bat -t tests\modulo_[nombre]_test.jmx"
Write-Host "   3. O ejecutar todos con el script de automatización`n"

Write-Host "════════════════════════════════════════════════════════════════`n" -ForegroundColor Cyan
