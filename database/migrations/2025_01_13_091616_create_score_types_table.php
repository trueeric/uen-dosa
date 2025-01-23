<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('score_types', function (Blueprint $table) {
      $table->id()->comment('主鍵');
      $table->string('score_type_code', 50)->unique()->comment('評分類別代碼');
      $table->string('score_type', 100)->comment('評分類別名稱');
      $table->string('memo', 190)->nullable()->comment('備註');
      $table->decimal('points', 5, 2)->default(0)->comment('分數');
      $table->unsignedTinyInteger('sort')->default(0)->comment('排序');
      $table->boolean('is_active')->default(true)->comment('是否啟用');
      $table->timestamps();
      $table->softDeletes();

      $table->index('score_type_code');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('score_types');
  }
};
