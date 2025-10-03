import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


/**
 * Konfigurasi CSRF Token
 * Laravel akan memblokir POST/PUT/DELETE tanpa token ini.
 */
let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    // Pesan peringatan jika token tidak ditemukan (biasanya hilang dari layout Blade)
    console.error('CSRF token not found: Pastikan <meta name="csrf-token" content="{{ csrf_token() }}"> ada di layout.');
}