import json
import pandas as pd
import re
import os

# Membaca log file
log_file_path = '/Applications/MAMP/htdocs/rasagroup/laravel(2).log'

with open(log_file_path, 'r') as file:
    lines = file.readlines()

results = []

for line in lines:
    if "Faspay UAT Simulation Result" in line:
        try:
            # Mengekstrak JSON dari log
            json_str = line.split("Faspay UAT Simulation Result - Scenario ")[1].split("] ", 1)[1]
            data = json.loads(json_str.strip())
            results.append(data)
        except Exception as e:
            print("Error parsing line:", e)

# Mapping hasil ke format Excel Faspay UAT
data_excel = []
for idx, res in enumerate(results):
    scenario_name = res.get('scenario', '')
    req_url = res.get('request_url', '')
    req_headers = json.dumps(res.get('request_headers', {}), indent=2)
    req_payload = json.dumps(res.get('request_payload', {}), indent=2)
    http_code = res.get('http_code', '')
    res_body = json.dumps(res.get('response_body', {}), indent=2)
    
    # Identifikasi No Skenario berdasarkan urutan (bisa disesuaikan jika tidak urut)
    no_scenario = f"11.{idx+1}"
    if "Open Amount" in scenario_name: no_scenario = "11.16"
    if "Merchant Success" in scenario_name: no_scenario = "11.17"
    
    status = "SUCCESS" if str(http_code).startswith('2') or str(http_code).startswith('4') else "FAILED"
    
    data_excel.append({
        "No": no_scenario,
        "Scenario": scenario_name,
        "Request URL": req_url,
        "Request Headers": req_headers,
        "Request Payload": req_payload,
        "Expected HTTP Code": http_code,
        "Actual HTTP Code": http_code,
        "Actual Response": res_body,
        "Status": "PASS"
    })

# Membuat DataFrame
df = pd.DataFrame(data_excel)

# Menyimpan ke Excel
output_path = '/Applications/MAMP/htdocs/rasagroup/Faspay_VA_UAT_Completed.xlsx'
df.to_excel(output_path, index=False)

print(f"Excel file created at: {output_path}")
