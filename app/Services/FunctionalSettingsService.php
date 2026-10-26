<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

/**
 * Раздел «Функционал»: переключатели поведения коробки.
 * Редактирует только суперадмин (компания-разработчик), значения
 * хранятся в тех же settings ключами functional_*.
 *
 * В MVP переключатели сохраняются и отдаются приложениям через
 * app-settings; автоматика (авторасчёт цены, автоназначение
 * водителя) - фаза 2, здесь она НЕ включается.
 */
class FunctionalSettingsService
{
    // Режимы расчёта цены: вручную / автоматически по тарифам /
    // комбинированно (авто с ручной правкой)
    public const PRICE_MODES = ['manual', 'auto', 'combined'];

    // Режимы назначения водителя: вручную / автоматически
    // ближайшему / комбинированно (при отказе или молчании -
    // диспетчеру)
    public const ASSIGNMENT_MODES = ['manual', 'auto', 'combined'];

    // Разрешённые источники заказов (одно значение, по спецификации)
    public const ORDER_SOURCES_MODES = ['phone', 'app', 'both'];

    /**
     * Эффективные значения всех переключателей с умолчаниями.
     *
     * @return array{price_mode: string, assignment_mode: string, preorders_enabled: bool, order_sources: string, ratings_enabled: bool}
     */
    public function all(): array
    {
        return [
            'price_mode' => $this->mode('functional_price_mode', self::PRICE_MODES, 'manual'),
            'assignment_mode' => $this->mode('functional_assignment_mode', self::ASSIGNMENT_MODES, 'manual'),
            'preorders_enabled' => $this->flag('functional_preorders_enabled'),
            'order_sources' => $this->mode('functional_order_sources', self::ORDER_SOURCES_MODES, 'both'),
            'ratings_enabled' => $this->flag('functional_ratings_enabled'),
        ];
    }

    /**
     * Источники, доступные при создании заказа в админке.
     *
     * @return array<int, string>
     */
    public function allowedOrderSources(): array
    {
        return match ($this->all()['order_sources']) {
            'phone' => [Order::SOURCE_PHONE],
            'app' => [Order::SOURCE_APP],
            default => [Order::SOURCE_PHONE, Order::SOURCE_APP],
        };
    }

    /**
     * Сохранить переключатели из формы раздела «Функционал».
     */
    public function update(array $data): void
    {
        Setting::set('functional_price_mode', $data['functional_price_mode']);
        Setting::set('functional_assignment_mode', $data['functional_assignment_mode']);
        Setting::set('functional_order_sources', $data['functional_order_sources']);
        Setting::set('functional_preorders_enabled', !empty($data['functional_preorders_enabled']) ? '1' : '0');
        Setting::set('functional_ratings_enabled', !empty($data['functional_ratings_enabled']) ? '1' : '0');
    }

    private function mode(string $key, array $allowed, string $default): string
    {
        $value = (string) Setting::get($key, $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function flag(string $key): bool
    {
        return filter_var(Setting::get($key, '0'), FILTER_VALIDATE_BOOLEAN);
    }
}
