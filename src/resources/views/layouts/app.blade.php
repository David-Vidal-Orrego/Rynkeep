@include('layouts.tema')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div x-data="{ lateral: false }" class="min-h-screen bg-fondo">
        @include('layouts.navigation')

        <div class="flex">
            @include('layouts.lateral')

            <div class="flex-1 min-w-0">
                @isset($header)
                <header class="bg-superficie shadow">
                    <div class="max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</body>

</html>