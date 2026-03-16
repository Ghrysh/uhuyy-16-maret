@extends('layouts.admin')

@section('title', 'Daftar Pegawai')

@push('styles')
    <style>
        @keyframes spin-custom {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-fast {
            animation: spin-custom 0.6s linear infinite;
        }

        #ajax-container {
            transition: all 0.3s ease;
        }

        .text-navy-custom {
            color: #112D4E;
        }

        .bg-navy-custom {
            background-color: #112D4E;
        }
    </style>
@endpush

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Data Kepegawaian</h2>
            <p class="text-sm text-slate-500">Kelola dan pantau informasi profil pegawai secara terpusat</p>
        </div>

        <div class="relative w-full md:w-80">
            <form action="{{ request()->fullUrl() }}" method="GET" id="search-form">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-sm"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl bg-white text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-navy-custom/20 focus:border-navy-custom transition-all"
                        placeholder="Cari Nama, NIP, atau Kode Satker...">
                </div>
            </form>
        </div>
    </div>

    <div id="ajax-container" class="relative min-h-[400px]">
        {{-- Loading Overlay --}}
        <div id="loading-overlay"
            class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-[2px] hidden rounded-2xl">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-[#112D4E] rounded-full animate-spin-fast"></div>
                <span class="mt-3 text-xs font-bold text-[#112D4E] tracking-widest uppercase">Memproses Data...</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="{{ route('admin.pegawai.update-satker') }}" method="POST" id="bulk-form">
                @csrf

                <div id="bulk-action-bar"
                    class="hidden px-6 py-3 bg-blue-50 border-b flex justify-between items-center transition-all">

                    <span class="text-sm font-semibold text-slate-700">
                        <span id="selected-count">0</span> pegawai dipilih
                    </span>

                    <button type="button" onclick="openModal()"
                        class="px-4 py-2 bg-navy-custom text-white text-sm rounded-lg hover:opacity-90 transition">
                        Tambah Kode Satker
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50/50 text-slate-500 text-[11px] uppercase tracking-widest border-b border-gray-100">
                                <th class="px-6 py-4">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th class="px-6 py-4 font-bold">NIP</th>
                                <th class="px-6 py-4 font-bold">Nama</th>
                                <th class="px-6 py-4 font-bold">Satker Lama</th>
                                <th class="px-6 py-4 font-bold">Satker Baru</th>
                                <th class="px-6 py-4 font-bold">Jabatan</th>
                                <th class="px-6 py-4 font-bold">Golongan</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($pegawais as $pegawai)
                                <tr class="hover:bg-slate-50/50 transition group">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $pegawai->id }}"
                                            class="row-checkbox">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-navy-custom/10 flex items-center justify-center text-navy-custom border border-navy-custom/20">
                                                <i class="fas fa-user text-[10px]"></i>
                                            </div>
                                            <div>
                                                <span
                                                    class="text-[12px] font-mono text-slate-600">{{ $pegawai->nip_baru ?? $pegawai->nip }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="block text-[13px] font-bold text-slate-700 leading-tight">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-[300px]">
                                            <span
                                                class="block text-[12px] font-semibold text-navy-custom leading-tight line-clamp-1">{{ $pegawai->kode_satuan_kerja ?? '-' }}</span>
                                            <span
                                                class="block text-[11px] text-slate-400 mt-0.5 line-clamp-1 italic">{{ $pegawai->satker_4 ?? $pegawai->grup_satuan_kerja }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $satker = optional(optional($pegawai->user2)->satker);
                                        @endphp

                                        @if ($satker && $satker->kode_satker)
                                            <div class="flex flex-col">
                                                <span class="text-[12px] font-mono font-semibold text-navy-custom">
                                                    {{ $satker->kode_satker }}
                                                </span>
                                                <span class="text-[11px] text-slate-500">
                                                    {{ $satker->nama_satker }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-[12px] text-slate-400 italic">
                                                Belum diset
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                            <span
                                                class="block text-[12px] font-semibold text-navy-custom leading-tight line-clamp-1">{{ $pegawai->tampil_jabatan }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[12px] font-medium text-slate-700">{{ $pegawai->pangkat }}</span>
                                            <span
                                                class="text-[10px] text-slate-400 uppercase font-bold">{{ $pegawai->gol_ruang }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-bold rounded-full uppercase">
                                            {{ $pegawai->status_pegawai ?? 'Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users-slash text-slate-200 text-4xl mb-3"></i>
                                            <p class="text-slate-400 text-sm">Tidak ada data pegawai ditemukan.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Wrapper --}}
                <div class="px-6 py-4 bg-gray-50/30 border-t border-gray-100 pagination-ajax-wrapper">
                    {{ $pegawais->links() }}
                </div>
            </form>
        </div>

        <div class="mt-10 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-bold text-slate-800">
                    Riwayat Bulk Update Satker
                </h3>

                <button onclick="loadBulking(event)"
                    class="px-4 py-2 bg-navy-custom text-white rounded-lg hover:opacity-90 transition flex items-center gap-2"
                    title="Refresh">

                    <i class="fas fa-sync-alt text-sm"></i>
                    <span class="text-sm">Refresh</span>

                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-50 text-xs uppercase text-slate-400 border-b">
                        <tr>
                            <th class="px-6 py-4 w-10"></th>
                            <th class="px-6 py-4 text-left">Tanggal</th>
                            <th class="px-6 py-4 text-left">Dibuat Oleh</th>
                            <th class="px-6 py-4 text-left">Total Data</th>
                            <th class="px-6 py-4 text-left">Tipe</th>
                        </tr>
                    </thead>
                    <tbody id="bulking-table-body">
                        @include('admin.pegawai.partials.bulking-table')
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $bulkings->links() }}
            </div>
        </div>
    </div>

    <div id="modalSatker" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">

        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6 relative">

            <h3 class="text-lg font-bold text-slate-800 mb-4">
                Update Kode Satker
            </h3>

            @php
                $user = auth()->user();
            @endphp

            @if ($user->satker_id)
                {{-- Jika user punya satker --}}
                <input type="hidden" name="satker_id" value="{{ $user->satker_id }}" form="bulk-form">

                <div class="mb-4">
                    <label class="text-sm font-semibold text-slate-600 block mb-1">
                        Satker
                    </label>
                    <div class="px-4 py-2 bg-gray-100 rounded-lg text-sm text-slate-700">
                        {{ $user->satker->nama_satker ?? 'Satker Anda' }}
                    </div>
                </div>
            @else
                {{-- Jika user tidak punya satker --}}
                <div class="mb-4">
                    <label class="text-sm font-semibold text-slate-600 block mb-1">
                        Pilih Satker
                    </label>

                    <select id="satkerSelect" name="satker_id" form="bulk-form" required class="w-full">

                        <option value="">-- Pilih Satker --</option>

                        @foreach ($satkers as $satker)
                            <option value="{{ $satker->id }}">
                                {{ $satker->kode_satker }} - {{ $satker->nama_satker }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-sm rounded-lg">
                    Batal
                </button>

                <button type="submit" form="bulk-form"
                    class="px-4 py-2 bg-navy-custom text-white text-sm rounded-lg hover:opacity-90">
                    Simpan
                </button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                confirmButtonColor: '#112D4E'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonColor: '#112D4E'
            });
        </script>
    @endif
    <script>
        const searchInput = document.querySelector('input[name="search"]');
        let searchTimeout;

        // 1. Handle Typing Search (Debounce 500ms)
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('search', searchTerm);
                url.searchParams.set('page', 1); // Reset ke halaman 1 saat cari baru

                performAjaxUpdate(url.toString());

                // Update URL di browser tanpa reload
                window.history.pushState({}, '', url.toString());
            }, 500);
        });

        // 2. Handle Pagination Click
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination-ajax-wrapper a');
            if (link) {
                e.preventDefault();
                const targetUrl = link.getAttribute('href');
                if (targetUrl && targetUrl !== '#') {
                    performAjaxUpdate(targetUrl);
                    window.history.pushState({}, '', targetUrl);
                }
            }
        });

        async function performAjaxUpdate(url) {
            const container = document.getElementById('ajax-container');
            const overlay = document.getElementById('loading-overlay');

            if (overlay) overlay.classList.remove('hidden');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            try {
                const response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                if (!response.ok) throw new Error("Gagal mengambil data.");

                const htmlResponse = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlResponse, "text/html");
                const newInnerContent = doc.getElementById('ajax-container').innerHTML;

                container.innerHTML = newInnerContent;
                container.style.opacity = '1';

                // Re-init overlay reference karena innerHTML di-replace
                const newOverlay = document.getElementById('loading-overlay');
                if (newOverlay) newOverlay.classList.add('hidden');

            } catch (error) {
                console.error("AJAX Error:", error);
                alert("Terjadi kesalahan saat memuat data.");
                container.style.opacity = '1';
            } finally {
                container.style.pointerEvents = 'auto';
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target.id === 'check-all') {
                const checked = e.target.checked;
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = checked;
                });
            }
        });

        function updateBulkBar() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const bar = document.getElementById('bulk-action-bar');
            const count = document.getElementById('selected-count');

            if (checked.length > 0) {
                bar.classList.remove('hidden');
                count.textContent = checked.length;
            } else {
                bar.classList.add('hidden');
            }
        }

        document.addEventListener('change', function(e) {

            if (e.target.classList.contains('row-checkbox')) {
                updateBulkBar();
            }

            if (e.target.id === 'check-all') {
                const checked = e.target.checked;
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = checked;
                });
                updateBulkBar();
            }
        });

        function openModal() {
            document.getElementById('modalSatker').classList.remove('hidden');
            document.getElementById('modalSatker').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('modalSatker').classList.add('hidden');
            document.getElementById('modalSatker').classList.remove('flex');
        }
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <script>
        new TomSelect("#satkerSelect", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            searchField: ['text'],
            placeholder: "Cari kode atau nama satker..."
        });
    </script>
    <script>
        function loadBulking(event) {

            const btn = event.currentTarget;
            const icon = btn.querySelector('i');

            icon.classList.add('fa-spin');

            fetch('{{ route('admin.bulking.list') }}')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('bulking-table-body').innerHTML = html;
                })
                .finally(() => {
                    setTimeout(() => {
                        icon.classList.remove('fa-spin');
                    }, 500);
                });
        }

        function toggleDetail(id) {
            const row = document.getElementById('detail-' + id);
            const icon = document.getElementById('icon-' + id);

            if (!row) return;

            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                row.classList.add('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }
    </script>
@endpush
