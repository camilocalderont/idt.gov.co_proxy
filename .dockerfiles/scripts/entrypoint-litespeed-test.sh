#!/bin/bash

# ==============================================
# ENTRYPOINT MEJORADO PARA IDT_TEST
# Basado en las lecciones del troubleshooting
# ==============================================

set -e

echo "🚀 Iniciando configuración de LiteSpeed para idt_test..."

# 1. CREAR DIRECTORIOS NECESARIOS
echo "📁 Creando directorios necesarios..."
mkdir -p /tmp/lshttpd
mkdir -p /var/log/php
mkdir -p /usr/local/lsws/logs

# 2. LIMPIAR SOCKETS ANTERIORES (Lección aprendida)
echo "🧹 Limpiando sockets anteriores..."
rm -f /tmp/lshttpd/lsphp83.sock
rm -f /tmp/lshttpd/lsphp.sock
rm -f /tmp/lshttpd/lsphp*.sock

# 3. CONFIGURAR PERMISOS CORRECTOS (Crítico para funcionamiento)
echo "🔐 Configurando permisos..."
chown -R nobody:nogroup /var/www/html
chown -R nobody:nogroup /tmp/lshttpd
chown -R nobody:nogroup /var/log/php
chown -R nobody:nogroup /usr/local/lsws/logs

# 4. CREAR ARCHIVOS DE LOG NECESARIOS
echo "📝 Creando archivos de log..."
touch /var/log/php/php_errors.log
touch /usr/local/lsws/logs/error.log
touch /usr/local/lsws/logs/access.log

# 5. CONFIGURAR PERMISOS DE ARCHIVOS DE LOG
chown nobody:nogroup /var/log/php/php_errors.log
chown nobody:nogroup /usr/local/lsws/logs/error.log
chown nobody:nogroup /usr/local/lsws/logs/access.log

# 6. VERIFICAR ARCHIVO INDEX.PHP
echo "🔍 Verificando archivo index.php..."
if [[ -f "/var/www/html/index.php" ]]; then
    echo "✅ Archivo index.php encontrado"
    chown nobody:nogroup /var/www/html/index.php
    chmod 644 /var/www/html/index.php
else
    echo "❌ Archivo index.php no encontrado"
fi

# 7. ESPERAR BASE DE DATOS (si está configurada)
if [ ! -z "$DRUPAL_DB_HOST" ]; then
    echo "⏳ Esperando base de datos en $DRUPAL_DB_HOST:3306..."
    while ! nc -z $DRUPAL_DB_HOST 3306; do
        echo "   Esperando base de datos..."
        sleep 2
    done
    echo "✅ Base de datos disponible"
fi

# 8. VALIDAR CONFIGURACIÓN DE LITESPEED
echo "🔧 Validando configuración de LiteSpeed..."
if /usr/local/lsws/bin/lshttpd -t; then
    echo "✅ Configuración de LiteSpeed válida"
else
    echo "❌ Error en configuración de LiteSpeed"
    exit 1
fi

# 9. MOSTRAR INFORMACIÓN DE CONFIGURACIÓN
echo "📊 Información de configuración:"
echo "   - Usuario: $(whoami)"
echo "   - Directorio trabajo: $(pwd)"
echo "   - Archivos en /var/www/html: $(ls -la /var/www/html/)"
echo "   - PHP Version: $(/usr/local/lsws/lsphp83/bin/php -v | head -1)"

# 10. INICIAR LITESPEED
echo "🚀 Iniciando LiteSpeed Web Server..."
exec /usr/local/lsws/bin/lshttpd -D