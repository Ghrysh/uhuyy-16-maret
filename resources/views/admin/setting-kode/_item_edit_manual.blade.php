@php
    $selfText = strtolower($item->nama_satker . ' ' . $item->kode_satker);

    $eselonName = $item->eselon ? $item->eselon->nama : '-';
    if ($item->jenis_satker_id == 1 && !empty($item->kode_satker)) {
        $prefix = substr($item->kode_satker, 0, 2);
        if (is_numeric($prefix) && (int)$prefix >= 21) {
            $eselonName = 'Tugas Tambahan';
        }
    }

    $isMatch = isset($matchedIds) && in_array($item->id, $matchedIds);
    $isPath = isset($forceShowChildren) && $forceShowChildren;
@endphp

<div class="satker-item w-full {{ $isPath ? 'is-shortlist-path' : '' }} {{ $isMatch ? 'is-match is-shortlist-target' : '' }}" data-search="{{ $selfText }}">

    <div class="satker-row flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 sm:p-3 
    bg-white hover:bg-blue-50/50 rounded-xl transition border border-slate-100 
    sm:border-transparent sm:hover:border-blue-100 group {{ $isMatch ? 'ring-2 ring-blue-400 bg-blue-50' : '' }}">

        <div class="flex items-start gap-3 min-w-0">
            <div class="text-slate-400 cursor-pointer w-5 flex-shrink-0 mt-1" onclick="toggleSatkerNodeManual(this, event, '{{ $item->id }}')">
                @if (($item->children_count ?? 0) > 0 || ($item->relationLoaded('children') && $item->children->count() > 0))
                    <i class="fas fa-chevron-right text-xs transition-transform duration-200 chevron-icon {{ isset($forceShowChildren) && $forceShowChildren ? 'rotate-90' : '' }}"></i>
                @else
                    <i class="fas fa-circle text-[5px] ml-1"></i>
                @endif
            </div>

            <div class="hidden sm:flex w-9 h-9 rounded-lg bg-gray-50 items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-[#112D4E] transition">
                <i class="fas fa-building text-sm"></i>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm sm:text-base font-semibold text-slate-700 break-words {{ $isMatch ? 'text-amber-700' : '' }}">
                        {{ $item->nama_satker }}
                    </span>
                    @if ($item->eselon)
                        <span class="px-2 py-0.5 text-[10px] text-white font-semibold rounded uppercase"
                            style="background-color: {{ $item->jenis_satker_id == 1 ? '#112D4E' : ($item->jenis_satker_id == 2 ? '#3F72AF' : '#607d8b') }}">
                            {{ $eselonName }}
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-xs font-medium tracking-wide text-slate-400">
                    Kode Lama: <span class="font-mono text-gray-500">{{ $item->kode_satker }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-4 mt-2 sm:mt-0">
            <div>
                <input type="text" 
                       name="kode_satker_baru[{{ $item->id }}]" 
                       value="{{ $item->kode_satker }}" 
                       data-original="{{ $item->kode_satker }}"
                       oninput="this.classList.toggle('bg-amber-50', this.value !== this.getAttribute('data-original')); this.classList.toggle('border-amber-400', this.value !== this.getAttribute('data-original'))"
                       class="kode-input border-gray-300 rounded-md shadow-sm px-2 py-1.5 border w-48 font-mono text-sm focus:ring-[#112D4E] focus:border-[#112D4E] bg-white text-[#112D4E] font-bold transition-colors duration-200">
            </div>
        </div>
    </div>

    @if (($item->children_count ?? 0) > 0 || ($item->relationLoaded('children') && $item->children->count() > 0))
        <div class="children-container ml-5 sm:ml-10 mt-2 border-l-2 border-gray-100 pl-4 space-y-2 {{ isset($forceShowChildren) && $forceShowChildren ? '' : 'hidden' }}">
            @if ($item->relationLoaded('children') && $item->children->count() > 0)
                @php
                    $sortedChildren = $item->children->sortBy(function($child) {
                        return str_pad(strlen($child->kode_satker), 2, '0', STR_PAD_LEFT) . '-' . $child->kode_satker;
                    });
                @endphp
                @foreach ($sortedChildren as $child)
                    @include('admin.setting-kode._item_edit_manual', [
                        'item' => $child,
                        'forceShowChildren' => isset($matchedIds) && in_array($child->id, $matchedIds) ? true : (isset($forceShowChildren) && $forceShowChildren),
                        'matchedIds' => $matchedIds ?? []
                    ])
                @endforeach
            @endif
        </div>
    @endif
</div>