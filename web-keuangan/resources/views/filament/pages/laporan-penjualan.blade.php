<x-filament::page>
    <x-filament::card class="mb-6">
        <div class="w-full md:w-1/3 mb-6">
            <div>
                <label class="text-sm font-medium">Pilih Bulan</label>
                <input type="month" wire:model.defer="month" wire:change="$refresh" class="w-full border-gray-300 rounded-md px-3 py-1.5" />
            </div>
        </div>
    </x-filament::card>

    <x-filament::card>
        @if (!$month)
            <div class="text-center py-12 text-gray-500 italic">
                Silakan pilih bulan terlebih dahulu untuk menampilkan laporan penjualan.
            </div>
        @else
         <div class="text-center mb-4 leading-snug">
        <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Laporan Penjualan</h2>
        <p class="text-lg font-semibold text-indigo-700">Cafe D'Klakon</p>
        <p class="text-sm text-gray-600">Periode: {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
    </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-gray-700 border">
                <thead>
                    <tr class="bg-gray-100 text-center font-semibold">
                        <th class="border px-3 py-2">Tanggal</th>
                        <th class="border px-3 py-2">Kategori</th>
                        <th class="border px-3 py-2">Menu</th>
                        <th class="border px-3 py-2">Jumlah</th>
                        <th class="border px-3 py-2">Harga Satuan</th>
                        <th class="border px-3 py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $row)
                        <tr class="text-center">
                            <td class="border px-3 py-2">{{ $row['tanggal'] }}</td>
                            <td class="border px-3 py-2">{{ $row['kategori'] }}</td>
                            <td class="border px-3 py-2 text-left">{{ $row['menu'] }}</td>
                            <td class="border px-3 py-2">{{ $row['jumlah'] }}</td>
                            <td class="border px-3 py-2 text-right">
                                {{ $row['harga_satuan'] > 0 ? 'Rp ' . number_format($row['harga_satuan'], 0, ',', '.') : '' }}
                            </td>
                            <td class="border px-3 py-2 text-right">
                                {{ $row['total'] > 0 ? 'Rp ' . number_format($row['total'], 0, ',', '.') : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 italic py-4">Tidak ada data penjualan untuk periode ini.</td>
                        </tr>
                    @endforelse
                    <tr class="bg-gray-100 font-semibold">
                        <td colspan="4" class="text-right border px-3 py-2">Grand Total</td>
                        <td colspan="2" class="text-right border px-3 py-2">
                            {{ $this->grandTotal > 0 ? 'Rp ' . number_format($this->grandTotal, 0, ',', '.') : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </x-filament::card>
</x-filament::page>
