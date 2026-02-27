<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    /** @var array<int, array<string, mixed>> */
    protected array $pendingItems = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'line_total' => $item->line_total,
            'meta' => $item->meta ?? [],
        ])->toArray();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['items']) && is_array($data['items'])) {
            $subtotal = 0;
            $this->pendingItems = [];

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id'] ?? null);
                $variant = ProductVariant::find($item['product_variant_id'] ?? null);

                $qty = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) ($item['unit_price'] ?? $variant?->selling_price ?? 0);
                $lineTotal = $qty * $unitPrice;

                $this->pendingItems[] = [
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_name' => $product?->name ?? ($item['product_id'] ? 'Product #'.$item['product_id'] : 'Unknown Product'),
                    'variant_name' => $variant ? trim(($variant->size?->name ?? '').($variant->colors->pluck('name')->implode(', ') ? ' / '.$variant->colors->pluck('name')->implode(', ') : '')) : null,
                    'sku' => $variant?->sku ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'meta' => $item['meta'] ?? [],
                ];

                $subtotal += $lineTotal;
            }

            $data['subtotal'] = $subtotal;
            $data['grand_total'] = $subtotal + ($data['tax_total'] ?? 0) + ($data['shipping_total'] ?? 0) - ($data['discount_total'] ?? 0);
        }

        // Remove items — not a column on the orders table
        unset($data['items']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (!empty($this->pendingItems)) {
            $this->record->items()->delete();
            $this->record->items()->createMany($this->pendingItems);
        }
    }
}
