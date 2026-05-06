<?php

namespace App\Http\Controllers\Organization;

use App\Models\User;
use App\Services\PersonalityService;
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
        $establishmentIds = $organization->establishments->pluck('id');

        // Statistiques globales
        $totalClicks = $organization->establishmentClicks()->count();

        $uniqueProspects = User::where(function ($q) use ($establishmentIds) {
            $q->whereHas('establishmentClicks', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds))
                ->orWhereHas('establishmentInterests', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds));
        })->count();

        // Clics récents (30 derniers jours)
        $recentClicks = $organization->establishmentClicks()
            ->where('establishment_clicks.created_at', '>=', now()->subDays(30))
            ->count();

        // Liste des prospects regroupés par utilisateur
        // On trie par la date de la dernière interaction (clic ou intérêt)
        $prospects = User::select('users.*')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_clicks')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_click_at')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_interests')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_interest_at')
            ->where(function ($q) use ($establishmentIds) {
                $q->whereHas('establishmentClicks', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds))
                    ->orWhereHas('establishmentInterests', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds));
            })
            ->orderByRaw('GREATEST(COALESCE(last_click_at, "1970-01-01"), COALESCE(last_interest_at, "1970-01-01")) DESC')
            ->with(['jeuneProfile', 'personalityTest', 'establishmentClicks' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }, 'establishmentInterests' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }])
            ->paginate(50);

        // Agrégation manuelle pour la vue
        $prospects->getCollection()->transform(function ($user) {
            $user->clicks_count = $user->establishmentClicks->count();
            $user->has_interest = $user->establishmentInterests->isNotEmpty();
            $user->last_interaction_at = $user->last_interest_at && $user->last_interest_at > ($user->last_click_at ?? '0')
                ? $user->last_interest_at
                : $user->last_click_at;

            return $user;
        });

        $mbtiDescriptions = PersonalityService::TYPE_DESCRIPTIONS;

        return view('organization.promotion.index', compact(
            'organization',
            'totalClicks',
            'uniqueProspects',
            'recentClicks',
            'prospects',
            'mbtiDescriptions'
        ));
    }

    /**
     * Export PDF de la liste des prospects
     */
    public function exportPdf()
    {
        $organization = auth()->user()->organization;
        $establishmentIds = $organization->establishments->pluck('id');

        $prospects = User::select('users.*')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_clicks')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_click_at')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_interests')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_interest_at')
            ->where(function ($q) use ($establishmentIds) {
                $q->whereHas('establishmentClicks', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds))
                    ->orWhereHas('establishmentInterests', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds));
            })
            ->orderByRaw('GREATEST(COALESCE(last_click_at, "1970-01-01"), COALESCE(last_interest_at, "1970-01-01")) DESC')
            ->with(['jeuneProfile', 'personalityTest', 'establishmentClicks' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }, 'establishmentInterests' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }])
            ->get();

        $prospects->transform(function ($user) {
            $user->clicks_count = $user->establishmentClicks->count();
            $user->has_interest = $user->establishmentInterests->isNotEmpty();
            $user->last_interaction_at = $user->last_interest_at && $user->last_interest_at > ($user->last_click_at ?? '0')
                ? $user->last_interest_at
                : $user->last_click_at;

            return $user;
        });

        $pdf = Pdf::loadView('organization.promotion.pdf', compact('organization', 'prospects'));

        return $pdf->download("prospects-{$organization->slug}-".now()->format('Y-m-d').'.pdf');
    }

    /**
     * Export CSV de la liste des prospects
     */
    public function exportCsv()
    {
        $organization = auth()->user()->organization;
        $establishmentIds = $organization->establishments->pluck('id');

        $prospects = User::select('users.*')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_clicks')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_click_at')
            ->selectSub(function ($query) use ($establishmentIds) {
                $query->from('establishment_interests')
                    ->whereColumn('user_id', 'users.id')
                    ->whereIn('establishment_id', $establishmentIds)
                    ->selectRaw('MAX(created_at)');
            }, 'last_interest_at')
            ->where(function ($q) use ($establishmentIds) {
                $q->whereHas('establishmentClicks', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds))
                    ->orWhereHas('establishmentInterests', fn ($sq) => $sq->whereIn('establishment_id', $establishmentIds));
            })
            ->orderByRaw('GREATEST(COALESCE(last_click_at, "1970-01-01"), COALESCE(last_interest_at, "1970-01-01")) DESC')
            ->with(['jeuneProfile', 'personalityTest', 'establishmentClicks' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }, 'establishmentInterests' => function ($q) use ($establishmentIds) {
                $q->whereIn('establishment_id', $establishmentIds);
            }])
            ->get();

        $prospects->transform(function ($user) {
            $user->clicks_count = $user->establishmentClicks->count();
            $user->has_interest = $user->establishmentInterests->isNotEmpty();
            $user->last_interaction_at = $user->last_interest_at && $user->last_interest_at > ($user->last_click_at ?? '0')
                ? $user->last_interest_at
                : $user->last_click_at;

            return $user;
        });

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=prospects-{$organization->slug}.csv",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($prospects) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nom', 'Email', 'Téléphone', 'Ville', 'Pays', 'Type MBTI', 'Clics', 'Intérêt', 'Dernière interaction']);

            foreach ($prospects as $prospect) {
                fputcsv($file, [
                    $prospect->name ?? 'Anonyme',
                    $prospect->email ?? '-',
                    $prospect->phone ?? '-',
                    $prospect->jeuneProfile?->city ?? '-',
                    $prospect->jeuneProfile?->country ?? '-',
                    $prospect->personalityTest?->personality_type ?? '-',
                    $prospect->clicks_count,
                    $prospect->has_interest ? 'Oui' : 'Non',
                    \Carbon\Carbon::parse($prospect->last_interaction_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
