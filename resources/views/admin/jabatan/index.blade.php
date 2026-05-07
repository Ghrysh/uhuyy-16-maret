@extends('layouts.admin')

@section('title', 'Master Jabatan & Distribusi Kuota')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Jabatan Fungsional</h2>
            <p class="text-sm text-slate-500">Kelola master data jabatan dan distribusi kuota formasi</p>
        </div>
    </div>

    {{-- TAB PERIODE NAVIGATION --}}
    <div class="flex items-center border-b border-gray-200 mb-6 overflow-x-auto">
        @foreach ($periodes as $pe)
            <a href="{{ route('admin.jabatan.index', ['periode_id' => $pe->id]) }}"
                class="px-6 py-3 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none {{ $activePeriodeId == $pe->id ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                {{ $pe->nama_periode }}
            </a>
        @endforeach
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="flex border-b border-gray-200 mb-6 space-x-6">
        <button onclick="switchTab('master')" id="tab-master" class="pb-3 px-2 border-b-2 border-[#112D4E] text-[#112D4E] font-bold text-sm transition-all outline-none">
            <i class="fas fa-list mr-2"></i> Master Jabatan
        </button>
        <button onclick="switchTab('distribusi')" id="tab-distribusi" class="pb-3 px-2 border-b-2 border-transparent text-gray-500 hover:text-[#112D4E] font-medium text-sm transition-all outline-none">
            <i class="fas fa-sitemap mr-2"></i> Distribusi Kuota Formasi
        </button>
    </div>

    {{-- ========================================== --}}
    {{-- TAB 1: MASTER JABATAN                      --}}
    {{-- ========================================== --}}
    <div id="content-master" class="block animate-fade-in" x-data="searchVSCode('jabatanTable', '.row-jabatan')">
        <div class="flex justify-end mb-4">
            @php $canCreate = $perm['is_super'] || $perm['all_access'] || in_array('create', $perm['actions'] ?? []); @endphp
            <button type="button" onclick="{{ $canCreate ? "openTambahJabatan()" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menambah Jabatan.', 'error')" }}"
                class="{{ $canCreate ? 'bg-[#112D4E] hover:bg-blue-900 text-white' : 'bg-slate-300 text-slate-500 cursor-not-allowed' }} px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm">
                <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Jabatan
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-50 bg-gray-50/30">
                <div class="relative max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-[12px]"></i>
                    </div>
                    <input type="text" x-model.debounce.300ms="search" @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();" placeholder="Cari kode atau jabatan..." class="block w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-[#112D4E] transition">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50">
                        <tr class="text-slate-500 text-[11px] uppercase tracking-widest border-b border-gray-100">
                            <th class="px-6 py-4 font-bold">Kode</th>
                            <th class="px-6 py-4 font-bold">Nama Jabatan</th>
                            <th class="px-6 py-4 font-bold text-center">Baseline per Jenjang</th>
                            <th class="px-6 py-4 font-bold text-center">Jenjang</th>
                            <th class="px-6 py-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jabatanTable" class="divide-y divide-gray-100">
                        @forelse($jabatans as $group)
                            {{-- Baris Induk (Group) --}}
                            <tr class="bg-slate-50/80 border-t-2 border-slate-200 row-jabatan" data-search="{{ strtolower($group['kode'] . ' ' . $group['nama_jabatan']) }}">
                                <td colspan="5" class="px-6 py-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-[#112D4E] flex items-center justify-center text-white shadow-sm">
                                                <i class="fas fa-briefcase text-xs"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-bold text-[#112D4E]">{{ $group['nama_jabatan'] }}</h4>
                                                <span class="text-xs font-mono font-bold text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm">Kode Dasar: {{ $group['kode'] }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @php $canEdit = $perm['is_super'] || $perm['all_access'] || in_array('edit', $perm['actions'] ?? []); @endphp
                                            <button type="button" 
                                                onclick="{{ $canEdit ? "openEditModalGlobal(" . json_encode($group) . ")" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin.', 'error')" }}"
                                                class="{{ $canEdit ? 'text-blue-600 hover:text-white hover:bg-blue-600' : 'text-slate-300' }} w-8 h-8 rounded-lg flex items-center justify-center transition shadow-sm border border-slate-200 bg-white" title="Edit Grup Jabatan">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- Baris Anak-anak Jenjang --}}
                            @foreach($group['jenjangs'] as $j)
                                @php
                                    $canDelete = $perm['is_super'] || $perm['all_access'] || in_array('delete', $perm['actions'] ?? []); 
                                @endphp
                                <tr class="hover:bg-blue-50/30 transition group/row bg-white row-jabatan" data-search="{{ strtolower($j['kode'] . ' ' . $j['nama_lengkap']) }}">
                                    <td class="px-6 py-3 text-sm text-slate-600 font-mono font-bold pl-12"><i class="fas fa-level-up-alt rotate-90 text-slate-300 mr-2 text-xs"></i>{{ $j['kode'] }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-700 font-medium">{{ $j['nama_lengkap'] }}</td>
                                    <td class="px-6 py-3 text-center">
                                        @php
                                            // Tentukan apakah grup ini "Semua Jenjang" (memiliki 8 baris)
                                            $isSemua = count($group['jenjangs']) > 4;
                                            $valBaseline = 0;

                                            switch($j['kode_ujung']) {
                                                case '1': $valBaseline = $j['b_pertama_menpan'] ?? 0; break;
                                                case '2': $valBaseline = $j['b_muda_menpan'] ?? 0; break;
                                                case '3': $valBaseline = $j['b_madya_menpan'] ?? 0; break;
                                                case '4': $valBaseline = $j['b_utama_menpan'] ?? 0; break;
                                                
                                                // Jika Semua Jenjang, ambil dari kolom lima-delapan. 
                                                // Jika kategori Keahlian biasa, tetap ambil dari pertama-utama.
                                                case '5': $valBaseline = $isSemua ? ($j['b_lima_menpan'] ?? 0) : ($j['b_pertama_menpan'] ?? 0); break;
                                                case '6': $valBaseline = $isSemua ? ($j['b_enam_menpan'] ?? 0) : ($j['b_muda_menpan'] ?? 0); break;
                                                case '7': $valBaseline = $isSemua ? ($j['b_tujuh_menpan'] ?? 0) : ($j['b_madya_menpan'] ?? 0); break;
                                                case '8': $valBaseline = $isSemua ? ($j['b_delapan_menpan'] ?? 0) : ($j['b_utama_menpan'] ?? 0); break;
                                            }
                                        @endphp
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-1 rounded shadow-sm border border-amber-200">
                                            Baseline: {{ $valBaseline }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        @php
                                            $jenjangLabel = 'Tidak Ada Jenjang';
                                            switch($j['kode_ujung']) {
                                                case '1': $jenjangLabel = 'Pemula'; break;
                                                case '2': $jenjangLabel = 'Terampil'; break;
                                                case '3': $jenjangLabel = 'Mahir'; break;
                                                case '4': $jenjangLabel = 'Penyelia'; break;
                                                case '5': $jenjangLabel = 'Ahli Pertama'; break;
                                                case '6': $jenjangLabel = 'Ahli Muda'; break;
                                                case '7': $jenjangLabel = 'Ahli Madya'; break;
                                                case '8': $jenjangLabel = 'Ahli Utama'; break;
                                            }
                                        @endphp
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-md uppercase tracking-wider">{{ $jenjangLabel }}</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-md uppercase tracking-wider">ID: {{ $j['kode_ujung'] }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <button type="button"
                                            onclick="{{ $canDelete ? "confirmDelete('{$j['id']}', '" . addslashes($j['nama_lengkap']) . "')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menghapus.', 'error')" }}"
                                            class="{{ $canDelete ? 'text-slate-400 hover:text-red-600' : 'text-slate-300 opacity-50' }} transition" title="Hapus">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                        
                                        @if($canDelete)
                                        <form id="delete-form-{{ $j['id'] }}" action="{{ route('admin.jabatan.destroy', $j['id']) }}" method="POST" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr id="noDataRow"><td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada data jabatan.</td></tr>
                        @endforelse
                        <tr id="notFoundRow" class="hidden"><td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Data tidak ditemukan.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 px-6 py-4 border-t border-gray-100">
                {{ $jabatans->withPath(route('admin.jabatan.index'))->appends(request()->query())->links() }}
            </div>
        </div>

        <div x-show="search && matches.length > 0" x-transition.opacity x-cloak class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.15)] border border-slate-200 rounded-full px-5 py-2.5 flex items-center gap-4 z-[55]">
            <div class="text-xs font-bold text-slate-600 tracking-wide">
                <span x-text="currentIndex + 1" class="text-blue-600"></span> <span class="text-slate-400 mx-1">dari</span> <span x-text="matches.length"></span>
            </div>
            <div class="w-[1px] h-4 bg-slate-200"></div>
            <div class="flex items-center gap-2">
                <button @click="prevMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition"><i class="fas fa-chevron-up text-xs"></i></button>
                <button @click="nextMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition"><i class="fas fa-chevron-down text-xs"></i></button>
            </div>
        </div>
    </div>

{{-- ========================================== --}}
    {{-- TAB 2: DISTRIBUSI KUOTA FORMASI            --}}
    {{-- ========================================== --}}
    <div id="content-distribusi" class="hidden animate-fade-in">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full relative" id="jabatan-search-container">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Cari & Pilih Jabatan Fungsional</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                        <input type="text" id="search_jabatan_input" placeholder="Ketik nama jabatan atau kode..." autocomplete="off"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all font-medium text-slate-700">
                        <button type="button" id="clear_search_btn" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden text-slate-400 hover:text-red-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <input type="hidden" id="filter_fungsional_distribusi">
                    <input type="hidden" id="filter_kategori_fungsional">
                    <input type="hidden" id="filter_kode_fungsional"> <ul id="jabatan_dropdown_list" class="absolute z-50 w-full bg-white border border-gray-100 rounded-xl shadow-xl mt-2 hidden max-h-64 overflow-y-auto divide-y divide-gray-50"></ul>
                </div>
                <button type="button" onclick="loadMatriks()" class="bg-[#112D4E] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition flex items-center">
                    <i class="fas fa-search mr-2"></i> Tampilkan Matriks
                </button>
            </div>
        </div>

        <div id="wrapper_matriks_distribusi" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative" x-data="{ ...searchVSCode('matriksTableBody', '.matriks-row'), tabMatriks: 'menpan' }">
            
            {{-- TOMBOL NAVIGASI 3 TAB (DISEMBUNYIKAN AWALNYA) --}}
            <div id="matriksTabsContainer" class="hidden">
                <div class="flex border-b border-gray-200 bg-slate-50 p-2 space-x-2">
                    <button @click="tabMatriks = 'menpan'; window.renderTableByTab('menpan')" :class="tabMatriks === 'menpan' ? 'bg-blue-600 text-white shadow-md' : 'bg-transparent text-gray-500 hover:bg-gray-200'" class="px-5 py-2.5 text-xs font-bold rounded-lg transition-all">Persetujuan Kebutuhan (MENPANRB)</button>
                    <button @click="tabMatriks = 'eksisting'; window.renderTableByTab('eksisting')" :class="tabMatriks === 'eksisting' ? 'bg-amber-500 text-white shadow-md' : 'bg-transparent text-gray-500 hover:bg-gray-200'" class="px-5 py-2.5 text-xs font-bold rounded-lg transition-all">Jumlah Yang Ada (Eksisting)</button>
                    <button @click="tabMatriks = 'lowongan'; window.renderTableByTab('lowongan')" :class="tabMatriks === 'lowongan' ? 'bg-emerald-600 text-white shadow-md' : 'bg-transparent text-gray-500 hover:bg-gray-200'" class="px-5 py-2.5 text-xs font-bold rounded-lg transition-all">Ketersediaan Lowongan</button>
                </div>
            </div>

            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-blue-900 text-sm">Matriks Alokasi Kuota Satker</h3>

                <div class="relative w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i class="fas fa-search text-blue-400 text-xs"></i></span>
                    <input type="text" x-model.debounce.300ms="search" @keydown.enter.prevent="if($event.shiftKey) prevMatch(); else nextMatch();" placeholder="Cari Satker di Tabel..." class="w-full pl-9 pr-3 py-1.5 border border-blue-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>
            
            <div id="panel_setup_baseline" class="hidden bg-amber-50 border-b-2 border-amber-200 p-4"></div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 border-b border-r border-gray-200 text-xs font-bold text-slate-700 uppercase w-1/3">Struktur Satuan Kerja</th>
                            
                            <th id="th_setup_kuota" colspan="4" class="px-6 py-2 border-b border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-slate-100/50">Setup Kuota per Jenjang</th>
                            
                            <th rowspan="2" class="px-4 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-emerald-50">Jumlah</th>
                            <th rowspan="2" class="px-6 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase w-32">Aksi</th>
                        </tr>
                        <tr>
                            <th id="th_jenjang_1" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 1</th>
                            <th id="th_jenjang_2" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 2</th>
                            <th id="th_jenjang_3" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 3</th>
                            <th id="th_jenjang_4" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 4</th>
                            
                            <th id="th_jenjang_5" class="th_extra_jenjang hidden px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 5</th>
                            <th id="th_jenjang_6" class="th_extra_jenjang hidden px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 6</th>
                            <th id="th_jenjang_7" class="th_extra_jenjang hidden px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 7</th>
                            <th id="th_jenjang_8" class="th_extra_jenjang hidden px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 8</th>
                        </tr>
                    </thead>
                    <tbody id="matriksTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>
                    </tbody>
                </table>
            </div>
            <div x-show="search && matches.length > 0" x-transition.opacity x-cloak class="fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-white shadow-[0_10px_25px_-5px_rgba(0,0,0,0.15)] border border-slate-200 rounded-full px-5 py-2.5 flex items-center gap-4 z-[55]">
                <div class="text-xs font-bold text-slate-600 tracking-wide">
                    <span x-text="currentIndex + 1" class="text-blue-600"></span> <span class="text-slate-400 mx-1">dari</span> <span x-text="matches.length"></span>
                </div>
                <div class="w-[1px] h-4 bg-slate-200"></div>
                <div class="flex items-center gap-2">
                    <button @click="prevMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition"><i class="fas fa-chevron-up text-xs"></i></button>
                    <button @click="nextMatch()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition"><i class="fas fa-chevron-down text-xs"></i></button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div id="modalTambahJabatan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalTambahJabatan')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl sm:max-w-lg w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Jabatan</h3>
                    <button onclick="toggleModal('modalTambahJabatan')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form action="{{ route('admin.jabatan.store') }}" method="POST" id="formAddJabatan">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $activePeriodeId }}">
                    <input type="hidden" name="jenis_jabatan_id" value="{{ $idFungsional }}">

                    <div class="px-6 py-6 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Dasar Jabatan (3 Digit)</label>
                            <input type="text" name="kode_jabatan" id="tambah_kode_urut" value="{{ $nextBaseCode }}" 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 font-mono tracking-widest text-blue-800" maxlength="3" required placeholder="Cth: 801">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Jabatan Fungsional</label>
                            <input type="text" name="nama_jabatan" required placeholder="Cth: Penyuluh Agama Islam"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-700 font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kategori Jenjang</label>
                            <div class="flex flex-col gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <label class="flex items-center gap-3 cursor-pointer group hover:bg-blue-50 p-2 rounded-lg transition-colors">
                                    <input type="radio" name="kategori_jenjang" value="keterampilan" onchange="updateJenjangLabels(this.value)" class="w-4 h-4 text-blue-600 focus:ring-blue-500 border-gray-300" required>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-blue-700">Keterampilan</span>
                                        <span class="text-[10px] text-slate-500 uppercase tracking-wider">Pemula - Terampil - Mahir - Penyelia (Kode Ujung: 1, 2, 3, 4)</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group hover:bg-amber-50 p-2 rounded-lg transition-colors">
                                    <input type="radio" name="kategori_jenjang" value="keahlian" onchange="updateJenjangLabels(this.value)" class="w-4 h-4 text-amber-600 focus:ring-amber-500 border-gray-300" required>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-amber-700">Keahlian</span>
                                        <span class="text-[10px] text-slate-500 uppercase tracking-wider">Ahli Pertama - Ahli Muda - Ahli Madya - Ahli Utama (Kode Ujung: 5, 6, 7, 8)</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group hover:bg-emerald-50 p-2 rounded-lg transition-colors">
                                    <input type="radio" name="kategori_jenjang" value="semua" onchange="updateJenjangLabels(this.value)" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 group-hover:text-emerald-700">Semua Jenjang</span>
                                        <span class="text-[10px] text-slate-500 uppercase tracking-wider">Mencakup 8 Jenjang (Pemula s/d Ahli Utama)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="container_baseline_tambah" class="hidden mt-4" x-data="{ tabBaseline: 'menpan' }">
                            <div class="flex border-b border-gray-200 mb-3 space-x-4">
                                <button type="button" @click="tabBaseline = 'menpan'" :class="tabBaseline === 'menpan' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Persetujuan Kebutuhan</button>
                                <button type="button" @click="tabBaseline = 'eksisting'" :class="tabBaseline === 'eksisting' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Jumlah Yang Ada</button>
                                <button type="button" @click="tabBaseline = 'lowongan'" :class="tabBaseline === 'lowongan' ? 'border-emerald-600 text-emerald-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Ketersediaan Lowongan</button>
                            </div>

                            <div x-show="tabBaseline === 'menpan'" class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                                <label class="block text-xs font-bold text-blue-900 mb-3">Input Persetujuan Kebutuhan (MENPANRB)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_tambah_j1 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_tambah_j2 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_tambah_j3 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_tambah_j4 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j5 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j6 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j7 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j8 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_menpan" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                </div>
                            </div>

                            <div x-show="tabBaseline === 'eksisting'" class="bg-amber-50 p-4 rounded-xl border border-amber-200" style="display: none;">
                                <label class="block text-xs font-bold text-amber-900 mb-3">Input Jumlah Yang Ada (Eksisting)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_tambah_j1 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_tambah_j2 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_tambah_j3 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_tambah_j4 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j5 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j6 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j7 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j8 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_eksisting" oninput="autoCalcTambah()" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                </div>
                            </div>

                            <div x-show="tabBaseline === 'lowongan'" class="bg-emerald-50 p-4 rounded-xl border border-emerald-200" style="display: none;">
                                <label class="block text-xs font-bold text-emerald-900 mb-3">Ketersediaan Lowongan (Otomatis)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_tambah_j1 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_tambah_j2 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_tambah_j3 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_tambah_j4 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j5 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j6 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j7 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_tambah_j8 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_lowongan" value="0" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <p class="text-xs text-blue-800 leading-relaxed">
                                Sistem akan otomatis meng-generate <strong>4 baris jabatan baru</strong> beserta jenjang dan kodenya sesuai kategori yang Anda pilih di atas. Anda tidak perlu menginputnya satu per satu.
                            </p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalTambahJabatan')" class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md hover:bg-blue-900 transition-colors">Generate Jabatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT GLOBAL --}}
    <div id="modalEditJabatanGlobal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalEditJabatanGlobal')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl sm:max-w-lg w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-slate-800">Edit Grup Jabatan</h3>
                    <button onclick="toggleModal('modalEditJabatanGlobal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <form action="" method="POST" id="formEditJabatanGlobal">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="periode_id" value="{{ $activePeriodeId }}">
                    <input type="hidden" name="kode_dasar" id="edit_global_kode_dasar">

                    <div class="px-6 py-6 space-y-5 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Jabatan Fungsional</label>
                            <input type="text" name="nama_jabatan" id="edit_global_nama" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 text-slate-700 font-semibold">
                        </div>

                        <div id="container_baseline_edit" class="mt-4" x-data="{ tabEditBaseline: 'menpan' }">
                            <div class="flex border-b border-gray-200 mb-3 space-x-4">
                                <button type="button" @click="tabEditBaseline = 'menpan'" :class="tabEditBaseline === 'menpan' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Persetujuan Kebutuhan</button>
                                <button type="button" @click="tabEditBaseline = 'eksisting'" :class="tabEditBaseline === 'eksisting' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Jumlah Yang Ada</button>
                                <button type="button" @click="tabEditBaseline = 'lowongan'" :class="tabEditBaseline === 'lowongan' ? 'border-emerald-600 text-emerald-600 font-bold' : 'border-transparent text-gray-500'" class="pb-2 border-b-2 text-[11px] uppercase tracking-wider transition">Ketersediaan Lowongan</button>
                            </div>

                            <div x-show="tabEditBaseline === 'menpan'" class="bg-blue-50 p-4 rounded-xl border border-blue-200">
                                <label class="block text-xs font-bold text-blue-900 mb-3">Update Persetujuan Kebutuhan</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_edit_j1 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_menpan" id="edit_b_p_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_edit_j2 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_menpan" id="edit_b_mu_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_edit_j3 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_menpan" id="edit_b_ma_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div><label class="lbl_edit_j4 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_menpan" id="edit_b_u_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j5 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_menpan" id="edit_b_lima_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j6 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_menpan" id="edit_b_enam_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j7 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_menpan" id="edit_b_tujuh_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j8 block text-[10px] font-bold text-blue-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_menpan" id="edit_b_delapan_menpan" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-blue-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-blue-500"></div>
                                </div>
                            </div>

                            <div x-show="tabEditBaseline === 'eksisting'" class="bg-amber-50 p-4 rounded-xl border border-amber-200" style="display:none;">
                                <label class="block text-xs font-bold text-amber-900 mb-3">Update Jumlah Yang Ada (Eksisting)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_edit_j1 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_eksisting" id="edit_b_p_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_edit_j2 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_eksisting" id="edit_b_mu_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_edit_j3 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_eksisting" id="edit_b_ma_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div><label class="lbl_edit_j4 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_eksisting" id="edit_b_u_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j5 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_eksisting" id="edit_b_lima_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j6 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_eksisting" id="edit_b_enam_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j7 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_eksisting" id="edit_b_tujuh_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j8 block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_eksisting" id="edit_b_delapan_eks" oninput="autoCalcEdit()" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500"></div>
                                </div>
                            </div>

                            <div x-show="tabEditBaseline === 'lowongan'" class="bg-emerald-50 p-4 rounded-xl border border-emerald-200" style="display:none;">
                                <label class="block text-xs font-bold text-emerald-900 mb-3">Ketersediaan Lowongan (Otomatis)</label>
                                <div class="grid grid-cols-4 gap-2">
                                    <div><label class="lbl_edit_j1 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J1</label><input type="number" name="b_pertama_lowongan" id="edit_b_p_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_edit_j2 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J2</label><input type="number" name="b_muda_lowongan" id="edit_b_mu_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_edit_j3 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J3</label><input type="number" name="b_madya_lowongan" id="edit_b_ma_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div><label class="lbl_edit_j4 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J4</label><input type="number" name="b_utama_lowongan" id="edit_b_u_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j5 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J5</label><input type="number" name="b_lima_lowongan" id="edit_b_lima_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j6 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J6</label><input type="number" name="b_enam_lowongan" id="edit_b_enam_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j7 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J7</label><input type="number" name="b_tujuh_lowongan" id="edit_b_tujuh_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                    <div class="jenjang-5-8 hidden"><label class="lbl_edit_j8 block text-[10px] font-bold text-emerald-800 uppercase text-center mb-1">J8</label><input type="number" name="b_delapan_lowongan" id="edit_b_delapan_low" readonly class="w-full px-2 py-2 bg-gray-100 border border-gray-200 rounded-lg text-center font-bold text-gray-500 outline-none cursor-not-allowed"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalEditJabatanGlobal')" class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-bold rounded-lg shadow-md hover:bg-blue-700 transition-colors">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div id="modalHapusJabatan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalHapusJabatan')"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden p-6 text-center">
                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Jabatan</h3>
                <p class="text-slate-500 text-sm mb-8">
                    Apakah Anda yakin ingin menghapus jabatan <span id="delete_nama_display" class="font-bold text-slate-700"></span>? Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex space-x-3">
                    <button type="button" onclick="toggleModal('modalHapusJabatan')"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="button" id="btnConfirmDelete"
                        class="flex-1 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl shadow-sm transition">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <script>
        const permMatriksEditKuota = {{ ($perm['is_super'] || $perm['all_access'] || in_array('edit_kuota', $perm['matriks'] ?? [])) ? 'true' : 'false' }};

        document.addEventListener('DOMContentLoaded', function() {
            const savedTab = localStorage.getItem('activeTab_jabatan') || 'master';
            switchTab(savedTab);
        });

        function switchTab(tabName) {
            localStorage.setItem('activeTab_jabatan', tabName);
            document.getElementById('content-master').classList.add('hidden');
            document.getElementById('content-distribusi').classList.add('hidden');
            
            const tabs = ['master', 'distribusi'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t);
                btn.classList.remove('border-[#112D4E]', 'text-[#112D4E]', 'font-bold');
                btn.classList.add('border-transparent', 'text-gray-500', 'font-medium');
            });

            document.getElementById('content-' + tabName).classList.remove('hidden');
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
            activeBtn.classList.add('border-[#112D4E]', 'text-[#112D4E]', 'font-bold');
        }

        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });

        @if (session('success')) Toast.fire({ icon: 'success', title: "{{ session('success') }}" }); @endif
        @if (session('error')) Toast.fire({ icon: 'error', title: "{{ session('error') }}" }); @endif

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }

        function confirmDelete(id, nama) {
            document.getElementById('delete_nama_display').innerText = nama;
            const btnConfirm = document.getElementById('btnConfirmDelete');
            btnConfirm.onclick = function() { document.getElementById('delete-form-' + id).submit(); };
            toggleModal('modalHapusJabatan');
        }

        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const tbody = document.getElementById("jabatanTable");
            const tr = tbody.getElementsByTagName("tr");
            const notFoundRow = document.getElementById("notFoundRow");
            let hasVisibleRow = false;

            for (let i = 0; i < tr.length; i++) {
                if (tr[i].id === "notFoundRow" || tr[i].id === "noDataRow") continue;
                const tdKode = tr[i].getElementsByTagName("td")[0];
                const tdNama = tr[i].getElementsByTagName("td")[1];
                if (tdKode && tdNama) {
                    const txtKode = tdKode.textContent || tdKode.innerText;
                    const txtNama = tdNama.textContent || tdNama.innerText;
                    if (txtKode.toLowerCase().indexOf(filter) > -1 || txtNama.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        hasVisibleRow = true;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
            if (filter !== "" && !hasVisibleRow) notFoundRow.classList.remove('hidden');
            else notFoundRow.classList.add('hidden');
        }

        function openTambahJabatan() {
            document.getElementById('tambah_kode_urut').value = "{{ $nextBaseCode }}";
            const radio = document.querySelector('input[name="kategori_jenjang"]:checked');
            if(radio) radio.checked = false;
            if(document.getElementById('container_baseline_tambah')) {
                document.getElementById('container_baseline_tambah').classList.add('hidden');
            }
            toggleModal('modalTambahJabatan');
        }

        function updateJenjangLabels(kategori) {
            document.getElementById('container_baseline_tambah').classList.remove('hidden');
            const isSemua = (kategori === 'semua');
            
            document.querySelectorAll('.jenjang-5-8').forEach(el => el.classList.toggle('hidden', !isSemua));

            const labels = ["Pemula", "Terampil", "Mahir", "Penyelia", "Ahli Pertama", "Ahli Muda", "Ahli Madya", "Ahli Utama"];
            if (kategori === 'keahlian') {
                for(let i=1; i<=4; i++) document.querySelectorAll('.lbl_tambah_j'+i).forEach(el => el.innerText = labels[i+3]);
            } else {
                for(let i=1; i<=8; i++) document.querySelectorAll('.lbl_tambah_j'+i).forEach(el => el.innerText = labels[i-1]);
            }
        }
        // Lakukan hal yang sama persis untuk fungsi updateJenjangLabelsEdit() 
        // (ganti .lbl_tambah_j menjadi .lbl_edit_j)

        // function openEditModal(id, kodeFull, nama, jenis, fungsional_id, bp, bmu, bma, bu, base) {
        //     const form = document.getElementById('formEditJabatan');
        //     const baseUrl = "{{ route('admin.jabatan.index') }}";
        //     form.action = `${baseUrl}/${id}`;

        //     document.getElementById('edit_nama').value = nama;
        //     document.getElementById('edit_jenis_jabatan_id').value = jenis;
        //     document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";
        //     document.getElementById('edit_baseline').value = base || 0;
            
        //     document.getElementById('edit_b_pertama').value = bp || 0;
        //     document.getElementById('edit_b_muda').value = bmu || 0;
        //     document.getElementById('edit_b_madya').value = bma || 0;
        //     document.getElementById('edit_b_utama').value = bu || 0;

        //     const kodeUrut = kodeFull.substring(0, 3);
        //     document.getElementById('edit_kode_urut').value = kodeUrut;
        //     document.getElementById('edit_kode_final').value = kodeFull;
            
        //     updateJenjangLabels('edit_jabatan_fungsional_id', 'lbl_edit_j');

        //     toggleModal('modalEditJabatan');
        // }

        // ==========================================
        // AJAX LOAD MATRIKS (ANTI-FREEZE DENGAN CHUNKING)
        // ==========================================
        let globalMatriksData = [];
        let globalBaselineData = {};

        async function loadMatriks() {
            const fungsionalId = document.getElementById('filter_fungsional_distribusi').value;
            const fungsionalKode = document.getElementById('filter_kode_fungsional').value;

            if (!fungsionalId) {
                Swal.fire({icon: 'warning', title: 'Pilih Jabatan', text: 'Silakan pilih Jabatan Fungsional terlebih dahulu!', confirmButtonColor: '#112D4E'});
                return;
            }

            Swal.fire({ title: 'Mengunduh Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            try {
                const response = await fetch(`{{ url('admin/jabatan/matriks') }}?jabatan_id=${fungsionalId}`);
                
                // CEK DULU APAKAH RESPONSE NYA OK ATAU ERROR DARI SERVER
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                
                const dataJSON = await response.json();
                
                globalMatriksData = dataJSON.satkers || [];
                globalBaselineData = dataJSON; 
                
                const isSemua = dataJSON.is_semua_jenjang;
                if (isSemua) {
                    document.getElementById('th_jenjang_1').innerText = "Pemula";
                    document.getElementById('th_jenjang_2').innerText = "Terampil";
                    document.getElementById('th_jenjang_3').innerText = "Mahir";
                    document.getElementById('th_jenjang_4').innerText = "Penyelia";
                    if(document.getElementById('th_jenjang_5')) {
                        document.getElementById('th_jenjang_5').innerText = "Ahli Pertama";
                        document.getElementById('th_jenjang_6').innerText = "Ahli Muda";
                        document.getElementById('th_jenjang_7').innerText = "Ahli Madya";
                        document.getElementById('th_jenjang_8').innerText = "Ahli Utama";
                    }
                } else {
                    const kodeF = parseInt(fungsionalKode);
                    if (kodeF >= 1 && kodeF <= 4) {
                        document.getElementById('th_jenjang_1').innerText = "Pemula";
                        document.getElementById('th_jenjang_2').innerText = "Terampil";
                        document.getElementById('th_jenjang_3').innerText = "Mahir";
                        document.getElementById('th_jenjang_4').innerText = "Penyelia";
                    } else {
                        document.getElementById('th_jenjang_1').innerText = "Ahli Pertama";
                        document.getElementById('th_jenjang_2').innerText = "Ahli Muda";
                        document.getElementById('th_jenjang_3').innerText = "Ahli Madya";
                        document.getElementById('th_jenjang_4').innerText = "Ahli Utama";
                    }
                }
                
                // MUNCULKAN TAB HANYA JIKA BERHASIL (TIDAK ADA ERROR DI ATAS)
                document.getElementById('matriksTabsContainer').classList.remove('hidden');
                
                // Perbaikan: Cari elemen berdasarkan ID dan gunakan Alpine() global
                const xDataEl = document.getElementById('wrapper_matriks_distribusi');
                if (xDataEl && xDataEl.__x) {
                    xDataEl.__x.$data.tabMatriks = 'menpan';
                }
                
                window.renderTableByTab('menpan');

            } catch (error) {
                console.error(error); // Untuk melihat detail error di Console
                Swal.fire('Error', 'Gagal memuat data dari server. Silakan cek console atau log.', 'error');
            }
        }

        window.currentMatriksTab = 'menpan';

        window.renderTableByTab = function(tabName) {
            window.currentMatriksTab = tabName; 
            Swal.showLoading();
            
            let bp=0, bmu=0, bma=0, bu=0, b5=0, b6=0, b7=0, b8=0;
            if (tabName === 'menpan') {
                bp = globalBaselineData.b_p_menpan || 0; bmu = globalBaselineData.b_mu_menpan || 0; bma = globalBaselineData.b_ma_menpan || 0; bu = globalBaselineData.b_u_menpan || 0;
                b5 = globalBaselineData.b_5_menpan || 0; b6 = globalBaselineData.b_6_menpan || 0; b7 = globalBaselineData.b_7_menpan || 0; b8 = globalBaselineData.b_8_menpan || 0;
            } else if (tabName === 'eksisting') {
                bp = globalBaselineData.b_p_eks || 0; bmu = globalBaselineData.b_mu_eks || 0; bma = globalBaselineData.b_ma_eks || 0; bu = globalBaselineData.b_u_eks || 0;
                b5 = globalBaselineData.b_5_eks || 0; b6 = globalBaselineData.b_6_eks || 0; b7 = globalBaselineData.b_7_eks || 0; b8 = globalBaselineData.b_8_eks || 0;
            } else if (tabName === 'lowongan') {
                bp = globalBaselineData.b_p_low || 0; bmu = globalBaselineData.b_mu_low || 0; bma = globalBaselineData.b_ma_low || 0; bu = globalBaselineData.b_u_low || 0;
                b5 = globalBaselineData.b_5_low || 0; b6 = globalBaselineData.b_6_low || 0; b7 = globalBaselineData.b_7_low || 0; b8 = globalBaselineData.b_8_low || 0;
            }
            
            const isSemua = globalBaselineData.is_semua_jenjang;
            const grandTotal = parseInt(bp) + parseInt(bmu) + parseInt(bma) + parseInt(bu) + (isSemua ? parseInt(b5)+parseInt(b6)+parseInt(b7)+parseInt(b8) : 0);
            
            // =============================================================
            // FITUR BARU: NAMA JENJANG DINAMIS DI PANEL BASELINE
            // =============================================================
            let lbl1 = "Pemula", lbl2 = "Terampil", lbl3 = "Mahir", lbl4 = "Penyelia";
            let lbl5 = "Ahli Pertama", lbl6 = "Ahli Muda", lbl7 = "Ahli Madya", lbl8 = "Ahli Utama";

            // Cek apakah ini Kategori Keahlian (Ahli Pertama dkk) untuk format 4 jenjang
            const th1Text = document.getElementById('th_jenjang_1')?.innerText?.toLowerCase() || '';
            if (!isSemua && th1Text.includes('pertama')) {
                lbl1 = "Ahli Pertama"; lbl2 = "Ahli Muda"; lbl3 = "Ahli Madya"; lbl4 = "Ahli Utama";
            }
            // =============================================================

            const elSetupKuota = document.getElementById('th_setup_kuota');
            if (elSetupKuota) elSetupKuota.colSpan = isSemua ? 8 : 4;
            document.querySelectorAll('.th_extra_jenjang').forEach(el => el.classList.toggle('hidden', !isSemua));

            let extraSetupHtml = isSemua ? `
                <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl5}">${lbl5}</p><div id="display_base_5" class="px-2 py-1 bg-white border rounded font-black text-xs">${b5}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_k5" class="font-bold">0</span></p></div>
                <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl6}">${lbl6}</p><div id="display_base_6" class="px-2 py-1 bg-white border rounded font-black text-xs">${b6}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_k6" class="font-bold">0</span></p></div>
                <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl7}">${lbl7}</p><div id="display_base_7" class="px-2 py-1 bg-white border rounded font-black text-xs">${b7}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_k7" class="font-bold">0</span></p></div>
                <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl8}">${lbl8}</p><div id="display_base_8" class="px-2 py-1 bg-white border rounded font-black text-xs">${b8}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_k8" class="font-bold">0</span></p></div>
            ` : '';

            document.getElementById('panel_setup_baseline').classList.remove('hidden');
            document.getElementById('panel_setup_baseline').innerHTML = `
                <div class="flex justify-between items-center gap-4">
                    <div><p class="text-xs font-bold uppercase mb-1">TOTAL FORMASI MENPAN RB</p><span class="text-2xl font-black">${grandTotal}</span></div>
                    <div class="flex-1 flex gap-2 justify-end">
                        <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl1}">${lbl1}</p><div id="display_base_p" class="px-2 py-1 bg-white border rounded font-black text-xs">${bp}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_kp" class="font-bold">0</span></p></div>
                        <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl2}">${lbl2}</p><div id="display_base_mu" class="px-2 py-1 bg-white border rounded font-black text-xs">${bmu}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_kmu" class="font-bold">0</span></p></div>
                        <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl3}">${lbl3}</p><div id="display_base_ma" class="px-2 py-1 bg-white border rounded font-black text-xs">${bma}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_kma" class="font-bold">0</span></p></div>
                        <div class="text-center min-w-[70px]"><p class="text-[9px] font-bold text-slate-500 uppercase mb-1 truncate" title="${lbl4}">${lbl4}</p><div id="display_base_u" class="px-2 py-1 bg-white border rounded font-black text-xs">${bu}</div><p class="text-[9px] mt-1 text-slate-500">Sisa: <span id="sisa_ku" class="font-bold">0</span></p></div>
                        ${extraSetupHtml}
                    </div>
                </div>
            `;

            const renderTd = (type, val, id, isReadOnly) => {
                const isEditable = !isReadOnly.includes('readonly');
                const focusEvents = isEditable ? `onfocus="showSisaLabel('${type}', '${id}')" onblur="hideSisaLabel('${type}', '${id}')"` : '';

                return `
                <td class="px-1 py-2 align-top relative">
                    <div id="floating_sisa_${type}_${id}" class="invisible opacity-0 absolute -top-1 left-1/2 -translate-x-1/2 -translate-y-full bg-emerald-600 text-white px-1.5 py-0.5 rounded text-[8px] font-bold whitespace-nowrap z-20 shadow-sm transition-all duration-200 pointer-events-none">
                        Sisa: <span class="val-sisa-${type}">0</span>
                    </div>
                    <input type="number" id="${type}_${id}" data-type="${type}" value="${val || ''}" ${isReadOnly} ${focusEvents}>
                    <div class="mt-1 min-h-[14px] text-[9px] leading-tight text-center">
                        <p id="err_${type}_${id}" class="font-bold text-red-500 hidden">Lebih: <span id="err_val_${type}_${id}">0</span></p>
                    </div>
                </td>
                `;
            };

            const tbody = document.getElementById('matriksTableBody');
            let html = '';
            
            globalMatriksData.forEach(item => {
                const level = item.level;
                const isParent = item.has_children || level === 0; 
                const padStyle = `padding-left: ${1.5 + (level * 2.5)}rem;`; 
                const bgClass = level === 0 ? 'bg-white' : (level % 2 === 1 ? 'bg-slate-50/70' : 'bg-slate-50');
                const textClass = level === 0 ? 'font-bold text-slate-800' : (level === 1 ? 'font-semibold text-slate-700' : 'font-medium text-slate-600');
                const iconHtml = level === 0 ? '<i class="fas fa-building mr-3 text-slate-400"></i>' : '<i class="fas fa-level-up-alt rotate-90 text-slate-300 mr-2 opacity-70 text-xs"></i>';

                let v1, v2, v3, v4, v5, v6, v7, v8;
                if (tabName === 'menpan') {
                    v1 = item.kp_menpan; v2 = item.kmu_menpan; v3 = item.kma_menpan; v4 = item.ku_menpan;
                    v5 = item.k5_menpan; v6 = item.k6_menpan; v7 = item.k7_menpan; v8 = item.k8_menpan;
                } else if (tabName === 'eksisting') {
                    v1 = item.kp_eksisting; v2 = item.kmu_eksisting; v3 = item.kma_eksisting; v4 = item.ku_eksisting;
                    v5 = item.k5_eksisting; v6 = item.k6_eksisting; v7 = item.k7_eksisting; v8 = item.k8_eksisting;
                } else {
                    v1 = item.kp_lowongan; v2 = item.kmu_lowongan; v3 = item.kma_lowongan; v4 = item.ku_lowongan;
                    v5 = item.k5_lowongan; v6 = item.k6_lowongan; v7 = item.k7_lowongan; v8 = item.k8_lowongan;
                }

                const isReadOnly = (tabName === 'lowongan') 
                    ? 'readonly disabled class="w-full text-center border border-gray-200 bg-gray-100 p-1.5 rounded text-gray-500 font-bold"' 
                    : 'class="w-full text-center border border-gray-300 p-1.5 rounded focus:ring-2 focus:ring-blue-500" oninput="autoCalcMatriks(this)"';

                const totalRow = (parseInt(v1)||0) + (parseInt(v2)||0) + (parseInt(v3)||0) + (parseInt(v4)||0) + (isSemua ? (parseInt(v5)||0)+(parseInt(v6)||0)+(parseInt(v7)||0)+(parseInt(v8)||0) : 0);

                html += `
                    <tr class="matriks-row ${bgClass}" id="row-${item.id}" data-parent="${item.parent_id || 'root'}" data-search="${item.nama_satker.toLowerCase()}">
                        <td class="py-4 pr-4 align-middle" style="${padStyle}">
                            <div class="flex items-center ${textClass}">${iconHtml}<span class="text-sm tracking-tight leading-snug satker-name">${item.nama_satker}</span></div>
                        </td>
                        ${renderTd('kp', v1, item.id, isReadOnly)}
                        ${renderTd('kmu', v2, item.id, isReadOnly)}
                        ${renderTd('kma', v3, item.id, isReadOnly)}
                        ${renderTd('ku', v4, item.id, isReadOnly)}
                        ${isSemua ? renderTd('k5', v5, item.id, isReadOnly) + renderTd('k6', v6, item.id, isReadOnly) + renderTd('k7', v7, item.id, isReadOnly) + renderTd('k8', v8, item.id, isReadOnly) : ''}
                        <td class="px-4 py-3 text-center font-bold text-emerald-600 align-top pt-4" id="total_${item.id}">${totalRow}</td>
                        <td class="px-4 py-3 text-center align-top pt-3">
                            ${isParent ? (tabName === 'lowongan' ? '<span class="text-[10px] text-gray-400 italic">Otomatis</span>' : `<button onclick="simpanKuotaGroup('${item.id}', '${tabName}')" class="bg-[#112D4E] text-white px-3 py-1.5 rounded-md text-[11px] font-bold uppercase w-full">Simpan</button>`) : '<span class="text-[10px] text-gray-400 italic">Via Induk</span>'}
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            validateAllHierarchies(); 
            Swal.close();

            // Beri tahu Alpine bahwa tabel baru saja dirender ulang (untuk fitur Search)
            window.dispatchEvent(new CustomEvent('dom-updated'));
        }

        window.autoCalcMatriks = function(input) {
            const id = input.id.split('_')[1];
            const type = input.dataset.type;
            const val = parseInt(input.value) || 0;
            
            let item = globalMatriksData.find(i => i.id == id);
            if(item) {
                // Menggunakan variabel aman, bukan __x yang rawan error
                const currentTab = window.currentMatriksTab || 'menpan';
                if (currentTab === 'menpan') item[`${type}_menpan`] = val;
                else if (currentTab === 'eksisting') item[`${type}_eksisting`] = val;
                item[`${type}_lowongan`] = (parseInt(item[`${type}_menpan`]) || 0) - (parseInt(item[`${type}_eksisting`]) || 0);
            }
            validateAllHierarchies(); // Sekarang ini pasti akan dipanggil!
        }

        function validateAllHierarchies() {
            let sums = { kp: 0, kmu: 0, kma: 0, ku: 0, k5: 0, k6: 0, k7: 0, k8: 0 };
            const inputs = document.querySelectorAll('#matriksTableBody input[data-type]:not([readonly])');
            const isSemua = globalBaselineData.is_semua_jenjang;
            
            // Ambil Kategori (semua jenjang, keahlian, atau keterampilan)
            const kategoriStr = document.getElementById('filter_kategori_fungsional')?.value.toLowerCase() || '';

            // 1. Hitung total input per kolom dan update baris TOTAL
            inputs.forEach(input => {
                const type = input.dataset.type;
                sums[type] += (parseInt(input.value) || 0);
                
                const id = input.id.split('_')[1];
                const totalVal = 
                    (parseInt(document.getElementById(`kp_${id}`)?.value)||0) + 
                    (parseInt(document.getElementById(`kmu_${id}`)?.value)||0) + 
                    (parseInt(document.getElementById(`kma_${id}`)?.value)||0) + 
                    (parseInt(document.getElementById(`ku_${id}`)?.value)||0) +
                    (isSemua ? (parseInt(document.getElementById(`k5_${id}`)?.value)||0) + (parseInt(document.getElementById(`k6_${id}`)?.value)||0) + (parseInt(document.getElementById(`k7_${id}`)?.value)||0) + (parseInt(document.getElementById(`k8_${id}`)?.value)||0) : 0);
                
                const totalEl = document.getElementById(`total_${id}`);
                if(totalEl) totalEl.innerText = totalVal;
            });

            // 2. Ambil limit baseline
            const limits = {
                kp: parseInt(document.getElementById('display_base_p')?.innerText)||0,
                kmu: parseInt(document.getElementById('display_base_mu')?.innerText)||0,
                kma: parseInt(document.getElementById('display_base_ma')?.innerText)||0,
                ku: parseInt(document.getElementById('display_base_u')?.innerText)||0,
                k5: parseInt(document.getElementById('display_base_5')?.innerText)||0,
                k6: parseInt(document.getElementById('display_base_6')?.innerText)||0,
                k7: parseInt(document.getElementById('display_base_7')?.innerText)||0,
                k8: parseInt(document.getElementById('display_base_8')?.innerText)||0,
            };

            // 3. Update tampilan SISA GLOBAL & LABEL MELAYANG
            Object.keys(limits).forEach(type => {
                const sisa = limits[type] - sums[type];
                
                // CEK HAK ISTIMEWA: Apakah ini kolom Ahli Pertama?
                let isExempt = false;
                if (kategoriStr === 'semua jenjang' && type === 'k5') isExempt = true;
                if (kategoriStr === 'keahlian' && type === 'kp') isExempt = true;

                // Jika punya Hak Istimewa, jangan pernah jadikan warna merah walaupun minus
                const isRed = sisa < 0 && !isExempt; 
                const textColor = isRed ? 'text-red-600' : 'text-emerald-600';
                const bgColor = isRed ? 'bg-red-600' : 'bg-emerald-600';
                
                // Update Panel Atas & Header
                const sisaEl = document.getElementById(`sisa_${type}`);
                if (sisaEl) {
                    sisaEl.innerText = sisa;
                    sisaEl.className = `font-bold ${textColor}`;
                }
                const hSisaEl = document.getElementById(`h_sisa_${type}`);
                if (hSisaEl) {
                    hSisaEl.innerText = 'Sisa: ' + sisa;
                    hSisaEl.className = `text-[9px] font-black ${textColor}`;
                }
                
                // UPDATE LABEL SISA MELAYANG (DI ATAS KOTAK)
                document.querySelectorAll(`.val-sisa-${type}`).forEach(el => {
                    el.innerText = sisa;
                    const parentLabel = el.parentElement;
                    parentLabel.classList.remove('bg-emerald-600', 'bg-red-600');
                    parentLabel.classList.add(bgColor);
                });
            });

            // 4. Validasi per kotak input (Peringatan "Lebih: Sekian")
            inputs.forEach(input => {
                const type = input.dataset.type;
                const id = input.id.split('_')[1];
                const val = parseInt(input.value) || 0;
                
                const limit = limits[type] || 0;
                const otherSum = sums[type] - val; 
                const sisaTersedia = limit - otherSum;
                
                let isExceeding = val > sisaTersedia;

                // CEK HAK ISTIMEWA KOTAK INPUT (Matikan peringatan "Lebih")
                let isExempt = false;
                if (kategoriStr === 'semua jenjang' && type === 'k5') isExempt = true;
                if (kategoriStr === 'keahlian' && type === 'kp') isExempt = true;

                if (isExempt) {
                    isExceeding = false; // Memaksa Ahli Pertama selalu dianggap valid
                }

                const errEl = document.getElementById(`err_${type}_${id}`);
                const errValEl = document.getElementById(`err_val_${type}_${id}`);

                if (isExceeding) {
                    input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.remove('border-gray-300');
                    
                    if (errValEl) errValEl.innerText = val - sisaTersedia;
                    if (errEl) errEl.classList.remove('hidden');
                } else {
                    input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.add('border-gray-300');
                    
                    if (errEl) errEl.classList.add('hidden');
                }
            });
        }

        async function simpanKuotaGroup(satkerId, tabAktif) {
            // BLOKIR JIKA ADA ERROR DI BARIS INI
            const row = document.getElementById(`row-${satkerId}`);
            if (row) {
                // Mencari tulisan "Lebih: [Angka]" yang tidak disembunyikan (berarti error aktif)
                const errors = row.querySelectorAll('p[id^="err_"]:not(.hidden)');
                if (errors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Dapat Disimpan',
                        text: 'Terdapat input yang melebihi sisa kuota (kotak merah). Silakan perbaiki terlebih dahulu!',
                        confirmButtonColor: '#112D4E'
                    });
                    return; // Hentikan eksekusi, jangan panggil server
                }
            }

            const jfId = document.getElementById('filter_fungsional_distribusi').value;
            const btn = document.querySelector(`#row-${satkerId} button`);
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i>...`;
            btn.disabled = true;
            
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                await fetch(`{{ url('admin/jabatan/matriks/save') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ 
                        satker_id: satkerId, jabatan_id: jfId, tab_aktif: tabAktif,
                        kp: document.getElementById(`kp_${satkerId}`)?.value || 0,
                        kmu: document.getElementById(`kmu_${satkerId}`)?.value || 0,
                        kma: document.getElementById(`kma_${satkerId}`)?.value || 0,
                        ku: document.getElementById(`ku_${satkerId}`)?.value || 0,
                        k5: document.getElementById(`k5_${satkerId}`)?.value || 0,
                        k6: document.getElementById(`k6_${satkerId}`)?.value || 0,
                        k7: document.getElementById(`k7_${satkerId}`)?.value || 0,
                        k8: document.getElementById(`k8_${satkerId}`)?.value || 0
                    })
                });
                Toast.fire({icon: 'success', title: 'Data ' + tabAktif.toUpperCase() + ' Tersimpan!'});
            } catch (error) {
                Toast.fire({icon: 'error', title: 'Gagal Menyimpan'});
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // ==========================================
        // FITUR PENCARIAN JABATAN
        // ==========================================
        const rawData = @json($dropdownJabatans);
        const daftarJabatan = rawData.map(j => ({ id: j.id, nama: j.nama, kode: j.kode, kategori: j.kategori }));

        const searchInputJabatan = document.getElementById('search_jabatan_input');
        const hiddenInputJabatan = document.getElementById('filter_fungsional_distribusi');
        const hiddenKategoriJabatan = document.getElementById('filter_kategori_fungsional');
        const dropdownListJabatan = document.getElementById('jabatan_dropdown_list');
        const clearBtn = document.getElementById('clear_search_btn');
        let currentFocus = -1;

        function renderDropdownList(data) {
            dropdownListJabatan.innerHTML = '';
            if (data.length === 0) {
                dropdownListJabatan.innerHTML = '<li class="px-4 py-3 text-sm text-slate-500 text-center italic">Tidak ada kelompok jabatan yang cocok</li>';
            } else {
                data.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className = "px-4 py-2.5 cursor-pointer hover:bg-blue-50 transition-colors flex items-center justify-between group";
                    li.innerHTML = `<div class="flex flex-col"><span class="text-sm font-semibold text-slate-800">${item.nama}</span><span class="text-xs text-slate-500 font-mono mt-0.5">Kode Dasar: ${item.kode}</span></div><span class="px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-md shadow-sm border border-amber-200 uppercase">${item.kategori}</span>`;
                    li.addEventListener('click', function() { selectJabatan(item); });
                    dropdownListJabatan.appendChild(li);
                });
            }
            dropdownListJabatan.classList.remove('hidden');
        }

        function selectJabatan(item) {
            searchInputJabatan.value = `${item.nama} (${item.kode})`; 
            hiddenInputJabatan.value = item.id;
            hiddenKategoriJabatan.value = item.kategori;
            const hiddenKode = document.getElementById('filter_kode_fungsional');
            if (hiddenKode) hiddenKode.value = item.kode;
            dropdownListJabatan.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        }

        searchInputJabatan.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            clearBtn.classList.toggle('hidden', val === '');
            hiddenInputJabatan.value = '';
            if (document.getElementById('filter_kode_fungsional')) document.getElementById('filter_kode_fungsional').value = '';
            currentFocus = -1;
            if (!val) { dropdownListJabatan.classList.add('hidden'); return; }
            renderDropdownList(daftarJabatan.filter(item => item.nama.toLowerCase().includes(val) || item.kode.toLowerCase().includes(val)));
        });

        searchInputJabatan.addEventListener('focus', function() {
            if(this.value === '') renderDropdownList(daftarJabatan);
            else this.dispatchEvent(new Event('input'));
        });

        document.addEventListener('click', function(e) {
            if (!document.getElementById('jabatan-search-container').contains(e.target)) dropdownListJabatan.classList.add('hidden');
        });

        clearBtn.addEventListener('click', function() {
            searchInputJabatan.value = ''; hiddenInputJabatan.value = ''; hiddenKategoriJabatan.value = '';
            this.classList.add('hidden'); dropdownListJabatan.classList.add('hidden');
            document.getElementById('panel_setup_baseline').classList.add('hidden');
            document.getElementById('matriksTabsContainer').classList.add('hidden'); 
            document.getElementById('matriksTableBody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks".</td></tr>';
        });

        function openEditModalGlobal(group) {
            const form = document.getElementById('formEditJabatanGlobal');
            form.action = "{{ route('admin.jabatan.update_global') }}";
            
            const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };

            setVal('edit_global_kode_dasar', group.kode);
            setVal('edit_global_nama', group.nama_jabatan);
            
            let category = 'keahlian';
            if (group.jenjangs && group.jenjangs.length > 4) {
                category = 'semua';
            } else if (parseInt(group.jenjangs[0]?.kode_ujung) <= 4) {
                category = 'keterampilan';
            }
            
            if(typeof updateJenjangLabelsEdit === 'function') updateJenjangLabelsEdit(category);
            
            const jenjangs = group.jenjangs || [];
            // Nama fungsi diseragamkan menjadi getV
            const getV = (suffix, field) => {
                const row = jenjangs.find(j => j.kode_ujung == suffix);
                if (row && row[field] !== undefined && row[field] !== null) return row[field];
                return 0;
            };

            if (category === 'semua') {
                // J1-J4
                setVal('edit_b_p_menpan', getV('1', 'b_pertama_menpan'));
                setVal('edit_b_p_eks', getV('1', 'b_pertama_eksisting'));
                setVal('edit_b_p_low', getV('1', 'b_pertama_lowongan'));

                setVal('edit_b_mu_menpan', getV('2', 'b_muda_menpan'));
                setVal('edit_b_mu_eks', getV('2', 'b_muda_eksisting'));
                setVal('edit_b_mu_low', getV('2', 'b_muda_lowongan'));

                setVal('edit_b_ma_menpan', getV('3', 'b_madya_menpan'));
                setVal('edit_b_ma_eks', getV('3', 'b_madya_eksisting'));
                setVal('edit_b_ma_low', getV('3', 'b_madya_lowongan'));

                setVal('edit_b_u_menpan', getV('4', 'b_utama_menpan')); // Sudah diperbaiki menjadi getV
                setVal('edit_b_u_eks', getV('4', 'b_utama_eksisting')); // Sudah diperbaiki menjadi getV
                setVal('edit_b_u_low', getV('4', 'b_utama_lowongan')); // Sudah diperbaiki menjadi getV

                // J5-J8
                setVal('edit_b_lima_menpan', getV('5', 'b_lima_menpan'));
                setVal('edit_b_lima_eks', getV('5', 'b_lima_eksisting'));
                setVal('edit_b_lima_low', getV('5', 'b_lima_lowongan'));

                setVal('edit_b_enam_menpan', getV('6', 'b_enam_menpan'));
                setVal('edit_b_enam_eks', getV('6', 'b_enam_eksisting'));
                setVal('edit_b_enam_low', getV('6', 'b_enam_lowongan'));

                setVal('edit_b_tujuh_menpan', getV('7', 'b_tujuh_menpan'));
                setVal('edit_b_tujuh_eks', getV('7', 'b_tujuh_eksisting'));
                setVal('edit_b_tujuh_low', getV('7', 'b_tujuh_lowongan'));

                setVal('edit_b_delapan_menpan', getV('8', 'b_delapan_menpan'));
                setVal('edit_b_delapan_eks', getV('8', 'b_delapan_eksisting'));
                setVal('edit_b_delapan_low', getV('8', 'b_delapan_lowongan'));

            } else if (category === 'keahlian') {
                setVal('edit_b_p_menpan', getV('5', 'b_pertama_menpan'));
                setVal('edit_b_p_eks', getV('5', 'b_pertama_eksisting'));
                setVal('edit_b_p_low', getV('5', 'b_pertama_lowongan'));

                setVal('edit_b_mu_menpan', getV('6', 'b_muda_menpan'));
                setVal('edit_b_mu_eks', getV('6', 'b_muda_eksisting'));
                setVal('edit_b_mu_low', getV('6', 'b_muda_lowongan'));

                setVal('edit_b_ma_menpan', getV('7', 'b_madya_menpan'));
                setVal('edit_b_ma_eks', getV('7', 'b_madya_eksisting'));
                setVal('edit_b_ma_low', getV('7', 'b_madya_lowongan'));

                setVal('edit_b_u_menpan', getV('8', 'b_utama_menpan'));
                setVal('edit_b_u_eks', getV('8', 'b_utama_eksisting'));
                setVal('edit_b_u_low', getV('8', 'b_utama_lowongan'));

            } else {
                setVal('edit_b_p_menpan', getV('1', 'b_pertama_menpan'));
                setVal('edit_b_p_eks', getV('1', 'b_pertama_eksisting'));
                setVal('edit_b_p_low', getV('1', 'b_pertama_lowongan'));

                setVal('edit_b_mu_menpan', getV('2', 'b_muda_menpan'));
                setVal('edit_b_mu_eks', getV('2', 'b_muda_eksisting'));
                setVal('edit_b_mu_low', getV('2', 'b_muda_lowongan'));

                setVal('edit_b_ma_menpan', getV('3', 'b_madya_menpan'));
                setVal('edit_b_ma_eks', getV('3', 'b_madya_eksisting'));
                setVal('edit_b_ma_low', getV('3', 'b_madya_lowongan'));

                setVal('edit_b_u_menpan', getV('4', 'b_utama_menpan'));
                setVal('edit_b_u_eks', getV('4', 'b_utama_eksisting'));
                setVal('edit_b_u_low', getV('4', 'b_utama_lowongan'));
            }

            toggleModal('modalEditJabatanGlobal');
        }

        function updateJenjangLabelsEdit(kategori) {
            // 1. Tentukan apakah harus memunculkan 8 input
            const isSemua = (kategori === 'semua');
            
            // 2. Cari semua div yang punya class jenjang-5-8 di modal edit
            // Pastikan div di HTML modal edit sudah diberi class 'jenjang-5-8'
            document.querySelectorAll('#modalEditJabatanGlobal .jenjang-5-8').forEach(el => {
                el.classList.toggle('hidden', !isSemua);
            });

            // 3. Siapkan daftar nama label
            const labels = ["Pemula", "Terampil", "Mahir", "Penyelia", "Ahli Pertama", "Ahli Muda", "Ahli Madya", "Ahli Utama"];

            // 4. Update Label J1 sampai J4 (Selalu ada)
            let startIndex = 0;
            if (kategori === 'keahlian') startIndex = 4; // Mulai dari Ahli Pertama jika kategori Keahlian biasa

            for (let i = 1; i <= 4; i++) {
                document.querySelectorAll('.lbl_edit_j' + i).forEach(el => {
                    el.innerText = labels[startIndex + (i - 1)];
                });
            }

            // 5. Update Label J5 sampai J8 (Hanya jika kategori 'semua')
            if (isSemua) {
                for (let i = 5; i <= 8; i++) {
                    document.querySelectorAll('.lbl_edit_j' + i).forEach(el => {
                        el.innerText = labels[i - 1];
                    });
                }
            }
        }

        // PERUBAHAN: Fungsi Auto Hitung Lowongan di Modal Edit
        window.autoCalcEditModal = function() {
            ['p', 'mu', 'ma', 'u'].forEach(lvl => {
                const m = parseInt(document.getElementById(`edit_b_${lvl}_menpan`)?.value) || 0;
                const e = parseInt(document.getElementById(`edit_b_${lvl}_eks`)?.value) || 0;
                const low = document.getElementById(`edit_b_${lvl}_low`);
                if(low) low.value = m - e;
            });
        }

        // PERUBAHAN: Fungsi Auto Hitung Lowongan di Modal Tambah
        window.autoCalcTambahModal = function() {
            ['pertama', 'muda', 'madya', 'utama'].forEach(lvl => {
                const m = parseInt(document.querySelector(`input[name="b_${lvl}_menpan"]`)?.value) || 0;
                const e = parseInt(document.querySelector(`input[name="b_${lvl}_eksisting"]`)?.value) || 0;
                const low = document.querySelector(`input[name="b_${lvl}_lowongan"]`);
                if(low) low.value = m - e;
            });
        }

        // Fungsi menghitung selisih saat ngetik di Modal Tambah
        window.autoCalcTambah = function() {
            // KUNCI: Tambahkan 'lima', 'enam', 'tujuh', 'delapan'
            ['pertama', 'muda', 'madya', 'utama', 'lima', 'enam', 'tujuh', 'delapan'].forEach(lvl => {
                let menpan = parseInt(document.querySelector(`input[name="b_${lvl}_menpan"]`)?.value) || 0;
                let eksisting = parseInt(document.querySelector(`input[name="b_${lvl}_eksisting"]`)?.value) || 0;
                let lowongan = document.querySelector(`input[name="b_${lvl}_lowongan"]`);
                
                if(lowongan) lowongan.value = menpan - eksisting;
            });
        }

        // Fungsi menghitung selisih saat ngetik di Modal Edit
        window.autoCalcEdit = function() {
            // KUNCI: Tambahkan 'lima', 'enam', 'tujuh', 'delapan'
            ['p', 'mu', 'ma', 'u', 'lima', 'enam', 'tujuh', 'delapan'].forEach(lvl => {
                let menpan = parseInt(document.getElementById(`edit_b_${lvl}_menpan`)?.value) || 0;
                let eksisting = parseInt(document.getElementById(`edit_b_${lvl}_eks`)?.value) || 0;
                let lowongan = document.getElementById(`edit_b_${lvl}_low`);
                
                if(lowongan) lowongan.value = menpan - eksisting;
            });
        }

        // Fungsi untuk menampilkan/menyembunyikan label sisa secara dinamis (melayang)
        window.showSisaLabel = function(type, id) {
            const el = document.getElementById(`floating_sisa_${type}_${id}`);
            if (el) el.classList.remove('invisible', 'opacity-0');
        }

        window.hideSisaLabel = function(type, id) {
            const el = document.getElementById(`floating_sisa_${type}_${id}`);
            if (el) el.classList.add('invisible', 'opacity-0');
        }

        // MESIN PENCARI VSCODE STYLE UNTUK TABEL
        document.addEventListener('alpine:init', () => {
            Alpine.data('searchVSCode', (containerId, rowSelector) => ({
                search: '',
                matches: [],
                currentIndex: 0,
                
                init() {
                    this.$watch('search', () => this.doSearch());
                    // Dengarkan event jika tabel dirender ulang (khusus tab distribusi)
                    window.addEventListener('dom-updated', () => {
                        if (this.search) this.doSearch();
                    });
                },
                
                doSearch() {
                    // Bersihkan highlight sebelumnya
                    this.matches.forEach(el => el.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-100'));
                    this.matches = [];
                    this.currentIndex = 0;
                    
                    if (!this.search) return;
                    
                    const term = this.search.toLowerCase();
                    const container = document.getElementById(containerId);
                    if (!container) return;
                    
                    const rows = container.querySelectorAll(rowSelector);
                    rows.forEach(row => {
                        const text = row.getAttribute('data-search') || '';
                        if (text.includes(term)) {
                            this.matches.push(row);
                        }
                    });
                    
                    if (this.matches.length > 0) this.scrollToMatch(0);
                },
                
                scrollToMatch(index) {
                    if (this.matches.length === 0) return;
                    if (this.matches[this.currentIndex]) {
                        this.matches[this.currentIndex].classList.remove('ring-2', 'ring-blue-500', 'bg-blue-100');
                    }
                    
                    if (index < 0) index = this.matches.length - 1;
                    if (index >= this.matches.length) index = 0;
                    this.currentIndex = index;
                    
                    const target = this.matches[this.currentIndex];
                    target.classList.add('ring-2', 'ring-blue-500', 'bg-blue-100');
                    
                    // Gulir layar tepat ke tengah elemen yang dicari
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                },
                
                nextMatch() { this.scrollToMatch(this.currentIndex + 1); },
                prevMatch() { this.scrollToMatch(this.currentIndex - 1); }
            }));
        });
    </script>
@endpush