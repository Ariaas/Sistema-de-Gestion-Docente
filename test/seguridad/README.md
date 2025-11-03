# Documentación de Pruebas de Seguridad
## Sistema de Gestión Docente

---

## 📋 Contenido de esta Carpeta

Este directorio contiene toda la documentación relacionada con las pruebas de seguridad del Sistema de Gestión Docente.

### Documentos Principales

| Documento | Descripción | Estado |
|-----------|-------------|--------|
| **Plan_Pruebas_Seguridad.md** | Plan maestro de pruebas de seguridad | ✅ Completo |
| **Casos_Prueba_Detallados.md** | 29 casos de prueba detallados | ✅ Completo |
| **Plantilla_Reporte_Ejecucion.md** | Template para documentar resultados | ✅ Completo |
| **Matriz_Trazabilidad.md** | Matriz de trazabilidad requisitos-pruebas | ✅ Completo |
| **Guia_JMeter_Pruebas_Seguridad.md** | Guía práctica de JMeter | ✅ Completo |

---

## 🚀 Inicio Rápido

### 1. Preparación del Ambiente

```powershell
# Verificar que XAMPP esté corriendo
# Verificar acceso a: http://localhost/org/Sistema-de-Gestion-Docente

# Crear usuarios de prueba en la base de datos
# - admin / Admin123!
# - coordinador / Coord123!
# - docente / Docente123!
```

### 2. Instalación de Herramientas

**JMeter:**
```powershell
# Descargar desde: https://jmeter.apache.org/download_jmeter.cgi
# Extraer en C:\jmeter
# Ejecutar: C:\jmeter\bin\jmeter.bat
```

**OWASP ZAP (Opcional):**
```powershell
# Descargar desde: https://www.zaproxy.org/download/
# Instalar y ejecutar
```

### 3. Ejecutar Primera Prueba

```powershell
# 1. Abrir JMeter
cd C:\jmeter\bin
.\jmeter.bat

# 2. Crear nuevo Test Plan
# 3. Seguir la Guía de JMeter (Guia_JMeter_Pruebas_Seguridad.md)
# 4. Ejecutar prueba de login básica
```

---

## 📊 Flujo de Trabajo

```
1. PLANIFICACIÓN
   ├── Leer Plan_Pruebas_Seguridad.md
   ├── Identificar pruebas prioritarias
   └── Preparar ambiente de pruebas

2. PREPARACIÓN
   ├── Configurar herramientas (JMeter, ZAP)
   ├── Crear datos de prueba
   └── Preparar archivos CSV con payloads

3. EJECUCIÓN
   ├── Seguir Casos_Prueba_Detallados.md
   ├── Ejecutar pruebas automatizadas (JMeter)
   ├── Ejecutar pruebas manuales
   └── Documentar resultados en tiempo real

4. ANÁLISIS
   ├── Revisar resultados de JMeter
   ├── Analizar logs del sistema
   ├── Identificar vulnerabilidades
   └── Clasificar por severidad

5. DOCUMENTACIÓN
   ├── Completar Plantilla_Reporte_Ejecucion.md
   ├── Actualizar Matriz_Trazabilidad.md
   ├── Capturar evidencias (screenshots, logs)
   └── Generar reporte final

6. SEGUIMIENTO
   ├── Reportar defectos al equipo
   ├── Priorizar correcciones
   └── Planificar re-testing
```

---

## 🎯 Casos de Uso Principales

### Caso 1: Prueba Rápida de Seguridad (1 hora)

**Objetivo**: Validación rápida de vulnerabilidades críticas

**Pruebas a ejecutar**:
- PS-003: Protección contra fuerza bruta
- PS-010: SQL Injection en login
- PS-016: Protección CSRF
- PS-022: Validación de archivos

**Herramienta**: Manual + JMeter básico

---

### Caso 2: Auditoría Completa (2-3 días)

**Objetivo**: Evaluación exhaustiva de seguridad

**Pruebas a ejecutar**: Todas (29 casos)

**Herramientas**: JMeter + OWASP ZAP + Manual

**Entregables**:
- Reporte de ejecución completo
- Matriz de trazabilidad actualizada
- Lista de vulnerabilidades priorizadas
- Recomendaciones de corrección

---

### Caso 3: Pruebas de Regresión (4 horas)

**Objetivo**: Verificar que correcciones no introdujeron nuevos problemas

**Pruebas a ejecutar**: Casos relacionados con defectos corregidos

**Herramienta**: Scripts JMeter guardados

---

## 📁 Estructura de Archivos Recomendada

```
test/seguridad/
├── README.md (este archivo)
├── Plan_Pruebas_Seguridad.md
├── Casos_Prueba_Detallados.md
├── Plantilla_Reporte_Ejecucion.md
├── Matriz_Trazabilidad.md
├── Guia_JMeter_Pruebas_Seguridad.md
│
├── jmeter/
│   ├── tests/
│   │   ├── login_security_test.jmx
│   │   ├── sql_injection_test.jmx
│   │   ├── xss_test.jmx
│   │   ├── brute_force_test.jmx
│   │   └── load_test.jmx
│   │
│   ├── data/
│   │   ├── passwords.csv
│   │   ├── sql_payloads.csv
│   │   ├── xss_payloads.csv
│   │   └── usuarios_roles.csv
│   │
│   └── results/
│       ├── 2025-11-02_login_results.jtl
│       └── reports/
│
├── evidencias/
│   ├── screenshots/
│   │   ├── vuln_001_brute_force.png
│   │   ├── vuln_002_csrf.png
│   │   └── ...
│   │
│   └── logs/
│       ├── apache_access.log
│       ├── apache_error.log
│       └── bitacora_sistema.log
│
└── reportes/
    ├── Reporte_Ejecucion_2025-11-02.md
    ├── Reporte_Vulnerabilidades.md
    └── Recomendaciones_Seguridad.md
```

