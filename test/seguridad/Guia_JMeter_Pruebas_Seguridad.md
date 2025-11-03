# Guía de JMeter para Pruebas de Seguridad
## Sistema de Gestión Docente

---

## 1. Instalación y Configuración

### 1.1 Requisitos Previos
- Java JDK 8 o superior
- Apache JMeter 5.6+
- Sistema de Gestión Docente corriendo en XAMPP

### 1.2 Instalación de JMeter

**Windows:**
```powershell
# Descargar desde https://jmeter.apache.org/download_jmeter.cgi
# Extraer en C:\jmeter
# Agregar al PATH (opcional)
```

**Verificar instalación:**
```powershell
cd C:\jmeter\bin
.\jmeter.bat
```

### 1.3 Plugins Recomendados
- **Custom Thread Groups**: Para patrones de carga avanzados
- **3 Basic Graphs**: Visualización de resultados
- **PerfMon**: Monitoreo de servidor

**Instalar via Plugins Manager:**
1. Descargar `plugins-manager.jar`
2. Colocar en `lib/ext/`
3. Reiniciar JMeter
4. Options > Plugins Manager

---

## 2. Estructura de un Plan de Pruebas

```
Test Plan
├── Thread Group (Usuarios)
│   ├── HTTP Request Defaults
│   ├── HTTP Cookie Manager
│   ├── HTTP Header Manager
│   ├── CSV Data Set Config (datos de prueba)
│   ├── Samplers (Requests)
│   │   ├── HTTP Request - Login
│   │   ├── HTTP Request - Operación
│   │   └── HTTP Request - Logout
│   ├── Assertions (Validaciones)
│   └── Listeners (Resultados)
└── Configuración Global
```

---

## 3. Prueba 1: Validación de Login

### 3.1 Configuración del Thread Group

```
Thread Group
├── Number of Threads: 1
├── Ramp-up Period: 1
└── Loop Count: 1
```

### 3.2 HTTP Request Defaults

```
Server Name: localhost
Port: 80
Protocol: http
Path: /org/Sistema-de-Gestion-Docente/
```

### 3.3 HTTP Cookie Manager

```
Add:
Name: Cookie Manager
Clear cookies each iteration: ✓
```

### 3.4 HTTP Request - Login

```
Name: Login Request
Method: POST
Path: ?pagina=login
Parameters:
  - accion: acceder
  - nombreUsuario: admin
  - contraseniaUsuario: Admin123!
  - g-recaptcha-response: [dejar vacío para pruebas]
```

### 3.5 Response Assertion

```
Name: Verificar Login Exitoso
Apply to: Main sample only
Response Field: Text Response
Pattern Matching Rules: Contains
Patterns to Test: principal
```

### 3.6 View Results Tree

```
Add Listener: View Results Tree
Para ver requests/responses detallados
```

---

## 4. Prueba 2: Ataque de Fuerza Bruta

### 4.1 Preparar Archivo CSV

**Crear archivo**: `passwords.csv`
```csv
password
Admin123!
admin123
password123
12345678
qwerty
letmein
admin
root
```

### 4.2 CSV Data Set Config

```
Filename: C:\jmeter\data\passwords.csv
Variable Names: password
Delimiter: ,
Recycle on EOF: False
Stop thread on EOF: True
Sharing mode: All threads
```

### 4.3 Thread Group

```
Number of Threads: 1
Ramp-up Period: 1
Loop Count: Forever
```

### 4.4 HTTP Request - Brute Force Login

```
Method: POST
Path: ?pagina=login
Parameters:
  - accion: acceder
  - nombreUsuario: admin
  - contraseniaUsuario: ${password}
```

### 4.5 Response Assertion - Detectar Éxito

```
Pattern: principal
If match: Stop thread
```

### 4.6 Constant Timer

```
Thread Delay: 1000 ms
(Para no saturar el servidor)
```

---

## 5. Prueba 3: Inyección SQL

### 5.1 Archivo CSV con Payloads SQL

**Crear**: `sql_payloads.csv`
```csv
payload
' OR '1'='1
' OR '1'='1' --
' OR '1'='1' /*
admin' --
admin' #
' UNION SELECT NULL--
1' AND 1=1 --
1' AND 1=2 --
' OR 'x'='x
') OR ('1'='1
```

### 5.2 CSV Data Set Config

```
Filename: sql_payloads.csv
Variable Names: payload
```

### 5.3 HTTP Request - SQL Injection Test

```
Method: POST
Path: ?pagina=login
Parameters:
  - accion: acceder
  - nombreUsuario: ${payload}
  - contraseniaUsuario: test
```

### 5.4 Response Assertion - Detectar Vulnerabilidad

```
Patterns to Test (NOT):
  - SQL syntax
  - mysql_
  - error in your SQL
  - Warning: mysql
```

