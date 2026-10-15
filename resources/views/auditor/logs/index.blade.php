<x-mycomponents.layout>

    <div class="space-y-5">

        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-md shrink-0">
                    <i class="fa-solid fa-clipboard-list text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Audit Log Aktivitas Sistem
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Riwayat aktivitas seluruh pengguna sistem. Log bersifat permanen: tidak dapat diubah maupun
                        dihapus oleh siapa pun.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="export-xlsx"
                    class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
                <button type="button" id="export-csv"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-file-csv"></i> CSV
                </button>
            </div>
        </div>

        {{-- Filter --}}
        <form id="filter-form"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div>
                    <label for="filter-from" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal Mulai
                    </label>
                    <input type="date" id="filter-from" name="from"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label for="filter-to" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Tanggal Akhir
                    </label>
                    <input type="date" id="filter-to" name="to"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label for="filter-user" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        User
                    </label>
                    <select id="filter-user" name="user_id"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Semua user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->role }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-role" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Role
                    </label>
                    <select id="filter-role" name="role"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Semua role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-module" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Modul
                    </label>
                    <select id="filter-module" name="module"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Semua modul</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}">{{ $module }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter-action" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Action
                    </label>
                    <select id="filter-action" name="action"
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">Semua action</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}">{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="filter-keyword" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Kata Kunci
                    </label>
                    <input type="text" id="filter-keyword" name="keyword"
                        placeholder="Cari deskripsi, nama user, modul..."
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    <i class="fa-solid fa-filter"></i> Tampilkan
                </button>
                <button type="button" id="filter-reset"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </form>

        {{-- Tabel --}}
        <div
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                        <select id="audit-log-page-length"
                            class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="10">10 baris</option>
                            <option value="25">25 baris</option>
                            <option value="50">50 baris</option>
                            <option value="100">100 baris</option>
                        </select>
                        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="table-audit-log"
                    class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                    <thead
                        class="text-[11px] md:text-xs font-semibold uppercase bg-gradient-to-r from-sky-600 via-sky-500 to-indigo-600 text-white tracking-wide">
                        <tr>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Waktu</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">User</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Role</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Action</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Modul</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3">Deskripsi</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">IP</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                </table>
            </div>

            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
                <div id="audit-log-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

                <div class="w-full md:w-auto overflow-x-auto">
                    <ul id="audit-log-custom-pagination"
                        class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                    </ul>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-400">
            Catatan: jumlah login gagal, aktivitas di luar jam operasional, dan tren per hari dapat dilihat di
            <a href="{{ route('auditor.dashboard') }}" class="text-sky-600 hover:underline font-semibold">Dashboard Aktivitas</a>.
            Riwayat per pasien ada di menu <a href="{{ route('auditor.logs.entity') }}"
                class="text-sky-600 hover:underline font-semibold">Riwayat per Entitas</a>.
        </p>
    </div>

    @vite(['resources/js/auditor/data-audit-log.js'])
</x-mycomponents.layout>
