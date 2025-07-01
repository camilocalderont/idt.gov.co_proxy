#!/bin/bash
#cd /data/idt.gov.co_proxy

echo "Deteniendo contenedores..."
docker-compose down

echo "Iniciando contenedores..."
docker-compose up -d

contenedores=("nuevo_idt" "anterior_idt")

for contenedor in "${contenedores[@]}"; do
    echo "Mantenimiento en: $contenedor"

    # Composer update
    docker exec -it "$contenedor" composer update -n

    # Limpiar OPcache (usar variable de entorno)
    docker exec -it "$contenedor" /usr/local/lsws/lsphp${PHP_VERSION}/bin/php -r "opcache_reset();"

    # Comandos específicos de Drupal
    docker exec -it "$contenedor" vendor/bin/drush cache:rebuild
    docker exec -it "$contenedor" vendor/bin/drush config:import -y
    docker exec -it "$contenedor" vendor/bin/drush updatedb -y
done