<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('attachments', function (Blueprint $table) {
      $table->id()->comment('主鍵');
      $table->string('student_no', 20)->comment('學生ID');
      $table->unsignedBigInteger('recorded_by')->comment('記錄者ID');
      $table->string('score_code', 50)->comment('評分項目代碼');
      $table->string('memo')->nullable()->comment('備註');
      $table->date('date')->comment('評分日期');
      $table->enum('status', ['pending', 'confirmed', 'cancelled'])
        ->default('pending')
        ->comment('狀態');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('student_no')
        ->references('student_no')
        ->on('uen_students')
        ->onDelete('restrict');

      $table->foreign('recorded_by')
        ->references('id')
        ->on('users')
        ->onDelete('restrict');

      $table->foreign('score_code')
        ->references('score_code')
        ->on('score_items')
        ->onDelete('restrict');

      $table->index(['student_id', 'date']);
      $table->index('status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('attachments');
  }
};
