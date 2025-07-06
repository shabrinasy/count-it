<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class KasChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan vs Pengeluaran';

    // Filter Bulan
    public $bulanRange = 'Jan - Jul'; // Default

    // Method untuk mendapatkan data chart
    protected function getData(): array
    {
        // Menentukan rentang bulan yang dipilih
        $months = $this->getMonths($this->bulanRange);

        // Ambil data pemasukan dan pengeluaran untuk bulan yang dipilih
        $incomes = $this->getIncomesForMonths($months);
        $expenses = $this->getExpensesForMonths($months);

        // Gabungkan pemasukan dan pengeluaran
        return [
            'labels' => $months, // X-Axis (Bulan)
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomes,
                    'borderColor' => 'green', // Warna garis pemasukan
                    'fill' => false,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenses,
                    'borderColor' => 'red', // Warna garis pengeluaran
                    'fill' => false,
                ],
            ],
        ];
    }

    // Menentukan bulan berdasarkan pilihan filter
    protected function getMonths($range): array
    {
        if ($range === 'Jan - Jul') {
            return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
        }

        return ['Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }

    // Method untuk mengambil data pemasukan berdasarkan bulan
    protected function getIncomesForMonths($months): array
    {
        $incomes = [];
        foreach ($months as $month) {
            $income = DB::table('incomes')
                ->whereMonth('date_income', $this->getMonthNumber($month))
                ->sum('amount_income');
            $incomes[] = $income;
        }
        return $incomes;
    }

    // Method untuk mengambil data pengeluaran berdasarkan bulan
    protected function getExpensesForMonths($months): array
    {
        $expenses = [];
        foreach ($months as $month) {
            $expense = DB::table('expenses')
                ->whereMonth('date_expense', $this->getMonthNumber($month))
                ->sum('amount_expense');
            $expenses[] = $expense;
        }
        return $expenses;
    }

    // Helper untuk mengubah nama bulan menjadi angka (1-12)
    protected function getMonthNumber($month): int
    {
        $months = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7,
            'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];
        return $months[$month] ?? 1; // Default ke Januari
    }

    // Menentukan jenis chart
    protected function getType(): string
    {
        return 'line'; // Jenis chart adalah line chart
    }

    // Mengatur tampilan full width
    protected function getViewData(): array
    {
        return [
            'chart_width' => '100%', // Mengatur chart agar memenuhi lebar penuh
        ];
    }
}
