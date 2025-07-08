#!/bin/bash

# Script para migrar la carpeta 'gavias' con rsync de forma reanudable.

# --- Configuración ---
USUARIO_ORIGEN="User_idt"
IP_ORIGEN="10.216.153.70"
RUTA_ORIGEN="/DATA/www/htdocs/gavias/"
RUTA_LLAVE_PRIVADA="/home/useridt/.ssh/id_rsa"

RUTA_DESTINO="/data/idt.gov.co_proxy/idt_anterior/sites/localhost/html/web/"

# --- Ejecución de Rsync ---
echo "▶️  Iniciando la sincronización desde $IP_ORIGEN..."

rsync -avz --progress --partial --rsync-path="sudo rsync" -e "ssh -i ${RUTA_LLAVE_PRIVADA}" ${USUARIO_ORIGEN}@${IP_ORIGEN}:${RUTA_ORIGEN} ${RUTA_DESTINO}

echo "✅  Sincronización finalizada."