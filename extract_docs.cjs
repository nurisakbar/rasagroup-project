const fs = require('fs');
const content = fs.readFileSync('/Users/nurisakbar/.gemini/antigravity-ide/brain/54303d82-02f2-4060-9ff4-5e7b18141343/.system_generated/steps/91/content.md', 'utf8');

// Unescape unicode
const unescaped = content.replace(/\\u003c/g, '<').replace(/\\u003e/g, '>').replace(/\\u0026/g, '&');

// Extract all <pre> or code blocks containing JSON
const regex = /<code[^>]*>([\s\S]*?)<\/code>/gi;
let match;
let count = 0;
while ((match = regex.exec(unescaped)) !== null) {
    const code = match[1].replace(/<[^>]+>/g, ''); // strip inner html
    if (code.includes('{') || code.includes('partnerReferenceNo')) {
        console.log(`--- CODE BLOCK ${count} ---`);
        console.log(code);
        count++;
    }
}
