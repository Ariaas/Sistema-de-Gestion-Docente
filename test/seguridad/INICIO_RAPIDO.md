# 🚀 Inicio Rápido - Pruebas de Seguridad con JMeter
## Sistema de Gestión Docente

---

## ⏱️ 5 Minutos para Empezar

### Paso 1: Verificar Requisitos (1 min)

```powershell
# ✅ XAMPP corriendo
# Abrir navegador: http://localhost/org/Sistema-de-Gestion-Docente
# Debe mostrar página de login

# ✅ JMeter instalado
# Verificar que existe: C:\jmeter\bin\jmeter.bat
```

---

### Paso 2: Ejecutar Primera Prueba (2 min)

**Opción A: Interfaz Gráfica (Recomendado)**

```powershell
# 1. Abrir JMeter
cd C:\jmeter\bin
.\jmeter.bat

# 2. File > Open
# 3. Navegar a: C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests\
# 4. Abrir: 01_login_basico_test.jmx
# 5. Click en Start (▶️)
# 6. Ver resultados en "View Results Tree"
```

**Opción B: Línea de Comandos (Más rápido)**

```powershell
# Navegar a carpeta de tests
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Ejecutar test
C:\jmeter\bin\jmeter.bat -n -t 01_login_basico_test.jmx -l ..\results\login_results.jtl

# Ver resultados
type ..\results\login_results.jtl
```

---

### Paso 3: Interpretar Resultados (2 min)

**En Interfaz Gráfica:**
- ✅ Verde = Prueba pasó
- ❌ Rojo = Prueba falló
- Click en cada request para ver detalles

**En Línea de Comandos:**
```
true = Prueba pasó
false = Prueba falló
```

---

## 🎯 Ejecutar Todas las Pruebas (4 minutos)

### Opción Automática (Recomendado)

```powershell
# Navegar a carpeta de tests
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# Ejecutar script automático
.\ejecutar_todos_tests.ps1

# Seguir las instrucciones en pantalla
# Seleccionar opción 1 para ejecutar todos los tests
```

**El script hará:**
1. ✅ Verificar que XAMPP esté corriendo
2. ✅ Ejecutar los 5 tests automáticamente
3. ✅ Generar reportes HTML
4. ✅ Mostrar resumen de resultados
5. ✅ Abrir reportes en navegador

---

## 📊 Tests Disponibles

| Test | Duración | Qué Detecta |
|------|----------|-------------|
| **01_login_basico_test.jmx** | 10 seg | Login funciona correctamente |
| **02_brute_force_test.jmx** | 30 seg | Protección contra fuerza bruta |
| **03_sql_injection_test.jmx** | 20 seg | Vulnerabilidades SQL Injection |
| **04_xss_test.jmx** | 25 seg | Vulnerabilidades XSS |
| **05_load_test.jmx** | 3 min | Rendimiento bajo carga |

---

## 🔍 Ver Resultados

### Reportes HTML (Más Visual)

```powershell
# Abrir reporte en navegador
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\results

# Buscar carpeta con fecha (ej: 2025-11-02_201530)
# Abrir: [fecha]/01_login_basico_test_report/index.html
```

### Archivos JTL (Más Detallado)

```powershell
# Ver archivo de resultados
type C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\results\[fecha]\01_login_basico_test.jtl
```

---

## ⚠️ Problemas Comunes

### "Connection refused"
```
❌ Problema: XAMPP no está corriendo
✅ Solución: Iniciar Apache en XAMPP Control Panel
```

### "File not found" (CSV)
```
❌ Problema: Archivos CSV no encontrados
✅ Solución: Verificar que existan en jmeter/data/
```

### "Assertion failed" en todos los tests
```
❌ Problema: Credenciales incorrectas o URL incorrecta
✅ Solución: 
   1. Verificar usuario: admin / Admin123!
   2. Verificar URL: http://localhost/org/Sistema-de-Gestion-Docente
```

### CAPTCHA bloquea pruebas
```
❌ Problema: reCAPTCHA activo
✅ Solución temporal (SOLO PARA PRUEBAS):
   Comentar validación de CAPTCHA en controller/login.php
```

---

## 📚 Documentación Completa

¿Necesitas más información?

| Documento | Para Qué |
|-----------|----------|
| **README.md** | Guía general y estructura |
| **Tutorial_Primera_Prueba.md** | Tutorial paso a paso (30 min) |
| **Guia_JMeter_Pruebas_Seguridad.md** | Guía completa de JMeter |
| **Casos_Prueba_Detallados.md** | 29 casos de prueba detallados |
| **Plan_Pruebas_Seguridad.md** | Plan maestro de pruebas |
| **jmeter/tests/README.md** | Documentación de scripts .jmx |

