<?php

namespace Tests\Feature\Api;

use App\Models\ClientNotification;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_change_creates_notification_for_client(): void
    {
        $order = Order::factory()->pending()->create();

        $order->update(['status' => Order::STATUS_ASSIGNED]);

        $this->assertDatabaseHas('client_notifications', [
            'user_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'order_status',
            'title' => 'Водитель назначен',
        ]);
    }

    public function test_searching_status_change_creates_no_notification(): void
    {
        $order = Order::factory()->pending()->create();

        $order->update(['status' => Order::STATUS_SEARCHING]);

        $this->assertDatabaseCount('client_notifications', 0);
    }

    public function test_admin_support_reply_creates_notification(): void
    {
        $client = User::factory()->create();

        Message::create([
            'user_id' => $client->id,
            'message' => 'Здравствуйте, ваш вопрос решён',
            'type' => 'admin',
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('client_notifications', [
            'user_id' => $client->id,
            'type' => 'support',
            'title' => 'Ответ поддержки',
        ]);
    }

    public function test_client_own_message_creates_no_notification(): void
    {
        $client = User::factory()->create();

        Message::create([
            'user_id' => $client->id,
            'message' => 'Помогите, пожалуйста',
            'type' => 'user',
            'is_active' => 0,
        ]);

        $this->assertDatabaseCount('client_notifications', 0);
    }

    public function test_notifications_api_list_unread_count_and_mark_read(): void
    {
        $client = User::factory()->create();
        $other = User::factory()->create();

        ClientNotification::create([
            'user_id' => $client->id, 'type' => 'order_status',
            'title' => 'Водитель назначен', 'body' => 'Заказ #1',
        ]);
        ClientNotification::create([
            'user_id' => $client->id, 'type' => 'support',
            'title' => 'Ответ поддержки', 'body' => 'Здравствуйте',
        ]);
        ClientNotification::create([
            'user_id' => $other->id, 'type' => 'support',
            'title' => 'Ответ поддержки', 'body' => 'Не ваше',
        ]);

        $headers = ['Authorization' => 'Bearer '.auth('api')->login($client)];

        $this->withHeaders($headers)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('unread_count', 2);

        $this->withHeaders($headers)
            ->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 2);

        $this->withHeaders($headers)
            ->postJson('/api/v1/notifications/read', [])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, ClientNotification::forUser($client->id)->unread()->count());
        $this->assertSame(1, ClientNotification::forUser($other->id)->unread()->count());
    }

    public function test_notifications_api_requires_auth(): void
    {
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
    }
}
