#!/bin/bash

# ========================================
# SCRIPT DE DEBUG PARA LITESPEED
# IDT.gov.co - Diagnóstico completo
# ========================================

echo "🔍 DIAGNÓSTICO DE LITESPEED - IDT.gov.co"
echo "======================================="

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para mostrar estado
show_status() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ $1${NC}"
    else
        echo -e "${RED}❌ $1${NC}"
    fi
}

# Función para mostrar warning
show_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

echo ""
echo "1. 🐳 ESTADO DE CONTENEDORES"
echo "----------------------------"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""

echo "2. 🌐 CONECTIVIDAD DE RED"
echo "-------------------------"
echo "🔍 Probando conectividad a servicios..."

# Test conexión a Traefik
curl -s -o /dev/null -w "%{http_code}" http://localhost:80 > /tmp/traefik_test
if [ "$(cat /tmp/traefik_test)" = "404" ] || [ "$(cat /tmp/traefik_test)" = "301" ]; then
    echo -e "${GREEN}✅ Traefik responde (HTTP $(cat /tmp/traefik_test))${NC}"
else
    echo -e "${RED}❌ Traefik no responde (HTTP $(cat /tmp/traefik_test))${NC}"
fi

# Test conexión a MariaDB
docker exec mariadb mysqladmin ping -h localhost 2>/dev/null
show_status "MariaDB responde"

# Test conexión interna al contenedor
docker exec idt_nuevo curl -s -o /dev/null -w "%{http_code}" http://localhost/test-litespeed.php > /tmp/internal_test 2>/dev/null
if [ "$(cat /tmp/internal_test)" = "200" ]; then
    echo -e "${GREEN}✅ LiteSpeed responde internamente (HTTP $(cat /tmp/internal_test))${NC}"
else
    echo -e "${RED}❌ LiteSpeed no responde internamente (HTTP $(cat /tmp/internal_test))${NC}"
fi

echo ""
echo "3. 🔧 CONFIGURACIÓN DE LITESPEED"
echo "--------------------------------"
echo "🔍 Validando configuración..."

# Validar configuración LiteSpeed
docker exec idt_nuevo /usr/local/lsws/bin/lshttpd -t 2>/dev/null
show_status "Configuración de LiteSpeed válida"

# Verificar procesos
echo ""
echo "🔍 Procesos de LiteSpeed:"
docker exec idt_nuevo ps aux | grep lshttpd | grep -v grep

echo ""
echo "🔍 Procesos de PHP:"
docker exec idt_nuevo ps aux | grep lsphp | grep -v grep

echo ""
echo "4. 📁 ARCHIVOS Y PERMISOS"
echo "-------------------------"
echo "🔍 Verificando archivos críticos..."

# Verificar socket
docker exec idt_nuevo ls -la /tmp/lshttpd/ 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Directorio de socket existe${NC}"
else
    echo -e "${RED}❌ Directorio de socket no existe${NC}"
fi

# Verificar index.php
docker exec idt_nuevo ls -la /var/www/html/index.php 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ index.php existe${NC}"
else
    echo -e "${YELLOW}⚠️  index.php no existe${NC}"
fi

# Verificar test-litespeed.php
docker exec idt_nuevo ls -la /var/www/html/test-litespeed.php 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ test-litespeed.php existe${NC}"
else
    echo -e "${YELLOW}⚠️  test-litespeed.php no existe${NC}"
fi

echo ""
echo "5. 📝 LOGS RECIENTES"
echo "-------------------"
echo "🔍 Últimos errores de LiteSpeed:"
docker exec idt_nuevo tail -n 10 /usr/local/lsws/logs/error.log 2>/dev/null

echo ""
echo "🔍 Últimos logs de PHP:"
docker exec idt_nuevo tail -n 5 /var/log/php/php_errors.log 2>/dev/null

echo ""
echo "6. 🌐 TESTS DE CONECTIVIDAD EXTERNA"
echo "-----------------------------------"
echo "🔍 Probando URLs externas..."

# Test desde host
curl -s -o /dev/null -w "www.idt.gov.co: %{http_code}\n" https://www.idt.gov.co/test-litespeed.php -k
curl -s -o /dev/null -w "idt.gov.co: %{http_code}\n" https://idt.gov.co -k

echo ""
echo "7. 💾 INFORMACIÓN DEL SISTEMA"
echo "-----------------------------"
echo "🔍 Uso de recursos:"
docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}"

echo ""
echo "🔍 Espacio en disco:"
df -h | grep -E "(Filesystem|/var/lib/docker|overlay)"

echo ""
echo "========================================="
echo "🎯 DIAGNÓSTICO COMPLETADO"
echo "========================================="
echo ""
echo "📋 GUÍA DE SOLUCIÓN:"
echo "1. Si LiteSpeed no responde internamente: docker-compose restart idt_nuevo"
echo "2. Si hay errores de socket: docker-compose down && docker-compose up -d"
echo "3. Si hay errores de permisos: docker exec idt_nuevo chown -R nobody:nogroup /var/www/html"
echo "4. Para ver logs en tiempo real: docker-compose logs -f idt_nuevo"
echo "5. Para entrar al contenedor: docker exec -it idt_nuevo /bin/bash"
echo ""

# Limpiar archivos temporales
rm -f /tmp/traefik_test /tmp/internal_test