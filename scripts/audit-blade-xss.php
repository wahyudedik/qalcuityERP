<?php
/**
 * Script untuk audit penggunaan {!! !!} di Blade views
 * Mengidentifikasi potensi XSS vulnerability
 * 
 * Cara pakai:
 * php scripts/audit-blade-xss.php
 */

$viewsDir = __DIR__ . '/../resources/views';
$riskyPatterns = [];
$safePatterns = [];
$totalFound = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

echo "🔍 Auditing Blade Views for XSS Risk...\n\n";

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'blade.php')
        continue;

    $filepath = $file->getPathname();
    $relativePath = str_replace(__DIR__ . '/../', '', $filepath);
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $lineNumber = $lineNum + 1;

        // Cari semua {!! !!}
        if (preg_match_all('/\{!!\s*(.+?)\s*!!\}/', $line, $matches)) {
            foreach ($matches[1] as $index => $expression) {
                $totalFound++;
                $expression = trim($expression);

                // Check jika sudah aman
                $isSafe = false;
                $reason = '';

                // Safe: json_encode()
                if (strpos($expression, 'json_encode(') !== false) {
                    $isSafe = true;
                    $reason = '✅ SAFE - Using json_encode()';
                }
                // Safe: nl2br(e())
                elseif (strpos($expression, 'nl2br(e(') !== false) {
                    $isSafe = true;
                    $reason = '✅ SAFE - Using nl2br(e())';
                }
                // Safe: e()
                elseif (preg_match('/^e\(/', $expression)) {
                    $isSafe = true;
                    $reason = '✅ SAFE - Using e()';
                }
                // Safe: Constants/controlled data (icons, svg paths from code)
                elseif (preg_match('/\$(icon|svgPath|header|indent|icons\[)/', $expression)) {
                    $isSafe = true;
                    $reason = '✅ SAFE - Controlled variable (icon/SVG)';
                }
                // Safe: Method calls on controlled objects
                elseif (preg_match('/\$\w+->(getIcon|format|toHtml|render)\(/', $expression)) {
                    $isSafe = true;
                    $reason = '✅ SAFE - Method returns safe HTML';
                }

                if ($isSafe) {
                    $safePatterns[] = [
                        'file' => $relativePath,
                        'line' => $lineNumber,
                        'expression' => $expression,
                        'reason' => $reason,
                    ];
                } else {
                    $riskyPatterns[] = [
                        'file' => $relativePath,
                        'line' => $lineNumber,
                        'expression' => $expression,
                        'full_line' => trim($line),
                    ];
                }
            }
        }
    }
}

// Print results
echo "📊 AUDIT SUMMARY\n";
echo "================\n\n";
echo "Total {!! !!} found: {$totalFound}\n";
echo "✅ Safe patterns: " . count($safePatterns) . "\n";
echo "⚠️  Potentially risky: " . count($riskyPatterns) . "\n\n";

if (!empty($riskyPatterns)) {
    echo "⚠️  RISKY PATTERNS (Need Review):\n";
    echo "==================================\n\n";

    foreach ($riskyPatterns as $risky) {
        echo "📁 File: {$risky['file']}:{$risky['line']}\n";
        echo " Expression: {!! {$risky['expression']} !!}\n";
        echo "📝 Full line: {$risky['full_line']}\n";
        echo "⚠️  Risk: User input might not be escaped\n";
        echo str_repeat('-', 80) . "\n\n";
    }
}

if (!empty($safePatterns)) {
    echo "✅ SAFE PATTERNS:\n";
    echo "=================\n\n";

    foreach (array_slice($safePatterns, 0, 10) as $safe) {
        echo "📁 {$safe['file']}:{$safe['line']} - {$safe['reason']}\n";
    }

    if (count($safePatterns) > 10) {
        echo "... and " . (count($safePatterns) - 10) . " more safe patterns\n";
    }
    echo "\n";
}

// Export risky patterns to JSON for fixing
if (!empty($riskyPatterns)) {
    $outputFile = __DIR__ . '/xss-risky-patterns.json';
    file_put_contents($outputFile, json_encode($riskyPatterns, JSON_PRETTY_PRINT));
    echo "📄 Risky patterns exported to: {$outputFile}\n";
}

echo "\n✅ Audit complete!\n";
