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
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Kartu Stok</h2>
                    <p class="text-lg font-semibold text-gray-700">Cafe D'Klakon</p>
                    <p class="text-sm text-pink-600">Periode: {{ \Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}</p>
                </div>

                <div class="overflow-auto border rounded-lg">
                    <table class="w-full text-sm text-left border-collapse" style="border: 2px solid black;">
                        <thead class="text-center bg-gray-100">
                            <tr>
                                <th class="border border-black px-2 py-1">Tanggal</th>
                                <th class="border border-black px-2 py-1">Transaksi</th>
                                <th colspan="3" class="border border-black">Pembelian</th>
                                <th colspan="3" class="border border-black">Penjualan</th>
                                <th colspan="3" class="border border-black">Persediaan</th>
                            </tr>
                            <tr class="text-xs bg-gray-50 text-center">
                                <th colspan="2"></th>
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
                                <tr>
                                    <td class="border border-black px-2 py-1 text-center">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                                    <td class="border border-black px-2 py-1">{{ $row['keterangan'] }}</td>

                                    {{-- Pembelian --}}
                                    <td class="border border-black text-center">
                                        {{ $row['pembelian']['qty'] ?? '-' }}
                                    </td>
                                    <td class="border border-black text-center">
                                        {{ isset($row['pembelian']['harga_unit']) ? 'Rp'.number_format($row['pembelian']['harga_unit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-black text-center">
                                        {{ isset($row['pembelian']['total']) ? 'Rp'.number_format($row['pembelian']['total'], 0, ',', '.') : '-' }}
                                    </td>

                                    {{-- Penjualan (HPP) --}}
                                    <td class="border border-black text-center">
                                        {{ $row['hpp']['qty'] ?? '-' }}
                                    </td>
                                    <td class="border border-black text-center">
                                        {{ isset($row['hpp']['harga_unit']) ? 'Rp'.number_format($row['hpp']['harga_unit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="border border-black text-center">
                                        {{ isset($row['hpp']['total']) ? 'Rp'.number_format($row['hpp']['total'], 0, ',', '.') : '-' }}
                                    </td>

                                    {{-- Persediaan (semua lapisan FIFO) --}}
                                    <td colspan="3" class="border border-black p-0">
                                        @if(isset($row['batches']) && count($row['batches']) > 0)
                                            <table class="w-full text-xs border-collapse">
                                                @foreach ($row['batches'] as $batch)
                                                    <tr>
                                                        <td class="border border-black text-center w-1/3">{{ $batch['qty'] }} Unit</td>
                                                        <td class="border border-black text-center w-1/3">Rp{{ number_format($batch['harga_unit'], 0, ',', '.') }}</td>
                                                        <td class="border border-black text-center w-1/3">Rp{{ number_format($batch['total'], 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        @else
                                            <div class="text-center py-1">-</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        @endif
    </div>
</x-filament::page>
