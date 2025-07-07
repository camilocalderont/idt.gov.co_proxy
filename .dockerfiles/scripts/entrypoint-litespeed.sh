#!/bin/sh

# ========================================
# ENTRYPOINT MEJORADO LITESPEED - PRODUCCIÓN
# IDT.gov.co - Corregido para socket UDS
# ========================================

echo "🚀 Iniciando LiteSpeed..."

# 🔥 CREAR DIRECTORIOS NECESARIOS
mkdir -p /tmp/lshttpd
mkdir -p /var/log/php
mkdir -p /usr/local/lsws/logs

# 🔥 CREAR ARCHIVO DE LOG SI NO EXISTE
touch /var/log/php/php_errors.log

# 🔥 LIMPIAR SOCKETS ANTERIORES
rm -f /tmp/lshttpd/lsphp83.sock
rm -f /tmp/lshttpd/lsphp.sock

# 🔥 PERMISOS CORRECTOS
chown -R nobody:nogroup /var/www/html
chown -R nobody:nogroup /tmp/lshttpd
chown -R nobody:nogroup /var/log/php
chown -R nobody:nogroup /usr/local/lsws/logs

# 🔥 PERMISOS ESPECÍFICOS PARA SOCKETS
chmod 755 /tmp/lshttpd
chmod 755 /var/log/php

# 🔥 ESPERAR A QUE LA BASE DE DATOS ESTÉ LISTA (si es necesario)
if [ ! -z "$DRUPAL_DB_HOST" ]; then
    echo "🔍 Esperando base de datos..."
    while ! nc -z $DRUPAL_DB_HOST 3306; do
        sleep 1
    done
    echo "✅ Base de datos disponible"
fi

# 🔥 VALIDAR CONFIGURACIÓN ANTES DE INICIAR
echo "🔍 Validando configuración..."
if ! /usr/local/lsws/bin/lshttpd -t; then
    echo "❌ ERROR: Configuración de LiteSpeed inválida"
    exit 1
fi

echo "✅ Configuración válida. Iniciando servidor..."

# 🔥 INICIAR LITESPEED EN FOREGROUND
exec /usr/local/lsws/bin/lshttpd -D