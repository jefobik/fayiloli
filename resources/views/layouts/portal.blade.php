<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Fayiloli EDMS') }} — Find Your Organisation</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon"  href="/favicon.ico">
    <link rel="apple-touch-icon"          href="/img/fayiloli-icon.svg">

    {{-- Inter font ---------------------------------------------------------------- --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    {{-- Font Awesome 6 (icons) --------------------------------------------------- --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{--
        Alpine.js from the shared Vite bundle (same version, versioned hash, CDN-cached).
        JS only — app.css is intentionally excluded so EDMS layout CSS does not bleed.
    --}}
    @vite(['resources/js/app.js'])

    {{-- Portal base styles ------------------------------------------------------- --}}
    <style>
        /* Alpine: hide x-cloak elements until Alpine initialises */
        [x-cloak] { display: none !important; }

        /* Prevent FOUC on page with Alpine */
        html { visibility: visible; }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            min-height: 100%;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        a { color: inherit; text-decoration: none; }

        /* Keyboard focus ring — matches app.css WCAG 2.4.7 */
        :focus { outline: none; }
        :focus-visible {
            outline: 2px solid #7c3aed;
            outline-offset: 2px;
            border-radius: 4px;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.15);
        }

        /* Respect user motion preference */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

@yield('content')

</body>
</html>
