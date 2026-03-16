@extends('layouts.admin')

@section('title', 'Pegawai per Satuan Kerja')

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

        .border-navy-custom {
            border-color: #112D4E;
        }
    </style>
@endpush

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Pegawai per Satuan Kerja</h2>
        <p class="text-sm text-slate-500">Daftar pegawai berdasarkan satuan kerja (data pegawai dikelola di aplikasi lain)</p>
    </div>

    <div id="ajax-container" class="relative min-h-[400px]">

        <div id="loading-overlay"
            class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-[2px] hidden rounded-2xl">
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-[#112D4E] rounded-full animate-spin-fast"></div>
                <span class="mt-3 text-xs font-bold text-[#112D4E] tracking-widest uppercase">Memproses Data...</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <div class="space-y-4" id="pegawaiAccordion">
                @forelse ($satkers as $satker)
                    <div class="border border-gray-200 rounded-xl overflow-hidden group">
                        <button onclick="toggleAccordion('content-{{ $satker->id }}', 'icon-{{ $satker->id }}')"
                            class="w-full flex items-center justify-between p-4 bg-gray-50/50 hover:bg-gray-50 transition text-left">
                            <div class="flex items-center space-x-4">
                                <i id="icon-{{ $satker->id }}"
                                    class="fas fa-chevron-right text-xs text-slate-400 transition-transform duration-200"></i>

                                <div
                                    class="w-10 h-10 flex items-center justify-center bg-[#112D4E]/10 rounded-full shrink-0">
                                    <i class="fas fa-building text-[#112D4E] text-sm"></i>
                                </div>

                                <div>
                                    <span
                                        class="block text-[13px] font-bold text-[#112D4E] leading-tight">{{ $satker->kode_satker }}</span>
                                    <span
                                        class="block text-[13px] font-medium text-slate-600 line-clamp-1">{{ $satker->nama_satker }}</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span
                                    class="px-3 py-1 bg-[#112D4E] text-white text-[10px] font-bold rounded-full uppercase">
                                    {{ $satker->pegawais->count() }} Pegawai
                                </span>
                            </div>
                        </button>

                        <div id="content-{{ $satker->id }}" class="hidden border-t border-gray-100 bg-white">
                            <div class="divide-y divide-gray-50">
                                @foreach ($satker->pegawais as $index => $pegawai)
                                    <div
                                        class="flex items-center justify-between p-4 pl-12 hover:bg-slate-50/50 transition">
                                        <div class="flex items-center space-x-4">
                                            <span class="text-xs text-slate-400 w-4">{{ $index + 1 }}.</span>
                                            <div
                                                class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 border border-gray-200">
                                                <i class="fas fa-user text-[10px]"></i>
                                            </div>
                                            <div>
                                                <p class="text-[13px] font-bold text-slate-700 leading-tight">
                                                    {{ $pegawai->name }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono tracking-tighter">NIP:
                                                    {{ $pegawai->nip }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="px-3 py-1 bg-slate-100 text-[#112D4E] text-[10px] font-bold rounded-md uppercase tracking-wider">
                                                {{ $pegawai->penugasanAktif->jabatan->nama_jabatan ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-slate-400 text-sm">Tidak ada data satuan kerja ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8 pagination-ajax-wrapper">
                {{ $satkers->appends(['page_kosong' => $satkerKosong->currentPage()])->links() }}
            </div>

            <hr class="my-12 border-gray-100">

            @if ($satkerKosong->total() > 0)
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-6">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Satker Belum Terisi
                            ({{ $satkerKosong->total() }})</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($satkerKosong as $sk)
                            <div
                                class="flex items-center p-4 bg-gray-50/50 border border-gray-100 rounded-xl hover:border-[#112D4E]/30 hover:bg-white hover:shadow-sm transition group">
                                <div
                                    class="w-9 h-9 flex items-center justify-center bg-gray-200 group-hover:bg-[#112D4E]/10 rounded-full transition shrink-0">
                                    <i class="fas fa-building text-gray-400 group-hover:text-[#112D4E] text-xs"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-[10px] font-bold text-slate-400 leading-none mb-1">
                                        {{ $sk->kode_satker }}</p>
                                    <p
                                        class="text-[11px] font-bold text-slate-600 line-clamp-1 group-hover:text-[#112D4E] transition">
                                        {{ $sk->nama_satker }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 pagination-ajax-wrapper">
                        {{ $satkerKosong->appends(['page_satker' => $satkers->currentPage()])->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleAccordion(contentId, iconId) {
            const content = document.getElementById(contentId);
            const icon = document.getElementById(iconId);
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-90');
        }

        document.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination-ajax-wrapper a');
            if (link) {
                e.preventDefault();
                const targetUrl = link.getAttribute('href');
                if (targetUrl && targetUrl !== '#') {
                    performAjaxUpdate(targetUrl);
                }
            }
        });

        async function performAjaxUpdate(url) {
            const container = document.getElementById('ajax-container');
            const overlay = document.getElementById('loading-overlay');

            overlay.classList.remove('hidden');
            container.style.pointerEvents = 'none';

            try {
                const response = await fetch(url, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                if (!response.ok) throw new Error("Gagal mengambil data dari server.");

                const htmlResponse = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlResponse, "text/html");
                const newInnerContent = doc.getElementById('ajax-container').innerHTML;

                container.innerHTML = newInnerContent;

                setTimeout(() => {
                    window.scrollTo({
                        top: container.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }, 50);

            } catch (error) {
                console.error("AJAX Error:", error);
                alert("Terjadi kesalahan saat memuat data.");
            } finally {
                container.style.pointerEvents = 'auto';
            }
        }
    </script>
@endpush
