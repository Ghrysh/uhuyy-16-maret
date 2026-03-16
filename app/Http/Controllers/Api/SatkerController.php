<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Satker;
use Illuminate\Http\Request;

class SatkerController extends Controller
{
    /**
     * GET /api/satker
     * GET /api/satker?search=xxxx
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        // dd($request->query('search'));
        $query = Satker::with([
            'wilayah',
            'eselon',
            'parent',
            'periode'
        ]);

        if (!empty($search)) {
            $query->where('kode_satker', $search);
        }

        $satker = $query->orderBy('kode_satker', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => $search 
                ? 'Data satker berdasarkan pencarian kode'
                : 'Semua data satker',
            'data' => $satker
        ], 200);
    }
}