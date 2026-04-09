@extends('layouts.admin')
@section('title', 'Manajemen Akses Role & Pejabat')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Manajemen Hak Akses</h2>
        <p class="text-sm text-slate-500">Atur menu, aksi, dan visibilitas untuk Role dan Jenis Penugasan Pejabat.</p>
    </div>
</div>

{{-- TABEL ROLES --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="bg-slate-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <i class="fas fa-user-shield text-blue-600 text-lg"></i>
            <h3 class="font-bold text-slate-700">Daftar Role Sistem</h3>
        </div>
        <button onclick="toggleModal('modalTambahRole')" class="bg-[#112D4E] hover:bg-blue-900 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
            <i class="fas fa-plus"></i> Tambah Role
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-white text-slate-500 font-bold uppercase text-[11px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Nama Role</th>
                    <th class="px-6 py-4">Key ID</th>
                    <th class="px-6 py-4 text-center">Ditampilkan di Penugasan?</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($roles as $role)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 font-bold text-slate-800">{{ $role->nama }}</td>
                    <td class="px-6 py-4"><span class="bg-slate-100 text-slate-500 px-2 py-1 rounded text-[10px] font-mono">{{ $role->key }}</span></td>
                    <td class="px-6 py-4 text-center">
                        @if($role->key === 'super_admin')
                            <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-2 py-1 rounded" title="Role sistem mutlak tidak dikelola via Penugasan">TIDAK BISA</span>
                        @elseif($role->is_assignable)
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded">YA</span>
                        @else
                            <span class="bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded">TIDAK</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        @if($role->key !== 'super_admin')
                        <button onclick="openEditModal({{ $role->id }}, 'role', '{{ $role->nama }}', {{ $role->is_assignable ? 'true' : 'false' }}, {{ json_encode($role->menus ?? []) }})" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold transition"><i class="fas fa-cog"></i> Akses</button>
                        <form action="{{ route('admin.role.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus role ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition"><i class="fas fa-trash"></i></button>
                        </form>
                        @else
                        <span class="text-xs text-slate-400 italic">Mutlak</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- TABEL PEJABAT (JENIS PENUGASAN) --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="bg-slate-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <i class="fas fa-user-tie text-amber-600 text-lg"></i>
            <div>
                <h3 class="font-bold text-slate-700">Daftar Hak Akses Pejabat (Struktural)</h3>
                <p class="text-[10px] text-slate-500">Pengaturan spesifik untuk user Definitif, PLT, dll.</p>
            </div>
        </div>
        <button onclick="toggleModal('modalTambahPenugasan')" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
            <i class="fas fa-plus"></i> Tambah Pejabat
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-white text-slate-500 font-bold uppercase text-[11px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Status Jabatan</th>
                    <th class="px-6 py-4 text-center">Ditampilkan di Penugasan?</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($penugasans as $jp)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 font-bold text-slate-800"><i class="fas fa-level-up-alt fa-rotate-90 text-slate-300 mr-2"></i> {{ $jp->nama }}</td>
                    <td class="px-6 py-4 text-center">
                        @if($jp->is_assignable)
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded">YA</span>
                        @else
                            <span class="bg-red-50 text-red-500 text-[10px] font-bold px-2 py-1 rounded">TIDAK</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                        <button onclick="openEditModal({{ $jp->id }}, 'penugasan', '{{ $jp->nama }}', {{ $jp->is_assignable ? 'true' : 'false' }}, {{ json_encode($jp->menus ?? []) }})" class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg text-xs font-bold transition"><i class="fas fa-cog"></i> Akses</button>
                        <form action="{{ route('admin.role.penugasan.destroy', $jp->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jenis penugasan ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-bold transition"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODALS TAMBAH --}}
<div id="modalTambahRole" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20"><div class="fixed inset-0 transition-opacity bg-slate-900/60" onclick="toggleModal('modalTambahRole')"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl sm:max-w-md w-full z-50 overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between"><h3 class="font-bold">Tambah Role</h3><button onclick="toggleModal('modalTambahRole')"><i class="fas fa-times"></i></button></div>
            <form action="{{ route('admin.role.store') }}" method="POST" class="p-6 space-y-4">@csrf
                <div><label class="block text-xs font-bold mb-2">Nama Role</label><input type="text" name="nama" required class="w-full border px-3 py-2 rounded-lg"></div>
                <div><label class="block text-xs font-bold mb-2">Key ID</label><input type="text" name="key" required class="w-full border px-3 py-2 rounded-lg" placeholder="huruf_kecil_saja"></div>
                <div class="text-right"><button type="submit" class="bg-[#112D4E] text-white px-4 py-2 rounded-lg text-sm font-bold">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<div id="modalTambahPenugasan" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20"><div class="fixed inset-0 transition-opacity bg-slate-900/60" onclick="toggleModal('modalTambahPenugasan')"></div>
        <div class="inline-block bg-white rounded-2xl shadow-xl sm:max-w-md w-full z-50 overflow-hidden">
            <div class="px-6 py-4 border-b flex justify-between"><h3 class="font-bold text-amber-700">Tambah Jenis Pejabat</h3><button onclick="toggleModal('modalTambahPenugasan')"><i class="fas fa-times"></i></button></div>
            <form action="{{ route('admin.role.penugasan.store') }}" method="POST" class="p-6 space-y-4">@csrf
                <div><label class="block text-xs font-bold mb-2">Nama Jenis Penugasan</label><input type="text" name="nama" required class="w-full border px-3 py-2 rounded-lg" placeholder="Contoh: Wakil PLT"></div>
                <div class="text-right"><button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-bold">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PENGATURAN AKSES --}}
<div id="modalEditAkses" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-10 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalEditAkses')"></div>
        <div class="inline-block w-full max-w-4xl overflow-hidden text-left align-middle transition-all transform bg-slate-50 rounded-2xl shadow-xl z-50 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 bg-white border-b flex justify-between items-center shrink-0">
                <div><h3 class="text-lg font-bold text-slate-800">Atur Hak Akses</h3><p class="text-xs text-blue-600 font-bold uppercase mt-1" id="edit_role_name">-</p></div>
                <button type="button" onclick="toggleModal('modalEditAkses')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
            </div>

            <form id="formEditAkses" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf @method('PUT')
                <input type="hidden" name="target_type" id="edit_target_type">
                <input type="hidden" name="nama" id="edit_nama_input">
                
                <div class="p-6 overflow-y-auto space-y-6">
                    <div class="bg-white p-4 border rounded-xl shadow-sm flex justify-between items-center">
                        <div><h4 class="font-bold text-slate-800 text-sm">Ditampilkan di Pilihan Penugasan?</h4></div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_assignable" id="edit_is_assignable" class="sr-only peer">
                            <div class="w-11 h-6 bg-red-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                    </div>

                    {{-- KELOMPOK UTAMA --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Menu Utama</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer shadow-sm"><input type="checkbox" name="menus[dashboard]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Dashboard</span></label>
                        </div>
                    </div>

                    {{-- KELOMPOK MASTER DATA --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Master Data</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[wilayah]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Wilayah</span></label>
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[pegawai]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Pegawai</span></label>
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[periode]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Periode</span></label>
                            
                            {{-- TRIGGER SATKER & JABATAN (ID DISESUAIKAN) --}}
                            <label class="flex items-center p-3 bg-amber-50 border-amber-200 border rounded-xl cursor-pointer">
                                <input type="checkbox" name="menus[satker][enabled]" value="1" id="trigger_satker" class="w-4 h-4 text-amber-600" onchange="toggleComplex('satker')">
                                <span class="ml-2 text-sm font-bold text-amber-800">Satuan Kerja</span>
                            </label>
                            <label class="flex items-center p-3 bg-purple-50 border-purple-200 border rounded-xl cursor-pointer">
                                <input type="checkbox" name="menus[jabatan][enabled]" value="1" id="trigger_jabatan" class="w-4 h-4 text-purple-600" onchange="toggleComplex('jabatan')">
                                <span class="ml-2 text-sm font-bold text-purple-800">Jabatan Fungsional</span>
                            </label>
                        </div>
                    </div>

                    {{-- KELOMPOK PENGATURAN --}}
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pengaturan Sistem</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[audit_log]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Audit Log</span></label>
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[setting_kode]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Rumus Kode</span></label>
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[manajemen_role]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Akses Role</span></label>
                            <label class="flex items-center p-3 bg-white border rounded-xl cursor-pointer"><input type="checkbox" name="menus[regulasi]" value="1" class="cb-simple w-4 h-4 text-blue-600"><span class="ml-2 text-sm font-bold">Regulasi Penugasan</span></label>
                        </div>
                    </div>

                    {{-- KOMPLEKS: SATUAN KERJA --}}
                    <div class="bg-white border rounded-xl shadow-sm p-5 hidden" id="complex_satker">
                        <h4 class="font-bold text-slate-800 mb-4 border-b pb-2"><i class="fas fa-building text-amber-500 mr-2"></i> Pengaturan Modul Satuan Kerja</h4>
                        <div class="flex gap-4 mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <label class="flex items-center"><input type="checkbox" name="menus[satker][view_only]" id="satker_view_only" value="1" class="w-4 h-4 text-amber-600 rounded"><span class="ml-2 text-sm font-bold text-amber-800">Hanya Melihat</span></label>
                            <label class="flex items-center"><input type="checkbox" name="menus[satker][all_access]" id="satker_all_access" value="1" class="w-4 h-4 text-amber-600 rounded"><span class="ml-2 text-sm font-bold text-amber-800">Semua Akses Mutlak</span></label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="target-disable-satker">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Hak Aksi</p>
                                <div class="space-y-2 text-sm">
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="create" class="mr-2 rounded"> Tambah Satker</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="edit" class="mr-2 rounded"> Edit Satker</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="delete" class="mr-2 rounded"> Hapus Satker</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="assign" class="mr-2 rounded"> Tambah Penugasan</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="end_self" class="mr-2 rounded"> End Date Diri Sendiri</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="end_other" class="mr-2 rounded"> End Date Orang Lain</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="cuti_self" class="mr-2 rounded"> Cuti Diri Sendiri</label>
                                    <label class="flex"><input type="checkbox" name="menus[satker][actions][]" value="cuti_other" class="mr-2 rounded"> Cutikan Orang Lain</label>
                                </div>
                            </div>
                            <div class="target-disable-satker">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Hak Visibilitas (Melihat)</p>
                                <div class="space-y-2 text-sm">
                                    <label class="flex"><input type="radio" name="menus[satker][visibility]" value="all" class="mr-2"> Semua Satker</label>
                                    <label class="flex"><input type="radio" name="menus[satker][visibility]" value="self_only" class="mr-2"> Satker yg Ditempatkan Saja</label>
                                    <label class="flex"><input type="radio" name="menus[satker][visibility]" value="self_up_down" class="mr-2"> Satker Ditempatkan + Induk & Bawahan</label>
                                    <label class="flex"><input type="radio" name="menus[satker][visibility]" value="self_down" class="mr-2"> Satker Ditempatkan + Bawahan Saja</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KOMPLEKS: JABATAN FUNGSIONAL (DIPERBAIKI ID-NYA) --}}
                    <div class="bg-white border rounded-xl shadow-sm p-5 hidden" id="complex_jabatan">
                        <h4 class="font-bold text-slate-800 mb-4 border-b pb-2"><i class="fas fa-id-card text-purple-500 mr-2"></i> Pengaturan Modul Jabatan Fungsional</h4>
                        <div class="flex gap-4 mb-5 p-3 bg-purple-50 border border-purple-200 rounded-lg">
                            <label class="flex items-center"><input type="checkbox" name="menus[jabatan][view_only]" id="jabatan_view_only" value="1" class="w-4 h-4 text-purple-600 rounded"><span class="ml-2 text-sm font-bold text-purple-800">Hanya Melihat</span></label>
                            <label class="flex items-center"><input type="checkbox" name="menus[jabatan][all_access]" id="jabatan_all_access" value="1" class="w-4 h-4 text-purple-600 rounded"><span class="ml-2 text-sm font-bold text-purple-800">Semua Akses Mutlak</span></label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="target-disable-jabatan">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Aksi Master</p>
                                <div class="space-y-2 text-sm">
                                    <label class="flex"><input type="checkbox" name="menus[jabatan][actions][]" value="create" class="mr-2 rounded"> Tambah Jafung</label>
                                    <label class="flex"><input type="checkbox" name="menus[jabatan][actions][]" value="edit" class="mr-2 rounded"> Edit Jafung</label>
                                    <label class="flex"><input type="checkbox" name="menus[jabatan][actions][]" value="delete" class="mr-2 rounded"> Hapus Jafung</label>
                                </div>
                            </div>
                            <div class="target-disable-jabatan">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Aksi Matriks</p>
                                <div class="space-y-2 text-sm">
                                    <label class="flex"><input type="checkbox" name="menus[jabatan][matriks][]" value="set_baseline" class="mr-2 rounded"> Set Baseline Alokasi</label>
                                    <label class="flex"><input type="checkbox" name="menus[jabatan][matriks][]" value="edit_kuota" class="mr-2 rounded"> Edit Kuota Satker</label>
                                </div>
                            </div>
                            <div class="target-disable-jabatan">
                                <p class="text-xs font-bold text-slate-400 uppercase mb-2">Hak Visibilitas</p>
                                <div class="space-y-2 text-sm">
                                    <label class="flex"><input type="radio" name="menus[jabatan][visibility]" value="all" class="mr-2"> Semua Satker</label>
                                    <label class="flex"><input type="radio" name="menus[jabatan][visibility]" value="self_only" class="mr-2"> Ditempatkan Saja</label>
                                    <label class="flex"><input type="radio" name="menus[jabatan][visibility]" value="self_up_down" class="mr-2"> Induk & Bawahan</label>
                                    <label class="flex"><input type="radio" name="menus[jabatan][visibility]" value="self_down" class="mr-2"> Bawahan Saja</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="p-6 bg-white border-t flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="toggleModal('modalEditAkses')" class="px-5 py-2 text-sm font-bold bg-slate-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-[#112D4E] rounded-xl">Simpan Semua Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')

@push('scripts')
{{-- ======================================================== --}}
{{-- NOTIFIKASI SWEETALERT (SUKSES & ERROR) --}}
{{-- ======================================================== --}}
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{!! session('success') !!}", timer: 3000, showConfirmButton: false });
    });
</script>
@endif

@if(session('error') || $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let errorHtml = '';
        @if(session('error')) errorHtml += '<p>{!! session('error') !!}</p>'; @endif
        @if($errors->any())
            @foreach($errors->all() as $error) errorHtml += '<p>{{ $error }}</p>'; @endforeach
        @endif
        Swal.fire({ icon: 'error', title: 'Gagal Memproses', html: errorHtml });
    });
</script>
@endif

{{-- ======================================================== --}}
{{-- EFEK LOADING PADA SEMUA TOMBOL SUBMIT FORM --}}
{{-- ======================================================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                // Biarkan browser menampilkan error validasi HTML5 (jika ada)
                if (!this.checkValidity()) return;

                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    // Cegah double submit
                    if (this.dataset.submitted === 'true') {
                        e.preventDefault();
                        return;
                    }
                    this.dataset.submitted = 'true';

                    // Ubah UI tombol menjadi loading
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
                    
                    // Simpan icon lama untuk jaga-jaga, lalu ganti dengan spinner
                    if(!submitBtn.dataset.original) submitBtn.dataset.original = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                }
            });
        });
    });
