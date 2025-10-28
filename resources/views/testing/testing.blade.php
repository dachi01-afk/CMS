<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/css/app.js'])
    <title>Document</title>
</head>

<body>
    <div x-data="{ isOpen: false }">
        <button @click="isOpen = !isOpen">Klik Aku</button>

        <div>Nama Saya David</div>
    </div>
</body>

</html>
