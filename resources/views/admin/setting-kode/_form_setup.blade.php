<h3 class="text-lg font-bold text-[#112D4E] mb-4"><i class="fas {{ $icon }} mr-2"></i>{{ $title }} Baru</h3>
<form action="{{ route('admin.setting-kode.storeRumus') }}" method="POST" class="bg-gray-50 p-4 rounded-lg border">
    @csrf
    <input type="hidden" name="is_auto_number" value="{{ $isAuto ? '1' : '0' }}">
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-xs font-bold text-gray-700">1. Filter Wilayah</label>
            <select name="tingkat_wilayah_id" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm">
                <option value="">-- Semua Wilayah --</option>
                @foreach($tingkatWilayahs as $w) 
                    <option value="{{ $w->id }}">{{ $w->nama }}</option> 
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">2. Filter Level Satker</label>
            <select name="jenis_satker_id" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm">
                <option value="">-- Semua Level --</option>
                @foreach($jenisSatkers as $js) <option value="{{ $js->id }}">{{ $js->nama }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-700">3. Filter Jabatan</label>
            <select name="ref_jabatan_satker_id" class="mt-1 block w-full rounded-md p-2 border bg-white text-sm">
                <option value="">-- Semua Jabatan --</option>
                @foreach($refJabatans as $ref) <option value="{{ $ref->id }}">{{ $ref->label_jabatan }}</option> @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-white p-3 border rounded">
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
            <input type="number" name="digit_auto_number" value="2" class="mt-1 block w-full rounded p-2 border text-sm border-amber-500 font-mono font-bold text-[#112D4E]">
        </div>
        @endif
    </div>

    <button type="submit" class="mt-4 bg-[#112D4E] hover:bg-[#0D2440] text-white px-4 py-2 rounded-md text-sm font-bold transition shadow-sm w-full sm:w-auto">
        <i class="fas fa-save mr-1"></i> Simpan Setup
    </button>
</form>