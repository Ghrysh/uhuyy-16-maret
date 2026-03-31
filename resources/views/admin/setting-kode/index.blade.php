@extends('layouts.admin')
@section('title', 'Setup Rumus Kode Satker')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-[#112D4E]">Setup Kode Satuan Kerja & Jabatan</h2>
    <p class="text-sm text-gray-500 mt-1">Konfigurasi pola kode otomatis berdasarkan Wilayah, Level, dan Jabatan.</p>
</div>

<script>
    // Data dilempar ke window agar aman
    window.rumusDatabase = @json($rumusList);
    window.refJabatanDatabase = @json($refJabatans); // <-- TAMBAHAN: Tarik data referensi jabatan
</script>

<div x-data="{ 
        tab: 'auto_number', 
        editModalOpen: false,
        rumusData: window.rumusDatabase,
        refData: window.refJabatanDatabase, // <-- TAMBAHAN: Masukkan ke Alpine

        activePeriode: '{{ $periodes->first()->id ?? '' }}',
        
        // Form Auto Number
        auto_wilayah: '', auto_jenis: '', auto_ref: '',
        activeAutoSetup: null,

        // Form Fix Code
        fix_wilayah: '', fix_jenis: '', fix_ref: '',
        activeFixSetup: null,

        // FUNGSI BARU: Mengambil Kode Dasar Bawaan Sistem
        getDefaultCode(type) {
            let r = type === 'auto' ? this.auto_ref : this.fix_ref;
            if (!r) return '[ Belum Pilih Jabatan ]';
            
            let jab = this.refData.find(item => item.id == r);
            return jab && jab.kode_dasar ? jab.kode_dasar : '[ Tidak Ada / Default Urut ]';
        },

        checkSetup(type) {
            let w = type === 'auto' ? this.auto_wilayah : this.fix_wilayah;
            let j = type === 'auto' ? this.auto_jenis : this.fix_jenis;
            let r = type === 'auto' ? this.auto_ref : this.fix_ref;

            let wStr = (w === '' || w === null) ? null : String(w).toLowerCase();
            let jStr = (j === '' || j === null) ? null : String(j).toLowerCase();
            let rStr = (r === '' || r === null) ? null : String(r).toLowerCase();

            let matches = this.rumusData.filter(item => {
                let dbW = item.tingkat_wilayah_id ? String(item.tingkat_wilayah_id).toLowerCase() : null;
                let dbJ = item.jenis_satker_id ? String(item.jenis_satker_id).toLowerCase() : null;
                let dbR = item.ref_jabatan_satker_id ? String(item.ref_jabatan_satker_id).toLowerCase() : null;
                
                let isAktif = (item.is_applied == 1 || item.is_applied === true);
                let isTypeMatch = type === 'auto' ? (item.is_auto_number == 1 || item.is_auto_number === true) : (item.is_auto_number == 0 || item.is_auto_number === false);

                let matchW = (dbW === null) || (dbW === wStr);
                let matchJ = (dbJ === null) || (dbJ === jStr);
                let matchR = (dbR === null) || (dbR === rStr);

                return matchW && matchJ && matchR && isAktif && isTypeMatch;
            });

            matches.sort((a, b) => {
                let scoreA = (a.tingkat_wilayah_id ? 1 : 0) + (a.jenis_satker_id ? 1 : 0) + (a.ref_jabatan_satker_id ? 1 : 0);
                let scoreB = (b.tingkat_wilayah_id ? 1 : 0) + (b.jenis_satker_id ? 1 : 0) + (b.ref_jabatan_satker_id ? 1 : 0);
                return scoreB - scoreA;
            });

            if (type === 'auto') {
                this.activeAutoSetup = matches.length > 0 ? matches[0] : null;
            } else {
                this.activeFixSetup = matches.length > 0 ? matches[0] : null;
            }
        },

        async updateManualBulk(event) {
            const form = event.target;
            const inputs = form.querySelectorAll('.kode-input');
            const changedData = {};
            let hasChanges = false;
            
            inputs.forEach(input => {
                if (input.value !== input.getAttribute('data-original')) {
                    const match = input.name.match(/\[(.*?)\]/);
                    if (match && match[1]) {
                        changedData[match[1]] = input.value;
                        hasChanges = true;
                    }
                }
            });

            if (!hasChanges) {
                Swal.fire('Info', 'Tidak ada perubahan kode yang Anda lakukan.', 'info');
                return;
            }

            const btn = form.querySelector('button[type=\'submit\']');
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class=\'fas fa-spinner fa-spin\'></i> Menyimpan...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('input[name=\'_token\']').value,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ kode_satker_baru: changedData })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 1500, showConfirmButton: false }).then(() => {
                        window.location.reload(); // Muat ulang layar agar data original ter-update
                    });
                } else {
                    Swal.fire('Validasi Gagal', result.message || 'Terjadi kesalahan sistem.', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan jaringan/sistem saat memproses bulk update.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        },

        init() {
            let sessionTab = '{{ session('tab') }}';
            let navType = window.performance.getEntriesByType('navigation')[0]?.type;
            
            if (sessionTab) {
                this.tab = sessionTab; 
            } else if (navType === 'reload') {
                let localTab = localStorage.getItem('settingKodeTab');
                if(localTab) this.tab = localTab; 
            }

            this.$watch('tab', value => {
                localStorage.setItem('settingKodeTab', value);
            });

            this.checkSetup('auto');
            this.checkSetup('fix');

            this.$watch('auto_wilayah', () => this.checkSetup('auto'));
            this.$watch('auto_jenis', () => this.checkSetup('auto'));
            this.$watch('auto_ref', () => this.checkSetup('auto'));
            
            this.$watch('fix_wilayah', () => this.checkSetup('fix'));
            this.$watch('fix_jenis', () => this.checkSetup('fix'));
            this.$watch('fix_ref', () => this.checkSetup('fix'));
        }
    }" class="w-full">

    <div class="flex flex-wrap gap-2 border-b border-gray-300 pb-2">
        <button @click="tab = 'auto_number'" :class="tab === 'auto_number' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none">
            <i class="fas fa-sort-numeric-down mr-1"></i> Auto-Number
        </button>
        <button @click="tab = 'fix_code'" :class="tab === 'fix_code' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none">
            <i class="fas fa-thumbtack mr-1"></i> Fix Code
        </button>
        <button @click="tab = 'jf'" :class="tab === 'jf' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none">
            <i class="fas fa-id-badge mr-1"></i> Jabatan Fungsional
        </button>
        <button @click="tab = 'manual'" :class="tab === 'manual' ? 'bg-[#112D4E] text-white rounded-t-lg' : 'text-gray-500 hover:text-[#112D4E]'" class="px-4 py-2 text-sm font-bold transition outline-none ml-auto border border-[#112D4E]/20">
            <i class="fas fa-sitemap mr-1"></i> Hirarki & Edit Manual
        </button>
    </div>

    <div x-show="tab === 'auto_number'" x-cloak class="bg-white shadow rounded-lg p-6 border-t-4 border-blue-500">
        @include('admin.setting-kode._form_setup', ['isAuto' => true, 'title' => 'Setup Auto-Number', 'icon' => 'fa-sort-numeric-down'])
        <div class="mt-8">
            @include('admin.setting-kode._table_rumus', ['isAuto' => true])
        </div>
    </div>

    <div x-show="tab === 'fix_code'" x-cloak class="bg-white shadow rounded-lg p-6 border-t-4 border-amber-500">
        @include('admin.setting-kode._form_setup', ['isAuto' => false, 'title' => 'Setup Fix Code (Angka Tetap)', 'icon' => 'fa-thumbtack'])
        <div class="mt-8">
            @include('admin.setting-kode._table_rumus', ['isAuto' => false])
        </div>
    </div>

    <div x-show="tab === 'jf'" x-cloak class="bg-white shadow rounded-lg p-6 border-t-4 border-green-500">
        <div class="text-center py-8">
            <i class="fas fa-magic fa-4x text-green-500 mb-4"></i>
            <h3 class="text-2xl font-bold text-[#112D4E]">Otomatis Ter-Concat</h3>
            <p class="text-gray-600 mt-2 max-w-2xl mx-auto">
                Sesuai dengan ketentuan, untuk status <b>Jabatan Fungsional</b> (seperti Analis SDM, Pranata Komputer, dll), kode akan dibentuk secara otomatis oleh sistem saat *user* men-generate satuan kerja baru. <br><br>Sistem akan otomatis menggabungkan kode <b>[Satker Induk]</b> + <b>[Kode Spesifik Jabatan Fungsional]</b>. Tidak diperlukan setup rumus khusus di sini.
            </p>
        </div>
    </div>

    <div x-show="tab === 'manual'" x-cloak class="bg-white shadow rounded-lg p-6 border-t-4 border-gray-800">
        <h3 class="text-lg font-bold text-[#112D4E] mb-4"><i class="fas fa-sitemap mr-2 text-gray-800"></i>Hirarki Satker & Edit Manual</h3>
        
        <div x-data="{ 
            search: '',
            scrollTimeout: null,
            init() {
                this.$watch('search', (val) => {
                    // Hanya jalan jika ketikan lebih dari 1 huruf
                    if (val && val.length > 1) {
                        
                        // Hentikan perintah scroll lama jika user mengetik terlalu cepat
                        clearTimeout(this.scrollTimeout);
                        
                        // Beri jeda 300ms agar animasi buka-tutup hierarki Alpine selesai
                        this.scrollTimeout = setTimeout(() => {
                            const container = document.getElementById('satkerManualContainer');
                            if (!container) return;

                            const term = val.toLowerCase();
                            
                            // Ambil SEMUA baris satker yang ada di dalam container
                            const rows = container.querySelectorAll('.satker-row');
                            let firstMatch = null;

                            // Loop dari atas ke bawah
                            for (let i = 0; i < rows.length; i++) {
                                const row = rows[i];
                                
                                // 1. Pastikan satkernya TERLIHAT di layar (tidak disembunyikan oleh tab periode)
                                if (row.offsetHeight > 0) {
                                    
                                    // 2. BACA TEKS MURNI NYA SECARA LANGSUNG (Bypass delay class Alpine)
                                    const text = row.innerText || row.textContent;
                                    
                                    if (text.toLowerCase().includes(term)) {
                                        firstMatch = row;
                                        break; // LANGSUNG BERHENTI di data paling atas!
                                    }
                                }
                            }

                            // Jika ketemu, eksekusi perintah Auto-Scroll
                            if (firstMatch) {
                                const cRect = container.getBoundingClientRect();
                                const mRect = firstMatch.getBoundingClientRect();
                                
                                // Rumus absolut untuk menaik-turunkan scroll di dalam container
                                container.scrollTo({
                                    top: container.scrollTop + (mRect.top - cRect.top) - 20,
                                    behavior: 'smooth'
                                });
                            }
                        }, 300);
                    }
                });
            }
        }" class="space-y-4">
            
            <div class="relative w-full mb-4">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-[#112D4E] block w-full pl-10 p-3" placeholder="Ketik Nama Satker atau Kode...">
            </div>

            <form @submit.prevent="updateManualBulk($event)" action="{{ route('admin.setting-kode.updateManualBulk') }}" method="POST">
                @csrf
                
                @if(isset($periodes) && $periodes->count() > 0)
                <div class="flex overflow-x-auto space-x-2 mb-4 pb-2 border-b border-gray-200 hide-scrollbar">
                    @foreach($periodes as $periode)
                        <button type="button" @click="activePeriode = '{{ $periode->id }}'"
                                :class="activePeriode === '{{ $periode->id }}' ? 'bg-[#112D4E] text-white border-[#112D4E] shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'"
                                class="px-5 py-2.5 text-sm font-semibold rounded-lg border whitespace-nowrap transition-all duration-200">
                            {{ $periode->nama_periode }}
                        </button>
                    @endforeach
                </div>
                @endif

                <div class="space-y-2 bg-slate-50/50 rounded-xl p-4 border min-h-[400px] max-h-[600px] overflow-y-auto shadow-inner scroll-smooth" id="satkerManualContainer">
                    @forelse($satkerRoots as $root)
                        <div x-show="activePeriode == '{{ $root->periode_id }}'">
                            @include('admin.setting-kode._item_edit_manual', ['item' => $root])
                        </div>
                    @empty
                        <div class="p-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            <i class="fas fa-folder-open text-3xl text-gray-300 mb-3 block"></i>
                            <p class="text-gray-500 font-medium">Belum ada data Satuan Kerja.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                    <button type="submit" class="bg-[#112D4E] hover:bg-blue-900 text-white px-6 py-2.5 rounded-xl text-sm font-bold shadow-md transition-all flex items-center gap-2 transform active:scale-95">
                        <i class="fas fa-save"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.setting-kode._modal_edit')

</div>
@endsection

@push('styles')
<style> [x-cloak] { display: none !important; } </style>
@endpush

@push('scripts')
<script>
    function editRumus(btn) {
        let id = btn.getAttribute('data-id');
        let is_auto = btn.getAttribute('data-isauto') === '1';

        document.getElementById('edit-rumus-form').action = "{{ route('admin.setting-kode.updateRumus', ':id') }}".replace(':id', id);
        
        document.getElementById('edit_nama_rumus').value = btn.getAttribute('data-nama');
        document.getElementById('edit_tingkat_wilayah_id').value = btn.getAttribute('data-wilayah');
        document.getElementById('edit_jenis_satker_id').value = btn.getAttribute('data-jenis');
        document.getElementById('edit_ref_jabatan_satker_id').value = btn.getAttribute('data-ref');
        document.getElementById('edit_kode_awalan').value = btn.getAttribute('data-awalan');
        document.getElementById('edit_default_nama_satker').value = btn.getAttribute('data-defaultnama');
        document.getElementById('edit_is_auto_number').value = is_auto ? '1' : '0';
        
        let digitWrap = document.getElementById('edit_digit_wrapper');
        if(is_auto) {
            digitWrap.style.display = 'block';
            document.getElementById('edit_digit_auto_number').value = btn.getAttribute('data-digit');
        } else {
            digitWrap.style.display = 'none';
        }

        window.dispatchEvent(new CustomEvent('open-edit-modal'));
    }

    @if(session('success'))
        Swal.fire({ title: 'Berhasil!', text: '{{ session("success") }}', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    @endif

    function confirmDelete(btn) {
        Swal.fire({
            title: 'Hapus Setup?', text: "Data tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
        }).then((result) => { if (result.isConfirmed) btn.closest('form').submit(); })
    }

    function confirmApply(btn) {
        Swal.fire({
            title: 'Terapkan & Alihkan Rumus?',
            text: "Jika ada rumus lain yang aktif pada 3 kombinasi (Wilayah, Level, Jabatan) yang sama, rumus tersebut akan dinonaktifkan diganti dengan ini.",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#112D4E', cancelButtonColor: '#d33', confirmButtonText: 'Ya, Terapkan Sekarang!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({title: 'Memproses...', text: 'Menerapkan ke semua data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
                btn.closest('form').submit(); 
            }
        })
    }
</script>
@endpush