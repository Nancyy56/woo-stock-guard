"""Build n8n-importable copies of the workflows with local IDs filled in.
Usage: python3 build_local.py <woo_cred_id> <data_table_id> <alert_email> [google_sheets_cred_id gmail_cred_id sheet_id error_sheet_id]
Without Google IDs, the Google nodes are imported disabled so the core flow can be tested."""
import json, sys, os
here = os.path.dirname(os.path.abspath(__file__)); root = os.path.dirname(here)
woo, table, email = sys.argv[1:4]
g = sys.argv[4:8] if len(sys.argv) >= 8 else None

w = json.load(open(f'{root}/workflow.json'))
for n in w['nodes']:
    p = n['parameters']
    if n['type'] == 'n8n-nodes-base.wooCommerce':
        n['credentials'] = {'wooCommerceApi': {'id': woo, 'name': 'WooCommerce account'}}
    if n['type'] == 'n8n-nodes-base.dataTable':
        p['dataTableId'] = {'__rl': True, 'value': table, 'mode': 'id'}
    if n['type'] == 'n8n-nodes-base.gmail':
        p['sendTo'] = email
    if n['type'] in ('n8n-nodes-base.gmail', 'n8n-nodes-base.googleSheets'):
        if g:
            if n['type'] == 'n8n-nodes-base.gmail':
                n['credentials'] = {'gmailOAuth2': {'id': g[1], 'name': 'Gmail account'}}
            else:
                n['credentials'] = {'googleSheetsOAuth2Api': {'id': g[0], 'name': 'Google Sheets account'}}
                p['documentId'] = {'__rl': True, 'value': g[2], 'mode': 'id'}
                p['sheetName'] = {'__rl': True, 'value': 'Sheet1', 'mode': 'name'}
            n.pop('disabled', None)
        else:
            n.pop('credentials', None); n['disabled'] = True
w['name'] = 'WooCommerce Orders & Inventory Automation'
json.dump(w, open(f'{here}/workflow.local.json', 'w'), indent=2, ensure_ascii=False)

e = json.load(open(f'{root}/error-handler-workflow.json'))
for n in e['nodes']:
    if n['type'] == 'n8n-nodes-base.googleSheets':
        if g:
            n['credentials'] = {'googleSheetsOAuth2Api': {'id': g[0], 'name': 'Google Sheets account'}}
            n['parameters']['documentId'] = {'__rl': True, 'value': g[3], 'mode': 'id'}
            n['parameters']['sheetName'] = {'__rl': True, 'value': 'Sheet1', 'mode': 'name'}
            n.pop('disabled', None)
        else:
            n.pop('credentials', None); n['disabled'] = True
e['name'] = 'WooCommerce Error Handler'
json.dump(e, open(f'{here}/error-handler.local.json', 'w'), indent=2, ensure_ascii=False)
print('built')
