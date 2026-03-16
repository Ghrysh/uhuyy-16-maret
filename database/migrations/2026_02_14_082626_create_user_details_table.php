<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('nip')->nullable();
            $table->string('nip_baru')->nullable();
            $table->string('nama')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('agama')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->timestamp('tanggal_lahir')->nullable();
            $table->integer('jenis_kelamin')->nullable();

            $table->string('pendidikan')->nullable();
            $table->string('jenjang_pendidikan')->nullable();

            $table->string('kode_level_jabatan')->nullable();
            $table->string('level_jabatan')->nullable();
            $table->string('pangkat')->nullable();
            $table->string('gol_ruang')->nullable();

            $table->date('tmt_cpns')->nullable();
            $table->date('tmt_pangkat')->nullable();

            $table->integer('mk_tahun')->nullable();
            $table->integer('mk_bulan')->nullable();
            $table->bigInteger('gaji_pokok')->nullable();

            $table->string('tipe_jabatan')->nullable();
            $table->string('kode_jabatan')->nullable();
            $table->string('tampil_jabatan')->nullable();
            $table->timestamp('tmt_jabatan')->nullable();

            $table->string('kode_satuan_kerja')->nullable();
            $table->string('satker_1')->nullable();
            $table->string('satker_2')->nullable();
            $table->string('kode_satker_2')->nullable();
            $table->string('satker_3')->nullable();
            $table->string('kode_satker_3')->nullable();
            $table->string('satker_4')->nullable();
            $table->string('kode_satker_4')->nullable();
            $table->string('satker_5')->nullable();
            $table->string('kode_satker_5')->nullable();

            $table->string('kode_grup_satuan_kerja')->nullable();
            $table->string('grup_satuan_kerja')->nullable();
            $table->text('keterangan_satuan_kerja')->nullable();

            $table->string('status_kawin')->nullable();
            $table->string('alamat_1')->nullable();
            $table->string('alamat_2')->nullable();
            $table->string('telepon')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();

            $table->string('kab_kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('kode_lokasi')->nullable();
            $table->string('iso')->nullable();

            $table->string('kode_pangkat')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamp('tmt_pangkat_yad')->nullable();
            $table->timestamp('tmt_kgb_yad')->nullable();

            $table->integer('usia_pensiun')->nullable();
            $table->timestamp('tmt_pensiun')->nullable();

            $table->integer('mk_tahun_1')->nullable();
            $table->integer('mk_bulan_1')->nullable();

            $table->string('nsm')->nullable();
            $table->string('npsn')->nullable();
            $table->string('kode_kua')->nullable();
            $table->string('kode_bidang_studi')->nullable();
            $table->string('bidang_studi')->nullable();

            $table->string('status_pegawai')->nullable();
            $table->string('lat')->nullable();
            $table->string('lon')->nullable();

            $table->string('satker_kelola')->nullable();
            $table->integer('hari_kerja')->nullable();
            $table->string('email_dinas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
