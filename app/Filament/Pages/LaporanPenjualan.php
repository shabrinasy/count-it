<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class LaporanPenjualan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $title = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.laporan-penjualan';

    public $month;

    public function mount()
    {
        $this->month = null;
    }


    public function getRecordsProperty()
{
    if (!$this->month) return collect();

    $start = Carbon::parse($this->month . '-01')->startOfMonth();
    $end = Carbon::parse($this->month . '-01')->endOfMonth();

    $items = OrderItem::with(['menu.categoryMenu', 'order'])
        ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
        ->get()
        ->map(function ($item) {
            return [
                'tanggal' => Carbon::parse($item->order->created_at)->format('d/m/Y'),
                'menu_id' => $item->menu_id,
                'kategori' => $item->menu->categoryMenu->name ?? '-',
                'menu' => $item->menu->name ?? '-',
                'harga_satuan' => $item->menu->price ?? 0,
                'jumlah' => $item->quantity,
                'total' => $item->quantity * ($item->menu->price ?? 0),
            ];
        });

    // Kelompokkan berdasarkan tanggal + menu_id
    return $items->groupBy(fn ($item) => $item['tanggal'] . '-' . $item['menu_id'])
        ->map(function ($group) {
            $first = $group->first();
            return [
                'tanggal' => $first['tanggal'],
                'kategori' => $first['kategori'],
                'menu' => $first['menu'],
                'harga_satuan' => $first['harga_satuan'],
                'jumlah' => $group->sum('jumlah'),
                'total' => $group->sum('total'),
            ];
        })
        ->values(); // reset indexing
}


    public function getGrandTotalProperty()
    {
        return $this->records->sum('total');
    }

    protected function getViewData(): array
    {
        return [
            'records' => $this->records,
            'month' => $this->month,
        ];
    }

    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}

}

