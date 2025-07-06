<x-filament::page>
    <x-filament::section>
        <x-slot name="heading">Purchase Detail</x-slot>

        <div class="grid grid-cols-2 gap-6">
            {{-- Kolom Kiri --}}
            <div class="space-y-3">
                <div>
                    <strong>Code:</strong>
                    <div>{{ $record->code }}</div>
                </div>
                <div>
                    <strong>Date:</strong>
                    <div>{{ $record->date }}</div>
                </div>
                <div>
                    <strong>Supplier:</strong>
                    <div>{{ $record->supplier->name ?? '-' }}</div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="space-y-3">
                <div>
                    <strong>File:</strong>
                    <div>
                        @if ($record->file)
                            <a href="{{ Storage::url($record->file) }}" target="_blank" class="text-primary-400 underline">
                                {{ basename($record->file) }}
                            </a>
                        @else
                            <span class="text-gray-500">No file attached</span>
                        @endif
                    </div>
                </div>
                <div>
                    <strong>Notes:</strong>
                    <div class="text-gray-200 whitespace-pre-line">
                        {{ $record->notes ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Widget untuk item akan tampil otomatis dari getHeaderWidgets() --}}
</x-filament::page>
