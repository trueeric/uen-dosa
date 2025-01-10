<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DatabaseTestController extends Controller {
  public function index() {
    return Inertia::render('DatabaseTest/Index');
  }

  public function checkConnections() {
    try {
      $connections = ['mysql', 'uen_students_db', 'uen_staff_db'];
      $status      = [];

      foreach ($connections as $connection) {
        try {
          DB::connection($connection)->getPdo();
          $status[$connection] = [
            'status'   => 'Connected',
            'database' => config("database.connections.{$connection}.database"),
          ];
        } catch (\Exception $e) {
          Log::error("Database connection error for {$connection}: " . $e->getMessage());
          $status[$connection] = [
            'status' => 'Failed',
            'error'  => $e->getMessage(),
          ];
        }
      }

      return response()->json($status);

    } catch (\Exception $e) {
      Log::error('Database connection check failed: ' . $e->getMessage());
      return response()->json([
        'error'   => 'Database connection check failed',
        'message' => $e->getMessage(),
      ], 500);
    }
  }
}
