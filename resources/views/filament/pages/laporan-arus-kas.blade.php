<table class="w-full text-sm text-gray-800 mb-8 border border-collapse">
    <thead class="bg-gray-200 text-left">
        <tr>
            <th class="px-3 py-2 w-1/2">Aktivitas</th>
            <th class="px-3 py-2 w-1/4">Jumlah</th>
            <th class="px-3 py-2 w-1/4">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $section)
            {{-- Header aktivitas --}}
            <tr class="bg-gray-100 font-semibold">
                <td colspan="3" class="px-3 py-2">
                    Aktivitas {{ $section['activity'] }}
                </td>
            </tr>

            {{-- Penerimaan --}}
            @php $firstIn = true; @endphp
            @foreach ($section['accounts']->where('jumlah', '>=', 0) as $row)
                @if ($firstIn)
                    <tr><td class="px-3 pt-3 font-medium">Penerimaan:</td><td></td><td></td></tr>
                    @php $firstIn = false; @endphp
                @endif
                <tr>
                    <td class="pl-6 py-1">{{ $row['keterangan'] }}</td>
                    <td class="text-right px-3 py-1">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endforeach

            {{-- Pengeluaran --}}
            @php $firstOut = true; @endphp
            @foreach ($section['accounts']->where('jumlah', '<', 0) as $row)
                @if ($firstOut)
                    <tr><td class="px-3 pt-3 font-medium">Pengeluaran:</td><td></td><td></td></tr>
                    @php $firstOut = false; @endphp
                @endif
                <tr>
                    <td class="pl-6 py-1">{{ $row['keterangan'] }}</td>
                    <td class="text-right px-3 py-1">(Rp {{ number_format(abs($row['jumlah']), 0, ',', '.') }})</td>
                    <td></td>
                </tr>
            @endforeach

            {{-- Arus kas neto --}}
            <tr class="bg-gray-50 font-semibold">
                <td class="px-3 py-2">Arus kas neto dari aktivitas {{ strtolower($section['activity']) }}</td>
                <td></td>
                <td class="text-right px-3 py-2">Rp {{ number_format($section['total'], 0, ',', '.') }}</td>
            </tr>
        @endforeach

        {{-- Kenaikan/Penurunan Kas --}}
        <tr class="bg-gray-100 font-semibold">
            <td class="px-3 py-2">Kenaikan (Penurunan) kas bersih</td>
            <td></td>
            <td class="text-right px-3 py-2">Rp {{ number_format($kasAkhir - $kasAwal, 0, ',', '.') }}</td>
        </tr>

        {{-- Saldo Awal --}}
        <tr class="font-semibold">
            <td class="px-3 py-2">Saldo awal kas</td>
            <td></td>
            <td class="text-right px-3 py-2">Rp {{ number_format($kasAwal, 0, ',', '.') }}</td>
        </tr>

        {{-- Saldo Akhir --}}
        <tr class="font-semibold">
            <td class="px-3 py-2">Saldo akhir kas</td>
            <td></td>
            <td class="text-right px-3 py-2">Rp {{ number_format($kasAkhir, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
