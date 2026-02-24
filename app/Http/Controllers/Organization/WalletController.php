<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\CreditPack;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Display the organization's wallet and available credit packs.
     */
    public function index()
    {
        $creditPacks = CreditPack::credits()
            ->where('user_type', 'organization')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        return view('organization.wallet.index', compact('creditPacks'));
    }

    /**
     * Handle credit pack purchase.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'pack_id' => 'required|exists:credit_packs,id',
        ]);

        $pack = CreditPack::findOrFail($request->pack_id);

        $amount = $pack->price;
        $description = 'Achat Crédits: '.$pack->name;

        // PACK-{orgId}-{packId}-{timestamp}
        $organization = $this->getCurrentOrganization();
        $reference = 'PACK-'.$organization->id.'-'.$pack->id.'-'.time();

        $returnUrl = route('organization.payment.callback');

        $monerooService = app(\App\Services\MonerooService::class);
        $user = auth()->user();

        // Create pending transaction record
        $localTransaction = \App\Models\MonerooTransaction::create([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'pending',
            'credits_amount' => $pack->credits,
            'metadata' => [
                'reference' => $reference,
                'pack_id' => $pack->id,
                'user_type' => 'organization',
            ],
        ]);

        $customer = [
            'email' => $user->email,
            'first_name' => $monerooService->splitName($user->name)['first_name'],
            'last_name' => $monerooService->splitName($user->name)['last_name'],
            'phone' => $user->phone,
        ];

        $paymentData = $monerooService->initializePayment(
            $amount,
            $description,
            $customer,
            [
                'reference' => $reference,
                'transaction_id' => $localTransaction->id,
            ],
            $returnUrl
        );

        if (isset($paymentData['checkout_url'])) {
            // Save Moneroo transaction ID
            $localTransaction->update([
                'moneroo_transaction_id' => $paymentData['id'],
            ]);

            return redirect($paymentData['checkout_url']);
        }

        return redirect()->back()->with('error', 'Erreur lors de l\'initialisation du paiement.');
    }
}
