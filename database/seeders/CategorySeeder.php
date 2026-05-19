<?php

namespace Database\Seeders;

use App\Models\Categories;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                "category_name" => "Makanan",
                "description" => "Produk makanan ringan dan kebutuhan harian.",
            ],
            [
                "category_name" => "Minuman",
                "description" => "Aneka minuman kemasan dingin dan hangat.",
            ],
            [
                "category_name" => "Kebersihan",
                "description" => "Produk kebersihan rumah tangga dan pribadi.",
            ],
            [
                "category_name" => "Alat Tulis",
                "description" => "Perlengkapan alat tulis kantor dan sekolah.",
            ],
        ];

        Categories::query()->insert($categories);
    }
}
