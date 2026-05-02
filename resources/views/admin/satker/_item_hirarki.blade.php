@php
    // Definisikan string pencarian level ini (nama + kode)
    $selfText = strtolower($item->nama_satker . ' ' . $item->kode_satker);

    $eselonName = $item->eselon ? $item->eselon->nama : '-';

    if ($item->jenis_satker_id == 6) {
        $eselonName = 'Tugas Tambahan';
    }
    
    if (!empty($item->kode_satker)) {
        $kode = $item->kode_satker;
        $len = strlen($kode);
        $prefix = substr($kode, 0, 2);
        
        // 1. PRIORITAS UTAMA: Jika mulai dari kode 21 atau lebih (Eselon 1 & Anak-anaknya)
        if (is_numeric($prefix) && (int)$prefix >= 21) {
            if ($len > 2) {
                $eselonName = "Tugas Tambahan $len Digit";
            } else {
                $eselonName = 'Tugas Tambahan';
            }
        } 
        // 2. ATURAN KEDUA: Jika di bawah 21 (01, 02, dst) tapi mengandung angka tetap '9'
        else {
            $isTugasTambahanSembilan = false;
            // Cek angka '9' di posisi sub-kode (indeks 2, 4, 6, dst)
            for ($i = 2; $i < $len; $i += 2) {
                if (isset($kode[$i]) && $kode[$i] === '9') {
                    $isTugasTambahanSembilan = true;
                    break;
                }
            }

            if ($isTugasTambahanSembilan) {
                if ($len > 2) {
                    $eselonName = "Tugas Tambahan $len Digit";
                } else {
                    $eselonName = 'Tugas Tambahan';
                }
            }
        }
    }

    // =========================================================================
    // JAWABAN FEEDBACK: OVERRIDE NAMA JIKA STATUSNYA "JABATAN FUNGSIONAL"
    // =========================================================================
    if ($item->ref_jabatan_satker_id) {
        $refJabatan = \App\Models\RefJabatanSatker::find($item->ref_jabatan_satker_id);
        if ($refJabatan && $refJabatan->key_jabatan === 'jabatan_fungsional') {
            $eselonName = 'Jabatan Fungsional';
        }
    }
    // =========================================================================

    $actions = $perm['actions'] ?? [];
    $canCreate = $perm['is_super'] || $perm['all_access'] || in_array('create', $actions);
    $canEdit   = $perm['is_super'] || $perm['all_access'] || in_array('edit', $actions);
    $canDelete = $perm['is_super'] || $perm['all_access'] || in_array('delete', $actions);
    
    $canViewDetail = true; 