---

## 🎓 Próximos Pasos

### Después de Ejecutar las Pruebas

1. **Revisar Reportes HTML**
   - Abrir en navegador
   - Ver gráficas y métricas
   - Identificar errores

2. **Documentar Hallazgos**
   - Abrir: `Plantilla_Reporte_Ejecucion.md`
   - Completar secciones con resultados
   - Capturar screenshots de vulnerabilidades

3. **Actualizar Matriz de Trazabilidad**
   - Abrir: `Matriz_Trazabilidad.md`
   - Marcar pruebas como pasadas/fallidas
   - Documentar defectos encontrados

4. **Reportar Vulnerabilidades**
   - Priorizar por severidad (Crítica > Alta > Media > Baja)
   - Crear tickets/issues para el equipo de desarrollo
   - Planificar correcciones

---

## 💡 Tips Rápidos

### Para Principiantes
```
1. Empieza con 01_login_basico_test.jmx
2. Usa la interfaz gráfica de JMeter
3. Lee Tutorial_Primera_Prueba.md
```

### Para Usuarios Avanzados
```
1. Usa el script ejecutar_todos_tests.ps1
2. Ejecuta desde línea de comandos
3. Personaliza los scripts .jmx según necesites
```

### Para Auditorías Completas
```
1. Ejecuta todos los tests (opción 1 del script)
2. Genera reportes HTML
3. Completa toda la documentación
4. Presenta resultados al equipo
```

---

## 🔗 Enlaces Rápidos

**Archivos Importantes:**
- Scripts JMeter: `jmeter/tests/*.jmx`
- Datos de prueba: `jmeter/data/*.csv`
- Resultados: `jmeter/results/`
- Documentación: `*.md`

**Comandos Útiles:**
```powershell
# Abrir JMeter GUI
C:\jmeter\bin\jmeter.bat

# Ejecutar test específico
C:\jmeter\bin\jmeter.bat -n -t [test.jmx] -l [results.jtl]

# Ejecutar todos los tests
.\ejecutar_todos_tests.ps1

# Ver resultados
type ..\results\[archivo].jtl
```

---

## ✅ Checklist Rápido

Antes de empezar:
- [ ] XAMPP corriendo
- [ ] JMeter instalado
- [ ] Usuario admin creado (admin/Admin123!)
- [ ] Archivos CSV en jmeter/data/

Durante las pruebas:
- [ ] Ejecutar tests en orden
- [ ] Revisar resultados de cada test
- [ ] Capturar screenshots de errores
- [ ] Anotar vulnerabilidades encontradas

Después de las pruebas:
- [ ] Revisar reportes HTML
- [ ] Completar documentación
- [ ] Reportar defectos al equipo
- [ ] Planificar correcciones

---

## 📞 ¿Necesitas Ayuda?

**Problemas técnicos:**
- Ver sección "Troubleshooting" en `jmeter/tests/README.md`
- Revisar logs de JMeter
- Verificar logs de Apache (XAMPP)

**Dudas sobre JMeter:**
- Leer `Guia_JMeter_Pruebas_Seguridad.md`
- Seguir `Tutorial_Primera_Prueba.md`
- Consultar documentación oficial de JMeter

**Dudas sobre casos de prueba:**
- Revisar `Casos_Prueba_Detallados.md`
- Ver `Plan_Pruebas_Seguridad.md`
- Consultar `Matriz_Trazabilidad.md`

---

## 🎯 Resumen Ultra-Rápido

```powershell
# 1. Verificar XAMPP
# http://localhost/org/Sistema-de-Gestion-Docente

# 2. Ir a carpeta de tests
cd C:\xampp\htdocs\org\Sistema-de-Gestion-Docente\test\seguridad\jmeter\tests

# 3. Ejecutar script automático
.\ejecutar_todos_tests.ps1

# 4. Seleccionar opción 1 (Todos los tests)

# 5. Esperar ~4 minutos

# 6. Revisar reportes HTML generados

# ¡Listo! 🎉
```

---

**¡Comienza ahora!** 🚀

Ejecuta tu primera prueba en menos de 5 minutos siguiendo esta guía.

---

**Última Actualización**: Noviembre 2025  
**Versión**: 1.0  
**Tiempo de Lectura**: 3 minutos
