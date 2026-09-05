from bs4 import BeautifulSoup
import sys

with open('/Users/nurisakbar/.gemini/antigravity-ide/brain/f21b034b-37c6-470f-b4ca-43aa247a5615/.system_generated/steps/944/content.md', 'r') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
print(soup.get_text('\n', strip=True))
