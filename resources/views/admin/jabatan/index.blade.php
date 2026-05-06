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
    <div id="content-master" class="block animate-fade-in">
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
                    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Cari kode atau jabatan..."
                        class="block w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-[#112D4E] transition">
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
                            <tr class="bg-slate-50/80 border-t-2 border-slate-200">
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
                                <tr class="hover:bg-blue-50/30 transition group/row bg-white">
                                    <td class="px-6 py-3 text-sm text-slate-600 font-mono font-bold pl-12"><i class="fas fa-level-up-alt rotate-90 text-slate-300 mr-2 text-xs"></i>{{ $j['kode'] }}</td>
                                    <td class="px-6 py-3 text-sm text-slate-700 font-medium">{{ $j['nama_lengkap'] }}</td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-1 rounded shadow-sm border border-amber-200">Baseline: {{ $j['baseline'] ?? 0 }}</span>
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
                    <ul id="jabatan_dropdown_list" class="absolute z-50 w-full bg-white border border-gray-100 rounded-xl shadow-xl mt-2 hidden max-h-64 overflow-y-auto divide-y divide-gray-50"></ul>
                </div>
                <button type="button" onclick="loadMatriks()" class="bg-[#112D4E] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition flex items-center">
                    <i class="fas fa-search mr-2"></i> Tampilkan Matriks
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative"
             x-data="{
                searchMatriks: '',
                matchesMatriks: [],
                currentMatchMatriksIndex: 0,
                scrollTimeoutMatriks: null,

                init() {
                    this.$watch('searchMatriks', (val) => {
                        if (val && val.trim().length > 0) {
                            clearTimeout(this.scrollTimeoutMatriks);
                            
                            this.scrollTimeoutMatriks = setTimeout(() => {
                                const container = document.getElementById('matriksTableContainer');
                                if (!container) return;

                                const term = val.toLowerCase().trim();
                                const rows = container.querySelectorAll('.matriks-row');
                                
                                // Bersihkan highlight lama secara manual
                                document.querySelectorAll('.matriks-row.ring-2').forEach(el => {
                                    el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
                                });
                                
                                let foundMatches = [];
                                rows.forEach(row => {
                                    const nameSpan = row.querySelector('.satker-name');
                                    // Gunakan textContent agar aman dari style tersembunyi
                                    if (nameSpan && nameSpan.textContent.toLowerCase().includes(term)) {
                                        // KUNCI: Hanya simpan ID-nya saja, bukan elemen utuh!
                                        if (row.id) foundMatches.push(row.id);
                                    }
                                });

                                this.matchesMatriks = foundMatches;
                                this.currentMatchMatriksIndex = 0;

                                if (this.matchesMatriks.length > 0) {
                                    this.scrollToMatchMatriks(0);
                                }
                            }, 300);
                        } else {
                            document.querySelectorAll('.matriks-row.ring-2').forEach(el => {
                                el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
                            });
                            this.matchesMatriks = [];
                        }
                    });
                },

                scrollToMatchMatriks(index) {
                    if (this.matchesMatriks.length === 0) return;
                    
                    document.querySelectorAll('.matriks-row.ring-2').forEach(el => {
                        el.classList.remove('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');
                    });
                    
                    if (index < 0) index = this.matchesMatriks.length - 1;
                    if (index >= this.matchesMatriks.length) index = 0;
                    
                    this.currentMatchMatriksIndex = index;
                    
                    // Ambil target elemen berdasarkan ID yang tersimpan
                    const targetId = this.matchesMatriks[this.currentMatchMatriksIndex];
                    const target = document.getElementById(targetId);
                    
                    if(target) {
                        target.classList.add('ring-2', 'ring-blue-400', 'bg-blue-50', 'transition-all');

                        const container = document.getElementById('matriksTableContainer');
                        const cRect = container.getBoundingClientRect();
                        const mRect = target.getBoundingClientRect();
                        
                        container.scrollTo({
                            top: container.scrollTop + (mRect.top - cRect.top) - 40,
                            behavior: 'smooth'
                        });
                    }
                },
                
                nextMatchMatriks() { this.scrollToMatchMatriks(this.currentMatchMatriksIndex + 1); },
                prevMatchMatriks() { this.scrollToMatchMatriks(this.currentMatchMatriksIndex - 1); }
             }">

            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex flex-col sm:flex-row justify-between items-center gap-3">
                <h3 class="font-bold text-blue-900 text-sm">Matriks Alokasi Kuota Satker</h3>
                
                {{-- KUNCI PERBAIKAN: Input Search Live --}}
                <div id="matriksSearchContainer" class="relative w-full sm:w-64 hidden">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-blue-400 text-xs"></i>
                    </span>
                    <input type="text" x-model.debounce.300ms="searchMatriks" 
                        @keydown.enter.prevent="if($event.shiftKey) prevMatchMatriks(); else nextMatchMatriks();"
                        placeholder="Cari satker (Enter utk Next)"
                        class="w-full pl-9 pr-4 py-2 bg-white border border-blue-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-sm placeholder-blue-300 text-blue-800 font-medium">
                </div>
            </div>
            
            <div id="panel_setup_baseline" class="hidden bg-amber-50 border-b-2 border-amber-200 p-4"></div>

            {{-- KUNCI PERBAIKAN: Tambahkan ID scroll container --}}
            <div id="matriksTableContainer" class="overflow-x-auto max-h-[60vh] overflow-y-auto scroll-smooth">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 border-b border-r border-gray-200 text-xs font-bold text-slate-700 uppercase w-1/3">Struktur Satuan Kerja</th>
                            <th colspan="4" class="px-6 py-2 border-b border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-slate-100/50">Setup Kuota per Jenjang</th>
                            <th rowspan="2" class="px-4 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-emerald-50">Jumlah</th>
                            <th rowspan="2" class="px-6 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase w-32">Aksi</th>
                        </tr>
                        <tr>
                            <th id="th_jenjang_1" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 1</th>
                            <th id="th_jenjang_2" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 2</th>
                            <th id="th_jenjang_3" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 3</th>
                            <th id="th_jenjang_4" class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Jenjang 4</th>
                        </tr>
                    </thead>
                    <tbody id="matriksTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- KUNCI PERBAIKAN: Navigator DIPINDAHKAN KE DALAM X-DATA --}}
            {{-- FLOATING SEARCH NAVIGATOR (VSCode Style) --}}      
            <div x-show="searchMatriks && matchesMatriks.length > 0" x-transition.opacity x-cloak
                 class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-blue-900 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.3)] border border-blue-800 rounded-full px-5 py-2 flex items-center gap-4 z-[99]">
                
                <div class="text-xs font-bold text-blue-100 tracking-wide">
                    <span x-text="currentMatchMatriksIndex + 1" class="text-white"></span> 
                    <span class="text-blue-300 mx-1">dari</span> 
                    <span x-text="matchesMatriks.length"></span>
                </div>
                
                <div class="w-[1px] h-4 bg-blue-700"></div>
                
                <div class="flex items-center gap-2">
                    <button @click="prevMatchMatriks()" class="w-7 h-7 flex items-center justify-center rounded-full bg-blue-800 text-blue-200 hover:bg-white hover:text-blue-900 transition" title="Sebelumnya (Shift + Enter)">
                        <i class="fas fa-chevron-up text-[10px]"></i>
                    </button>
                    <button @click="nextMatchMatriks()" class="w-7 h-7 flex items-center justify-center rounded-full bg-blue-800 text-blue-200 hover:bg-white hover:text-blue-900 transition" title="Selanjutnya (Enter)">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                </div>
            </div>

        </div> </div> {{-- MODAL TAMBAH --}}

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
                            </div>
                        </div>

                        <div id="container_baseline_tambah" class="hidden bg-amber-50 p-4 rounded-xl border border-amber-200 mt-2">
                            <label class="block text-xs font-bold text-amber-900 uppercase mb-3">Setup Baseline Kuota per Jenjang</label>
                            <div class="grid grid-cols-4 gap-2">
                                <div>
                                    <label id="lbl_tambah_j1" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 1</label>
                                    <input type="number" name="b_pertama" id="tambah_b_pertama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j2" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 2</label>
                                    <input type="number" name="b_muda" id="tambah_b_muda" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j3" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 3</label>
                                    <input type="number" name="b_madya" id="tambah_b_madya" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j4" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 4</label>
                                    <input type="number" name="b_utama" id="tambah_b_utama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
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

                        <div id="container_baseline_edit" class="bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <label class="block text-xs font-bold text-amber-900 uppercase mb-3">Update Baseline Kuota per Jenjang</label>
                            <div class="grid grid-cols-4 gap-2">
                                <div>
                                    <label id="lbl_edit_j1" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 1</label>
                                    <input type="number" name="b_pertama" id="edit_b_pertama" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_edit_j2" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 2</label>
                                    <input type="number" name="b_muda" id="edit_b_muda" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_edit_j3" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 3</label>
                                    <input type="number" name="b_madya" id="edit_b_madya" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
                                </div>
                                <div>
                                    <label id="lbl_edit_j4" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 4</label>
                                    <input type="number" name="b_utama" id="edit_b_utama" min="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none focus:ring-2 focus:ring-amber-500">
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
            const container = document.getElementById('container_baseline_tambah');
            container.classList.remove('hidden');

            const lbl1 = document.getElementById('lbl_tambah_j1');
            const lbl2 = document.getElementById('lbl_tambah_j2');
            const lbl3 = document.getElementById('lbl_tambah_j3');
            const lbl4 = document.getElementById('lbl_tambah_j4');

            if (kategori === 'keterampilan') {
                lbl1.innerText = "Pemula";
                lbl2.innerText = "Terampil";
                lbl3.innerText = "Mahir";
                lbl4.innerText = "Penyelia";
            } else if (kategori === 'keahlian') {
                lbl1.innerText = "Ahli Pertama";
                lbl2.innerText = "Ahli Muda";
                lbl3.innerText = "Ahli Madya";
                lbl4.innerText = "Ahli Utama";
            }
        }

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
        async function loadMatriks() {
            const fungsionalId = document.getElementById('filter_fungsional_distribusi').value;
            const kategori = document.getElementById('filter_kategori_fungsional').value;

            if (!fungsionalId) {
                Swal.fire({icon: 'warning', title: 'Pilih Jabatan', text: 'Silakan pilih Kelompok Jabatan terlebih dahulu!', confirmButtonColor: '#112D4E'});
                return;
            }

            Swal.fire({ 
                title: 'Mengunduh Data...', 
                text: 'Harap tunggu, mengambil data satker dari server.', 
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); } 
            });

            try {
                // Gunakan perbandingan string kategori
                const isKeterampilan = (kategori === 'Keterampilan');

                if (isKeterampilan) {
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

                const response = await fetch(`{{ url('admin/jabatan/matriks') }}?jabatan_id=${fungsionalId}`);
                const dataJSON = await response.json();
                const data = dataJSON.satkers || [];
                
                const bp = parseInt(dataJSON.b_pertama) || 0;
                const bmu = parseInt(dataJSON.b_muda) || 0;
                const bma = parseInt(dataJSON.b_madya) || 0;
                const bu = parseInt(dataJSON.b_utama) || 0;

                const grandTotal = bp + bmu + bma + bu;

                const lbl1 = isKeterampilan ? "Pemula" : "Ahli Pertama";
                const lbl2 = isKeterampilan ? "Terampil" : "Ahli Muda";
                const lbl3 = isKeterampilan ? "Mahir" : "Ahli Madya";
                const lbl4 = isKeterampilan ? "Penyelia" : "Ahli Utama";

                const panelBaseline = document.getElementById('panel_setup_baseline');
                panelBaseline.classList.remove('hidden');
                document.getElementById('matriksSearchContainer').classList.remove('hidden');
                panelBaseline.innerHTML = `
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <p class="text-xs text-amber-700 font-bold uppercase mb-1">TOTAL FORMASI MENPAN RB</p>
                            <span class="text-2xl font-black text-amber-900 bg-amber-200/50 px-4 py-1 rounded-lg">${grandTotal}</span>
                        </div>
                        <div class="flex-1 flex gap-3 items-end justify-end">
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl1}</p><div id="display_base_p" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bp}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl2}</p><div id="display_base_mu" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bmu}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl3}</p><div id="display_base_ma" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bma}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl4}</p><div id="display_base_u" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bu}</div></div>
                        </div>
                    </div>
                `;

                function createInputHTML(val, id, type, parentId, eks, isAhliPertama) {
                    const v = (val == 0 || val == null) ? '' : val;
                    const safeParentId = parentId ? parentId : 'root';
                    
                    const kuotaNum = parseInt(val) || 0;
                    const sisa = kuotaNum - eks;
                    const isMinus = sisa < 0;
                    
                    let sisaColor = 'text-emerald-600';
                    if (isMinus) {
                        sisaColor = isAhliPertama ? 'text-amber-500' : 'text-red-500';
                    }

                    return `
                    <div class="relative w-full pt-1 pb-3">
                        <div id="tooltip_${type}_${id}" class="absolute bottom-[95%] left-1/2 transform -translate-x-1/2 mb-1 bg-slate-800 text-white text-[10px] px-2 py-1 rounded shadow-xl hidden z-20 whitespace-nowrap transition-opacity duration-200 opacity-0 pointer-events-none">
                            Sisa Baseline: <span id="sisa_val_${type}_${id}" class="font-bold text-amber-300"></span>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                        </div>

                        <input type="number" min="0"
                            id="${type}_${id}"
                            data-type="${type}"
                            data-parent="${safeParentId}"
                            value="${v}"
                            placeholder="0"
                            onfocus="handleFocus(this, '${id}', '${type}', '${safeParentId}')"
                            onblur="handleBlur(this, '${id}', '${type}')"
                            oninput="handleInput(this, '${id}', '${type}', '${safeParentId}')"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white">
                        
                        <p id="err_${type}_${id}" class="absolute bottom-[-2px] left-0 right-0 text-center text-[9px] text-red-500 font-bold hidden leading-none tracking-tight">Lebih <span id="err_val_${type}_${id}"></span></p>
                    </div>
                    `;
                }

                const tbody = document.getElementById('matriksTableBody');
                tbody.innerHTML = '';

                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-8 text-slate-500">Data Satker Kosong di Periode ini</td></tr>`;
                    Swal.close();
                    return;
                }

                // =========================================================
                // TEKNIK CHUNKING: Memecah render 100 baris per frame
                // agar CPU Browser tidak jebol / freeze
                // =========================================================
                Swal.update({ title: 'Membangun Tabel...', text: 'Memproses baris 0%' });
                
                const CHUNK_SIZE = 100;
                let currentIndex = 0;

                function renderChunk() {
                    let html = '';
                    const end = Math.min(currentIndex + CHUNK_SIZE, data.length);
                    
                    for (; currentIndex < end; currentIndex++) {
                        const item = data[currentIndex];
                        const level = item.level;
                        const isParent = item.has_children || level === 0; 
                        
                        const padStyle = `padding-left: ${1.5 + (level * 2.5)}rem;`; 
                        const bgClass = level === 0 ? 'bg-white' : (level % 2 === 1 ? 'bg-slate-50/70' : 'bg-slate-50');
                        const textClass = level === 0 ? 'font-bold text-slate-800' : (level === 1 ? 'font-semibold text-slate-700' : 'font-medium text-slate-600');
                        
                        const iconHtml = level === 0 
                            ? '<i class="fas fa-building mr-3 text-slate-400"></i>' 
                            : '<i class="fas fa-level-up-alt rotate-90 text-slate-300 mr-2 opacity-70 text-xs"></i>';

                        const totalRow = parseInt(item.kuota_pertama || 0) + parseInt(item.kuota_muda || 0) + parseInt(item.kuota_madya || 0) + parseInt(item.kuota_utama || 0);
                        
                        const kp = item.kuota_pertama == 0 ? '' : item.kuota_pertama;
                        const kmu = item.kuota_muda == 0 ? '' : item.kuota_muda;
                        const kma = item.kuota_madya == 0 ? '' : item.kuota_madya;
                        const ku = item.kuota_utama == 0 ? '' : item.kuota_utama;

                        // Tarik Data Eksisting Baru
                        const eks1 = item.eks_pertama || 0;
                        const eks2 = item.eks_muda || 0;
                        const eks3 = item.eks_madya || 0;
                        const eks4 = item.eks_utama || 0;
                        
                        // Deteksi Pengecualian Ahli Pertama (Bukan Keterampilan)
                        const isAP = !isKeterampilan;

                        html += `
                            <tr class="transition border-b border-gray-100 ${bgClass} matriks-row" id="row-${item.id}" data-parent="${item.parent_id || 'root'}">
                                <td class="py-4 pr-6 align-middle" style="${padStyle}">
                                    <div class="flex items-center ${textClass}">
                                        ${iconHtml}
                                        <span class="text-sm tracking-tight leading-snug satker-name">${item.nama_satker}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kp, item.id, 'kp', item.parent_id, eks1, isAP) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kp === '' ? '0' : kp}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kmu, item.id, 'kmu', item.parent_id, eks2, false) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kmu === '' ? '0' : kmu}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kma, item.id, 'kma', item.parent_id, eks3, false) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kma === '' ? '0' : kma}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(ku, item.id, 'ku', item.parent_id, eks4, false) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${ku === '' ? '0' : ku}</div>`}</td>
                                <td class="px-4 py-3 align-middle text-center font-bold text-emerald-600" id="total_${item.id}">${totalRow}</td>
                                <td class="px-4 py-3 align-middle text-center">
                                    ${isParent 
                                        ? `<button onclick="${permMatriksEditKuota ? `simpanKuotaGroup('${item.id}')` : `Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin.', 'error')`}" class="text-[11px] ${permMatriksEditKuota ? 'bg-[#112D4E] hover:bg-blue-900 text-white' : 'bg-slate-300 text-slate-500 opacity-60'} px-3 py-2 rounded-md transition shadow-sm w-full font-bold uppercase tracking-wider"><i class="fas fa-save mr-1"></i> Simpan</button>` 
                                        : `<span class="text-[10px] text-slate-400 italic block w-full">Via Induk</span>`
                                    }
                                </td>
                            </tr>
                        `;
                    }
                    
                    // Tempelkan blok 100 baris ke HTML (Ringan)
                    tbody.insertAdjacentHTML('beforeend', html);

                    // Jika masih ada sisa, lanjutkan proses render
                    if (currentIndex < data.length) {
                        Swal.update({ text: `Memproses baris ${Math.round((currentIndex / data.length) * 100)}%` });
                        requestAnimationFrame(renderChunk);
                    } else {
                        // Jika sudah selesai 100%, jalankan validasi
                        Swal.update({ title: 'Memvalidasi Kuota...', text: 'Tunggu sebentar' });
                        setTimeout(() => {
                            validateAllHierarchies();
                            Swal.close();
                        }, 50);
                    }
                }

                // Panggil render pertama
                requestAnimationFrame(renderChunk);

            } catch (error) {
                Swal.fire({icon: 'error', title: 'Error', text: 'Gagal memuat data dari server.'});
            }
        }

        // ==========================================
        // LOGIKA INTERAKTIF (TOOLTIP, SISA, & MERAH)
        // ==========================================
        function getBaseId(type) {
            return { 'kp': 'display_base_p', 'kmu': 'display_base_mu', 'kma': 'display_base_ma', 'ku': 'display_base_u' }[type];
        }

        // FUNGSI BARU: Hitung Sisa Langsung dari Grand Total Menpan RB
        function getLimitAndSisa(input, type) {
            // 1. Ambil Limit dari Grand Total
            const limit = parseInt(document.getElementById(getBaseId(type))?.innerText) || 0;
            
            // 2. Jumlahkan semua input di kolom yang sama (Kecuali kotak yang sedang diketik ini)
            let otherSum = 0;
            document.querySelectorAll(`input[data-type="${type}"]`).forEach(el => {
                if (el.id !== input.id) otherSum += parseInt(el.value) || 0;
            });

            // 3. Sisa yang tersedia untuk kotak ini
            const sisa = limit - otherSum;
            const val = parseInt(input.value) || 0;
            
            return { limit, otherSum, sisa, isExceeding: val > sisa, excessAmount: val - sisa };
        }

        function handleFocus(input, id, type) {
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:ring-red-500');
            input.classList.add('focus:ring-blue-500', 'border-gray-300');
            
            const errEl = document.getElementById(`err_${type}_${id}`);
            if(errEl) errEl.classList.add('hidden');

            const { sisa } = getLimitAndSisa(input, type);
            const sisaValEl = document.getElementById(`sisa_val_${type}_${id}`);
            if(sisaValEl) sisaValEl.innerText = sisa;
            
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            if(tooltip) {
                tooltip.classList.remove('hidden');
                setTimeout(() => tooltip.classList.remove('opacity-0'), 10);
            }
        }

        function handleBlur(input, id, type) {
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            if(tooltip) {
                tooltip.classList.add('opacity-0');
                setTimeout(() => tooltip.classList.add('hidden'), 200);
            }
            validateAllHierarchies(); // Validasi ulang semua saat kotak ditinggalkan
        }

        function handleInput(input, id, type) {
            const kp = parseInt(document.getElementById(`kp_${id}`)?.value) || 0;
            const kmu = parseInt(document.getElementById(`kmu_${id}`)?.value) || 0;
            const kma = parseInt(document.getElementById(`kma_${id}`)?.value) || 0;
            const ku = parseInt(document.getElementById(`ku_${id}`)?.value) || 0;
            
            const totalEl = document.getElementById(`total_${id}`);
            if(totalEl) totalEl.innerText = kp + kmu + kma + ku;

            // Validasi Real-Time ke seluruh baris saat mengetik
            validateAllHierarchies();
        }

        // OPTIMASI TOTAL: Memeriksa semua baris terhadap Grand Total secara Real-Time
        function validateAllHierarchies() {
            let sums = { kp: 0, kmu: 0, kma: 0, ku: 0 };
            const inputs = document.querySelectorAll('#matriksTableBody input[data-type]');
            
            // Deteksi apakah ini Kategori Keahlian (Ahli Pertama)
            const kategoriFungsional = document.getElementById('filter_kategori_fungsional')?.value || '';
            const isAhliPertama = (kategoriFungsional === 'Keahlian' || kategoriFungsional === 'keahlian');

            // 1. Kumpulkan total seluruh input per kolom
            inputs.forEach(input => {
                const type = input.dataset.type;
                sums[type] += (parseInt(input.value) || 0);
            });

            // 2. Ambil limit Grand Total Menpan
            const limits = {
                kp: parseInt(document.getElementById('display_base_p')?.innerText) || 0,
                kmu: parseInt(document.getElementById('display_base_mu')?.innerText) || 0,
                kma: parseInt(document.getElementById('display_base_ma')?.innerText) || 0,
                ku: parseInt(document.getElementById('display_base_u')?.innerText) || 0,
            };

            // 3. Terapkan warna merah jika ada yang menyebabkan jebol
            inputs.forEach(input => {
                const type = input.dataset.type;
                const id = input.id.split('_')[1];
                const val = parseInt(input.value) || 0;
                
                const limit = limits[type];
                const otherSum = sums[type] - val;
                const sisa = limit - otherSum;
                let isExceeding = val > sisa;

                // ========================================================
                // KUNCI: PENGECUALIAN KHUSUS AHLI PERTAMA (KEBAL LIMIT)
                // ========================================================
                if (isAhliPertama && type === 'kp') {
                    isExceeding = false; // Paksa jadi false, abaikan error limit
                }

                const errEl = document.getElementById(`err_${type}_${id}`);
                const errValEl = document.getElementById(`err_val_${type}_${id}`);

                if (isExceeding) {
                    input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.remove('border-gray-300');
                    if (errValEl) errValEl.innerText = val - sisa;
                    if (errEl) errEl.classList.remove('hidden');
                } else {
                    input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.add('border-gray-300');
                    if (errEl) errEl.classList.add('hidden');
                }
            });
        }

        // ==========================================
        // FUNGSI SIMPAN
        // ==========================================
        async function simpanKuotaGroup(parentId) {
            const jfId = document.getElementById('filter_fungsional_distribusi').value;
            
            const groupIds = [parentId];
            document.querySelectorAll(`input[data-parent="${parentId}"]`).forEach(el => {
                const childId = el.id.split('_')[1];
                if (!groupIds.includes(childId)) groupIds.push(childId);
            });

            let hasError = false;
            validateAllHierarchies();
            groupIds.forEach(id => {
                ['kp', 'kmu', 'kma', 'ku'].forEach(type => {
                    const input = document.getElementById(`${type}_${id}`);
                    if (input && input.classList.contains('border-red-500')) hasError = true;
                });
            });

            if (hasError) {
                Swal.fire({icon: 'error', title: 'Distribusi Ditolak!', text: 'Ada input yang melebihi batas/limit kuota. Silakan periksa kotak yang berwarna merah!'});
                return;
            }

            const btn = document.querySelector(`#row-${parentId} button`);
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;
            btn.disabled = true;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // MENGGUNAKAN FOR EACH SECARA SEKUENSIAL AGAR DATABASE LEBIH AMAN
                for (const id of groupIds) {
                    await fetch(`{{ url('admin/jabatan/matriks/save') }}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ 
                            satker_id: id, jabatan_id: jfId, 
                            kuota_pertama: parseInt(document.getElementById(`kp_${id}`).value) || 0, 
                            kuota_muda: parseInt(document.getElementById(`kmu_${id}`).value) || 0, 
                            kuota_madya: parseInt(document.getElementById(`kma_${id}`).value) || 0, 
                            kuota_utama: parseInt(document.getElementById(`ku_${id}`).value) || 0 
                        })
                    });
                }
                
                Toast.fire({icon: 'success', title: 'Distribusi Tersimpan!'});
                
                groupIds.forEach(id => {
                    const row = document.getElementById(`row-${id}`);
                    if(row) {
                        row.classList.add('bg-emerald-50');
                        setTimeout(() => row.classList.remove('bg-emerald-50'), 1500);
                    }
                });

            } catch (error) {
                Toast.fire({icon: 'error', title: 'Gagal menghubungi server'});
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // ==========================================
        // FITUR AUTOCOMPLETE PENCARIAN JABATAN
        // ==========================================
        const rawData = @json($dropdownJabatans);
        const daftarJabatan = rawData.map(j => {
            return {
                id: j.id,
                nama: j.nama,
                kode: j.kode,
                kategori: j.kategori
            };
        });

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
                    li.dataset.id = item.id;
                    li.dataset.index = index;
                    
                    let jenjangHtml = `<span class="px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-md whitespace-nowrap ml-2 shadow-sm border border-amber-200 uppercase tracking-wider">${item.kategori}</span>`;

                    li.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-[#112D4E]">${item.nama}</span>
                            <span class="text-xs text-slate-500 font-mono mt-0.5">Kode Dasar: ${item.kode}</span>
                        </div>
                        ${jenjangHtml}
                    `;

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
            dropdownListJabatan.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        }

        searchInputJabatan.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            clearBtn.classList.toggle('hidden', val === '');
            hiddenInputJabatan.value = '';
            hiddenKodeFungsional.value = '';
            currentFocus = -1;
            if (!val) { dropdownListJabatan.classList.add('hidden'); return; }
            const filteredData = daftarJabatan.filter(item => item.nama.toLowerCase().includes(val) || item.kode.toLowerCase().includes(val));
            renderDropdownList(filteredData);
        });

        searchInputJabatan.addEventListener('focus', function() {
            if(this.value === '') renderDropdownList(daftarJabatan);
            else this.dispatchEvent(new Event('input'));
        });

        searchInputJabatan.addEventListener('keydown', function(e) {
            const items = dropdownListJabatan.getElementsByTagName('li');
            if (dropdownListJabatan.classList.contains('hidden') || items.length === 0) return;
            if (e.key === "ArrowDown") { currentFocus++; addActive(items); } 
            else if (e.key === "ArrowUp") { currentFocus--; addActive(items); } 
            else if (e.key === "Enter") {
                e.preventDefault();
                if (currentFocus > -1) { if (items[currentFocus]) items[currentFocus].click(); } 
                else if (items.length > 0) items[0].click();
            }
        });

        function addActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);
            items[currentFocus].classList.add("bg-blue-100", "border-l-4", "border-[#112D4E]");
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }

        function removeActive(items) {
            for (let i = 0; i < items.length; i++) items[i].classList.remove("bg-blue-100", "border-l-4", "border-[#112D4E]");
        }

        document.addEventListener('click', function(e) {
            if (!document.getElementById('jabatan-search-container').contains(e.target)) dropdownListJabatan.classList.add('hidden');
        });

        clearBtn.addEventListener('click', function() {
            searchInputJabatan.value = '';
            hiddenInputJabatan.value = '';
            document.getElementById('filter_kategori_fungsional').value = '';
            this.classList.add('hidden');
            dropdownListJabatan.classList.add('hidden');
            
            document.getElementById('panel_setup_baseline').classList.add('hidden');
            document.getElementById('matriksSearchContainer').classList.add('hidden'); // Sembunyikan Input Search kembali
            document.getElementById('matriksTableBody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>';
            
            document.getElementById('th_jenjang_1').innerText = "Jenjang 1";
            document.getElementById('th_jenjang_2').innerText = "Jenjang 2";
            document.getElementById('th_jenjang_3').innerText = "Jenjang 3";
            document.getElementById('th_jenjang_4').innerText = "Jenjang 4";
        });

        function openEditModalGlobal(group) {
            const form = document.getElementById('formEditJabatanGlobal');
            form.action = "{{ route('admin.jabatan.update_global') }}";
            
            document.getElementById('edit_global_kode_dasar').value = group.kode;
            document.getElementById('edit_global_nama').value = group.nama_jabatan;
            
            const category = (parseInt(group.jenjangs[0].kode_ujung) <= 4) ? 'keterampilan' : 'keahlian';
            updateJenjangLabelsEdit(category);
            
            document.getElementById('edit_b_pertama').value = group.b_pertama;
            document.getElementById('edit_b_muda').value    = group.b_muda;
            document.getElementById('edit_b_madya').value   = group.b_madya;
            document.getElementById('edit_b_utama').value   = group.b_utama;

            toggleModal('modalEditJabatanGlobal');
        }

        function updateJenjangLabelsEdit(kategori) {
            const lbl1 = document.getElementById('lbl_edit_j1');
            const lbl2 = document.getElementById('lbl_edit_j2');
            const lbl3 = document.getElementById('lbl_edit_j3');
            const lbl4 = document.getElementById('lbl_edit_j4');

            if (kategori === 'keterampilan') {
                lbl1.innerText = "Pemula"; lbl2.innerText = "Terampil"; lbl3.innerText = "Mahir"; lbl4.innerText = "Penyelia";
            } else {
                lbl1.innerText = "Ahli Pertama"; lbl2.innerText = "Ahli Muda"; lbl3.innerText = "Ahli Madya"; lbl4.innerText = "Ahli Utama";
            }
        }
    </script>
@endpush