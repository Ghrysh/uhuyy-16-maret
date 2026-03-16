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
    <div x-data="{ search: '' }">
        {{-- Header Section --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Satuan Kerja</h2>
                <p class="text-sm text-slate-500">Kelola data satuan kerja Kementerian Agama</p>
            </div>
        </div>

        {{-- Search Section --}}
        <div class="flex items-center justify-end mb-4">
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-slate-400 text-xs"></i>
                </span>
                <input type="text" x-model="search" placeholder="Cari kode atau nama..."
                    class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition shadow-sm">
            </div>
        </div>

        {{-- Main Content Container --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- View: Pohon Hierarki --}}
            <div class="p-6 space-y-2">
                @forelse ($satkers as $satker)
                    @include('admin.satker._item_hirarki', ['item' => $satker])
                @empty
                    <div class="py-12 text-center">
                        <i class="fas fa-folder-open text-slate-300 text-3xl mb-3 block"></i>
                        <p class="text-slate-400 text-sm italic">Belum ada data satuan kerja.</p>
                    </div>
                @endforelse
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

@endsection

@push('scripts')
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

        // FUNGSI BARU: Auto Generate Kode Satker
        async function generateSatkerCode(event) {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            const inputKode = document.querySelector('input[name="kode_satker"]');
            const jenisId = document.getElementById('jenis_satker_id').value;
            const parentId = document.getElementById('parent_satker_id').value;

            if (!jenisId || (jenisId !== "1" && !parentId)) {
                Toast.fire({
                    icon: 'warning',
                    title: !jenisId ? 'Pilih Jenis Satker Terlebih Dahulu!' :
                        'Pilih Satker Induk Terlebih Dahulu!'
                });
                return;
            }

            // --- START LOADING STATE ---
            const originalIconClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin'; // Memaksa icon berputar
            btn.disabled = true;
            btn.classList.add('opacity-75');

            try {
                // 1. Jalankan Fetch dan Timer secara bersamaan
                // Kita paksa loading minimal 600ms agar animasi putaran terlihat jelas
                const [response] = await Promise.all([
                    fetch(`/admin/satker/generate-code?jenis_id=${jenisId}&parent_id=${parentId}`),
                    new Promise(resolve => setTimeout(resolve, 600))
                ]);

                const data = await response.json();

                if (response.ok) {
                    inputKode.value = data.code;
                    inputKode.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
                    setTimeout(() => {
                        inputKode.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50');
                    }, 1000);
                } else {
                    throw new Error(data.error || 'Gagal generate kode');
                }
            } catch (error) {
                Toast.fire({
                    icon: 'error',
                    title: error.message
                });
            } finally {
                // --- END LOADING STATE ---
                icon.className = originalIconClass;
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
        }
    </script>
    <script>
        var control = new TomSelect('#parent_satker_id', {
            render: {
                option: function(data, escape) {
                    return `
                <div class="py-1">
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
            const parentSelect = document.getElementById('parent_satker_id');
            const options = parentSelect.querySelectorAll('option');

            // Jika Jenis Satker belum dipilih atau memilih Eselon 1, sembunyikan Satker Induk
            if (jenisId === "" || jenisId === "1") {
                parentContainer.classList.add('hidden');
                parentSelect.value = "";
                return;
            }

            // Tampilkan container induk untuk Eselon 2-5
            parentContainer.classList.remove('hidden');

            // Logika: Satker Induk harus 1 tingkat di atas level yang dipilih
            // Contoh: Pilih Eselon 3 (ID: 3), maka yang muncul adalah Eselon 2 (ID: 2)
            const targetParentEselon = parseInt(jenisId) - 1;

            let hasMatch = false;
            options.forEach(option => {
                // Abaikan opsi default "Pilih Satker Induk"
                if (option.value === "") return;

                const optionEselon = parseInt(option.getAttribute('data-eselon'));

                // FILTER HANYA BERDASARKAN ESELON
                if (optionEselon === targetParentEselon) {
                    option.style.display = 'block';
                    hasMatch = true;
                } else {
                    option.style.display = 'none';
                }
            });

            // Jika tidak ada satker yang cocok di level atasnya, reset pilihan
            if (!hasMatch) {
                parentSelect.value = "";
            }
        }

        function openEditSatkerModal(id, kode, nama, jenis_id, parent_id, wilayah_id, keterangan, status) {
            const form = document.getElementById('formEditSatker');
            form.action = `/admin/satker/${id}`; // Pastikan route update benar

            document.getElementById('edit_kode_satker').value = kode;
            document.getElementById('edit_nama_satker').value = nama;
            document.getElementById('edit_jenis_satker_id').value = jenis_id;
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
            document.getElementById('detail_kode').innerText = kode;
            document.getElementById('detail_nama').innerText = nama;
            document.getElementById('detail_eselon').innerText = eselon;
            document.getElementById('detail_wilayah').innerText = wilayah;

            // 2. Render Status Badge
            const statusContainer = document.getElementById('detail_status_container');
            statusContainer.innerHTML = (status == 1 || status == 'Aktif') ?
                '<span class="bg-emerald-500 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Aktif</span>' :
                '<span class="bg-slate-400 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Non-Aktif</span>';

            // 3. Tampilkan Efek Loading (Skeleton)
            const tableBody = document.getElementById('detail_user_table_body');
            const skeletonRow = `
        <tr class="animate-pulse">
            <td class="px-4 py-4"><div class="h-3 w-4 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-32 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-24 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-16 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-40 bg-slate-200 rounded"></div></td>
            <td class="px-4 py-4"><div class="h-3 w-12 bg-slate-200 rounded"></div></td>
        </tr>
    `;
            // Masukkan 3 baris loading
            tableBody.innerHTML = skeletonRow.repeat(3);

            // Tampilkan modal terlebih dahulu agar user melihat loading-nya
            toggleModal('modalDetailSatker');

            // 4. Ambil Data via AJAX
            try {
                const response = await fetch(`/admin/satker/users/${kode}`);
                const users = await response.json();

                // Berikan sedikit delay agar efek transisi loading terlihat halus (opsional)
                // await new Promise(resolve => setTimeout(resolve, 500));

                tableBody.innerHTML = '';

                if (users.length > 0) {
                    users.forEach((user, index) => {
                        tableBody.innerHTML += `
                    <tr class="hover:bg-blue-50/50 transition duration-200">
                        <td class="px-4 py-3 text-slate-600">${index + 1}</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">${user.name}</td>
                        <td class="px-4 py-3 text-slate-500">${user.nip ?? '-'}</td>
                        <td class="px-4 py-3 text-slate-500">${user.golongan ?? '-'}</td>
                        <td class="px-4 py-3 text-slate-500">${user.jabatan ?? '-'}</td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">AKTIF</span>
                        </td>
                    </tr>
                `;
                    });
                    document.getElementById('detail_user_count').innerText = `Menampilkan ${users.length} pejabat`;
                } else {
                    tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-16 text-center">
                        <i class="fas fa-user-slash text-slate-300 text-3xl mb-3 block"></i>
                        <span class="text-slate-400 italic text-sm">Tidak ada pejabat di satker ini</span>
                    </td>
                </tr>`;
                    document.getElementById('detail_user_count').innerText = 'Menampilkan 0 pejabat';
                }
            } catch (error) {
                tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i> Gagal memuat data pejabat
                </td>
            </tr>`;
            }
        }

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
@endpush
