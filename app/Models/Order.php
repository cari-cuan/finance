<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'package_id',
        'voucher_id',
        'base_price',
        'discount_amount',
        'total_amount',
        'status',
        'payment_token',
        'payment_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
