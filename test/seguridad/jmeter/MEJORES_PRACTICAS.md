# 🎯 Mejores Prácticas - Scripts JMeter
## Basado en el Script CRUD_Completo_Malla.jmx

---

## 📚 Lecciones Aprendidas

Este documento recopila las **mejores prácticas** observadas en el script `CRUD_Completo_Malla.jmx` que pueden aplicarse a otros tests.

---

## 1️⃣ Variables Globales en Test Plan

### ✅ Buena Práctica

```xml
<Arguments guiclass="ArgumentsPanel" testclass="Arguments" testname="Variables">
  <collectionProp name="Arguments.arguments">
    <elementProp name="USERNAME" elementType="Argument">
      <stringProp name="Argument.value">LigiaDuran</stringProp>
    </elementProp>
    <elementProp name="PASSWORD" elementType="Argument">
      <stringProp name="Argument.value">Carolina.16</stringProp>
    </elementProp>
  </collectionProp>
</Arguments>
```

### 💡 Ventajas

- ✅ **Centralización**: Cambiar credenciales en un solo lugar
- ✅ **Reutilización**: Usar `${USERNAME}` y `${PASSWORD}` en todos los requests
- ✅ **Mantenimiento**: Fácil actualización sin editar múltiples requests
- ✅ **Flexibilidad**: Puedes sobrescribir desde línea de comandos

### 📝 Cómo Usar

```powershell
# Ejecutar con credenciales diferentes
C:\jmeter\bin\jmeter.bat -n -t test.jmx `
  -JUSERNAME=OtroUsuario `
  -JPASSWORD=OtraContraseña `
  -l results.jtl
```

---

## 2️⃣ SetupThreadGroup para Login

### ✅ Buena Práctica

```xml
<SetupThreadGroup guiclass="SetupThreadGroupGui" testclass="SetupThreadGroup" testname="Login">
  <intProp name="ThreadGroup.num_threads">1</intProp>
  <intProp name="ThreadGroup.ramp_time">1</intProp>
  <elementProp name="ThreadGroup.main_controller" elementType="LoopController">
    <stringProp name="LoopController.loops">1</stringProp>
  </elementProp>
</SetupThreadGroup>
```

### 💡 Ventajas

- ✅ **Ejecución única**: Se ejecuta UNA SOLA VEZ antes de todos los tests
- ✅ **Sesión compartida**: Todos los ThreadGroups usan la misma sesión
- ✅ **Eficiencia**: No hace login repetidamente
- ✅ **Realismo**: Simula comportamiento real (login una vez, múltiples acciones)

### ❌ Evitar

```xml
<!-- NO hacer login en cada ThreadGroup -->
<ThreadGroup>
  <HTTPSamplerProxy testname="Login">...</HTTPSamplerProxy>
  <HTTPSamplerProxy testname="Acción 1">...</HTTPSamplerProxy>
  <HTTPSamplerProxy testname="Acción 2">...</HTTPSamplerProxy>
</ThreadGroup>
```

### 📊 Comparación

| Aspecto | Login en cada Thread | SetupThreadGroup |
|---------|---------------------|------------------|
| **Ejecuciones de login** | N × threads | 1 vez |
| **Carga en servidor** | Alta | Baja |
| **Realismo** | Bajo | Alto |
| **Eficiencia** | Baja | Alta |

---

## 3️⃣ Cookie Manager Configurado Correctamente

### ✅ Buena Práctica

```xml
<CookieManager guiclass="CookiePanel" testclass="CookieManager" testname="Cookies">
  <collectionProp name="CookieManager.cookies"/>
  <boolProp name="CookieManager.clearEachIteration">false</boolProp>
  <boolProp name="CookieManager.controlledByThreadGroup">false</boolProp>
