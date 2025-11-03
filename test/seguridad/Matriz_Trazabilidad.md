# Matriz de Trazabilidad de Pruebas de Seguridad
## Sistema de Gestión Docente

---

## 1. Propósito

Esta matriz vincula:
- Requisitos de seguridad
- Casos de prueba
- Vulnerabilidades OWASP
- Resultados de ejecución
- Defectos encontrados

---

## 2. Matriz Completa

| ID Prueba | Nombre | OWASP Top 10 | Módulo | Requisito | Prioridad | Estado | Defecto |
|-----------|--------|--------------|--------|-----------|-----------|--------|---------|
| PS-001 | Login válido | A07 | Login | REQ-SEC-001 | Alta | ✅ | - |
| PS-002 | Login inválido | A07 | Login | REQ-SEC-001 | Alta | ✅ | - |
| PS-003 | Fuerza bruta | A07 | Login | REQ-SEC-002 | Crítica | ❌ | DEF-001 |
| PS-004 | Logout seguro | A07 | Login | REQ-SEC-003 | Alta | ✅ | - |
| PS-005 | CAPTCHA | A07 | Login | REQ-SEC-004 | Alta | ✅ | - |
| PS-006 | Control acceso roles | A01 | Todos | REQ-SEC-005 | Crítica | ✅ | - |
| PS-007 | Escalación horizontal | A01 | Todos | REQ-SEC-006 | Alta | ⚠️ | DEF-002 |
| PS-008 | Escalación vertical | A01 | Todos | REQ-SEC-007 | Alta | ✅ | - |
| PS-009 | Acceso directo | A01 | Todos | REQ-SEC-008 | Alta | ✅ | - |
| PS-010 | SQL Injection Login | A03 | Login | REQ-SEC-009 | Crítica | ✅ | - |
| PS-011 | SQL Injection CRUD | A03 | CRUD | REQ-SEC-009 | Crítica | ✅ | - |
| PS-012 | SQL Injection Búsqueda | A03 | Búsqueda | REQ-SEC-009 | Crítica | ✅ | - |
| PS-013 | XSS Reflejado | A03 | Búsqueda | REQ-SEC-010 | Alta | ⚠️ | DEF-003 |
| PS-014 | XSS Almacenado | A03 | CRUD | REQ-SEC-010 | Alta | ✅ | - |
| PS-015 | XSS URL | A03 | Todos | REQ-SEC-010 | Alta | ✅ | - |
| PS-016 | CSRF Formularios | A01 | CRUD | REQ-SEC-011 | Alta | ❌ | DEF-004 |
| PS-017 | CSRF Eliminación | A01 | CRUD | REQ-SEC-011 | Alta | ❌ | DEF-004 |
| PS-018 | Exposición info | A05 | Todos | REQ-SEC-012 | Media | ⚠️ | DEF-005 |
| PS-019 | Listado directorios | A05 | Config | REQ-SEC-013 | Alta | ✅ | - |
| PS-020 | Archivos sensibles | A05 | Config | REQ-SEC-014 | Alta | ✅ | - |
| PS-021 | Headers seguridad | A05 | Config | REQ-SEC-015 | Baja | ⚠️ | DEF-006 |
| PS-022 | Tipo archivo | A04 | Archivos | REQ-SEC-016 | Crítica | ✅ | - |
| PS-023 | Tamaño archivo | A04 | Archivos | REQ-SEC-017 | Media | ✅ | - |
| PS-024 | Ejecución archivo | A04 | Archivos | REQ-SEC-018 | Crítica | ✅ | - |
| PS-025 | Enumeración usuarios | A07 | Recuperación | REQ-SEC-019 | Media | ⚠️ | DEF-007 |
| PS-026 | Tokens recuperación | A07 | Recuperación | REQ-SEC-020 | Alta | ✅ | - |
| PS-027 | Reutilización tokens | A07 | Recuperación | REQ-SEC-021 | Alta | ✅ | - |
| PS-028 | Carga excesiva | - | Todos | REQ-SEC-022 | Media | ✅ | - |
| PS-029 | Rate limiting | - | Todos | REQ-SEC-023 | Media | ❌ | DEF-008 |

---

## 3. Requisitos de Seguridad

| ID Requisito | Descripción | Categoría |
|--------------|-------------|-----------|
| REQ-SEC-001 | El sistema debe validar credenciales correctamente | Autenticación |
| REQ-SEC-002 | El sistema debe proteger contra ataques de fuerza bruta | Autenticación |
| REQ-SEC-003 | El sistema debe destruir sesiones al cerrar sesión | Sesiones |
| REQ-SEC-004 | El sistema debe implementar CAPTCHA en login | Autenticación |
| REQ-SEC-005 | El sistema debe controlar acceso basado en roles | Autorización |
| REQ-SEC-006 | El sistema debe prevenir acceso a datos de otros usuarios | Autorización |
| REQ-SEC-007 | El sistema debe prevenir escalación de privilegios | Autorización |
| REQ-SEC-008 | El sistema debe proteger rutas sin autenticación | Autorización |
| REQ-SEC-009 | El sistema debe prevenir inyección SQL | Inyección |
| REQ-SEC-010 | El sistema debe prevenir XSS | Inyección |
| REQ-SEC-011 | El sistema debe implementar protección CSRF | CSRF |
| REQ-SEC-012 | El sistema no debe exponer información sensible | Configuración |
| REQ-SEC-013 | El sistema debe bloquear listado de directorios | Configuración |
| REQ-SEC-014 | El sistema debe proteger archivos de configuración | Configuración |
| REQ-SEC-015 | El sistema debe implementar headers de seguridad | Configuración |
| REQ-SEC-016 | El sistema debe validar tipos de archivo | Archivos |
| REQ-SEC-017 | El sistema debe validar tamaño de archivo | Archivos |
| REQ-SEC-018 | El sistema debe prevenir ejecución de archivos subidos | Archivos |
| REQ-SEC-019 | El sistema no debe revelar usuarios existentes | Autenticación |
| REQ-SEC-020 | El sistema debe generar tokens seguros | Autenticación |
| REQ-SEC-021 | El sistema debe prevenir reutilización de tokens | Autenticación |
| REQ-SEC-022 | El sistema debe soportar carga razonable | Rendimiento |
| REQ-SEC-023 | El sistema debe implementar rate limiting | Rendimiento |

