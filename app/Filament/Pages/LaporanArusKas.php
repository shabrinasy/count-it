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
        $this->month = null;
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

        $kasAwal = 0;
        $kasAwal += Order::with('orderItem')
            ->where('created_at', '<', $start)
            ->get()
            ->sum(fn($o) => $o->orderItem->sum(fn($item) => $item->quantity * $item->price));

        $kasAwal += Income::where('date_income', '<', $start)->sum('amount_income');
        $kasAwal -= Expense::where('date_expense', '<', $start)->sum('amount_expense');
        $kasAwal -= Purchase::with('purchaseItems')
            ->where('date', '<', $start)
            ->get()
            ->sum(fn($p) => $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price));

        foreach ($activities as $activity) {
            $rows = collect();
            $totalIn = 0;
            $totalOut = 0;

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
                    $rows->push(['keterangan' => 'Penerimaan kas dari penjualan', 'jumlah' => $totalOrder, 'type' => 'in']);
                    $totalIn += $totalOrder;
                }

                $purchases = Purchase::with('purchaseItems')
                    ->whereBetween('date', [$start, $end])
                    ->get();

                $totalPurchase = 0;
                foreach ($purchases as $purchase) {
                    $amount = $purchase->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
                    $totalPurchase += $amount;
                }

                if ($totalPurchase > 0) {
                    $rows->push(['keterangan' => 'Pembayaran kas untuk pembelian bahan baku', 'jumlah' => $totalPurchase, 'type' => 'out']);
                    $totalOut += $totalPurchase;
                }
            }

            $incomes = Income::with('category.account')
                ->whereBetween('date_income', [$start, $end])
                ->get()
                ->filter(fn($i) => $i->category?->account?->account_activity === $activity);

            foreach ($incomes as $i) {
                $amount = $i->amount_income;
                $name = $i->category->name_category ?? 'Pemasukan Lainnya';
                $rows->push(['keterangan' => $name, 'jumlah' => $amount, 'type' => 'in']);
                $totalIn += $amount;
            }

            $expenses = Expense::with('category.account')
                ->whereBetween('date_expense', [$start, $end])
                ->get()
                ->filter(fn($e) => $e->category?->account?->account_activity === $activity);

            foreach ($expenses as $e) {
                $amount = $e->amount_expense;
                $name = $e->category->name_category ?? 'Pengeluaran Lainnya';
                $rows->push(['keterangan' => $name, 'jumlah' => $amount, 'type' => 'out']);
                $totalOut += $amount;
            }

            if ($rows->isNotEmpty()) {
                $netCashFlow = $totalIn - $totalOut;

                $rows->push(['keterangan' => '', 'jumlah' => '', 'type' => '']);
                $rows->push(['keterangan' => 'Arus kas neto dari ' . ucfirst($activity), 'jumlah' => abs($netCashFlow), 'type' => 'neto']);

                $result->push([
                    'activity' => ucfirst($activity),
                    'accounts' => $rows,
                    'total' => $netCashFlow,
                ]);

                $grandTotalIn += $totalIn;
                $grandTotalOut += $totalOut;
            }
        }

        $netKas = $grandTotalIn - $grandTotalOut;
        $this->kasAkhir = $kasAwal + $netKas;

        $result->push([
            'activity' => 'Kenaikan (penurunan) bersih kas',
            'accounts' => collect([
                ['keterangan' => 'Kenaikan (penurunan) bersih kas', 'jumlah' => abs($netKas), 'type' => 'neto'],
                ['keterangan' => '', 'jumlah' => '', 'type' => ''],
                ['keterangan' => 'Saldo akhir kas', 'jumlah' => abs($this->kasAkhir), 'type' => 'neto']
            ]),
            'total' => $this->kasAkhir
        ]);

        return $result;
    }

    protected function getViewData(): array
    {
        return [
            'records' => $this->records,
            'month' => $this->month,
            'kasAwal' => $this->kasAkhir - ($this->records->sum('total')),
            'kasAkhir' => $this->kasAkhir,
        ];
    }


    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }
}
