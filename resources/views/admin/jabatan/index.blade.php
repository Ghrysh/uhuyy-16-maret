@extends('layouts.admin')

@section('title', 'Master Jabatan & Distribusi Kuota')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Jabatan Fungsional</h2>
            <p class="text-sm text-slate-500">Kelola master data jabatan dan distribusi kuota formasi</p>
        </div>
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
                                    <button type="button"
                                        onclick="openEditModal('{{ $item->id }}', '{{ $item->kode_jabatan }}', '{{ $item->nama_jabatan }}', '{{ $item->jenis_jabatan_id }}', '{{ $item->jabatan_fungsional_id }}')"
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
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-blue-900 text-sm">Matriks Alokasi Kuota Satker</h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">Total Baseline: 100</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-6 py-4 border-b border-r border-gray-200 text-xs font-bold text-slate-700 uppercase w-1/3">Struktur Satuan Kerja</th>
                            <th colspan="4" class="px-6 py-2 border-b border-gray-200 text-xs font-bold text-center text-slate-700 uppercase bg-slate-100/50">Setup Kuota per Jenjang</th>
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
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
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
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Pratinjau Kode Jabatan
                                Final</label>
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

                        {{-- Kolom Jenjang --}}
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

                        {{-- Kode Jabatan Final (Pratinjau) --}}
                        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                            <label class="block text-xs font-bold text-amber-700 uppercase mb-1">Kode Jabatan Final
                                (Tersimpan)</label>
                            <input type="text" name="kode_jabatan" id="edit_kode_final" readonly
                                class="w-full bg-transparent text-lg font-bold text-amber-800 outline-none font-mono">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal('modalEditJabatan')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md">Simpan
                            Perubahan</button>
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
                    Apakah Anda yakin ingin menghapus jabatan <span id="delete_nama_display"
                        class="font-bold text-slate-700"></span>? Tindakan ini tidak dapat dibatalkan.
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
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script>
        // === FUNGSI TAB SWITCHER ===
        function switchTab(tabName) {
            // Sembunyikan semua konten
            document.getElementById('content-master').classList.add('hidden');
            document.getElementById('content-distribusi').classList.add('hidden');
            
            // Reset styling semua tombol tab
            const tabs = ['master', 'distribusi'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t);
                btn.classList.remove('border-[#112D4E]', 'text-[#112D4E]', 'font-bold');
                btn.classList.add('border-transparent', 'text-gray-500', 'font-medium');
            });

            // Tampilkan konten yang dipilih
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Aktifkan styling tombol yang dipilih
            const activeBtn = document.getElementById('tab-' + tabName);
            activeBtn.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
            activeBtn.classList.add('border-[#112D4E]', 'text-[#112D4E]', 'font-bold');
        }

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif
        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
            
            // Reset filter saat modal tambah dibuka
            if (modalId === 'modalTambahJabatan' && !modal.classList.contains('hidden')) {
                filterEselon('tambah');
            }
        }

        // Fungsi untuk menangani visibilitas Eselon
        function filterEselon(type) {
            const jenisSelect = document.getElementById(type === 'tambah' ? 'tambah_jenis' : 'edit_jenis');
            const eselonContainer = document.getElementById(type === 'tambah' ? 'container_eselon_tambah' :
                'container_eselon_edit');

            if(!jenisSelect) return; // safety check

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
            btnConfirm.onclick = function() {
                document.getElementById('delete-form-' + id).submit();
            };
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

                    if (txtKode.toLowerCase().indexOf(filter) > -1 ||
                        txtNama.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        hasVisibleRow = true;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

            if (filter !== "" && !hasVisibleRow) {
                notFoundRow.classList.remove('hidden');
            } else {
                notFoundRow.classList.add('hidden');
            }
        }

        function updateKodeJabatan() {
            const kodeUrut = document.getElementById('tambah_kode_urut').value;
            const selectFungsional = document.getElementById('tambah_fungsional_id');
            const inputFinal = document.getElementById('tambah_kode_jabatan');

            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption.getAttribute('data-kode') || "";

            if (kodeUrut) {
                inputFinal.value = kodeUrut + kodeJenjang;
            } else {
                inputFinal.value = "";
            }
        }

        function openTambahJabatan() {
            document.getElementById('tambah_fungsional_id').value = "";
            document.getElementById('tambah_kode_jabatan').value = "{{ $nextBaseCode }}";
            toggleModal('modalTambahJabatan');
        }

        function updateKodeJabatanEdit() {
            const kodeUrut = document.getElementById('edit_kode_urut').value;
            const selectFungsional = document.getElementById('edit_jabatan_fungsional_id');
            const inputFinal = document.getElementById('edit_kode_final');

            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption ? (selectedOption.getAttribute('data-kode') || "") : "";

            if (kodeUrut) {
                inputFinal.value = kodeUrut + kodeJenjang;
            } else {
                inputFinal.value = "";
            }
        }

        function openEditModal(id, kodeFull, nama, jenis, fungsional_id) {
            const form = document.getElementById('formEditJabatan');
            const baseUrl = "{{ route('admin.jabatan.index') }}";
            form.action = `${baseUrl}/${id}`;

            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jenis_jabatan_id').value = jenis;
            document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";

            const kodeUrut = kodeFull.substring(0, 3);
            document.getElementById('edit_kode_urut').value = kodeUrut;
            document.getElementById('edit_kode_final').value = kodeFull;

            toggleModal('modalEditJabatan');
        }

        // --- AJAX FETCH & SAVE MATRIKS --- //
        async function loadMatriks() {
            const fungsionalId = document.getElementById('filter_fungsional_distribusi').value;
            
            if (!fungsionalId) {
                Swal.fire({icon: 'warning', title: 'Pilih Jabatan', text: 'Silakan pilih Jabatan Fungsional terlebih dahulu!', confirmButtonColor: '#112D4E'});
                return;
            }

            Swal.fire({
                title: 'Memuat Matriks...',
                text: 'Mengambil data hierarki Satker...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await fetch(`/admin/jabatan/matriks?jabatan_id=${fungsionalId}`);
                const data = await response.json();
                
                let tbodyHtml = '';
                if(data.length === 0) {
                    tbodyHtml = `<tr><td colspan="6" class="text-center py-8 text-slate-500">Data Satker Kosong</td></tr>`;
                } else {
                    data.forEach(item => {
                        // Konfigurasi indentasi UI
                        const indentClass = item.level > 0 ? 'pl-12' : '';
                        const iconHtml = item.level > 0 
                            ? '<i class="fas fa-level-up-alt rotate-90 text-slate-400 mr-3"></i>' 
                            : '<i class="fas fa-building text-blue-600 mr-3"></i>';
                        const bgClass = item.level === 0 ? 'bg-slate-50/50' : '';
                        
                        tbodyHtml += `
                            <tr class="hover:bg-blue-50/30 transition ${bgClass}" id="row-${item.id}">
                                <td class="px-6 py-4 ${indentClass}">
                                    <div class="flex items-center">
                                        ${iconHtml}
                                        <span class="text-sm ${item.level === 0 ? 'font-bold' : 'font-medium'} text-slate-800">${item.nama_satker}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3"><input type="number" id="kp_${item.id}" value="${item.kuota_pertama}" class="w-full px-2 py-1 border border-gray-300 rounded text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none"></td>
                                <td class="px-4 py-3"><input type="number" id="kmu_${item.id}" value="${item.kuota_muda}" class="w-full px-2 py-1 border border-gray-300 rounded text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none"></td>
                                <td class="px-4 py-3"><input type="number" id="kma_${item.id}" value="${item.kuota_madya}" class="w-full px-2 py-1 border border-gray-300 rounded text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none"></td>
                                <td class="px-4 py-3"><input type="number" id="ku_${item.id}" value="${item.kuota_utama}" class="w-full px-2 py-1 border border-gray-300 rounded text-center text-sm font-semibold focus:ring-2 focus:ring-blue-500 outline-none"></td>
                                <td class="px-4 py-3 text-center border-l border-gray-100">
                                    <button onclick="simpanKuota('${item.id}')" class="text-xs bg-[#112D4E] hover:bg-blue-900 text-white px-3 py-1.5 rounded transition shadow-sm w-full"><i class="fas fa-save mr-1"></i> Simpan</button>
                                </td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('matriksTableBody').innerHTML = tbodyHtml;
                Swal.close();
                
            } catch (error) {
                Swal.fire({icon: 'error', title: 'Error', text: 'Gagal memuat data dari server.'});
            }
        }

        async function simpanKuota(satkerId) {
            const jfId = document.getElementById('filter_fungsional_distribusi').value;
            const kp = document.getElementById(`kp_${satkerId}`).value;
            const kmu = document.getElementById(`kmu_${satkerId}`).value;
            const kma = document.getElementById(`kma_${satkerId}`).value;
            const ku = document.getElementById(`ku_${satkerId}`).value;

            // Menampilkan efek loading pada tombol (opsional)
            const btnRow = document.querySelector(`#row-${satkerId} button`);
            const originalText = btnRow.innerHTML;
            btnRow.innerHTML = `<i class="fas fa-spinner fa-spin"></i>`;
            btnRow.disabled = true;

            try {
                const response = await fetch('/admin/jabatan/matriks/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // Mengambil token CSRF Laravel (Pastikan di <head> layout Anda ada tag meta csrf)
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        satker_id: satkerId,
                        jabatan_id: jfId,
                        kuota_pertama: kp,
                        kuota_muda: kmu,
                        kuota_madya: kma,
                        kuota_utama: ku
                    })
                });

                const result = await response.json();
                if(result.status === 'success') {
                    Toast.fire({icon: 'success', title: 'Tersimpan!'});
                    // Efek kedip hijau pada baris agar user tau berhasil
                    const row = document.getElementById(`row-${satkerId}`);
                    row.classList.add('bg-emerald-50');
                    setTimeout(() => row.classList.remove('bg-emerald-50'), 1500);
                } else {
                    Toast.fire({icon: 'error', title: 'Terjadi kesalahan'});
                }
            } catch (error) {
                Toast.fire({icon: 'error', title: 'Gagal menghubungi server'});
            } finally {
                btnRow.innerHTML = originalText;
                btnRow.disabled = false;
            }
        }

        // ==========================================
        // FITUR AUTOCOMPLETE PENCARIAN JABATAN
        // ==========================================
        
        // 1. Ambil data dari PHP dan ubah ke format JSON Array
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

        // Fungsi merender list HTML
        function renderDropdownList(data) {
            dropdownListJabatan.innerHTML = '';
            if (data.length === 0) {
                dropdownListJabatan.innerHTML = '<li class="px-4 py-3 text-sm text-slate-500 text-center italic">Tidak ada jabatan yang cocok</li>';
            } else {
                data.forEach((item, index) => {
                    const li = document.createElement('li');
                    li.className = "px-4 py-2.5 cursor-pointer hover:bg-blue-50 transition-colors flex items-center justify-between group";
                    li.dataset.id = item.id;
                    li.dataset.index = index; // Untuk navigasi keyboard
                    
                    // Render HTML untuk Label Jenjang Berwarna
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

                    // Event klik pada item list
                    li.addEventListener('click', function() {
                        selectJabatan(item);
                    });

                    dropdownListJabatan.appendChild(li);
                });
            }
            dropdownListJabatan.classList.remove('hidden');
        }

        // Fungsi saat item dipilih
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

        // Event saat mengetik di input
        searchInputJabatan.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            clearBtn.classList.toggle('hidden', val === '');
            hiddenInputJabatan.value = ''; // Reset hidden ID saat ngetik ulang
            currentFocus = -1;

            if (!val) {
                dropdownListJabatan.classList.add('hidden');
                return;
            }

            // Filter data array
            const filteredData = daftarJabatan.filter(item => 
                item.nama.toLowerCase().includes(val) || 
                item.kode.toLowerCase().includes(val)
            );

            renderDropdownList(filteredData);
        });

        // Tampilkan semua saat input di-klik (fokus)
        searchInputJabatan.addEventListener('focus', function() {
            if(this.value === '') {
                renderDropdownList(daftarJabatan); // Munculkan semua data
            } else {
                // Trigger input event manual jika sudah ada isinya
                const event = new Event('input');
                this.dispatchEvent(event);
            }
        });

        // Event Navigasi Keyboard (Arrow Up, Arrow Down, Enter)
        searchInputJabatan.addEventListener('keydown', function(e) {
            const items = dropdownListJabatan.getElementsByTagName('li');
            if (dropdownListJabatan.classList.contains('hidden') || items.length === 0) return;

            if (e.key === "ArrowDown") {
                currentFocus++;
                addActive(items);
            } else if (e.key === "ArrowUp") {
                currentFocus--;
                addActive(items);
            } else if (e.key === "Enter") {
                e.preventDefault();
                if (currentFocus > -1) {
                    if (items[currentFocus]) items[currentFocus].click();
                } else if (items.length > 0) {
                    // Jika belum fokus panah bawah tapi langsung enter, pilih yang paling atas
                    items[0].click();
                }
            }
        });

        // Tambah class active (sorotan keyboard)
        function addActive(items) {
            if (!items) return false;
            removeActive(items);
            if (currentFocus >= items.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (items.length - 1);
            items[currentFocus].classList.add("bg-blue-100", "border-l-4", "border-[#112D4E]");
            
            // Auto scroll jika menggunakan keyboard
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }

        function removeActive(items) {
            for (let i = 0; i < items.length; i++) {
                items[i].classList.remove("bg-blue-100", "border-l-4", "border-[#112D4E]");
            }
        }

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', function(e) {
            if (!document.getElementById('jabatan-search-container').contains(e.target)) {
                dropdownListJabatan.classList.add('hidden');
            }
        });

        // Tombol silang untuk hapus pencarian
        clearBtn.addEventListener('click', function() {
            searchInputJabatan.value = '';
            hiddenInputJabatan.value = '';
            this.classList.add('hidden');
            dropdownListJabatan.classList.add('hidden');
            document.getElementById('matriksTableBody').innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>';
        });
    </script>
@endpush