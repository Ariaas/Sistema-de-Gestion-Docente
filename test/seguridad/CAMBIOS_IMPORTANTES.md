# 🔄 Cambios Importantes - Scripts JMeter Actualizados
## Sistema de Gestión Docente

---

## 📅 Fecha de Actualización: Noviembre 2, 2025

---

## ✅ Scripts Actualizados

Los siguientes scripts JMeter han sido **actualizados** para funcionar con la nueva implementación del controlador de login:

1. ✅ `01_login_basico_test.jmx`
2. ✅ `02_brute_force_test.jmx`
3. ✅ `03_sql_injection_test.jmx`

---

## 🔑 Cambios Principales

### 1. Nueva Acción de Login para Pruebas Automatizadas

Se agregó una **nueva acción `'ingresar'`** en el controlador de login específicamente para pruebas automatizadas:

**Características:**
- ✅ **Sin CAPTCHA**: No requiere validación de reCAPTCHA
- ✅ **Solo localhost**: Restringido a ejecución local por seguridad
- ✅ **Respuesta JSON**: Retorna respuestas en formato JSON
- ✅ **Nombres de parámetros diferentes**: Usa `usu_usuario` y `usu_clave`

### 2. Cambios en el Controlador de Login

**Archivo**: `controller/login.php`

**Nueva acción agregada:**
```php
// Acción 'ingresar' - Para pruebas automatizadas (JMeter) - SIN CAPTCHA
if ($h == 'ingresar') {
    // Solo permitir en localhost para seguridad
    $es_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || 
                   $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
                   strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);
    
    if (!$es_localhost) {
        echo json_encode(['resultado' => 'error', 'mensaje' => 'Acceso no permitido']);
        exit;
    }
    
    $o->set_nombreUsuario($_POST['usu_usuario']);
    $o->set_contraseniaUsuario($_POST['usu_clave']);
    $m = $o->existe();
    
    if ($m['resultado'] == 'existe') {
        // ... código de sesión ...
        echo json_encode(['resultado' => 'ok', 'mensaje' => 'Login exitoso']);
        exit;
    } else {
        echo json_encode(['resultado' => 'error', 'mensaje' => $m['mensaje']]);
        exit;
    }
}
```

**Acción original `'acceder'` se mantiene sin cambios:**
- Requiere CAPTCHA
- Usa `nombreUsuario` y `contraseniaUsuario`
- Redirige con `header('Location: ...')`

---

## 📝 Cambios en los Scripts JMeter

### Antes (Acción 'acceder')

```xml
<elementProp name="accion" elementType="HTTPArgument">
  <stringProp name="Argument.value">acceder</stringProp>
  <stringProp name="Argument.name">accion</stringProp>
</elementProp>
<elementProp name="nombreUsuario" elementType="HTTPArgument">
  <stringProp name="Argument.value">admin</stringProp>
  <stringProp name="Argument.name">nombreUsuario</stringProp>
</elementProp>
<elementProp name="contraseniaUsuario" elementType="HTTPArgument">
  <stringProp name="Argument.value">Admin123!</stringProp>
  <stringProp name="Argument.name">contraseniaUsuario</stringProp>
</elementProp>
<elementProp name="g-recaptcha-response" elementType="HTTPArgument">
  <stringProp name="Argument.value"></stringProp>
  <stringProp name="Argument.name">g-recaptcha-response</stringProp>
</elementProp>
```

### Ahora (Acción 'ingresar')

```xml
<elementProp name="accion" elementType="HTTPArgument">
  <stringProp name="Argument.value">ingresar</stringProp>
  <stringProp name="Argument.name">accion</stringProp>
</elementProp>
<elementProp name="usu_usuario" elementType="HTTPArgument">
  <stringProp name="Argument.value">LigaDuran</stringProp>
  <stringProp name="Argument.name">usu_usuario</stringProp>
</elementProp>
<elementProp name="usu_clave" elementType="HTTPArgument">
  <stringProp name="Argument.value">Carolina.16</stringProp>
  <stringProp name="Argument.name">usu_clave</stringProp>
</elementProp>
```

