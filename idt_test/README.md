# 🧪 IDT_TEST - Entorno de Pruebas para LiteSpeed + PHP

## 📋 Propósito

El servicio `idt_test` es un entorno de pruebas diseñado para **refinar y validar** la configuración de LiteSpeed y PHP antes de aplicarla al servicio de producción. Su objetivo principal es ejecutar un simple `phpinfo()` y validar que todo funcione correctamente.

## 🎯 Principios de Diseño

### ⚡ Stateless (Sin Estado)
- **Contenedor completamente stateless**: No almacena datos internos
- **Configuración por volúmenes**: Toda la configuración se inyecta desde el host
- **Archivos por volúmenes**: El código y configuración viven en el host

### 🔄 Iteración Rápida
- **Dockerfile optimizado**: Aplica las lecciones del troubleshooting
- **Script de diagnóstico**: Herramientas para identificar problemas rápidamente
- **Configuración simplificada**: Enfocada en funcionalidad básica

## 📁 Estructura de Archivos

```
idt_test/
├── config/
│   ├── litespeed/
│   │   └── httpd_config.conf      # Configuración LiteSpeed optimizada
│   └── php/
│       └── php83-optimized.ini    # Configuración PHP optimizada
├── drupal/
│   └── index.php                  # Archivo de prueba con phpinfo()
├── logs/                          # Logs persistentes
│   ├── php/                       # Logs de PHP
│   └── litespeed/                 # Logs de LiteSpeed
└── README.md                      # Este archivo
```

## 🔧 Configuración Aplicada

### Basada en Troubleshooting
Este servicio aplica todas las **lecciones aprendidas** del troubleshooting previo:

#### ✅ Problemas Solucionados:
1. **suEXEC deshabilitado**: Configurado tanto global como localmente
2. **Procesador PHP TCP**: Usa 127.0.0.1:9000 en lugar de sockets UDS
3. **Permisos correctos**: Usuario `nobody:nogroup` en toda la estructura
4. **Directorio de trabajo**: `/var/www/html` con permisos correctos

#### 🔧 Mejoras Implementadas:
- **Script de entrypoint mejorado**: Limpieza de sockets, permisos, validaciones
- **Healthcheck funcional**: Verifica que PHP funcione correctamente
- **Logs estructurados**: Logs persistentes para diagnóstico
- **Configuración validada**: Validación automática de configuración LiteSpeed

## 🚀 Uso

### 1. Construir y Ejecutar
```bash
# Construir imagen
docker compose build idt_test

# Ejecutar servicio
docker compose up idt_test

# Ejecutar en segundo plano
docker compose up -d idt_test
```

### 2. Verificar Funcionamiento
```bash
# Ejecutar diagnóstico completo
./debug-idt-test.sh

# Verificar conectividad interna
docker exec idt_test curl -f http://localhost/index.php

# Verificar conectividad externa
curl -k https://anterior.idt.gov.co/index.php
```

### 3. Monitorear Logs
```bash
# Logs del contenedor
docker compose logs -f idt_test

# Logs de LiteSpeed
docker exec idt_test tail -f /usr/local/lsws/logs/error.log

# Logs de PHP
docker exec idt_test tail -f /var/log/php/php_errors.log
```

## 🌐 Acceso Web

- **URL Externa**: https://anterior.idt.gov.co/index.php
- **URL Interna**: http://localhost/index.php (desde dentro del contenedor)

## 🔍 Diagnóstico

### Script de Diagnóstico
```bash
./debug-idt-test.sh
```

Este script proporciona:
- ✅ Estado del contenedor
- ✅ Healthcheck status
- ✅ Conectividad interna y externa
- ✅ Procesos LiteSpeed
- ✅ Archivos y permisos
- ✅ Validación de configuración
- ✅ Logs recientes
- ✅ Información del sistema

### Comandos Útiles
```bash
# Acceder al contenedor
docker exec -it idt_test /bin/bash

# Reiniciar servicio
docker compose restart idt_test

# Ver estado detallado
docker inspect idt_test

# Reconstruir imagen
docker compose build --no-cache idt_test
```

## 📊 Validación

### ✅ Criterios de Éxito
Para que el servicio se considere **funcionando correctamente**:

1. **Healthcheck**: Status = `healthy`
2. **Conectividad interna**: `curl http://localhost/index.php` = 200
3. **Conectividad externa**: `curl https://anterior.idt.gov.co/index.php` = 200
4. **Configuración**: `lshttpd -t` = sin errores
5. **Procesos**: LiteSpeed y PHP corriendo
6. **Logs**: Sin errores críticos

### 🔧 Solución de Problemas

#### Problema: Healthcheck Unhealthy
```bash
# Verificar logs
docker compose logs idt_test

# Verificar configuración
docker exec idt_test /usr/local/lsws/bin/lshttpd -t
```

#### Problema: PHP no funciona
```bash
# Verificar procesos
docker exec idt_test ps aux | grep php

# Probar PHP directamente
docker exec idt_test /usr/local/lsws/lsphp83/bin/php /var/www/html/index.php
```

#### Problema: Conectividad externa
```bash
# Verificar Traefik
docker compose logs traefik

# Verificar configuración de red
docker network ls
```

## 🔄 Workflow de Desarrollo

### 1. Hacer Cambios
- Editar configuración en `config/`
- Modificar archivos en `drupal/`
- Actualizar Dockerfile si es necesario

### 2. Aplicar Cambios
```bash
# Reconstruir imagen
docker compose build idt_test

# Reiniciar servicio
docker compose up idt_test
```

### 3. Validar
```bash
# Ejecutar diagnóstico
./debug-idt-test.sh

# Verificar funcionalidad
curl -k https://anterior.idt.gov.co/index.php
```

### 4. Aplicar a Producción
Una vez validado, aplicar las configuraciones al servicio `idt_nuevo`.

## 🎯 Próximos Pasos

1. **Validar configuración actual**: Ejecutar diagnóstico completo
2. **Refinar configuración**: Ajustar según resultados
3. **Probar cambios**: Iteración rápida de mejoras
4. **Aplicar a producción**: Migrar configuración validada

## 📞 Soporte

- **Script de diagnóstico**: `./debug-idt-test.sh`
- **Logs**: `docker compose logs -f idt_test`
- **Troubleshooting**: Ver `TROUBLESHOOTING_LITESPEED.md`

---

**Creado**: 5 de Julio de 2025
**Propósito**: Refinamiento de configuración LiteSpeed + PHP
**Estado**: Listo para pruebas