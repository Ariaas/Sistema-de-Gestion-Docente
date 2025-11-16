# 🚀 Guía Rápida: Pruebas de Seguridad por Módulo

## ⚡ Inicio Rápido en 5 Minutos

### 1. Verificar Requisitos

```powershell
# ✅ XAMPP corriendo
# ✅ JMeter instalado en C:\jmeter
# ✅ Usuario de prueba: LigiaDuran / Carolina.16
```

### 2. Probar un Módulo Específico

#### Opción A: Módulo Docente (Script Dedicado)

```powershell
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Ejecutar en modo GUI (recomendado para primera vez)
C:\jmeter\bin\jmeter.bat -t 11_modulo_docente_test.jmx

# O en modo CLI (más rápido)
C:\jmeter\bin\jmeter.bat -n -t 11_modulo_docente_test.jmx -l ..\results\docente_results.jtl
```

#### Opción B: Cualquier Otro Módulo (Script Genérico)

```powershell
# 1. Abrir JMeter
C:\jmeter\bin\jmeter.bat

# 2. File > Open > 13_modulos_generales_test.jmx

# 3. En "Variables Definidas" cambiar MODULO a uno de estos:
#    - usuario
#    - uc
#    - area
#    - eje
#    - categoria
#    - titulo
#    - turno
#    - rol
#    - coordinacion
#    - mallacurricular
#    - prosecusion
#    - anio
#    - espacios

# 4. Click en el botón verde "Start" (▶)

# 5. Ver resultados en "Ver Árbol de Resultados"
```

---

## 📋 Scripts Disponibles por Módulo

| Módulo | Script a Usar | Configuración |
|--------|---------------|---------------|
| **Login/Usuario** | 01-04 (múltiples) | Ya configurado |
| **Docente** | 11_modulo_docente_test.jmx | Ya configurado |
| **Sección/Horario** | 12_modulo_seccion_test.jmx | Ya configurado |
| **UC** | 13_modulos_generales_test.jmx | MODULO=uc |
| **Área** | 13_modulos_generales_test.jmx | MODULO=area |
| **Eje** | 13_modulos_generales_test.jmx | MODULO=eje |
| **Categoría** | 13_modulos_generales_test.jmx | MODULO=categoria |
| **Título** | 13_modulos_generales_test.jmx | MODULO=titulo |
| **Turno** | 13_modulos_generales_test.jmx | MODULO=turno |
| **Rol** | 13_modulos_generales_test.jmx | MODULO=rol |
| **Coordinación** | 13_modulos_generales_test.jmx | MODULO=coordinacion |
| **Malla Curricular** | 13_modulos_generales_test.jmx | MODULO=mallacurricular |
| **Prosecución** | 13_modulos_generales_test.jmx | MODULO=prosecusion |
| **Año** | 13_modulos_generales_test.jmx | MODULO=anio |
| **Espacios** | 13_modulos_generales_test.jmx | MODULO=espacios |

---

## 🎯 Qué Prueba Cada Script

### Script 11: Módulo Docente
```
✅ SQL Injection en nombre, cédula, ID
✅ XSS en nombre, correo
✅ IDOR (ver, modificar, eliminar)
✅ CSRF en guardar
```

### Script 12: Módulo Sección/Horario
```
✅ SQL Injection en nombre, ID, filtros
✅ Lógica de negocio (conflictos de horario)
✅ IDOR (ver, modificar, eliminar)
✅ Mass Assignment (campos protegidos)
```

### Script 13: Módulos Generales
```
✅ SQL Injection en ID y búsqueda
✅ XSS en campo nombre
✅ IDOR (acceso, modificación, eliminación)
✅ CSRF (guardar, eliminar)
✅ Access Control
```

---

## 📊 Interpretar Resultados

### ✅ Verde = SEGURO
```
El sistema rechazó correctamente el ataque
No se encontraron vulnerabilidades
```

### ❌ Rojo = VULNERABLE
```
El sistema aceptó el ataque
Se encontró una vulnerabilidad
ACCIÓN REQUERIDA: Corregir inmediatamente
```

### Mensajes Comunes

| Mensaje | Significado | Acción |
|---------|-------------|--------|
| "¡VULNERABLE! Error SQL detectado" | SQL Injection encontrado | Usar prepared statements |
| "¡VULNERABLE! XSS detectado" | XSS encontrado | Sanitizar entrada |
| "¡VULNERABLE! IDOR" | Acceso no autorizado | Validar permisos |
| "¡VULNERABLE! CSRF" | Sin protección CSRF | Implementar tokens |
| "SEGURO: ..." | Todo correcto | ✅ Continuar |

---

## 🔄 Workflow Completo

### Para UN Módulo

```
1. Seleccionar módulo a probar
   ↓
2. Abrir script correspondiente
   ↓
3. Configurar variable MODULO (si es script 13)
   ↓
4. Ejecutar pruebas
   ↓
5. Revisar resultados
   ↓
6. Documentar vulnerabilidades
   ↓
7. Corregir código
   ↓
8. Re-ejecutar pruebas
   ↓
9. Marcar como completado
```

### Para TODOS los Módulos

