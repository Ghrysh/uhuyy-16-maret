@php
    // Definisikan string pencarian level ini (nama + kode)
    $selfText = strtolower($item->nama_satker . ' ' . $item->kode_satker);
    $itemMatch = "('{$item->nama_satker}'.toLowerCase().includes(search.toLowerCase()) || '{$item->kode_satker}'.includes(search))";

    // LOGIKA BARU: Eselon 1 -> Tugas Tambahan (Jika Kode >= 21)
    $eselonName = $item->eselon ? $item->eselon->nama : '-';
    if ($item->jenis_satker_id == 1 && !empty($item->kode_satker)) {
        $prefix = substr($item->kode_satker, 0, 2);
        if (is_numeric($prefix) && (int)$prefix >= 21) {
            $eselonName = 'Tugas Tambahan';
        }
    }

    // Logika Role/Permission (Tetap sama)
    $user = auth()->user();
    $isRestrictedRole = $user
        ->roles()
        ->whereIn('key', ['admin_satker', 'pejabat'])
        ->exists();
    
    $canManage = true;
    if ($isRestrictedRole) {
        $canManage = $item->id == $user->satker_id;
        if (!$canManage) {
            $parent = $item->parent;
            while ($parent) {
                if ($parent->id == $user->satker_id) {
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

    get isVisible() {
        if (search === '') return true;
        if (this.selfText.includes(search.toLowerCase())) return true;
        return $el.querySelectorAll('.satker-row:not([style*=\'display: none\'])').length > 0;
    }
}" x-show="isVisible" class="satker-item w-full">

    <div class="satker-row flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 sm:p-3 
    bg-white hover:bg-blue-50/50 rounded-xl transition border border-slate-100 
    sm:border-transparent sm:hover:border-blue-100 group"
        :class="search !== '' && selfText.includes(search.toLowerCase()) ?
            'bg-amber-50 border-amber-200 ring-1 ring-amber-200' :
            ''">

        {{-- BAGIAN KIRI --}}
        <div class="flex items-start gap-3 min-w-0">

            {{-- Toggle --}}
            <div class="text-slate-400 cursor-pointer w-5 flex-shrink-0 mt-1" @click="open = !open">
                @if ($item->children && $item->children->count() > 0)
                    <i class="fas fa-chevron-right text-xs transition-transform duration-200"
                        :class="(open || search !== '') ? 'rotate-90' : ''"></i>
                @else
                    <i class="fas fa-circle text-[5px] ml-1"></i>
                @endif
            </div>

            {{-- Icon --}}
            <div
                class="hidden sm:flex w-9 h-9 rounded-lg bg-gray-50 
            items-center justify-center text-slate-400 
            group-hover:bg-white group-hover:text-blue-500 transition">
                <i class="fas fa-building text-sm"></i>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm sm:text-base font-semibold text-slate-700 break-words"
                        :class="search !== '' && '{{ strtolower($item->nama_satker) }}'.includes(search.toLowerCase()) ?
                            'text-amber-700' :
                            ''">
                        {{ $item->nama_satker }}
                    </span>

                    @if ($item->eselon)
                        <span class="px-2 py-0.5 text-[10px] text-white font-semibold rounded uppercase"
                            style="background-color: {{ $item->jenis_satker_id == 1 ? '#112D4E' : ($item->jenis_satker_id == 2 ? '#3F72AF' : '#607d8b') }}">
                            {{ $eselonName }}
                        </span>
                    @endif
                </div>

                <div class="mt-1 text-xs font-medium tracking-wide"
                    :class="search !== '' && '{{ $item->kode_satker }}'.includes(search) ?
                        'text-amber-600' :
                        'text-slate-400'">
                    {{ $item->kode_satker }}
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">

            {{-- Status --}}
            <span
                class="text-xs font-semibold px-3 py-1 rounded-md border w-fit
            {{ $item->status_aktif
                ? 'text-emerald-600 bg-emerald-50 border-emerald-200'
                : 'text-slate-400 bg-slate-50 border-slate-200' }}">
                {{ $item->status_aktif ? 'AKTIF' : 'NON-AKTIF' }}
            </span>

            {{-- Action Buttons --}}
            @if ($canManage)
                <div
                    class="flex items-center gap-1 border-t sm:border-t-0 sm:border-l border-slate-200 pt-2 sm:pt-0 sm:pl-3">

                    <button type="button"
                        onclick="openTambahSubSatker('{{ $item->id }}', '{{ $item->jenis_satker_id }}', '{{ $item->wilayah_id }}', '{{ $item->periode_id }}', true)"
                        class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Tambah">
                        <i class="fas fa-plus text-sm"></i>
                    </button>

                    <button type="button"
                        onclick="openDetailModal('{{ $item->kode_satker }}', '{{ $item->nama_satker }}', '{{ $eselonName }}', '{{ $item->wilayah ? $item->wilayah->nama_wilayah : '-' }}', '{{ $item->status_aktif }}', '{{ $item->id }}')"
                        class="p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition"
                        title="Detail">
                        <i class="fas fa-eye text-sm"></i>
                    </button>

                    <button type="button"
                        onclick="openEditSatkerModal('{{ $item->id }}', '{{ $item->kode_satker }}', '{{ $item->nama_satker }}', '{{ $item->periode_id }}', '{{ $item->jenis_satker_id }}', '{{ $item->parent_satker_id }}', '{{ $item->wilayah_id }}', '{{ $item->keterangan }}', '{{ $item->status_aktif }}')"
                        class="p-2 text-slate-500 hover:bg-amber-50 hover:text-amber-600 rounded-lg transition"
                        title="Edit">
                        <i class="fas fa-edit text-sm"></i>
                    </button>

                    <button type="button"
                        onclick="openDeleteModal('{{ $item->id }}', '{{ $item->nama_satker }}', '{{ $item->kode_satker }}')"
                        class="p-2 text-slate-500 hover:bg-red-50 hover:text-red-600 rounded-lg transition"
                        title="Hapus">
                        <i class="fas fa-trash text-sm"></i>
                    </button>

                </div>
            @endif
        </div>
    </div>

    {{-- Kontainer Anak --}}
    @if ($item->children && $item->children->count() > 0)
        {{-- OTOMATIS TAMPIL: x-show akan true jika user sedang mencari --}}
        <div x-show="open || search !== ''" class="ml-5 sm:ml-10 mt-2 border-l-2 border-gray-100 pl-4 space-y-2">
            @foreach ($item->children as $child)
                @include('admin.satker._item_hirarki', ['item' => $child])
            @endforeach
        </div>
    @endif
</div>
