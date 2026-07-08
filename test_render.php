<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = microtime(true);
$periodes = App\Models\Periode::orderBy('created_at', 'asc')->get();
$activePeriodeId = $periodes->first()->id ?? null;

$satkerQuery = App\Models\Satker::with('eselon')->withCount('children')->where('periode_id', $activePeriodeId)->whereNull('parent_satker_id');
$satkers = $satkerQuery->orderByRaw('LENGTH(kode_satker) ASC')->orderBy('kode_satker', 'asc')->get();

echo "Query time: " . (microtime(true) - $start) . "s\n";

$startRender = microtime(true);
$html = view('admin.satker.index', [
    'satkers' => $satkers,
    'activePeriodeId' => $activePeriodeId,
    'periodes' => $periodes,
    'perm' => ['is_super'=>true, 'can_view'=>true, 'all_access'=>true, 'visibility'=>'all', 'actions'=>['create','edit','delete','assign'], 'allowed_ids'=>[]],
    'allSatkers' => collect(),
    'listAllSatkers' => collect(),
    'wilayahs' => collect(),
    'kabupaten' => collect(),
    'parents' => collect(),
    'jenisSatkers' => collect(),
    'allJabatan' => collect(),
    'pegawais' => collect(),
    'jenis_penugasans' => collect(),
    'roles' => collect(),
    'userRoles' => [],
    'allSatkersFlat' => collect(),
    'refJabatanSatker' => collect(),
    'rumusList' => collect(),
    'jabatanCategories' => collect(),
    'jabatanItems' => collect(),
])->render();
echo "Render time: " . (microtime(true) - $startRender) . "s\n";
echo "HTML size: " . strlen($html) . " bytes\n";
