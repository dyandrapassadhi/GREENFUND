<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kampanye', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->enum('kategori', ['Penghijauan', 'Mangrove', 'Restorasi DAS', 'Gambut'])->default('Penghijauan');
            $table->string('lokasi');
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('target_pohon');
            $table->unsignedInteger('harga_per_pohon')->default(25000);
            $table->enum('status', ['aktif', 'terpenuhi', 'selesai', 'dibatalkan'])->default('aktif');
            $table->string('cover')->nullable();
            $table->string('penyelenggara')->default('Yayasan Bakau Lestari');
            $table->date('tenggat')->nullable();
            $table->timestamps();
        });

        Schema::create('log_perkembangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kampanye_id')->constrained('kampanye')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('kondisi', ['baik', 'perawatan'])->default('baik');
            $table->string('judul');
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        Schema::create('donasi', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('kampanye_id')->constrained('kampanye')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_donatur');
            $table->string('email');
            $table->unsignedInteger('jumlah_pohon');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('biaya_admin')->default(5000);
            $table->unsignedInteger('kode_unik')->default(0);
            $table->unsignedInteger('total');
            $table->enum('status', ['pending', 'terverifikasi', 'ditolak'])->default('pending');
            $table->string('bukti')->nullable();
            $table->dateTime('tenggat_bayar')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('donatur')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
        Schema::dropIfExists('donasi');
        Schema::dropIfExists('log_perkembangan');
        Schema::dropIfExists('kampanye');
    }
};
