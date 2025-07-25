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
                            <tr>
                                <td colspan="4" class="font-semibold bg-pink-100 text-left p-2">{{ $section['activity'] }}</td>
                            </tr>

                            <tr>
                                <td colspan="2" class="font-semibold p-2">Pemasukan:</td>
                            </tr>
                            @foreach ($section['accounts'] as $row)
                                @if ($row['pemasukan'] !== 0)
                                   <tr>
                                       <td class="px-3 py-2">{{ $row['keterangan'] }}</td>
                                        <td class="px-3 py-2 text-right">Rp {{ number_format((float) $row['pemasukan'], 0, ',', '.') }}</td>
                                    </tr>

                                @endif
                            @endforeach

                            <tr>
                                <td colspan="2" class="font-semibold p-2">Pengeluaran:</td>
                            </tr>
                            @foreach ($section['accounts'] as $row)
                                @if ($row['pengeluaran'] !== 0)
                                    <tr>
                                        <td class="px-3 py-2">{{ $row['keterangan'] }}</td>
                                        <td class="px-3 py-2 text-right">(Rp {{ number_format((float) $row['pengeluaran'], 0, ',', '.') }})</td>
                                    </tr>
                                @endif
                            @endforeach
                      
                            <tr>
                                <td colspan="2" class="font-semibold p-2">Arus Kas Neto dari {{ $section['activity'] }}:</td>
                                <td colspan="2" class="font-semibold text-right">Rp {{ number_format($section['total'], 0, ',', '.') }}</td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>

                <!-- Final Arus Kas Neto -->
                <div class="text-right font-semibold text-base px-3 py-2">
                    Kenaikan neto dalam kas dan saldo kas {{ $tanggalAkhir }}: Rp {{ number_format($kasAkhir, 0, ',', '.') }}
                </div>
            @else
                <div class="text-center italic text-gray-500 py-6">
                    Tidak ada data arus kas untuk periode ini.
                </div>
            @endif
        </x-filament::card>
    @endif
</x-filament::page>
