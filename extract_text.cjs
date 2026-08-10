const fs = require('fs');
const content = fs.readFileSync('/Users/nurisakbar/.gemini/antigravity-ide/brain/54303d82-02f2-4060-9ff4-5e7b18141343/.system_generated/steps/91/content.md', 'utf8');
const unescaped = content.replace(/\\u003c/g, '<').replace(/\\u003e/g, '>').replace(/\\u0026/g, '&');
const text = unescaped.replace(/<[^>]+>/g, ' ');
fs.writeFileSync('faspay_snap_qris_text.txt', text);
