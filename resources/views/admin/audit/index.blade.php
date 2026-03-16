@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Audit Log</h2>
            <p class="text-sm text-slate-500">Riwayat perubahan data pada sistem</p>
        </div>
        {{-- Tombol Refresh --}}
        <a href="{{ route('admin.audit.index') }}"
            class="bg-white border border-gray-200 text-slate-600 px-4 py-2 rounded-lg text-sm flex items-center hover:bg-gray-50 transition shadow-sm">
            <i class="fas fa-sync-alt mr-2 text-[10px]"></i> Refresh
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-slate-500 text-[11px] uppercase tracking-widest">
                        <th class="px-6 py-4 font-bold">Waktu</th>
                        <th class="px-6 py-4 font-bold">Aksi</th>
                        <th class="px-6 py-4 font-bold">Tabel</th>
                        <th class="px-6 py-4 font-bold">Data ID</th>
                        <th class="px-6 py-4 font-bold">Detail Perubahan</th>
                        <th class="px-6 py-4 font-bold">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-blue-50/30 transition group">
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $log->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $color = match ($log->aksi) {
                                        'CREATE' => 'bg-green-100 text-green-700 border-green-200',
                                        'UPDATE' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'DELETE' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="{{ $color }} text-[10px] px-2 py-1 rounded font-bold border">
                                    {{ $log->aksi }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-blue-600 uppercase">
                                {{ $log->nama_tabel }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500 font-mono">
                                {{ Str::limit($log->data_id, 8) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 italic">
                                "{{ $log->perubahan }}"
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-semibold text-slate-700">{{ $log->user->name ?? 'System' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $log->user->email ?? '-' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                Tidak ada data audit log ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
