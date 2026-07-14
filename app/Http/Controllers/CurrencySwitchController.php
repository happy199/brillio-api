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
        $validated = $request->validate(['currency' => 'nullable|string|max:10|alpha']);
        $currency = strtoupper($validated['currency'] ?? 'XOF');
        $supported = array_keys(CurrencyService::getSupportedCurrencies());

        if (in_array($currency, $supported)) {
            session()->put('currency', $currency);
        }

        return redirect()->back();
    }
}
