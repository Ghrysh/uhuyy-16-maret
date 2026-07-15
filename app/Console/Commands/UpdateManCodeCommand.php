<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Satker;
use Illuminate\Support\Facades\DB;

class UpdateManCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'satker:update-man-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate MAN satker codes from 96x to 95x';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Fetching MAN Satkers...");

        $mans = Satker::where('nama_satker', 'like', 'Madrasah Aliyah Negeri%')
                    ->orWhere('nama_satker', 'like', 'MAN %')
                    ->get();
                    
        $this->info("Found " . $mans->count() . " MAN-like records.");

        $changed = 0;
        
        DB::beginTransaction();
        try {
            foreach ($mans as $man) {
                $kode = $man->kode_satker;
                if (!$kode) continue;
                
                $len = strlen($kode);
                if ($len < 3) continue;
                
                // Ambil 3 digit terakhir
                $last3 = substr($kode, -3);
                
                // Cek apakah formatnya diawali 9 dan diikuti 2 digit angka (Misal 961, 965)
                if ($last3[0] === '9' && is_numeric(substr($last3, 1))) {
                    $incValue = (int) substr($last3, 1);
                    
                    // Filter HANYA yang angkanya di atas atau sama dengan 61 (misal 961, 962)
                    // Yang 901 atau 931 akan dilewati karena kurang dari 61
                    if ($incValue >= 61) {
                        $newIncValue = $incValue - 10;
                        $newLast3 = '9' . str_pad($newIncValue, 2, '0', STR_PAD_LEFT);
                        
                        $newKode = substr($kode, 0, $len - 3) . $newLast3;
                        
                        $this->line("Updating {$man->nama_satker}: {$kode} -> {$newKode}");
                        
                        $man->update(['kode_satker' => $newKode]);
                        $changed++;
                        
                        // Cascade update untuk anak-anaknya jika ada
                        $changed += $this->cascadeUpdateKode($man->id, $kode, $newKode);
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
