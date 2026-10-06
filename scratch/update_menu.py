import os

files_to_update = [
    "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/themes/nest/partials/header.blade.php",
    "/Applications/MAMP/htdocs/rasagroup/rasagroup-project/resources/views/themes/nest/partials/mobile-header.blade.php"
]

for filepath in files_to_update:
    if os.path.exists(filepath):
        with open(filepath, 'r') as f:
            content = f.read()
        
        # Replace >DRIPP< with >DRiPP<
        content = content.replace('>DRIPP<', '>DRiPP<')
        
        # Replace >MULTIBEV< with >MULTiBEV<
        content = content.replace('>MULTIBEV<', '>MULTiBEV<')
        
        with open(filepath, 'w') as f:
            f.write(content)
            
print("Updated menus successfully.")
