<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Satker;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('agenda-kpisda', fn (User $user) => $user->canSeeAgendaKpisda());
        Gate::define('agenda-kabalai', fn (User $user) => $user->canSeeAgendaKabalai());

        // Sediakan daftar satker (+ jumlah paket) untuk menu sidebar.
        View::composer('layout.v_sidebar', function ($view) {
            $counts = [];

            if (Schema::hasTable('packets')) {
                $counts = \App\Models\Packet::query()
                    ->selectRaw('satker_group, COUNT(*) as jml')
                    ->groupBy('satker_group')
                    ->pluck('jml', 'satker_group')
                    ->toArray();
            }

            $view->with('satkerGroups', Satker::groups())
                 ->with('satkerCounts', $counts);
        });
    }
}
