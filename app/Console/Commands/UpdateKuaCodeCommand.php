<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Satker;
use Illuminate\Support\Facades\DB;

class UpdateKuaCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:update-kua-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate KUA satker codes from 90x to 97x';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Fetching KUA Satkers...");

        $kuas = Satker::where('nama_satker', 'like', 'Kantor Urusan Agama%')
                    ->orWhere('nama_satker', 'like', 'KUA %')
                    ->get();
                    
        $this->info("Found " . $kuas->count() . " KUA-like records.");

        $changed = 0;
        
        DB::beginTransaction();
        try {
            foreach ($kuas as $kua) {
                $kode = $kua->kode_satker;
                if (!$kode) continue;
                
                $len = strlen($kode);
                if ($len < 3) continue;
                
                // Ambil 3 digit terakhir
                $last3 = substr($kode, -3);
                
                // Cek apakah formatnya diawali 9 dan diikuti 2 digit angka (Misal 901, 905)
                if ($last3[0] === '9' && is_numeric(substr($last3, 1))) {
                    $incValue = (int) substr($last3, 1);
                    
                    // Jika angkanya kurang dari 70, berarti ini masih format lama
                    if ($incValue > 0 && $incValue < 70) {
                        $newIncValue = $incValue + 70;
                        $newLast3 = '9' . str_pad($newIncValue, 2, '0', STR_PAD_LEFT);
                        
                        $newKode = substr($kode, 0, $len - 3) . $newLast3;
                        
                        $this->line("Updating {$kua->nama_satker}: {$kode} -> {$newKode}");
                        
                        $kua->update(['kode_satker' => $newKode]);
                        $changed++;
                        
                        // Cascade update untuk anak-anaknya jika ada (biasanya KUA tidak punya anak, tapi jaga-jaga)
                        $changed += $this->cascadeUpdateKode($kua->id, $kode, $newKode);
                    }
                }
            }
            DB::commit();
            $this->info("Successfully updated {$changed} records.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error occurred: " . $e->getMessage());
        }
    }
    
    private function cascadeUpdateKode($parentId, $oldParentKode, $newParentKode)
    {
        $changes = 0;
        $children = Satker::where('parent_satker_id', $parentId)->get();
        
        foreach ($children as $child) {
            $childOldKode = $child->kode_satker;
            if (str_starts_with($childOldKode, $oldParentKode)) {
                $childNewKode = $newParentKode . substr($childOldKode, strlen($oldParentKode));
                $this->line("   - Cascade Child: {$childOldKode} -> {$childNewKode}");
                $child->update(['kode_satker' => $childNewKode]);
                $changes++;
                
                // Recursive untuk cucu-cucunya
                $changes += $this->cascadeUpdateKode($child->id, $childOldKode, $childNewKode);
            }
        }
        
        return $changes;
    }
}
