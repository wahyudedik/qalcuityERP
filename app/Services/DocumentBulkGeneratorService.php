<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;

/**
 * Document Bulk Generator Service
 * 
 * Generates multiple documents from templates with batch processing.
 */
class DocumentBulkGeneratorService
{
    /**
     * Generate documents in bulk from template
     */
    public function generateFromTemplate(DocumentTemplate $template, array $dataRows, string $outputFormat = 'pdf'): array
    {
        $results = [
            'total' => count($dataRows),
            'success' => 0,
            'failed' => 0,
            'documents' => [],
            'errors' => [],
        ];

        foreach ($dataRows as $index => $data) {
            try {
                $document = $this->generateSingleDocument($template, $data, $outputFormat);
                $results['documents'][] = $document->id;
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Generate single document from template
     */
    protected function generateSingleDocument(DocumentTemplate $template, array $data, string $format): Document
    {
        // Render template content
        $content = $this->renderTemplate($template, $data);

        // Generate file based on format
        $fileData = match ($format) {
            'pdf' => $this->generatePdf($content, $data['title'] ?? 'Document'),
            'docx' => $this->generateDocx($content, $data['title'] ?? 'Document'),
            default => throw new \Exception("Unsupported format: {$format}"),
        };

        // Create document record
        return Document::create([
            'tenant_id' => Auth::user()->tenant_id,
            'uploaded_by' => Auth::id(),
            'title' => $data['title'] ?? 'Generated Document',
            'file_name' => $fileData['file_name'],
            'file_path' => $fileData['file_path'],
            'file_type' => $fileData['file_type'],
            'file_size' => $fileData['file_size'],
            'category' => $template->category ?? 'generated',
            'description' => $data['description'] ?? "Generated from template: {$template->name}",
            'version' => 1,
            'status' => 'draft',
        ]);
    }

    /**
     * Render template with data
     */
    protected function renderTemplate(DocumentTemplate $template, array $data): string
    {
        $content = $template->content;

        // Replace placeholders {{key}} with values
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $content = str_replace($placeholder, $value, $content);
        }

        return $content;
    }

    /**
     * Generate PDF from content
     */
    protected function generatePdf(string $content, string $title): array
    {
        $pdf = Pdf::loadHTML($content);
        $fileName = strtolower(str_replace(' ', '_', $title)) . '_' . time() . '.pdf';
        $filePath = "documents/" . Auth::user()->tenant_id . "/generated/{$fileName}";

        // Save to storage
        Storage::put($filePath, $pdf->output());

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => 'pdf',
            'file_size' => strlen($pdf->output()),
        ];
    }

    /**
     * Generate DOCX from content
     */
    protected function generateDocx(string $content, string $title): array
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add content as HTML
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $content);

        $fileName = strtolower(str_replace(' ', '_', $title)) . '_' . time() . '.docx';
        $filePath = "documents/" . Auth::user()->tenant_id . "/generated/{$fileName}";

        // Save to storage
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        Storage::put($filePath, file_get_contents($tempFile));
        unlink($tempFile);

        return [
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => 'docx',
            'file_size' => Storage::size($filePath),
        ];
    }

    /**
     * Generate batch invoices
     */
    public function generateBatchInvoices(array $invoices, string $templateName): array
    {
        $template = DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', $templateName)
            ->firstOrFail();

        $dataRows = [];
        foreach ($invoices as $invoice) {
            $dataRows[] = [
                'title' => "Invoice {$invoice->invoice_number}",
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name ?? 'Customer',
                'amount' => number_format($invoice->total_amount, 2),
                'due_date' => $invoice->due_date->format('d M Y'),
                'description' => "Invoice for {$invoice->invoice_number}",
            ];
        }

        return $this->generateFromTemplate($template, $dataRows, 'pdf');
    }

    /**
     * Generate batch certificates
     */
    public function generateBatchCertificates(array $records, string $templateName): array
    {
        $template = DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', $templateName)
            ->firstOrFail();

        $dataRows = [];
        foreach ($records as $record) {
            $dataRows[] = [
                'title' => "Certificate - {$record->name}",
                'recipient_name' => $record->name,
                'certificate_number' => $record->certificate_number ?? 'N/A',
                'issue_date' => now()->format('d M Y'),
                'description' => "Certificate for {$record->name}",
            ];
        }

        return $this->generateFromTemplate($template, $dataRows, 'pdf');
    }

    /**
     * Generate batch contracts
     */
    public function generateBatchContracts(array $contracts, string $templateName): array
    {
        $template = DocumentTemplate::where('tenant_id', Auth::user()->tenant_id)
            ->where('name', $templateName)
            ->firstOrFail();

        $dataRows = [];
        foreach ($contracts as $contract) {
            $dataRows[] = [
                'title' => "Contract - {$contract->party_name}",
                'party_name' => $contract->party_name,
                'contract_number' => $contract->contract_number ?? 'N/A',
                'start_date' => $contract->start_date->format('d M Y'),
                'end_date' => $contract->end_date->format('d M Y'),
                'value' => number_format($contract->value, 2),
                'description' => "Contract with {$contract->party_name}",
            ];
        }

        return $this->generateFromTemplate($template, $dataRows, 'pdf');
    }

    /**
     * Preview template with sample data
     */
    public function previewTemplate(DocumentTemplate $template, array $sampleData): string
    {
        return $this->renderTemplate($template, $sampleData);
    }

    /**
     * Get bulk generation statistics
     */
    public function getBulkGenerationStats(int $tenantId): array
    {
        $totalGenerated = Document::where('tenant_id', $tenantId)
            ->where('category', 'generated')
            ->count();

        $generatedToday = Document::where('tenant_id', $tenantId)
            ->where('category', 'generated')
            ->whereDate('created_at', today())
            ->count();

        return [
            'total_generated' => $totalGenerated,
            'generated_today' => $generatedToday,
            'most_used_template' => $this->getMostUsedTemplate($tenantId),
        ];
    }

    /**
     * Get most used template
     */
    protected function getMostUsedTemplate(int $tenantId): ?string
    {
        // This would require tracking template usage
        // Simplified implementation
        return null;
    }
}
