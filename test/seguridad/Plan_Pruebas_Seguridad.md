# Plan de Pruebas de Seguridad
## Sistema de Gestión Docente

---

## 1. Información General

| Campo | Descripción |
|-------|-------------|
| **Proyecto** | Sistema de Gestión Docente |
| **Versión** | 1.0 |
| **Fecha de Creación** | Noviembre 2025 |
| **Responsable** | [Nombre del Responsable] |
| **Tecnologías** | PHP, MySQL, JavaScript, Bootstrap |
| **Servidor** | XAMPP (Apache + MySQL) |

---

## 2. Objetivos de las Pruebas

### 2.1 Objetivo General
Evaluar la seguridad del Sistema de Gestión Docente identificando vulnerabilidades y validando los controles de seguridad implementados.

### 2.2 Objetivos Específicos
- Validar mecanismos de autenticación y autorización
- Verificar protección contra inyección SQL
- Comprobar protección contra XSS (Cross-Site Scripting)
- Evaluar gestión de sesiones
- Verificar control de acceso a recursos
- Validar protección CSRF
- Evaluar resistencia a ataques de fuerza bruta
- Verificar manejo seguro de archivos
- Comprobar configuración de seguridad del servidor

---

## 3. Alcance de las Pruebas

### 3.1 Módulos a Probar
- ✅ Módulo de Login y Autenticación
- ✅ Módulo de Gestión de Usuarios
- ✅ Módulo de Gestión de Roles y Permisos
- ✅ Módulo de Eje Integrador
- ✅ Módulo de Docentes
- ✅ Módulo de Malla Curricular
- ✅ Módulo de Reportes
- ✅ Módulo de Backup
- ✅ Módulo de Bitácora
- ✅ Recuperación de Contraseña

### 3.2 Funcionalidades Críticas
1. **Autenticación**: Login, logout, recuperación de contraseña
2. **Autorización**: Control de acceso basado en roles
3. **Gestión de Sesiones**: Creación, validación, expiración
4. **Operaciones CRUD**: Crear, leer, actualizar, eliminar registros
5. **Carga de Archivos**: Validación y almacenamiento seguro
6. **Generación de Reportes**: Acceso y descarga de información

### 3.3 Fuera del Alcance
- Pruebas de infraestructura de red
- Auditoría de código fuente completa
- Pruebas de penetración física
- Análisis de malware

---

## 4. Metodología de Pruebas

### 4.1 Enfoque
Se utilizará una metodología híbrida combinando:
- **Pruebas de Caja Negra**: Sin conocimiento del código fuente
- **Pruebas de Caja Gris**: Con conocimiento parcial de la arquitectura
- **Pruebas Automatizadas**: Usando JMeter y OWASP ZAP
- **Pruebas Manuales**: Validación manual de vulnerabilidades

### 4.2 Estándares y Referencias
- **OWASP Top 10 (2021)**: Principales riesgos de seguridad web
- **OWASP Testing Guide v4**: Metodología de pruebas de seguridad
- **CWE/SANS Top 25**: Errores de software más peligrosos
- **ISO/IEC 27001**: Gestión de seguridad de la información

---

## 5. Herramientas de Prueba

| Herramienta | Propósito | Versión |
|-------------|-----------|---------|
| **Apache JMeter** | Pruebas de carga y seguridad automatizadas | 5.6+ |
| **OWASP ZAP** | Escaneo de vulnerabilidades web | 2.14+ |
| **Burp Suite Community** | Proxy de interceptación y análisis | Latest |
| **Postman** | Pruebas de API y endpoints | Latest |
| **SQLMap** | Detección de inyección SQL | 1.7+ |
| **Browser DevTools** | Análisis de requests/responses | Chrome/Firefox |

---

## 6. Categorías de Pruebas de Seguridad

### 6.1 Autenticación y Gestión de Sesiones (OWASP A07:2021)

#### PS-001: Validación de Credenciales
- **Objetivo**: Verificar que solo credenciales válidas permitan acceso
- **Método**: Pruebas con credenciales válidas e inválidas
- **Herramienta**: JMeter, Manual

#### PS-002: Protección contra Fuerza Bruta
- **Objetivo**: Validar bloqueo de cuenta tras intentos fallidos
- **Método**: Múltiples intentos de login con credenciales incorrectas
- **Herramienta**: JMeter (Thread Group con loops)

#### PS-003: Gestión de Sesiones
- **Objetivo**: Verificar creación, validación y expiración de sesiones
- **Método**: Análisis de cookies de sesión, timeout
- **Herramienta**: Burp Suite, Browser DevTools

#### PS-004: Logout Seguro
- **Objetivo**: Confirmar destrucción completa de sesión al cerrar
- **Método**: Intentar acceder con token de sesión después de logout
- **Herramienta**: Manual, Postman

