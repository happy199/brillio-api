<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\HasWallet;
use App\Models\CreditPack;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    use HasWallet;

    protected function getWalletConfig(): array
    {
        return [
            'user_type' => 'mentor',
            'view_prefix' => 'mentor.wallet.',
        ];
    }

    public function index()
    {
        $user = Auth::user();
        $creditPrice = $this->walletService->getCreditPrice('mentor');

        $walletTransactions = $user->walletTransactions()
            ->whereIn('type', ['purchase', 'expense', 'coupon', 'service_fee'])
            ->latest()
            ->paginate(10, ['*'], 'wallet_page');

        $incomeTransactions = $user->walletTransactions()
            ->where('type', 'income')
            ->latest()
            ->paginate(10, ['*'], 'income_page');

        $totalCreditsEarned = $user->walletTransactions()->where('type', 'income')->sum('amount');
        $estimatedValueFcfa = $totalCreditsEarned * $creditPrice;

        $packs = CreditPack::where('user_type', 'mentor')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('mentor.wallet.index', compact(
            'user',
            'walletTransactions',
            'incomeTransactions',
            'creditPrice',
            'packs',
            'totalCreditsEarned',
            'estimatedValueFcfa'
        ));
    }
}
