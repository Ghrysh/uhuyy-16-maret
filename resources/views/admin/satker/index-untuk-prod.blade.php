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

@section('content')
    {{-- Ganti baris ini --}}
@section('content')
    <div x-data="{
        search: '',
        activePeriode: '{{ $periodes->first()->id ?? '' }}'
    }">

        {{-- Header Section --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Satuan Kerja</h2>
                <p class="text-sm text-slate-500">Kelola data satuan kerja Kementerian Agama</p>
            </div>
            <div class="flex items-center space-x-4">
                {{-- Search Bar --}}
                <form action="{{ route('admin.satker.index') }}" method="GET" class="relative w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode atau nama..."
                        class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition shadow-sm">
                </form>

                <button type="button" onclick="toggleModal('modalTambahSatker')"
                    class="bg-[#112D4E] hover:bg-blue-900 text-white px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm">
                    <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Satker
                </button>
            </div>
        </div>

        {{-- TABS PERIODE --}}
        <div class="flex items-center border-b border-gray-200 mb-6 overflow-x-auto">
            @foreach ($periodes as $pe)
                <button type="button" @click="activePeriode = '{{ $pe->id }}'"
                    :class="activePeriode == '{{ $pe->id }}' ? 'border-blue-600 text-blue-600' :
                        'border-transparent text-slate-400 hover:text-slate-600'"
                    class="px-6 py-3 border-b-2 font-bold text-xs uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none">
                    {{ $pe->nama_periode }}
                </button>
            @endforeach
        </div>

        {{-- Main Content Container --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 space-y-2">
                @forelse ($satkers as $satker)
                    {{-- Filter Berdasarkan activePeriode --}}
                    {{-- Ganti 'id_periode' sesuai dengan nama kolom foreign key di tabel satker Anda --}}
                    <div x-show="activePeriode == '{{ $satker->periode_id }}'"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        @include('admin.satker._item_hirarki', ['item' => $satker])
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="fas fa-folder-open text-slate-300 text-3xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm italic">Belum ada data satuan kerja.</p>
                    </div>
                @endforelse

                {{-- State: Jika periode yang dipilih tidak memiliki data --}}
                {{-- Bagian ini mendeteksi jika semua div tersembunyi --}}
                <div x-cloak x-show="!document.querySelector(`[x-show*='activePeriode ==']`)"
                    class="py-12 text-center text-slate-400 text-sm italic">
                    Tidak ada data untuk periode ini.
                </div>
            </div>

            {{-- Loader --}}
            <div id="page-loader"
                class="fixed inset-0 z-[9999] bg-slate-900/20 backdrop-blur-sm flex items-center justify-center hidden">
                <div class="bg-white p-4 rounded-xl shadow-xl flex flex-col items-center">
                    <div class="w-10 h-10 border-4 border-slate-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-xs font-bold text-slate-600 mt-3 uppercase tracking-wider">Memuat Data...</p>
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
                            <option value="">Pilih Satker Induk</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}" data-eselon="{{ $p->jenis_satker_id }}"
                                    data-wilayah="{{ $p->wilayah_id }}" data-periode="{{ $p->periode_id }}">
                                    {{-- Pastikan data-periode ada --}}
                                    {{ $p->kode_satker }} - {{ $p->nama_satker }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Input Jabatan (Opsional) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Jabatan Fungsional
                            (Opsional)</label>
                        <select name="jabatan_id" id="jabatan_id"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">Pilih Jabatan</option>
                            @foreach ($jabatan as $j)
                                <option value="{{ $j->id }}">{{ $j->kode_jabatan }} - {{ $j->nama_jabatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Kode Satker (Generate)</label>
                        <div class="flex gap-2 items-center" id="kode_container">
                            <input type="text" name="kode_satker" id="kode_satker" placeholder="Contoh: 0102" required
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
                        <input type="text" name="nama_satker" placeholder="Contoh: Biro Kepegawaian" required
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

                    <select name="wilayah_id" id="wilayah_id" onchange="filterParent()" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">Pilih wilayah</option>
                        @foreach ($wilayahs as $w)
                            <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>

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
                            @foreach ($allSatkers as $p)
                                <option value="{{ $p->id }}" data-eselon="{{ $p->jenis_satker_id }}">
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
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm"
                onclick="toggleModal('modalDetailSatker')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-start bg-white">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Detail Satuan Kerja</h3>
                        <p class="text-sm text-slate-500">Informasi lengkap satuan kerja dan daftar pejabat</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalDetailSatker')"
                        class="text-slate-400 hover:text-slate-600 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-8 bg-white">
                    {{-- Info Grid --}}
                    <div class="grid grid-cols-3 gap-8 mb-10">
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
                                    class="bg-blue-900 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">-</span>
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

                    {{-- Filter Pejabat --}}
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-filter text-slate-400 text-sm"></i>
                            <h4 class="text-sm font-bold text-slate-700">Filter Pejabat</h4>
                        </div>

                        {{-- Button Tambah Penugasan diletakkan di sini (Diatas Table) --}}
                        <button type="button" onclick="openModalPenugasanDariDetail()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-blue-100">
                            <i class="fas fa-user-plus mr-2"></i>
                            Tambah Penugasan
                        </button>
                    </div>

                    {{-- Row Filter Selects --}}
                    <div class="grid grid-cols-6 gap-3 mb-8">
                        <div class="col-span-4 relative">

                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search text-slate-400 text-xs"></i>
                            </span>
                            <input type="text" id="searchUserDetail" placeholder="Cari Nama / NIP..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs outline-none focus:border-blue-400 transition">

                        </div>
                        <select
                            class="col-span-2 bg-slate-50 border border-slate-200 rounded-lg text-xs p-2 outline-none focus:border-blue-400">
                            <option>Semua Periode</option>
                        </select>
                    </div>

                    {{-- Table Pejabat --}}
                    <div class="border border-gray-100 rounded-xl">
                        <div class="overflow-x-auto">
                            <table class="min-w-[1000px] w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-3">No</th>
                                        <th class="px-4 py-3">Penugasan ID</th>
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
                <div class="px-8 py-5 bg-slate-50/50 border-t border-gray-100 flex justify-between items-center">
                    <span id="detail_user_count" class="text-xs text-slate-400">Menampilkan 0 pejabat</span>
                    <div class="flex space-x-3">
                        <button type="button" onclick="toggleModal('modalDetailSatker')"
                            class="px-6 py-2 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg hover:bg-blue-100 transition">
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
                <form action="{{ route('admin.penugasan.store') }}" method="POST">
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
                            <label class="block text-xs font-bold text-slate-700 uppercase">User / Pegawai</label>

                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <i
                                        class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="search_nip" placeholder="Masukkan NIP Pegawai..."
                                        class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                </div>
                                <button type="button" onclick="searchPegawaiByNip()" id="btn_search_nip"
                                    class="px-4 py-2 bg-[#112D4E] text-white rounded-lg hover:bg-blue-900 transition shadow-sm flex items-center justify-center min-w-[45px]">
                                    <i class="fas fa-sync-alt" id="icon_search"></i>
                                </button>
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
                title: "{{ session('success') }}"
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif


        // Notifikasi Error (Validasi atau Custom Error)
        @if ($errors->any())
            Toast.fire({
                icon: 'error',
                title: "Terjadi kesalahan!",
                text: "{{ $errors->first() }}"
            });
        @endif

        // Fungsi Toggle Modal (Sudah ada)
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                if (modal.classList.contains('hidden')) {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                } else {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
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
                hiddenKodeInput.value = satkerKode; // 🔥 tambahkan ini

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

            const roleSelect = document.getElementById('role_select');
            const jenisContainer = document.getElementById('container_jenis_penugasan');
            const jenisSelect = document.getElementById('jenis_penugasan_select');

            function toggleJenisPenugasan() {

                let selectedText = roleSelect.options[roleSelect.selectedIndex]?.text?.toLowerCase();

                if (selectedText && selectedText.includes('admin satker')) {

                    jenisContainer.style.display = 'none';
                    jenisSelect.removeAttribute('required');
                    jenisSelect.value = '';

                    // jika pakai TomSelect
                    if (jenisSelect.tomselect) {
                        jenisSelect.tomselect.clear();
                    }

                } else {

                    jenisContainer.style.display = '';
                    jenisSelect.setAttribute('required', 'required');
                }
            }

            roleSelect.addEventListener('change', toggleJenisPenugasan);

            // Jalankan saat load pertama
            toggleJenisPenugasan();
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

            // Efek Loading
            btn.disabled = true;
            icon.classList.add('fa-spin');
            nipInput.classList.add('bg-slate-50');

            try {
                const response = await fetch(`https://ropegdev.kemenag.go.id/simsdm/pegawai/search?nip=${nip}`);
                if (!response.ok) throw new Error('Gagal menghubungi server');

                const result = await response.json();
                console.log("Isi Data API:", result);

                // Perbaikan: Masuk ke result.data.data sesuai struktur log konsol Anda
                if (result.success && result.data && result.data.data) {
                    const d = result.data.data;

                    // 1. Ambil Nama Lengkap atau Nama Biasa
                    const namaValid = d.NAMA_LENGKAP || d.NAMA;

                    // 2. Set Value ke Input Readonly
                    resNama.value = namaValid;

                    // 3. Set Info Tambahan
                    resInfo.innerText = `${d.GOL_RUANG || '-'} • ${d.TAMPIL_JABATAN || d.LEVEL_JABATAN || '-'}`;

                    // 4. Set Hidden Input untuk disubmit ke Database
                    document.getElementById('hidden_nip').value = d.NIP_BARU || d.NIP || nip;
                    document.getElementById('hidden_nama').value = namaValid;

                    Toast.fire({
                        icon: 'success',
                        title: 'Pegawai terverifikasi!',
                        text: namaValid
                    });
                } else {
                    // Jika result.data.data tidak ada
                    throw new Error(result.message || 'Format data API tidak sesuai atau NIP tidak ditemukan');
                }
            } catch (error) {
                console.error("Search Error:", error);

                // Reset jika error
                resNama.value = "";
                resInfo.innerText = "";
                document.getElementById('hidden_nip').value = "";
                document.getElementById('hidden_nama').value = "";

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

        // FUNGSI BARU: Auto Generate Kode Satker
        /**
         * Fungsi untuk menggabungkan semua potongan input di dalam kode_container
         * menjadi satu string utuh di input readonly "kode_satker_full"
         */
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

        /**
         * Event Listener untuk memantau perubahan input manual
         * (Jika user mengubah angka di kolom yang tidak readonly)
         */
        document.getElementById('kode_container').addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT') {
                updateFullCode();
            }
        });

        /**
         * Fungsi Generate Kode dari Server
         */
        async function generateSatkerCode(event) {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');

            const jenisId = document.getElementById('jenis_satker_id').value;
            const parentId = document.getElementById('parent_satker_id').value;
            const jabatanId = document.getElementById('jabatan_id').value;

            // Validasi awal
            if (!jenisId || (jenisId !== "1" && !parentId)) {
                Toast.fire({
                    icon: 'warning',
                    title: !jenisId ?
                        'Pilih Jenis Satker Terlebih Dahulu!' : 'Pilih Satker Induk Terlebih Dahulu!'
                });
                return;
            }

            // Loading state
            const originalIconClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            btn.disabled = true;
            btn.classList.add('opacity-75');

            try {
                const queryParams = new URLSearchParams({
                    jenis_id: jenisId,
                    parent_id: parentId,
                    jabatan_id: jabatanId
                });

                const [response] = await Promise.all([
                    fetch(`/admin/satker/generate-code?${queryParams.toString()}`),
                    new Promise(resolve => setTimeout(resolve, 600)) // Delay halus untuk UX
                ]);

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Gagal generate kode');
                }

                const fullCode = data.code;
                const container = document.getElementById('kode_container');
                const parentSelect = document.getElementById('parent_satker_id');

                // Hapus input lama di container kecuali tombol magic
                container.querySelectorAll('input').forEach(el => el.remove());

                // ==========================================
                // LOGIC PECAH KODE (ESELON 2++)
                // ==========================================
                if (jenisId !== "1") {
                    const selectedOption = parentSelect.options[parentSelect.selectedIndex];
                    const parentText = selectedOption?.text || '';
                    const parentKode = parentText.split(' - ')[0].trim();

                    const prefixLength = parentKode.length;
                    const prefix = fullCode.slice(0, prefixLength);
                    const middle = fullCode.slice(prefixLength, prefixLength + 2);
                    const suffix = fullCode.slice(prefixLength + 2);

                    let htmlInputs = `
    <input type="text" value="${prefix}" readonly
        class="w-20 px-3 py-2 bg-slate-200 border border-slate-200 rounded-xl text-sm text-center font-semibold text-slate-600 cursor-not-allowed">

    <input type="text" value="${middle}" required
        class="w-20 px-3 py-2 bg-white border border-blue-300 rounded-xl text-sm text-center focus:ring-2 focus:ring-blue-500/20 outline-none shadow-sm">
    `;

                    if (jabatanId && suffix.length > 0) {
                        htmlInputs += `
    <input type="text" value="${suffix}" readonly
        class="w-20 px-3 py-2 bg-slate-200 border border-slate-200 rounded-xl text-sm text-center font-semibold text-slate-600 cursor-not-allowed">
    `;
                    }
                    container.insertAdjacentHTML('afterbegin', htmlInputs);
                }
                // ==========================================
                // LOGIC PECAH KODE (ESELON 1)
                // ==========================================
                else {
                    if (jabatanId && jabatanId !== "") {
                        const prefix = fullCode.slice(0, 2);
                        const suffix = fullCode.slice(2);

                        container.insertAdjacentHTML('afterbegin', `
    <input type="text" value="${prefix}" required
        class="w-20 px-3 py-2 bg-white border border-blue-300 rounded-xl text-sm text-center focus:ring-2 focus:ring-blue-500/20 outline-none shadow-sm">
    <input type="text" value="${suffix}" readonly
        class="w-20 px-3 py-2 bg-slate-200 border border-slate-200 rounded-xl text-sm text-center font-semibold text-slate-600 cursor-not-allowed">
    `);
                    } else {
                        container.insertAdjacentHTML('afterbegin', `
    <input type="text" value="${fullCode}" required
        class="flex-1 px-4 py-2.5 bg-white border border-blue-300 rounded-xl text-sm outline-none focus:ring-2 focus:ring-blue-500/20 shadow-sm">
    `);
                    }
                }

                // Jalankan penggabungan otomatis ke kolom FINAL
                updateFullCode();

            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: error.message
                });
            } finally {
                icon.className = originalIconClass;
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
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

        console.log("Daftar Parent Options:", allParentOptions);
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
            const controlSelect = document.getElementById('parent_satker_id');

            if (jenisId === "1" || jenisId === "") {
                parentContainer.classList.add('hidden');
                control.clear();
                return;
            }

            parentContainer.classList.remove('hidden');
            const targetParentEselon = parseInt(jenisId) - 1;

            // Filter data dari variabel allParentOptions yang sudah kita buat di atas
            const filteredData = allParentOptions.filter(opt => opt.eselon === targetParentEselon);

            control.clearOptions();
            filteredData.forEach(opt => {
                control.addOption({
                    value: opt.id,
                    text: opt.text,
                    // Simpan periode di sini agar TomSelect tidak membuangnya
                    periode: opt.periode
                });
            });
            control.refreshOptions(false);
        }

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
        function openEditSatkerModal(id, kode, nama, periode_id, jenis_id, parent_id, wilayah_id, keterangan, status) {
            const form = document.getElementById('formEditSatker');
            form.action = `/admin/satker/${id}`;

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

        async function openDetailModal(kode, nama, eselon, wilayah, status, id) {

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
            <td class="px-4 py-4"><div class="h-3 w-40 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-12 bg-slate-200 rounded"></div></td>
        </tr>
    `;

            tableBody.innerHTML = skeletonRow.repeat(3);

            // Tampilkan modal
            toggleModal('modalDetailSatker');

            // 4. Fetch berdasarkan SATKER ID
            try {

                const response = await fetch(`/admin/satker/users/${id}`);
                const users = await response.json();

                tableBody.innerHTML = '';

                if (users.length > 0) {

                    users.forEach((user, index) => {

                        const statusBadge = user.status_aktif == 1 ?
                            `<span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">
                                        AKTIF
                                </span>` :
                            `<span class="text-[10px] font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200">
                                        NON-AKTIF
                                </span>`;

                        const actionButton = user.status_aktif == 1 ?
                            `<button 
                                    onclick="unassignUser('${user.penugasan_id}')"
                                    class="text-red-600 hover:text-red-800 transition"
                                    title="Unassign">
                                    <i class="fas fa-trash text-sm"></i>
                            </button>` :
                            '-';

                        tableBody.innerHTML += `
                        <tr class="hover:bg-blue-50/50 transition duration-200">
                            <td class="px-4 py-3 text-slate-600">${index + 1}</td>
                            <td class="px-4 py-3 text-slate-600">${user.penugasan_id}</td>
                            <td class="px-4 py-3 font-semibold text-slate-700">${user.name}</td>
                            <td class="px-4 py-3 text-slate-500">${user.nip ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.email ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.jabatan ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.roles ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.jenis_penugasan ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.tanggal_mulai ?? '-'}</td>
                            <td class="px-4 py-3 text-slate-500">${user.tanggal_selesai ?? '-'}</td>
                            <td class="px-4 py-3">${statusBadge}</td>
                            <td class="px-4 py-3">${actionButton}</td>
                        </tr>
                    `;
                    });


                    document.getElementById('detail_user_count').innerText =
                        `Menampilkan ${users.length} pejabat`;

                } else {

                    tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-16 text-center">
                        <i class="fas fa-user-slash text-slate-300 text-3xl mb-3 block"></i>
                        <span class="text-slate-400 italic text-sm">
                            Tidak ada pejabat di satker ini
                        </span>
                    </td>
                </tr>`;

                    document.getElementById('detail_user_count').innerText =
                        'Menampilkan 0 pejabat';
                }

            } catch (error) {

                tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Gagal memuat data pejabat
                </td>
            </tr>`;
            }
        }

        async function unassignUser(penugasanId) {

            // Gunakan SweetAlert2 confirm
            const {
                isConfirmed
            } = await Swal.fire({
                title: 'Yakin ingin meng-unassign pegawai ini?',
                text: "Tindakan ini tidak bisa dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Unassign!',
                cancelButtonText: 'Batal'
            });

            if (!isConfirmed) return;

            try {
                const response = await fetch(`/admin/penugasan/unassign/${penugasanId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                // Cek status response
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Fetch error:', response.status, text);
                    Swal.fire('Error', `Server error: ${response.status}`, 'error');
                    return;
                }

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil di-unassign',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // reload detail modal
                    const satkerId = document.getElementById('detail_satker_id').value;
                    openDetailModal(
                        document.getElementById('detail_kode').innerText,
                        document.getElementById('detail_nama').innerText,
                        document.getElementById('detail_eselon').innerText,
                        document.getElementById('detail_wilayah').innerText,
                        1,
                        satkerId
                    );

                } else {
                    console.error('Response error:', result);
                    Swal.fire('Gagal', 'Gagal unassign', 'error');
                }

            } catch (error) {
                console.error('Fetch failed:', error);
                Swal.fire('Terjadi Kesalahan', `Tidak dapat memproses unassign: ${error}`, 'error');
            }

        }

        document.getElementById('searchUserDetail').addEventListener('keyup', function() {

            let keyword = this.value.toLowerCase();
            let rows = document.querySelectorAll('#detail_user_table_body tr');
            let visibleIndex = 1;
            let visibleCount = 0;

            rows.forEach(row => {

                // skip row kosong / pesan tidak ada data
                if (!row.dataset.name) return;

                let name = row.dataset.name;
                let nip = row.dataset.nip;

                if (name.includes(keyword) || nip.includes(keyword)) {

                    row.style.display = '';
                    row.querySelector('.row-number').innerText = visibleIndex++;
                    visibleCount++;

                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('detail_user_count').innerText =
                `Menampilkan ${visibleCount} pejabat`;

        });



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
        const userRoles = @json(auth()->user()->roles->pluck('key'));
        const isRestrictedRole =
            userRoles.includes('admin_satker') ||
            userRoles.includes('pejabat');


        function openTambahSubSatker(parentId, parentJenisId, wilayahId, periodeId) {

            const form = document.querySelector('#modalTambahSatker form');
            form.reset();

            toggleModal('modalTambahSatker');

            // 1. Set Wilayah
            const wilayahSelect = document.getElementById('wilayah_id');
            if (wilayahSelect) wilayahSelect.value = wilayahId;

            // 2. Set Periode (Otomatis mengikuti induk)
            const periodeSelect = document.getElementById('periode_id');
            const periodeContainer = document.getElementById('container_periode');

            if (periodeSelect) {
                periodeSelect.value = periodeId;
                // Opsional: Sembunyikan container periode karena sudah otomatis terisi dari induk
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

            // 3. Set Level Satker Otomatis
            if (jenisSelect.querySelector(`option[value="${nextLevel}"]`)) {
                jenisSelect.value = nextLevel;
            }

            // 4. Jalankan filter agar TomSelect terisi opsi yang sesuai
            filterParent();

            // 5. Set Parent Satker di TomSelect
            if (control) {
                control.setValue(parentId, true); // true agar tidak trigger event change yang aneh-aneh
            } else {
                parentSelect.value = parentId;
            }

            // =============================
            // HARD LOCK (Jika Restricted)
            // =============================
            if (isRestrictedRole) {
                // Lock Jenis Satker
                jenisSelect.value = nextLevel;
                jenisSelect.disabled = true;
                jenisSelect.style.pointerEvents = "none";

                // Lock Parent Satker
                if (control) {
                    control.setValue(parentId, true);
                    control.lock();
                    control.disable();
                    control.wrapper.style.pointerEvents = "none";
                } else {
                    parentSelect.value = parentId;
                    parentSelect.disabled = true;
                    parentSelect.style.pointerEvents = "none";
                }
            } else {
                // Balikkan ke kondisi normal jika bukan restricted
                jenisSelect.disabled = false;
                jenisSelect.style.pointerEvents = "auto";

                if (control) {
                    control.unlock();
                    control.enable();
                    control.wrapper.style.pointerEvents = "auto";
                } else {
                    parentSelect.disabled = false;
                    parentSelect.style.pointerEvents = "auto";
                }
            }

            if (typeof updateFullCode === "function") {
                updateFullCode();
            }
        }
    </script>
@endpush
