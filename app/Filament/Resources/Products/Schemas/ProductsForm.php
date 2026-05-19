<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

class ProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make("category_id")
                ->label("Kategori")
                ->relationship("categories", "category_name")
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make("product_name")->required(),
            TextInput::make("price")->required()->numeric()->prefix("Rp"),
            TextInput::make("stock")->required()->numeric(),
            TextInput::make("unit")->required(),
            FileUpload::make("gambar")
                ->label("Gambar Produk")
                ->image()
                ->disk("public")
                ->directory("product")
                ->imageEditor()
                ->nullable(),
        ]);
    }
}
