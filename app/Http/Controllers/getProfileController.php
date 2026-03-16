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
