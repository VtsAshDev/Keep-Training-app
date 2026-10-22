<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    @pwaHead('apple-touch-icon.png', '#18181b')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{ $slot }}
</body>
</html>
