<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_income',
        'date_income',
        'name_income',
        'amount_income',
        'note_income',
        'category_id',
    ];

    protected static function booted()
    {
        static::creating(function ($income) {
            if (empty($income->code_income)) {
                $income->code_income = self::getKode();
            }
        });
    }

    public static function getKode()
    {
        $tanggal = date('Ymd');

        $prefix = 'TRX-IN-' . $tanggal . '-';

        $kode = DB::table('incomes')
            ->where('code_income', 'like', $prefix . '%')
            ->orderByDesc('code_income')
            ->value('code_income'); // ambil kode terakhir

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
