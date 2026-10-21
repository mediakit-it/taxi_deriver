<?php

namespace App\View\Composers;

use App\Models\CostOfTrip;
use App\Models\PolicyDocument;
use App\Models\Setting;
use Illuminate\View\View;

class LandingComposer
{
    /**
     * Демо-значения лендинга: пока коробка не настроена в админке,
     * главная страница показывает их вместо пустых мест.
     */
    private const FALLBACKS = [
        'company_name' => 'СитиТакси',
        'city' => 'Ваш город',
        'email' => 'zakaz@example.com',
        'currency_label' => 'руб.',
    ];

    /**
     * Презентация известных тарифов из таблицы cost_of_trip:
     * заголовок карточки, подпись, признак цены за километр,
     * выделение карточки и пункты списка на лендинге.
     */
    private const TARIFF_PRESENTATION = [
        'City' => [
            'title' => 'По городу',
            'note' => 'поездка по городу',
            'per_km' => false,
            'featured' => false,
            'features' => [
                'Подача за 3-5 минут',
                'Цена фиксируется при заказе',
                'Оплата картой или наличными',
                'Работаем круглосуточно',
            ],
        ],
        'Outside_the_City' => [
            'title' => 'За городом',
            'note' => 'междугородние поездки',
            'per_km' => true,
            'featured' => true,
            'features' => [
                'Тариф за километр - без скрытых доплат',
                'Поездки в соседние города и деревни',
                'Опытные водители на трассе',
                'Детское кресло по запросу',
            ],
        ],
    ];

    /**
     * Данные лендинга из раздела «Настройки»: брендинг, контакты,
     * тарифы из cost_of_trip и документы из policy_documents.
     */
    public function compose(View $view): void
    {
        $settings = Setting::allCached();

        $companyName = $this->value($settings, 'company_name', self::FALLBACKS['company_name']);
        $currency = $this->value($settings, 'currency_label', self::FALLBACKS['currency_label']);

        $phones = $this->phones($settings['phones'] ?? null);
        $callPhone = $phones[0];

        $view->with('landing', [
            'company_name' => $companyName,
            'city' => $this->value($settings, 'city', self::FALLBACKS['city']),
            'email' => $this->value($settings, 'notification_email', self::FALLBACKS['email']),
            'phones' => $phones,
            'call_phone' => $callPhone['number'],
            'call_phone_href' => $callPhone['href'],
            'addresses' => $this->addresses($settings['addresses'] ?? null),
            'socials' => array_filter([
                'telegram' => $settings['social_telegram'] ?? null,
                'viber' => $settings['social_viber'] ?? null,
                'whatsapp' => $settings['social_whatsapp'] ?? null,
                'instagram' => $settings['social_instagram'] ?? null,
            ]),
            'logo_url' => !empty($settings['logo_path'])
                ? asset('storage/' . $settings['logo_path'])
                : null,
            'favicon_url' => !empty($settings['favicon_path'])
                ? asset('storage/' . $settings['favicon_path'])
                : asset('assetsSite2/img/favicon.png'),
            'color_primary' => $this->filled($settings, 'color_primary'),
            'color_accent' => $this->filled($settings, 'color_accent'),
            'seo_title' => $this->value(
                $settings,
                'seo_title',
                $companyName . ' - заказ такси в вашем городе'
            ),
            'seo_description' => $this->value(
                $settings,
                'seo_description',
                $companyName . ' - заказ такси в вашем городе по телефону ' . $callPhone['number']
                    . '. Поездки по городу и за город, трансферы, корпоративные поездки.'
                    . ' Заказ по телефону и в приложении.'
            ),
            'seo_og_url' => !empty($settings['seo_og_image_path'])
                ? asset('storage/' . $settings['seo_og_image_path'])
                : null,
        ]);

        $view->with('tariffCards', $this->tariffCards($currency));
        $view->with('policyDocuments', PolicyDocument::ordered()->get());
    }

    /**
     * Карточки тарифов из cost_of_trip; пустой массив -
     * лендинг покажет демо-блок.
     *
     * @return array<int, array<string, mixed>>
     */
    private function tariffCards(string $currency): array
    {
        $cards = [];

        foreach (CostOfTrip::query()->orderBy('id')->get() as $tariff) {
            $presentation = self::TARIFF_PRESENTATION[$tariff->name] ?? [
                'title' => $tariff->name,
                'note' => '',
                'per_km' => false,
                'featured' => false,
                'features' => [],
            ];

            $cards[] = [
                'title' => $presentation['title'],
                'price' => $tariff->price,
                'unit' => $presentation['per_km'] ? $currency . '/км' : $currency,
                'note' => $presentation['note'],
                'featured' => $presentation['featured'],
                'features' => $presentation['features'],
            ];
        }

        return $cards;
    }

    /**
     * Телефоны из настройки phones (JSON) с готовыми href для tel:.
     * Если не заданы - демо-номер коробки.
     *
     * @return array<int, array{number: string, label: string, href: string}>
     */
    private function phones(?string $value): array
    {
        $phones = [];

        foreach ($this->jsonList($value) as $row) {
            $number = trim((string) ($row['number'] ?? ''));

            if ($number === '') {
                continue;
            }

            $phones[] = [
                'number' => $number,
                'label' => trim((string) ($row['label'] ?? '')),
                'href' => preg_replace('/[^0-9+]/', '', $number),
            ];
        }

        if ($phones === []) {
            $phones[] = ['number' => '7777', 'label' => 'Короткий номер', 'href' => '7777'];
        }

        return $phones;
    }

    /**
     * Адреса из настройки addresses (JSON).
     *
     * @return array<int, array{address: string, label: string}>
     */
    private function addresses(?string $value): array
    {
        $addresses = [];

        foreach ($this->jsonList($value) as $row) {
            $address = trim((string) ($row['address'] ?? ''));

            if ($address === '') {
                continue;
            }

            $addresses[] = [
                'address' => $address,
                'label' => trim((string) ($row['label'] ?? '')),
            ];
        }

        return $addresses;
    }

    /**
     * Непустое строковое значение настройки или null.
     */
    private function filled(array $settings, string $key): ?string
    {
        $value = $settings[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * Значение настройки с демо-запасным вариантом.
     */
    private function value(array $settings, string $key, string $fallback): string
    {
        return $this->filled($settings, $key) ?? $fallback;
    }

    /**
     * Настройки-массивы хранятся JSON-строками; вернуть как массив.
     */
    private function jsonList(?string $value): array
    {
        $decoded = $value ? json_decode($value, true) : null;

        return is_array($decoded) ? $decoded : [];
    }
}
