# Scripts de Pruebas JMeter - Sistema de Gestión Docente

Este directorio contiene los scripts de pruebas de seguridad automatizadas usando Apache JMeter.

## 📋 Scripts Disponibles

### Pruebas Básicas de Seguridad

| Script | Descripción | Casos de Prueba | Prioridad |
|--------|-------------|-----------------|-----------|
| `01_login_basico_test.jmx` | Validación de login con credenciales correctas e incorrectas | PS-001, PS-002 | Alta |
| `02_brute_force_test.jmx` | Prueba de protección contra ataques de fuerza bruta | PS-003 | Alta |
| `03_sql_injection_test.jmx` | Prueba de vulnerabilidades de inyección SQL | PS-010, PS-011, PS-012 | Crítica |
| `04_xss_test.jmx` | Prueba de vulnerabilidades Cross-Site Scripting (XSS) | PS-013, PS-014, PS-015 | Alta |
| `05_load_test.jmx` | Prueba de carga y rendimiento del sistema | PS-016 | Media |

### Pruebas Avanzadas de Seguridad

| Script | Descripción | Casos de Prueba | Prioridad |
|--------|-------------|-----------------|-----------|
| `06_session_management_test.jmx` | Prueba de gestión de sesiones y tokens | PS-017 | Alta |
| `07_access_control_test.jmx` | Prueba de control de acceso y autorización (OWASP A01) | PS-018 | Crítica |
| `08_csrf_test.jmx` | Prueba de protección contra CSRF | PS-019 | Alta |
| `09_file_upload_test.jmx` | Prueba de seguridad en subida de archivos | PS-020 | Alta |
| `10_security_headers_test.jmx` | Verificación de headers de seguridad HTTP | PS-021 | Media |

---

## 🚀 Cómo Ejecutar

### Opción 1: Interfaz Gráfica (Recomendado para aprender)

```powershell
# 1. Abrir JMeter
cd C:\jmeter\bin
.\jmeter.bat

# 2. File > Open
# 3. Seleccionar el archivo .jmx deseado
# 4. Click en Start (▶️)
```

### Opción 2: Línea de Comandos (Recomendado para ejecución)

```powershell
# Navegar a la carpeta de tests
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Ejecutar un test específico
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ../results/login_results.jtl

# Con reporte HTML
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ../results/login_results.jtl -e -o ../results/login_report
```

---

## 📊 Detalles de Cada Script

### 1. Login Básico Test (01_login_basico_test.jmx)

**Objetivo**: Validar autenticación básica

**Configuración**:
- Threads: 1
- Loop: 1
- Duración: ~10 segundos

**Pruebas**:
1. Login con credenciales válidas (admin/Admin123!)
2. Login con credenciales inválidas

**Resultado Esperado**:
- ✅ Login válido: Redirección a "principal"
- ✅ Login inválido: Permanece en login

**Cómo ejecutar**:
```powershell
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ../results/01_results.jtl
```

---

### 2. Brute Force Test (02_brute_force_test.jmx)

**Objetivo**: Detectar protección contra fuerza bruta

**Configuración**:
- Threads: 1
- Loop: Infinito (hasta terminar CSV)
- CSV: passwords.csv (22 contraseñas)
- Delay: 1 segundo entre intentos

**Pruebas**:
1. Intenta login con cada contraseña del CSV
2. Detecta si encuentra la contraseña correcta
3. Detecta mensajes de bloqueo de cuenta

**Resultado Esperado**:
- ✅ Sistema seguro: Bloqueo después de N intentos
- ❌ Sistema vulnerable: Permite todos los intentos

**Cómo ejecutar**:
```powershell
C:\jmeter\bin\jmeter.bat -n -t 02_brute_force_test.jmx -l ../results/02_results.jtl
```

**⚠️ ADVERTENCIA**: Esta prueba puede bloquear cuentas. Usar solo en ambiente de pruebas.

---

### 3. SQL Injection Test (03_sql_injection_test.jmx)

**Objetivo**: Detectar vulnerabilidades de inyección SQL

