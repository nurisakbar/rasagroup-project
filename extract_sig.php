<?php
$html = file_get_contents('/Users/nurisakbar/.gemini/antigravity-ide/brain/54303d82-02f2-4060-9ff4-5e7b18141343/.system_generated/steps/149/content.md');
$html = str_replace(['\u003c', '\u003e', '\u0026'], ['<', '>', '&'], $html);
$text = strip_tags($html);
echo substr($text, 0, 5000);
