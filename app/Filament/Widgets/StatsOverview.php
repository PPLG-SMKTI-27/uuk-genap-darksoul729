<?php

namespace App\Filament\Widgets;

use App\Models\Categories;
use App\Models\Products;
use App\Models\Transactions;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCategories = Categories::query()->count();
        $totalProducts = Products::query()->count();
        $totalTransactions = Transactions::query()->count();
        $totalRevenue = Transactions::query()
            ->where("status", "complete")
            ->sum("total_price");

        return [
            Stat::make("Total Kategori", (string) $totalCategories)
                ->description("Jumlah kategori produk")
                ->color("primary"),
            Stat::make("Total Produk", (string) $totalProducts)
                ->description("Produk terdaftar")
                ->color("info"),
            Stat::make("Total Transaksi", (string) $totalTransactions)
                ->description("Semua transaksi")
                ->color("warning"),
            Stat::make("Total Penjualan", "Rp " . number_format((float) $totalRevenue, 0, ",", "."))
                ->description("Transaksi complete")
                ->color("success"),
        ];
    }
}
