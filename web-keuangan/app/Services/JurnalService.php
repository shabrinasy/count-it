<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;

class JurnalService
{
    public static function getJurnalUmum(): array
    {
        $journals = [];

        // Income
        foreach (Income::with('category.account')->get() as $income) {
            $account = $income->category->account;
            $isDebit = strtolower($account->balance) === 'debit';
            $journals[] = [
                'date' => $income->transaction_date,
                'code' => $income->code_income,
                'transaction' => 'Income: ' . $income->name_income,
                'entries' => [
                    ['account' => 'Kas', 'debit' => $isDebit ? $income->amount_income : 0, 'credit' => $isDebit ? 0 : $income->amount_income],
                    ['account' => $account->name, 'debit' => $isDebit ? 0 : $income->amount_income, 'credit' => $isDebit ? $income->amount_income : 0],
                ]
            ];
        }

        // Expense
        foreach (Expense::with('category.account')->get() as $expense) {
            $account = $expense->category->account;
            $isDebit = strtolower($account->balance) === 'debit';
            $journals[] = [
                'date' => $expense->transaction_date,
                'code' => $expense->code_expense,
                'transaction' => 'Expense: ' . $expense->name_expense,
                'entries' => [
                    ['account' => 'Kas', 'debit' => $isDebit ? 0 : $expense->amount_expense, 'credit' => $isDebit ? $expense->amount_expense : 0],
                    ['account' => $account->name, 'debit' => $isDebit ? $expense->amount_expense : 0, 'credit' => $isDebit ? 0 : $expense->amount_expense],
                ]
            ];
        }

        // Order (Penjualan)
        foreach (Order::with('orderItem')->get() as $order) {
            $total = 0;
            foreach ($order->orderItem as $item) {
                $total += $item->quantity * $item->price;
            }

            $journals[] = [
                'date' => $order->created_at->format('Y-m-d'),
                'code' => $order->code,
                'transaction' => 'Penjualan: ' . $order->code,
                'entries' => [
                    ['account' => 'Kas', 'debit' => $total, 'credit' => 0],
                    ['account' => 'Pendapatan Penjualan', 'debit' => 0, 'credit' => $total],
                ]
            ];
        }

        // Purchase (Pembelian)
        foreach (Purchase::with('purchaseItems.supplies')->get() as $purchase) {
            $total = 0;
            foreach ($purchase->purchaseItems as $item) {
                $total += $item->quantity * $item->price;
            }

            $journals[] = [
                'date' => $purchase->date,
                'code' => $purchase->code,
                'transaction' => 'Pembelian: ' . $purchase->code,
                'entries' => [
                    ['account' => 'Persediaan Bahan Baku', 'debit' => $total, 'credit' => 0],
                    ['account' => 'Kas', 'debit' => 0, 'credit' => $total],
                ]
            ];
        }

        return $journals;
    }
}
