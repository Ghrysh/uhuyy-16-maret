<div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="relative w-full max-w-3xl p-4" @click.away="editModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl">
            <div class="flex justify-between p-4 border-b bg-gray-50">
                <h3 class="text-lg font-bold text-[#112D4E]">Edit Setup</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times fa-lg"></i></button>
            </div>
            <form id="edit-rumus-form" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="is_auto_number" id="edit_is_auto_number">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="text-xs font-bold">Nama Setup</label><input type="text" id="edit_nama_rumus" name="nama_rumus" class="w-full border p-2 text-sm rounded mt-1"></div>
                    <div><label class="text-xs font-bold">Default Naming</label><input type="text" id="edit_default_nama_satker" name="default_nama_satker" class="w-full border p-2 text-sm rounded mt-1"></div>
                    <div>
                        <label class="text-xs font-bold">Filter Wilayah</label>
                        <select id="edit_tingkat_wilayah_id" name="tingkat_wilayah_id" class="w-full border p-2 text-sm rounded mt-1">
                            <option value="">-- Semua --</option>
                            @foreach($tingkatWilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold">Filter Level</label>
                        <select id="edit_jenis_satker_id" name="jenis_satker_id" class="w-full border p-2 text-sm rounded mt-1"><option value="">-- Semua --</option>@foreach($jenisSatkers as $j)<option value="{{ $j->id }}">{{ $j->nama }}</option>@endforeach</select>
                    </div>
                    <div>
                        <label class="text-xs font-bold">Filter Jabatan</label>
                        <select id="edit_ref_jabatan_satker_id" name="ref_jabatan_satker_id" class="w-full border p-2 text-sm rounded mt-1"><option value="">-- Semua --</option>@foreach($refJabatans as $r)<option value="{{ $r->id }}">{{ $r->label_jabatan }}</option>@endforeach</select>
                    </div>
                    <div><label class="text-xs font-bold">Kode Awalan / Fix</label><input type="text" id="edit_kode_awalan" name="kode_awalan" class="w-full border p-2 text-sm rounded mt-1 font-mono font-bold text-blue-700"></div>
                    <div id="edit_digit_wrapper"><label class="text-xs font-bold">Digit Increment</label><input type="number" id="edit_digit_auto_number" name="digit_auto_number" class="w-full border p-2 text-sm rounded mt-1 font-mono font-bold text-amber-600"></div>
                </div>
                <div class="flex justify-end pt-4"><button type="submit" class="bg-[#112D4E] text-white px-4 py-2 rounded-md font-bold">Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
</div>