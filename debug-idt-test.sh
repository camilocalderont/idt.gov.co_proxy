#!/bin/bash

# =========================================================
# SCRIPT DE DIAGNÓSTICO PARA IDT_TEST
# Herramienta para monitorear y solucionar problemas
# =========================================================

echo "🔍 DIAGNÓSTICO COMPLETO DEL SERVICIO IDT_TEST"
echo "=============================================="
echo ""

# =========================================================
# 1. ESTADO DEL CONTENEDOR
# =========================================================
echo "📊 1. ESTADO DEL CONTENEDOR"
echo "----------------------------"
if docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "(NAMES|idt_test)"; then
    echo "✅ Contenedor idt_test encontrado"
else
    echo "❌ Contenedor idt_test no encontrado"
fi
echo ""

# =========================================================
# 2. HEALTHCHECK
# =========================================================
echo "💚 2. HEALTHCHECK"
echo "------------------"
HEALTH_STATUS=$(docker inspect --format='{{.State.Health.Status}}' idt_test 2>/dev/null || echo "no_container")
if [ "$HEALTH_STATUS" = "healthy" ]; then
    echo "✅ Healthcheck: HEALTHY"
elif [ "$HEALTH_STATUS" = "unhealthy" ]; then
    echo "❌ Healthcheck: UNHEALTHY"
elif [ "$HEALTH_STATUS" = "starting" ]; then
    echo "⏳ Healthcheck: STARTING"
else
    echo "❓ Healthcheck: $HEALTH_STATUS"
fi
echo ""

# =========================================================
# 3. CONECTIVIDAD INTERNA
# =========================================================
echo "🔗 3. CONECTIVIDAD INTERNA"
echo "---------------------------"
if docker exec idt_test curl -sf http://localhost/index.php > /dev/null 2>&1; then
    echo "✅ Conectividad interna PHP: OK"
else
    echo "❌ Conectividad interna PHP: FALLO"
fi

if docker exec idt_test curl -sf http://localhost/ > /dev/null 2>&1; then
    echo "✅ Conectividad interna Web: OK"
else
    echo "❌ Conectividad interna Web: FALLO"
fi
echo ""

# =========================================================
# 4. CONECTIVIDAD EXTERNA
# =========================================================
echo "🌐 4. CONECTIVIDAD EXTERNA"
echo "---------------------------"
if curl -sf https://anterior.idt.gov.co/index.php > /dev/null 2>&1; then
    echo "✅ Conectividad externa PHP: OK"
else
    echo "❌ Conectividad externa PHP: FALLO"
fi

if curl -sf https://anterior.idt.gov.co/ > /dev/null 2>&1; then
    echo "✅ Conectividad externa Web: OK"
else
    echo "❌ Conectividad externa Web: FALLO"
fi
echo ""

# =========================================================
# 5. PROCESOS INTERNOS
# =========================================================
echo "⚙️ 5. PROCESOS INTERNOS"
echo "------------------------"
echo "Procesos LiteSpeed:"
docker exec idt_test ps aux | grep -E "(lshttpd|lsphp)" | grep -v grep || echo "❌ No hay procesos LiteSpeed"
echo ""

# =========================================================
# 6. ARCHIVOS Y PERMISOS
# =========================================================
echo "📁 6. ARCHIVOS Y PERMISOS"
echo "--------------------------"
echo "Archivos en /var/www/html:"
docker exec idt_test ls -la /var/www/html/ || echo "❌ No se puede acceder a /var/www/html"
echo ""

echo "Permisos de index.php:"
docker exec idt_test ls -la /var/www/html/index.php || echo "❌ No se encuentra index.php"
echo ""

# =========================================================
# 7. CONFIGURACIÓN LITESPEED
# =========================================================
echo "🔧 7. CONFIGURACIÓN LITESPEED"
echo "------------------------------"
echo "Validación de configuración:"
if docker exec idt_test /usr/local/lsws/bin/lshttpd -t; then
    echo "✅ Configuración LiteSpeed válida"
else
    echo "❌ Configuración LiteSpeed inválida"
fi
echo ""

# =========================================================
# 8. LOGS RECIENTES
# =========================================================
echo "📝 8. LOGS RECIENTES"
echo "---------------------"
echo "Últimas 5 líneas del log de errores de LiteSpeed:"
docker exec idt_test tail -5 /usr/local/lsws/logs/error.log 2>/dev/null || echo "❌ No se puede acceder al log de errores"
echo ""

echo "Últimas 5 líneas del log de errores de PHP:"
docker exec idt_test tail -5 /var/log/php/php_errors.log 2>/dev/null || echo "❌ No se puede acceder al log de PHP"
echo ""

# =========================================================
# 9. INFORMACIÓN DEL SISTEMA
# =========================================================
echo "💻 9. INFORMACIÓN DEL SISTEMA"
echo "------------------------------"
echo "Versión PHP:"
docker exec idt_test /usr/local/lsws/lsphp83/bin/php -v | head -1 || echo "❌ No se puede obtener versión PHP"
echo ""

echo "Memoria disponible:"
docker exec idt_test free -h || echo "❌ No se puede obtener información de memoria"
echo ""

# =========================================================
# 10. PRUEBA DE CONTENIDO PHP
# =========================================================
echo "🧪 10. PRUEBA DE CONTENIDO PHP"
echo "-------------------------------"
echo "Contenido de index.php:"
docker exec idt_test cat /var/www/html/index.php || echo "❌ No se puede leer index.php"
echo ""

echo "Ejecutando PHP directamente:"
docker exec idt_test /usr/local/lsws/lsphp83/bin/php /var/www/html/index.php 2>&1 | head -10 || echo "❌ Error ejecutando PHP"
echo ""

# =========================================================
# 11. RECOMENDACIONES
# =========================================================
echo "💡 11. RECOMENDACIONES"
echo "----------------------"
if [ "$HEALTH_STATUS" != "healthy" ]; then
    echo "🔴 PRIORIDAD ALTA: Revisar healthcheck y logs"
fi

if ! docker exec idt_test curl -sf http://localhost/index.php > /dev/null 2>&1; then
    echo "🔴 PRIORIDAD ALTA: Revisar configuración PHP y LiteSpeed"
fi

if ! curl -sf https://anterior.idt.gov.co/index.php > /dev/null 2>&1; then
    echo "🟡 PRIORIDAD MEDIA: Revisar configuración de Traefik"
fi

echo ""
echo "🎯 COMANDOS ÚTILES:"
echo "   docker compose logs -f idt_test          # Ver logs en tiempo real"
echo "   docker compose restart idt_test          # Reiniciar servicio"
echo "   docker exec -it idt_test /bin/bash       # Acceder al contenedor"
echo "   docker compose up --build idt_test       # Reconstruir imagen"
echo ""
echo "✅ Diagnóstico completado"