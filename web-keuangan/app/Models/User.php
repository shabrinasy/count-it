<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenancy;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'avatar_url',
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Untuk menampilkan avatar di pojok kanan atas Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ? asset('storage/' . $this->avatar_url)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name);
    }

    /**
     * Agar user ini bisa login ke panel Filament.
     */
    public function canAccessFilament(): bool
    {
        return true; // Atau bisa diatur pakai role === 'pemilik', dll.
    }

    /**
     * Required by FilamentUser contract to determine panel access.
     */
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Semua role boleh login ke panel
        return in_array($this->role, ['pemilik', 'keuangan', 'pembelian', 'penjualan']);
    }
}