</script>

<script>
    function toggleModal(modalId) { document.getElementById(modalId).classList.toggle('hidden'); }

    // Logika Show/Hide Complex Modul
    function toggleComplex(prefix) {
        const isChecked = document.getElementById('trigger_' + prefix).checked;
        document.getElementById('complex_' + prefix).style.display = isChecked ? 'block' : 'none';
        if(!isChecked) {
            // Uncheck semua di dalamnya jika modul dimatikan
            document.querySelectorAll(`#complex_${prefix} input[type="checkbox"], #complex_${prefix} input[type="radio"]`).forEach(el => el.checked = false);
        }
    }

    function openEditModal(id, type, nama, isAssignable, menusData) {
        document.getElementById('edit_role_name').innerText = nama;
        document.getElementById('edit_nama_input').value = nama;
        document.getElementById('edit_target_type').value = type;
        document.getElementById('edit_is_assignable').checked = isAssignable;

        document.getElementById('formEditAkses').reset();
        document.getElementById('edit_is_assignable').checked = isAssignable; 
        
        // Anti error untuk format data lama berupa array biasa
        const isOldArrayFormat = Array.isArray(menusData);
        
        // Checklist Simple
        document.querySelectorAll('.cb-simple').forEach(cb => {
            const key = cb.name.match(/\[(.*?)\]/)[1];
            if(isOldArrayFormat) {
                if(menusData.includes(key)) cb.checked = true;
            } else {
                if(menusData && menusData[key]) cb.checked = true;
            }
        });

        if(!isOldArrayFormat) {
            // Set Data Kompleks
            ['satker', 'jabatan'].forEach(prefix => {
                const trig = document.getElementById(`trigger_${prefix}`);
                if(!trig) return; // Safeguard anti-error

                const data = menusData ? menusData[prefix] : null;
                if(data && data.enabled) {
                    trig.checked = true;
                    toggleComplex(prefix);
                    
                    let elView = document.getElementById(`${prefix}_view_only`);
                    let elAll = document.getElementById(`${prefix}_all_access`);
                    
                    if(data.view_only && elView) elView.checked = true;
                    if(data.all_access && elAll) elAll.checked = true;
                    
                    ['actions', 'matriks'].forEach(group => {
                        if(data[group]) data[group].forEach(val => {
                            let cb = document.querySelector(`input[name="menus[${prefix}][${group}][]"][value="${val}"]`);
                            if(cb) cb.checked = true;
                        });
                    });
                    if(data.visibility) {
                        let rb = document.querySelector(`input[name="menus[${prefix}][visibility]"][value="${data.visibility}"]`);
                        if(rb) rb.checked = true;
                    }
                } else {
                    trig.checked = false;
                    toggleComplex(prefix);
                }
                triggerComplexLogic(prefix);
            });
        }

        let baseUrl = "{{ url('admin/role') }}";
        document.getElementById('formEditAkses').action = `${baseUrl}/${id}`;
        toggleModal('modalEditAkses');
    }

    function handleComplexMenuLogic(prefix) {
        const viewOnly = document.getElementById(`${prefix}_view_only`);
        const allAccess = document.getElementById(`${prefix}_all_access`);
        if(!viewOnly || !allAccess) return;

        const targets = document.querySelectorAll(`.target-disable-${prefix} input`);

        const applyLogic = () => {
            if (allAccess.checked) {
                viewOnly.disabled = true;
                targets.forEach(el => { el.disabled = true; if(el.type === 'checkbox') el.checked = true; if(el.type === 'radio' && el.value === 'all') el.checked = true; });
            } else if (viewOnly.checked) {
                allAccess.disabled = true;
                targets.forEach(el => { el.disabled = true; if(el.type === 'checkbox') el.checked = false; if(el.type === 'radio' && el.value === 'all') el.checked = true; });
            } else {
                allAccess.disabled = false; viewOnly.disabled = false; targets.forEach(el => el.disabled = false);
            }
        };

        viewOnly.addEventListener('change', applyLogic);
        allAccess.addEventListener('change', applyLogic);
        window['triggerComplexLogic_' + prefix] = applyLogic;
    }

    function triggerComplexLogic(prefix) { if(window['triggerComplexLogic_' + prefix]) window['triggerComplexLogic_' + prefix](); }

    document.addEventListener('DOMContentLoaded', () => { handleComplexMenuLogic('satker'); handleComplexMenuLogic('jabatan'); });
</script>
@endpush