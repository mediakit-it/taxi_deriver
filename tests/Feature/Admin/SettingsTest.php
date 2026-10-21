<?php

namespace Tests\Feature\Admin;

use App\Models\AppDeletionStep;
use App\Models\PolicyDocument;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_from_settings_page(): void
    {
        $response = $this->get('/admin/settings');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_open_settings_page(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin/settings');

        $response->assertOk();
    }

    public function test_admin_can_update_company_name(): void
    {
        $response = $this->actingAs($this->admin())->put('/admin/settings', [
            'company_name' => 'Такси Тест',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertDatabaseHas('settings', [
            'key' => 'company_name',
            'value' => 'Такси Тест',
        ]);
        $this->assertSame('Такси Тест', Setting::get('company_name'));
    }

    public function test_policy_document_can_be_uploaded(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin())->post('/admin/settings/documents', [
            'title' => 'Политика конфиденциальности',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('policy_documents', [
            'title' => 'Политика конфиденциальности',
        ]);

        $document = PolicyDocument::first();
        $this->assertNotNull($document);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_app_deletion_step_can_be_created_and_deleted(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin())->post('/admin/settings/steps', [
            'text' => 'Откройте настройки приложения',
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('app_deletion_steps', [
            'text' => 'Откройте настройки приложения',
        ]);

        $step = AppDeletionStep::first();
        $this->assertNotNull($step);

        $response = $this->actingAs($this->admin())->delete('/admin/settings/steps/' . $step->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('app_deletion_steps', ['id' => $step->id]);
    }
}
