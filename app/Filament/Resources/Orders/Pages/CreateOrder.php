<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /** @var array<int, array<string, mixed>> */
    protected array $pendingItems = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order_number'] ??= $this->generateOrderNumber();
        $data['currency'] ??= 'LKR';
        $data['subtotal'] ??= 0;
        $data['tax_total'] ??= 0;
        $data['discount_total'] ??= 0;
        $data['shipping_total'] ??= 0;
        $data['grand_total'] ??= 0;
        $data['billing_address'] ??= $data['shipping_address'] ?? [];
        $data['customer_name'] ??= null;
        $data['customer_email'] ??= null;
        $data['customer_phone'] ??= null;
        $data['notes'] ??= null;

        // Calculate subtotal and stash items for afterCreate()
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

        // Remove items from data — they are not columns on the orders table
        unset($data['items']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->pendingItems)) {
            $this->record->items()->createMany($this->pendingItems);
        }
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
    }
}
