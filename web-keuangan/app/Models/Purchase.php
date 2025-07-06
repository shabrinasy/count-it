<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'date',
        'suppliers_id',
        'file',          
        'notes', 
    ];

    protected static function booted()
    {
        static::creating(function ($purchase) {
            // Jika 'code' kosong, set dengan kode otomatis
            if (empty($purchase->code)) {
                $purchase->code = self::getKode();
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class, 'purchases_id');
    }


    public static function getKode()
    {
        $tanggal = date('Ymd');

        $prefix = 'TRX-OUT-' . $tanggal . '-';

        $kode = DB::table('purchases')
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code'); // ambil kode terakhir

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

}
