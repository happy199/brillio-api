<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencySwitchController extends Controller
{
    /**
     * Switch the active session currency.
     */
    public function switch(Request $request)
    {
        // nosemgrep
        $currency = strtoupper($request->get('currency', 'XOF'));
        $supported = array_keys(CurrencyService::getSupportedCurrencies());

        if (in_array($currency, $supported)) {
            session()->put('currency', $currency);
        }

        return redirect()->back();
    }
}
