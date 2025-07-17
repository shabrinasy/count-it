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

        // Hitung saldo awal dari semua transaksi sebelumnya
        $allTransactions = collect();

        // Ambil transaksi pembelian
        $prevPurchases = Purchase::with('purchaseItems')->where('date', '<', $start)->get();
        foreach ($prevPurchases as $p) {
            $total = $p->purchaseItems->sum(fn($item) => $item->quantity * $item->price);
            if ($account->code_account == '1103') $allTransactions->push(['debit' => $total, 'credit' => 0]);
            elseif ($account->code_account == '1101') $allTransactions->push(['debit' => 0, 'credit' => $total]);
        }

        // Ambil transaksi penjualan
        $prevOrders = Order::with('orderItem')->where('created_at', '<', $start)->get();
        foreach ($prevOrders as $o) {
            $total = $o->orderItem->sum(fn($item) => $item->quantity * $item->price);
            if ($account->code_account == '1101') $allTransactions->push(['debit' => $total, 'credit' => 0]);
            elseif ($account->code_account == '4101') $allTransactions->push(['debit' => 0, 'credit' => $total]);
        }

        // Ambil pemasukan
        $prevIncomes = Income::with('category')->where('date_income', '<', $start)->get();
        foreach ($prevIncomes as $i) {
            $amount = $i->amount_income;
            $akunKategori = Account::find($i->category->account_id);
            if (!$akunKategori) continue;

            if ($account->code_account == '1101') {
                $allTransactions->push(['debit' => $amount, 'credit' => 0]);
            } elseif ($account->id == $akunKategori->id) {
                $allTransactions->push(['debit' => 0, 'credit' => $amount]);
            }
        }

        // Ambil pengeluaran
        $prevExpenses = Expense::with('category')->where('date_expense', '<', $start)->get();
        foreach ($prevExpenses as $e) {
            $amount = $e->amount_expense;
            $akunKategori = Account::find($e->category->account_id);
            if (!$akunKategori) continue;

            if ($account->id == $akunKategori->id) {
                $allTransactions->push(['debit' => $amount, 'credit' => 0]);
            } elseif ($account->code_account == '1101') {
                $allTransactions->push(['debit' => 0, 'credit' => $amount]);
            }
        }

        $totalDebit = $allTransactions->sum('debit');
        $totalCredit = $allTransactions->sum('credit');
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

        // Selanjutnya: isi transaksi bulan ini (purchases, orders, incomes, expenses)
        // Tidak perlu diubah karena sudah benar

        // ... [biarkan bagian TRANSAKSI BULAN INI tetap seperti di kode Anda]

        // Saldo berjalan
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
