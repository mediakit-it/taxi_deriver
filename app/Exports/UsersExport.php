<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::select('country', 'first_name', 'name', 'otchestvo', 'phone', 'birthdate')->get();
    }

    public function headings(): array
    {
        return [
            'Страна',        // Новый столбец для страны
            'Фамилия', 
            'Имя',           
            'Отчество',    
            'Номер телефона',
            'Дата рождения'
        ];
    }
}


