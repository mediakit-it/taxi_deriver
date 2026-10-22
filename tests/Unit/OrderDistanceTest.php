<?php

namespace Tests\Unit;

use App\Models\Order;
use PHPUnit\Framework\TestCase;

class OrderDistanceTest extends TestCase
{
    public function test_distance_is_converted_from_meters_to_kilometers(): void
    {
        $order = new Order(['distance' => 1500]);

        $this->assertSame(1.5, $order->distance_km);
    }

    public function test_duration_is_converted_from_seconds_to_whole_minutes(): void
    {
        $order = new Order(['duration' => 300]);

        $this->assertSame(5, $order->duration_min);
    }

    public function test_distance_to_client_is_converted_to_kilometers(): void
    {
        $order = new Order(['distance_to_client' => 2500]);

        $this->assertSame(2.5, $order->distance_to_client_km);
    }

    public function test_time_to_client_is_converted_to_whole_minutes(): void
    {
        $order = new Order(['time_to_client' => 120]);

        $this->assertSame(2, $order->time_to_client_min);
    }

    public function test_accessors_are_null_safe(): void
    {
        $order = new Order();

        $this->assertNull($order->distance_km);
        $this->assertNull($order->duration_min);
        $this->assertNull($order->distance_to_client_km);
        $this->assertNull($order->time_to_client_min);
    }
}
