import re

filepath = "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/index.blade.php"

with open(filepath, 'r') as f:
    content = f.read()

# Fix production Mandiri VA
prod_mandiri_pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_mandiri_va\'\)"[\s\S]*?</div>\s*</div>)'
if "@if($faspayActive['active_faspay_mandiri_va']" not in content[:content.find('faspay_sinarmas_va')]:
    # only replace the first occurrence (production) if it lacks @if
    content = re.sub(prod_mandiri_pattern, r"@if($faspayActive['active_faspay_mandiri_va'] ?? true)\n\1\n@endif", content, count=1)

prod_permata_pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_permata_va\'\)"[\s\S]*?</div>\s*</div>)'
if "@if($faspayActive['active_faspay_permata_va']" not in content[:content.find('faspay_maybank_va')]:
    # only replace the first occurrence (production) if it lacks @if
    content = re.sub(prod_permata_pattern, r"@if($faspayActive['active_faspay_permata_va'] ?? true)\n\1\n@endif", content, count=1)

# Fix production CIMB VA
prod_cimb_pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_cimb_va\'\)"[\s\S]*?</div>\s*</div>)'
if "@if($faspayActive['active_faspay_cimb_va']" not in content[:content.find('faspay_bni_va')]:
    # only replace the first occurrence (production) if it lacks @if
    content = re.sub(prod_cimb_pattern, r"@if($faspayActive['active_faspay_cimb_va'] ?? true)\n\1\n@endif", content, count=1)

# Fix production BNI VA
prod_bni_pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_bni_va\'\)"[\s\S]*?</div>\s*</div>)'
if "@if($faspayActive['active_faspay_bni_va']" not in content[:content.find('faspay_bca_va')]:
    # only replace the first occurrence (production) if it lacks @if
    content = re.sub(prod_bni_pattern, r"@if($faspayActive['active_faspay_bni_va'] ?? true)\n\1\n@endif", content, count=1)


with open(filepath, 'w') as f:
    f.write(content)
print("Fixed remaining VAs")
