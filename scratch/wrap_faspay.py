import re

filepath = "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/index.blade.php"

with open(filepath, 'r') as f:
    content = f.read()

banks = [
    'mandiri_va', 'sinarmas_va', 'permata_va', 'maybank_va', 
    'danamon_va', 'bsi_va', 'cimb_va', 'bni_va', 'bca_va'
]

for bank in banks:
    # Pattern to match the col-md-6 wrapping the payment option for this bank
    pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_' + bank + r'\'\)")'
    
    # We want to replace it with @if($faspayActive['active_faspay_{bank}'] ?? true) ...
    # But we need to find the matching closing </div></div>.
    # A simpler way is to match from <div class="col-md-6"> down to the closing </div> of col-md-6.
    
    # Regex to capture the whole col-md-6 block
    block_pattern = r'(<div class="col-md-6">\s*<div class="payment-option mb-10 payment-method-card" onclick="selectPayment\(\'faspay_' + bank + r'\'\)"[\s\S]*?</div>\s*</div>)'
    
    replacement = r'@if($faspayActive[\'active_faspay_' + bank + r'\'] ?? true)\n\1\n@endif'
    
    # Check if we already wrapped it (to avoid double wrap)
    if f"@if($faspayActive['active_faspay_{bank}']" not in content:
        content = re.sub(block_pattern, replacement, content)

# For QRIS, it's slightly different
qris_pattern = r'(<div class="payment-option mb-10 payment-method-card active" onclick="selectPayment\(\'faspay_qris\'\)"[\s\S]*?</div>\s*</div>)'
if "@if($faspayActive['active_faspay_qris']" not in content:
    qris_repl = r'@if($faspayActive[\'active_faspay_qris\'] ?? true)\n\1\n@endif'
    content = re.sub(qris_pattern, qris_repl, content)

with open(filepath, 'w') as f:
    f.write(content)
print("Updated index.blade.php")
