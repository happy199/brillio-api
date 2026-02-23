<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class PersonalityPdfController extends Controller
{
    /**
     * Export du dernier test en PDF
     */
    public function exportCurrent()
    {
        $user = auth()->user();
        $test = $user->personalityTest;

        if (! $test) {
            return back()->with('error', 'Aucun test de personnalité trouvé.');
        }

        $pdf = Pdf::loadView('pdf.personality-test', [
            'test' => $test,
            'user' => $user,
        ]);

        return $pdf->download('test-personnalite-'.$test->type.'.pdf');
    }

    /**
     * Export de l'historique complet en PDF
     */
    public function exportHistory()
    {
        $user = auth()->user();
        $tests = $user->personalityTests;

        if ($tests->isEmpty()) {
            return back()->with('error', 'Aucun historique de tests trouvé.');
        }

        $pdf = Pdf::loadView('pdf.personality-history', [
            'tests' => $tests,
            'user' => $user,
        ]);

        return $pdf->download('historique-tests-personnalite.pdf');
    }
}
