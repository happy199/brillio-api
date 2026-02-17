@props([
'page' => 'default',
'title' => null,
'description' => null,
'keywords' => null,
'image' => null,
'type' => null,
'noindex' => false,
'nofollow' => false,
'canonical' => null,
'hreflang' => true,
])

@php
$seoService = app(\App\Services\SeoService::class);
$meta = $seoService->getMetaTags($page, array_filter([
'title' => $title,
'description' => $description,
'keywords' => $keywords,
'image' => $image,
'type' => $type,
]));

$ogTags = $seoService->getOpenGraphTags($meta);
$twitterTags = $seoService->getTwitterCardTags($meta);
$robotsMeta = $seoService->getRobotsMeta(!$noindex, !$nofollow);
$canonicalUrl = $seoService->getCanonicalUrl($canonical);
@endphp

{{-- Title --}}
<title>{{ $meta['title'] }}</title>

{{-- Meta Tags --}}
<meta name="description" content="{{ $meta['description'] }}">
@if(isset($meta['keywords']))
<meta name="keywords" content="{{ $meta['keywords'] }}">
@endif
<meta name="author" content="{{ config('seo.default.author') }}">
<meta name="robots" content="{{ $robotsMeta }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonicalUrl }}">

{{-- Open Graph / Facebook --}}
@foreach($ogTags as $property => $content)
<meta property="{{ $property }}" content="{{ $content }}">
@endforeach

{{-- Twitter Card --}}
@foreach($twitterTags as $name => $content)
<meta name="{{ $name }}" content="{{ $content }}">
@endforeach

{{-- Hreflang Tags for International SEO --}}
@if($hreflang)
@php
$hreflangs = $seoService->getHreflangTags(app()->getLocale());
@endphp
@foreach($hreflangs as $lang => $url)
<link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
@endforeach
@endif

{{-- Favicons (reusable) --}}
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}?v=2">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('android-chrome-512x512.png') }}?v=2">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#6366f1">

{{-- CSRF Token --}}
<meta name="csrf-token" content="{{ csrf_token() }}">