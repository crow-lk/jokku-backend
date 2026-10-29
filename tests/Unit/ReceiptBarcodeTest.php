<?php

namespace Tests\Unit;

use App\Services\ReceiptBarcode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ReceiptBarcodeTest extends TestCase
{
    public function test_barcode_encodes_timestamp_with_start_stop_and_quiet_zones(): void
    {
        $barcode = (new ReceiptBarcode)->encode('20261029153931');

        $this->assertSame(227, $barcode['width']);
        $this->assertCount(80, $barcode['bars']);
        $this->assertSame(['x' => 10, 'width' => 1], $barcode['bars'][0]);
        $this->assertSame(['x' => 216, 'width' => 1], $barcode['bars'][79]);

        $decoded = '';
        for ($symbol = 1; $symbol <= 14; $symbol++) {
            $offset = $symbol * 5;
            $pattern = '';
            for ($bar = 0; $bar < 5; $bar++) {
                $current = $barcode['bars'][$offset + $bar];
                $pattern .= $current['width'];
                if ($bar < 4) {
                    $pattern .= $barcode['bars'][$offset + $bar + 1]['x'] - $current['x'] - $current['width'];
                }
            }
            $patterns = [0x034, 0x121, 0x061, 0x160, 0x031, 0x130, 0x070, 0x025, 0x124, 0x064];
            $bits = 0;
            foreach (str_split($pattern) as $width) {
                $bits = ($bits << 1) | ($width === '2' ? 1 : 0);
            }
            $decoded .= (string) array_search($bits, $patterns, true);
        }
        $this->assertSame('20261029153931', $decoded);
    }

    public function test_existing_alphanumeric_order_numbers_are_supported(): void
    {
        $this->assertCount(45, (new ReceiptBarcode)->encode('ORD-123')['bars']);
    }

    public function test_unsupported_characters_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ReceiptBarcode)->encode('<script>');
    }

    public function test_empty_values_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ReceiptBarcode)->encode('');
    }
}
