<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
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
        if (!env('IS_TOOL')) {
            app('url')->forceRootUrl(env('APP_URL'));

            \App\Category::disableSearchSyncing();
            \App\DataSet::disableSearchSyncing();
            \App\Document::disableSearchSyncing();
            \App\HelpPage::disableSearchSyncing();
            \App\HelpSection::disableSearchSyncing();
            \App\Organisation::disableSearchSyncing();
            \App\Page::disableSearchSyncing();
            \App\Resource::disableSearchSyncing();
            \App\Signal::disableSearchSyncing();
            \App\TermsOfUseRequest::disableSearchSyncing();
            \App\User::disableSearchSyncing();
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
