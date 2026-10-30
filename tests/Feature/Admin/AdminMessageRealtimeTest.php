<?php

namespace Tests\Feature\Admin;

use App\Events\MessageAdminSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminMessageRealtimeTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = auth('api')->login($user);

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_user_message_dispatches_admin_event_with_sender(): void
    {
        Event::fake([MessageAdminSent::class]);

        $driver = User::factory()->create([
            'first_name' => 'Иван',
            'last_name' => 'Петров',
            'role' => 'driving',
        ]);

        $response = $this->postJson('/api/addNewMessage', [
            'message' => 'Не могу принять заказ, приложение висит',
        ], $this->authHeaders($driver));

        $response->assertOk()->assertJson(['success' => 'true']);

        Event::assertDispatched(
            MessageAdminSent::class,
            function (MessageAdminSent $event) use ($driver) {
                $channels = $event->broadcastOn();
                $channel = is_array($channels) ? $channels[0] : $channels;
                $payload = $event->broadcastWith();

                return $channel->name === 'admin'
                    && $event->broadcastAs() === 'sendMailAdmin'
                    && $payload['user']['id'] === $driver->id
                    && $payload['user']['name'] === 'Иван Петров'
                    && $payload['message']->message === 'Не могу принять заказ, приложение висит';
            }
        );
    }

    public function test_admin_reply_does_not_dispatch_admin_event(): void
    {
        Event::fake([MessageAdminSent::class]);

        $admin = User::factory()->admin()->create();
        $driver = User::factory()->create(['role' => 'driving']);

        $response = $this->actingAs($admin, 'web')->postJson('/admin/addNewMessageUser', [
            'userId' => $driver->id,
            'message' => 'Перезапустите приложение, пожалуйста',
        ]);

        $response->assertOk()->assertJson(['status' => 'true']);

        Event::assertNotDispatched(MessageAdminSent::class);
    }

    public function test_unread_count_endpoint_counts_only_unread_user_messages(): void
    {
        $admin = User::factory()->admin()->create();
        $driver = User::factory()->create(['role' => 'driving']);
        $other = User::factory()->create(['role' => 'driving']);

        Message::create(['user_id' => $driver->id, 'type' => 'user', 'message' => 'Первое', 'is_active' => 0]);
        Message::create(['user_id' => $driver->id, 'type' => 'user', 'message' => 'Второе', 'is_active' => 0]);
        Message::create(['user_id' => $other->id, 'type' => 'user', 'message' => 'Уже прочитано', 'is_active' => 1]);
        Message::create(['user_id' => $driver->id, 'type' => 'admin', 'message' => 'Ответ админа', 'is_active' => 0]);

        $this->actingAs($admin, 'web')
            ->getJson('/admin/data/unread-messages')
            ->assertOk()
            ->assertJsonPath('count', 2);

        // Открытие диалога помечает сообщения этого водителя прочитанными.
        $this->actingAs($admin, 'web')
            ->postJson('/admin/getMessagesUser', ['userId' => $driver->id])
            ->assertOk();

        $this->actingAs($admin, 'web')
            ->getJson('/admin/data/unread-messages')
            ->assertOk()
            ->assertJsonPath('count', 0);
    }
}
