<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

/*
 * Test berjalan di database MySQL yang sama dengan .env (lihat phpunit.xml).
 *
 * Sengaja memakai DatabaseTransactions, bukan RefreshDatabase:
 * RefreshDatabase menjalankan migrate:fresh sehingga seluruh data klinik
 * di database development akan terhapus. DatabaseTransactions hanya
 * membungkus setiap test dalam transaksi dan me-rollback perubahan setelah
 * selesai, sehingga data asli tetap aman.
 */
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\DatabaseTransactions::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Helper khusus test audit
|--------------------------------------------------------------------------
*/

/** Membuat user klinik lengkap dengan role tertentu (tanpa factory). */
function userKlinik(string $role, array $overrides = []): App\Models\User
{
    $unique = Illuminate\Support\Str::lower(Illuminate\Support\Str::random(8));

    return App\Models\User::create(array_merge([
        'username' => 'test_'.Illuminate\Support\Str::slug($role).'_'.$unique,
        'email' => 'test_'.$unique.'@example.test',
        // Password plain: cast 'hashed' pada model User yang melakukan hashing.
        'password' => 'Password123!',
        'role' => $role,
    ], $overrides));
}

/** Membuat data pasien minimal untuk keperluan test. */
function pasienKlinik(App\Models\User $user, array $overrides = []): App\Models\Pasien
{
    return App\Models\Pasien::create(array_merge([
        'user_id' => $user->id,
        'no_emr' => 'RM-TEST-'.Illuminate\Support\Str::upper(Illuminate\Support\Str::random(6)),
        'nama_pasien' => 'Pasien Uji '.Illuminate\Support\Str::random(4),
    ], $overrides));
}
