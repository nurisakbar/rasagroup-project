
import re

file_path = 'resources/views/themes/nest/home/index.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# The pattern is: style="background-image: url("{{ asset(...) }}")"
# This is double quotes inside double quotes. The browser breaks.
# We want: style="background-image: url('{{ asset(...) }}')"

# Let's try to match exactly what we saw in the view_file tool output:
# style="background-image: url("{{ asset('themes/nest-frontend/assets/imgs/slider/slider-1.png') }}")"

def replacer(match):
    # match.group(0) is the whole thing
    # match.group(1) is the asset path
    asset_path = match.group(1)
    return f'style="background-image: url({{{{ asset(\'{asset_path}\') }}}})"'

# Regex:
# style="background-image: url\("{{ asset\('([^']+)'\) }}"\)"
# Let's be less strict with regex.

new_content = re.sub(
    r'style="background-image: url\("{{ asset\(\'([^\']+)\'\) }}"\)"',
    lambda m: f'style="background-image: url({{{{ asset(\'{m.group(1)}\') }}}})"',
    content
)

# Also check for single quotes used in the attribute itself if inconsistent?
# The view_file output showed attribute using double quotes: style="background-image: url("...")"

if new_content == content:
    # Try alternate pattern if the spaces are different
    # Maybe simply replace `url("{{` with `url({{` and `}}")"` with `}})`
    # Or better `url("` -> `url('` and `")` -> `')` inside that context?
    
    # Let's do a targeted replace for the specific known slider images to be safe.
    content = content.replace(
        'style="background-image: url("{{ asset(\'themes/nest-frontend/assets/imgs/slider/slider-1.png\') }}")"',
        'style="background-image: url({{ asset(\'themes/nest-frontend/assets/imgs/slider/slider-1.png\') }})"'
    )
    content = content.replace(
        'style="background-image: url("{{ asset(\'themes/nest-frontend/assets/imgs/slider/slider-2.png\') }}")"',
        'style="background-image: url({{ asset(\'themes/nest-frontend/assets/imgs/slider/slider-2.png\') }})"'
    )
    
    if content != open(file_path, 'r', encoding='utf-8').read():
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        print("Fixed slider via string replace.")
    else:
        print("Could not find pattern to fix.")
else:
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(new_content)
    print("Fixed slider via regex.")
