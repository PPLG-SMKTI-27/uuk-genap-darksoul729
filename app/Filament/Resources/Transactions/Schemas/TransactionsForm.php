<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Products;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Str;

class TransactionsForm
{
    protected static function calculateTotal(array|null $items): float
    {
        return collect($items ?? [])->sum(function ($item) {
            return (float) ($item["subtotal"] ?? 0);
        });
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make("transaction_no")
                    ->label("No Transaksi")
                    ->disabled()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (
                        TextInput $component,
                        string $operation,
                        $model,
                    ) {
                        if ($operation === "create") {
                            $latestModel = \App\Models\Transactions::latest(
                                "id",
                            )->first();
                            $nextNumber = $latestModel ? $latestModel->id + 1 : 1;

                            $component->state(
                                "TRX-" . Str::padLeft($nextNumber, 5, "0"),
                            );
                        }
                    }),
                DatePicker::make("date")->required()->default(now()),
                TextInput::make("customer_name")
                    ->label("Nama Customer")
                    ->required()
                    ->columnSpan(2),
                Repeater::make("transactions_detail")
                    ->label("Detail Transaksi")
                    ->relationship()
                    ->schema([
                        Select::make("product_id")
                            ->label("Produk")
                            ->relationship("product", "product_name")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set("unit_price", 0);
                                    $set("subtotal", 0);
                                    return;
                                }

                                $product = Products::query()->find($state);
                                $quantity = (int) ($get("quantity") ?: 0);
                                $unitPrice = (float) ($product?->price ?? 0);

                                $set("unit_price", $unitPrice);
                                $set("subtotal", $quantity * $unitPrice);
                            }),
                        TextInput::make("quantity")
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $quantity = (int) ($state ?: 0);
                                $unitPrice = (float) ($get("unit_price") ?: 0);
                                $set("subtotal", $quantity * $unitPrice);
                            }),
                        TextInput::make("unit_price")
                            ->label("Harga Satuan")
                            ->numeric()
                            ->prefix("Rp")
                            ->readOnly()
                            ->dehydrated(),
                        TextInput::make("subtotal")
                            ->numeric()
                            ->prefix("Rp")
                            ->readOnly()
                            ->dehydrated(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state["product_id"] ? "Item Produk" : "Item Baru")
                    ->defaultItems(1)
                    ->addActionLabel("Tambah Item")
                    ->live()
                    ->required()
                    ->columnSpan(2),
                TextInput::make("total_price")
                    ->label("Total Harga")
                    ->required()
                    ->numeric()
                    ->prefix("Rp")
                    ->default(0)
                    ->formatStateUsing(fn (Get $get) => self::calculateTotal($get("transactions_detail")))
                    ->afterStateHydrated(function (callable $set, Get $get) {
                        $set("total_price", self::calculateTotal($get("transactions_detail")));
                    })
                    ->disabled()
                    ->dehydrated(),
                Select::make("status")
                    ->options([
                        "pending" => "Pending",
                        "complete" => "Complete",
                        "failed" => "Failed",
                    ])
                    ->default("pending")
                    ->required(),
            ]);
    }
}
