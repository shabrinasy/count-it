<?php

namespace App\Filament\Pages;

use App\Models\Supplies;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class LaporanPersediaan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static string $view = 'filament.pages.laporan-persediaan';
    protected static ?string $navigationLabel = 'Laporan Persediaan';
    protected static ?string $navigationGroup = 'Laporan';

    public ?string $selectedSupply = null;
    public ?string $selectedMonth = null;
    public array $results = [];
    public array $supplyList = [];
    public ?string $unit = null;

    public function mount(): void
    {
        $this->supplyList = Supplies::pluck('name', 'id')->toArray();
        $this->selectedSupply = null;
        $this->selectedMonth = null;
    }

    public function reloadData(): void
    {
        if (!$this->selectedSupply || !$this->selectedMonth) {
            $this->results = [];
            return;
        }

        $this->results = $this->generateLaporanPersediaan($this->selectedSupply, $this->selectedMonth);
    }

    public function updatedSelectedSupply(): void
    {
        $this->reloadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->reloadData();
    }

private function generateLaporanPersediaan($suppliesId, $bulan): array
{
    $log = [];
    $fifo = collect();

    $tanggalAwal = Carbon::parse($bulan)->startOfMonth();
    $tanggalAkhir = Carbon::parse($bulan)->endOfMonth();

    $saldoAwal = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->where('purchase_items.supplies_id', $suppliesId)
        ->where('purchases.date', '<', $tanggalAwal)
        ->orderBy('purchases.date')
        ->get();

    foreach ($saldoAwal as $item) {
        $qty = $item->quantity; // pakai quantity saja
        $hargaUnit = $item->price / $qty;
        $fifo->push(['qty' => $qty, 'harga_unit' => $hargaUnit]);
    }

    $log[] = [
        'tanggal' => $tanggalAwal->toDateString(),
        'keterangan' => 'Saldo Awal',
        'pembelian' => null,
        'hpp' => null,
        'batches' => $fifo->map(function ($batch) {
            return [
                'qty' => $batch['qty'],
                'harga_unit' => $batch['harga_unit'],
                'total' => $batch['qty'] * $batch['harga_unit']
            ];
        })->toArray(),
    ];

    $pembelian = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->where('purchase_items.supplies_id', $suppliesId)
        ->whereBetween('purchases.date', [$tanggalAwal, $tanggalAkhir])
        ->select(
            'purchases.date as tanggal',
            'purchase_items.quantity',
            'purchase_items.price as harga_unit'
        )
        ->get()
        ->map(function ($item) {
            $qty = $item->quantity; // hanya quantity
            $total = $qty * $item->harga_unit;

            return [
                'tanggal' => $item->tanggal,
                'keterangan' => 'Pembelian',
                'qty' => $qty,
                'harga_unit' => $item->harga_unit,
                'nilai' => $total,
                'type' => 'masuk',
            ];
        });

    $pemakaian = DB::table('bill_of_material_items')
    ->join('bill_of_materials', 'bill_of_material_items.bill_of_materials_id', '=', 'bill_of_materials.id')
    ->join('order_items', 'order_items.menus_id', '=', 'bill_of_materials.menus_id')
    ->join('orders', 'orders.id', '=', 'order_items.orders_id')
    ->where('bill_of_material_items.supplies_id', $suppliesId)
    ->whereBetween('orders.created_at', [$tanggalAwal, $tanggalAkhir])
    ->select('orders.created_at as tanggal', 'order_items.quantity')
    ->get()
    ->map(function ($item) {
        return [
            'tanggal' => $item->tanggal,
            'keterangan' => 'Pemakaian',
            'qty' => $item->quantity,  // ✅ cukup quantity dari order_items saja
            'type' => 'keluar',
        ];
    });


    $transactions = $pembelian->concat($pemakaian)->sortBy('tanggal');

    foreach ($transactions as $trx) {
        $row = [
            'tanggal' => Carbon::parse($trx['tanggal'])->toDateString(),
            'keterangan' => $trx['keterangan'],
            'pembelian' => null,
            'hpp' => null,
        ];

        if ($trx['type'] === 'masuk') {
            $fifo->push(['qty' => $trx['qty'], 'harga_unit' => $trx['harga_unit']]);

            $row['pembelian'] = [
                'qty' => $trx['qty'],
                'harga_unit' => $trx['harga_unit'],
                'total' => $trx['nilai']
            ];
        } elseif ($trx['type'] === 'keluar') {
            $qtyOut = $trx['qty'];
            $totalKeluar = 0;
            $hargaUnitKeluar = null;

            while ($qtyOut > 0 && $fifo->isNotEmpty()) {
                $batch = $fifo->first();
                $ambil = min($batch['qty'], $qtyOut);

                if ($hargaUnitKeluar === null) {
                    $hargaUnitKeluar = $batch['harga_unit'];
                }

                $totalKeluar += $ambil * $batch['harga_unit'];
                $qtyOut -= $ambil;

                if ($ambil == $batch['qty']) {
                    $fifo->shift();
                } else {
                    $batch['qty'] -= $ambil;
                    $fifo[0] = $batch;
                }
            }

            $row['hpp'] = [
                'qty' => $trx['qty'],
                'harga_unit' => $hargaUnitKeluar,
                'total' => $totalKeluar
            ];
        }

        $row['batches'] = $fifo->map(function ($batch) {
            return [
                'qty' => $batch['qty'],
                'harga_unit' => $batch['harga_unit'],
                'total' => $batch['qty'] * $batch['harga_unit']
            ];
        })->toArray();

        $log[] = $row;
    }

    return $log;
}

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }
}
