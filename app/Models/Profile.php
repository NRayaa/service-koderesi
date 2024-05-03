<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'username',
        'phone_number',
        'image',
        'user_id'
    ];

    public function waybill(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