</CookieManager>
```

### 💡 Configuración Clave

**`clearEachIteration = false`**
- ✅ Mantiene cookies entre iteraciones
- ✅ Preserva la sesión de login
- ✅ Simula navegación real

**`controlledByThreadGroup = false`**
- ✅ Cookies compartidas entre ThreadGroups
- ✅ Sesión global para todo el test

### ❌ Evitar

```xml
<!-- NO limpiar cookies si necesitas mantener sesión -->
<boolProp name="CookieManager.clearEachIteration">true</boolProp>
```

---

## 4️⃣ Headers AJAX Apropiados

### ✅ Buena Práctica

```xml
<HeaderManager guiclass="HeaderPanel" testclass="HeaderManager" testname="Headers">
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
```

### 💡 Importancia

**`X-Requested-With: XMLHttpRequest`**
- ✅ Identifica la petición como AJAX
- ✅ Algunos backends validan este header
- ✅ Respuestas pueden variar según este header
- ✅ Simula comportamiento de JavaScript real

**`Content-Type: application/x-www-form-urlencoded`**
- ✅ Formato estándar para formularios POST
- ✅ Compatible con PHP `$_POST`

---

## 5️⃣ Distribución Realista de Carga

### ✅ Buena Práctica

```xml
<!-- 70% Lectura -->
<ThreadGroup testname="Lectura (70%)">
  <intProp name="ThreadGroup.num_threads">35</intProp>
  <intProp name="ThreadGroup.ramp_time">10</intProp>
  <stringProp name="LoopController.loops">5</stringProp>
</ThreadGroup>

<!-- 15% Escritura -->
<ThreadGroup testname="Registrar (15%)">
  <intProp name="ThreadGroup.num_threads">8</intProp>
  <intProp name="ThreadGroup.ramp_time">5</intProp>
  <stringProp name="LoopController.loops">2</stringProp>
</ThreadGroup>

<!-- 10% Actualización -->
<ThreadGroup testname="Modificar (10%)">
  <intProp name="ThreadGroup.num_threads">5</intProp>
  <intProp name="ThreadGroup.ramp_time">5</intProp>
  <stringProp name="LoopController.loops">2</stringProp>
</ThreadGroup>

<!-- 5% Eliminación -->
<ThreadGroup testname="Eliminar (5%)">
  <intProp name="ThreadGroup.num_threads">2</intProp>
  <intProp name="ThreadGroup.ramp_time">5</intProp>
  <stringProp name="LoopController.loops">1</stringProp>
</ThreadGroup>
```

### 💡 Regla 70-20-10

En aplicaciones web típicas:
- **70%** Operaciones de **lectura** (SELECT, GET)
- **20%** Operaciones de **escritura** (INSERT, POST)
- **10%** Operaciones de **actualización/eliminación** (UPDATE, DELETE)

### 📊 Beneficios

- ✅ **Realismo**: Simula tráfico real
- ✅ **Detección de cuellos de botella**: Identifica problemas en operaciones específicas
- ✅ **Optimización dirigida**: Saber dónde enfocar mejoras

---

## 6️⃣ Pausas Realistas (Think Time)

### ✅ Buena Práctica

```xml
<ConstantTimer guiclass="ConstantTimerGui" testclass="ConstantTimer" testname="Pausa">
  <stringProp name="ConstantTimer.delay">300</stringProp>
</ConstantTimer>
```

### 💡 Tiempos Recomendados

| Acción | Think Time | Razón |
|--------|-----------|-------|
| **Entre páginas** | 1-3 segundos | Usuario lee contenido |
| **Formulario simple** | 5-10 segundos | Usuario completa campos |
| **Formulario complejo** | 15-30 segundos | Usuario piensa y valida |
| **Búsqueda** | 2-5 segundos | Usuario escribe query |
| **Click en botón** | 0.3-1 segundo | Reacción humana |

### ❌ Evitar

```xml
<!-- NO hacer requests sin pausas -->
<HTTPSamplerProxy testname="Página 1"/>
<HTTPSamplerProxy testname="Página 2"/>
<HTTPSamplerProxy testname="Página 3"/>
<!-- Esto no es realista -->
```

### 🎯 Timer Aleatorio (Más Realista)

```xml
<UniformRandomTimer guiclass="UniformRandomTimerGui" testclass="UniformRandomTimer">
  <stringProp name="ConstantTimer.delay">1000</stringProp>
  <stringProp name="RandomTimer.range">2000</stringProp>
