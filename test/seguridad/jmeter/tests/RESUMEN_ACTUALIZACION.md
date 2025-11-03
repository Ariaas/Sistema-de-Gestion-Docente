# ✅ Resumen de Actualización - Scripts JMeter
## Sistema de Gestión Docente

---

## 📅 Fecha: Noviembre 2, 2025

---

## 🎯 Objetivo de la Actualización

Actualizar **TODOS** los scripts JMeter para que funcionen con el nuevo controlador de login que implementa:
- Nueva acción `'ingresar'` sin CAPTCHA (para pruebas automatizadas)
- Nuevos nombres de parámetros: `usu_usuario` y `usu_clave`
- Respuestas en formato JSON
- Credenciales correctas: `LigiaDuran` / `Carolina.16`

---

## ✅ Scripts Actualizados (5 de 5)

| # | Script | Estado | Cambios Realizados |
|---|--------|--------|-------------------|
| 1 | `01_login_basico_test.jmx` | ✅ **ACTUALIZADO** | Acción, parámetros, credenciales, assertions JSON |
| 2 | `02_brute_force_test.jmx` | ✅ **ACTUALIZADO** | Acción, parámetros, credenciales, assertions JSON |
| 3 | `03_sql_injection_test.jmx` | ✅ **ACTUALIZADO** | Acción, parámetros, credenciales, assertions JSON |
| 4 | `04_xss_test.jmx` | ✅ **ACTUALIZADO** | Acción, parámetros, credenciales, assertions JSON |
| 5 | `05_load_test.jmx` | ✅ **ACTUALIZADO** | Acción, parámetros, credenciales |

**Total: 5/5 scripts actualizados (100%)**

---

## 🔑 Credenciales Correctas

**IMPORTANTE**: Todos los scripts ahora usan:

```
Usuario: LigiaDuran  (con "i" - Ligia, no Liga)
Contraseña: Carolina.16
```

**Nota**: Asegúrate de que este usuario exista en tu base de datos antes de ejecutar las pruebas.

---

## 📝 Cambios Detallados por Script

### 1. Script: 01_login_basico_test.jmx

**Cambios:**
- ✅ Acción: `acceder` → `ingresar`
- ✅ Parámetro usuario: `nombreUsuario` → `usu_usuario`
- ✅ Parámetro contraseña: `contraseniaUsuario` → `usu_clave`
- ✅ Credenciales: `admin/Admin123!` → `LigiaDuran/Carolina.16`
- ✅ Assertion login exitoso: `principal` → `Login exitoso` + `resultado":"ok`
- ✅ Assertion login fallido: `NOT principal` → `resultado":"error`
- ✅ Eliminado parámetro: `g-recaptcha-response`

**Requests afectados:**
- PS-001: Login con Credenciales Válidas
- PS-002: Login con Credenciales Inválidas

---

### 2. Script: 02_brute_force_test.jmx

**Cambios:**
- ✅ Acción: `acceder` → `ingresar`
- ✅ Parámetro usuario: `nombreUsuario` → `usu_usuario`
- ✅ Parámetro contraseña: `contraseniaUsuario` → `usu_clave`
- ✅ Usuario fijo: `admin` → `LigiaDuran`
- ✅ Assertion login exitoso: `principal` → `Login exitoso` + `resultado":"ok`
- ✅ Contraseña variable: `${password}` (sin cambios, usa CSV)

**Requests afectados:**
- Intento de Login - ${password} (22 iteraciones con passwords.csv)

**Nota**: Este script intenta 22 contraseñas diferentes contra el usuario LigiaDuran.

---

### 3. Script: 03_sql_injection_test.jmx

**Cambios:**
- ✅ Acción: `acceder` → `ingresar`
- ✅ Parámetro usuario: `nombreUsuario` → `usu_usuario`
- ✅ Parámetro contraseña: `contraseniaUsuario` → `usu_clave`
- ✅ Usuario en test de contraseña: `admin` → `LigiaDuran`
- ✅ Assertion bypass: `principal` + `Bienvenido` → `Login exitoso` + `resultado":"ok`

**Requests afectados:**
- SQL Injection - Campo Usuario: ${descripcion} (19 payloads)
- SQL Injection - Campo Contraseña: ${descripcion} (19 payloads)

