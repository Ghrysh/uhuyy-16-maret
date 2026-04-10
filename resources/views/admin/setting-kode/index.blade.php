@extends('layouts.admin')
@section('title', 'Setup Rumus & Jabatan Satker')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-[#112D4E]">Setup Kode Satuan Kerja</h2>
    <p class="text-sm text-gray-500 mt-1">Konfigurasi pola kode otomatis, status jabatan, dan edit hierarki secara manual.</p>
</div>

<script>
    window.rumusDatabase = @json($rumusList);
    window.refJabatanDatabase = @json($refJabatans); 
</script>

<div x-data="formulaBuilder()" class="w-full">

    {{-- TAB NAVIGASI --}}
    <div class="flex flex-wrap gap-2 border-b border-gray-300 pb-2">
        <button @click="tab = 'builder'" :class="tab === 'builder' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none">
            <i class="fas fa-hammer mr-1"></i> Pembuat Rumus
        </button>
        {{-- TAMBAHAN TAB JABATAN --}}
        <button @click="tab = 'jabatan'" :class="tab === 'jabatan' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none">
            <i class="fas fa-user-tie mr-1"></i> Status Jabatan & Fix Code
        </button>
        <button @click="tab = 'manual'" :class="tab === 'manual' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none ml-auto border border-[#112D4E]/20">
            <i class="fas fa-sitemap mr-1"></i> Hirarki & Edit Manual
        </button>
    </div>

    {{-- ========================================================== --}}
    {{-- TAB 1: FORMULA BUILDER --}}
    {{-- ========================================================== --}}
    <div x-show="tab === 'builder'" x-cloak class="mt-4">
        
        {{-- FORM PEMBUAT RUMUS --}}
        <div class="bg-white shadow rounded-2xl p-6 border border-gray-100 mb-8">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-[#112D4E]"><i class="fas fa-plus-circle mr-2 text-blue-500"></i>Buat Formula Baru</h3>
                <p class="text-xs text-slate-500">Isi form secara berurutan untuk membuka kunci pembuat rumus.</p>
            </div>

            <form action="{{ route('admin.setting-kode.storeRumus') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 relative">
                    
                    {{-- 1. NAMA RUMUS --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">1. Nama Rumus</label>
                        <input type="text" name="nama_rumus" x-model="namaRumus" placeholder="Contoh: Format MTsN" required 
                            class="w-full text-sm px-3 py-2 border rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all bg-white">
                    </div>
                    
                    {{-- 2. LEVEL ESELON --}}
                    <div class="relative">
                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1" :class="!isEselonEnabled ? 'text-slate-400' : ''">2. Berlaku di Level Eselon?</label>
                        <select name="jenis_satker_id" x-model="selectedLevel" :disabled="!isEselonEnabled" 
                            class="w-full text-sm px-3 py-2 border rounded-lg outline-none transition-all"
                            :class="!isEselonEnabled ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'">
                            <option value="">Pilih Level Eselon</option>
                            <option value="all" class="font-bold text-blue-600">Semua Level (Global)</option>
                            @foreach($jenisSatkers as $js) <option value="{{ $js->id }}">{{ $js->nama }}</option> @endforeach
                        </select>
                    </div>

                    {{-- 3. WILAYAH --}}
                    <div class="relative">
                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1" :class="!isWilayahEnabled ? 'text-slate-400' : ''">3. Berlaku di Wilayah?</label>
                        
                        {{-- Select Utama (Hanya untuk Tampilan User) --}}
                        <select id="wilayah_select_builder" x-model="selectedWilayahId" @change="filterJabatan(); updateTingkatWilayahId();" :disabled="!isWilayahEnabled" 
                            class="w-full text-sm px-3 py-2 border rounded-lg outline-none transition-all"
                            :class="!isWilayahEnabled ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'">
                            <option value="" data-tingkat="">Pilih Wilayah</option>
                            <option value="all" data-tingkat="">Semua Wilayah (Global)</option>
                            
                            {{-- Logika agar support variabel data yang lama maupun yang baru --}}
                            @isset($wilayahs)
                                @foreach($wilayahs as $w) 
                                    <option value="{{ $w->id }}" data-tingkat="{{ $w->tingkat_wilayah_id ?? $w->id }}">{{ $w->nama_wilayah ?? $w->nama }}</option> 
                                @endforeach
                            @elseif(isset($tingkatWilayahs))
                                @foreach($tingkatWilayahs as $tw) 
                                    <option value="{{ $tw->id }}" data-tingkat="{{ $tw->id }}">{{ $tw->nama }}</option> 
                                @endforeach
                            @endisset
                        </select>

                        {{-- INPUT TERSEMBUNYI (Ini yang akan ditarik oleh Controller ke Database) --}}
                        <input type="hidden" name="tingkat_wilayah_id" id="hidden_tingkat_wilayah_id">
                    </div>

                    {{-- 4. JABATAN (Dengan tampilan FIX CODE) --}}
                    <div x-show="selectedLevel !== '1'" class="relative">
                        <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1" :class="!isJabatanEnabled ? 'text-slate-400' : ''">4. Khusus Jabatan? (Opsional)</label>
                        <select name="ref_jabatan_satker_id" x-model="selectedJabatan" :disabled="!isJabatanEnabled" 
                            class="w-full text-sm px-3 py-2 border rounded-lg outline-none transition-all"
                            :class="!isJabatanEnabled ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed' : 'bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20'">
                            <option value="">Semua Jabatan</option>
                            <template x-for="jb in filteredJabatans" :key="jb.id">
                                {{-- PERUBAHAN: Menampilkan [Fix Code] jika ada --}}
                                <option :value="jb.id" x-text="jb.label_jabatan + (jb.kode_dasar ? ' [' + jb.kode_dasar + ']' : '')"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- 5. WORKSPACE BUILDER (Dengan Visual Lock) --}}
                <div class="relative bg-slate-800 p-5 rounded-xl shadow-inner border border-slate-700 overflow-hidden">
                    
                    {{-- OVERLAY LOCK BUKUM --}}
                    <div x-show="!isWorkspaceEnabled" x-transition.opacity 
                        class="absolute inset-0 bg-slate-900/80 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center">
                        <div class="bg-slate-800 px-4 py-2 rounded-full border border-slate-700 shadow-lg text-amber-400 text-sm font-bold flex items-center animate-pulse">
                            <i class="fas fa-lock mr-2"></i> Isi form urutan 1 s/d 3 untuk membuka workspace
                        </div>
                    </div>

                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">5. Workspace Formula</label>
                    
                    <div class="relative">
                        <input type="text" name="pola" id="input_pola" x-model="formulaInput" readonly required placeholder="Klik tombol di bawah untuk menyusun rumus..." class="w-full text-xl font-mono px-4 py-3 bg-slate-900 border border-slate-600 text-amber-400 rounded-lg outline-none mb-3 shadow-inner placeholder-slate-700 cursor-not-allowed select-none">
                        
                        {{-- Tombol Hapus (TIDAK DIKUNCI LAGI) --}}
                        <button type="button" x-show="formulaInput.length > 0" @click="hapusBalokTerakhir()" class="absolute right-3 top-3 text-slate-500 hover:text-red-400 transition" title="Hapus Balok Terakhir">
                            <i class="fas fa-backspace text-xl"></i>
                        </button>
                    </div>
                    
                    <div class="flex items-start justify-between px-3 py-2 bg-slate-900/50 rounded text-sm text-white mb-4 border border-slate-700 gap-4">
                        <div class="flex items-center gap-2 mt-1">
                            <i class="fas fa-eye text-slate-500"></i>
                            <div id="formula_preview" class="font-bold flex items-center gap-1.5"><span class="text-slate-300 italic">Preview kode akan muncul di sini...</span></div>
                        </div>
                        
                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <div x-show="formulaInput.length > 0" class="text-[10px] font-bold bg-indigo-500/20 text-indigo-300 px-2 py-1 rounded border border-indigo-500/30 w-fit shadow-sm">
                                <i class="fas fa-calculator mr-1"></i> ~ <span x-text="estimatedDigits"></span> 
                                <span x-show="formulaInput.includes('[PARENT]') && getInheritedExtra(selectedLevel, selectedWilayahId) > 0" class="text-amber-400 font-black">
                                    (+<span x-text="getInheritedExtra(selectedLevel, selectedWilayahId)"></span>)
                                </span> Digit
                            </div>
                            
                            <div class="flex flex-col gap-1 items-end">
                                <div x-show="formulaInput.includes('[PARENT]') && getInheritedExtra(selectedLevel, selectedWilayahId) > 0" class="text-[9px] text-amber-100 bg-amber-500/20 border border-amber-500/30 px-1.5 py-1 rounded flex items-start gap-1 w-fit max-w-[200px] leading-tight text-right shadow-sm">
                                    <i class="fas fa-info-circle mt-0.5"></i> 
                                    <span>[PARENT] memiliki angka tetap</span>
                                </div>

                                <div x-show="hasFixedCode && formulaInput.length > 0 && !formulaInput.includes(currentFixedCode)" class="text-[9px] text-red-100 bg-red-500/20 border border-red-500/30 px-1.5 py-1 rounded flex items-start gap-1 w-fit max-w-[200px] leading-tight text-right shadow-sm">
                                    <i class="fas fa-exclamation-triangle mt-0.5"></i> 
                                    <span>Angka tetap (<b x-text="currentFixedCode"></b>) belum dimasukkan.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <div class="flex flex-wrap gap-2">
                            {{-- Tombol Induk & No Urut TIDAK DIKUNCI LAGI --}}
                            <button type="button" x-show="selectedLevel !== '1'" @click="insertTag('[PARENT]')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-level-up-alt mr-1"></i> + Induk (Parent)</button>
                            <button type="button" @click="askIncrement()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-sort-numeric-down mr-1"></i> + No. Urut (Increment)</button>
                            
                            {{-- Tombol Angka Tetap (Sembunyi HANYA JIKA ada Fix Code) --}}
                            <button type="button" x-show="!hasFixedCode" @click="askStatic()" class="px-3 py-1.5 bg-slate-600 hover:bg-slate-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-font mr-1"></i> + Angka Tetap</button>

                            {{-- TOMBOL BARU: Muncul jika punya Fix Code, Disable jika sudah diklik --}}
                            <button type="button" 
                                x-show="hasFixedCode" 
                                :disabled="formulaInput.includes(currentFixedCode)"
                                @click="insertTag(currentFixedCode)" 
                                :class="formulaInput.includes(currentFixedCode) ? 'bg-slate-400 text-white cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow'"
                                class="px-3 py-1.5 text-xs font-bold rounded transition">
                                <i class="fas fa-thumbtack mr-1"></i> + Kode Jabatan (<span x-text="currentFixedCode"></span>)
                            </button>
                        </div>
                        
                        {{-- Tombol Kosongkan (TIDAK DIKUNCI LAGI) --}}
                        <button type="button" x-show="formulaInput.length > 0" @click="formulaInput = ''" class="px-3 py-1.5 bg-red-900/40 hover:bg-red-600 text-red-300 hover:text-white text-xs font-bold rounded shadow transition">
                            <i class="fas fa-trash-alt mr-1"></i> Kosongkan
                        </button>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" 
                        :disabled="!isWorkspaceEnabled || formulaInput.trim() === ''"
                        :class="(!isWorkspaceEnabled || formulaInput.trim() === '') ? 'bg-slate-300 text-slate-500 cursor-not-allowed' : 'bg-[#112D4E] hover:bg-blue-900 text-white shadow'"
                        class="px-6 py-2.5 rounded-lg text-sm font-bold transition">
                        <i class="fas fa-save mr-2"></i> Simpan Formula
                    </button>
                </div>
            </form>
        </div>

        {{-- TABEL DAFTAR RUMUS --}}
        <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-100">
            <div class="p-4 bg-slate-50 border-b flex flex-col sm:flex-row justify-between items-center gap-3">
                <h3 class="font-bold text-slate-700"><i class="fas fa-list-ul mr-2"></i>Daftar Formula Tersimpan</h3>
                {{-- KOTAK PENCARIAN RUMUS --}}
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-slate-400 text-xs"></i></span>
                    <input type="text" x-model.debounce.300ms="search" @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();" placeholder="Cari nama rumus / pola..." class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 outline-none transition">
                </div>
            </div>
            {{-- TAMBAHAN ID CONTAINER & KELAS SCROLL --}}
            <div id="rumusTableContainer" class="overflow-y-auto overflow-x-auto max-h-[60vh] scroll-smooth">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="px-4 py-3">Nama Aturan</th>
                            <th class="px-4 py-3">Kombinasi (Wil/Lvl/Jab)</th>
                            <th class="px-4 py-3 font-mono">Formula [Pola]</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($rumusList as $r)
                        {{-- TAMBAHAN CLASS rumus-row --}}
                        <tr class="rumus-row hover:bg-blue-50/50 transition">
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $r->nama_rumus }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                @if(!$r->tingkat_wilayah_id && !$r->jenis_satker_id && !$r->ref_jabatan_satker_id)
                                    <span class="italic text-slate-400">Global (Berlaku Semua)</span>
                                @else
                                    <div class="flex flex-col gap-1.5">
                                        @if($r->tingkat_wilayah_id) 
                                            @php
                                                $nw = match((int)$r->tingkat_wilayah_id) { 1=>'Pusat', 2=>'Provinsi (Kanwil)', 3=>'Kabupaten/Kota', 4=>'PTKN', default=>'Level '.$r->tingkat_wilayah_id };
                                            @endphp
                                            <span class="bg-indigo-100 text-indigo-700 px-2.5 py-0.5 rounded-md w-fit font-semibold">Wil: {{ $nw }}</span> 
                                        @endif
                                        @if($r->jenis_satker_id) 
                                            <span class="bg-sky-100 text-sky-700 px-2.5 py-0.5 rounded-md w-fit font-semibold">Lvl: {{ \App\Models\MJenisSatker::find($r->jenis_satker_id)->nama ?? 'ID '.$r->jenis_satker_id }}</span> 
                                        @endif
                                        @if($r->ref_jabatan_satker_id) 
                                            <span class="bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-md w-fit font-semibold">Jab: {{ \App\Models\RefJabatanSatker::find($r->ref_jabatan_satker_id)->label_jabatan ?? 'ID '.$r->ref_jabatan_satker_id }}</span> 
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5">
                                    <span class="font-mono font-bold text-blue-600 bg-blue-50/30 px-2 py-1 rounded w-fit border border-blue-100">{{ $r->pola }}</span>
                                    @php
                                        // 1. FUNGSI UNTUK MELACAK TAMBAHAN ANGKA TETAP DARI PARENT (+X)
                                        if (!isset($getInheritedExtra)) {
                                            $getInheritedExtra = function($es, $wil, $allRumus, $allJabs) use (&$getInheritedExtra) {
                                                $es = (int)$es;
                                                if ($es <= 1) return 0;
                                                $p = $allRumus->first(function($item) use ($es, $wil) { return (int)$item->jenis_satker_id === ($es - 1) && $item->tingkat_wilayah_id == $wil; });
                                                if (!$p) $p = $allRumus->first(function($item) use ($es) { return (int)$item->jenis_satker_id === ($es - 1) && empty($item->tingkat_wilayah_id); });
                                                
                                                $extra = 0;
                                                if ($p) {
                                                    if ($p->ref_jabatan_satker_id) {
                                                        $jab = $allJabs->firstWhere('id', $p->ref_jabatan_satker_id);
                                                        if ($jab && $jab->kode_dasar) $extra += strlen(trim($jab->kode_dasar));
                                                    }
                                                    if (str_contains($p->pola ?? '', '[PARENT]')) $extra += $getInheritedExtra($es - 1, $wil, $allRumus, $allJabs);
                                                }
                                                return $extra;
                                            };
                                        }

                                        $est_digit = 0;
                                        $temp_pola = $r->pola ?? '';
                                        $eselon = (int) ($r->jenis_satker_id ?? 1);
                                        $wilayah = $r->tingkat_wilayah_id;
                                        
                                        // 2. ESTIMASI DIGIT DASAR (Otomatis: Eselon - 1 dikali 2)
                                        if (str_contains($temp_pola, '[PARENT]')) {
                                            $est_digit += max(0, ($eselon - 1) * 2);
                                        }
                                        
                                        // 3. Tambah digit dari [INC] dan angka manual yang diketik di rumus ini
                                        if (preg_match_all('/\[INC:(\d+)/', $temp_pola, $matches)) {
                                            foreach($matches[1] as $digit) $est_digit += (int) $digit;
                                        }
                                        $clean_pola = preg_replace('/\[PARENT\]|\[INC:[^\]]+\]/', '', $temp_pola);
                                        $est_digit += strlen(trim($clean_pola));

                                        // 4. Cek apakah rumus ini sendiri punya kode tetap yang terlewat
                                        $fixed_code = '';
                                        if ($r->ref_jabatan_satker_id && isset($refJabatans)) {
                                            $jabatan = $refJabatans->firstWhere('id', $r->ref_jabatan_satker_id);
                                            if ($jabatan && $jabatan->kode_dasar) $fixed_code = trim($jabatan->kode_dasar);
                                        }

                                        // 5. Hitung pelacakan Angka Tetap Parent (Memunculkan Warning Kuning)
                                        $inherited_extra = 0;
                                        if (str_contains($temp_pola, '[PARENT]')) {
                                            $inherited_extra = $getInheritedExtra($eselon, $wilayah, $rumusList, $refJabatans);
                                        }
                                    @endphp
                                    
                                    {{-- TAMPILAN BARU --}}
                                    <div class="flex flex-col gap-1 mt-0.5">
                                        <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold w-fit border border-indigo-200 shadow-sm">
                                            <i class="fas fa-calculator mr-1 text-indigo-400"></i> ~ 
                                            {{ $est_digit }} 
                                            @if($inherited_extra > 0)
                                                <span class="text-amber-600 font-black">(+{{ $inherited_extra }})</span>
                                            @endif
                                            Digit
                                        </span>
                                        
                                        @if($inherited_extra > 0)
                                            <div class="text-[9px] text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-1 rounded-md flex items-start gap-1 w-fit leading-tight shadow-sm mt-0.5">
                                                <i class="fas fa-info-circle mt-0.5 text-amber-500"></i> 
                                                <span>[PARENT] memiliki angka tetap</span>
                                            </div>
                                        @endif

                                        @if($fixed_code !== '' && !str_contains($temp_pola, $fixed_code))
                                            <div class="text-[9px] text-red-700 bg-red-50 border border-red-200 px-1.5 py-1 rounded-md flex items-start gap-1 w-fit leading-tight shadow-sm mt-0.5">
                                                <i class="fas fa-exclamation-triangle mt-0.5 text-red-500"></i> 
                                                <span>Angka tetap <b>({{ $fixed_code }})</b> belum dimasukkan.</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($r->is_applied)
                                    <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-black uppercase shadow-sm border border-emerald-200"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                                @else
                                    <form action="{{ route('admin.setting-kode.applyRumus', $r->id) }}" method="POST">
                                        @csrf <button type="button" onclick="confirmApply(this)" class="text-[10px] bg-slate-200 hover:bg-slate-300 text-slate-600 px-3 py-1 rounded-full font-bold uppercase transition"><i class="fas fa-toggle-off mr-1"></i> Terapkan</button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="openEditModal({{ json_encode($r) }})" class="text-amber-500 hover:text-amber-700 p-2 bg-amber-50 rounded-lg transition" title="Edit Formula"><i class="fas fa-edit"></i></button>
                                    <form action="{{ route('admin.setting-kode.destroyRumus', $r->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)" class="text-red-500 hover:text-red-700 p-2 bg-red-50 rounded-lg transition" title="Hapus Formula"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- TAB 2: MANAJEMEN STATUS JABATAN (BARU) --}}
    {{-- ========================================================== --}}
    <div x-show="tab === 'jabatan'" x-cloak class="mt-4">
        <div class="bg-white shadow rounded-2xl overflow-hidden border border-gray-100">
            <div class="p-5 bg-slate-50 border-b flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div>
                    <h3 class="font-bold text-slate-700 text-lg"><i class="fas fa-user-tie mr-2 text-indigo-500"></i>Daftar Status Jabatan</h3>
                    <p class="text-xs text-slate-500 mt-1">Kelola daftar jabatan dan tentukan angka tetap (kode khusus) jika ada.</p>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    {{-- KOTAK PENCARIAN JABATAN --}}
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-slate-400 text-xs"></i></span>
                        <input type="text" x-model.debounce.300ms="search" @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();" placeholder="Cari jabatan / angka tetap..." class="w-full pl-9 pr-4 py-2.5 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>
                    <button type="button" @click="openAddJabatanModal()" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-md transition flex items-center justify-center shrink-0">
                        <i class="fas fa-plus mr-2"></i> Tambah Jabatan
                    </button>
                </div>
            </div>
            {{-- TAMBAHAN ID CONTAINER & KELAS SCROLL --}}
            <div id="jabatanTableContainer" class="overflow-y-auto overflow-x-auto max-h-[60vh] scroll-smooth">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-100 text-slate-600 font-bold uppercase text-[10px] sticky top-0 z-10">
                        <tr>
                            <th class="px-5 py-4">Nama Status Jabatan</th>
                            <th class="px-5 py-4">Angka Tetap [Fix Code]</th>
                            <th class="px-5 py-4">Lingkup Wilayah</th>
                            <th class="px-5 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($refJabatans as $jb)
                        {{-- TAMBAHAN CLASS jabatan-row --}}
                        <tr class="jabatan-row hover:bg-indigo-50/30 transition">
                            <td class="px-5 py-3 font-bold text-slate-800">{{ $jb->label_jabatan }}</td>
                            <td class="px-5 py-3 font-mono font-bold text-indigo-600">
                                @if($jb->kode_dasar)
                                    <span class="bg-indigo-100 text-indigo-800 px-2.5 py-1 rounded shadow-sm border border-indigo-200">{{ $jb->kode_dasar }}</span>
                                @else
                                    <span class="text-slate-400 italic font-sans text-xs font-normal">Tidak ada patokan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-500">
                                @php
                                    $lingkup = 'Global (Semua Wilayah)';
                                    $bg = 'bg-slate-100 text-slate-600';
                                    if($jb->tingkat_wilayah_id == 1) { $lingkup = 'Pusat'; $bg = 'bg-emerald-100 text-emerald-700'; }
                                    if($jb->tingkat_wilayah_id == 2) { $lingkup = 'Provinsi (Kanwil)'; $bg = 'bg-sky-100 text-sky-700'; }
                                    if($jb->tingkat_wilayah_id == 3) { $lingkup = 'Kabupaten/Kota'; $bg = 'bg-amber-100 text-amber-700'; }
                                    if($jb->tingkat_wilayah_id == 4) { $lingkup = 'PTKN'; $bg = 'bg-purple-100 text-purple-700'; }
                                @endphp
                                <span class="{{ $bg }} px-2.5 py-1 rounded-md font-semibold">{{ $lingkup }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEditJabatanModal({{ json_encode($jb) }})" class="text-indigo-500 hover:text-indigo-700 p-2 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition" title="Edit Jabatan">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.setting-kode.destroyJabatan', $jb->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)" class="text-red-500 hover:text-red-700 p-2 bg-red-50 hover:bg-red-100 rounded-lg transition" title="Hapus Jabatan"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================== --}}
    {{-- TAB 3: HIRARKI & EDIT MANUAL --}}
    {{-- ========================================================== --}}
    <div x-show="tab === 'manual'" x-cloak class="mt-4">
        <div class="bg-white shadow rounded-2xl p-6 border-t-4 border-gray-800">
            <div class="mb-4 flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-[#112D4E]"><i class="fas fa-sitemap mr-2 text-gray-800"></i>Hirarki Satker & Edit Manual</h3>
                    <p class="text-xs text-slate-500">Ubah kode secara manual. Perubahan pada kode induk akan otomatis merubah (Cascade) kode anak-anaknya.</p>
                </div>
            </div>

            <div class="relative w-full mb-4">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" x-model.debounce.300ms="search" @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-[#112D4E] block w-full pl-10 p-3 outline-none" placeholder="Ketik Nama Satker atau Kode... (Tekan Enter)">
            </div>

            <form @submit.prevent="updateManualBulk($event)" action="{{ route('admin.setting-kode.updateManualBulk') ?? url('admin/setting-kode/update-manual') }}" method="POST">
                @csrf
                
                @if(isset($periodes) && $periodes->count() > 0)
                <div class="flex overflow-x-auto space-x-2 mb-4 pb-2 border-b border-gray-200 hide-scrollbar">
                    @foreach($periodes as $periode)
                        <button type="button" @click="activePeriode = '{{ $periode->id }}'"
                                :class="activePeriode === '{{ $periode->id }}' ? 'bg-[#112D4E] text-white border-[#112D4E] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                class="px-5 py-2.5 text-sm font-semibold rounded-lg border whitespace-nowrap transition-all duration-200">
                            {{ $periode->nama_periode }}
                        </button>
                    @endforeach
                </div>
                @endif

                <div class="space-y-2 bg-slate-50/50 rounded-xl p-4 border min-h-[400px] max-h-[600px] overflow-y-auto shadow-inner scroll-smooth" id="satkerManualContainer">
                    @forelse($satkerRoots as $root)
                        <div x-show="activePeriode == '{{ $root->periode_id }}'">
                            @include('admin.setting-kode._item_edit_manual', ['item' => $root])
                        </div>
                    @empty
                        <div class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <i class="fas fa-folder-open text-3xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-500 font-medium">Belum ada data Satuan Kerja.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                    <button type="submit" class="bg-[#112D4E] hover:bg-blue-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 transform active:scale-95">
                        <i class="fas fa-save"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL EDIT RUMUS --}}
    {{-- ========================================== --}}
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="editModalOpen = false"></div>

            <div x-show="editModalOpen" x-transition 
                class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-[#112D4E]"><i class="fas fa-edit mr-2 text-amber-500"></i>Edit Formula & Filter</h3>
                    <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button>
                </div>

                <form :action="getUpdateRoute(editData.id)" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-5">
                        
                        {{-- ROW FILTER EDIT --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Nama Rumus</label>
                                <input type="text" name="nama_rumus" x-model="editData.nama_rumus" required class="w-full text-sm px-3 py-2 border rounded-lg focus:border-blue-500 outline-none bg-slate-50">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Level Eselon</label>
                                <select name="jenis_satker_id" x-model="editData.jenis_satker_id" class="w-full text-sm px-3 py-2 border rounded-lg focus:border-blue-500 outline-none bg-slate-50">
                                    <option value="all" class="font-bold text-blue-600">Semua Level (Global)</option>
                                    @foreach($jenisSatkers as $js) <option value="{{ $js->id }}">{{ $js->nama }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tingkat Wilayah</label>
                                <select name="tingkat_wilayah_id" x-model="editData.tingkat_wilayah_id" @change="filterEditJabatan()" class="w-full text-sm px-3 py-2 border rounded-lg focus:border-blue-500 outline-none bg-slate-50">
                                    <option value="all" class="font-bold text-blue-600">Semua Wilayah (Global)</option>
                                    @foreach($tingkatWilayahs as $tw) 
                                        <option value="{{ $tw->id }}">{{ $tw->nama }}</option> 
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="editData.jenis_satker_id !== '1'">
                                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Khusus Jabatan (Opsional)</label>
                                <select name="ref_jabatan_satker_id" x-model="editData.ref_jabatan_satker_id" class="w-full text-sm px-3 py-2 border rounded-lg focus:border-blue-500 outline-none bg-slate-50">
                                    <option value="">Semua Jabatan</option>
                                    <template x-for="jb in filteredEditJabatans" :key="jb.id">
                                        <option :value="jb.id" x-text="jb.label_jabatan + (jb.kode_dasar ? ' [' + jb.kode_dasar + ']' : '')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- WORKSPACE EDIT --}}
                        <div class="bg-slate-800 p-5 rounded-xl shadow-inner border border-slate-700">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Workspace Formula</label>
                            
                            <div class="relative">
                                <input type="text" name="pola" x-model="editData.pola" readonly required class="w-full text-xl font-mono px-4 py-3 bg-slate-900 border border-slate-600 text-amber-400 rounded-lg outline-none mb-3 shadow-inner cursor-not-allowed select-none">
                                <button type="button" x-show="editData.pola.length > 0" @click="hapusBalokEdit()" class="absolute right-3 top-3 text-slate-500 hover:text-red-400 transition" title="Hapus Balok Terakhir">
                                    <i class="fas fa-backspace text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="flex items-start justify-between px-3 py-2 bg-slate-900/50 rounded text-sm text-white mb-4 border border-slate-700 gap-4">
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fas fa-eye text-slate-500"></i>
                                    <div class="font-bold flex items-center gap-1.5" x-html="getEditPreview()"></div>
                                </div>
                                
                                <div class="flex flex-col items-end gap-1.5 shrink-0">
                                    <div x-show="editData.pola.length > 0" class="text-[10px] font-bold bg-amber-500/20 text-amber-300 px-2 py-1 rounded border border-amber-500/30 w-fit shadow-sm">
                                        <i class="fas fa-calculator mr-1"></i> ~ <span x-text="editEstimatedDigits"></span> 
                                        <span x-show="editData.pola.includes('[PARENT]') && getInheritedExtra(editData.jenis_satker_id, editData.tingkat_wilayah_id) > 0" class="text-amber-200 font-black">
                                            (+<span x-text="getInheritedExtra(editData.jenis_satker_id, editData.tingkat_wilayah_id)"></span>)
                                        </span> Digit
                                    </div>
                                    
                                    <div class="flex flex-col gap-1 items-end">
                                        <div x-show="editData.pola.includes('[PARENT]') && getInheritedExtra(editData.jenis_satker_id, editData.tingkat_wilayah_id) > 0" class="text-[9px] text-amber-100 bg-amber-500/20 border border-amber-500/30 px-1.5 py-1 rounded flex items-start gap-1 w-fit max-w-[200px] leading-tight text-right shadow-sm">
                                            <i class="fas fa-info-circle mt-0.5"></i> 
                                            <span>[PARENT] memiliki angka tetap</span>
                                        </div>

                                        <div x-show="editHasFixedCode && editData.pola.length > 0 && !editData.pola.includes(editCurrentFixedCode)" class="text-[9px] text-red-100 bg-red-500/20 border border-red-500/30 px-1.5 py-1 rounded flex items-start gap-1 w-fit max-w-[200px] leading-tight text-right shadow-sm">
                                            <i class="fas fa-exclamation-triangle mt-0.5"></i> 
                                            <span>Angka tetap (<b x-text="editCurrentFixedCode"></b>) belum dimasukkan.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 items-center justify-between">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" x-show="editData.jenis_satker_id !== '1'" @click="insertTagEdit('[PARENT]')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-level-up-alt mr-1"></i> + Induk</button>
                                    <button type="button" @click="askIncrementEdit()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-sort-numeric-down mr-1"></i> + No. Urut</button>
                                    
                                    <button type="button" x-show="!editHasFixedCode" @click="askStaticEdit()" class="px-3 py-1.5 bg-slate-600 hover:bg-slate-500 text-white text-xs font-bold rounded shadow transition"><i class="fas fa-font mr-1"></i> + Tetap</button>

                                    {{-- TOMBOL BARU: Kode Jabatan Edit --}}
                                    <button type="button" 
                                        x-show="editHasFixedCode" 
                                        :disabled="editData.pola.includes(editCurrentFixedCode)"
                                        @click="insertTagEdit(editCurrentFixedCode)" 
                                        :class="editData.pola.includes(editCurrentFixedCode) ? 'bg-slate-400 text-white cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow'"
                                        class="px-3 py-1.5 text-xs font-bold rounded transition">
                                        <i class="fas fa-thumbtack mr-1"></i> + Kode Jabatan (<span x-text="editCurrentFixedCode"></span>)
                                    </button>
                                </div>
                                <button type="button" x-show="editData.pola.length > 0" @click="editData.pola = ''" class="px-3 py-1.5 bg-red-900/40 hover:bg-red-600 text-red-300 hover:text-white text-xs font-bold rounded shadow transition">
                                    <i class="fas fa-trash-alt mr-1"></i> Kosongkan
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-[#112D4E] hover:bg-blue-900 text-white rounded-lg text-sm font-bold shadow">Update Formula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- MODAL JABATAN (ADD/EDIT) --}}
    {{-- ========================================== --}}
    <div x-show="jabatanModalOpen" x-cloak class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="jabatanModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="jabatanModalOpen = false"></div>
            <div x-show="jabatanModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-900" x-text="jabatanFormMode === 'add' ? 'Tambah Status Jabatan' : 'Edit Status Jabatan'"></h3>
                    <button type="button" @click="jabatanModalOpen = false" class="text-indigo-400 hover:text-indigo-600"><i class="fas fa-times text-xl"></i></button>
                </div>

                <form :action="getJabatanActionUrl()" method="POST">
                    @csrf
                    <input type="hidden" name="_method" x-model="jabatanMethod">

                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Nama Status Jabatan</label>
                            <input type="text" name="label_jabatan" x-model="jabatanData.label_jabatan" required placeholder="Contoh: Rektor, Dekan, Tata Usaha" class="w-full text-sm px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none bg-slate-50 transition">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Angka Tetap (Kode Fix)</label>
                            <input type="text" name="kode_dasar" x-model="jabatanData.kode_dasar" placeholder="Contoh: 00, 01, 7" class="w-full text-sm font-mono font-bold px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none bg-slate-50 transition placeholder-slate-300">
                            <p class="text-[10px] text-slate-500 mt-1.5 italic">*Opsional. Isi hanya jika jabatan ini wajib menggunakan kode angka bawaan dari Kemenag.</p>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Lingkup Wilayah</label>
                            <select name="tingkat_wilayah_id" x-model="jabatanData.tingkat_wilayah_id" class="w-full text-sm px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none bg-slate-50 transition">
                                <option value="">Global (Berlaku Semua Wilayah)</option>
                                @foreach($tingkatWilayahs as $tw) 
                                    <option value="{{ $tw->id }}">{{ $tw->nama }}</option> 
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1.5 italic">*Tentukan di mana opsi status jabatan ini akan dimunculkan.</p>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                        <button type="button" @click="jabatanModalOpen = false" class="px-5 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-md transition" x-text="jabatanFormMode === 'add' ? 'Simpan Jabatan' : 'Update Jabatan'"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= FLOATING SEARCH NAVIGATOR (VSCode Style) ================= --}}
    <div x-show="search && matches.length > 0" x-transition.opacity x-cloak
         class="fixed bottom-8 right-8 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1)] border border-slate-200 rounded-full px-5 py-2.5 flex items-center gap-4 z-50">
        
        <div class="text-xs font-bold text-slate-600 tracking-wide">
            <span x-text="currentMatchIndex + 1" class="text-blue-600"></span> 
            <span class="text-slate-400 mx-1">dari</span> 
            <span x-text="matches.length"></span>
        </div>
        
        <div class="w-[1px] h-4 bg-slate-200"></div>
        
        <div class="flex items-center gap-2">
            <button @click="prevMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition" title="Sebelumnya (Shift + Enter)">
                <i class="fas fa-chevron-up text-xs"></i>
            </button>
            <button @click="nextMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition" title="Selanjutnya (Enter)">
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style> [x-cloak] { display: none !important; } </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
Alpine.data('formulaBuilder', () => ({
            tab: 'builder',
            formulaInput: '',
            
            // Variabel Penampung Form
            namaRumus: '',
            selectedLevel: '',
            selectedWilayahId: '',
            selectedJabatan: '',
            
            // Variabel Fitur Live Search VS Code Style
            activePeriode: '{{ $periodes->first()->id ?? '' }}',
            search: '',
            matches: [],
            currentMatchIndex: 0,
            scrollTimeout: null,
            
            allJabatans: window.refJabatanDatabase || [],
            filteredJabatans: [],

            // LOGIKA SEQUENTIAL FORM 
            get isEselonEnabled() { return (this.namaRumus || '').trim().length > 0; },
            get isWilayahEnabled() { return this.isEselonEnabled && this.selectedLevel !== ''; },
            get isJabatanEnabled() { return this.isWilayahEnabled && this.selectedWilayahId !== ''; },
            get isWorkspaceEnabled() { return this.isWilayahEnabled && this.selectedWilayahId !== ''; },

            get hasFixedCode() {
                if (!this.selectedJabatan) return false;
                const jab = this.allJabatans.find(j => j.id == this.selectedJabatan);
                return jab && jab.kode_dasar ? true : false;
            },

            get currentFixedCode() {
                if (!this.selectedJabatan) return '';
                const jab = this.allJabatans.find(j => j.id == this.selectedJabatan);
                return jab ? (jab.kode_dasar || '') : '';
            },

            // 🟢 LOGIKA FIX CODE UNTUK TAB EDIT
            get editHasFixedCode() {
                if (!this.editData.ref_jabatan_satker_id) return false;
                const jab = this.allJabatans.find(j => j.id == this.editData.ref_jabatan_satker_id);
                return jab && jab.kode_dasar ? true : false;
            },

            get editCurrentFixedCode() {
                if (!this.editData.ref_jabatan_satker_id) return '';
                const jab = this.allJabatans.find(j => j.id == this.editData.ref_jabatan_satker_id);
                return jab ? (jab.kode_dasar || '') : '';
            },

            allRumus: window.rumusDatabase || [],

            // ==========================================
            // LOGIKA PELACAK HIERARKI & ANGKA TETAP
            // ==========================================
            
            // 1. Fungsi Baku Estimasi Panjang (Eselon - 1 * 2)
            getParentLength(eselon) {
                eselon = parseInt(eselon);
                if (isNaN(eselon) || eselon <= 1) return 0;
                return (eselon - 1) * 2; 
            },

            // 2. FUNGSI UNTUK MENDETEKSI TOTAL EXTRA DIGIT DARI PARENT (TETAP ADA)
            getInheritedExtra(eselon, wilayahId) {
                eselon = parseInt(eselon);
                if (isNaN(eselon) || eselon <= 1) return 0;

                let parent = this.allRumus.find(r => parseInt(r.jenis_satker_id) === eselon - 1 && r.tingkat_wilayah_id == wilayahId);
                if (!parent) parent = this.allRumus.find(r => parseInt(r.jenis_satker_id) === eselon - 1 && (!r.tingkat_wilayah_id || r.tingkat_wilayah_id === 'all'));

                let extra = 0;
                if (parent) {
                    if (parent.ref_jabatan_satker_id) {
                        let jab = this.allJabatans.find(j => j.id == parent.ref_jabatan_satker_id);
                        if (jab && jab.kode_dasar) {
                            extra += String(jab.kode_dasar).trim().length;
                        }
                    }
                    if ((parent.pola || '').includes('[PARENT]')) {
                        extra += this.getInheritedExtra(eselon - 1, wilayahId);
                    }
                }
                return extra;
            },

            // 3. Hitung Preview Buat Baru
            get estimatedDigits() {
                let total = 0; let p = this.formulaInput || ''; let lvl = parseInt(this.selectedLevel) || 1;
                
                if (p.includes('[PARENT]')) total += this.getParentLength(lvl);
                
                const incRegex = /\[INC:(\d+)/g; let match;
                while ((match = incRegex.exec(p)) !== null) total += parseInt(match[1]);
                
                let clean = p.replace(/\[PARENT\]/g, '').replace(/\[INC:[^\]]+\]/g, '').trim();
                return total + clean.length;
            },

            // 4. Hitung Preview Modal Edit
            get editEstimatedDigits() {
                let total = 0; let p = this.editData.pola || ''; let lvl = parseInt(this.editData.jenis_satker_id) || 1;
                
                if (p.includes('[PARENT]')) total += this.getParentLength(lvl);
                
                const incRegex = /\[INC:(\d+)/g; let match;
                while ((match = incRegex.exec(p)) !== null) total += parseInt(match[1]);
                
                let clean = p.replace(/\[PARENT\]/g, '').replace(/\[INC:[^\]]+\]/g, '').trim();
                return total + clean.length;
            },

            init() {
                let sessionTab = '{{ session('tab') }}';
                let navType = window.performance.getEntriesByType('navigation')[0]?.type;
                if (sessionTab) { this.tab = sessionTab; } 
                else if (navType === 'reload') {
                    let localTab = localStorage.getItem('settingKodeTab');
                    if(localTab) this.tab = localTab; 
                }

                // RESET PENCARIAN SAAT GANTI TAB AGAR TIDAK BENTROK
                this.$watch('tab', value => { 
                    localStorage.setItem('settingKodeTab', value); 
                    this.search = '';
                    this.matches.forEach(row => row.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50/50', 'transition-all'));
                    this.matches = [];
                });

                this.filterJabatan();

                this.$watch('namaRumus', val => { if (!val || !val.trim()) { this.selectedLevel = ''; } });

                this.$watch('selectedLevel', val => {
                    if (!val) {
                        this.selectedWilayahId = ''; this.selectedJabatan = ''; this.formulaInput = '';
                    } else if (val === '1') {
                        this.selectedJabatan = '';
                        this.formulaInput = (this.formulaInput || '').replace(/\[PARENT\]/g, ''); 
                    }
                });

                this.$watch('selectedWilayahId', val => {
                    if (!val) { this.selectedJabatan = ''; this.formulaInput = ''; }
                    if (typeof this.updateTingkatWilayahId === "function") this.updateTingkatWilayahId();
                });

                this.$watch('selectedJabatan', val => {
                    if (val) {
                        const jab = this.allJabatans.find(j => j.id == val);
                        if (jab && jab.kode_dasar) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'info', title: 'Angka Tetap Tersedia',
                                    text: `Jabatan ini memiliki kode fix [${jab.kode_dasar}]. Silakan klik tombol "+ Kode Jabatan" di bawah untuk menyisipkannya.`,
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 5000
                                });
                            }
                        }
                    }
                });

                this.$watch('formulaInput', val => {
                    let preview = (val || '')
                        .replace(/\[PARENT\]/g, '<span class="bg-blue-100 text-blue-700 px-1 rounded font-mono">PARENT</span>')
                        .replace(/\[INC:(\d+),\s*START:(\d+)\]/g, '<span class="bg-emerald-100 text-emerald-700 px-1 rounded font-mono">+$1 Digit (Start: $2)</span>')
                        .replace(/\[INC:(\d+)\]/g, '<span class="bg-emerald-100 text-emerald-700 px-1 rounded font-mono">+$1 Digit Urut</span>');
                    
                    const previewElement = document.getElementById('formula_preview');
                    if (previewElement) {
                        previewElement.innerHTML = preview || '<span class="text-slate-300 italic">Preview kode akan muncul di sini...</span>';
                    }
                });

                // WATCHER UNTUK FORM EDIT
                this.$watch('editData.jenis_satker_id', val => {
                    if (val === '1') {
                        this.editData.ref_jabatan_satker_id = '';
                        this.editData.pola = (this.editData.pola || '').replace(/\[PARENT\]/g, ''); 
                    }
                });

                this.$watch('editData.ref_jabatan_satker_id', val => {
                    if (val) {
                        const jab = this.allJabatans.find(j => j.id == val);
                        if (jab && jab.kode_dasar) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'info', title: 'Angka Tetap Tersedia',
                                    text: `Jabatan ini memiliki kode fix [${jab.kode_dasar}]. Silakan klik tombol "+ Kode Jabatan" di bawah untuk menyisipkannya.`,
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4500
                                });
                            }
                        }
                    }
                });

                // 🟢 LOGIKA LIVE SEARCH UNIVERSAL (3 TAB)
                this.$watch('search', (val) => {
                    if (val && val.length > 0) {
                        clearTimeout(this.scrollTimeout);
                        this.scrollTimeout = setTimeout(() => {
                            let containerId = '';
                            let rowClass = '';
                            
                            // Deteksi sedang di tab mana
                            if (this.tab === 'builder') { containerId = 'rumusTableContainer'; rowClass = '.rumus-row'; }
                            else if (this.tab === 'jabatan') { containerId = 'jabatanTableContainer'; rowClass = '.jabatan-row'; }
                            else if (this.tab === 'manual') { containerId = 'satkerManualContainer'; rowClass = '.satker-row'; }

                            const container = document.getElementById(containerId);
                            if (!container) return;

                            const term = val.toLowerCase();
                            const rows = container.querySelectorAll(rowClass);
                            
                            // Bersihkan highlight lama
                            this.matches.forEach(row => row.classList.remove('ring-2', 'ring-blue-400', '!bg-blue-200', 'transition-all'));
                            this.matches = [];
                            this.currentMatchIndex = 0;

                            // Kumpulkan baris yang cocok
                            for (let i = 0; i < rows.length; i++) {
                                const row = rows[i];
                                if (row.offsetHeight > 0) { 
                                    const text = row.innerText || row.textContent;
                                    if (text.toLowerCase().includes(term)) {
                                        this.matches.push(row);
                                    }
                                }
                            }

                            if (this.matches.length > 0) {
                                this.scrollToMatch(0, containerId);
                            }
                        }, 300);
                    } else {
                        // Bersihkan saat search kosong
                        this.matches.forEach(row => row.classList.remove('ring-2', 'ring-blue-400', '!bg-blue-200', 'transition-all'));
                        this.matches = [];
                    }
                });
            },

            // 🟢 METODE SCROLL & HIGHLIGHT VS CODE STYLE
            scrollToMatch(index, containerId) {
                if (this.matches.length === 0) return;
                
                // Hapus highlight dari hasil yang sebelumnya aktif
                if (this.matches[this.currentMatchIndex]) {
                    this.matches[this.currentMatchIndex].classList.remove('ring-2', 'ring-blue-400', '!bg-blue-200', 'transition-all');
                }
                
                // Looping index (Jika next di hasil terakhir, kembali ke 1. Dan sebaliknya)
                if (index < 0) index = this.matches.length - 1;
                if (index >= this.matches.length) index = 0;
                
                this.currentMatchIndex = index;
                const target = this.matches[this.currentMatchIndex];
                
                // Tambahkan highlight ke target yang sedang dilihat (Gunakan !bg-blue-200 agar tembus <tr>)
                target.classList.add('ring-2', 'ring-blue-400', '!bg-blue-200', 'transition-all');

                // Gulir ke elemen tersebut
                const container = document.getElementById(containerId);
                const cRect = container.getBoundingClientRect();
                const mRect = target.getBoundingClientRect();
                
                container.scrollTo({
                    top: container.scrollTop + (mRect.top - cRect.top) - 40,
                    behavior: 'smooth'
                });
            },
            
            nextMatch() {
                let containerId = this.tab === 'builder' ? 'rumusTableContainer' : (this.tab === 'jabatan' ? 'jabatanTableContainer' : 'satkerManualContainer');
                this.scrollToMatch(this.currentMatchIndex + 1, containerId);
            },
            
            prevMatch() {
                let containerId = this.tab === 'builder' ? 'rumusTableContainer' : (this.tab === 'jabatan' ? 'jabatanTableContainer' : 'satkerManualContainer');
                this.scrollToMatch(this.currentMatchIndex - 1, containerId);
            },

            updateTingkatWilayahId() {
                const select = document.getElementById('wilayah_select_builder');
                const hiddenInput = document.getElementById('hidden_tingkat_wilayah_id');
                
                if (select && select.selectedIndex >= 0) {
                    const selectedOpt = select.options[select.selectedIndex];
                    hiddenInput.value = selectedOpt.getAttribute('data-tingkat') || '';
                } else {
                    hiddenInput.value = '';
                }
            },

            // FUNGSI MENCARI JABATAN
            filterJabatan() {
                this.selectedJabatan = '';
                
                // Jika kosong atau global, tampilkan semua jabatan
                if (!this.selectedWilayahId || this.selectedWilayahId === 'all') { 
                    this.filteredJabatans = this.allJabatans; 
                    return; 
                }

                let finalOptions = [];
                // Jabatan yang tidak terikat wilayah khusus
                const commonOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null && item.key_jabatan !== 'manajerial');

                if (this.selectedWilayahId == '1') { // 1 = Pusat
                    finalOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null);
                } else if (this.selectedWilayahId == '4') { // 4 = PTKN
                    const ptknOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id == 4 && item.parent_id === null);
                    finalOptions = [...ptknOptions, ...commonOptions];
                } else { // 2 = Kanwil, 3 = Kab/Kota
                    const wilayahOptions = this.allJabatans.filter(item => item.key_jabatan === 'jabatan_kanwil' || item.key_jabatan === 'jabatan_kotakab');
                    finalOptions = [...wilayahOptions, ...commonOptions];
                }

                // Buang duplikat jika ada
                const uniqueOptions = [];
                const seenIds = new Set();
                for (const opt of finalOptions) {
                    if (!seenIds.has(opt.id)) {
                        seenIds.add(opt.id);
                        uniqueOptions.push(opt);
                    }
                }

                this.filteredJabatans = uniqueOptions;
            },
            
            insertTag(tag) { this.formulaInput += tag; },

            // FUNGSI MENGHAPUS TAG / BALOK TERAKHIR (TOMBOL BACKSPACE)
            hapusBalokTerakhir() {
                if (!this.formulaInput) return;

                // 1. CEK DULU: Jika berakhiran dengan Kode Jabatan Tetap, hapus SEKALIGUS!
                if (this.hasFixedCode && this.currentFixedCode && this.formulaInput.endsWith(this.currentFixedCode)) {
                    this.formulaInput = this.formulaInput.slice(0, -this.currentFixedCode.length);
                    return;
                }

                // 2. CEK TAG KURUNG SIKU: Jika berakhiran dengan kurung tutup ']', hapus sampai kurung buka '['
                if (this.formulaInput.endsWith(']')) {
                    const lastOpenBracket = this.formulaInput.lastIndexOf('[');
                    if (lastOpenBracket !== -1) {
                        this.formulaInput = this.formulaInput.substring(0, lastOpenBracket); 
                        return;
                    }
                }
                
                // 3. DEFAULT: Hapus satu per satu karakter (untuk ketikan angka tetap biasa)
                this.formulaInput = this.formulaInput.slice(0, -1);
            },
            
            askIncrement() {
                Swal.fire({
                    title: 'Atur Nomor Urut (Increment)',
                    html: `<div class='text-left mt-2'><label class='block text-xs font-bold text-slate-700 uppercase mb-1'>Berapa Digit?</label><input type='number' id='swal-digit' class='swal2-input !mt-0' value='2' min='1'><label class='block text-xs font-bold text-slate-700 uppercase mt-4 mb-1'>Mulai dari Angka Berapa?</label><input type='number' id='swal-start' class='swal2-input !mt-0' placeholder='Contoh: 1, 11, 31, 61'><p class='text-[10px] text-slate-500 mt-1'>*Kosongkan jika ingin mulai dari 1</p></div>`,
                    focusConfirm: false, showCancelButton: true, confirmButtonText: 'Tambahkan ke Rumus',
                    preConfirm: () => {
                        const d = document.getElementById('swal-digit').value || 2;
                        const s = document.getElementById('swal-start').value;
                        return s ? `[INC:${d}, START:${s}]` : `[INC:${d}]`;
                    }
                }).then((result) => { if (result.isConfirmed) { this.insertTag(result.value); } });
            },

            askStatic() {
                Swal.fire({ title: 'Angka/Teks Tetap', input: 'text', inputPlaceholder: 'Contoh: 9, 01, 00', showCancelButton: true, confirmButtonText: 'Tambahkan' }).then((result) => { if (result.isConfirmed && result.value) { this.insertTag(result.value); } });
            },

            // ==========================================
            // LOGIKA MODAL EDIT RUMUS & FILTER
            // ==========================================
            editModalOpen: false,
            editData: { id: '', nama_rumus: '', pola: '', jenis_satker_id: '', tingkat_wilayah_id: '', ref_jabatan_satker_id: '' },
            filteredEditJabatans: [],

            getUpdateRoute(id) { return '{{ url("admin/setting-kode/rumus") }}/' + id; },

            filterEditJabatan() {
                this.editData.ref_jabatan_satker_id = '';
                this.runEditJabatanFilter();
            },

            runEditJabatanFilter() {
                if (!this.editData.tingkat_wilayah_id || this.editData.tingkat_wilayah_id === 'all') {
                    this.filteredEditJabatans = this.allJabatans;
                    return;
                }

                let finalOptions = [];
                const commonOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null && item.key_jabatan !== 'manajerial');

                if (this.editData.tingkat_wilayah_id == '1') { // Pusat
                    finalOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null);
                } else if (this.editData.tingkat_wilayah_id == '4') { // PTKN
                    const ptknOptions = this.allJabatans.filter(item => item.tingkat_wilayah_id == 4 && item.parent_id === null);
                    finalOptions = [...ptknOptions, ...commonOptions];
                } else { // Kanwil / KabKota
                    const wilayahOptions = this.allJabatans.filter(item => item.key_jabatan === 'jabatan_kanwil' || item.key_jabatan === 'jabatan_kotakab');
                    finalOptions = [...wilayahOptions, ...commonOptions];
                }

                const uniqueOptions = [];
                const seenIds = new Set();
                for (const opt of finalOptions) {
                    if (!seenIds.has(opt.id)) { seenIds.add(opt.id); uniqueOptions.push(opt); }
                }
                this.filteredEditJabatans = uniqueOptions;
            },

            openEditModal(rumus) {
                this.editData.id = rumus.id;
                this.editData.nama_rumus = rumus.nama_rumus;
                this.editData.jenis_satker_id = rumus.jenis_satker_id || 'all';
                this.editData.tingkat_wilayah_id = rumus.tingkat_wilayah_id || 'all';
                
                this.runEditJabatanFilter(); // Filter jabatan dropdown
                
                this.editData.ref_jabatan_satker_id = rumus.ref_jabatan_satker_id || '';
                this.editData.pola = rumus.pola;
                this.editModalOpen = true;
            },

            getEditPreview() {
                let val = this.editData.pola || '';
                let preview = val.replace(/\[PARENT\]/g, '<span class="bg-blue-100 text-blue-700 px-1 rounded font-mono">PARENT</span>').replace(/\[INC:(\d+),\s*START:(\d+)\]/g, '<span class="bg-emerald-100 text-emerald-700 px-1 rounded font-mono">+$1 Digit (Start: $2)</span>').replace(/\[INC:(\d+)\]/g, '<span class="bg-emerald-100 text-emerald-700 px-1 rounded font-mono">+$1 Digit Urut</span>');
                return preview || '<span class="text-slate-300 italic">Preview kode akan muncul di sini...</span>';
            },

            insertTagEdit(tag) { this.editData.pola += tag; },

            hapusBalokEdit() {
                if (!this.editData.pola) return;
                
                // Menghapus FIX CODE sekaligus jika ada
                if (this.editHasFixedCode && this.editCurrentFixedCode && this.editData.pola.endsWith(this.editCurrentFixedCode)) {
                    this.editData.pola = this.editData.pola.slice(0, -this.editCurrentFixedCode.length);
                    return;
                }

                if (this.editData.pola.endsWith(']')) {
                    const lastOpenBracket = this.editData.pola.lastIndexOf('[');
                    if (lastOpenBracket !== -1) {
                        this.editData.pola = this.editData.pola.substring(0, lastOpenBracket); return;
                    }
                }
                this.editData.pola = this.editData.pola.slice(0, -1);
            },

            askIncrementEdit() {
                Swal.fire({
                    title: 'Atur Nomor Urut (Edit)',
                    html: `<div class='text-left mt-2'><label class='block text-xs font-bold text-slate-700 uppercase mb-1'>Berapa Digit?</label><input type='number' id='swal-digit-edit' class='swal2-input !mt-0' value='2' min='1'><label class='block text-xs font-bold text-slate-700 uppercase mt-4 mb-1'>Mulai dari Angka Berapa?</label><input type='number' id='swal-start-edit' class='swal2-input !mt-0' placeholder='Contoh: 1, 11, 31'><p class='text-[10px] text-slate-500 mt-1'>*Kosongkan jika ingin mulai dari 1</p></div>`,
                    focusConfirm: false, showCancelButton: true, confirmButtonText: 'Tambahkan ke Rumus',
                    preConfirm: () => {
                        const d = document.getElementById('swal-digit-edit').value || 2;
                        const s = document.getElementById('swal-start-edit').value;
                        return s ? `[INC:${d}, START:${s}]` : `[INC:${d}]`;
                    }
                }).then((result) => { if (result.isConfirmed) this.insertTagEdit(result.value); });
            },

            askStaticEdit() {
                Swal.fire({ title: 'Angka/Teks Tetap (Edit)', input: 'text', inputPlaceholder: 'Contoh: 9, 01, 00', showCancelButton: true, confirmButtonText: 'Tambahkan' }).then((result) => { if (result.isConfirmed && result.value) this.insertTagEdit(result.value); });
            },

            // ==========================================
            // LOGIKA TAB & MODAL JABATAN BARU
            // ==========================================
            jabatanModalOpen: false,
            jabatanFormMode: 'add',
            jabatanData: { id: '', label_jabatan: '', kode_dasar: '', tingkat_wilayah_id: '' },

            get jabatanMethod() { return this.jabatanFormMode === 'edit' ? 'PUT' : 'POST'; },

            openAddJabatanModal() {
                this.jabatanFormMode = 'add';
                this.jabatanData = { id: '', label_jabatan: '', kode_dasar: '', tingkat_wilayah_id: '' };
                this.jabatanModalOpen = true;
            },

            openEditJabatanModal(jb) {
                this.jabatanFormMode = 'edit';
                this.jabatanData = { 
                    id: jb.id, 
                    label_jabatan: jb.label_jabatan, 
                    kode_dasar: jb.kode_dasar || '', 
                    tingkat_wilayah_id: jb.tingkat_wilayah_id || '' 
                };
                this.jabatanModalOpen = true;
            },

            getJabatanActionUrl() {
                if(this.jabatanFormMode === 'add') {
                    return '{{ url("admin/setting-kode/jabatan") }}';
                } else {
                    return '{{ url("admin/setting-kode/jabatan") }}/' + this.jabatanData.id;
                }
            },

            // FUNGSI SUBMIT FORM MASS UPDATE DI TAB MANUAL
            async updateManualBulk(event) {
                const form = event.target;
                const inputs = form.querySelectorAll('.kode-input, .data-kode-edit');
                const changedData = {};
                let hasChanges = false;
                
                inputs.forEach(input => {
                    if (input.value !== input.getAttribute('data-original')) {
                        const match = input.name.match(/\[(.*?)\]/);
                        if (match && match[1]) {
                            changedData[match[1]] = input.value;
                            hasChanges = true;
                        }
                    }
                });

                if (!hasChanges) {
                    Swal.fire('Info', 'Tidak ada perubahan kode yang Anda lakukan.', 'info');
                    return;
                }

                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn.innerHTML;

                const { isConfirmed } = await Swal.fire({
                    title: 'Simpan Perubahan Massal?',
                    text: "Semua kode anak (bawahan) akan otomatis disesuaikan dengan kode induk baru (Cascade). Lanjutkan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#112D4E',
                    confirmButtonText: 'Ya, Simpan & Cascade'
                });

                if (!isConfirmed) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

                try {
                    const payload = { kode_satker_baru: changedData };
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false }).then(() => {
                            window.location.reload(); 
                        });
                    } else {
                        Swal.fire('Gagal', result.message || 'Terjadi kesalahan validasi atau kode ganda.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Terjadi kesalahan jaringan/sistem saat memproses bulk update.', 'error');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        }));
    });
</script>

<script>
    @if(session('success'))
        Swal.fire({ title: 'Berhasil!', text: '{{ session("success") }}', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    @endif
    @if(session('error'))
        Swal.fire({ title: 'Gagal!', text: '{{ session("error") }}', icon: 'error', toast: true, position: 'top-end', showConfirmButton: false, timer: 4000 });
    @endif

    function confirmDelete(btn) {
        Swal.fire({
            title: 'Yakin hapus data ini?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then((result) => { if (result.isConfirmed) btn.closest('form').submit(); })
    }

    function confirmApply(btn) {
        Swal.fire({
            title: 'Aktifkan Formula?',
            text: "Sistem akan mulai menggunakan pola ini untuk men-generate kode Satker baru.",
            icon: 'info', showCancelButton: true, confirmButtonColor: '#112D4E', confirmButtonText: 'Ya, Terapkan!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
                btn.closest('form').submit(); 
            }
        })
    }
</script>
@endpush