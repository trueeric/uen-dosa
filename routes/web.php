<?php

use App\Http\Controllers\DatabaseTestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UenScoreRecordController;
use App\Http\Controllers\UenScoreTypeSubTotalController;
use App\Http\Controllers\UenStudentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin'       => Route::has('login'),
    'canRegister'    => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion'     => PHP_VERSION,
  ]);
});

Route::get('/dashboard', function () {
  return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  // 一般路由使用 Inertia 渲染
  Route::get('/database-test', [DatabaseTestController::class, 'index'])
    ->name('database.test');

  // API 路由用於獲取資料庫狀態
  Route::get('/api/database-status', [DatabaseTestController::class, 'checkConnections'])
    ->name('api.database.status');

  // 學生基本資料
  Route::get('/uen-students', [UenStudentController::class, 'index'])
    ->name('uen-students.index');
  Route::get('/uen-students/create', [UenStudentController::class, 'create'])
    ->name('uen-students.create');

  // 榮譽競賽評分 - 標準資源路由
  Route::resource('uen-score-records', UenScoreRecordController::class);

  // 添加軟刪除的恢復路由
  Route::post('/uen-score-records/{id}/restore', [UenScoreRecordController::class, 'restore'])
    ->name('uen-score-records.restore');

  // 榮譽競賽評分彙總表
  Route::get('/uen-score-type-sub-total', [UenScoreTypeSubTotalController::class, 'index'])
    ->name('uen-score-type-sub-total.index');

});

require __DIR__ . '/auth.php';
