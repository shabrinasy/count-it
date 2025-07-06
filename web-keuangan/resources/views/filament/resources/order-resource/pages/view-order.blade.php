<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">Order Detail</x-slot>

        <div class="grid grid-cols-2 gap-6">
            {{-- Kolom Kiri --}}
            <div class="space-y-3">
                <div>
                    <strong>Code:</strong>
                    <div>{{ $record->code }}</div>
                </div>
                <div>
                    <strong>Payment Method:</strong>
                    <div>{{
                    [
                        'qris' => 'QRIS',
                        'cash' => 'Cash',
                    ][$record->payment] ?? '-'
                }}</div>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Widget untuk item akan tampil otomatis dari getHeaderWidgets() --}}
</x-filament::page>
