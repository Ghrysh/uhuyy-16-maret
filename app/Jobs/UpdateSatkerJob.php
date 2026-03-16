<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Bulking;
use App\Models\Satker;
use App\Models\UserDetail;
use App\Models\BulkingDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSatkerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $selectedIds;
    protected $satkerId;
    protected $userId;

    public function __construct($selectedIds, $satkerId, $userId)
    {
        $this->selectedIds = $selectedIds;
        $this->satkerId    = $satkerId;
        $this->userId      = $userId;
    }

    public function handle(): void
    {
        DB::transaction(function () {

            $details = UserDetail::whereIn('id', $this->selectedIds)->get();

            // Ambil data satker
            $satker = Satker::find($this->satkerId);

            $bulking = Bulking::create([
                'type'       => 'update_satker',
                'satker_id'  => $this->satkerId,
                'created_by' => $this->userId,
                'total_data' => $details->count(),
            ]);

            foreach ($details as $detail) {

                try {

                    $user = User::where('nip', $detail->nip_baru)->first();

                    if ($user) {
                        $user->update([
                            'satker_id' => $this->satkerId
                        ]);

                        $action = 'memperbarui';
                    } else {
                        $user = User::create([
                            'nip'       => $detail->nip_baru,
                            'name'      => $detail->nama_lengkap ?? $detail->nama,
                            'email'     => $detail->email_dinas ?? $detail->nip_baru . '@kemenag.go.id',
                            'password'  => Hash::make('password123'),
                            'satker_id' => $this->satkerId,
                        ]);

                        $action = 'menambahkan';
                    }

                    // Tambahkan message sukses
                    $message = "Berhasil {$action} kode satker "
                        . ($satker->kode_satker ?? '-')
                        . " - "
                        . ($satker->nama_satker ?? '-');

                    BulkingDetail::create([
                        'bulking_id'     => $bulking->id,
                        'user_detail_id' => $detail->id,
                        'user_id'        => $user->id,
                        'nip'            => $detail->nip_baru,
                        'status'         => 'success',
                        'message'        => $message,
                    ]);

                } catch (\Exception $e) {

                    BulkingDetail::create([
                        'bulking_id'     => $bulking->id,
                        'user_detail_id' => $detail->id,
                        'nip'            => $detail->nip_baru,
                        'status'         => 'failed',
                        'message'        => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}