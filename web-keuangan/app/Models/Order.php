<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'date',
        'payment',
    ];

    // public function employee()
    // {
    //     return $this->belongsTo(Employee::class, 'employees_id');
    // }

    public function orderItem() {
        return $this->hasMany(OrderItem::class, 'orders_id');
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            // Jika 'code' kosong, set dengan kode otomatis
            if (empty($order->code)) {
                $order->code = self::getKodeOrder();
            }
        });
    }

    public static function getKodeOrder()
    {
        $tanggal = date('Ymd');

        $prefix = 'TRX-IN-' . $tanggal . '-';

        $kodeOrder = DB::table('purchases')
            ->where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code'); // ambil kode terakhir

        if ($kodeOrder) {
            // Pisahkan dan ambil bagian terakhir (nomor urut)
            $parts = explode('-', $kodeOrder);
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
