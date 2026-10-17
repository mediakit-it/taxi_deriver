<?php

namespace App\Listeners;

use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Auth\Events\Registered;

class ExportUsersAfterRegistration
{
    public function handle(Registered $event)
    {
        $filePath = storage_path('app/public/users_export.xlsx');

        Excel::store(new UsersExport, 'public/users_export.xlsx');
    }
}

