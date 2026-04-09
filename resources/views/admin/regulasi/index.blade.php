@extends('layouts.admin')
@section('title', 'Regulasi Penugasan')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-800">Regulasi & Aturan Penugasan</h2>
    <p class="text-sm text-slate-500">Atur logika rangkap jabatan, kuota maksimal, dan aturan cuti untuk masing-masing peran.</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[11px] tracking-wider border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Tipe Peran</th>
                    <th class="px-6 py-4">Nama Peran</th>
                    <th class="px-6 py-4 text-center">Detail Regulasi</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {{-- LOOP ROLES --}}
                @foreach ($roles as $role)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4"><span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-[10px] font-bold">ADMIN / SISTEM</span></td>
                    <td class="px-6 py-4 font-bold text-slate-800">{{ $role->nama }}</td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" onclick="openViewModal('{{ $role->nama }}', {{ json_encode($role->regulations ?? []) }})" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition text-xs font-bold">
                            <i class="fas fa-eye mr-1"></i> Lihat Regulasi
                        </button>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" onclick="openRegulasiModal({{ $role->id }}, 'role', '{{ $role->nama }}', {{ json_encode($role->regulations ?? []) }}, {{ json_encode($penugasans) }})" class="text-[#112D4E] hover:text-blue-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-xs font-bold">
                            <i class="fas fa-gavel mr-1"></i> Edit Regulasi
                        </button>
                    </td>
                </tr>
                @endforeach

                @foreach ($penugasans as $jp)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4"><span class="bg-amber-100 text-amber-700 px-2 py-1 rounded text-[10px] font-bold">PEJABAT (STRUKTURAL)</span></td>
                    <td class="px-6 py-4 font-bold text-slate-800">{{ $jp->nama }}</td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" onclick="openViewModal('{{ $jp->nama }}', {{ json_encode($jp->regulations ?? []) }})" class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition text-xs font-bold">
                            <i class="fas fa-eye mr-1"></i> Lihat Regulasi
                        </button>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" onclick="openRegulasiModal({{ $jp->id }}, 'penugasan', '{{ $jp->nama }}', {{ json_encode($jp->regulations ?? []) }}, {{ json_encode($penugasans) }})" class="text-[#112D4E] hover:text-blue-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition text-xs font-bold">
                            <i class="fas fa-gavel mr-1"></i> Edit Regulasi
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL LIHAT DETAIL REGULASI --}}
<div id="modalViewRegulasi" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-10 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalViewRegulasi')"></div>
        <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-slate-50 rounded-2xl shadow-xl z-50 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 bg-white border-b flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-slate-800">Detail Regulasi: <span id="view_reg_nama" class="text-blue-600"></span></h3>
                <button type="button" onclick="toggleModal('modalViewRegulasi')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6 overflow-y-auto space-y-4" id="view_reg_content">
                {{-- Konten di-inject melalui JS --}}
            </div>
            <div class="p-4 bg-white border-t flex justify-end shrink-0">
                <button type="button" onclick="toggleModal('modalViewRegulasi')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EDIT REGULASI --}}
