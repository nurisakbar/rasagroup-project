import pandas as pd
import requests
import json
import urllib3

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

file_path = 'Faspay_VA_UAT_Fixed.xlsx'
xl = pd.ExcelFile(file_path)
df = xl.parse(xl.sheet_names[0])

with open('/Users/nurisakbar/.gemini/antigravity-ide/brain/28832cbf-57d5-47d3-9b76-9db5ad28f243/test_results_dev.md', 'w') as out:
    out.write("# Hasil Testing UAT Faspay SNAP (dev.rasaconnect.com)\n\n")
    out.write("Semua skenario pengujian ditembak langsung ke API dev (`https://dev.rasaconnect.com/api/faspay/snap/*`) menggunakan payload aktual dari dokumen UAT.\n\n")
    
    for i, row in df.iterrows():
        scenario_no = str(row.values[0])
        scenario_name = str(row.values[2]).replace('\n', ' ')
        req = str(row.values[4])
        
        if scenario_no.startswith('11.'):
            if 'url:' not in req:
                out.write(f"## Skenario {scenario_no}: {scenario_name}\n")
                out.write(f"> [!NOTE]\n> Skenario dilewati (Hanya untuk VA Dynamic)\n\n")
                continue
                
            lines = req.split('\n')
            url = None
            headers = {}
            body = ""
            mode = 'none'
            
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                if line == 'url:':
                    mode = 'url'
                    continue
                elif line == 'header:':
                    mode = 'header'
                    continue
                elif line == 'body:':
                    mode = 'body'
                    continue
                
                if mode == 'url':
                    url = line
                    mode = 'none'
                elif mode == 'header':
                    if ':' in line:
                        parts = line.split(':', 1)
                        headers[parts[0].strip()] = parts[1].strip()
                elif mode == 'body':
                    body += line
            
            test_url = url
                
            try:
                body_json = json.loads(body) if body.strip() else {}
            except json.JSONDecodeError:
                body_json = {}
                
            try:
                resp = requests.post(test_url, headers=headers, json=body_json, timeout=15, verify=False)
                try:
                    actual_resp = resp.json()
                except:
                    actual_resp = {"error": "Non-JSON response", "text": resp.text[:500]}
                status_code = resp.status_code
            except Exception as e:
                actual_resp = {"error": str(e)}
                status_code = "Error"
                
            out.write(f"## Skenario {scenario_no}: {scenario_name}\n\n")
            
            out.write("### Request Payload:\n")
            out.write("```json\n")
            out.write(json.dumps(body_json, indent=2) if body_json else "{}")
            out.write("\n```\n\n")
            
            out.write(f"### API Response (Status: {status_code}):\n")
            out.write("```json\n")
            out.write(json.dumps(actual_resp, indent=2))
            out.write("\n```\n\n")
            
            if status_code in [200, 400, 401, 404, 409]:
                out.write("> [!TIP]\n> ✅ Testing Selesai dieksekusi.\n\n")
            else:
                out.write("> [!WARNING]\n> ⚠️ Peringatan: Status code tidak standar atau terjadi error saat pengujian.\n\n")
            out.write("---\n\n")
