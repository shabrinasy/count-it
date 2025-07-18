<?php

namespace App\Filament\Pages;

use App\Models\Supplies;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class KartuStok extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard';
    protected static string $view = 'filament.pages.kartu-stok';
    protected static ?string $navigationLabel = 'Kartu Stok';
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

        $this->results = $this->generateKartuStok($this->selectedSupply, $this->selectedMonth);
    }

    public function updatedSelectedSupply(): void
    {
        $this->reloadData();
    }

    public function updatedSelectedMonth(): void
    {
        $this->reloadData();
    }

    private function generateKartuStok($suppliesId, $bulan): array
    {
        $log = [];
        $fifo = collect();

        $tanggalAwal = Carbon::parse($bulan)->startOfMonth();
        $tanggalAkhir = Carbon::parse($bulan)->endOfMonth();

        // Saldo awal
        $saldoAwal = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->where('purchase_items.supplies_id', $suppliesId)
            ->where('purchases.date', '<', $tanggalAwal)
            ->select('purchase_items.quantity', 'purchase_items.actual_weight', 'purchase_items.price')
            ->get();

        $totalSaldo = collect();

        foreach ($saldoAwal as $item) {
            $totalQty = $item->quantity * $item->actual_weight;
            $hargaUnit = $totalQty > 0 ? ($item->price / $totalQty) : 0;
            $fifo->push(['qty' => $totalQty, 'harga_unit' => $hargaUnit]);
            $totalSaldo->push([
                'qty' => $totalQty,
                'harga_unit' => $hargaUnit,
                'total' => $totalQty * $hargaUnit,
            ]);
        }

        if ($totalSaldo->count()) {
            $log[] = [
                'tanggal' => $tanggalAwal->toDateString(),
                'keterangan' => 'Saldo Awal',
                'pembelian' => null,
                'hpp' => null,
                'batches' => $totalSaldo->toArray(),
            ];
        }

        // Ambil transaksi pembelian & pemakaian
        $pembelian = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->where('purchase_items.supplies_id', $suppliesId)
            ->whereBetween('purchases.date', [$tanggalAwal, $tanggalAkhir])
            ->select('purchases.date as tanggal', 'purchase_items.quantity', 'purchase_items.actual_weight', 'purchase_items.price')
            ->get()
            ->map(function ($item) {
                $totalQty = $item->quantity * $item->actual_weight;
                $hargaUnit = $totalQty > 0 ? ($item->price / $totalQty) : 0;
                return [
                    'tanggal' => $item->tanggal,
                    'keterangan' => 'Pembelian',
                    'qty' => $totalQty,
                    'harga_unit' => $hargaUnit,
                    'type' => 'masuk',
                ];
            });

        $pemakaian = DB::table('bill_of_material_items')
            ->join('bill_of_materials', 'bill_of_material_items.bill_of_materials_id', '=', 'bill_of_materials.id')
            ->join('order_items', 'order_items.menus_id', '=', 'bill_of_materials.menus_id')
            ->join('orders', 'orders.id', '=', 'order_items.orders_id')
            ->where('bill_of_material_items.supplies_id', $suppliesId)
            ->whereBetween('orders.created_at', [$tanggalAwal, $tanggalAkhir])
            ->select('orders.created_at as tanggal', 'bill_of_material_items.quantity as qty_per_menu', 'order_items.quantity as order_qty')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'keterangan' => 'Pemakaian',
                    'qty' => $item->qty_per_menu * $item->order_qty,
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
                    'total' => $trx['qty'] * $trx['harga_unit'],
                ];
            } elseif ($trx['type'] === 'keluar') {
                $qtyOut = $trx['qty'];
                $totalKeluar = 0;
                $hppLayers = [];

                while ($qtyOut > 0 && $fifo->isNotEmpty()) {
                    $batch = $fifo->first();
                    $ambil = min($batch['qty'], $qtyOut);

                    $hppLayers[] = [
                        'qty' => $ambil,
                        'harga_unit' => $batch['harga_unit'],
                        'total' => $ambil * $batch['harga_unit']
                    ];

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
                    'harga_unit' => count($hppLayers) ? $hppLayers[0]['harga_unit'] : null,
                    'total' => $totalKeluar,
                    'detail' => $hppLayers,
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

        // Sinkronkan supplies.stock dengan jumlah akhir FIFO
        Supplies::where('id', $suppliesId)->update([
            'stock' => $fifo->sum('qty'),
        ]);

        return $log;
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }
}
