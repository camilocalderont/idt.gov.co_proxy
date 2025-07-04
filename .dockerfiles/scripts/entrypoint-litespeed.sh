#!/bin/sh

# ========================================
# ENTRYPOINT MÍNIMO LITESPEED - PRODUCCIÓN
# IDT.gov.co - Solo lo esencial
# ========================================

echo "🚀 Iniciando LiteSpeed..."

# 🔥 CREAR DIRECTORIO SOCKET (CRUCIAL)
mkdir -p /tmp/lshttpd

# 🔥 PERMISOS BÁSICOS (CRUCIAL)
chown -R nobody:nogroup /var/www/html
chown -R nobody:nogroup /tmp/lshttpd

# 🔥 VALIDAR CONFIGURACIÓN ANTES DE INICIAR (PRODUCCIÓN)
echo "🔍 Validando configuración..."
if ! /usr/local/lsws/bin/lshttpd -t; then
    echo "❌ ERROR: Configuración de LiteSpeed inválida"
    exit 1
fi

echo "✅ Configuración válida. Iniciando servidor..."

# 🔥 INICIAR LITESPEED (CRUCIAL)
exec /usr/local/lsws/bin/lshttpd -D