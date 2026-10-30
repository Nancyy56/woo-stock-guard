#!/usr/bin/env bash
# Run wp-cli against the local WordPress container
docker run --rm -i --network woo-n8n --volumes-from wordpress --user 33:33 \
  -e WORDPRESS_DB_HOST=woo-db -e WORDPRESS_DB_USER=wp -e WORDPRESS_DB_PASSWORD=wp -e WORDPRESS_DB_NAME=wordpress \
  -e HOME=/tmp wordpress:cli-php8.3 wp "$@"
