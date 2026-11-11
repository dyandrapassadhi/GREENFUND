<?php

namespace Database\Seeders;

use App\Models\Donasi;
use App\Models\Kampanye;
use App\Models\LogPerkembangan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GreenFundSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@greenfund.id'],
            [
                'name' => 'Admin Yayasan',
                'username' => 'admin',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // akun super admin terpisah (username login khusus panel)
        User::updateOrCreate(
            ['email' => 'superadmin@greenfund.id'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'role' => 'admin',
                'password' => Hash::make('super123'),
            ]
        );

        $rows = [
            ['Hutan Mangrove Pesisir Demak', 'Mangrove', 'Demak, Jawa Tengah', 10000, 7200, 24],
            ['Restorasi DAS Citarum Hulu', 'Restorasi DAS', 'Bandung, Jawa Barat', 10000, 4500, 41],
            ['Rehabilitasi Gambut Kalimantan', 'Gambut', 'Kapuas, Kalimantan Tengah', 15000, 13200, 18],
            ['Penghijauan Lereng Merapi', 'Penghijauan', 'Sleman, DI Yogyakarta', 8000, 3100, 56],
            ['Reboisasi Hulu Brantas', 'Restorasi DAS', 'Malang, Jawa Timur', 12000, 6800, 33],
            ['Sabuk Hijau Pantai Bali', 'Mangrove', 'Jembrana, Bali', 5000, 2400, 62],
        ];

        foreach ($rows as [$judul, $kategori, $lokasi, $target, $terkumpul, $sisaHari]) {
            Kampanye::create([
                'judul' => $judul,
                'kategori' => $kategori,
                'lokasi' => $lokasi,
                'deskripsi' => "Kampanye ini menanam bibit di {$lokasi} untuk menahan abrasi, memulihkan habitat endemik, dan menambah pendapatan petani bibit lokal.",
                'target_pohon' => $target,
                'harga_per_pohon' => 25000,
                'status' => 'aktif',
                'penyelenggara' => 'Yayasan Bakau Lestari',
                'tenggat' => now()->addDays($sisaHari)->toDateString(),
            ]);
            // sisa target dicatat sebagai donasi terverifikasi agar progres konsisten dengan angka desain
            Donasi::create([
                'kode' => Donasi::kodeBaru(),
                'kampanye_id' => Kampanye::where('judul', $judul)->value('id'),
                'user_id' => $admin->id,
                'nama_donatur' => 'Donasi Kolektif',
                'email' => 'kolektif@greenfund.id',
                'jumlah_pohon' => $terkumpul,
                'subtotal' => $terkumpul * 25000,
                'biaya_admin' => 0,
                'kode_unik' => 0,
                'total' => $terkumpul * 25000,
                'status' => 'terverifikasi',
                'tenggat_bayar' => now(),
            ]);
        }

        $demak = Kampanye::where('judul', 'Hutan Mangrove Pesisir Demak')->value('id');

        foreach ([
            ['2026-11-12', 'baik', 'Penanaman batch #4 — 1.200 bibit', 'Zona C, jarak tanam 3×3 m, curah hujan 120 mm/bulan.'],
            ['2026-10-28', 'perawatan', 'Perawatan & penyulaman 84 bibit', 'Sebagian terserang hama kepiting, tim menambah pelindung bambu.'],
            ['2026-09-04', 'baik', 'Penanaman batch #3 — 1.500 bibit', 'Tinggi rata-rata 42 cm, survival rate 94%.'],
        ] as [$tgl, $kondisi, $judul, $catatan]) {
            LogPerkembangan::create([
                'kampanye_id' => $demak,
                'tanggal' => $tgl,
                'kondisi' => $kondisi,
                'judul' => $judul,
                'catatan' => $catatan,
            ]);
        }

        foreach ([
            ['Rani Nuraini', 'Hutan Mangrove Pesisir Demak', 10, 'pending'],
            ['Siti Pertiwi', 'Restorasi DAS Citarum Hulu', 4, 'terverifikasi'],
            ['Kevin Wijaya', 'Rehabilitasi Gambut Kalimantan', 25, 'terverifikasi'],
            ['Aditya Nugraha', 'Hutan Mangrove Pesisir Demak', 2, 'ditolak'],
            ['Maya Kusuma', 'Restorasi DAS Citarum Hulu', 8, 'terverifikasi'],
        ] as [$nama, $kampanye, $pohon, $status]) {
            $subtotal = $pohon * 25000;
            $unik = random_int(100, 999);
            Donasi::create([
                'kode' => Donasi::kodeBaru(),
                'kampanye_id' => Kampanye::where('judul', $kampanye)->value('id'),
                'nama_donatur' => $nama,
                'email' => strtolower(str_replace(' ', '.', $nama)).'@email.com',
                'jumlah_pohon' => $pohon,
                'subtotal' => $subtotal,
                'biaya_admin' => 5000,
                'kode_unik' => $unik,
                'total' => $subtotal + 5000 + $unik,
                'status' => $status,
                'tenggat_bayar' => now()->addDay(),
            ]);
        }
    }
}
