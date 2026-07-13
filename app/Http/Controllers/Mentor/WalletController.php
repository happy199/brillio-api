<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\HasWallet;

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
}
