<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CleanControlCharsTest extends TestCase
{
    public function test_strips_unit_separator_and_null_bytes(): void
    {
        $input = "Acme" . chr(0x1F) . "Sdn" . chr(0x00) . "Bhd";

        $this->assertSame('AcmeSdnBhd', cleanControlChars($input));
    }

    public function test_preserves_tab_newline_and_carriage_return(): void
    {
        $input = "Line1\tcol\nLine2\r";

        $this->assertSame("Line1\tcol\nLine2\r", cleanControlChars($input));
    }

    public function test_preserves_multibyte_utf8_characters(): void
    {
        $input = "café" . chr(0x1F) . "x";

        $this->assertSame('caféx', cleanControlChars($input));
    }

    public function test_cleans_strings_within_arrays_recursively(): void
    {
        $input = ['01' . chr(0x1F) . '23', 'clean', ['nested' . chr(0x07)]];

        $this->assertSame(['0123', 'clean', ['nested']], cleanControlChars($input));
    }

    public function test_leaves_non_string_scalars_untouched(): void
    {
        $this->assertSame(42, cleanControlChars(42));
        $this->assertNull(cleanControlChars(null));
        $this->assertTrue(cleanControlChars(true));
    }
}
