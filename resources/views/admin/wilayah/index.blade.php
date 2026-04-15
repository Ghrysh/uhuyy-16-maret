@extends('layouts.admin')

@section('title', 'Master Wilayah')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Master Wilayah</h2>
            <p class="text-sm text-slate-500">Kelola data wilayah Pusat, Provinsi, dan Kabupaten/Kota</p>
        </div>
        <button onclick="toggleModal('modalTambahWilayah')"
            class="bg-[#112D4E] hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm flex items-center transition shadow-sm">
            <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Wilayah
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-slate-500 text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4 font-bold">Kode</th>
                        <th class="px-6 py-4 font-bold">Nama Wilayah</th>
                        <th class="px-6 py-4 font-bold text-center">Tingkat</th>
                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($wilayahs as $item)
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="px-6 py-4 text-sm text-slate-600 font-mono font-medium">{{ $item->kode_wilayah }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="w-9 h-9 rounded-full bg-[#112D4E]/10 flex items-center justify-center mr-3 group-hover:bg-[#112D4E] group-hover:text-white transition-all duration-300 text-[#112D4E]">
                                        <i class="fas fa-location-dot text-xs"></i>
                                    </div>
                                    <span class="text-sm text-slate-700 font-semibold">{{ $item->nama_wilayah }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="bg-[#112D4E]/10 text-[#112D4E] text-[10px] px-3 py-1 rounded-full font-bold uppercase tracking-wider">
                                    {{ $item->tingkat->nama ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button"
                                    onclick="openEditModal('{{ $item->id }}', '{{ $item->kode_wilayah }}', '{{ $item->nama_wilayah }}', '{{ $item->tingkat_wilayah_id }}', '{{ $item->parent_wilayah_id }}')"
                                    class="p-2 text-slate-400 hover:text-[#112D4E] hover:bg-slate-100 rounded-lg transition">
                                    <i class="fas fa-pen-to-square text-sm"></i>
                                </button>

                                <button type="button"
                                    onclick="confirmDelete('{{ $item->id }}', '{{ $item->nama_wilayah }}')"
                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <i class="fas fa-trash-can text-sm"></i>
                                </button>

                                <form id="delete-form-{{ $item->id }}"
                                    action="{{ route('admin.wilayah.destroy', $item->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-map-location-dot text-slate-200 text-4xl mb-3"></i>
                                    <p class="text-slate-400 text-sm">Belum ada data wilayah ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
            {{ $wilayahs->withPath(url('admin/wilayah'))->links() }}
        </div>
    </div>

    <div id="modalTambahWilayah" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm"
                onclick="toggleModal('modalTambahWilayah')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Tambah Wilayah</h3>
                    <button onclick="toggleModal('modalTambahWilayah')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.wilayah.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Kode
                                Wilayah</label>
                            <input type="text" name="kode_wilayah" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 focus:border-[#112D4E] outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Nama
                                Wilayah</label>
                            <input type="text" name="nama_wilayah" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 focus:border-[#112D4E] outline-none transition">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Tingkat</label>
                                <select name="tingkat_wilayah_id" required
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 outline-none">
                                    <option value="">Pilih</option>
                                    @foreach ($tingkats as $t)
                                        <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Induk</label>
                                <select name="parent_wilayah_id"
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 outline-none">
                                    <option value="">Tidak ada</option>
                                    @foreach ($parents as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_wilayah }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal('modalTambahWilayah')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] text-white text-sm font-bold rounded-lg shadow-md hover:bg-slate-800 transition">
                            Tambah Wilayah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditWilayah" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm"
                onclick="toggleModal('modalEditWilayah')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div
                class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800">Edit Wilayah</h3>
                    <button onclick="toggleModal('modalEditWilayah')" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="formEditWilayah" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Kode
                                Wilayah</label>
                            <input type="text" name="kode_wilayah" id="edit_kode" readonly
                                class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-sm text-slate-500 cursor-not-allowed outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Nama
                                Wilayah</label>
                            <input type="text" name="nama_wilayah" id="edit_nama" required
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1 tracking-wider">Wilayah
                                Induk</label>
                            <select name="parent_wilayah_id" id="edit_parent"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-[#112D4E]/20 outline-none">
                                <option value="">Pilih wilayah induk</option>
                                @foreach ($parents as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_wilayah }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="tingkat_wilayah_id" id="edit_tingkat">
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="toggleModal('modalEditWilayah')"
                            class="px-4 py-2 text-sm font-semibold text-slate-600">Batal</button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#112D4E] hover:bg-slate-800 text-white text-sm font-bold rounded-lg shadow-md transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif
        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
            document.body.style.overflow = modal.classList.contains('hidden') ? 'auto' : 'hidden';
        }

        function openEditModal(id, kode, nama, tingkat_id, parent_id) {
            // Set action form secara dinamis menggunakan helper url() Laravel
            const form = document.getElementById('formEditWilayah');
            form.action = `{{ url('admin/wilayah') }}/${id}`;

            // Isi nilai input
            document.getElementById('edit_kode').value = kode;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_tingkat').value = tingkat_id;
            document.getElementById('edit_parent').value = parent_id === "" ? "" : parent_id;

            toggleModal('modalEditWilayah');
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.getElementById('modalTambahWilayah').classList.add('hidden');
                document.getElementById('modalEditWilayah').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });

        function confirmDelete(id, namaWilayah) {
            Swal.fire({
                title: 'Hapus Wilayah',
                text: `Apakah Anda yakin ingin menghapus wilayah ${namaWilayah}? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444', // Warna merah 
                cancelButtonColor: '#f1f5f9', // Warna slate-100
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'px-6 py-2 rounded-lg font-bold text-sm',
                    cancelButton: 'px-6 py-2 rounded-lg font-bold text-sm text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
@endpush
