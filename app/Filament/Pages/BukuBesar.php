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
        $models = [
            [Purchase::with('purchaseItems')->where('date', '<', $start)->get(), 'purchase'],
            [Order::with('orderItem')->where('created_at', '<', $start)->get(), 'order'],
            [Income::with('category')->where('date_income', '<', $start)->get(), 'income'],
            [Expense::with('category')->where('date_expense', '<', $start)->get(), 'expense'],
        ];

        foreach ($models as [$data, $type]) {
            foreach ($data as $item) {
                switch ($type) {
                    case 'purchase':
                        $total = $item->purchaseItems->sum(fn($i) => $i->quantity * $i->price);
                        if ($account->code_account == '1103') $previousTransactions->push(['debit' => $total, 'credit' => 0]);
                        elseif ($account->code_account == '1101') $previousTransactions->push(['debit' => 0, 'credit' => $total]);
                        break;
                    case 'order':
                        $total = $item->orderItem->sum(fn($i) => $i->quantity * $i->price);
                        if ($account->code_account == '1101') $previousTransactions->push(['debit' => $total, 'credit' => 0]);
                        elseif ($account->code_account == '4101') $previousTransactions->push(['debit' => 0, 'credit' => $total]);
                        break;
                    case 'income':
                        $amount = $item->amount_income;
                        $akunKategori = Account::find($item->category->account_id);
                        if (!$akunKategori) continue;
                        if ($account->code_account == '1101') $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
                        elseif ($account->id == $akunKategori->id) $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
                        break;
                    case 'expense':
                        $amount = $item->amount_expense;
                        $akunKategori = Account::find($item->category->account_id);
                        if (!$akunKategori) continue;
                        if ($account->id == $akunKategori->id) $previousTransactions->push(['debit' => $amount, 'credit' => 0]);
                        elseif ($account->code_account == '1101') $previousTransactions->push(['debit' => 0, 'credit' => $amount]);
                        break;
                }
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
        $entries = collect();

        $entries = $entries->merge($this->processTransactions(Purchase::with('purchaseItems')->whereBetween('date', [$start, $end])->get(), 'purchase', $account));
        $entries = $entries->merge($this->processTransactions(Order::with('orderItem')->whereBetween('created_at', [$start, $end])->get(), 'order', $account));
        $entries = $entries->merge($this->processTransactions(Income::with('category')->whereBetween('date_income', [$start, $end])->get(), 'income', $account));
        $entries = $entries->merge($this->processTransactions(Expense::with('category')->whereBetween('date_expense', [$start, $end])->get(), 'expense', $account));

        $transactions = $transactions->merge($entries);

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

    private function processTransactions($items, $type, $account)
    {
        $result = collect();

        foreach ($items as $item) {
            switch ($type) {
                case 'purchase':
                    $total = $item->purchaseItems->sum(fn($i) => $i->quantity * $i->price);
                    if ($account->code_account == '1103') {
                        $result->push([...]);
                    } elseif ($account->code_account == '1101') {
                        $result->push([...]);
                    }
                    break;
                case 'order':
                    $total = $item->orderItem->sum(fn($i) => $i->quantity * $i->price);
                    if ($account->code_account == '1101') {
                        $result->push([...]);
                    } elseif ($account->code_account == '4101') {
                        $result->push([...]);
                    }
                    break;
                case 'income':
                    $amount = $item->amount_income;
                    $akunKategori = Account::find($item->category->account_id);
                    if (!$akunKategori) continue;
                    if ($account->code_account == '1101') {
                        $result->push([...]);
                    } elseif ($account->id == $akunKategori->id) {
                        $result->push([...]);
                    }
                    break;
                case 'expense':
                    $amount = $item->amount_expense;
                    $akunKategori = Account::find($item->category->account_id);
                    if (!$akunKategori) continue;
                    if ($account->id == $akunKategori->id) {
                        $result->push([...]);
                    } elseif ($account->code_account == '1101') {
                        $result->push([...]);
                    }
                    break;
            }
        }

        return $result;
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }
}
