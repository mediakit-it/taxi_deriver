<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Order;
use App\Observers\MessageObserver;
use App\Observers\OrderObserver;
use App\View\Composers\LandingComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();

        View::composer('site.html.index', LandingComposer::class);

        Order::observe(OrderObserver::class);
        Message::observe(MessageObserver::class);
    }
}
