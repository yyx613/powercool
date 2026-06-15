<?php

namespace Tests\Unit;

use App\Models\DeliveryOrder;
use App\Models\TransportAcknowledgement;
use PHPUnit\Framework\TestCase;

class TransportAcknowledgementTypeNameTest extends TestCase
{
    private function ack(array $attributes): TransportAcknowledgement
    {
        $ack = new TransportAcknowledgement;
        $ack->forceFill($attributes);

        return $ack;
    }

    public function test_delivery_type_returns_delivery(): void
    {
        $ack = $this->ack(['type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY]);

        $this->assertSame('Delivery', $ack->typeName());
    }

    public function test_collection_type_returns_collection(): void
    {
        $ack = $this->ack(['type' => DeliveryOrder::TRANSPORT_ACK_TYPE_COLLECTION]);

        $this->assertSame('Collection', $ack->typeName());
    }

    public function test_falls_back_to_delivery_from_sku_prefix_when_type_missing(): void
    {
        $ack = $this->ack(['type' => null, 'sku' => 'DL001']);

        $this->assertSame('Delivery', $ack->typeName());
    }

    public function test_falls_back_to_collection_from_sku_prefix_when_type_missing(): void
    {
        $ack = $this->ack(['type' => null, 'sku' => 'CL001']);

        $this->assertSame('Collection', $ack->typeName());
    }

    public function test_returns_null_when_type_and_sku_prefix_unknown(): void
    {
        $ack = $this->ack(['type' => null, 'sku' => 'XX001']);

        $this->assertNull($ack->typeName());
    }

    public function test_dealer_label_powercool_special_id(): void
    {
        $this->assertSame('Powercool', TransportAcknowledgement::dealerLabel('-1', null, null));
    }

    public function test_dealer_label_hi_ten_special_id(): void
    {
        $this->assertSame('Hi-Ten', TransportAcknowledgement::dealerLabel('-2', null, null));
    }

    public function test_dealer_label_real_dealer_in_hi_ten_group(): void
    {
        $this->assertSame('Ahmad (Hi-Ten)', TransportAcknowledgement::dealerLabel(5, 'Ahmad', 2));
    }

    public function test_dealer_label_real_dealer_in_powercool_group(): void
    {
        $this->assertSame('Ahmad (Powercool)', TransportAcknowledgement::dealerLabel(5, 'Ahmad', 1));
    }

    public function test_dealer_label_real_dealer_defaults_to_powercool_when_group_missing(): void
    {
        $this->assertSame('Ahmad (Powercool)', TransportAcknowledgement::dealerLabel(5, 'Ahmad', null));
    }
}