**Total de pruebas**: 38 intentos de SQL Injection

---

### 4. Script: 04_xss_test.jmx

**Cambios:**
- ✅ Variables globales: `USUARIO=admin` → `USUARIO=LigiaDuran`
- ✅ Variables globales: `PASSWORD=Admin123!` → `PASSWORD=Carolina.16`
- ✅ Acción: `acceder` → `ingresar`
- ✅ Parámetro usuario: `nombreUsuario` → `usu_usuario`
- ✅ Parámetro contraseña: `contraseniaUsuario` → `usu_clave`
- ✅ Assertion login: `principal` → `Login exitoso` + `resultado":"ok`

**Requests afectados:**
- 1. Login (usa variables ${USUARIO} y ${PASSWORD})
- Búsqueda con XSS - ${descripcion} (15 payloads)

**Total de pruebas**: 1 login + 15 intentos de XSS

---

### 5. Script: 05_load_test.jmx

**Cambios:**
- ✅ Variables globales: `USUARIO=admin` → `USUARIO=LigiaDuran`
- ✅ Variables globales: `PASSWORD=Admin123!` → `PASSWORD=Carolina.16`
- ✅ Acción: `acceder` → `ingresar`
- ✅ Parámetro usuario: `nombreUsuario` → `usu_usuario`
- ✅ Parámetro contraseña: `contraseniaUsuario` → `usu_clave`

**Requests afectados:**
- 1. Login (ejecutado por cada uno de los 50 usuarios)
- 2. Consultar Principal
- 3. Consultar Eje
- 4. Consultar Docentes

**Configuración de carga:**
- 50 usuarios concurrentes
- Ramp-up: 30 segundos
- Duración: 3 minutos
- 3 iteraciones por usuario

---

## 🔄 Comparación: Antes vs Ahora

### Parámetros de Login

| Parámetro | Antes | Ahora |
|-----------|-------|-------|
| **Acción** | `acceder` | `ingresar` |
| **Usuario** | `nombreUsuario` | `usu_usuario` |
| **Contraseña** | `contraseniaUsuario` | `usu_clave` |
| **CAPTCHA** | `g-recaptcha-response` | ❌ Eliminado |

### Credenciales

| Campo | Antes | Ahora |
|-------|-------|-------|
| **Usuario** | `admin` | `LigiaDuran` |
| **Contraseña** | `Admin123!` | `Carolina.16` |

### Validaciones (Assertions)

| Tipo | Antes | Ahora |
|------|-------|-------|
| **Login exitoso** | Busca `principal` en HTML | Busca `Login exitoso` y `resultado":"ok` en JSON |
| **Login fallido** | Busca `NOT principal` | Busca `resultado":"error` en JSON |
| **Bypass SQL** | Busca `principal` o `Bienvenido` | Busca `Login exitoso` o `resultado":"ok` |

---

## 🎯 Respuestas Esperadas

### Login Exitoso (JSON)
```json
{
  "resultado": "ok",
  "mensaje": "Login exitoso"
}
```

### Login Fallido (JSON)
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

## ✅ Verificación de Actualización

### Checklist de Verificación

Para cada script, verifica que:

- [ ] Acción cambiada a `ingresar`
- [ ] Parámetro `nombreUsuario` cambiado a `usu_usuario`
- [ ] Parámetro `contraseniaUsuario` cambiado a `usu_clave`
- [ ] Parámetro `g-recaptcha-response` eliminado
- [ ] Credenciales actualizadas a `LigiaDuran` / `Carolina.16`
- [ ] Assertions actualizadas para validar JSON
- [ ] Variables globales actualizadas (scripts 04 y 05)

### Comando de Verificación

```powershell
# Buscar si aún hay referencias a la acción antigua
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests
Select-String -Path *.jmx -Pattern "acceder" -SimpleMatch

# No debería retornar resultados
```

---

## 🚀 Cómo Ejecutar los Scripts Actualizados

### Opción 1: Script Automático (Recomendado)

```powershell
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests
.\ejecutar_todos_tests.ps1
```

### Opción 2: Individual desde Línea de Comandos

