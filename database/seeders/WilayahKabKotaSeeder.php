<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WilayahKabKotaSeeder extends Seeder
{
    public function run(): void
    {
        $kabkota = [

            // ===================== SUMATERA =====================

            // ACEH (11)
            ['1101','Kabupaten Simeulue'], ['1102','Kabupaten Aceh Singkil'],
            ['1103','Kabupaten Aceh Selatan'], ['1104','Kabupaten Aceh Tenggara'],
            ['1105','Kabupaten Aceh Timur'], ['1106','Kabupaten Aceh Tengah'],
            ['1107','Kabupaten Aceh Barat'], ['1108','Kabupaten Aceh Besar'],
            ['1109','Kabupaten Pidie'], ['1110','Kabupaten Bireuen'],
            ['1111','Kabupaten Aceh Utara'], ['1112','Kabupaten Aceh Barat Daya'],
            ['1113','Kabupaten Gayo Lues'], ['1114','Kabupaten Aceh Tamiang'],
            ['1115','Kabupaten Nagan Raya'], ['1116','Kabupaten Aceh Jaya'],
            ['1117','Kabupaten Bener Meriah'], ['1118','Kabupaten Pidie Jaya'],
            ['1171','Kota Banda Aceh'], ['1172','Kota Sabang'],
            ['1173','Kota Langsa'], ['1174','Kota Lhokseumawe'],
            ['1175','Kota Subulussalam'],

            // SUMATERA UTARA (12)
            ['1201','Kabupaten Nias'], ['1202','Kabupaten Mandailing Natal'],
            ['1203','Kabupaten Tapanuli Selatan'], ['1204','Kabupaten Tapanuli Tengah'],
            ['1205','Kabupaten Tapanuli Utara'], ['1206','Kabupaten Toba'],
            ['1207','Kabupaten Labuhanbatu'], ['1208','Kabupaten Asahan'],
            ['1209','Kabupaten Simalungun'], ['1210','Kabupaten Dairi'],
            ['1211','Kabupaten Karo'], ['1212','Kabupaten Deli Serdang'],
            ['1213','Kabupaten Langkat'], ['1214','Kabupaten Nias Selatan'],
            ['1215','Kabupaten Humbang Hasundutan'], ['1216','Kabupaten Pakpak Bharat'],
            ['1217','Kabupaten Samosir'], ['1218','Kabupaten Serdang Bedagai'],
            ['1219','Kabupaten Batu Bara'], ['1220','Kabupaten Padang Lawas Utara'],
            ['1221','Kabupaten Padang Lawas'], ['1222','Kabupaten Labuhanbatu Selatan'],
            ['1223','Kabupaten Labuhanbatu Utara'], ['1224','Kabupaten Nias Utara'],
            ['1225','Kabupaten Nias Barat'],
            ['1271','Kota Medan'], ['1272','Kota Pematangsiantar'],
            ['1273','Kota Sibolga'], ['1274','Kota Tanjung Balai'],
            ['1275','Kota Binjai'], ['1276','Kota Tebing Tinggi'],
            ['1277','Kota Padangsidimpuan'], ['1278','Kota Gunungsitoli'],

            // DKI JAKARTA (31)
            ['3101','Kabupaten Kepulauan Seribu'],
            ['3171','Kota Jakarta Selatan'],
            ['3172','Kota Jakarta Timur'],
            ['3173','Kota Jakarta Pusat'],
            ['3174','Kota Jakarta Barat'],
            ['3175','Kota Jakarta Utara'],

            // JAWA TENGAH (33)
            ['3301','Kabupaten Cilacap'], ['3302','Kabupaten Banyumas'],
            ['3303','Kabupaten Purbalingga'], ['3304','Kabupaten Banjarnegara'],
            ['3305','Kabupaten Kebumen'], ['3306','Kabupaten Purworejo'],
            ['3307','Kabupaten Wonosobo'], ['3308','Kabupaten Magelang'],
            ['3309','Kabupaten Boyolali'], ['3310','Kabupaten Klaten'],
            ['3311','Kabupaten Sukoharjo'], ['3312','Kabupaten Wonogiri'],
            ['3313','Kabupaten Karanganyar'], ['3314','Kabupaten Sragen'],
            ['3315','Kabupaten Grobogan'], ['3316','Kabupaten Blora'],
            ['3317','Kabupaten Rembang'], ['3318','Kabupaten Pati'],
            ['3319','Kabupaten Kudus'], ['3320','Kabupaten Jepara'],
            ['3321','Kabupaten Demak'], ['3322','Kabupaten Semarang'],
            ['3323','Kabupaten Temanggung'], ['3324','Kabupaten Kendal'],
            ['3325','Kabupaten Batang'], ['3326','Kabupaten Pekalongan'],
            ['3327','Kabupaten Pemalang'], ['3328','Kabupaten Tegal'],
            ['3329','Kabupaten Brebes'],
            ['3371','Kota Magelang'], ['3372','Kota Surakarta'],
            ['3373','Kota Salatiga'], ['3374','Kota Semarang'],
            ['3375','Kota Pekalongan'], ['3376','Kota Tegal'],

            // DI YOGYAKARTA (34)
            ['3401','Kabupaten Kulon Progo'],
            ['3402','Kabupaten Bantul'],
            ['3403','Kabupaten Gunungkidul'],
            ['3404','Kabupaten Sleman'],
            ['3471','Kota Yogyakarta'],

            // JAWA TIMUR (35)
            ['3501','Kabupaten Pacitan'], ['3502','Kabupaten Ponorogo'],
            ['3503','Kabupaten Trenggalek'], ['3504','Kabupaten Tulungagung'],
            ['3505','Kabupaten Blitar'], ['3506','Kabupaten Kediri'],
            ['3507','Kabupaten Malang'], ['3508','Kabupaten Lumajang'],
            ['3509','Kabupaten Jember'], ['3510','Kabupaten Banyuwangi'],
            ['3511','Kabupaten Bondowoso'], ['3512','Kabupaten Situbondo'],
            ['3513','Kabupaten Probolinggo'], ['3514','Kabupaten Pasuruan'],
            ['3515','Kabupaten Sidoarjo'], ['3516','Kabupaten Mojokerto'],
            ['3517','Kabupaten Jombang'], ['3518','Kabupaten Nganjuk'],
            ['3519','Kabupaten Madiun'], ['3520','Kabupaten Magetan'],
            ['3521','Kabupaten Ngawi'], ['3522','Kabupaten Bojonegoro'],
            ['3523','Kabupaten Tuban'], ['3524','Kabupaten Lamongan'],
            ['3525','Kabupaten Gresik'], ['3526','Kabupaten Bangkalan'],
            ['3527','Kabupaten Sampang'], ['3528','Kabupaten Pamekasan'],
            ['3529','Kabupaten Sumenep'],
            ['3571','Kota Kediri'], ['3572','Kota Blitar'],
            ['3573','Kota Malang'], ['3574','Kota Probolinggo'],
            ['3575','Kota Pasuruan'], ['3576','Kota Mojokerto'],
            ['3577','Kota Madiun'], ['3578','Kota Surabaya'],
            ['3579','Kota Batu'],

            // ===================== BALI =====================
            ['5101','Kabupaten Jembrana'], ['5102','Kabupaten Tabanan'],
            ['5103','Kabupaten Badung'], ['5104','Kabupaten Gianyar'],
            ['5105','Kabupaten Klungkung'], ['5106','Kabupaten Bangli'],
            ['5107','Kabupaten Karangasem'], ['5108','Kabupaten Buleleng'],
            ['5171','Kota Denpasar'],

            // ===================== NTB =====================
            ['5201','Kabupaten Lombok Barat'], ['5202','Kabupaten Lombok Tengah'],
            ['5203','Kabupaten Lombok Timur'], ['5204','Kabupaten Sumbawa'],
            ['5205','Kabupaten Dompu'], ['5206','Kabupaten Bima'],
            ['5207','Kabupaten Sumbawa Barat'], ['5208','Kabupaten Lombok Utara'],
            ['5271','Kota Mataram'], ['5272','Kota Bima'],

            // ===================== NTT =====================
            ['5301','Kabupaten Kupang'], ['5302','Kabupaten Timor Tengah Selatan'],
            ['5303','Kabupaten Timor Tengah Utara'], ['5304','Kabupaten Belu'],
            ['5305','Kabupaten Alor'], ['5306','Kabupaten Flores Timur'],
            ['5307','Kabupaten Sikka'], ['5308','Kabupaten Ende'],
            ['5309','Kabupaten Ngada'], ['5310','Kabupaten Manggarai'],
            ['5311','Kabupaten Sumba Timur'], ['5312','Kabupaten Sumba Barat'],
            ['5313','Kabupaten Lembata'], ['5314','Kabupaten Rote Ndao'],
            ['5315','Kabupaten Manggarai Barat'], ['5316','Kabupaten Nagekeo'],
            ['5317','Kabupaten Sumba Tengah'], ['5318','Kabupaten Sumba Barat Daya'],
            ['5319','Kabupaten Manggarai Timur'], ['5320','Kabupaten Sabu Raijua'],
            ['5321','Kabupaten Malaka'], ['5371','Kota Kupang'],
        ];

        foreach ($kabkota as [$kode, $nama]) {
            $kodeProv = substr($kode, 0, 2);

            $provinsi = DB::table('wilayah')
                ->where('kode_wilayah', $kodeProv)
                ->where('tingkat_wilayah_id', 2)
                ->first();

            if (!$provinsi) {
                continue; // skip kalau provinsi belum ada
            }

            DB::table('wilayah')->insert([
                'id' => Str::uuid(),
                'kode_wilayah' => $kode,
                'nama_wilayah' => $nama,
                'tingkat_wilayah_id' => 3,
                'parent_wilayah_id' => $provinsi->id,
            ]);
        }
    }
}
