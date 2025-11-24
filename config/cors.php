<?php

return [

    // Pastikan semua route API kamu masuk di sini
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Boleh semua method (GET, POST, PUT, DELETE, dll)
    'allowed_methods' => ['*'],


    // 🔥 DEV MODE (boleh semua origin) – hanya untuk pengembangan
    // 'allowed_origins' => ['*'],

    // 🔐 PRODUKSI – lebih aman: sebutkan satu per satu
    // 'allowed_origins' => [
    //     'http://localhost:5173',      // misal frontend web (Vite)
    //     'http://localhost:19006',     // misal React Native dev server
    //     'capacitor://localhost',      // kalau pakai Capacitor
    //     'ionic://localhost',          // kalau pakai Ionic
    //     'https://website-mu.com',     // domain web
    //     'https://mobile-mu.com',      // kalau nanti pakai domain sendiri
    // ],

    'allowed_origins' => ['*'],

    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Kalau kamu pakai cookie / Sanctum / Auth
    'supports_credentials' => true,
];
