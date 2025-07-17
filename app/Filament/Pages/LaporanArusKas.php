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

        $orders = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($order) {
                return [
                    'tanggal' => Carbon::parse($order->created_at)->format('Y-m-d'),
                    'keterangan' => 'Penjualan: ' . $order->customer_name,
                    'jumlah' => $order->total,
                    'is_expense' => false,
                    'aktivitas' => 'operasional',
                ];
            });

        $purchases = Purchase::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($purchase) {
                return [
                    'tanggal' => Carbon::parse($purchase->created_at)->format('Y-m-d'),
                    'keterangan' => 'Pembelian: ' . $purchase->supplier_name,
                    'jumlah' => $purchase->total,
                    'is_expense' => true,
                    'aktivitas' => 'operasional',
                ];
            });

        $incomes = Income::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($income) {
                return [
                    'tanggal' => $income->date,
                    'keterangan' => $income->category->name ?? 'Pemasukan Lainnya',
                    'jumlah' => $income->amount,
                    'is_expense' => false,
                    'aktivitas' => $income->category->type ?? 'operasional',
                ];
            });

        $expenses = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($expense) {
                return [
                    'tanggal' => $expense->date,
                    'keterangan' => $expense->category->name ?? 'Pengeluaran Lainnya',
                    'jumlah' => $expense->amount,
                    'is_expense' => true,
                    'aktivitas' => $expense->category->type ?? 'operasional',
                ];
            });

        // Kas awal dihitung dari transaksi sebelum bulan terpilih
        $totalPemasukanSebelumnya = Order::where('created_at', '<', $startOfMonth)->sum('total')
            + Income::where('date', '<', $startOfMonth)->sum('amount');
        $totalPengeluaranSebelumnya = Purchase::where('created_at', '<', $startOfMonth)->sum('total')
            + Expense::where('date', '<', $startOfMonth)->sum('amount');

        $this->kasAwal = $totalPemasukanSebelumnya - $totalPengeluaranSebelumnya;

        $records = $orders
            ->concat($purchases)
            ->concat($incomes)
            ->concat($expenses)
            ->sortBy('tanggal')
            ->values();

        $this->kasAkhir = $this->kasAwal + ($records->sum(fn ($item) => $item['is_expense'] ? -$item['jumlah'] : $item['jumlah']));

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
