@extends('layouts.admin')
@push('styles')
    <style>
        .fa-spin {
            display: inline-block;
            animation: fa-spin 2s infinite linear;
        }

        @keyframes fa-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(359deg);
            }
        }
    </style>
    <style>
        /* Container utama TomSelect agar tingginya pas dengan input lain */
        .ts-wrapper.single .ts-control {
            padding: 0.65rem 1rem !important;
            border-radius: 0.75rem !important;
            /* Rounded-xl */
            background-color: #f8fafc !important;
            /* bg-slate-50 */
            border: 1px solid #e2e8f0 !important;
            /* border-slate-200 */
            font-size: 0.875rem !important;
            /* text-sm */
            box-shadow: none !important;
        }

        /* Tampilan saat diklik (Focus) */
        .ts-wrapper.single.focus .ts-control {
            border-color: #3b82f6 !important;
            /* focus:border-blue-500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
            /* ring-blue-500/20 */
        }

        /* Panel Dropdown (pilihan yang muncul) */
        .ts-dropdown {
            border-radius: 0.75rem !important;
            margin-top: 5px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999 !important;
        }

        /* Mengatur teks panjang agar turun ke bawah (Wrap) */
        .ts-dropdown .option,
        .ts-control {
            white-space: normal !important;
            /* Biar turun ke bawah */
            word-break: break-word !important;
            line-height: 1.5 !important;
        }

        /* Style untuk tiap baris pilihan */
        .ts-dropdown .option {
            padding: 0.75rem 1rem !important;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Warna saat cursor diarahkan (Hover) */
        .ts-dropdown .active {
            background-color: #eff6ff !important;
            /* bg-blue-50 */
            color: #1e40af !important;
            /* text-blue-800 */
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endpush
@section('title', 'Master Satuan Kerja')

    {{-- Ganti baris ini --}}
@section('content')
    <div x-data="{
        search: '',
        activePeriode: '{{ $periodes->first()->id ?? '' }}',
    
        isMatch(nama, kode) {
            if (!this.search) return false;
            let s = this.search.toLowerCase();
            return nama.includes(s) || kode.includes(s);
        },
    
        hasMatchingChild(children) {
            if (!children || children.length === 0) return false;
    
            for (let child of children) {
                if (this.isMatch(child.nama_satker.toLowerCase(), child.kode_satker.toLowerCase())) {
                    return true;
                }
    
                if (this.hasMatchingChild(child.children_recursive)) {
                    return true;
                }
            }
    
            return false;
        },

        init() {
            this.$watch('search', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        // querySelector secara natural akan selalu mengambil elemen PERTAMA dari atas
                        let firstMatch = document.querySelector('.satker-search-item:not([style*=\'display: none\'])');
                        if (firstMatch) {
                            // Scroll ke elemen tersebut dengan posisi start (atas)
                            firstMatch.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    });
                }
            });
        }
    }">

        {{-- ================= HEADER ================= --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">
                    Satuan Kerja
                </h2>
                <p class="text-sm text-slate-500">
                    Kelola data satuan kerja Kementerian Agama
                </p>
            </div>
        </div>


        {{-- ================= TABS PERIODE ================= --}}
        <div x-show="!search" class="flex items-center border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach ($periodes as $pe)
                <button type="button" @click="activePeriode = '{{ $pe->id }}'"
                    :class="activePeriode == '{{ $pe->id }}' ?
                        'border-blue-600 text-blue-600' :
                        'border-transparent text-slate-400 hover:text-slate-600'"
                    class="px-6 py-3 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none">
                    {{ $pe->nama_periode }}
                </button>
            @endforeach
        </div>


        {{-- ================= CONTAINER ================= --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-gray-100 bg-white">

                <div class="flex items-center justify-between gap-3">

                    {{-- SEARCH --}}
                    <div class="relative flex-1 max-w-xs sm:max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-xs"></i>
                        </span>
                        <input type="text" x-model.debounce.300ms="search" placeholder="Cari nama / kode..."
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                    </div>

                    {{-- BUTTON TAMBAH --}}
                    <button type="button" onclick="toggleModal('modalTambahSatker')"
                        class="bg-[#112D4E] hover:bg-blue-900 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm whitespace-nowrap">
                        <i class="fas fa-plus text-[10px]"></i>
                        <span class="hidden sm:inline">Tambah Satker</span>
                    </button>

                </div>
            </div>

            {{-- ===================================== --}}
            {{-- MODE A : HIRARKI (SEARCH KOSONG) --}}
            {{-- ===================================== --}}
            <div x-show="!search" class="px-4 sm:px-6 py-4 space-y-2">

                @forelse ($satkers as $satker)
                    <div x-show="activePeriode == '{{ $satker->periode_id }}'">
                        @include('admin.satker._item_hirarki', [
                            'item' => $satker,
                            'isSearching' => false,
                        ])
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-sm italic">
                        Belum ada data.
                    </div>
                @endforelse

            </div>

            <div x-show="search" x-cloak class="px-4 sm:px-6 py-4 
            max-h-[65vh] overflow-y-auto">

                {{-- <div class="mb-4 text-xs font-semibold text-slate-400 uppercase tracking-widest sticky top-0 bg-white z-10 pb-2">
                    
                </div> --}}

                <div class="space-y-2">

                    @foreach ($allSatkersFlat as $item)
                        <div x-show="
                    isMatch(
                        @js(strtolower($item->nama_satker)),
                        @js(strtolower($item->kode_satker))
                    )
                    ||
                    hasMatchingChild(@js($item->childrenRecursive))
                "
                            class="satker-search-item border border-slate-100 rounded-xl p-1 shadow-sm">
                            @include('admin.satker._item_hirarki', [
                                'item' => $item,
                                'isSearching' => true,
                            ])
                        </div>
                    @endforeach

                </div>

            </div>

        </div>
    </div>

    </div>

    {{-- Modal Tambah Satker --}}
    <div id="modalTambahSatker" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Background Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                onclick="toggleModal('modalTambahSatker')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Tambah Satker</h3>
                        <p class="text-xs text-slate-500">Tambahkan satuan kerja baru ke dalam sistem</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalTambahSatker')"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form action="{{ route('admin.satker.store') }}" method="POST" class="p-6 space-y-5 bg-white">
                    @csrf
                    <input type="hidden" name="jenis_satker_id" id="hidden_jenis_satker_id" disabled>
                    <input type="hidden" name="ref_jabatan_satker_id" id="ref_jabatan_satker_id">
                    <input type="hidden" name="parent_satker_id" id="hidden_parent_satker_id" disabled>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenis/Level Satker</label>
                        <select name="jenis_satker_id" id="jenis_satker_id" onchange="filterParent()" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Jenis Satker</option>
                            @foreach ($jenisSatkers as $js)
                                <option value="{{ $js->id }}">{{ $js->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="parent_container" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Satker Induk</label>
                        <select name="parent_satker_id" id="parent_satker_id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Cari atau pilih Satker Induk...</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}" data-eselon="{{ $p->jenis_satker_id }}"
                                    data-wilayah="{{ $p->wilayah_id }}" data-periode="{{ $p->periode_id }}">
                                    {{-- Pastikan data-periode ada --}}
                                    {{ $p->kode_satker }} - {{ $p->nama_satker }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Wilayah</label>
                        <select name="wilayah_id" id="wilayah_id" onchange="handleWilayahChange()" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tambahkan ID 'container_status_jabatan' dan class 'hidden' --}}
                    <div id="container_status_jabatan" class="hidden space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Status Jabatan</label>
                            <select name="tanpa_jabatan" id="tanpa_jabatan" onchange="handleJabatanChange()"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">-- Pilih Status Jabatan --</option>
                                {{-- Isi akan dihandle oleh JavaScript handleWilayahChange --}}
                            </select>
                        </div>

                        <div id="container_jabatan_fungsional" class="hidden mt-4">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilih Jabatan
                                Fungsional</label>
                            <select name="jabatan_id" id="jabatan_id"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach ($jabatan as $j)
                                    <option value="{{ $j->id }}" data-kode="{{ $j->kode_jabatan }}">
                                        {{ $j->kode_jabatan }} - {{ $j->nama_jabatan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Kontainer Kabupaten (Existing) --}}
                    <div id="container_kabupaten" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kabupaten / Kota</label>
                        <select name="kabupaten_id" id="kabupaten_id" onchange="updateNamaSatkerDariKabupaten()"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Kabupaten/Kota</option>
                            @foreach ($kabupaten as $kab)
                                <option value="{{ $kab->id }}">{{ $kab->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TAMBAHKAN INI: Kategori Unit (Tata Usaha / Non TU) --}}
                    <div id="container_kategori_unit" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                            Kategori Unit
                        </label>

                        <select name="kategori_unit" id="kategori_unit" onchange="handleKategoriUnitChange()"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">

                            <option value="">-- Pilih Kategori Unit --</option>

                        </select>
                    </div>

                    {{-- Picklist Khusus Jabatan Kota/Kab --}}
                    <div id="container_kategori_kotakab" class="hidden space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kategori Unit
                                Kota/Kab</label>
                            <select id="kategori_kotakab" onchange="handleKategoriKotaKabChange()"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">>
                                <option value="">-- Pilih Kategori --</option>
                            </select>
                        </div>

                        {{-- Picklist Spesifik Madrasah --}}
                        <div id="container_jenis_madrasah" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenis Madrasah</label>
                            <select id="jenis_madrasah" onchange="handleJenisMadrasahChange()"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">>
                                <option value="">-- Pilih Jenis Madrasah --</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kode Satker (Generate)</label>
                        <div class="flex gap-2 items-center" id="kode_container">
                            <input type="text" name="kode_satker" id="kode_satker" placeholder="Contoh: 0102"
                                required
                                class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">

                            <button type="button" onclick="generateSatkerCode(event)" title="Auto-generate kode"
                                class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition flex items-center justify-center min-w-[45px]">
                                <i class="fas fa-magic"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                        <label class="block text-[10px] font-bold text-blue-700 uppercase mb-1">Kode Satker Final (Akan
                            Tersimpan)</label>
                        <input type="text" name="kode_satker_full" id="kode_satker_full" readonly required
                            placeholder="Kode otomatis tergabung..."
                            class="w-full px-4 py-2 bg-white border border-blue-200 rounded-lg text-sm font-mono font-bold text-blue-600 outline-none">
                    </div>


                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Satker</label>
                        <input type="text" name="nama_satker" id="nama_satker" placeholder="Contoh: Biro Kepegawaian"
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>

                    <div id="container_periode">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Periode</label>
                        <select name="periode_id" id="periode_id" onchange="filterParent()" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Periode</option>
                            @foreach ($periodes as $pe)
                                <option value="{{ $pe->id }}">{{ $pe->nama_periode }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition resize-none"></textarea>
                    </div>

                    <input type="hidden" name="status_aktif" value="1">

                    {{-- Modal Footer --}}
                    <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalTambahSatker')"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-xl transition">Batal</button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#112D4E] hover:bg-blue-900 text-white text-sm font-bold rounded-xl shadow-md transition">
                            Tambah Satker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Satker --}}
    <div id="modalEditSatker" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                onclick="toggleModal('modalEditSatker')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Edit Satker</h3>
                        <p class="text-xs text-slate-500">Perbarui data satuan kerja</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalEditSatker')"
                        class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="formEditSatker" method="POST" class="p-6 space-y-5 bg-white">
                    @csrf
                    @method('PUT')

                    {{-- Jenis Satker --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenis/Level Satker</label>
                        <select name="jenis_satker_id" id="edit_jenis_satker_id" onchange="filterParentEdit()" required
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm focus:outline-none pointer-events-none select-none"
                            tabindex="-1">
                            @foreach ($jenisSatkers as $js)
                                <option value="{{ $js->id }}">{{ $js->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Satker Induk --}}
                    <div id="edit_parent_container" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Satker Induk</label>
                        <select name="parent_satker_id" id="edit_parent_satker_id"
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm focus:outline-none pointer-events-none select-none text-slate-500"
                            tabindex="-1">
                            <option value="">Pilih Satker Induk</option>
                            @foreach ($listAllSatkers as $p)
                                <option value="{{ $p->id }}" data-eselon="{{ $p->jenis_satker_id }}"
                                    data-periode="{{ $p->periode_id }}"> {{-- Tambahkan ini --}}
                                    {{ $p->kode_satker }} - {{ $p->nama_satker }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kode Satker (Readonly) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kode Satker</label>
                        <input type="text" name="kode_satker" id="edit_kode_satker" readonly
                            class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-sm text-slate-500 outline-none">
                    </div>

                    {{-- Nama Satker --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Satker</label>
                        <input type="text" name="nama_satker" id="edit_nama_satker" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>

                    {{-- Periode --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Periode</label>
                        <select name="periode_id" id="edit_periode_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach ($periodes as $pe)
                                <option value="{{ $pe->id }}">{{ $pe->nama_periode }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Wilayah --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Wilayah</label>
                        <select name="wilayah_id" id="edit_wilayah_id" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Keterangan (Baru) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" rows="3"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition"
                            placeholder="Keterangan tambahan (opsional)"></textarea>
                    </div>

                    {{-- Status Aktif (Baru) --}}
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <h4 class="text-sm font-bold text-slate-700">Status Aktif</h4>
                            <p class="text-[11px] text-slate-500">Satker aktif akan ditampilkan dalam daftar</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status_aktif" id="edit_status_aktif" value="1"
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#112D4E]">
                            </div>
                        </label>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="pt-4 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalEditSatker')"
                            class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-xl transition">Batal</button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-[#112D4E] hover:bg-blue-900 text-white text-sm font-bold rounded-xl shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div id="modalHapusSatker" class="fixed inset-0 z-[60] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                onclick="toggleModal('modalHapusSatker')"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                class="inline-block overflow-hidden text-center align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-8">
                {{-- Icon Warning --}}
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                </div>

                <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Satker</h3>
                <p class="text-sm text-slate-500 mb-8 leading-relaxed">
                    Apakah Anda yakin ingin menghapus satker <span id="hapus_nama_display"
                        class="font-bold text-slate-700"></span>? <br>
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                {{-- Form Hapus --}}
                <form id="formHapusSatker" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-center space-x-3">
                        <button type="button" onclick="toggleModal('modalHapusSatker')"
                            class="flex-1 px-5 py-3 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 px-5 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-red-200 transition">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Detail Satker --}}
    <div id="modalDetailSatker" class="fixed inset-0 z-50 hidden overflow-y-auto">
        {{-- Overlay: Gunakan flex agar modal selalu di tengah --}}
        <div class="flex items-center justify-center min-h-screen p-4 text-center">

            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                onclick="toggleModal('modalDetailSatker')"></div>

            {{-- Container Modal --}}
            <div
                class="relative inline-block w-full max-w-[1300px] overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">

                {{-- Header --}}
                <div
                    class="px-6 py-4 sm:px-8 sm:py-6 border-b border-gray-100 flex justify-between items-start bg-white sticky top-0 z-10">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800">Detail Satuan Kerja</h3>
                        <p class="text-xs sm:text-sm text-slate-500">Informasi lengkap dan daftar pejabat</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalDetailSatker')"
                        class="text-slate-400 hover:text-slate-600 transition p-2">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 sm:p-8 bg-white max-h-[70vh] overflow-y-auto">
                    {{-- Info Grid: Di mobile jadi 1 kolom, di desktop 3 kolom --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode
                                Satker</label>
                            <p id="detail_kode" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Eselon</label>
                            <div>
                                <span id="detail_eselon"
                                    class="bg-blue-900 text-white text-[9px] px-3 py-1 rounded-full font-bold uppercase inline-block">-</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama
                                Satker</label>
                            <p id="detail_nama" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Wilayah</label>
                            <p id="detail_wilayah" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label
                                class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                            <div id="detail_status_container"></div>
                        </div>
                    </div>

                    <hr class="border-gray-100 mb-8">

                    {{-- Filter & Action --}}
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-filter text-slate-400 text-sm"></i>
                            <h4 class="text-sm font-bold text-slate-700">Daftar Pejabat</h4>
                        </div>

                        <button type="button" onclick="openModalPenugasanDariDetail()"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-blue-100">
                            <i class="fas fa-user-plus mr-2"></i> Tambah Penugasan
                        </button>
                    </div>

                    {{-- Row Filter Inputs --}}
                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-3 mb-6">
                        <div class="sm:col-span-4 relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </span>
                            <input type="text" id="searchUserDetail" placeholder="Cari Nama / NIP..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-blue-400 transition">
                        </div>
                        <div class="sm:col-span-2">
                            <select
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg text-xs p-2 outline-none focus:border-blue-400 h-full">
                                <option>Semua Periode</option>
                            </select>
                        </div>
                    </div>

                    {{-- Table Pejabat --}}
                    <div class="border border-gray-100 rounded-xl">
                        <div class="overflow-x-auto">
                            <table class="min-w-4xl w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">No</th>
                                        <th class="px-4 py-3">Nama Pejabat</th>
                                        <th class="px-4 py-3">NIP</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Tampil Jabatan</th>
                                        <th class="px-4 py-3">Role</th>
                                        <th class="px-4 py-3">Jenis Penugasan</th>
                                        <th class="px-4 py-3">Tgl. Mulai</th>
                                        <th class="px-4 py-3">Tgl. Selesai</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_user_table_body" class="divide-y divide-gray-100">
                                    <tr>
                                        <td colspan="8" class="px-4 py-10 text-center text-slate-400 italic">
                                            Tidak ada pejabat di satker ini
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div
                    class="px-6 py-4 sm:px-8 sm:py-5 bg-slate-50/50 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <span id="detail_user_count" class="text-xs text-slate-400 order-2 sm:order-1">Menampilkan 0
                        pejabat</span>
                    <div class="flex w-full sm:w-auto order-1 sm:order-2">
                        <button type="button" onclick="toggleModal('modalDetailSatker')"
                            class="w-full sm:w-auto px-6 py-2 bg-white border border-slate-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-50 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modalTambahPenugasan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <input type="hidden" id="detail_satker_id">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalTambahPenugasan')">
            </div>
            <div
                class="inline-block overflow-hidden text-left bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Penugasan</h3>
                    <button onclick="toggleModal('modalTambahPenugasan')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="formTambahPenugasan" action="{{ route('admin.penugasan.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Satuan Kerja</label>
                            <div class="flex items-center px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-lg">
                                <i class="fas fa-building text-slate-400 mr-2 text-xs"></i>
                                {{-- Tempat menampilkan teks nama satker --}}
                                <span id="display_satker_nama"
                                    class="text-sm font-semibold text-slate-600">Memuat...</span>
                            </div>
                            {{-- Input hidden yang akan dikirim ke Form --}}
                            <input type="hidden" name="satker_id" id="form_penugasan_satker_id">
                            <input type="hidden" name="satker_kode" id="form_penugasan_satker_kode">
                        </div>
                        <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">User / Pegawai</label>
                        <div class="w-full">
                            <select name="user_id" id="select_pegawai_local" placeholder="Ketik Nama atau NIP Pegawai..." required></select>
                        </div>

                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                                <label class="block text-[10px] font-bold text-blue-600 uppercase tracking-wider">Hasil
                                    Verifikasi API</label>

                                <input type="text" id="res_nama" readonly
                                    placeholder="Nama pegawai akan muncul di sini..."
                                    class="w-full bg-transparent border-none p-0 text-sm font-bold text-slate-700 focus:ring-0 cursor-default placeholder:text-slate-400 placeholder:font-normal">

                                <div id="res_info_tambahan" class="text-[11px] text-slate-500 leading-tight min-h-[1rem]">
                                </div>

                                <input type="hidden" name="user_nip" id="hidden_nip">
                                <input type="hidden" name="name" id="hidden_nama">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role</label>
                                <select name="role_id" id="role_select" class="tom-select" required>
                                    <option value="">Pilih Role</option>
                                    @foreach ($roles as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="container_jenis_penugasan">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                    Jenis Penugasan
                                </label>
                                <select name="jenis_penugasan_id" id="jenis_penugasan_select" class="tom-select">
                                    <option value="">Pilih jenis</option>
                                    @foreach ($jenis_penugasans as $jp)
                                        <option value="{{ $jp->id }}">{{ $jp->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" required
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal
                                    Selesai</label>
                                <input type="date" name="tanggal_selesai"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
                            </div>
                        </div>
                        <div
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <span class="text-sm font-bold text-slate-700">Status Aktif</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="status_aktif" value="1" checked class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#112D4E]">
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="toggleModal('modalTambahPenugasan')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg hover:bg-blue-900 transition shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let timeout = null;
        const searchInput = document.querySelector('input[name="search"]');
        const loader = document.getElementById('page-loader');

        searchInput.addEventListener('keyup', function() {
            clearTimeout(timeout);

            // Opsional: Beri feedback visual langsung di input jika ingin
            timeout = setTimeout(() => {
                // Tampilkan loader sebelum submit
                if (loader) {
                    loader.classList.remove('hidden');
                }
                this.closest('form').submit();
            }, 500); // Tunggu 500ms setelah user berhenti mengetik
        });

        // Menangani jika user menekan tombol 'Enter' secara manual
        searchInput.closest('form').addEventListener('submit', function() {
            if (loader) {
                loader.classList.remove('hidden');
            }
        });
    </script>

    <script>
        // Konfigurasi Standar SweetAlert Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Notifikasi Sukses
        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: {!! json_encode(session('success')) !!}
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: {!! json_encode(session('error')) !!}
            });
        @endif

        // Notifikasi Error (Validasi atau Custom Error)
        @if ($errors->any())
            Toast.fire({
                icon: 'error',
                title: "Terjadi kesalahan!",
                text: {!! json_encode($errors->first()) !!}
            });
        @endif

        // Fungsi Toggle Modal (Sudah ada)
        function toggleModal(id) {

            const modal = document.getElementById(id);

            modal.classList.toggle('hidden');

            // jika modal ditutup
            if (modal.classList.contains('hidden')) {

                if (id === 'modalTambahSatker') {
                    resetTambahSatkerModal();
                }

            }
        }

        function openModalPenugasanDariDetail() {

            const satkerId = document.getElementById('detail_satker_id')?.value;
            const satkerKode = document.getElementById('detail_kode')?.innerText;
            const satkerNama = document.getElementById('detail_nama')?.innerText;

            const displayElement = document.getElementById('display_satker_nama');
            const hiddenIdInput = document.getElementById('form_penugasan_satker_id');
            const hiddenKodeInput = document.getElementById('form_penugasan_satker_kode');

            if (satkerId && displayElement && hiddenIdInput && hiddenKodeInput) {

                displayElement.innerText = `${satkerKode} - ${satkerNama}`;

                hiddenIdInput.value = satkerId;
                hiddenKodeInput.value = satkerKode;

                console.log("Menyiapkan penugasan untuk:");
                console.log("ID:", satkerId);
                console.log("Kode:", satkerKode);

            } else {
                console.warn("Gagal mengambil data Satker dari modal detail.");
            }

            const modalTambah = document.getElementById('modalTambahPenugasan');
            if (modalTambah) {
                modalTambah.classList.remove('z-50');
                modalTambah.classList.add('z-[60]');
            }

            toggleModal('modalTambahPenugasan');
        }


        document.addEventListener("DOMContentLoaded", function() {
            // Pastikan elemennya ada sebelum inisialisasi
            const selectPegawai = document.getElementById('select_pegawai_local');
            
            if (selectPegawai) {
                new TomSelect("#select_pegawai_local", {
                    valueField: 'id',
                    labelField: 'text',
                    searchField: ['id', 'nama'],
                    load: function(query, callback) {
                        if (!query.length) return callback();
                        
                        // 1. CARI LOKAL DULU (Cepat & Bisa pakai Nama)
                        fetch(`{{ route('admin.pegawai.search-local') }}?q=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    },
                    onChange: async function(value) {
                        if(!value) return;

                        const resNama = document.getElementById('res_nama');
                        const resInfo = document.getElementById('res_info_tambahan');
                        
                        // 1. Ambil elemen tombol submit
                        const submitBtn = document.querySelector('#modalTambahPenugasan button[type="submit"]');
                        
                        // 2. Kunci tombol simpan agar tidak di-klik terlalu cepat
                        if(submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memverifikasi...';
                        }
                        
                        Toast.fire({
                            icon: 'info',
                            title: 'Memverifikasi data ke server Kemenag...'
                        });

                        try {
                            const response = await fetch(`{{ url('admin/pegawai/search') }}?nip=${value}`);
                            if (!response.ok) throw new Error('Gagal menghubungi server');

                            const result = await response.json();

                            if (result.success && result.data && result.data.data) {
                                const d = result.data.data;
                                const namaValid = d.NAMA_LENGKAP || d.NAMA;

                                resNama.value = namaValid;
                                resInfo.innerText = `${d.GOL_RUANG || '-'} • ${d.TAMPIL_JABATAN || d.LEVEL_JABATAN || '-'}`;

                                const form = document.querySelector('#modalTambahPenugasan form');
                                const fields = [
                                    'NIP', 'NIP_BARU', 'NAMA', 'NAMA_LENGKAP', 'AGAMA', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR',
                                    'JENIS_KELAMIN', 'PENDIDIKAN', 'JENJANG_PENDIDIKAN', 'KODE_LEVEL_JABATAN', 'LEVEL_JABATAN',
                                    'PANGKAT', 'GOL_RUANG', 'TMT_CPNS', 'TMT_PANGKAT', 'MK_TAHUN', 'MK_BULAN', 'Gaji_Pokok',
                                    'TIPE_JABATAN', 'KODE_JABATAN', 'TAMPIL_JABATAN', 'TMT_JABATAN', 'KODE_SATUAN_KERJA',
                                    'SATKER_1', 'SATKER_2', 'KODE_SATKER_2', 'SATKER_3', 'KODE_SATKER_3', 'SATKER_4',
                                    'KODE_SATKER_4', 'SATKER_5', 'KODE_SATKER_5', 'KODE_GRUP_SATUAN_KERJA', 'GRUP_SATUAN_KERJA',
                                    'KETERANGAN_SATUAN_KERJA', 'STATUS_KAWIN', 'ALAMAT_1', 'ALAMAT_2', 'TELEPON', 'NO_HP',
                                    'EMAIL', 'KAB_KOTA', 'PROVINSI', 'KODE_POS', 'KODE_LOKASI', 'ISO', 'KODE_PANGKAT',
                                    'KETERANGAN', 'tmt_pangkat_yad', 'tmt_kgb_yad', 'USIA_PENSIUN', 'TMT_PENSIUN',
                                    'MK_TAHUN_1', 'MK_BULAN_1', 'NSM', 'NPSN', 'KODE_KUA', 'KODE_BIDANG_STUDI', 'BIDANG_STUDI',
                                    'STATUS_PEGAWAI', 'LAT', 'LON', 'SATKER_KELOLA', 'HARI_KERJA', 'EMAIL_DINAS'
                                ];

                                fields.forEach(field => {
                                    let hiddenInput = document.getElementById(`hidden_${field.toLowerCase()}`);
                                    if (!hiddenInput) {
                                        hiddenInput = document.createElement('input');
                                        hiddenInput.type = 'hidden';
                                        hiddenInput.id = `hidden_${field.toLowerCase()}`;
                                        hiddenInput.name = field.toLowerCase();
                                        form.appendChild(hiddenInput);
                                    }
                                    hiddenInput.value = d[field] ?? '';
                                });

                                Toast.fire({
                                    icon: 'success',
                                    title: 'Pegawai terverifikasi!',
                                    text: namaValid
                                });
                            } else {
                                throw new Error(result.message || 'Data API Kemenag tidak valid.');
                            }
                        } catch (error) {
                            console.error("Search Error:", error);
                            resNama.value = "";
                            resInfo.innerText = "";
                            
                            document.querySelectorAll('#modalTambahPenugasan form input[type="hidden"]').forEach(i => {
                                if(i.id.startsWith('hidden_')) i.value = '';
                            });

                            Toast.fire({
                                icon: 'error',
                                title: 'Pencarian Gagal',
                                text: error.message
                            });
                        } finally {
                            // 3. Kembalikan tombol simpan ke kondisi semula (Bisa diklik lagi)
                            if(submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                                submitBtn.innerHTML = 'Simpan';
                            }
                        }
                    }
                });
            }

            const tanpaJabatanSelect = document.getElementById('tanpa_jabatan');
            const namaSatkerInput = document.getElementById('nama_satker');

            if (tanpaJabatanSelect && namaSatkerInput) {
                tanpaJabatanSelect.addEventListener('change', function() {

                    if (this.value === 'tidak_ada') {
                        namaSatkerInput.value = 'Tidak Ada Jabatan';
                    }

                    if (this.value === 'ada') {
                        namaSatkerInput.value = '';
                    }

                });
            }

            // --- Kode Anda yang sudah ada ---
            const roleSelect = document.getElementById('role_select');
            const jenisContainer = document.getElementById('container_jenis_penugasan');
            const jenisSelect = document.getElementById('jenis_penugasan_select');

            function toggleJenisPenugasan() {
                let selectedText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase();

                if (selectedText && selectedText.includes('admin satker')) {
                    jenisContainer.style.display = 'none';
                    jenisSelect.removeAttribute('required');
                    jenisSelect.value = '';

                    if (jenisSelect.tomselect) {
                        jenisSelect.tomselect.clear();
                    }
                } else {
                    jenisContainer.style.display = '';
                    jenisSelect.setAttribute('required', 'required');
                }
            }

            roleSelect.addEventListener('change', toggleJenisPenugasan);
            toggleJenisPenugasan();

            const kabupatenSelect = document.getElementById('kabupaten_id');

            if (kabupatenSelect) {
                kabupatenSelect.addEventListener('change', updateNamaSatkerDariKabupaten);
            }
        });

        async function searchPegawaiByNip() {
            const nipInput = document.getElementById('search_nip');
            const btn = document.getElementById('btn_search_nip');
            const icon = document.getElementById('icon_search');
            const resNama = document.getElementById('res_nama');
            const resInfo = document.getElementById('res_info_tambahan');

            const nip = nipInput.value.trim();
            if (!nip) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Silakan masukkan NIP!'
                });
                return;
            }

            // Efek loading
            btn.disabled = true;
            icon.classList.add('fa-spin');
            nipInput.classList.add('bg-slate-50');

            try {
                const response = await fetch(`{{ url('admin/pegawai/search') }}?nip=${nip}`);
                if (!response.ok) throw new Error('Gagal menghubungi server');

                const result = await response.json();
                console.log("Isi Data API:", result);

                if (result.success && result.data && result.data.data) {
                    const d = result.data.data;

                    // Ambil nama valid
                    const namaValid = d.NAMA_LENGKAP || d.NAMA;

                    // Set value ke input readonly
                    resNama.value = namaValid;
                    resInfo.innerText = `${d.GOL_RUANG || '-'} • ${d.TAMPIL_JABATAN || d.LEVEL_JABATAN || '-'}`;

                    // Hidden input untuk form
                    const form = document.querySelector('#modalTambahPenugasan form');
                    if (!form) throw new Error("Form modal tidak ditemukan!");

                    const fields = [
                        'NIP', 'NIP_BARU', 'NAMA', 'NAMA_LENGKAP', 'AGAMA', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR',
                        'JENIS_KELAMIN', 'PENDIDIKAN', 'JENJANG_PENDIDIKAN', 'KODE_LEVEL_JABATAN', 'LEVEL_JABATAN',
                        'PANGKAT', 'GOL_RUANG', 'TMT_CPNS', 'TMT_PANGKAT', 'MK_TAHUN', 'MK_BULAN', 'Gaji_Pokok',
                        'TIPE_JABATAN', 'KODE_JABATAN', 'TAMPIL_JABATAN', 'TMT_JABATAN', 'KODE_SATUAN_KERJA',
                        'SATKER_1', 'SATKER_2', 'KODE_SATKER_2', 'SATKER_3', 'KODE_SATKER_3', 'SATKER_4',
                        'KODE_SATKER_4', 'SATKER_5', 'KODE_SATKER_5', 'KODE_GRUP_SATUAN_KERJA', 'GRUP_SATUAN_KERJA',
                        'KETERANGAN_SATUAN_KERJA', 'STATUS_KAWIN', 'ALAMAT_1', 'ALAMAT_2', 'TELEPON', 'NO_HP',
                        'EMAIL', 'KAB_KOTA', 'PROVINSI', 'KODE_POS', 'KODE_LOKASI', 'ISO', 'KODE_PANGKAT',
                        'KETERANGAN', 'tmt_pangkat_yad', 'tmt_kgb_yad', 'USIA_PENSIUN', 'TMT_PENSIUN',
                        'MK_TAHUN_1', 'MK_BULAN_1', 'NSM', 'NPSN', 'KODE_KUA', 'KODE_BIDANG_STUDI', 'BIDANG_STUDI',
                        'STATUS_PEGAWAI', 'LAT', 'LON', 'SATKER_KELOLA', 'HARI_KERJA', 'EMAIL_DINAS'
                    ];

                    fields.forEach(field => {
                        let hiddenInput = document.getElementById(`hidden_${field.toLowerCase()}`);
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.id = `hidden_${field.toLowerCase()}`;
                            hiddenInput.name = field.toLowerCase();
                            form.appendChild(hiddenInput);
                        }
                        hiddenInput.value = d[field] ?? '';
                    });

                    Toast.fire({
                        icon: 'success',
                        title: 'Pegawai terverifikasi!',
                        text: namaValid
                    });
                } else {
                    throw new Error(result.message || 'Format data API tidak sesuai atau NIP tidak ditemukan');
                }
            } catch (error) {
                console.error("Search Error:", error);

                resNama.value = "";
                resInfo.innerText = "";
                document.querySelectorAll('#modalTambahPenugasan form input[type="hidden"]').forEach(i => i.value = '');

                Toast.fire({
                    icon: 'error',
                    title: 'Pencarian Gagal',
                    text: error.message
                });
            } finally {
                btn.disabled = false;
                icon.classList.remove('fa-spin');
                nipInput.classList.remove('bg-slate-50');
            }
        }

        // Tambahan: Trigger pencarian saat tekan Enter di input NIP
        document.getElementById('search_nip')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPegawaiByNip();
            }
        });


        document.getElementById('kode_container').addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT') {
                updateFullCode();
            }
        });
    </script>
    <script>
        // Simpan semua data asli saat halaman dimuat
        const allParentOptions = Array.from(document.querySelectorAll('#parent_satker_id option'))
            .filter(opt => opt.value !== "")
            .map(opt => ({
                id: opt.value,
                text: opt.text,
                eselon: parseInt(opt.getAttribute('data-eselon')),
                periode: opt.getAttribute('data-periode') || ''
            }));

        // console.log("Daftar Parent Options:", allParentOptions);
        // Inisialisasi TomSelect
        var control = new TomSelect('#parent_satker_id', {
            render: {
                option: function(data, escape) {
                    return `<div class="py-1">
                        <div class="text-sm text-slate-700 leading-relaxed">${escape(data.text)}</div>
                    </div>`;
                },
                item: function(data, escape) {
                    return `<div class="text-slate-700">${escape(data.text)}</div>`;
                }
            }
        });

        function filterParent() {
            const jenisId = document.getElementById('jenis_satker_id').value;
            const parentContainer = document.getElementById('parent_container');
            const jabatanContainer = document.getElementById('container_status_jabatan'); // Ambil container baru

            // --- LOGIKA TAMPIL/SEMBUNYI JABATAN ---
            // Jika jenisId ada dan nilainya >= 2 (Eselon 2, 3, 4, dst)
            if (jenisId !== "" && parseInt(jenisId) >= 2) {
                jabatanContainer.classList.remove('hidden');
            } else {
                jabatanContainer.classList.add('hidden');
                // Reset nilai jika disembunyikan
                document.getElementById('tanpa_jabatan').value = "";
                document.getElementById('container_jabatan_fungsional').classList.add('hidden');
            }

            // --- LOGIKA PARENT (Sudah ada di kode Anda) ---
            if (jenisId === "1" || jenisId === "") {
                parentContainer.classList.add('hidden');
                if (control) control.clear();
                return;
            }

            parentContainer.classList.remove('hidden');
            const targetParentEselon = parseInt(jenisId) - 1;

            const filteredData = allParentOptions.filter(opt => opt.eselon === targetParentEselon);

            control.clearOptions();
            filteredData.forEach(opt => {
                control.addOption({
                    value: opt.id,
                    text: opt.text,
                    periode: opt.periode
                });
            });
            control.refreshOptions(false);
        }


        function updateNamaSatkerDariKabupaten() {
            const namaSatkerInput = document.getElementById('nama_satker');
            const wilayahSelect = document.getElementById('kabupaten_id');
            const madrasahSelect = document.getElementById('jenis_madrasah');
            const kategoriSelect = document.getElementById('kategori_kotakab');

            let baseName = "";

            // 1. Prioritas: Jika Madrasah dipilih
            if (madrasahSelect && madrasahSelect.value !== "") {
                baseName = madrasahSelect.options[madrasahSelect.selectedIndex].text;
            }
            // 2. Jika Kategori Unit dipilih (misal: Bimas Islam/Penyelenggara)
            else if (kategoriSelect && kategoriSelect.value !== "" && kategoriSelect.value !== "madrasah") {
                baseName = kategoriSelect.options[kategoriSelect.selectedIndex].text;
            }
            // 3. Fallback: Nama Kantor Kemenag Kabupaten
            else if (wilayahSelect && wilayahSelect.value !== "") {
                baseName = wilayahSelect.options[wilayahSelect.selectedIndex].text;
            }

            namaSatkerInput.value = baseName;
            namaSatkerInput.dataset.staticText = baseName ? baseName + " " : " ";
        }

        // Tambahkan fungsi handleJabatanChange jika belum ada untuk mendeteksi pilihan "Ada/Tidak Ada"
        function handleJabatanChange() {
            const jabatanSelect = document.getElementById('tanpa_jabatan');
            const status = jabatanSelect.value;
            const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];
            const parentUuid = selectedOption ? selectedOption.dataset.id : null;
            const namaSatkerInput = document.getElementById('nama_satker');
            const jenisSatker = document.getElementById('jenis_satker_id').value;

            const containers = [
                'container_kategori_unit',
                'container_kategori_kotakab',
                'container_kabupaten',
                'container_jenis_madrasah', // Kontainer madrasah
                'container_jabatan_fungsional'
            ];

            // Sembunyikan semua dulu
            containers.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });

            if (!status) return;

            // 1. JABATAN FUNGSIONAL
            if (status === 'jabatan_fungsional') {
                document.getElementById('container_jabatan_fungsional').classList.remove('hidden');
            }

            // 2. LOGIC JABATAN BERTINGKAT (KAB/KOTA & KANWIL)
            const children = refJabatan.filter(item => item.parent_id === parentUuid);

            if (children.length > 0) {
                let targetSelect = null;

                if (status === 'jabatan_kotakab') {
                    document.getElementById('container_kabupaten').classList.remove('hidden');
                    if (jenisSatker === '4') {
                        document.getElementById('container_kategori_kotakab').classList.remove('hidden');
                        targetSelect = document.getElementById('kategori_kotakab');

                        // CRITICAL: Jika kategori_kotakab sudah terpilih "Madrasah", pastikan container madrasah tetap muncul
                        const kategoriSelect = document.getElementById('kategori_kotakab');
                        if (kategoriSelect.value !== "") {
                            handleKategoriKotaKabChange(); // Trigger agar madrasah muncul jika sudah dipilih
                        }
                    }
                } else if (jenisSatker === '3') {
                    document.getElementById('container_kategori_unit').classList.remove('hidden');
                    targetSelect = document.getElementById('kategori_unit');
                }

                if (targetSelect && targetSelect.options.length <= 1) {
                    populateSubJabatan(parentUuid, targetSelect);
                }
            }

            // 3. UPDATE NAMA SATKER
            if (status === 'tidak_ada') {
                namaSatkerInput.value = 'Tidak Ada Jabatan';
                namaSatkerInput.dataset.staticText = '';
            } else if (status === 'jabatan_fungsional') {
                const fungsionalSelect = document.getElementById('jabatan_id');
                const opt = fungsionalSelect.options[fungsionalSelect.selectedIndex];
                if (fungsionalSelect.value && opt.value !== "") {
                    const text = opt.text.includes(' - ') ? opt.text.split(' - ')[1] : opt.text;
                    namaSatkerInput.value = text;
                    namaSatkerInput.dataset.staticText = text + " ";
                }
            } else if (status === 'jabatan_kotakab') {
                updateNamaSatkerDariKabupaten();
            } else if (status === 'jabatan_kanwil') {
                // LOGIKA BARU: Cek apakah Kategori Unit (Eselon III Kanwil) ada isinya
                const kategoriUnitSelect = document.getElementById('kategori_unit');
                if (kategoriUnitSelect && kategoriUnitSelect.value !== "") {
                    const text = kategoriUnitSelect.options[kategoriUnitSelect.selectedIndex].text;
                    namaSatkerInput.value = text;
                    namaSatkerInput.dataset.staticText = text + " ";
                } else {
                    namaSatkerInput.value = selectedOption.text;
                    namaSatkerInput.dataset.staticText = selectedOption.text + " ";
                }
            } else {
                namaSatkerInput.value = selectedOption.text;
                namaSatkerInput.dataset.staticText = selectedOption.text + " ";
            }

            updateRefJabatanId();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Daftar semua ID yang harus memicu update nama satker saat nilainya berubah
            const triggerIds = [
                'kabupaten_id',
                'kategori_kotakab',
                'jenis_madrasah',
                'kategori_unit',
                'jabatan_id'
            ];

            triggerIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function() {
                        // Jika statusnya adalah jabatan kotakab, gunakan fungsi khusus kabupaten
                        const status = document.getElementById('tanpa_jabatan').value;
                        if (status === 'jabatan_kotakab') {
                            updateNamaSatkerDariKabupaten();
                        } else {
                            handleJabatanChange();
                        }
                    });
                }
            });
        });

        /**
         * Helper function untuk mengisi dropdown anak (level 2 atau 3)
         * berdasarkan parent_id dari database
         */
        function getKode(selectId) {

            const el = document.getElementById(selectId);

            if (!el) return "";

            const option = el.options[el.selectedIndex];

            return option?.dataset?.kode || "";

        }


        // ================================
        // POPULATE SUB JABATAN
        // ================================
        function populateSubJabatan(parentId, targetSelect) {

            const children = refJabatan.filter(item => item.parent_id === parentId);

            targetSelect.innerHTML = '<option value="">-- Pilih --</option>';

            children.forEach(item => {

                const option = document.createElement("option");

                option.value = item.key_jabatan;
                option.textContent = item.label_jabatan;

                option.dataset.id = item.id;
                option.dataset.kode = item.kode_dasar || "";
                option.dataset.key = item.key_jabatan;

                targetSelect.appendChild(option);

            });

        }

        function getMiddleCode() {

            const status = document.getElementById("tanpa_jabatan").value;

            const kodeJabatan = getKode("tanpa_jabatan");
            const kodeFungsional = getKode("jabatan_id");
            const kodeUnit = getKode("kategori_unit");
            const kodeKategori = getKode("kategori_kotakab");
            const kodeMadrasah = getKode("jenis_madrasah");
            const kategoriUnitEl = document.getElementById("kategori_unit");

            // =========================
            // JABATAN FUNGSIONAL
            // =========================
            if (status === "jabatan_fungsional") {
                return kodeFungsional || "";
            }

            // =========================
            // MANAJERIAL
            // =========================
            if (status === "manajerial") {
                return ""; // biarkan backend yang generate
            }

            // =========================
            // MADRASAH
            // =========================
            if (kodeMadrasah) {
                return kodeMadrasah;
            }

            // =========================
            // KATEGORI KOTA/KAB
            // =========================
            if (kodeKategori) {
                return kodeKategori;
            }

            // =========================
            // UNIT KANWIL
            // =========================
            if (kategoriUnitEl && kategoriUnitEl.value) {

                const selected = kategoriUnitEl.options[kategoriUnitEl.selectedIndex];
                const key = selected?.dataset?.key;
                const kode = selected?.dataset?.kode;

                console.log("UNIT KEY:", key);
                console.log("UNIT KODE:", kode);

                // Non Tata Usaha Kanwil
                if (key === "no_tu_kanwil") {
                    return "04";
                }

                return kode || "";
            }

            return kodeJabatan || "";
        }


        const refJabatan = @json($refJabatanSatker);

        function handleWilayahChange() {

            const wilayahSelect = document.getElementById('wilayah_id');
            const jabatanSelect = document.getElementById('tanpa_jabatan');

            if (!wilayahSelect.value) {
                jabatanSelect.innerHTML = '<option value="">-- Pilih Status Jabatan --</option>';
                return;
            }

            const wilayahText = wilayahSelect.options[wilayahSelect.selectedIndex].text.toLowerCase();

            jabatanSelect.innerHTML = '<option value="">-- Pilih Status Jabatan --</option>';

            let finalOptions = [];

            // jabatan umum TANPA manajerial
            const commonOptions = refJabatan.filter(item =>
                item.tingkat_wilayah_id === null &&
                item.parent_id === null &&
                item.key_jabatan !== 'manajerial'
            );

            // PUSAT
            if (wilayahText.includes('pusat')) {

                const pusatOptions = refJabatan.filter(item =>
                    item.tingkat_wilayah_id === null && item.parent_id === null
                );

                finalOptions = pusatOptions;

            }

            // PTKN
            else if (wilayahText.includes('ptkn')) {

                const ptknOptions = refJabatan.filter(item =>
                    item.tingkat_wilayah_id === 4 && item.parent_id === null
                );

                finalOptions = [...ptknOptions, ...commonOptions];

            }

            // PROVINSI / KABKOTA
            else {

                const wilayahOptions = refJabatan.filter(item =>
                    item.key_jabatan === 'jabatan_kanwil' ||
                    item.key_jabatan === 'jabatan_kotakab'
                );

                finalOptions = [...wilayahOptions, ...commonOptions];

            }

            finalOptions.forEach(opt => {

                const el = document.createElement('option');
                el.value = opt.key_jabatan;
                el.textContent = opt.label_jabatan;
                el.dataset.id = opt.id;
                el.dataset.kode = opt.kode_dasar || "";

                jabatanSelect.appendChild(el);

            });

            handleJabatanChange();
        }

        function handleKategoriKotaKabChange() {
            const kategoriSelect = document.getElementById('kategori_kotakab');
            const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
            const parentUuid = selectedOption ? selectedOption.dataset.id : null;
            const selectedKey = kategoriSelect.value;

            const madrasahDiv = document.getElementById('container_jenis_madrasah');
            const madrasahSelect = document.getElementById('jenis_madrasah');

            madrasahDiv.classList.add('hidden');

            // cek apakah kategori ini punya child di DB
            const children = refJabatan.filter(item => item.parent_id === parentUuid);

            if (children.length > 0) {
                madrasahDiv.classList.remove('hidden');
                populateSubJabatan(parentUuid, madrasahSelect);
            }

            updateNamaSatkerDariKabupaten();
            updateRefJabatanId();
        }

        function handleJenisMadrasahChange() {
            resetKodeSatker();

            const jenisMadrasah = document.getElementById('jenis_madrasah');
            const namaSatkerInput = document.getElementById('nama_satker');

            if (jenisMadrasah.value !== "") {

                const staticName = jenisMadrasah.options[jenisMadrasah.selectedIndex].text;

                namaSatkerInput.dataset.staticText = staticName + " ";
                namaSatkerInput.value = staticName + " ";
                namaSatkerInput.focus();

            } else {

                namaSatkerInput.dataset.staticText = "";
                namaSatkerInput.value = "";

            }
            updateRefJabatanId();
        }

        const namaSatkerInput = document.getElementById('nama_satker');

        if (namaSatkerInput) {
            // Proteksi agar prefix tidak bisa dihapus
            namaSatkerInput.addEventListener('input', function() {
                const staticPrefix = this.dataset.staticText;

                // Jika ada prefix statis dan user mencoba menghapusnya
                if (staticPrefix && !this.value.startsWith(staticPrefix)) {
                    this.value = staticPrefix;
                }
            });

            // Mencegah cursor diletakkan di tengah/depan teks statis
            namaSatkerInput.addEventListener('click', function() {
                const staticPrefix = this.dataset.staticText;
                if (staticPrefix && this.selectionStart < staticPrefix.length) {
                    this.setSelectionRange(staticPrefix.length, staticPrefix.length);
                }
            });

            // Proteksi tambahan saat menekan tombol backspace/delete di area terlarang
            namaSatkerInput.addEventListener('keydown', function(e) {
                const staticPrefix = this.dataset.staticText;
                if (staticPrefix && (e.key === 'Backspace' || e.key === 'Delete')) {
                    if (this.selectionStart <= staticPrefix.length && this.selectionEnd <= staticPrefix
                        .length) {
                        e.preventDefault();
                    }
                }
            });
        }


        function handleKategoriUnitChange() {
            // 1. Reset Kode Satker (Fungsi lama Anda)
            resetKodeSatker();

            // 2. Update Nama Satker
            const kategoriSelect = document.getElementById('kategori_unit');
            const namaSatkerInput = document.getElementById('nama_satker');

            if (kategoriSelect.value !== "") {
                // Mengambil teks "Tata Usaha" atau "Non Tata Usaha"
                const selectedText = kategoriSelect.options[kategoriSelect.selectedIndex].text;
                namaSatkerInput.value = selectedText;
            } else {
                // Jika dikosongkan, balikkan ke nama Jabatan yang dipilih (Jabatan Kanwil)
                const statusSelect = document.getElementById('tanpa_jabatan');
                if (statusSelect.value !== "") {
                    namaSatkerInput.value = statusSelect.options[statusSelect.selectedIndex].text;
                }
            }
            updateRefJabatanId();
        }

        function resetKodeSatker() {
            const container = document.getElementById('kode_container');

            // hapus hanya input hasil generate
            container.querySelectorAll('.generated-kode').forEach(el => el.remove());

            // reset full code
            const fullCodeInput = document.getElementById('kode_satker_full');
            if (fullCodeInput) {
                fullCodeInput.value = '';
            }
        }

        async function generateSatkerCode(event) {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            const jenisId = document.getElementById('jenis_satker_id').value;
            const parentId = document.getElementById('parent_satker_id').value;

            if (!jenisId || (jenisId !== "1" && !parentId)) {
                Toast.fire({
                    icon: 'warning',
                    title: !jenisId ? 'Pilih Jenis Satker Terlebih Dahulu!' :
                        'Pilih Satker Induk Terlebih Dahulu!'
                });
                return;
            }

            const originalIconClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            btn.disabled = true;

            try {
                const wilayahId = document.getElementById('wilayah_id')?.value || '';
                const refJabatanId = document.getElementById("ref_jabatan_satker_id")?.value || document.getElementById('jabatan_id')?.value || '';

                const queryParams = new URLSearchParams({
                    jenis_id: jenisId,
                    parent_id: parentId,
                    ref_jabatan_satker_id: refJabatanId,
                    wilayah_id: wilayahId,
                    _t: Date.now()
                });

                const response = await fetch(`{{ url('admin/satker/generate-code') }}?${queryParams}`, {
                    method: 'GET',
                    headers: {
                        'Pragma': 'no-cache',
                        'Cache-Control': 'no-cache'
                    },
                    cache: 'no-store'
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error);

                const container = document.getElementById('kode_container');

                container.querySelectorAll('input').forEach(el => el.remove());

                if (jenisId === "1") {
                    const fullCode = data.code || "";
                    let htmlInputs = `
                <input type="text" value="${fullCode}" readonly
                class="w-full px-3 py-2 bg-slate-200 border rounded-xl text-sm text-center generated-kode">
            `;
                    container.insertAdjacentHTML('afterbegin', htmlInputs);
                } else {
                    const parentSelect = document.getElementById('parent_satker_id');
                    const parentText = parentSelect?.options[parentSelect.selectedIndex]?.text || '';
                    const displayParentKode = parentText.split(' - ')[0].trim();
                    const prefixLength = displayParentKode.length;

                    const middle = (data.code || "").substring(prefixLength);

                    let htmlInputs = `
                <input type="text" value="${displayParentKode}" readonly
                class="w-40 px-3 py-2 bg-slate-200 border rounded-xl text-sm text-center font-medium generated-kode">
                <input type="text" value="${middle}" readonly
                class="w-40 px-3 py-2 bg-slate-200 border rounded-xl text-sm text-center font-medium generated-kode">
            `;
                    container.insertAdjacentHTML('afterbegin', htmlInputs);
                }

                const finalInput = document.getElementById('kode_satker_full');
                if (finalInput) finalInput.value = data.code;

                if (data.default_nama) {
                    const namaInput = document.querySelector('input[name="nama_satker"]');
                    if (namaInput && namaInput.value.trim() === '') {
                        namaInput.value = data.default_nama;
                    }
                }

                if (typeof updateFullCode === "function") {
                    updateFullCode();
                }

            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: error.message
                });
            } finally {
                icon.className = originalIconClass;
                btn.disabled = false;
            }
        }

        function updateFullCode() {

            const container = document.getElementById('kode_container');

            const inputs = container.querySelectorAll('input');

            const finalInput = document.getElementById('kode_satker_full');

            let combined = '';

            inputs.forEach(input => {

                combined += input.value.trim();

            });

            finalInput.value = combined;

        }

        function updateRefJabatanId() {

            let id = "";

            const selects = [
                "jenis_madrasah",
                "kategori_kotakab",
                "kategori_unit",
                "jabatan_id",
                "tanpa_jabatan"
            ];

            for (let s of selects) {

                const el = document.getElementById(s);

                if (el && el.value) {

                    const opt = el.options[el.selectedIndex];

                    if (opt?.dataset?.id) {
                        id = opt.dataset.id;
                        break;
                    }
                }
            }

            document.getElementById("ref_jabatan_satker_id").value = id;
        }

        /**
         * Handle Submit Form
         */
        document.getElementById('formTambahSatker').addEventListener('submit', function(e) {
            const finalCode = document.getElementById('kode_satker_full').value;

            if (!finalCode) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Kode Satker Kosong',
                    text: 'Silahkan klik tombol magic untuk generate kode atau isi manual.'
                });
            }
        });

        // Update Listener TomSelect
        if (control) {
            control.on('change', function(value) {
                const periodeContainer = document.getElementById('container_periode');
                const periodeSelect = document.getElementById('periode_id');

                if (value) {

                    const dataAsli = allParentOptions.find(item => item.id == value);

                    console.log("Data ditemukan:", dataAsli);

                    if (dataAsli && dataAsli.periode && dataAsli.periode !== "") {
                        // ISI OTOMATIS
                        periodeSelect.value = dataAsli.periode;

                        // SEMBUNYIKAN
                        periodeContainer.classList.add('hidden');
                        periodeSelect.removeAttribute('required');

                        console.log("Periode otomatis terisi: " + dataAsli.periode);
                    } else {
                        // TAMPILKAN MANUAL JIKA PERIODE KOSONG
                        periodeContainer.classList.remove('hidden');
                        periodeSelect.setAttribute('required', 'required');
                        periodeSelect.value = "";
                    }
                } else {
                    periodeContainer.classList.remove('hidden');
                    periodeSelect.setAttribute('required', 'required');
                }
            });
        }

        // Tambahkan parameter periode_id di sini
    </script>

    <script>
        function openEditSatkerModal(id, kode, nama, periode_id, jenis_id, parent_id, wilayah_id, keterangan, status) {
            const form = document.getElementById('formEditSatker');
            form.action = `{{ url('admin/satker') }}/${id}`;

            document.getElementById('edit_kode_satker').value = kode;
            document.getElementById('edit_nama_satker').value = nama;
            document.getElementById('edit_jenis_satker_id').value = jenis_id;

            // Sekarang periode_id sudah terdefinisi dari parameter fungsi
            document.getElementById('edit_periode_id').value = periode_id;

            document.getElementById('edit_wilayah_id').value = wilayah_id;
            document.getElementById('edit_keterangan').value = keterangan || '';

            // Set status aktif (toggle switch)
            const statusCheckbox = document.getElementById('edit_status_aktif');
            statusCheckbox.checked = (status == 1 || status == 'Aktif');

            // Jalankan filter parent agar opsi yang muncul sesuai eselon
            filterParentEdit(jenis_id, parent_id);

            toggleModal('modalEditSatker');
        }

        function filterParentEdit(jenisIdManual = null, parentIdManual = null) {
            const jenisId = jenisIdManual || document.getElementById('edit_jenis_satker_id').value;
            const container = document.getElementById('edit_parent_container');
            const select = document.getElementById('edit_parent_satker_id');
            const options = select.querySelectorAll('option');

            // Sembunyikan jika Eselon 1
            if (jenisId === "1" || jenisId === "") {
                container.classList.add('hidden');
                select.value = "";
                return;
            }

            container.classList.remove('hidden');
            const targetParentEselon = parseInt(jenisId) - 1;

            options.forEach(option => {
                if (option.value === "") return;
                const optionEselon = parseInt(option.getAttribute('data-eselon'));
                option.style.display = (optionEselon === targetParentEselon) ? 'block' : 'none';
            });

            if (parentIdManual) {
                select.value = parentIdManual;
            }
        }
    </script>

    <script>
        function openDeleteModal(id, nama, kode) {
            const form = document.getElementById('formHapusSatker');
            const displayNama = document.getElementById('hapus_nama_display');

            // Menyiapkan template URL dari named route Laravel
            // Kita gunakan string 'ID_REPLACE' sebagai placeholder
            let url = "{{ route('admin.satker.destroy', ':id') }}";

            // Ganti placeholder dengan ID asli menggunakan JavaScript replace
            form.action = url.replace(':id', id);

            // Set Teks Konfirmasi (Nama Satker + Kode)
            displayNama.innerText = `${nama} (${kode})`;

            toggleModal('modalHapusSatker');
        }
    </script>

    <script>
        async function openDetailModal(kode, nama, eselon, wilayah, status, id) {
            // 0. Reset Input Pencarian agar data tidak terfilter dari pencarian sebelumnya
            const searchInput = document.getElementById('searchUserDetail');
            if (searchInput) searchInput.value = '';

            // 1. Isi Info Dasar
            const hiddenInput = document.getElementById('detail_satker_id');
            if (hiddenInput) hiddenInput.value = id;

            document.getElementById('detail_kode').innerText = kode;
            document.getElementById('detail_nama').innerText = nama;
            document.getElementById('detail_eselon').innerText = eselon;
            document.getElementById('detail_wilayah').innerText = wilayah;

            // 2. Render Status Badge
            const statusContainer = document.getElementById('detail_status_container');
            statusContainer.innerHTML =
                (status == 1 || status == 'Aktif') ?
                '<span class="bg-emerald-500 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Aktif</span>' :
                '<span class="bg-slate-400 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Non-Aktif</span>';

            // 3. Skeleton Loading
            const tableBody = document.getElementById('detail_user_table_body');
            const skeletonRow = `
            <tr class="animate-pulse">
                <td class="px-4 py-4"><div class="h-3 w-4 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-32 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-24 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-16 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-40 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-12 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-40 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-12 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-40 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-3 w-12 bg-slate-200 rounded"></div></td>
                <td class="px-4 py-4"><div class="h-8 w-20 bg-slate-200 rounded"></div></td>
            </tr>
        `;
            tableBody.innerHTML = skeletonRow.repeat(3);

            // Tampilkan modal
            toggleModal('modalDetailSatker');

            // 4. Fetch berdasarkan SATKER ID
            try {
                // Pastikan menggunakan url() Laravel agar tidak error di local
                const response = await fetch(`{{ url('admin/satker/users') }}/${id}`);
                const users = await response.json();

                tableBody.innerHTML = '';

                if (users.length > 0) {
                    users.forEach((user, index) => {
                        // LOGIKA BARU UNTUK STATUS BADGE (Ada tambahan status Cuti)
                        let statusBadge = '';
                        if (user.is_cuti) {
                            statusBadge = `<span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded border border-amber-200">SEDANG CUTI</span>`;
                        } else if (user.status_aktif == 1) {
                            statusBadge = `<span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">AKTIF</span>`;
                        } else {
                            statusBadge = `<span class="text-[10px] font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200">NON-AKTIF</span>`;
                        }

                        // LOGIKA BARU UNTUK TOMBOL (Berubah otomatis saat sedang cuti)
                        let actionButton = '-';
                        if (user.status_aktif == 1 && !user.is_cuti) {
                            actionButton = `
                                <div class="flex flex-col gap-1.5 min-w-[85px]">
                                    <button onclick="unassignUser('${user.penugasan_id}', '${user.name}', 'selesai')" class="w-full px-2 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Akhiri Tugas Permanen">
                                        <i class="fas fa-check-circle mr-1.5"></i> Selesai
                                    </button>
                                    <button onclick="unassignUser('${user.penugasan_id}', '${user.name}', 'cuti')" class="w-full px-2 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Mulai Cuti">
                                        <i class="fas fa-calendar-minus mr-1.5"></i> Cuti
                                    </button>
                                </div>`;
                        } else if (user.is_cuti) {
                            actionButton = `
                                <div class="flex flex-col gap-1.5 min-w-[85px]">
                                    <button onclick="showDetailCuti('${user.name}', '${user.tanggal_mulai_cuti_raw}', '${user.tanggal_selesai_cuti_raw}')" class="w-full px-2 py-1.5 bg-sky-500 hover:bg-sky-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Lihat Detail Waktu Cuti">
                                        <i class="fas fa-info-circle mr-1.5"></i> Detail Cuti
                                    </button>
                                    <button onclick="unassignUser('${user.penugasan_id}', '${user.name}', 'selesai')" class="w-full px-2 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Akhiri Tugas Permanen">
                                        <i class="fas fa-check-circle mr-1.5"></i> Selesai
                                    </button>
                                </div>`;
                        }

                        tableBody.innerHTML += `
                        <tr class="hover:bg-blue-50/50 transition duration-200" 
                            data-name="${(user.name || '').toLowerCase()}" 
                            data-nip="${(user.nip || '').toLowerCase()}">
                            <td class="row-number px-4 py-3 text-slate-600">${index + 1}</td>
                            <td class="px-4 py-3 font-semibold text-slate-700">${user.name}</td>
                            <td class="px-4 py-3 text-slate-500">${user.nip ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.email ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.jabatan ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.roles ?? '-'}</td>
                            <td class="px-4 py-3 font-bold text-blue-600">${user.jenis_penugasan ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.tanggal_mulai ?? '-'}</td>
                            <td class="px-4 py-3 font-bold ${user.tanggal_selesai ? 'text-amber-600' : 'text-slate-400'}">${user.tanggal_selesai ?? 'Belum Berakhir'}</td>
                            <td class="px-4 py-3">${statusBadge}</td>
                            <td class="px-4 py-3 text-center align-middle">${actionButton}</td>
                        </tr>
                    `;
                    });

                    document.getElementById('detail_user_count').innerText = `Menampilkan ${users.length} pejabat`;

                } else {
                    tableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="px-4 py-16 text-center">
                            <i class="fas fa-user-slash text-slate-300 text-3xl mb-3 block"></i>
                            <span class="text-slate-400 italic text-sm">Tidak ada pejabat di satker ini</span>
                        </td>
                    </tr>`;
                    document.getElementById('detail_user_count').innerText = 'Menampilkan 0 pejabat';
                }

            } catch (error) {
                console.error(error);
                tableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="px-4 py-10 text-center text-red-400">
                        <i class="fas fa-exclamation-circle mr-2"></i> Gagal memuat data pejabat
                    </td>
                </tr>`;
            }
        }

        function showDetailCuti(nama, start, end) {
            Swal.fire({
                title: 'Informasi Cuti Pegawai',
                html: `
                    <div class="text-left text-sm text-slate-700">
                        <p class="mb-3">Pejabat: <b class="text-slate-900">${nama}</b></p>
                        <div class="bg-amber-50 p-3 rounded-lg border border-amber-200">
                            <p class="mb-1">Mulai Cuti: <b class="text-amber-700">${start}</b></p>
                            <p>Selesai Cuti: <b class="text-amber-700">${end}</b></p>
                        </div>
                        <p class="mt-4 text-xs text-slate-500 italic">*Pegawai akan otomatis kembali berstatus AKTIF satu hari setelah masa cuti berakhir.</p>
                    </div>
                `,
                icon: 'info',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#3b82f6'
            });
        }

        // ==========================================
        // PERUBAHAN FUNGSI UNASSIGN DENGAN PARAMETER 'TYPE'
        // ==========================================
        async function unassignUser(penugasanId, namaPegawai, type = 'selesai') {
            const today = new Date().toISOString().split('T')[0];
            
            let titleTxt = type === 'cuti' ? 'Mulai Cuti' : 'Akhiri Tugas';
            let descTxt = type === 'cuti' ? `Tentukan rentang tanggal <b>cuti</b> untuk pejabat <b class="text-slate-800">${namaPegawai}</b>.` : `Silakan tentukan tanggal <b>selesai tugas</b> untuk pejabat <b class="text-slate-800">${namaPegawai}</b>.`;
            
            // Siapkan desain kalender berdasarkan tombol yang diklik
            let htmlContent = '';
            if(type === 'cuti') {
                htmlContent = `
                    <div class="text-sm text-slate-500 mb-4 text-left">${descTxt}</div>
                    <div class="text-left mb-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tgl Mulai Cuti</label>
                        <input type="date" id="tgl_mulai_cuti" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-blue-500 outline-none" value="${today}">
                    </div>
                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tgl Selesai Cuti</label>
                        <input type="date" id="tgl_selesai_cuti" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-blue-500 outline-none" value="${today}">
                    </div>
                `;
            } else {
                htmlContent = `
                    <div class="text-sm text-slate-500 mb-4 text-left">${descTxt}</div>
                    <div class="text-left">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai Tugas</label>
                        <input type="date" id="tgl_selesai_input" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm focus:border-blue-500 outline-none" value="${today}">
                    </div>
                `;
            }

            const { value: formValues, isConfirmed } = await Swal.fire({
                title: titleTxt,
                html: htmlContent,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: type === 'cuti' ? '#f59e0b' : '#ef4444', 
                cancelButtonColor: '#cbd5e1', 
                confirmButtonText: type === 'cuti' ? 'Simpan Cuti' : 'Selesaikan Tugas',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    if (type === 'cuti') {
                        const start = document.getElementById('tgl_mulai_cuti').value;
                        const end = document.getElementById('tgl_selesai_cuti').value;
                        if (!start || !end) {
                            Swal.showValidationMessage('Tgl Mulai dan Selesai Cuti wajib diisi!');
                            return false;
                        }
                        if (start > end) {
                            Swal.showValidationMessage('Tgl Selesai tidak boleh lebih awal dari Tgl Mulai!');
                            return false;
                        }
                        return { jenis_aksi: 'cuti', tanggal_mulai_cuti: start, tanggal_selesai_cuti: end };
                    } else {
                        const tgl = document.getElementById('tgl_selesai_input').value;
                        if (!tgl) {
                            Swal.showValidationMessage('Tanggal Selesai harus diisi!');
                            return false;
                        }
                        return { jenis_aksi: 'selesai', tanggal_selesai: tgl };
                    }
                }
            });

            if (!isConfirmed) return;

            try {
                const response = await fetch(`{{ url('admin/penugasan/unassign') }}/${penugasanId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formValues) 
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Diperbarui',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Refresh data tabel modal
                        const satkerId = document.getElementById('detail_satker_id').value;
                        openDetailModal(
                            document.getElementById('detail_kode').innerText,
                            document.getElementById('detail_nama').innerText,
                            document.getElementById('detail_eselon').innerText,
                            document.getElementById('detail_wilayah').innerText,
                            1,
                            satkerId
                        );
                    }
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem saat memperbarui status.', 'error');
            }
        }

        document.getElementById('searchUserDetail').addEventListener('keyup', function() {
            let keyword = this.value.toLowerCase().trim();
            let rows = document.querySelectorAll('#detail_user_table_body tr');
            let visibleIndex = 1;
            let visibleCount = 0;

            rows.forEach(row => {
                let nameAttr = row.getAttribute('data-name');
                // Abaikan baris "Tidak ada data" atau baris Skeleton
                if (!nameAttr) return;

                let nipAttr = row.getAttribute('data-nip') || '';

                if (keyword === '' || nameAttr.includes(keyword) || nipAttr.includes(keyword)) {
                    row.style.display = '';

                    // Update nomor urut baris yang nampak
                    let numberCell = row.querySelector('.row-number');
                    if (numberCell) numberCell.innerText = visibleIndex++;

                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const countLabel = document.getElementById('detail_user_count');
            if (countLabel) {
                countLabel.innerText = `Menampilkan ${visibleCount} pejabat`;
            }
        });
    </script>

    <script>
        const userRoles = @json(auth()->user()->roles->pluck('key'));
        const isRestrictedRole =
            userRoles.includes('admin_satker') ||
            userRoles.includes('pejabat');


        // Tambahkan parameter periodeId di sini
        function openTambahSubSatker(parentId, parentJenisId, wilayahId, periodeId, lockParent = false) {

            const form = document.querySelector('#modalTambahSatker form');
            form.reset();

            toggleModal('modalTambahSatker');

            const wilayahSelect = document.getElementById('wilayah_id');
            if (wilayahSelect) {
                wilayahSelect.value = wilayahId;
                handleWilayahChange();
            }

            const periodeSelect = document.getElementById('periode_id');
            const periodeContainer = document.getElementById('container_periode');

            if (periodeSelect) {
                periodeSelect.value = periodeId;

                if (periodeId && periodeContainer) {
                    periodeContainer.classList.add('hidden');
                    periodeSelect.removeAttribute('required');
                } else if (periodeContainer) {
                    periodeContainer.classList.remove('hidden');
                    periodeSelect.setAttribute('required', 'required');
                }
            }

            const jenisSelect = document.getElementById('jenis_satker_id');
            const parentSelect = document.getElementById('parent_satker_id');
            const nextLevel = parseInt(parentJenisId) + 1;

            // set level
            if (jenisSelect.querySelector(`option[value="${nextLevel}"]`)) {
                jenisSelect.value = nextLevel;
            }

            filterParent();

            setTimeout(() => {

                if (control) {
                    control.setValue(parentId, true);
                } else {
                    parentSelect.value = parentId;
                }

                // =========================
                // LOCK jika dari tombol tambah
                // =========================
                if (lockParent) {
                    // 1. Lock visual Select
                    jenisSelect.value = nextLevel;
                    jenisSelect.disabled = true;

                    // 2. Set dan Aktifkan Hidden Input agar terkirim ke server
                    const hiddenJenis = document.getElementById('hidden_jenis_satker_id');
                    hiddenJenis.value = nextLevel;
                    hiddenJenis.disabled = false; // Aktifkan agar terkirim

                    // 3. Hal yang sama untuk Parent ID
                    const hiddenParent = document.getElementById('hidden_parent_satker_id');
                    hiddenParent.value = parentId;
                    hiddenParent.disabled = false;

                    if (control) {
                        control.disable();
                    } else {
                        parentSelect.disabled = true;
                    }
                } else {
                    // Normal state: Matikan hidden input agar tidak bentrok dengan select utama
                    jenisSelect.disabled = false;
                    document.getElementById('hidden_jenis_satker_id').disabled = true;
                    document.getElementById('hidden_parent_satker_id').disabled = true;

                    if (control) control.enable();
                    else parentSelect.disabled = false;
                }

                if (typeof updateFullCode === "function") {
                    updateFullCode();
                }

            }, 50);
        }
    </script>
    <script>
        function resetTambahSatkerModal() {

            const form = document.querySelector('#modalTambahSatker form');
            if (form) form.reset();

            const jenisSelect = document.getElementById('jenis_satker_id');
            const parentSelect = document.getElementById('parent_satker_id');
            const wilayahSelect = document.getElementById('wilayah_id');
            const periodeContainer = document.getElementById('container_periode');

            // reset jenis satker
            if (jenisSelect) {
                jenisSelect.disabled = false;
                jenisSelect.style.pointerEvents = "auto";
                jenisSelect.value = "";
            }

            // reset parent satker
            if (control) {
                control.enable();
                control.unlock();
                control.clear();
                control.wrapper.style.pointerEvents = "auto";
            } else if (parentSelect) {
                parentSelect.disabled = false;
                parentSelect.style.pointerEvents = "auto";
                parentSelect.value = "";
            }

            // reset wilayah
            if (wilayahSelect) {
                wilayahSelect.value = "";
            }

            // tampilkan kembali container periode
            if (periodeContainer) {
                periodeContainer.classList.remove('hidden');
            }

            // reset parent filter
            if (typeof filterParent === "function") {
                filterParent();
            }

            // reset kode
            if (typeof updateFullCode === "function") {
                updateFullCode();
            }
        }

        // ==========================================
        // FITUR TAMBAH PENUGASAN TANPA RELOAD (AJAX)
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            const formPenugasan = document.getElementById('formTambahPenugasan');
            
            if (formPenugasan) {
                formPenugasan.addEventListener('submit', async function(e) {
                    e.preventDefault(); // Mencegah halaman me-reload
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    // 1. Tampilkan Efek Loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
                    
                    try {
                        const formData = new FormData(this);
                        
                        // 2. Kirim data ke server secara rahasia (AJAX)
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest', // Penanda bahwa ini AJAX
                                'Accept': 'application/json'          // Meminta balasan berupa JSON
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok && result.success) {
                            // 3a. Jika Sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: result.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            
                            // Tutup modal form tambah
                            toggleModal('modalTambahPenugasan');
                            
                            // Reset isi form
                            this.reset();
                            const tomSelectEl = document.getElementById('select_pegawai_local');
                            if (tomSelectEl && tomSelectEl.tomselect) tomSelectEl.tomselect.clear();
                            document.getElementById('res_nama').value = '';
                            document.getElementById('res_info_tambahan').innerText = '';
                            
                            // 4. Refresh Tabel di Modal Detail tanpa menutupnya
                            const satkerId = document.getElementById('detail_satker_id').value;
                            const satkerKode = document.getElementById('detail_kode').innerText;
                            const satkerNama = document.getElementById('detail_nama').innerText;
                            const satkerEselon = document.getElementById('detail_eselon').innerText;
                            const satkerWilayah = document.getElementById('detail_wilayah').innerText;
                            
                            openDetailModal(satkerKode, satkerNama, satkerEselon, satkerWilayah, 1, satkerId);
                            
                        } else if (response.status === 422) {
                            // 3b. Jika Gagal Validasi Bawaan Laravel
                            let errorText = 'Silakan periksa kembali input Anda:\n';
                            for (let key in result.errors) {
                                errorText += `- ${result.errors[key][0]}\n`;
                            }
                            Swal.fire('Validasi Gagal', errorText, 'error');
                        } else {
                            // 3c. Jika Gagal karena Aturan Bisnis PM (ex: Double Definitif)
                            Swal.fire('Gagal', result.message || 'Terjadi kesalahan sistem.', 'error');
                        }
                        
                    } catch (error) {
                        console.error(error);
                        Swal.fire('Error', 'Terjadi kesalahan jaringan atau server.', 'error');
                    } finally {
                        // 5. Kembalikan tombol seperti semula
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>
@endpush
