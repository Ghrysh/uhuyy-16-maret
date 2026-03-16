@extends('layouts.admin')

@section('title', 'Riwayat Penugasan')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Container utama TomSelect */
        .ts-wrapper.single .ts-control {
            padding: 0.65rem 1rem !important;
            border-radius: 0.75rem !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.875rem !important;
            box-shadow: none !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Mencegah teks meluber ke samping (Force Wrap) */
        .ts-wrapper.single .ts-control .item {
            white-space: normal !important;
            word-break: break-word !important;
            padding-right: 20px !important; /* Ruang untuk arrow */
        }

        /* Panel Dropdown */
        .ts-dropdown {
            border-radius: 0.75rem !important;
            margin-top: 5px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 9999 !important;
            max-width: 100% !important; /* Kunci agar tidak melebar */
        }

        /* Mengatur tiap pilihan di dropdown agar teks turun ke bawah */
        .ts-dropdown .option {
            padding: 0.75rem 1rem !important;
            border-bottom: 1px solid #f1f5f9;
            white-space: normal !important;
            word-break: break-word !important;
            line-height: 1.4 !important;
        }

        .ts-dropdown .active {
            background-color: #eff6ff !important;
            color: #1e40af !important;
        }

        /* Menyesuaikan posisi arrow TomSelect */
        .ts-wrapper.single .ts-control::after {
            right: 15px !important;
        }
    </style>
@endpush

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Penugasan Pejabat</h2>
            <p class="text-sm text-slate-500">Kelola penugasan pejabat pada satuan kerja</p>
        </div>
        <button onclick="toggleModal('modalTambahPenugasan')"
            class="bg-[#112D4E] hover:bg-blue-900 text-white px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm">
            <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Penugasan
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-slate-500 text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold">Satker / Unit</th>
                        <th class="px-6 py-4 font-bold">Jabatan</th>
                        <th class="px-6 py-4 font-bold">Jenis</th>
                        <th class="px-6 py-4 font-bold">Periode</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($penugasans as $item)
                        <tr class="hover:bg-blue-50/30 transition group">
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-700 font-semibold">{{ $item->user->name ?? '-' }}</span>
                                <p class="text-[10px] text-slate-400">NIP: {{ $item->user->nip ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600 block leading-relaxed">{{ $item->satker->nama_satker ?? '-' }}</span>
                                <span class="text-[10px] text-blue-500 font-mono">{{ $item->satker->kode_satker ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 font-medium">
                                {{ $item->jabatan->nama_jabatan ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                    {{ $item->jenisPenugasan->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                <div class="flex items-center space-x-1 font-medium">
                                    <span>{{ $item->tanggal_mulai ?? '-' }}</span>
                                    <span class="text-slate-300">-</span>
                                    <span>{{ $item->tanggal_selesai ?? 'Tidak ditentukan' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if ($item->status_aktif)
                                    <span class="bg-green-100 text-green-700 text-[10px] px-3 py-1 rounded-full font-bold uppercase border border-green-200">Aktif</span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 text-[10px] px-3 py-1 rounded-full font-bold uppercase">Selesai</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                <button type="button"
                                    onclick="openEditModal('{{ $item->id }}', '{{ $item->user_id }}', '{{ $item->satker_id }}', '{{ $item->jabatan_id }}', '{{ $item->jenis_penugasan_id }}', '{{ $item->tanggal_mulai }}', '{{ $item->tanggal_selesai }}', '{{ $item->status_aktif }}')"
                                    class="text-slate-400 hover:text-blue-600 transition">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada data penugasan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div id="modalTambahPenugasan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalTambahPenugasan')"></div>
            <div class="inline-block overflow-hidden text-left bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Penugasan</h3>
                    <button onclick="toggleModal('modalTambahPenugasan')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.penugasan.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Satuan Kerja</label>
                            <select name="satker_id" class="tom-select" required>
                                <option value="">Pilih satker</option>
                                @foreach ($satkers as $s)
                                    <option value="{{ $s->id }}">{{ $s->kode_satker }} - {{ $s->nama_satker }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">User / Pegawai</label>
                            <select name="user_id" class="tom-select" required>
                                <option value="">Pilih user</option>
                                @foreach ($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nip }} - {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jabatan</label>
                                <select name="jabatan_id" class="tom-select" required>
                                    <option value="">Pilih jabatan</option>
                                    @foreach ($jabatans as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Penugasan</label>
                                <select name="jenis_penugasan_id" class="tom-select" required>
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
                                <input type="date" name="tanggal_mulai" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500 outline-none">
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <span class="text-sm font-bold text-slate-700">Status Aktif</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="status_aktif" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#112D4E]"></div>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="toggleModal('modalTambahPenugasan')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-gray-100 rounded-lg transition">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg hover:bg-blue-900 transition shadow-md">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="modalEditPenugasan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="toggleModal('modalEditPenugasan')"></div>
            <div class="inline-block overflow-hidden text-left bg-white rounded-2xl shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Edit Penugasan</h3>
                    <button onclick="toggleModal('modalEditPenugasan')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="formEditPenugasan" method="POST">
                    @csrf @method('PUT')
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Satuan Kerja</label>
                            <select name="satker_id" id="edit_satker_id" class="tom-select">
                                @foreach ($satkers as $s)
                                    <option value="{{ $s->id }}">{{ $s->kode_satker }} - {{ $s->nama_satker }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">User</label>
                            <select name="user_id" id="edit_user_id" class="tom-select">
                                @foreach ($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nip }} - {{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jabatan</label>
                                <select name="jabatan_id" id="edit_jabatan_id" class="tom-select">
                                    @foreach ($jabatans as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama_jabatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Penugasan</label>
                                <select name="jenis_penugasan_id" id="edit_jenis_penugasan_id" class="tom-select">
                                    @foreach ($jenis_penugasans as $jp)
                                        <option value="{{ $jp->id }}">{{ $jp->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:border-blue-500">
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-bold text-slate-700">Status Aktif</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="status_aktif" id="edit_status_aktif" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#112D4E]"></div>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-2xl">
                        <button type="button" onclick="toggleModal('modalEditPenugasan')" class="px-4 py-2 text-sm font-semibold text-slate-600 rounded-lg transition">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg transition shadow-md">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        // Simpan instance TomSelect agar bisa diupdate saat buka modal edit
        var tsInstances = {};

        document.querySelectorAll('.tom-select').forEach((el) => {
            tsInstances[el.id || el.name] = new TomSelect(el, {
                create: false,
                sortField: { field: "text", order: "asc" }
            });
        });

        function toggleModal(id) {
            document.getElementById(id).classList.toggle('hidden');
        }

        function openEditModal(id, user_id, satker_id, jabatan_id, jenis_id, tgl_mulai, tgl_selesai, status) {
            const form = document.getElementById('formEditPenugasan');
            form.action = `/admin/penugasan/${id}`;

            // Update nilai TomSelect
            tsInstances['user_id'].setValue(user_id);
            tsInstances['satker_id'].setValue(satker_id);
            tsInstances['jabatan_id'].setValue(jabatan_id);
            tsInstances['jenis_penugasan_id'].setValue(jenis_id);

            document.getElementById('edit_tanggal_mulai').value = tgl_mulai;
            document.getElementById('edit_tanggal_selesai').value = tgl_selesai;
            document.getElementById('edit_status_aktif').checked = (status == 1);

            toggleModal('modalEditPenugasan');
        }
    </script>
@endpush