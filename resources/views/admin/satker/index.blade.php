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
        activePeriode: '{{ $activePeriodeId ?? '' }}',
        scrollTimeout: null, 
        matches: [],             // BARU: Menyimpan semua baris hasil yang cocok
        currentMatchIndex: 0,    // BARU: Melacak urutan hasil yang sedang dilihat
    
        isMatch(nama, kode) {
            if (!this.search) return false;
            let s = this.search.toLowerCase();
            return nama.includes(s) || kode.includes(s);
        },
    
        hasMatchingChild(children) {
            if (!children || children.length === 0) return false;
            for (let child of children) {
                if (this.isMatch(child.nama_satker.toLowerCase(), child.kode_satker.toLowerCase())) return true;
                if (this.hasMatchingChild(child.children_recursive)) return true;
            }
            return false;
        },

        init() {
            this.$watch('search', (val) => {
                if (val && val.length > 0) {
                    clearTimeout(this.scrollTimeout);
                    
                    this.scrollTimeout = setTimeout(() => {
                        const container = document.getElementById('mainTreeContainer');
                        if (!container) return;

                        const term = val.toLowerCase();
                        const rows = container.querySelectorAll('.satker-row');
                        
                        // Bersihkan sisa highlight sebelumnya
                        this.matches.forEach(row => row.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all'));
                        
                        this.matches = [];
                        this.currentMatchIndex = 0;

                        // Kumpulkan SEMUA baris yang cocok
                        for (let i = 0; i < rows.length; i++) {
                            const row = rows[i];
                            if (row.offsetHeight > 0) { 
                                const text = row.innerText || row.textContent;
                                if (text.toLowerCase().includes(term)) {
                                    this.matches.push(row);
                                }
                            }
                        }

                        // Scroll otomatis ke hasil pertama jika ada
                        if (this.matches.length > 0) {
                            this.scrollToMatch(0);
                        }
                    }, 300);
                } else {
                    // Bersihkan state & highlight jika input pencarian dikosongkan
                    this.matches.forEach(row => row.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all'));
                    this.matches = [];
                }
            });
        },

        // --- FUNGSI BARU UNTUK SCROLL & HIGHLIGHT (VSCode Style) ---
        scrollToMatch(index) {
            if (this.matches.length === 0) return;
            
            // Hapus highlight dari hasil yang sebelumnya aktif
            if (this.matches[this.currentMatchIndex]) {
                this.matches[this.currentMatchIndex].classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
            }
            
            // Looping index (Jika next di hasil terakhir, kembali ke 1. Dan sebaliknya)
            if (index < 0) index = this.matches.length - 1;
            if (index >= this.matches.length) index = 0;
            
            this.currentMatchIndex = index;
            const target = this.matches[this.currentMatchIndex];
            
            // Tambahkan highlight ke target yang sedang dilihat
            target.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');

            // Gulir ke elemen tersebut
            const container = document.getElementById('mainTreeContainer');
            const cRect = container.getBoundingClientRect();
            const mRect = target.getBoundingClientRect();
            
            container.scrollTo({
                top: container.scrollTop + (mRect.top - cRect.top) - 40,
                behavior: 'smooth'
            });
        },
        
        nextMatch() {
            this.scrollToMatch(this.currentMatchIndex + 1);
        },
        
        prevMatch() {
            this.scrollToMatch(this.currentMatchIndex - 1);
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
                <a href="{{ route('admin.satker.index', ['periode_id' => $pe->id]) }}"
                    class="px-6 py-3 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none {{ $activePeriodeId == $pe->id ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                    {{ $pe->nama_periode }}
                </a>
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
                        <input type="text" x-model.debounce.300ms="search" 
                            @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();"
                            placeholder="Cari nama / kode..."
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                    </div>

                    {{-- BUTTON TAMBAH --}}
                    @php $canCreateSatker = $perm['is_super'] || $perm['all_access'] || in_array('create', $perm['actions'] ?? []); @endphp
                    
                    <button type="button" @click="{{ $canCreateSatker ? "openTambahModalWithPeriode('$activePeriodeId')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menambah Satker.', 'error')" }}"
                        class="bg-[#112D4E] hover:bg-blue-900 text-white px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm whitespace-nowrap">
                        <i class="fas fa-plus text-[10px]"></i>
                        <span class="hidden sm:inline">Tambah Satker</span>
                    </button>

                </div>
            </div>

            {{-- ===================================== --}}
            {{-- KONTANER UTAMA SATKER (AUTO FILTER) --}}
            {{-- ===================================== --}}
            <div id="mainTreeContainer" class="px-4 sm:px-6 py-4 space-y-2 max-h-[65vh] overflow-y-auto scroll-smooth">

                @forelse ($satkers as $satker)
                    <div> {{-- X-SHOW DIHAPUS KARENA DATA SUDAH DIFILTER OLEH CONTROLLER --}}
                        @include('admin.satker._item_hirarki', [
                            'item' => $satker,
                            'isSearching' => false,
                        ])
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-sm italic">
                        Belum ada data pada periode ini.
                    </div>
                @endforelse

            </div>

        {{-- ================= FLOATING SEARCH NAVIGATOR (VSCode Style) ================= --}}      
        <div x-show="search && matches.length > 0" x-transition.opacity x-cloak
             {{-- KUNCI PERBAIKAN: Ubah right-8 menjadi left-1/2 dan -translate-x-1/2 agar posisinya di tengah bawah --}}
             class="fixed bottom-24 md:bottom-8 left-1/2 transform -translate-x-1/2 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.15)] border border-slate-200 rounded-full px-5 py-2.5 flex items-center gap-4 z-[55]">
            
            {{-- Indikator Angka (contoh: 1 dari 5) --}}
            <div class="text-xs font-bold text-slate-600 tracking-wide">
                <span x-text="currentMatchIndex + 1" class="text-blue-600"></span> 
                <span class="text-slate-400 mx-1">dari</span> 
                <span x-text="matches.length"></span>
            </div>
            
            <div class="w-[1px] h-4 bg-slate-200"></div>
            
            {{-- Tombol Arah --}}
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
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kategori Wilayah</label>
                        <select id="kategori_wilayah" onchange="handleKategoriWilayahChange()" required
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Kategori Wilayah</option>
                            <option value="1">Pusat</option>
                            <option value="4">PTKN</option>
                            <option value="2">Satker Daerah</option>
                        </select>
                    </div>

                    <div id="container_wilayah" class="hidden">
                        <label id="label_wilayah" class="block text-xs font-bold text-slate-700 uppercase mb-2">Detail Wilayah</label>
                        <select name="wilayah_id" id="wilayah_id" onchange="handleWilayahChange()"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="" data-tingkat="">Pilih detail wilayah</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->id }}" data-tingkat="{{ $w->tingkat_wilayah_id }}">{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ANCHOR UNTUK RUMUS PTKN --}}
                    <div id="anchor_rumus_ptkn"></div>

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

                        {{-- JAWABAN POIN 1: Hierarki Jabatan Fungsional 3 & 4 Digit --}}
                        <div id="container_jabatan_fungsional" class="hidden space-y-5 mt-4">
                            {{-- Dropdown 1: Kategori (3 Digit) --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kategori Jabatan Fungsional</label>
                                <select id="kategori_jabatan_fungsional" onchange="handleKategoriJabatanChange()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">-- Pilih Kategori (3 Digit) --</option>
                                    @foreach ($jabatanCategories as $cat)
                                        <option value="{{ $cat->kode_jabatan }}">
                                            {{ $cat->kode_jabatan }} - {{ $cat->nama_jabatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Dropdown 2: Jenjang (4 Digit) - Akan muncul setelah Kategori dipilih --}}
                            <div id="wrapper_jenjang_jabatan" class="hidden">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenjang Jabatan</label>
                                <select name="jabatan_id" id="jabatan_id" onchange="handleJenjangJabatanChange()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">-- Pilih Jenjang (4 Digit) --</option>
                                    @foreach ($jabatanItems as $item)
                                        @php
                                            // Gabungkan nama jabatan dasar dengan nama jenjang dari relasi fungsional
                                            $namaJenjang = $item->fungsional ? $item->fungsional->name : '';
                                            $displayFull = trim($item->nama_jabatan . ' ' . $namaJenjang);
                                        @endphp
                                        <option value="{{ $item->id }}" 
                                                data-kode="{{ $item->kode_jabatan }}"
                                                data-parent-kode="{{ substr($item->kode_jabatan, 0, 3) }}">
                                            {{ $item->kode_jabatan }} - {{ $displayFull }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Kontainer Kabupaten (Existing) --}}
                    <div id="container_kabupaten" class="hidden">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kabupaten / Kota</label>
                        <select name="kabupaten_id" id="kabupaten_id" onchange="updateNamaSatkerDariKabupaten()"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Kabupaten/Kota</option>
                            @foreach ($kabupaten as $kab)
                                {{-- TAMBAHKAN data-parent DI SINI --}}
                                <option value="{{ $kab->id }}" data-parent="{{ $kab->parent_wilayah_id }}">{{ $kab->nama_wilayah }}</option>
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

                    <div>
                        {{-- TAMBAHAN: Opsi Khusus Penomoran Balai --}}
                        <div id="wrapper_opsi_balai" class="hidden">
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100 mb-3">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-700">Format Penomoran Balai</h4>
                                    <p class="text-[11px] text-slate-500">Gunakan angka awalan khusus untuk Balai</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="is_balai_checkbox" class="sr-only peer" onchange="toggleBalaiOptions()">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#112D4E]"></div>
                                </label>
                            </div>

                            <div id="container_opsi_balai" class="hidden mb-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilih Kategori Balai</label>
                                <select id="start_num_balai" onchange="updateNamaBalai()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">-- Pilih Jenis Balai --</option>
                                    <option value="11">Balai Pendidikan dan Pelatihan (Mulai dari 11)</option>
                                    <option value="31">Balai Penelitian dan Pengembangan (Mulai dari 31)</option>
                                </select>
                            </div>
                        </div>

                        {{-- FILTER KELOMPOK FUNGSI (Standard Look) --}}
                        <div id="container_filter_fungsi" class="hidden mb-5">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                                Filter Kelompok Fungsi (Opsional)
                            </label>
                            <select id="filter_fungsi" onchange="handleFilterFungsiChange()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">-- Tampilkan Semua Rumus --</option>
                                <option value="Tata Usaha">Fungsi Tata Usaha</option> <option value="SPI">Satuan Pengawas Internal</option>
                                <option value="Pendidikan Islam">Fungsi Pendidikan Islam</option>
                                <option value="Bimas Islam">Fungsi Bimas Islam</option>
                                <option value="Haji dan Umrah">Fungsi Haji dan Umrah</option>
                                <option value="Bimas Kristen">Fungsi Bimas Kristen</option>
                                <option value="Bimas Katolik">Fungsi Bimas Katolik</option>
                                <option value="Bimas Hindu">Fungsi Bimas Hindu</option>
                                <option value="Bimas Buddha">Fungsi Bimas Buddha</option>
                                <option value="Bimas Khonghucu">Fungsi Bimas Khonghucu</option>
                            </select>
                        </div>

                        {{-- JAWABAN POIN 2: Rumpun Fakultas Khusus Eselon 2 PTKN --}}
                        <div id="container_rumpun_fakultas" class="hidden mb-5">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                                Rumpun Fakultas
                            </label>
                            <select name="rumpun_fakultas" id="rumpun_fakultas" onchange="handleRumpunFakultasChange()" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                <option value="">-- Pilih Rumpun Fakultas --</option>
                                <option value="901">Ilmu Tarbiyah dan Keguruan</option>
                                <option value="902">Adab dan Humaniora</option>
                                <option value="903">Ushuluddin</option>
                                <option value="904">Syariah dan Hukum</option>
                                <option value="905">Dakwah dan Ilmu Komunikasi</option>
                                <option value="906">Dirasat Islamiyah</option>
                                <option value="907">Psikologi</option>
                                <option value="908">Ekonomi dan Bisnis</option>
                                <option value="909">Sains dan Teknologi</option>
                                <option value="910">Kedokteran</option>
                                <option value="911">Ilmu Sosial dan Ilmu Politik</option>
                                <option value="912">Ilmu Kesehatan</option>
                            </select>
                        </div>

                        {{-- ANCHOR DEFAULT RUMUS MANUAL --}}
                        <div id="anchor_rumus_default">
                            {{-- DROPDOWN RUMUS MANUAL (OPSIONAL) --}}
                            <div id="container_rumus_manual" class="mb-5 mt-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                                    Gunakan Rumus Tersimpan (Opsional)
                                </label>
                                <select name="rumus_id" id="rumus_id" class="w-full">
                                    <option value="">-- Default (Sistem Otomatis) --</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mt-1 italic">*Hanya rumus yang sesuai dengan hierarki yang akan ditampilkan.</p>
                            </div>
                        </div>

                        {{-- Picklist Khusus Jabatan Kota/Kab --}}
                        <div id="container_kategori_kotakab" class="hidden space-y-5 mb-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kategori Unit
                                    Kota/Kab</label>
                                <select id="kategori_kotakab" onchange="handleKategoriKotaKabChange()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">-- Pilih Kategori --</option>
                                </select>
                            </div>

                            {{-- Picklist Spesifik Madrasah --}}
                            <div id="container_jenis_madrasah" class="hidden">
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jenis Madrasah</label>
                                <select id="jenis_madrasah" onchange="handleJenisMadrasahChange()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                                    <option value="">-- Pilih Jenis Madrasah --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Kode Satker (Generate) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kode Satker (Generate)</label>
                            <div class="flex gap-2 items-center" id="kode_container">
                                <input type="text" name="kode_satker" id="kode_satker" placeholder="Contoh: 0102" required class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">

                                <button type="button" onclick="generateSatkerCode(event)" title="Auto-generate kode"
                                    class="px-4 py-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 transition flex items-center justify-center min-w-[45px]">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                            
                            {{-- PESAN INFORMATIF STATUS RUMUS (BARU DITAMBAHKAN) --}}
                            <div id="info_generate_container" class="hidden mt-3 p-3 text-xs rounded-xl border"></div>

                            {{-- Container Gap/Nomor Bolong (Dibersihkan dari duplikasi) --}}
                            <div id="gap_selection_container" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                <p class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-2"><i class="fas fa-info-circle mr-1"></i> Terdeteksi Nomor Kosong di Tengah:</p>
                                <div id="gap_list" class="flex flex-wrap gap-2"></div>
                            </div>
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

                    {{-- Periode (Hidden, otomatis mengikuti Tab yang sedang aktif) --}}
                    <input type="hidden" name="periode_id" id="periode_id" value="">

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
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="" data-tingkat="">Pilih wilayah</option>
                            @foreach ($wilayahs as $w)
                                {{-- BAGIAN INI YANG DITAMBAH DATA-TINGKAT --}}
                                <option value="{{ $w->id }}" data-tingkat="{{ $w->tingkat_wilayah_id }}">{{ $w->nama_wilayah }}</option>
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

                        @php $canAssign = $perm['is_super'] || $perm['all_access'] || in_array('assign', $perm['actions'] ?? []); @endphp
                        
                        <button type="button" onclick="{{ $canAssign ? "openModalPenugasanDariDetail()" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menambah Penugasan.', 'error')" }}"
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

            if (satkerNama && satkerNama.trim().toLowerCase() === 'tidak ada jabatan') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Diizinkan',
                    text: 'Gagal: Satuan Kerja ini dikonfigurasi sebagai "Tidak Ada Jabatan" (00). Anda tidak dapat menugaskan pegawai ke dalam Satker ini.',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

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

                if (selectedText && (selectedText.includes('admin satker') || selectedText.includes('admin jafung') || selectedText.includes('admin jabatan fungsional'))) {
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

    document.addEventListener('alpine:init', () => {
        Alpine.store('selection', {
            isSelectionMode: false,
            selectedIds: [],
            selectedNames: [],
            selectedEselons: [], // TAMBAHKAN INI
            clipboard: { mode: '', ids: [] },

            toggleSelectionMode() {
                this.isSelectionMode = !this.isSelectionMode;
                if (!this.isSelectionMode) {
                    this.selectedIds = [];
                    this.selectedNames = [];
                    this.selectedEselons = []; // RESET INI
                    this.clearClipboard();
                    window.dispatchEvent(new CustomEvent('close-all-nodes'));
                } else {
                    window.dispatchEvent(new CustomEvent('open-all-nodes'));
                }
            },

            // UPDATE: Tambahkan parameter echelon
            toggleId(id, name, echelon) {
                const index = this.selectedIds.indexOf(id);
                if (index > -1) {
                    this.selectedIds.splice(index, 1);
                    this.selectedNames = this.selectedNames.filter(n => n !== name);
                    // Hapus eselon terkait (pake cara manual karena array bisa duplikat level)
                    const escIndex = this.selectedEselons.indexOf(parseInt(echelon));
                    if (escIndex > -1) this.selectedEselons.splice(escIndex, 1);
                } else {
                    this.selectedIds.push(id);
                    this.selectedNames.push(name);
                    this.selectedEselons.push(parseInt(echelon));
                }
            },

            // FUNGSI CEK: Apakah boleh melakukan aksi Copy/Cut?
            // Aturan: Jika ada Eselon 1, harus lebih dari 1 item yang dipilih.
            canPerformAction() {
                const hasEselon1 = this.selectedEselons.includes(1);
                if (hasEselon1 && this.selectedIds.length === 1) {
                    return false;
                }
                return true;
            },

            updateSelectedNames(name, isChecked) {
                if (isChecked) this.selectedNames.push(name);
                else this.selectedNames = this.selectedNames.filter(n => n !== name);
            },

            setClipboard(mode) {
                this.clipboard.mode = mode;
                this.clipboard.ids = [...this.selectedIds];
                Toast.fire({ icon: 'info', title: `Siap ${mode}. Silakan klik Paste pada Parent tujuan.` });
            },

            clearClipboard() {
                this.clipboard.mode = '';
                this.clipboard.ids = [];
            },

            // --- TAMBAHAN BARU: FUNGSI VALIDASI KONFIRMASI PASTE ---
            confirmPaste(targetParentId, parentCode = '', parentName = '') {
                const count = this.clipboard.ids.length;
                const modeText = this.clipboard.mode === 'copy' ? 'Menyalin (Copy)' : 'Memindahkan (Cut)';
                const prefixText = parentCode ? parentCode + '...' : 'Kode Awal Mandiri';

                Swal.fire({
                    title: 'Konfirmasi Paste',
                    html: `Anda akan ${modeText} <b>${count} Satker</b> ke dalam parent:<br><br>
                           <span class="text-blue-700 font-bold text-lg">${parentName}</span><br><br>
                           <span class="text-sm text-gray-500">Nantinya, kode satker akan menyesuaikan prefix hirarki: <b class="text-amber-600">${prefixText}</b></span>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Paste Sekarang',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.executePaste(targetParentId);
                    }
                });
            },

            // --- PERBAIKAN EXECUTE PASTE MENCEGAH ERROR HTML ---
            async executePaste(targetParentId, force = false) {
                try {
                    const res = await fetch("{{ url('admin/satker/bulk-action') }}", {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // INI WAJIB AGAR SERVER TAHU KITA MINTA JSON, BUKAN HTML
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            action: this.clipboard.mode,
                            ids: this.clipboard.ids,
                            target_parent_id: targetParentId,
                            periode_id: '{{ $activePeriodeId }}',
                            force: force
                        })
                    });

                    // Cek jika server mengembalikan error 500/404/419
                    if (!res.ok) {
                        throw new Error(`Server merespon dengan kode ${res.status}. Pastikan Route 'bulk-action' sudah ada di web.php`);
                    }

                    const data = await res.json();
                    if (data.duplicate) {
                        Swal.fire({
                            title: 'Peringatan Duplikasi',
                            html: data.message + '<br><br><span class="text-xs text-red-500">*Melanjutkan akan menimpa kode yang sama di parent tersebut.</span>',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Ya, Tetap Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) this.executePaste(targetParentId, true);
                        });
                    } else if (data.success) {
                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                } catch (error) {
                    Swal.fire('Terjadi Kesalahan', error.message, 'error');
                    console.error("Paste Error: ", error);
                }
            },

            confirmBulkDelete() {
                const listHtml = '<ul class="text-left text-sm mt-4 space-y-1 text-slate-600">' + 
                                 this.selectedNames.map(n => `<li>• ${n}</li>`).join('') + 
                                 '</ul>';
                Swal.fire({
                    title: 'Hapus Satker Terpilih?',
                    html: `Satker berikut akan dihapus beserta anak-anaknya:<br> ${listHtml}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#cbd5e1',
                    confirmButtonText: 'Ya, Hapus Semua'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.clipboard.mode = 'delete';
                        this.clipboard.ids = this.selectedIds;
                        this.executePaste(null); 
                    }
                });
            }
        });
    });
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

            const editWilayah = document.querySelector('#modalEditSatker select[name="wilayah_id"]');
            if (editWilayah) editWilayah.value = wilayah_id;
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

            toggleModal('modalDetailSatker');

            // 4. Fetch berdasarkan SATKER ID
            try {
                const timestamp = new Date().getTime();
                const response = await fetch(`{{ url('admin/satker/users') }}/${id}?_t=${timestamp}`, {
                    method: 'GET',
                    headers: {
                        'Pragma': 'no-cache',
                        'Cache-Control': 'no-cache'
                    }
                });
                const users = await response.json();

                tableBody.innerHTML = '';

                if (users.length > 0) {
                    users.forEach((user, index) => {
                        // LOGIKA UNTUK STATUS BADGE
                        let statusBadge = '';
                        if (user.is_cuti) {
                            statusBadge = `<span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded border border-amber-200">SEDANG CUTI</span>`;
                        } else if (user.status_aktif == 1) {
                            statusBadge = `<span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">AKTIF</span>`;
                        } else {
                            statusBadge = `<span class="text-[10px] font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200">NON-AKTIF</span>`;
                        }

                        // LOGIKA UNTUK TOMBOL (Selalu Tampil, dengan jebakan SweetAlert)
                        let actionButton = '';
                        
                        if (user.status_aktif == 1 || user.is_cuti) { 
                            actionButton = '<div class="flex flex-col gap-1.5 min-w-[85px]">';
                            
                            // Siapkan fungsi penolakan atau fungsi asli berdasarkan izin
                            let endAction = user.can_end ? `unassignUser('${user.penugasan_id}', '${user.name}', 'selesai')` : `Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Mengakhiri Tugas pegawai ini.', 'error')`;
                            let cutiAction = user.can_cuti ? `unassignUser('${user.penugasan_id}', '${user.name}', 'cuti')` : `Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Mencutikan pegawai ini.', 'error')`;

                            if (!user.is_cuti) {
                                actionButton += `<button onclick="${endAction}" class="w-full px-2 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Akhiri Tugas Permanen"><i class="fas fa-check-circle mr-1.5"></i> Selesai</button>`;
                                actionButton += `<button onclick="${cutiAction}" class="w-full px-2 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Mulai Cuti"><i class="fas fa-calendar-minus mr-1.5"></i> Cuti</button>`;
                            } else {
                                actionButton += `<button onclick="showDetailCuti('${user.name}', '${user.tanggal_mulai_cuti_raw}', '${user.tanggal_selesai_cuti_raw}')" class="w-full px-2 py-1.5 bg-sky-500 hover:bg-sky-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Lihat Detail Waktu Cuti"><i class="fas fa-info-circle mr-1.5"></i> Detail Cuti</button>`;
                                actionButton += `<button onclick="${endAction}" class="w-full px-2 py-1.5 bg-red-500 hover:bg-red-600 text-white text-[10px] font-bold rounded-lg transition-all shadow-sm flex items-center justify-center" title="Akhiri Tugas Permanen"><i class="fas fa-check-circle mr-1.5"></i> Selesai</button>`;
                            }
                            actionButton += '</div>';
                        } else {
                            actionButton = '-'; // Jika Non-Aktif, tidak ada tombol aksi
                        }

                        // RENDER BARIS TABEL
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


        // Fungsi Tambah Sub Satker (Ditempel ke window agar terbaca global oleh tombol HTML)
        window.openTambahSubSatker = function(parentId, parentJenisId, wilayahId, periodeId, lockParent = false) {
            const form = document.querySelector('#modalTambahSatker form');
            if (form) form.reset();

            toggleModal('modalTambahSatker');

            const wilayahSelect = document.getElementById('wilayah_id');
            if (wilayahSelect) {
                wilayahSelect.value = wilayahId;
                if (typeof handleWilayahChange === 'function') handleWilayahChange();
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
            
            // JAWABAN FEEDBACK: Jika parent eselon 5 atau sudah Tugas Tambahan (6), anak otomatis jadi level 6
            let nextLevel = parseInt(parentJenisId) + 1;
            if (nextLevel > 6) nextLevel = 6; 

            // Set select level UI
            if (jenisSelect && jenisSelect.querySelector(`option[value="${nextLevel}"]`)) {
                jenisSelect.value = nextLevel;
            }

            if (typeof filterParent === 'function') filterParent();

            setTimeout(() => {
                if (typeof control !== 'undefined' && control) {
                    control.setValue(parentId, true);
                } else if (parentSelect) {
                    parentSelect.value = parentId;
                }

                // =========================
                // LOCK jika dari tombol tambah
                // =========================
                if (lockParent) {
                    // 1. Lock visual Select
                    if (jenisSelect) {
                        jenisSelect.value = nextLevel;
                        jenisSelect.disabled = true;
                    }

                    // 2. Set dan Aktifkan Hidden Input agar terkirim ke server (Controller)
                    const hiddenJenis = document.getElementById('hidden_jenis_satker_id');
                    if (hiddenJenis) {
                        hiddenJenis.value = nextLevel;
                        hiddenJenis.disabled = false; 
                    }

                    // 3. Hal yang sama untuk Parent ID
                    const hiddenParent = document.getElementById('hidden_parent_satker_id');
                    if (hiddenParent) {
                        hiddenParent.value = parentId;
                        hiddenParent.disabled = false;
                    }

                    if (typeof control !== 'undefined' && control) {
                        control.disable();
                    } else if (parentSelect) {
                        parentSelect.disabled = true;
                    }
                } else {
                    // Normal state: Matikan hidden input agar tidak bentrok dengan select utama
                    if (jenisSelect) jenisSelect.disabled = false;
                    
                    const hiddenJenis = document.getElementById('hidden_jenis_satker_id');
                    if (hiddenJenis) hiddenJenis.disabled = true;
                    
                    const hiddenParent = document.getElementById('hidden_parent_satker_id');
                    if (hiddenParent) hiddenParent.disabled = true;

                    if (typeof control !== 'undefined' && control) control.enable();
                    else if (parentSelect) parentSelect.disabled = false;
                }

                if (typeof updateFullCode === "function") {
                    updateFullCode();
                }

            }, 50);
        };
    </script>
    <script>
        function resetTambahSatkerModal() {
            const form = document.querySelector('#modalTambahSatker form');
            if (form) form.reset();

            const filterFungsi = document.getElementById('filter_fungsi');
            if (filterFungsi) filterFungsi.value = "";

            const jenisSelect = document.getElementById('jenis_satker_id');
            const parentSelect = document.getElementById('parent_satker_id');
            const wilayahSelect = document.getElementById('wilayah_id');
            const periodeContainer = document.getElementById('container_periode');

            const containerFungsi = document.getElementById('container_filter_fungsi');
            if (containerFungsi) containerFungsi.classList.add('hidden');

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

            // reset rumus (jika ada)
            const rumusSelect = document.getElementById('rumus_id');
            if (rumusSelect) {
                rumusSelect.value = "";
            }

            const containerFakultas = document.getElementById('container_rumpun_fakultas');
            if (containerFakultas) containerFakultas.classList.add('hidden');
            const dropdownFakultas = document.getElementById('rumpun_fakultas');
            if (dropdownFakultas) dropdownFakultas.value = "";

            // reset rumus (jika ada)
            const rumusSelect = document.getElementById('rumus_id');
            if (rumusSelect) {
                rumusSelect.value = "";
            }

            const rumusSelect = document.getElementById('rumus_id');
            const activeRumusId = rumusSelect ? rumusSelect.value : null;
            const namaInput = document.getElementById('nama_satker');

            if (activeRumusId && namaInput && window.rumusDatabase) {
                const rumus = window.rumusDatabase.find(r => r.id == activeRumusId);
                
                // KONDISI 1: JIKA RUMUS MENGGUNAKAN NAMA OTOMATIS
                if (rumus && (rumus.is_auto_name == 1 || rumus.is_auto_name === true)) {
                    
                    // 1. Ambil ujung kode (suffix) untuk dicocokkan dengan map
                    let digitCount = parseInt(rumus.digit_auto_number) || 2;
                    let suffix = fullCode.slice(-digitCount); // Ambil x digit dari belakang
                    
                    // (Khusus Jabatan Fungsional, pakai seluruh kode)
                    const statJab = document.getElementById('tanpa_jabatan')?.value;
                    if (statJab === 'jabatan_fungsional') suffix = fullCode;

                    // 2. Siapkan nama dasar
                    let finalName = rumus.base_auto_name || '';

                    // 3. Timpa dengan nama dari Map jika ujung kodenya terdaftar
                    if (rumus.custom_names_map) {
                        try {
                            let map = typeof rumus.custom_names_map === 'string' ? JSON.parse(rumus.custom_names_map) : rumus.custom_names_map;
                            if (map && map[suffix]) {
                                finalName = map[suffix];
                            }
                        } catch(e) { console.error("Map parsing error", e); }
                    }

                    // 4. Terapkan ke Input Box
                    namaInput.value = finalName;
                    
                    // 5. Atur status Lock / Kunci
                    if (rumus.is_name_locked == 1 || rumus.is_name_locked === true) {
                        // Berikan spasi di akhir agar sistem anti-backspace bawaan Anda bekerja
                        namaInput.dataset.staticText = finalName + (finalName.endsWith(' ') ? '' : ' ');
                    } else {
                        namaInput.dataset.staticText = ""; // Bebas diedit
                    }

                } 
                // KONDISI 2: JIKA TIDAK PAKAI NAMA OTOMATIS (DEFAULT)
                else {
                    // Biarkan logika lama Anda (seperti Fakultas, Tata Usaha, Madrasah) tetap berjalan.
                    // Kita hanya mengisi default dari server JIKA kotak input masih benar-benar kosong.
                    if (data.default_nama && !namaInput.value.trim()) {
                        namaInput.value = data.default_nama;
                        namaInput.dataset.staticText = ""; 
                    }
                }
            }

            // Kembalikan posisi div rumus ke default saat modal ditutup
            const containerRumus = document.getElementById('container_rumus_manual');
            const anchorDefault = document.getElementById('anchor_rumus_default');
            if (anchorDefault && containerRumus) anchorDefault.appendChild(containerRumus);
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
                    
                    const processSubmit = async (formDataObj) => {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
                        
                        let result = null; // DEKLARASIKAN DI LUAR TRY AGAR BISA DIBACA OLEH FINALLY
                        
                        try {
                            const response = await fetch(formPenugasan.action, {
                                method: 'POST',
                                body: formDataObj,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            result = await response.json();

                            // ========================================================
                            // TANGKAP RESPON KONFIRMASI (PLT/PLH DITEMUKAN)
                            // ========================================================
                            if (result.require_confirmation) {
                                Swal.fire({
                                    title: 'Konfirmasi Penggantian',
                                    text: result.message,
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3b82f6',
                                    cancelButtonColor: '#cbd5e1',
                                    confirmButtonText: 'Ya, Lanjutkan',
                                    cancelButtonText: 'Batal'
                                }).then((confirmRes) => {
                                    if (confirmRes.isConfirmed) {
                                        formDataObj.append('confirm_override', '1');
                                        processSubmit(formDataObj); 
                                    } else {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalText;
                                    }
                                });
                                return; // Hentikan eksekusi yang ini, tunggu respon user
                            }
                            // ========================================================
                            
                            if (response.ok && result.success) {
                                // Jika Sukses
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: result.message,
                                    timer: 2500,
                                    showConfirmButton: false
                                });
                                
                                toggleModal('modalTambahPenugasan');
                                formPenugasan.reset();
                                const tomSelectEl = document.getElementById('select_pegawai_local');
                                if (tomSelectEl && tomSelectEl.tomselect) tomSelectEl.tomselect.clear();
                                document.getElementById('res_nama').value = '';
                                document.getElementById('res_info_tambahan').innerText = '';
                                
                                const satkerId = document.getElementById('detail_satker_id').value;
                                const satkerKode = document.getElementById('detail_kode').innerText;
                                const satkerNama = document.getElementById('detail_nama').innerText;
                                const satkerEselon = document.getElementById('detail_eselon').innerText;
                                const satkerWilayah = document.getElementById('detail_wilayah').innerText;
                                
                                openDetailModal(satkerKode, satkerNama, satkerEselon, satkerWilayah, 1, satkerId);
                                
                            } else if (response.status === 422) {
                                // Jika Gagal Validasi Bawaan Laravel
                                let errorText = 'Silakan periksa kembali input Anda:\n';
                                for (let key in result.errors) {
                                    errorText += `- ${result.errors[key][0]}\n`;
                                }
                                Swal.fire('Validasi Gagal', errorText, 'error');
                            } else {
                                // Jika Gagal karena Aturan Bisnis (ex: Regulasi / Double Definitif)
                                Swal.fire('Gagal', result.message || 'Terjadi kesalahan sistem.', 'error');
                            }
                            
                        } catch (error) {
                            console.error(error);
                            Swal.fire('Error', 'Terjadi kesalahan jaringan atau server.', 'error');
                        } finally {
                            // Kembalikan tombol seperti semula jika tidak sedang menunggu konfirmasi
                            if (!result || !result.require_confirmation) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            }
                        }
                    };

                    // Jalankan fungsi submit pertama kali dengan data awal
                    processSubmit(new FormData(this));
                });
            }
        });

        function toggleBalaiOptions() {
            const isChecked = document.getElementById('is_balai_checkbox').checked;
            const container = document.getElementById('container_opsi_balai');
            const select = document.getElementById('start_num_balai');
            
            if (isChecked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                select.value = '';
            }
        }

        // Opsional tapi keren: otomatis ngisi input nama satker!
        function updateNamaBalai() {
            const select = document.getElementById('start_num_balai');
            const namaInput = document.getElementById('nama_satker');
            
            if(select.value === "11") {
                namaInput.value = "Balai Pendidikan dan Pelatihan ";
            } else if (select.value === "31") {
                namaInput.value = "Balai Penelitian dan Pengembangan ";
            }
        }
    </script>

    <script>
        // ==============================================================
        // 1. DATA MASTER & INISIALISASI
        // ==============================================================
        const refJabatan = @json($refJabatanSatker ?? \Illuminate\Support\Facades\DB::table('ref_jabatan_satker')->get());
        window.allRumusData = @json($rumusList ?? \Illuminate\Support\Facades\DB::table('rumus_kodes')->orderBy('nama_rumus', 'asc')->get());
        window.existingSatkers = @json(\Illuminate\Support\Facades\DB::table('satker')->select('kode_satker', 'periode_id')->get()); 
        
        const allParentOptions = Array.from(document.querySelectorAll('#parent_satker_id option')).filter(opt => opt.value !== "").map(opt => ({
            id: opt.value, text: opt.text, eselon: parseInt(opt.getAttribute('data-eselon')), periode: opt.getAttribute('data-periode') || ''
        }));

        let control = null;
        let rumusTomSelect = null;

        document.addEventListener("DOMContentLoaded", function() {
            const parentSelectEl = document.getElementById('parent_satker_id');
            if (parentSelectEl) {
                if (parentSelectEl.tomselect) parentSelectEl.tomselect.destroy();
                control = new TomSelect('#parent_satker_id', {
                    onChange: function(value) { if (typeof updateDropdownRumus === 'function') updateDropdownRumus(); },
                    render: {
                        option: function(data, escape) { return `<div class="py-1"><div class="text-sm text-slate-700 leading-relaxed">${escape(data.text)}</div></div>`; },
                        item: function(data, escape) { return `<div class="text-slate-700">${escape(data.text)}</div>`; }
                    }
                });
            }

            const rumusEl = document.getElementById('rumus_id');
            if (rumusEl) {
                if (rumusEl.tomselect) rumusEl.tomselect.destroy();
                rumusTomSelect = new TomSelect('#rumus_id', {
                    valueField: 'id', labelField: 'nama_rumus', searchField: ['nama_rumus', 'preview_text'],
                    onChange: function(value) {
                        const namaSatkerInput = document.getElementById('nama_satker');
                        if (value !== "") {
                            const selectedOption = this.options[value];
                            if (selectedOption && selectedOption.nama_rumus && namaSatkerInput) {
                                namaSatkerInput.value = selectedOption.nama_rumus + " ";
                                namaSatkerInput.dataset.staticText = selectedOption.nama_rumus + " "; 
                            }
                        } else {
                            if(typeof handleJabatanChange === 'function') handleJabatanChange();
                        }
                    },
                    render: {
                        option: function(data, escape) {
                            if(data.id === "") return `<div class="font-bold text-slate-600 py-2">${escape(data.nama_rumus)}</div>`;
                            let badge = (data.is_applied == 1 || data.is_applied == true) ? '' : `<span class="ml-2 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Tidak Aktif</span>`;
                            return `
                                <div class="py-1.5 flex flex-col">
                                    <div class="flex items-center">
                                        <span class="text-sm font-semibold text-slate-800">${escape(data.nama_rumus)}</span>${badge}
                                    </div>
                                    <span class="text-[11px] font-mono text-slate-500 mt-0.5">Output: <span class="text-slate-700">${escape(data.prefix)}</span><span class="text-blue-600 font-bold bg-blue-100 px-1 rounded ml-[2px]">${escape(data.suffix)}</span></span>
                                </div>`;
                        },
                        item: function(data, escape) {
                            if(data.id === "") return `<div class="font-bold text-slate-600">${escape(data.nama_rumus)}</div>`;
                            return `<div class="flex items-center font-semibold text-sm text-slate-800">${escape(data.nama_rumus)} <span class="font-mono text-xs ml-2 text-slate-500">(${escape(data.prefix)}<span class="text-blue-600 font-bold ml-[2px]">${escape(data.suffix)}</span>)</span></div>`;
                        }
                    }
                });
            }

            // PERBAIKAN FEEDBACK 2: Kunci input nama satker untuk teks tertentu
            const namaSatkerInputEvent = document.getElementById('nama_satker');
            if (namaSatkerInputEvent) {
                namaSatkerInputEvent.addEventListener('input', function(e) {
                    const lockedPhrases = ["Madrasah Ibtidaiyah Negeri", "Wakil Rektor Bidang", "Kantor Urusan Agama"];
                    let staticText = this.dataset.staticText || '';
                    
                    // Cek apakah staticText saat ini mengandung salah satu kata yang dilindungi
                    let shouldLock = lockedPhrases.some(phrase => staticText.includes(phrase));
                    
                    if (shouldLock) {
                        // Jika user mencoba menghapus kata utama, paksa kembalikan
                        if (!this.value.startsWith(staticText)) {
                            this.value = staticText;
                        }
                    }
                });
            }

            const triggerIds = ['jenis_satker_id', 'parent_satker_id', 'wilayah_id', 'tanpa_jabatan', 'kategori_kotakab', 'jenis_madrasah', 'kategori_unit', 'jabatan_id'];
            triggerIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('change', function() {
                    checkFungsiVisibility(); 
                    updateDropdownRumus();
                    
                    // PERBAIKAN: Cegah reset paksa! Jangan panggil handleJabatanChange untuk setiap dropdown
                    if (id === 'tanpa_jabatan') {
                        handleJabatanChange(); // Hanya reset jika Status Jabatan Induknya yang diubah
                    } else if (id === 'kategori_kotakab' || id === 'jenis_madrasah') {
                        updateNamaSatkerDariKabupaten();
                    }
                });
            });

            document.getElementById('kode_container')?.addEventListener('input', function(e) { if (e.target.tagName === 'INPUT') updateFullCode(); });

            document.getElementById('formTambahSatker')?.addEventListener('submit', function(e) {
                const finalCode = document.getElementById('kode_satker_full').value;
                if (!finalCode) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Kode Satker Kosong', text: 'Silahkan klik tombol magic untuk generate kode.' });
                }
            });
            setTimeout(updateDropdownRumus, 500);
        });

        // ==============================================================
        // LOGIKA KATEGORI WILAYAH (POIN 3)
        // ==============================================================
        function handleKategoriWilayahChange() {
            const kategoriSelect = document.getElementById('kategori_wilayah');
            const wilayahContainer = document.getElementById('container_wilayah');
            const wilayahSelect = document.getElementById('wilayah_id');

            // --- TAMBAHAN LOGIKA PINDAH POSISI RUMUS TERSIMPAN ---
            const containerRumus = document.getElementById('container_rumus_manual');
            const anchorPtkn = document.getElementById('anchor_rumus_ptkn');
            const anchorDefault = document.getElementById('anchor_rumus_default');

            // Jika PTKN (4) terpilih, pindahkan ke atas. Jika tidak, kembalikan ke bawah.
            if (kategoriSelect.value === '4') {
                if (anchorPtkn && containerRumus) anchorPtkn.appendChild(containerRumus);
            } else {
                if (anchorDefault && containerRumus) anchorDefault.appendChild(containerRumus);
            }
            // -----------------------------------------------------

            if (kategoriSelect.value === "") {
                wilayahContainer.classList.add('hidden');
                wilayahSelect.value = "";
                wilayahSelect.removeAttribute('required');
                handleWilayahChange();
                return;
            }

            const targetTingkat = kategoriSelect.value;

            // KUNCI PERBAIKAN: Lepaskan status 'disabled' pada semua opsi terlebih dahulu
            // agar browser mau mengirimkan data ini ke server (Laravel) saat di-submit.
            const options = wilayahSelect.querySelectorAll('option');
            options.forEach(opt => {
                opt.hidden = false;
                opt.disabled = false;
            });

            // Jika Pusat atau PTKN -> Auto-Select dan Sembunyikan Dropdown Detail
            if (targetTingkat === '1' || targetTingkat === '4') {
                wilayahContainer.classList.add('hidden');
                wilayahSelect.removeAttribute('required');
                
                // Cari opsi yang sesuai di dropdown wilayah, lalu pilih diam-diam
                for (let opt of options) {
                    if (opt.getAttribute('data-tingkat') === targetTingkat) {
                        wilayahSelect.value = opt.value;
                        break;
                    }
                }
            } 
            // Jika Satker Daerah -> Tampilkan Dropdown Provinsi untuk dipilih
            else if (targetTingkat === '2') {
                wilayahContainer.classList.remove('hidden');
                wilayahSelect.setAttribute('required', 'required');
                wilayahSelect.value = ""; // Reset agar user harus milih
                
                // Hanya tampilkan provinsi (tingkat wilayah 2), sisanya disable lagi
                options.forEach(opt => {
                    if (opt.value === "") return;
                    if (opt.getAttribute('data-tingkat') !== targetTingkat) {
                        opt.hidden = true; 
                        opt.disabled = true; // Disable yang bukan provinsi
                    }
                });
            }
            
            // Panggil trigger ke Jabatan dkk
            handleWilayahChange();
            checkFungsiVisibility();
            updateDropdownRumus();
        }

        function openTambahModalWithPeriode(periodeId) {
            resetTambahSatkerModal(); 
            document.getElementById('periode_id').value = periodeId; 
            toggleModal('modalTambahSatker'); 
            filterParent(); 
        }

        function getPrediksiKode(pola, parentCode, periodeId) {
            let rawStr = pola || "";
            if (rawStr.includes("[PARENT]")) {
                let pCode = parentCode && parentCode !== "[-]" ? parentCode : "[Induk]";
                rawStr = rawStr.replace(/\[PARENT\]/g, pCode);
            }

            let finalPrefix = ""; let finalSuffix = "";
            let incRegex = /\[INC:(\d+)(?:,\s*START:(\d+))?\]/g;
            let match = incRegex.exec(rawStr);
            
            if (match) {
                finalPrefix = rawStr.substring(0, match.index);
                let digits = parseInt(match[1]);
                let startNum = parseInt(match[2] || '1');
                
                const startNumBalai = document.getElementById('start_num_balai')?.value;
                const containerBalai = document.getElementById('container_opsi_balai');
                if (startNumBalai && containerBalai && !containerBalai.classList.contains('hidden')) startNum = parseInt(startNumBalai);
                
                finalSuffix = String(startNum).padStart(digits, '0');
                
            } else { 
                finalPrefix = rawStr; 
                finalSuffix = ""; 
            }
            
            return { prefix: finalPrefix, suffix: finalSuffix };
        }

        const fungsiConfig = {
            "2": { "SPI": ["61"] },
            "3": { 
                "Tata Usaha": ["01"],
                "Pendidikan Islam": ["02", "03", "04", "05", "06"], "Bimas Islam": ["07", "08", "09", "10", "11"], "Haji dan Umrah": ["10", "12"],
                "Bimas Kristen": ["21", "22", "23", "24", "25"], "Bimas Katolik": ["26", "27", "28", "29"], "Bimas Hindu": ["30", "31", "32", "33"],
                "Bimas Buddha": ["34"], "Bimas Khonghucu": ["35"]
            },
            "4": { 
                "Tata Usaha": ["01"],
                "Pendidikan Islam": ["02", "03", "04", "05", "06", "07", "08", "09", "10"], "Bimas Islam": ["11", "12", "13", "14", "15", "16", "17", "18", "07", "08", "09"],
                "Haji dan Umrah": ["19", "20", "08", "09"], "Bimas Kristen": ["21", "22", "23", "24", "25", "26", "27"], "Bimas Katolik": ["28", "29", "30", "31", "32"],
                "Bimas Hindu": ["33", "34", "35", "36", "37"], "Bimas Buddha": ["38", "39", "40", "37"], "Bimas Khonghucu": ["41"]
            }
        };

        function checkFungsiVisibility() {
            const jenisId = document.getElementById('jenis_satker_id')?.value; 
            const wilayahSelect = document.getElementById('wilayah_id'); 
            const statusJabatan = document.getElementById('tanpa_jabatan')?.value; 
            const container = document.getElementById('container_filter_fungsi');
            
            let tingkatWilayah = "";
            if (wilayahSelect && wilayahSelect.selectedIndex >= 0) tingkatWilayah = wilayahSelect.options[wilayahSelect.selectedIndex]?.getAttribute('data-tingkat');

            const isPTKN = (tingkatWilayah === '4');
            const isKanwil = (tingkatWilayah === '2' && statusJabatan === 'jabatan_kanwil');
            const isKabKota = (tingkatWilayah === '2' && statusJabatan === 'jabatan_kotakab') || (tingkatWilayah === '3' && statusJabatan === 'jabatan_kotakab');
            
            // Logika Visibilitas Rumpun Fakultas
            const containerFakultas = document.getElementById('container_rumpun_fakultas');
            const dropdownFakultas = document.getElementById('rumpun_fakultas');
            
            if (jenisId === '2' && isPTKN) {
                if (containerFakultas) containerFakultas.classList.remove('hidden');
            } else {
                if (containerFakultas) containerFakultas.classList.add('hidden');
                if (dropdownFakultas) dropdownFakultas.value = "";
            }

            let showFilter = false;
            if (jenisId === '2' && isPTKN) showFilter = true; // Muncul di Eselon 2 PTKN (Khusus SPI)
            if (jenisId === '3' && isKanwil) showFilter = true;
            if (jenisId === '4' && isKabKota) showFilter = true;

            if (showFilter && container) {
                container.classList.remove('hidden');
                
                // Menyembunyikan option yang tidak relevan dengan Eselon-nya
                const filterEl = document.getElementById('filter_fungsi');
                const opts = filterEl.querySelectorAll('option');
                opts.forEach(opt => {
                    if(opt.value === "") return;
                    if(jenisId === '2') opt.style.display = (opt.value === 'SPI') ? 'block' : 'none'; // Eselon 2 HANYA SPI
                    else opt.style.display = (opt.value === 'SPI') ? 'none' : 'block'; // Eselon lain JANGAN tampilkan SPI
                });
            } else if (container) {
                container.classList.add('hidden');
                const filterEl = document.getElementById('filter_fungsi');
                if (filterEl) filterEl.value = "";
            }
        }

        function handleFilterFungsiChange() {
            // Selalu update dropdown rumus saat filter diubah
            updateDropdownRumus();
            
            const status = document.getElementById('tanpa_jabatan')?.value;
            // Arahkan ke fungsi penamaan masing-masing agar namanya ikut berubah otomatis
            if (status === 'jabatan_kotakab') {
                updateNamaSatkerDariKabupaten();
            } else if (status === 'jabatan_kanwil') {
                handleKategoriUnitChange();
            } else {
                // Fallback umum jika bukan keduanya
                const filterVal = document.getElementById('filter_fungsi').value;
                const jenisSatkerId = document.getElementById('jenis_satker_id').value;
                const namaSatkerInput = document.getElementById('nama_satker');
                
                if (filterVal === "Tata Usaha" && namaSatkerInput) {
                    let baseName = "Tata Usaha";
                    if (jenisSatkerId === "3") baseName = "Bagian Tata Usaha";
                    if (jenisSatkerId === "4") baseName = "Subbagian Tata Usaha";
                    namaSatkerInput.value = baseName;
                    namaSatkerInput.dataset.staticText = baseName + " ";
                }
            }
        }

        function updateDropdownRumus() {
            if (!rumusTomSelect) return;
            const formJenisId = document.getElementById('jenis_satker_id')?.value || "";
            const formJabatanId = document.getElementById('ref_jabatan_satker_id')?.value || "";
            const currentPeriodeId = document.getElementById('periode_id')?.value || "";
            const wilayahSelect = document.getElementById('wilayah_id');
            const filterFungsiVal = document.getElementById('filter_fungsi')?.value || ""; 
            const statusJabatan = document.getElementById('tanpa_jabatan')?.value || ""; 
            
            let formTingkatWilayahId = "";
            if (wilayahSelect && wilayahSelect.selectedIndex >= 0) formTingkatWilayahId = wilayahSelect.options[wilayahSelect.selectedIndex]?.getAttribute('data-tingkat') || "";

            if (statusJabatan === 'jabatan_kotakab') formTingkatWilayahId = "3"; // Paksa Kab/Kota untuk Eselon 4

            let parentCode = "[-]";
            if (control) {
                const selectedParentId = control.getValue();
                if (selectedParentId && control.options[selectedParentId]) parentCode = control.options[selectedParentId].text.split('-')[0].trim();
            }

            let validRumus = (window.allRumusData || []).filter(rm => {
                let matchJenis = false;
                if (formJenisId === "4") matchJenis = (rm.jenis_satker_id == 4); 
                else if (formJenisId === "3") matchJenis = (!rm.jenis_satker_id || rm.jenis_satker_id == 3); 
                else matchJenis = (!rm.jenis_satker_id || rm.jenis_satker_id == formJenisId);

                let matchWilayah = (!rm.tingkat_wilayah_id || rm.tingkat_wilayah_id == formTingkatWilayahId);
                let matchJabatan = (!rm.ref_jabatan_satker_id || rm.ref_jabatan_satker_id == formJabatanId);
                let matchPola = true;
                
                if (formJenisId === "1" && (rm.pola || "").includes('[PARENT]')) matchPola = false;
                else if (formJenisId !== "1" && formJenisId !== "" && !(rm.pola || "").includes('[PARENT]')) matchPola = false;

                let matchFungsi = true;
                if (filterFungsiVal !== "") {
                    let matchStart = /START:(\d+)/.exec(rm.pola || "");
                    if (matchStart) {
                        let startNum = matchStart[1].padStart(2, '0'); 
                        let configEselon = fungsiConfig[formJenisId] || {};
                        let allowedStarts = configEselon[filterFungsiVal] || [];
                        if (!allowedStarts.includes(startNum)) matchFungsi = false;
                    } else matchFungsi = false; 
                }

                return matchJenis && matchWilayah && matchJabatan && matchPola && matchFungsi;
            });

            let calculatedOptions = [];
            validRumus.forEach(rm => {
                let parsed = getPrediksiKode(rm.pola || "", parentCode, currentPeriodeId);
                calculatedOptions.push({ id: rm.id, nama_rumus: rm.nama_rumus, prefix: parsed.prefix, suffix: parsed.suffix, preview_text: parsed.prefix + parsed.suffix, is_applied: rm.is_applied });
            });

            calculatedOptions.sort((a, b) => {
                let numA = parseInt(a.suffix) || 0;
                let numB = parseInt(b.suffix) || 0;
                return numA - numB;
            });

            let optionsData = [{ id: "", nama_rumus: "-- Default (Sistem Otomatis) --", prefix: "", suffix: "", preview_text: "", is_applied: true }, ...calculatedOptions];
            validRumus.forEach(rm => {
                let parsed = getPrediksiKode(rm.pola || "", parentCode, currentPeriodeId);
                optionsData.push({ id: rm.id, nama_rumus: rm.nama_rumus, prefix: parsed.prefix, suffix: parsed.suffix, preview_text: parsed.prefix + parsed.suffix, is_applied: rm.is_applied });
            });

            let currentValue = rumusTomSelect.getValue();
            rumusTomSelect.clear(true);
            rumusTomSelect.clearOptions();
            rumusTomSelect.addOptions(optionsData);
            if (optionsData.some(opt => String(opt.id) === String(currentValue))) rumusTomSelect.setValue(currentValue, true);
        }

        window.filterParent = function() {
            const jenisSelect = document.getElementById('jenis_satker_id');
            if(!jenisSelect) return;
            const jenisId = jenisSelect.value;
            const currentPeriodeId = document.getElementById('periode_id')?.value || "";
            const targetParentEselon = parseInt(jenisId) - 1;
            
            const parentContainer = document.getElementById('parent_container');
            const wrapperBalai = document.getElementById('wrapper_opsi_balai');
            const jabatanContainer = document.getElementById('container_status_jabatan'); 

            if (jenisId === '4') { if (wrapperBalai) wrapperBalai.classList.remove('hidden'); } 
            else {
                if (wrapperBalai) {
                    wrapperBalai.classList.add('hidden'); document.getElementById('is_balai_checkbox').checked = false;
                    if(typeof toggleBalaiOptions === 'function') toggleBalaiOptions(); 
                }
            }

            if (jenisId !== "" && parseInt(jenisId) >= 2) {
                if(jabatanContainer) jabatanContainer.classList.remove('hidden');
            } else {
                if(jabatanContainer) jabatanContainer.classList.add('hidden');
                document.getElementById('tanpa_jabatan').value = "";
                document.getElementById('container_jabatan_fungsional').classList.add('hidden');
                const namaSatkerInput = document.getElementById('nama_satker');
                if (namaSatkerInput) { namaSatkerInput.value = ''; namaSatkerInput.dataset.staticText = ''; }
                updateRefJabatanId();
            }

            if (jenisId === "1" || jenisId === "") {
                if(parentContainer) parentContainer.classList.add('hidden');
                if (control) control.clear();
            } else {
                if(parentContainer) parentContainer.classList.remove('hidden');
                const filteredData = allParentOptions.filter(opt => opt.eselon === targetParentEselon && opt.periode == currentPeriodeId);
                if (control) {
                    control.clearOptions();
                    filteredData.forEach(opt => control.addOption({ value: opt.id, text: opt.text, periode: opt.periode }));
                    control.refreshOptions(false);
                }
            }
            checkFungsiVisibility();
            updateDropdownRumus();
        };

        function resetKodeSatker() {
            const container = document.getElementById('kode_container');
            if(container) container.querySelectorAll('.generated-kode').forEach(el => el.remove());
            const fullCodeInput = document.getElementById('kode_satker_full');
            if (fullCodeInput) fullCodeInput.value = '';

            const infoContainer = document.getElementById('info_generate_container');
            if (infoContainer) {
                infoContainer.classList.add('hidden');
                infoContainer.innerHTML = '';
            }
            
            const gapContainer = document.getElementById('gap_selection_container');
            if (gapContainer) gapContainer.classList.add('hidden');
        }

        function updateFullCode() {
            const container = document.getElementById('kode_container');
            const inputs = container.querySelectorAll('input');
            const finalInput = document.getElementById('kode_satker_full');
            let combined = '';
            inputs.forEach(input => { combined += input.value.trim(); });
            if(finalInput) finalInput.value = combined;
        }

        async function generateSatkerCode(event) {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            const jenisId = document.getElementById('jenis_satker_id').value;
            const parentId = document.getElementById('parent_satker_id').value;
            const periodeId = document.getElementById('periode_id')?.value || '';

            if (!jenisId || (jenisId !== "1" && !parentId)) { Toast.fire({ icon: 'warning', title: !jenisId ? 'Pilih Jenis Satker Terlebih Dahulu!' : 'Pilih Satker Induk Terlebih Dahulu!' }); return; }
            if (!periodeId) { Toast.fire({ icon: 'warning', title: 'Pilih Periode Terlebih Dahulu!' }); return; }

            const originalIconClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            btn.disabled = true;

            const gapContainer = document.getElementById('gap_selection_container');
            const gapList = document.getElementById('gap_list');
            if(gapContainer) gapContainer.classList.add('hidden');
            if(gapList) gapList.innerHTML = '';

            const infoContainer = document.getElementById('info_generate_container');
            if (infoContainer) {
                infoContainer.classList.add('hidden');
                infoContainer.innerHTML = '';
            }

            try {
                const wilayahId = document.getElementById('wilayah_id')?.value || '';
                const refJabatanId = document.getElementById("ref_jabatan_satker_id")?.value || document.getElementById('jabatan_id')?.value || '';
                const startNumBalai = document.getElementById('start_num_balai')?.value || '';
                const rumusId = document.getElementById('rumus_id')?.value || '';

                const rumpunFakultas = document.getElementById('rumpun_fakultas')?.value || '';
                const jabatanId = document.getElementById('jabatan_id')?.value || '';

                const queryParams = new URLSearchParams({
                    jenis_id: jenisId, parent_id: parentId, ref_jabatan_satker_id: refJabatanId,
                    wilayah_id: wilayahId, periode_id: periodeId, start_num: startNumBalai,
                    rumus_id: rumusId, rumpun_fakultas: rumpunFakultas, jabatan_id: jabatanId, _t: Date.now()
                });

                const response = await fetch(`{{ url('admin/satker/generate-code') }}?${queryParams}`);
                
                const textResponse = await response.text();
                let data;
                try {
                    data = JSON.parse(textResponse);
                } catch (e) {
                    throw new Error("Terjadi kesalahan pada server (Data terformat HTML/Error). Silakan hubungi admin.");
                }

                if (!response.ok && !data.success) throw new Error(data.message || 'Gagal membuat kode satker.');

                const generatedCode = data.kode || data.code || "";
                const renderUiCode = (fullCode) => {
                    const container = document.getElementById('kode_container');
                    container.querySelectorAll('input').forEach(el => el.remove());

                    const statusJabatan = document.getElementById('tanpa_jabatan')?.value;

                    // JIKA ESELON 1 ATAU JABATAN FUNGSIONAL, SATUKAN DALAM 1 KOTAK PENUH
                    if (jenisId === "1" || statusJabatan === 'jabatan_fungsional') {
                        container.insertAdjacentHTML('afterbegin', `<input type="text" value="${fullCode}" readonly class="w-full px-3 py-2 bg-slate-200 border border-slate-300 rounded-xl text-sm text-center font-bold text-slate-800 tracking-widest shadow-inner generated-kode">`);
                    } else {
                        // JIKA SELAIN ITU, BELAH KOTAK MENJADI DUA (PARENT + ANAK)
                        const parentSelect = document.getElementById('parent_satker_id');
                        const parentText = parentSelect?.options[parentSelect.selectedIndex]?.text || (control && control.options[parentId] ? control.options[parentId].text : '');
                        const displayParentKode = parentText.split(' - ')[0].trim();
                        const prefixLength = displayParentKode.length;
                        const middle = fullCode.substring(prefixLength);

                        container.insertAdjacentHTML('afterbegin', `
                            <input type="text" value="${displayParentKode}" readonly class="w-40 px-3 py-2 bg-slate-200 border rounded-xl text-sm text-center font-medium generated-kode">
                            <input type="text" value="${middle}" readonly class="w-40 px-3 py-2 bg-slate-200 border rounded-xl text-sm text-center font-medium generated-kode">
                        `);
                    }

                    const finalInput = document.getElementById('kode_satker_full');
                    if (finalInput) finalInput.value = fullCode;

                    // ==============================================================
                    // MENGUNCI TEKS NAMA SATKER & LOGIKA RUMUS KHUSUS (TERMASUK AUTO NAME)
                    // ==============================================================
                    const namaInput = document.getElementById('nama_satker');
                    const rumusSelect = document.getElementById('rumus_id');
                    const activeRumusId = rumusSelect ? rumusSelect.value : null;

                    let isAutoNameApplied = false;

                    if (activeRumusId && window.allRumusData) {
                        const rumus = window.allRumusData.find(r => r.id == activeRumusId);
                        
                        if (rumus && (rumus.is_auto_name == 1 || rumus.is_auto_name === true)) {
                            isAutoNameApplied = true;
                            
                            // =========================================================
                            // PERBAIKAN: Deteksi digit INC secara dinamis dari Pola Rumus!
                            // =========================================================
                            let digitCount = 2; // Default bawaan
                            if (rumus.pola) {
                                // Ekstrak angka dari teks [INC:1], [INC:3], dll
                                let match = rumus.pola.match(/\[INC:(\d+)/);
                                if (match && match[1]) {
                                    digitCount = parseInt(match[1]);
                                }
                            }
                            
                            let suffix = fullCode.slice(-digitCount); 
                            // =========================================================
                            
                            // (Khusus Jabatan Fungsional, pakai seluruh kode)
                            const statJab = document.getElementById('tanpa_jabatan')?.value;
                            if (statJab === 'jabatan_fungsional') suffix = fullCode;

                            let finalName = rumus.base_auto_name || '';

                            if (rumus.custom_names_map) {
                                try {
                                    let map = typeof rumus.custom_names_map === 'string' ? JSON.parse(rumus.custom_names_map) : rumus.custom_names_map;
                                    if (map && map[suffix]) finalName = map[suffix];
                                } catch(e) {}
                            }

                            if (namaInput) {
                                namaInput.value = finalName;
                                if (rumus.is_name_locked == 1 || rumus.is_name_locked === true) {
                                    namaInput.dataset.staticText = finalName + (finalName.endsWith(' ') ? '' : ' ');
                                } else {
                                    namaInput.dataset.staticText = ""; 
                                }
                            }
                        }
                    }

                    // 2. JIKA TIDAK PAKAI AUTO NAME, GUNAKAN LOGIKA LAMA (DEFAULT)
                    if (!isAutoNameApplied && data.default_nama && namaInput) {
                        let namaRumus = data.default_nama;
                        
                        // Logika Khusus "Biro di PTKN" (Logika Sebelumnya)
                        if (namaRumus === 'Biro di PTKN') {
                            const suffix = fullCode.slice(-2);
                            const biroMap = {
                                '01': 'Biro Administrasi Umum, Perencanaan, dan Keuangan',
                                '02': 'Biro Administrasi Akademik, Kemahasiswaan, dan Kerja Sama',
                                '03': 'Biro Administrasi Umum, Akademik, dan Kemahasiswaan',
                                '04': 'Biro Perencanaan dan Keuangan',
                                '05': 'Biro Kepegawaian',
                                '06': 'Biro Keuangan dan BMN',
                                '07': 'Biro Organisasi dan Tata Laksana',
                                '08': 'Biro Hukum dan Kerjasama',
                                '09': 'Biro Umum'
                            };
                            namaRumus = biroMap[suffix] || 'Biro ';
                        }

                        // Logika Khusus "Kepala MAN IC sebagai Tugas Tambahan"
                        if (namaRumus === 'Kepala MAN IC sebagai Tugas Tambahan') {
                            namaRumus = 'Madrasah Aliyah Negeri Insan Cendikia';
                        }

                        const statJab = document.getElementById('tanpa_jabatan')?.value;

                        // Daftar kata yang akan dikunci
                        const isLockedName = namaRumus.includes('Madrasah Ibtidaiyah Negeri') || 
                                             namaRumus.includes('Wakil Rektor Bidang') || 
                                             namaRumus.includes('Kantor Urusan Agama') ||
                                             namaRumus.includes('Madrasah Aliyah Negeri Insan Cendikia');
                                             (statJab === 'jabatan_fungsional');

                        if (isLockedName) {
                            const lockedPrefix = namaRumus + ' ';
                            namaInput.value = lockedPrefix; 
                            namaInput.dataset.staticText = lockedPrefix; 
                            namaInput.focus(); 
                        } else if (namaRumus.startsWith('Biro')) {
                            namaInput.value = namaRumus;
                            namaInput.dataset.staticText = 'Biro '; 
                            namaInput.focus();
                        } else {
                            namaInput.value = namaRumus;
                            namaInput.dataset.staticText = '';
                        }
                    }

                    updateFullCode();
                };

                renderUiCode(generatedCode);

                if (infoContainer) {
                    infoContainer.classList.remove('hidden');
                    if (data.is_incremental) {
                        if (data.is_new) {
                            infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-blue-200 bg-blue-50 text-blue-700 leading-relaxed";
                            infoContainer.innerHTML = `<i class="fas fa-info-circle mr-1"></i> Belum ada satker yang menggunakan awalan kode ini. Kode akan dimulai dari <b class="font-mono text-sm">${generatedCode}</b>.`;
                        } else {
                            // LOGIKA FEEDBACK 1 & 4: KONDISI PESAN RINCI
                            if (data.next_num < data.max_num) {
                                infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-blue-200 bg-blue-50 text-blue-700 leading-relaxed";
                                infoContainer.innerHTML = `<i class="fas fa-info-circle mr-1"></i> Rumus ini belum ada yang menggunakan, di parent ini terakhir oleh <b>${data.last_nama}</b> (<span class="font-mono">${data.last_kode}</span>). Sistem akan memulai dari <b class="font-mono text-sm">${generatedCode}</b>.`;
                            } else if (data.is_different_formula) {
                                if (data.is_same_start) {
                                    infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-amber-200 bg-amber-50 text-amber-700 leading-relaxed";
                                    infoContainer.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i> Peringatan: Sudah ada satker dengan awalan ini. Kode saat ini akan melanjutkan ke <b class="font-mono text-sm">${generatedCode}</b>.`;
                                } else {
                                    infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-amber-200 bg-amber-50 text-amber-700 leading-relaxed";
                                    infoContainer.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> Awalan ini sudah digunakan oleh rumus lain. Terakhir dipakai oleh <b>${data.last_nama}</b> (<span class="font-mono">${data.last_kode}</span>). Sistem akan melanjutkan ke <b class="font-mono text-sm">${generatedCode}</b>.`;
                                }
                            } else {
                                infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 leading-relaxed";
                                infoContainer.innerHTML = `<i class="fas fa-check-circle mr-1"></i> Awalan ini sudah digunakan. Terakhir dipakai oleh <b>${data.last_nama}</b> (<span class="font-mono">${data.last_kode}</span>). Sistem akan melanjutkan ke <b class="font-mono text-sm">${generatedCode}</b>.`;
                            }
                        }
                    } else {
                        infoContainer.className = "mt-3 p-3 text-[11px] rounded-xl border border-slate-200 bg-slate-50 text-slate-700 leading-relaxed";
                        infoContainer.innerHTML = `<i class="fas fa-info-circle mr-1"></i> Rumus ini menghasilkan kode paten statis (tidak memiliki penambahan urutan).`;
                    }
                }

                if (data.gaps && data.gaps.length > 0 && gapContainer && gapList) {
                    gapContainer.classList.remove('hidden');
                    const btnNext = document.createElement('button');
                    btnNext.type = 'button';
                    btnNext.innerHTML = `Lanjut ( <span class="font-mono">${generatedCode.slice(-2)}</span> )`;
                    btnNext.className = "px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 font-bold transition shadow-sm";
                    btnNext.onclick = () => renderUiCode(generatedCode);
                    gapList.appendChild(btnNext);

                    data.gaps.forEach(gCode => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.innerHTML = `Gunakan ( <span class="font-mono text-amber-800">${gCode.slice(-2)}</span> )`;
                        btn.className = "px-3 py-1.5 text-xs bg-white border border-amber-300 text-amber-700 rounded hover:bg-amber-100 font-bold transition shadow-sm";
                        btn.onclick = () => renderUiCode(gCode);
                        gapList.appendChild(btn);
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Proses Ditolak',
                    text: error.message
                });
            } finally {
                icon.className = originalIconClass; btn.disabled = false;
            }
        }

        function handleWilayahChange() {
            const wilayahSelect = document.getElementById('wilayah_id');
            const jabatanSelect = document.getElementById('tanpa_jabatan');
            const kabupatenSelect = document.getElementById('kabupaten_id'); 
            const wilayahContainer = document.getElementById('container_wilayah');

            // Cek Dropdown Provinsi untuk List Kabupaten
            const selectedProvinsiId = wilayahSelect.value;
            if (kabupatenSelect) {
                const options = kabupatenSelect.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value === "") return; 
                    if (opt.getAttribute('data-parent') === selectedProvinsiId) {
                        opt.hidden = false; opt.disabled = false;
                    } else {
                        opt.hidden = true; opt.disabled = true;
                    }
                });
                kabupatenSelect.value = ""; 
            }

            if (!wilayahSelect.value && !wilayahContainer.classList.contains('hidden')) {
                jabatanSelect.innerHTML = '<option value="">-- Pilih Wilayah Terlebih Dahulu --</option>';
                handleJabatanChange();
                return;
            }

            const wilayahText = (wilayahSelect.options[wilayahSelect.selectedIndex]?.text || "").toLowerCase();
            jabatanSelect.innerHTML = '<option value="">-- Pilih Status Jabatan --</option>';

            let finalOptions = [];
            const commonOptions = refJabatan.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null && item.key_jabatan !== 'manajerial');

            // Sesuaikan filter dropdown berdasarkan kategori juga
            const kategoriValue = document.getElementById('kategori_wilayah')?.value;

            if (wilayahText.includes('pusat') || kategoriValue === '1') {
                finalOptions = refJabatan.filter(item => item.tingkat_wilayah_id === null && item.parent_id === null);
            } else if (wilayahText.includes('ptkn') || wilayahText.includes('universitas') || wilayahText.includes('institut') || kategoriValue === '4') {
                const ptknOptions = refJabatan.filter(item => item.tingkat_wilayah_id === 4 && item.parent_id === null);
                finalOptions = [...ptknOptions, ...commonOptions];
            } else {
                const wilayahOptions = refJabatan.filter(item => item.key_jabatan === 'jabatan_kanwil' || item.key_jabatan === 'jabatan_kotakab');
                finalOptions = [...wilayahOptions, ...commonOptions];
            }

            finalOptions.forEach(opt => {
                const el = document.createElement('option');
                el.value = opt.key_jabatan; el.textContent = opt.label_jabatan; el.dataset.id = opt.id; el.dataset.kode = opt.kode_dasar || "";
                jabatanSelect.appendChild(el);
            });

            handleJabatanChange();
        }

        function populateSubJabatan(parentUuid, targetSelect) {
            targetSelect.innerHTML = '<option value="">-- Pilih Kategori Unit --</option>';
            const jenisSatkerId = document.getElementById('jenis_satker_id').value;

            if (targetSelect.id === 'kategori_kotakab') {
                targetSelect.innerHTML += '<option value="madrasah">Madrasah (MIN/MTsN/MAN)</option>';
            }
            
            const children = refJabatan.filter(item => item.parent_id === parentUuid);
            children.forEach(child => {
                // REVISI: Jika Eselon 4, jangan tampilkan opsi yang mengandung kata "Tata Usaha"
                const isTataUsaha = child.label_jabatan.toLowerCase().includes('tata usaha');
                if (jenisSatkerId === '4' && isTataUsaha) {
                    return; // Skip/Jangan masukkan ke dropdown
                }

                const opt = document.createElement('option');
                opt.value = child.id;
                opt.text = child.label_jabatan;
                targetSelect.appendChild(opt);
            });
        }

        function handleJabatanChange() {
            const jabatanSelect = document.getElementById('tanpa_jabatan');
            const status = jabatanSelect.value;
            const selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];
            const parentUuid = selectedOption ? selectedOption.dataset.id : null;
            const namaSatkerInput = document.getElementById('nama_satker');
            const jenisSatker = document.getElementById('jenis_satker_id').value;

            // Kontainer yang harus dikelola
            const containers = [
                'container_kategori_unit', 
                'container_kategori_kotakab', 
                'container_kabupaten', 
                'container_jenis_madrasah', 
                'container_jabatan_fungsional',
                'container_rumpun_fakultas'
            ];
            
            // Sembunyikan semua dulu
            containers.forEach(id => { 
                const el = document.getElementById(id); 
                if (el) el.classList.add('hidden'); 
            });

            // Reset value elemen-elemen di dalamnya
            if(document.getElementById('kategori_unit')) document.getElementById('kategori_unit').value = "";
            if(document.getElementById('kategori_kotakab')) document.getElementById('kategori_kotakab').value = "";
            if(document.getElementById('jenis_madrasah')) document.getElementById('jenis_madrasah').value = "";
            if(document.getElementById('kategori_jabatan_fungsional')) document.getElementById('kategori_jabatan_fungsional').value = "";
            if(document.getElementById('jabatan_id')) {
                document.getElementById('jabatan_id').value = "";
                const wrapperJenjang = document.getElementById('wrapper_jenjang_jabatan');
                if (wrapperJenjang) wrapperJenjang.classList.add('hidden');
            }

            if (!status) {
                if (namaSatkerInput) {
                    namaSatkerInput.value = ''; 
                    namaSatkerInput.dataset.staticText = '';
                }
                updateRefJabatanId(); 
                if(typeof resetKodeSatker === 'function') resetKodeSatker(); 
                return;
            }

            // PERBAIKAN PENTING: Tampilkan kontainer Jafung dan pastikan BISA DIKLIK
            if (status === 'jabatan_fungsional') {
                const jfContainer = document.getElementById('container_jabatan_fungsional');
                if (jfContainer) {
                    jfContainer.classList.remove('hidden');
                    jfContainer.style.pointerEvents = 'auto'; // Mengizinkan klik
                }
            }

            const children = refJabatan.filter(item => item.parent_id === parentUuid);
            if (children.length > 0) {
                let targetSelect = null;
                if (status === 'jabatan_kotakab') {
                    document.getElementById('container_kabupaten').classList.remove('hidden');
                    if (jenisSatker === '4') { 
                        document.getElementById('container_kategori_kotakab').classList.remove('hidden');
                        targetSelect = document.getElementById('kategori_kotakab');
                        if (targetSelect.value !== "") handleKategoriKotaKabChange(); 
                    }
                } else if (jenisSatker === '3') { 
                    document.getElementById('container_kategori_unit').classList.remove('hidden');
                    targetSelect = document.getElementById('kategori_unit');
                }
                if (targetSelect && targetSelect.options.length <= 1) populateSubJabatan(parentUuid, targetSelect);
            }

            if (status === 'tidak_ada') {
                namaSatkerInput.value = 'Tidak Ada Jabatan'; namaSatkerInput.dataset.staticText = '';
            } else if (status === 'jabatan_fungsional') {
                // Di handle khusus oleh handleJenjangJabatanChange, jadi di sini cukup kosongkan saja
                namaSatkerInput.value = ''; 
                namaSatkerInput.dataset.staticText = '';
            } else if (status === 'jabatan_kotakab') {
                updateNamaSatkerDariKabupaten();
            } else if (status === 'jabatan_kanwil') {
                const kategoriUnitSelect = document.getElementById('kategori_unit');
                if (kategoriUnitSelect && kategoriUnitSelect.value !== "") {
                    const text = kategoriUnitSelect.options[kategoriUnitSelect.selectedIndex].text;
                    namaSatkerInput.value = text; namaSatkerInput.dataset.staticText = text + " ";
                } else {
                    namaSatkerInput.value = selectedOption.text; namaSatkerInput.dataset.staticText = ''; 
                }
            } else {
                namaSatkerInput.value = selectedOption.text; namaSatkerInput.dataset.staticText = ''; 
            }
            
            checkFungsiVisibility();
            updateRefJabatanId();
        }

        function updateNamaSatkerDariKabupaten() {
            const namaSatkerInput = document.getElementById('nama_satker');
            const wilayahSelect = document.getElementById('kabupaten_id');
            const madrasahSelect = document.getElementById('jenis_madrasah');
            const kategoriSelect = document.getElementById('kategori_kotakab');
            const jenisSatkerId = document.getElementById('jenis_satker_id').value;
            const filterFungsi = document.getElementById('filter_fungsi'); // Ambil nilai filter fungsi

            let baseName = "";
            
            if (madrasahSelect && madrasahSelect.value !== "") {
                baseName = madrasahSelect.options[madrasahSelect.selectedIndex].text;
            } 
            // LOGIKA TATA USAHA PINDAH KESINI (Membaca dari Filter Fungsi)
            else if (filterFungsi && filterFungsi.value === "Tata Usaha") {
                if (jenisSatkerId === "3") baseName = "Bagian Tata Usaha";
                else if (jenisSatkerId === "4") baseName = "Subbagian Tata Usaha";
                else baseName = "Tata Usaha";
            } 
            else if (kategoriSelect && kategoriSelect.value !== "" && kategoriSelect.value !== "madrasah") {
                baseName = kategoriSelect.options[kategoriSelect.selectedIndex].text;
            } 
            else if (wilayahSelect && wilayahSelect.value !== "") {
                baseName = wilayahSelect.options[wilayahSelect.selectedIndex].text;
            }

            namaSatkerInput.value = baseName; 
            namaSatkerInput.dataset.staticText = baseName ? baseName + " " : " ";
            
            checkFungsiVisibility();
        }

        function handleKategoriKotaKabChange() {
            const kategoriSelect = document.getElementById('kategori_kotakab');
            const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
            const parentUuid = selectedOption ? selectedOption.dataset.id : null;
            const madrasahDiv = document.getElementById('container_jenis_madrasah');
            const madrasahSelect = document.getElementById('jenis_madrasah');
            madrasahDiv.classList.add('hidden');
            const children = refJabatan.filter(item => item.parent_id === parentUuid);
            if (children.length > 0) {
                madrasahDiv.classList.remove('hidden'); populateSubJabatan(parentUuid, madrasahSelect);
            }
            updateNamaSatkerDariKabupaten(); updateRefJabatanId();
        }

        function handleJenisMadrasahChange() {
            if(typeof resetKodeSatker === 'function') resetKodeSatker();
            const jenisMadrasah = document.getElementById('jenis_madrasah');
            const namaSatkerInput = document.getElementById('nama_satker');
            if (jenisMadrasah.value !== "") {
                const staticName = jenisMadrasah.options[jenisMadrasah.selectedIndex].text;
                namaSatkerInput.dataset.staticText = staticName + " "; namaSatkerInput.value = staticName + " "; namaSatkerInput.focus();
            } else {
                namaSatkerInput.dataset.staticText = ""; namaSatkerInput.value = "";
            }
            updateRefJabatanId();
        }

        function handleJenisSatkerChange() {
            const jabatanSelect = document.getElementById('tanpa_jabatan');
            if (jabatanSelect) {
                jabatanSelect.value = "";
                handleJabatanChange(); 
            }
            
            checkFungsiVisibility();
            updateDropdownRumus();
            if(typeof resetKodeSatker === 'function') resetKodeSatker();
        }

        function handleRumpunFakultasChange() {
            const val = document.getElementById('rumpun_fakultas').value;
            const namaInput = document.getElementById('nama_satker');
            
            if (val !== "") {
                if (namaInput) {
                    namaInput.value = "Fakultas ";
                    namaInput.dataset.staticText = ""; 
                    namaInput.focus();
                }

                if (typeof generateSatkerCode === 'function') {
                    generateSatkerCode(); 
                }
            } else {
                if (namaInput && namaInput.value.trim() === "Fakultas") {
                    namaInput.value = "";
                }
                if (typeof resetKodeSatker === 'function') resetKodeSatker();
            }
            
            if (typeof updateDropdownRumus === 'function') updateDropdownRumus();
        }

        function handleKategoriUnitChange() {
            if(typeof resetKodeSatker === 'function') resetKodeSatker();
            const kategoriSelect = document.getElementById('kategori_unit');
            const namaSatkerInput = document.getElementById('nama_satker');
            const filterFungsi = document.getElementById('filter_fungsi');
            const jenisSatkerId = document.getElementById('jenis_satker_id').value;

            // LOGIKA TATA USAHA PINDAH KESINI (Membaca dari Filter Fungsi)
            if (filterFungsi && filterFungsi.value === "Tata Usaha") {
                let baseName = (jenisSatkerId === "3") ? "Bagian Tata Usaha" : ((jenisSatkerId === "4") ? "Subbagian Tata Usaha" : "Tata Usaha");
                namaSatkerInput.value = baseName;
                namaSatkerInput.dataset.staticText = baseName + " ";
            } 
            else if (kategoriSelect.value !== "") {
                namaSatkerInput.value = kategoriSelect.options[kategoriSelect.selectedIndex].text;
                namaSatkerInput.dataset.staticText = kategoriSelect.options[kategoriSelect.selectedIndex].text + " ";
            } else {
                const statusSelect = document.getElementById('tanpa_jabatan');
                if (statusSelect && statusSelect.value !== "") {
                    namaSatkerInput.value = statusSelect.options[statusSelect.selectedIndex].text;
                    namaSatkerInput.dataset.staticText = "";
                }
            }
            updateRefJabatanId();
        }

        function updateRefJabatanId() {
            let id = "";
            const selects = ["jenis_madrasah", "kategori_kotakab", "kategori_unit", "jabatan_id", "tanpa_jabatan"];
            for (let s of selects) {
                const el = document.getElementById(s);
                if (el && el.value) {
                    const opt = el.options[el.selectedIndex];
                    if (opt?.dataset?.id) { id = opt.dataset.id; break; }
                }
            }
            document.getElementById("ref_jabatan_satker_id").value = id;
            if(typeof updateDropdownRumus === 'function') updateDropdownRumus();
        }
    </script>

    <script>
        // FUNGSI UNTUK MERFRESH HIERARKI SATKER TANPA RELOAD PAGE
        async function refreshSatkerTree(targetId, action) {
            const container = document.getElementById('mainTreeContainer');
            if (!container) return;

            const currentScroll = container.scrollTop;
            const openFolderIds = [];

            // 1. Simpan state folder yang terbuka dengan membaca otak Alpine.js
            container.querySelectorAll('.satker-item').forEach(item => {
                if (typeof Alpine !== 'undefined') {
                    try {
                        if (Alpine.$data(item).open) {
                            const row = item.querySelector('.satker-row');
                            if (row && row.getAttribute('data-id')) {
                                openFolderIds.push(row.getAttribute('data-id'));
                            }
                        }
                    } catch (e) {}
                }
            });

            try {
                // Tampilkan loader
                const loaderHtml = `<div id="tree_loader" class="absolute top-4 right-4 bg-blue-600 shadow-lg rounded-full px-4 py-2 text-xs font-bold text-white z-50 flex items-center"><i class="fas fa-sync fa-spin mr-2"></i> Memperbarui Tampilan...</div>`;
                container.parentElement.insertAdjacentHTML('beforeend', loaderHtml);

                // Fetch DOM baru
                const response = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const htmlText = await response.text();

                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, 'text/html');
                const newTree = doc.getElementById('mainTreeContainer');

                if (newTree) {
                    // Update DOM secara instan
                    container.innerHTML = newTree.innerHTML;
                    container.scrollTop = currentScroll; // Tahan agar tidak lompat ke atas

                    // KUNCI: Beri jeda 200ms agar Alpine.js selesai me-restart DOM yang baru
                    setTimeout(() => {
                        
                        // 2. Kembalikan folder yang tadinya terbuka
                        openFolderIds.forEach(id => {
                            const row = container.querySelector(`.satker-row[data-id="${id}"]`);
                            if (row) {
                                const item = row.closest('.satker-item');
                                if (item && typeof Alpine !== 'undefined') {
                                    try { Alpine.$data(item).open = true; } catch(e) {}
                                }
                            }
                        });

                        // 3. Cari target, Buka Paksa Induknya, lalu Scroll (Metode Fitur Search)
                        if (targetId && action !== 'delete') {
                            const targetRow = container.querySelector(`.satker-row[data-id="${targetId}"]`);
                            
                            if (targetRow) {
                                // Rayap ke atas, temukan semua bungkus Alpine-nya, dan paksa ubah open = true
                                let currentEl = targetRow;
                                while(currentEl && currentEl.id !== 'mainTreeContainer') {
                                    if (currentEl.classList.contains('satker-item') && typeof Alpine !== 'undefined') {
                                        try { Alpine.$data(currentEl).open = true; } catch(e) {}
                                    }
                                    currentEl = currentEl.parentElement;
                                }

                                // Beri efek Highlight sama seperti fitur search
                                targetRow.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
                                setTimeout(() => {
                                    targetRow.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
                                }, 2500);

                                // Jeda ekstra untuk memastikan animasi buka folder selesai, lalu scroll!
                                setTimeout(() => {
                                    const cRect = container.getBoundingClientRect();
                                    const mRect = targetRow.getBoundingClientRect();
                                    
                                    container.scrollTo({
                                        top: container.scrollTop + (mRect.top - cRect.top) - 40,
                                        behavior: 'smooth'
                                    });
                                }, 250); 
                            }
                        }

                        document.getElementById('tree_loader')?.remove();
                    }, 200); 
                }
            } catch (err) {
                console.error('AJAX Refresh Error:', err);
                window.location.reload(); // Fallback jika gagal
            }
        }

        // INIT AJAX FORM SUBMISSION
        function initAjaxForm(formId, modalId, isDelete = false) {
            const form = document.getElementById(formId);
            if (!form) return;

            // Kita harus replace event listener lama, agar tidak submit dobel
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Proses...';

                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST', // Laravel menerima DELETE via method override di form
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        Toast.fire({ icon: 'success', title: result.message });
                        toggleModal(modalId);
                        
                        if (!isDelete) {
                            this.reset();
                            if(typeof resetTambahSatkerModal === 'function' && formId === 'formTambahSatker') resetTambahSatkerModal();
                        }

                        // Panggil Refresh Data Tanpa Reload
                        refreshSatkerTree(result.satker_id, isDelete ? 'delete' : 'save');

                    } else {
                        let errorMsg = result.message || 'Terjadi kesalahan sistem.';
                        if (result.errors) errorMsg = Object.values(result.errors).flat().join('\n');
                        Swal.fire('Gagal', errorMsg, 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Gagal memproses data. Cek koneksi internet.', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            });
        }

        // Terapkan script AJAX di atas pada ketiga Modal
        document.addEventListener('DOMContentLoaded', function() {
            initAjaxForm('formTambahSatker', 'modalTambahSatker', false);
            initAjaxForm('formEditSatker', 'modalEditSatker', false);
            initAjaxForm('formHapusSatker', 'modalHapusSatker', true);
        });

        function handleKategoriJabatanChange() {
            const kategoriVal = document.getElementById('kategori_jabatan_fungsional').value;
            const jenjangWrapper = document.getElementById('wrapper_jenjang_jabatan');
            const jenjangSelect = document.getElementById('jabatan_id');
            const options = jenjangSelect.querySelectorAll('option');

            if (!kategoriVal) {
                jenjangWrapper.classList.add('hidden');
                jenjangSelect.value = "";
                return;
            }

            jenjangWrapper.classList.remove('hidden');
            jenjangSelect.value = "";

            // Filter opsi 4 digit berdasarkan 3 digit pertama
            options.forEach(opt => {
                if (opt.value === "") return;
                // Cek data-parent-kode yang kita buat di HTML tadi
                if (opt.dataset.parentKode === kategoriVal) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
        }

        function handleJenjangJabatanChange() {
            const jenjangSelect = document.getElementById('jabatan_id');
            const namaInput = document.getElementById('nama_satker');
            const selectedOption = jenjangSelect.options[jenjangSelect.selectedIndex];

            if (jenjangSelect.value !== "") {
                // Ambil teks setelah tanda " - " agar bersih dari kode
                const fullDisplayText = selectedOption.text;
                const namePart = fullDisplayText.includes(' - ') ? fullDisplayText.split(' - ')[1] : fullDisplayText;
                
                if (namaInput) {
                    namaInput.value = namePart.trim();
                    // KUNCI: Set dataset staticText agar user tidak bisa sembarangan hapus (fitur anti-backspace Anda)
                    namaInput.dataset.staticText = namePart.trim() + " "; 
                }
                
                // Picu generate kode (agar parent + 4 digit jafung menyatu)
                if (typeof generateSatkerCode === 'function') generateSatkerCode();
            }
            
            updateRefJabatanId();
            if(typeof updateDropdownRumus === 'function') updateDropdownRumus();
        }

        // Fungsi untuk mereset modal dan semua state input
        window.resetTambahSatkerModal = function() {
            const form = document.getElementById('formTambahSatker');
            if (form) form.reset();

            // Reset manual untuk elemen khusus
            document.getElementById('kode_satker_full').value = "";
            document.getElementById('nama_satker').dataset.staticText = "";
            
            // Sembunyikan semua container opsional
            const containers = [
                'container_kategori_unit', 'container_kategori_kotakab', 
                'container_kabupaten', 'container_jenis_madrasah', 
                'container_jabatan_fungsional', 'container_rumpun_fakultas',
                'container_filter_fungsi', 'gap_container'
            ];
            containers.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });

            // Reset Dropdown Rumus
            if (typeof updateDropdownRumus === 'function') updateDropdownRumus();
            
            // Kembalikan posisi dropdown rumus ke default (jika sebelumnya pindah karena PTKN)
            const containerRumus = document.getElementById('container_rumus_manual');
            const anchorDefault = document.getElementById('anchor_rumus_default');
            if (anchorDefault && containerRumus) anchorDefault.appendChild(containerRumus);
        };
    </script>

    <div x-data class="fixed bottom-24 md:bottom-8 right-4 md:right-8 flex flex-col items-end gap-3 z-[60]">
        <button @click="$store.selection.toggleSelectionMode()" 
                :class="$store.selection.isSelectionMode ? 'bg-red-600' : 'bg-blue-600'"
                class="flex items-center gap-2 px-6 py-3 text-white rounded-full shadow-2xl hover:scale-105 transition-all font-bold">
            <i class="fas" :class="$store.selection.isSelectionMode ? 'fa-times' : 'fa-check-double'"></i>
            <span x-text="$store.selection.isSelectionMode ? 'Batal Pilih' : 'Pilih Satker'"></span>
        </button>

        <div x-show="$store.selection.isSelectionMode && $store.selection.selectedIds.length > 0 && $store.selection.clipboard.mode === ''" 
             x-transition class="flex gap-2 bg-white p-2 rounded-2xl shadow-xl border border-slate-200">
            
            <template x-if="$store.selection.canPerformAction()">
                <div class="flex gap-2">
                    <button @click="$store.selection.setClipboard('copy')" class="p-3 text-blue-600 hover:bg-blue-50 rounded-xl transition" title="Copy"><i class="fas fa-copy"></i></button>
                    <button @click="$store.selection.setClipboard('move')" class="p-3 text-amber-600 hover:bg-amber-50 rounded-xl transition" title="Potong (Move)"><i class="fas fa-cut"></i></button>
                </div>
            </template>
            
            <button @click="$store.selection.confirmBulkDelete()" class="p-3 text-red-600 hover:bg-red-50 rounded-xl transition" title="Hapus Massal"><i class="fas fa-trash"></i></button>
        </div>

        <div x-show="$store.selection.clipboard.mode !== ''" x-transition 
             class="flex flex-col items-center gap-2 bg-white p-4 rounded-2xl shadow-xl border-2 border-blue-400">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">Mode: <span x-text="$store.selection.clipboard.mode"></span></span>
            <div class="flex gap-2">
                {{-- KUNCI PERBAIKAN: Sembunyikan tombol Paste ke Root jika tidak lolos validasi Eselon 1 --}}
                <template x-if="$store.selection.canPerformAction()">
                    <button @click="$store.selection.confirmPaste(null, '', 'Root (Eselon 1)')" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold shadow-sm">Paste ke Root (Khusus Eselon 1)</button>
                </template>
                <button @click="$store.selection.clearClipboard()" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-xs font-bold">Batal</button>
            </div>
            <p class="text-[9px] text-slate-400 mt-1">*Klik icon "Paste" biru pada Parent untuk memasukkannya ke dalam Parent tersebut.</p>
        </div>
    </div>
@endpush