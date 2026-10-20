<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mdFile = __DIR__ . '/../docs/MANUAL_BOOK_ADMIN_ORDER_RASACONNECT.md';
$htmlFile = __DIR__ . '/../docs/MANUAL_BOOK_ADMIN_ORDER_RASACONNECT.html';
$pdfFile = __DIR__ . '/../docs/MANUAL_BOOK_ADMIN_ORDER_RASACONNECT.pdf';
$artifactPdf = '/Users/nurisakbar/.gemini/antigravity-ide/brain/3d77e286-5219-49d3-9d80-1cb781181f51/MANUAL_BOOK_ADMIN_ORDER_RASACONNECT.pdf';

if (!file_exists($mdFile)) {
    die("File MD tidak ditemukan: $mdFile\n");
}

$rawMd = file_get_contents($mdFile);

// Strip redundant top document title before DAFTAR ISI if present
$rawMd = preg_replace('/^# BUKU PANDUAN.*?\n---\s*\n/s', '', $rawMd);

// Strip raw <i> tags if any
$rawMd = preg_replace('/<i class="[^"]*"><\/i>\s*/i', '', $rawMd);

// Process Alert blockquotes before converting markdown
$rawMd = preg_replace_callback('/^>\s*\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*\n((?:^>.*$\n?)+)/m', function ($matches) {
    $type = strtolower($matches[1]);
    $content = preg_replace('/^>\s?/m', '', $matches[2]);
    $title = strtoupper($type);
    return "<div class=\"alert alert-{$type}\"><div class=\"alert-title\"><i class=\"icon-{$type}\"></i> {$title}</div>{$content}</div>\n";
}, $rawMd);

// Convert Markdown to HTML
$htmlContent = \Illuminate\Support\Str::markdown($rawMd);

// Convert local image paths to base64 or absolute file URLs so Chrome renders them reliably
$imgDir = __DIR__ . '/../docs/';
$htmlContent = preg_replace_callback('/<img\s+src="([^"]+)"\s+alt="([^"]*)"([^>]*)>/i', function ($m) use ($imgDir) {
    $src = $m[1];
    $alt = $m[2];
    $extra = $m[3];

    // If relative path
    if (!str_starts_with($src, 'http') && !str_starts_with($src, 'data:')) {
        $realPath = realpath($imgDir . $src);
        if ($realPath && file_exists($realPath)) {
            $mime = mime_content_type($realPath);
            $b64 = base64_encode(file_get_contents($realPath));
            $src = "data:{$mime};base64,{$b64}";
        }
    }

    return "<div class=\"img-container\"><img src=\"{$src}\" alt=\"{$alt}\"{$extra}></div>";
}, $htmlContent);

