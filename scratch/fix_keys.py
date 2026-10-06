import re

filepath = "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/index.blade.php"

with open(filepath, 'r') as f:
    content = f.read()

# Replace $faspayActive['active_faspay_xxx'] with $faspayActive['faspay_xxx']
content = re.sub(r"\$faspayActive\['active_faspay_([a-z_]+)'\]", r"$faspayActive['faspay_\1']", content)

with open(filepath, 'w') as f:
    f.write(content)
print("Fixed array keys")
