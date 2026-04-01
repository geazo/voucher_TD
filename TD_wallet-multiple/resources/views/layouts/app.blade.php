<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.8/build/css/intlTelInput.css">

    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@21.0.8/build/js/intlTelInput.min.js"></script>
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('logo/logo_tamandayu.jpg') }}">
    @yield('styles')
</head>

<body class="bg-light">
    <div class="min-vh-100">
        @include('layouts.navigationL')

        @isset($header)
            <header class="bg-white shadow-sm mb-4">
                <div class="container py-3">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="container py-4">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Fungsi Utama Pemisah Ribuan
        function formatRibuan(value) {
            // Hapus semua karakter selain angka (termasuk huruf dan simbol)
            let number_string = value.replace(/[^,\d]/g, '').toString();
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            // Tambahkan titik jika yang diinput lebih dari 3 digit
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        }

        document.addEventListener('DOMContentLoaded', function() {

            // 2. Event Listener Global (Berlaku untuk elemen lama maupun yang baru dirender)
            document.body.addEventListener('input', function(e) {
                // Jika elemen yang sedang diketik memiliki class 'input-ribuan'
                if (e.target.classList.contains('input-ribuan')) {
                    e.target.value = formatRibuan(e.target.value);
                }
            });

            // 3. Pembersih Siluman (Cegat proses Submit Form)
            document.body.addEventListener('submit', function(e) {
                let form = e.target;
                // Cari semua input ber-class 'input-ribuan' di dalam form yang sedang disubmit
                let ribuanInputs = form.querySelectorAll('.input-ribuan');

                ribuanInputs.forEach(function(input) {
                    // Hilangkan semua titik sebelum dikirim ke Laravel
                    input.value = input.value.replace(/\./g, '');
                });
            });

        });
    </script>
</body>

</html>
