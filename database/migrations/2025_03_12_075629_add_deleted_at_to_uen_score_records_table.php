<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::table('uen_score_records', function (Blueprint $table) {
      $table->softDeletes(); // 這會添加 deleted_at 欄位 //
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::table('uen_score_records', function (Blueprint $table) {
      $table->dropSoftDeletes(); // 這會移除 deleted_at 欄位//
    });
  }
};
