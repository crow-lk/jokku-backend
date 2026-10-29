<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\IdentifierService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('orders');

        parent::tearDown();
    }

    public function test_order_number_uses_fourteen_digit_timestamp(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(15, 39, 31));

        $this->assertSame('20261029153931', app(IdentifierService::class)->makeOrderNumber());
    }

    public function test_orders_in_the_same_second_receive_distinct_numbers(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(15, 39, 31));
        $service = app(IdentifierService::class);
        Order::query()->create(['order_number' => $service->makeOrderNumber()]);
        Order::query()->create(['order_number' => $service->makeOrderNumber()]);

        $this->assertSame('20261029153933', $service->makeOrderNumber());
    }
}