<div id="modalRegulasi" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 py-10 text-center sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="toggleModal('modalRegulasi')"></div>
        <div class="inline-block w-full max-w-2xl overflow-hidden text-left align-middle transition-all transform bg-slate-50 rounded-2xl shadow-xl z-50 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 bg-white border-b flex justify-between items-center shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Edit Regulasi: <span id="reg_nama" class="text-blue-600"></span></h3>
                </div>
                <button type="button" onclick="toggleModal('modalRegulasi')" class="text-gray-400 hover:text-gray-600 text-xl"><i class="fas fa-times"></i></button>
            </div>

            <form id="formRegulasi" method="POST" class="flex flex-col overflow-hidden h-full">
                @csrf
                @method('PUT')
                <input type="hidden" name="target_type" id="reg_target_type">
                
                <div class="p-6 overflow-y-auto space-y-6">
                    
                    {{-- Q1: KUOTA SATKER --}}
                    <div class="bg-white p-4 border rounded-xl shadow-sm">
                        <h4 class="font-bold text-slate-700 text-sm mb-3">1. Apakah boleh ada lebih dari 1 orang di peran ini dalam satu Satker yang sama?</h4>
                        <div class="flex gap-4 mb-3">
                            <label><input type="radio" name="regulations[allow_multiple]" value="0" class="mr-1" onchange="toggleQuota(false)"> Tidak Bisa (Hanya 1)</label>
                            <label><input type="radio" name="regulations[allow_multiple]" value="1" class="mr-1" onchange="toggleQuota(true)"> Bisa Banyak</label>
                        </div>
                        <div id="wrap_quota" class="hidden mt-2 p-3 bg-slate-50 border rounded-lg">
                            <label class="text-xs font-bold text-slate-600">Maksimal Kuota (Orang):</label>
                            <input type="number" name="regulations[max_multiple]" id="input_quota" class="w-full mt-1 border px-3 py-1.5 rounded-lg text-sm" placeholder="Isi 0 untuk tanpa batas">
                        </div>
                    </div>

                    {{-- Q2 & Q3: RANGKAP SESAMA ADMIN --}}
                    <div class="bg-white p-4 border rounded-xl shadow-sm">
                        <h4 class="font-bold text-slate-700 text-sm mb-3">2. Aturan Rangkap Peran/Admin</h4>
                        <label class="flex items-center mb-2">
                            <input type="checkbox" name="regulations[same_role_other_satker]" value="1" class="cb-reg mr-2 rounded"> Dapat menjadi peran yang sama di Satker Lain
                        </label>
                        <label class="flex items-center mb-2">
                            <input type="checkbox" name="regulations[other_admin_same_satker]" value="1" class="cb-reg mr-2 rounded"> Dapat merangkap menjadi peran Admin/Sistem LAIN di Satker yang sama
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="regulations[other_admin_other_satker]" value="1" class="cb-reg mr-2 rounded"> Dapat merangkap menjadi peran Admin/Sistem LAIN di Satker Lain
                        </label>
                    </div>

                    {{-- Q4: RANGKAP PEJABAT SATKER SAMA --}}
                    <div class="bg-white p-4 border rounded-xl shadow-sm">
                        <h4 class="font-bold text-slate-700 text-sm mb-3">3. Bisa merangkap menjadi Pejabat (Struktural) di Satker yang SAMA?</h4>
                        <div class="flex gap-4 mb-2">
                            <label><input type="radio" name="regulations[rangkap_pejabat_same_satker]" value="0" class="mr-1" onchange="togglePejabatOptions('same', false)"> Tidak Bisa</label>
                            <label><input type="radio" name="regulations[rangkap_pejabat_same_satker]" value="1" class="mr-1" onchange="togglePejabatOptions('same', true)"> Ya, Bisa</label>
                        </div>
                        <div id="wrap_pejabat_same" class="hidden ml-6 grid grid-cols-3 gap-2 mt-2">
                            @foreach($penugasans as $jp)
                            <label><input type="checkbox" name="regulations[allowed_pejabat_same_satker][]" value="{{ $jp->id }}" class="mr-1 rounded cb-pejabat-same"> {{ $jp->nama }}</label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Q5: RANGKAP PEJABAT SATKER LAIN --}}
                    <div class="bg-white p-4 border rounded-xl shadow-sm">
                        <h4 class="font-bold text-slate-700 text-sm mb-3">4. Bisa merangkap menjadi Pejabat (Struktural) di Satker LAIN?</h4>
                        <div class="flex gap-4 mb-2">
                            <label><input type="radio" name="regulations[rangkap_pejabat_other_satker]" value="0" class="mr-1" onchange="togglePejabatOptions('other', false)"> Tidak Bisa</label>
                            <label><input type="radio" name="regulations[rangkap_pejabat_other_satker]" value="1" class="mr-1" onchange="togglePejabatOptions('other', true)"> Ya, Bisa</label>
                        </div>
                        <div id="wrap_pejabat_other" class="hidden ml-6 grid grid-cols-3 gap-2 mt-2">
                            @foreach($penugasans as $jp)
                            <label><input type="checkbox" name="regulations[allowed_pejabat_other_satker][]" value="{{ $jp->id }}" class="mr-1 rounded cb-pejabat-other"> {{ $jp->nama }}</label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Q6: PENGGANTI CUTI --}}
                    <div class="bg-white p-4 border rounded-xl shadow-sm">
                        <h4 class="font-bold text-slate-700 text-sm mb-3">5. Jika sedang CUTI, apakah posisinya dapat digantikan sementara?</h4>
                        <div class="flex gap-4 mb-2">
                            <label><input type="radio" name="regulations[cuti_replaceable]" value="0" class="mr-1" onchange="toggleCutiOptions(false)"> Tidak Bisa</label>
                            <label><input type="radio" name="regulations[cuti_replaceable]" value="1" class="mr-1" onchange="toggleCutiOptions(true)"> Ya, Bisa Digantikan</label>
                        </div>
                        <div id="wrap_cuti" class="hidden ml-6 mt-3 bg-slate-50 p-3 rounded-lg border">
                            <p class="text-xs font-bold text-slate-500 mb-2">Peran apa saja yang bisa menjadi penggantinya?</p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <label class="col-span-2 border-b pb-2 mb-1 font-bold text-slate-700">Pilih dari Pejabat Struktural:</label>
                                @foreach($penugasans as $jp)
                                <label><input type="checkbox" name="regulations[cuti_replacement_roles][]" value="penugasan_{{ $jp->id }}" class="mr-1 rounded cb-cuti-roles"> {{ $jp->nama }}</label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Q7: SYARAT PENOLAKAN PENAMBAHAN (KONDISI KOSONG) --}}
                    <div class="bg-red-50 p-4 border border-red-100 rounded-xl shadow-sm">
                        <h4 class="font-bold text-red-800 text-sm mb-2"><i class="fas fa-ban mr-1"></i> 6. Syarat Penolakan Penambahan</h4>
                        <p class="text-[11px] text-red-600 mb-3">
                            Peran ini <b>TIDAK BISA DITAMBAHKAN</b> jika di Satker tersebut masih ada peran di bawah ini yang sedang <b>Aktif bekerja (Tidak Cuti)</b>.
                        </p>
                        
                        <div class="space-y-4">
                            {{-- Kelompok Pejabat --}}
                            <div>
                                <label class="block text-[10px] font-black text-red-900/50 uppercase tracking-widest mb-2">Jenis Penugasan (Pejabat)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($penugasans as $jp)
                                        <label class="flex items-center p-2 bg-white border border-red-100 rounded-lg hover:bg-red-100/50 transition cursor-pointer">
                                            <input type="checkbox" name="regulations[requires_absence_of][]" value="jp_{{ $jp->id }}" class="cb-requires-absence mr-2 rounded text-red-600 focus:ring-red-500">
                                            <span class="text-xs font-medium text-slate-700">{{ $jp->nama }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Kelompok Admin --}}
                            <div>
                                <label class="block text-[10px] font-black text-red-900/50 uppercase tracking-widest mb-2">Role Sistem (Admin)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($roles as $rl)
                                        <label class="flex items-center p-2 bg-white border border-red-100 rounded-lg hover:bg-red-100/50 transition cursor-pointer">
                                            <input type="checkbox" name="regulations[requires_absence_of][]" value="role_{{ $rl->id }}" class="cb-requires-absence mr-2 rounded text-red-600 focus:ring-red-500">
                                            <span class="text-xs font-medium text-slate-700">{{ $rl->nama }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-[10px] text-slate-500 mt-3 italic">
                            *Contoh: Jika peran ini adalah PLT, maka checklist "Definitif".
                        </p>
                    </div>

                </div>

                <div class="p-6 bg-white border-t flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="toggleModal('modalRegulasi')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">Batal</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-[#112D4E] hover:bg-blue-900 rounded-xl">Simpan Regulasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Dictionary untuk translate ID ke Nama Penugasan di JS --}}
