<?php

namespace App\Traits;

trait HandlesAfricanPhoneNumbers
{
    private const COTE_D_IVOIRE_VAL = "Cote d'Ivoire";

    protected function isValidAfricanPhoneNumber(string $country, string $phone): bool
    {
        $configs = $this->getAfricanPhoneConfigs();
        $clean = preg_replace('/\D/', '', $phone);

        if (! isset($configs[$country])) {
            return strlen($clean) >= 7 && strlen($clean) <= 12;
        }

        $config = $configs[$country];
        $dialCode = preg_replace('/\D/', '', $config['code']);

        if (str_starts_with($clean, $dialCode)) {
            $local = substr($clean, strlen($dialCode));
        } elseif (str_starts_with($clean, '00'.$dialCode)) {
            $local = substr($clean, strlen('00'.$dialCode));
        } else {
            $local = $clean;
        }

        if ($country === self::COTE_D_IVOIRE_VAL && strlen($local) === 9 && in_array($local[0], ['1', '5', '7'])) {
            $local = '0'.$local;
        }
        if ($country === 'Benin' && strlen($local) === 9 && in_array($local[0], ['1', '4', '5', '6', '9'])) {
            $local = '0'.$local;
        }

        foreach ($config['lengths'] as $len) {
            if (strlen($local) === $len) {
                return true;
            }
            if (strlen($local) === $len + 1 && str_starts_with($local, '0')) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeToE164(string $country, string $phone): string
    {
        $configs = $this->getAfricanPhoneConfigs();
        $clean = preg_replace('/\D/', '', $phone);

        if (! isset($configs[$country])) {
            return '+'.$clean;
        }

        $config = $configs[$country];
        $dialCode = preg_replace('/\D/', '', $config['code']);

        if (str_starts_with($clean, $dialCode)) {
            $local = substr($clean, strlen($dialCode));
        } elseif (str_starts_with($clean, '00'.$dialCode)) {
            $local = substr($clean, strlen('00'.$dialCode));
        } else {
            $local = $clean;
        }

        if ($country === self::COTE_D_IVOIRE_VAL && strlen($local) === 9 && in_array($local[0], ['1', '5', '7'])) {
            $local = '0'.$local;
        }
        if ($country === 'Benin' && strlen($local) === 9 && in_array($local[0], ['1', '4', '5', '6', '9'])) {
            $local = '0'.$local;
        }

        foreach ($config['lengths'] as $len) {
            if (strlen($local) === $len + 1 && str_starts_with($local, '0')) {
                $local = substr($local, 1);
                break;
            }
        }

        return '+'.$dialCode.$local;
    }

    protected function getAfricanPhoneConfigs(): array
    {
        return [
            'Algerie' => ['code' => '+213', 'lengths' => [9]],
            'Angola' => ['code' => '+244', 'lengths' => [9]],
            'Benin' => ['code' => '+229', 'lengths' => [8, 10]],
            'Botswana' => ['code' => '+267', 'lengths' => [7, 8]],
            'Burkina Faso' => ['code' => '+226', 'lengths' => [8]],
            'Burundi' => ['code' => '+257', 'lengths' => [8]],
            'Cap-Vert' => ['code' => '+238', 'lengths' => [7]],
            'Cameroun' => ['code' => '+237', 'lengths' => [9]],
            'Centrafrique' => ['code' => '+236', 'lengths' => [8]],
            'Tchad' => ['code' => '+235', 'lengths' => [8]],
            'Comores' => ['code' => '+269', 'lengths' => [7]],
            'Congo' => ['code' => '+242', 'lengths' => [7, 9]],
            'RD Congo' => ['code' => '+243', 'lengths' => [9]],
            'Djibouti' => ['code' => '+253', 'lengths' => [8]],
            'Egypte' => ['code' => '+20', 'lengths' => [9, 10, 11]],
            'Guinee Equatoriale' => ['code' => '+240', 'lengths' => [9]],
            'Erythree' => ['code' => '+291', 'lengths' => [7]],
            'Eswatini' => ['code' => '+268', 'lengths' => [8]],
            'Ethiopie' => ['code' => '+251', 'lengths' => [9]],
            'Gabon' => ['code' => '+241', 'lengths' => [7, 8, 9]],
            'Gambie' => ['code' => '+220', 'lengths' => [7]],
            'Ghana' => ['code' => '+233', 'lengths' => [9]],
            'Guinee' => ['code' => '+224', 'lengths' => [9]],
            'Guinee-Bissau' => ['code' => '+245', 'lengths' => [7, 9]],
            self::COTE_D_IVOIRE_VAL => ['code' => '+225', 'lengths' => [10]],
            'Kenya' => ['code' => '+254', 'lengths' => [9]],
            'Lesotho' => ['code' => '+266', 'lengths' => [8]],
            'Liberia' => ['code' => '+231', 'lengths' => [7, 9]],
            'Libye' => ['code' => '+218', 'lengths' => [9, 10]],
            'Madagascar' => ['code' => '+261', 'lengths' => [9]],
            'Malawi' => ['code' => '+265', 'lengths' => [7, 8, 9]],
            'Mali' => ['code' => '+223', 'lengths' => [8]],
            'Mauritanie' => ['code' => '+222', 'lengths' => [8]],
            'Maurice' => ['code' => '+230', 'lengths' => [7, 8]],
            'Maroc' => ['code' => '+212', 'lengths' => [9]],
            'Mozambique' => ['code' => '+258', 'lengths' => [9]],
            'Namibie' => ['code' => '+264', 'lengths' => [8, 9]],
            'Niger' => ['code' => '+227', 'lengths' => [8]],
            'Nigeria' => ['code' => '+234', 'lengths' => [10]],
            'Rwanda' => ['code' => '+250', 'lengths' => [9]],
            'Sao Tome-et-Principe' => ['code' => '+239', 'lengths' => [7]],
            'Senegal' => ['code' => '+221', 'lengths' => [9]],
            'Seychelles' => ['code' => '+248', 'lengths' => [7]],
            'Sierra Leone' => ['code' => '+232', 'lengths' => [8]],
            'Somalie' => ['code' => '+252', 'lengths' => [8, 9]],
            'Afrique du Sud' => ['code' => '+27', 'lengths' => [9]],
            'Soudan du Sud' => ['code' => '+211', 'lengths' => [9]],
            'Soudan' => ['code' => '+249', 'lengths' => [9]],
            'Tanzanie' => ['code' => '+255', 'lengths' => [9]],
            'Togo' => ['code' => '+228', 'lengths' => [8]],
            'Tunisie' => ['code' => '+216', 'lengths' => [8]],
            'Ouganda' => ['code' => '+256', 'lengths' => [9]],
            'Zambie' => ['code' => '+260', 'lengths' => [9]],
            'Zimbabwe' => ['code' => '+263', 'lengths' => [9]],
        ];
    }

    protected function getAfricanCountries(): array
    {
        return [
            'DZ' => 'Algerie',
            'AO' => 'Angola',
            'BJ' => 'Benin',
            'BW' => 'Botswana',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'CV' => 'Cap-Vert',
            'CM' => 'Cameroun',
            'CF' => 'Centrafrique',
            'TD' => 'Tchad',
            'KM' => 'Comores',
            'CG' => 'Congo',
            'CD' => 'RD Congo',
            'DJ' => 'Djibouti',
            'EG' => 'Egypte',
            'GQ' => 'Guinee Equatoriale',
            'ER' => 'Erythree',
            'SZ' => 'Eswatini',
            'ET' => 'Ethiopie',
            'GA' => 'Gabon',
            'GM' => 'Gambie',
            'GH' => 'Ghana',
            'GN' => 'Guinee',
            'GW' => 'Guinee-Bissau',
            'CI' => self::COTE_D_IVOIRE_VAL,
            'KE' => 'Kenya',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libye',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'ML' => 'Mali',
            'MR' => 'Mauritanie',
            'MU' => 'Maurice',
            'MA' => 'Maroc',
            'MZ' => 'Mozambique',
            'NA' => 'Namibie',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'RW' => 'Rwanda',
            'ST' => 'Sao Tome-et-Principe',
            'SN' => 'Senegal',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SO' => 'Somalie',
            'ZA' => 'Afrique du Sud',
            'SS' => 'Soudan du Sud',
            'SD' => 'Soudan',
            'TZ' => 'Tanzanie',
            'TG' => 'Togo',
            'TN' => 'Tunisie',
            'UG' => 'Ouganda',
            'ZM' => 'Zambie',
            'ZW' => 'Zimbabwe',
        ];
    }
}
