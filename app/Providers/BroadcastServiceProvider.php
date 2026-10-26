<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:api']]);

        // Админка подписывается на приватный канал диспетчерских сигналов
        // (admin.alerts) под веб-сессией: POST /admin/broadcasting/auth
        Broadcast::routes(['middleware' => ['web', 'auth:web'], 'prefix' => 'admin']);

        require base_path('routes/channels.php');
    }
}
