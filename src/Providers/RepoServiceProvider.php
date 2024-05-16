<?php
namespace Vulgar\Repo\Providers;

use Illuminate\Support\ServiceProvider;
use Vulgar\Repo\Services\RepoService;

class RepoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(RepoService::class, function($app) {
            return new RepoService();

        });
        $this->mergeConfigFrom(
            __DIR__.'/../../config/repo.php', 'repo'
        );
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/repo.php' => config_path('repo.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Vulgar\Repo\Commands\FetchRepos::class,
            ]);
        }
    }
}
