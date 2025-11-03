# Tutorial: Tu Primera Prueba de Seguridad con JMeter
## Sistema de Gestión Docente

---

## 🎯 Objetivo

Ejecutar tu primera prueba de seguridad automatizada usando JMeter para validar el sistema de login contra ataques de fuerza bruta.

**Tiempo estimado**: 30 minutos  
**Nivel**: Principiante  
**Requisitos**: JMeter instalado, XAMPP corriendo

---

## 📋 Paso 1: Verificar Requisitos

### 1.1 Verificar XAMPP

```powershell
# Abrir navegador y verificar:
http://localhost/org/Sistema-de-Gestion-Docente
```

✅ Debe mostrar la página de login

### 1.2 Verificar JMeter

```powershell
# Abrir PowerShell
cd C:\jmeter\bin
.\jmeter.bat
```

✅ Debe abrir la interfaz gráfica de JMeter

---

## 🔧 Paso 2: Crear el Test Plan

### 2.1 Crear Nuevo Test Plan

1. En JMeter, click derecho en "Test Plan"
2. Cambiar nombre a: **"Prueba de Seguridad - Login"**
3. En "Comments" escribir:
   ```
   Prueba de validación de login y protección contra fuerza bruta
   Sistema de Gestión Docente
   Fecha: [HOY]
   ```

---

## 👥 Paso 3: Configurar Thread Group

### 3.1 Agregar Thread Group

1. Click derecho en "Test Plan"
2. Add > Threads (Users) > Thread Group
3. Configurar:
   - **Name**: `Usuarios de Prueba`
   - **Number of Threads**: `1`
   - **Ramp-up Period**: `1`
   - **Loop Count**: `1`

**¿Qué significa esto?**
- 1 usuario virtual
- Se inicia en 1 segundo
- Ejecuta 1 vez

---

## 🌐 Paso 4: Configurar HTTP Defaults

### 4.1 Agregar HTTP Request Defaults

1. Click derecho en "Thread Group"
2. Add > Config Element > HTTP Request Defaults
3. Configurar:
   - **Server Name**: `localhost`
   - **Port Number**: `80`
   - **Protocol**: `http`
   - **Path**: `/org/Sistema-de-Gestion-Docente/`

**¿Para qué?** Para no repetir estos valores en cada request

---

## 🍪 Paso 5: Agregar Cookie Manager

### 5.1 Configurar Cookies

1. Click derecho en "Thread Group"
2. Add > Config Element > HTTP Cookie Manager
3. Marcar: ✅ **Clear cookies each iteration**

**¿Para qué?** Para manejar la sesión PHP automáticamente

---

## 📨 Paso 6: Crear Request de Login

### 6.1 Agregar HTTP Request

1. Click derecho en "Thread Group"
2. Add > Sampler > HTTP Request
3. Configurar:
   - **Name**: `Login - Credenciales Válidas`
   - **Method**: `POST`
   - **Path**: `?pagina=login`

### 6.2 Agregar Parámetros

En la sección "Parameters", click "Add" y agregar:

| Name | Value |
|------|-------|
| accion | acceder |
| nombreUsuario | admin |
| contraseniaUsuario | Admin123! |

**Nota**: Dejar `g-recaptcha-response` vacío por ahora

---

## ✅ Paso 7: Agregar Validación

### 7.1 Agregar Response Assertion

1. Click derecho en "Login - Credenciales Válidas"
2. Add > Assertions > Response Assertion
3. Configurar:
   - **Name**: `Verificar Login Exitoso`
   - **Apply to**: Main sample only
   - **Response Field**: Text Response
   - **Pattern Matching Rules**: ✅ Contains
   - **Patterns to Test**: Click "Add" y escribir: `principal`

**¿Qué valida?** Que la respuesta contenga "principal" (redirección exitosa)

---

## 📊 Paso 8: Agregar Listeners

### 8.1 View Results Tree

1. Click derecho en "Thread Group"
2. Add > Listener > View Results Tree

**¿Para qué?** Ver cada request/response en detalle

### 8.2 Summary Report

1. Click derecho en "Thread Group"
2. Add > Listener > Summary Report

