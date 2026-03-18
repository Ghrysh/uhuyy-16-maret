@php
    $selfText = strtolower($item->nama_satker . ' ' . $item->kode_satker);
@endphp

<div x-data="{
    open: false,
    selfText: '{{ $selfText }}',
    hasScrolled: false,

    get isVisible() {
        if (search === '') return true;
        if (this.selfText.includes(search.toLowerCase())) return true;
        return $el.querySelectorAll('.satker-row:not([style*=\'display: none\'])').length > 0;
    },

    init() {
        this.$watch('search', value => {
            if (!value) {
                this.hasScrolled = false;
                return;
            }
            this.$nextTick(() => {
                let isMatch = this.selfText.includes(value.toLowerCase());
                if (isMatch && !this.hasScrolled) {
                    this.hasScrolled = true;
                    this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    }
}" x-show="isVisible" class="satker-item w-full">

    <div class="satker-row flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 sm:p-3 
    bg-white hover:bg-blue-50/50 rounded-xl transition border border-slate-100 
    sm:border-transparent sm:hover:border-blue-100 group"
        :class="search !== '' && selfText.includes(search.toLowerCase()) ? 'bg-amber-50 border-amber-200 ring-1 ring-amber-200' : ''">

        <div class="flex items-start gap-3 min-w-0">
            <div class="text-slate-400 cursor-pointer w-5 flex-shrink-0 mt-1" @click="open = !open">
                @if ($item->children && $item->children->count() > 0)
                    <i class="fas fa-chevron-right text-xs transition-transform duration-200" :class="(open || search !== '') ? 'rotate-90' : ''"></i>
                @else
                    <i class="fas fa-circle text-[5px] ml-1"></i>
                @endif
            </div>

            <div class="hidden sm:flex w-9 h-9 rounded-lg bg-gray-50 items-center justify-center text-slate-400 group-hover:bg-white group-hover:text-[#112D4E] transition">
                <i class="fas fa-building text-sm"></i>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm sm:text-base font-semibold text-slate-700 break-words" :class="search !== '' && '{{ strtolower($item->nama_satker) }}'.includes(search.toLowerCase()) ? 'text-amber-700' : ''">
                        {{ $item->nama_satker }}
                    </span>
                    @if ($item->eselon)
                        <span class="px-2 py-0.5 text-[10px] text-white font-semibold rounded uppercase"
                            style="background-color: {{ $item->jenis_satker_id == 1 ? '#112D4E' : ($item->jenis_satker_id == 2 ? '#3F72AF' : '#607d8b') }}">
                            {{ $item->eselon->nama }}
                        </span>
                    @endif
                </div>
                <div class="mt-1 text-xs font-medium tracking-wide text-slate-400">
                    Kode Lama: <span class="font-mono text-gray-500">{{ $item->kode_satker }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center border-t sm:border-t-0 sm:border-l border-slate-200 pt-3 sm:pt-0 sm:pl-4 mt-2 sm:mt-0">
            <form action="{{ route('admin.setting-kode.updateManual', $item->id) }}" method="POST" class="flex items-end gap-2">
                @csrf @method('PUT')
                <div>
                    <input type="text" name="kode_satker_baru" value="{{ $item->kode_satker }}" class="border-gray-300 rounded-md shadow-sm px-2 py-1.5 border w-48 font-mono text-sm focus:ring-[#112D4E] focus:border-[#112D4E] bg-white text-[#112D4E] font-bold">
                </div>
                <button type="submit" class="bg-gray-100 hover:bg-[#112D4E] hover:text-white border border-gray-300 hover:border-[#112D4E] text-gray-700 px-3 py-1.5 rounded-md text-xs font-bold transition shadow-sm h-[34px] flex items-center justify-center">
                    <i class="fas fa-save sm:mr-1"></i> <span class="hidden sm:inline">Update</span>
                </button>
            </form>
        </div>
    </div>

    @if ($item->children && $item->children->count() > 0)
        <div x-show="open || search !== ''" class="ml-5 sm:ml-10 mt-2 border-l-2 border-gray-100 pl-4 space-y-2">
            @foreach ($item->children as $child)
                @include('admin.setting-kode._item_edit_manual', ['item' => $child])
            @endforeach
        </div>
    @endif
</div>