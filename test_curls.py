import re
import json
import subprocess

md_file = '/Applications/MAMP/htdocs/rasagroup/UAT OK/Hasil_Testing_DEV_cURL.md'

def extract_between(text, start, end):
    try:
        return text.split(start)[1].split(end)[0].strip()
    except IndexError:
        return ""

with open(md_file, 'r', encoding='utf-8') as f:
    content = f.read()

scenarios = content.split("## Skenario ")

print("=== Running cURL Tests ===")
for s in scenarios[1:]:
    scenario_id = s.split(":")[0].strip()
    
    curl_block = extract_between(s, "```bash", "```")
    if not curl_block:
        continue
        
    status_match = re.search(r"### API Response \(Status: (\d+)\):", s)
    expected_status = status_match.group(1) if status_match else "Unknown"
    
    # We will run the raw curl command, but we need to add -w "%{http_code}" to capture the status
    # To do this safely, we will execute the bash command.
    
    # Clean up the curl command so it runs in one line or handle multiline safely
    # It's already valid bash syntax. We can run it via subprocess.
    
    # We want to capture stdout and stderr, and also HTTP status.
    # The easiest way is to modify the curl string slightly or just run it and check response JSON.
    # But some might not return JSON if it's 404 or 500 from nginx.
    # Let's run it as-is and capture stdout.
    
    try:
        # Run using bash -c
        # Adding -i to curl might mess up JSON parsing, so we just run as is
        cmd = curl_block.replace("curl -s ", "curl -s -w '\\nHTTP_STATUS:%{http_code}' ")
        result = subprocess.run(["bash", "-c", cmd], capture_output=True, text=True)
        
        output = result.stdout.strip()
        
        # Parse output for HTTP_STATUS
        if "HTTP_STATUS:" in output:
            parts = output.split("HTTP_STATUS:")
            body = parts[0].strip()
            status = parts[1].strip()
        else:
            body = output
            status = "Error"
            
        print(f"[{scenario_id}] Expected: {expected_status}, Actual: {status}")
        try:
            parsed = json.loads(body)
            print(f"  Response: {parsed.get('responseMessage', parsed.get('responseCode', 'No message'))}")
        except:
            print(f"  Response (raw): {body[:100]}...")
            
    except Exception as e:
        print(f"[{scenario_id}] Execution Error: {e}")
