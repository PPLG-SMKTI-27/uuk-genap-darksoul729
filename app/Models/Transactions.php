<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transactions extends Model
{
    protected $fillable = [
        "transaction_no",
        "date",
        "customer_name",
        "total_price",
        "status",
    ];

    public function transactions_detail()
    {
        return $this->hasMany(Transactions_details::class, "transaction_id");
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $latestModel = static::latest("id")->first();
            $nextNumber = $latestModel ? $latestModel->id + 1 : 1;

            // Format to INV-00001
            $model->transaction_no = "TRX-" . Str::padLeft($nextNumber, 5, "0");
        });
    }
}
