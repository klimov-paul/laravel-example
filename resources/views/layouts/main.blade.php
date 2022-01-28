<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('meta-title') | {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta-description', config('app.name'))" />
    <meta name="robots" content="@yield('meta-robots', config('appearance.allow_robots') ? 'index,follow' : 'noindex,nofollow')" />
    @stack('meta')

    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="theme-color" content="#ffffff">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Manifest -->
    <link rel="manifest" href="{{ route('manifest.webmanifest') }}">
    <!-- Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/favicons/favicon-16x16.png') }}">
    <link rel="mask-icon" href="{{ asset('img/favicons/safari-pinned-tab.svg') }}" color="#38bdf8">
    <link rel="shortcut icon" href="{{ asset('img/favicons/favicon.ico') }}">

    <!-- Styles -->
    @stack('before-styles')
    <link href="{{ asset(mix('css/app.css')) }}" rel="stylesheet">
    @stack('after-styles')
</head>
<body>
    <div id="app" class="container">
        @include('includes.header')
        @yield('content')
        @include('includes.footer')
    </div>
    <!-- Scripts -->
    @stack('before-scripts')
    <script src="{{ asset(mix('js/app.js')) }}"></script>
    @stack('after-scripts')
</body>
</html>