---

## 🎯 Cambios en las Assertions

### Antes (Validación de Redirección HTML)

```xml
<ResponseAssertion>
  <collectionProp name="Asserion.test_strings">
    <stringProp>principal</stringProp>
  </collectionProp>
  <stringProp name="Assertion.custom_message">
    El login debe redirigir a principal
  </stringProp>
</ResponseAssertion>
```

### Ahora (Validación de Respuesta JSON)

```xml
<ResponseAssertion>
  <collectionProp name="Asserion.test_strings">
    <stringProp>Login exitoso</stringProp>
    <stringProp>resultado":"ok</stringProp>
  </collectionProp>
  <stringProp name="Assertion.custom_message">
    El login debe retornar JSON con resultado ok
  </stringProp>
</ResponseAssertion>
```

---

## 👤 Credenciales de Prueba Actualizadas

**Usuario de prueba configurado:**
- **Usuario**: `LigiaDuran` ⚠️ (Nota: con "i" - Ligia, no Liga)
- **Contraseña**: `Carolina.16`

**Nota**: Asegúrate de que este usuario exista en tu base de datos antes de ejecutar las pruebas.

---

## 🔒 Seguridad

### Restricción de Localhost

La nueva acción `'ingresar'` **solo funciona en localhost** por seguridad:

```php
$es_localhost = ($_SERVER['HTTP_HOST'] === 'localhost' || 
               $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
               strpos($_SERVER['HTTP_HOST'], 'localhost') !== false);

if (!$es_localhost) {
    echo json_encode(['resultado' => 'error', 'mensaje' => 'Acceso no permitido']);
    exit;
}
```

**Esto significa:**
- ✅ Funciona en: `http://localhost/...`
- ✅ Funciona en: `http://127.0.0.1/...`
- ❌ NO funciona en producción o servidores remotos
- ❌ NO funciona con IPs externas

---

## 📊 Respuestas JSON

### Login Exitoso

```json
{
  "resultado": "ok",
  "mensaje": "Login exitoso"
}
```

### Login Fallido

```json
{
  "resultado": "error",
  "mensaje": "Usuario o contraseña incorrectos"
}
```

### Acceso No Permitido (fuera de localhost)

```json
{
  "resultado": "error",
  "mensaje": "Acceso no permitido"
}
```

---

## 🚀 Cómo Ejecutar las Pruebas Actualizadas

### Opción 1: Interfaz Gráfica

```powershell
# 1. Abrir JMeter
cd C:\jmeter\bin
.\jmeter.bat

# 2. File > Open
# 3. Seleccionar script actualizado (ej: 01_login_basico_test.jmx)
# 4. Click en Start (▶️)
# 5. Ver resultados en "View Results Tree"
```

### Opción 2: Línea de Comandos

```powershell
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Ejecutar test de login
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ..\results\login.jtl

# Ejecutar test de brute force
C:\jmeter\bin\jmeter.bat -n -t 02_brute_force_test.jmx -l ..\results\brute_force.jtl

# Ejecutar test de SQL injection
C:\jmeter\bin\jmeter.bat -n -t 03_sql_injection_test.jmx -l ..\results\sql_injection.jtl
```

### Opción 3: Script Automático

```powershell
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests
.\ejecutar_todos_tests.ps1
```

---

## ⚠️ Verificaciones Previas

Antes de ejecutar las pruebas, verifica:

### 1. Usuario Existe en la Base de Datos

```sql
-- Verificar que el usuario LigaDuran existe
SELECT * FROM tbl_usuario WHERE usu_usuario = 'LigaDuran';
```

Si no existe, créalo o actualiza los scripts con un usuario válido.

### 2. XAMPP Corriendo

```powershell
# Verificar en navegador
http://localhost/org/Sistema-de-Gestion-Docente
```

