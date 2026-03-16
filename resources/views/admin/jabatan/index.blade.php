@extends('layouts.admin')

@section('title', 'Master Jabatan')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Master Jabatan Fungsional</h2>
            <p class="text-sm text-slate-500">Kelola data jabatan</p>
        </div>
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
                                    <i
                                        class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-blue-600"></i>
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
                                    <i
                                        class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }} text-blue-600"></i>
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
                                    <div
                                        class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-3 text-blue-600">
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
                            {{-- <td class="px-6 py-4 text-center">
                                <span class="text-xs text-slate-600">{{ $item->jenisSatker->nama ?? '-' }}</span>
                            </td> --}}
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada data jabatan.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="notFoundRow" class="hidden">
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">Data tidak ditemukan.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-6 py-4 border-t border-gray-100">
            {{ $jabatans->links() }}
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
    <script>
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
        }

        // Fungsi untuk menangani visibilitas Eselon
        function filterEselon(type) {
            const jenisSelect = document.getElementById(type === 'tambah' ? 'tambah_jenis' : 'edit_jenis');
            const eselonContainer = document.getElementById(type === 'tambah' ? 'container_eselon_tambah' :
                'container_eselon_edit');

            // Cek jika teks yang dipilih adalah "Struktural" atau ID nya adalah 1 (sesuaikan dengan database Anda)
            // Di sini saya menggunakan pengecekan teks agar lebih aman jika ID berubah
            const selectedText = jenisSelect.options[jenisSelect.selectedIndex].text.toLowerCase();

            if (selectedText.includes('struktural')) {
                eselonContainer.classList.remove('hidden');
            } else {
                eselonContainer.classList.add('hidden');
                // Reset nilai eselon jika bukan struktural
                const eselonSelect = eselonContainer.querySelector('select');
                eselonSelect.value = "";
            }
        }

        function toggleModal(id) {
            const m = document.getElementById(id);
            m.classList.toggle('hidden');
            document.body.style.overflow = m.classList.contains('hidden') ? 'auto' : 'hidden';

            // Reset filter saat modal tambah dibuka
            if (id === 'modalTambahJabatan' && !m.classList.contains('hidden')) {
                filterEselon('tambah');
            }
        }

        function openEditModal(id, kode, nama, jenis, fungsional_id) {
            const form = document.getElementById('formEditJabatan');
            form.action = `/admin/jabatan/${id}`;

            // Set value ke input hidden & text
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_kode').value = kode;

            // Set value untuk ID Jenis (Hidden)
            document.getElementById('edit_jenis_jabatan_id').value = jenis;

            // Set value untuk Dropdown Jabatan Fungsional
            document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";

            toggleModal('modalEditJabatan');
        }

        function confirmDelete(id, nama) {
            // Set nama jabatan di teks modal
            document.getElementById('delete_nama_display').innerText = nama;

            // Atur aksi klik pada tombol "Hapus" di dalam modal
            const btnConfirm = document.getElementById('btnConfirmDelete');
            btnConfirm.onclick = function() {
                document.getElementById('delete-form-' + id).submit();
            };

            // Tampilkan modal
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
                // Abaikan baris "Data tidak ditemukan" dan baris "Belum ada data"
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

            // Tampilkan pesan jika tidak ada data yang cocok
            if (filter !== "" && !hasVisibleRow) {
                notFoundRow.classList.remove('hidden');
            } else {
                notFoundRow.classList.add('hidden');
            }
        }

        function updateKodeJabatan() {
            // Ambil nilai kode urut yang diinput user
            const kodeUrut = document.getElementById('tambah_kode_urut').value;

            // Ambil select element fungsional
            const selectFungsional = document.getElementById('tambah_fungsional_id');
            const inputFinal = document.getElementById('tambah_kode_jabatan');

            // Ambil data-kode dari option yang dipilih
            const selectedOption = selectFungsional.options[selectFungsional.selectedIndex];
            const kodeJenjang = selectedOption.getAttribute('data-kode') || "";

            // Gabungkan (Contoh: 801 + 11 = 80111)
            // Jika kode urut kosong, biarkan kosong atau beri placeholder
            if (kodeUrut) {
                inputFinal.value = kodeUrut + kodeJenjang;
            } else {
                inputFinal.value = "";
            }
        }

        // Opsional: Jalankan saat modal dibuka untuk memastikan sinkronisasi
        function openTambahJabatan() {
            document.getElementById('tambah_fungsional_id').value = "";
            document.getElementById('tambah_kode_jabatan').value = "{{ $nextBaseCode }}";
            toggleModal('modalTambahJabatan');
        }

        // Fungsi untuk sinkronisasi Kode di Modal Edit
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

        // Fungsi saat tombol Pen-To-Square diklik
        function openEditModal(id, kodeFull, nama, jenis, fungsional_id) {
            const form = document.getElementById('formEditJabatan');
            form.action = `/admin/jabatan/${id}`;

            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_jenis_jabatan_id').value = jenis;
            document.getElementById('edit_jabatan_fungsional_id').value = fungsional_id || "";

            // LOGIKA MEMUTUS KODE: Ambil 3 digit pertama untuk urutan (misal 80111 -> 801)
            // Kita asumsikan kode urut selalu 3 digit di depan
            const kodeUrut = kodeFull.substring(0, 3);
            document.getElementById('edit_kode_urut').value = kodeUrut;
            document.getElementById('edit_kode_final').value = kodeFull;

            toggleModal('modalEditJabatan');
        }
    </script>
@endpush
