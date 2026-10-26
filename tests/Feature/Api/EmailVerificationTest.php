<?php

namespace Tests\Feature\Api;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use App\Services\EmailDomainValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /**
     * DNS в тестах не настоящий: валидатор подменяется фейком, у которого
     * почту «принимают» только перечисленные домены. Так тесты герметичны
     * и не зависят от сети и реальных DNS-записей.
     *
     * @param  string[]  $deliverableDomains
     */
    private function fakeDns(array $deliverableDomains): void
    {
        $fake = new class($deliverableDomains) extends EmailDomainValidator
        {
            /** @param string[] $domains */
            public function __construct(private readonly array $domains)
            {
            }

            protected function domainHasMailServer(string $domain): bool
            {
                return in_array($domain, $this->domains, true);
            }
        };

        $this->app->instance(EmailDomainValidator::class, $fake);
    }

    /**
     * Запросить код через verify2 и достать его из «отправленного» письма.
     */
    private function sendCode(string $email): string
    {
        $this->postJson('/api/auth/verify2', ['email' => $email])->assertOk();

        $code = null;
        Mail::assertSent(VerificationCodeMail::class, function (VerificationCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->assertNotNull($code);

        return $code;
    }

    private function signupPayload(string $email): array
    {
        return [
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'email' => $email,
            'phone' => '+375 29 123-45-67',
            'password' => 'secret-password',
        ];
    }

    public function test_verify2_sends_code_but_never_returns_it(): void
    {
        $this->fakeDns(['gmail.com']);

        $response = $this->postJson('/api/auth/verify2', ['email' => 'new.client@gmail.com']);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('status_code', 'verified');

        // Код живёт только на сервере: в ответе его быть не должно.
        $this->assertArrayNotHasKey('code', $response->json());
        $this->assertTrue(Cache::has('email_verification:code:'.sha1('new.client@gmail.com')));

        Mail::assertSent(VerificationCodeMail::class);
    }

    public function test_verify2_rejects_undeliverable_domain(): void
    {
        $this->fakeDns([]);

        $response = $this->postJson('/api/auth/verify2', ['email' => 'client@no-such-domain.example']);

        $data = $response->assertStatus(422)->json();
        $this->assertSame('Похоже, такой почты не существует. Проверьте адрес.', $data['message']);
        $this->assertArrayHasKey('suggestion', $data);
        $this->assertNull($data['suggestion']);

        Mail::assertNothingSent();
    }

    public function test_verify2_suggests_correct_domain_for_typo(): void
    {
        $this->fakeDns([]);

        $response = $this->postJson('/api/auth/verify2', ['email' => 'client@gamil.com']);

        $data = $response->assertStatus(422)->json();
        $this->assertSame('Похоже, такой почты не существует. Проверьте адрес.', $data['message']);
        $this->assertSame('gmail.com', $data['suggestion']);

        Mail::assertNothingSent();
    }

    public function test_verify2_existing_user_still_returns_not_verified(): void
    {
        User::factory()->create(['email' => 'taken@gmail.com']);
        $this->fakeDns([]);

        $this->postJson('/api/auth/verify2', ['email' => 'taken@gmail.com'])
            ->assertOk()
            ->assertJsonPath('status_code', 'not_verified');

        Mail::assertNothingSent();
    }

    public function test_verify_email_accepts_correct_code(): void
    {
        $this->fakeDns(['gmail.com']);
        $code = $this->sendCode('new.client@gmail.com');

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'new.client@gmail.com',
            'code' => $code,
        ])->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_verify_email_rejects_wrong_code(): void
    {
        $this->fakeDns(['gmail.com']);
        $code = $this->sendCode('new.client@gmail.com');
        $wrong = $code === '111111' ? '222222' : '111111';

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'new.client@gmail.com',
            'code' => $wrong,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Неверный код подтверждения.');
    }

    public function test_verify_email_rejects_after_attempts_exhausted(): void
    {
        $this->fakeDns(['gmail.com']);
        $code = $this->sendCode('new.client@gmail.com');
        $wrong = $code === '111111' ? '222222' : '111111';

        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/api/v1/auth/verify-email', [
                'email' => 'new.client@gmail.com',
                'code' => $wrong,
            ])->assertStatus(422);
        }

        // Пятая неверная попытка исчерпывает лимит.
        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'new.client@gmail.com',
            'code' => $wrong,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Превышено число попыток ввода кода. Запросите новый код.');

        // После этого даже правильный код не принимается.
        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'new.client@gmail.com',
            'code' => $code,
        ])->assertStatus(422);
    }

    public function test_signup_is_blocked_until_email_is_verified(): void
    {
        $this->fakeDns(['gmail.com']);
        $code = $this->sendCode('new.client@gmail.com');

        $this->postJson('/api/auth/userSignUp', $this->signupPayload('new.client@gmail.com'))
            ->assertStatus(422);
        $this->assertDatabaseMissing('users', ['email' => 'new.client@gmail.com']);

        $this->postJson('/api/v1/auth/verify-email', [
            'email' => 'new.client@gmail.com',
            'code' => $code,
        ])->assertOk();

        $this->postJson('/api/auth/userSignUp', $this->signupPayload('new.client@gmail.com'))
            ->assertOk()
            ->assertJsonPath('status', true);
        $this->assertDatabaseHas('users', ['email' => 'new.client@gmail.com']);
    }

    public function test_signup_without_verification_flow_is_unchanged(): void
    {
        // Потоки, где код не запрашивали (verify2 не вызывался),
        // регистрируются как раньше - гейт их не касается.
        $this->postJson('/api/auth/userSignUp', $this->signupPayload('direct@gmail.com'))
            ->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('users', ['email' => 'direct@gmail.com']);
    }
}
