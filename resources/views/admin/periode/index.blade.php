@extends('layouts.admin')

@section('title', 'Manajemen Periode')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Data Periode</h2>
            <p class="text-sm text-slate-500">Kelola periode aktif untuk sistem</p>
        </div>

        <button onclick="toggleModal('modalTambahPeriode')"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm flex items-center hover:bg-blue-700 transition shadow-sm font-medium">
            <i class="fas fa-plus mr-2 text-[10px]"></i> Tambah Periode
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-slate-500 text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4 font-bold">Nama Periode</th>
                        <th class="px-6 py-4 font-bold">Keterangan</th>
                        <th class="px-6 py-4 font-bold">Dibuat Pada</th>
                        <th class="px-6 py-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($periodes as $item)
                        <tr class="hover:bg-blue-50/30 transition group">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                {{ $item->nama_periode }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">
                                {{ $item->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-1">
                                    <button
                                        onclick="openEditPeriode('{{ $item->id }}', '{{ $item->nama_periode }}', '{{ $item->keterangan }}')"
                                        class="p-2 text-slate-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    <form action="{{ route('admin.periode.destroy', $item->id) }}" method="POST"
                                        class="inline">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete(this)"
                                            class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i class="fas fa-calendar-times text-slate-200 text-4xl mb-3 block"></i>
                                <span class="text-slate-400 text-sm italic">Belum ada data periode ditemukan</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div id="modalTambahPeriode" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 shadow-2xl">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleModal('modalTambahPeriode')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 overflow-hidden">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Periode Baru</h3>
                <form action="{{ route('admin.periode.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Periode</label>
                            <input type="text" name="nama_periode" required placeholder="Contoh: Tahun 2024"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Keterangan</label>
                            <textarea name="keterangan" rows="3" placeholder="Opsional..."
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="toggleModal('modalTambahPeriode')"
                            class="px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50 rounded-lg transition">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div id="modalEditPeriode" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="toggleModal('modalEditPeriode')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 overflow-hidden">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Edit Periode</h3>
                <form id="formEditPeriode" method="POST">
                    @csrf @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Nama Periode</label>
                            <input type="text" name="nama_periode" id="edit_nama_periode" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" rows="3"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" onclick="toggleModal('modalEditPeriode')"
                            class="px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-50 rounded-lg transition">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm transition">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

    @push('scripts')
        <script>
            function confirmDelete(button) {
                const form = button.closest('form');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data periode tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        </script>
        <script>
            function toggleModal(id) {
                const modal = document.getElementById(id);
                modal.classList.toggle('hidden');
            }

            function openEditPeriode(id, nama, keterangan) {
                const form = document.getElementById('formEditPeriode');
                // Arahkan ke rute manual update
                form.action = `/admin/periode/${id}/update`;

                document.getElementById('edit_nama_periode').value = nama;
                document.getElementById('edit_keterangan').value = keterangan || '';

                toggleModal('modalEditPeriode');
            }
        </script>
        <script>
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#2563eb',
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#dc2626'
                });
            @endif
        </script>
    @endpush
