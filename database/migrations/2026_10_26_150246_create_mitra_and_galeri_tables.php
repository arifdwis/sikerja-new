<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_mitra', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mitra_kerjasama', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('kategori_mitra_id')->constrained('kategori_mitra')->onDelete('cascade');
            $table->string('nama');
            $table->string('logo')->nullable();
            $table->string('url_website')->nullable();
            $table->text('deskripsi')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('galeri_tkksd', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('foto');
            $table->date('tanggal_kegiatan');
            $table->string('kategori_kegiatan')->default('Rapat Koordinasi'); // Rapat Koordinasi, Pembahasan Draf, Penandatanganan, Monev Lapangan
            $table->string('lokasi')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed 4 Kategori Awal sesuai dokumen sikerja.docx
        $now = now();
        $kategoriIds = [
            'pemerintah-daerah' => DB::table('kategori_mitra')->insertGetId([
                'nama' => 'Pemerintah Daerah',
                'slug' => 'pemerintah-daerah',
                'deskripsi' => 'Kerja Sama Antar Daerah (KSDD) dengan Pemerintah Provinsi/Kabupaten/Kota Lain',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'pihak-ketiga' => DB::table('kategori_mitra')->insertGetId([
                'nama' => 'Pihak Ketiga',
                'slug' => 'pihak-ketiga',
                'deskripsi' => 'Kerja Sama Daerah dengan Pihak Ketiga (KSDPK) Badan Usaha, BUMD, dan Swasta',
                'order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'organisasi' => DB::table('kategori_mitra')->insertGetId([
                'nama' => 'Organisasi',
                'slug' => 'organisasi',
                'deskripsi' => 'Kemitraan dengan Perguruan Tinggi, Lembaga, dan Organisasi Kemasyarakatan',
                'order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            'sister-city' => DB::table('kategori_mitra')->insertGetId([
                'nama' => 'Sister City',
                'slug' => 'sister-city',
                'deskripsi' => 'Kerja Sama Kemitraan Antar Kota / Daerah Luar Negeri (Sister City / Twin City)',
                'order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];

        // Seed Contoh Mitra Awal dari lampiran sikerja.docx
        DB::table('mitra_kerjasama')->insert([
            [
                'uuid' => (string) Str::uuid(),
                'kategori_mitra_id' => $kategoriIds['pemerintah-daerah'],
                'nama' => 'Pemerintah Kabupaten Kutai Kartanegara',
                'logo' => 'uploads/mitra/kukar.png',
                'url_website' => 'https://kukarkab.go.id',
                'deskripsi' => 'Kerja sama antar daerah wilayah penyangga perbatasan dan ketahanan pangan.',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'kategori_mitra_id' => $kategoriIds['pihak-ketiga'],
                'nama' => 'Perumda Tirta Kencana Kota Samarinda',
                'logo' => 'uploads/mitra/tirtakencana.png',
                'url_website' => 'https://tirtakencana.com',
                'deskripsi' => 'Optimalisasi pelayanan penyediaan air bersih dan retribusi daerah.',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'kategori_mitra_id' => $kategoriIds['organisasi'],
                'nama' => 'Universitas Mulawarman',
                'logo' => 'uploads/mitra/unmul.png',
                'url_website' => 'https://unmul.ac.id',
                'deskripsi' => 'Penyelenggaraan Tri Dharma Perguruan Tinggi, riset, dan pengabdian masyarakat.',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'kategori_mitra_id' => $kategoriIds['organisasi'],
                'nama' => 'Universitas Sebelas Maret (UNS)',
                'logo' => 'uploads/mitra/uns.png',
                'url_website' => 'https://uns.ac.id',
                'deskripsi' => 'Pengembangan kapasitas aparatur sipil daerah dan kajian akademis kebijakan.',
                'order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'kategori_mitra_id' => $kategoriIds['sister-city'],
                'nama' => 'Sister City Yekaterinburg (Rusia)',
                'logo' => 'uploads/mitra/yekaterinburg.png',
                'url_website' => 'https://samarindakota.go.id',
                'deskripsi' => 'Kemitraan Sister City bidang kebudayaan, perdagangan, dan pariwisata.',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed Contoh Dokumentasi Kegiatan TKKSD Awal
        DB::table('galeri_tkksd')->insert([
            [
                'uuid' => (string) Str::uuid(),
                'judul' => 'Rapat Pleno Pembahasan Draf Kesepakatan Bersama TKKSD',
                'deskripsi' => 'Rapat koordinasi Tim Koordinasi Kerja Sama Daerah Kota Samarinda dalam menelaah pasal-pasal kemitraan strategis daerah.',
                'foto' => 'uploads/galeri_tkksd/kegiatan_1.jpg',
                'tanggal_kegiatan' => '2026-02-10',
                'kategori_kegiatan' => 'Rapat Pembahasan',
                'lokasi' => 'Ruang Rapat Bagian Kerja Sama Setda Kota Samarinda',
                'order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'judul' => 'Penandatanganan Naskah Perjanjian Kerja Sama Bersama Mitra',
                'deskripsi' => 'Prosesi penandatanganan naskah PKS antara Kepala Perangkat Daerah pengampu dan pimpinan lembaga mitra.',
                'foto' => 'uploads/galeri_tkksd/kegiatan_2.jpg',
                'tanggal_kegiatan' => '2026-03-15',
                'kategori_kegiatan' => 'Penandatanganan',
                'lokasi' => 'Balai Kota Samarinda',
                'order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'judul' => 'Monitoring dan Evaluasi Lapangan Pelaksanaan Kerja Sama',
                'deskripsi' => 'Kunjungan lapangan Tim TKKSD bersama OPD pengampu dalam rangka monitoring realisasi program kerja sama semester pertama.',
                'foto' => 'uploads/galeri_tkksd/kegiatan_3.jpg',
                'tanggal_kegiatan' => '2026-04-22',
                'kategori_kegiatan' => 'Monev Lapangan',
                'lokasi' => 'Lokasi Pelaksanaan Program Kerja Sama Kota Samarinda',
                'order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galeri_tkksd');
        Schema::dropIfExists('mitra_kerjasama');
        Schema::dropIfExists('kategori_mitra');
    }
};
