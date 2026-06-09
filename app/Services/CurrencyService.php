<?php

namespace App\Services;

class CurrencyService
{
    /**
     * Get all supported currencies with their details.
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'XOF' => [
                'name' => 'Franc CFA (XOF)',
                'symbol' => 'FCFA',
                'position' => 'suffix',
                'decimals' => 0,
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'rate_from_xof' => 1.0,
            ],
            'XAF' => [
                'name' => 'Franc CFA (XAF)',
                'symbol' => 'FCFA',
                'position' => 'suffix',
                'decimals' => 0,
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'rate_from_xof' => 1.0, // Pegged 1:1 with XOF
            ],
            'GNF' => [
                'name' => 'Franc Guinéen (GNF)',
                'symbol' => 'FG',
                'position' => 'suffix',
                'decimals' => 0,
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'rate_from_xof' => 13.1, // 1 XOF = 13.1 GNF
            ],
            'MAD' => [
                'name' => 'Dirham marocain (MAD)',
                'symbol' => 'MAD',
                'position' => 'suffix',
                'decimals' => 2,
                'decimal_separator' => ',',
                'thousands_separator' => ' ',
                'rate_from_xof' => 0.01639344, // 1 MAD = 61 XOF
            ],
        ];
    }

    /**
     * Get the currently active currency from session.
     */
    public static function getCurrentCurrency(): string
    {
        return session()->get('currency', 'XOF');
    }

    /**
     * Get the symbol of the active currency (or a specific currency).
     */
    public static function symbol(?string $currency = null): string
    {
        $currency = $currency ?? self::getCurrentCurrency();
        $currencies = self::getSupportedCurrencies();

        return $currencies[$currency]['symbol'] ?? 'FCFA';
    }

    /**
     * Convert an amount from one currency to another.
     */
    public static function convert(float $amount, string $from = 'XOF', ?string $to = null): float
    {
        $to = $to ?? self::getCurrentCurrency();
        $currencies = self::getSupportedCurrencies();

        if (! isset($currencies[$from]) || ! isset($currencies[$to])) {
            return $amount;
        }

        if ($from === $to) {
            return $amount;
        }

        // Convert to base currency (XOF) first
        $amountInXof = ($from === 'XOF') ? $amount : $amount / $currencies[$from]['rate_from_xof'];

        // Convert from XOF to target currency
        return $amountInXof * $currencies[$to]['rate_from_xof'];
    }

    /**
     * Format an amount in XOF into the target currency representation.
     */
    public static function format(float $amountInXof, ?string $currency = null): string
    {
        $currency = $currency ?? self::getCurrentCurrency();
        $currencies = self::getSupportedCurrencies();

        if (! isset($currencies[$currency])) {
            $currency = 'XOF';
        }

        $config = $currencies[$currency];
        $converted = self::convert($amountInXof, 'XOF', $currency);

        $formatted = number_format(
            $converted,
            $config['decimals'],
            $config['decimal_separator'],
            $config['thousands_separator']
        );

        if ($config['position'] === 'prefix') {
            return $config['symbol'].' '.$formatted;
        }

        return $formatted.' '.$config['symbol'];
    }
}
