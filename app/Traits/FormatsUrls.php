<?php

namespace App\Traits;

trait FormatsUrls
{
    /**
     * Formater une URL pour s'assurer qu'elle commence par http(s)://
     */
    protected function formatUrl(?string $url): ?string
    {
        if (empty($url)) {
            return $url;
        }

        $url = trim($url);

        // Si l'URL ne commence pas par http:// ou https://
        if (! preg_match('/^https?:\/\//i', $url)) {
            // On ajoute https:// par défaut
            return 'https://'.ltrim($url, '/');
        }

        return $url;
    }
}
