<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Order;
use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;

class BukuBesar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $title = 'Buku Besar';
    protected static ?string $navigationGroup = 'Laporan';

    public $accountId;
    public $month;
    public $entries = [];

    protected static string $view = 'filament.pages.buku-besar';

    public function mount()
    {
        $this->month = null;
        $this->accountId = null;
        $this->entries = [];
    }


    public function updated($property)
    {
        if ($this->month && $this->accountId && in_array($property, ['month', 'accountId'])) {
            $this->generateEntries();
        }
    }

    public function generateEntries()
{
    $this->entries = [];

    if (!$this->accountId) return;

    $account = Account::find($this->accountId);
    $start = Carbon::parse($this->month . '-01')->startOfMonth();
    $end = Carbon::parse($this->month . '-01')->endOfMonth();

    $transactions = collect();
    $previousTransactions = collect();

    // === SALDO AWAL ===
    // Purchases sebelum bulan ini
    $prevPurchases = Purchase::with('purchaseItems')->where('date', '<', $start)->get();
    foreach ($prevPurchases as $p) {
        $total = $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
        if ($account->id == 5) $previousTransactions->push(['debit' => $total, 'credit' => 0]);
        elseif ($account->id == 3) $previousTransactions->push(['debit' => 0, 'credit' => $total]);
    }

    // Orders sebelum bulan ini
    $prevOrders = Order::with('orderItem')->where('created_at', '<', $start)->get();
    foreach ($prevOrders as $o) {
        $total = $o->orderItem->sum(fn($item) => $item->quantity * $item->price);
        if ($account->id == 3) $previousTransactions->push(['debit' => $total, 'credit' => 0]);
        elseif ($account->id == 14) $previousTransactions->push(['debit' => 0, 'credit' => $total]);
    }

    // Incomes sebelum bulan ini
    $prevIncomes = Income::with('category')->where('date_income', '<', $start)->get();
    foreach ($prevIncomes as $i) {
        $amount = $i->amount_income;
        $akunKategori = $i->category->account_id;
        if ($account->id == 3) $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
        elseif ($account->id == $akunKategori) $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
    }

    // Expenses sebelum bulan ini
    $prevExpenses = Expense::with('category')->where('date_expense', '<', $start)->get();
    foreach ($prevExpenses as $e) {
        $amount = $e->amount_expense;
        $akunKategori = $e->category->account_id;
        if ($account->id == $akunKategori) $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
        elseif ($account->id == 3) $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
    }

    // Hitung saldo awal
    $totalDebit = $previousTransactions->sum('debit');
    $totalCredit = $previousTransactions->sum('credit');
    $saldoAwalDebit = 0;
    $saldoAwalKredit = 0;

    if ($account->balance === 'debit') {
        $saldoAwalDebit = $totalDebit - $totalCredit;
    } else {
        $saldoAwalKredit = $totalCredit - $totalDebit;
    }

    $transactions->push([
        'date' => $start->format('Y-m-d'),
        'transaksi' => 'Saldo Awal',
        'nomor' => '-',
        'keterangan' => 'Saldo awal bulan ' . $start->translatedFormat('F Y'),
        'debit' => 0,
        'credit' => 0,
        'saldo_debit' => $saldoAwalDebit ?: '',
        'saldo_kredit' => $saldoAwalKredit ?: '',
    ]);

    // === TRANSAKSI BULAN INI ===

    // PURCHASES
    $purchases = Purchase::with('purchaseItems')->whereBetween('date', [$start, $end])->get();
    foreach ($purchases as $p) {
        $total = $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price);

        if ($account->id == 5) {
            $transactions->push([
                'date' => $p->date,
                'transaksi' => 'Pembelian',
                'nomor' => $p->code,
                'keterangan' => $p->notes ?? 'Pembelian bahan baku',
                'debit' => $total,
                'credit' => 0,
            ]);
        } elseif ($account->id == 3) {
            $transactions->push([
                'date' => $p->date,
                'transaksi' => 'Pembelian',
                'nomor' => $p->code,
                'keterangan' => $p->notes ?? 'Pembelian bahan baku',
                'debit' => 0,
                'credit' => $total,
            ]);
        }
    }

    // ORDERS
    $orders = Order::with('orderItem')->whereBetween('created_at', [$start, $end])->get();
    foreach ($orders as $o) {
        $total = $o->orderItem->sum(fn($item) => $item->quantity * $item->price);

        if ($account->id == 3) {
            $transactions->push([
                'date' => $o->created_at->toDateString(),
                'transaksi' => 'Penjualan',
                'nomor' => $o->code,
                'keterangan' => 'Penjualan oleh karyawan',
                'debit' => $total,
                'credit' => 0,
            ]);
        } elseif ($account->id == 14) {
            $transactions->push([
                'date' => $o->created_at->toDateString(),
                'transaksi' => 'Penjualan',
                'nomor' => $o->code,
                'keterangan' => 'Penjualan oleh karyawan',
                'debit' => 0,
                'credit' => $total,
            ]);
        }
    }

    // INCOMES
    $incomes = Income::with('category')->whereBetween('date_income', [$start, $end])->get();
    foreach ($incomes as $i) {
        $amount = $i->amount_income;
        $akunKategori = $i->category->account_id;

        if ($account->id == 3) {
            $transactions->push([
                'date' => $i->date_income,
                'transaksi' => 'Pemasukan',
                'nomor' => $i->code_income,
                'keterangan' => $i->name_income,
                'debit' => $amount,
                'credit' => 0,
            ]);
        } elseif ($account->id == $akunKategori) {
            $transactions->push([
                'date' => $i->date_income,
                'transaksi' => 'Pemasukan',
                'nomor' => $i->code_income,
                'keterangan' => $i->name_income,
                'debit' => 0,
                'credit' => $amount,
            ]);
        }
    }

    // EXPENSES
    $expenses = Expense::with('category')->whereBetween('date_expense', [$start, $end])->get();
    foreach ($expenses as $e) {
        $amount = $e->amount_expense;
        $akunKategori = $e->category->account_id;

        if ($account->id == $akunKategori) {
            $transactions->push([
                'date' => $e->date_expense,
                'transaksi' => 'Pengeluaran',
                'nomor' => $e->code_expense,
                'keterangan' => $e->name_expense,
                'debit' => $amount,
                'credit' => 0,
            ]);
        } elseif ($account->id == 3) {
            $transactions->push([
                'date' => $e->date_expense,
                'transaksi' => 'Pengeluaran',
                'nomor' => $e->code_expense,
                'keterangan' => $e->name_expense,
                'debit' => 0,
                'credit' => $amount,
            ]);
        }
    }

    // SALDO BERJALAN
    $saldoDebit = $saldoAwalDebit;
    $saldoKredit = $saldoAwalKredit;

    $this->entries = $transactions
        ->sortBy('date')
        ->map(function ($entry) use ($account, &$saldoDebit, &$saldoKredit) {
            $debit = $entry['debit'] ?? 0;
            $credit = $entry['credit'] ?? 0;

            if ($account->balance === 'debit') {
                $saldoDebit += $debit - $credit;
                $saldoKredit = 0;
            } else {
                $saldoKredit += $credit - $debit;
                $saldoDebit = 0;
            }

            return [
                'tanggal' => $entry['date'],
                'transaksi' => $entry['transaksi'],
                'nomor' => $entry['nomor'],
                'keterangan' => $entry['keterangan'],
                'debit' => $debit,
                'kredit' => $credit,
                'saldo_debit' => $saldoDebit ?: '',
                'saldo_kredit' => $saldoKredit ?: '',
            ];
        })->values()->toArray();
}

public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}


}
