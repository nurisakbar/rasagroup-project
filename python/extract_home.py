
import os
import re

source_file = 'public/themes/nest-frontend/index.html'
output_dir = 'resources/views/themes/nest/home'

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

# Main Content (inside <main>): 851-5238 (Index 850-5238)
# Line 850: <main class="main">
# Line 5239: </main>
main_lines = lines[850:5239]

# Prepend extends and section
blade_content = "@extends('themes.nest.layouts.app')\n\n@section('content')\n" + intelligent_replace("".join(main_lines)) + "\n@endsection\n"

# Save Home Index
with open(f'{output_dir}/index.blade.php', 'w') as f:
    f.write(blade_content)

print("Home content extracted.")
