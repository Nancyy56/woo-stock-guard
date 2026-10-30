#!/usr/bin/env bash
# Re-import (update in place) local workflow copies into n8n and publish them. Needs $J cookie jar, $W main id, $E error id.
set -e; cd "$(dirname "$0")"
ver(){ curl -s -b $J localhost:5678/rest/workflows/$1 | python3 -c "import json,sys;print(json.load(sys.stdin)['data']['versionId'])"; }
put(){ python3 -c "
import json,sys;w=json.load(open('$2'));w2={k:w[k] for k in ('name','nodes','connections')};w2['versionId']='$(ver $1)'
if '$3': w2['settings']=dict(w['settings'],errorWorkflow='$3')
print(json.dumps(w2))" | curl -s -b $J -X PATCH localhost:5678/rest/workflows/$1 -H 'Content-Type: application/json' -H 'browser-id: x' -d @- >/dev/null
  curl -s -b $J -X POST localhost:5678/rest/workflows/$1/activate -H 'Content-Type: application/json' -H 'browser-id: x' -d "{\"versionId\":\"$(ver $1)\"}" | python3 -c "import json,sys;d=json.load(sys.stdin);print('$1', d.get('message') or ('active', d['data']['activeVersionId']))"; }
put $E error-handler.local.json ""
put $W workflow.local.json $E
