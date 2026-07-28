<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class getProfileController extends Controller
{
    private function getAuthToken()
    {
        $response = Http::asForm()->post('https://api.kemenag.go.id/v1/auth/login', [
            'email' => 'mantabanget.id@gmail.com',
            'password' => 'Akubisa22',
        ]);

        if ($response->ok()) {
            $data = json_decode($response->body());
            return $data;
        } else {
            return false;
        }
    }

    private function getPegawaiByNIP($nip)
    {
        $token = $this->getAuthToken()->token;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get('https://api.kemenag.go.id/v1/pegawai/profil/' . $nip);

        if ($response->ok()) {
            $data = json_decode($response->body());
            return $data;
        } else {
            return false;
        }
    }

    public function searchByNIP(Request $request)
    {
        $request->validate([
            'nip' => 'required|string'
        ]);

        $nip = $request->input('nip');
        
        // 1. Check local database first ONLY for specific dummy accounts
        $dummyNips = ['0292827771890', '0292827771878', '0292827771809', '0292827771889'];
        
        if (in_array($nip, $dummyNips)) {
            $localUser = \App\Models\UserDetail::where('nip_baru', $nip)->orWhere('nip', $nip)->first();
            if ($localUser) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'data' => [
                            'NIP' => $localUser->nip,
                            'NIP_BARU' => $localUser->nip_baru,
                            'NAMA' => $localUser->nama,
                            'NAMA_LENGKAP' => $localUser->nama_lengkap,
                            'AGAMA' => $localUser->agama,
                            'TEMPAT_LAHIR' => $localUser->tempat_lahir,
                            'TANGGAL_LAHIR' => $localUser->tanggal_lahir,
                            'JENIS_KELAMIN' => $localUser->jenis_kelamin,
                            'GOL_RUANG' => $localUser->gol_ruang,
                            'TAMPIL_JABATAN' => $localUser->tampil_jabatan,
                            'LEVEL_JABATAN' => $localUser->level_jabatan,
                        ]
                    ]
                ]);
            }
        }

        // 2. Hit external SIMPEG API for all other normal users
        $pegawai = $this->getPegawaiByNIP($nip);

        if ($pegawai) {
            return response()->json([
                'success' => true,
                'data' => $pegawai
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data pegawai tidak ditemukan atau token tidak valid.'
            ], 404);
        }
    }

}
