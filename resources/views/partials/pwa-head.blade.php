<!-- PWA Meta & Icons -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Brillio">
<meta name="application-name" content="Brillio">
<meta name="color-scheme" content="light dark">
<meta name="msapplication-TileColor" content="{{ $primaryColor ?? '#6366f1' }}">
<meta name="msapplication-TileImage" content="{{ asset('android-chrome-192x192.png') }}">
<meta name="theme-color" content="{{ $primaryColor ?? '#6366f1' }}">

<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=2">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=2">

<!-- iOS Splash Screens -->
<link rel="apple-touch-startup-image" href="{{ asset('apple-touch-icon.png') }}">
<link rel="apple-touch-startup-image" href="{{ asset('splash/launch-750x1334.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
<link rel="apple-touch-startup-image" href="{{ asset('splash/launch-1125x2436.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
<link rel="apple-touch-startup-image" href="{{ asset('splash/launch-1242x2208.png') }}" media="(device-width: 414px) and (device-height: 736px) and (-webkit-device-pixel-ratio: 3)">

<!-- PWA Mobile Ergonomics & Motion Safety -->
<style>
    html, body {
        overscroll-behavior-y: contain;
    }
    button, a, input, select, textarea {
        touch-action: manipulation;
    }
    @media (prefers-reduced-motion: reduce) {
        *, ::before, ::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
