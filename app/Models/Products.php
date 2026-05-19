<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $fillable = [
        "category_id",
        "product_name",
        "price",
        "stock",
        "unit",
        "gambar",
    ];

    public function categories()
    {
        return $this->belongsTo(Categories::class, "category_id");
    }

    public function transactions_detail()
    {
        return $this->hasMany(Transactions_details::class, "product_id");
    }
}