@endphp

    <div x-data="{
        open: false,
        selfText: '{{ $selfText }}',

        get isVisible() {
            if (search === '') return true;
            if (this.selfText.includes(search.toLowerCase())) return true;
            return $el.querySelectorAll('.satker-row:not([style*=\'display: none\'])').length > 0;
        }
    }" 
    x-show="isVisible" 
    @open-all-nodes.window="open = true" 
    @close-all-nodes.window="open = false"
    class="satker-item w-full">

    {{-- KUNCI PERBAIKAN: Penggabungan class Array, Baris bisa di-klik, dan efek highlight --}}
    <div class="satker-row flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 sm:p-3 
    bg-white hover:bg-blue-50/50 rounded-xl transition border border-slate-100 
    sm:border-transparent sm:hover:border-blue-100 group cursor-pointer"
        data-id="{{ $item->id }}"
        {{-- KUNCI PERBAIKAN: Tambahkan '{{ $item->jenis_satker_id }}' di akhir fungsi toggleId --}}
        @click="($store.selection.isSelectionMode && $store.selection.clipboard.mode === '') ? $store.selection.toggleId('{{ $item->id }}', '{{ addslashes($item->nama_satker) }}', '{{ $item->jenis_satker_id }}') : null"
        :class="[
            search !== '' && selfText.includes(search.toLowerCase()) ? 'bg-amber-50 border-amber-200 ring-1 ring-amber-200' : '',
            $store.selection.clipboard.ids.includes('{{ $item->id }}') && $store.selection.clipboard.mode !== '' ? 'opacity-40 grayscale bg-slate-100' : '',
            $store.selection.selectedIds.includes('{{ $item->id }}') && $store.selection.isSelectionMode ? 'ring-2 ring-blue-500 bg-blue-50/50' : ''
        ]">

        {{-- BAGIAN KIRI --}}
        <div class="flex items-start gap-3 min-w-0">
            <div class="text-slate-400 cursor-pointer w-5 flex-shrink-0 mt-1" @click="open = !open">
                @if ($item->children && $item->children->count() > 0)
                    <i class="fas fa-chevron-right text-xs transition-transform duration-200"
                        :class="(open || search !== '') ? 'rotate-90' : ''"></i>
                @else
                    <i class="fas fa-circle text-[5px] ml-1"></i>
                @endif
            </div>

            {{-- Checkbox untuk Mode Pilih --}}
            {{-- Logika: Muncul jika mode pilih AKTIF DAN (Belum ada yang di copy ATAU item ini adalah bagian dari yang di copy) --}}
            <div x-show="$store.selection.isSelectionMode && ($store.selection.clipboard.mode === '' || $store.selection.selectedIds.includes('{{ $item->id }}'))" 
                 x-transition
                 class="flex items-center mr-2 transition-all">
                <input type="checkbox" 
                       :value="'{{ $item->id }}'" 
                       x-model="$store.selection.selectedIds"
                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 pointer-events-none">
            </div>

            <div class="hidden sm:flex w-9 h-9 rounded-lg bg-gray-50 items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-blue-500 transition">
                <i class="fas fa-building text-sm"></i>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm sm:text-base font-semibold text-slate-700 break-words"
                        :class="search !== '' && '{{ strtolower($item->nama_satker) }}'.includes(search.toLowerCase()) ? 'text-amber-700' : ''">
                        {{ $item->nama_satker }}
                    </span>

                    @if ($item->eselon || str_contains($eselonName, 'Tugas Tambahan'))
                        <span class="px-2 py-0.5 text-[10px] text-white font-semibold rounded uppercase"
                            style="background-color: {{ $item->jenis_satker_id == 1 || str_contains($eselonName, 'Tugas Tambahan') ? '#112D4E' : ($item->jenis_satker_id == 2 ? '#3F72AF' : '#607d8b') }}">
                            {{ $eselonName }}
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-xs font-medium tracking-wide"
                    :class="search !== '' && '{{ $item->kode_satker }}'.includes(search) ? 'text-amber-600' : 'text-slate-400'">
                    {{ $item->kode_satker }}
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3" @click.stop>
            <span class="text-xs font-semibold px-3 py-1 rounded-md border w-fit {{ $item->status_aktif ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-slate-400 bg-slate-50 border-slate-200' }}">
                {{ $item->status_aktif ? 'AKTIF' : 'NON-AKTIF' }}
            </span>
            
            {{-- TOMBOL PASTE KHUSUS DENGAN VALIDASI --}}
            <template x-if="$store.selection.clipboard.mode !== '' && !$store.selection.clipboard.ids.includes('{{ $item->id }}')">
                <button type="button" 
                    x-show="$store.selection.clipboard.mode !== '' && ! $store.selection.clipboard.ids.includes('{{ $item->id }}')" 
                    @click.stop="$store.selection.confirmPaste('{{ $item->id }}', '{{ $item->kode_satker }}', '{{ addslashes($item->nama_satker) }}')"
                    class="bg-blue-100 hover:bg-blue-600 text-blue-600 hover:text-white rounded-md w-7 h-7 flex items-center justify-center transition shadow-sm ml-2 hover:scale-110" 
                    title="Paste di dalam Satker ini">
                    <i class="fas fa-paste text-xs"></i>
                </button>
            </template>

            {{-- TOMBOL AKSI NORMAL (Sembunyi otomatis saat Anda sedang Copy/Cut) --}}
            <div x-show="$store.selection.clipboard.mode === ''" class="flex items-center gap-1 border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-3">
                <button type="button" onclick="{{ $canCreate ? "openTambahSubSatker('{$item->id}', '{$item->jenis_satker_id}', '{$item->wilayah_id}', '{$item->periode_id}', true)" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menambah Satker.', 'error')" }}" class="p-2 {{ $canCreate ? 'text-emerald-600 hover:bg-emerald-50' : 'text-emerald-400 opacity-60' }} rounded-lg transition" title="Tambah"><i class="fas fa-plus text-sm"></i></button>
                <button type="button" onclick="openDetailModal('{{ $item->kode_satker }}', '{{ addslashes($item->nama_satker) }}', '{{ $eselonName }}', '{{ $item->wilayah ? $item->wilayah->nama_wilayah : '-' }}', '{{ $item->status_aktif }}', '{{ $item->id }}')" class="p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition" title="Detail"><i class="fas fa-eye text-sm"></i></button>
                <button type="button" onclick="{{ $canEdit ? "openEditSatkerModal('{$item->id}', '{$item->kode_satker}', '".addslashes($item->nama_satker)."', '{$item->periode_id}', '{$item->jenis_satker_id}', '{$item->parent_satker_id}', '{$item->wilayah_id}', '{$item->keterangan}', '{$item->status_aktif}')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Mengedit Satker ini.', 'error')" }}" class="p-2 {{ $canEdit ? 'text-amber-500 hover:bg-amber-50 hover:text-amber-600' : 'text-amber-300 opacity-60' }} rounded-lg transition" title="Edit"><i class="fas fa-edit text-sm"></i></button>
                <button type="button" onclick="{{ $canDelete ? "openDeleteModal('{$item->id}', '".addslashes($item->nama_satker)."', '{$item->kode_satker}')" : "Swal.fire('Akses Ditolak', 'Anda tidak memiliki izin untuk Menghapus Satker ini.', 'error')" }}" class="p-2 {{ $canDelete ? 'text-red-500 hover:bg-red-50 hover:text-red-600' : 'text-red-300 opacity-60' }} rounded-lg transition" title="Hapus"><i class="fas fa-trash text-sm"></i></button>
            </div>
        </div>
    </div>

    {{-- Kontainer Anak --}}
    @if ($item->children && $item->children->count() > 0)
        <div x-show="open || search !== ''" class="ml-5 sm:ml-10 mt-2 border-l-2 border-gray-100 pl-4 space-y-2">
            @php
                // Urutkan anak berdasarkan panjang digit kodenya (misal: 2109100 sebelum 21091101)
                $sortedChildren = $item->children->sortBy(function($child) {
                    return strlen($child->kode_satker) . '-' . $child->kode_satker;
                });
            @endphp
            @foreach ($sortedChildren as $child)
                @include('admin.satker._item_hirarki', ['item' => $child])
            @endforeach
        </div>
    @endif
</div>