<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Page d'accueil
        $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');

        // Pages publiques statiques
        $publicPages = [
            '/jeune/connexion',
            '/jeune/inscription',
            '/mentor/connexion',
            '/politique-de-confidentialite',
        ];

        foreach ($publicPages as $page) {
            $sitemap .= $this->addUrl(url($page), '0.8', 'weekly');
        }

        // Profils publics des mentors vérifiés
        $verifiedMentors = MentorProfile::where('is_validated', true)
            ->whereNotNull('public_slug')
            ->with('user')
            ->get()
            ->filter(function ($mentor) {
                return $mentor->user && !$mentor->user->is_archived;
            });

        foreach ($verifiedMentors as $mentor) {
            $sitemap .= $this->addUrl(
                url('/mentors/' . $mentor->public_slug),
                '0.7',
                'weekly',
                $mentor->updated_at->toAtomString()
            );
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    private function addUrl($loc, $priority = '0.5', $changefreq = 'monthly', $lastmod = null)
    {
        $url = '<url>';
        $url .= '<loc>' . htmlspecialchars($loc) . '</loc>';

        if ($lastmod) {
            $url .= '<lastmod>' . $lastmod . '</lastmod>';
        }

        $url .= '<changefreq>' . $changefreq . '</changefreq>';
        $url .= '<priority>' . $priority . '</priority>';
        $url .= '</url>';

        return $url;
    }
}
