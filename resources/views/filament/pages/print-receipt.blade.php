<x-filament-panels::page>
    <div class="flex justify-end print:hidden">
        <x-filament::button type="button" onclick="window.print()">Print Receipt (80mm)</x-filament::button>
    </div>

    <div id="invoice-root">
        <div id="invoice-content">
            <div class="receipt-brand">
                <img src="{{ asset('images/logo1.jpeg') }}" alt="Jokku.lk logo" width="1280" height="331">
            </div>
            <h2 class="receipt-heading">SALES RECEIPT</h2>

            <div class="receipt-business receipt-section">
                <strong>Jokku.lk</strong>
                <p>D10/25/32, Grandburg Pl, Maharagama</p>
                <p>0717156558</p>
            </div>

            <table class="receipt-details receipt-section">
                <tbody>
                    <tr><td>Order</td><td>{{ $order->order_number ?? $order->id }}</td></tr>
                    <tr><td>Reference</td><td>{{ $order->payment?->reference_number ?: '-' }}</td></tr>
                    <tr><td>Date</td><td>{{ $order->created_at?->format('d/m/Y H:i') ?: '-' }}</td></tr>
                    <tr><td>Customer</td><td>{{ $order->customer_name ?: 'Walk-In Customer' }}</td></tr>
                    <tr><td>Phone</td><td>{{ $order->customer_phone ?: 'N/A' }}</td></tr>
                </tbody>
            </table>

            <table class="receipt-items">
                <thead><tr><th>Purchased item</th><th class="receipt-quantity">Qty</th><th>Amount</th></tr></thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td>
                                <div class="receipt-item-name">{{ $sale->product_name ?? $sale->product?->name ?? 'Item' }}</div>
                                @if ($sale->variant_name)
                                    <div class="receipt-item-detail">{{ $sale->variant_name }}</div>
                                @else
                                    @if ($size = data_get($sale->meta, 'size') ?? $sale->variant?->size?->name)
                                        <div class="receipt-item-detail">Size: {{ $size }}</div>
                                    @endif
                                    @if ($color = data_get($sale->meta, 'color') ?? $sale->variant?->color_names)
                                        <div class="receipt-item-detail">Color: {{ $color }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="receipt-quantity">{{ $sale->quantity }}</td>
                            <td>LKR {{ number_format($sale->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="receipt-totals receipt-section">
                <tbody>
                    <tr><td>Subtotal</td><td>LKR {{ number_format($order->subtotal, 2) }}</td></tr>
                    <tr><td>Discount</td><td>- LKR {{ number_format($order->discount_total ?? 0, 2) }}</td></tr>
                    <tr><td>Delivery</td><td>LKR {{ number_format($order->shipping_total ?? 0, 2) }}</td></tr>
                    @if ($order->tax_total > 0)
                        <tr><td>Tax</td><td>LKR {{ number_format($order->tax_total, 2) }}</td></tr>
                    @endif
                    <tr class="receipt-total"><td>TOTAL</td><td>LKR {{ number_format($order->grand_total, 2) }}</td></tr>
                    @if ($order->payment && $order->payment->amount_paid !== null)
                        <tr><td>Amount Paid</td><td>LKR {{ number_format($order->payment->amount_paid, 2) }}</td></tr>
                        <tr><td>Balance Due</td><td>LKR {{ number_format(max(0, $order->grand_total - $order->payment->amount_paid), 2) }}</td></tr>
                    @endif
                </tbody>
            </table>

            <div class="receipt-footer receipt-section">
                @php
                    $barcodeValue = (string) ($order->order_number ?? $order->id);
                    $barcode = app(\App\Services\ReceiptBarcode::class)->encode($barcodeValue);
                @endphp
                <svg class="receipt-barcode" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{ $barcode['width'] }} 50" role="img" aria-label="Order barcode: {{ $barcodeValue }}" shape-rendering="crispEdges">
                    <rect width="{{ $barcode['width'] }}" height="50" fill="#fff" />
                    @foreach ($barcode['bars'] as $bar)
                        <rect x="{{ $bar['x'] }}" y="0" width="{{ $bar['width'] }}" height="50" fill="#000" />
                    @endforeach
                </svg>
                <p class="receipt-order-number">{{ $barcodeValue }}</p>
                <p class="receipt-thanks">THANK YOU</p>
                <p class="receipt-powered">Powered by crow.lk</p>
            </div>
        </div>
    </div>

    <style>
        #invoice-root {
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            color: #000;
        }

        #invoice-content {
            box-sizing: border-box;
            width: 100%;
            padding: 4mm;
            font: 12px/1.35 Arial, sans-serif;
            overflow-wrap: anywhere;
        }

        #invoice-content p, #invoice-content h2 { margin: 0; }
        #invoice-content .receipt-brand {
            padding: 3mm 0 4mm;
            text-align: center;
        }
        #invoice-content .receipt-brand img {
            display: block;
            width: 56mm;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            filter: grayscale(1) invert(1);
        }
        #invoice-content .receipt-heading {
            text-align: center;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 1px;
            padding-bottom: 3mm;
        }
        #invoice-content .receipt-section { border-top: 1px dashed #000; }
        #invoice-content .receipt-business { text-align: center; padding: 2mm 0; }
        #invoice-content table { width: 100%; border-collapse: collapse; margin: 0; }
        #invoice-content th, #invoice-content td { text-align: left; vertical-align: top; padding: 0.5mm 0; }
        #invoice-content td:last-child, #invoice-content th:last-child { text-align: right; }
        #invoice-content .receipt-details { border-bottom: 1px dashed #000; }
        #invoice-content .receipt-details td:first-child { width: 25%; }
        #invoice-content .receipt-details td:last-child { font-weight: 600; }
        #invoice-content .receipt-details tr:first-child td { padding-top: 2mm; }
        #invoice-content .receipt-details tr:last-child td { padding-bottom: 2mm; }
        #invoice-content .receipt-items { margin-top: 2mm; table-layout: fixed; }
        #invoice-content .receipt-items th:first-child { width: 56%; }
        #invoice-content .receipt-items .receipt-quantity { width: 10%; text-align: center; }
        #invoice-content .receipt-items th:last-child { width: 34%; }
        #invoice-content .receipt-items thead { border-top: 1px dashed #000; border-bottom: 1px dashed #000; }
        #invoice-content .receipt-items th { font-size: 10px; padding: 1mm 0; }
        #invoice-content .receipt-items td { padding: 1.5mm 0; }
        #invoice-content .receipt-items td:first-child { padding-right: 2mm; }
        #invoice-content .receipt-items td:last-child { white-space: nowrap; font-size: 10px; }
        #invoice-content .receipt-item-name { font-weight: 700; font-size: 11px; }
        #invoice-content .receipt-item-detail { font-size: 9px; line-height: 1.3; }
        #invoice-content .receipt-totals td:last-child { white-space: nowrap; }
        #invoice-content .receipt-totals tr:first-child td { padding-top: 2mm; }
        #invoice-content .receipt-total { border-top: 1px dashed #000; font-weight: 700; }
        #invoice-content .receipt-total td { padding-top: 2mm; }
        #invoice-content .receipt-footer { margin-top: 3mm; padding-top: 4mm; text-align: center; }
        #invoice-content .receipt-order-number { font-size: 11px; }
        #invoice-content .receipt-barcode { display: block; width: 100%; height: 14mm; margin: 0 auto; }
        #invoice-content .receipt-thanks { margin-top: 4mm; font-weight: 700; font-size: 14px; }
        #invoice-content .receipt-powered { margin-top: 5mm; font-size: 10px; }

        @media print {
            @page { size: auto; margin: 0; }
            html, body { width: 80mm !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }
            body * { visibility: hidden !important; }
            #invoice-root, #invoice-root * { visibility: visible !important; }
            #invoice-root { position: absolute; top: 0; left: 0; width: 80mm; margin: 0; }
            #invoice-content tr, #invoice-content .receipt-footer { break-inside: avoid; }
            .print\:hidden { display: none !important; }
        }
    </style>
</x-filament-panels::page>