#### PS-005: Validación de CAPTCHA
- **Objetivo**: Verificar implementación correcta de reCAPTCHA
- **Método**: Intentar login sin resolver CAPTCHA
- **Herramienta**: Manual, Postman

---

### 6.2 Control de Acceso (OWASP A01:2021)

#### PS-006: Autorización Basada en Roles
- **Objetivo**: Validar que usuarios solo accedan según sus permisos
- **Método**: Intentar acceder a módulos sin permisos
- **Herramienta**: Manual, Postman

#### PS-007: Escalación de Privilegios Horizontal
- **Objetivo**: Verificar que usuarios no accedan a datos de otros usuarios
- **Método**: Modificar parámetros de ID en URLs
- **Herramienta**: Burp Suite, Manual

#### PS-008: Escalación de Privilegios Vertical
- **Objetivo**: Confirmar que usuarios no puedan ejecutar acciones de admin
- **Método**: Intentar acceder a funciones administrativas
- **Herramienta**: Manual, Postman

#### PS-009: Acceso Directo a Recursos Protegidos
- **Objetivo**: Validar protección de rutas sin autenticación
- **Método**: Acceder a URLs directamente sin login
- **Herramienta**: Manual, Browser

---

### 6.3 Inyección (OWASP A03:2021)

#### PS-010: Inyección SQL - Formularios de Login
- **Objetivo**: Detectar vulnerabilidades de SQL Injection en login
- **Método**: Payloads SQL maliciosos en campos de usuario/contraseña
- **Herramienta**: SQLMap, JMeter, Manual

#### PS-011: Inyección SQL - Operaciones CRUD
- **Objetivo**: Verificar protección en formularios de registro/edición
- **Método**: Payloads SQL en campos de texto
- **Herramienta**: SQLMap, Burp Suite

#### PS-012: Inyección SQL - Búsquedas y Filtros
- **Objetivo**: Validar sanitización en parámetros de búsqueda
- **Método**: Payloads SQL en parámetros GET/POST
- **Herramienta**: SQLMap, Manual

---

### 6.4 Cross-Site Scripting - XSS (OWASP A03:2021)

#### PS-013: XSS Reflejado
- **Objetivo**: Detectar ejecución de scripts en respuestas inmediatas
- **Método**: Inyectar scripts en campos de formulario
- **Herramienta**: OWASP ZAP, Manual

#### PS-014: XSS Almacenado
- **Objetivo**: Verificar que scripts no se almacenen en BD
- **Método**: Guardar scripts en campos de texto y verificar renderizado
- **Herramienta**: Manual, Burp Suite

#### PS-015: XSS en Parámetros URL
- **Objetivo**: Validar sanitización de parámetros GET
- **Método**: Scripts en parámetros de URL
- **Herramienta**: OWASP ZAP, Manual

---

### 6.5 Cross-Site Request Forgery - CSRF (OWASP A01:2021)

#### PS-016: Protección CSRF en Formularios
- **Objetivo**: Verificar tokens CSRF en operaciones críticas
- **Método**: Enviar requests sin token CSRF válido
- **Herramienta**: Burp Suite, Manual

#### PS-017: CSRF en Operaciones de Eliminación
- **Objetivo**: Validar protección en acciones destructivas
- **Método**: Crear página externa que ejecute eliminación
- **Herramienta**: Manual

---

### 6.6 Configuración de Seguridad (OWASP A05:2021)

#### PS-018: Exposición de Información Sensible
- **Objetivo**: Verificar que no se expongan datos sensibles
- **Método**: Revisar mensajes de error, headers HTTP
- **Herramienta**: Burp Suite, Browser DevTools

#### PS-019: Listado de Directorios
- **Objetivo**: Confirmar que directorios no sean navegables
- **Método**: Acceder a /model/, /controller/, /config/
- **Herramienta**: Browser, Manual

#### PS-020: Archivos Sensibles Expuestos
- **Objetivo**: Validar protección de archivos de configuración
- **Método**: Intentar acceder a .env, config.php, composer.json
- **Herramienta**: Browser, Manual

#### PS-021: Headers de Seguridad HTTP
- **Objetivo**: Verificar presencia de headers de seguridad
- **Método**: Analizar headers: X-Frame-Options, CSP, HSTS, etc.
- **Herramienta**: Browser DevTools, OWASP ZAP

---

### 6.7 Carga de Archivos (OWASP A04:2021)

#### PS-022: Validación de Tipo de Archivo
- **Objetivo**: Verificar que solo se permitan tipos válidos
- **Método**: Intentar subir archivos .php, .exe, .sh
- **Herramienta**: Manual, Postman

#### PS-023: Validación de Tamaño de Archivo
- **Objetivo**: Confirmar límites de tamaño de archivo
- **Método**: Subir archivos excesivamente grandes
- **Herramienta**: Manual, JMeter

