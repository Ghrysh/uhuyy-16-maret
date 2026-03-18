<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-[#112D4E] text-white">
        <tr>
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Detail Target & Kriteria</th>
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Hasil Logika (Pola)</th>
            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Status & Terapkan</th>
            <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach($rumusList->filter(function($item) use ($isAuto) { return isset($item->is_auto_number) && (bool)$item->is_auto_number === $isAuto; }) as $r)
        
        @php
            $wilayah = $tingkatWilayahs->firstWhere('id', $r->tingkat_wilayah_id);
            $jenis = $jenisSatkers->firstWhere('id', $r->jenis_satker_id);
            $jabatan = $refJabatans->firstWhere('id', $r->ref_jabatan_satker_id);
        @endphp

        <tr class="hover:bg-blue-50/30 transition">
            <td class="px-4 py-4 text-sm text-gray-900">
                <div class="font-bold text-[#112D4E] text-base mb-2">{{ $r->nama_rumus }}</div>
                
                <div class="space-y-1.5 bg-gray-50 p-2 rounded border border-gray-100">
                    <div class="text-xs text-gray-600 flex items-center">
                        <span class="w-5 text-center text-gray-400 mr-1"><i class="fas fa-map-marker-alt"></i></span> 
                        <span>Wilayah: <b class="text-gray-800 ml-1">{{ $wilayah ? $wilayah->nama : 'Semua Wilayah' }}</b></span>
                    </div>
                    <div class="text-xs text-gray-600 flex items-center">
                        <span class="w-5 text-center text-gray-400 mr-1"><i class="fas fa-layer-group"></i></span> 
                        <span>Level Satker: <b class="text-gray-800 ml-1">{{ $jenis ? $jenis->nama : 'Semua Level' }}</b></span>
                    </div>
                    <div class="text-xs text-gray-600 flex items-center">
                        <span class="w-5 text-center text-gray-400 mr-1"><i class="fas fa-user-tie"></i></span> 
                        <span>Status Jabatan: <b class="text-gray-800 ml-1">{{ $jabatan ? $jabatan->label_jabatan : 'Semua Jabatan' }}</b></span>
                    </div>
                </div>

                @if(isset($r->default_nama_satker) && $r->default_nama_satker) 
                    <div class="text-xs bg-amber-50 text-amber-700 border border-amber-200 rounded px-2 py-1 mt-2 inline-block font-medium">
                        <i class="fas fa-font mr-1"></i> Default Nama: <b>{{ $r->default_nama_satker }}</b>
                    </div> 
                @endif
            </td>
            <td class="px-4 py-3 text-sm font-mono text-blue-700 font-bold">
                <span class="bg-blue-50 border border-blue-200 px-2 py-1.5 rounded">{{ $r->pola }}</span>
            </td>
            <td class="px-4 py-3 text-center">
                <form action="{{ route('admin.setting-kode.applyRumus', $r->id) }}" method="POST">
                    @csrf
                    @if($r->is_applied)
                        <button type="button" onclick="confirmApply(this)" class="bg-green-100 text-green-800 border-green-300 px-3 py-1.5 rounded-full text-xs font-bold border shadow-sm transition hover:bg-green-200">
                            <i class="fas fa-check-circle mr-1"></i> Sedang Dipakai
                        </button>
                    @else
                        <button type="button" onclick="confirmApply(this)" class="bg-white hover:bg-yellow-50 text-gray-600 hover:text-yellow-700 border-gray-300 hover:border-yellow-400 px-3 py-1.5 rounded-full text-xs font-bold border shadow-sm transition">
                            <i class="fas fa-play mr-1"></i> Gunakan Rumus
                        </button>
                    @endif
                </form>
            </td>
            <td class="px-4 py-3 text-center space-x-1">
                <button type="button" onclick="editRumus(this)" 
                    data-id="{{ $r->id }}" 
                    data-nama="{{ $r->nama_rumus }}" 
                    data-isauto="{{ $r->is_auto_number }}" 
                    data-wilayah="{{ $r->tingkat_wilayah_id ?? '' }}" 
                    data-jenis="{{ $r->jenis_satker_id ?? '' }}" 
                    data-ref="{{ $r->ref_jabatan_satker_id ?? '' }}" 
                    data-awalan="{{ $r->kode_awalan ?? '' }}" 
                    data-digit="{{ $r->digit_auto_number ?? '' }}" 
                    data-defaultnama="{{ $r->default_nama_satker ?? '' }}" 
                    class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded shadow-sm text-xs transition">
                    <i class="fas fa-edit"></i>
                </button>
                <form action="{{ route('admin.setting-kode.destroyRumus', $r->id) }}" method="POST" class="inline-block">
                    @csrf @method('DELETE')
                    <button type="button" onclick="confirmDelete(this)" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded shadow-sm text-xs transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>