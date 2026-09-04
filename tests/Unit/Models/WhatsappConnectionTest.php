<?php

namespace Tests\Unit\Models;

use App\Models\WhatsappConnection;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class WhatsappConnectionTest extends TestCase
{
    public function test_delays_delivery_until_quiet_hours_end_when_period_crosses_midnight(): void
    {
        $connection = new WhatsappConnection([
            'quiet_hours_enabled' => true,
            'timezone' => 'Asia/Jakarta',
            'quiet_hours_start' => '21:00',
            'quiet_hours_end' => '07:00',
        ]);
        $now = CarbonImmutable::parse('2026-08-30 15:30:00', 'UTC');

        $deliveryAt = $connection->nextDeliveryAt($now);

        $this->assertSame('2026-08-31 00:00:00', $deliveryAt->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $deliveryAt->timezoneName);
    }

    public function test_keeps_immediate_delivery_outside_quiet_hours(): void
    {
        $connection = new WhatsappConnection([
            'quiet_hours_enabled' => true,
            'timezone' => 'Asia/Jakarta',
            'quiet_hours_start' => '21:00',
            'quiet_hours_end' => '07:00',
        ]);
        $now = CarbonImmutable::parse('2026-08-30 05:00:00', 'UTC');

        $deliveryAt = $connection->nextDeliveryAt($now);

        $this->assertSame($now->toIso8601String(), $deliveryAt->toIso8601String());
    }
}
