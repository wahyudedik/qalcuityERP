<?php

namespace Tests\Unit;

use App\Services\BankFormatParser;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BankFormatParserTest extends TestCase
{
    private BankFormatParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new BankFormatParser;
    }

    /** @test */
    public function it_can_get_supported_banks()
    {
        $banks = $this->parser->getSupportedBanks();

        $this->assertIsArray($banks);
        $this->assertGreaterThanOrEqual(5, count($banks));

        $bankKeys = collect($banks)->pluck('key')->toArray();
        $this->assertContains('bca', $bankKeys);
        $this->assertContains('mandiri', $bankKeys);
        $this->assertContains('bni', $bankKeys);
        $this->assertContains('bri', $bankKeys);
        $this->assertContains('generic', $bankKeys);
    }

    /** @test */
    public function it_can_parse_bca_format_csv()
    {
        $csvContent = "Tanggal,Keterangan,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,TRANSFER DARI PT MAJU JAYA,5000000,50000000\n";
        $csvContent .= "16/01/2024,PEMBAYARAN LISTRIK PLN,-1500000,48500000\n";
        $csvContent .= "17/01/2024,GAJI KARYAWAN,-8000000,40500000\n";

        $file = $this->createCsvFile('bca_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'bca');

        $this->assertCount(3, $statements);

        // First statement (credit)
        $this->assertEquals('2024-01-15', $statements[0]['transaction_date']);
        $this->assertEquals('TRANSFER DARI PT MAJU JAYA', $statements[0]['description']);
        $this->assertEquals('credit', $statements[0]['type']);
        $this->assertEquals(5000000.0, $statements[0]['amount']);
        $this->assertEquals(50000000.0, $statements[0]['balance']);

        // Second statement (debit - negative amount)
        $this->assertEquals('2024-01-16', $statements[1]['transaction_date']);
        $this->assertEquals('PEMBAYARAN LISTRIK PLN', $statements[1]['description']);
        $this->assertEquals('debit', $statements[1]['type']);
        $this->assertEquals(1500000.0, $statements[1]['amount']);
    }

    /** @test */
    public function it_can_parse_mandiri_format_csv()
    {
        $csvContent = "Tanggal,Uraian,Debit,Kredit,Saldo\n";
        $csvContent .= "15-01-2024,TRANSFER DARI PT ABC,-,5000000,50000000\n";
        $csvContent .= "16-01-2024,PEMBAYARAN TAGIHAN LISTRIK,1500000,-,48500000\n";
        $csvContent .= "17-01-2024,PENGELUARAN GAJI KARYAWAN,8000000,-,40500000\n";

        $file = $this->createCsvFile('mandiri_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'mandiri');

        $this->assertCount(3, $statements);

        // First statement (credit)
        $this->assertEquals('2024-01-15', $statements[0]['transaction_date']);
        $this->assertEquals('credit', $statements[0]['type']);
        $this->assertEquals(5000000.0, $statements[0]['amount']);

        // Second statement (debit)
        $this->assertEquals('debit', $statements[1]['type']);
        $this->assertEquals(1500000.0, $statements[1]['amount']);
    }

    /** @test */
    public function it_can_parse_bni_format_csv()
    {
        $csvContent = "Tanggal,Deskripsi,Jumlah,Tipe,Saldo\n";
        $csvContent .= "2024-01-15,Transfer masuk dari PT XYZ,5000000,Credit,50000000\n";
        $csvContent .= "2024-01-16,Pembayaran listrik PLN,1500000,Debit,48500000\n";
        $csvContent .= "2024-01-17,Gaji karyawan bulan Januari,8000000,Debit,40500000\n";

        $file = $this->createCsvFile('bni_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'bni');

        $this->assertCount(3, $statements);

        $this->assertEquals('2024-01-15', $statements[0]['transaction_date']);
        $this->assertEquals('Transfer masuk dari PT XYZ', $statements[0]['description']);
        $this->assertEquals('credit', $statements[0]['type']);
        $this->assertEquals(5000000.0, $statements[0]['amount']);

        $this->assertEquals('debit', $statements[1]['type']);
        $this->assertEquals('Pembayaran listrik PLN', $statements[1]['description']);
    }

    /** @test */
    public function it_can_parse_bri_format_csv()
    {
        $csvContent = "Tanggal,Uraian,Debit,Kredit,Saldo\n";
        $csvContent .= "15/01/2024,Transfer dari PT DEF,0,5000000,50000000\n";
        $csvContent .= "16/01/2024,Pembayaran tagihan listrik,1500000,0,48500000\n";

        $file = $this->createCsvFile('bri_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'bri');

        $this->assertCount(2, $statements);

        $this->assertEquals('2024-01-15', $statements[0]['transaction_date']);
        $this->assertEquals('credit', $statements[0]['type']);
        $this->assertEquals(5000000.0, $statements[0]['amount']);

        $this->assertEquals('debit', $statements[1]['type']);
        $this->assertEquals(1500000.0, $statements[1]['amount']);
    }

    /** @test */
    public function it_can_parse_generic_format_csv()
    {
        $csvContent = "Tanggal,Deskripsi,Tipe,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,Transfer masuk dari Customer A,Credit,5000000,50000000\n";
        $csvContent .= "16/01/2024,Pembayaran listrik PLN,Debit,1500000,48500000\n";

        $file = $this->createCsvFile('generic_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertCount(2, $statements);

        $this->assertEquals('credit', $statements[0]['type']);
        $this->assertEquals('debit', $statements[1]['type']);
    }

    /** @test */
    public function it_auto_detects_bank_format()
    {
        // BCA format
        $bcaContent = "Tanggal,Keterangan,Jumlah,Saldo\n";
        $bcaContent .= "15/01/2024,TRANSFER DARI PT MAJU JAYA,5000000,50000000\n";
        $bcaFile = $this->createCsvFile('auto_bca.csv', $bcaContent);

        // Should detect BCA from headers
        $statements = $this->parser->parse($bcaFile);
        $this->assertNotEmpty($statements);

        // Mandiri format
        $mandiriContent = "Tanggal,Uraian,Debit,Kredit,Saldo\n";
        $mandiriContent .= "15-01-2024,TRANSFER DARI PT ABC,-,5000000,50000000\n";
        $mandiriFile = $this->createCsvFile('auto_mandiri.csv', $mandiriContent);

        $statements = $this->parser->parse($mandiriFile);
        $this->assertNotEmpty($statements);
    }

    /** @test */
    public function it_handles_various_amount_formats()
    {
        $csvContent = "Tanggal,Deskripsi,Tipe,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,Test 1 - No separator,Credit,5000000,50000000\n";
        $csvContent .= "16/01/2024,Test 2 - Dot thousand,Credit,5.000.000,50000000\n";
        $csvContent .= "17/01/2024,Test 3 - Comma thousand,Credit,5,000,000,50000000\n";
        $csvContent .= "18/01/2024,Test 4 - Decimal,Credit,5000000.50,50000000\n";
        $csvContent .= "19/01/2024,Test 5 - Rp prefix,Credit,Rp 5000000,50000000\n";
        $csvContent .= "20/01/2024,Test 6 - Parentheses,Debit,(1500000),48500000\n";

        $file = $this->createCsvFile('amount_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertGreaterThanOrEqual(5, count($statements));

        // All amounts should be parsed correctly
        foreach ($statements as $stmt) {
            $this->assertIsFloat($stmt['amount']);
            $this->assertGreaterThan(0, $stmt['amount']);
        }
    }

    /** @test */
    public function it_handles_various_date_formats()
    {
        $csvContent = "Tanggal,Deskripsi,Tipe,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,Test 1 - DD/MM/YYYY,Credit,1000000,50000000\n";
        $csvContent .= "2024-01-16,Test 2 - YYYY-MM-DD,Credit,1000000,50000000\n";
        $csvContent .= "16-01-2024,Test 3 - DD-MM-YYYY,Credit,1000000,50000000\n";

        $file = $this->createCsvFile('date_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertCount(3, $statements);

        // All should be normalized to Y-m-d format
        foreach ($statements as $stmt) {
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $stmt['transaction_date']);
        }
    }

    /** @test */
    public function it_skips_empty_and_invalid_rows()
    {
        $csvContent = "Tanggal,Deskripsi,Tipe,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,Valid Transaction,Credit,5000000,50000000\n";
        $csvContent .= "\n"; // Empty row
        $csvContent .= "INVALID DATE,Invalid,Debit,1000,50000000\n";
        $csvContent .= "16/01/2024,,Debit,1000,50000000\n"; // Empty description
        $csvContent .= "17/01/2024,Valid Transaction 2,Debit,2000000,48000000\n";

        $file = $this->createCsvFile('skip_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertCount(2, $statements);
        $this->assertEquals('Valid Transaction', $statements[0]['description']);
        $this->assertEquals('Valid Transaction 2', $statements[1]['description']);
    }

    /** @test */
    public function it_extracts_reference_from_description()
    {
        $csvContent = "Tanggal,Deskripsi,Tipe,Jumlah,Saldo\n";
        $csvContent .= "15/01/2024,Transfer REF:TRX12345678,Credit,5000000,50000000\n";
        $csvContent .= "16/01/2024,Pembayaran NO.INV-2024-001,Debit,1500000,48500000\n";

        $file = $this->createCsvFile('reference_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertCount(2, $statements);
        $this->assertNotNull($statements[0]['reference']);
        $this->assertNotNull($statements[1]['reference']);
    }

    /** @test */
    public function it_handles_semicolon_delimiter()
    {
        $csvContent = "Tanggal;Deskripsi;Tipe;Jumlah;Saldo\n";
        $csvContent .= "15/01/2024;Transfer masuk;Credit;5000000;50000000\n";
        $csvContent .= "16/01/2024;Pembayaran listrik;Debit;1500000;48500000\n";

        $file = $this->createCsvFile('semicolon_test.csv', $csvContent);
        $statements = $this->parser->parse($file, 'generic');

        $this->assertCount(2, $statements);
        $this->assertEquals('Transfer masuk', $statements[0]['description']);
    }

    /** @test */
    public function it_rejects_invalid_file_extension()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Format file tidak didukung');

        $file = UploadedFile::fake()->create('test.pdf', 100);
        $this->parser->parse($file);
    }

    /** @test */
    public function it_rejects_file_too_large()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ukuran file terlalu besar');

        // Create 11MB file
        $file = UploadedFile::fake()->createWithContent('large.csv', str_repeat('x', 11 * 1024 * 1024));
        $this->parser->parse($file);
    }

    /**
     * Helper to create CSV file
     */
    private function createCsvFile(string $name, string $content): UploadedFile
    {
        $path = storage_path('app/testing/'.$name);

        // Ensure directory exists
        if (! file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $content);

        return new UploadedFile(
            $path,
            $name,
            'text/csv',
            null,
            true
        );
    }
}
