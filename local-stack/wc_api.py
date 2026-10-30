"""Tiny WooCommerce REST helper for the local store. Usage: python3 wc_api.py METHOD path [json]"""
import json, sys, os, urllib.request
here = os.path.dirname(os.path.abspath(__file__))
ck, cs = open(f'{here}/.wc-keys').read().split()

def call(method, path, body=None):
    sep = '&' if '?' in path else '?'
    req = urllib.request.Request(f'http://localhost:8080/wp-json/wc/v3/{path}{sep}consumer_key={ck}&consumer_secret={cs}',
                                 data=json.dumps(body).encode() if body is not None else None, method=method,
                                 headers={'Content-Type': 'application/json', 'Host': 'wordpress'})
    try:
        return json.load(urllib.request.urlopen(req))
    except urllib.error.HTTPError as e:
        return json.load(e)

if __name__ == '__main__':
    print(json.dumps(call(sys.argv[1], sys.argv[2], json.loads(sys.argv[3]) if len(sys.argv) > 3 else None), indent=1))
