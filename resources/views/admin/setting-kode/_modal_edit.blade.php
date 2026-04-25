<div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="relative w-full max-w-3xl p-4 max-h-screen overflow-y-auto" @click.away="editModalOpen = false">
        <div class="bg-white rounded-xl shadow-2xl">
            <div class="flex justify-between p-4 border-b bg-gray-50 rounded-t-xl">
                <h3 class="text-lg font-bold text-[#112D4E]">Edit Setup</h3>
                <button @click="editModalOpen = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times fa-lg"></i></button>
            </div>
            
            <form id="edit-rumus-form" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="is_auto_number" id="edit_is_auto_number">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="text-xs font-bold">Nama Setup</label>
                        <input type="text" id="edit_nama_rumus" name="nama_rumus" class="w-full border p-2 text-sm rounded mt-1">
                    </div>
                    
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
                        <select id="edit_jenis_satker_id" name="jenis_satker_id" class="w-full border p-2 text-sm rounded mt-1">
                            <option value="">-- Semua --</option>
                            @foreach($jenisSatkers as $j)
                                <option value="{{ $j->id }}">{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-bold">Filter Jabatan</label>
                        <select id="edit_ref_jabatan_satker_id" name="ref_jabatan_satker_id" class="w-full border p-2 text-sm rounded mt-1">
                            <option value="">-- Semua --</option>
                            @foreach($refJabatans as $r)
                                <option value="{{ $r->id }}">{{ $r->label_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div></div> {{-- Empty space --}}
                    
                    <div>
                        <label class="text-xs font-bold">Kode Awalan / Fix</label>
                        <input type="text" id="edit_kode_awalan" name="kode_awalan" class="w-full border p-2 text-sm rounded mt-1 font-mono font-bold text-blue-700">
                    </div>
                    <div id="edit_digit_wrapper">
                        <label class="text-xs font-bold">Digit Increment</label>
                        <input type="number" id="edit_digit_auto_number" name="digit_auto_number" class="w-full border p-2 text-sm rounded mt-1 font-mono font-bold text-amber-600">
                    </div>
                </div>

                {{-- FITUR BARU: NAMA SATKER OTOMATIS & MAPPING PADA EDIT --}}
                <div class="mt-4 p-4 border border-amber-200 rounded-xl bg-amber-50/30">
                    <div class="flex items-center gap-2 mb-3">
                        <input type="checkbox" id="edit_is_auto_name" name="is_auto_name" value="1" x-model="editData.is_auto_name" class="w-4 h-4 text-amber-600 rounded border-slate-300">
                        <label for="edit_is_auto_name" class="text-sm font-bold text-slate-700 cursor-pointer">Aktifkan Penamaan Satker Otomatis</label>
                    </div>

                    <div x-show="editData.is_auto_name" x-cloak x-transition class="space-y-4 border-t border-amber-100 pt-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Teks Nama Dasar</label>
                                <input type="text" name="base_auto_name" x-model="editData.base_auto_name" class="w-full rounded-lg border-slate-200 text-sm">
                            </div>
                            <div class="flex items-center pt-6">
                                <input type="checkbox" id="edit_is_name_locked" name="is_name_locked" value="1" x-model="editData.is_name_locked" class="w-4 h-4 text-amber-600 rounded border-slate-300">
                                <label for="edit_is_name_locked" class="ml-2 text-sm font-semibold text-slate-700 cursor-pointer">Kunci teks dasar ini?</label>
                            </div>
                        </div>

                        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-sm">
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-slate-700">Pemetaan per Ujung Kode</label>
                                <button type="button" @click="addEditMapItem()" class="text-xs bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-2 py-1 rounded font-bold transition">
                                    + Tambah
                                </button>
                            </div>

                            <template x-for="(item, index) in editData.custom_names_map" :key="index">
                                <div class="flex items-center gap-2 mb-2">
                                    <input type="text" x-model="item.code" placeholder="01" class="w-16 rounded border-slate-200 text-sm py-1.5 px-2 font-mono" required>
                                    <span class="text-slate-400 text-xs"><i class="fas fa-arrow-right"></i></span>
                                    <input type="text" x-model="item.name" placeholder="Nama Satker" class="flex-1 rounded border-slate-200 text-sm py-1.5 px-2" required>
                                    <button type="button" @click="removeEditMapItem(index)" class="p-1.5 text-red-500 hover:bg-red-50 rounded"><i class="fas fa-times"></i></button>
                                </div>
                            </template>
                            
                            <input type="hidden" name="custom_names_map" :value="getEditMapObject()">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-[#112D4E] text-white px-4 py-2 rounded-md font-bold hover:bg-blue-900 transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Alpine State & Fungsi Pembuka Modal --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('autoNameFormEdit', () => ({
            isAutoName: false,
            baseName: '',
            isLocked: false,
            customMaps: [],

            init() {
                // Dengarkan event kustom dari luar untuk mengisi data saat modal dibuka
                window.addEventListener('load-edit-auto-name', (e) => {
                    const data = e.detail;
                    this.isAutoName = data.isAutoName == 1 || data.isAutoName === true;
                    this.baseName = data.baseName || '';
                    this.isLocked = data.isLocked == 1 || data.isLocked === true;
                    
                    this.customMaps = [];
                    if (data.customMaps) {
                        try {
                            const parsedMap = typeof data.customMaps === 'string' ? JSON.parse(data.customMaps) : data.customMaps;
                            if (parsedMap && typeof parsedMap === 'object') {
                                Object.keys(parsedMap).forEach(key => {
                                    this.customMaps.push({ code: key, name: parsedMap[key] });
                                });
                            }
                        } catch (e) { console.error("Gagal parse custom maps", e); }
                    }
                });
            },

            addMapItem() { this.customMaps.push({ code: '', name: '' }); },
            removeMapItem(index) { this.customMaps.splice(index, 1); },
            getMapObject() {
                let obj = {};
                this.customMaps.forEach(m => { if (m.code && m.name) obj[m.code] = m.name; });
                return obj;
            }
        }));
    });

    // Update fungsi openEditModal yang ada di view index setting-kode Anda:
    function openEditModal(id, nama, tingkat, jenis, ref, pola, isAuto, autoNum, kodeAwalan, isAutoName, baseName, isLocked, customMapsJson) {
        document.getElementById('edit-rumus-form').action = `/admin/setting-kode/rumus/${id}`;
        document.getElementById('edit_nama_rumus').value = nama;
        document.getElementById('edit_tingkat_wilayah_id').value = tingkat || '';
        document.getElementById('edit_jenis_satker_id').value = jenis || '';
        document.getElementById('edit_ref_jabatan_satker_id').value = ref || '';
        document.getElementById('edit_is_auto_number').value = isAuto;
        document.getElementById('edit_kode_awalan').value = kodeAwalan || '';
        
        if(isAuto == 1) {
            document.getElementById('edit_digit_wrapper').style.display = 'block';
            document.getElementById('edit_digit_auto_number').value = autoNum || 2;
        } else {
            document.getElementById('edit_digit_wrapper').style.display = 'none';
            document.getElementById('edit_digit_auto_number').value = '';
        }

        // Tembakkan event untuk mengisi form Alpine Auto-Name
        window.dispatchEvent(new CustomEvent('load-edit-auto-name', {
            detail: {
                isAutoName: isAutoName,
                baseName: baseName,
                isLocked: isLocked,
                customMaps: customMapsJson
            }
        }));
    }
</script>