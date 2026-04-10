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
                                    <div class="flex gap-1 justify-center text-[10px] font-mono font-bold">
                                        <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200" title="Jenjang 1">{{ $item->b_pertama ?? 0 }}</span>
                                        <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200" title="Jenjang 2">{{ $item->b_muda ?? 0 }}</span>
                                        <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200" title="Jenjang 3">{{ $item->b_madya ?? 0 }}</span>
                                        <span class="bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded border border-amber-200" title="Jenjang 4">{{ $item->b_utama ?? 0 }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($item->fungsional)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-sm text-slate-700 font-medium">{{ $item->fungsional->name }}</span>
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-bold">ID: {{ $item->fungsional->kode }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tanpa Jenjang</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-3">
                                    @php 
                                        $canEdit = $perm['is_super'] || $perm['all_access'] || in_array('edit', $perm['actions'] ?? []); 
                                        $canDelete = $perm['is_super'] || $perm['all_access'] || in_array('delete', $perm['actions'] ?? []); 
                                    @endphp

                                    <button type="button"
                                        onclick="{{ $canEdit ? "openEditModal('{$item->id}', '{$item->kode_jabatan}', '{$item->nama_jabatan}', '{$item->jenis_jabatan_id}', '{$item->jabatan_fungsional_id}', '{$item->b_pertama}', '{$item->b_muda}', '{$item->b_madya}', '{$item->b_utama}', '{$item->baseline}')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Mengedit Jabatan.', 'error')" }}"
                                        class="{{ $canEdit ? 'text-slate-400 hover:text-blue-600' : 'text-slate-300 opacity-50' }} transition" title="Edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    
                                    <button type="button"
                                        onclick="{{ $canDelete ? "confirmDelete('{$item->id}', '{$item->nama_jabatan}')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menghapus Jabatan.', 'error')" }}"
                                        class="{{ $canDelete ? 'text-slate-400 hover:text-red-600' : 'text-slate-300 opacity-50' }} transition" title="Hapus">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                    
                                    @if($canDelete)
                                    <form id="delete-form-{{ $item->id }}" action="{{ route('admin.jabatan.destroy', $item->id) }}" method="POST" class="hidden">
                                        @csrf @method('DELETE')
                                    </form>
                                    @endif
                                </td>
                            </tr>
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
                    <input type="hidden" id="filter_kode_fungsional">
                    <ul id="jabatan_dropdown_list" class="absolute z-50 w-full bg-white border border-gray-100 rounded-xl shadow-xl mt-2 hidden max-h-64 overflow-y-auto divide-y divide-gray-50"></ul>
                </div>
                <button type="button" onclick="loadMatriks()" class="bg-[#112D4E] text-white px-6 py-2.5 rounded-lg text-sm font-semibold hover:bg-blue-900 transition flex items-center">
                    <i class="fas fa-search mr-2"></i> Tampilkan Matriks
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
            <div class="p-4 bg-blue-50/50 border-b border-blue-100 flex justify-between items-center">
                <h3 class="font-bold text-blue-900 text-sm">Matriks Alokasi Kuota Satker</h3>
            </div>
            
            <div id="panel_setup_baseline" class="hidden bg-amber-50 border-b-2 border-amber-200 p-4"></div>

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
                <form action="{{ route('admin.jabatan.store') }}" method="POST" id="formAddJabatan" onsubmit="hitungTotalBaseline('tambah', event)">
                    @csrf
                    <input type="hidden" name="periode_id" value="{{ $activePeriodeId }}">
                    <input type="hidden" name="baseline" id="tambah_baseline" value="0">

                    <div class="px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Urut Jabatan</label>
                            <input type="text" id="tambah_kode_urut" value="{{ $nextBaseCode }}" oninput="updateKodeJabatan()"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kelompok Jabatan</label>
                            <input type="text" name="nama_jabatan" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                        </div>

                        <input type="hidden" name="jenis_jabatan_id" id="tambah_jenis" value="{{ $idFungsional }}">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenjang Fungsional</label>
                            <select name="jabatan_fungsional_id" id="tambah_fungsional_id" required onchange="updateJenjangLabels('tambah_fungsional_id', 'lbl_tambah_j'); updateKodeJabatan();"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Pilih Jenjang</option>
                                @foreach ($fungsionals as $f)
                                    <option value="{{ $f->id }}" data-kode="{{ $f->kode }}">{{ $f->name }} ({{ $f->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="container_baseline_tambah" class="hidden bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <label class="block text-xs font-bold text-amber-900 uppercase mb-3">Baseline per Jenjang</label>
                            <div class="grid grid-cols-4 gap-2">
                                <div>
                                    <label id="lbl_tambah_j1" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 1</label>
                                    <input type="number" name="b_pertama" id="tambah_b_pertama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j2" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 2</label>
                                    <input type="number" name="b_muda" id="tambah_b_muda" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j3" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 3</label>
                                    <input type="number" name="b_madya" id="tambah_b_madya" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_tambah_j4" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 4</label>
                                    <input type="number" name="b_utama" id="tambah_b_utama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Pratinjau Kode Jabatan Final</label>
                            <input type="text" name="kode_jabatan" id="tambah_kode_jabatan" value="{{ $nextBaseCode }}" readonly class="w-full bg-transparent text-lg font-bold text-blue-800 outline-none font-mono">
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalTambahJabatan')" class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md">Simpan Jabatan</button>
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
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white">
                    <h3 class="text-lg font-bold text-slate-800">Edit Jabatan</h3>
                    <button onclick="toggleModal('modalEditJabatan')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>

                <form id="formEditJabatan" method="POST" onsubmit="hitungTotalBaseline('edit', event)">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="baseline" id="edit_baseline" value="0">

                    <div class="px-6 py-6 space-y-4 max-h-[70vh] overflow-y-auto">
                        <input type="hidden" name="jenis_jabatan_id" id="edit_jenis_jabatan_id">

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

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenjang Fungsional</label>
                            <select name="jabatan_fungsional_id" id="edit_jabatan_fungsional_id" required
                                onchange="updateJenjangLabels('edit_jabatan_fungsional_id', 'lbl_edit_j'); updateKodeJabatanEdit();"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500/20">
                                <option value="">Pilih Jenjang</option>
                                @foreach ($fungsionals as $f)
                                    <option value="{{ $f->id }}" data-kode="{{ $f->kode }}">{{ $f->name }} ({{ $f->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="container_baseline_edit" class="hidden bg-amber-50 p-4 rounded-xl border border-amber-200">
                            <label class="block text-xs font-bold text-amber-900 uppercase mb-3">Baseline per Jenjang</label>
                            <div class="grid grid-cols-4 gap-2">
                                <div>
                                    <label id="lbl_edit_j1" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 1</label>
                                    <input type="number" name="b_pertama" id="edit_b_pertama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_edit_j2" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 2</label>
                                    <input type="number" name="b_muda" id="edit_b_muda" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_edit_j3" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 3</label>
                                    <input type="number" name="b_madya" id="edit_b_madya" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                                <div>
                                    <label id="lbl_edit_j4" class="block text-[10px] font-bold text-amber-800 uppercase text-center mb-1">Jenjang 4</label>
                                    <input type="number" name="b_utama" id="edit_b_utama" min="0" value="0" class="w-full px-2 py-2 bg-white border border-amber-300 rounded-lg text-center font-bold outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                            <label class="block text-xs font-bold text-amber-700 uppercase mb-1">Kode Jabatan Final</label>
                            <input type="text" name="kode_jabatan" id="edit_kode_final" readonly class="w-full bg-transparent text-lg font-bold text-amber-800 outline-none font-mono">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modalEditJabatan')" class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md">Simpan Perubahan</button>
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

        // ==========================================
        // FIX BUG: Hitung Ulang Total Sebelum Submit
        // ==========================================
        function hitungTotalBaseline(type, event) {
            const bp = parseInt(document.getElementById(`${type}_b_pertama`).value) || 0;
            const bmu = parseInt(document.getElementById(`${type}_b_muda`).value) || 0;
            const bma = parseInt(document.getElementById(`${type}_b_madya`).value) || 0;
            const bu = parseInt(document.getElementById(`${type}_b_utama`).value) || 0;
            
            const total = bp + bmu + bma + bu;
            document.getElementById(`${type}_baseline`).value = total;
        }

        function updateJenjangLabels(selectId, labelPrefix) {
            const select = document.getElementById(selectId);
            const containerId = selectId === 'tambah_fungsional_id' ? 'container_baseline_tambah' : 'container_baseline_edit';
            const container = document.getElementById(containerId);
            
            if (!select.value) {
                container.classList.add('hidden');
                return;
            }
            
            const option = select.options[select.selectedIndex];
            const kode = parseInt(option.getAttribute('data-kode'));
            
            container.classList.remove('hidden');
            
            const lbl1 = document.getElementById(labelPrefix + '1');
            const lbl2 = document.getElementById(labelPrefix + '2');
            const lbl3 = document.getElementById(labelPrefix + '3');
            const lbl4 = document.getElementById(labelPrefix + '4');
            
            if (kode >= 1 && kode <= 4) { // Keterampilan
                lbl1.innerText = "Pemula"; lbl2.innerText = "Terampil"; lbl3.innerText = "Mahir"; lbl4.innerText = "Penyelia";
            } else if (kode >= 5 && kode <= 8) { // Keahlian
                lbl1.innerText = "Ahli Pertama"; lbl2.innerText = "Ahli Muda"; lbl3.innerText = "Ahli Madya"; lbl4.innerText = "Ahli Utama";
            } else {
                lbl1.innerText = "Jenjang 1"; lbl2.innerText = "Jenjang 2"; lbl3.innerText = "Jenjang 3"; lbl4.innerText = "Jenjang 4";
            }
        }

        function updateKodeJabatan() {
            const kodeUrut = document.getElementById('tambah_kode_urut').value;
            const selectFungsional = document.getElementById('tambah_fungsional_id');
            const inputFinal = document.getElementById('tambah_kode_jabatan');
            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption ? (selectedOption.getAttribute('data-kode') || "") : "";
            if (kodeUrut) inputFinal.value = kodeUrut + kodeJenjang;
            else inputFinal.value = "";
        }

        function openTambahJabatan() {
            document.getElementById('tambah_fungsional_id').value = "";
            document.getElementById('tambah_kode_jabatan').value = "{{ $nextBaseCode }}";
            document.getElementById('tambah_b_pertama').value = '0';
            document.getElementById('tambah_b_muda').value = '0';
            document.getElementById('tambah_b_madya').value = '0';
            document.getElementById('tambah_b_utama').value = '0';
            document.getElementById('container_baseline_tambah').classList.add('hidden');
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

        function openEditModal(id, kodeFull, nama, jenis, fungsional_id, bp, bmu, bma, bu, base) {
            const form = document.getElementById('formEditJabatan');
            const baseUrl = "{{ route('admin.jabatan.index') }}";
            form.action = `${baseUrl}/${id}`;

            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jenis_jabatan_id').value = jenis;
            document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";
            document.getElementById('edit_baseline').value = base || 0;
            
            document.getElementById('edit_b_pertama').value = bp || 0;
            document.getElementById('edit_b_muda').value = bmu || 0;
            document.getElementById('edit_b_madya').value = bma || 0;
            document.getElementById('edit_b_utama').value = bu || 0;

            const kodeUrut = kodeFull.substring(0, 3);
            document.getElementById('edit_kode_urut').value = kodeUrut;
            document.getElementById('edit_kode_final').value = kodeFull;
            
            updateJenjangLabels('edit_jabatan_fungsional_id', 'lbl_edit_j');

            toggleModal('modalEditJabatan');
        }

        // ==========================================
        // AJAX LOAD MATRIKS (ANTI-FREEZE DENGAN CHUNKING)
        // ==========================================
        async function loadMatriks() {
            const fungsionalId = document.getElementById('filter_fungsional_distribusi').value;
            const fungsionalKode = document.getElementById('filter_kode_fungsional').value;

            if (!fungsionalId) {
                Swal.fire({icon: 'warning', title: 'Pilih Jabatan', text: 'Silakan pilih Jabatan Fungsional terlebih dahulu!', confirmButtonColor: '#112D4E'});
                return;
            }

            Swal.fire({ 
                title: 'Mengunduh Data...', 
                text: 'Harap tunggu, mengambil data satker dari server.', 
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); } 
            });

            try {
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

                const response = await fetch(`{{ url('admin/jabatan/matriks') }}?jabatan_id=${fungsionalId}`);
                const dataJSON = await response.json();
                const data = dataJSON.satkers || [];
                
                const bp = dataJSON.b_pertama == 0 ? 0 : dataJSON.b_pertama;
                const bmu = dataJSON.b_muda == 0 ? 0 : dataJSON.b_muda;
                const bma = dataJSON.b_madya == 0 ? 0 : dataJSON.b_madya;
                const bu = dataJSON.b_utama == 0 ? 0 : dataJSON.b_utama;

                const lbl1 = (kodeF >= 1 && kodeF <= 4) ? "Pemula" : "Ahli Pertama";
                const lbl2 = (kodeF >= 1 && kodeF <= 4) ? "Terampil" : "Ahli Muda";
                const lbl3 = (kodeF >= 1 && kodeF <= 4) ? "Mahir" : "Ahli Madya";
                const lbl4 = (kodeF >= 1 && kodeF <= 4) ? "Penyelia" : "Ahli Utama";

                const panelBaseline = document.getElementById('panel_setup_baseline');
                panelBaseline.classList.remove('hidden');
                panelBaseline.innerHTML = `
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div>
                            <p class="text-xs text-amber-700 font-bold uppercase mb-1">Total Keseluruhan</p>
                            <span class="text-2xl font-black text-amber-900 bg-amber-200/50 px-4 py-1 rounded-lg">${dataJSON.baseline}</span>
                        </div>
                        <div class="flex-1 flex gap-3 items-end justify-end">
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl1}</p><div id="display_base_p" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bp}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl2}</p><div id="display_base_mu" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bmu}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl3}</p><div id="display_base_ma" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bma}</div></div>
                            <div class="text-center w-24"><p class="text-[10px] font-bold text-amber-800 uppercase mb-1">${lbl4}</p><div id="display_base_u" class="px-4 py-2 bg-white border border-amber-300 rounded font-black text-sm text-amber-900 shadow-sm">${bu}</div></div>
                        </div>
                    </div>
                `;

                function createInputHTML(val, id, type, parentId) {
                    const v = (val == 0 || val == null) ? '' : val;
                    const safeParentId = parentId ? parentId : 'root';
                    return `
                    <div class="relative w-full pt-1 pb-3">
                        <div id="tooltip_${type}_${id}" class="absolute bottom-[85%] left-1/2 transform -translate-x-1/2 mb-1 bg-slate-800 text-white text-[10px] px-2 py-1 rounded shadow-xl hidden z-20 whitespace-nowrap transition-opacity duration-200 opacity-0 pointer-events-none">
                            Sisa Kuota: <span id="sisa_val_${type}_${id}" class="font-bold text-amber-300"></span>
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
                        <p id="err_${type}_${id}" class="absolute bottom-0 left-0 right-0 text-center text-[9px] text-red-500 font-bold hidden leading-none tracking-tight">Lebih <span id="err_val_${type}_${id}"></span></p>
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

                        html += `
                            <tr class="transition border-b border-gray-100 ${bgClass}" id="row-${item.id}" data-parent="${item.parent_id || 'root'}">
                                <td class="py-4 pr-6 align-middle" style="${padStyle}">
                                    <div class="flex items-center ${textClass}">
                                        ${iconHtml}
                                        <span class="text-sm tracking-tight leading-snug">${item.nama_satker}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kp, item.id, 'kp', item.parent_id) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kp === '' ? '0' : kp}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kmu, item.id, 'kmu', item.parent_id) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kmu === '' ? '0' : kmu}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(kma, item.id, 'kma', item.parent_id) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${kma === '' ? '0' : kma}</div>`}</td>
                                <td class="px-2 py-1 align-top">${permMatriksEditKuota ? createInputHTML(ku, item.id, 'ku', item.parent_id) : `<div class="text-center font-bold text-slate-500 bg-slate-100 py-1.5 rounded border border-slate-200">${ku === '' ? '0' : ku}</div>`}</td>
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

        function getLimitAndSisa(input, type, parentId) {
            let limit = 0; let otherSum = 0;
            const val = parseInt(input.value) || 0;

            if (parentId === 'root') {
                limit = parseInt(document.getElementById(getBaseId(type)).innerText) || 0;
            } else {
                const parentEl = document.getElementById(`${type}_${parentId}`);
                limit = parentEl ? (parseInt(parentEl.value) || 0) : 0;
            }

            const siblings = document.querySelectorAll(`input[data-type="${type}"][data-parent="${parentId}"]`);
            siblings.forEach(el => {
                if (el.id !== input.id) otherSum += parseInt(el.value) || 0;
            });

            const sisa = limit - otherSum;
            return { limit, otherSum, sisa, isExceeding: val > sisa, excessAmount: val - sisa };
        }

        function handleFocus(input, id, type, parentId) {
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:ring-red-500');
            input.classList.add('focus:ring-blue-500', 'border-gray-300');
            
            const errEl = document.getElementById(`err_${type}_${id}`);
            if(errEl) errEl.classList.add('hidden');

            const { sisa } = getLimitAndSisa(input, type, parentId);
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
            validateInput(input, id, type, input.getAttribute('data-parent'));
        }

        function handleInput(input, id, type, parentId) {
            const kp = parseInt(document.getElementById(`kp_${id}`)?.value) || 0;
            const kmu = parseInt(document.getElementById(`kmu_${id}`)?.value) || 0;
            const kma = parseInt(document.getElementById(`kma_${id}`)?.value) || 0;
            const ku = parseInt(document.getElementById(`ku_${id}`)?.value) || 0;
            
            const totalEl = document.getElementById(`total_${id}`);
            if(totalEl) totalEl.innerText = kp + kmu + kma + ku;

            if (parentId === 'root') {
                document.querySelectorAll(`input[data-type="${type}"][data-parent="${id}"]`).forEach(childInput => {
                    const childId = childInput.id.split('_')[1];
                    validateInput(childInput, childId, type, id);
                });
            }
        }

        function validateInput(input, id, type, parentId) {
            const { isExceeding, excessAmount } = getLimitAndSisa(input, type, parentId);
            const errEl = document.getElementById(`err_${type}_${id}`);
            
            if (isExceeding && document.activeElement !== input) {
                input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                input.classList.remove('border-gray-300');
                
                const errValEl = document.getElementById(`err_val_${type}_${id}`);
                if(errValEl) errValEl.innerText = excessAmount;
                if(errEl) errEl.classList.remove('hidden');
            } else if (!isExceeding) {
                input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                input.classList.add('border-gray-300');
                if(errEl) errEl.classList.add('hidden');
            }
        }

        // ==========================================
        // OPTIMASI TOTAL O(N) PADA VALIDASI 
        // Agar DOM tidak thrashing saat di-load
        // ==========================================
        function validateAllHierarchies() {
            let sums = { kp: {}, kmu: {}, kma: {}, ku: {} };
            const inputs = document.querySelectorAll('#matriksTableBody input[data-type]');
            
            let inputMap = {};
            let errMap = {};
            let errValMap = {};

            // 1. Kumpulkan semua nilai (Hanya membaca)
            for (let i = 0; i < inputs.length; i++) {
                const input = inputs[i];
                const idAttr = input.id;
                inputMap[idAttr] = input;
                
                const errEl = document.getElementById('err_' + idAttr);
                if (errEl) {
                    errMap[idAttr] = errEl;
                    errValMap[idAttr] = document.getElementById('err_val_' + idAttr);
                }

                const type = input.dataset.type;
                const parentId = input.dataset.parent;
                if (!sums[type][parentId]) sums[type][parentId] = 0;
                sums[type][parentId] += (parseInt(input.value) || 0);
            }

            // 2. Ambil nilai dasar dari header
            const rootLimits = {
                kp: parseInt(document.getElementById('display_base_p')?.innerText) || 0,
                kmu: parseInt(document.getElementById('display_base_mu')?.innerText) || 0,
                kma: parseInt(document.getElementById('display_base_ma')?.innerText) || 0,
                ku: parseInt(document.getElementById('display_base_u')?.innerText) || 0,
            };

            // 3. Terapkan validasi secara mandiri dari Memori (Tanpa mencari DOM berkali-kali)
            for (let i = 0; i < inputs.length; i++) {
                const input = inputs[i];
                const type = input.dataset.type;
                const parentId = input.dataset.parent;
                const idAttr = input.id;
                const val = parseInt(input.value) || 0;

                let limit = 0;
                if (parentId === 'root') {
                    limit = rootLimits[type];
                } else {
                    const parentInput = inputMap[`${type}_${parentId}`];
                    limit = parentInput ? (parseInt(parentInput.value) || 0) : 0;
                }

                const otherSum = sums[type][parentId] - val;
                const sisa = limit - otherSum;
                const isExceeding = val > sisa;

                const errEl = errMap[idAttr];
                const errValEl = errValMap[idAttr];

                if (isExceeding && document.activeElement !== input) {
                    if (!input.classList.contains('border-red-500')) {
                        input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                        input.classList.remove('border-gray-300');
                        if (errEl) errEl.classList.remove('hidden');
                    }
                    if (errValEl) errValEl.innerText = val - sisa;
                } else if (!isExceeding) {
                    if (input.classList.contains('border-red-500')) {
                        input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                        input.classList.add('border-gray-300');
                        if (errEl) errEl.classList.add('hidden');
                    }
                }
            }
        }

        // ==========================================
        // LOGIKA INTERAKTIF (TOOLTIP, SISA, & MERAH)
        // ==========================================
        function getBaseId(type) {
            return { 'kp': 'display_base_p', 'kmu': 'display_base_mu', 'kma': 'display_base_ma', 'ku': 'display_base_u' }[type];
        }

        function getLimitAndSisa(input, type, parentId) {
            let limit = 0; let otherSum = 0;
            const val = parseInt(input.value) || 0;

            if (parentId === 'root') {
                limit = parseInt(document.getElementById(getBaseId(type)).innerText) || 0;
            } else {
                const parentEl = document.getElementById(`${type}_${parentId}`);
                limit = parentEl ? (parseInt(parentEl.value) || 0) : 0;
            }

            const siblings = document.querySelectorAll(`input[data-type="${type}"][data-parent="${parentId}"]`);
            siblings.forEach(el => {
                if (el.id !== input.id) otherSum += parseInt(el.value) || 0;
            });

            const sisa = limit - otherSum;
            return { limit, otherSum, sisa, isExceeding: val > sisa, excessAmount: val - sisa };
        }

        function handleFocus(input, id, type, parentId) {
            input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600', 'focus:ring-red-500');
            input.classList.add('focus:ring-blue-500', 'border-gray-300');
            document.getElementById(`err_${type}_${id}`).classList.add('hidden');

            const { sisa } = getLimitAndSisa(input, type, parentId);
            document.getElementById(`sisa_val_${type}_${id}`).innerText = sisa;
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            tooltip.classList.remove('hidden');
            setTimeout(() => tooltip.classList.remove('opacity-0'), 10);
        }

        function handleBlur(input, id, type) {
            const tooltip = document.getElementById(`tooltip_${type}_${id}`);
            tooltip.classList.add('opacity-0');
            setTimeout(() => tooltip.classList.add('hidden'), 200);
            validateInput(input, id, type, input.getAttribute('data-parent'));
        }

        function handleInput(input, id, type, parentId) {
            const kp = parseInt(document.getElementById(`kp_${id}`).value) || 0;
            const kmu = parseInt(document.getElementById(`kmu_${id}`).value) || 0;
            const kma = parseInt(document.getElementById(`kma_${id}`).value) || 0;
            const ku = parseInt(document.getElementById(`ku_${id}`).value) || 0;
            document.getElementById(`total_${id}`).innerText = kp + kmu + kma + ku;

            if (parentId === 'root') {
                document.querySelectorAll(`input[data-type="${type}"][data-parent="${id}"]`).forEach(childInput => {
                    validateInput(childInput, childInput.id.split('_')[1], type, id);
                });
            }
        }

        function validateInput(input, id, type, parentId) {
            const { isExceeding, excessAmount } = getLimitAndSisa(input, type, parentId);
            const errEl = document.getElementById(`err_${type}_${id}`);
            
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

        // OPTIMASI TOTAL: Algoritma Linear O(N). Tidak ada lagi lag/browser freeze!
        function validateAllHierarchies() {
            let sums = { kp: {}, kmu: {}, kma: {}, ku: {} };
            const inputs = document.querySelectorAll('#matriksTableBody input[data-type]');
            
            inputs.forEach(input => {
                const type = input.dataset.type;
                const parentId = input.dataset.parent;
                if (!sums[type][parentId]) sums[type][parentId] = 0;
                sums[type][parentId] += (parseInt(input.value) || 0);
            });

            inputs.forEach(input => {
                const type = input.dataset.type;
                const parentId = input.dataset.parent;
                const id = input.id.split('_')[1];
                const val = parseInt(input.value) || 0;
                
                let limit = 0;
                if (parentId === 'root') {
                    limit = parseInt(document.getElementById(getBaseId(type)).innerText) || 0;
                } else {
                    const parentInput = document.getElementById(`${type}_${parentId}`);
                    limit = parentInput ? (parseInt(parentInput.value) || 0) : 0;
                }

                const otherSum = sums[type][parentId] - val;
                const sisa = limit - otherSum;
                const isExceeding = val > sisa;

                const errEl = document.getElementById(`err_${type}_${id}`);
                if (isExceeding && document.activeElement !== input) {
                    input.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.remove('border-gray-300');
                    document.getElementById(`err_val_${type}_${id}`).innerText = val - sisa;
                    errEl.classList.remove('hidden');
                } else if (!isExceeding) {
                    input.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
                    input.classList.add('border-gray-300');
                    errEl.classList.add('hidden');
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
                nama: j.nama_jabatan,
                kode: j.kode_jabatan,
                jenjang: j.fungsional ? j.fungsional.name : null,
                kode_fungsional: j.fungsional ? parseInt(j.fungsional.kode) : null
            };
        });

        const searchInputJabatan = document.getElementById('search_jabatan_input');
        const hiddenInputJabatan = document.getElementById('filter_fungsional_distribusi');
        const hiddenKodeFungsional = document.getElementById('filter_kode_fungsional');
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
            hiddenKodeFungsional.value = item.kode_fungsional || '';
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
            hiddenKodeFungsional.value = '';
            this.classList.add('hidden');
            dropdownListJabatan.classList.add('hidden');
            
            document.getElementById('panel_setup_baseline').classList.add('hidden');
            document.getElementById('matriksTableBody').innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm"><i class="fas fa-sitemap mb-3 text-2xl text-slate-300 block"></i>Pilih Jabatan Fungsional dan klik "Tampilkan Matriks" untuk melihat sebaran kuota.</td></tr>';
            
            document.getElementById('th_jenjang_1').innerText = "Jenjang 1";
            document.getElementById('th_jenjang_2').innerText = "Jenjang 2";
            document.getElementById('th_jenjang_3').innerText = "Jenjang 3";
            document.getElementById('th_jenjang_4').innerText = "Jenjang 4";
        });
    </script>
@endpush