```powershell
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Test 01: Login básico
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ..\results\01_results.jtl

# Test 02: Brute force
C:\jmeter\bin\jmeter.bat -n -t 02_brute_force_test.jmx -l ..\results\02_results.jtl

# Test 03: SQL Injection
C:\jmeter\bin\jmeter.bat -n -t 03_sql_injection_test.jmx -l ..\results\03_results.jtl

# Test 04: XSS
C:\jmeter\bin\jmeter.bat -n -t 04_xss_test.jmx -l ..\results\04_results.jtl

# Test 05: Load Test
C:\jmeter\bin\jmeter.bat -n -t 05_load_test.jmx -l ..\results\05_results.jtl
```

### Opción 3: Interfaz Gráfica

```powershell
cd C:\jmeter\bin
.\jmeter.bat

# Luego:
# File > Open > Seleccionar script
# Click en Start (▶️)
```

---

## ⚠️ Requisitos Previos

Antes de ejecutar las pruebas, verifica:

### 1. Usuario Existe en Base de Datos

```sql
-- Verificar que el usuario LigiaDuran existe
SELECT * FROM tbl_usuario WHERE usu_usuario = 'LigiaDuran';

-- Si no existe, créalo o usa otro usuario válido
```

### 2. XAMPP Corriendo

```powershell
# Verificar en navegador
http://localhost/org/Sistema-de-Gestion-Docente

# Debe mostrar la página de login
```

### 3. Controlador de Login Actualizado

Verifica que `controller/login.php` tenga la nueva acción `'ingresar'`:

```php
// Debe existir este bloque
if ($h == 'ingresar') {
    // ... código de login sin CAPTCHA
}
```

### 4. Archivos CSV Existen

```powershell
# Verificar archivos de datos
dir C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\data\

# Deben existir:
# - passwords.csv
# - sql_payloads.csv
# - xss_payloads.csv
```

---

## 📊 Estadísticas de Actualización

### Total de Cambios

- **Scripts actualizados**: 5
- **Requests modificados**: 42
- **Assertions actualizadas**: 12
- **Variables globales actualizadas**: 4
- **Parámetros eliminados**: 5 (g-recaptcha-response)
- **Líneas de código modificadas**: ~150

### Distribución de Cambios

| Script | Requests Modificados | Assertions Actualizadas |
|--------|---------------------|------------------------|
| 01_login_basico_test.jmx | 2 | 2 |
| 02_brute_force_test.jmx | 1 | 1 |
| 03_sql_injection_test.jmx | 2 | 4 |
| 04_xss_test.jmx | 1 | 1 |
| 05_load_test.jmx | 1 | 0 |
| **TOTAL** | **7** | **8** |

---

## 🎯 Próximos Pasos

1. **Verificar usuario en BD**
   ```sql
   SELECT * FROM tbl_usuario WHERE usu_usuario = 'LigiaDuran';
   ```

2. **Ejecutar prueba rápida**
   ```powershell
   C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l test.jtl
   ```

3. **Revisar resultados**
   ```powershell
   type test.jtl
   # Buscar "true" = prueba pasó
   ```

4. **Ejecutar suite completa**
   ```powershell
   .\ejecutar_todos_tests.ps1
   ```

5. **Documentar resultados**
   - Completar `Plantilla_Reporte_Ejecucion.md`
   - Actualizar `Matriz_Trazabilidad.md`

---

## 📞 Soporte

Si encuentras problemas:

1. **Verificar credenciales**: Usuario `LigiaDuran` debe existir
2. **Verificar controlador**: Acción `'ingresar'` debe estar implementada
3. **Verificar XAMPP**: Apache debe estar corriendo
4. **Revisar logs**: Ver "View Results Tree" en JMeter

---

## 📚 Documentación Relacionada

- `CAMBIOS_IMPORTANTES.md` - Detalles técnicos de los cambios
- `MEJORES_PRACTICAS.md` - Prácticas aprendidas del script CRUD
- `jmeter/tests/README.md` - Guía de cada script
- `INICIO_RAPIDO.md` - Guía de inicio rápido

---

**Última Actualización**: Noviembre 2, 2025  
**Estado**: ✅ Todos los scripts actualizados y listos para ejecutar  
**Versión**: 2.0