Si encuentra estos patrones = VULNERABLE

---

## 6. Prueba 4: XSS (Cross-Site Scripting)

### 6.1 Payloads XSS

**Crear**: `xss_payloads.csv`
```csv
payload
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
"><script>alert(String.fromCharCode(88,83,83))</script>
<iframe src="javascript:alert('XSS')">
```

### 6.2 HTTP Request - Test XSS en Búsqueda

```
Method: GET
Path: ?pagina=eje
Parameters:
  - buscar: ${payload}
```

### 6.3 Response Assertion - Detectar XSS

```
Pattern (NOT): <script>alert
Si encuentra el script sin escapar = VULNERABLE
```

---

## 7. Prueba 5: CSRF (Cross-Site Request Forgery)

### 7.1 Secuencia de Requests

**Request 1: Login**
```
POST ?pagina=login
Parameters:
  - accion: acceder
  - nombreUsuario: admin
  - contraseniaUsuario: Admin123!
```

**Request 2: Extraer Token (si existe)**
```
Regular Expression Extractor:
  Reference Name: csrf_token
  Regular Expression: name="csrf_token" value="(.+?)"
  Template: $1$
  Match No: 1
```

**Request 3: Operación sin Token**
```
POST controller/eje.php
Parameters:
  - accion: registrar
  - ejeNombre: Test CSRF
  - ejeDescripcion: Prueba
  - csrf_token: [OMITIR]
```

### 7.2 Validación

```
Response Assertion:
Pattern: "CSRF token inválido" o similar
Si NO encuentra el mensaje = VULNERABLE
```

---

## 8. Prueba 6: Control de Acceso

### 8.1 Archivo CSV con Usuarios

**Crear**: `usuarios_roles.csv`
```csv
usuario,password,rol
admin,Admin123!,administrador
coordinador,Coord123!,coordinador
docente,Docente123!,docente
```

### 8.2 Login como Docente

```
POST ?pagina=login
Parameters:
  - nombreUsuario: docente
  - contraseniaUsuario: Docente123!
```

### 8.3 Intentar Acceso Administrativo

```
GET ?pagina=usuario
```

### 8.4 Validación

```
Response Assertion:
Pattern (NOT): Gestión de Usuarios
Pattern: No tiene permisos | 403 | Acceso Denegado
```

---

## 9. Prueba 7: Carga y Rendimiento

### 9.1 Thread Group - Carga Progresiva

```
Ultimate Thread Group (plugin requerido):
  Start Threads Count: 10
  Initial Delay: 0
  Startup Time: 30 sec
  Hold Load For: 120 sec
  Shutdown Time: 10 sec
```

### 9.2 Escenario de Carga

```
1. Login (10% requests)
2. Consultar Eje (30% requests)
3. Consultar Docentes (30% requests)
4. Generar Reporte (20% requests)
5. Logout (10% requests)
```

### 9.3 Throughput Controller

```
Throughput Controller - Login:
  Percent Executions: 10%
  
Throughput Controller - Consultas:
  Percent Executions: 60%
```

### 9.4 Listeners para Análisis

```
- Aggregate Report
- Response Time Graph
- Transactions per Second
- Active Threads Over Time
```

---

## 10. Prueba 8: Rate Limiting

### 10.1 Thread Group

```
Number of Threads: 1
Ramp-up: 0
Loop Count: 100
```

### 10.2 HTTP Request - Múltiples Peticiones

```
GET ?pagina=principal
```

### 10.3 Constant Timer

```
Thread Delay: 10 ms
(100 requests por segundo)
```

### 10.4 Validación

```
Response Assertion:
Pattern: 429 | Too Many Requests | Rate limit
Si NO encuentra = Sin protección
```

---

## 11. Configuración Avanzada

### 11.1 Extraer Datos de Respuesta

**Regular Expression Extractor:**
```
Reference Name: session_id
Regular Expression: PHPSESSID=([^;]+)
Template: $1$
Match No: 1
Default Value: ERROR
```

**JSON Extractor:**
```
Names of created variables: resultado
JSON Path: $.resultado
Default Value: ERROR
```

### 11.2 Validaciones Múltiples

**Duration Assertion:**
```
Duration in milliseconds: 3000
(Falla si respuesta > 3 segundos)
```

**Size Assertion:**
```
Size in bytes: 1000
Comparison: >
(Verifica que respuesta tenga contenido)
```

### 11.3 Manejo de Errores

**If Controller:**
```
Condition: ${JMeterThread.last_sample_ok}
Execute if: false
  └── Log Error
  └── Stop Thread
```

---

## 12. Ejecución y Análisis

### 12.1 Ejecutar desde GUI

```
1. Abrir JMeter
2. File > Open > [tu_plan.jmx]
3. Click en "Start" (▶)
4. Ver resultados en Listeners
```

