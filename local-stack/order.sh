#!/usr/bin/env bash
# Create a paid test order in the local WooCommerce store. Usage: ./order.sh 10:1 11:1 12:1   (product_id:qty ...)
cd "$(dirname "$0")"; read CK CS < .wc-keys
ITEMS=$(printf '%s\n' "$@" | awk -F: 'BEGIN{printf "["} {printf "%s{\"product_id\":%s,\"quantity\":%s}", (NR>1?",":""), $1, $2} END{printf "]"}')
docker exec n8n wget -qO- --header='Content-Type: application/json' \
  --post-data="{\"payment_method\":\"cod\",\"set_paid\":true,\"billing\":{\"first_name\":\"Jane\",\"last_name\":\"Doe\",\"email\":\"jane@example.com\"},\"line_items\":$ITEMS}" \
  "http://wordpress/wp-json/wc/v3/orders?consumer_key=$CK&consumer_secret=$CS" | python3 -c "import json,sys;d=json.load(sys.stdin);print('order',d['id'],d['status'],d['total'])"
