<?php

namespace Tests\Feature\Admin;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessengerDialogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_with_message_appears_in_messenger_dialogs(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'user']);
        $driver = User::factory()->create(['role' => 'driving']);

        Message::create([
            'user_id' => $client->id,
            'message' => 'Здравствуйте, где моя машина?',
            'type' => 'user',
            'is_active' => 0,
        ]);

        $response = $this->actingAs($admin, 'web')->get(route('messenger'));

        $response->assertOk();
        $ids = collect($response->viewData('users'))->pluck('id');
        $this->assertTrue($ids->contains($client->id), 'Клиент с сообщением должен быть в списке диалогов');
        $this->assertTrue($ids->contains($driver->id), 'Водитель без сообщений остаётся в адресной книге');
    }

    public function test_opening_client_dialog_marks_messages_read_and_badge_clears(): void
    {
        $admin = User::factory()->admin()->create();
        $client = User::factory()->create(['role' => 'user']);

        $message = Message::create([
            'user_id' => $client->id,
            'message' => 'тем',
            'type' => 'user',
            'is_active' => 0,
        ]);

        $this->actingAs($admin, 'web')
            ->get(route('admin.messages.unread'))
            ->assertOk()
            ->assertJson(['count' => 1]);

        $this->actingAs($admin, 'web')
            ->post('/admin/getMessagesUser', ['userId' => $client->id])
            ->assertOk();

        $this->assertSame(1, (int) $message->fresh()->is_active);

        $this->actingAs($admin, 'web')
            ->get(route('admin.messages.unread'))
            ->assertOk()
            ->assertJson(['count' => 0]);
    }
}
