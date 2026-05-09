<?php

namespace App\Services\Audit;

use App\DTOs\Audit\AuditFinding;
use App\DTOs\Audit\Severity;

class PerformanceAnalyzer implements AnalyzerInterface
{
    private string $basePath;

    private string $controllerPath;

    private string $exportPath;

    private string $jobsPath;

    private string $servicePath;

    public function __construct(
        ?string $basePath = null,
        ?string $controllerPath = null,
        ?string $exportPath = null,
        ?string $jobsPath = null,
        ?string $servicePath = null,
    ) {
        if ($basePath !== null) {
            $this->basePath = $basePath;
        } else {
            try {
                $this->basePath = base_path();
            } catch (\Throwable) {
                $this->basePath = getcwd();
            }
        }

        $this->controllerPath = $controllerPath ?? ($this->basePath.'/app/Http/Controllers');
        $this->exportPath = $exportPath ?? ($this->basePath.'/app/Exports');
        $this->jobsPath = $jobsPath ?? ($this->basePath.'/app/Jobs');
        $this->servicePath = $servicePath ?? ($this->basePath.'/app/Services');
    }

    /**
     * @return AuditFinding[]
     */
    public function analyze(): array
    {
        $findings = [];

        array_push($findings, ...$this->checkPagination());
        array_push($findings, ...$this->checkEagerLoading());
        array_push($findings, ...$this->checkExportChunking());
        array_push($findings, ...$this->checkJobConfiguration());

        return $findings;
    }

    public function category(): string
    {
        return 'performance';
    }

    /**
     * @return AuditFinding[]
     */
    public function checkPagination(): array
    {
        $queryAnalyzer = new QueryAnalyzer(
            controllerPath: $this->controllerPath,
            servicePath: $this->servicePath,
            basePath: $this->basePath,
        );

        $findings = [];
        foreach ($queryAnalyzer->analyze() as $finding) {
            if (($finding->metadata['check'] ?? null) !== 'unbounded_query') {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'Missing pagination: '.$finding->title,
                description: $finding->description,
                file: $finding->file,
                line: $finding->line,
                recommendation: 'Use paginate()/simplePaginate()/cursorPaginate() for list endpoints.',
                metadata: ['check' => 'pagination', 'source_check' => 'unbounded_query'],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkEagerLoading(): array
    {
        $queryAnalyzer = new QueryAnalyzer(
            controllerPath: $this->controllerPath,
            servicePath: $this->servicePath,
            basePath: $this->basePath,
        );

        $findings = [];
        foreach ($queryAnalyzer->analyze() as $finding) {
            if (($finding->metadata['check'] ?? null) !== 'n_plus_one') {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::Medium,
                title: 'Potential eager-loading gap: '.$finding->title,
                description: $finding->description,
                file: $finding->file,
                line: $finding->line,
                recommendation: 'Add with()/load()/loadMissing() for relationship-heavy loops.',
                metadata: ['check' => 'eager_loading', 'source_check' => 'n_plus_one'],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkExportChunking(): array
    {
        $findings = [];
        foreach ($this->discoverPhpFiles($this->exportPath) as $exportFile) {
            if (basename($exportFile) === 'ChunkedExport.php') {
                continue;
            }

            $source = (string) @file_get_contents($exportFile);
            if ($source === '') {
                continue;
            }

            if (! $this->isExportClass($source)) {
                continue;
            }

            $usesChunkedExport = preg_match('/extends\s+ChunkedExport/', $source) === 1;
            $hasChunkSize = preg_match('/function\s+chunkSize\s*\(/', $source) === 1;
            if ($usesChunkedExport || $hasChunkSize) {
                continue;
            }

            $findings[] = new AuditFinding(
                category: $this->category(),
                severity: Severity::High,
                title: 'Export class missing chunk processing',
                description: 'Export class does not extend ChunkedExport and does not define chunkSize().',
                file: $this->relativePath($exportFile),
                line: null,
                recommendation: 'Extend ChunkedExport or implement chunkSize() for large dataset safety.',
                metadata: ['check' => 'export_chunking'],
            );
        }

        return $findings;
    }

    /**
     * @return AuditFinding[]
     */
    public function checkJobConfiguration(): array
    {
        $findings = [];
        foreach ($this->discoverPhpFiles($this->jobsPath) as $jobFile) {
            $source = (string) @file_get_contents($jobFile);
            if ($source === '' || preg_match('/implements\s+ShouldQueue/', $source) !== 1) {
                continue;
            }

            $className = $this->extractClassName($source) ?? basename($jobFile, '.php');
            $isBulkStyleJob = (bool) preg_match('/(sync|process|generate|export|notify|batch)/i', $className);
            $hasTimeout = preg_match('/\$timeout\s*=/', $source) === 1;
            $hasChunkHint = preg_match('/(chunk|batch|cursor|paginate|chunkById)/i', $source) === 1;

            if (! $hasTimeout) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Medium,
                    title: "Queue job missing timeout: {$className}",
                    description: "Queued job {$className} does not define \$timeout.",
                    file: $this->relativePath($jobFile),
                    line: null,
                    recommendation: 'Define $timeout and retry/backoff policy to avoid runaway workers.',
                    metadata: ['check' => 'job_timeout', 'job' => $className],
                );
            }

            if ($isBulkStyleJob && ! $hasChunkHint) {
                $findings[] = new AuditFinding(
                    category: $this->category(),
                    severity: Severity::Low,
                    title: "Queue job may miss chunking: {$className}",
                    description: "Bulk-style job {$className} has no obvious chunk/batch processing indicator.",
                    file: $this->relativePath($jobFile),
                    line: null,
                    recommendation: 'Process large records in chunks/batches to reduce memory pressure.',
                    metadata: ['check' => 'job_chunking', 'job' => $className],
                );
            }
        }

        return $findings;
    }

    private function isExportClass(string $source): bool
    {
        return preg_match('/class\s+\w+Export\b/', $source) === 1
            || preg_match('/namespace\s+App\\\\Exports;/', $source) === 1;
    }

    /**
     * @return string[]
     */
    private function discoverPhpFiles(string $directory): array
    {
        $files = [];
        if (! is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function extractClassName(string $source): ?string
    {
        if (preg_match('/class\s+(\w+)/', $source, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function relativePath(string $absolutePath): string
    {
        $basePath = $this->basePath.'/';
        if (str_starts_with($absolutePath, $basePath)) {
            return substr($absolutePath, strlen($basePath));
        }

        return $absolutePath;
    }
}