### 12.2 Ejecutar desde Línea de Comandos

```powershell
cd C:\jmeter\bin

# Modo no-GUI (recomendado para pruebas largas)
.\jmeter.bat -n -t ..\tests\login_test.jmx -l ..\results\login_results.jtl

# Con reporte HTML
.\jmeter.bat -n -t ..\tests\login_test.jmx -l ..\results\login_results.jtl -e -o ..\reports\login_report
```

### 12.3 Parámetros Útiles

```
-n : Modo no-GUI
-t : Archivo de test plan
-l : Archivo de log de resultados
-e : Generar reporte HTML
-o : Carpeta de salida del reporte
-J : Definir propiedades
```

---

## 13. Interpretación de Resultados

### 13.1 Aggregate Report

| Métrica | Descripción | Valor Aceptable |
|---------|-------------|-----------------|
| **Samples** | Número de requests | - |
| **Average** | Tiempo promedio (ms) | < 1000 ms |
| **Median** | Tiempo mediano | < 800 ms |
| **90% Line** | 90th percentile | < 2000 ms |
| **Min** | Tiempo mínimo | - |
| **Max** | Tiempo máximo | < 5000 ms |
| **Error %** | Porcentaje de errores | < 1% |
| **Throughput** | Requests/segundo | > 10 req/s |

### 13.2 Identificar Vulnerabilidades

**SQL Injection:**
```
✅ SEGURO: Error % = 0%, sin mensajes SQL en respuestas
❌ VULNERABLE: Mensajes de error SQL, login exitoso con payload
```

**XSS:**
```
✅ SEGURO: Payloads escapados en HTML
❌ VULNERABLE: Scripts ejecutados, payloads sin escapar
```

**Fuerza Bruta:**
```
✅ SEGURO: Bloqueo después de N intentos, CAPTCHA requerido
❌ VULNERABLE: Intentos ilimitados, sin delay
```

---

## 14. Mejores Prácticas

### 14.1 Organización de Archivos

```
jmeter/
├── bin/
├── tests/
│   ├── security/
│   │   ├── sql_injection.jmx
│   │   ├── xss_test.jmx
│   │   ├── brute_force.jmx
│   │   └── csrf_test.jmx
│   └── performance/
│       └── load_test.jmx
├── data/
│   ├── passwords.csv
│   ├── sql_payloads.csv
│   └── xss_payloads.csv
└── results/
    ├── logs/
    └── reports/
```

### 14.2 Nomenclatura

```
Archivos JMX: [modulo]_[tipo]_test.jmx
Ejemplo: login_security_test.jmx

Resultados: [fecha]_[test]_results.jtl
Ejemplo: 2025-11-02_login_security_results.jtl
```

### 14.3 Documentación

```
Cada test plan debe incluir:
- Test Plan > Comments: Descripción del objetivo
- Thread Group > Comments: Escenario de prueba
- Samplers > Comments: Qué se está probando
```

---

## 15. Troubleshooting

### 15.1 Problemas Comunes

**Error: Connection refused**
```
Solución: Verificar que XAMPP esté corriendo
```

**Error: Out of memory**
```
Solución: Editar jmeter.bat
set HEAP=-Xms1g -Xmx4g
```

**Cookies no se mantienen**
```
Solución: Agregar HTTP Cookie Manager
```

**CAPTCHA bloquea pruebas**
```
Solución: 
1. Desactivar CAPTCHA en ambiente de pruebas
2. Usar token de prueba de Google reCAPTCHA
```

---

## 16. Checklist de Pruebas

### Antes de Ejecutar
- [ ] XAMPP corriendo
- [ ] Base de datos con datos de prueba
- [ ] Usuarios de prueba creados
- [ ] Archivos CSV preparados
- [ ] JMeter configurado

### Durante Ejecución
- [ ] Monitorear uso de recursos
- [ ] Verificar logs de Apache/PHP
- [ ] Revisar resultados en tiempo real
- [ ] Documentar hallazgos

### Después de Ejecutar
- [ ] Generar reportes HTML
- [ ] Analizar métricas
- [ ] Documentar vulnerabilidades
- [ ] Limpiar datos de prueba
- [ ] Respaldar resultados

---

## 17. Recursos Adicionales

### 17.1 Documentación Oficial
- https://jmeter.apache.org/usermanual/
- https://jmeter.apache.org/usermanual/component_reference.html

### 17.2 Tutoriales Recomendados
- JMeter Academy
- BlazeMeter University
- OWASP Testing Guide

### 17.3 Comunidad
- JMeter Users Mailing List
- Stack Overflow [jmeter]
- Reddit r/jmeter

---

**Versión**: 1.0  
**Última Actualización**: Noviembre 2025  
**Autor**: [Nombre]
