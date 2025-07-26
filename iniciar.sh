#!/bin/bash

# Script minimalista para IDT.gov.co - Drupal con LiteSpeed
# Versión básica y funcional

echo "🚀 Iniciando IDT.gov.co..."

# Ir al directorio del proyecto
#cd /data/idt.gov.co_proxy || { echo "❌ Error: No se encuentra el directorio del proyecto"; exit 1; }

# Crear directorios básicos si no existen
echo "📁 Creando directorios necesarios..."
mkdir -p traefik/{certs,logs}

# Verificar archivo docker compose.yml
if [[ ! -f "docker-compose.yml" ]]; then
    echo "❌ Error: No se encuentra docker compose.yml"
    exit 1
fi

# Detener contenedores existentes
echo "⏹️  Deteniendo contenedores..."
docker compose down

# Construir e iniciar servicios
echo "🔨 Construyendo imágenes..."
docker compose build

echo "▶️  Iniciando servicios..."
docker compose up -d

# Esperar un poco para que los servicios estén listos
echo "⏳ Esperando servicios..."
sleep 15

# Verificar que los contenedores estén ejecutándose
echo "✅ Verificando contenedores..."
if ! docker compose ps | grep -q "Up"; then
    echo "❌ Error: Algunos contenedores no están ejecutándose"
    docker compose ps
    exit 1
fi

# Lista de contenedores para mantenimiento
contenedores=("idt_nuevo" "idt_anterior" "idt_intranet" "idt_observatorio")

# Función básica de mantenimiento
mantenimiento_basico() {
    local contenedor=$1
    local php_version=$2

    echo "🔧 Mantenimiento en: $contenedor"

    # Verificar que el contenedor esté corriendo
    if ! docker ps --format "table {{.Names}}" | grep -q "^$contenedor$"; then
        echo "⚠️  Contenedor $contenedor no está ejecutándose"
        return 1
    fi

    # Gestionar composer si existe
    if docker exec "$contenedor" test -f /var/www/vhosts/localhost/html/web/composer.json 2>/dev/null; then
        if docker exec "$contenedor" test -d /var/www/vhosts/localhost/html/web/vendor 2>/dev/null; then
            echo "📦 Actualizando composer en $contenedor (vendor existe)..."
            docker exec "$contenedor" composer update -n 2>/dev/null || echo "⚠️  Error con composer update"
        else
            echo "📦 Instalando composer en $contenedor (vendor no existe)..."
            docker exec "$contenedor" composer install --no-dev --optimize-autoloader 2>/dev/null || echo "⚠️  Error con composer install"
        fi
    fi

    # Limpiar OPcache
    echo "🧹 Limpiando OPcache en $contenedor..."
    docker exec "$contenedor" /usr/local/lsws/lsphp${php_version}/bin/php -r "opcache_reset();" 2>/dev/null || echo "⚠️  Error limpiando OPcache"

    # Ejecutar comandos Drupal básicos si existe drush
    if docker exec "$contenedor" test -f /var/www/vhosts/localhost/html/web/vendor/bin/drush 2>/dev/null; then
        echo "🔄 Limpiando cache Drupal en $contenedor..."
        docker exec "$contenedor" /var/www/vhosts/localhost/html/web/vendor/bin/drush cache:rebuild 2>/dev/null || echo "⚠️  Error con drush cache"
    fi

    echo "✅ Mantenimiento completado en $contenedor"
}

# Ejecutar mantenimiento en cada contenedor
for contenedor in "${contenedores[@]}"; do
    case $contenedor in
        "idt_nuevo")
            mantenimiento_basico "$contenedor" "83"
            ;;
        "idt_anterior")
            mantenimiento_basico "$contenedor" "74"
            ;;
        "idt_observatorio")
            mantenimiento_basico "$contenedor" "83"
            ;;
        "idt_intranet")
            mantenimiento_basico "$contenedor" "74"
            ;;
    esac
done

# Mostrar estado final
echo ""
echo "🎉 Proceso completado!"
echo ""
echo "📊 Estado de contenedores:"
docker compose ps

echo ""
echo "🌐 URLs disponibles:"
echo "   • Sitio principal: https://www.idt.gov.co"
echo "   • Sitio anterior:  https://anterior.idt.gov.co"
echo ""
echo "📝 Comandos útiles:"
echo "   • Ver logs:        docker compose logs -f [servicio]"
echo "   • Entrar al contenedor: docker exec -it [contenedor] bash"
echo "   • Reiniciar:       docker compose restart [servicio]"

echo ""
echo "✨ ¡IDT.gov.co está listo!"