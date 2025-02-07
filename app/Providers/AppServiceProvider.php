<?php
namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
  /**
   * Register any application services.
   */
  public function register(): void {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void {
    Vite::prefetch(concurrency: 3);
    Relation::morphMap([
      'student' => 'App\Models\UenStudentView',
      'class'   => 'App\Models\UenClassView',
    ]);
  }
}
