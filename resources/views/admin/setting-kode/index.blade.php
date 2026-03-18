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
    }" 
    @open-edit-modal.window="editModalOpen = true" 
    class="space-y-6 relative">

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
        
        <div x-data="{ search: '' }" class="space-y-4">
            <div class="relative w-full mb-6">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-[#112D4E] block w-full pl-10 p-3" placeholder="Ketik Nama Satker atau Kode...">
            </div>

            <div class="space-y-2 bg-slate-50/50 rounded-xl p-4 border min-h-[400px] max-h-[600px] overflow-y-auto shadow-inner">
                @foreach($satkerRoots as $root)
                    @include('admin.setting-kode._item_edit_manual', ['item' => $root])
                @endforeach
            </div>
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