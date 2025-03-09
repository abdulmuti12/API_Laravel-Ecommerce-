<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'total_price', 'status','qty','unit_price','trx_order_code'];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}

