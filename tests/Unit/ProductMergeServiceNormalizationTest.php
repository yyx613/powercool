<?php

namespace Tests\Unit;

use App\Services\ProductMergeService;
use PHPUnit\Framework\TestCase;

class ProductMergeServiceNormalizationTest extends TestCase
{
    public function test_passes_through_plain_sku(): void
    {
        $this->assertSame('ACC-K325CZ1', ProductMergeService::normalizeFromPhotoFilename('ACC-K325CZ1'));
    }

    public function test_reverses_bang_to_slash(): void
    {
        // Filename `A.VALVE 1!4` represents SKU `A.VALVE 1/4`.
        $this->assertSame('A.VALVE 1/4', ProductMergeService::normalizeFromPhotoFilename('A.VALVE 1!4'));
    }

    public function test_reverses_at_to_asterisk(): void
    {
        // Filename `COIL@BTM` represents SKU `COIL*BTM`.
        $this->assertSame('COIL*BTM', ProductMergeService::normalizeFromPhotoFilename('COIL@BTM'));
    }

    public function test_reverses_apostrophe_to_double_quote(): void
    {
        // Filename `SCR-5'` represents SKU `SCR-5"`.
        $this->assertSame('SCR-5"', ProductMergeService::normalizeFromPhotoFilename("SCR-5'"));
    }

    public function test_reverses_multiple_symbols_in_one_name(): void
    {
        $this->assertSame('A/B*C"', ProductMergeService::normalizeFromPhotoFilename('A!B@C\''));
    }

    public function test_trims_whitespace(): void
    {
        $this->assertSame('SKU', ProductMergeService::normalizeFromPhotoFilename('  SKU  '));
    }

    public function test_leaves_unknown_special_chars_intact(): void
    {
        $this->assertSame('COIL-100-3-900', ProductMergeService::normalizeFromPhotoFilename('COIL-100-3-900'));
    }
}
