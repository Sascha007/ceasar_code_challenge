<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Services\QrCodeService;

class AdminController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Zeigt die Admin-Dashboard-Seite an.
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Generiert ein PDF mit QR-Codes für alle Teams.
     */
    public function downloadTeamQrCodes()
    {
        $teams = Team::all();
        $qrCodes = [];

        foreach ($teams as $team) {
            $qrCodes[] = [
                'name' => $team->name,
                'qr' => $this->qrCodeService->generateTeamQr($team),
            ];
        }

        return $this->qrCodeService->generateQrCodesPdf($qrCodes);
    }
}
