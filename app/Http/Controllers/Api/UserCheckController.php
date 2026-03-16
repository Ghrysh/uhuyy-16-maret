<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Penugasan;
use Illuminate\Http\Request;

class UserCheckController extends Controller
{
    public function checkByNip($nip)
    {
        $user = User::with(['userDetail', 'satker'])
            ->where('nip', $nip)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki kode satuan kerja baru'
            ], 404);
        }

        // Cek apakah user memiliki satker_id
        if (!$user->satker_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki kode satuan kerja baru'
            ], 200);
        }

        // Cek apakah ada penugasan aktif untuk satker_id user (bisa admin/pejabat lain)
        $penugasan = Penugasan::where('satker_id', $user->satker_id)
            ->where('status_aktif', 1)
            ->first();

        if (!$penugasan) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki admin dan pejabat'
            ], 200);
        }

        // Jika ada penugasan aktif untuk satker
        return response()->json([
            'success' => true,
            'data' => [
                'user_id'     => $user->id,
                'name'        => $user->name,
                'email'       => $user->email,
                'nip'         => $user->nip,
                'satker_id'   => $user->satker_id,
                'satker'      => $user->satker?->nama_satker ?? null,
                'user_detail' => $user->userDetail
            ]
        ]);
    }

    // Pengcekan penugasan
    public function checkRoleByNip($nip)
    {
        $user = User::with([
            'penugasanAktif.satker',
            'roles:id,nama,key'
        ])->where('nip', $nip)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $hasPenugasan = !is_null($user->penugasanAktif);

        return response()->json([
            'success' => $hasPenugasan,
            'has_penugasan' => $hasPenugasan,
            'has_role' => $hasPenugasan ? $user->roles->isNotEmpty() : false,
            'penugasan' => $hasPenugasan ? [
                'id' => $user->penugasanAktif->id,
                'satker' => $user->penugasanAktif->satker->nama_satker ?? null,
                'status_aktif' => $user->penugasanAktif->status_aktif,
            ] : null,
            'roles' => $hasPenugasan ? $user->roles : []
        ]);
    }
}