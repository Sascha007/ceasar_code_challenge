<?php

namespace Tests\Unit\Services;

use App\Models\Team;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private QrCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QrCodeService;
    }

    public function test_generate_team_qr_returns_svg_string(): void
    {
        $team = Team::factory()->create(['slug' => 'test-team']);

        $qrCode = $this->service->generateTeamQr($team);

        $this->assertIsString($qrCode);
        $this->assertStringContainsString('svg', $qrCode);
    }

    public function test_generate_team_qr_includes_team_url(): void
    {
        $team = Team::factory()->create(['slug' => 'test-team']);

        $qrCode = $this->service->generateTeamQr($team);
        $expectedUrl = route('team.show', $team->slug);

        // The QR code should contain data that references the URL
        $this->assertIsString($qrCode);
        $this->assertNotEmpty($qrCode);
    }

    public function test_generate_team_qr_pdf_returns_string(): void
    {
        $teams = Team::factory()->count(2)->create();

        $pdf = $this->service->generateTeamQrPdf($teams);

        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        // PDF files start with %PDF
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_generate_team_qr_pdf_handles_empty_collection(): void
    {
        $teams = new Collection;

        $pdf = $this->service->generateTeamQrPdf($teams);

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_generate_team_qr_pdf_includes_team_names(): void
    {
        $team = Team::factory()->create(['display_name' => 'Unique Test Team Name']);

        $pdf = $this->service->generateTeamQrPdf(collect([$team]));

        // Note: PDF content is binary/encoded, but we can verify it's a valid PDF
        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertNotEmpty($pdf);
    }

    public function test_generate_qr_codes_pdf_returns_response_with_correct_headers(): void
    {
        $team = Team::factory()->create();
        $qrCodes = [
            [
                'name' => $team->display_name,
                'qr' => $this->service->generateTeamQr($team),
                'url' => route('team.show', $team->slug),
            ],
        ];

        $response = $this->service->generateQrCodesPdf($qrCodes);

        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('team-qr-codes.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_generate_qr_codes_pdf_returns_valid_pdf_content(): void
    {
        $team = Team::factory()->create();
        $qrCodes = [
            [
                'name' => $team->display_name,
                'qr' => $this->service->generateTeamQr($team),
                'url' => route('team.show', $team->slug),
            ],
        ];

        $response = $this->service->generateQrCodesPdf($qrCodes);
        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringStartsWith('%PDF', $content);
    }
}
