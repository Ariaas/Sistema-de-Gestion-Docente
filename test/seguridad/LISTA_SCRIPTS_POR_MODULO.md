# 📋 Lista Completa de Scripts JMeter por Módulo

## ✅ Scripts Ya Creados

| # | Módulo | Archivo | Pruebas | Estado |
|---|--------|---------|---------|--------|
| 1 | **Docente** | `11_modulo_docente_test.jmx` | 12 | ✅ Creado |
| 2 | **Sección/Horario** | `12_modulo_seccion_test.jmx` | 15 | ✅ Creado |
| 3 | **Usuario** | `modulo_usuario_test.jmx` | 5 | ✅ Creado |

---

## 📝 Scripts a Crear (16 módulos restantes)

### Prioridad Alta

| # | Módulo | Archivo a Crear | Página | Campo Principal |
|---|--------|-----------------|--------|-----------------|
| 4 | **UC (Unidad Curricular)** | `modulo_uc_test.jmx` | `?pagina=uc` | `uc_nombre` |
| 5 | **Rol** | `modulo_rol_test.jmx` | `?pagina=rol` | `rol_nombre` |
| 6 | **Coordinación** | `modulo_coordinacion_test.jmx` | `?pagina=coordinacion` | `coo_nombre` |
| 7 | **Perfil** | `modulo_perfil_test.jmx` | `?pagina=perfil` | `usu_nombre` |

### Prioridad Media

| # | Módulo | Archivo a Crear | Página | Campo Principal |
|---|--------|-----------------|--------|-----------------|
| 8 | **Malla Curricular** | `modulo_mallacurricular_test.jmx` | `?pagina=mallacurricular` | `mal_nombre` |
| 9 | **Categoría** | `modulo_categoria_test.jmx` | `?pagina=categoria` | `cat_nombre` |
| 10 | **Área** | `modulo_area_test.jmx` | `?pagina=area` | `area_nombre` |
| 11 | **Eje** | `modulo_eje_test.jmx` | `?pagina=eje` | `eje_nombre` |
| 12 | **Fase** | `modulo_fase_test.jmx` | `?pagina=fase` | `fas_nombre` |
| 13 | **Título** | `modulo_titulo_test.jmx` | `?pagina=titulo` | `tit_nombre` |
| 14 | **Turno** | `modulo_turno_test.jmx` | `?pagina=turno` | `tur_nombre` |

### Prioridad Baja

| # | Módulo | Archivo a Crear | Página | Campo Principal |
|---|--------|-----------------|--------|-----------------|
| 15 | **Prosecución** | `modulo_prosecusion_test.jmx` | `?pagina=prosecusion` | `pro_nombre` |
| 16 | **Año** | `modulo_anio_test.jmx` | `?pagina=anio` | `anio_nombre` |
| 17 | **Espacios** | `modulo_espacios_test.jmx` | `?pagina=espacios` | `esp_nombre` |
| 18 | **Backup** | `modulo_backup_test.jmx` | `?pagina=backup` | N/A |
| 19 | **Bitácora** | `modulo_bitacora_test.jmx` | `?pagina=bitacora` | N/A |

---

## 🎯 Estructura de Cada Script

Cada script incluye las siguientes pruebas:

### 1. Acceso al Módulo
- ✅ Verifica que el usuario autenticado puede acceder
- ✅ Código HTTP 200 esperado

### 2. SQL Injection
- ✅ Prueba en campo de búsqueda
- ✅ Prueba con payloads del CSV
- ✅ Detecta errores MySQL

### 3. XSS (Cross-Site Scripting)
- ✅ Prueba en campo principal
- ✅ Prueba con payloads del CSV
- ✅ Detecta scripts reflejados

### 4. IDOR (Insecure Direct Object Reference)
- ✅ Intenta acceder a ID no autorizado (999999)
- ✅ Verifica que se rechace el acceso

### 5. CSRF (Cross-Site Request Forgery)
- ✅ Intenta guardar sin token CSRF
- ✅ Verifica que se rechace la acción

---

## 🚀 Cómo Crear los Scripts Restantes

### Opción 1: Copiar y Modificar (Recomendado)

```powershell
# 1. Ir al directorio de tests
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# 2. Copiar el script de usuario como plantilla
Copy-Item modulo_usuario_test.jmx modulo_uc_test.jmx

# 3. Abrir en editor y buscar/reemplazar:
#    - "Usuario" → "UC"
#    - "usuario" → "uc"
#    - "usu_usuario" → "uc_nombre"
#    - "usu_nombre" → "uc_nombre"

# 4. Guardar y probar
C:\jmeter\bin\jmeter.bat -t modulo_uc_test.jmx
```