---

## 🔍 Categorías de Pruebas

### Autenticación y Sesiones (7 pruebas)
- Validación de credenciales
- Protección contra fuerza bruta
- Gestión de sesiones
- Logout seguro
- CAPTCHA
- Recuperación de contraseña

### Control de Acceso (4 pruebas)
- Autorización por roles
- Escalación de privilegios
- Acceso directo a recursos

### Inyección (6 pruebas)
- SQL Injection (login, CRUD, búsqueda)
- XSS (reflejado, almacenado, URL)

### CSRF (2 pruebas)
- Protección en formularios
- Protección en operaciones críticas

### Configuración (4 pruebas)
- Exposición de información
- Listado de directorios
- Archivos sensibles
- Headers de seguridad

### Carga de Archivos (3 pruebas)
- Validación de tipo
- Validación de tamaño
- Prevención de ejecución

### Rendimiento (2 pruebas)
- Resistencia a carga
- Rate limiting

---

## 🛠️ Herramientas Utilizadas

| Herramienta | Versión | Propósito | Descarga |
|-------------|---------|-----------|----------|
| **Apache JMeter** | 5.6+ | Pruebas automatizadas | [Link](https://jmeter.apache.org/) |
| **OWASP ZAP** | 2.14+ | Escaneo de vulnerabilidades | [Link](https://www.zaproxy.org/) |
| **Burp Suite Community** | Latest | Proxy e interceptación | [Link](https://portswigger.net/burp) |
| **Postman** | Latest | Pruebas de API | [Link](https://www.postman.com/) |
| **SQLMap** | 1.7+ | Detección SQL Injection | [Link](https://sqlmap.org/) |

---

## 📝 Plantillas y Formatos

### Formato de Caso de Prueba

```markdown
## PS-XXX: [Nombre]

| Campo | Valor |
|-------|-------|
| **ID** | PS-XXX |
| **Categoría** | [OWASP] |
| **Prioridad** | [Nivel] |

### Precondiciones
[Estado requerido]

### Pasos
1. [Paso 1]
2. [Paso 2]

### Resultado Esperado
- ✅ [Comportamiento seguro]

### Resultado Obtenido
_[A completar]_

### Estado
- [ ] Pasó
- [ ] Falló
```

### Formato de Defecto

```markdown
## DEF-XXX: [Título]

**Severidad**: [Crítica/Alta/Media/Baja]
**Módulo**: [Nombre del módulo]
**Prueba**: PS-XXX

**Descripción**: [Detalle del problema]

**Pasos para Reproducir**:
1. [Paso 1]
2. [Paso 2]

**Resultado Actual**: [Comportamiento inseguro]
**Resultado Esperado**: [Comportamiento seguro]

**Impacto**: [Consecuencias]
**Recomendación**: [Solución propuesta]
```

---

## 🎓 Recursos de Aprendizaje

### OWASP Top 10 (2021)
1. A01:2021 - Broken Access Control
2. A02:2021 - Cryptographic Failures
3. A03:2021 - Injection
4. A04:2021 - Insecure Design
5. A05:2021 - Security Misconfiguration
6. A06:2021 - Vulnerable and Outdated Components
7. A07:2021 - Identification and Authentication Failures
8. A08:2021 - Software and Data Integrity Failures
9. A09:2021 - Security Logging and Monitoring Failures
10. A10:2021 - Server-Side Request Forgery (SSRF)

### Enlaces Útiles
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [JMeter Documentation](https://jmeter.apache.org/usermanual/)
- [OWASP ZAP User Guide](https://www.zaproxy.org/docs/)

---

## ⚠️ Advertencias Importantes

### ⚠️ SOLO EN AMBIENTE DE PRUEBAS
- **NUNCA** ejecutar pruebas de seguridad en producción
- Usar solo datos de prueba, no datos reales
- Informar al equipo antes de ejecutar pruebas de carga

### ⚠️ LEGALIDAD
- Solo probar sistemas propios o con autorización explícita
- Documentar todas las aprobaciones
- Respetar límites éticos y legales

### ⚠️ BACKUP
- Realizar backup de la base de datos antes de pruebas
- Tener plan de rollback preparado
- Documentar estado inicial del sistema

---

## 📞 Contacto y Soporte

**Responsable de Pruebas**: [Nombre]  
**Email**: [email@ejemplo.com]  
**Última Actualización**: Noviembre 2025

---

## 📈 Métricas de Progreso

### Estado Actual
```
Total de Pruebas: 29
├── Planificadas: 29 (100%)
├── Ejecutadas: 0 (0%)
├── Pasadas: 0 (0%)
└── Falladas: 0 (0%)

Vulnerabilidades Encontradas: 0
├── Críticas: 0
├── Altas: 0
├── Medias: 0
└── Bajas: 0
```

### Próximos Pasos
1. [ ] Configurar ambiente de pruebas
2. [ ] Instalar herramientas (JMeter, ZAP)
3. [ ] Crear usuarios de prueba
4. [ ] Preparar datos de prueba (CSV)
5. [ ] Ejecutar primera prueba piloto
6. [ ] Documentar resultados iniciales

---

## 🔄 Historial de Versiones

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2025-11-02 | Creación inicial de documentación |

---

**¡Buena suerte con las pruebas de seguridad! 🔒**
