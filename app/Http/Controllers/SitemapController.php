<?php

namespace App\Http\Controllers;

use App\Models\MentorProfile;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        // Cache sitemap for 1 hour to improve performance
        return Cache::remember('sitemap', 3600, function () {
            $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
            $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
            $sitemap .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

            // Page d'accueil
            $sitemap .= $this->addUrl(url('/'), '1.0', 'daily', now()->toAtomString());

            // Pages publiques statiques avec dates de modification
            $publicPages = [
                [
                    'url' => '/jeune/connexion',
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => now()->subDays(7)->toAtomString(),
                ],
                [
                    'url' => '/jeune/inscription',
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => now()->subDays(7)->toAtomString(),
                ],
                [
                    'url' => '/mentor/connexion',
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => now()->subDays(7)->toAtomString(),
                ],
                [
                    'url' => '/contact',
                    'priority' => '0.9',
                    'changefreq' => 'monthly',
                    'lastmod' => now()->subDays(3)->toAtomString(),
                ],
                [
                    'url' => '/a-propos',
                    'priority' => '0.7',
                    'changefreq' => 'monthly',
                    'lastmod' => now()->subDays(30)->toAtomString(),
                ],
                [
                    'url' => '/politique-de-confidentialite',
                    'priority' => '0.5',
                    'changefreq' => 'yearly',
                    'lastmod' => now()->subMonths(3)->toAtomString(),
                ],
                [
                    'url' => '/conditions-utilisation',
                    'priority' => '0.5',
                    'changefreq' => 'yearly',
                    'lastmod' => now()->subMonths(3)->toAtomString(),
                ],
            ];

            foreach ($publicPages as $page) {
                $sitemap .= $this->addUrl(
                    url($page['url']),
                    $page['priority'],
                    $page['changefreq'],
                    $page['lastmod']
                );
            }

            // Profils publics des mentors vérifiés avec images
            $verifiedMentors = MentorProfile::where('is_validated', true)
                ->whereNotNull('public_slug')
                ->with('user')
                ->get()
                ->filter(function ($mentor) {
                    return $mentor->user && ! $mentor->user->is_archived;
                }
                );

            foreach ($verifiedMentors as $mentor) {
                $images = [];

                // Ajouter la photo de profil du mentor
                if ($mentor->user && $mentor->user->profile_photo_url) {
                    $images[] = [
                        'loc' => $mentor->user->profile_photo_url,
                        'caption' => $mentor->user->name.' - Mentor Brillio',
                        'title' => $mentor->title ?? 'Mentor professionnel',
                    ];
                }

                $sitemap .= $this->addUrl(
                    url('/mentors/'.$mentor->public_slug),
                    '0.7',
                    'weekly',
                    $mentor->updated_at->toAtomString(),
                    $images
                );
            }

            $sitemap .= '</urlset>';

            return response($sitemap, 200)
                ->header('Content-Type', 'application/xml')
                ->header('Cache-Control', 'public, max-age=3600');
        });
    }

    /**
     * Add URL with optional images to sitemap
     */
    private function addUrl($loc, $priority = '0.5', $changefreq = 'monthly', $lastmod = null, $images = [])
    {
        $url = '<url>';
        $url .= '<loc>'.htmlspecialchars($loc).'</loc>';

        if ($lastmod) {
            $url .= '<lastmod>'.$lastmod.'</lastmod>';
        }

        $url .= '<changefreq>'.$changefreq.'</changefreq>';
        $url .= '<priority>'.$priority.'</priority>';

        // Add image tags if provided
        foreach ($images as $image) {
            $url .= '<image:image>';
            $url .= '<image:loc>'.htmlspecialchars($image['loc']).'</image:loc>';

            if (isset($image['caption'])) {
                $url .= '<image:caption>'.htmlspecialchars($image['caption']).'</image:caption>';
            }

            if (isset($image['title'])) {
                $url .= '<image:title>'.htmlspecialchars($image['title']).'</image:title>';
            }

            $url .= '</image:image>';
        }

        $url .= '</url>';

        return $url;
    }

    /**
     * Clear sitemap cache (call this when content is updated)
     */
    public function clearCache()
    {
        Cache::forget('sitemap');

        return response()->json(['message' => 'Sitemap cache cleared']);
    }
}
