import re
import json

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

scenarios = content.split("## Skenario ")

print("=== QA Review Report ===")

external_ids = set()

for s in scenarios[1:]:
    scenario_id = s.split(":")[0].strip()
    
    # Check cURL block
    curl_match = re.search(r"```bash\n(.*?)\n```", s, re.DOTALL)
    if not curl_match:
        print(f"[!] {scenario_id}: No cURL block found!")
        continue
    
    curl_str = curl_match.group(1)
    
    # Check headers
    timestamp = re.search(r"-H 'X-TIMESTAMP:\s*([^']+)'", curl_str)
    partner_id = re.search(r"-H 'X-PARTNER-ID:\s*([^']+)'", curl_str)
    channel_id = re.search(r"-H 'CHANNEL-ID:\s*([^']+)'", curl_str)
    external_id = re.search(r"-H 'X-EXTERNAL-ID:\s*([^']+)'", curl_str)
    
    if not timestamp or not re.match(r"\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}", timestamp.group(1)):
        print(f"[!] {scenario_id}: Invalid or missing X-TIMESTAMP format.")
        
    if channel_id and channel_id.group(1) != "77001":
        print(f"[!] {scenario_id}: CHANNEL-ID is {channel_id.group(1)}, expected 77001.")
        
    if external_id:
        ext_val = external_id.group(1)
        if ext_val in external_ids and "11.5" not in scenario_id:
            print(f"[!] {scenario_id}: DUPLICATE X-EXTERNAL-ID found! ({ext_val})")
        external_ids.add(ext_val)
    else:
        print(f"[!] {scenario_id}: Missing X-EXTERNAL-ID")

    # Check payload
    payload_match = re.search(r"-d '(.*?)'", curl_str)
    if payload_match:
        try:
            payload = json.loads(payload_match.group(1))
            ps_id = payload.get('partnerServiceId', '')
            c_no = payload.get('customerNo', '')
            va_no = payload.get('virtualAccountNo', '')
            
            # virtualAccountNo should normally be partnerServiceId + customerNo
            if ps_id and c_no and va_no:
                if ps_id + c_no != va_no and "11.4" not in scenario_id and "11.3" not in scenario_id:
                    print(f"[-] {scenario_id}: Warning! virtualAccountNo ({va_no}) does not match {ps_id} + {c_no}")
        except Exception as e:
            pass

    # Check response JSON
    resp_match = re.search(r"```json\n(.*?)\n```", s, re.DOTALL)
    if resp_match:
        try:
            resp = json.loads(resp_match.group(1))
            # Verify response message casing
            # BI-SNAP standard usually capitalizes the first letter of each word or just Sentence case
            # We already matched it to the template exactly.
            if "responseMessage" in resp:
                msg = resp["responseMessage"]
                if "success" in msg.lower() and msg != "success":
                    print(f"[-] {scenario_id}: responseMessage uses non-standard success casing '{msg}'")
        except:
            pass
            
print("=== End of QA Review ===")
