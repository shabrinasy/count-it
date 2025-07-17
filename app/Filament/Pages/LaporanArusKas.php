<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Order;
use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LaporanArusKas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $title = 'Laporan Arus Kas';
    protected static string $view = 'filament.pages.laporan-arus-kas';

    public $month;
    public Collection $records;
    public $kasAkhir = 0;
    public $kasAwal = 0;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    protected function getRecordsProperty()
{
    $selectedMonth = Carbon::parse($this->month);
    $startOfMonth = $selectedMonth->copy()->startOfMonth();
    $endOfMonth = $selectedMonth->copy()->endOfMonth();

    $orders = Order::with('orderItem')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($order) {
            $items = $order->orderItem ?? collect([]);
            return [
                'tanggal' => Carbon::parse($order->created_at)->format('Y-m-d'),
                'keterangan' => 'Penjualan: ' . $order->customer_name,
                'jumlah' => $items->sum(fn ($item) => $item->quantity * $item->price),
                'is_expense' => false,
                'aktivitas' => 'operasional',
            ];
        });

    $purchases = Purchase::with('purchaseItems')
        ->whereBetween('date', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($purchase) {
            $items = $purchase->purchaseItems ?? collect([]);
            return [
                'tanggal' => Carbon::parse($purchase->date)->format('Y-m-d'),
                'keterangan' => 'Pembelian: ' . $purchase->supplier_name,
                'jumlah' => $items->sum(fn ($item) => $item->quantity * $item->price),
                'is_expense' => true,
                'aktivitas' => 'operasional',
            ];
        });

    $incomes = Income::whereBetween('date_income', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($income) {
            return [
                'tanggal' => $income->date_income,
                'keterangan' => $income->category->name ?? 'Pemasukan Lainnya',
                'jumlah' => $income->amount_income,
                'is_expense' => false,
                'aktivitas' => $income->category->type ?? 'operasional',
            ];
        });

    $expenses = Expense::whereBetween('date_expense', [$startOfMonth, $endOfMonth])
        ->get()
        ->map(function ($expense) {
            return [
                'tanggal' => $expense->date_expense,
                'keterangan' => $expense->category->name ?? 'Pengeluaran Lainnya',
                'jumlah' => $expense->amount_expense,
                'is_expense' => true,
                'aktivitas' => $expense->category->type ?? 'operasional',
            ];
        });

    // Kas awal dihitung dari transaksi sebelum bulan terpilih

$totalOrderSebelumnya = Order::with('orderItem')
    ->where('created_at', '<', $startOfMonth)
    ->get()
    ->sum(function ($order) {
        return $order->orderItem->sum(fn($item) => $item->quantity * $item->price);
    });

$totalPurchaseSebelumnya = Purchase::with('purchaseItems')
    ->where('date', '<', $startOfMonth)
    ->get()
    ->sum(function ($purchase) {
        return $purchase->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
    });

$totalPemasukanSebelumnya = $totalOrderSebelumnya + Income::where('date_income', '<', $startOfMonth)->sum('amount_income');
$totalPengeluaranSebelumnya = $totalPurchaseSebelumnya + Expense::where('date_expense', '<', $startOfMonth)->sum('amount_expense');

$this->kasAwal = $totalPemasukanSebelumnya - $totalPengeluaranSebelumnya;


    $records = $orders
        ->concat($purchases)
        ->concat($incomes)
        ->concat($expenses)
        ->sortBy('tanggal')
        ->values();

    $this->kasAkhir = $this->kasAwal + $records->sum(fn ($item) => $item['is_expense'] ? -$item['jumlah'] : $item['jumlah']);

    return $records;
}

    protected function getViewData(): array
    {
        return [
            'records' => $this->getRecordsProperty(),
            'month' => $this->month,
            'kasAkhir' => $this->kasAkhir,
        ];
    }

}
