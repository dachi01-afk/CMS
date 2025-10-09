<aside class="w-64 bg-blue-800 text-white flex flex-col">
    <div class="p-4 text-2xl font-bold border-b border-blue-600">Panel Dokter</div>
    <nav class="flex-1 p-4 space-y-2">
        <a href="{{ route('dokter.dashboard') }}" class="block px-3 py-2 rounded hover:bg-blue-700">Dashboard</a>
        <div>
            <a href="{{ route('dokter.kunjungan') }}" class="block px-3 py-2 rounded hover:bg-blue-700">Kunjungan</a>
        </div>
        <div>
            <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">Riwayat Pasien</a>
        </div>
        <a href="#" class="block px-3 py-2 rounded hover:bg-blue-700">Profil Dokter</a>
    </nav>
</aside>
