<x-filament::page>
    <x-filament::card>
        <div class="w-full md:w-1/3 mb-6">
            <label class="text-sm font-medium">Pilih Bulan</label>
            <input type="month" wire:model.defer="month" wire:change="$refresh" class="w-full border-gray-300 rounded-md px-3 py-1.5" />
        </div>
    </x-filament::card>

    @php
        $tanggalAkhir = \Carbon\Carbon::parse($month)->endOfMonth()->format('d F Y');
    @endphp

    @if ($month)
        <x-filament::card>
            @if ($records->isNotEmpty())
                <div class="text-center mb-4 leading-snug">
                    <p class="text-sm text-gray-700">Cafe D'Klakon</p>
                    <h2 class="text-xl font-bold">Laporan Arus Kas</h2>
                    <p class="text-sm text-gray-600">Untuk bulan yang berakhir pada {{ $tanggalAkhir }}</p>
                </div>

                <table class="w-full text-sm text-gray-700 border mb-8">
                    <tbody>
                        @foreach ($records as $section)
    <table class="w-full text-sm text-gray-700 border mb-8">
        <thead>
            <tr class="text-left font-semibold">
                <th colspan="4" class="px-3 py-2 text-base bg-gray-100">
                    {{ $section['activity'] }}
                </th>
            </tr>
            <tr class="bg-pink-50 text-left font-semibold">
                <th class="border px-3 py-2">Keterangan</th>
                <th class="border px-3 py-2 text-right">Pemasukan</th>
                <th class="border px-3 py-2 text-right">Pengeluaran</th>
                <th class="border px-3 py-2 text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($section['accounts'] as $row)
                <tr>
                    <td class="border px-3 py-2">{{ $row['keterangan'] }}</td>
                    <td class="border px-3 py-2 text-right">
                        {{ ($row['pemasukan'] ?? null) > 0 ? 'Rp ' . number_format($row['pemasukan'], 0, ',', '.') : '' }}
                    </td>
                    <td class="border px-3 py-2 text-right">
                        {{ ($row['pengeluaran'] ?? null) > 0 ? 'Rp ' . number_format($row['pengeluaran'], 0, ',', '.') : '' }}
                    </td>
                    <td class="border px-3 py-2 text-right font-semibold">
                        {{ isset($row['saldo']) && $row['saldo'] !== 0 ? 'Rp ' . number_format($row['saldo'], 0, ',', '.') : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
                    </tbody>
                </table>

                <div class="text-right font-semibold text-base px-3 py-2">
                    Kenaikan neto dalam kas dan saldo kas {{ $tanggalAkhir }}: {{ 'Rp ' . number_format($kasAkhir, 0, ',', '.') }}
                </div>
            @else
                <div class="text-center italic text-gray-500 py-6">
                    Tidak ada data arus kas untuk periode ini.
                </div>
            @endif
        </x-filament::card>
    @endif
</x-filament::page>
