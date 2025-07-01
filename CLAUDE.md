# IDT.gov.co - Instituto Distrital de Turismo

## Descripción General

Este proyecto implementa la infraestructura web para el Instituto Distrital de Turismo de Colombia (IDT.gov.co), una solución gubernamental basada en contenedores Docker que utiliza Drupal 10 como CMS principal con una arquitectura de proxy reverso mediante Traefik.

## Arquitectura del Sistema

### Stack Tecnológico

- **Contenedores**: Docker + Docker Compose
- **Proxy Reverso**: Traefik v2.10.5
- **Servidor Web**: OpenLiteSpeed 1.7.19
- **CMS Principal**: Drupal 10.4.7
- **CMS Legado**: Drupal 8
- **Base de Datos**: MariaDB 10.6.22
- **PHP**: 8.3.21 (Drupal 10) / 7.4 (Drupal 8)

### Componentes Principales

```
Internet → Traefik (80/443) → [nuevo_idt | anterior_idt] → MariaDB
```

#### 1. **Traefik (Proxy Reverso)**
- Punto de entrada único con terminación SSL
- Enrutamiento automático por dominio
- Panel de administración en `traefik.idt.gov.co`
- Redirección automática de `idt.gov.co` a `www.idt.gov.co`

#### 2. **Nuevo IDT (Drupal 10.4.7)**
- Sitio principal en `www.idt.gov.co`
- PHP 8.3.21 con OpenLiteSpeed
- 102 módulos instalados para funcionalidad completa
- Tema personalizado responsive

#### 3. **Anterior IDT (Drupal 8)**
- Sitio legado en `anterior.idt.gov.co`
- Mantenido para migración gradual
- PHP 7.4 compatible

#### 4. **MariaDB**
- Base de datos centralizada para ambos sitios
- Puerto 33069 para acceso externo
- Backups automatizados

## Estructura de Directorios

```
idt.gov.co_proxy/
├── docker-compose.yml                 # Configuración producción
├── docker-compose.initial.yml         # Configuración desarrollo
├── .dockerfiles/                      # Dockerfiles personalizados
│   ├── dockerfile-drupal10            # Container Drupal 10
│   └── dockerfile-drupal8             # Container Drupal 8
├── nuevo_idt/                         # Drupal 10 (Sitio principal)
│   ├── drupal/                        # Instalación Drupal 10.4.7
│   │   ├── core/                      # Core de Drupal
│   │   ├── modules/                   # Módulos contrib y custom
│   │   │   ├── contrib/               # Módulos de la comunidad
│   │   │   └── custom/                # Módulos personalizados
│   │   ├── themes/                    # Temas
│   │   │   ├── contrib/gin/           # Tema admin Gin
│   │   │   └── custom/idt_theme/      # Tema personalizado IDT
│   │   ├── sites/default/             # Configuración del sitio
│   │   │   ├── settings.php           # Configuración principal
│   │   │   └── files/                 # Archivos subidos
│   │   ├── vendor/                    # Dependencias Composer
│   │   ├── composer.json              # Gestión dependencias
│   │   └── libraries/                 # Librerías JavaScript
│   ├── configuraciones/               # Configuraciones servidor
│   │   ├── php.ini                    # Configuración PHP 8.3
│   │   ├── settings.php               # Configuración Drupal
│   │   └── *.txt                      # Documentación técnica
│   └── backup-*.tar.gz                # Backups base de datos
├── traefik/                           # Configuración proxy
│   ├── logs/                          # Logs de acceso y errores
│   ├── certs/                         # Certificados SSL
│   └── dynamic_conf.yml               # Configuración dinámica
└── mariadb/                           # Base de datos
    ├── data/                          # Datos persistentes
    ├── conf/my.cnf                    # Configuración MariaDB
    └── logs/                          # Logs de base de datos
```

## Funcionalidades Principales

### Sitio Web Institucional

#### **Gestión de Contenido**
- **Artículos**: Noticias y comunicados con galerías de imágenes
- **Páginas**: Contenido estático institucional
- **PITs**: Proyectos de Inversión Territorial con mapas interactivos
- **Directivos**: Perfiles del equipo ejecutivo
- **Documentos**: Biblioteca de documentos PDF con búsqueda

#### **Características Técnicas**
- **Diseño Responsive**: Optimizado para dispositivos móviles
- **SEO Avanzado**: Meta tags, sitemaps XML, URLs amigables
- **Rendimiento**: Caché multicapa, lazy loading, optimización WebP
- **Accesibilidad**: Cumple estándares WCAG
- **Multiidioma**: Soporte para internacionalización

### Módulos Drupal Clave

#### **Administración y Experiencia de Usuario**
- `admin_toolbar` - Toolbar administrativo mejorado
- `gin` + `gin_toolbar` - Tema administrativo moderno
- `coffee` - Navegación rápida para administradores

#### **Gestión de Contenido**
- `paragraphs` - Constructor de contenido modular
- `field_group` - Agrupación de campos
- `layout_builder` - Constructor de layouts visuales
- `media_library` - Gestión avanzada de medios

