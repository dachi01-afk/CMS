@php
    $styles = [
        'create' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Tambah'],
        'update' => ['bg-amber-50 text-amber-700 border-amber-200', 'Ubah'],
        'delete' => ['bg-rose-50 text-rose-700 border-rose-200', 'Hapus'],
        'view' => ['bg-sky-50 text-sky-700 border-sky-200', 'Lihat'],
        'export' => ['bg-violet-50 text-violet-700 border-violet-200', 'Export'],
        'print' => ['bg-indigo-50 text-indigo-700 border-indigo-200', 'Cetak'],
        'login' => ['bg-teal-50 text-teal-700 border-teal-200', 'Login'],
        'logout' => ['bg-slate-100 text-slate-700 border-slate-200', 'Logout'],
        'login_failed' => ['bg-red-50 text-red-700 border-red-200', 'Login Gagal'],
        'reset_password' => ['bg-orange-50 text-orange-700 border-orange-200', 'Reset Password'],
        'cancel' => ['bg-rose-50 text-rose-700 border-rose-200', 'Batal'],
        'refund' => ['bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200', 'Refund'],
    ];

    [$classes, $label] = $styles[$action] ?? ['bg-slate-100 text-slate-700 border-slate-200', ucfirst((string) $action)];
@endphp

<span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $classes }}">
    {{ $label }}
</span>
