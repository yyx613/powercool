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
}