</UniformRandomTimer>
<!-- Pausa entre 1-3 segundos aleatoriamente -->
```

---

## 7️⃣ HTTP Request Defaults

### ✅ Buena Práctica

```xml
<ConfigTestElement guiclass="HttpDefaultsGui" testclass="ConfigTestElement" testname="HTTP Defaults">
  <stringProp name="HTTPSampler.domain">localhost</stringProp>
  <stringProp name="HTTPSampler.port">80</stringProp>
  <stringProp name="HTTPSampler.protocol">http</stringProp>
  <stringProp name="HTTPSampler.implementation">HttpClient4</stringProp>
</ConfigTestElement>
```

### 💡 Ventajas

- ✅ **DRY**: No repetir dominio en cada request
- ✅ **Portabilidad**: Cambiar servidor en un solo lugar
- ✅ **Ambientes**: Fácil cambio entre dev/staging/prod

### 📝 Uso con Variables

```xml
<Arguments testname="Variables">
  <elementProp name="BASE_URL" elementType="Argument">
    <stringProp name="Argument.value">localhost</stringProp>
  </elementProp>
</Arguments>

<ConfigTestElement testname="HTTP Defaults">
  <stringProp name="HTTPSampler.domain">${BASE_URL}</stringProp>
</ConfigTestElement>
```

```powershell
# Cambiar servidor desde línea de comandos
C:\jmeter\bin\jmeter.bat -n -t test.jmx -JBASE_URL=staging.example.com
```

---

## 8️⃣ Estructura de Carpetas Organizada

### ✅ Buena Práctica

```
proyecto/
├── test/
│   └── seguridad/
│       ├── jmeter/
│       │   ├── tests/           # Scripts .jmx
│       │   ├── data/            # CSV con datos
│       │   ├── results/         # Resultados de ejecución
│       │   └── lib/             # Plugins de JMeter
│       ├── docs/                # Documentación
│       └── scripts/             # Scripts de automatización
```

### 💡 Beneficios

- ✅ **Organización**: Todo relacionado junto
- ✅ **Versionamiento**: Fácil de versionar en Git
- ✅ **Colaboración**: Equipo encuentra archivos fácilmente
- ✅ **CI/CD**: Rutas predecibles para automatización

---

## 9️⃣ Nomenclatura Clara

### ✅ Buena Práctica

```
01_login_basico_test.jmx
02_brute_force_test.jmx
03_sql_injection_test.jmx
CRUD_Completo_Malla.jmx
Load_Test_50_Users.jmx
```

### 💡 Convenciones

- ✅ **Prefijo numérico**: Orden de ejecución
- ✅ **Descripción clara**: Qué hace el test
- ✅ **Sufijo `_test`**: Identifica como test
- ✅ **Snake_case o PascalCase**: Consistencia

### ❌ Evitar

```
test1.jmx
prueba.jmx
nuevo.jmx
test_final_final_v2.jmx
```

---

## 🔟 Comentarios y Documentación

### ✅ Buena Práctica

```xml
<TestPlan testname="CRUD Completo - Malla Curricular">
  <stringProp name="TestPlan.comments">
    Prueba CRUD completa del módulo Malla Curricular
    - Crea 5 mallas
    - Consulta todas
    - Modifica 3 mallas
    - Elimina 2 mallas
    Duración estimada: 2 minutos
  </stringProp>
