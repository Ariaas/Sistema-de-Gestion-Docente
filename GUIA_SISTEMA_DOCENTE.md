# 📚 Guía del Sistema de Gestión Docente

## 🎯 ¿Qué es este sistema?

Este es un **Sistema de Gestión Académica Departamental** desarrollado en PHP que permite administrar:

- 👨‍🏫 **Docentes** y su información académica
- 📅 **Horarios** de clases y actividades docentes
- 📖 **Unidades Curriculares** (materias/asignaturas)
- 🏗️ **Malla Curricular** y estructura académica
- 📊 **Reportes** académicos y administrativos
- 👥 **Usuarios** del sistema con diferentes permisos
- 🏛️ **Espacios físicos** (aulas, laboratorios)

## 🏗️ Arquitectura del Sistema

### Estructura MVC (Model-View-Controller)
```
📁 departamento/
├── 🎮 controller/     # Lógica de negocio
├── 📊 model/          # Acceso a datos y base de datos
├── 🖼️ views/          # Interfaces de usuario
├── 🌐 public/         # Archivos estáticos (CSS, JS, imágenes)
├── ⚙️ config/         # Configuraciones del sistema
└── 🗃️ database/       # Scripts de base de datos
```

### Tecnologías Utilizadas
- **Backend**: PHP 8.0+ con PDO
- **Base de datos**: MySQL/MariaDB
- **Frontend**: Bootstrap 5 + jQuery
- **Reportes PDF**: DOMPDF
- **Notificaciones**: PHPMailer
- **Gestión de dependencias**: Composer

## 🚀 Instalación y Configuración

### 1. Requisitos del Sistema
- PHP 8.0 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Apache/Nginx con mod_rewrite
- Composer

### 2. Instalación Paso a Paso

```bash
# 1. Clonar el proyecto
git clone [URL_DEL_REPOSITORIO] departamento
cd departamento

# 2. Instalar dependencias
composer install

# 3. Configurar base de datos
```

**Edita el archivo `config/config.php`:**
```php
<?php
define('_DB_NAME_', 'db_orgdocente');    // Nombre de tu BD
define('_DB_HOST_', 'localhost');        // Servidor BD
define('_DB_USER_', 'tu_usuario');       // Usuario BD
define('_DB_PASS_', 'tu_contraseña');    // Contraseña BD
date_default_timezone_set('America/Caracas');
```

**Importa la base de datos:**
```bash
mysql -u tu_usuario -p tu_contraseña < database/db_orgdocente.sql
```

### 3. Configuración del Servidor Web

**Apache (.htaccess en la raíz):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?pagina=$1 [QSA,L]
```

## 🔐 Sistema de Usuarios y Permisos

### Tipos de Usuario
El sistema maneja diferentes roles con permisos específicos:

- **Administrador**: Acceso completo al sistema
- **Coordinador**: Gestión académica y reportes
- **Docente**: Consulta de horarios y datos personales
- **Asistente**: Funciones limitadas de apoyo

### Permisos Disponibles
- `Docentes`: Gestionar información de docentes
- `Horario`: Crear y modificar horarios
- `Reportes`: Generar reportes del sistema
- `Malla Curricular`: Administrar plan de estudios
- `Usuarios`: Gestión de usuarios del sistema
- `Bitacora`: Ver logs del sistema
- `Respaldo`: Realizar copias de seguridad
- `Configuracion`: Ajustes del sistema

## 📋 Funcionalidades Principales

### 🏠 Panel Principal
- Dashboard con acceso rápido a funcionalidades
- Notificaciones del sistema
- Resumen de actividades recientes

### 👨‍🏫 Gestión de Docentes
- **Crear/Editar**: Información personal y académica
- **Categorías**: Ordinario, Contratado
- **Dedicación**: Exclusiva, Tiempo Completo, Medio Tiempo
- **Títulos académicos**: Gestión de títulos del docente
- **Coordinaciones**: Asignación a coordinaciones

### 📅 Gestión de Horarios
- **Horarios de clase**: Asignación de UC a horarios
- **Horarios docentes**: Actividades académicas del docente
- **Espacios**: Gestión de aulas y laboratorios
- **Turnos**: Configuración de horarios de trabajo

### 📚 Malla Curricular
- **Unidades Curriculares**: Materias del plan de estudios
- **Trayectos**: Organización por niveles (1, 2, 3, 4, inicial)
- **Ejes**: Clasificación temática de las UC
- **Áreas**: Agrupación por área de conocimiento

### 📊 Sistema de Reportes
- **Reporte de Carga Académica**: Carga de trabajo por docente
- **Reporte de Horarios**: Horarios por docente/UC
- **Reporte Definitivo**: Consolidado general
- **Reporte de Aulas**: Ocupación de espacios
- **Reporte de UC**: Listado de unidades curriculares
- **Transcripción**: Documentos oficiales

### 🔧 Administración
- **Usuarios**: Gestión de accesos al sistema
- **Bitácora**: Logs de actividades del sistema
- **Respaldos**: Copias de seguridad automáticas
- **Configuración**: Parámetros del sistema

## 🗄️ Base de Datos

### Tablas Principales
- `tbl_docente`: Información de docentes
- `tbl_horario`: Horarios de clases
- `tbl_uc`: Unidades curriculares
- `tbl_seccion`: Secciones de clases
- `tbl_espacio`: Aulas y laboratorios
- `tbl_malla`: Malla curricular
- `tbl_usuario`: Usuarios del sistema

### Relaciones Importantes
```sql
-- Docente ← → Horario (muchos a muchos)
docente_horario

