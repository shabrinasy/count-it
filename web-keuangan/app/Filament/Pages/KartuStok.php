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

        // Ambil unit dari bahan baku yang dipilih
        if ($this->selectedSupply) {
            $this->unit = Supplies::find($this->selectedSupply)?->unit;
        }
    }


    public function reloadData(): void
    {
        if (!$this->selectedSupply || !$this->selectedMonth) {
            $this->results = []; // kosongkan data jika filter belum lengkap
            return;
        }

        $this->results = $this->generateKartuStok($this->selectedSupply, $this->selectedMonth);
    }

    public function updatedSelectedSupply(): void
    {
        $this->reloadData();
        // Ambil unit ketika selectedSupply diperbarui
        if ($this->selectedSupply) {
            $this->unit = Supplies::find($this->selectedSupply)?->unit;
        }
    }

    public function updatedSelectedMonth(): void
    {
        $this->reloadData();
    }


    private function generateKartuStok($suppliesId, $bulan): array
{
    $fifo = collect();
    $log = [];

    // Mengambil tanggal awal dan akhir bulan
    $tanggalAwal = \Carbon\Carbon::parse($bulan)->startOfMonth();
    $tanggalAkhir = \Carbon\Carbon::parse($bulan)->endOfMonth();

    // Mengambil saldo dari bulan sebelumnya (termasuk qty, harga_unit, dan jumlah)
    $previousMonth = \Carbon\Carbon::parse($bulan)->subMonth()->endOfMonth(); // Akhir bulan sebelumnya
    $previousMonthSaldo = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->where('purchase_items.supplies_id', $suppliesId)
        ->where('purchases.date', '<=', $previousMonth)
        ->select(
            DB::raw('SUM(purchase_items.quantity * purchase_items.actual_weight) as qty'),
            DB::raw('SUM(purchase_items.price) as total_price')
        )
        ->first();

    // Mengatur saldo awal berdasarkan saldo bulan sebelumnya
    $saldoAwalQty = $previousMonthSaldo->qty ?? 0;
    $saldoAwalTotalPrice = $previousMonthSaldo->total_price ?? 0;

    // Menghitung harga per unit dari saldo awal
    $saldoAwalHargaUnit = $saldoAwalQty > 0 ? $saldoAwalTotalPrice / $saldoAwalQty : 0;

    // Menambahkan saldo awal ke dalam FIFO
    $fifo->push([
        'qty' => $saldoAwalQty,
        'harga_unit' => $saldoAwalHargaUnit,
    ]);

    // Menambahkan log untuk saldo awal
    $log[] = [
        'tanggal' => \Carbon\Carbon::parse($previousMonth)->toDateString(),
        'pembelian' => null,
        'pemakaian' => null,
        'saldo' => [
            'qty' => $saldoAwalQty,
            'harga_unit' => $saldoAwalHargaUnit,
            'jumlah' => $saldoAwalTotalPrice,
        ],
    ];

    // Pembelian
    $pembelian = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->where('purchase_items.supplies_id', $suppliesId)
        ->whereBetween('purchases.date', [$tanggalAwal, $tanggalAkhir])
        ->select(
            'purchases.date',
            'purchase_items.quantity',
            'purchase_items.actual_weight',
            'purchase_items.price'
        )
        ->orderBy('purchases.date')
        ->get();

    foreach ($pembelian as $item) {
        $qty = $item->quantity * $item->actual_weight;
        $hargaUnit = $item->price / $qty;
        $jumlah = $qty * $hargaUnit;

        $fifo->push([
            'qty' => $qty,
            'harga_unit' => $hargaUnit,
        ]);

        $log[] = [
            'tanggal' => \Carbon\Carbon::parse($item->date)->toDateString(),
            'pembelian' => [
                'qty' => $qty,
                'harga_unit' => $hargaUnit,
                'jumlah' => $jumlah,
            ],
            'pemakaian' => null,
            'saldo' => [
                'qty' => $fifo->sum('qty'),
                'harga_unit' => $fifo->last()['harga_unit'] ?? 0,
                'jumlah' => $fifo->sum(fn ($row) => $row['qty'] * $row['harga_unit']),
            ],
        ];
    }

    // Pemakaian (penjualan/order)
    $pemakaian = DB::table('bill_of_material_items')
        ->join('bill_of_materials', 'bill_of_material_items.bill_of_materials_id', '=', 'bill_of_materials.id')
        ->join('order_items', 'order_items.menus_id', '=', 'bill_of_materials.menus_id')
        ->join('orders', 'orders.id', '=', 'order_items.orders_id')
        ->where('bill_of_material_items.supplies_id', $suppliesId)
        ->whereBetween('orders.created_at', [$tanggalAwal, $tanggalAkhir])
        ->select(
            'orders.created_at',
            'bill_of_material_items.quantity',
            'order_items.quantity as order_qty'
        )
        ->orderBy('orders.created_at')
        ->get();

    foreach ($pemakaian as $pakai) {
        $totalQty = $pakai->quantity * $pakai->order_qty;
        $qtyLeft = $totalQty;
        $totalJumlah = 0;
        $hargaUnitTerpakai = null;

        while ($qtyLeft > 0 && $fifo->isNotEmpty()) {
            $batch = $fifo->first();
            $ambil = min($batch['qty'], $qtyLeft);
            $totalJumlah += $ambil * $batch['harga_unit'];
            $hargaUnitTerpakai = $batch['harga_unit'];
            $qtyLeft -= $ambil;

            if ($ambil >= $batch['qty']) {
                $fifo->shift();
            } else {
                $updated = $batch;
                $updated['qty'] -= $ambil;
                $fifo = $fifo->slice(1)->prepend($updated);
            }
        }

        $log[] = [
            'tanggal' => \Carbon\Carbon::parse($pakai->created_at)->toDateString(),
            'pembelian' => null,
            'pemakaian' => [
                'qty' => $totalQty,
                'harga_unit' => $hargaUnitTerpakai,
                'jumlah' => $totalJumlah,
            ],
            'saldo' => [
                'qty' => $fifo->sum('qty'),
                'harga_unit' => $fifo->last()['harga_unit'] ?? 0,
                'jumlah' => $fifo->sum(fn ($row) => $row['qty'] * $row['harga_unit']),
            ],
        ];
    }

    return $log;
}




public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}


}