### Opción 2: Usar JMeter GUI

```
1. Abrir JMeter
2. File > Open > modulo_usuario_test.jmx
3. Modificar:
   - Test Plan name
   - Thread Group name
   - HTTP Samplers (cambiar ?pagina=usuario a ?pagina=uc)
   - Nombres de campos
4. File > Save As > modulo_uc_test.jmx
```

---

## 📊 Plantilla de Reemplazo

Para crear un nuevo script, reemplaza estos valores:

| Elemento | Valor Original | Nuevo Valor (Ejemplo: UC) |
|----------|----------------|---------------------------|
| **Test Plan Name** | "Módulo Usuario" | "Módulo UC" |
| **Thread Group** | "Usuario" | "UC" |
| **Página** | `?pagina=usuario` | `?pagina=uc` |
| **Campo Principal** | `usu_usuario` | `uc_nombre` |
| **Campo Secundario** | `usu_nombre` | `uc_codigo` |
| **ID Parameter** | `usu_id` | `uc_id` |

---

## ✅ Checklist de Validación

Después de crear cada script, verifica:

```
□ Test Plan tiene el nombre correcto del módulo
□ Setup Login funciona correctamente
□ Thread Group tiene nombre descriptivo
□ Prueba 1: Acceso al módulo (GET)
□ Prueba 2: SQL Injection con CSV
□ Prueba 3: XSS con CSV
□ Prueba 4: IDOR con ID 999999
□ Prueba 5: CSRF sin token
□ Listeners configurados (Ver Árbol, Resumen)
□ Paths correctos (?pagina=[modulo])
□ Nombres de campos correctos
```

---

## 🎯 Ejecución Rápida

### Probar un Módulo Individual

```powershell
# Ejemplo: Probar módulo UC
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

C:\jmeter\bin\jmeter.bat -n -t modulo_uc_test.jmx -l ..\results\uc_results.jtl
```

### Probar Todos los Módulos

```powershell
# Crear un script batch simple
$modulos = @("usuario", "uc", "rol", "coordinacion", "area", "eje", "categoria", "titulo", "turno", "fase", "prosecusion", "anio", "espacios")

foreach ($mod in $modulos) {
    Write-Host "Probando módulo: $mod" -ForegroundColor Yellow
    C:\jmeter\bin\jmeter.bat -n -t "tests\modulo_${mod}_test.jmx" -l "results\${mod}_results.jtl"
}
```

---

## 📈 Progreso de Creación

```
Total de Módulos: 19
Scripts Creados: 3 (16%)
Scripts Pendientes: 16 (84%)

Prioridad Alta: 4 módulos
Prioridad Media: 7 módulos
Prioridad Baja: 5 módulos
```

---

## 💡 Tips

1. **Empieza por prioridad alta**: UC, Rol, Coordinación, Perfil
2. **Usa buscar/reemplazar**: Es más rápido que crear desde cero
3. **Prueba cada script**: Antes de crear el siguiente
4. **Documenta hallazgos**: Anota vulnerabilidades encontradas
5. **Mantén consistencia**: Usa la misma estructura en todos

---

## 🔧 Solución de Problemas

### Error: "Campo no encontrado"
```
Solución: Verifica el nombre del campo en el archivo PHP del módulo
Ubicación: views/[modulo].php o controller/[modulo].php
```

### Error: "Página no existe"
```
Solución: Verifica que el parámetro ?pagina=[nombre] sea correcto
Prueba manualmente: http://localhost/org/Sistema-de-Gestion-Docente/?pagina=[modulo]
```

### Error: "Acceso denegado"
```
Solución: Verifica que el usuario LigiaDuran tenga permisos para ese módulo
O usa otro usuario con permisos adecuados
```

---

## 📞 Próximos Pasos

1. ✅ **Crear scripts de prioridad alta** (UC, Rol, Coordinación, Perfil)
2. ✅ **Ejecutar y validar** cada script
3. ✅ **Documentar vulnerabilidades** encontradas
4. ✅ **Crear scripts de prioridad media**
5. ✅ **Crear scripts de prioridad baja**
6. ✅ **Generar reporte consolidado**

---

**¿Necesitas ayuda para crear algún script específico?** 

Puedo crear cualquiera de los 16 scripts restantes. Solo dime cuál módulo quieres probar primero. 🚀
