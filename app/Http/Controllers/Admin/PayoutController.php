<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * Afficher la liste des payouts
     */
    public function index(Request $request)
    {
        $query = PayoutRequest::with('mentorProfile.user');

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mentorProfile.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payouts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    /**
     * Afficher les détails d'un payout
     */
    public function show(PayoutRequest $payout)
    {
        $payout->load('mentorProfile.user');
        return view('admin.payouts.show', compact('payout'));
    }
}
