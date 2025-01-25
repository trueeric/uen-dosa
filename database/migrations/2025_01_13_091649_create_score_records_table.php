<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('uen_score_records', function (Blueprint $table) {
      $table->id();
      $table->string('target_type')->comment('記錄對象類型: student/class');
      $table->string('target_id', 10)->comment('學生ID或班級ID');
      $table->string('target_no', 10)->comment('輸入的學號或班級號碼');
      $table->unsignedMediumInteger('recorded_by');
      $table->string('score_no')->comment('違規碼 t01+秩序碼, t02整潔碼');
      $table->text('description')->nullable();
      $table->date('score_date')->comment('登記日期');
      $table->enum('status', ['pending', 'confirmed', 'cancelled']);
      $table->timestamps();

      // 索引
      $table->index(['target_type', 'target_id']);
      $table->foreign('recorded_by')->references('id')->on('users');
      $table->foreign('score_no')->references('score_no')->on('uen_score_items');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('uen_score_records');
  }
};