**Configuración**:
- Threads: 1
- Loop: Infinito (hasta terminar CSV)
- CSV: sql_payloads.csv (19 payloads)
- Delay: 500ms entre intentos

**Pruebas**:
1. Inyecta payloads SQL en campo usuario
2. Inyecta payloads SQL en campo contraseña
3. Detecta errores SQL en respuesta
4. Detecta bypass de autenticación

**Payloads incluidos**:
- `' OR '1'='1`
- `admin' --`
- `' UNION SELECT NULL--`
- Y más...

**Resultado Esperado**:
- ✅ Sistema seguro: No muestra errores SQL, rechaza payloads
- ❌ Sistema vulnerable: Errores SQL visibles o login exitoso

**Cómo ejecutar**:
```powershell
C:\jmeter\bin\jmeter.bat -n -t 03_sql_injection_test.jmx -l ../results/03_results.jtl
```

---

### 4. XSS Test (04_xss_test.jmx)

**Objetivo**: Detectar vulnerabilidades XSS

**Configuración**:
- Threads: 1
- Loop: 1 (con 15 sub-loops para payloads)
- CSV: xss_payloads.csv (15 payloads)
- Requiere login previo

**Pruebas**:
1. Login como admin
2. Inyecta payloads XSS en búsqueda
3. Verifica si scripts se ejecutan o se escapan

**Payloads incluidos**:
- `<script>alert('XSS')</script>`
- `<img src=x onerror=alert('XSS')>`
- `<svg onload=alert('XSS')>`
- Y más...

**Resultado Esperado**:
- ✅ Sistema seguro: Scripts escapados (ej: `&lt;script&gt;`)
- ❌ Sistema vulnerable: Scripts sin escapar

**Cómo ejecutar**:
```powershell
C:\jmeter\bin\jmeter.bat -n -t 04_xss_test.jmx -l ../results/04_results.jtl
```

---

### 5. Load Test (05_load_test.jmx)

**Objetivo**: Evaluar rendimiento bajo carga

**Configuración**:
- Threads: 50 usuarios
- Ramp-up: 30 segundos
- Loop: 3 iteraciones
- Duración: 3 minutos
- Think time: 1-3 segundos

**Escenario**:
1. Login
2. Consultar página principal
3. Consultar módulo Eje
4. Consultar módulo Docentes
5. (Repetir 3 veces)

**Métricas evaluadas**:
- Tiempo de respuesta promedio
- Throughput (requests/segundo)
- Tasa de error
- 90th percentile

**Resultado Esperado**:
- ✅ Average < 3000ms
- ✅ Error % < 1%
- ✅ Throughput > 10 req/s

**Cómo ejecutar**:
```powershell
C:\jmeter\bin\jmeter.bat -n -t 05_load_test.jmx -l ../results/05_results.jtl -e -o ../results/05_report
```

---

## 🔧 Configuración Previa

### Requisitos
1. ✅ XAMPP corriendo
2. ✅ Sistema accesible en http://localhost/org/Sistema-de-Gestion-Docente
3. ✅ Usuario admin creado con contraseña Admin123!
4. ✅ Archivos CSV en carpeta `../data/`

### Verificar Archivos CSV

```powershell
# Verificar que existen los archivos
dir ..\data\

# Deberías ver:
# passwords.csv
# sql_payloads.csv
# xss_payloads.csv
# usuarios_roles.csv
```

---

## 📈 Interpretar Resultados

### En Interfaz Gráfica

**View Results Tree**:
- Verde ✅ = Prueba pasó
- Rojo ❌ = Prueba falló
- Ver "Response data" para detalles

**Summary Report**:
- **# Samples**: Número de requests
- **Average**: Tiempo promedio (ms)
- **Error %**: Porcentaje de errores
- **Throughput**: Requests por segundo

### En Línea de Comandos

**Archivo .jtl**:
```powershell
# Ver resultados
type ..\results\01_results.jtl
```

**Reporte HTML**:
```powershell
# Abrir en navegador
start ..\results\01_report\index.html
```

---

## 🎯 Orden Recomendado de Ejecución

### Para Auditoría Completa

