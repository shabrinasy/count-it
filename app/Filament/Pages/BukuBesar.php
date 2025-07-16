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
    $prevPurchases = Purchase::with('purchaseItems')->where('date', '<', $start)->get();
    foreach ($prevPurchases as $p) {
        $total = $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
        if ($account->code_account == '1103') $previousTransactions->push(['debit' => $total, 'credit' => 0]);
        elseif ($account->code_account == '1101') $previousTransactions->push(['debit' => 0, 'credit' => $total]);
    }

    $prevOrders = Order::with('orderItem')->where('created_at', '<', $start)->get();
    foreach ($prevOrders as $o) {
        $total = $o->orderItem->sum(fn($item) => $item->quantity * $item->price);
        if ($account->code_account == '1101') $previousTransactions->push(['debit' => $total, 'credit' => 0]);
        elseif ($account->code_account == '4101') $previousTransactions->push(['debit' => 0, 'credit' => $total]);
    }

    $prevIncomes = Income::with('category')->where('date_income', '<', $start)->get();
    foreach ($prevIncomes as $i) {
        $amount = $i->amount_income;
        $akunKategori = Account::find($i->category->account_id);
        if (!$akunKategori) continue;

        if ($account->code_account == '1101') {
            $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
        } elseif ($account->id == $akunKategori->id) {
            $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
        }
    }

    $prevExpenses = Expense::with('category')->where('date_expense', '<', $start)->get();
    foreach ($prevExpenses as $e) {
        $amount = $e->amount_expense;
        $akunKategori = Account::find($e->category->account_id);
        if (!$akunKategori) continue;

        if ($account->id == $akunKategori->id) {
            $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
        } elseif ($account->code_account == '1101') {
            $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
        }
    }

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

        if ($account->code_account == '1103') {
            $transactions->push([
                'date' => $p->date,
                'transaksi' => 'Pembelian',
                'nomor' => $p->code,
                'keterangan' => 'Kas',
                'kode_akun' => '1101',
                'debit' => $total,
                'credit' => 0,
            ]);
        } elseif ($account->code_account == '1101') {
            $transactions->push([
                'date' => $p->date,
                'transaksi' => 'Pembelian',
                'nomor' => $p->code,
                'keterangan' => 'Persediaan Bahan Baku',
                'kode_akun' => '1103',
                'debit' => 0,
                'credit' => $total,
            ]);
        }
    }

    // ORDERS
    $orders = Order::with('orderItem')->whereBetween('created_at', [$start, $end])->get();
    foreach ($orders as $o) {
        $total = $o->orderItem->sum(fn($item) => $item->quantity * $item->price);

        if ($account->code_account == '1101') {
            $transactions->push([
                'date' => $o->created_at->toDateString(),
                'transaksi' => 'Penjualan',
                'nomor' => $o->code,
                'keterangan' => 'Penjualan',
                'kode_akun' => '4101',
                'debit' => $total,
                'credit' => 0,
            ]);
        } elseif ($account->code_account == '4101') {
            $transactions->push([
                'date' => $o->created_at->toDateString(),
                'transaksi' => 'Penjualan',
                'nomor' => $o->code,
                'keterangan' => 'Kas',
                'kode_akun' => '1101',
                'debit' => 0,
                'credit' => $total,
            ]);
        }
    }

    // INCOMES
    $incomes = Income::with('category')->whereBetween('date_income', [$start, $end])->get();
    foreach ($incomes as $i) {
        $amount = $i->amount_income;
        $akunKategori = Account::find($i->category->account_id);
        if (!$akunKategori) continue;

        if ($account->code_account == '1101') {
            $transactions->push([
                'date' => $i->date_income,
                'transaksi' => 'Pemasukan',
                'nomor' => $i->code_income,
                'keterangan' => $akunKategori->name_account,
                'kode_akun' => $akunKategori->code_account,
                'debit' => $amount,
                'credit' => 0,
            ]);
        } elseif ($account->id == $akunKategori->id) {
            $transactions->push([
                'date' => $i->date_income,
                'transaksi' => 'Pemasukan',
                'nomor' => $i->code_income,
                'keterangan' => 'Kas',
                'kode_akun' => '1101',
                'debit' => 0,
                'credit' => $amount,
            ]);
        }
    }

    // EXPENSES
    $expenses = Expense::with('category')->whereBetween('date_expense', [$start, $end])->get();
    foreach ($expenses as $e) {
        $amount = $e->amount_expense;
        $akunKategori = Account::find($e->category->account_id);
        if (!$akunKategori) continue;

        if ($account->id == $akunKategori->id) {
            $transactions->push([
                'date' => $e->date_expense,
                'transaksi' => 'Pengeluaran',
                'nomor' => $e->code_expense,
                'keterangan' => 'Kas',
                'kode_akun' => '1101',
                'debit' => $amount,
                'credit' => 0,
            ]);
        } elseif ($account->code_account == '1101') {
            $transactions->push([
                'date' => $e->date_expense,
                'transaksi' => 'Pengeluaran',
                'nomor' => $e->code_expense,
                'keterangan' => $akunKategori->name_account,
                'kode_akun' => $akunKategori->code_account,
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
                'kode_akun' => $entry['kode_akun'] ?? null,
            ];
        })->values()->toArray();
}



public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}


}
