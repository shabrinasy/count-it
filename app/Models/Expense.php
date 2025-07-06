<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_expense',
        'date_expense',
        'name_expense',
        'amount_expense',
        'note_expense',
        'category_id',
    ];

    protected static function booted()
    {
        static::creating(function ($expense) {
            if (empty($expense->code_expense)) {
                $expense->code_expense = self::getKode();
            }
        });
    }

    public static function getKode()
    {
        $tanggal = date('Ymd');

        $prefix = 'TRX-OUT-' . $tanggal . '-';

        $kode = DB::table('expenses')
            ->where('code_expense', 'like', $prefix . '%')
            ->orderByDesc('code_expense')
            ->value('code_expense'); // ambil kode terakhir

        if ($kode) {
            // Pisahkan dan ambil bagian terakhir (nomor urut)
            $parts = explode('-', $kode);
            $noUrutTerakhir = intval(end($parts));
        } else {
            $noUrutTerakhir = 0;
        }

        $noUrutBaru = $noUrutTerakhir + 1;

        // Padding 3 digit, misalnya 001, 002
        $kodeBaru = $prefix . str_pad($noUrutBaru, 3, '0', STR_PAD_LEFT);

        return $kodeBaru;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
