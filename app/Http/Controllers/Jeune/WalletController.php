<?php

namespace App\Http\Controllers\Jeune;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\HasWallet;

class WalletController extends Controller
{
    use HasWallet;

    protected function getWalletConfig(): array
    {
        return [
            'user_type' => 'jeune',
            'view_prefix' => 'jeune.wallet.',
        ];
    }
}
