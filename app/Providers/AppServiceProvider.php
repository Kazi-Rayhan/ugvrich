<?php

namespace App\Providers;

use App\Routing\LocalizedUrlGenerator;
use App\Support\Site;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Site::class);

        // route() should answer in the language being read, so the generator
        // is swapped for one that knows about the `en.` route names.
        $this->app->singleton('url', function ($app) {
            $url = new LocalizedUrlGenerator(
                $app['router']->getRoutes(),
                $app->rebinding('request', fn ($app, $request) => $app['url']->setRequest($request)),
                $app['config']['app.asset_url'],
            );

            $url->setSessionResolver(fn () => $app['session'] ?? null);
            $url->setKeyResolver(fn () => $app->make('config')->get('app.key'));

            $app->rebinding('routes', fn ($app, $routes) => $app['url']->setRoutes($routes));

            return $url;
        });
    }

    public function boot(): void
    {
        // Livewire stops temporary uploads at 12 MB, too small for the videos
        // the admin panel takes. Each field still sets its own, smaller limit.
        config([
            'livewire.temporary_file_upload.rules' => ['required', 'file', 'max:204800'],
            'livewire.temporary_file_upload.max_upload_time' => 30,
        ]);

        View::composer('*', function ($view) {
            $view->with('site', app(Site::class));
        });

        // Error pages render outside the web middleware group, so nothing has
        // shared an error bag yet and any @error directive would fatal. The
        // session middleware overwrites this on normal requests.
        View::share('errors', new ViewErrorBag);
    }
}
