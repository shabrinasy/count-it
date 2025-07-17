<x-filament::page>
    <x-filament::card>
        <div class="mb-4 flex items-center gap-4">
            <label for="bulan" class="font-semibold text-gray-700">Pilih Bulan:</label>
            <input type="month" wire:model.defer="bulan" wire:change="$refresh"
                class="border-gray-300 rounded-md shadow-sm">
        </div>

        <table class="table-auto w-full text-sm text-left border border-gray-300">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2">Jumlah</th>
                    <th class="px-4 py-2"></th> <!-- untuk Arus Kas Neto & Saldo Kas -->
                </tr>
            </thead>
            <tbody>
                @foreach ($laporan as $item)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $item['keterangan'] }}</td>
                        <td class="px-4 py-2 text-right">
                            {{ number_format($item['jumlah'], 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                @endforeach

                <tr class="font-semibold border-t bg-gray-100">
                    <td class="px-4 py-2">Arus Kas Neto</td>
                    <td></td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($arusKasNeto, 0, ',', '.') }}
                    </td>
                </tr>

                <tr class="font-semibold border-t bg-gray-100">
                    <td class="px-4 py-2">Kenaikan Saldo Kas</td>
                    <td></td>
                    <td class="px-4 py-2 text-right">
                        {{ number_format($saldoAkhir, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </x-filament::card>
</x-filament::page>
