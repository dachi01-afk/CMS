<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body>
    <div class="flex items-center justify-center h-screen gap-8">
        <div class="w-64">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 text-center">
                Nama Obat <span class="text-red-500">*</span>
            </label>
            <div class="mt-1">
                <select name="obat" id="input-obat" placeholder="Pilih Obat" autocomplete="off"></select>
            </div>
            <div class="text-red-600 text-[11px] mt-1" data-error="obat_id"></div>
        </div>

        {{-- Kategori Obat --}}
        <div class="w-64">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 text-center">
                Kategori Obat <span class="text-red-500">*</span>
            </label>
            <input name="kategori_obat_id" id="kategori_obat_id"
                class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2
               dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                disabled placeholder="Otomatis">
            <div class="text-red-600 text-[11px] mt-1" data-error="kategori_obat_id"></div>
        </div>
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
<script>
    $(document).ready(function() {
        new TomSelect('#input-obat', {
            valueField: 'id',
            labelField: 'nama_obat',
            searchField: 'nama_obat',
            maxItems: 1,
            preload: true,
            create: true,
            createOnBlur: true,
            openOnFocus: true,

            shouldLoad: function(query) {
                return true;
            },

            load: function(query, callback) {
                $.ajax({
                    url: '/testing-tom-select/data-obat',
                    type: 'GET',
                    data: {
                        q: query || ''
                    },
                    success: function(res) {
                        callback(res);
                    },
                    error: function() {
                        callback();
                    }
                });
            },

            onChange: function(value) {
                const data = this.options[value];
                if (data) {
                    console.log('Data Terpilih:', data);

                    $('#kategori_obat_id').val(data.kategori_obat.nama_kategori_obat);
                } else {
                    $('#kategori_obat_id').val('');
                }
            },
        });
    })
</script>

</html>
