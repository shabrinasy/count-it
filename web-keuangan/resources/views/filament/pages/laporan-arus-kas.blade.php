<x-filament::page>
    <x-filament::card>
        <div class="w-full md:w-1/3 mb-6">
            <label class="text-sm font-medium">Pilih Bulan</label>
            <input type="month" wire:model.defer="month" wire:change="$refresh" class="w-full border-gray-300 rounded-md px-3 py-1.5" />
        </div>
    </x-filament::card>

    
        @php
            $kasAkhir = $records->sum('total');
            $tanggalAkhir = \Carbon\Carbon::parse($month)->endOfMonth()->format('d F Y');
        @endphp
            @if ($month)
            <x-filament::card>
                 @if ($month && $records->flatMap(fn($r) => $r['accounts'])->isNotEmpty())
                <div class="text-center mb-4 leading-snug">
                    <h2 class="text-xl font-bold">Laporan Arus Kas</h2>
                    <p class="text-sm text-gray-700">Cafe D'Klakon</p>
                    <p class="text-sm text-gray-600">Periode: {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
                </div>

                @foreach ($records as $section)
                    <table class="w-full text-sm text-gray-700 border mb-8">
                        <thead>
                            <tr class="text-left font-semibold">
                                <th colspan="2" class="px-3 py-2 text-base">{{ $section['activity'] }}</th>
                            </tr>
                            <tr class="bg-gray-100 text-left font-semibold">
                                <th class="border px-3 py-2">Keterangan</th>
                                <th class="border px-3 py-2 text-right">Jumlah (IDR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($section['accounts'] as $acc)
                                <tr>
                                    <td class="border px-3 py-2">{{ $acc['code'] }} - {{ $acc['name'] }}</td>
                                    <td class="border px-3 py-2 text-right">{{ 'Rp ' . number_format($acc['amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-semibold text-right bg-gray-50">
                                <td class="px-3 py-2 text-left text-sm text-gray-700">Kas bersih yang diperoleh dari {{ $section['activity'] }}</td>
                                <td class="px-3 py-2 text-right">{{ 'Rp ' . number_format($section['total'], 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endforeach

                <div class="text-right font-semibold text-base px-3 py-2">
                    Kas bersih tanggal {{ $tanggalAkhir }}: {{ 'Rp ' . number_format($kasAkhir, 0, ',', '.') }}
                </div>
                @elseif ($month)
                <div class="text-center italic text-gray-500 py-6">
                    Tidak ada data arus kas untuk periode ini.
                </div>
        @endif
            </x-filament::card>
    @endif
</x-filament::page>
