<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!config('app.IS_TOOL')) {
            app('url')->forceRootUrl(config('app.APP_URL'));
        }

        Schema::defaultStringLength(191);

        if (!Collection::hasMacro('paginate')) {
            Collection::macro('paginate', function ($perPage = 15, $page = null, $options = []) {
                $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

                return (new LengthAwarePaginator(
                    $this->forPage($page, $perPage), $this->count(), $perPage, $page, $options
                ))->withPath('');
            });
        }

        \DB::listen(function ($query) {
            if ($query->time/1000 > 2) {
                \Log::error(' Time: '. $query->time/1000 .' Query: '. $query->sql);
            }
        });

        Validator::extend('phone_number', function($attribute, $value, $parameters) {
            return strlen($value) >= 3 && strlen($value) <= 15 && ctype_digit($value);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