**¿Para qué?** Ver estadísticas resumidas

---

## 💾 Paso 9: Guardar el Test Plan

1. File > Save Test Plan as...
2. Guardar en: `C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests\`
3. Nombre: `login_basico_test.jmx`

---

## ▶️ Paso 10: Ejecutar la Prueba

### 10.1 Primera Ejecución

1. Click en el botón verde "Start" (▶️) en la barra superior
2. Observar "View Results Tree"
3. Click en "Login - Credenciales Válidas"
4. Ver:
   - **Request**: Los datos enviados
   - **Response data**: La respuesta del servidor

### 10.2 Verificar Resultado

En "View Results Tree":
- ✅ **Verde** = Prueba pasó
- ❌ **Rojo** = Prueba falló

En "Summary Report":
- **# Samples**: 1
- **Average**: Tiempo de respuesta en ms
- **Error %**: Debe ser 0%

---

## 🔄 Paso 11: Probar Credenciales Incorrectas

### 11.1 Duplicar Request

1. Click derecho en "Login - Credenciales Válidas"
2. Copy
3. Click derecho en "Thread Group"
4. Paste

### 11.2 Modificar Nuevo Request

1. Cambiar nombre a: `Login - Credenciales Inválidas`
2. En Parameters, cambiar:
   - **contraseniaUsuario**: `ContraseñaIncorrecta123`

### 11.3 Modificar Assertion

1. Click en la Assertion dentro de este request
2. Cambiar:
   - **Pattern Matching Rules**: ✅ NOT Contains
   - **Patterns to Test**: `principal`

**¿Qué valida?** Que NO se redirija a principal (login debe fallar)

### 11.4 Ejecutar de Nuevo

1. Click "Start" (▶️)
2. Ahora deberías ver 2 requests en "View Results Tree"
3. Ambos deben estar en verde ✅

---

## 🔨 Paso 12: Prueba de Fuerza Bruta

### 12.1 Modificar Thread Group

1. Click en "Thread Group"
2. Cambiar:
   - **Number of Threads**: `10`
   - **Ramp-up Period**: `5`
   - **Loop Count**: `3`

**¿Qué hace?** 10 usuarios, iniciando en 5 segundos, 3 intentos cada uno = 30 requests totales

### 12.2 Deshabilitar Request de Credenciales Válidas

1. Click derecho en "Login - Credenciales Válidas"
2. Disable

### 12.3 Ejecutar Prueba de Carga

1. Click "Start" (▶️)
2. Observar "Summary Report"
3. Verificar:
   - **# Samples**: 30
   - **Throughput**: requests por segundo
   - **Error %**: Debería ser 100% (todos fallan, es correcto)

---

## 📈 Paso 13: Interpretar Resultados

### 13.1 Métricas Importantes

En "Summary Report":

| Métrica | Valor Esperado | Interpretación |
|---------|----------------|----------------|
| **Average** | < 1000 ms | Tiempo promedio de respuesta |
| **Min** | ~100-300 ms | Respuesta más rápida |
| **Max** | < 3000 ms | Respuesta más lenta |
| **Error %** | 100% | Todos los logins fallaron (correcto) |
| **Throughput** | 2-10 req/s | Capacidad del servidor |

### 13.2 Verificar Protección

**✅ Sistema SEGURO si:**
- Después de 5 intentos fallidos, se bloquea la cuenta
- Aparece mensaje de CAPTCHA requerido
- Hay delay incremental entre intentos

**❌ Sistema VULNERABLE si:**
- Permite intentos ilimitados sin bloqueo
- No hay CAPTCHA después de múltiples fallos
- No hay registro en bitácora

---

## 🎓 Paso 14: Prueba Avanzada - CSV Data

### 14.1 Agregar CSV Data Set Config

1. Click derecho en "Thread Group"
2. Add > Config Element > CSV Data Set Config
3. Configurar:
   - **Filename**: `C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\data\passwords.csv`
   - **Variable Names**: `password`
   - **Delimiter**: `,`
   - **Recycle on EOF**: `False`
   - **Stop thread on EOF**: `True`

### 14.2 Modificar Request

1. Click en "Login - Credenciales Inválidas"
2. En Parameters, cambiar:
   - **contraseniaUsuario**: `${password}`

### 14.3 Modificar Thread Group

1. **Number of Threads**: `1`
2. **Loop Count**: `Forever`

### 14.4 Ejecutar

1. Click "Start" (▶️)
2. JMeter probará cada contraseña del CSV
3. Se detendrá al terminar el archivo

---

## 📝 Paso 15: Documentar Resultados

### 15.1 Capturar Evidencia

1. En "View Results Tree", click en un request
2. Click en "Response data" tab
3. Tomar screenshot

### 15.2 Exportar Resultados

1. En "Summary Report", click derecho
2. Save Table Data
3. Guardar como: `resultados_login_[FECHA].csv`

### 15.3 Completar Plantilla

Abrir: `Plantilla_Reporte_Ejecucion.md`

Completar sección de Login:

```markdown
| PS-001 | Validación de Credenciales | ✅ Pasó | - | Login funciona correctamente |
| PS-002 | Credenciales Incorrectas | ✅ Pasó | - | Rechaza credenciales inválidas |
| PS-003 | Protección Fuerza Bruta | ❌ Falló | Crítica | Sin límite de intentos |
```

---

## 🚀 Próximos Pasos

### Has completado tu primera prueba! 🎉

**Ahora puedes:**

1. ✅ Crear pruebas para otros módulos
2. ✅ Probar inyección SQL (usar `sql_payloads.csv`)
3. ✅ Probar XSS (usar `xss_payloads.csv`)
4. ✅ Ejecutar pruebas de carga más intensivas
5. ✅ Integrar con OWASP ZAP

### Recursos Adicionales

- 📖 `Guia_JMeter_Pruebas_Seguridad.md` - Guía completa
- 📋 `Casos_Prueba_Detallados.md` - Todos los casos de prueba
- 🎯 `Plan_Pruebas_Seguridad.md` - Plan maestro

---

## ❓ Troubleshooting

### Problema: "Connection refused"

**Solución:**
```powershell
# Verificar que Apache esté corriendo en XAMPP
# Verificar URL: http://localhost/org/Sistema-de-Gestion-Docente
```

### Problema: "Assertion failed"

**Solución:**
- Verificar que el patrón de búsqueda sea correcto
- Revisar "Response data" para ver qué devuelve el servidor
- Verificar que las credenciales sean correctas

### Problema: CAPTCHA bloquea pruebas

**Solución temporal:**
```php
// En controller/login.php (SOLO PARA PRUEBAS)
// Comentar temporalmente la validación de CAPTCHA
// if (!$o->validarCaptcha($captcha)) {
//     $mensaje = "Captcha inválido. Intente de nuevo.";
// } else {
    // ... resto del código
