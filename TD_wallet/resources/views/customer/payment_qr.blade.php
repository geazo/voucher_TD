@extends('layouts.appC')

@section('content')
    <div class="container py-1">
        <div class="row justify-content-center">
            <div class="col-md-5 text-center">
                <h5 class="fw-bold mb-1">Tunjukkan QR Ini ke Kasir</h5>
                <p class="text-muted small mb-3">QR Code ini otomatis kedaluwarsa dalam <span class="fw-bold">1 menit</span>.
                </p>

                <h2 id="timerDisplay" class="fw-bold text-danger mb-4">01:00</h2>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3">

                        <div class="bg-white p-3 rounded shadow-sm d-inline-block">
                            {!! $qrCode !!}
                        </div>

                        <h5 class="fw-bold mt-4 mb-0">{{ $customer->nama }}</h5>
                        <span class="badge bg-dark mt-2">{{ $customer->membership->name ?? 'Reguler' }}</span>
                    </div>
                </div>

                <a href="{{ route('customer.dashboard') }}" id="btnAction"
                    class="btn btn-outline-danger w-100 fw-bold py-3 rounded-3">
                    <i class="bi bi-x-circle me-2"></i> TUTUP & KEMBALI
                </a>
            </div>
        </div>
    </div>

    <script>
        // 1. Ambil data variabel dari Controller
        let timeLeft = {{ $sisaDetik ?? 60 }};
        let token = '{{ $uniqueToken }}';
        let timerDisplay = document.getElementById('timerDisplay');

        // 2. Fungsi untuk memformat dan menampilkan waktu (MM:SS)
        function updateTimer() {
            let m = Math.floor(timeLeft / 60);
            let s = Math.floor(timeLeft % 60);
            timerDisplay.innerHTML = (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
        }

        // Panggil sekali saat halaman dimuat agar angka langsung akurat tanpa menunggu 1 detik
        updateTimer();

        // 3. Jalankan Countdown (Timer)
        let countdown = setInterval(function() {
            timeLeft--;
            updateTimer();

            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerDisplay.innerHTML = "00:00";

                // Ubah tampilan menjadi kedaluwarsa jika waktu habis
                document.querySelector('.card-body').innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-x-circle text-danger mb-2" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold text-danger mt-2">QR Code Kedaluwarsa</h5>
                    <p class="text-muted small">Silakan buat ulang QR Code Anda.</p>
                    <a href="{{ route('customer.payment.auth') }}" class="btn btn-sm btn-outline-primary mt-2">Buat QR Baru</a>
                </div>
            `;
            }
        }, 1000);

        // 4. Polling Check Status (Menunggu Kasir Men-scan)
        let checkStatus = setInterval(function() {
            // Hanya mengecek jika waktu masih ada
            if (timeLeft > 0) {
                // Gunakan URL yang mengarah ke route payment.check_status milikmu
                fetch(`/customer/pay/check-status/${token}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Hentikan semua timer jika pembayaran sukses terdeteksi
                            clearInterval(countdown);
                            clearInterval(checkStatus);

                            // Tampilkan layar sukses hijau
                            document.querySelector('.card-body').innerHTML = `
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                                <h4 class="fw-bold mt-3 text-success">Pembayaran Berhasil!</h4>
                                <p class="text-muted small">Transaksi telah diproses oleh kasir.</p>
                            </div>
                        `;

                            let invoiceUrl = `/customer/pay/invoice/${token}`;
                            // UBAH TOMBOL BAWAH MENJADI "LIHAT STRUK"
                            let btnAction = document.getElementById('btnAction');
                            btnAction.href = invoiceUrl; // Ganti link ke halaman invoice
                            btnAction.className = "btn btn-success w-100 fw-bold py-3 rounded-3"; // Ganti warna jadi hijau
                            btnAction.innerHTML = '<i class="bi bi-receipt me-2"></i> LIHAT STRUK SEKARANG'; // Ganti teks dan ikon

                            // Pindah otomatis ke halaman Struk/Invoice dalam 7 detik
                            setTimeout(() => {
                                window.location.href = `/customer/pay/invoice/${token}`;
                            }, 7000);
                        }
                    })
                    .catch(error => console.error('Gagal mengecek status transaksi:', error));
            } else {
                // Hentikan pengecekan jika waktu habis
                clearInterval(checkStatus);
            }
        }, 2000); // Mengecek ke database setiap 2 detik
    </script>
@endsection
