<?php

namespace Tests\Feature;

use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Schemas\OrderInfolist;
use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderInfolistTest extends TestCase
{
    #[DataProvider('addresses')]
    public function test_order_addresses_render_without_errors(mixed $address, string $expected): void
    {
        $order = new Order;
        $order->shipping_address = $address;
        $order->billing_address = $address;
        $schema = OrderInfolist::configure(Schema::make(new ViewOrder)->record($order));
        $entries = collect($schema->getFlatComponents())
            ->filter(fn ($component): bool => $component instanceof TextEntry && in_array($component->getName(), ['shipping_address', 'billing_address']));

        $this->assertCount(2, $entries);

        foreach ($entries as $entry) {
            $this->assertSame($expected, (string) $entry->formatState($entry->getState()));
        }
    }

    public static function addresses(): array
    {
        return [
            'missing' => [null, ''],
            'empty' => [[], ''],
            'blank fields' => [['line1' => null, 'city' => null], ''],
            'backend address' => [['line1' => '123 Main St', 'line2' => 'Unit 2', 'city' => 'Colombo', 'state' => 'Western', 'country' => 'Sri Lanka', 'postal_code' => '00100'], '123 Main St<br />Unit 2<br />Colombo, Western, Sri Lanka<br />Postal code: 00100'],
            'checkout address' => [['first_name' => 'Jane', 'last_name' => 'Doe', 'address_line1' => '123 Main St', 'address_line2' => 'Unit 2', 'phone' => '0771234567', 'email' => 'jane@example.com'], 'Jane Doe<br />123 Main St<br />Unit 2<br />Phone: 0771234567<br />Email: jane&#64;example.com'],
            'escaped html' => [['line1' => '<script>alert(1)</script>'], '&lt;script&gt;alert(1)&lt;/script&gt;'],
            'legacy string' => ['123 Main St', '123 Main St'],
            'invalid value' => [123, ''],
        ];
    }
}