// Build complete HTML template
$fullHtml = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Panduan Operasional Admin - RasaConnect</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            mermaid.initialize({
                startOnLoad: true,
                theme: 'neutral',
                flowchart: { curve: 'basis', useMaxWidth: true }
            });
        });
    </script>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
            @bottom-right {
                content: counter(page);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 9pt;
                color: #64748b;
            }
            @bottom-left {
                content: "RasaConnect | Manual Book Admin v2.0";
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                font-size: 9pt;
                color: #64748b;
            }
        }

        *, *:before, *:after {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1e293b;
            line-height: 1.6;
            font-size: 10pt;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        /* Cover Section */
        .cover-page {
            page-break-after: always;
            padding-top: 60px;
            text-align: center;
        }
        .cover-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #8B1E1E;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11pt;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        .cover-title {
            font-size: 26pt;
            font-weight: 800;
            color: #8B1E1E;
            line-height: 1.25;
            margin: 0 0 15px 0;
        }
        .cover-subtitle {
            font-size: 14pt;
            color: #475569;
            font-weight: 500;
            margin-bottom: 40px;
        }
        .cover-meta-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            display: inline-block;
            padding: 18px 30px;
            text-align: left;
            margin-top: 30px;
            font-size: 10pt;
        }
        .cover-meta-box table {
            margin: 0;
            border-collapse: collapse;
        }
        .cover-meta-box td {
            padding: 4px 10px;
            border: none;
        }

        a, a:visited, a:hover, a:active {
            color: #1e293b !important;
            text-decoration: none !important;
        }

        /* Headings */
        h1 {
            color: #8B1E1E;
            font-size: 18pt;
            font-weight: 800;
            border-bottom: 2px solid #8B1E1E;
            padding-bottom: 6px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        h2 {
            color: #1e293b;
            font-size: 13.5pt;
            font-weight: 700;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 12px;
            page-break-after: avoid;
            page-break-before: always;
        }
        h2:first-of-type {
            page-break-before: auto;
        }
        h3 {
            color: #334155;
            font-size: 11pt;
            font-weight: 700;
            margin-top: 18px;
            margin-bottom: 8px;
            page-break-after: avoid;
        }
        h4 {
            color: #475569;
            font-size: 10pt;
            font-weight: 600;
            margin-top: 14px;
            margin-bottom: 6px;
            page-break-after: avoid;
        }

        /* Section Breaks */
        .chapter-break {
            page-break-before: always;
        }

        p {
            margin-top: 0;
            margin-bottom: 10px;
            text-align: justify;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 18px 0;
            font-size: 9pt;
            page-break-inside: avoid;
        }
        table, th, td {
            border: 1px solid #cbd5e1;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-align: left;
            padding: 8px 12px;
        }
        td {
            padding: 7px 12px;
            vertical-align: top;
            color: #1e293b;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Code and Pre */
        code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 8.5pt;
            border: 1px solid #e2e8f0;
        }
        pre {
            background-color: #1e293b;
            color: #f8fafc;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 8pt;
            overflow-x: auto;
            page-break-inside: avoid;
        }
        pre code {
            background: none;
            color: inherit;
            border: none;
            padding: 0;
        }

        /* Alerts */
        .alert {
            padding: 10px 14px;
            margin: 14px 0;
            border-radius: 6px;
            border-left: 4px solid;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        .alert-tip, .alert-note {
            background-color: #f0fdf4;
            border-color: #22c55e;
            color: #14532d;
        }
        .alert-warning, .alert-important {
            background-color: #fffbeb;
            border-color: #f59e0b;
            color: #78350f;
        }
        .alert-caution, .alert-danger {
            background-color: #fff1f2;
            border-color: #f43f5e;
            color: #881337;
        }
        .alert-title {
            font-weight: 700;
            margin-bottom: 4px;
            font-size: 9.5pt;
        }

        /* Images */
        .img-container {
            text-align: center;
            margin: 14px 0 6px 0;
            page-break-inside: avoid;
        }
        .img-container img {
            max-width: 95%;
            max-height: 520px;
            object-fit: contain;
            height: auto;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
        }
        .img-container + p {
            text-align: center;
            font-size: 8.5pt;
            color: #64748b;
            font-style: italic;
            margin-top: 2px;
            margin-bottom: 16px;
            page-break-before: avoid;
        }

        /* Mermaid Diagrams */
        .mermaid {
            text-align: center;
            margin: 16px 0;
            page-break-inside: avoid;
        }

        /* Table of Contents */
        .toc-list {
            list-style: none;
            padding-left: 0;
        }
        .toc-list li {
            padding: 4px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .toc-list a {
            color: #1e293b;
            text-decoration: none;
            font-weight: 600;
        }

        ul, ol {
            margin-top: 0;
            margin-bottom: 10px;
            padding-left: 22px;
        }
        li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-badge">Standard Operating Procedure (SOP)</div>
        <div class="cover-title">BUKU PANDUAN OPERASIONAL ADMIN<br>(MANUAL BOOK)</div>
        <div class="cover-subtitle">Penerimaan, Verifikasi & Pemrosesan Pesanan Portal RasaConnect</div>
        
        <div style="margin: 40px auto; width: 120px; height: 3px; background: #8B1E1E;"></div>

        <div class="cover-meta-box">
            <table>
                <tr>
                    <td><strong>Versi Dokumen</strong></td>
                    <td>: 2.0 (Edisi Lengkap dengan Tangkapan Layar)</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Efektif</strong></td>
                    <td>: Oktober 2026</td>
                </tr>
                <tr>
                    <td><strong>Target Pengguna</strong></td>
                    <td>: Admin Customer Service, Tim Operasional Hub / Gudang, & Tim Finance</td>
                </tr>
                <tr>
                    <td><strong>Sistem Terkait</strong></td>
                    <td>: RasaConnect Web Portal, QAD ERP, Jubelio Omnichannel, WMS, & EkspedisiKu</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        {$htmlContent}
    </div>

</body>
</html>
HTML;

file_put_contents($htmlFile, $fullHtml);
echo "HTML berhasil dibuat: $htmlFile (" . filesize($htmlFile) . " bytes)\n";

// Execute Google Chrome headless --print-to-pdf
$chrome = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
$cmd = sprintf(
    '"%s" --headless=new --disable-gpu --virtual-time-budget=6000 --run-all-compositor-stages-before-draw --no-pdf-header-footer --print-to-pdf="%s" "file://%s"',
    $chrome,
    $pdfFile,
    $htmlFile
);

echo "Mengonversi ke PDF menggunakan Google Chrome...\n";
exec($cmd, $output, $returnCode);

if ($returnCode === 0 && file_exists($pdfFile) && filesize($pdfFile) > 0) {
    echo "PDF BERHASIL DIGENERATE!\n";
    echo "Lokasi: $pdfFile\n";
    echo "Ukuran: " . number_format(filesize($pdfFile) / 1024, 2) . " KB\n";

    // Copy to artifact directory
    copy($pdfFile, $artifactPdf);
    echo "Tersalin ke artifact: $artifactPdf\n";
} else {
    echo "Gagal membuat PDF. Return code: $returnCode\n";
    echo implode("\n", $output) . "\n";
}
