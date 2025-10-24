<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Testing Generate QR Code</title>
    @vite(['resources/css/app.css'])
</head>

<body>
    <div class="flex flex-col items-center justify-center h-screen gap-3">
        <h1>Your QR Code</h1>
        <div>
            {!! $qrCode !!}
        </div>
    </div>
</body>

</html>