```powershell
# Script automatizado para probar todos los módulos
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

$modulos = @("usuario", "uc", "area", "eje", "categoria", "titulo", "turno", "rol", "coordinacion", "mallacurricular", "prosecusion", "anio", "espacios")
$fecha = Get-Date -Format "yyyy-MM-dd_HHmm"

# Pruebas específicas
Write-Host "Ejecutando pruebas específicas..." -ForegroundColor Green
C:\jmeter\bin\jmeter.bat -n -t 11_modulo_docente_test.jmx -l "..\results\${fecha}_docente.jtl"
C:\jmeter\bin\jmeter.bat -n -t 12_modulo_seccion_test.jmx -l "..\results\${fecha}_seccion.jtl"

# Pruebas genéricas
Write-Host "Ejecutando pruebas genéricas..." -ForegroundColor Green
foreach ($modulo in $modulos) {
    Write-Host "Probando módulo: $modulo" -ForegroundColor Yellow
    # Nota: Necesitas modificar el .jmx para cambiar la variable MODULO
    # O ejecutar manualmente cada uno
}

Write-Host "¡Pruebas completadas!" -ForegroundColor Green
Write-Host "Revisa los resultados en: test\seguridad\jmeter\results\" -ForegroundColor Cyan
```

---

## 📝 Plantilla de Reporte por Módulo

```markdown
# Reporte de Seguridad: Módulo [NOMBRE]

**Fecha**: [DD/MM/YYYY]
**Ejecutor**: [Tu nombre]
**Script**: [Nombre del script]

## Resumen Ejecutivo
- Total de pruebas: X
- Exitosas: X (verde)
- Fallidas: X (rojo)
- Severidad: [Crítica/Alta/Media/Baja]

## Vulnerabilidades Encontradas

### 1. [Tipo de Vulnerabilidad]
- **Severidad**: [Crítica/Alta/Media/Baja]
- **Campo afectado**: [nombre del campo]
- **Descripción**: [qué se encontró]
- **Evidencia**: [captura o log]
- **Recomendación**: [cómo corregir]

### 2. ...

## Pruebas Exitosas
- ✅ SQL Injection bloqueado
- ✅ XSS sanitizado
- ✅ IDOR protegido
- ✅ CSRF con token

## Próximos Pasos
1. [ ] Corregir vulnerabilidades críticas
2. [ ] Corregir vulnerabilidades altas
3. [ ] Re-ejecutar pruebas
4. [ ] Documentar cambios

## Estado Final
- [ ] ✅ APROBADO (sin vulnerabilidades)
- [ ] ⚠️ APROBADO CON OBSERVACIONES
- [ ] ❌ RECHAZADO (vulnerabilidades críticas)
```

---

## 🎓 Tips y Mejores Prácticas

### Antes de Ejecutar
```
✅ Hacer backup de la base de datos
✅ Usar ambiente de pruebas (NO producción)
✅ Verificar que XAMPP esté corriendo
✅ Tener usuario de prueba válido
```

### Durante la Ejecución
```
✅ Monitorear el View Results Tree
✅ Anotar cualquier comportamiento extraño
✅ Tomar capturas de vulnerabilidades
✅ No interrumpir las pruebas
```

### Después de Ejecutar
```
✅ Guardar los archivos .jtl
✅ Generar reporte HTML
✅ Documentar vulnerabilidades
✅ Priorizar correcciones
```

---

## 🆘 Solución de Problemas Comunes

### Error: "Connection refused"
```
Solución: Verificar que Apache esté corriendo en XAMPP
```

### Error: "Could not read CSV file"
```
Solución: Verificar rutas absolutas en CSV Data Set Config
```

### Error: "Login failed"
```
Solución: Verificar credenciales: LigiaDuran / Carolina.16
```

### Muchos falsos positivos
```
Solución: Ajustar las assertions en el script
```

### Pruebas muy lentas
```
Solución: Reducir threads o aumentar ramp-up time
```

---

## 📞 Comandos Útiles

### Ejecutar en Modo CLI (Sin GUI)
```powershell
C:\jmeter\bin\jmeter.bat -n -t [SCRIPT].jmx -l results.jtl
```

### Generar Reporte HTML
```powershell
C:\jmeter\bin\jmeter.bat -g results.jtl -o reporte_html
```

### Ver Logs de JMeter
```powershell
Get-Content C:\jmeter\bin\jmeter.log -Tail 50
```

### Limpiar Resultados Antiguos
```powershell
Remove-Item ..\results\*.jtl
```

---

## 📊 Dashboard de Progreso

Usa esta tabla para trackear tu progreso:

| Módulo | Script | Ejecutado | Vulnerabilidades | Corregido | Re-test | Estado |
|--------|--------|-----------|------------------|-----------|---------|--------|
| Login | 01-04 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| Docente | 11 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| Sección | 12 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| UC | 13 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| Área | 13 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| Eje | 13 | ⬜ | - | ⬜ | ⬜ | ⏳ |
| ... | ... | ... | ... | ... | ... | ... |

**Leyenda**: ⬜ Pendiente | ✅ Completado | ❌ Fallido | ⏳ En progreso

---

## 🎯 Objetivos de Seguridad

### Mínimo Aceptable
```
❌ 0 vulnerabilidades CRÍTICAS
⚠️ < 3 vulnerabilidades ALTAS
✅ < 5 vulnerabilidades MEDIAS
```

### Ideal
```
✅ 0 vulnerabilidades de cualquier tipo
✅ 100% de pruebas pasadas
✅ Código limpio y seguro
```

---

**¡Listo para empezar! 🚀**

**Siguiente paso**: Abre JMeter y ejecuta tu primera prueba con `11_modulo_docente_test.jmx`

**Documentación completa**: Ver `MATRIZ_PRUEBAS_MODULOS.md`

**Soporte**: Revisa `RECOMENDACIONES_SEGURIDAD.md` para guías de corrección
