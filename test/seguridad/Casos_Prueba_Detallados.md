# Casos de Prueba de Seguridad - Detallados
## Sistema de Gestión Docente

---

## Formato de Caso de Prueba

Cada caso de prueba contiene:
- **ID**: Identificador único
- **Nombre**: Título descriptivo
- **Categoría**: Tipo de vulnerabilidad (OWASP)
- **Prioridad**: Crítica, Alta, Media, Baja
- **Precondiciones**: Estado requerido antes de la prueba
- **Datos de Entrada**: Valores a utilizar
- **Pasos de Ejecución**: Procedimiento detallado
- **Resultado Esperado**: Comportamiento seguro esperado
- **Resultado Obtenido**: A completar durante ejecución
- **Estado**: Pasó / Falló / Bloqueado
- **Evidencia**: Referencia a capturas/logs

---

## PS-001: Validación de Credenciales Correctas

| Campo | Valor |
|-------|-------|
| **ID** | PS-001 |
| **Nombre** | Login con credenciales válidas |
| **Categoría** | Autenticación (OWASP A07) |
| **Prioridad** | Alta |
| **Herramienta** | Manual, JMeter |

### Precondiciones
- Sistema accesible en http://localhost/org/Sistema-de-Gestion-Docente
- Usuario de prueba existe en BD: `admin` / `Admin123!`
- Sesión no iniciada

### Datos de Entrada
```
Usuario: admin
Contraseña: Admin123!
```

### Pasos de Ejecución
1. Navegar a la página de login
2. Ingresar usuario válido en campo "nombreUsuario"
3. Ingresar contraseña válida en campo "contraseniaUsuario"
4. Resolver CAPTCHA
5. Hacer clic en botón "Acceder"

### Resultado Esperado
- ✅ Redirección a `?pagina=principal`
- ✅ Sesión creada con `$_SESSION['name']` establecida
- ✅ Cookie de sesión generada
- ✅ Mensaje de bienvenida visible

### Resultado Obtenido
_[A completar durante ejecución]_

### Estado
- [ ] Pasó
- [ ] Falló
- [ ] Bloqueado

### Evidencia
_[Captura de pantalla, log]_

---

## PS-002: Validación de Credenciales Incorrectas

| Campo | Valor |
|-------|-------|
| **ID** | PS-002 |
| **Nombre** | Login con credenciales inválidas |
| **Categoría** | Autenticación (OWASP A07) |
| **Prioridad** | Alta |
| **Herramienta** | Manual, JMeter |

### Precondiciones
- Sistema accesible
- Sesión no iniciada

### Datos de Entrada
```
Usuario: admin
Contraseña: ContraseñaIncorrecta123
```

### Pasos de Ejecución
1. Navegar a la página de login
2. Ingresar usuario válido
3. Ingresar contraseña incorrecta
4. Resolver CAPTCHA
5. Hacer clic en "Acceder"

### Resultado Esperado
- ✅ Permanece en página de login
- ✅ Mensaje de error genérico (no específico)
- ✅ No se crea sesión
- ✅ No se revela si usuario existe

### Resultado Obtenido
_[A completar]_

### Estado
- [ ] Pasó
- [ ] Falló
- [ ] Bloqueado

---

## PS-003: Protección contra Fuerza Bruta

| Campo | Valor |
|-------|-------|
| **ID** | PS-003 |
| **Nombre** | Múltiples intentos fallidos de login |
| **Categoría** | Autenticación (OWASP A07) |
| **Prioridad** | Crítica |
| **Herramienta** | JMeter |

### Precondiciones
- Sistema accesible
- Usuario de prueba: `testuser`

### Datos de Entrada
```
Usuario: testuser
Contraseñas: [lista de 50 contraseñas incorrectas]
Intentos: 50 en 1 minuto
```

### Pasos de Ejecución (JMeter)
1. Crear Thread Group con 50 threads
2. Configurar HTTP Request a login
3. Parametrizar contraseñas desde CSV
4. Ejecutar en loop de 1 iteración
5. Analizar respuestas

### Resultado Esperado
- ✅ Bloqueo temporal de cuenta después de 5 intentos
- ✅ CAPTCHA más difícil o requerido
- ✅ Delay incremental entre intentos
- ✅ Registro en bitácora de intentos fallidos

