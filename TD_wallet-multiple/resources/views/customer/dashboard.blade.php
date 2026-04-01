@extends('layouts.appC')

@push('styles')
    <style>
        .wallet-carousel {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            padding: 1rem 0 2rem 0;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .wallet-carousel::-webkit-scrollbar {
            display: none;
        }

        /* Kartu mengambil 90% layar dan selalu ke tengah */
        .wallet-carousel .card-wrapper {
            flex: 0 0 90%;
            scroll-snap-align: center;
            margin: 0 5%;
            transition: all 0.3s ease;
            opacity: 0.5;
            transform: scale(0.95);
        }

        /* Saat kartu sedang fokus (ditambahkan oleh JS) */
        .wallet-carousel .card-wrapper.active-card {
            opacity: 1;
            transform: scale(1);
        }
    </style>
@endpush

@section('content')
    <div class="container py-4">
        <div class="mb-2 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1">Halo, {{ $customer->nama }} 👋</h4>
                <p class="text-muted small mb-0">Selamat datang Kembali </p>
            </div>
        </div>

        <div class="position-relative mt-2 mb-2">

            <button id="btnPrevCard"
                class="btn btn-light text-success shadow-sm position-absolute top-50 start-0 translate-middle-y"
                style="z-index: 10; border-radius: 50%; width: 40px; height: 40px; display: none;">
                <i class="bi bi-chevron-left fs-5 fw-bold"></i>
            </button>

            <div class="wallet-carousel mx-n3" id="walletCarousel">
                @forelse($displayCards as $index => $card)
                    @php
                        $m = $card->membership;
                        $tierName = $m ? $m->name : 'Customer';
                        $textColor = $m ? $m->text_color : 'text-dark';
                        $cardStyle = $m
                            ? "background: linear-gradient(135deg, {$m->color_start} 0%, {$m->color_end} 100%); border: none;"
                            : 'background-color: #ffffff; border: 1px solid #dee2e6 !important;';
                        $badgeStyle = $m
                            ? 'background-color: rgba(255,255,255,0.2); color: inherit;'
                            : 'background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;';
                    @endphp

                    <div class="card-wrapper {{ $index === 0 ? 'active-card' : '' }}"
                        data-membership-id="{{ $card->membership_id }}" data-total-saldo="{{ $card->totalSaldo }}">

                        <a href="{{ route('customer.saldo.info') }}?membership_id={{ $card->membership_id }}"
                            class="text-decoration-none">
                            <div class="card {{ $textColor }} shadow h-100"
                                style="border-radius: 1.25rem; {{ $cardStyle }} position: relative; overflow: hidden;">

                                @if ($m)
                                    <div
                                        style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.08); border-radius: 50%;">
                                    </div>
                                @endif

                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div>
                                            <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Member ID</p>
                                            <h5 class="fw-bold mb-0" style="letter-spacing: 2px; font-family: monospace;">
                                                {{ $card->no_rekening }}
                                            </h5>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge shadow-sm mb-2"
                                                style="{{ $badgeStyle }} letter-spacing: 1px; padding: 0.5em 1em;">
                                                {{ strtoupper($tierName) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-6 border-end border-dark border-opacity-10">
                                            <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Saldo Uang</p>
                                            <div class="fw-bold text-nowrap" style="font-size: clamp(1.1rem, 5vw, 1.5rem);">
                                                <span style="font-size: 0.7em;">Rp</span>
                                                {{ number_format($card->saldoUang, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="col-6 ps-3">
                                            <p class="mb-1 opacity-75" style="font-size: 0.8rem;">Saldo Poin</p>
                                            <div class="fw-bold text-nowrap" style="font-size: clamp(1.1rem, 5vw, 1.5rem);">
                                                {{ number_format($card->saldoPoin, 0, ',', '.') }} <span
                                                    style="font-size: 0.7em;" class="opacity-75">Pts</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="card-wrapper active-card w-100 mx-3" data-total-saldo="0">
                        <div class="card bg-light border-0 shadow-sm" style="border-radius: 1.25rem; min-height: 160px;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-muted">
                                <i class="bi bi-wallet2 fs-2 mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Rekening</h6>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>

            <button id="btnNextCard"
                class="btn btn-light text-success shadow-sm position-absolute top-50 end-0 translate-middle-y"
                style="z-index: 10; border-radius: 50%; width: 40px; height: 40px; display: none;">
                <i class="bi bi-chevron-right fs-5 fw-bold"></i>
            </button>
        </div>

        <div class="mb-4 mt-2">
            <a id="btnPayNow" href="{{ route('customer.payment.auth') }}"
                class="btn btn-success w-100 py-3 shadow-sm rounded-4 d-flex justify-content-center align-items-center gap-2"
                style="background: linear-gradient(135deg, #198754, #146c43); border: none;">
                <i class="bi bi-qr-code-scan fs-4"></i>
                <span class="fs-5 fw-bold">BAYAR SEKARANG</span>
            </a>
        </div>

        <div class="card border-0 shadow-sm mb-5">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Riwayat Transaksi</h6>
                <span id="activeTierLabel" class="badge bg-light text-dark border small">Memuat...</span>
            </div>

            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="transactionList">
                    @foreach ($transactions as $t)
                        @php
                            if ($t->type === 'kredit') {
                                $judul = 'Topup Saldo';
                                $icon = 'bi-wallet2 text-success';
                                $tanda = '+';
                                $warna = 'text-success';
                            } elseif ($t->type === 'adjustment') {
                                $judul = 'Expired / Hangus';
                                $icon = 'bi-calendar-x text-warning';
                                $tanda = '-';
                                $warna = 'text-warning';
                            } else {
                                $judul = 'Pembayaran';
                                $icon = 'bi-cart-dash text-danger';
                                $tanda = '-';
                                $warna = 'text-danger';
                            }
                            // Tangkap membership_id dari query
                            $txMembershipId = $t->membership_id ? $t->membership_id : 'default';
                        @endphp

                        <a href="{{ route('customer.transaction.detail', $t->id) }}"
                            class="list-group-item list-group-item-action py-3 border-bottom border-light tx-row"
                            data-membership-id="{{ $txMembershipId }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center"
                                        style="width: 42px; height: 42px;">
                                        <i class="bi {{ $icon }} fs-5"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">{{ $judul }}
                                        </p>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y, H:i') }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="mb-0 fw-bold {{ $warna }}">{{ $tanda }}
                                        {{ number_format($t->total_nominal, 0, ',', '.') }}</p>
                                    <span class="badge bg-light text-muted fw-normal border"
                                        style="font-size: 0.65rem;">{{ strtoupper($t->type) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach

                    <div id="emptyTxMsg" class="p-5 text-center text-muted" style="display: none;">
                        <i class="bi bi-receipt fs-1 d-block mb-2 opacity-25"></i>
                        Belum ada riwayat transaksi untuk kartu ini.
                    </div>
                </div>
            </div>

            @if ($transactions->hasPages())
                <div class="card-footer bg-white border-0 pt-2 pb-3 text-center">
                    <small class="text-muted">Untuk melihat riwayat lama, akses menu Profil.</small>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const carousel = document.getElementById('walletCarousel');
            const cards = document.querySelectorAll('.card-wrapper');
            const txRows = document.querySelectorAll('.tx-row');
            const emptyMsg = document.getElementById('emptyTxMsg');
            const activeTierLabel = document.getElementById('activeTierLabel');

            // Variabel Tombol
            const btnPrev = document.getElementById('btnPrevCard');
            const btnNext = document.getElementById('btnNextCard');
            const btnPayNow = document.getElementById('btnPayNow');
            const btnPayText = document.getElementById('btnPayText');
            const btnPayIcon = document.getElementById('btnPayIcon');

            // 1. FUNGSI UPDATE KARTU AKTIF & DISABLE TOMBOL BAYAR
            function updateActiveCard() {
                if (cards.length === 0) return;

                let containerCenter = carousel.scrollLeft + (carousel.clientWidth / 2);
                let closestCard = cards[0];
                let minDistance = Infinity;

                cards.forEach(card => {
                    let cardCenter = card.offsetLeft + (card.clientWidth / 2) - carousel.offsetLeft;
                    let distance = Math.abs(containerCenter - cardCenter);

                    if (distance < minDistance) {
                        minDistance = distance;
                        closestCard = card;
                    }
                });

                // Efek Visual Kartu Fokus
                cards.forEach(c => c.classList.remove('active-card'));
                closestCard.classList.add('active-card');

                // Filter Daftar Transaksi
                let activeMembershipId = closestCard.getAttribute('data-membership-id');
                let visibleCount = 0;

                txRows.forEach(row => {
                    if (row.getAttribute('data-membership-id') === activeMembershipId) {
                        row.style.display = 'block';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';

                let tierName = closestCard.querySelector('.badge') ? closestCard.querySelector('.badge').innerText :
                    'Reguler';
                activeTierLabel.innerText = "Kartu: " + tierName;

                // ==========================================
                // LOGIKA SMART PAYMENT BUTTON
                // ==========================================
                let totalSaldo = parseFloat(closestCard.getAttribute('data-total-saldo'));
                const btnPayNow = document.getElementById('btnPayNow');

                if (totalSaldo <= 0) {
                    // Tambahkan class disabled bawaan Bootstrap
                    btnPayNow.classList.add('disabled');
                    btnPayNow.href = "javascript:void(0);"; // Cegah aksi klik
                } else {
                    // Hapus class disabled
                    btnPayNow.classList.remove('disabled');

                    let memberIdParam = activeMembershipId && activeMembershipId !== 'default' ? activeMembershipId : '';
                    btnPayNow.href = "{{ route('customer.payment.auth') }}?membership_id=" + memberIdParam;
                }
            }

            // 2. FUNGSI CEK TOMBOL KANAN KIRI
            function checkButtonVisibility() {
                if (cards.length <= 1) {
                    btnPrev.style.display = 'none';
                    btnNext.style.display = 'none';
                    return;
                }

                if (Math.ceil(carousel.scrollLeft) <= 5) {
                    btnPrev.style.display = 'none';
                } else {
                    btnPrev.style.display = 'flex';
                    btnPrev.style.justifyContent = 'center';
                    btnPrev.style.alignItems = 'center';
                }

                let maxScrollLeft = carousel.scrollWidth - carousel.clientWidth;
                if (Math.ceil(carousel.scrollLeft) >= (maxScrollLeft - 5)) {
                    btnNext.style.display = 'none';
                } else {
                    btnNext.style.display = 'flex';
                    btnNext.style.justifyContent = 'center';
                    btnNext.style.alignItems = 'center';
                }
            }

            // 3. EVENT LISTENER TOMBOL KANAN KIRI
            if (btnPrev) {
                btnPrev.addEventListener('click', function() {
                    let cardWidth = cards[0].clientWidth + (cards[0].offsetLeft * 2);
                    carousel.scrollBy({
                        left: -cardWidth,
                        behavior: 'smooth'
                    });
                });
            }

            if (btnNext) {
                btnNext.addEventListener('click', function() {
                    let cardWidth = cards[0].clientWidth + (cards[0].offsetLeft * 2);
                    carousel.scrollBy({
                        left: cardWidth,
                        behavior: 'smooth'
                    });
                });
            }

            // 4. EVENT LISTENER SCROLL
            let scrollTimeout;
            carousel.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(function() {
                    updateActiveCard();
                    checkButtonVisibility();
                }, 50);
            });

            updateActiveCard();
            setTimeout(checkButtonVisibility, 150);
        });
    </script>
@endpush
