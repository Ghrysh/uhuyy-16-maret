<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProxyController extends Controller
{
    public function searchPegawai(Request $request) {
        $nip = $request->query('nip');
        $response = Http::get("https://ropegdev.kemenag.go.id/simsdm/pegawai/search?nip=$nip");
        return $response->json();
    }
}