### Resultado Obtenido
_[A completar]_

### Estado
- [ ] Pasó
- [ ] Falló
- [ ] Bloqueado

---

## PS-010: Inyección SQL en Login - Usuario

| Campo | Valor |
|-------|-------|
| **ID** | PS-010 |
| **Nombre** | SQL Injection en campo usuario |
| **Categoría** | Inyección (OWASP A03) |
| **Prioridad** | Crítica |
| **Herramienta** | Manual, SQLMap |

### Precondiciones
- Sistema accesible
- Formulario de login disponible

### Datos de Entrada (Payloads)
```sql
' OR '1'='1
' OR '1'='1' --
' OR '1'='1' /*
admin' --
admin' #
' UNION SELECT NULL, NULL, NULL --
1' AND 1=1 --
1' AND 1=2 --
```

### Pasos de Ejecución
1. Navegar a login
2. Ingresar payload en campo "nombreUsuario"
3. Ingresar cualquier valor en contraseña
4. Intentar login
5. Observar respuesta

### Resultado Esperado
- ✅ Login rechazado
- ✅ Payload sanitizado/escapado
- ✅ No se ejecuta código SQL malicioso
- ✅ Mensaje de error genérico (sin detalles SQL)

### Resultado Obtenido
_[A completar]_

### Estado
- [ ] Pasó
- [ ] Falló
- [ ] Bloqueado

### Evidencia
_[Captura de pantalla, response]_

---

## PS-013: XSS Reflejado en Búsqueda

| Campo | Valor |
|-------|-------|
| **ID** | PS-013 |
| **Nombre** | Cross-Site Scripting en búsqueda |
| **Categoría** | XSS (OWASP A03) |
| **Prioridad** | Alta |
| **Herramienta** | Manual, OWASP ZAP |

### Precondiciones
- Usuario autenticado
- Módulo con funcionalidad de búsqueda accesible

### Datos de Entrada (Payloads)
```html
<script>alert('XSS')</script>
<img src=x onerror=alert('XSS')>
<svg onload=alert('XSS')>
"><script>alert(String.fromCharCode(88,83,83))</script>
```

### Pasos de Ejecución
1. Iniciar sesión
2. Navegar a módulo con búsqueda
3. Ingresar payload en campo de búsqueda
4. Ejecutar búsqueda
5. Observar si script se ejecuta

