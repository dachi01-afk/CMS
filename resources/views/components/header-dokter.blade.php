<!-- resources/views/components/header.blade.php -->
<header class="bg-white shadow p-4 flex justify-between items-center">
    <h1 class="text-xl font-semibold text-gray-800">{{ $slot }}</h1>

    <div class="flex items-center space-x-4">
        <span class="text-gray-600"> {{ Auth::user()->dokter->nama_dokter ?? 'Nama Dokter' }}</span>
        <form action="#">
            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">
                Logout
            </button>
        </form>
    </div>
</header>
