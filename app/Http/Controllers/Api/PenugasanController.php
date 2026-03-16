<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenugasanController extends Controller
{
    /**
     * Search Penugasan berdasarkan kode_satker
     * GET /api/penugasan?kode_satker=123456
     */
    public function index(Request $request)
    {
        $request->validate([
            'kode_satker' => 'required|string'
        ]);

        $kodeSatker = $request->kode_satker;

        $data = Penugasan::with([
                'user:id,name,nip',
                'satker:id,kode_satker,nama_satker',
                'jabatan:id,nama_jabatan',
                'jenisPenugasan:id,nama'
            ])
            ->whereHas('satker', function ($query) use ($kodeSatker) {
                $query->where('kode_satker', $kodeSatker);
            })
            ->where('status_aktif', 1)
            ->latest()
            ->get(); // pagination dihapus

        // Tambahkan role user
        $data->transform(function ($item) {

            $role = DB::table('user_roles')
                ->join('m_roles', 'user_roles.role_id', '=', 'm_roles.id')
                ->where('user_roles.user_id', $item->user->id)
                ->select('m_roles.key')
                ->first();

            $item->role = $role->key ?? null;

            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data penugasan berdasarkan kode satker',
            'total' => $data->count(),
            'data' => $data
        ]);
    }
}