```
1. 01_login_basico_test.jmx (Validación básica)
2. 03_sql_injection_test.jmx (Vulnerabilidad crítica)
3. 04_xss_test.jmx (Vulnerabilidad alta)
4. 02_brute_force_test.jmx (Protección de cuenta)
5. 05_load_test.jmx (Rendimiento)
```

### Para Prueba Rápida (15 minutos)

```
1. 01_login_basico_test.jmx
2. 03_sql_injection_test.jmx
```

---

## 🛠️ Personalización

### Cambiar Credenciales

Editar variables en cada script:
```xml
<elementProp name="USUARIO" elementType="Argument">
  <stringProp name="Argument.value">admin</stringProp>
</elementProp>
<elementProp name="PASSWORD" elementType="Argument">
  <stringProp name="Argument.value">Admin123!</stringProp>
</elementProp>
```

### Cambiar URL

Editar HTTP Request Defaults:
```xml
<stringProp name="HTTPSampler.domain">localhost</stringProp>
<stringProp name="HTTPSampler.path">/org/Sistema-de-Gestion-Docente/</stringProp>
```

### Cambiar Número de Usuarios (Load Test)

```xml
<stringProp name="ThreadGroup.num_threads">50</stringProp>
<stringProp name="ThreadGroup.ramp_time">30</stringProp>
```

---

## 📝 Logs y Resultados

### Estructura de Carpetas

```
jmeter/
├── tests/          (Scripts .jmx)
├── data/           (Archivos CSV)
└── results/        (Resultados de ejecución)
    ├── *.jtl       (Logs de resultados)
    └── */          (Reportes HTML)
```

### Guardar Resultados

```powershell
# Crear carpeta de resultados con fecha
$fecha = Get-Date -Format "yyyy-MM-dd"
mkdir ..\results\$fecha

# Ejecutar y guardar
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ..\results\$fecha\01_results.jtl
```

---

## ⚠️ Advertencias Importantes

### 🚨 SOLO EN AMBIENTE DE PRUEBAS
- **NUNCA** ejecutar en producción
- Usar solo datos de prueba
- Informar al equipo antes de ejecutar

### 🚨 CAPTCHA
Si el sistema tiene CAPTCHA activo:
```php
// Comentar temporalmente en controller/login.php
// SOLO PARA PRUEBAS
/*
if (!$o->validarCaptcha($captcha)) {
    $mensaje = "Captcha inválido. Intente de nuevo.";
} else {
*/
    // ... código de login
/*
}
*/
```

### 🚨 BACKUP
Antes de ejecutar pruebas:
```sql
-- Hacer backup de la base de datos
mysqldump -u root sistema_gestion > backup_antes_pruebas.sql
```

---

## 🐛 Troubleshooting

### Error: "Connection refused"
```
Solución: Verificar que XAMPP esté corriendo
```

### Error: "File not found" (CSV)
```
Solución: Verificar rutas relativas en CSV Data Set Config
Ruta correcta: ../data/passwords.csv
```

### Error: "Assertion failed" en todos los tests
```
Solución: 
1. Verificar URL del sistema
2. Verificar credenciales
3. Ver "Response data" en View Results Tree
```

### Pruebas muy lentas
```
Solución:
1. Reducir número de threads
2. Aumentar ramp-up time
3. Agregar delays entre requests
```

---

## 🆕 Descripción de Pruebas Avanzadas

### 6. Session Management Test (06_session_management_test.jmx)

**Objetivo**: Verificar seguridad de gestión de sesiones

**Pruebas**:
1. **Unicidad de tokens**: Verifica que cada login genera un token único
2. **Invalidación**: Verifica que sesiones se invalidan después del logout
3. **Reutilización**: Intenta reutilizar sesiones expiradas

**Resultado Esperado**:
- ✅ Tokens únicos por sesión
- ✅ Sesiones invalidadas al logout
- ❌ Sesiones reutilizables = VULNERABLE

---

### 7. Access Control Test (07_access_control_test.jmx)

**Objetivo**: Verificar control de acceso y autorización (OWASP A01:2021)

