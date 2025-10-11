<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Username Akun Royal Clinic</title>
    <style>
        /* BASE STYLES */
        body { font-family: 'Arial', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; line-height: 1.6; color: #333; }
        
        /* CONTAINER */
        .container { 
            max-width: 600px; 
            margin: 20px auto; 
            background-color: #ffffff; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); 
            border: 1px solid #ddd; 
        }
        
        /* HEADER - Warna Primer: #2C7D67 */
        .header { 
            background-color: #2C7D67; /* Primary Color (Teal/Green) */
            color: #ffffff; 
            padding: 30px 20px 10px 20px; 
            text-align: center; 
            /* Tambahkan gradient di sini agar lebih mirip button Flutter */
            background-image: linear-gradient(to right, #2C7D67, #67B09F); 
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            padding-top: 10px;
        }
        .header .logo {
            font-size: 30px;
            font-weight: bold;
            color: #ffffff;
            display: block;
            margin-bottom: 5px;
        }

        /* CONTENT */
        .content { padding: 30px 40px; text-align: left; }
        .content p { margin-bottom: 15px; font-size: 16px; }

        /* USERNAME BOX - Background lebih terang, Text Warna Primer */
        .username-box { 
            background-color: #E6F5F2; /* Very Light Green/Teal for contrast */
            color: #2C7D67; /* Primary Text Color */
            font-size: 24px;
            font-weight: bold;
            padding: 20px 30px;
            margin: 25px auto;
            border-radius: 8px;
            display: block; 
            text-align: center;
            letter-spacing: 2px;
            border: 1px solid #C3E6CB; /* Border sedikit gelap */
            word-break: break-all;
        }
        
        /* INFO BOX */
        .info-box {
            background-color: #FFF3CD;
            border: 1px solid #FFEAA7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 14px;
        }
        
        /* FOOTER */
        .footer { 
            padding: 20px; 
            font-size: 12px; 
            color: #777; 
            text-align: center; 
            border-top: 1px solid #eee; 
            background-color: #f9f9f9; 
        }

        /* Link/Button Style (untuk teks penting) */
        .primary-text {
            color: #2C7D67; /* Warna link/teks penting */
            font-weight: bold;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="logo">⚕️</span> <h1>Royal Clinic</h1>
            <h1 style="font-size: 18px; font-weight: normal; margin-top: 5px;">Informasi Username Akun</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Anda telah meminta informasi <span class="primary-text">Username</span> untuk akun Royal Clinic yang terdaftar dengan email ini.</p>
            
            <p style="text-align: center; margin-bottom: 10px; font-weight: bold;">Username Akun Anda:</p>
            <div class="username-box">{{ $username }}</div>

            <div class="info-box">
                <strong>Informasi Akun:</strong><br>
                <strong>Email:</strong> {{ $email }}<br>
                <strong>Role:</strong> {{ $user_role }}<br>
                <strong>Dikirim pada:</strong> {{ date('d F Y H:i:s') }}
            </div>

            <p style="margin-top: 40px; font-size: 14px; border-top: 1px solid #eee; padding-top: 20px; color: #555;">
                Jika Anda tidak merasa membuat permintaan ini, harap abaikan email ini. Akun Anda tetap aman. Untuk keamanan tambahan, pertimbangkan untuk mengganti password akun Anda.
            </p>

            <p style="font-size: 14px; color: #666; margin-top: 20px;">
                <strong>Tips Keamanan:</strong><br>
                • Jangan bagikan username dan password Anda kepada siapa pun<br>
                • Gunakan password yang kuat dan unik<br>
                • Logout dari aplikasi setelah selesai menggunakan
            </p>
        </div>
        <div class="footer">
            Royal Clinic | Menjaga Kesehatan Anda
            <br>&copy; {{ date('Y') }} Aplikasi Klinik Anda.
        </div>
    </div>
</body>
</html>