### Resultado Esperado
- ✅ Script no se ejecuta
- ✅ Payload mostrado como texto plano
- ✅ Caracteres especiales escapados (< > " ')

### Resultado Obtenido
_[A completar]_

---

## PS-006: Control de Acceso Basado en Roles

| Campo | Valor |
|-------|-------|
| **ID** | PS-006 |
| **Nombre** | Acceso a módulo sin permisos |
| **Categoría** | Control de Acceso (OWASP A01) |
| **Prioridad** | Crítica |
| **Herramienta** | Manual, Postman |

### Precondiciones
- Usuario con rol "Docente" (sin permisos de admin)
- Módulo "Gestión de Usuarios" requiere rol Admin

### Datos de Entrada
```
Usuario: docente_test
Contraseña: Docente123!
URL: ?pagina=usuario
```

### Pasos de Ejecución
1. Login como usuario "Docente"
2. Intentar acceder directamente a `?pagina=usuario`
3. Observar respuesta
4. Verificar en código que se validan permisos

### Resultado Esperado
- ✅ Acceso denegado
- ✅ Redirección a página principal o error 403
- ✅ Mensaje: "No tiene permisos para acceder"
- ✅ Registro en bitácora del intento

### Resultado Obtenido
_[A completar]_

---

## PS-016: Protección CSRF en Formularios

| Campo | Valor |
|-------|-------|
| **ID** | PS-016 |
| **Nombre** | Validación de token CSRF |
| **Categoría** | CSRF (OWASP A01) |
| **Prioridad** | Alta |
| **Herramienta** | Burp Suite, Manual |

### Precondiciones
- Usuario autenticado
- Formulario de registro/edición disponible

### Pasos de Ejecución
1. Login como usuario válido
2. Abrir formulario de "Registrar Eje"
3. Interceptar request con Burp Suite
4. Eliminar o modificar token CSRF (si existe)
5. Enviar request modificado
6. Observar respuesta

### Resultado Esperado
- ✅ Request rechazado
- ✅ Mensaje: "Token CSRF inválido"
- ✅ Operación no se ejecuta
- ✅ Sesión no comprometida

### Resultado Obtenido
_[A completar]_

---

## PS-019: Listado de Directorios

| Campo | Valor |
|-------|-------|
| **ID** | PS-019 |
| **Nombre** | Acceso directo a directorios protegidos |
| **Categoría** | Configuración (OWASP A05) |
| **Prioridad** | Alta |
| **Herramienta** | Browser |

### Precondiciones
- Sistema accesible
- Sin autenticación

### Datos de Entrada (URLs)
```
http://localhost/org/Sistema-de-Gestion-Docente/model/
http://localhost/org/Sistema-de-Gestion-Docente/controller/
http://localhost/org/Sistema-de-Gestion-Docente/config/
http://localhost/org/Sistema-de-Gestion-Docente/db/
http://localhost/org/Sistema-de-Gestion-Docente/vendor/
```

### Pasos de Ejecución
1. Abrir navegador
2. Acceder a cada URL listada
3. Observar respuesta

### Resultado Esperado
- ✅ Error 403 Forbidden
- ✅ No se muestra listado de archivos
- ✅ Redirección a página de error
- ✅ Mensaje: "Acceso Denegado"

### Resultado Obtenido
_[A completar]_

---

## PS-022: Validación de Tipo de Archivo

| Campo | Valor |
|-------|-------|
| **ID** | PS-022 |
| **Nombre** | Carga de archivo malicioso |
| **Categoría** | Carga de Archivos (OWASP A04) |
| **Prioridad** | Crítica |
| **Herramienta** | Manual |

### Precondiciones
- Usuario autenticado con permisos de carga
- Módulo de carga de archivos accesible

### Datos de Entrada
```
Archivos a probar:
- shell.php (script PHP malicioso)
- malware.exe
- script.sh
- archivo.php.jpg (doble extensión)
```

### Pasos de Ejecución
1. Login como usuario válido
2. Navegar a módulo de carga
3. Seleccionar archivo malicioso
4. Intentar subir
5. Verificar si se acepta

### Resultado Esperado
- ✅ Archivo rechazado
- ✅ Mensaje: "Tipo de archivo no permitido"
- ✅ Solo se aceptan: PDF, DOC, DOCX, JPG, PNG
- ✅ Validación en servidor (no solo cliente)

### Resultado Obtenido
_[A completar]_

---

## PS-025: Enumeración de Usuarios en Recuperación

| Campo | Valor |
|-------|-------|
| **ID** | PS-025 |
| **Nombre** | Revelación de usuarios existentes |
| **Categoría** | Autenticación (OWASP A07) |
| **Prioridad** | Media |
| **Herramienta** | Manual, Postman |

### Precondiciones
- Función de recuperación de contraseña disponible

### Datos de Entrada
```
Usuario existente: admin
Usuario no existente: usuarioInexistente123
```

### Pasos de Ejecución
1. Ir a "¿Olvidó su contraseña?"
2. Ingresar usuario existente
3. Observar mensaje de respuesta
4. Repetir con usuario inexistente
5. Comparar mensajes

### Resultado Esperado
- ✅ Mismo mensaje para ambos casos
- ✅ Ejemplo: "Si el usuario existe, recibirá un correo"
- ✅ No se revela si usuario existe o no

### Resultado Obtenido
_[A completar]_

---

## Plantilla de Caso de Prueba en Blanco

```markdown
## PS-XXX: [Nombre de la Prueba]

| Campo | Valor |
|-------|-------|
| **ID** | PS-XXX |
| **Nombre** | [Título] |
| **Categoría** | [OWASP Category] |
| **Prioridad** | [Crítica/Alta/Media/Baja] |
| **Herramienta** | [Tool] |

### Precondiciones
[Estado requerido]

### Datos de Entrada
[Valores a usar]

### Pasos de Ejecución
1. [Paso 1]
2. [Paso 2]

### Resultado Esperado
- ✅ [Comportamiento esperado]

### Resultado Obtenido
_[A completar]_

### Estado
- [ ] Pasó
- [ ] Falló
- [ ] Bloqueado

### Evidencia
_[Referencias]_
```

---

**Total de Casos de Prueba**: 29  
**Última Actualización**: Noviembre 2025
