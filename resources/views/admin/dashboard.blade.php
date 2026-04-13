@extends('layouts.admin')

@section('title', 'Dashboard Executive')

@section('content')
    {{-- Header & Tombol Export --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 border-b border-gray-200 pb-5">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Executive</h2>
            <p class="text-sm text-slate-500">Ringkasan Analitik Satuan Kerja & Kepegawaian</p>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Filter Periode (Mencegah Data Dummy) --}}
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex items-center">
                <select name="periode_id" onchange="this.form.submit()" class="bg-white border border-gray-300 text-slate-700 text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm font-semibold">
                    @foreach ($periodes as $pe)
                        <option value="{{ $pe->id }}" {{ $activePeriodeId == $pe->id ? 'selected' : '' }}>
                            Periode: {{ $pe->nama_periode }}
                        </option>
                    @endforeach
                </select>
            </form>

            {{-- TOMBOL UNDUH LAPORAN EXCEL UNTUK ATASAN --}}
            <button type="button" onclick="toggleModal('modalDownloadLaporan')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2 whitespace-nowrap">
                <i class="fas fa-file-excel"></i>
                Unduh Rekap (Excel)
            </button>
        </div>
    </div>

    {{-- Kartu Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @foreach ([['label' => 'Total Satuan Kerja', 'value' => $stats['total_satker'], 'sub' => 'Unit organisasi aktif', 'color' => 'border-blue-800', 'icon' => 'fa-city'], ['label' => 'Total Wilayah', 'value' => $stats['total_wilayah'], 'sub' => 'Pusat, Provinsi, Kab/Kota', 'color' => 'border-orange-400', 'icon' => 'fa-location-dot'], ['label' => 'Total Pegawai', 'value' => $stats['total_pegawai'], 'sub' => 'Pegawai terdaftar', 'color' => 'border-green-500', 'icon' => 'fa-user-group'], ['label' => 'Penugasan Aktif', 'value' => $stats['penugasan_aktif'], 'sub' => 'Definitif, Plt, Plh', 'color' => 'border-blue-900', 'icon' => 'fa-user-check']] as $item)
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 {{ $item['color'] }} flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ $item['label'] }}</p>
                    <h3 class="text-3xl font-bold text-slate-800 my-1">{{ $item['value'] }}</h3>
                    <p class="text-[10px] text-slate-400">{{ $item['sub'] }}</p>
                </div>
                <div class="bg-slate-100 p-2 rounded-lg text-slate-600">
                    <i class="fas {{ $item['icon'] }}"></i>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Alert --}}
    @if ($satkerTanpaDefinitif > 0)
        <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex items-center space-x-4 mb-8">
            <div class="bg-orange-100 text-orange-500 w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-orange-800 leading-tight">Peringatan: Terdapat Kekosongan Kursi!</p>
                <p class="text-xs text-orange-700">Ada <span class="font-bold">{{ $satkerTanpaDefinitif }}</span> satuan kerja yang belum memiliki pejabat sama sekali pada periode ini. Silakan cek tabel di bawah.</p>
            </div>
        </div>
    @endif

    {{-- Charts Section (2 Grafik) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-bold text-slate-800 mb-6 flex items-center uppercase tracking-tight">
                <i class="fas fa-chart-line mr-3 text-blue-800"></i> Distribusi Satker per Eselon
            </h4>
            <div class="h-[250px] w-full"><canvas id="barChart"></canvas></div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-bold text-slate-800 mb-6 flex items-center uppercase tracking-tight">
                <i class="fas fa-chart-pie mr-3 text-blue-800"></i> Distribusi Jenis Penugasan
            </h4>
            <div class="h-[250px] w-full"><canvas id="donutChart"></canvas></div>
        </div>
    </div>

    {{-- Tabel Daftar Satker Kosong --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
        <div class="px-6 py-4 border-b border-gray-100 bg-red-50/50 flex justify-between items-center">
            <h3 class="text-sm font-bold text-red-800 flex items-center">
                <i class="fas fa-chair mr-2"></i> Daftar Satker Kosong (Belum Ada Pejabat)
            </h3>
            <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ $satkerTanpaDefinitif }} Satker</span>
        </div>
        
        <div class="overflow-x-auto max-h-[400px] overflow-y-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                    <tr class="text-slate-500 text-[11px] uppercase tracking-widest border-b border-gray-200">
                        <th class="px-6 py-3 font-bold">Kode Satker</th>
                        <th class="px-6 py-3 font-bold">Nama Satuan Kerja</th>
                        <th class="px-6 py-3 font-bold">Tingkat / Eselon</th>
                        <th class="px-6 py-3 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($satkerKosong as $kosong)
                        <tr class="hover:bg-red-50/30 transition">
                            <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $kosong->kode_satker }}</td>
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $kosong->nama_satker }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                    {{ $kosong->eselon ? $kosong->eselon->nama : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <button type="button" onclick="openDetailKosongModal('{{ $kosong->kode_satker }}', '{{ $kosong->nama_satker }}', '{{ $kosong->eselon ? $kosong->eselon->nama : '-' }}', '{{ $kosong->wilayah ? $kosong->wilayah->nama_wilayah : '-' }}', '{{ $kosong->status_aktif }}', '{{ route('admin.satker.index', ['periode_id' => $activePeriodeId, 'search' => $kosong->kode_satker]) }}')" class="text-xs font-bold text-blue-600 hover:text-blue-800 border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition inline-flex items-center">
                                    <i class="fas fa-eye mr-1"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-emerald-600 font-bold text-sm bg-emerald-50/30">
                                <i class="fas fa-check-circle text-2xl mb-2 block"></i>
                                Hebat! Seluruh Satuan Kerja sudah terisi pejabat Definitif.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Detail Satker Kosong --}}
    <div id="modalDetailKosong" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalDetailKosong')"></div>

            <div class="relative inline-block w-full max-w-[800px] overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                <div class="px-6 py-4 sm:px-8 sm:py-6 border-b border-gray-100 flex justify-between items-start bg-white sticky top-0 z-10">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-slate-800">Detail Satuan Kerja</h3>
                        <p class="text-xs sm:text-sm text-slate-500">Informasi satuan kerja yang kosong</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalDetailKosong')" class="text-slate-400 hover:text-slate-600 transition p-2">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="p-6 sm:p-8 bg-white">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-8 mb-8">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Satker</label>
                            <p id="detail_kode_kosong" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Eselon</label>
                            <div><span id="detail_eselon_kosong" class="bg-blue-900 text-white text-[9px] px-3 py-1 rounded-full font-bold uppercase inline-block">-</span></div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Satker</label>
                            <p id="detail_nama_kosong" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Wilayah</label>
                            <p id="detail_wilayah_kosong" class="text-sm font-bold text-slate-700">-</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                            <div id="detail_status_kosong_container"></div>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-100 rounded-xl p-8 text-center">
                        <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-slash text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-bold text-red-800 mb-2">Belum Ada Pejabat</h4>
                        <p class="text-sm text-red-600">Satuan kerja ini sama sekali belum memiliki pejabat (baik Definitif, Plt, maupun Plh) yang aktif pada periode ini.</p>
                        
                        <a id="btn_tambah_pejabat" href="#" class="mt-6 inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-external-link-alt mr-2"></i> Kelola di Menu Satker
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Pilihan Download Laporan --}}
    <div id="modalDownloadLaporan" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalDownloadLaporan')"></div>

            <div class="relative inline-block w-full max-w-[420px] overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-start bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Unduh Laporan</h3>
                        <p class="text-xs text-slate-500">Pilih jenis rekap data yang ingin diunduh</p>
                    </div>
                    <button type="button" onclick="toggleModal('modalDownloadLaporan')" class="text-slate-400 hover:text-slate-600 transition p-2">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="p-6 bg-slate-50">
                    <div class="grid grid-cols-1 gap-4">
                        {{-- Tombol Laporan Pejabat --}}
                        <a href="{{ route('admin.dashboard.export', ['periode_id' => $activePeriodeId, 'type' => 'pejabat']) }}" 
                           onclick="toggleModal('modalDownloadLaporan')"
                           class="flex items-center p-4 bg-white border border-blue-200 rounded-xl hover:bg-blue-50 transition shadow-sm group">
                            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-4 group-hover:scale-110 transition">
                                <i class="fas fa-user-tie text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Laporan Pejabat</h4>
                                <p class="text-xs text-slate-500">Data struktural: Definitif, Plt, Plh</p>
                            </div>
                        </a>

                        {{-- Tombol Laporan Admin --}}
                        <a href="{{ route('admin.dashboard.export', ['periode_id' => $activePeriodeId, 'type' => 'admin']) }}" 
                           onclick="toggleModal('modalDownloadLaporan')"
                           class="flex items-center p-4 bg-white border border-emerald-200 rounded-xl hover:bg-emerald-50 transition shadow-sm group">
                            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mr-4 group-hover:scale-110 transition">
                                <i class="fas fa-user-cog text-xl"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Laporan Admin</h4>
                                <p class="text-xs text-slate-500">Data operasional: Admin Satker, dll</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const eselonLabels = @json($eselonData->pluck('nama'));
        const eselonValues = @json($eselonData->pluck('total'));
        
        const penugasanLabels = @json($penugasanData->map(fn($item) => $item->nama . ': ' . $item->total)->values());
        const penugasanValues = @json($penugasanData->pluck('total'));

        // 1. Bar Chart Eselon
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: eselonLabels,
                datasets: [{ data: eselonValues, backgroundColor: '#1D4076', borderRadius: 4 }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1} } }
            }
        });

        // 2. Donut Chart Penugasan
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: penugasanLabels,
                datasets: [{ data: penugasanValues, backgroundColor: ['#1D4076', '#FBBF24', '#22C55E', '#0EA5E9'], borderWidth: 0, cutout: '70%' }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 10, font: { size: 10 } } } }
            }
        });

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
                document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
            }
        }

        function openDetailKosongModal(kode, nama, eselon, wilayah, status, urlSatker) {
            document.getElementById('detail_kode_kosong').innerText = kode;
            document.getElementById('detail_nama_kosong').innerText = nama;
            document.getElementById('detail_eselon_kosong').innerText = eselon;
            document.getElementById('detail_wilayah_kosong').innerText = wilayah;
            
            const statusContainer = document.getElementById('detail_status_kosong_container');
            statusContainer.innerHTML = (status == 1 || status == 'Aktif') ?
                '<span class="bg-emerald-500 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Aktif</span>' :
                '<span class="bg-slate-400 text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Non-Aktif</span>';

            const btnTambah = document.getElementById('btn_tambah_pejabat');
            if (btnTambah) {
                btnTambah.href = urlSatker;
            }

            toggleModal('modalDetailKosong');
        }
    </script>
@endpush