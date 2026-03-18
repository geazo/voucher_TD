<!DOCTYPE html>
<html>

<head>
    <title>Your Voucher</title>
</head>

<body>
    {{-- Tambahan khusus Padel Malang --}}
    @php
        $isPadel = optional($voucher->outlet)->kode === 'PDM' || \Illuminate\Support\Str::lower(optional($voucher->outlet)->name) === 'padel malang';
    @endphp
    <p>Dear Mr. {{ $penerima->name }},</p>
    @if ($isPadel)
        <p><em>This Voucher from Padel Malang Event.</em></p>
    @endif
    <p>We are pleased to present your exclusive voucher:</p>
    <ul>
        <li><strong>Voucher ID:</strong> {{ $voucher->kode_voucher }}</li>
        <li><strong>Outlet:</strong> {{ $voucher->outlet->name }}</li>
        {{-- <li><strong>Deskripsi:</strong> {{ $voucher->description }}</li> --}}
        <li><strong>Valid From:</strong> {{ \Carbon\Carbon::parse($voucher->tgl_terbit_voucher)->format('d/m/Y') }}</li>
        <li><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($voucher->tgl_exp_voucher)->format('d/m/Y') }}</li>
    </ul>
    <p>For your convenience, please download your QR code from the PDF via the link below:</p>
    <p><a href="{{ $pdfUrl }}" target="_blank">{{ $pdfUrl }}</a></p>
    <p>We look forward to providing you with an exceptional dining experience.</p>
    <p>With warmest regards,</p>

    {{-- <p>Thank You</p> --}}
</body>

</html>
