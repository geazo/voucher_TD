<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Voucher QR</title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .voucher-container {
            width: 100%;
            height: 100%;
            position: relative;
            page-break-after: always;
            background-image: url('{{ public_path('storage/benefit-bg.jpg') }}');
            /* ganti sesuai path */
            background-size: cover;
            background-position: center;
        }

        .voucher-container:last-child {
            page-break-after: auto;
        }

        .info {
            position: absolute;
            top: 120px;
            /* dari sebelumnya 150px, jadi lebih ke atas */
            left: 40px;
            color: #000;
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.1;
            max-width: 300px;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 8px;
            border-radius: 6px;
        }

        .info h2 {
            margin: 0 0 4px;
            /* jarak antar judul dan teks dikurangi */
            font-size: 16px;
            /* judul sedikit lebih besar */
        }

        .info p {
            margin: 3px 0;
            /* Memberi jarak atas & bawah paragraf hanya 3px */
        }

        .qr-code {
            position: absolute;
            right: 40px;
            bottom: 20px;
            width: 140px;
            height: auto;
        }
    </style>
</head>

<body>
    @foreach ($voucher->voucherBenefits as $index => $vb)
        <div class="voucher-container"
            style="background-image: url('{{ public_path('storage/' . $vb->benefit->photo) }}');">
            <!-- Info kiri atas -->
            <div class="info">
                <h2>Your Voucher</h2>
                <p><strong>Name:</strong> {{ $penerima->name }}</p>
                <p><strong>Voucher Code:</strong> {{ $vb->kode_benefit }}</p>
                <p><strong>Redemption Desk:</strong> {{ $vb->benefit->redemption }}</p>
                <p><strong>Date Issued:</strong>
                    {{ \Carbon\Carbon::parse($voucher->tgl_terbit_voucher)->format('d/m/Y') }}</p>
                <p><strong>Expired Date:</strong>
                    {{ \Carbon\Carbon::parse($voucher->tgl_exp_voucher)->format('d/m/Y') }}</p>
            </div>

            <!-- QR code kanan bawah -->
            <img src="{{ $vb->qr_path }}" alt="QR Code" class="qr-code">
        </div>
    @endforeach
</body>

</html>
