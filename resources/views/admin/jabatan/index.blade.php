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
            <button onclick="toggleModal('modalTambahJabatan')"
                class="bg-[#112D4E] hover:bg-blue-900 text-white px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm">
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
                            <th class="px-6 py-4 font-bold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'kode_jabatan', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="flex items-center gap-1 hover:text-navy-custom transition-colors">
                                    Kode
                                    @if (request('sort') == 'kode_jabatan')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-blue-600"></i>
                                    @else
                                        <i class="fas fa-sort text-slate-300"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 font-bold">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'nama_jabatan', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}"
                                    class="flex items-center gap-1 hover:text-navy-custom transition-colors">
                                    Nama Jabatan
                                    @if (request('sort') == 'nama_jabatan')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-blue-600"></i>
                                    @else
                                        <i class="fas fa-sort text-slate-300"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-6 py-4 font-bold text-center">Baseline</th>
                            <th class="px-6 py-4 font-bold text-center">Jenis</th>
                            <th class="px-6 py-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jabatanTable" class="divide-y divide-gray-100">
                        @forelse($jabatans as $item)
                            <tr class="hover:bg-blue-50/30 transition group">
                                <td class="px-6 py-4 text-sm text-slate-600 font-medium">{{ $item->kode_jabatan }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3 text-blue-600">
                                            <i class="fas fa-briefcase text-xs"></i>
                                        </div>
                                        <span class="text-sm text-slate-700 font-semibold">{{ $item->nama_jabatan }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">
                                        {{ $item->baseline ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($item->fungsional)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm text-slate-700 font-medium">
                                                {{ $item->fungsional->name }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-bold">
                                                ID: {{ $item->fungsional->kode }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tanpa Jenjang</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    {{-- NOTE: Tambahan parameter baseline di sini --}}
                                    <button type="button"
                                        onclick="openEditModal('{{ $item->id }}', '{{ $item->kode_jabatan }}', '{{ $item->nama_jabatan }}', '{{ $item->jenis_jabatan_id }}', '{{ $item->jabatan_fungsional_id }}', '{{ $item->baseline }}')"
                                        class="text-slate-400 hover:text-blue-600 transition">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <button type="button"
                                        onclick="confirmDelete('{{ $item->id }}', '{{ $item->nama_jabatan }}')"
                                        class="text-slate-400 hover:text-red-600 transition">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                    <form id="delete-form-{{ $item->id }}"
                                        action="{{ route('admin.jabatan.destroy', $item->id) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr id="noDataRow">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada data jabatan.</td>
                            </tr>
                        @endforelse
                        <tr id="notFoundRow" class="hidden">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Data tidak ditemukan.</td>
                        </tr>
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
        {{-- Filter Section --}}
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full relative" id="jabatan-search-container">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Cari & Pilih Jabatan Fungsional</label>
                    
                    {{-- Input Teks untuk Pencarian --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                        <input type="text" id="search_jabatan_input" placeholder="Ketik nama jabatan atau kode..." autocomplete="off"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 transition-all font-medium text-slate-700">
                        
                        {{-- Tombol clear (silang) --}}
                        <button type="button" id="clear_search_btn" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden text-slate-400 hover:text-red-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    {{-- Input Hidden (Menyimpan ID untuk dikirim ke Controller via AJAX) --}}
                    <input type="hidden" id="filter_fungsional_distribusi">

                    {{-- List Dropdown (Akan diisi oleh Javascript) --}}
                    <ul id="jabatan_dropdown_list" class="absolute z-50 w-full bg-white border border-gray-100 rounded-xl shadow-xl mt-2 hidden max-h-64 overflow-y-auto divide-y divide-gray-50">
                        {{-- Opsi dirender via JS --}}
                    </ul>
                </div>
                <button type="button" onclick="loadMatriks()" class="bg-[#112D4E] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition flex items-center">
                    <i class="fas fa-search mr-2"></i> Tampilkan Matriks
                </button>
            </div>
        </div>

        {{-- Tabel Matriks Breakdown --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-blue-900 text-sm">Matriks Alokasi Kuota Satker</h3>
            </div>
            
            {{-- PANEL SETUP BASELINE JENJANG (Akan diisi oleh JS) --}}
            <div id="panel_setup_baseline" class="hidden bg-amber-50 border-b-2 border-amber-200 p-4">
                </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 border-b border-r border-gray-200 text-xs font-bold text-slate-700 uppercase w-1/3">Struktur Satuan Kerja</th>
                            <th colspan="4" class="px-6 py-2 border-b border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-slate-100/50">Setup Kuota per Jenjang</th>
                            <th rowspan="2" class="px-4 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-emerald-50">Jumlah</th>
                            <th rowspan="2" class="px-6 py-4 border-b border-l border-gray-200 text-xs font-bold text-center text-slate-700 uppercase w-32">Aksi</th>
                        </tr>
                        <tr>
                            <th class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Pertama / Pemula</th>
                            <th class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Muda / Terampil</th>
                            <th class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Madya / Mahir</th>
                            <th class="px-4 py-3 border-b border-gray-200 text-[10px] font-bold text-center text-slate-600 uppercase">Utama / Penyelia</th>
                        </tr>
                    </thead>
                    <tbody id="matriksTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                <i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>
                                Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    {{-- MODAL TAMBAH --}}
    <div id="modalTambahJabatan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalTambahJabatan')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl sm:max-w-lg w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Jabatan</h3>
                    <button onclick="toggleModal('modalTambahJabatan')" class="text-gray-400 hover:text-gray-600"><i
                            class="fas fa-times"></i></button>
                </div>
                <form action="{{ route('admin.jabatan.store') }}" method="POST">
                    @csrf
                    
                    {{-- Hidden Periode ID --}}
                    <input type="hidden" name="periode_id" value="{{ $activePeriodeId }}">

                    <div class="px-6 py-6 space-y-4">
                        {{-- Kode Jabatan (Sekarang bisa diedit) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Urut Jabatan</label>
                            <input type="text" id="tambah_kode_urut" value="{{ $nextBaseCode }}"
                                oninput="updateKodeJabatan()"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 font-mono">
                            <p class="text-[10px] text-slate-500 mt-1 italic">*Anda bisa menyesuaikan angka urutan ini</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kelompok Jabatan</label>
                            <input type="text" name="nama_jabatan" placeholder="Contoh: Kepala Biro" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <input type="hidden" name="jenis_jabatan_id" id="tambah_jenis" value="{{ $idFungsional }}">
                        
                        {{-- Jenjang Fungsional --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenjang Fungsional</label>
                            <select name="jabatan_fungsional_id" id="tambah_fungsional_id" onchange="updateKodeJabatan()"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Pilih Jenjang</option>
                                @foreach ($fungsionals as $f)
                                    <option value="{{ $f->id }}" data-kode="{{ $f->kode }}">
                                        {{ $f->name }} ({{ $f->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BASELINE INPUT --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Baseline</label>
                            <input type="number" name="baseline" id="tambah_baseline" placeholder="0" min="0" required
                                class="w-full px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-emerald-500/20 font-bold text-emerald-800">
                        </div>

                        <div id="container_eselon_tambah" class="hidden">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Eselon (Opsional)</label>
                            <select name="jenis_satker_id"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none">
                                <option value="">Pilih Eselon</option>
                                @foreach ($eselons as $e)
                                    <option value="{{ $e->id }}">{{ $e->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kolom Kode Jabatan Final (Readonly/Pratinjau) --}}
                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Pratinjau Kode Jabatan Final</label>
                            <input type="text" name="kode_jabatan" id="tambah_kode_jabatan"
                                value="{{ $nextBaseCode }}" readonly
                                class="w-full bg-transparent text-lg font-bold text-blue-800 outline-none font-mono">
                            <p class="text-[10px] text-blue-600/70 mt-1">Ini adalah kode yang akan disimpan ke sistem.</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal('modalTambahJabatan')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md">Simpan
                            Jabatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="modalEditJabatan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalEditJabatan')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl sm:max-w-lg w-full overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Edit Jabatan</h3>
                    <button onclick="toggleModal('modalEditJabatan')" class="text-gray-400 hover:text-gray-600"><i
                            class="fas fa-times"></i></button>
                </div>

                <form id="formEditJabatan" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="px-6 py-6 space-y-4">
                        <input type="hidden" name="jenis_jabatan_id" id="edit_jenis_jabatan_id">

                        {{-- Kode Urut Jabatan (Edit) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Urut Jabatan</label>
                            <input type="text" id="edit_kode_urut" oninput="updateKodeJabatanEdit()"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kelompok Jabatan</label>
                            <input type="text" name="nama_jabatan" id="edit_nama" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        {{-- Jenjang Fungsional --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenjang Fungsional</label>
                            <select name="jabatan_fungsional_id" id="edit_jabatan_fungsional_id" required
                                onchange="updateKodeJabatanEdit()"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Pilih Jenjang</option>
                                @foreach ($fungsionals as $f)
                                    <option value="{{ $f->id }}" data-kode="{{ $f->kode }}">
                                        {{ $f->name }} ({{ $f->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- EDIT BASELINE INPUT --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Baseline</label>
                            <input type="number" name="baseline" id="edit_baseline" placeholder="0" min="0" required
                                class="w-full px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-emerald-500/20 font-bold text-emerald-800">
                        </div>

                        {{-- Kode Jabatan Final (Pratinjau) --}}
                        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                            <label class="block text-xs font-bold text-amber-700 uppercase mb-1">Kode Jabatan Final (Tersimpan)</label>
                            <input type="text" name="kode_jabatan" id="edit_kode_final" readonly
                                class="w-full bg-transparent text-lg font-bold text-amber-800 outline-none font-mono">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal('modalEditJabatan')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md">Simpan Perubahan</button>
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
        // ==========================================
        // PERSISTENSI TAB (ANTI RESET SAAT RELOAD)
        // ==========================================
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
            if (modalId === 'modalTambahJabatan' && !modal.classList.contains('hidden')) filterEselon('tambah');
        }

        function filterEselon(type) {
            const jenisSelect = document.getElementById(type === 'tambah' ? 'tambah_jenis' : 'edit_jenis');
            const eselonContainer = document.getElementById(type === 'tambah' ? 'container_eselon_tambah' : 'container_eselon_edit');
            if(!jenisSelect) return;
            const selectedText = jenisSelect.options[jenisSelect.selectedIndex].text.toLowerCase();
            if (selectedText.includes('struktural')) {
                eselonContainer.classList.remove('hidden');
            } else {
                eselonContainer.classList.add('hidden');
                const eselonSelect = eselonContainer.querySelector('select');
                if(eselonSelect) eselonSelect.value = "";
            }
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

        function updateKodeJabatan() {
            const kodeUrut = document.getElementById('tambah_kode_urut').value;
            const selectFungsional = document.getElementById('tambah_fungsional_id');
            const inputFinal = document.getElementById('tambah_kode_jabatan');
            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption.getAttribute('data-kode') || "";
            if (kodeUrut) inputFinal.value = kodeUrut + kodeJenjang;
            else inputFinal.value = "";
        }

        function openTambahJabatan() {
            document.getElementById('tambah_fungsional_id').value = "";
            document.getElementById('tambah_kode_jabatan').value = "{{ $nextBaseCode }}";
            document.getElementById('tambah_baseline').value = '';
            toggleModal('modalTambahJabatan');
        }

        function updateKodeJabatanEdit() {
            const kodeUrut = document.getElementById('edit_kode_urut').value;
            const selectFungsional = document.getElementById('edit_jabatan_fungsional_id');
            const inputFinal = document.getElementById('edit_kode_final');
            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption ? (selectedOption.getAttribute('data-kode') || "") : "";
            if (kodeUrut) inputFinal.value = kodeUrut + kodeJenjang;
            else inputFinal.value = "";
        }

        function openEditModal(id, kodeFull, nama, jenis, fungsional_id, baseline) {
            const form = document.getElementById('formEditJabatan');
            const baseUrl = "{{ route('admin.jabatan.index') }}";
            form.action = `${baseUrl}/${id}`;

            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jenis_jabatan_id').value = jenis;
            document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";
            document.getElementById('edit_baseline').value = (baseline == 0) ? '' : baseline;

            const kodeUrut = kodeFull.substring(0, 3);
            document.getElementById('edit_kode_urut').value = kodeUrut;
            document.getElementById('edit_kode_final').value = kodeFull;

            toggleModal('modalEditJabatan');
        }

        // ==========================================
        // AJAX LOAD MATRIKS & VALIDASI HIERARKI 
        // ==========================================
        async function loadMatriks() {
            const fungsionalId = document.getElementById('filter_fungsional_distribusi').value;
            if (!fungsionalId) {
                Swal.fire({icon: 'warning', title: 'Pilih Jabatan', text: 'Silakan pilih Jabatan Fungsional terlebih dahulu!', confirmButtonColor: '#112D4E'});
                return;
            }

            Swal.fire({ title: 'Memuat Matriks...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            try {
                const response = await fetch(`/admin/jabatan/matriks?jabatan_id=${fungsionalId}`);
                const dataJSON = await response.json();
                const data = dataJSON.satkers;
                
                // 1. Render Panel Setup Baseline Jenjang
                const bp = dataJSON.b_pertama == 0 ? '' : dataJSON.b_pertama;
                const bmu = dataJSON.b_muda == 0 ? '' : dataJSON.b_muda;
                const bma = dataJSON.b_madya == 0 ? '' : dataJSON.b_madya;
                const bu = dataJSON.b_utama == 0 ? '' : dataJSON.b_utama;

                const panelBaseline = document.getElementById('panel_setup_baseline');
                panelBaseline.classList.remove('hidden');
                panelBaseline.innerHTML = `
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <p class="text-xs text-amber-700 font-bold uppercase mb-1">Total Grand Baseline</p>
                            <span id="grand_baseline_display" class="text-2xl font-black text-amber-900 bg-amber-200/50 px-4 py-1 rounded-lg">${dataJSON.baseline}</span>
                        </div>
                        <div class="flex-1 flex gap-2 items-end justify-end">
                            <div class="w-20"><label class="text-[10px] font-bold text-amber-800">PERTAMA</label><input type="number" id="base_p" value="${bp}" placeholder="0" class="w-full px-2 py-1 text-sm border-amber-300 rounded focus:ring-amber-500 text-center"></div>
                            <div class="w-20"><label class="text-[10px] font-bold text-amber-800">MUDA</label><input type="number" id="base_mu" value="${bmu}" placeholder="0" class="w-full px-2 py-1 text-sm border-amber-300 rounded focus:ring-amber-500 text-center"></div>
                            <div class="w-20"><label class="text-[10px] font-bold text-amber-800">MADYA</label><input type="number" id="base_ma" value="${bma}" placeholder="0" class="w-full px-2 py-1 text-sm border-amber-300 rounded focus:ring-amber-500 text-center"></div>
                            <div class="w-20"><label class="text-[10px] font-bold text-amber-800">UTAMA</label><input type="number" id="base_u" value="${bu}" placeholder="0" class="w-full px-2 py-1 text-sm border-amber-300 rounded focus:ring-amber-500 text-center"></div>
                            <button onclick="simpanBaselineJenjang('${fungsionalId}')" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm h-[30px] ml-2 transition-colors"><i class="fas fa-save mr-1"></i> Set Baseline</button>
                        </div>
                    </div>
                `;

                // 2. Helper untuk membuat HTML Input yang interaktif
                function createInputHTML(val, id, type, parentId) {
                    const v = (val == 0 || val == null) ? '' : val;
                    return `
                    <div class="relative w-full pt-1 pb-3">
                        <div id="tooltip_${type}_${id}" class="absolute bottom-[85%] left-1/2 transform -translate-x-1/2 mb-1 bg-slate-800 text-white text-[10px] px-2 py-1 rounded shadow-xl hidden z-20 whitespace-nowrap transition-opacity duration-200 opacity-0 pointer-events-none">
                            Sisa: <span id="sisa_val_${type}_${id}" class="font-bold text-amber-300"></span>
                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-slate-800"></div>
                        </div>
                        <input type="number" min="0"
                            id="${type}_${id}"
                            data-type="${type}"
                            data-parent="${parentId || 'root'}"
                            value="${v}"
                            placeholder="0"
                            onfocus="handleFocus(this, '${id}', '${type}', '${parentId || 'root'}')"
                            onblur="handleBlur(this, '${id}', '${type}')"
                            oninput="handleInput(this, '${id}', '${type}', '${parentId || 'root'}')"
                            class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none transition-all bg-white">
                        <p id="err_${type}_${id}" class="absolute bottom-0 left-0 right-0 text-center text-[9px] text-red-500 font-bold hidden leading-none tracking-tight">Lebih <span id="err_val_${type}_${id}"></span></p>
                    </div>
                    `;
                }

                // 3. Render Tabel Matriks
                let tbodyHtml = '';
                if(data.length === 0) {
                    tbodyHtml += `<tr><td colspan="7" class="text-center py-8 text-slate-500">Data Satker Kosong di Periode ini</td></tr>`;
                } else {
                    let groupIndex = -1;

                    data.forEach(item => {
                        const isParent = item.level === 0;
                        
                        if (isParent) {
                            groupIndex++;
                        }

                        const indentClass = isParent ? '' : 'pl-10';
                        const iconHtml = isParent 
                            ? '<i class="fas fa-building mr-3 text-slate-400"></i>' 
                            : '<i class="fas fa-level-up-alt rotate-90 text-slate-300 mr-3"></i>';
                        
                        // --- LOGIKA ZEBRA FLAT (PUTIH DAN ABU-ABU) ---
                        const isEvenGroup = groupIndex % 2 === 0;
                        
                        // Induk dan Anak dalam 1 kelompok warnanya SAMA PERSIS
                        const bgClass = isEvenGroup ? 'bg-white' : 'bg-slate-50';
                        
                        // Teks Induk tetap ditebalkan agar mudah dibedakan
                        const textClass = isParent ? 'font-bold text-slate-800' : 'font-medium text-slate-600';

                        const totalRow = parseInt(item.kuota_pertama) + parseInt(item.kuota_muda) + parseInt(item.kuota_madya) + parseInt(item.kuota_utama);
                        
                        const kp = item.kuota_pertama == 0 ? '' : item.kuota_pertama;
                        const kmu = item.kuota_muda == 0 ? '' : item.kuota_muda;
                        const kma = item.kuota_madya == 0 ? '' : item.kuota_madya;
                        const ku = item.kuota_utama == 0 ? '' : item.kuota_utama;

                        tbodyHtml += `
                            <tr class="transition border-b border-gray-100 ${bgClass}" id="row-${item.id}" data-parent="${item.parent_id || 'root'}">
                                <td class="px-6 py-4 ${indentClass} align-middle">
                                    <div class="flex items-center ${textClass}">
                                        ${iconHtml}
                                        <span class="text-sm tracking-tight">${item.nama_satker}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-1 align-top">${createInputHTML(kp, item.id, 'kp', item.parent_id)}</td>
                                <td class="px-2 py-1 align-top">${createInputHTML(kmu, item.id, 'kmu', item.parent_id)}</td>
                                <td class="px-2 py-1 align-top">${createInputHTML(kma, item.id, 'kma', item.parent_id)}</td>
                                <td class="px-2 py-1 align-top">${createInputHTML(ku, item.id, 'ku', item.parent_id)}</td>
                                <td class="px-4 py-3 align-middle text-center font-bold text-emerald-600" id="total_${item.id}">${totalRow}</td>
                                <td class="px-4 py-3 align-middle text-center">
                                    ${isParent 
                                        ? `<button onclick="simpanKuotaGroup('${item.id}')" class="text-[11px] bg-[#112D4E] hover:bg-blue-900 text-white px-3 py-2 rounded-md transition shadow-sm w-full font-bold uppercase tracking-wider"><i class="fas fa-save mr-1"></i> Simpan</button>` 
                                        : `<span class="text-[10px] text-slate-400 italic block w-full">Via Induk</span>`
                                    }
                                </td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('matriksTableBody').innerHTML = tbodyHtml;
                
                setTimeout(() => validateAllHierarchies(), 100);
                Swal.close();
            } catch (error) {
                Swal.fire({icon: 'error', title: 'Error', text: 'Gagal memuat data dari server.'});
            }
        }

        // ==========================================
        // LOGIKA INTERAKTIF (TOOLTIP, SISA, & MERAH)
        // ==========================================
        function getBaseId(type) {
            return { 'kp': 'base_p', 'kmu': 'base_mu', 'kma': 'base_ma', 'ku': 'base_u' }[type];
        }

        function getLimitAndSisa(input, type, parentId) {
            let limit = 0; let otherSum = 0;
            const val = parseInt(input.value) || 0;

            if (parentId === 'root') {
                // Induk memotong dari Baseline MenpanRB
                limit = parseInt(document.getElementById(getBaseId(type)).value) || 0;
                document.querySelectorAll(`input[data-type="${type}"][data-parent="root"]`).forEach(el => {
                    if (el.id !== input.id) otherSum += parseInt(el.value) || 0;
                });
            } else {
                // Anak memotong dari Induknya
                limit = parseInt(document.getElementById(`${type}_${parentId}`).value) || 0;
                document.querySelectorAll(`input[data-type="${type}"][data-parent="${parentId}"]`).forEach(el => {
                    if (el.id !== input.id) otherSum += parseInt(el.value) || 0;
                });
            }

            const sisa = limit - otherSum;
            return { limit, otherSum, sisa, isExceeding: val > sisa, excessAmount: val - sisa };
        }

        function handleFocus(input, id, type, parentId) {
            // Hilangkan warna merah saat diklik
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:ring-red-500');
            input.classList.add('focus:ring-blue-500', 'border-gray-300');
            document.getElementById(`err_${type}_${id}`).classList.add('hidden');

            // Hitung dan tampilkan Sisa Tooltip
            const { sisa } = getLimitAndSisa(input, type, parentId);
            document.getElementById(`sisa_val_${type}_${id}`).innerText = sisa;
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            tooltip.classList.remove('hidden');
            setTimeout(() => tooltip.classList.remove('opacity-0'), 10);
        }

        function handleBlur(input, id, type) {
            // Sembunyikan Tooltip
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            tooltip.classList.add('opacity-0');
            setTimeout(() => tooltip.classList.add('hidden'), 200);

            // Validasi ulang saat input dilepas
            validateInput(input, id, type, input.getAttribute('data-parent'));
        }

        function handleInput(input, id, type, parentId) {
            // Kalkulasi Total Kanan (Baris)
            const kp = parseInt(document.getElementById(`kp_${id}`).value) || 0;
            const kmu = parseInt(document.getElementById(`kmu_${id}`).value) || 0;
            const kma = parseInt(document.getElementById(`kma_${id}`).value) || 0;
            const ku = parseInt(document.getElementById(`ku_${id}`).value) || 0;
            document.getElementById(`total_${id}`).innerText = kp + kmu + kma + ku;

            // Jika yang diedit adalah Induk, pastikan validasi anak-anaknya juga terupdate (siapa tau induknya dikecilkan angkanya)
            if (parentId === 'root') {
                document.querySelectorAll(`input[data-type="${type}"][data-parent="${id}"]`).forEach(childInput => {
                    validateInput(childInput, childInput.id.split('_')[1], type, id);
                });
            }
        }

        function validateInput(input, id, type, parentId) {
            const { isExceeding, excessAmount } = getLimitAndSisa(input, type, parentId);
            const errEl = document.getElementById(`err_${type}_${id}`);
            
            // Jika melebihi & tidak sedang di-fokus, jadikan merah
            if (isExceeding && document.activeElement !== input) {
                input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                input.classList.remove('border-gray-300');
                document.getElementById(`err_val_${type}_${id}`).innerText = excessAmount;
                errEl.classList.remove('hidden');
            } else if (!isExceeding) {
                input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                input.classList.add('border-gray-300');
                errEl.classList.add('hidden');
            }
        }

        function validateAllHierarchies() {
            document.querySelectorAll('#matriksTableBody input[data-type]').forEach(input => {
                validateInput(input, input.id.split('_')[1], input.dataset.type, input.dataset.parent);
            });
        }

        // ==========================================
        // FUNGSI SIMPAN (BULK SAVE VIA INDUK)
        // ==========================================
        async function simpanBaselineJenjang(jabatanId) {
            const p = parseInt(document.getElementById('base_p').value) || 0;
            const mu = parseInt(document.getElementById('base_mu').value) || 0;
            const ma = parseInt(document.getElementById('base_ma').value) || 0;
            const u = parseInt(document.getElementById('base_u').value) || 0;

            const grandBaseline = parseInt(document.getElementById('grand_baseline_display').innerText) || 0;
            const totalInput = p + mu + ma + u;

            if (totalInput > grandBaseline) {
                Swal.fire({ icon: 'warning', title: 'Melebihi Limit!', text: `Total rincian (${totalInput}) melebihi Grand Baseline (${grandBaseline})!` });
                return; 
            }

            try {
                const response = await fetch('/admin/jabatan/matriks/save-baseline', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: JSON.stringify({ jabatan_id: jabatanId, b_pertama: p, b_muda: mu, b_madya: ma, b_utama: u })
                });

                const result = await response.json();
                if(result.status === 'success') {
                    Toast.fire({icon: 'success', title: 'Baseline Jenjang Disimpan!'});
                    validateAllHierarchies(); // Update warna baris di bawahnya
                } else {
                    Swal.fire({icon: 'error', title: 'Gagal', text: result.message});
                }
            } catch (error) { Toast.fire({icon: 'error', title: 'Gagal menghubungi server'}); }
        }

        async function simpanKuotaGroup(parentId) {
            const jfId = document.getElementById('filter_fungsional_distribusi').value;
            
            // 1. Kumpulkan ID Satker Induk dan seluruh Anak-anaknya
            const groupIds = [parentId];
            document.querySelectorAll(`input[data-parent="${parentId}"]`).forEach(el => {
                const childId = el.id.split('_')[1];
                if (!groupIds.includes(childId)) groupIds.push(childId);
            });

            // 2. Paksa validasi sebelum menyimpan, jika ada yg merah, batalkan!
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

            // 3. Simpan ke Database secara paralel
            const btn = document.querySelector(`#row-${parentId} button`);
            const originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;
            btn.disabled = true;

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const requests = groupIds.map(id => {
                    return fetch('/admin/jabatan/matriks/save', {
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
                });

                await Promise.all(requests); // Tunggu semua satker di group ini tersimpan
                
                Toast.fire({icon: 'success', title: 'Distribusi Tersimpan!'});
                
                // Efek kedip sukses pada semua baris di group ini
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
                nama: j.nama_jabatan,
                kode: j.kode_jabatan,
                jenjang: j.fungsional ? j.fungsional.name : null
            };
        });

        const searchInputJabatan = document.getElementById('search_jabatan_input');
        const hiddenInputJabatan = document.getElementById('filter_fungsional_distribusi');
        const dropdownListJabatan = document.getElementById('jabatan_dropdown_list');
        const clearBtn = document.getElementById('clear_search_btn');
        let currentFocus = -1;

        function renderDropdownList(data) {
            dropdownListJabatan.innerHTML = '';
            if (data.length === 0) {
                dropdownListJabatan.innerHTML = '<li class="px-4 py-3 text-sm text-slate-500 text-center italic">Tidak ada jabatan yang cocok</li>';
            } else {
                data.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className = "px-4 py-2.5 cursor-pointer hover:bg-blue-50 transition-colors flex items-center justify-between group";
                    li.dataset.id = item.id;
                    li.dataset.index = index;
                    
                    let jenjangHtml = '';
                    if (item.jenjang) {
                        jenjangHtml = `<span class="px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-md whitespace-nowrap ml-2 shadow-sm border border-amber-200 uppercase tracking-wider">${item.jenjang}</span>`;
                    }

                    li.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-slate-800 group-hover:text-[#112D4E]">${item.nama}</span>
                            <span class="text-xs text-slate-500 font-mono mt-0.5">Kode: ${item.kode}</span>
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
            if (item.jenjang) {
                searchInputJabatan.value = `${item.nama} — [ ${item.jenjang.toUpperCase()} ] (${item.kode})`;
            } else {
                searchInputJabatan.value = `${item.nama} (${item.kode})`;
            }
            hiddenInputJabatan.value = item.id;
            dropdownListJabatan.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        }

        searchInputJabatan.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            clearBtn.classList.toggle('hidden', val === '');
            hiddenInputJabatan.value = '';
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
            this.classList.add('hidden');
            dropdownListJabatan.classList.add('hidden');
            document.getElementById('matriksTableBody').innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>';
        });
    </script>
@endpush