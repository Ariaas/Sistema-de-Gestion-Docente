# Recomendaciones de Seguridad
## Sistema de Gestión Docente

**Fecha:** Noviembre 2025  
**Versión:** 1.0  
**Basado en:** Resultados de Pruebas de Seguridad JMeter

---

## 📋 Índice

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Hallazgos de Seguridad](#hallazgos-de-seguridad)
3. [Recomendaciones Prioritarias](#recomendaciones-prioritarias)
4. [Implementaciones Sugeridas](#implementaciones-sugeridas)
5. [Plan de Acción](#plan-de-acción)

---

## 🎯 Resumen Ejecutivo

### Estado Actual de Seguridad

El Sistema de Gestión Docente ha sido sometido a pruebas de seguridad exhaustivas utilizando Apache JMeter. Los resultados muestran:

| Prueba | Resultado | Estado |
|--------|-----------|--------|
| **SQL Injection** | 38/38 payloads bloqueados | ✅ **SEGURO** |
| **Brute Force** | 22/22 intentos rechazados | ⚠️ **MEJORABLE** |
| **XSS** | Pendiente | 🔄 En evaluación |
| **Validación Login** | Credenciales correctas funcionan | ✅ **CORRECTO** |

### Nivel de Seguridad Global: **BUENO** ⭐⭐⭐⭐☆

---

## 🔍 Hallazgos de Seguridad

### ✅ Fortalezas Identificadas

#### 1. Protección contra SQL Injection
- **Estado:** ✅ **EXCELENTE**
- **Evidencia:** 38 payloads SQL bloqueados exitosamente
- **Detalles:**
  - No se exponen errores SQL en las respuestas
  - No se permite bypass de autenticación mediante inyección
  - Respuestas consistentes (535 bytes)
  - Tiempos de respuesta rápidos (8-29ms)

#### 2. Validación de Credenciales
- **Estado:** ✅ **CORRECTO**
- **Evidencia:** Login con credenciales válidas funciona correctamente
- **Detalles:**
  - Usuario: `LigiaDuran` / Contraseña: `Carolina.16`
  - Respuesta JSON correcta: `{"resultado":"ok"}`
  - Sesión establecida correctamente

#### 3. Rechazo de Credenciales Inválidas
- **Estado:** ✅ **CORRECTO**
- **Evidencia:** 22 contraseñas comunes rechazadas
- **Detalles:**
  - Contraseñas débiles bloqueadas (Admin123, password, 12345678, etc.)
  - Sin bypass de autenticación
  - Respuestas consistentes (493 bytes)

---

### ⚠️ Áreas de Mejora Identificadas

#### 1. **CRÍTICO: Ausencia de Límite de Intentos de Login**

**Descripción del Problema:**
- El sistema permite **intentos ilimitados** de inicio de sesión
- Un atacante puede realizar ataques de fuerza bruta sin restricciones
- No hay bloqueo temporal ni rate limiting implementado

**Evidencia:**
- Test de Brute Force: 22 intentos consecutivos sin bloqueo
- Throughput: 57.6 intentos/minuto sin restricción
- No se detectó mecanismo de protección contra intentos repetidos

**Riesgo:**
- **Nivel:** 🔴 **ALTO**
- **Impacto:** Un atacante con tiempo suficiente podría eventualmente encontrar credenciales válidas
- **Probabilidad:** Media (requiere tiempo, pero es factible)

**Recomendación:**
Implementar sistema de límite de intentos fallidos.

---

#### 2. **MEDIO: Falta de Registro de Intentos Fallidos**

**Descripción del Problema:**
- No se registran logs de intentos de login fallidos
- Dificulta la detección de ataques en curso
- No hay alertas de seguridad automáticas

**Riesgo:**
- **Nivel:** 🟡 **MEDIO**
- **Impacto:** Imposibilidad de detectar y responder a ataques en tiempo real

**Recomendación:**
Implementar sistema de logging y monitoreo.

---

#### 3. **BAJO: Ausencia de CAPTCHA Progresivo**

**Descripción del Problema:**
- CAPTCHA solo en login normal, no en endpoint de pruebas
- No hay CAPTCHA progresivo después de intentos fallidos

**Riesgo:**
- **Nivel:** 🟢 **BAJO**
- **Impacto:** Facilita automatización de ataques

**Recomendación:**
Implementar CAPTCHA progresivo.

---

## 🚀 Recomendaciones Prioritarias

### Prioridad 1: Implementar Límite de Intentos de Login

#### Objetivo
Prevenir ataques de fuerza bruta mediante bloqueo temporal de cuentas o IPs después de múltiples intentos fallidos.

#### Especificaciones Técnicas

**Opción A: Bloqueo por Cuenta de Usuario**
```
- Máximo de intentos: 5 intentos fallidos
- Tiempo de bloqueo: 15 minutos
- Reseteo: Automático después del tiempo de bloqueo
- Notificación: Email al usuario sobre intento de acceso sospechoso
```

**Opción B: Bloqueo por Dirección IP**
```
- Máximo de intentos: 10 intentos fallidos (cualquier usuario)
- Tiempo de bloqueo: 30 minutos
- Reseteo: Automático después del tiempo de bloqueo
- Whitelist: IPs de administradores exentas
```

**Opción C: Combinada (RECOMENDADA)**
```
- Por cuenta: 5 intentos → Bloqueo 15 minutos
- Por IP: 10 intentos → Bloqueo 30 minutos
- Escalamiento: Bloqueos incrementales (15min → 1h → 24h)
```

#### Implementación Sugerida

**1. Tabla de Base de Datos:**
```sql
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    ip_address VARCHAR(45),
    attempt_time DATETIME,
    success BOOLEAN,
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_time (attempt_time)
);

CREATE TABLE blocked_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    blocked_until DATETIME,
    reason VARCHAR(255),
    attempts_count INT
);

CREATE TABLE blocked_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) UNIQUE,
    blocked_until DATETIME,
    attempts_count INT
);
```

**2. Lógica en PHP (login.php):**
```php
function checkLoginAttempts($username, $ip) {
    // Verificar si la cuenta está bloqueada
    $blockedAccount = checkBlockedAccount($username);
    if ($blockedAccount && $blockedAccount['blocked_until'] > date('Y-m-d H:i:s')) {
        $remainingTime = strtotime($blockedAccount['blocked_until']) - time();
        return [
            'blocked' => true,
            'reason' => 'account',
            'remaining_minutes' => ceil($remainingTime / 60)
        ];
    }
    
    // Verificar si la IP está bloqueada
    $blockedIP = checkBlockedIP($ip);
    if ($blockedIP && $blockedIP['blocked_until'] > date('Y-m-d H:i:s')) {
        $remainingTime = strtotime($blockedIP['blocked_until']) - time();
        return [
            'blocked' => true,
            'reason' => 'ip',
            'remaining_minutes' => ceil($remainingTime / 60)
        ];
    }
    
    // Contar intentos recientes (últimos 15 minutos)
    $recentAttempts = countRecentAttempts($username, $ip, 15);
    
    if ($recentAttempts['by_account'] >= 5) {
        blockAccount($username, 15); // 15 minutos
        return ['blocked' => true, 'reason' => 'too_many_attempts'];
    }
    
    if ($recentAttempts['by_ip'] >= 10) {
        blockIP($ip, 30); // 30 minutos
        return ['blocked' => true, 'reason' => 'too_many_attempts'];
    }
    
    return ['blocked' => false];
}

function registerLoginAttempt($username, $ip, $success) {
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time, success) VALUES (?, ?, NOW(), ?)");
    $stmt->execute([$username, $ip, $success ? 1 : 0]);
}

function blockAccount($username, $minutes) {
    $db = getDBConnection();
    $blockedUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
    $stmt = $db->prepare("INSERT INTO blocked_accounts (username, blocked_until, reason, attempts_count) 
                          VALUES (?, ?, 'Too many failed attempts', 1)
                          ON DUPLICATE KEY UPDATE 
                          blocked_until = ?, 
                          attempts_count = attempts_count + 1");
    $stmt->execute([$username, $blockedUntil, $blockedUntil]);
    
    // Enviar email de notificación
    sendSecurityAlert($username, 'account_blocked', $minutes);
}

function blockIP($ip, $minutes) {
    $db = getDBConnection();
    $blockedUntil = date('Y-m-d H:i:s', strtotime("+$minutes minutes"));
    $stmt = $db->prepare("INSERT INTO blocked_ips (ip_address, blocked_until, attempts_count) 
                          VALUES (?, ?, 1)
                          ON DUPLICATE KEY UPDATE 
                          blocked_until = ?, 
                          attempts_count = attempts_count + 1");
    $stmt->execute([$ip, $blockedUntil, $blockedUntil]);
}
```

**3. Integración en el Controlador:**
```php
// En controller/login.php - Acción 'ingresar'
if ($h == 'ingresar') {
    $username = $_POST['usu_usuario'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Verificar bloqueos
    $attemptCheck = checkLoginAttempts($username, $ip);
    if ($attemptCheck['blocked']) {
        echo json_encode([
            'resultado' => 'error',
            'mensaje' => 'Cuenta bloqueada temporalmente por múltiples intentos fallidos. Intente nuevamente en ' . $attemptCheck['remaining_minutes'] . ' minutos.'
        ]);
        exit;
    }
    
    // Proceso de login normal
    $o->set_nombreUsuario($username);
    $o->set_contraseniaUsuario($_POST['usu_clave']);
    $m = $o->existe();
    
    // Registrar intento
    registerLoginAttempt($username, $ip, $m['resultado'] == 'existe');
    
    if ($m['resultado'] == 'existe') {
        // Login exitoso - limpiar intentos
        clearLoginAttempts($username, $ip);
        // ... resto del código de login exitoso
    } else {
        // Login fallido
        echo json_encode([
            'resultado' => 'error',
            'mensaje' => 'Credenciales incorrectas'
        ]);
    }
}
```

#### Beneficios Esperados
- ✅ Prevención de ataques de fuerza bruta
- ✅ Protección de cuentas de usuario
- ✅ Reducción de carga en el servidor
- ✅ Cumplimiento con mejores prácticas de seguridad (OWASP)

#### Métricas de Éxito
- Reducción del 95% en intentos de fuerza bruta exitosos
- Tiempo promedio de bloqueo: 15-30 minutos
- Tasa de falsos positivos: < 1%

---

### Prioridad 2: Sistema de Logging y Monitoreo

#### Objetivo
Registrar y monitorear eventos de seguridad para detección temprana de ataques.

#### Implementación

**1. Tabla de Logs:**
```sql
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50),
    username VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,
    event_data JSON,
    severity ENUM('info', 'warning', 'critical'),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (event_type),
    INDEX idx_username (username),
    INDEX idx_ip (ip_address),
    INDEX idx_severity (severity),
    INDEX idx_created (created_at)
);
```

**2. Eventos a Registrar:**
- Login exitoso
- Login fallido
- Cuenta bloqueada
- IP bloqueada
- Intento de SQL Injection detectado
- Intento de XSS detectado
- Cambio de contraseña
- Acceso a recursos protegidos

**3. Dashboard de Seguridad:**
- Gráfico de intentos de login (exitosos vs fallidos)
- Lista de IPs bloqueadas
- Alertas de seguridad en tiempo real
- Reporte diario por email

---

### Prioridad 3: CAPTCHA Progresivo

#### Objetivo
Dificultar la automatización de ataques mediante CAPTCHA adaptativo.

#### Implementación
```
- Intento 1-2: Sin CAPTCHA
- Intento 3-4: CAPTCHA simple (reCAPTCHA v2)
- Intento 5+: CAPTCHA complejo + Bloqueo temporal
```

---

## 📊 Plan de Acción

### Fase 1: Implementación Inmediata (1-2 semanas)
- [ ] Crear tablas de base de datos para intentos de login
- [ ] Implementar función de verificación de bloqueos
- [ ] Integrar en controlador de login
- [ ] Pruebas unitarias

### Fase 2: Monitoreo y Logging (2-3 semanas)
- [ ] Crear tabla de security_logs
- [ ] Implementar funciones de logging
- [ ] Crear dashboard básico de seguridad
- [ ] Configurar alertas por email

### Fase 3: Mejoras Avanzadas (3-4 semanas)
- [ ] Implementar CAPTCHA progresivo
- [ ] Sistema de whitelist para IPs confiables
- [ ] Análisis de patrones de ataque
- [ ] Reportes automáticos de seguridad

---

## 📈 Métricas de Seguimiento

### KPIs de Seguridad
| Métrica | Valor Actual | Objetivo | Plazo |
|---------|--------------|----------|-------|
| Intentos de fuerza bruta bloqueados | 0% | 95% | 1 mes |
| Tiempo promedio de detección de ataque | N/A | < 5 min | 2 meses |
| Falsos positivos en bloqueos | N/A | < 1% | 1 mes |
| Cobertura de logging | 0% | 100% | 2 meses |

---

## 📚 Referencias

### Estándares y Mejores Prácticas
- OWASP Top 10 2021
- NIST Cybersecurity Framework
- ISO/IEC 27001:2013

### Documentación Relacionada
- `Casos_Prueba_Detallados.md` - Casos de prueba ejecutados
- `INICIO_RAPIDO.md` - Guía de ejecución de pruebas
- `tests/README.md` - Documentación de scripts JMeter

---

## 🔄 Historial de Revisiones

| Versión | Fecha | Cambios | Autor |
|---------|-------|---------|-------|
| 1.0 | Nov 2025 | Documento inicial basado en resultados de pruebas | Equipo de Seguridad |

---

## 📞 Contacto

Para consultas sobre estas recomendaciones o su implementación, contactar al equipo de seguridad del proyecto.

---

**Última actualización:** Noviembre 2025  
**Estado:** Pendiente de implementación