**Pruebas**:
1. **Acceso sin autenticación**: Intenta acceder a páginas protegidas
2. **Archivos sensibles**: Intenta acceder a config/database.php
3. **Directory listing**: Verifica que directorios no sean listables
4. **Path traversal**: Intenta acceder a archivos del sistema

**Resultado Esperado**:
- ✅ Redirección a login (302/303)
- ✅ Archivos config inaccesibles (403/404)
- ❌ Acceso directo = VULNERABLE

---

### 8. CSRF Test (08_csrf_test.jmx)

**Objetivo**: Verificar protección contra Cross-Site Request Forgery

**Pruebas**:
1. **Sin token CSRF**: Intenta enviar formulario sin token
2. **Referer malicioso**: Envía request desde origen externo

**Resultado Esperado**:
- ✅ Requests rechazados sin token
- ✅ Validación de origen
- ❌ Acción ejecutada = VULNERABLE

---

### 9. File Upload Test (09_file_upload_test.jmx)

**Objetivo**: Verificar seguridad en subida de archivos

**Pruebas**:
1. **Archivo PHP**: Intenta subir shell.php
2. **Doble extensión**: Intenta subir shell.php.jpg
3. **Archivo ejecutable**: Intenta subir malware.exe

**Resultado Esperado**:
- ✅ Archivos peligrosos rechazados
- ✅ Validación de extensión y MIME type
- ❌ Archivo subido = VULNERABLE

---

### 10. Security Headers Test (10_security_headers_test.jmx)

**Objetivo**: Verificar presencia de headers de seguridad HTTP

**Headers Verificados**:
- `X-Frame-Options`: Protección contra clickjacking
- `X-Content-Type-Options`: Previene MIME sniffing
- `X-XSS-Protection`: Protección XSS del navegador
- `Content-Security-Policy`: Política de seguridad de contenido
- `Strict-Transport-Security`: Forzar HTTPS (HSTS)

**Resultado Esperado**:
- ✅ Headers presentes y configurados
- ⚠️ Headers ausentes = RECOMENDACIÓN

---

## 📞 Comandos Útiles

### Ejecutar Todos los Tests

```powershell
# Windows PowerShell - Todas las pruebas
$tests = @("01_login_basico_test.jmx", "02_brute_force_test.jmx", "03_sql_injection_test.jmx", "04_xss_test.jmx", "05_load_test.jmx", "06_session_management_test.jmx", "07_access_control_test.jmx", "08_csrf_test.jmx", "09_file_upload_test.jmx", "10_security_headers_test.jmx")
$fecha = Get-Date -Format "yyyy-MM-dd_HHmm"

foreach ($test in $tests) {
    $nombre = $test -replace ".jmx", ""
    Write-Host "Ejecutando $test..."
    C:\jmeter\bin\jmeter.bat -n -t $test -l "..\results\${fecha}_${nombre}.jtl"
}
```

### Ejecutar Solo Pruebas Críticas

```powershell
# Solo pruebas de prioridad crítica
$tests = @("03_sql_injection_test.jmx", "07_access_control_test.jmx")
$fecha = Get-Date -Format "yyyy-MM-dd_HHmm"

foreach ($test in $tests) {
    $nombre = $test -replace ".jmx", ""
    Write-Host "Ejecutando $test..."
    C:\jmeter\bin\jmeter.bat -n -t $test -l "..\results\${fecha}_${nombre}.jtl"
}
```

### Generar Reporte HTML desde JTL

```powershell
C:\jmeter\bin\jmeter.bat -g ..\results\01_results.jtl -o ..\results\01_report
```

### Limpiar Resultados Antiguos

```powershell
Remove-Item ..\results\*.jtl
Remove-Item ..\results\*_report -Recurse
```

---

## 📚 Recursos Adicionales

- **Documentación**: Ver `Guia_JMeter_Pruebas_Seguridad.md`
- **Tutorial**: Ver `Tutorial_Primera_Prueba.md`
- **Casos de Prueba**: Ver `Casos_Prueba_Detallados.md`

---

**Última Actualización**: Noviembre 2025  
**Versión**: 1.0  
**Autor**: [Equipo de QA]
