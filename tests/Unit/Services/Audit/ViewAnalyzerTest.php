<?php

namespace Tests\Unit\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;
use App\Services\Audit\ViewAnalyzer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ViewAnalyzer.
 *
 * Uses temporary Blade fixture files to test detection of:
 * - Missing responsive breakpoints
 * - Hardcoded pixel widths
 * - Form accessibility issues (missing labels, ARIA, validation feedback)
 * - Tables without mobile overflow handling
 *
 * Validates: Requirements 4.1, 4.2, 4.3, 4.4, 4.7, 4.8
 */
class ViewAnalyzerTest extends TestCase
{
    private string $fixtureDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureDir = sys_get_temp_dir().'/view_analyzer_test_'.uniqid();
        mkdir($this->fixtureDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->fixtureDir);
        parent::tearDown();
    }

    // ── Category ──────────────────────────────────────────────────

    public function test_category_returns_ui(): void
    {
        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $this->assertSame('ui', $analyzer->category());
    }

    // ── Responsiveness Detection ─────────────────────────────────

    public function test_detects_missing_responsive_breakpoints_in_layout_file(): void
    {
        $this->writeFixture('products/index.blade.php', <<<'BLADE'
<div class="grid grid-cols-4 gap-4">
    <div class="col-span-1">Sidebar</div>
    <div class="col-span-3">Content</div>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $responsiveFindings = $this->filterByCheck($findings, 'responsiveness');

        $this->assertNotEmpty($responsiveFindings, 'Should detect missing responsive breakpoints');
        $finding = reset($responsiveFindings);
        $this->assertSame('ui', $finding->category);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertStringContainsString('responsive breakpoints', $finding->title);
    }

    public function test_does_not_flag_file_with_responsive_breakpoints(): void
    {
        $this->writeFixture('products/index.blade.php', <<<'BLADE'
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    <div class="col-span-1">Item 1</div>
    <div class="col-span-1">Item 2</div>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $responsiveFindings = $this->filterByCheck($findings, 'responsiveness');
        $this->assertEmpty($responsiveFindings, 'Should not flag file with responsive breakpoints');
    }

    public function test_does_not_flag_file_without_layout_classes(): void
    {
        $this->writeFixture('partials/message.blade.php', <<<'BLADE'
<div class="bg-white rounded-lg p-4">
    <p class="text-gray-600">Hello world</p>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $responsiveFindings = $this->filterByCheck($findings, 'responsiveness');
        $this->assertEmpty($responsiveFindings, 'Should not flag file without layout classes');
    }

    // ── Hardcoded Pixel Detection ────────────────────────────────

    public function test_detects_hardcoded_pixel_widths(): void
    {
        $this->writeFixture('dashboard/widget.blade.php', <<<'BLADE'
<div style="width: 500px; height: 300px;">
    <img src="logo.png" style="width: 200px;">
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $pixelFindings = $this->filterByCheck($findings, 'hardcoded_pixels');

        $this->assertNotEmpty($pixelFindings, 'Should detect hardcoded pixel widths');
        $finding = reset($pixelFindings);
        $this->assertSame('ui', $finding->category);
        $this->assertSame(Severity::Low, $finding->severity);
        $this->assertStringContainsString('Hardcoded pixel', $finding->title);
    }

    public function test_ignores_small_pixel_values_for_borders(): void
    {
        $this->writeFixture('components/card.blade.php', <<<'BLADE'
<div style="border: 1px solid #ccc; border-radius: 2px;">
    <p style="margin: 0; padding: 4px;">Content</p>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $pixelFindings = $this->filterByCheck($findings, 'hardcoded_pixels');
        $this->assertEmpty($pixelFindings, 'Should ignore small pixel values (1px, 2px, 4px)');
    }

    public function test_ignores_safe_css_contexts(): void
    {
        $this->writeFixture('components/text.blade.php', <<<'BLADE'
<div style="font-size: 14px; line-height: 20px; border-radius: 8px;">
    <p style="letter-spacing: 5px;">Content</p>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $pixelFindings = $this->filterByCheck($findings, 'hardcoded_pixels');
        $this->assertEmpty($pixelFindings, 'Should ignore px in safe CSS contexts like font-size, line-height, border-radius');
    }

    public function test_does_not_flag_file_without_pixel_values(): void
    {
        $this->writeFixture('products/show.blade.php', <<<'BLADE'
<div class="w-full max-w-lg mx-auto">
    <h1 class="text-2xl font-bold">Product</h1>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $pixelFindings = $this->filterByCheck($findings, 'hardcoded_pixels');
        $this->assertEmpty($pixelFindings, 'Should not flag file without pixel values');
    }

    // ── Form Accessibility ───────────────────────────────────────

    public function test_detects_inputs_without_labels(): void
    {
        $this->writeFixture('forms/create.blade.php', <<<'BLADE'
<form method="POST" action="/products">
    @csrf
    <input type="text" name="name" class="form-input">
    <input type="email" name="email" class="form-input">
    <select name="category">
        <option value="">Select</option>
    </select>
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');

        $this->assertNotEmpty($accessibilityFindings, 'Should detect inputs without labels');
        $finding = reset($accessibilityFindings);
        $this->assertSame('ui', $finding->category);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertStringContainsString('accessibility', $finding->title);
    }

    public function test_does_not_flag_inputs_with_labels(): void
    {
        $this->writeFixture('forms/labeled.blade.php', <<<'BLADE'
<form method="POST" action="/products">
    @csrf
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-input">
    <label for="email">Email</label>
    <input type="email" name="email" id="email" class="form-input">
    @error('name')
        <span class="text-red-500">{{ $message }}</span>
    @enderror
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');
        $this->assertEmpty($accessibilityFindings, 'Should not flag inputs with proper labels');
    }

    public function test_does_not_flag_inputs_with_aria_label(): void
    {
        $this->writeFixture('forms/aria.blade.php', <<<'BLADE'
<form method="POST" action="/search">
    @csrf
    <input type="text" name="search" aria-label="Search products" class="form-input">
    @error('search')
        <span>{{ $message }}</span>
    @enderror
    <button type="submit">Search</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');
        $this->assertEmpty($accessibilityFindings, 'Should not flag inputs with aria-label');
    }

    public function test_does_not_flag_inputs_with_aria_labelledby(): void
    {
        $this->writeFixture('forms/aria-labelledby.blade.php', <<<'BLADE'
<form method="POST" action="/items">
    @csrf
    <span id="name-label">Name</span>
    <input type="text" name="name" aria-labelledby="name-label" class="form-input">
    @error('name')
        <span>{{ $message }}</span>
    @enderror
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');
        $this->assertEmpty($accessibilityFindings, 'Should not flag inputs with aria-labelledby');
    }

    public function test_does_not_flag_hidden_inputs(): void
    {
        $this->writeFixture('forms/hidden.blade.php', <<<'BLADE'
<form method="POST" action="/products">
    @csrf
    <input type="hidden" name="_token" value="abc123">
    <input type="hidden" name="tenant_id" value="1">
    @error('name')
        <span>{{ $message }}</span>
    @enderror
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');
        $this->assertEmpty($accessibilityFindings, 'Should not flag hidden inputs');
    }

    public function test_does_not_flag_inputs_inside_label_tags(): void
    {
        $this->writeFixture('forms/wrapped.blade.php', <<<'BLADE'
<form method="POST" action="/products">
    @csrf
    <label>
        Name
        <input type="text" name="name" class="form-input">
    </label>
    @error('name')
        <span>{{ $message }}</span>
    @enderror
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');
        $this->assertEmpty($accessibilityFindings, 'Should not flag inputs wrapped inside <label> tags');
    }

    public function test_detects_missing_validation_feedback(): void
    {
        $this->writeFixture('forms/no-validation.blade.php', <<<'BLADE'
<form method="POST" action="/products">
    @csrf
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-input">
    <button type="submit">Save</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $accessibilityFindings = $this->filterByCheck($findings, 'form_accessibility');

        $this->assertNotEmpty($accessibilityFindings, 'Should detect missing validation feedback');
        $finding = reset($accessibilityFindings);
        $this->assertStringContainsString('validation', $finding->description);
    }

    // ── Table Mobile Overflow ────────────────────────────────────

    public function test_detects_table_without_overflow_container(): void
    {
        $this->writeFixture('products/list.blade.php', <<<'BLADE'
<div class="bg-white rounded-lg">
    <table class="w-full text-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Product 1</td><td>100</td><td>50</td></tr>
        </tbody>
    </table>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tableFindings = $this->filterByCheck($findings, 'table_mobile');

        $this->assertNotEmpty($tableFindings, 'Should detect table without overflow container');
        $finding = reset($tableFindings);
        $this->assertSame('ui', $finding->category);
        $this->assertSame(Severity::Medium, $finding->severity);
        $this->assertStringContainsString('overflow', $finding->title);
    }

    public function test_does_not_flag_table_with_overflow_x_auto(): void
    {
        $this->writeFixture('products/scrollable.blade.php', <<<'BLADE'
<div class="bg-white rounded-lg">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr><th>Name</th><th>Price</th></tr>
            </thead>
            <tbody>
                <tr><td>Product 1</td><td>100</td></tr>
            </tbody>
        </table>
    </div>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tableFindings = $this->filterByCheck($findings, 'table_mobile');
        $this->assertEmpty($tableFindings, 'Should not flag table with overflow-x-auto wrapper');
    }

    public function test_does_not_flag_table_with_overflow_auto(): void
    {
        $this->writeFixture('products/overflow-auto.blade.php', <<<'BLADE'
<div class="overflow-auto">
    <table class="w-full text-sm">
        <thead><tr><th>Name</th></tr></thead>
        <tbody><tr><td>Item</td></tr></tbody>
    </table>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $tableFindings = $this->filterByCheck($findings, 'table_mobile');
        $this->assertEmpty($tableFindings, 'Should not flag table with overflow-auto wrapper');
    }

    // ── Empty / Nonexistent Directory ────────────────────────────

    public function test_returns_empty_for_empty_directory(): void
    {
        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    public function test_returns_empty_for_nonexistent_directory(): void
    {
        $analyzer = new ViewAnalyzer('/nonexistent/path');
        $findings = $analyzer->analyze();

        $this->assertSame([], $findings);
    }

    // ── Scanning Subdirectories ──────────────────────────────────

    public function test_scans_subdirectories(): void
    {
        $this->writeFixture('inventory/products/create.blade.php', <<<'BLADE'
<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2">
        <form method="POST" action="/products">
            <input type="text" name="name">
            <button type="submit">Save</button>
        </form>
    </div>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings, 'Should scan Blade files in subdirectories');
    }

    // ── Finding Structure ────────────────────────────────────────

    public function test_findings_have_correct_structure(): void
    {
        $this->writeFixture('test/structure.blade.php', <<<'BLADE'
<div class="grid grid-cols-4 gap-4" style="width: 800px;">
    <table class="w-full">
        <tr><th>Col</th></tr>
    </table>
    <form method="POST">
        <input type="text" name="field">
    </form>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->analyze();

        $this->assertNotEmpty($findings);

        foreach ($findings as $finding) {
            $this->assertInstanceOf(AuditFinding::class, $finding);
            $this->assertSame('ui', $finding->category);
            $this->assertInstanceOf(Severity::class, $finding->severity);
            $this->assertNotEmpty($finding->title);
            $this->assertNotEmpty($finding->description);
            $this->assertNotNull($finding->file);
            $this->assertNotNull($finding->recommendation);
            $this->assertIsArray($finding->metadata);
            $this->assertArrayHasKey('check', $finding->metadata);
        }
    }

    // ── Individual Method Tests ──────────────────────────────────

    public function test_check_responsiveness_with_direct_path(): void
    {
        $filePath = $this->writeFixture('direct/layout.blade.php', <<<'BLADE'
<div class="flex gap-4">
    <div class="w-64">Sidebar</div>
    <div class="flex-1">Content</div>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->checkResponsiveness($filePath);

        $this->assertNotEmpty($findings, 'checkResponsiveness should work with direct file path');
    }

    public function test_find_hardcoded_pixels_with_direct_path(): void
    {
        $filePath = $this->writeFixture('direct/styled.blade.php', <<<'BLADE'
<div style="width: 600px;">Content</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->findHardcodedPixels($filePath);

        $this->assertNotEmpty($findings, 'findHardcodedPixels should work with direct file path');
    }

    public function test_check_form_accessibility_with_direct_path(): void
    {
        $filePath = $this->writeFixture('direct/form.blade.php', <<<'BLADE'
<form method="POST">
    <input type="text" name="field">
    <button type="submit">Go</button>
</form>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->checkFormAccessibility($filePath);

        $this->assertNotEmpty($findings, 'checkFormAccessibility should work with direct file path');
    }

    public function test_check_table_mobile_with_direct_path(): void
    {
        $filePath = $this->writeFixture('direct/table.blade.php', <<<'BLADE'
<div class="bg-white">
    <table class="w-full">
        <tr><th>Header</th></tr>
    </table>
</div>
BLADE);

        $analyzer = new ViewAnalyzer($this->fixtureDir);
        $findings = $analyzer->checkTableMobile($filePath);

        $this->assertNotEmpty($findings, 'checkTableMobile should work with direct file path');
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Write a fixture Blade file and return its absolute path.
     */
    private function writeFixture(string $relativePath, string $content): string
    {
        $fullPath = $this->fixtureDir.'/'.$relativePath;
        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($fullPath, $content);

        return $fullPath;
    }

    /**
     * Filter findings by the 'check' metadata key.
     *
     * @param  AuditFinding[]  $findings
     * @return AuditFinding[]
     */
    private function filterByCheck(array $findings, string $check): array
    {
        return array_values(array_filter(
            $findings,
            fn (AuditFinding $f) => ($f->metadata['check'] ?? null) === $check
        ));
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