#### **SEO y Rendimiento**
- `metatag` + `schema_metatag` - Meta tags y Schema.org
- `simple_sitemap` - Sitemaps XML automáticos
- `pathauto` - URLs automáticas amigables
- `blazy` - Carga lazy de imágenes
- `imageapi_optimize_webp` - Optimización WebP

#### **Seguridad**
- `csp` - Content Security Policy
- `seckit` - Kit de seguridad XSS/CSRF
- `httpswww` - Redirecciones HTTPS/WWW

#### **Búsqueda y Navegación**
- `search_api` + `search_api_autocomplete` - Búsqueda avanzada
- `better_exposed_filters` - Filtros mejorados
- `easy_breadcrumb` - Breadcrumbs automáticos

## Configuración del Entorno

### Variables de Entorno

```bash
# Base de datos
DB_ROOT_PASSWORD=secure_root_password
DB_NAME=idt_drupal
DB_USER=idt_user
DB_PASSWORD=secure_password
DB_NAME_OLD=idt_drupal_old

# URLs base
DRUPAL_BASE_URL=https://www.idt.gov.co
```

### Configuración PHP 8.3

```ini
# Configuración de memoria y rendimiento
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 128M

# Timezone para Colombia
date.timezone = "America/Bogota"

# OPcache para producción
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000

# 42 extensiones PHP habilitadas incluyendo:
# bcmath, curl, gd, imagick, intl, mbstring, mysql, opcache, etc.
```

### Configuración Traefik

```yaml
# Enrutamiento automático
www.idt.gov.co → nuevo_idt (Drupal 10)
anterior.idt.gov.co → anterior_idt (Drupal 8)
traefik.idt.gov.co → Dashboard Traefik

# Redirecciones
idt.gov.co → www.idt.gov.co (301)
HTTP → HTTPS (301)
```

## Seguridad

### Medidas Implementadas

#### **Nivel de Red**
- Proxy reverso con terminación SSL
- Aislamiento de contenedores via Docker networks
- Certificados SSL automáticos

#### **Nivel de Aplicación**
- Content Security Policy (CSP)
- Protección XSS/CSRF via Security Kit
- Headers de seguridad HTTP
- Funciones PHP peligrosas deshabilitadas

#### **Nivel de Base de Datos**
- Acceso restringido via red Docker
- Credenciales via variables de entorno
- Backups automáticos encriptados

#### **Nivel de Archivos**
- Permisos restrictivos (755/644)
- Upload de archivos validado
- Logs de errores segregados

## Monitoreo y Mantenimiento

### Logs Importantes

```bash
# Logs Traefik
./traefik/logs/traefik.log    # Logs aplicación
./traefik/logs/access.log     # Logs acceso HTTP

# Logs Drupal
./nuevo_idt/drupal/sites/default/files/logs/
/var/log/php_errors.log       # Logs PHP

# Logs MariaDB
./mariadb/logs/               # Logs base de datos
```

## Características del Tema Personalizado

### IDT Theme

```
themes/custom/idt_theme/
├── css/           # Estilos compilados
├── scss/          # Fuentes SCSS
├── js/            # JavaScript personalizado
├── templates/     # Plantillas Twig
├── images/        # Assets gráficos
└── libraries/     # Definición de librerías
```

#### **Características Técnicas**
- **Sistema de Grid**: CSS Grid + Flexbox
- **Componentes**: Arquitectura BEM para CSS
- **JavaScript**: ES6+ con transpilación Babel
- **Librerías**: Swiper.js, AOS (Animate on Scroll)
- **Performance**: CSS/JS minificado y concatenado

#### **Plantillas Personalizadas**
- `page--front.html.twig` - Página de inicio
- `node--article.html.twig` - Artículos de noticias
- `node--pit.html.twig` - Proyectos de inversión
- `views-view--search.html.twig` - Resultados de búsqueda

## Optimización de Rendimiento

### Estrategias Implementadas

#### **Caché**
- **Page Cache**: Caché de páginas completas para usuarios anónimos
- **Dynamic Page Cache**: Caché inteligente para usuarios autenticados
- **BigPipe**: Carga progresiva de componentes pesados
- **OPcache**: Caché de opcodes PHP para mejor rendimiento

#### **Assets**
- **Agregación CSS/JS**: Minimización de requests HTTP
- **Lazy Loading**: Carga diferida de imágenes via Blazy
- **WebP**: Conversión automática para mejor compresión
- **CDN Ready**: Headers optimizados para CDN

#### **Base de Datos**
- **Query Cache**: Caché de consultas MySQL
- **Connection Pooling**: Reutilización de conexiones
- **Índices Optimizados**: Índices de base de datos específicos

## Contacto y Soporte

### Equipo de Desarrollo
- **Arquitecto de Software**: [Nombre]
- **Desarrollador Drupal**: [Nombre]
- **DevOps Engineer**: [Nombre]

### Recursos Adicionales
- **Documentación Drupal**: https://www.drupal.org/docs
- **Traefik Docs**: https://doc.traefik.io/traefik/
- **Docker Compose**: https://docs.docker.com/compose/

---

*Documentación generada automáticamente para el proyecto IDT.gov.co*
*Última actualización: 2025-06-30*