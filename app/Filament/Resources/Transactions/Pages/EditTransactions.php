<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionsResource;
use App\Models\Products;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditTransactions extends EditRecord
{
    protected static string $resource = TransactionsResource::class;

    protected array $previousQuantities = [];

    protected string $previousStatus = "pending";

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data["total_price"] = collect($this->data["transactions_detail"] ?? [])->sum(
            fn (array $item): float => (float) ($item["subtotal"] ?? 0),
        );

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->previousStatus = (string) $this->record->status;
        $this->previousQuantities = $this->getRecordQuantities();

        $deltas = $this->calculateStockDeltas(
            $this->previousStatus,
            $this->previousQuantities,
            (string) ($this->data["status"] ?? "pending"),
            $this->getFormQuantities(),
        );

        foreach ($deltas as $productId => $delta) {
            if ($delta <= 0) {
                continue;
            }

            $product = Products::query()->find($productId);

            if (!$product || $product->stock < $delta) {
                throw ValidationException::withMessages([
                    "data.transactions_detail" => "Stok produk tidak cukup untuk perubahan transaksi ini.",
                ]);
            }
        }
    }

    protected function afterSave(): void
    {
        $newStatus = (string) $this->record->status;
        $newQuantities = $this->getRecordQuantities();

        $deltas = $this->calculateStockDeltas(
            $this->previousStatus,
            $this->previousQuantities,
            $newStatus,
            $newQuantities,
        );

        foreach ($deltas as $productId => $delta) {
            if ($delta > 0) {
                Products::query()->whereKey($productId)->decrement("stock", $delta);
            } elseif ($delta < 0) {
                Products::query()->whereKey($productId)->increment("stock", abs($delta));
            }
        }
    }

    protected function getRecordQuantities(): array
    {
        return $this->record
            ->transactions_detail()
            ->get()
            ->groupBy("product_id")
            ->map(fn ($items) => (int) $items->sum("quantity"))
            ->toArray();
    }

    protected function getFormQuantities(): array
    {
        return collect($this->data["transactions_detail"] ?? [])
            ->filter(fn ($item) => filled($item["product_id"] ?? null))
            ->groupBy("product_id")
            ->map(fn ($items) => (int) collect($items)->sum("quantity"))
            ->toArray();
    }

    protected function calculateStockDeltas(
        string $oldStatus,
        array $oldQuantities,
        string $newStatus,
        array $newQuantities,
    ): array {
        $oldApplied = $oldStatus === "complete" ? $oldQuantities : [];
        $newApplied = $newStatus === "complete" ? $newQuantities : [];
        $productIds = array_unique(array_merge(array_keys($oldApplied), array_keys($newApplied)));
        $deltas = [];

        foreach ($productIds as $productId) {
            $oldQty = (int) ($oldApplied[$productId] ?? 0);
            $newQty = (int) ($newApplied[$productId] ?? 0);
            $deltas[$productId] = $newQty - $oldQty;
        }

        return $deltas;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
