<?php

namespace App\Services;

class SeoService
{
    /**
     * Get SEO meta tags for a specific page
     *
     * @param  string  $page  Page identifier (e.g., 'home', 'mentors', 'contact')
     * @param  array  $customData  Custom data to override defaults
     */
    public function getMetaTags(string $page = 'default', array $customData = []): array
    {
        $config = config("seo.pages.{$page}", config('seo.default'));

        return array_merge($config, $customData);
    }

    /**
     * Generate title with separator
     */
    public function generateTitle(string $title, bool $appendSiteName = false): string
    {
        if ($appendSiteName) {
            $separator = config('seo.default.title_separator', ' - ');
            $siteName = config('seo.site.name', 'Brillio');

            return $title.$separator.$siteName;
        }

        return $title;
    }

    /**
     * Get robots meta content
     */
    public function getRobotsMeta(?bool $index = null, ?bool $follow = null): string
    {
        $index = $index ?? config('seo.robots.index', true);
        $follow = $follow ?? config('seo.robots.follow', true);

        $robots = [];
        $robots[] = $index ? 'index' : 'noindex';
        $robots[] = $follow ? 'follow' : 'nofollow';

        // Add advanced directives
        if ($maxSnippet = config('seo.robots.max_snippet')) {
            $robots[] = $maxSnippet === -1 ? 'max-snippet:-1' : "max-snippet:{$maxSnippet}";
        }

        if ($maxImage = config('seo.robots.max_image_preview')) {
            $robots[] = "max-image-preview:{$maxImage}";
        }

        if ($maxVideo = config('seo.robots.max_video_preview')) {
            $robots[] = $maxVideo === -1 ? 'max-video-preview:-1' : "max-video-preview:{$maxVideo}";
        }

        return implode(', ', $robots);
    }

    /**
     * Get canonical URL
     */
    public function getCanonicalUrl(?string $url = null): string
    {
        return $url ?? url()->current();
    }

    /**
     * Get Open Graph meta tags
     */
    public function getOpenGraphTags(array $data): array
    {
        $defaults = [
            'og:type' => config('seo.default.type', 'website'),
            'og:url' => url()->current(),
            'og:site_name' => config('seo.site.name', 'Brillio'),
            'og:locale' => config('seo.default.locale', 'fr_FR'),
        ];

        $og = [];

        if (isset($data['og_title']) || isset($data['title'])) {
            $og['og:title'] = $data['og_title'] ?? $data['title'];
        }

        if (isset($data['og_description']) || isset($data['description'])) {
            $og['og:description'] = $data['og_description'] ?? $data['description'];
        }

        if (isset($data['og_image']) || isset($data['image'])) {
            $image = $data['og_image'] ?? $data['image'];
            $og['og:image'] = str_starts_with($image, 'http') ? $image : asset($image);
            $og['og:image:alt'] = $data['og_image_alt'] ?? ($data['og_title'] ?? $data['title']);
        }

        return array_merge($defaults, $og);
    }

    /**
     * Get Twitter Card meta tags
     */
    public function getTwitterCardTags(array $data): array
    {
        $twitter = [
            'twitter:card' => $data['twitter_card'] ?? 'summary_large_image',
            'twitter:site' => config('seo.site.twitter', '@brillioafrica'),
        ];

        if (isset($data['og_title']) || isset($data['title'])) {
            $twitter['twitter:title'] = $data['og_title'] ?? $data['title'];
        }

        if (isset($data['og_description']) || isset($data['description'])) {
            $twitter['twitter:description'] = $data['og_description'] ?? $data['description'];
        }

        if (isset($data['og_image']) || isset($data['image'])) {
            $image = $data['og_image'] ?? $data['image'];
            $twitter['twitter:image'] = str_starts_with($image, 'http') ? $image : asset($image);
        }

        return $twitter;
    }

    /**
     * Get hreflang tags for international SEO
     */
    public function getHreflangTags(string $currentLocale = 'fr'): array
    {
        $locales = config('seo.locales', []);
        $hreflangs = [];
        $currentPath = request()->path();

        foreach ($locales as $locale => $config) {
            $url = config('seo.site.url');

            if (! empty($config['url_prefix'])) {
                $url .= $config['url_prefix'];
            }

            if ($currentPath && $currentPath !== '/') {
                $url .= '/'.$currentPath;
            }

            $hreflangs[$config['hreflang']] = $url;
        }

        // Add x-default
        $hreflangs['x-default'] = config('seo.site.url');

        return $hreflangs;
    }

    /**
     * Generate JSON-LD Organization Schema
     */
    public function getOrganizationSchema(): array
    {
        $org = config('seo.organization');
        $social = config('seo.social');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $org['name'],
            'legalName' => $org['legal_name'] ?? $org['name'],
            'url' => config('seo.site.url'),
            'logo' => asset('android-chrome-512x512.png'),
            'foundingDate' => $org['foundation_date'],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Service',
                'email' => $org['email'],
                'telephone' => $org['phone'],
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $org['address']['street'],
                'addressLocality' => $org['address']['city'],
                'addressRegion' => $org['address']['region'],
                'addressCountry' => $org['address']['country'],
            ],
            'sameAs' => array_values($social),
        ];
    }

    /**
     * Generate JSON-LD WebSite Schema with SearchAction
     */
    public function getWebSiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.site.name'),
            'url' => config('seo.site.url'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => config('seo.site.url').'/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * Generate JSON-LD BreadcrumbList Schema
     */
    public function getBreadcrumbSchema(array $breadcrumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($breadcrumbs as $breadcrumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Generate JSON-LD Person Schema for mentor profiles
     *
     * @param  object  $mentor
     */
    public function getPersonSchema($mentor): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $mentor->user->name ?? '',
            'jobTitle' => $mentor->title ?? '',
            'description' => $mentor->bio ?? '',
            'image' => $mentor->user->profile_photo_url ?? '',
            'url' => url('/mentors/'.$mentor->public_slug),
            'worksFor' => $mentor->company ?? null,
            'knowsAbout' => $mentor->tags ?? [],
        ];
    }

    /**
     * Generate JSON-LD FAQ Schema
     */
    public function getFAQSchema(array $faqs): array
    {
        $mainEntity = [];

        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}
