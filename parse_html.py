import re
import html

with open('/Users/nurisakbar/.gemini/antigravity-ide/brain/28832cbf-57d5-47d3-9b76-9db5ad28f243/.system_generated/steps/24/content.md', 'r') as f:
    content = f.read()

# Basic HTML tag stripping
text = re.sub(r'<[^>]+>', ' ', content)
text = html.unescape(text)

# Clean up multiple spaces and newlines
text = re.sub(r'[ \t]+', ' ', text)
text = re.sub(r'\n\s*\n', '\n\n', text)

with open('parsed_docs.txt', 'w') as f:
    f.write(text)
