<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY total_price DECIMAL(15,2) NOT NULL");
        DB::statement("ALTER TABLE transactions_details MODIFY unit_price DECIMAL(15,2) NOT NULL");
        DB::statement("ALTER TABLE transactions_details MODIFY subtotal DECIMAL(15,2) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY total_price DECIMAL(8,2) NOT NULL");
        DB::statement("ALTER TABLE transactions_details MODIFY unit_price DECIMAL(8,2) NOT NULL");
        DB::statement("ALTER TABLE transactions_details MODIFY subtotal DECIMAL(8,2) NOT NULL");
    }
};
