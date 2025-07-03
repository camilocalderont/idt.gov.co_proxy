#!/bin/sh

# Establece la propiedad de la carpeta del sitio al usuario 'nobody'.
# Esto soluciona el error "Path for document root is not accessible".
# La opción '-R' lo hace de forma recursiva para todos los archivos y carpetas.
chown -R nobody:nogroup /var/www/html

# Ejecuta el comando original para iniciar LiteSpeed en primer plano.
exec /usr/local/lsws/bin/lshttpd -D