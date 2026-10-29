<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class PrintReceiptTest extends TestCase
{
    public function test_receipt_uses_80mm_layout_and_jokku_contact_details(): void
    {
        $html = $this->renderReceipt();

        $this->assertStringContainsString('src="'.asset('images/logo1.jpeg').'"', $html);
        $this->assertStringContainsString('alt="Jokku.lk logo"', $html);
        $this->assertStringContainsString('filter: grayscale(1) invert(1);', $html);

        foreach (['Jokku.lk', '0717156558', 'D10/25/32, Grandburg Pl, Maharagama', 'Print Receipt (80mm)', 'width: 80mm', 'margin: 0;', 'window.print()', 'Powered by crow.lk', 'SALES RECEIPT', 'Walk-In Customer', 'N/A', 'Purchased item', '<th class="receipt-quantity">Qty</th>', 'padding: 0.5mm 0;'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        foreach (['A4', 'a4', 'aaliyaa', 'setPrint', 'insta.png', 'tiktok.png'] as $removed) {
            $this->assertStringNotContainsString($removed, $html);
        }

        $this->assertStringContainsString('size: auto;', file_get_contents(resource_path('css/filament/admin/theme.css')));
        $this->assertStringNotContainsString('size: A4;', file_get_contents(resource_path('css/filament/admin/theme.css')));
    }

    public function test_receipt_shows_item_snapshot_and_totals_without_payment_or_product(): void
    {
        $html = $this->renderReceipt();

        foreach (['<div class="receipt-item-name">Test shirt</div>', '<div class="receipt-item-detail">Shirt variant</div>', '<td class="receipt-quantity">2</td>', '2,000.00', '100.00', '250.00', '2,150.00', '20261029153931'] as $expected) {
            $this->assertStringContainsString($expected, $html);
        }

        $this->assertStringContainsString('aria-label="Order barcode: 20261029153931"', $html);
        $this->assertLessThan(strpos($html, 'THANK YOU'), strpos($html, '<svg class="receipt-barcode"'));
        $this->assertStringNotContainsString('Ref:', $html);
        $this->assertStringNotContainsString('Shirt variant x 2', $html);
        $this->assertStringNotContainsString('Unit price:', $html);
        $this->assertStringNotContainsString('<td>Tax</td>', $html);
    }

    public function test_receipt_shows_payment_reference_tax_and_escaped_product_names(): void
    {
        $html = $this->renderReceipt(true);

        $this->assertStringContainsString('PAY-123', $html);
        $this->assertStringContainsString('<td>Tax</td>', $html);
        $this->assertStringContainsString('2,200.00', $html);
        $this->assertStringContainsString('Amount Paid', $html);
        $this->assertStringContainsString('LKR 200.00', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('Size: XL', $html);
        $this->assertStringContainsString('Color: Blue / Black', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    private function renderReceipt(bool $withPayment = false): string
    {
        $order = new Order([
            'order_number' => '20261029153931',
            'subtotal' => 2000,
            'discount_total' => 100,
            'shipping_total' => 250,
            'tax_total' => $withPayment ? 50 : 0,
            'grand_total' => $withPayment ? 2200 : 2150,
        ]);
        $order->setRelation('payment', $withPayment ? new Payment(['reference_number' => 'PAY-123', 'amount_paid' => 2000]) : null);

        $item = new OrderItem([
            'product_name' => $withPayment ? '<script>' : 'Test shirt',
            'variant_name' => $withPayment ? null : 'Shirt variant',
            'meta' => ['size' => 'XL', 'color' => 'Blue / Black'],
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]);
        $item->setRelation('product', null);

        $template = file_get_contents(resource_path('views/filament/pages/print-receipt.blade.php'));
        $template = str_replace(['<x-filament-panels::page>', '</x-filament-panels::page>'], '', $template);

        return Blade::render($template, ['order' => $order, 'sales' => collect([$item])]);
    }
}
