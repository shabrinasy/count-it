<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BPenjPembOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Total penjualan bulan ini
        $order = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.orders_id')
            ->whereMonth('orders.created_at', now()->month) 
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        // Total penjualan bulan sebelumnya
        $previousMonthOrder = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.orders_id')
            ->whereMonth('orders.created_at', now()->subMonth()->month) 
            ->sum(DB::raw('order_items.quantity * order_items.price'));

        // Perbandingan Penjualan Bulan Ini vs Bulan Sebelumnya
        if ($order > $previousMonthOrder) {
            $description = number_format($order - $previousMonthOrder, 0, ',', '.').' increase';
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
        } elseif ($order < $previousMonthOrder) {
            $description = number_format($previousMonthOrder - $order, 0, ',', '.').' decrease';
            $icon = 'heroicon-m-arrow-trending-down';
            $color = 'danger';
        } else {
            $description = 'increase but 0';
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
        }

        $purchase = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->whereMonth('purchases.date', now()->month)
        ->sum(DB::raw('purchase_items.quantity * purchase_items.price'));
        $previousMonthPurchase = DB::table('purchase_items')
        ->join('purchases', 'purchases.id', '=', 'purchase_items.purchases_id')
        ->whereMonth('purchases.date', now()->subMonth()->month)
        ->sum(DB::raw('purchase_items.quantity * purchase_items.price'));

        if ($purchase > $previousMonthPurchase) {
            $description = number_format($purchase - $previousMonthPurchase, 0, ',', '.').' increase';
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
        } elseif ($purchase < $previousMonthPurchase) {
            $description = number_format($previousMonthPurchase - $purchase, 0, ',', '.').' decrease';
            $icon = 'heroicon-m-arrow-trending-down';
            $color = 'danger';
        } else {
            $description = 'increase but 0';
            $icon = 'heroicon-m-arrow-trending-up';
            $color = 'success';
        }

        return [
            Stat::make('Total Penjualan Bulan ini', 'Rp. ' . number_format($order, 0, ',', '.'))
            ->description($description)
            ->descriptionIcon($icon)
            ->color($color),
            Stat::make('Total Pembelian Bulan ini', 'Rp. ' . number_format($purchase, 0, ',', '.'))
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color),
        ];
    }
}