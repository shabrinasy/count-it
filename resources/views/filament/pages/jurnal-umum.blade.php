<x-filament::page>
    <x-filament::card>
        <div class="flex items-center gap-4">
            <label for="bulan" class="font-semibold text-gray-700 min-w-max">Pilih Bulan:</label>
            <input
                type="month"
                wire:model.defer="bulan"
                wire:change="$refresh"
                id="bulan"
                class="border-gray-300 rounded-lg px-4 py-1.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            />
        </div>
    </x-filament::card>


    <x-filament::card>
        @if (!$bulan)
            <div class="text-center py-12 text-gray-500 italic">
                Silakan pilih bulan terlebih dahulu untuk menampilkan jurnal umum.
            </div>
        @else
    <div class="text-center mb-4 leading-snug">
    <h3 class="text-lg font-semibold text-indigo-700">Cafe D'Klakon</h3>
    <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">JURNAL UMUM</h2>
    <p class="text-sm text-gray-600">
        Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y') }}
    </p>
</div>



    <div class="w-full overflow-x-auto">
    <div class="mx-auto max-w-screen-lg">
    <table class="w-full text-sm divide-y divide-gray-200 bord er border-gray-300 rounded-xl shadow-sm">
    <thead>
        <tr>
            <th class="px-4 py-3 text-left">Tanggal</th>
            <th class="px-4 py-3 text-left">Kode Akun</th>
            <th class="px-4 py-3 text-left">Akun</th>
            <th class="px-4 py-3 text-left">Ref</th>
            <th class="px-4 py-3 text-right">Debit</th>
            <th class="px-4 py-3 text-right">Kredit</th>
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-100">
    @php $totalDebit = 0; $totalKredit = 0; @endphp

    @forelse ($records as $row)
        @foreach ($row['entries'] as $i => $entry)
            <tr class="hover:bg-indigo-50 transition-all">
                <!-- Kolom Tanggal  -->
                @if ($i === 0)
                    <td class="px-4 py-2 align-top font-medium text-gray-700" rowspan="{{ count($row['entries']) }}">
                        {{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d M Y') }}
                    </td>
                @else
                    
                    <td class="hidden"></td>
                @endif
                
                <td class="px-4 py-2 text-gray-900">
                    {{ $entry['account']['code'] }}
                </td>

                <!-- Kolom Akun -->
                <td class="px-4 py-2 text-gray-900">
                    {{ $entry['account']['name'] }}
                </td>

                <!-- Kolom Ref  -->
                @if ($i === 0)
                    <td class="px-4 py-2 align-top text-indigo-600 font-semibold" rowspan="{{ count($row['entries']) }}">
                        {{ $row['code'] }}
                    </td>
                @else
                    
                    <td class="hidden"></td>
                @endif

                <!-- Kolom Debit  -->
                <td class="px-4 py-2 text-right text-gray-800">
                    {{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '' }}
                </td>

                <!-- Kolom Kredit -->
                <td class="px-4 py-2 text-right text-gray-800">
                    {{ $entry['credit'] > 0 ? 'Rp ' . number_format($entry['credit'], 0, ',', '.') : '' }}
                </td>

            </tr>

            @php
                $totalDebit += $entry['debit'];
                $totalKredit += $entry['credit'];
            @endphp
        @endforeach
    @empty
        <tr>
            <td colspan="5" class="text-center px-4 py-6 text-gray-500 italic">
                Tidak ada data jurnal untuk periode ini
            </td>
        </tr>
    @endforelse
</tbody>

    @if(count($records) > 0)
        <tfoot class="bg-gray-100 text-sm font-bold">
            <tr>
                <td colspan="2" class="px-4 py-2 text-right">Total</td>
                <td class="px-4 py-2 text-right text-green-600">
                    {{ $totalDebit > 0 ? 'Rp ' . number_format($totalDebit, 0, ',', '.') : '' }}
                </td>

                <td colspan="3" class="px-4 py-2 text-right text-red-600">
                    {{ $totalKredit > 0 ? 'Rp ' . number_format($totalKredit, 0, ',', '.') : '' }}
                </td>

            </tr>
        </tfoot>
    @endif
</table>
    </div>
    </div>

@endif
</x-filament::card>

</x-filament::page>
