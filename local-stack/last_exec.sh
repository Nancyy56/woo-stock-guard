#!/usr/bin/env bash
# Show per-node item counts / errors of the latest n8n executions. Needs cookie jar path in $J.
N=${1:-1}
{ [ -n "$ID" ] && echo "{\"data\":{\"results\":[{\"id\":$ID,\"status\":\"?\"}]}}" || curl -s -b "$J" "localhost:5678/rest/executions?limit=$N"; } | python3 -c "
import json,sys,subprocess,os
for e in json.load(sys.stdin)['data']['results']:
  d=json.loads(subprocess.check_output(['curl','-s','-b',os.environ['J'],'localhost:5678/rest/executions/'+str(e['id'])]))['data']
  print('#',e['id'],e['workflowName'] if 'workflowName' in e else '',e['status'])
  data=json.loads(d['data'])
  # flatted format: resolve
  def R(v,seen=0):
    if isinstance(v,str) and v.isdigit(): return data[int(v)]
    return v
  root=data[0]; rd=R(root['resultData']); run=R(rd['runData'])
  for node,ref in run.items():
    runs=R(ref)
    for r in runs:
      r=R(r); err=R(r.get('error')) if r.get('error') else None
      outs=[]
      if r.get('data'):
        m=R(R(r['data']).get('main'))
        if m: outs=[len(R(o)) if o else 0 for o in [R(x) for x in m]]
      msg=R(err['message']) if err else ''
      print('  ',node,'outputs=',outs,'ERR '+str(msg) if err else '')
"
