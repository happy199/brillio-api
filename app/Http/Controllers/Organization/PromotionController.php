<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PromotionController extends Controller
{
    /**
     * Dashboard de promotion pour les établissements
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        // Statistiques globales
        $totalClicks = $organization->establishmentClicks()->count();
        $uniqueProspects = $organization->establishmentClicks()->distinct('user_id')->count('user_id');

        // Clics récents (30 derniers jours)
        $recentClicks = $organization->establishmentClicks()
            ->where('establishment_clicks.created_at', '>=', now()->subDays(30))
            ->count();

        // Liste des prospects (Jeunes ayant cliqué)
        $clicks = $organization->establishmentClicks()
            ->with('user.jeuneProfile', 'user.personalityTest')
            ->latest()
            ->paginate(50);

        return view('organization.promotion.index', compact(
            'organization',
            'totalClicks',
            'uniqueProspects',
            'recentClicks',
            'clicks'
        ));
    }

    /**
     * Export PDF de la liste des prospects
     */
    public function exportPdf()
    {
        $organization = auth()->user()->organization;
        $clicks = $organization->establishmentClicks()->with('user.jeuneProfile')->latest()->get();

        $pdf = Pdf::loadView('organization.promotion.pdf', compact('organization', 'clicks'));

        return $pdf->download("prospects-{$organization->slug}-".now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export CSV de la liste des prospects
     */
    public function exportCsv()
    {
        $organization = auth()->user()->organization;
        $clicks = $organization->establishmentClicks()->with('user.jeuneProfile')->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=prospects-{$organization->slug}.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($clicks) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nom', 'Email', 'Téléphone', 'Ville', 'Pays', 'Date du clic']);

            foreach ($clicks as $click) {
                fputcsv($file, [
                    $click->user?->name ?? 'Anonyme',
                    $click->user?->email ?? '-',
                    $click->user?->phone ?? '-',
                    $click->user?->city ?? '-',
                    $click->user?->country ?? '-',
                    $click->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
