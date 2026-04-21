
import os
import re

source_file = 'public/themes/nest-frontend/index.html'
output_dir = 'resources/views/themes/nest'

# Read content
with open(source_file, 'r', encoding='utf-8') as f:
    lines = f.readlines()

def intelligent_replace(text):
    # 1. src="assets/..."
    text = re.sub(r'src=["\']assets/([^"\']+)["\']', 
                  r'src="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
    
    # 2. href="assets/..."
    text = re.sub(r'href=["\']assets/([^"\']+)["\']', 
                  r'href="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
                  
    # 3. url('assets/...') or url("assets/...") or url(assets/...)
    text = re.sub(r'url\([\'"]?assets/([^\'"\)]+)[\'"]?\)', 
                  r'url("{{ asset(\'themes/nest-frontend/assets/\1\') }}")', text)

    # 4. data-background="assets/..."
    text = re.sub(r'data-background=["\']assets/([^"\']+)["\']', 
                  r'data-background="{{ asset(\'themes/nest-frontend/assets/\1\') }}"', text)
                  
    return text

# Extract Sections based on line numbers (0-indexed)
# Header: 156-666 (Index 155 to 666) -> inclusive of closing div 666?
# Line 666 is </header>. Yes.
header_lines = lines[155:666] 

# Mobile Header: 667-848 -> 
# Line 667 is <div class="mobile-header-active...
# Line 848 is </div> (closing mobile header wrapper)
mobile_header_lines = lines[666:849] # Python slice is end-exclusive

# Footer: 5240-5443
# Line 5240 is <footer class="main">
# Line 5443 is </footer>
footer_lines = lines[5239:5443]

# Scripts: 5455-5476
scripts_lines = lines[5454:5477]

# Save Header
with open(f'{output_dir}/partials/header.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(header_lines)))

# Save Mobile Header
with open(f'{output_dir}/partials/mobile-header.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(mobile_header_lines)))

# Save Footer
with open(f'{output_dir}/partials/footer.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(footer_lines)))

# Save Scripts
with open(f'{output_dir}/partials/scripts.blade.php', 'w') as f:
    f.write(intelligent_replace("".join(scripts_lines)))

print("Partials extracted and saved successfully.")
