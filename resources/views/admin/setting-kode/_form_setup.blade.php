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

    <div class="mb-5 transition-all duration-300">
        <div x-show="{{ $activeVar }} !== null" x-cloak x-transition.opacity class="p-3 bg-blue-50 border border-blue-300 rounded-lg shadow-inner flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start">
                <div class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-exclamation-triangle text-sm"></i>
                </div>
                <div class="ml-3">
                    <span class="font-bold text-blue-900 text-sm block">Perhatian: Ada Setup yang Sedang Aktif!</span>
                    <p class="text-[11px] text-blue-700 leading-tight">Jika Anda membuat setup baru untuk filter di atas, maka setup lama ini akan terganti (dinonaktifkan).</p>
                </div>
            </div>
            <div class="bg-white p-2 rounded shadow-sm border border-blue-200 text-[10px] w-full sm:w-auto flex-shrink-0 min-w-[200px]">
                <div class="font-bold text-[#112D4E] border-b pb-1 mb-1 border-gray-100 truncate" x-text="{{ $activeVar }}?.nama_rumus"></div>
                <div class="flex justify-between items-center gap-4">
                    <span class="text-gray-500">Pola Lama:</span>
                    <span class="font-mono font-bold text-blue-700" x-text="{{ $activeVar }}?.pola"></span>
                </div>
                <div class="flex justify-between items-center gap-4 mt-0.5">
                    <span class="text-gray-500">Awalan/Fix:</span>
                    <span class="font-bold text-gray-800" x-text="{{ $activeVar }}?.kode_awalan || '-'"></span>
                </div>
            </div>
        </div>

        <div x-show="{{ $activeVar }} === null" x-cloak x-transition.opacity class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg shadow-inner flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-start">
                <div class="bg-emerald-500 text-white rounded-full w-8 h-8 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-info-circle text-sm"></i>
                </div>
                <div class="ml-3">
                    <span class="font-bold text-emerald-900 text-sm block">Kombinasi Baru (Belum Ada Setup Rumus)</span>
                    <p class="text-[11px] text-emerald-700 leading-tight">Jika Anda tidak membuat setup rumus, maka sistem secara otomatis akan menggunakan referensi bawaan di samping ini:</p>
                </div>
            </div>
            <div class="bg-white p-2 rounded shadow-sm border border-emerald-200 text-xs w-full sm:w-auto flex-shrink-0 min-w-[200px]">
                <span class="text-gray-500 block text-[10px] mb-0.5 border-b border-gray-100 pb-0.5">Kode Dasar Bawaan Sistem (Saat Ini):</span>
                <span class="font-mono font-bold text-emerald-700 text-sm flex items-center mt-1">
                    <i class="fas fa-hashtag text-[10px] mr-1.5 opacity-50"></i>
                    <span x-text="getDefaultCode('{{ $prefix }}')"></span>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-white p-3 border rounded shadow-sm relative z-10">
        <div class="md:col-span-2 lg:col-span-1">
            <label class="block text-xs font-bold text-gray-700">Nama Setup (Identitas)</label>
            <input type="text" name="nama_rumus" placeholder="Cth: Setup Wadir PTKN" class="mt-1 block w-full rounded p-2 border text-sm" required>
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

    {{-- FITUR BARU: NAMA SATKER OTOMATIS & MAPPING --}}
    <div class="mt-4 p-4 border border-blue-200 rounded-xl bg-blue-50/30">
        <div class="flex items-center gap-2 mb-3">
            <input type="checkbox" id="is_auto_name_{{ $prefix }}" name="is_auto_name" value="1" x-model="isAutoName" class="w-4 h-4 text-blue-600 rounded border-slate-300">
            <label for="is_auto_name_{{ $prefix }}" class="text-sm font-bold text-slate-700 cursor-pointer">Aktifkan Penamaan Satker Otomatis</label>
        </div>

        <div x-show="isAutoName" x-cloak x-transition class="space-y-4 border-t border-blue-100 pt-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Teks Nama Dasar (Misal: "Biro ", "Fakultas ")</label>
                    <input type="text" name="base_auto_name" x-model="baseName" placeholder="Gunakan spasi di akhir jika perlu" class="w-full rounded-lg border-slate-200 text-sm">
                </div>
                <div class="flex items-center pt-6">
                    <input type="checkbox" id="is_name_locked_{{ $prefix }}" name="is_name_locked" value="1" x-model="isLocked" class="w-4 h-4 text-blue-600 rounded border-slate-300">
                    <label for="is_name_locked_{{ $prefix }}" class="ml-2 text-sm font-semibold text-slate-700 cursor-pointer">Kunci teks dasar ini? (User tidak bisa hapus)</label>
                </div>
            </div>

            <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-xs font-bold text-slate-700">Pemetaan per Ujung Kode (Opsional)</label>
                    <button type="button" @click="addMapItem()" class="text-xs bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-2 py-1 rounded font-bold transition">
                        + Tambah
                    </button>
                </div>
                
                <p class="text-[10px] text-slate-500 mb-3">Jika dikosongkan, semua ujung kode akan menggunakan "Teks Nama Dasar".</p>

                <template x-for="(item, index) in customMaps" :key="index">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" x-model="item.code" placeholder="01" class="w-20 rounded border-slate-200 text-sm py-1.5 px-2 font-mono" required>
                        <span class="text-slate-400 text-xs"><i class="fas fa-arrow-right"></i></span>
                        <input type="text" x-model="item.name" placeholder="Fakultas Tarbiyah" class="flex-1 rounded border-slate-200 text-sm py-1.5 px-2" required>
                        <button type="button" @click="removeMapItem(index)" class="p-1.5 text-red-500 hover:bg-red-50 rounded"><i class="fas fa-times"></i></button>
                    </div>
                </template>
                
                <input type="hidden" name="custom_names_map" :value="getMapObject()">
            </div>
        </div>
    </div>

    <button type="submit" class="mt-4 bg-[#112D4E] hover:bg-[#0D2440] text-white px-4 py-2 rounded-md text-sm font-bold transition shadow-sm w-full sm:w-auto">
        <i class="fas fa-save mr-1"></i> Simpan Setup
    </button>
</form>

{{-- Alpine State Khusus Form Tambah (Menggunakan Window agar kebal error timing) --}}
<script>
    window.autoNameFormTambah = function() {
        return {
            isAutoName: false,
            baseName: '',
            isLocked: false,
            customMaps: [],

            addMapItem() { this.customMaps.push({ code: '', name: '' }); },
            removeMapItem(index) { this.customMaps.splice(index, 1); },
            getMapObject() {
                let obj = {};
                this.customMaps.forEach(m => { if (m.code && m.name) obj[m.code] = m.name; });
                return obj;
            }
        }
    }
</script>