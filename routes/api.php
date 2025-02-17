<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UenScoreRecordController;

Route::apiResource('score-records', UenScoreRecordController::class);
