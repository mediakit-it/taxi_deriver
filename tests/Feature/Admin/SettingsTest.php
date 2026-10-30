<?php

namespace Tests\Feature\Admin;

use App\Models\AppDeletionStep;
use App\Models\PolicyDocument;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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
        $response = $this->actingAs($this->admin(), 'web')->get('/admin/settings');

        $response->assertOk();
    }

    public function test_admin_can_update_company_name(): void
    {
        $response = $this->actingAs($this->admin(), 'web')->put('/admin/settings', [
            'company_name' => 'Такси Тест',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertDatabaseHas('settings', [
            'key' => 'company_name',
            'value' => 'Такси Тест',
        ]);
        $this->assertSame('Такси Тест', Setting::get('company_name'));
    }

    public function test_car_bind_password_is_stored_hashed(): void
    {
        $response = $this->actingAs($this->admin(), 'web')->put('/admin/settings', [
            'company_name' => 'Такси Тест',
            'car_bind_password' => 'installer-secret',
        ]);

        $response->assertRedirect(route('settings.edit'));

        $stored = Setting::get('car_bind_password');
        $this->assertNotNull($stored);
        $this->assertNotSame('installer-secret', $stored);
        $this->assertTrue(Hash::check('installer-secret', $stored));
    }

    public function test_empty_car_bind_password_keeps_existing_hash(): void
    {
        $hash = Hash::make('installer-secret');
        Setting::set('car_bind_password', $hash);

        $response = $this->actingAs($this->admin(), 'web')->put('/admin/settings', [
            'company_name' => 'Такси Тест',
            'car_bind_password' => '',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertSame($hash, Setting::get('car_bind_password'));
    }

    public function test_policy_document_can_be_uploaded(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin(), 'web')->post('/admin/settings/documents', [
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

        $response = $this->actingAs($this->admin(), 'web')->post('/admin/settings/steps', [
            'text' => 'Откройте настройки приложения',
            'sort_order' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('app_deletion_steps', [
            'text' => 'Откройте настройки приложения',
        ]);

        $step = AppDeletionStep::first();
        $this->assertNotNull($step);

        $response = $this->actingAs($this->admin(), 'web')->delete('/admin/settings/steps/' . $step->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('app_deletion_steps', ['id' => $step->id]);
    }

    public function test_admin_can_update_service_area_settings(): void
    {
        $response = $this->actingAs($this->admin(), 'web')->put('/admin/settings', [
            'company_name' => 'Такси Тест',
            'service_area_lat' => '53.9',
            'service_area_lon' => '27.5667',
            'service_area_radius_km' => '30',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $this->assertSame('53.9', Setting::get('service_area_lat'));
        $this->assertSame('27.5667', Setting::get('service_area_lon'));
        $this->assertSame('30', Setting::get('service_area_radius_km'));
    }
}
