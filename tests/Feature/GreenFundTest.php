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

        $this->get('/donasi/'.$donasi->kode)->assertOk()->assertSee('Rp 55.123');
    }

    public function test_admin_wajib_login(): void
    {
        foreach (['/admin', '/admin/kampanye', '/admin/log', '/admin/donasi', '/admin/donatur'] as $url) {
            $this->get($url)->assertRedirect('/login');
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

    public function test_donasi_publik_membuat_invoice(): void
    {
        $k = $this->kampanye();

        $res = $this->post('/donasi', [
            'kampanye_id' => $k->id,
            'nama_donatur' => 'Budi',
            'email' => 'budi@email.com',
            'jumlah_pohon' => 4,
        ]);

        $d = Donasi::firstOrFail();
        $res->assertRedirect('/donasi/'.$d->kode);
        $this->assertSame(4 * 25000 + 5000 + $d->kode_unik, $d->total);
        $this->assertSame('pending', $d->status);
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

        $res = $this->get('/donasi/'.$d->kode.'/qris.png');
        $res->assertOk();
        $this->assertSame('image/png', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith("\x89PNG", $res->getContent());

        config(['services.qris.static' => '']);
        $this->get('/donasi/'.$d->kode.'/qris.png')->assertNotFound();
    }
}
