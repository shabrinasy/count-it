<x-filament::page>
    <x-filament::card>
        <div class="w-full md:w-1/3 mb-6">
            <label class="text-sm font-medium">Pilih Bulan</label>
            <input type="month" wire:model.defer="month" wire:change="$refresh" class="w-full border-gray-300 rounded-md px-3 py-1.5" />
        </div>
    </x-filament::card>

    @php
        $tanggalAkhir = $month ? \Carbon\Carbon::parse($month)->endOfMonth()->format('d F Y') : '';
    @endphp

    @if ($month)
        <x-filament::card>
            @if ($records->isNotEmpty())
                <div class="text-center mb-4 leading-snug">
                    <p class="text-sm text-gray-700">Cafe D'Klakon</p>
                    <h2 class="text-xl font-bold">Laporan Arus Kas</h2>
                    <p class="text-sm text-gray-600">Untuk bulan yang berakhir pada {{ $tanggalAkhir }}</p>
                </div>

                @foreach ($records as $section)
                    <table class="w-full text-sm text-gray-800 mb-8 border-separate border-spacing-y-1">
                        <tr>
                            <td colspan="3" class="font-semibold text-base py-2">
                                {{ $section['activity'] }}
                            </td>
                        </tr>

                        @php $firstIn = true; $firstOut = true; @endphp

                        @foreach ($section['accounts'] as $row)
                            @if ($row['keterangan'] && $row['jumlah'] !== '')
                                @if ($row['jumlah'] >= 0)
                                    <tr>
                                        <td class="pl-4 pr-2">
                                            @if ($firstIn)
                                                <span class="font-medium">Penerimaan:</span><br>
                                                @php $firstIn = false; @endphp
                                            @endif
                                            {{ $row['keterangan'] }}
                                        </td>
                                        <td class="text-right pr-3">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="pl-4 pr-2">
                                            @if ($firstOut)
                                                <br><span class="font-medium">Pengeluaran:</span><br>
                                                @php $firstOut = false; @endphp
                                            @endif
                                            {{ $row['keterangan'] }}
                                        </td>
                                        <td class="text-right pr-3">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                        <td></td>
                                    </tr>
                                @endif
                            @elseif ($row['keterangan'] && $row['jumlah'] === '')
                                {{-- Baris Arus Kas Neto --}}
                                <tr>
                                    <td class="font-semibold">{{ $row['keterangan'] }}</td>
                                    <td></td>
                                    <td class="text-right font-semibold">
                                        Rp {{ number_format($section['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                @endforeach

                {{-- Kenaikan / Penurunan Kas --}}
                <div class="text-right font-semibold text-base px-3 py-2">
                    Kenaikan (penurunan) kas: Rp {{ number_format($kasAkhir, 0, ',', '.') }}
                </div>
            @else
                <div class="text-center italic text-gray-500 py-6">
                    Tidak ada data arus kas untuk periode ini.
                </div>
            @endif
        </x-filament::card>
    @endif
</x-filament::page>
