<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\EstablishmentInterest;
use Illuminate\Http\Request;

class EstablishmentController extends Controller
{
    /**
     * Get recommended establishments for the current user based on MBTI
     */
    public function recommended(Request $request)
    {
        $user = auth()->user();
        $test = $user->personalityTest;

        if (!$test || !$test->isCompleted()) {
            return response()->json(['establishments' => []]);
        }

        $mbtiType = $test->personality_type;

        // Fetch establishments that match this MBTI type
        $establishments = Establishment::where('is_published', true)
            ->whereJsonContains('mbti_types', $mbtiType)
            ->get()
            ->map(function ($est) use ($user) {
                // Check if user already expressed interest
                $est->user_has_interest = EstablishmentInterest::where('user_id', $user->id)
                    ->where('establishment_id', $est->id)
                    ->exists();
                return $est;
            });

        return response()->json([
            'mbti_type' => $mbtiType,
            'establishments' => $establishments
        ]);
    }

    /**
     * Store quick interest and update user phone if needed
     */
    public function quickInterest(Request $request, Establishment $establishment)
    {
        $user = auth()->user();

        // 1. If phone is provided in request, update user profile
        if ($request->has('phone') && !empty($request->phone)) {
            $user->update(['phone' => $request->phone]);
        }

        // 2. Validate phone existence (either in profile or request)
        if (empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Il manque votre numéro de téléphone.'
            ], 422);
        }

        // 3. Record interest (Avoid duplicates)
        EstablishmentInterest::firstOrCreate([
            'user_id' => $user->id,
            'establishment_id' => $establishment->id,
            'type' => 'quick'
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$establishment->name} vous recontactera dans les meilleurs délais."
        ]);
    }

    /**
     * Store precise interest with dynamic form data
     */
    public function preciseInterest(Request $request, Establishment $establishment)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'form_data' => 'required|array',
            'phone' => 'sometimes|nullable|string'
        ]);

        // 1. If phone is provided in request, update user profile
        if (!empty($request->phone)) {
            $user->update(['phone' => $request->phone]);
        }

        // 2. Validate phone existence
        if (empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Il manque votre numéro de téléphone.'
            ], 422);
        }

        EstablishmentInterest::updateOrCreate(
            [
                'user_id' => $user->id,
                'establishment_id' => $establishment->id,
            ],
            [
                'type' => 'precise',
                'form_data' => $validated['form_data']
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Votre demande a été envoyée avec succès à '.$establishment->name
        ]);
    }
}
