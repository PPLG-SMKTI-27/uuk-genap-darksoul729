<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionsResource;
use App\Models\Products;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateTransactions extends CreateRecord
{
    protected static string $resource = TransactionsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["total_price"] = collect(
            $this->data["transactions_detail"] ?? [],
        )->sum(fn(array $item): float => (float) ($item["subtotal"] ?? 0));

        return $data;
    }

    protected function beforeCreate(): void
    {
        if (($this->data["status"] ?? "pending") !== "complete") {
            return;
        }

        $required = collect($this->data["transactions_detail"] ?? [])
            ->groupBy("product_id")
            ->map(fn($items) => (int) collect($items)->sum("quantity"))
            ->filter(fn($qty, $productId) => filled($productId) && $qty > 0);

        foreach ($required as $productId => $qty) {
            $product = Products::query()->find($productId);

            if (!$product || $product->stock < $qty) {
                throw ValidationException::withMessages([
                    "data.transactions_detail" =>
                        "Stok produk tidak cukup untuk jumlah yang diminta.",
                ]);
            }
        }
    }

    protected function afterCreate(): void
    {
        if ($this->record->status !== "complete") {
            return;
        }

        $used = $this->record
            ->transactions_detail()
            ->get()
            ->groupBy("product_id")
            ->map(fn($items) => (int) $items->sum("quantity"));

        foreach ($used as $productId => $qty) {
            Products::query()->whereKey($productId)->decrement("stock", $qty);
        }
    }
}