<script>
    const mapPenugasan = {
        @foreach($penugasans as $p)
        "{{ $p->id }}": "{{ $p->nama }}",
        @endforeach
        @foreach($roles as $r)
        "{{ $r->id }}": "{{ $r->nama }}",
        @endforeach
    };
</script>

<script>
    function toggleModal(modalId) {
        document.getElementById(modalId).classList.toggle('hidden');
    }

    function toggleQuota(show) { document.getElementById('wrap_quota').style.display = show ? 'block' : 'none'; }
    function togglePejabatOptions(target, show) { document.getElementById('wrap_pejabat_' + target).style.display = show ? 'grid' : 'none'; }
    function toggleCutiOptions(show) { document.getElementById('wrap_cuti').style.display = show ? 'block' : 'none'; }

    // FUNGSI UNTUK MODAL LIHAT DETAIL
    function openViewModal(nama, regData) {
        document.getElementById('view_reg_nama').innerText = nama;
        let html = '';

        if (!regData || Object.keys(regData).length === 0) {
            html = '<div class="p-5 bg-amber-50 text-amber-800 rounded-xl border border-amber-200 flex items-center gap-3"><i class="fas fa-exclamation-triangle text-xl"></i> <div><b>Belum Diatur!</b><p class="text-sm">Peran ini belum memiliki konfigurasi regulasi khusus. Sistem akan menerapkan aturan bawaan (Kuota maksimal 1 orang & tidak bisa merangkap jabatan apa pun).</p></div></div>';
        } else {
            const yes = '<span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-xs"><i class="fas fa-check-circle mr-1"></i> Ya</span>';
            const no = '<span class="text-red-500 font-bold bg-red-50 px-2 py-0.5 rounded border border-red-200 text-xs"><i class="fas fa-times-circle mr-1"></i> Tidak</span>';

            let kuota = (regData.allow_multiple == "1") ? `Bisa Banyak (Maks: <span class="font-bold text-blue-600">${regData.max_multiple > 0 ? regData.max_multiple + ' Orang' : 'Tak Terbatas'}</span>)` : '<span class="font-bold text-red-600">Hanya 1 Orang (Mutlak)</span>';
            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">1. Kuota Per Satker</h4><p class="text-sm text-slate-700">${kuota}</p></div>`;

            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-3">2. Aturan Rangkap Admin / Sistem</h4><ul class="space-y-3 text-sm text-slate-700">`;
            html += `<li class="flex justify-between items-center border-b pb-2 border-dashed border-gray-200"><span>Merangkap peran yang SAMA di Satker Lain</span> ${regData.same_role_other_satker == "1" ? yes : no}</li>`;
            html += `<li class="flex justify-between items-center border-b pb-2 border-dashed border-gray-200"><span>Merangkap peran sistem LAIN di Satker SAMA</span> ${regData.other_admin_same_satker == "1" ? yes : no}</li>`;
            html += `<li class="flex justify-between items-center"><span>Merangkap peran sistem LAIN di Satker LAIN</span> ${regData.other_admin_other_satker == "1" ? yes : no}</li>`;
            html += `</ul></div>`;

            let pSame = regData.rangkap_pejabat_same_satker == "1" ? yes : no;
            if (regData.rangkap_pejabat_same_satker == "1" && regData.allowed_pejabat_same_satker) {
                let roles = regData.allowed_pejabat_same_satker.map(id => mapPenugasan[id] || id).join(', ');
                pSame += `<div class="mt-2 p-2 bg-slate-50 border rounded-lg text-xs text-slate-600">Diizinkan merangkap sebagai: <b>${roles}</b></div>`;
            }
            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">3. Rangkap Pejabat Struktural di Satker SAMA</h4><div class="text-sm text-slate-700 flex flex-col items-start">${pSame}</div></div>`;

            let pOther = regData.rangkap_pejabat_other_satker == "1" ? yes : no;
            if (regData.rangkap_pejabat_other_satker == "1" && regData.allowed_pejabat_other_satker) {
                let roles = regData.allowed_pejabat_other_satker.map(id => mapPenugasan[id] || id).join(', ');
                pOther += `<div class="mt-2 p-2 bg-slate-50 border rounded-lg text-xs text-slate-600">Diizinkan merangkap sebagai: <b>${roles}</b></div>`;
            }
            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">4. Rangkap Pejabat Struktural di Satker LAIN</h4><div class="text-sm text-slate-700 flex flex-col items-start">${pOther}</div></div>`;

            let cuti = regData.cuti_replaceable == "1" ? yes : no;
            if (regData.cuti_replaceable == "1" && regData.cuti_replacement_roles) {
                let roles = regData.cuti_replacement_roles.map(val => {
                    let id = val.replace('penugasan_', '');
                    return mapPenugasan[id] || id;
                }).join(', ');
                cuti += `<div class="mt-2 p-2 bg-slate-50 border rounded-lg text-xs text-slate-600">Dapat digantikan sementara oleh: <b>${roles}</b></div>`;
            }
            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">5. Aturan Pengganti Cuti</h4><div class="text-sm text-slate-700 flex flex-col items-start">${cuti}</div></div>`;
            
            let reqAbs = '';
            if (regData.requires_absence_of && (Array.isArray(regData.requires_absence_of) || Object.values(regData.requires_absence_of).length > 0)) {
                let absenceData = Array.isArray(regData.requires_absence_of) ? regData.requires_absence_of : Object.values(regData.requires_absence_of);
                let reqRoles = absenceData.map(val => {
                    let id = val.replace('jp_', '').replace('role_', '');
                    return mapPenugasan[id] || val;
                }).join(', ');
                reqAbs = `<div class="mt-2 p-2 bg-red-50 text-red-700 border border-red-200 rounded-lg text-xs">Tertolak jika peran ini sedang aktif: <b>${reqRoles}</b></div>`;
            }
            html += `<div class="bg-white p-4 border rounded-xl shadow-sm"><h4 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">6. Syarat Kekosongan</h4><div class="text-sm text-slate-700">${regData.requires_absence_of ? reqAbs : '<span class="text-slate-500 italic">Bebas ditambahkan kapan saja</span>'}</div></div>`;
        }

        document.getElementById('view_reg_content').innerHTML = html;
        toggleModal('modalViewRegulasi');
    }

    // FUNGSI UNTUK MODAL EDIT REGULASI
    function openRegulasiModal(id, type, nama, regData, penugasansList) {
        document.getElementById('reg_nama').innerText = nama;
        document.getElementById('reg_target_type').value = type;
        
        document.getElementById('formRegulasi').reset();
        
        // Reset SEMUA checkbox
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        
        toggleQuota(false); togglePejabatOptions('same', false); togglePejabatOptions('other', false); toggleCutiOptions(false);

        if (regData) {
            if (regData.allow_multiple == "1") { document.querySelector('input[name="regulations[allow_multiple]"][value="1"]').checked = true; toggleQuota(true); document.getElementById('input_quota').value = regData.max_multiple || ''; } else { document.querySelector('input[name="regulations[allow_multiple]"][value="0"]').checked = true; }

            ['same_role_other_satker', 'other_admin_same_satker', 'other_admin_other_satker'].forEach(key => {
                if (regData[key] == "1") { let cb = document.querySelector(`input[name="regulations[${key}]"]`); if (cb) cb.checked = true; }
            });

            if (regData.rangkap_pejabat_same_satker == "1") { document.querySelector('input[name="regulations[rangkap_pejabat_same_satker]"][value="1"]').checked = true; togglePejabatOptions('same', true); } else { document.querySelector('input[name="regulations[rangkap_pejabat_same_satker]"][value="0"]').checked = true; }
            if (regData.rangkap_pejabat_other_satker == "1") { document.querySelector('input[name="regulations[rangkap_pejabat_other_satker]"][value="1"]').checked = true; togglePejabatOptions('other', true); } else { document.querySelector('input[name="regulations[rangkap_pejabat_other_satker]"][value="0"]').checked = true; }
            if (regData.cuti_replaceable == "1") { document.querySelector('input[name="regulations[cuti_replaceable]"][value="1"]').checked = true; toggleCutiOptions(true); } else { document.querySelector('input[name="regulations[cuti_replaceable]"][value="0"]').checked = true; }

            if (regData.allowed_pejabat_same_satker) regData.allowed_pejabat_same_satker.forEach(val => { let cb = document.querySelector(`.cb-pejabat-same[value="${val}"]`); if (cb) cb.checked = true; });
            if (regData.allowed_pejabat_other_satker) regData.allowed_pejabat_other_satker.forEach(val => { let cb = document.querySelector(`.cb-pejabat-other[value="${val}"]`); if (cb) cb.checked = true; });
            if (regData.cuti_replacement_roles) regData.cuti_replacement_roles.forEach(val => { let cb = document.querySelector(`.cb-cuti-roles[value="${val}"]`); if (cb) cb.checked = true; });
            
            // LOGIKA BARU: Checklist untuk Syarat Penolakan Penambahan (Diperbaiki)
            if (regData.requires_absence_of) {
                let absenceData = Array.isArray(regData.requires_absence_of) 
                                  ? regData.requires_absence_of 
                                  : Object.values(regData.requires_absence_of);
                
                absenceData.forEach(val => {
                    let cb = document.querySelector(`input[type="checkbox"][value="${val}"]`);
                    if (cb) cb.checked = true;
                });
            }
        } else {
            document.querySelector('input[name="regulations[allow_multiple]"][value="0"]').checked = true;
            document.querySelector('input[name="regulations[rangkap_pejabat_same_satker]"][value="0"]').checked = true;
            document.querySelector('input[name="regulations[rangkap_pejabat_other_satker]"][value="0"]').checked = true;
            document.querySelector('input[name="regulations[cuti_replaceable]"][value="0"]').checked = true;
        }

        let baseUrlRegulasi = "{{ url('admin/regulasi') }}";
        document.getElementById('formRegulasi').action = `${baseUrlRegulasi}/${id}`;
        toggleModal('modalRegulasi');
    }
</script>

{{-- EFEK LOADING DAN NOTIFIKASI SWEETALERT --}}
@if(session('success'))
<script>document.addEventListener('DOMContentLoaded', function() { Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{!! session('success') !!}", timer: 3000, showConfirmButton: false }); });</script>
@endif
@if(session('error') || $errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let err = ''; @if(session('error')) err += '<p>{!! session('error') !!}</p>'; @endif
        @if($errors->any()) @foreach($errors->all() as $error) err += '<p>{{ $error }}</p>'; @endforeach @endif
        Swal.fire({ icon: 'error', title: 'Gagal Memproses', html: err });
    });
</script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) return;
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    if (this.dataset.submitted === 'true') { e.preventDefault(); return; }
                    this.dataset.submitted = 'true';
                    btn.disabled = true; btn.classList.add('opacity-70', 'cursor-not-allowed');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                }
            });
        });
    });
</script>
@endpush