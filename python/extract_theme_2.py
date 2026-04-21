
import os
import re

source_file = 'public/themes/nest-frontend/index.html'
output_dir = 'resources/views/themes/nest/partials'

# Read content
with open(source_file, 'r', encoding='utf-8') as f:
    lines = f.readlines()

def intelligent_replace(text):
    text = re.sub(r'src=["\']assets/([^"\']+)["\']', 
                  r'src="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
    text = re.sub(r'href=["\']assets/([^"\']+)["\']', 
                  r'href="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
    text = re.sub(r'url\([\'"]?assets/([^\'"\)]+)[\'"]?\)', 
                  r'url("{{ asset(\'themes/nest-frontend/assets/\1\') }}")', text)
    text = re.sub(r'data-background=["\']assets/([^"\']+)["\']', 
                  r'data-background="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
    return text

# Modals: 22-155 (Index 21-155)
modals_lines = lines[21:156]

# Preloader: 5445-5453 (Index 5444-5453)
preloader_lines = lines[5444:5454]

# Save Modals
with open(f'{output_dir}/modals.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(modals_lines)))

# Save Preloader
with open(f'{output_dir}/preloader.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(preloader_lines)))

print("More partials extracted.")
