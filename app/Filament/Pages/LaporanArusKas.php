<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Order;
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

    public function mount()
    {
        $this->month = null; // pengguna wajib pilih dulu
    }


    public function getRecordsProperty()
{
    if (!$this->month) {
        return collect(); // Penting! untuk support empty state
    }

    $start = Carbon::parse($this->month . '-01')->startOfMonth();
    $end = Carbon::parse($this->month . '-01')->endOfMonth();

    $accounts = Account::whereNotNull('account_activity')->get();

    $result = collect();

    foreach (['operating', 'investing', 'financing'] as $activity) {
        $activityAccounts = $accounts->where('account_activity', $activity);

        $data = $activityAccounts->map(function ($account) use ($start, $end) {
            $total = 0;

            // Purchase (khusus akun persediaan)
            if ($account->code_account === '1103') {
                $purchases = Purchase::with('purchaseItems')->whereBetween('date', [$start, $end])->get();
                $total -= $purchases->sum(fn($p) => $p->purchaseItems->sum(fn($i) => $i->quantity * $i->price)); // Kas berkurang
            }

            // Order (khusus akun penjualan)
            if ($account->code_account === '4101') {
                $orders = Order::with('orderItem')->whereBetween('created_at', [$start, $end])->get();
                $total += $orders->sum(fn($o) => $o->orderItem->sum(fn($i) => $i->quantity * $i->price)); // Kas bertambah
            }

            // Income
            $incomes = Income::with('category.account')->whereBetween('date_income', [$start, $end])->get();
            foreach ($incomes as $income) {
                if ($income->category && $income->category->account_id == $account->id) {
                    $total += $income->amount_income; // Kas bertambah
                }
            }

            // Expense
            $expenses = Expense::with('category.account')->whereBetween('date_expense', [$start, $end])->get();
            foreach ($expenses as $expense) {
                if ($expense->category && $expense->category->account_id == $account->id) {
                    $total -= $expense->amount_expense; // Kas berkurang
                }
            }

            // Jika akun balance-nya credit → total dijadikan negatif
            if ($account->balance === 'credit') {
                $total *= -1;
            }

            return [
                'code' => $account->code_account,
                'name' => $account->name_account,
                'amount' => $total,
            ];
        })->filter(fn ($x) => $x['amount'] != 0);

        $totalKas = $data->sum('amount');

        $result->push([
            'activity' => ucfirst($activity) . ' Activity',
            'accounts' => $data->values(),
            'total' => $totalKas,
        ]);
    }

    return $result;
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
