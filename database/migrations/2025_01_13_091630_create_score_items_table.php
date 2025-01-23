<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create('score_items', function (Blueprint $table) {
      $table->id()->comment('主鍵');
      $table->string('score_code', 50)->unique()->comment('評分項目代碼');
      $table->string('score_type_code', 50)->comment('評分類別代碼');
      $table->string('score_item', 100)->comment('評分項目名稱');
      $table->string('memo', 100)->nullable()->comment('備註');
      $table->decimal('points', 5, 2)->default(0)->comment('分數');
      $table->boolean('is_active')->default(true)->comment('是否啟用');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('score_type_code')
        ->references('score_type_code')
        ->on('score_types')
        ->onDelete('restrict');

      $table->index(['score_type_code', 'score_code']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists('score_items');
  }
};
