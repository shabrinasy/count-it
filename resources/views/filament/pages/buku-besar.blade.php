<x-filament::page>
    <x-filament::card class="mb-6">
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-6">
            <div class="flex items-center gap-4">
                <label for="month" class="font-semibold text-gray-800">Pilih Bulan:</label>
                <input
                    type="month"
                    wire:model.defer="month"
                    wire:change="$refresh"
                    id="month"
                    class="border-gray-300 rounded-lg px-4 py-1.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-6">
                <div class="flex items-center gap-4">
                    <label for="account" class="font-semibold text-gray-800">Pilih Akun:</label>
                    <select
                        wire:model.defer="accountId"
                        wire:change="$refresh"
                        id="account"
                        class="border-gray-300 rounded-lg px-4 py-1.5 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                    >
                        <option value="">-------------</option>
                        @foreach (\App\Models\Account::where('type', 'item')->get() as $account)
                            <option value="{{ $account->id }}">{{ $account->code_account }} - {{ $account->name_account }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </x-filament::card>

    <x-filament::card>
        @if (!$month || !$accountId)
            <div class="text-center py-12 text-gray-500 italic">
                Silakan pilih bulan dan akun terlebih dahulu untuk menampilkan laporan buku besar.
            </div>
        @else
            <div class="text-center mb-4 leading-snug">
                <p class="text-sm text-gray-700">Cafe D'Klakon</p>
                <h2 class="text-xl font-bold tracking-wide uppercase">Buku Besar</h2>
                <p class="text-sm text-gray-600">Periode {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
                @if ($accountId)
                    <p class="text-sm mt-1 font-medium flex justify-between">
                        <span>Akun <b>{{ \App\Models\Account::find($accountId)?->name_account }}</b></span>
                        <span>No. Akun <b>{{ \App\Models\Account::find($accountId)?->code_account }}</b></span>
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border text-sm text-gray-700">
                    <thead>
                        <tr class="bg-gray-100 text-center font-semibold border-b">
                            <th rowspan="2" class="px-3 py-2 border">Tanggal</th>
                            <th rowspan="2" class="px-3 py-2 border">Uraian</th>
                            <th rowspan="2" class="px-3 py-2 border">Ref</th>
                            <th rowspan="2" class="px-3 py-2 border">Debit</th>
                            <th rowspan="2" class="px-3 py-2 border">Kredit</th>
                            <th colspan="2" class="px-3 py-2 border">Saldo</th>
                        </tr>
                        <tr class="bg-gray-100 text-center font-semibold border-b">
                            <th class="px-3 py-2 border">Debit</th>
                            <th class="px-3 py-2 border">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $hanyaSaldoAwal = count($this->entries) === 1 && $this->entries[0]['transaksi'] === 'Saldo Awal';
                        @endphp

                        @foreach ($this->entries as $entry)
                            <tr class="text-center hover:bg-gray-50 transition">
                                <td class="border px-3 py-2">{{ \Carbon\Carbon::parse($entry['tanggal'])->format('d/m/Y') }}</td>
                                <td class="border px-3 py-2 text-left">{{ $entry['keterangan'] }}</td>
                                <td class="border px-3 py-2">{{ $entry['nomor'] }}</td>
                                <td class="border px-3 py-2 text-right text-gray-800">
                                    {{ $entry['debit'] > 0 ? 'Rp ' . number_format($entry['debit'], 0, ',', '.') : '' }}
                                </td>
                                <td class="border px-3 py-2 text-right text-gray-800">
                                    {{ $entry['kredit'] > 0 ? 'Rp ' . number_format($entry['kredit'], 0, ',', '.') : '' }}
                                </td>
                                <td class="border px-3 py-2 text-right font-semibold text-green-600">
                                    @if ($entry['saldo_debit'] !== '')
                                        {{ $entry['saldo_debit'] < 0 ? 'Rp (' . number_format(abs($entry['saldo_debit']), 0, ',', '.') . ')' : 'Rp ' . number_format($entry['saldo_debit'], 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="border px-3 py-2 text-right font-semibold text-red-600">
                                    @if ($entry['saldo_kredit'] !== '')
                                        {{ $entry['saldo_kredit'] < 0 ? 'Rp (' . number_format(abs($entry['saldo_kredit']), 0, ',', '.') . ')' : 'Rp ' . number_format($entry['saldo_kredit'], 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if ($hanyaSaldoAwal)
                            <tr class="text-center text-gray-500 italic">
                                <td colspan="8" class="py-4">Belum ada transaksi untuk akun ini di bulan ini.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::card>
</x-filament::page>
