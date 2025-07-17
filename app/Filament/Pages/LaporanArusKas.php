<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Order;
use App\Models\OrderItem;       
use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;

class LaporanArusKas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $title = 'Laporan Arus Kas';
    protected static ?string $navigationGroup = 'Laporan';
    protected static string $view = 'filament.pages.laporan-arus-kas';

    public $month;

    public $kasAkhir = 0;

    public function mount()
    {
        $this->month = null; // pengguna wajib pilih dulu
    }


    public function getRecordsProperty()
{
    if (!$this->month) return collect();

    $start = Carbon::parse($this->month . '-01')->startOfMonth();
    $end = Carbon::parse($this->month . '-01')->endOfMonth();

    $activities = ['operating', 'investing', 'financing'];
    $result = collect();

    $grandTotalIn = 0;
    $grandTotalOut = 0;

    // === SALDO AWAL KAS ===
    $kasAwal = 0;

    // Order (Penjualan)
    $kasAwal += Order::with('orderItem')
        ->where('created_at', '<', $start)
        ->get()
        ->sum(fn($o) => $o->orderItem->sum(fn($item) => $item->quantity * $item->price));

    // Income
    $kasAwal += Income::where('date_income', '<', $start)->sum('amount_income');

    // Expense
    $kasAwal -= Expense::where('date_expense', '<', $start)->sum('amount_expense');

    // Purchase
    $kasAwal -= Purchase::with('purchaseItems')
        ->where('date', '<', $start)
        ->get()
        ->sum(fn($p) => $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price));

    // === DETAIL ARUS KAS BULAN BERJALAN ===
    foreach ($activities as $activity) {
        $rows = collect();
        $totalIn = 0;
        $totalOut = 0;

        // ORDER (Penjualan)
        if ($activity === 'operating') {
            $orders = Order::with('orderItem')
                ->whereBetween('created_at', [$start, $end])
                ->get();

            $totalOrder = 0;
            foreach ($orders as $order) {
                $total = $order->orderItem->sum(fn($item) => $item->quantity * $item->price);
                $totalOrder += $total;
            }

            if ($totalOrder > 0) {
                $rows->push([
                    'keterangan' => 'Penerimaan kas dari penjualan',
                    'pemasukan' => $totalOrder,
                    'pengeluaran' => 0,
                ]);
                $totalIn += $totalOrder;
            }
        }

        // PURCHASE (Pembelian bahan baku)
        if ($activity === 'operating') {
            $purchases = Purchase::with('purchaseItems')
                ->whereBetween('date', [$start, $end])
                ->get();

            $totalPurchase = 0;
            foreach ($purchases as $purchase) {
                $amount = $purchase->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
                $totalPurchase += $amount;
            }

            if ($totalPurchase > 0) {
                $rows->push([
                    'keterangan' => 'Pembayaran kas untuk pembelian bahan baku',
                    'pemasukan' => 0,
                    'pengeluaran' => $totalPurchase,
                ]);
                $totalOut += $totalPurchase;
            }
        }

        // INCOME
        $incomes = Income::with('category.account')
            ->whereBetween('date_income', [$start, $end])
            ->get()
            ->filter(fn($i) => $i->category?->account?->account_activity === $activity)
            ->groupBy('category_id');

        foreach ($incomes as $catId => $items) {
            $category = $items->first()->category;
            $name = $category->name_category ?? 'Pemasukan Lainnya';
            $isExpense = $category->is_expense ?? false;

            $amount = $items->sum('amount_income');

            if ($amount === 0) continue;

            if ($isExpense) {
                $rows->push([
                    'keterangan' => $name,
                    'pemasukan' => 0,
                    'pengeluaran' => $amount,
                ]);
                $totalOut += $amount;
            } else {
                $rows->push([
                    'keterangan' => $name,
                    'pemasukan' => $amount,
                    'pengeluaran' => 0,
                ]);
                $totalIn += $amount;
            }
        }

        // EXPENSE
        $expenses = Expense::with('category.account')
            ->whereBetween('date_expense', [$start, $end])
            ->get()
            ->filter(fn($e) => $e->category?->account?->account_activity === $activity)
            ->groupBy('category_id');

        foreach ($expenses as $catId => $items) {
            $category = $items->first()->category;
            $name = $category->name_category ?? 'Pengeluaran Lainnya';
            $isExpense = $category->is_expense ?? true;

            $amount = $items->sum('amount_expense');

            if ($amount === 0) continue;

            if ($isExpense) {
                $rows->push([
                    'keterangan' => $name,
                    'pemasukan' => 0,
                    'pengeluaran' => $amount,
                ]);
                $totalOut += $amount;
            } else {
                $rows->push([
                    'keterangan' => $name,
                    'pemasukan' => $amount,
                    'pengeluaran' => 0,
                ]);
                $totalIn += $amount;
            }
        }

        // === TOTAL NETO PER AKTIVITAS ===
        if ($rows->isNotEmpty()) {
            $netCashFlow = $totalIn - $totalOut;

            $rows->push([
                'pemasukan' => '',
                'pengeluaran' => '',
                'saldo' => $netCashFlow,
            ]);

             $result->push([
                'activity' => ucfirst($activity) . ' Activity',
                'accounts' => $rows,
                'total' => $netCashFlow,
            ]);

            $grandTotalIn += $totalIn;
            $grandTotalOut += $totalOut;
        }
    }

    $this->kasAkhir = $kasAwal + ($grandTotalIn - $grandTotalOut);

    return $result;
}


    protected function getViewData(): array
    {
        return [
            'records' => $this->records,
            'month' => $this->month,
            'kasAkhir' => $this->kasAkhir,
        ];
    }

    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}

}