</TestPlan>
```

### 💡 Qué Documentar

- ✅ **Objetivo del test**: Qué valida
- ✅ **Precondiciones**: Qué debe existir antes
- ✅ **Datos requeridos**: CSV, usuarios, etc.
- ✅ **Duración estimada**: Tiempo de ejecución
- ✅ **Casos de prueba**: IDs de casos relacionados

---

## 📋 Checklist de Mejores Prácticas

Al crear un nuevo script JMeter, verifica:

### Configuración General
- [ ] Variables globales definidas (USERNAME, PASSWORD, BASE_URL)
- [ ] HTTP Request Defaults configurado
- [ ] Cookie Manager agregado con `clearEachIteration=false`
- [ ] Header Manager con headers apropiados

### Estructura
- [ ] SetupThreadGroup para login (si aplica)
- [ ] ThreadGroups con nombres descriptivos
- [ ] Distribución realista de carga (70-20-10)
- [ ] Pausas (Think Time) entre requests

### Assertions
- [ ] Validación de código HTTP (200, 201, etc.)
- [ ] Validación de contenido de respuesta
- [ ] Validación de tiempo de respuesta
- [ ] Mensajes de error descriptivos

### Listeners
- [ ] View Results Tree (para debugging)
- [ ] Summary Report (para métricas)
- [ ] Aggregate Report (para análisis)

### Datos
- [ ] CSV Data Set Config para datos variables
- [ ] Datos de prueba suficientes
- [ ] Cleanup de datos después del test

### Documentación
- [ ] Comentarios en TestPlan
- [ ] README.md explicando el test
- [ ] Casos de prueba documentados

---

## 🎯 Plantilla Recomendada

```xml
<?xml version="1.0" encoding="UTF-8"?>
<jmeterTestPlan version="1.2">
  <hashTree>
    <TestPlan testname="[Nombre del Test]">
      <stringProp name="TestPlan.comments">[Descripción y objetivo]</stringProp>
    </TestPlan>
    <hashTree>
      
      <!-- 1. Variables Globales -->
      <Arguments testname="Variables">
        <elementProp name="USERNAME">
          <stringProp name="Argument.value">usuario</stringProp>
        </elementProp>
        <elementProp name="PASSWORD">
          <stringProp name="Argument.value">password</stringProp>
        </elementProp>
      </Arguments>
      
      <!-- 2. HTTP Defaults -->
      <ConfigTestElement testname="HTTP Defaults">
        <stringProp name="HTTPSampler.domain">localhost</stringProp>
        <stringProp name="HTTPSampler.port">80</stringProp>
      </ConfigTestElement>
      
      <!-- 3. Cookie Manager -->
      <CookieManager testname="Cookies">
        <boolProp name="CookieManager.clearEachIteration">false</boolProp>
      </CookieManager>
      
      <!-- 4. Header Manager -->
      <HeaderManager testname="Headers">
        <elementProp name="">
          <stringProp name="Header.name">X-Requested-With</stringProp>
          <stringProp name="Header.value">XMLHttpRequest</stringProp>
        </elementProp>
      </HeaderManager>
      
      <!-- 5. Setup: Login -->
      <SetupThreadGroup testname="Login">
        <HTTPSamplerProxy testname="Login">
          <!-- Login request -->
        </HTTPSamplerProxy>
      </SetupThreadGroup>
      
      <!-- 6. Test Principal -->
      <ThreadGroup testname="Test Principal">
        <HTTPSamplerProxy testname="Acción 1">
          <!-- Request -->
        </HTTPSamplerProxy>
        <ConstantTimer testname="Pausa">
          <stringProp name="ConstantTimer.delay">1000</stringProp>
        </ConstantTimer>
      </ThreadGroup>
      
      <!-- 7. Listeners -->
      <ResultCollector testname="View Results Tree"/>
      <ResultCollector testname="Summary Report"/>
      
    </hashTree>
  </hashTree>
</jmeterTestPlan>
```

---

## 📚 Recursos Adicionales

### Documentación Oficial
- [JMeter User Manual](https://jmeter.apache.org/usermanual/)
- [Best Practices](https://jmeter.apache.org/usermanual/best-practices.html)

### Scripts de Referencia
- `CRUD_Completo_Malla.jmx` - Ejemplo completo de CRUD
- `01_login_basico_test.jmx` - Login simple
- `05_load_test.jmx` - Prueba de carga

---

**Última Actualización**: Noviembre 2, 2025  
**Basado en**: CRUD_Completo_Malla.jmx  
**Autor**: Equipo de QA
