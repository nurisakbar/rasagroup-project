import re
import json

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

scenarios = content.split("## Skenario ")
new_content = [scenarios[0]]

for s in scenarios[1:]:
    # Only for Payment scenarios (hit /payment endpoint)
    if "api/faspay/snap/payment" in s:
        # Find the payload
        match = re.search(r"-d '(\{.*?\})'", s)
        if match:
            payload_str = match.group(1)
            try:
                payload = json.loads(payload_str)
                # Add missing real-world fields if not present
                if "trxDateTime" not in payload:
                    # Extract timestamp from headers to use as trxDateTime
                    ts_match = re.search(r"-H 'X-TIMESTAMP:\s*([^']+)'", s)
                    ts = ts_match.group(1) if ts_match else "2026-08-10T11:54:19+07:00"
                    payload["trxDateTime"] = ts
                
                if "referenceNo" not in payload:
                    # Generate a random 16 digit reference no
                    import random
                    payload["referenceNo"] = str(random.randint(1000000000000000, 9999999999999999))
                
                # Convert paymentRequestId to look like a UUID for maximum realism (if it starts with PAY-)
                if payload.get("paymentRequestId", "").startswith("PAY-"):
                    import uuid
                    payload["paymentRequestId"] = str(uuid.uuid4())

                # Put it back
                new_payload_str = json.dumps(payload)
                s = s.replace(f"-d '{payload_str}'", f"-d '{new_payload_str}'")
            except Exception as e:
                print("Error parsing JSON in scenario:", e)
                
    new_content.append("## Skenario " + s)

with open(md_file, 'w', encoding='utf-8') as f:
    f.write("".join(new_content))

print("Injected trxDateTime, referenceNo, and UUIDs to Payment payloads.")
