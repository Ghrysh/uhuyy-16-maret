@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Header & Stats Cards Tetap Sama --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard</h2>
        <p class="text-sm text-slate-500">Ringkasan data Satuan Kerja Kementerian Agama</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        {{-- Loop stats cards Anda di sini --}}
        @foreach ([['label' => 'Total Satuan Kerja', 'value' => $stats['total_satker'], 'sub' => 'Unit organisasi aktif', 'color' => 'border-blue-800', 'icon' => 'fa-city'], ['label' => 'Total Wilayah', 'value' => $stats['total_wilayah'], 'sub' => 'Pusat, Provinsi, Kab/Kota', 'color' => 'border-orange-400', 'icon' => 'fa-location-dot'], ['label' => 'Total Pegawai', 'value' => $stats['total_pegawai'], 'sub' => 'Pegawai terdaftar', 'color' => 'border-green-500', 'icon' => 'fa-user-group'], ['label' => 'Penugasan Aktif', 'value' => $stats['penugasan_aktif'], 'sub' => 'Definitif, Plt, Plh, Admin', 'color' => 'border-blue-900', 'icon' => 'fa-user-check']] as $item)
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
            <div
                class="bg-orange-100 text-orange-500 w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-orange-800 leading-tight">Perhatian</p>
                <p class="text-xs text-orange-700">Terdapat <span class="font-bold">{{ $satkerTanpaDefinitif }}</span>
                    satuan kerja yang belum memiliki pejabat definitif</p>
            </div>
        </div>
    @endif

    {{-- Charts Section --}}
    {{-- Charts Section: Diubah menjadi grid 2 kolom agar tidak terlalu lebar --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- Bar Chart Card --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-bold text-slate-800 mb-6 flex items-center uppercase tracking-tight">
                <i class="fas fa-chart-line mr-3 text-blue-800"></i> Distribusi Satker per Eselon
            </h4>
            {{-- Tinggi ditingkatkan ke 300px agar batang chart memiliki ruang --}}
            <div class="h-[300px] w-full">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        {{-- Donut Chart Card --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h4 class="text-xs font-bold text-slate-800 mb-6 flex items-center uppercase tracking-tight">
                <i class="fas fa-chart-pie mr-3 text-blue-800"></i> Distribusi Jenis Penugasan
            </h4>
            {{-- Tinggi disamakan 300px agar simetris --}}
            <div class="h-[300px] w-full">
                <canvas id="donutChart"></canvas>
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

        // Bar Chart
        // Bar Chart
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: eselonLabels,
                datasets: [{
                    data: eselonValues,
                    backgroundColor: '#1D4076',
                    borderRadius: 6, // Radius sedikit ditambah agar seimbang dengan batang besar
                    barThickness: 45, // Batang jauh lebih besar
                    maxBarThickness: 50 // Batas maksimal ketebalan
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [5, 5],
                            color: '#E2E8F0'
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '700'
                            }, // Label diperjelas
                            padding: 10 // Jarak label ke batang
                        }
                    }
                }
            }
        });

        // Donut Chart
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: penugasanLabels,
                datasets: [{
                    data: penugasanValues,
                    backgroundColor: ['#1D4076', '#FBBF24', '#22C55E', '#0EA5E9'],
                    borderWidth: 0,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 10,
                            font: {
                                size: 10,
                                weight: '600'
                            }
                        }
                    }
                }
            }
        });
    </script>
@endpush
