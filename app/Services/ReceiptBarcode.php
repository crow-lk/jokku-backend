<?php

namespace App\Services;

use InvalidArgumentException;

class ReceiptBarcode
{
    private const CHARACTERS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';

    private const PATTERNS = [
        0x034, 0x121, 0x061, 0x160, 0x031, 0x130, 0x070, 0x025, 0x124, 0x064,
        0x109, 0x049, 0x148, 0x019, 0x118, 0x058, 0x00D, 0x10C, 0x04C, 0x01C,
        0x103, 0x043, 0x142, 0x013, 0x112, 0x052, 0x007, 0x106, 0x046, 0x016,
        0x181, 0x0C1, 0x1C0, 0x091, 0x190, 0x0D0, 0x085, 0x184, 0x0C4, 0x0A8,
        0x0A2, 0x08A, 0x02A,
    ];

    /**
     * @return array{width: int, bars: list<array{x: int, width: int}>}
     */
    public function encode(string $value): array
    {
        if ($value === '') {
            throw new InvalidArgumentException('The barcode value must not be empty.');
        }

        $patterns = [0x094];

        foreach (str_split($value) as $character) {
            $index = strpos(self::CHARACTERS, $character);

            if ($index === false) {
                throw new InvalidArgumentException('Unsupported Code 39 character in the order number.');
            }

            $patterns[] = self::PATTERNS[$index];
        }

        $patterns[] = 0x094;
        $bars = [];
        $position = 10;

        foreach ($patterns as $pattern) {
            for ($element = 0; $element < 9; $element++) {
                $width = ($pattern & (1 << (8 - $element))) !== 0 ? 2 : 1;

                if ($element % 2 === 0) {
                    $bars[] = ['x' => $position, 'width' => $width];
                }

                $position += $width;
            }

            $position++;
        }

        return ['width' => $position + 9, 'bars' => $bars];
    }
}
