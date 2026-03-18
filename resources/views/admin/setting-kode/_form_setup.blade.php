@php 
    $prefix = $isAuto ? 'auto' : 'fix'; 
    $activeVar = $isAuto ? 'activeAutoSetup' : 'activeFixSetup';
@endphp

<h3 class="text-lg font-bold text-[#112D4E] mb-4"><i class="fas {{ $icon }} mr-2"></i>{{ $title }} Baru</h3>
<form action="{{ route('admin.setting-kode.storeRumus') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border">
    @csrf
    <input type="hidden" name="is_auto_number" value="{{ $isAuto ? '1' : '0' }}">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-xs font-bold text-gray-700">1. Filter Wilayah</label>
            <select name="tingkat_wilayah_id" x-model="{{ $prefix }}_wilayah" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm focus:ring-[#112D4E]">
                <option value="">-- Semua Wilayah --</option>
                @foreach($tingkatWilayahs as $w) <option value="{{ $w->id }}">{{ $w->nama }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">2. Filter Level Satker</label>
            <select name="jenis_satker_id" x-model="{{ $prefix }}_jenis" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm focus:ring-[#112D4E]">
                <option value="">-- Semua Level --</option>
                @foreach($jenisSatkers as $js) <option value="{{ $js->id }}">{{ $js->nama }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">3. Filter Jabatan</label>
            <select name="ref_jabatan_satker_id" x-model="{{ $prefix }}_ref" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm focus:ring-[#112D4E]">
                <option value="">-- Semua Jabatan --</option>
                @foreach($refJabatans as $ref) <option value="{{ $ref->id }}">{{ $ref->label_jabatan }}</option> @endforeach
            </select>
        </div>
    </div>

    <div x-show="{{ $activeVar }}" x-cloak x-transition.opacity duration.500ms class="mb-5 p-3 bg-blue-50 border border-blue-300 rounded-lg shadow-inner">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-info text-lg"></i>
                </div>
                <div class="ml-3">
                    <span class="font-bold text-blue-900 text-sm block">Perhatian: Ada Setup yang Sedang Aktif!</span>
                    <p class="text-[11px] text-blue-700 leading-tight">Jika Anda membuat setup baru untuk kombinasi 3 filter di atas, maka setup lama ini akan otomatis terganti (dinonaktifkan).</p>
                </div>
            </div>
            <div class="bg-white p-2 rounded shadow-sm border border-blue-200 text-xs w-full sm:w-auto flex-shrink-0">
                <div class="font-bold text-[#112D4E] border-b pb-1 mb-1 border-gray-100" x-text="{{ $activeVar }}?.nama_rumus"></div>
                <div class="flex justify-between items-center gap-4">
                    <span class="text-gray-500">Pola/Rumus:</span>
                    <span class="font-mono font-bold text-blue-700" x-text="{{ $activeVar }}?.pola"></span>
                </div>
                <div class="flex justify-between items-center gap-4 mt-0.5">
                    <span class="text-gray-500">Awalan/Fix:</span>
                    <span class="font-bold text-gray-800" x-text="{{ $activeVar }}?.kode_awalan || '-'"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-3 border rounded shadow-sm">
        <div>
            <label class="block text-xs font-bold text-gray-700">Nama Setup (Identitas)</label>
            <input type="text" name="nama_rumus" placeholder="Cth: Setup Wadir PTKN" class="mt-1 block w-full rounded p-2 border text-sm" required>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">Naming (Default Nama)</label>
            <input type="text" name="default_nama_satker" placeholder="Cth: Wakil Rektor Bidang" class="mt-1 block w-full rounded p-2 border text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">Kode Sisipan/Awalan</label>
            <input type="text" name="kode_awalan" placeholder="{{ $isAuto ? 'Cth: 9' : 'Cth: 00' }}" class="mt-1 block w-full rounded p-2 border text-sm border-blue-400 font-mono font-bold text-[#112D4E]">
        </div>
        @if($isAuto)
        <div>
            <label class="block text-xs font-bold text-gray-700">Jml Digit Increment</label>
            <input type="number" name="digit_auto_number" value="2" min="1" max="5" class="mt-1 block w-full rounded p-2 border text-sm border-amber-500 font-mono font-bold text-[#112D4E]">
        </div>
        @endif
    </div>

    <button type="submit" class="mt-4 bg-[#112D4E] hover:bg-[#0D2440] text-white px-4 py-2 rounded-md text-sm font-bold transition shadow-sm w-full sm:w-auto">
        <i class="fas fa-save mr-1"></i> Simpan Setup
    </button>
</form>