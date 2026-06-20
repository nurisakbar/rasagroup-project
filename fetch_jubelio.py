import urllib.request
import json

email = 'jubelioxzoho@rasagroup.co.id'
password = 'P@ssw0rd'

req = urllib.request.Request('https://api2.jubelio.com/login', data=json.dumps({'email': email, 'password': password}).encode(), headers={'Content-Type': 'application/json'})
res = urllib.request.urlopen(req)
token = json.loads(res.read())['token']

req2 = urllib.request.Request('https://api2.jubelio.com/inventory/items/?page=1&pageSize=1', headers={'Authorization': 'Bearer ' + token})
res2 = urllib.request.urlopen(req2)
data = json.loads(res2.read())

sample_data = data['data'][0] if len(data.get('data', [])) > 0 else 'No data'
markdown_content = f'''# Contoh Data Produk Jubelio

Berikut adalah sampel lengkap dari satu produk yang dikembalikan oleh API Jubelio:

```json
{json.dumps(sample_data, indent=2)}
```
'''

with open('api_jubelio_sample.md', 'w') as f:
    f.write(markdown_content)
print('File api_jubelio_sample.md created successfully.')
