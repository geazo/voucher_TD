<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('logo/logo_tamandayu.jpg') }}">
    {{-- <img src="{{ asset('logo/logo-vozatower.png') }}" alt="Foto" class="mr-4 rounded-full h-15 w-15"> --}}
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        @include('layouts.navigationF')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="container px-4 py-6 mx-auto sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="container px-4 mx-auto sm:px-6 lg:px-8">
            @yield('content')
        </main>
    </div>
</body>

</html>
