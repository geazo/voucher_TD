<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Halo, Customer Taman Dayu!</h2>
    <p>Kami menerima permintaan untuk mengatur ulang password akun Anda.</p>
    <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>

    <a href="{{ $resetUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #198754; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Reset Password Sekarang
    </a>

    <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
        Jika Anda tidak merasa meminta reset password, abaikan saja email ini.<br>
        Link ini akan kedaluwarsa dalam waktu 60 menit.
    </p>
</body>
</html>