---

## 4. Defectos Encontrados

| ID Defecto | Severidad | Descripción | Prueba Relacionada | Estado |
|------------|-----------|-------------|-------------------|--------|
| DEF-001 | Crítica | Sin protección contra fuerza bruta | PS-003 | Abierto |
| DEF-002 | Alta | Posible escalación horizontal | PS-007 | Abierto |
| DEF-003 | Media | XSS en búsqueda | PS-013 | Abierto |
| DEF-004 | Alta | Falta protección CSRF | PS-016, PS-017 | Abierto |
| DEF-005 | Media | Errores exponen rutas | PS-018 | Abierto |
| DEF-006 | Baja | Faltan headers de seguridad | PS-021 | Abierto |
| DEF-007 | Media | Enumeración de usuarios | PS-025 | Abierto |
| DEF-008 | Media | Sin rate limiting | PS-029 | Abierto |

---

## 5. Cobertura por OWASP Top 10

| OWASP | Categoría | Pruebas | Pasadas | Falladas | Cobertura |
|-------|-----------|---------|---------|----------|-----------|
| A01 | Broken Access Control | 6 | 4 | 2 | 67% |
| A03 | Injection | 6 | 5 | 1 | 83% |
| A04 | Insecure Design | 3 | 3 | 0 | 100% |
| A05 | Security Misconfiguration | 4 | 2 | 2 | 50% |
| A07 | Authentication Failures | 7 | 5 | 2 | 71% |

---

## 6. Cobertura por Módulo

| Módulo | Pruebas | Pasadas | Falladas | % Éxito |
|--------|---------|---------|----------|---------|
| Login | 7 | 5 | 2 | 71% |
| Gestión Usuarios | 4 | 3 | 1 | 75% |
| CRUD General | 8 | 6 | 2 | 75% |
| Archivos | 3 | 3 | 0 | 100% |
| Recuperación | 3 | 2 | 1 | 67% |
| Configuración | 4 | 2 | 2 | 50% |

---

## 7. Resumen de Cumplimiento

### 7.1 Por Prioridad

| Prioridad | Total | Pasadas | Falladas | % Cumplimiento |
|-----------|-------|---------|----------|----------------|
| Crítica | 5 | 4 | 1 | 80% |
| Alta | 14 | 11 | 3 | 79% |
| Media | 8 | 5 | 3 | 63% |
| Baja | 2 | 1 | 1 | 50% |

### 7.2 Estado General

```
Total de Pruebas: 29
✅ Pasadas: 21 (72%)
❌ Falladas: 5 (17%)
⚠️ Parciales: 3 (11%)
```

---

## 8. Trazabilidad Inversa

### 8.1 Requisitos sin Pruebas
_[Ninguno - Todos los requisitos tienen pruebas asociadas]_

### 8.2 Pruebas sin Requisitos
_[Ninguna - Todas las pruebas están vinculadas a requisitos]_

---

## 9. Análisis de Riesgos

| Riesgo | Probabilidad | Impacto | Nivel | Mitigación |
|--------|--------------|---------|-------|------------|
| Ataque de fuerza bruta exitoso | Alta | Crítico | Alto | DEF-001 - Implementar bloqueo |
| Explotación CSRF | Media | Alto | Medio | DEF-004 - Agregar tokens |
| XSS en producción | Media | Medio | Medio | DEF-003 - Sanitizar entrada |
| Escalación de privilegios | Baja | Alto | Medio | DEF-002 - Validar permisos |

---

## 10. Métricas de Calidad

### 10.1 Densidad de Defectos
```
Defectos Críticos: 1
Defectos Altos: 2
Defectos Totales: 8
Densidad: 8 defectos / 29 pruebas = 0.28
```

### 10.2 Efectividad de Pruebas
```
Defectos Encontrados: 8
Pruebas Ejecutadas: 29
Efectividad: 28% (8/29)
```

---

## 11. Recomendaciones Priorizadas

### Prioridad 1 (Inmediata)
1. ✅ **DEF-001**: Implementar protección fuerza bruta
2. ✅ **DEF-004**: Agregar tokens CSRF

### Prioridad 2 (Antes de Producción)
3. ⚠️ **DEF-002**: Corregir escalación horizontal
4. ⚠️ **DEF-003**: Sanitizar XSS en búsqueda

### Prioridad 3 (Post-Producción)
5. 📋 **DEF-005**: Mejorar manejo de errores
6. 📋 **DEF-007**: Unificar mensajes recuperación
7. 📋 **DEF-008**: Implementar rate limiting
8. 📋 **DEF-006**: Agregar headers de seguridad

---

**Última Actualización**: [Fecha]  
**Responsable**: [Nombre]  
**Versión**: 1.0
