# Local test stack

This project is fully separate from project 18. It has its own n8n, containers and Docker network (`woo-n8n`).

Runs everything on your machine with Docker:

| Service | URL | Login |
|---|---|---|
| n8n | http://localhost:5678 | admin@example.com / Admin12345! |
| WordPress + WooCommerce | http://localhost:8080/wp-admin | admin / admin123 |

Products (all low-stock threshold 5): Essential Cotton Tee (10), Stoneware Coffee Mug (11), Wireless Pro Mouse (12), Studio Wireless Headphones (23), Minimal Smart Watch (24), Everyday Commuter Backpack (25).
WooCommerce webhook `order.created` → `http://n8n:5678/webhook/woocommerce-order-created`.

## Files

- `start.sh` – start/restart the containers (MariaDB, WordPress, n8n) on Docker network `woo-n8n`.
- `mu-plugins/local-dev.php` – lets WordPress send webhooks to the n8n container (internal host, port 5678). Local dev only.
- `wp.sh` – run wp-cli against the store, e.g. `./wp.sh wc product list --user=admin`.
- `order.sh` – create a paid test order: `./order.sh 10:1 11:1 12:1` (product_id:qty).
- `build_local.py` + `import.sh` – fill IDs into copies of the workflows and push them to n8n.
- `login.sh`: logs in to this n8n; use `export J=$(./login.sh)` before `import.sh` / `last_exec.sh`.
- `last_exec.sh` – show per-node item counts / errors of the latest n8n executions.
- `mu-plugins/store-design.php` + `setup-design.php` – store look (fonts, styles, header, footer, homepage, shop page). Re-apply with `./wp.sh eval-file - < setup-design.php`.
- `wc_api.py` – small WooCommerce REST helper: `python3 wc_api.py GET products`.

## Connect Google Sheets + Gmail

Until this is done, the Google Sheets and Gmail nodes are imported **disabled** (data passes straight through).

1. Google Cloud Console: create an OAuth client (type "Web application"), enable the Google Sheets API, Google Drive API and Gmail API,
   and add the redirect URI `http://localhost:5678/rest/oauth2-credential/callback`.
2. In n8n → Credentials, create **Google Sheets OAuth2 API** and **Gmail OAuth2 API** with that client ID/secret and click *Sign in with Google*.
3. Create two Google Sheets with these header rows:
   - Inventory log: `Timestamp | Order Number | Product | SKU | Ordered Qty | Current Stock | Low Stock Threshold | Status`
   - Error log: `Timestamp | Workflow | Execution ID | Failed Node | HTTP Code | Error Message | Execution URL | Raw Error Data`
4. Rebuild and re-import (sheet IDs come from the sheet URLs):

```bash
python3 build_local.py <woo_cred_id> <data_table_id> <alert_email> <sheets_cred_id> <gmail_cred_id> <inventory_sheet_id> <error_sheet_id>
J=<cookie jar> W=<main workflow id> E=<error workflow id> ./import.sh
```

Or just open the workflow in the n8n editor, enable the three Google nodes, pick the credentials and sheets, and publish.

## Stop / reset

```bash
docker stop n8n wordpress woo-db                      # stop
docker rm -f n8n wordpress woo-db && docker volume rm n8n-data woo-wp woo-db   # wipe everything
```
