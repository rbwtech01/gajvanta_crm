<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoiceController;
use App\Models\Customer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceDownloadTest extends TestCase
{
    public function test_invoice_download_returns_a_pdf_with_a_pdf_filename(): void
    {
        Schema::create('category_attachments', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });

        $customer = new Customer([
            'customer_name' => 'Rupchan Ali Mondal',
            'address' => 'Sample address',
            'district' => 'Sample district',
            'state' => 'Sample state',
            'pincode' => '123456',
            'mobile' => '1234567890',
            'price' => 1000,
            'advance_payment' => 200,
            'quantity' => 1,
            'attachments' => [],
        ]);
        $customer->setRelation('category', null);
        $customer->setRelation('machineModel', null);

        $response = app(InvoiceController::class)->download($customer);
        $content = $response->getContent();

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'filename=Invoice-rupchan-ali-mondal.pdf',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('%%EOF', $content);
    }

    public function test_livewire_can_deliver_binary_pdf_downloads_without_json_encoding(): void
    {
        $component = new class extends Component
        {
            public function download()
            {
                $pdfContent = "%PDF-1.4\n\xFF\xFE\n%%EOF";

                return response()->streamDownload(
                    fn () => print ($pdfContent),
                    'Invoice-test.pdf',
                    ['Content-Type' => 'application/pdf']
                );
            }

            public function render(): string
            {
                return '<div></div>';
            }
        };

        Livewire::test($component)
            ->call('download')
            ->assertFileDownloaded(
                'Invoice-test.pdf',
                "%PDF-1.4\n\xFF\xFE\n%%EOF",
                'application/pdf'
            );
    }
}