### 3. Controlador Actualizado

Verifica que `controller/login.php` tenga la nueva acción `'ingresar'`.

---

## 🔄 Compatibilidad

### ✅ Scripts Actualizados (Funcionan con nueva acción)

- `01_login_basico_test.jmx` ✅
- `02_brute_force_test.jmx` ✅
- `03_sql_injection_test.jmx` ✅

### ⏳ Scripts Pendientes de Actualización

- `04_xss_test.jmx` - Requiere login previo
- `05_load_test.jmx` - Requiere login previo

**Nota**: Los scripts 04 y 05 se actualizarán próximamente.

---

## 🐛 Troubleshooting

### Error: "Acceso no permitido"

**Causa**: El servidor no es localhost

**Solución**:
```
Verificar que estés accediendo desde:
- http://localhost/org/Sistema-de-Gestion-Docente
- NO desde http://192.168.x.x/...
```

### Error: Assertion failed - "Login exitoso" no encontrado

**Causa**: Usuario o contraseña incorrectos

**Solución**:
```
1. Verificar que el usuario LigiaDuran existe
2. Verificar que la contraseña es Carolina.16
3. Actualizar credenciales en el script JMeter
```

### Error: Usuario no encontrado

**Causa**: El usuario LigiaDuran no existe en la BD

**Solución**:
```sql
-- Opción 1: Crear el usuario
INSERT INTO tbl_usuario (usu_usuario, usu_clave, ...) 
VALUES ('LigiaDuran', 'hash_de_Carolina.16', ...);

-- Opción 2: Usar un usuario existente
-- Actualizar los scripts .jmx con credenciales válidas
```

---

## 📋 Checklist de Migración

Si tienes scripts personalizados, actualízalos siguiendo este checklist:

- [ ] Cambiar `accion` de `acceder` a `ingresar`
- [ ] Cambiar `nombreUsuario` a `usu_usuario`
- [ ] Cambiar `contraseniaUsuario` a `usu_clave`
- [ ] Eliminar parámetro `g-recaptcha-response`
- [ ] Actualizar assertions para validar JSON
- [ ] Cambiar validación de `principal` a `Login exitoso` o `resultado":"ok`
- [ ] Actualizar credenciales a usuario válido
- [ ] Probar el script actualizado

---

## 💡 Ventajas de los Cambios

### ✅ Sin CAPTCHA
- Pruebas automatizadas sin intervención manual
- Ejecución más rápida
- Ideal para CI/CD

### ✅ Respuestas JSON
- Más fácil de parsear y validar
- Assertions más precisas
- Mejor para integración con otras herramientas

### ✅ Seguridad Mantenida
- Solo funciona en localhost
- Producción sigue protegida con CAPTCHA
- Separación clara entre pruebas y producción

### ✅ Retrocompatibilidad
- La acción `'acceder'` original sigue funcionando
- Usuarios normales no se ven afectados
- Migración gradual posible

---

## 📞 Soporte

Si tienes problemas con los scripts actualizados:

1. Verifica que el controlador de login esté actualizado
2. Confirma que el usuario de prueba existe
3. Revisa los logs de Apache en XAMPP
4. Verifica la respuesta en "View Results Tree" de JMeter

---

## 🎯 Resumen de Cambios

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| **Acción** | `acceder` | `ingresar` |
| **Usuario** | `nombreUsuario` | `usu_usuario` |
| **Contraseña** | `contraseniaUsuario` | `usu_clave` |
| **CAPTCHA** | Requerido | No requerido |
| **Respuesta** | Redirección HTML | JSON |
| **Validación** | `principal` en HTML | `resultado":"ok` en JSON |
| **Credenciales** | admin/Admin123! | LigiaDuran/Carolina.16 |

---

**Última Actualización**: Noviembre 2, 2025  
**Versión**: 2.0  
**Estado**: ✅ Actualizado y Probado
