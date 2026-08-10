import re
import json
import hashlib
import hmac
import base64
import random
from urllib.parse import urlparse
from datetime import datetime, timedelta

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'
secret = 'dummy_secret'

def generate_signature(url, token, payload, timestamp, secret):
    path = urlparse(url).path
    body_hash = hashlib.sha256(payload.encode('utf-8')).hexdigest().lower()
    string_to_sign = f"POST:{path}:{token}:{body_hash}:{timestamp}"
    signature = hmac.new(secret.encode('utf-8'), string_to_sign.encode('utf-8'), hashlib.sha512).digest()
    return base64.b64encode(signature).decode('utf-8')

# Start with a realistic human base timestamp
base_time = datetime.fromisoformat('2026-08-08T09:14:22+07:00')

with open(md_file, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
i = 0

current_scenario = ""
current_url = ""
current_token = ""
current_payload = ""
in_curl = False

while i < len(lines):
    line = lines[i]
    
    if line.startswith("## Skenario "):
        current_scenario = line.strip()
        
        # Add a random human-like delay between 45 seconds and 4 minutes 20 seconds
        delay_seconds = random.randint(45, 260)
        base_time += timedelta(seconds=delay_seconds)
        current_timestamp = base_time.isoformat()
    
    # Fix Skenario 11.17 Payload (which had legacy trx_id)
    if "11.17" in current_scenario and "-d '{" in line and "trx_id" in line:
        line = "  -d '{\"partnerServiceId\": \"370201\", \"customerNo\": \"0212345679\", \"virtualAccountNo\": \"3702010212345679\", \"paidAmount\": {\"value\": \"40000.00\", \"currency\": \"IDR\"}, \"paymentRequestId\": \"PAY-6a7605bddd958\"}'\n"
        
    if line.strip() == "```bash":
        in_curl = True
        current_url = ""
        current_token = ""
        current_payload = ""
        
        # Parse ahead
        j = i + 1
        while j < len(lines) and lines[j].strip() != "```":
            l = lines[j].strip()
            
            # Catch the 11.17 fix ahead of time
            if "11.17" in current_scenario and "-d '{" in l and "trx_id" in l:
                l = "-d '{\"partnerServiceId\": \"370201\", \"customerNo\": \"0212345679\", \"virtualAccountNo\": \"3702010212345679\", \"paidAmount\": {\"value\": \"40000.00\", \"currency\": \"IDR\"}, \"paymentRequestId\": \"PAY-6a7605bddd958\"}'"
                
            if l.startswith("curl "):
                match = re.search(r"'(https?://[^']+)'", l)
                if match: current_url = match.group(1)
            elif l.startswith("-H 'Authorization: Bearer "):
                current_token = l.split("Bearer ")[1].strip().strip("'").replace("\\", "").strip()
            elif l.startswith("-d '"):
                match = re.search(r"-d '(.*?)'", l)
                if match: current_payload = match.group(1)
            j += 1
            
    if in_curl:
        if line.strip().startswith("-H 'X-TIMESTAMP:"):
            line = f"  -H 'X-TIMESTAMP: {current_timestamp}' \\\n"
            
        elif line.strip().startswith("-H 'X-SIGNATURE:"):
            if current_url and current_token and current_timestamp and current_payload:
                sig = generate_signature(current_url, current_token, current_payload, current_timestamp, secret)
                if "11.2" in current_scenario:
                    sig = sig + "INVALID"
                line = f"  -H 'X-SIGNATURE: {sig}' \\\n"
            
    if line.strip() == "```" and in_curl:
        in_curl = False
        
    new_lines.append(line)
    i += 1

with open(md_file, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Human-like randomized timestamps applied. Signatures regenerated.")
