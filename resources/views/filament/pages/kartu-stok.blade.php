<x-filament::page>
    <div class="space-y-6">

        {{-- Filter Card --}}
        <x-filament::card>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                {{-- Kiri: Filter --}}
                <div class="flex flex-wrap items-center gap-4">
                    {{-- Bahan Baku --}}
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <label class="block text-sm font-medium text-gray-700">Pilih Bahan Baku</label>
                        <select wire:model.defer="selectedSupply" class="w-full rounded-md shadow-sm border-gray-300">
                            <option value="">-----</option>
                            @foreach ($supplyList as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bulan --}}
                    <div class="flex items-center gap-2 whitespace-nowrap">
                        <label class="block text-sm font-medium text-gray-700">Pilih Bulan</label>
                        <input type="month" wire:model.defer="selectedMonth" class="w-full rounded-md shadow-sm border-gray-300" />
                    </div>
                </div>

                {{-- Kanan: Tombol --}}
                <div class="flex justify-end">
                    <button wire:click="reloadData"
                        class="bg-primary-600 text-white px-4 py-2 rounded-md shadow hover:bg-primary-700">
                        Tampilkan
                    </button>
                </div>

            </div>
        </x-filament::card>



        {{-- Kondisional: Jika filter belum dipilih --}}
        @if (!$selectedMonth || !$selectedSupply)
            <x-filament::card>
                <div class="text-center py-12 text-gray-500 italic">
                    Silakan pilih bulan dan bahan baku terlebih dahulu untuk menampilkan kartu stok.
                </div>
            </x-filament::card>
        @else
            {{-- Kop Laporan --}}
            <x-filament::card>
                <div class="text-center mb-4 leading-snug">
                    <h2 class="text-xl font-bold tracking-wide uppercase">Kartu Stok</h2>
                    <p class="text-sm text-gray-700">Cafe D'Klakon</p>
                    <p class="text-sm text-gray-600">
                        Periode: {{ \Carbon\Carbon::parse($selectedMonth)->translatedFormat('F Y') }}
                    </p>
                    @if ($selectedSupply)
                        <p class="text-sm mt-1 font-medium">
                            Bahan Baku: {{ $supplyList[$selectedSupply] ?? '-' }}
                        </p>
                        @if ($this->unit) <!-- Menambahkan unit di bawah bahan baku -->
                            <p class="text-sm mt-1 font-medium">
                                Unit: {{ $this->unit }}
                            </p>
                        @endif
                    @endif
                </div>
                <div class="overflow-auto border rounded-lg">
                    <table class="w-full text-sm text-center border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-xs">
                                <th class="border px-2 py-2" rowspan="2">Tanggal</th>
                                <th class="border px-2" colspan="3">Pembelian</th>
                                <th class="border px-2" colspan="3">Pemakaian</th>
                                <th class="border px-2" colspan="3">Saldo</th>
                            </tr>
                            <tr class="bg-gray-50 text-xs">
                                @for ($i = 0; $i < 3; $i++)
                                    <th class="border">Qty</th>
                                    <th class="border">Harga/Unit</th>
                                    <th class="border">Jumlah</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $row)
                                <tr>
                                    <td class="border p-1">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/y') }}</td>

                                    {{-- Pembelian --}}
                                    @if ($row['pembelian'])
                                        <td class="border">{{ $row['pembelian']['qty'] }}</td>
                                        <td class="border">
                                            {{ $row['pembelian']['harga_unit'] > 0 ? 'Rp ' . number_format($row['pembelian']['harga_unit'], 2, ',', '.') : '' }}
                                        </td>
                                        <td class="border">
                                            {{ $row['pembelian']['jumlah'] > 0 ? 'Rp ' . number_format($row['pembelian']['jumlah'], 2, ',', '.') : '' }}
                                        </td>
                                    @else
                                        <td class="border" colspan="3">-</td>
                                    @endif

                                    {{-- Pemakaian --}}
                                    @if ($row['pemakaian'])
                                        <td class="border">{{ $row['pemakaian']['qty'] }}</td>
                                        <td class="border">
                                            {{ $row['pemakaian']['harga_unit'] > 0 ? 'Rp ' . number_format($row['pemakaian']['harga_unit'], 2, ',', '.') : '' }}
                                        </td>
                                        <td class="border">
                                            {{ $row['pemakaian']['jumlah'] > 0 ? 'Rp ' . number_format($row['pemakaian']['jumlah'], 2, ',', '.') : '' }}
                                        </td>
                                    @else
                                        <td class="border" colspan="3">-</td>
                                    @endif

                                    {{-- Saldo --}}
                                    @if ($row['saldo'])
                                        <td class="border">{{ $row['saldo']['qty'] }}</td>
                                        <td class="border">
                                            {{ $row['saldo']['harga_unit'] > 0 ? 'Rp ' . number_format($row['saldo']['harga_unit'], 2, ',', '.') : '' }}
                                        </td>
                                        <td class="border">
                                            {{ $row['saldo']['jumlah'] > 0 ? 'Rp ' . number_format($row['saldo']['jumlah'], 2, ',', '.') : '' }}
                                        </td>
                                    @else
                                        <td class="border" colspan="3">-</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-4 text-gray-500">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::card>
        @endif

    </div>
</x-filament::page>
