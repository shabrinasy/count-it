<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Supplies;
use Carbon\Carbon;

class LaporanPembelian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $title = 'Laporan Pembelian';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.laporan-pembelian';

    public $startDate;
    public $endDate;
    public $selectedSupplier = '';
    public $selectedSupply = '';

    public function mount()
    {
        $this->startDate = null;
        $this->endDate = null;
    }

    public function updatedStartDate($value)
    {
        if ($value) {
            $this->startDate = Carbon::parse($value)->startOfMonth()->toDateString();
            $this->endDate = Carbon::parse($value)->endOfMonth()->toDateString();
        } else {
            $this->startDate = null;
            $this->endDate = null;
        }
    }


    public function getRecordsProperty()
    {
        if (!$this->startDate || !$this->endDate) {
            return collect(); 
        }

        $query = PurchaseItem::with(['supplies', 'purchase.supplier'])
            ->whereHas('purchase', function ($q) {
                $q->whereBetween('date', [$this->startDate, $this->endDate]);

                if ($this->selectedSupplier !== '') {
                    $q->where('suppliers_id', $this->selectedSupplier);
                }
            });

        if ($this->selectedSupply !== '') {
            $query->where('supplies_id', $this->selectedSupply);
        }

        return $query->get()->map(function ($item) {
            return [
                'tanggal' => Carbon::parse($item->purchase->date)->format('d/m/Y'),
                'nama_barang' => $item->supplies->name,
                'jumlah_angka' => $item->quantity,
                'satuan' => $item->unit_purchase,
                'harga_satuan' => $item->price,
                'total' => $item->quantity * $item->price,
                'pemasok' => $item->purchase->supplier->name,
            ];
        })->groupBy(function ($item) {
            return $item['tanggal'] . '-' . $item['nama_barang'];
        })->map(function ($group) {
            $first = $group[0];
            return [
                'tanggal' => $first['tanggal'],
                'nama_barang' => $first['nama_barang'],
                'jumlah' => collect($group)->sum('jumlah_angka') . ' ' . $first['satuan'],
                'harga_satuan' => $first['harga_satuan'], // asumsi harga satuan sama
                'total' => collect($group)->sum('total'),
                'pemasok' => $first['pemasok'], // asumsi pemasok sama
            ];
        })->values();
    }

    public function getGrandTotalProperty()
    {
        return $this->records->sum('total');
    }


    protected function getViewData(): array
    {
        return [
            'records' => $this->records,
            'suppliers' => Supplier::all(),
            'supplies' => Supplies::all(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'selectedSupplier' => $this->selectedSupplier,
            'selectedSupply' => $this->selectedSupply,
        ];
    }

    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}

}
