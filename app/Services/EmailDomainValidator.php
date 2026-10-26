<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;

/**
 * Проверка адреса почты до отправки кода подтверждения: формат,
 * принимает ли домен почту (MX, а если MX нет - A/AAAA самого домена)
 * и подсказка при опечатке в популярном домене.
 */
class EmailDomainValidator
{
    /**
     * Очевидные опечатки в доменах: набранное => правильное.
     *
     * @var array<string, string>
     */
    private const TYPO_DOMAINS = [
        'gamil.com' => 'gmail.com',
        'gmal.com' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gmail.co' => 'gmail.com',
        'gmail.ru' => 'gmail.com',
        'hotmial.com' => 'hotmail.com',
        'hotmal.com' => 'hotmail.com',
        'outlok.com' => 'outlook.com',
        'yaho.com' => 'yahoo.com',
        'yahoo.co' => 'yahoo.com',
        'mail.ry' => 'mail.ru',
        'mal.ru' => 'mail.ru',
        'mial.ru' => 'mail.ru',
        'yandex.ry' => 'yandex.ru',
        'yndex.ru' => 'yandex.ru',
        'iclod.com' => 'icloud.com',
        'icloud.co' => 'icloud.com',
    ];

    /**
     * Популярные домены: опечатка ищется также на расстоянии
     * редактирования 1 от любого из них.
     *
     * @var string[]
     */
    private const POPULAR_DOMAINS = [
        'gmail.com',
        'hotmail.com',
        'outlook.com',
        'yahoo.com',
        'icloud.com',
        'mail.ru',
        'yandex.ru',
        'ya.ru',
        'bk.ru',
        'inbox.ru',
        'list.ru',
        'rambler.ru',
        'protonmail.com',
        'live.com',
        'gmx.com',
    ];

    public function hasValidFormat(string $email): bool
    {
        return ! Validator::make(
            ['email' => $email],
            ['email' => ['required', 'string', 'email', 'max:255']],
        )->fails();
    }

    /**
     * Адрес синтаксически валиден и его домен принимает почту.
     */
    public function isDeliverable(string $email): bool
    {
        if (! $this->hasValidFormat($email)) {
            return false;
        }

        $domain = $this->domainOf($email);

        return $domain !== null && $this->domainHasMailServer($domain);
    }

    /**
     * Правильный домен при опечатке (голой строкой, без адреса), иначе null.
     */
    public function suggestDomain(string $email): ?string
    {
        $domain = $this->domainOf($email);

        if ($domain === null || in_array($domain, self::POPULAR_DOMAINS, true)) {
            return null;
        }

        if (isset(self::TYPO_DOMAINS[$domain])) {
            return self::TYPO_DOMAINS[$domain];
        }

        foreach (self::POPULAR_DOMAINS as $popular) {
            if (levenshtein($domain, $popular) === 1) {
                return $popular;
            }
        }

        return null;
    }

    /**
     * Единственная точка DNS-запросов во всём валидаторе.
     * В тестах подменяется фейком: подкласс с переопределённым методом
     * регистрируется через $this->app->instance(EmailDomainValidator::class, $fake).
     */
    protected function domainHasMailServer(string $domain): bool
    {
        $mx = dns_get_record($domain, DNS_MX);
        if (is_array($mx) && $mx !== []) {
            return true;
        }

        $addresses = dns_get_record($domain, DNS_A + DNS_AAAA);

        return is_array($addresses) && $addresses !== [];
    }

    private function domainOf(string $email): ?string
    {
        $at = strrpos($email, '@');
        if ($at === false) {
            return null;
        }

        $domain = strtolower(trim(substr($email, $at + 1)));

        return $domain !== '' ? $domain : null;
    }
}