-- UC ← → Horario (muchos a muchos)  
uc_horario

-- Docente ← → Título (muchos a muchos)
titulo_docente

-- Sección ← → Horario (muchos a muchos)
seccion_horario
```

## 🛠️ Estructura del Código

### Controladores Principales
- `login.php`: Autenticación de usuarios
- `principal.php`: Dashboard principal
- `docente.php`: Gestión de docentes
- `horario.php`: Gestión de horarios
- `reportes.php`: Generación de reportes

### Modelos de Datos
- `dbconnection.php`: Conexión a base de datos
- `login.php`: Autenticación y sesiones
- `docente.php`: Operaciones CRUD de docentes
- `horario.php`: Gestión de horarios

### Vistas
- Utilizan Bootstrap 5 para diseño responsivo
- Componentes reutilizables en `public/components/`
- JavaScript personalizado para interactividad

## 🔧 Uso del Sistema

### 1. Acceso Inicial
1. Ir a `http://tu-dominio/departamento`
2. Ingresar credenciales de usuario
3. El sistema redirige al dashboard principal

### 2. Navegación
- **Menú superior**: Acceso a funciones principales
- **Gestión**: Submenu para administrar entidades
- **Horarios**: Gestión de horarios y actividades
- **Administración**: Funciones administrativas

### 3. Flujo de Trabajo Típico
1. **Configurar espacios** (aulas, laboratorios)
2. **Registrar docentes** con su información
3. **Crear unidades curriculares** y malla
4. **Asignar horarios** a UC y docentes
5. **Generar reportes** según necesidades

## 🚨 Solución de Problemas Comunes

### Error de Conexión a BD
```bash
# Verificar configuración
cat config/config.php

# Probar conexión manual
mysql -h localhost -u root -p db_orgdocente
```

### Permisos de Archivos
```bash
# Establecer permisos correctos
chmod 755 .
chmod 644 *.php
chmod 777 uploads/ respaldos/
```

### Problemas con Reportes PDF
```bash
# Verificar extensiones PHP
php -m | grep -i gd
php -m | grep -i mbstring

# Limpiar cache de composer
composer clear-cache
composer install
```

### Sesiones no Funcionan
```php
// Verificar en php.ini
session.save_path = "/tmp"
session.auto_start = 0
```

## 📞 Soporte y Mantenimiento

### Logs del Sistema
- **Bitácora**: Dentro del sistema web
- **Logs PHP**: `/var/log/apache2/error.log`
- **Logs MySQL**: `/var/log/mysql/error.log`

### Copias de Seguridad
El sistema incluye funcionalidad automatizada de respaldos:
- Respaldo de base de datos
- Respaldo de archivos subidos
- Programación automática opcional

### Actualizaciones
1. Hacer backup completo
2. Descargar nueva versión
3. Ejecutar scripts de migración si existen
4. Probar funcionalidades críticas

## 🔍 Características Técnicas

### Seguridad
- ✅ Consultas preparadas (PDO)
- ✅ Validación de entrada
- ✅ Control de sesiones
- ✅ Permisos por roles
- ✅ Escape de salida HTML

### Rendimiento
- ✅ Paginación en listados
- ✅ Índices en BD
- ✅ Cache de sesiones
- ✅ Compresión de archivos

### Compatibilidad
- ✅ PHP 8.0+
- ✅ MySQL 5.7+/MariaDB 10.3+
- ✅ Browsers modernos
- ✅ Diseño responsivo (móvil/tablet/desktop)

---

## 📋 Lista de Verificación Rápida

### ✅ Instalación
- [ ] PHP 8.0+ instalado
- [ ] MySQL/MariaDB configurado  
- [ ] Composer instalado
- [ ] Dependencias instaladas (`composer install`)
- [ ] Base de datos importada
- [ ] Configuración en `config/config.php`
- [ ] Permisos de archivos establecidos

### ✅ Primer Uso
- [ ] Acceso al login funciona
- [ ] Dashboard se carga correctamente
- [ ] Crear usuario administrador
- [ ] Configurar espacios básicos
- [ ] Registrar primer docente
- [ ] Probar generación de reportes

---

**¿Necesitas ayuda específica?** 
Revisa la sección de "Solución de Problemas" o verifica los logs del sistema para identificar errores específicos.