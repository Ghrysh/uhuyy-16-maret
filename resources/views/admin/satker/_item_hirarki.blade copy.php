@php
    // Definisikan string pencarian level ini (nama + kode)
    $selfText = strtolower($item->nama_satker . ' ' . $item->kode_satker);
    $itemMatch = "('{$item->nama_satker}'.toLowerCase().includes(search.toLowerCase()) || '{$item->kode_satker}'.includes(search))";

    // Logika Role/Permission (Tetap sama)
    $user = auth()->user();
    $isRestrictedRole = $user
        ->roles()
        ->whereIn('key', ['admin_satker', 'pejabat'])
        ->exists();
    $canManage = true;
    if ($isRestrictedRole) {
        $canManage = $item->kode_satker == $user->satker_id;
        if (!$canManage) {
            $parent = $item->parent;
            while ($parent) {
                if ($parent->kode_satker == $user->satker_id) {
                    $canManage = true;
                    break;
                }
                $parent = $parent->parent;
            }
        }
    }
@endphp

<div x-data="{
    open: false,
    selfText: '{{ $selfText }}',
    {{-- Fungsi pengecekan: Apakah saya atau salah satu keturunan saya cocok dengan search? --}}
    get isVisible() {
        if (search === '') return true;
        // Cek diri sendiri
        if (this.selfText.includes(search.toLowerCase())) return true;
        // Cek apakah ada elemen anak yang sedang tampil (artinya anak itu cocok)
        return $el.querySelectorAll('.satker-row:not([style*=\'display: none\'])').length > 0;
    }
}" {{-- Gunakan x-show berbasis fungsi isVisible agar parent tetap muncul jika child cocok --}} x-show="isVisible" class="satker-item w-full">

    {{-- Baris Item Satker --}}
    {{-- Penanda: Tambahkan class 'satker-row' untuk deteksi isVisible, dan bg-amber-50 sebagai highlight --}}
    <div class="satker-row flex flex-col sm:flex-row sm:items-center justify-between p-3 hover:bg-blue-50/50 rounded-xl transition border border-transparent hover:border-blue-100 group"
        :class="search !== '' && selfText.includes(search.toLowerCase()) ?
            'bg-amber-50 border-amber-200 ring-1 ring-amber-200' : ''">

        <div class="flex items-start sm:items-center space-x-3 sm:space-x-4 overflow-hidden">
            {{-- Tombol Dropdown --}}
            <div class="text-slate-400 cursor-pointer w-4 mt-1 sm:mt-0 flex-shrink-0" @click="open = !open">
                @if ($item->children && $item->children->count() > 0)
                    <i class="fas fa-chevron-right text-[10px] transition-transform duration-200" {{-- OTOMATIS BUKA: Jika sedang mencari, paksa rotate 90 --}}
                        :class="(open || search !== '') ? 'rotate-90' : ''"></i>
                @else
                    <i class="fas fa-circle text-[4px] ml-1"></i>
                @endif
            </div>

            <div
                class="hidden xs:flex w-8 h-8 rounded-lg bg-gray-50 flex-shrink-0 items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-blue-500 transition-colors">
                <i class="fas fa-building text-xs"></i>
            </div>

            {{-- Informasi Satker --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    {{-- Nama Satker dengan efek highlight teks jika cocok --}}
                    <span class="text-sm font-bold text-slate-700 truncate max-w-[150px] sm:max-w-none"
                        :class="search !== '' && '{{ strtolower($item->nama_satker) }}'.includes(search.toLowerCase()) ?
                            'text-amber-700' : ''">
                        {{ $item->nama_satker }}
                    </span>
                    @if ($item->eselon)
                        <span
                            class="px-1.5 py-0.5 text-[8px] sm:text-[9px] text-white font-bold rounded uppercase flex-shrink-0"
                            style="background-color: {{ $item->jenis_satker_id == 1 ? '#112D4E' : ($item->jenis_satker_id == 2 ? '#3F72AF' : '#607d8b') }}">
                            {{ $item->eselon->nama }}
                        </span>
                    @endif
                </div>
                <span class="text-[10px] font-medium tracking-wider block mt-0.5"
                    :class="search !== '' && '{{ $item->kode_satker }}'.includes(search) ? 'text-amber-600' :
                        'text-slate-400'">
                    {{ $item->kode_satker }}
                </span>
            </div>
        </div>

        {{-- Status & Aksi --}}
        <div class="flex items-center justify-end mt-2 sm:mt-0 ml-7 sm:ml-0 flex-shrink-0 space-x-2">
            <span
                class="text-[9px] sm:text-[10px] font-bold {{ $item->status_aktif ? 'text-emerald-500 bg-emerald-50 border-emerald-100' : 'text-slate-400 bg-slate-50 border-slate-200' }} px-2 py-0.5 sm:py-1 rounded-md border">
                {{ $item->status_aktif ? 'AKTIF' : 'NON-AKTIF' }}
            </span>

            <div class="flex items-center border-l border-slate-200 ml-2 pl-2 space-x-1">
                @if ($canManage)
                    <button type="button"
                        onclick="openTambahSubSatker('{{ $item->id }}', '{{ $item->jenis_satker_id }}', '{{ $item->wilayah_id }}', '{{ $item->periode_id }}')"
                        class="p-1.5 text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                        title="Tambah Sub Satker">
                        <i class="fas fa-plus-circle text-xs"></i>
                    </button>
                    <button type="button"
                        onclick="openDetailModal('{{ $item->kode_satker }}', '{{ $item->nama_satker }}', '{{ $item->eselon ? $item->eselon->nama : '-' }}', '{{ $item->wilayah ? $item->wilayah->nama_wilayah : '-' }}', '{{ $item->status_aktif }}', '{{ $item->id }}')"
                        class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Lihat Detail">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button type="button"
                        onclick="openEditSatkerModal('{{ $item->id }}', '{{ $item->kode_satker }}', '{{ $item->nama_satker }}', '{{ $item->periode_id }}', '{{ $item->jenis_satker_id }}', '{{ $item->parent_satker_id }}', '{{ $item->wilayah_id }}', '{{ $item->keterangan }}', '{{ $item->status_aktif }}')"
                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                        title="Edit Satker">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <button type="button"
                        onclick="openDeleteModal('{{ $item->id }}', '{{ $item->nama_satker }}', '{{ $item->kode_satker }}')"
                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Hapus Satker">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Kontainer Anak --}}
    @if ($item->children && $item->children->count() > 0)
        {{-- OTOMATIS TAMPIL: x-show akan true jika user sedang mencari --}}
        <div x-show="open || search !== ''"
            class="ml-4 sm:ml-10 mt-1 border-l-2 border-gray-100 pl-3 sm:pl-4 space-y-1">
            @foreach ($item->children as $child)
                @include('admin.satker._item_hirarki', ['item' => $child])
            @endforeach
        </div>
    @endif
</div>
