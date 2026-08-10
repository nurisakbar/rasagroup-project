import re

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

scenarios = content.split("## Skenario ")
new_content = [scenarios[0]]

for s in scenarios[1:]:
    # Find the cURL block
    curl_match = re.search(r"```bash\n(.*?)\n```", s, re.DOTALL)
    if curl_match:
        curl_cmd = curl_match.group(1)
        
        # Check if ORIGIN is missing, if so, inject it before X-PARTNER-ID or X-TIMESTAMP
        if "ORIGIN" not in curl_cmd:
            curl_cmd = curl_cmd.replace(
                "-H 'X-TIMESTAMP:", 
                "-H 'ORIGIN: dev.rasaconnect.com' \\\n  -H 'X-TIMESTAMP:"
            )
            
        s = s.replace(curl_match.group(1), curl_cmd)
        
    new_content.append("## Skenario " + s)

with open(md_file, 'w', encoding='utf-8') as f:
    f.write("".join(new_content))

print("Injected ORIGIN header into all cURL commands.")
