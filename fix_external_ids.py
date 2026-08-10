import re
import random

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace all EXT-... with a numeric string
# To ensure uniqueness, we'll replace them iteratively.
scenarios = content.split("## Skenario ")

new_content = [scenarios[0]]
last_valid_external_id = ""

for s in scenarios[1:]:
    # Find the current X-EXTERNAL-ID
    match = re.search(r"-H 'X-EXTERNAL-ID: ([^']+)'", s)
    if match:
        old_id = match.group(1)
        scenario_id_match = re.match(r"^([\d\.]+)", s)
        scenario_id = scenario_id_match.group(1) if scenario_id_match else ""
        
        if "11.5" in scenario_id:
            # 11.5 Conflict MUST use the exact same ID as the previous request (11.4)
            new_id = last_valid_external_id
        else:
            # Generate a 16-digit numeric string
            new_id = str(random.randint(1000000000000000, 9999999999999999))
            last_valid_external_id = new_id
            
        s = s.replace(f"-H 'X-EXTERNAL-ID: {old_id}'", f"-H 'X-EXTERNAL-ID: {new_id}'")
        
    new_content.append("## Skenario " + s)

with open(md_file, 'w', encoding='utf-8') as f:
    f.write("".join(new_content))

print("X-EXTERNAL-ID has been updated to purely NUMERIC STRINGS.")
