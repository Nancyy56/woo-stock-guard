#!/usr/bin/env bash
# Starts a local stack: MariaDB + WordPress/WooCommerce (http://localhost:8080) + n8n (http://localhost:5678)
set -e
cd "$(dirname "$0")"
NET=woo-n8n
docker network inspect $NET >/dev/null 2>&1 || docker network create $NET
docker volume create woo-db >/dev/null; docker volume create woo-wp >/dev/null; docker volume create n8n-data >/dev/null

docker start woo-db 2>/dev/null || docker run -d --name woo-db --network $NET --restart unless-stopped \
  -e MARIADB_DATABASE=wordpress -e MARIADB_USER=wp -e MARIADB_PASSWORD=wp -e MARIADB_ROOT_PASSWORD=root \
  -v woo-db:/var/lib/mysql mariadb:11

# Requests from n8n arrive with Host "wordpress"; mark those as HTTPS so WooCommerce accepts API key auth over plain HTTP.
docker start wordpress 2>/dev/null || docker run -d --name wordpress --network $NET --restart unless-stopped -p 8080:80 \
  -e WORDPRESS_DB_HOST=woo-db -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp -e WORDPRESS_DB_NAME=wordpress \
  -e WORDPRESS_CONFIG_EXTRA="if (isset(\$_SERVER['HTTP_HOST']) && \$_SERVER['HTTP_HOST'] === 'wordpress') { \$_SERVER['HTTPS'] = 'on'; }" \
  -v woo-wp:/var/www/html -v "$PWD/mu-plugins:/var/www/html/wp-content/mu-plugins:ro" wordpress:php8.3-apache

docker start n8n 2>/dev/null || docker run -d --name n8n --network $NET --restart unless-stopped -p 5678:5678 \
  -e GENERIC_TIMEZONE=UTC -e TZ=UTC -e N8N_SECURE_COOKIE=false -e WEBHOOK_URL=http://localhost:5678/ \
  -v n8n-data:/home/node/.n8n docker.n8n.io/n8nio/n8n:latest

echo "WordPress: http://localhost:8080   n8n: http://localhost:5678"
