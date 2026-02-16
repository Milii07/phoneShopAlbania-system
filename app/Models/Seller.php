<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age',
        'phone_bonus_percentage', // % bonusi për telefona
        'accessory_bonus_percentage', // % bonusi për aksesorë
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function bonuses()
    {
        return $this->hasMany(SellerBonus::class);
    }
}
