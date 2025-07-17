<x-filament::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <label class="block text-sm font-medium text-gray-700">Pilih Bahan Baku</label>
                        <select wire:model.defer="selectedSupply" class="w-full rounded-md shadow-sm border-gray-300">
                            <option value="">-----</option>
                            @foreach ($this->supplyList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <label class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                        <input type="month" wire:model.defer="selectedMonth" class="w-full rounded-md shadow-sm border-gray-300" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <button wire:click="reloadData" class="bg-primary-600 text-white px-4 py-2 rounded-md shadow hover:bg-primary-700">Tampilkan</button>
                </div>
            </div>
        </x-filament::card>

        @if ($results)
            <x-filament::card>
                <div class="overflow-auto border rounded-lg">
                    <table class="w-full text-sm text-center border-collapse table-fixed" style="border: 2px solid black;">
                        <thead>
                            <tr style="background-color: #f3f4f6;">
                                <th rowspan="2" class="border border-black">Tgl</th>
                                <th colspan="3" class="border border-black">Pembelian</th>
                                <th colspan="3" class="border border-black">HPP</th>
                                <th colspan="3" class="border border-black">Persediaan</th>
                            </tr>
                            <tr style="background-color: #f9fafb;">
                                <th class="border border-black">Unit</th>
                                <th class="border border-black">Harga/Unit</th>
                                <th class="border border-black">Total</th>
                                <th class="border border-black">Unit</th>
                                <th class="border border-black">Harga/Unit</th>
                                <th class="border border-black">Total</th>
                                <th class="border border-black">Unit</th>
                                <th class="border border-black">Harga/Unit</th>
                                <th class="border border-black">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($results as $row)
                                @php $batchCount = max(count($row['batches'] ?? []), 1); @endphp
                                <tr>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ $row['pembelian']['qty'] ?? '-' }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ isset($row['pembelian']['harga_unit']) ? 'Rp'.number_format($row['pembelian']['harga_unit'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ isset($row['pembelian']['total']) ? 'Rp'.number_format($row['pembelian']['total'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ $row['hpp']['qty'] ?? '-' }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ isset($row['hpp']['harga_unit']) ? 'Rp'.number_format($row['hpp']['harga_unit'], 0, ',', '.') : '-' }}</td>
                                    <td class="border border-black align-top" rowspan="{{ $batchCount }}">{{ isset($row['hpp']['total']) ? 'Rp'.number_format($row['hpp']['total'], 0, ',', '.') : '-' }}</td>

                                    @if (!empty($row['batches']))
                                        @foreach ($row['batches'] as $i => $batch)
                                            @if ($i > 0) <tr> @endif
                                            <td class="border border-black">{{ $batch['qty'] }}</td>
                                            <td class="border border-black">Rp{{ number_format($batch['harga_unit'], 0, ',', '.') }}</td>
                                            <td class="border border-black">Rp{{ number_format($batch['total'], 0, ',', '.') }}</td>
                                            @if ($i > 0) </tr> @endif
                                        @endforeach
                                    @else
                                        <td class="border border-black">-</td>
                                        <td class="border border-black">-</td>
                                        <td class="border border-black">-</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament::page>
