import re

file_path = '/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/checkout/success.blade.php'
with open(file_path, 'r') as f:
    content = f.read()

# Replace QRIS
qris_pattern = r'(<!-- QRIS -->\s*)<div class="form-check(.*?)pay_faspay_qris(.*?)</div>'
content = re.sub(qris_pattern, r'@if($faspayActive[\'faspay_qris\'] ?? true)\n                \1<div class="form-check\2pay_faspay_qris\3</div>\n                @endif', content, flags=re.DOTALL)

banks = [
    ('bca', 'bca_va'),
    ('mandiri', 'mandiri_va'),
    ('bsi', 'bsi_va'),
    ('danamon', 'danamon_va'),
    ('sinarmas', 'sinarmas_va'),
    ('maybank', 'maybank_va'),
    ('bni', 'bni_va'),
    ('permata', 'permata_va'),
    ('cimb', 'cimb_va')
]

for bank, va_key in banks:
    pattern = r'(\s*)<div class="form-check d-flex align-items-center mb-2 p-2 rounded"[^>]*onclick="document\.getElementById\(\'pay_faspay_' + bank + r'\'\)\.click\(\)"(.*?)</div>'
    
    def repl(m):
        indent = m.group(1)
        inner = m.group(2)
        full_match = m.group(0)
        return f"{indent}@if($faspayActive['faspay_{va_key}'] ?? true){full_match}{indent}@endif"
    
    content = re.sub(pattern, repl, content, flags=re.DOTALL)

with open(file_path, 'w') as f:
    f.write(content)
print("Done")
