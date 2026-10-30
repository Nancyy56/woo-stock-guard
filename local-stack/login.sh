#!/usr/bin/env bash
# Log in to this project's n8n (http://localhost:5678) and print the cookie-jar path. Usage: export J=$(./login.sh)
J=${J:-/tmp/n8n-woo-$(id -u).cookies}
curl -s -c "$J" localhost:5678/rest/login -H 'Content-Type: application/json' \
  -d '{"emailOrLdapLoginId":"admin@example.com","password":"Admin12345!"}' -o /dev/null && echo "$J"
