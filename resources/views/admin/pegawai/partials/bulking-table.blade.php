
    @forelse ($bulkings as $bulking)
        <tr class="hover:bg-slate-50 transition">
            <td class="px-6 py-4">
                <button type="button"
                    onclick="toggleDetail('{{ $bulking->id }}')"
                    class="text-slate-500 hover:text-navy-custom transition">
                    <i id="icon-{{ $bulking->id }}" class="fas fa-chevron-right"></i>
                </button>
            </td>

            <td class="px-6 py-4 text-xs text-slate-600">
                {{ $bulking->created_at->format('d M Y H:i') }}
            </td>

            <td class="px-6 py-4 text-xs font-semibold text-slate-700">
                {{ $bulking->creator->name ?? '-' }}
            </td>

            <td class="px-6 py-4">
                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                    {{ $bulking->total_data }} Data
                </span>
            </td>

            <td class="px-6 py-4 text-xs uppercase font-bold text-navy-custom">
                {{ $bulking->type }}
            </td>
        </tr>

        <tr id="detail-{{ $bulking->id }}" class="hidden bg-gray-50">
            <td colspan="5" class="px-10 py-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="uppercase text-slate-400 border-b">
                            <tr>
                                <th class="py-2 text-left">NIP</th>
                                <th class="py-2 text-left">User</th>
                                <th class="py-2 text-left">Status</th>
                                <th class="py-2 text-left">Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bulking->details as $detail)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 font-mono">{{ $detail->nip }}</td>
                                    <td class="py-2">{{ $detail->user->name ?? '-' }}</td>
                                    <td class="py-2">
                                        @if ($detail->status === 'success')
                                            <span class="px-2 py-1 bg-green-50 text-green-600 rounded-full font-bold">
                                                Success
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-red-50 text-red-600 rounded-full font-bold">
                                                Failed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-slate-500">
                                        {{ $detail->message ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                Belum ada riwayat bulk update.
            </td>
        </tr>
    @endforelse