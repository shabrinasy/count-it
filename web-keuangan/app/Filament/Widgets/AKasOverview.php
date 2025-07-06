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

        // Total pemasukan bulan sebelumnya
        $previousMonthIncomes = DB::table('incomes')
            ->whereMonth('date_income', now()->subMonth()->month)
            ->sum('amount_income');

        // Perbandingan Pemasukan Bulan Ini vs Bulan Sebelumnya
        if ($incomes > $previousMonthIncomes) {
            $incomeDescription = number_format($incomes - $previousMonthIncomes, 0, ',', '.').' increase';
            $incomeIcon = 'heroicon-m-arrow-trending-up';
            $incomeColor = 'success';
        } elseif ($incomes < $previousMonthIncomes) {
            $incomeDescription = number_format($previousMonthIncomes - $incomes, 0, ',', '.').' decrease';
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

        // Total pengeluaran bulan sebelumnya
        $previousMonthExpenses = DB::table('expenses')
            ->whereMonth('date_expense', now()->subMonth()->month)
            ->sum('amount_expense');

        // Perbandingan Pengeluaran Bulan Ini vs Bulan Sebelumnya
        if ($expenses > $previousMonthExpenses) {
            $expenseDescription = number_format($expenses - $previousMonthExpenses, 0, ',', '.').' increase';
            $expenseIcon = 'heroicon-m-arrow-trending-up';
            $expenseColor = 'success';
        } elseif ($expenses < $previousMonthExpenses) {
            $expenseDescription = number_format($previousMonthExpenses - $expenses, 0, ',', '.').' decrease';
            $expenseIcon = 'heroicon-m-arrow-trending-down';
            $expenseColor = 'danger';
        } else {
            $expenseDescription = 'increase but 0';
            $expenseIcon = 'heroicon-m-arrow-trending-up';
            $expenseColor = 'success';
        }

        // Saldo saat ini (pemasukan - pengeluaran)
        $currentBalance = $incomes - $expenses;

        // Saldo bulan sebelumnya
        $previousBalance = $previousMonthIncomes - $previousMonthExpenses;

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
            Stat::make('Total Pemasukan Bulan ini', 'Rp. ' . number_format($incomes, 0, ',', '.'))
                ->description($incomeDescription)
                ->descriptionIcon($incomeIcon)
                ->color($incomeColor),
            Stat::make('Total Pengeluaran Bulan ini', 'Rp. ' . number_format($expenses, 0, ',', '.'))
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
