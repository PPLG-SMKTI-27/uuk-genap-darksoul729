<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions_details extends Model
{
    protected $table = "transactions_details";

    protected $fillable = [
        "transaction_id",
        "product_id",
        "quantity",
        "unit_price",
        "subtotal",
    ];

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, "transaction_id");
    }

    public function product()
    {
        return $this->belongsTo(Products::class, "product_id");
    }
}
