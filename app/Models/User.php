<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Daftar Pilihan Asal UPTD (Kantor Pusat + 22 Kab/Kota di NTT)
     */
    public const LIST_UPTD = [
        'Kantor Pusat',
        'UPTD Kota Kupang',
        'UPTD Kabupaten Kupang',
        'UPTD Kabupaten Timor Tengah Selatan',
        'UPTD Kabupaten Timor Tengah Utara',
        'UPTD Kabupaten Belu',
        'UPTD Kabupaten Malaka',
        'UPTD Kabupaten Rote Ndao',
        'UPTD Kabupaten Sabu Raijua',
        'UPTD Kabupaten Alor',
        'UPTD Kabupaten Lembata',
        'UPTD Kabupaten Flores Timur',
        'UPTD Kabupaten Sikka',
        'UPTD Kabupaten Ende',
        'UPTD Kabupaten Ngada',
        'UPTD Kabupaten Nagekeo',
        'UPTD Kabupaten Manggarai',
        'UPTD Kabupaten Manggarai Timur',
        'UPTD Kabupaten Manggarai Barat',
        'UPTD Kabupaten Sumba Timur',
        'UPTD Kabupaten Sumba Barat',
        'UPTD Kabupaten Sumba Tengah',
        'UPTD Kabupaten Sumba Barat Daya',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'name',
        'uptd',
        'role',
        'role_id',
        'email',
        'password',
        'is_blocked',
        'api_token',
        'device_id',
        'device_name',
        'last_login_at',
        'last_activity_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
