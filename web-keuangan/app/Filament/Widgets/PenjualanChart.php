<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Penjualan vs Pembelian';

    // Menentukan jenis chart (bar chart)
    protected function getType(): string
    {
        return 'bar'; // Jenis chart adalah bar
    }

    // Mendapatkan data untuk chart
    protected function getData(): array
    {
        // Mengambil data bulan (Jan - Dec)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Mengambil data penjualan dan pembelian per bulan
        $salesData = [];
        $purchaseData = [];
        foreach ($months as $month) {
            // Mengambil total penjualan berdasarkan bulan
            $totalSales = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.orders_id')
                ->whereMonth('orders.created_at', $this->getMonthNumber($month)) // Menggunakan month untuk filter
                ->sum(DB::raw('order_items.quantity * order_items.price'));

            // Mengambil total pembelian berdasarkan bulan
            $totalPurchase = DB::table('purchase_items')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
                ->whereMonth('purchases.date', $this->getMonthNumber($month)) // Menggunakan month untuk filter
                ->sum(DB::raw('purchase_items.quantity * purchase_items.price'));

            $salesData[] = $totalSales;
            $purchaseData[] = $totalPurchase;
        }

        return [
            'labels' => $months, // X-Axis (Bulan)
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $salesData, // Y-Axis data (total penjualan per bulan)
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)', // Warna bar untuk penjualan
                    'borderColor' => 'rgba(54, 162, 235, 1)', // Border warna bar untuk penjualan
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Pembelian',
                    'data' => $purchaseData, // Y-Axis data (total pembelian per bulan)
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)', // Warna bar untuk pembelian
                    'borderColor' => 'rgba(255, 99, 132, 1)', // Border warna bar untuk pembelian
                    'borderWidth' => 1,
                ],
            ],
        ];
    }

    // Helper untuk mengubah nama bulan menjadi angka (1-12)
    protected function getMonthNumber($month): int
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12,
        ];

        return $months[$month] ?? 1; // Default ke Januari
    }
}
