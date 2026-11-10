<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $pageTitle = trim($__env->yieldContent('title'));
        $fullTitle = $pageTitle !== '' ? $pageTitle.' | '.$site['name'] : $site['name'];
        $metaDescription = trim($__env->yieldContent('description'));
        $metaImage = trim($__env->yieldContent('image'));
    @endphp

    <title>{{ $fullTitle }}</title>
    @if ($metaDescription !== '')
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @php
        // Halaman 2+ memakai kanonikal sendiri; parameter filter tidak ikut.
        $canonical = url()->current().(request()->integer('page') > 1 ? '?page='.request()->integer('page') : '');
        $robots = ! config('seo.indexable') ? 'noindex,nofollow' : trim($__env->yieldContent('robots'));
    @endphp
    @if ($robots !== '')
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $site['name'] }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    @if ($metaDescription !== '')
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="id_ID">
    @if ($metaImage !== '')
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    <meta name="twitter:card" content="{{ $metaImage !== '' ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $fullTitle }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
    @stack('jsonld')
</head>
<body class="min-h-screen flex flex-col antialiased">
    <a href="#konten" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">Lewati ke konten</a>

    <x-header :site="$site" />

    <main id="konten" class="flex-1">
        @yield('content')
    </main>

    <x-footer :site="$site" />
</body>
</html>
