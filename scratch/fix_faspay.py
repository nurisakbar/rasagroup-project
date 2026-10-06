import re

filepath = "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/index.blade.php"

with open(filepath, 'r') as f:
    content = f.read()

# Fix the escaped quotes
content = content.replace(r"\'", "'")
# Also fix the weird formatting where <div class="col-md-6"> is not indented correctly
content = content.replace("@if($faspayActive['active_faspay_sinarmas_va'] ?? true)\n<div", "@if($faspayActive['active_faspay_sinarmas_va'] ?? true)\n                                                <div")
content = content.replace("@if($faspayActive['active_faspay_maybank_va'] ?? true)\n<div", "@if($faspayActive['active_faspay_maybank_va'] ?? true)\n                                                <div")
content = content.replace("@if($faspayActive['active_faspay_danamon_va'] ?? true)\n<div", "@if($faspayActive['active_faspay_danamon_va'] ?? true)\n                                                <div")
content = content.replace("@if($faspayActive['active_faspay_bsi_va'] ?? true)\n<div", "@if($faspayActive['active_faspay_bsi_va'] ?? true)\n                                                <div")
content = content.replace("@if($faspayActive['active_faspay_bca_va'] ?? true)\n<div", "@if($faspayActive['active_faspay_bca_va'] ?? true)\n                                                <div")

# Fix missing ones that didn't match the regex due to previous replacements
# We already did replace_file_content earlier which was partially successful maybe? Let's check.
# The python script I wrote generated some weird output because of `\1`. I should just re-apply the fixed ones.

with open(filepath, 'w') as f:
    f.write(content)
print("Fixed backslashes")
