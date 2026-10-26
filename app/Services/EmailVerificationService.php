<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Жизненный цикл кода подтверждения почты при регистрации.
 *
 * Код наружу не отдаётся: на сервере хранится только его HMAC-хеш
 * в кэше (ключ - на email), срок жизни ~10 минут, не более 5 попыток
 * ввода. Успешная проверка ставит метку «email подтверждён» на
 * ~30 минут - её требует регистрация (AuthController@register),
 * если по адресу вообще запрашивали код.
 */
class EmailVerificationService
{
    public const RESULT_OK = 'ok';
    public const RESULT_INVALID = 'invalid';
    public const RESULT_EXPIRED = 'expired';
    public const RESULT_EXHAUSTED = 'exhausted';

    private const CODE_TTL_SECONDS = 600;      // 10 минут на ввод кода
    private const VERIFIED_TTL_SECONDS = 1800; // 30 минут от проверки до регистрации
    private const FLOW_TTL_SECONDS = 1800;     // 30 минут живёт метка «проверка начата»
    private const MAX_ATTEMPTS = 5;

    /**
     * Сгенерировать код, сохранить его хеш и отправить письмо
     * существующим путём (VerificationCodeMail).
     */
    public function sendCode(string $email): void
    {
        $email = $this->normalize($email);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->codeKey($email), [
            'hash' => $this->hash($code),
            'attempts' => 0,
            'expires_at' => now()->addSeconds(self::CODE_TTL_SECONDS)->timestamp,
        ], self::CODE_TTL_SECONDS);

        Cache::put($this->flowKey($email), true, self::FLOW_TTL_SECONDS);

        Mail::to($email)->send(new VerificationCodeMail($code));
    }

    /**
     * Проверить введённый код. Возвращает одну из констант RESULT_*.
     */
    public function verify(string $email, string $code): string
    {
        $email = $this->normalize($email);
        $entry = Cache::get($this->codeKey($email));

        if (! is_array($entry) || now()->timestamp > $entry['expires_at']) {
            return self::RESULT_EXPIRED;
        }

        if (! hash_equals($entry['hash'], $this->hash($code))) {
            $entry['attempts']++;

            if ($entry['attempts'] >= self::MAX_ATTEMPTS) {
                Cache::forget($this->codeKey($email));

                return self::RESULT_EXHAUSTED;
            }

            Cache::put(
                $this->codeKey($email),
                $entry,
                max(1, $entry['expires_at'] - now()->timestamp),
            );

            return self::RESULT_INVALID;
        }

        Cache::forget($this->codeKey($email));
        Cache::put($this->verifiedKey($email), true, self::VERIFIED_TTL_SECONDS);

        return self::RESULT_OK;
    }

    /**
     * По адресу запрашивали код: регистрация обязана дождаться
     * подтверждения, пока жива эта метка.
     */
    public function flowInitiated(string $email): bool
    {
        return Cache::has($this->flowKey($this->normalize($email)));
    }

    public function isVerified(string $email): bool
    {
        return Cache::has($this->verifiedKey($this->normalize($email)));
    }

    /**
     * Снять метки после успешной регистрации: подтверждение одноразовое.
     */
    public function consumeVerification(string $email): void
    {
        $email = $this->normalize($email);

        Cache::forget($this->verifiedKey($email));
        Cache::forget($this->flowKey($email));
    }

    private function hash(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function normalize(string $email): string
    {
        return strtolower(trim($email));
    }

    private function codeKey(string $email): string
    {
        return 'email_verification:code:'.sha1($email);
    }

    private function flowKey(string $email): string
    {
        return 'email_verification:flow:'.sha1($email);
    }

    private function verifiedKey(string $email): string
    {
        return 'email_verification:verified:'.sha1($email);
    }
}
