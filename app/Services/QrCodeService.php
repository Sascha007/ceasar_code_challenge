<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use App\Models\Team;
use Illuminate\Support\Collection;
use Dompdf\Dompdf;

class QrCodeService
{
    /**
     * Generate a QR code for a team URL.
     */
    public function generateTeamQr(Team $team): string
    {
        $options = new QROptions([
            'eccLevel' => QRCode::ECC_L,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'version' => 5,
        ]);

        $qrCode = new QRCode($options);
        return $qrCode->render(route('team.show', $team->slug));
    }

    /**
     * Generate a PDF with QR codes for all teams.
     */
    public function generateQrCodesPdf(array $qrCodes): \Illuminate\Http\Response
    {
        $dompdf = new Dompdf();
        $html = view('admin.qr-codes-pdf', ['qrCodes' => $qrCodes])->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        
        return response($dompdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="team-qr-codes.pdf"');
    }
    public function generateTeamQrPdf(Collection $teams): string
    {
        $html = view('admin.qr-codes-pdf', [
            'teams' => $teams->map(function ($team) {
                return [
                    'name' => $team->display_name,
                    'qr' => $this->generateTeamQr($team),
                    'url' => route('team.show', $team->slug),
                ];
            }),
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}