<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Satker;
use App\Models\UserDetail;
use App\Models\Bulking;
use App\Models\BulkingDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\UpdateSatkerJob;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();

        $periodes = \App\Models\Periode::orderBy('created_at', 'asc')->get();
        $activePeriodeId = $request->input('periode_id', $periodes->first()->id ?? null);

        // Ambil semua satker hanya jika user tidak punya satker_id
        $satkers = null;
        if (!$user->satker_id) {
            $satkers = Satker::where('periode_id', $activePeriodeId)->orderBy('nama_satker')->get();
        }
        // dd($user);

        $pegawais = UserDetail::with(['riwayatSatkers' => function($q) use ($activePeriodeId) {
            $q->where('periode_id', $activePeriodeId)->with('satker:id,kode_satker,nama_satker');
        }])
        ->when($search, function ($query, $search) {
            $qStr = "%{$search}%";
            $query->where(function ($q) use ($qStr) {
                $q->whereRaw('nama ILIKE ?', [$qStr])
                ->orWhereRaw('nama_lengkap ILIKE ?', [$qStr])
                ->orWhere('nip', 'like', $qStr)
                ->orWhere('nip_baru', 'like', $qStr)
                ->orWhere('kode_satuan_kerja', 'like', $qStr);
            });
        })
        ->orderBy('nama_lengkap', 'asc')
        ->paginate(15)
        ->withQueryString();
        
        // dd($pegawais);

        $bulkings = Bulking::with(['details', 'creator'])
                    ->latest()
                    ->paginate(5, ['*'], 'bulking_page');


        if ($request->ajax()) {
            return view('admin.pegawai.index', compact('pegawais','satkers', 'bulkings', 'periodes', 'activePeriodeId'))->render();
        }

        return view('admin.pegawai.index', compact('pegawais','satkers', 'bulkings', 'periodes', 'activePeriodeId'));
    }

    public function updateSatker(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'satker_id' => 'required|exists:satker,id',
        ]);

        $selectedIds = $request->selected_ids;
        $authId = auth()->id();

        // Log the intent
        Log::info('UpdateSatker attempt initiated', [
            'admin_id' => $authId,
            'satker_target' => $request->satker_id,
            'total_users' => count($selectedIds)
        ]);

        // Jika lebih dari 1 → pakai queue + bulk
        if (count($selectedIds) > 1) {
            Log::info('Dispatching UpdateSatkerJob for bulk update', ['count' => count($selectedIds)]);
            
            UpdateSatkerJob::dispatch(
                $selectedIds,
                $request->satker_id,
                $authId
            );

            return back()->with('success', 'Bulk update sedang diproses di background.');
        }

        // Jika hanya 1 → update langsung
        try {
            $detail = UserDetail::findOrFail($selectedIds[0]);

            DB::transaction(function () use ($detail, $request) {
                if (!$detail->nip_baru) {
                    throw new \Exception('NIP baru tidak tersedia untuk user ini.');
                }

                $satkerTarget = Satker::find($request->satker_id);
                if (!$satkerTarget) {
                    throw new \Exception('Satker tujuan tidak ditemukan.');
                }

                $user = User::where('nip', $detail->nip_baru)->first();

                if ($user) {
                    $user->satker_id = $request->satker_id; // Keep backwards compatibility
                    $user->save();
                    
                    \App\Models\PegawaiPeriode::updateOrCreate(
                        ['user_id' => $user->id, 'periode_id' => $satkerTarget->periode_id],
                        ['satker_id' => $satkerTarget->id]
                    );

                    Log::info("User satker updated", ['user_id' => $user->id, 'new_satker' => $request->satker_id]);
                } else {
                    $user = User::create([
                        'nip'       => $detail->nip_baru,
                        'name'      => $detail->nama_lengkap ?? $detail->nama,
                        'email'     => $detail->email_dinas ?? $detail->nip_baru . '@kemenag.go.id',
                        'password'  => bcrypt('password123'),
                        'satker_id' => $request->satker_id,
                    ]);

                    \App\Models\PegawaiPeriode::create([
                        'user_id' => $user->id,
                        'periode_id' => $satkerTarget->periode_id,
                        'satker_id' => $satkerTarget->id
                    ]);

                    Log::info("User created and assigned to satker", ['nip' => $detail->nip_baru, 'satker' => $request->satker_id]);
                }
            });

            return back()->with('success', 'Update satker berhasil.');

        } catch (\Exception $e) {
            Log::error('UpdateSatker failed for user ID ' . $selectedIds[0] . ': ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkingList(Request $request)
    {
        $bulkings = Bulking::with(['details.user', 'creator'])
            ->latest()
            ->paginate(5);

        return view('admin.pegawai.partials.bulking-table', compact('bulkings'));
    }

    public function searchLocal(Request $request)
    {
        $search = $request->q;
        
        if (empty($search)) {
            return response()->json([]);
        }

        $pegawai = UserDetail::where('nip_baru', 'ILIKE', "%{$search}%")
            ->orWhere('nip', 'ILIKE', "%{$search}%")
            ->orWhere('nama_lengkap', 'ILIKE', "%{$search}%")
            ->orWhere('nama', 'ILIKE', "%{$search}%")
            ->limit(20)
            ->get();

        $formatted = $pegawai->map(function ($item) {
            $nipFinal = $item->nip_baru ?? $item->nip;
            $namaFinal = $item->nama_lengkap ?? $item->nama;
            return [
                'id' => $nipFinal, 
                'text' => $nipFinal . ' - ' . $namaFinal, 
                'nama' => $namaFinal
            ];
        });

        return response()->json($formatted);
    }
}