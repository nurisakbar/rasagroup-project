
import os

target_dir = 'resources/views/themes/nest'

for root, dirs, files in os.walk(target_dir):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Fix the escaped quotes issue
            # Replace asset(\' with asset('
            # Replace \') with ')
            
            new_content = content.replace("asset(\\'", "asset('")
            new_content = new_content.replace("\\')", "')")
            
            # Also check if double quotes were escaped like \"
            new_content = new_content.replace('asset(\\"', 'asset("')
            new_content = new_content.replace('\\")', '")')
            
            if new_content != content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Fixed: {path}")

print("Syntax fix complete.")
