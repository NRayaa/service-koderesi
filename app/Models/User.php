<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'key',
        'total_tokens'
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

    public function CreditIn() : HasMany
    {
        return $this->hasMany(CreditIn::class, 'user_id', 'id');
    }

    public function CreditOut() : HasMany
    {
        return $this->hasMany(CreditOut::class, 'user_id', 'id');
    }

    public function Transaction() : HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }

    public function profile(): HasOne{
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }
}
