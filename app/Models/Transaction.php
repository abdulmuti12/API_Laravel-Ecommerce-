<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','trx_order_code','total_transaction','payment_method','status','admin_id'];

    public function user(){
        return $this->belongsTo(Customer::class);
    }
    public function orders() // Ubah dari 'order' menjadi 'orders' (plural)
    {
        return $this->hasMany(Order::class, 'trx_order_code', 'trx_order_code');
    }

}
