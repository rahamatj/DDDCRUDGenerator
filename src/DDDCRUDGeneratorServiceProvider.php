<?php

namespace Raham\DDDCRUDGen;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;
use Raham\DDDCRUDGen\MakeDDDCRUD;

class DDDCRUDGeneratorServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    if ($this->app->runningInConsole()) {
      $this->commands([
        MakeDDDCRUD::class,
      ]);
    }

    $this->publishes([
      __DIR__ . '/../stubs' => base_path('stubs/dddcrudgen'),
    ], 'dddcrudgen-stubs');

    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dddcrudgen');
  }
}
