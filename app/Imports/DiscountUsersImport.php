<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

HeadingRowFormatter::default('none');
class DiscountUsersImport implements ToModel, WithHeadingRow
{
    private const COLUMN_NUMBER_DISCOUNT = 'namberdiskont';
    private const COLUMN_USER_PHONE = 'phone';
    private const COLUMN_DISCOUNT = 'discount';
    private const COLUMN_MONEY = 'maney';

    /**
     * @param array $row
     * @return void
     */
    public function model(array $row): void
    {
        // Приведение номера телефона из таблицы к единому формату
        $tablePhone = preg_replace('/\D/', '', $row[self::COLUMN_USER_PHONE]); // Удаляем все, кроме цифр

        // Поиск пользователя с аналогичным номером телефона
        $currentUser = User::query()
            ->where(DB::raw('REPLACE(phone, "+", "")'), $tablePhone)
            ->orWhere(DB::raw('REPLACE(phone, "+", "")'), ltrim($tablePhone, '0')) // Если номер без начального нуля
            ->first();

        if ($currentUser) {
            $currentUser->update([
                'discountNumber' => $row[self::COLUMN_NUMBER_DISCOUNT],
                'discountUser' => $row[self::COLUMN_DISCOUNT] !== '' ? intval($row[self::COLUMN_DISCOUNT]) : 0,
                'discountMoney' => $row[self::COLUMN_MONEY] !== '' ? intval($row[self::COLUMN_MONEY]) : 0,
            ]);

            Log::info("Обновление скидки у пользователя с номером телефона: " . $row[self::COLUMN_USER_PHONE]);
        } else {
            Log::info("Не обновили скидку. Пользователь с номером: " . $row[self::COLUMN_USER_PHONE] . " не найден.");
        }
    }



}
