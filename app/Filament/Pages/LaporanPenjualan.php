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

        return OrderItem::with(['menu.categoryMenu', 'order'])
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => Carbon::parse($item->order->created_at)->format('d/m/Y'),
                    'kategori' => $item->menu->categoryMenu->name ?? '-',
                    'menu' => $item->menu->name ?? '-',
                    'jumlah' => $item->quantity,
                    'harga_satuan' => $item->menu->price ?? 0,
                    'total' => $item->quantity * ($item->menu->price ?? 0),
                ];
            });
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