// }
```

**⚠️ IMPORTANTE**: Restaurar después de las pruebas

---

## 📊 Checklist de Completitud

- [x] JMeter instalado y funcionando
- [x] Test Plan creado
- [x] Thread Group configurado
- [x] HTTP Defaults configurado
- [x] Cookie Manager agregado
- [x] Request de login creado
- [x] Assertions agregadas
- [x] Listeners configurados
- [x] Prueba ejecutada exitosamente
- [x] Resultados interpretados
- [x] Evidencia capturada
- [x] Resultados documentados

---

## 🎯 Resumen de lo Aprendido

1. ✅ Crear un Test Plan en JMeter
2. ✅ Configurar Thread Groups para simular usuarios
3. ✅ Crear HTTP Requests con parámetros POST
4. ✅ Usar Assertions para validar respuestas
5. ✅ Interpretar resultados con Listeners
6. ✅ Usar CSV para datos dinámicos
7. ✅ Documentar hallazgos de seguridad

---

**¡Felicitaciones! Has completado tu primera prueba de seguridad automatizada. 🔒**

**Siguiente paso recomendado**: Ejecutar prueba de SQL Injection siguiendo la `Guia_JMeter_Pruebas_Seguridad.md`

---

**Creado**: Noviembre 2025  
**Dificultad**: ⭐ Principiante  
**Tiempo**: 30 minutos