#### PS-024: Ejecución de Archivos Subidos
- **Objetivo**: Validar que archivos subidos no sean ejecutables
- **Método**: Subir script PHP y tratar de ejecutarlo
- **Herramienta**: Manual

---

### 6.8 Recuperación de Contraseña

#### PS-025: Enumeración de Usuarios
- **Objetivo**: Verificar que no se revele existencia de usuarios
- **Método**: Solicitar recuperación con usuarios válidos/inválidos
- **Herramienta**: Manual, Postman

#### PS-026: Tokens de Recuperación
- **Objetivo**: Validar generación segura de tokens
- **Método**: Analizar predictibilidad y tiempo de expiración
- **Herramienta**: Manual, Burp Suite

#### PS-027: Reutilización de Tokens
- **Objetivo**: Confirmar que tokens no sean reutilizables
- **Método**: Usar mismo token múltiples veces
- **Herramienta**: Manual, Postman

---

### 6.9 Pruebas de Rendimiento y DoS

#### PS-028: Resistencia a Carga Excesiva
- **Objetivo**: Evaluar comportamiento bajo carga alta
- **Método**: Simular múltiples usuarios concurrentes
- **Herramienta**: JMeter (1000+ threads)

#### PS-029: Rate Limiting
- **Objetivo**: Verificar límites de peticiones por IP
- **Método**: Enviar múltiples requests en corto tiempo
- **Herramienta**: JMeter

---

## 7. Criterios de Aceptación

### 7.1 Niveles de Severidad

| Nivel | Descripción | Acción Requerida |
|-------|-------------|------------------|
| **Crítico** | Permite acceso no autorizado total o pérdida de datos | Corrección inmediata |
| **Alto** | Compromete seguridad significativamente | Corrección antes de producción |
| **Medio** | Vulnerabilidad explotable con dificultad | Corrección planificada |
| **Bajo** | Riesgo menor, mejora recomendada | Corrección opcional |
| **Informativo** | Sin riesgo directo, buena práctica | Considerar mejora |

### 7.2 Criterios de Éxito
- ✅ 0 vulnerabilidades críticas
- ✅ 0 vulnerabilidades altas
- ✅ Máximo 3 vulnerabilidades medias
- ✅ Implementación de headers de seguridad
- ✅ Protección contra OWASP Top 10

---

## 8. Entregables

1. **Plan de Pruebas de Seguridad** (este documento)
2. **Casos de Prueba Detallados** (documento separado)
3. **Scripts de JMeter** (.jmx files)
4. **Reporte de Ejecución de Pruebas**
5. **Reporte de Vulnerabilidades Encontradas**
6. **Matriz de Trazabilidad**
7. **Recomendaciones de Seguridad**
8. **Evidencias** (capturas de pantalla, logs)

---

## 9. Cronograma

| Fase | Actividad | Duración Estimada |
|------|-----------|-------------------|
| 1 | Preparación del ambiente de pruebas | 1 día |
| 2 | Configuración de herramientas | 1 día |
| 3 | Ejecución de pruebas automatizadas | 2 días |
| 4 | Ejecución de pruebas manuales | 3 días |
| 5 | Análisis de resultados | 1 día |
| 6 | Documentación de hallazgos | 2 días |
| 7 | Reporte final y recomendaciones | 1 día |
| **Total** | | **11 días** |

---

## 10. Roles y Responsabilidades

| Rol | Responsabilidad |
|-----|-----------------|
| **Tester de Seguridad** | Ejecutar pruebas, documentar hallazgos |
| **Desarrollador** | Corregir vulnerabilidades encontradas |
| **Líder de Proyecto** | Aprobar plan, revisar reportes |
| **Administrador de BD** | Configurar ambiente de pruebas |

---

## 11. Ambiente de Pruebas

### 11.1 Requisitos
- Servidor XAMPP (Apache + MySQL + PHP)
- Copia de la base de datos de desarrollo
- Usuarios de prueba con diferentes roles
- Navegadores: Chrome, Firefox, Edge
- Herramientas instaladas y configuradas

### 11.2 Datos de Prueba
- Usuarios con roles: Administrador, Coordinador, Docente
- Registros de prueba en todas las tablas
- Archivos de prueba de diferentes tipos y tamaños

---

## 12. Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Caída del servidor durante pruebas | Media | Alto | Realizar backup antes de iniciar |
| Datos de producción afectados | Baja | Crítico | Usar solo ambiente de pruebas |
| Herramientas no disponibles | Baja | Medio | Tener alternativas identificadas |
| Tiempo insuficiente | Media | Medio | Priorizar pruebas críticas |

---

## 13. Aprobaciones

| Rol | Nombre | Firma | Fecha |
|-----|--------|-------|-------|
| Responsable de Pruebas | | | |
| Líder de Proyecto | | | |
| Cliente/Stakeholder | | | |

---

**Versión del Documento**: 1.0  
**Última Actualización**: Noviembre 2025  
**Próxima Revisión**: [Fecha]
