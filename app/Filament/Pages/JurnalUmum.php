<?php

namespace App\Filament\Pages;

use App\Services\JurnalService;
use App\Filament\Resources\JurnalUmumResource;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Carbon\Carbon;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Account;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;

class JurnalUmum extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $title = 'Jurnal Umum';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Jurnal Umum';

    public function downloadPDF($bulan)
    {
        // Ambil data berdasarkan bulan
        $records = $this->getRecordsProperty();  
        
        // Generate PDF menggunakan DomPDF
        $pdf = PDF::loadView('filament.pages.jurnal-pdf', compact('records', 'bulan'));
        
        // Mengunduh PDF dengan nama file yang diinginkan
        return $pdf->download('jurnalumum_' . $bulan . '.pdf');
    }

    public function getAccount(string $name)
    {
        return \App\Models\Account::where('name_account', $name)->firstOr(function () use ($name) {
            return (object) [
                'code_account' => '-',
                'name_account' => $name
            ];
        });
    }

    public $bulan;

    public function mount()
    {
        $this->bulan = null;
    }

    public function getRecordsProperty()
{
    $bulan = Carbon::parse($this->bulan);

    $data = collect();

    $akunKas = $this->getAccount('Kas');
    $akunPersediaan = $this->getAccount('Persediaan Bahan Baku');
    $akunPendapatan = $this->getAccount('Penjualan');

    // INCOME
    $incomes = Income::with('category.account')
    ->whereMonth('date_income', $bulan->month)
    ->whereYear('date_income', $bulan->year)
    ->get()
    ->map(function ($item) use ($akunKas) {
        $akunKategori = $item->category && $item->category->account
            ? [
                'code' => $item->category->account->code_account,
                'name' => $item->category->account->name_account,
            ]
            : [
                'code' => '-',
                'name' => 'Akun Tidak Ditemukan',
            ];

        return [
            'date' => $item->date_income,
            'code' => $item->code_income,
            'entries' => [
                ['account' => ['code' => $akunKas->code_account, 'name' => $akunKas->name_account], 'debit' => $item->amount_income, 'credit' => 0],
                ['account' => $akunKategori, 'debit' => 0, 'credit' => $item->amount_income],
            ],
        ];
    });


    // EXPENSE
    $expenses = Expense::with('category.account')
    ->whereMonth('date_expense', $bulan->month)
    ->whereYear('date_expense', $bulan->year)
    ->get()
    ->map(function ($item) use ($akunKas) {
        $akunKategori = $item->category && $item->category->account
            ? [
                'code' => $item->category->account->code_account,
                'name' => $item->category->account->name_account,
            ]
            : [
                'code' => '-',
                'name' => 'Akun Tidak Ditemukan',
            ];

        return [
            'date' => $item->date_expense,
            'code' => $item->code_expense,
            'entries' => [
                ['account' => $akunKategori, 'debit' => $item->amount_expense, 'credit' => 0],
                ['account' => ['code' => $akunKas->code_account, 'name' => $akunKas->name_account], 'debit' => 0, 'credit' => $item->amount_expense],
            ],
        ];
    });



    // PURCHASE
    $purchases = Purchase::with('purchaseItems')
    ->whereMonth('date', $bulan->month)
    ->whereYear('date', $bulan->year)
    ->get()
    ->map(function ($item) use ($akunKas, $akunPersediaan) {
        $total = $item->purchaseItems->sum(fn($purchaseItems) => $purchaseItems->quantity * $purchaseItems->price);
        return [
            'date' => $item->date,
            'code' => $item->code,
            'entries' => [
                ['account' => ['code' => $akunPersediaan->code_account, 'name' => $akunPersediaan->name_account], 'debit' => $total, 'credit' => 0],
                ['account' => ['code' => $akunKas->code_account, 'name' => $akunKas->name_account], 'debit' => 0, 'credit' => $total],
            ],
        ];
    });


    // ORDER (penjualan)
    // Ambil akun HPP dan Persediaan
$akunHPP = $this->getAccount('Harga Pokok Penjualan');
$akunPersediaanBarangDagang = $this->getAccount('Persediaan Barang Dagang');

// ORDER (penjualan)
$orders = Order::with('orderItem.menu.billOfMaterialItems')
    ->whereMonth('created_at', $bulan->month)
    ->whereYear('created_at', $bulan->year)
    ->get()
    ->flatMap(function ($item) use ($akunKas, $akunPendapatan, $akunHPP, $akunPersediaanBarangDagang) {
        // Pakai collect() agar aman
        $total = collect($item->orderItem)->sum(fn($orderItem) => $orderItem->quantity * $orderItem->price);

        $totalHPP = 0;

        foreach (collect($item->orderItem) as $orderItem) {
            $bomItems = $orderItem->menu->billOfMaterialItems ?? collect();

            foreach ($bomItems as $bomItem) {
                $purchase = \App\Models\PurchaseItem::where('supplies_id', $bomItem->supplies_id)
                    ->orderBy('id')
                    ->first();

                $harga = $purchase ? ($purchase->price / $purchase->quantity) : 0;

                $totalHPP += $harga * $bomItem->quantity * $orderItem->quantity;
            }
        }

        return [
            [
                'date' => $item->created_at,
                'code' => $item->code,
                'entries' => [
                    ['account' => ['code' => $akunKas->code_account, 'name' => $akunKas->name_account], 'debit' => $total, 'credit' => 0],
                    ['account' => ['code' => $akunPendapatan->code_account, 'name' => $akunPendapatan->name_account], 'debit' => 0, 'credit' => $total],
                    ['account' => ['code' => $akunHPP->code_account, 'name' => $akunHPP->name_account], 'debit' => $totalHPP, 'credit' => 0],
                    ['account' => ['code' => $akunPersediaanBarangDagang->code_account, 'name' => $akunPersediaanBarangDagang->name_account], 'debit' => 0, 'credit' => $totalHPP],
                ],
            ],
        ];
    });




    // Gabungkan semua data
    $data = $data->merge($incomes)->merge($expenses)->merge($purchases)->merge($orders);

    // Urutkan berdasarkan tanggal
    return $data->sortBy('date')->values();
}


    protected function getViewData(): array
    {
        return [
            'records' => $this->records, // ini otomatis panggil getRecordsProperty()
            'bulan' => $this->bulan,
        ];
    }


    protected function getFormSchema(): array
    {
        return [
            Select::make('bulan')
                ->label('Pilih Bulan')
                ->options($this->generateMonthOptions())
                ->reactive()
                ->afterStateUpdated(fn () => $this->dispatch('refresh'))
        ];
    }

    public function generateMonthOptions(): array
    {
        return collect(range(0, 11))->mapWithKeys(function ($i) {
            $date = now()->subMonths($i);
            return [$date->format('Y-m') => $date->translatedFormat('F Y')];
        })->toArray();
    }


    protected static string $view = 'filament.pages.jurnal-umum';

    public static function canAccess(): bool
{
    return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
}

}
