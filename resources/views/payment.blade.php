<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment</title>
</head>

<body>
    <h2>Bayar Sekarang</h2>

    <button id="pay-button">Bayar</button>

    <!-- Midtrans JS -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script>
    {{-- <script type="text/javascript" src="https://app.midtrans.com/snap/snap.js"
        data-client-key="{{ config('midtrans.client_key') }}"></script> --}}

    <script type="text/javascript">
        document.getElementById('pay-button').onclick = function() {
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    alert("Pembayaran Berhasil!");
                    console.log(result);
                },
                onPending: function(result) {
                    alert("Menunggu Pembayaran...");
                    console.log(result);
                },
                onError: function(result) {
                    alert("Pembayaran Gagal!");
                    console.log(result);
                },
                onClose: function() {
                    alert('Kamu menutup popup tanpa menyelesaikan pembayaran');
                }
            });
        };
    </script>
</body>

</html>
