<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AKasOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Total pemasukan bulan ini
        $incomes = DB::table('incomes')
            ->whereMonth('date_income', now()->month)
            ->sum('amount_income');

        // Total penjualan bulan ini (diambil dari order_items)
        $sales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.orders_id')
            ->whereMonth('orders.created_at', now()->month)
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        $totalIncomes = $incomes + $sales;  // Gabungkan pemasukan dan penjualan

        // Total pemasukan bulan sebelumnya
        $previousMonthIncomes = DB::table('incomes')
            ->whereMonth('date_income', now()->subMonth()->month)
            ->sum('amount_income');

        $previousSales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.orders_id')
            ->whereMonth('orders.created_at', now()->subMonth()->month)
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        $previousTotalIncomes = $previousMonthIncomes + $previousSales;

        // Perbandingan Pemasukan Bulan Ini vs Bulan Sebelumnya
        if ($totalIncomes > $previousTotalIncomes) {
            $incomeDescription = number_format($totalIncomes - $previousTotalIncomes, 0, ',', '.').' increase';
            $incomeIcon = 'heroicon-m-arrow-trending-up';
            $incomeColor = 'success';
        } elseif ($totalIncomes < $previousTotalIncomes) {
            $incomeDescription = number_format($previousTotalIncomes - $totalIncomes, 0, ',', '.').' decrease';
            $incomeIcon = 'heroicon-m-arrow-trending-down';
            $incomeColor = 'danger';
        } else {
            $incomeDescription = 'increase but 0';
            $incomeIcon = 'heroicon-m-arrow-trending-up';
            $incomeColor = 'success';
        }

        // Total pengeluaran bulan ini
        $expenses = DB::table('expenses')
            ->whereMonth('date_expense', now()->month)
            ->sum('amount_expense');

        // Total pembelian bulan ini (diambil dari purchase_items)
        $purchaseExpenses = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->whereMonth('purchases.date', now()->month)
            ->sum(DB::raw('purchase_items.quantity * purchase_items.price'));

        $totalExpenses = $expenses + $purchaseExpenses;

        // Total pengeluaran bulan sebelumnya
        $previousMonthExpenses = DB::table('expenses')
            ->whereMonth('date_expense', now()->subMonth()->month)
            ->sum('amount_expense');

        $previousPurchaseExpenses = DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
            ->whereMonth('purchases.date', now()->subMonth()->month)
            ->sum(DB::raw('purchase_items.quantity * purchase_items.price'));

        $previousTotalExpenses = $previousMonthExpenses + $previousPurchaseExpenses;

        // Perbandingan Pengeluaran Bulan Ini vs Bulan Sebelumnya
        if ($totalExpenses > $previousTotalExpenses) {
            $expenseDescription = number_format($totalExpenses - $previousTotalExpenses, 0, ',', '.').' increase';
            $expenseIcon = 'heroicon-m-arrow-trending-up';
            $expenseColor = 'success';
        } elseif ($totalExpenses < $previousTotalExpenses) {
            $expenseDescription = number_format($previousTotalExpenses - $totalExpenses, 0, ',', '.').' decrease';
            $expenseIcon = 'heroicon-m-arrow-trending-down';
            $expenseColor = 'danger';
        } else {
            $expenseDescription = 'increase but 0';
            $expenseIcon = 'heroicon-m-arrow-trending-up';
            $expenseColor = 'success';
        }

        // Saldo saat ini (pemasukan - pengeluaran)
        $currentBalance = $totalIncomes - $totalExpenses;

        // Saldo bulan sebelumnya
        $previousBalance = $previousTotalIncomes - $previousTotalExpenses;

        // Perbandingan Saldo Bulan Ini vs Bulan Sebelumnya
        if ($currentBalance > $previousBalance) {
            $balanceDescription = number_format($currentBalance - $previousBalance, 0, ',', '.').' increase';
            $balanceIcon = 'heroicon-m-arrow-trending-up';
            $balanceColor = 'success';
        } elseif ($currentBalance < $previousBalance) {
            $balanceDescription = number_format($previousBalance - $currentBalance, 0, ',', '.').' decrease';
            $balanceIcon = 'heroicon-m-arrow-trending-down';
            $balanceColor = 'danger';
        } else {
            $balanceDescription = '0 increase';
            $balanceIcon = 'heroicon-m-arrow-trending-up';
            $balanceColor = 'success';
        }

        return [
            Stat::make('Total Pemasukan Bulan ini', 'Rp. ' . number_format($totalIncomes, 0, ',', '.'))
                ->description($incomeDescription)
                ->descriptionIcon($incomeIcon)
                ->color($incomeColor),
            Stat::make('Total Pengeluaran Bulan ini', 'Rp. ' . number_format($totalExpenses, 0, ',', '.'))
                ->description($expenseDescription)
                ->descriptionIcon($expenseIcon)
                ->color($expenseColor),
            Stat::make('Saldo Kas Saat Ini', 'Rp. ' . number_format($currentBalance, 0, ',', '.'))
                ->description($balanceDescription)
                ->descriptionIcon($balanceIcon)
                ->color($balanceColor),
        ];
    }
}
