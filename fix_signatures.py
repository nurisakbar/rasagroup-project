import re
import json
import hashlib
import hmac
import base64
from urllib.parse import urlparse

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'
secret = 'dummy_secret'

def generate_signature(url, token, payload, timestamp, secret):
    path = urlparse(url).path
    body_hash = hashlib.sha256(payload.encode('utf-8')).hexdigest().lower()
    string_to_sign = f"POST:{path}:{token}:{body_hash}:{timestamp}"
    signature = hmac.new(secret.encode('utf-8'), string_to_sign.encode('utf-8'), hashlib.sha512).digest()
    return base64.b64encode(signature).decode('utf-8')

with open(md_file, 'r', encoding='utf-8') as f:
    lines = f.readlines()

new_lines = []
i = 0

current_scenario = ""
current_url = ""
current_token = ""
current_timestamp = ""
current_payload = ""
in_curl = False
curl_block_start = 0

while i < len(lines):
    line = lines[i]
    
    if line.startswith("## Skenario "):
        current_scenario = line.strip()
    
    if line.strip() == "```bash":
        in_curl = True
        curl_block_start = i
        current_url = ""
        current_token = ""
        current_timestamp = ""
        current_payload = ""
        
        # parse the curl block ahead to gather variables
        j = i + 1
        payload_lines = []
        in_payload = False
        while j < len(lines) and lines[j].strip() != "```":
            l = lines[j].strip()
            
            if l.startswith("curl "):
                match = re.search(r"'(https?://[^']+)'", l)
                if match: current_url = match.group(1)
            elif l.startswith("-H 'Authorization: Bearer "):
                current_token = l.split("Bearer ")[1].strip().strip("'").replace("\\", "").strip()
            elif l.startswith("-H 'X-TIMESTAMP: "):
                current_timestamp = l.split("X-TIMESTAMP: ")[1].strip().strip("'").replace("\\", "").strip()
            elif l.startswith("-d '"):
                # single line payload
                match = re.search(r"-d '(.*?)'", l)
                if match: current_payload = match.group(1)
            j += 1
            
    if in_curl and line.strip().startswith("-H 'X-SIGNATURE:"):
        # We need to replace this line with the mathematically correct signature
        if current_url and current_token and current_timestamp and current_payload:
            sig = generate_signature(current_url, current_token, current_payload, current_timestamp, secret)
            
            # for 11.2 we need invalid signature
            if "11.2" in current_scenario:
                sig = sig + "INVALID"
                
            line = f"  -H 'X-SIGNATURE: {sig}' \\\n"
            
    if line.strip() == "```" and in_curl:
        in_curl = False
        
    new_lines.append(line)
    i += 1

with open(md_file, 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Signatures fixed and written to markdown file.")
