<?php
$html = file_get_contents('http://localhost:8001/checkout');
// This will just give us the redirect. We need an authenticated session.
