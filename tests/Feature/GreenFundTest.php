<?php

namespace Tests\Feature;

use App\Models\Donasi;
use App\Models\Kampanye;
use App\Models\User;
use App\Services\Qris;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreenFundTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function kampanye(array $over = []): Kampanye
    {
        return Kampanye::create(array_merge([
            'judul' => 'Uji Mangrove',
            'slug' => 'uji-mangrove',
            'kategori' => 'Mangrove',
            'lokasi' => 'Demak, Jawa Tengah',
            'target_pohon' => 1000,
            'harga_per_pohon' => 25000,
            'status' => 'aktif',
        ], $over));
    }

    public function test_halaman_publik_terbuka_tanpa_login(): void
    {
        $this->kampanye();

        foreach (['/', '/kampanye', '/tentang-sdg', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_detail_kampanye_dan_invoice_render(): void
    {
        $k = $this->kampanye();
        $this->get('/kampanye/uji-mangrove')->assertOk()->assertSee('Uji Mangrove');

        $donasi = Donasi::create([
            'kode' => 'INV-2026-09001', 'kampanye_id' => $k->id, 'nama_donatur' => 'Rani',
            'email' => 'rani@email.com', 'jumlah_pohon' => 2, 'subtotal' => 50000,
            'biaya_admin' => 5000, 'kode_unik' => 123, 'total' => 55123, 'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'donatur']))
            ->get('/donasi/'.$donasi->kode)->assertOk()->assertSee('Rp 55.123');
    }

    public function test_admin_wajib_login(): void
    {
        foreach (['/admin', '/admin/kampanye', '/admin/log', '/admin/donasi', '/admin/donatur'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_donatur_tidak_bisa_masuk_halaman_admin(): void
    {
        $donatur = User::factory()->create(['role' => 'donatur']);

        foreach (['/admin', '/admin/kampanye', '/admin/log', '/admin/donasi', '/admin/donatur'] as $url) {
            $this->actingAs($donatur)->get($url)->assertForbidden();
        }

        // aksi tulis juga tertutup
        $k = $this->kampanye();
        $this->actingAs($donatur)->post('/admin/kampanye', [
            'judul' => 'Kampanye Gelap', 'kategori' => 'Gambut', 'lokasi' => 'X',
            'target_pohon' => 10, 'harga_per_pohon' => 25000, 'status' => 'aktif',
        ])->assertForbidden();
        $this->assertDatabaseMissing('kampanye', ['judul' => 'Kampanye Gelap']);

        // link Panel Admin tidak dirender untuk donatur
        $this->actingAs($donatur)->get('/')->assertOk()->assertDontSee('Panel Admin');
    }

    public function test_akun_admin_khusus_bisa_masuk_panel(): void
    {
        $this->seed(\Database\Seeders\GreenFundSeeder::class);

        foreach (['admin' => 'admin123', 'superadmin' => 'super123'] as $username => $password) {
            $this->post('/logout');
            $this->post('/login', ['login' => $username, 'password' => $password])
                ->assertRedirect(route('admin.dashboard'));
            $this->assertSame($username, auth()->user()->username);
            $this->get('/admin')->assertOk();
            $this->get('/')->assertSee('Panel Admin');
        }
    }

    public function test_login_seeder_lalu_semua_halaman_admin_terbuka(): void
    {
        $this->seed(\Database\Seeders\GreenFundSeeder::class);
        $admin = User::where('email', 'admin@greenfund.id')->firstOrFail();

        $this->actingAs($admin);
        foreach (['/admin', '/admin/kampanye', '/admin/log', '/admin/donasi', '/admin/donatur'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_admin_crud_kampanye(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/kampanye', [
            'judul' => 'Kampanye Baru',
            'kategori' => 'Gambut',
            'lokasi' => 'Kapuas',
            'target_pohon' => 500,
            'harga_per_pohon' => 20000,
            'status' => 'aktif',
            'deskripsi' => 'Deskripsi singkat.',
        ])->assertRedirect();
        $this->assertDatabaseHas('kampanye', ['judul' => 'Kampanye Baru']);

        $k = Kampanye::where('judul', 'Kampanye Baru')->firstOrFail();
        $this->put("/admin/kampanye/{$k->id}", [
            'judul' => 'Kampanye Diubah',
            'kategori' => 'Gambut',
            'lokasi' => 'Kapuas',
            'target_pohon' => 800,
            'harga_per_pohon' => 20000,
            'status' => 'selesai',
        ])->assertRedirect();
        $this->assertDatabaseHas('kampanye', ['id' => $k->id, 'judul' => 'Kampanye Diubah', 'status' => 'selesai']);

        $this->delete("/admin/kampanye/{$k->id}")->assertRedirect();
        $this->assertDatabaseMissing('kampanye', ['id' => $k->id]);
    }

    public function test_validasi_kampanye_menolak_input_kosong(): void
    {
        $this->actingAs($this->admin());

        $this->from('/admin/kampanye')
            ->post('/admin/kampanye', ['judul' => '', 'target_pohon' => 0])
            ->assertSessionHasErrors(['judul', 'kategori', 'lokasi', 'target_pohon', 'harga_per_pohon', 'status']);
    }

    public function test_verifikasi_donasi_mengubah_status(): void
    {
        $this->actingAs($this->admin());
        $k = $this->kampanye();
        $d = Donasi::create([
            'kode' => 'INV-2026-09002', 'kampanye_id' => $k->id, 'nama_donatur' => 'Siti',
            'email' => 'siti@email.com', 'jumlah_pohon' => 3, 'subtotal' => 75000,
            'biaya_admin' => 5000, 'kode_unik' => 321, 'total' => 80321, 'status' => 'pending',
        ]);

        $this->patch("/admin/donasi/{$d->id}/status", ['status' => 'terverifikasi'])->assertRedirect();
        $this->assertDatabaseHas('donasi', ['id' => $d->id, 'status' => 'terverifikasi']);
    }

    public function test_simpan_log_muncul_di_halaman_publik(): void
    {
        $this->actingAs($this->admin());
        $k = $this->kampanye();

        $this->post('/admin/log', [
            'kampanye_id' => $k->id,
            'tanggal' => now()->toDateString(),
            'kondisi' => 'baik',
            'judul' => 'Penanaman batch #1',
            'catatan' => 'Tinggi rata-rata 40 cm.',
        ])->assertRedirect();

        $this->assertDatabaseHas('log_perkembangan', ['kampanye_id' => $k->id, 'judul' => 'Penanaman batch #1']);
        $this->get('/kampanye/uji-mangrove')->assertOk()->assertSee('Penanaman batch #1');
    }

    public function test_donasi_wajib_login(): void
    {
        $k = $this->kampanye();

        // endpoint donasi menolak guest
        $this->post('/donasi', ['kampanye_id' => $k->id, 'jumlah_pohon' => 4])
            ->assertRedirect('/login');
        $this->get('/donasi/INV-2026-09001')->assertRedirect('/login');

        // halaman detail tetap publik, tapi menampilkan ajakan login
        $this->get('/kampanye/uji-mangrove')->assertOk()
            ->assertSee('Masuk untuk Donasi')
            ->assertSee('Masuk dulu untuk melanjutkan donasi');

        $this->assertDatabaseCount('donasi', 0);
    }

    public function test_donasi_login_memakai_identitas_akun(): void
    {
        $k = $this->kampanye();
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@email.com',
            'role' => 'donatur',
        ]);

        $res = $this->actingAs($user)->post('/donasi', [
            'kampanye_id' => $k->id,
            'jumlah_pohon' => 4,
        ]);

        $d = Donasi::firstOrFail();
        $res->assertRedirect('/donasi/'.$d->kode);
        $this->assertSame($user->id, $d->user_id);
        $this->assertSame('Budi Santoso', $d->nama_donatur);
        $this->assertSame('budi@email.com', $d->email);
        $this->assertSame(4 * 25000 + 5000 + $d->kode_unik, $d->total);
        $this->assertSame('pending', $d->status);
    }

    public function test_login_admin_pakai_username_dan_masuk_panel(): void
    {
        $this->seed(\Database\Seeders\GreenFundSeeder::class);

        $this->post('/login', ['login' => 'admin', 'password' => 'admin123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()->username);
    }

    public function test_login_donatur_pakai_email_tidak_masuk_panel(): void
    {
        $user = User::factory()->create([
            'name' => 'Donatur Biasa',
            'email' => 'donatur@email.com',
            'role' => 'donatur',
            'password' => bcrypt('rahasia123'),
        ]);

        $this->post('/login', ['login' => 'donatur@email.com', 'password' => 'rahasia123'])
            ->assertRedirect(route('katalog'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_salah_menampilkan_error(): void
    {
        $this->seed(\Database\Seeders\GreenFundSeeder::class);

        $this->from('/login')
            ->post('/login', ['login' => 'admin', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_register_membuat_akun_donatur(): void
    {
        $this->post('/register', [
            'name' => 'Donatur Baru',
            'email' => 'baru@email.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/kampanye');

        $this->assertDatabaseHas('users', ['email' => 'baru@email.com', 'role' => 'donatur']);
    }

    public function test_qris_statis_diubah_jadi_dinamis_dengan_nominal(): void
    {
        $t = fn (string $tag, string $val) => $tag.str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT).$val;
        $base = $t('00', '01').$t('01', '11').$t('52', '5411').$t('53', '360')
            .$t('58', 'ID').$t('59', 'YAYASAN BAKAU LESTARI').$t('60', 'DEMAK');
        $static = $base.'6304'.Qris::crc16($base.'6304');

        config(['services.qris.static' => $static]);

        $dyn = Qris::dynamic($static, 255572);
        $tags = collect(Qris::parse($dyn))->keyBy(0);

        $this->assertSame('12', $tags['01'][1], 'tag 01 harus jadi dynamic');
        $this->assertSame('255572', $tags['54'][1], 'tag 54 harus berisi nominal');
        $this->assertSame(substr($dyn, -4), Qris::crc16(substr($dyn, 0, -4)), 'CRC harus valid');

        config(['services.qris.static' => '']);
        $this->assertNull(Qris::forAmount(1000), 'template kosong harus null');
    }

    public function test_endpoint_qris_png(): void
    {
        $t = fn (string $tag, string $val) => $tag.str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT).$val;
        $base = $t('00', '01').$t('01', '11').$t('52', '5411').$t('53', '360').$t('58', 'ID').$t('59', 'TEST').$t('60', 'DEMAK');
        config(['services.qris.static' => $base.'6304'.Qris::crc16($base.'6304')]);

        $k = $this->kampanye();
        $d = Donasi::create([
            'kode' => 'INV-2026-09003', 'kampanye_id' => $k->id, 'nama_donatur' => 'Budi',
            'email' => 'budi@email.com', 'jumlah_pohon' => 2, 'subtotal' => 50000,
            'biaya_admin' => 5000, 'kode_unik' => 111, 'total' => 55111, 'status' => 'pending',
        ]);

        $res = $this->actingAs(User::factory()->create())->get('/donasi/'.$d->kode.'/qris.png');
        $res->assertOk();
        $this->assertSame('image/png', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith("\x89PNG", $res->getContent());

        config(['services.qris.static' => '']);
        $this->get('/donasi/'.$d->kode.'/qris.png')->assertNotFound();
    }

    public function test_google_login_tersembunyi_tanpa_kredensial(): void
    {
        config(['services.google.client_id' => '', 'services.google.client_secret' => '']);

        // tombol tetap tampil (sesuai desain), tapi diberi catatan belum aktif
        $this->get('/login')->assertOk()
            ->assertSee('Masuk dengan Google')
            ->assertSee('Google login belum aktif');
        $this->get('/auth/google')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
    }

    public function test_google_login_mengarahkan_ke_google(): void
    {
        config([
            'services.google.client_id' => 'dummy-client-id',
            'services.google.client_secret' => 'dummy-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $this->get('/login')->assertOk()->assertSee('Masuk dengan Google');

        $res = $this->get('/auth/google');
        $res->assertRedirectContains('accounts.google.com');
        $this->assertStringContainsString('dummy-client-id', $res->headers->get('Location'));
    }

    public function test_google_callback_membuat_akun_donatur(): void
    {
        config([
            'services.google.client_id' => 'dummy-client-id',
            'services.google.client_secret' => 'dummy-secret',
        ]);

        $googleUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getEmail')->andReturn('googleuser@gmail.com');
        $googleUser->shouldReceive('getName')->andReturn('Google User');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/a.png');

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        $this->get('/auth/google/callback')->assertRedirect('/kampanye');

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@gmail.com',
            'role' => 'donatur',
            'google_id' => 'google-123',
        ]);
        $this->assertAuthenticated();
    }

    public function test_google_callback_tidak_menimpa_role_admin(): void
    {
        config([
            'services.google.client_id' => 'dummy-client-id',
            'services.google.client_secret' => 'dummy-secret',
        ]);

        $admin = User::factory()->create(['email' => 'admin@greenfund.id', 'role' => 'admin']);

        $googleUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $googleUser->shouldReceive('getId')->andReturn('google-999');
        $googleUser->shouldReceive('getEmail')->andReturn('admin@greenfund.id');
        $googleUser->shouldReceive('getName')->andReturn('Admin Yayasan');
        $googleUser->shouldReceive('getAvatar')->andReturn(null);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        $this->get('/auth/google/callback')->assertRedirect('/kampanye');

        $this->assertSame('admin', $admin->fresh()->role, 'role admin tidak boleh turun jadi donatur');
        $this->assertSame('google-999', $admin->fresh()->google_id);
    }

    public function test_google_callback_menangani_penolakan(): void
    {
        config(['services.google.client_id' => 'dummy', 'services.google.client_secret' => 'dummy']);

        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    // ---------- hapus data dari panel admin ----------

    public function test_admin_hapus_donasi(): void
    {
        $this->actingAs($this->admin());
        $k = $this->kampanye();
        $d = Donasi::create([
            'kode' => 'INV-2026-09901', 'kampanye_id' => $k->id, 'nama_donatur' => 'Hapus Saya',
            'email' => 'hapus@email.com', 'jumlah_pohon' => 2, 'subtotal' => 50000,
            'biaya_admin' => 5000, 'kode_unik' => 111, 'total' => 55111, 'status' => 'pending',
        ]);

        $this->delete("/admin/donasi/{$d->id}")->assertRedirect();
        $this->assertDatabaseMissing('donasi', ['id' => $d->id]);
        $this->assertDatabaseHas('kampanye', ['id' => $k->id]);
    }

    public function test_admin_hapus_donatur_beserta_donasinya(): void
    {
        $this->actingAs($this->admin());
        $k = $this->kampanye();
        $user = User::factory()->create(['email' => 'target@email.com', 'role' => 'donatur']);

        Donasi::create([
            'kode' => 'INV-2026-09902', 'kampanye_id' => $k->id, 'user_id' => $user->id,
            'nama_donatur' => 'Target', 'email' => 'target@email.com', 'jumlah_pohon' => 1,
            'subtotal' => 25000, 'biaya_admin' => 5000, 'kode_unik' => 222, 'total' => 30222, 'status' => 'pending',
        ]);
        // donasi milik orang lain tidak boleh ikut terhapus
        $lain = Donasi::create([
            'kode' => 'INV-2026-09903', 'kampanye_id' => $k->id, 'nama_donatur' => 'Lain',
            'email' => 'lain@email.com', 'jumlah_pohon' => 1, 'subtotal' => 25000,
            'biaya_admin' => 5000, 'kode_unik' => 333, 'total' => 30333, 'status' => 'pending',
        ]);

        $this->delete('/admin/donatur/'.urlencode('target@email.com'), ['konfirmasi' => 'HAPUS'])
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['email' => 'target@email.com']);
        $this->assertDatabaseMissing('donasi', ['email' => 'target@email.com']);
        $this->assertDatabaseHas('donasi', ['id' => $lain->id]);
    }

    public function test_hapus_donatur_menolak_tanpa_konfirmasi(): void
    {
        $this->actingAs($this->admin());

        $this->delete('/admin/donatur/'.urlencode('x@email.com'), ['konfirmasi' => 'salah'])
            ->assertSessionHasErrors('konfirmasi');
    }

    public function test_donatur_tidak_bisa_menghapus_data(): void
    {
        $donatur = User::factory()->create(['role' => 'donatur']);
        $k = $this->kampanye();
        $d = Donasi::create([
            'kode' => 'INV-2026-09904', 'kampanye_id' => $k->id, 'nama_donatur' => 'Korban',
            'email' => 'korban@email.com', 'jumlah_pohon' => 1, 'subtotal' => 25000,
            'biaya_admin' => 5000, 'kode_unik' => 444, 'total' => 30444, 'status' => 'pending',
        ]);

        $this->actingAs($donatur)->delete("/admin/donasi/{$d->id}")->assertForbidden();
        $this->assertDatabaseHas('donasi', ['id' => $d->id]);

        $this->actingAs($donatur)->delete('/admin/donatur/'.urlencode('korban@email.com'), ['konfirmasi' => 'HAPUS'])
            ->assertForbidden();
        $this->assertDatabaseHas('donasi', ['id' => $d->id]);
    }

    // ---------- DOKU payment gateway ----------

    public function test_doku_signature_dan_digest_sesuai_spesifikasi(): void
    {
        $body = json_encode(['order' => ['amount' => 20000]], JSON_UNESCAPED_SLASHES);

        $this->assertSame(
            base64_encode(hash('sha256', $body, true)),
            \App\Services\Doku::digest($body)
        );

        $sig = \App\Services\Doku::signature('CID', 'RID', '2020-08-11T08:45:42Z', '/checkout/v1/payment', 'DGT', 'SK');
        $want = 'HMACSHA256='.base64_encode(hash_hmac('sha256',
            "Client-Id:CID\nRequest-Id:RID\nRequest-Timestamp:2020-08-11T08:45:42Z\nRequest-Target:/checkout/v1/payment\nDigest:DGT", 'SK', true));

        $this->assertSame($want, $sig);

        // tanpa digest (GET) = 4 komponen
        $get = \App\Services\Doku::signature('CID', 'RID', '2020-08-11T08:45:42Z', '/status', null, 'SK');
        $this->assertStringNotContainsString('Digest:', base64_decode(substr($get, 11)));
    }

    public function test_tombol_doku_muncul_di_invoice_bila_terkonfigurasi(): void
    {
        $k = $this->kampanye();
        $user = User::factory()->create(['role' => 'donatur']);
        $d = Donasi::create([
            'kode' => 'INV-2026-09701', 'kampanye_id' => $k->id, 'user_id' => $user->id,
            'nama_donatur' => 'Doku User', 'email' => 'doku@email.com', 'jumlah_pohon' => 2,
            'subtotal' => 50000, 'biaya_admin' => 5000, 'kode_unik' => 111, 'total' => 55111, 'status' => 'pending',
        ]);

        config(['services.doku.client_id' => '', 'services.doku.secret_key' => '']);
        $this->actingAs($user)->get('/donasi/'.$d->kode)->assertOk()->assertDontSee('Bayar via DOKU');

        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'SK-TEST']);
        $this->actingAs($user)->get('/donasi/'.$d->kode)->assertOk()->assertSee('Bayar via DOKU');
    }

    public function test_webhook_doku_menolak_signature_palsu(): void
    {
        $this->postJson('/doku/notify', ['order' => ['invoice_number' => 'INV-X']])
            ->assertStatus(401);
    }

    public function test_webhook_doku_melunasi_donasi(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'SK-TEST']);

        $k = $this->kampanye();
        $user = User::factory()->create(['role' => 'donatur']);
        $d = Donasi::create([
            'kode' => 'INV-2026-09702', 'kampanye_id' => $k->id, 'user_id' => $user->id,
            'nama_donatur' => 'Lunas', 'email' => 'lunas@email.com', 'jumlah_pohon' => 1,
            'subtotal' => 25000, 'biaya_admin' => 5000, 'kode_unik' => 222, 'total' => 30222, 'status' => 'pending',
        ]);

        $payload = ['order' => ['invoice_number' => $d->kode], 'transaction' => ['status' => 'SUCCESS']];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $reqId = 'req-1';
        $ts = '2026-11-16T01:00:00Z';

        $res = $this->call('POST', '/doku/notify', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_CLIENT_ID' => 'MCH-TEST',
            'HTTP_REQUEST_ID' => $reqId,
            'HTTP_REQUEST_TIMESTAMP' => $ts,
            'HTTP_SIGNATURE' => \App\Services\Doku::signature('MCH-TEST', $reqId, $ts, '/doku/notify', \App\Services\Doku::digest($body), 'SK-TEST'),
        ], $body);

        $res->assertOk();
        $this->assertSame('terverifikasi', $d->fresh()->status);
        $this->assertNotNull($d->fresh()->paid_at);
    }

    public function test_donasi_lunas_tidak_buat_transaksi_doku_baru(): void
    {
        config(['services.doku.client_id' => 'MCH-TEST', 'services.doku.secret_key' => 'SK-TEST']);

        $k = $this->kampanye();
        $user = User::factory()->create(['role' => 'donatur']);
        $d = Donasi::create([
            'kode' => 'INV-2026-09703', 'kampanye_id' => $k->id, 'user_id' => $user->id,
            'nama_donatur' => 'Sudah Bayar', 'email' => 'sudah@email.com', 'jumlah_pohon' => 1,
            'subtotal' => 25000, 'biaya_admin' => 5000, 'kode_unik' => 333, 'total' => 30333, 'status' => 'terverifikasi',
        ]);

        $this->actingAs($user)->post('/donasi/'.$d->kode.'/doku')
            ->assertRedirect('/donasi/'.$d->kode)
            ->assertSessionHas('ok');
    }
}
